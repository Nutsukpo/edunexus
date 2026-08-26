<?php

namespace App\Http\Controllers;

use App\Models\StudentClass;
use App\Models\AcademicYear;
use App\Models\Staff;
use App\Models\Subject;
use App\Models\ClassSubjectStaff;
use App\Models\StudentClassAssignment;
use Illuminate\Http\Request;
use App\Models\AttendanceSession;
use App\Models\AttendanceRecord;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

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
    | SHOW SINGLE CLASS (WITH PROPER ATTENDANCE FETCHING)
    |--------------------------------------------------------------------------
    */
        /*
|--------------------------------------------------------------------------
| SHOW SINGLE CLASS (WITH PROPER ATTENDANCE FETCHING)
|--------------------------------------------------------------------------
*/
    // In your StudentClassController.php or relevant controller



    public function show($id)
    {
        $studentClass = StudentClass::with([
            'assignments.student',
            'subjects',
            'classTeacher'
        ])->findOrFail($id);
    
        $studentIds = $studentClass->assignments
                        ->where('is_current', true)
                        ->pluck('student_id');
    
                        $attendanceStats = DB::table('attendances')
                        ->join('attendance_sessions', 'attendance_sessions.id', '=', 'attendances.attendance_session_id')
                        ->where('attendance_sessions.student_class_id', $studentClass->id)
                        ->whereIn('attendances.student_id', $studentClass->assignments()
                            ->where('is_current', true)
                            ->pluck('student_id'))
                        ->selectRaw("
                            COUNT(*) as total,
                            SUM(CASE WHEN LOWER(attendances.status) IN ('present', 'late') THEN 1 ELSE 0 END) as present_count
                        ")
                        ->first();
    
        $totalSessions = $attendanceStats->total ?? 0;
        $presentSessions = $attendanceStats->present_count ?? 0;
    
        $attendanceRate = $totalSessions > 0
            ? round(($presentSessions / $totalSessions) * 100, 1)
            : 0;
    
        return view(
            'student_classes.show',
            compact('studentClass', 'attendanceRate')
        );
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

    /*
    |--------------------------------------------------------------------------
    | GET ATTENDANCE DATA FOR CHART (FIXED)
    |--------------------------------------------------------------------------
    */
    public function getAttendanceData(StudentClass $studentClass, Request $request)
    {
        $period = $request->get('period', 'month');
        $endDate = Carbon::now();
        
        switch($period) {
            case 'week':
                $startDate = Carbon::now()->subDays(7);
                break;
            case 'month':
                $startDate = Carbon::now()->subDays(30);
                break;
            case 'term':
                $startDate = Carbon::now()->subMonths(4);
                break;
            default:
                $startDate = Carbon::now()->subDays(30);
        }
        
        // Get all attendance sessions for this class in date range
        $sessions = AttendanceSession::where('student_class_id', $studentClass->id)
            ->whereBetween('attendance_date', [$startDate, $endDate])
            ->orderBy('attendance_date', 'asc')
            ->get();
        
        // Daily attendance data
        $dailyData = [];
        foreach ($sessions as $session) {
            $date = $session->attendance_date->format('M d');
            $presentCount = AttendanceRecord::where('attendance_session_id', $session->id)
                ->whereIn('status', ['present', 'late'])
                ->count();
            
            if (!isset($dailyData[$date])) {
                $dailyData[$date] = 0;
            }
            $dailyData[$date] += $presentCount;
        }
        
        $chartLabels = array_keys($dailyData);
        $chartRates = array_values($dailyData);
        
        // Calculate overall statistics
        $totalSessions = $sessions->count();
        $totalPresent = 0;
        $totalAbsent = 0;
        $totalLate = 0;
        $totalExcused = 0;
        
        foreach ($sessions as $session) {
            $totalPresent += AttendanceRecord::where('attendance_session_id', $session->id)->where('status', 'present')->count();
            $totalAbsent += AttendanceRecord::where('attendance_session_id', $session->id)->where('status', 'absent')->count();
            $totalLate += AttendanceRecord::where('attendance_session_id', $session->id)->where('status', 'late')->count();
            $totalExcused += AttendanceRecord::where('attendance_session_id', $session->id)->where('status', 'excused')->count();
        }
        
        $totalMarked = $totalPresent + $totalAbsent + $totalLate + $totalExcused;
        $overallRate = $totalMarked > 0 ? round(($totalPresent + $totalLate) / $totalMarked * 100, 1) : 0;
        
        // Get total students count
        $totalStudents = $studentClass->assignments->where('is_current', true)->count();
        
        return response()->json([
            'labels' => $chartLabels,
            'data' => $chartRates,
            'termAvg' => count($chartRates) > 0 ? round(array_sum($chartRates) / count($chartRates), 0) : 0,
            'totalStudents' => $totalStudents,
            'stats' => [
                'total_sessions' => $totalSessions,
                'overall_rate' => $overallRate,
                'total_present' => $totalPresent,
                'total_absent' => $totalAbsent,
                'total_late' => $totalLate,
                'total_excused' => $totalExcused
            ]
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | ATTENDANCE DATA FOR MODAL (FIXED)
    |--------------------------------------------------------------------------
    */
    public function attendanceData(Request $request, $id)
    {
        $studentClass = StudentClass::with(['assignments.student'])
            ->findOrFail($id);
    
        $fromDate = $request->from_date ?? Carbon::now()->startOfMonth();
        $toDate   = $request->to_date ?? Carbon::now();
    
        // GET ATTENDANCE SESSIONS
        $sessions = AttendanceSession::where('student_class_id', $id)
            ->whereBetween('attendance_date', [$fromDate, $toDate])
            ->latest()
            ->get();
    
        // TODAY SUMMARY
        $todaySession = AttendanceSession::where('student_class_id', $id)
            ->whereDate('attendance_date', Carbon::today())
            ->latest()
            ->first();
    
        $presentCount = 0;
        $absentCount  = 0;
        $todayRate    = 0;
    
        if ($todaySession) {
            $presentCount = AttendanceRecord::where('attendance_session_id', $todaySession->id)
                ->where('status', 'present')
                ->count();
    
            $absentCount = AttendanceRecord::where('attendance_session_id', $todaySession->id)
                ->where('status', 'absent')
                ->count();
    
            $total = $presentCount + $absentCount;
            $todayRate = $total > 0 ? round(($presentCount / $total) * 100) : 0;
        }
    
        // FORMAT SESSIONS
        $formattedSessions = $sessions->map(function ($session) {
            $present = AttendanceRecord::where('attendance_session_id', $session->id)
                ->where('status', 'present')
                ->count();
    
            $absent = AttendanceRecord::where('attendance_session_id', $session->id)
                ->where('status', 'absent')
                ->count();
    
            $total = $present + $absent;
            $rate = $total > 0 ? round(($present / $total) * 100) : 0;
    
            return [
                'id' => $session->id,
                'date' => Carbon::parse($session->attendance_date)->format('d M Y'),
                'present' => $present,
                'absent' => $absent,
                'rate' => $rate,
                'taken_by' => $session->takenBy->name ?? 'N/A',
            ];
        });
    
        // STUDENT SUMMARY
        $students = [];
        $activeAssignments = $studentClass->assignments->where('is_current', true);
    
        foreach ($activeAssignments as $assignment) {
            $student = $assignment->student;
    
            $present = AttendanceRecord::where('student_id', $student->id)
                ->where('status', 'present')
                ->whereHas('attendanceSession', function ($query) use ($fromDate, $toDate) {
                    $query->whereBetween('attendance_date', [$fromDate, $toDate]);
                })
                ->count();
    
            $absent = AttendanceRecord::where('student_id', $student->id)
                ->where('status', 'absent')
                ->whereHas('attendanceSession', function ($query) use ($fromDate, $toDate) {
                    $query->whereBetween('attendance_date', [$fromDate, $toDate]);
                })
                ->count();
    
            $total = $present + $absent;
            $rate = $total > 0 ? round(($present / $total) * 100) : 0;
    
            $students[] = [
                'id' => $student->id,
                'name' => $student->first_name . ' ' . $student->last_name,
                'student_id' => $student->student_id,
                'present' => $present,
                'absent' => $absent,
                'rate' => $rate,
            ];
        }
    
        // CHART DATA
        $chartLabels = [];
        $chartRates  = [];
    
        foreach ($formattedSessions as $session) {
            $chartLabels[] = $session['date'];
            $chartRates[]  = $session['rate'];
        }
    
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

            // In app/Models/StudentClass.php

public function getAttendanceRateAttribute()
{
    // Get all active students in this class
    $students = $this->assignments()->where('is_current', true)->with('student')->get();
    
    if ($students->isEmpty()) {
        return 0;
    }
    
    $totalSessions = 0;
    $presentSessions = 0;
    
    foreach ($students as $assignment) {
        // Count total attendance sessions for each student
        $attendance = \App\Models\Attendance::where('student_id', $assignment->student_id)
            ->where('student_class_id', $this->id)
            ->get();
        
        $totalSessions += $attendance->count();
        $presentSessions += $attendance->where('status', 'present')->count();
    }
    
    if ($totalSessions == 0) {
        return 0;
    }
    
    return round(($presentSessions / $totalSessions) * 100, 1);
}

        // Or a more efficient version using a single query
        public function getAttendanceRateEfficientAttribute()
        {
            // Get all active students in this class
            $studentIds = $this->assignments()
                ->where('is_current', true)
                ->pluck('student_id');
            
            if ($studentIds->isEmpty()) {
                return 0;
            }
            
            // Get all attendance records for these students in this class
            $attendance = \App\Models\Attendance::whereIn('student_id', $studentIds)
                ->where('student_class_id', $this->id)
                ->selectRaw('
                    COUNT(*) as total,
                    SUM(CASE WHEN status = "present" THEN 1 ELSE 0 END) as present_count
                ')
                ->first();
            
            if (!$attendance || $attendance->total == 0) {
                return 0;
            }
            
            return round(($attendance->present_count / $attendance->total) * 100, 1);
        }
}