<?php

namespace App\Services;

use App\Models\Student;
use App\Models\StudentClass;
use App\Models\StudentProgression;

class StudentProgressionService
{
    public function promote($studentId, $nextClassId, $academicYearId, $userId = null)
    {
        $student = Student::findOrFail($studentId);

        $record = StudentProgression::create([
            'student_id' => $studentId,
            'from_class_id' => $student->student_class_id,
            'to_class_id' => $nextClassId,
            'academic_year_id' => $academicYearId,
            'action' => 'promoted',
            'processed_by' => $userId,
        ]);

        // Update student class
        $student->update([
            'student_class_id' => $nextClassId
        ]);

        return $record;
    }

    public function repeat($studentId, $academicYearId, $userId = null)
    {
        $student = Student::findOrFail($studentId);

        return StudentProgression::create([
            'student_id' => $studentId,
            'from_class_id' => $student->student_class_id,
            'to_class_id' => $student->student_class_id,
            'academic_year_id' => $academicYearId,
            'action' => 'repeated',
            'processed_by' => $userId,
        ]);
    }

    public function graduate($studentId, $academicYearId, $userId = null)
    {
        $student = Student::findOrFail($studentId);

        StudentProgression::create([
            'student_id' => $studentId,
            'from_class_id' => $student->student_class_id,
            'to_class_id' => null,
            'academic_year_id' => $academicYearId,
            'action' => 'graduated',
            'processed_by' => $userId,
        ]);

        // Mark student as inactive / graduated
        $student->update([
            'status' => 'graduated'
        ]);
    }
}