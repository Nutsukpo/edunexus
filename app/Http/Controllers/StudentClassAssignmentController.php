<?php

namespace App\Http\Controllers;

use App\Models\Student;
use App\Models\StudentClass;
use App\Models\AcademicYear;
use App\Models\StudentClassAssignment;
use App\Models\Attendance;
use App\Models\AttendanceSession;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class StudentClassAssignmentController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | INDEX - SHOW ALL ASSIGNMENTS
    |--------------------------------------------------------------------------
    */
    public function index()
    {
        $assignments = StudentClassAssignment::with([
            'student',
            'studentClass',
            'academicYear'
        ])->latest()->paginate(15);

        return view(
            'student_class_assignments.index',
            compact('assignments')
        );
    }

    /*
    |--------------------------------------------------------------------------
    | CREATE FORM
    |--------------------------------------------------------------------------
    */
    public function create()
    {
        // Simplified: Only exclude graduated students
        $graduatedStudentIds = StudentClassAssignment::where('status', 'graduated')
            ->pluck('student_id')
            ->toArray();
    
        // Get students who are not graduated
        $students = Student::whereNotIn('id', $graduatedStudentIds)
            ->orderBy('first_name')
            ->get();
            
        $classes = StudentClass::orderBy('name')->get();
        $academicYears = AcademicYear::orderBy('name', 'desc')->get();
    
        return view('student_class_assignments.create', compact('students', 'classes', 'academicYears'));
    }

    /*
    |--------------------------------------------------------------------------
    | STORE ASSIGNMENT
    |--------------------------------------------------------------------------
    */
    public function store(Request $request)
    {
        $request->validate([
            'student_id'        => 'required|exists:students,id',
            'student_class_id'  => 'required|exists:student_classes,id',
            'academic_year_id'  => 'nullable|exists:academic_years,id',
        ]);

        // Check if student is graduated
        $isGraduated = StudentClassAssignment::where('student_id', $request->student_id)
            ->where('status', 'graduated')
            ->exists();

        if ($isGraduated) {
            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'This student has already graduated and cannot be assigned to a class.');
        }

        // Check if student already has an active assignment
        $existingAssignment = StudentClassAssignment::where('student_id', $request->student_id)
            ->where('is_current', true)
            ->where('status', 'active')
            ->first();

        if ($existingAssignment) {
            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'This student already has an active class assignment. Please deactivate the current assignment first.');
        }

        // Check if student is already assigned to the same class and academic year
        $duplicateAssignment = StudentClassAssignment::where('student_id', $request->student_id)
            ->where('student_class_id', $request->student_class_id)
            ->where('academic_year_id', $request->academic_year_id)
            ->where('status', 'active')
            ->exists();

        if ($duplicateAssignment) {
            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'This student is already assigned to this class for the selected academic year.');
        }

        DB::beginTransaction();

        try {
            /*
            |----------------------------------------------------------
            | STEP 1: DEACTIVATE CURRENT ASSIGNMENT
            |----------------------------------------------------------
            */
            StudentClassAssignment::where('student_id', $request->student_id)
                ->where('is_current', true)
                ->update([
                    'is_current' => false,
                    'status'     => 'inactive'
                ]);

            /*
            |----------------------------------------------------------
            | STEP 2: CREATE NEW ASSIGNMENT
            |----------------------------------------------------------
            */
            $assignment = StudentClassAssignment::create([
                'student_id'        => $request->student_id,
                'student_class_id'  => $request->student_class_id,
                'academic_year_id'  => $request->academic_year_id,
                'status'            => 'active',
                'is_current'        => true,
                'assigned_date'     => now(),
            ]);

            DB::commit();

            return redirect()
                ->route('student-class-assignments.index')
                ->with('success', "Student assigned to class successfully.");

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Assignment creation error: ' . $e->getMessage());
            
            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Error creating assignment: ' . $e->getMessage());
        }
    }

    /*
    |--------------------------------------------------------------------------
    | SHOW
    |--------------------------------------------------------------------------
    */
    public function show($id)
    {
        $assignment = StudentClassAssignment::with([
            'student',
            'studentClass',
            'academicYear'
        ])->findOrFail($id);

        return view(
            'student_class_assignments.show',
            compact('assignment')
        );
    }

    /*
    |--------------------------------------------------------------------------
    | EDIT
    |--------------------------------------------------------------------------
    */
    public function edit($id)
    {
        $assignment = StudentClassAssignment::findOrFail($id);

        // Check if student is graduated
        if ($assignment->status === 'graduated') {
            return redirect()
                ->route('student-class-assignments.index')
                ->with('error', 'Cannot edit a graduated student\'s assignment.');
        }

        $classes = StudentClass::orderBy('name')->get();
        $academicYears = AcademicYear::orderBy('name', 'desc')->get();

        return view(
            'student_class_assignments.edit',
            compact('assignment', 'classes', 'academicYears')
        );
    }

    /*
    |--------------------------------------------------------------------------
    | UPDATE - WITH VALIDATION
    |--------------------------------------------------------------------------
    */
    public function update(Request $request, $id)
    {
        $request->validate([
            'student_class_id' => 'required|exists:student_classes,id',
            'academic_year_id' => 'nullable|exists:academic_years,id',
        ]);

        $assignment = StudentClassAssignment::findOrFail($id);

        // Check if student is graduated
        if ($assignment->status === 'graduated') {
            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Cannot update a graduated student\'s assignment.');
        }

        // Check if student already has another active assignment (excluding current one)
        $existingAssignment = StudentClassAssignment::where('student_id', $assignment->student_id)
            ->where('is_current', true)
            ->where('status', 'active')
            ->where('id', '!=', $id)
            ->first();

        if ($existingAssignment) {
            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'This student already has another active class assignment.');
        }

        // Check for duplicate assignment (same class, same year)
        $duplicateAssignment = StudentClassAssignment::where('student_id', $assignment->student_id)
            ->where('student_class_id', $request->student_class_id)
            ->where('academic_year_id', $request->academic_year_id)
            ->where('status', 'active')
            ->where('id', '!=', $id)
            ->exists();

        if ($duplicateAssignment) {
            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'This student is already assigned to this class for the selected academic year.');
        }

        DB::beginTransaction();

        try {
            $assignment->update([
                'student_class_id' => $request->student_class_id,
                'academic_year_id' => $request->academic_year_id ?? $assignment->academic_year_id,
                'status' => 'active'
            ]);

            DB::commit();

            return redirect()
                ->route('student-class-assignments.index')
                ->with('success', 'Assignment updated successfully.');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Assignment update error: ' . $e->getMessage());
            
            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Error updating assignment: ' . $e->getMessage());
        }
    }

    /*
    |--------------------------------------------------------------------------
    | DESTROY - SOFT DELETE
    |--------------------------------------------------------------------------
    */
    public function destroy($id)
    {
        $assignment = StudentClassAssignment::findOrFail($id);

        // Prevent deletion of graduated student assignments
        if ($assignment->status === 'graduated') {
            return redirect()
                ->back()
                ->with('error', 'Cannot delete a graduated student\'s assignment.');
        }

        DB::beginTransaction();

        try {
            $assignment->delete();

            DB::commit();

            return redirect()
                ->route('student-class-assignments.index')
                ->with('success', 'Assignment deleted successfully.');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Assignment deletion error: ' . $e->getMessage());
            
            return redirect()
                ->back()
                ->with('error', 'Error deleting assignment: ' . $e->getMessage());
        }
    }

    /*
    |--------------------------------------------------------------------------
    | GET STUDENTS BY CLASS (AJAX)
    |--------------------------------------------------------------------------
    */
    public function getStudentsByClass(Request $request)
    {
        $request->validate([
            'class_id' => 'required|exists:student_classes,id',
            'academic_year_id' => 'nullable|exists:academic_years,id',
        ]);

        $query = StudentClassAssignment::with(['student'])
            ->where('student_class_id', $request->class_id)
            ->where('is_current', true)
            ->where('status', 'active');

        if ($request->academic_year_id) {
            $query->where('academic_year_id', $request->academic_year_id);
        }

        $students = $query->get()->map(function($assignment) {
            return [
                'id' => $assignment->student->id,
                'student_id' => $assignment->student->student_id,
                'full_name' => $assignment->student->full_name,
                'email' => $assignment->student->email,
            ];
        });

        return response()->json([
            'success' => true,
            'students' => $students
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | GET AVAILABLE STUDENTS FOR ASSIGNMENT (AJAX)
    |--------------------------------------------------------------------------
    */
    public function getAvailableStudents(Request $request)
    {
        // Get IDs of students with active assignments
        $assignedStudentIds = StudentClassAssignment::where('is_current', true)
            ->where('status', 'active')
            ->pluck('student_id')
            ->toArray();

        // Get IDs of graduated students
        $graduatedStudentIds = StudentClassAssignment::where('status', 'graduated')
            ->pluck('student_id')
            ->toArray();

        // Get students who are not assigned and not graduated
        $students = Student::whereNotIn('id', array_merge($assignedStudentIds, $graduatedStudentIds))
            ->orderBy('first_name')
            ->get(['id', 'student_id', 'first_name', 'last_name', 'email']);

        return response()->json([
            'success' => true,
            'students' => $students
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | BULK ASSIGN STUDENTS TO A CLASS
    |--------------------------------------------------------------------------
    */
    public function bulkAssign(Request $request)
    {
        $request->validate([
            'student_ids' => 'required|array',
            'student_ids.*' => 'exists:students,id',
            'student_class_id' => 'required|exists:student_classes,id',
            'academic_year_id' => 'nullable|exists:academic_years,id',
        ]);

        $processed = 0;
        $errors = [];
        $skippedGraduated = [];

        DB::beginTransaction();

        try {
            foreach ($request->student_ids as $studentId) {
                // Check if student is graduated
                $isGraduated = StudentClassAssignment::where('student_id', $studentId)
                    ->where('status', 'graduated')
                    ->exists();

                if ($isGraduated) {
                    $student = Student::find($studentId);
                    $skippedGraduated[] = $student->full_name ?? $studentId;
                    continue;
                }

                // Check if student already has an active assignment
                $existing = StudentClassAssignment::where('student_id', $studentId)
                    ->where('is_current', true)
                    ->where('status', 'active')
                    ->first();

                if ($existing) {
                    $student = Student::find($studentId);
                    $errors[] = "Student {$student->full_name} already has an active assignment.";
                    continue;
                }

                // Deactivate any old assignments
                StudentClassAssignment::where('student_id', $studentId)
                    ->where('is_current', true)
                    ->update([
                        'is_current' => false,
                        'status' => 'inactive'
                    ]);

                // Create new assignment
                StudentClassAssignment::create([
                    'student_id' => $studentId,
                    'student_class_id' => $request->student_class_id,
                    'academic_year_id' => $request->academic_year_id,
                    'status' => 'active',
                    'is_current' => true,
                    'assigned_date' => now(),
                ]);

                $processed++;
            }

            DB::commit();

            $message = "{$processed} students assigned successfully.";
            
            if (!empty($skippedGraduated)) {
                $message .= ' Skipped ' . count($skippedGraduated) . ' graduated students: ' . implode(', ', $skippedGraduated);
            }
            
            if (!empty($errors)) {
                $message .= ' Errors: ' . implode(', ', $errors);
            }

            return redirect()
                ->route('student-class-assignments.index')
                ->with('success', $message);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Bulk assignment error: ' . $e->getMessage());
            
            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Error assigning students: ' . $e->getMessage());
        }
    }

    /*
    |--------------------------------------------------------------------------
    | DEACTIVATE ASSIGNMENT
    |--------------------------------------------------------------------------
    */
    public function deactivate($id)
    {
        $assignment = StudentClassAssignment::findOrFail($id);

        // Prevent deactivation of graduated students
        if ($assignment->status === 'graduated') {
            return redirect()
                ->back()
                ->with('error', 'Cannot deactivate a graduated student\'s assignment.');
        }

        DB::beginTransaction();

        try {
            $assignment->update([
                'is_current' => false,
                'status' => 'inactive'
            ]);

            DB::commit();

            return redirect()
                ->route('student-class-assignments.index')
                ->with('success', 'Assignment deactivated successfully.');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Assignment deactivation error: ' . $e->getMessage());
            
            return redirect()
                ->back()
                ->with('error', 'Error deactivating assignment: ' . $e->getMessage());
        }
    }

    /*
    |--------------------------------------------------------------------------
    | REACTIVATE ASSIGNMENT
    |--------------------------------------------------------------------------
    */
    public function reactivate($id)
    {
        $assignment = StudentClassAssignment::findOrFail($id);

        // Prevent reactivation of graduated students
        if ($assignment->status === 'graduated') {
            return redirect()
                ->back()
                ->with('error', 'Cannot reactivate a graduated student\'s assignment.');
        }

        // Check if student already has an active assignment
        $existing = StudentClassAssignment::where('student_id', $assignment->student_id)
            ->where('is_current', true)
            ->where('status', 'active')
            ->where('id', '!=', $id)
            ->first();

        if ($existing) {
            return redirect()
                ->back()
                ->with('error', 'Student already has an active assignment. Please deactivate it first.');
        }

        DB::beginTransaction();

        try {
            $assignment->update([
                'is_current' => true,
                'status' => 'active'
            ]);

            DB::commit();

            return redirect()
                ->route('student-class-assignments.index')
                ->with('success', 'Assignment reactivated successfully.');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Assignment reactivation error: ' . $e->getMessage());
            
            return redirect()
                ->back()
                ->with('error', 'Error reactivating assignment: ' . $e->getMessage());
        }
    }

    /*
    |--------------------------------------------------------------------------
    | GET STUDENT ASSIGNMENT HISTORY
    |--------------------------------------------------------------------------
    */
    public function getStudentHistory($studentId)
    {
        $student = Student::findOrFail($studentId);
        
        $assignments = StudentClassAssignment::with(['studentClass', 'academicYear'])
            ->where('student_id', $studentId)
            ->orderBy('created_at', 'desc')
            ->get();

        return view('student_class_assignments.history', compact('student', 'assignments'));
    }

    /*
    |--------------------------------------------------------------------------
    | GET ATTENDANCE STATISTICS FOR A CLASS
    |--------------------------------------------------------------------------
    */
    public function getAttendanceStats($classId)
    {
        try {
            // Get all students in the class
            $assignments = StudentClassAssignment::with('student')
                ->where('student_class_id', $classId)
                ->where('is_current', true)
                ->where('status', 'active')
                ->get();

            $studentIds = $assignments->pluck('student_id')->toArray();
            $totalStudents = count($studentIds);

            // Get attendance sessions for this class
            $sessions = AttendanceSession::where('student_class_id', $classId)
                ->orderBy('attendance_date', 'desc')
                ->get();

            $totalSessions = $sessions->count();
            $attendanceData = [];

            // Calculate attendance for each student
            foreach ($assignments as $assignment) {
                $student = $assignment->student;
                $studentAttendance = Attendance::where('student_id', $student->id)
                    ->whereHas('attendanceSession', function($query) use ($classId) {
                        $query->where('student_class_id', $classId);
                    })
                    ->get();

                $present = $studentAttendance->where('status', 'present')->count();
                $absent = $studentAttendance->where('status', 'absent')->count();
                $late = $studentAttendance->where('status', 'late')->count();
                $excused = $studentAttendance->where('status', 'excused')->count();
                $total = $studentAttendance->count();

                $attendanceData[] = [
                    'student_id' => $student->id,
                    'student_name' => $student->first_name . ' ' . $student->last_name,
                    'student_number' => $student->student_id,
                    'present' => $present,
                    'absent' => $absent,
                    'late' => $late,
                    'excused' => $excused,
                    'total' => $total,
                    'rate' => $total > 0 ? round(($present / $total) * 100, 1) : 0
                ];
            }

            // Calculate class averages
            $classAverage = collect($attendanceData)->avg('rate') ?? 0;
            $totalPresent = collect($attendanceData)->sum('present');
            $totalAbsent = collect($attendanceData)->sum('absent');
            $totalLate = collect($attendanceData)->sum('late');
            $totalExcused = collect($attendanceData)->sum('excused');
            $totalAttendanceRecords = collect($attendanceData)->sum('total');

            return response()->json([
                'success' => true,
                'total_students' => $totalStudents,
                'total_sessions' => $totalSessions,
                'class_average' => round($classAverage, 1),
                'total_present' => $totalPresent,
                'total_absent' => $totalAbsent,
                'total_late' => $totalLate,
                'total_excused' => $totalExcused,
                'total_records' => $totalAttendanceRecords,
                'students' => $attendanceData
            ]);

        } catch (\Exception $e) {
            Log::error('Attendance stats error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error fetching attendance statistics: ' . $e->getMessage()
            ], 500);
        }
    }

    /*
    |--------------------------------------------------------------------------
    | GET STUDENT ATTENDANCE DETAILS
    |--------------------------------------------------------------------------
    */
    public function getStudentAttendance($studentId, $classId = null)
    {
        try {
            $student = Student::findOrFail($studentId);
            
            $query = Attendance::with(['attendanceSession.studentClass'])
                ->where('student_id', $studentId);

            if ($classId) {
                $query->whereHas('attendanceSession', function($q) use ($classId) {
                    $q->where('student_class_id', $classId);
                });
            }

            $records = $query->orderBy('created_at', 'desc')->get();

            $stats = [
                'present' => $records->where('status', 'present')->count(),
                'absent' => $records->where('status', 'absent')->count(),
                'late' => $records->where('status', 'late')->count(),
                'excused' => $records->where('status', 'excused')->count(),
                'total' => $records->count(),
                'rate' => $records->count() > 0 ? round(($records->where('status', 'present')->count() / $records->count()) * 100, 1) : 0
            ];

            return response()->json([
                'success' => true,
                'student' => [
                    'id' => $student->id,
                    'name' => $student->first_name . ' ' . $student->last_name,
                    'student_id' => $student->student_id
                ],
                'stats' => $stats,
                'records' => $records->map(function($record) {
                    return [
                        'date' => $record->attendanceSession->attendance_date ?? $record->created_at,
                        'class' => $record->attendanceSession->studentClass->name ?? 'N/A',
                        'status' => $record->status,
                        'remarks' => $record->remarks
                    ];
                })
            ]);

        } catch (\Exception $e) {
            Log::error('Student attendance error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error fetching student attendance: ' . $e->getMessage()
            ], 500);
        }
    }

    /*
    |--------------------------------------------------------------------------
    | GET ATTENDANCE SUMMARY CARDS FOR CLASS
    |--------------------------------------------------------------------------
    */
    public function getAttendanceSummary($classId)
    {
        try {
            // Get total students in class
            $totalStudents = StudentClassAssignment::where('student_class_id', $classId)
                ->where('is_current', true)
                ->where('status', 'active')
                ->count();

            // Get today's attendance
            $today = now()->toDateString();
            $todaySession = AttendanceSession::where('student_class_id', $classId)
                ->whereDate('attendance_date', $today)
                ->first();

            $todayPresent = 0;
            $todayAbsent = 0;
            $todayLate = 0;
            $todayExcused = 0;

            if ($todaySession) {
                $todayAttendance = Attendance::where('attendance_session_id', $todaySession->id)->get();
                $todayPresent = $todayAttendance->where('status', 'present')->count();
                $todayAbsent = $todayAttendance->where('status', 'absent')->count();
                $todayLate = $todayAttendance->where('status', 'late')->count();
                $todayExcused = $todayAttendance->where('status', 'excused')->count();
            }

            // Get overall attendance rate
            $totalAttendance = Attendance::whereHas('attendanceSession', function($q) use ($classId) {
                $q->where('student_class_id', $classId);
            })->count();

            $totalPresent = Attendance::whereHas('attendanceSession', function($q) use ($classId) {
                $q->where('student_class_id', $classId);
            })->where('status', 'present')->count();

            $overallRate = $totalAttendance > 0 ? round(($totalPresent / $totalAttendance) * 100, 1) : 0;

            // Get this week's attendance
            $weekStart = now()->startOfWeek()->toDateString();
            $weekEnd = now()->endOfWeek()->toDateString();

            $weekSessions = AttendanceSession::where('student_class_id', $classId)
                ->whereBetween('attendance_date', [$weekStart, $weekEnd])
                ->get();

            $weekTotal = 0;
            $weekPresent = 0;

            foreach ($weekSessions as $session) {
                $attendance = Attendance::where('attendance_session_id', $session->id)->get();
                $weekTotal += $attendance->count();
                $weekPresent += $attendance->where('status', 'present')->count();
            }

            $weekRate = $weekTotal > 0 ? round(($weekPresent / $weekTotal) * 100, 1) : 0;

            return response()->json([
                'success' => true,
                'total_students' => $totalStudents,
                'today' => [
                    'present' => $todayPresent,
                    'absent' => $todayAbsent,
                    'late' => $todayLate,
                    'excused' => $todayExcused,
                    'total' => $todayPresent + $todayAbsent + $todayLate + $todayExcused
                ],
                'overall' => [
                    'rate' => $overallRate,
                    'total' => $totalAttendance,
                    'present' => $totalPresent
                ],
                'week' => [
                    'rate' => $weekRate,
                    'total' => $weekTotal,
                    'present' => $weekPresent
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Attendance summary error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error fetching attendance summary: ' . $e->getMessage()
            ], 500);
        }
    }
}