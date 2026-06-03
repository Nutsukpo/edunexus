<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StudentResult extends Model
{
    protected $fillable = [
        'student_id',
        'student_class_id',
        'subject_id',
        'academic_year_id',
        'term_id',
        'class_score',
        'exam_score',
        'total_score',
        'grade',
        'remark'
    ];

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function studentClass()
    {
        return $this->belongsTo(StudentClass::class);
    }

    public function subject()
    {
        return $this->belongsTo(Subject::class);
    }

    public function academicYear()
    {
        return $this->belongsTo(AcademicYear::class);
    }

    public function term()
    {
        return $this->belongsTo(Term::class);
    }


        
}