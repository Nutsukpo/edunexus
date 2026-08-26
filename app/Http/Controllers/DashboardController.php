<?php

namespace App\Http\Controllers;

use App\Models\Student;
use App\Models\Staff;
use App\Models\StudentClass;
use App\Models\Attendance;
use App\Models\AttendanceSession;
use App\Models\StudentClassAssignment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        /*
        |--------------------------------------------------------------------------
        | TOTAL COUNTS
        |--------------------------------------------------------------------------
        */

        $totalStudents = Student::count();
        $totalStaff = Staff::count();
        $totalClasses = StudentClass::count();
        $activeClasses = StudentClass::count();

        /*
        |--------------------------------------------------------------------------
        | GENDER COUNTS
        |--------------------------------------------------------------------------
        */

        $maleCount = Student::where('gender', 'Male')->count();
        $femaleCount = Student::where('gender', 'Female')->count();

        /*
        |--------------------------------------------------------------------------
        | MONTHLY STATS
        |--------------------------------------------------------------------------
        */

        $studentsThisYear = Student::whereYear('created_at', now()->year)->count();
        $staffThisYear = Staff::whereYear('created_at', now()->year)->count();

        /*
        |--------------------------------------------------------------------------
        | STUDENT ATTENDANCE RATE
        |--------------------------------------------------------------------------
        */

        $totalAttendanceRecords = Attendance::count();
        $presentAttendanceRecords = Attendance::where('status', 'Present')->count();

        $studentAttendanceRate = $totalAttendanceRecords > 0
            ? round(($presentAttendanceRecords / $totalAttendanceRecords) * 100, 1)
            : 0;

        /*
        |--------------------------------------------------------------------------
        | STAFF ATTENDANCE RATE
        |--------------------------------------------------------------------------
        */

        // Check if staff attendance table exists
        $staffAttendanceRate = 0;
        if (Schema::hasTable('staff_attendances')) {
            try {
                $totalStaffAttendance = DB::table('staff_attendances')->count();
                $presentStaffAttendance = DB::table('staff_attendances')
                    ->where('status', 'Present')
                    ->count();
                
                $staffAttendanceRate = $totalStaffAttendance > 0
                    ? round(($presentStaffAttendance / $totalStaffAttendance) * 100, 1)
                    : 0;
            } catch (\Exception $e) {
                // If there's an error (like column doesn't exist), set to 0
                $staffAttendanceRate = 0;
            }
        }

        /*
        |--------------------------------------------------------------------------
        | CLASS DISTRIBUTION
        |--------------------------------------------------------------------------
        */

        // Get class distribution using DB query
        $classDistribution = DB::table('student_classes')
            ->leftJoin('student_class_assignments', function($join) {
                $join->on('student_classes.id', '=', 'student_class_assignments.student_class_id')
                    ->where('student_class_assignments.is_current', '=', true);
            })
            ->select(
                'student_classes.id',
                'student_classes.name',
                DB::raw('COUNT(DISTINCT student_class_assignments.student_id) as student_count')
            )
            ->groupBy('student_classes.id', 'student_classes.name')
            ->orderBy('student_classes.name')
            ->get();

        /*
        |--------------------------------------------------------------------------
        | RECENT ATTENDANCE ACTIVITY
        |--------------------------------------------------------------------------
        */

        // Get recent attendance with student details
        $recentAttendance = DB::table('attendances')
            ->join('students', 'attendances.student_id', '=', 'students.id')
            ->join('attendance_sessions', 'attendances.attendance_session_id', '=', 'attendance_sessions.id')
            ->select(
                'attendances.id',
                'attendances.status',
                'attendances.created_at',
                'students.first_name',
                'students.last_name',
                'students.student_id as student_number',
                'attendance_sessions.attendance_date'
            )
            ->orderBy('attendances.created_at', 'desc')
            ->limit(10)
            ->get();

        /*
        |--------------------------------------------------------------------------
        | TODAY'S ATTENDANCE SUMMARY
        |--------------------------------------------------------------------------
        */

        $today = now()->toDateString();
        $todayAttendance = DB::table('attendances')
            ->join('attendance_sessions', 'attendances.attendance_session_id', '=', 'attendance_sessions.id')
            ->whereDate('attendance_sessions.attendance_date', $today)
            ->select(
                DB::raw('COUNT(*) as total'),
                DB::raw('SUM(CASE WHEN attendances.status = "present" THEN 1 ELSE 0 END) as present')
            )
            ->first();

        $todayTotal = $todayAttendance->total ?? 0;
        $todayPresent = $todayAttendance->present ?? 0;
        $todayRate = $todayTotal > 0 ? round(($todayPresent / $todayTotal) * 100, 1) : 0;

        /*
        |--------------------------------------------------------------------------
        | RETURN VIEW
        |--------------------------------------------------------------------------
        */

        return view('dashboard.index', compact(
            'totalStudents',
            'totalStaff',
            'totalClasses',
            'activeClasses',
            'maleCount',
            'femaleCount',
            'studentsThisYear',
            'staffThisYear',
            'studentAttendanceRate',
            'staffAttendanceRate',
            'classDistribution',
            'recentAttendance',
            'todayTotal',
            'todayPresent',
            'todayRate'
        ));
    }

    /**
     * Get attendance data for charts from database
     */
    public function getAttendanceData(Request $request)
    {
        $days = (int) $request->get('days', 30);

        try {
            $startDate = now()->subDays($days - 1)->startOfDay();
            $endDate = now()->endOfDay();

            $attendanceData = DB::table('attendance_sessions')
                ->leftJoin('attendances', 'attendance_sessions.id', '=', 'attendances.attendance_session_id')
                ->select(
                    DB::raw('DATE(attendance_sessions.attendance_date) as date'),
                    DB::raw('COUNT(attendances.id) as total_records'),
                    DB::raw("SUM(CASE WHEN attendances.status = 'present' THEN 1 ELSE 0 END) as present_count")
                )
                ->whereDate('attendance_sessions.attendance_date', '>=', $startDate->toDateString())
                ->whereDate('attendance_sessions.attendance_date', '<=', $endDate->toDateString())
                ->groupBy(DB::raw('DATE(attendance_sessions.attendance_date)'))
                ->orderBy(DB::raw('DATE(attendance_sessions.attendance_date)'), 'ASC')
                ->get();

            $dataMap = [];
            foreach ($attendanceData as $row) {
                $dataMap[$row->date] = [
                    'present' => (int) $row->present_count,
                    'total' => (int) $row->total_records,
                ];
            }

            $labels = [];
            $attendance = [];
            $totalStudents = [];

            $currentDate = clone $startDate;
            while ($currentDate <= $endDate) {
                $dateKey = $currentDate->format('Y-m-d');
                $labels[] = $currentDate->format('M d, D');
                $attendance[] = $dataMap[$dateKey]['present'] ?? 0;
                $totalStudents[] = $dataMap[$dateKey]['total'] ?? 0;
                $currentDate->addDay();
            }

            $daysWithData = count(array_filter($attendance, fn($v) => $v > 0));
            $totalPresent = array_sum($attendance);
            $average = $daysWithData > 0 ? round($totalPresent / $daysWithData, 1) : 0;
            $peak = !empty($attendance) ? max($attendance) : 0;
            $peakIndex = $peak > 0 ? array_search($peak, $attendance) : false;
            $peakDay = ($peakIndex !== false && isset($labels[$peakIndex])) ? $labels[$peakIndex] : 'N/A';

            $benchmark = array_fill(0, count($attendance), $average);

            return response()->json([
                'labels' => $labels,
                'attendance' => $attendance,
                'benchmark' => $benchmark,
                'total_students' => $totalStudents,
                'total' => $totalPresent,
                'average' => $average,
                'peak' => $peak,
                'peak_day' => $peakDay,
                'days_with_data' => $daysWithData,
                'status' => 'success'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'labels' => [],
                'attendance' => [],
                'benchmark' => [],
                'peak' => 0,
                'peak_day' => 'N/A',
                'average' => 0,
                'total' => 0,
                'error' => $e->getMessage(),
                'status' => 'error'
            ], 500);
        }
    }

    /**
     * Get class attendance data for the table
     */
    public function getClassAttendance(Request $request)
    {
        try {
            $date = $request->get('date', now()->toDateString());
            
            // Get all active classes with their student counts
            $classes = StudentClass::withCount(['classAssignments' => function($query) {
                $query->where('is_current', true)
                      ->where('status', 'active');
            }])->orderBy('name')->get();
            
            $classData = [];
            
            foreach ($classes as $class) {
                // Get attendance session for this date
                $session = AttendanceSession::where('student_class_id', $class->id)
                    ->whereDate('attendance_date', $date)
                    ->first();
                
                $totalStudents = $class->class_assignments_count ?? 0;
                
                if ($session) {
                    // Get attendance records for this session
                    $attendance = Attendance::where('attendance_session_id', $session->id)->get();
                    
                    $present = $attendance->where('status', 'present')->count();
                    $absent = $attendance->where('status', 'absent')->count();
                    $late = $attendance->where('status', 'late')->count();
                    $excused = $attendance->where('status', 'excused')->count();
                    
                    $rate = $totalStudents > 0 ? round(($present / $totalStudents) * 100, 1) : 0;
                    
                    $classData[] = [
                        'class_name' => $class->name,
                        'total_students' => $totalStudents,
                        'present' => $present,
                        'absent' => $absent,
                        'late' => $late,
                        'excused' => $excused,
                        'rate' => $rate
                    ];
                } else {
                    // No attendance taken for this class on this date
                    $classData[] = [
                        'class_name' => $class->name,
                        'total_students' => $totalStudents,
                        'present' => 0,
                        'absent' => 0,
                        'late' => 0,
                        'excused' => 0,
                        'rate' => 0
                    ];
                }
            }
            
            // Filter out classes with 0 students
            $classData = array_filter($classData, function($class) {
                return $class['total_students'] > 0;
            });
            
            // Re-index array
            $classData = array_values($classData);
            
            return response()->json([
                'success' => true,
                'classes' => $classData,
                'date' => $date,
                'total_classes' => count($classData)
            ]);
            
        } catch (\Exception $e) {
            \Log::error('Class attendance error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }
}