<?php

namespace App\Http\Controllers;

use App\Models\Student;
use App\Models\StudentClass;
use App\Models\AcademicYear;
use App\Models\StudentProgression;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class StudentProgressionController extends Controller
{
    public function index(Request $request)
    {
        $classes = StudentClass::orderBy('name')->get();
        $academicYears = AcademicYear::orderByDesc('id')->get();
        
        $students = collect();
        $selectedClass = null;
        
        if ($request->class_id && $request->academic_year_id) {
            $selectedClass = StudentClass::find($request->class_id);
            $students = Student::where('student_class_id', $request->class_id)
                ->orderBy('last_name')
                ->orderBy('first_name')
                ->get();
        }

        return view('progressions.index', compact('classes', 'academicYears', 'students', 'selectedClass'));
    }

    public function process(Request $request)
    {
        $request->validate([
            'academic_year_id' => 'required|exists:academic_years,id',
            'students' => 'required|array',
        ]);

        $processed = 0;
        $errors = [];
        
        DB::beginTransaction();
        
        try {
            foreach ($request->students as $studentId => $data) {
                // Skip if not selected
                if (!isset($data['selected']) || $data['selected'] != 1) {
                    continue;
                }

                $student = Student::findOrFail($studentId);
                $action = $data['action'] ?? null;
                
                if (!$action) {
                    continue;
                }
                
                // Create progression record
                StudentProgression::create([
                    'student_id' => $student->id,
                    'from_class_id' => $student->student_class_id,
                    'to_class_id' => $data['to_class_id'] ?? null,
                    'academic_year_id' => $request->academic_year_id,
                    'action' => $action,
                    'remarks' => $data['remarks'] ?? null,
                    'processed_by' => auth()->id(),
                ]);

                // Update student based on action
                if ($action === 'promoted' && !empty($data['to_class_id'])) {
                    $student->update(['student_class_id' => $data['to_class_id']]);
                    $processed++;
                } elseif ($action === 'repeated') {
                    // Student stays in same class - no change needed
                    $student->touch();
                    $processed++;
                } elseif ($action === 'graduated') {
                    $student->update([
                        'status' => 'graduated',
                        'student_class_id' => null
                    ]);
                    $processed++;
                }
            }
            
            DB::commit();
            
            if ($processed > 0) {
                $message = $processed == 1 
                    ? "$processed student processed successfully." 
                    : "$processed students processed successfully.";
                return redirect()
                    ->route('student-progressions.index')
                    ->with('success', $message);
            } else {
                return redirect()
                    ->route('student-progressions.index')
                    ->with('warning', 'No students were selected for processing.');
            }
            
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()
                ->route('student-progressions.index')
                ->with('error', 'Error processing students: ' . $e->getMessage());
        }
    }
}