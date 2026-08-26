<?php

namespace App\Http\Controllers;

use App\Models\Student;
use App\Models\StudentClass;
use App\Models\AcademicYear;
use App\Models\StudentProgression;
use App\Models\StudentClassAssignment;
use App\Models\ClassProgression;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class StudentProgressionController extends Controller
{
    /**
     * Display the student progression management page.
     */
    public function index(Request $request)
    {
        $classes = StudentClass::orderBy('name')->get();
        $academicYears = AcademicYear::orderBy('name', 'desc')->get();
        
        $students = collect();
        $selectedClass = null;
        $selectedYear = null;
        
        if ($request->class_id && $request->academic_year_id) {
            $selectedClass = StudentClass::find($request->class_id);
            $selectedYear = AcademicYear::find($request->academic_year_id);
            
            // Get students through class assignments
            $students = Student::whereHas('classAssignments', function($query) use ($request) {
                $query->where('student_class_id', $request->class_id)
                      ->where('is_current', true)
                      ->where('status', 'active');
            })
            ->with(['classAssignments' => function($query) use ($request) {
                $query->where('student_class_id', $request->class_id)
                      ->where('is_current', true)
                      ->where('status', 'active');
            }])
            ->orderBy('last_name')
            ->orderBy('first_name')
            ->get();
        }

        return view('student-progressions.index', compact(
            'classes', 
            'academicYears', 
            'students', 
            'selectedClass',
            'selectedYear'
        ));
    }

    /**
     * Process student progressions (promote, repeat, or graduate).
     */
    public function process(Request $request)
    {
        $request->validate([
            'academic_year_id' => 'required|exists:academic_years,id',
            'students' => 'required|array',
            'students.*.action' => 'required|in:promoted,repeated,graduated',
        ]);

        $processed = 0;
        $errors = [];
        $successes = [];
        
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
                $currentClass = StudentClass::find($fromClassId);

                // Validate graduation eligibility (only JHS 3 can graduate)
                if ($action === 'graduated') {
                    if (!$currentClass || !str_contains(strtoupper($currentClass->name), 'JHS 3')) {
                        $errors[] = "{$student->first_name} {$student->last_name} is not in JHS 3 and cannot graduate.";
                        continue;
                    }
                }

                // Get progression path for promotion
                $toClassId = null;
                if ($action === 'promoted') {
                    $progression = ClassProgression::where('from_class_id', $fromClassId)->first();
                    
                    if (!$progression) {
                        $errors[] = "No progression path defined for {$student->first_name} {$student->last_name}.";
                        continue;
                    }
                    
                    $toClassId = $progression->to_class_id;
                }

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

                // Process based on action
                if ($action === 'promoted' && !empty($toClassId)) {
                    // Mark old assignment as NOT current
                    $currentAssignment->update([
                        'is_current' => false,
                        'status' => 'completed'
                    ]);
                    
                    // Create NEW class assignment for promoted class
                    StudentClassAssignment::create([
                        'student_id' => $student->id,
                        'student_class_id' => $toClassId,
                        'academic_year_id' => $request->academic_year_id,
                        'is_current' => true,
                        'status' => 'active',
                    ]);
                    
                    // Update the student's direct class_id
                    $student->update(['student_class_id' => $toClassId]);
                    
                    $processed++;
                    $successes[] = "{$student->first_name} {$student->last_name} promoted to {$currentClass->name} → " . StudentClass::find($toClassId)->name;
                    
                } elseif ($action === 'repeated') {
                    // Student repeats - keep same class, update academic year
                    $currentAssignment->update([
                        'academic_year_id' => $request->academic_year_id,
                    ]);
                    
                    // Ensure student_class_id stays the same
                    $student->update(['student_class_id' => $fromClassId]);
                    
                    $processed++;
                    $successes[] = "{$student->first_name} {$student->last_name} repeated {$currentClass->name}";
                    
                } elseif ($action === 'graduated') {
                    // Student graduated - remove from active assignments
                    $currentAssignment->update([
                        'is_current' => false,
                        'status' => 'graduated'
                    ]);
                    
                    // Remove class from student
                    $student->update(['student_class_id' => null]);
                    
                    $processed++;
                    $successes[] = "{$student->first_name} {$student->last_name} graduated from {$currentClass->name}";
                }
            }
            
            DB::commit();
            
            // Prepare response message
            if ($processed > 0) {
                $message = $processed == 1 
                    ? "1 student processed successfully." 
                    : "{$processed} students processed successfully.";
                
                if (!empty($errors)) {
                    $message .= ' However, some students were skipped: ' . implode(', ', $errors);
                }
                
                // Log success details
                Log::info('Student progression processed', [
                    'processed' => $processed,
                    'successes' => $successes,
                    'errors' => $errors
                ]);
                
                return redirect()
                    ->route('student-progressions.index')
                    ->with('success', $message)
                    ->with('progression_details', $successes);
                    
            } else {
                $errorMsg = !empty($errors) 
                    ? 'No students were processed. Errors: ' . implode(', ', $errors) 
                    : 'No students were selected for processing.';
                    
                return redirect()
                    ->route('student-progressions.index')
                    ->with('error', $errorMsg);
            }
            
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Progression error: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
                'request' => $request->all()
            ]);
            
            return redirect()
                ->route('student-progressions.index')
                ->with('error', 'Error processing students: ' . $e->getMessage());
        }
    }

    /**
     * Bulk promote students from one class to another.
     */
    public function bulkPromote(Request $request)
    {
        $request->validate([
            'from_class_id' => 'required|exists:student_classes,id',
            'to_class_id' => 'required|exists:student_classes,id',
            'academic_year_id' => 'required|exists:academic_years,id',
            'student_ids' => 'required|array',
            'student_ids.*' => 'exists:students,id',
        ]);
        
        $processed = 0;
        $errors = [];
        
        DB::beginTransaction();
        
        try {
            $fromClass = StudentClass::find($request->from_class_id);
            $toClass = StudentClass::find($request->to_class_id);
            $academicYear = AcademicYear::find($request->academic_year_id);
            
            foreach ($request->student_ids as $studentId) {
                $student = Student::find($studentId);
                
                if (!$student) {
                    $errors[] = "Student ID {$studentId} not found.";
                    continue;
                }
                
                // Get current class assignment
                $currentAssignment = StudentClassAssignment::where('student_id', $student->id)
                    ->where('is_current', true)
                    ->first();
                
                if (!$currentAssignment) {
                    $errors[] = "Student {$student->first_name} {$student->last_name} has no active assignment.";
                    continue;
                }
                
                // Verify student is in the correct class
                if ($currentAssignment->student_class_id != $request->from_class_id) {
                    $errors[] = "Student {$student->first_name} {$student->last_name} is not in {$fromClass->name}.";
                    continue;
                }
                
                // Mark old assignment as not current
                $currentAssignment->update([
                    'is_current' => false,
                    'status' => 'promoted'
                ]);
                
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
            
            DB::commit();
            
            $message = $processed == 1 
                ? "1 student promoted successfully from {$fromClass->name} to {$toClass->name}." 
                : "{$processed} students promoted successfully from {$fromClass->name} to {$toClass->name}.";
            
            if (!empty($errors)) {
                $message .= ' However, some students were skipped: ' . implode(', ', $errors);
            }
            
            return redirect()
                ->route('student-classes.show', $request->from_class_id)
                ->with('success', $message);
                
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Bulk promotion error: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
                'request' => $request->all()
            ]);
            
            return redirect()
                ->back()
                ->with('error', 'Error promoting students: ' . $e->getMessage());
        }
    }

    /**
     * Get students for a specific class (AJAX endpoint).
     */
    public function getStudents(Request $request)
    {
        $request->validate([
            'class_id' => 'required|exists:student_classes,id',
            'academic_year_id' => 'required|exists:academic_years,id',
        ]);
        
        $students = Student::whereHas('classAssignments', function($query) use ($request) {
            $query->where('student_class_id', $request->class_id)
                  ->where('academic_year_id', $request->academic_year_id)
                  ->where('is_current', true)
                  ->where('status', 'active');
        })
        ->with(['classAssignments' => function($query) use ($request) {
            $query->where('student_class_id', $request->class_id)
                  ->where('academic_year_id', $request->academic_year_id)
                  ->where('is_current', true)
                  ->where('status', 'active');
        }])
        ->orderBy('last_name')
        ->orderBy('first_name')
        ->get(['id', 'first_name', 'last_name', 'student_id']);
        
        return response()->json([
            'success' => true,
            'students' => $students
        ]);
    }

    /**
     * Get progression history for a student.
     */
    public function history($studentId)
    {
        $student = Student::findOrFail($studentId);
        
        $progressions = StudentProgression::with(['fromClass', 'toClass', 'academicYear', 'processor'])
            ->where('student_id', $studentId)
            ->orderBy('created_at', 'desc')
            ->get();
        
        return view('student-progressions.history', compact('student', 'progressions'));
    }

    /**
     * Display progression statistics.
     */
    public function statistics(Request $request)
    {
        $totalPromoted = StudentProgression::where('action', 'promoted')->count();
        $totalRepeated = StudentProgression::where('action', 'repeated')->count();
        $totalGraduated = StudentProgression::where('action', 'graduated')->count();
        
        $recentProgressions = StudentProgression::with(['student', 'fromClass', 'toClass'])
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();
        
        return view('student-progressions.statistics', compact(
            'totalPromoted',
            'totalRepeated',
            'totalGraduated',
            'recentProgressions'
        ));
    }
}