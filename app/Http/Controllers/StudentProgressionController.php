<?php

namespace App\Http\Controllers;

use App\Models\Student;
use App\Models\StudentClass;
use App\Models\AcademicYear;
use App\Models\StudentProgression;
use App\Models\StudentClassAssignment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class StudentProgressionController extends Controller
{
    public function index(Request $request)
    {
        $classes = StudentClass::orderBy('name')->get();
        $academicYears = AcademicYear::orderBy('name', 'desc')->get();
        
        $students = collect();
        $selectedClass = null;
        
        if ($request->class_id && $request->academic_year_id) {
            $selectedClass = StudentClass::find($request->class_id);
            
            // Get students through class assignments
            $students = Student::whereHas('classAssignments', function($query) use ($request) {
                $query->where('student_class_id', $request->class_id)
                      ->where('is_current', true);
            })
            ->orderBy('last_name')
            ->orderBy('first_name')
            ->get();
        }

        return view('student-progressions.index', compact('classes', 'academicYears', 'students', 'selectedClass'));
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

                // Find the student
                $student = Student::find($studentId);
                
                if (!$student) {
                    $errors[] = "Student with ID {$studentId} not found.";
                    continue;
                }
                
                $action = $data['action'] ?? null;
                
                if (!$action) {
                    $errors[] = "No action selected for student {$student->first_name} {$student->last_name}.";
                    continue;
                }
                
                // Get current class assignment
                $currentAssignment = StudentClassAssignment::where('student_id', $student->id)
                    ->where('is_current', true)
                    ->first();
                
                if (!$currentAssignment) {
                    $errors[] = "Student {$student->first_name} {$student->last_name} has no active class assignment.";
                    continue;
                }
                
                $fromClassId = $currentAssignment->student_class_id;
                $toClassId = $data['to_class_id'] ?? null;
                
                // Create progression record
                StudentProgression::create([
                    'student_id' => $student->id,
                    'from_class_id' => $fromClassId,
                    'to_class_id' => $toClassId,
                    'academic_year_id' => $request->academic_year_id,
                    'action' => $action,
                    'remarks' => $data['remarks'] ?? null,
                    'processed_by' => auth()->id(),
                ]);

                // Update based on action
                if ($action === 'promoted' && !empty($toClassId)) {
                    // Mark old assignment as NOT current
                    $currentAssignment->update(['is_current' => false]);
                    
                    // Create NEW class assignment for promoted class
                    StudentClassAssignment::create([
                        'student_id' => $student->id,
                        'student_class_id' => $toClassId,
                        'academic_year_id' => $request->academic_year_id,
                        'is_current' => true,
                        'status' => 'active',
                    ]);
                    
                    // IMPORTANT: Update the student's direct class_id
                    $student->update(['student_class_id' => $toClassId]);
                    
                    $processed++;
                    
                } elseif ($action === 'repeated') {
                    // Student repeats - keep same class, update academic year
                    $currentAssignment->update([
                        'academic_year_id' => $request->academic_year_id,
                    ]);
                    
                    // Ensure student_class_id stays the same
                    $student->update(['student_class_id' => $fromClassId]);
                    
                    $processed++;
                    
                } elseif ($action === 'graduated') {
                    // Student graduated - remove from active assignments
                    $currentAssignment->update([
                        'is_current' => false,
                        'status' => 'graduated'
                    ]);
                    
                    // Remove class from student
                    $student->update(['student_class_id' => null]);
                    
                    $processed++;
                }
            }
            
            DB::commit();
            
            if ($processed > 0) {
                $message = $processed == 1 
                    ? "$processed student processed successfully." 
                    : "$processed students processed successfully.";
                    
                if (!empty($errors)) {
                    $message .= ' However, some students were skipped: ' . implode(', ', $errors);
                }
                
                return redirect()
                    ->route('student-progressions.index')
                    ->with('success', $message);
            } else {
                $errorMsg = !empty($errors) ? implode(', ', $errors) : 'No students were selected for processing.';
                return redirect()
                    ->route('student-progressions.index')
                    ->with('error', $errorMsg);
            }
            
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Progression error: ' . $e->getMessage());
            
            return redirect()
                ->route('student-progressions.index')
                ->with('error', 'Error processing students: ' . $e->getMessage());
        }
    }

    public function bulkPromote(Request $request)
    {
        $request->validate([
            'from_class_id' => 'required|exists:student_classes,id',
            'to_class_id' => 'required|exists:student_classes,id',
            'academic_year_id' => 'required|exists:academic_years,id',
            'student_ids' => 'required|array',
        ]);
        
        $processed = 0;
        
        DB::beginTransaction();
        
        try {
            foreach ($request->student_ids as $studentId) {
                $student = Student::findOrFail($studentId);
                
                // Get current class assignment
                $currentAssignment = StudentClassAssignment::where('student_id', $student->id)
                    ->where('is_current', true)
                    ->first();
                
                if ($currentAssignment) {
                    // Mark old assignment as not current
                    $currentAssignment->update(['is_current' => false]);
                    
                    // Create new assignment
                    StudentClassAssignment::create([
                        'student_id' => $student->id,
                        'student_class_id' => $request->to_class_id,
                        'academic_year_id' => $request->academic_year_id,
                        'is_current' => true,
                        'status' => 'active',
                    ]);
                    
                    // Update student's direct class reference
                    $student->update(['student_class_id' => $request->to_class_id]);
                    
                    // Create progression record
                    StudentProgression::create([
                        'student_id' => $student->id,
                        'from_class_id' => $request->from_class_id,
                        'to_class_id' => $request->to_class_id,
                        'academic_year_id' => $request->academic_year_id,
                        'action' => 'promoted',
                        'remarks' => $request->remarks[$studentId] ?? null,
                        'processed_by' => auth()->id(),
                    ]);
                    
                    $processed++;
                }
            }
            
            DB::commit();
            
            return redirect()->route('student-classes.show', $request->from_class_id)
                ->with('success', "$processed student(s) promoted successfully.");
                
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Error promoting students: ' . $e->getMessage());
        }
    }
}