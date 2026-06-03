<?php

namespace App\Http\Controllers;

use App\Models\StudentClass;
use App\Models\AcademicYear;
use App\Models\Staff;
use App\Models\Subject;
use App\Models\ClassSubjectStaff;
use App\Models\StudentClassAssignment;
// use App\Models\Student;
use Illuminate\Http\Request;
use App\Models\AttendanceSession;
// use App\Models\Attendance;
use App\Models\AttendanceRecord;
use Carbon\Carbon;

// use Illuminate\Support\Facades\DB;

class StudentClassController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | DISPLAY ALL CLASSES
    |--------------------------------------------------------------------------
    */
    public function index()
    {
        $classes = StudentClass::with([
            'academicYear',
            'classTeacher'
        ])->latest()->get();

        return view('student_classes.index', compact('classes'));
    }

    /*
    |--------------------------------------------------------------------------
    | SHOW CREATE FORM
    |--------------------------------------------------------------------------
    */
    public function create()
    {
        $academicYears = AcademicYear::latest()->get();
        $staff = Staff::orderBy('first_name')->get();

        return view('student_classes.create', compact(
            'academicYears',
            'staff'
        ));
    }

    /*
    |--------------------------------------------------------------------------
    | STORE CLASS
    |--------------------------------------------------------------------------
    */
        public function store(Request $request)
    {
        $validated = $request->validate([
            'academic_year_id' => 'required|exists:academic_years,id',
            'name'             => 'required|string|max:255',
            'education_type'   => 'required|string|max:255',
            'class_type'       => 'required|string|max:255',
            'stream'           => 'nullable|string|max:50',
            'staff_id'         => 'nullable|exists:staff,id',
            'capacity'         => 'nullable|integer|min:1',
        ]);

        StudentClass::create($validated);

        return redirect()
            ->route('student-classes.index')
            ->with('success', 'Class created successfully.');
    }

    /*
    |--------------------------------------------------------------------------
    | SHOW SINGLE CLASS (FIXED - WITH ATTENDANCE SESSIONS)
    |--------------------------------------------------------------------------
    */
    public function show($student_class)
    {
        $studentClass = StudentClass::with([
            'students',
            'subjects',
            'staff',
            'classTeacher',
            'classPrefect'
        ])->findOrFail($student_class);
        
        // Get attendance sessions for this class
        $attendanceSessions = AttendanceSession::where('student_class_id', $student_class)
            ->with('takenBy')
            ->latest('attendance_date')
            ->get()
            ->map(function ($session) {
                // Calculate present and absent counts for each session
                $presentCount = AttendanceRecord::where('attendance_session_id', $session->id)
                    ->where('status', 'present')
                    ->count();
                
                $absentCount = AttendanceRecord::where('attendance_session_id', $session->id)
                    ->where('status', 'absent')
                    ->count();
                
                $session->present_count = $presentCount;
                $session->absent_count = $absentCount;
                
                return $session;
            });
        
        // Calculate attendance rate for the class
        $totalStudents = $studentClass->students()
            ->wherePivot('is_current', true)
            ->count();
        
        $recentSessions = AttendanceSession::where('student_class_id', $student_class)
            ->whereDate('attendance_date', '>=', now()->subDays(30))
            ->get();
        
        $totalPresent = 0;
        $totalPossible = 0;
        
        foreach ($recentSessions as $session) {
            $presentCount = AttendanceRecord::where('attendance_session_id', $session->id)
                ->whereIn('status', ['present', 'late'])
                ->count();
            $totalPresent += $presentCount;
            $totalPossible += $totalStudents;
        }
        
        $attendanceRate = $totalPossible > 0 
            ? round(($totalPresent / $totalPossible) * 100, 1) 
            : 0;
        
        // Calculate fees paid percentage (if you have this logic)
        $feesPaidPercentage = 0; // Implement your fees logic here
        
        return view('student_classes.show', [
            'studentClass' => $studentClass,
            'students' => $studentClass->students ?? collect(),
            'subjects' => $studentClass->subjects ?? collect(),
            'staff' => $studentClass->staff ?? collect(),
            'attendanceSessions' => $attendanceSessions,
            'attendanceRate' => $attendanceRate,
            'feesPaidPercentage' => $feesPaidPercentage,
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | EDIT
    |--------------------------------------------------------------------------
    */
    public function edit(StudentClass $studentClass)
    {
        $academicYears = AcademicYear::latest()->get();
        $staff = Staff::orderBy('first_name')->get();

        return view('student_classes.edit', compact(
            'studentClass',
            'academicYears',
            'staff'
        ));
    }

    /*
    |--------------------------------------------------------------------------
    | UPDATE
    |--------------------------------------------------------------------------
    */
    public function update(Request $request, StudentClass $studentClass)
    {
        $validated = $request->validate([
            'academic_year_id' => 'required|exists:academic_years,id',
            'name' => 'required|string|max:255',
            'education_type' => 'required|string|max:255',
            'class_type' => 'required|string|max:255',
            'stream' => 'nullable|string|max:50',
            'staff_id' => 'nullable|exists:staff,id',
            'capacity' => 'nullable|integer|min:1',
        ]);

        $studentClass->update($validated);

        return redirect()
            ->route('student-classes.index')
            ->with('success', 'Class updated successfully');
    }

    /*
    |--------------------------------------------------------------------------
    | DELETE
    |--------------------------------------------------------------------------
    */
    public function destroy(StudentClass $studentClass)
    {
        $studentClass->delete();

        return redirect()
            ->route('student-classes.index')
            ->with('success', 'Class deleted successfully');
    }

    /*
    |--------------------------------------------------------------------------
    | SUBJECT ATTACH
    |--------------------------------------------------------------------------
    */
    public function attachSubject(Request $request, $classId)
    {
        $request->validate([
            'subject_id' => 'required|exists:subjects,id'
        ]);

        $class = StudentClass::findOrFail($classId);
        $class->subjects()->syncWithoutDetaching($request->subject_id);

        return back()->with('success', 'Subject assigned successfully.');
    }

    /*
    |--------------------------------------------------------------------------
    | SUBJECT DETACH
    |--------------------------------------------------------------------------
    */
    public function detachSubject($classId, $subjectId)
    {
        $class = StudentClass::findOrFail($classId);
        $class->subjects()->detach($subjectId);

        return back()->with('success', 'Subject removed successfully.');
    }

    /*
    |--------------------------------------------------------------------------
    | ASSIGN SUBJECT TEACHER
    |--------------------------------------------------------------------------
    */
    public function assignSubjectTeacher(Request $request, StudentClass $studentClass)
    {
        $request->validate([
            'subject_id' => 'required|exists:subjects,id',
            'staff_id' => 'required|exists:staff,id',
        ]);

        ClassSubjectStaff::updateOrCreate(
            [
                'student_class_id' => $studentClass->id,
                'subject_id' => $request->subject_id,
            ],
            [
                'staff_id' => $request->staff_id,
            ]
        );

        return back()->with('success', 'Teacher assigned successfully.');
    }

    /*
    |--------------------------------------------------------------------------
    | REMOVE SUBJECT TEACHER
    |--------------------------------------------------------------------------
    */
    public function removeSubjectTeacher(StudentClass $studentClass, Subject $subject)
    {
        ClassSubjectStaff::where('student_class_id', $studentClass->id)
            ->where('subject_id', $subject->id)
            ->delete();

        return back()->with('success', 'Assignment removed.');
    }

    /*
    |--------------------------------------------------------------------------
    | ASSIGN PREFECT
    |--------------------------------------------------------------------------
    */
    public function assignPrefect(Request $request, StudentClass $studentClass)
    {
        $request->validate([
            'student_id' => 'required|exists:students,id',
        ]);

        $exists = StudentClassAssignment::where('student_class_id', $studentClass->id)
            ->where('student_id', $request->student_id)
            ->where('is_current', true)
            ->exists();

        if (!$exists) {
            return back()->with('error', 'Student not in this class.');
        }

        $studentClass->update([
            'class_prefect_id' => $request->student_id,
        ]);

        return back()->with('success', 'Class prefect assigned successfully.');
    }

    public function getAttendanceData(StudentClass $studentClass, Request $request)
    {
        $period = $request->get('period', 'month'); // week, month, term
        $endDate = Carbon::now();
        $startDate = match($period) {
            'week' => Carbon::now()->subDays(7),
            'month' => Carbon::now()->subDays(30),
            'term' => Carbon::now()->subMonths(4),
            default => Carbon::now()->subDays(30),
        };
        
        // Get all attendance sessions for this class in date range
        $sessions = AttendanceSession::where('student_class_id', $studentClass->id)
            ->whereBetween('attendance_date', [$startDate, $endDate])
            ->with('attendances')
            ->orderBy('attendance_date', 'desc')
            ->get();
        
        // Calculate overall statistics
        $totalSessions = $sessions->count();
        $totalPresent = 0;
        $totalAbsent = 0;
        $totalLate = 0;
        $totalExcused = 0;
        
        foreach ($sessions as $session) {
            $totalPresent += $session->attendances->where('status', 'present')->count();
            $totalAbsent += $session->attendances->where('status', 'absent')->count();
            $totalLate += $session->attendances->where('status', 'late')->count();
            $totalExcused += $session->attendances->where('status', 'excused')->count();
        }
        
        $totalMarked = $totalPresent + $totalAbsent + $totalLate + $totalExcused;
        $overallRate = $totalMarked > 0 ? round(($totalPresent + $totalLate) / $totalMarked * 100, 1) : 0;
        
        // Chart data: daily attendance rate over time
        $chartLabels = [];
        $chartRates = [];
        
        $groupedByDate = $sessions->groupBy(function($session) {
            return $session->attendance_date->format('Y-m-d');
        });
        
        foreach ($groupedByDate as $date => $daySessions) {
            $dayTotal = 0;
            $dayPresent = 0;
            foreach ($daySessions as $session) {
                $presentCount = $session->attendances->whereIn('status', ['present', 'late'])->count();
                $totalCount = $session->attendances->count();
                if ($totalCount > 0) {
                    $dayPresent += $presentCount;
                    $dayTotal += $totalCount;
                }
            }
            $rate = $dayTotal > 0 ? round($dayPresent / $dayTotal * 100, 1) : 0;
            $chartLabels[] = Carbon::parse($date)->format('M d');
            $chartRates[] = $rate;
        }
        
        // Student-wise attendance
        $students = $studentClass->students()
            ->wherePivot('is_current', true)
            ->with(['attendances' => function($q) use ($startDate, $endDate) {
                $q->whereHas('session', function($sq) use ($startDate, $endDate) {
                    $sq->whereBetween('attendance_date', [$startDate, $endDate]);
                });
            }])
            ->get();
        
        $studentAttendance = [];
        foreach ($students as $student) {
            $present = $student->attendances->whereIn('status', ['present', 'late'])->count();
            $absent = $student->attendances->where('status', 'absent')->count();
            $total = $present + $absent;
            $rate = $total > 0 ? round($present / $total * 100, 1) : 0;
            
            $studentAttendance[] = [
                'id' => $student->id,
                'name' => $student->first_name . ' ' . $student->last_name,
                'student_id' => $student->student_id,
                'present' => $present,
                'absent' => $absent,
                'rate' => $rate,
                'status' => $rate >= 90 ? 'good' : ($rate >= 75 ? 'warning' : 'danger')
            ];
        }
        
        // Recent sessions with attendance summary
        $recentSessions = $sessions->take(10)->map(function($session) {
            $total = $session->attendances->count();
            $present = $session->attendances->whereIn('status', ['present', 'late'])->count();
            $absent = $session->attendances->where('status', 'absent')->count();
            
            return [
                'id' => $session->id,
                'date' => $session->attendance_date->format('d M Y'),
                'total_students' => $total,
                'present' => $present,
                'absent' => $absent,
                'rate' => $total > 0 ? round($present / $total * 100, 1) : 0,
                'taken_by' => $session->takenBy->name ?? 'Unknown'
            ];
        });
        
        return response()->json([
            'stats' => [
                'total_sessions' => $totalSessions,
                'overall_rate' => $overallRate,
                'total_present' => $totalPresent,
                'total_absent' => $totalAbsent,
                'total_late' => $totalLate,
                'total_excused' => $totalExcused,
                'total_students' => $students->count()
            ],
            'chart' => [
                'labels' => array_reverse($chartLabels),
                'rates' => array_reverse($chartRates)
            ],
            'recent_sessions' => $recentSessions,
            'student_attendance' => $studentAttendance
        ]);
    }

    public function attendanceData(Request $request, $id)
    {
        $studentClass = StudentClass::with([
            'assignments.student'
        ])->findOrFail($id);
    
        $fromDate = $request->from_date ?? now()->startOfMonth();
        $toDate   = $request->to_date ?? now();
    
        /*
        |--------------------------------------------------------------------------
        | GET ATTENDANCE SESSIONS
        |--------------------------------------------------------------------------
        */
    
        $sessions = AttendanceSession::where('student_class_id', $id)
            ->whereBetween('attendance_date', [$fromDate, $toDate])
            ->latest()
            ->get();
    
        /*
        |--------------------------------------------------------------------------
        | TODAY SUMMARY
        |--------------------------------------------------------------------------
        */
    
        $todaySession = AttendanceSession::where('student_class_id', $id)
            ->whereDate('attendance_date', today())
            ->latest()
            ->first();
    
        $presentCount = 0;
        $absentCount  = 0;
        $todayRate    = 0;
    
        if ($todaySession)
        {
            $presentCount = AttendanceRecord::where('attendance_session_id', $todaySession->id)
                ->where('status', 'present')
                ->count();
    
            $absentCount = AttendanceRecord::where('attendance_session_id', $todaySession->id)
                ->where('status', 'absent')
                ->count();
    
            $total = $presentCount + $absentCount;
    
            $todayRate = $total > 0
                ? round(($presentCount / $total) * 100)
                : 0;
        }
    
        /*
        |--------------------------------------------------------------------------
        | FORMAT SESSIONS
        |--------------------------------------------------------------------------
        */
    
        $formattedSessions = $sessions->map(function ($session) {
    
            $present = AttendanceRecord::where('attendance_session_id', $session->id)
                ->where('status', 'present')
                ->count();
    
            $absent = AttendanceRecord::where('attendance_session_id', $session->id)
                ->where('status', 'absent')
                ->count();
    
            $total = $present + $absent;
    
            $rate = $total > 0
                ? round(($present / $total) * 100)
                : 0;
    
            return [
                'id' => $session->id,
                'date' => \Carbon\Carbon::parse($session->attendance_date)->format('d M Y'),
                'present' => $present,
                'absent' => $absent,
                'rate' => $rate,
                'taken_by' => $session->takenBy->name ?? 'N/A',
            ];
        });
    
        /*
        |--------------------------------------------------------------------------
        | STUDENT SUMMARY
        |--------------------------------------------------------------------------
        */
    
        $students = [];
    
        foreach ($studentClass->assignments as $assignment)
        {
            $student = $assignment->student;
    
            $present = AttendanceRecord::where('student_id', $student->id)
                ->where('status', 'present')
                ->whereHas('session', function ($query) use ($fromDate, $toDate) {
                    $query->whereBetween('attendance_date', [$fromDate, $toDate]);
                })
                ->count();
    
            $absent = AttendanceRecord::where('student_id', $student->id)
                ->where('status', 'absent')
                ->whereHas('session', function ($query) use ($fromDate, $toDate) {
                    $query->whereBetween('attendance_date', [$fromDate, $toDate]);
                })
                ->count();
    
            $total = $present + $absent;
    
            $rate = $total > 0
                ? round(($present / $total) * 100)
                : 0;
    
            $students[] = [
                'id' => $student->id,
                'name' => $student->first_name . ' ' . $student->last_name,
                'student_id' => $student->student_id,
                'present' => $present,
                'absent' => $absent,
                'rate' => $rate,
            ];
        }
    
        /*
        |--------------------------------------------------------------------------
        | CHART DATA
        |--------------------------------------------------------------------------
        */
    
        $chartLabels = [];
        $chartRates  = [];
    
        foreach ($formattedSessions as $session)
        {
            $chartLabels[] = $session['date'];
            $chartRates[]  = $session['rate'];
        }
    
        /*
        |--------------------------------------------------------------------------
        | RESPONSE
        |--------------------------------------------------------------------------
        */
    
        return response()->json([
            'today_rate' => $todayRate,
            'present' => $presentCount,
            'absent' => $absentCount,
            'total_sessions' => $sessions->count(),
            'sessions' => $formattedSessions,
            'students' => $students,
            'chart' => [
                'labels' => $chartLabels,
                'data' => $chartRates,
            ]
        ]);
    }


        
}