<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\AttendanceSession;
use App\Models\StudentClass;
use App\Models\StudentClassAssignment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Services\EdunexusAuthorizationService;

class AttendanceSessionController extends Controller
{
    public function __construct(private EdunexusAuthorizationService $authorization)
    {
    }

    /*
    |--------------------------------------------------------------------------
    | DISPLAY ALL ATTENDANCE SESSIONS
    |--------------------------------------------------------------------------
    */
    public function index()
    {
        abort_unless(auth()->user()->can('attendance.view'), 403);

        $sessions = AttendanceSession::with([
            'studentClass',
            'takenBy'
        ])
        ->when($this->authorization->isScopedStaff(auth()->user()), function ($query) {
            $query->whereIn(
                'student_class_id',
                $this->authorization->accessibleClasses(auth()->user())->select('student_classes.id')
            );
        })
        ->latest()
        ->paginate(20);

        return view('attendance.index', compact('sessions'));
    }

    /*
    |--------------------------------------------------------------------------
    | SHOW CREATE FORM
    |--------------------------------------------------------------------------
    */
    public function create()
    {
        abort_unless(auth()->user()->can('attendance.create'), 403);

        $classes = $this->authorization->accessibleClasses(auth()->user())
            ->orderBy('name')
            ->get();
        return view('attendance.create', compact('classes'));
    }

    /*
    |--------------------------------------------------------------------------
    | STORE ATTENDANCE
    |--------------------------------------------------------------------------
    */
    public function store(Request $request)
    {
        abort_unless(auth()->user()->can('attendance.create'), 403);

        $request->validate([
            'student_class_id' => 'required|exists:student_classes,id',
            'attendance_date'  => 'required|date',
            'attendance'       => 'required|array',
        ]);

        abort_unless(
            $this->authorization->canAccessClass(auth()->user(), (int) $request->student_class_id),
            403
        );

        // Prevent duplicate attendance
        $exists = AttendanceSession::where('student_class_id', $request->student_class_id)
            ->where('attendance_date', $request->attendance_date)
            ->exists();

        if ($exists) {
            return back()->with('error', 'Attendance already taken for this class and date.');
        }

        DB::transaction(function () use ($request) {
            // Create session
            $session = AttendanceSession::create([
                'student_class_id' => $request->student_class_id,
                'attendance_date'  => $request->attendance_date,
                'taken_by'         => auth()->id(),
                'status'           => 'completed',
            ]);

            // Save student attendance
            foreach ($request->attendance as $assignmentId => $status) {
                $assignment = StudentClassAssignment::with('student')->find($assignmentId);
                if (!$assignment) {
                    continue;
                }

                Attendance::create([
                    'attendance_session_id' => $session->id,
                    'student_class_assignment_id' => $assignment->id,
                    'student_id' => $assignment->student_id,
                    'status' => $status,
                ]);
            }
        });

        return redirect()
            ->route('student-classes.show', $request->student_class_id)
            ->with('success', 'Attendance submitted successfully.');
    }

    /*
    |--------------------------------------------------------------------------
    | STORE ATTENDANCE FOR SPECIFIC CLASS (AJAX)
    |--------------------------------------------------------------------------
    */
    public function storeForClass(Request $request, $classId)
    {
        abort_unless(auth()->user()->can('attendance.create'), 403);
        abort_unless($this->authorization->canAccessClass(auth()->user(), (int) $classId), 403);

        $request->validate([
            'attendance_date' => 'required|date',
            'attendance' => 'required|array',
        ]);

        $exists = AttendanceSession::where('student_class_id', $classId)
            ->where('attendance_date', $request->attendance_date)
            ->exists();

        if ($exists) {
            return response()->json([
                'success' => false,
                'message' => 'Attendance already taken for this class and date.'
            ]);
        }

        try {
            DB::transaction(function () use ($request, $classId) {
                $session = AttendanceSession::create([
                    'student_class_id' => $classId,
                    'attendance_date' => $request->attendance_date,
                    'taken_by' => auth()->id(),
                    'status' => 'completed',
                ]);

                foreach ($request->attendance as $studentId => $data) {
                    $assignment = StudentClassAssignment::where('student_id', $studentId)
                        ->where('student_class_id', $classId)
                        ->where('is_current', true)
                        ->first();

                    if ($assignment) {
                        Attendance::create([
                            'attendance_session_id' => $session->id,
                            'student_class_assignment_id' => $assignment->id,
                            'student_id' => $studentId,
                            'status' => $data['status'] ?? 'present',
                        ]);
                    }
                }
            });

            return response()->json([
                'success' => true,
                'message' => 'Attendance saved successfully!'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error saving attendance: ' . $e->getMessage()
            ]);
        }
    }

    /*
    |--------------------------------------------------------------------------
    | GET ATTENDANCE DATA (AJAX)
    |--------------------------------------------------------------------------
    */
    public function getAttendanceData(Request $request, $classId)
    {
        abort_unless(auth()->user()->can('attendance.view'), 403);
        abort_unless($this->authorization->canAccessClass(auth()->user(), (int) $classId), 403);

        $date = $request->get('date');
        $month = $request->get('month');
        $year = $request->get('year');

        $query = AttendanceSession::with(['attendances.student', 'studentClass'])
            ->where('student_class_id', $classId);

        if ($date) {
            $query->whereDate('attendance_date', $date);
        } else {
            if ($month) {
                $query->whereMonth('attendance_date', $month);
            }
            if ($year) {
                $query->whereYear('attendance_date', $year);
            } else {
                $query->whereYear('attendance_date', date('Y'));
            }
        }

        $sessions = $query->orderBy('attendance_date', 'desc')->get();

        if ($sessions->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'No attendance records found for the selected criteria.'
            ]);
        }

        $records = [];
        foreach ($sessions as $session) {
            foreach ($session->attendances as $attendance) {
                $records[] = [
                    'date' => $session->attendance_date,
                    'student_name' => $attendance->student->first_name . ' ' . $attendance->student->last_name,
                    'student_id' => $attendance->student->student_id,
                    'gender' => $attendance->student->gender,
                    'status' => $attendance->status,
                    'remarks' => $attendance->remarks ?? '',
                ];
            }
        }

        $summary = [
            'present' => collect($records)->where('status', 'present')->count(),
            'absent' => collect($records)->where('status', 'absent')->count(),
            'late' => collect($records)->where('status', 'late')->count(),
            'excused' => collect($records)->where('status', 'excused')->count(),
        ];

        return response()->json([
            'success' => true,
            'records' => $records,
            'summary' => $summary,
            'total' => count($records)
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | SHOW SINGLE SESSION
    |--------------------------------------------------------------------------
    */
    public function show(AttendanceSession $attendanceSession)
    {
        abort_unless(auth()->user()->can('attendance.view'), 403);
        abort_unless(
            $this->authorization->canAccessClass(auth()->user(), (int) $attendanceSession->student_class_id),
            403
        );

        $attendanceSession->load([
            'studentClass',
            'attendances.student',
            'takenBy'
        ]);

        return view('attendance.show', compact('attendanceSession'));
    }

    /*
    |--------------------------------------------------------------------------
    | AJAX LOAD CLASS STUDENTS
    |--------------------------------------------------------------------------
    */
    public function getStudents($classId)
    {
        abort_unless(auth()->user()->can('attendance.view') || auth()->user()->can('attendance.create'), 403);
        abort_unless($this->authorization->canAccessClass(auth()->user(), (int) $classId), 403);

        $students = StudentClassAssignment::with('student')
            ->where('student_class_id', $classId)
            ->where('is_current', true)
            ->orderBy('id')
            ->get();

        return response()->json($students);
    }

    /*
    |--------------------------------------------------------------------------
    | CREATE ATTENDANCE FOR CLASS
    |--------------------------------------------------------------------------
    */
    public function createForClass($studentClassId)
    {
        abort_unless(auth()->user()->can('attendance.create'), 403);
        abort_unless($this->authorization->canAccessClass(auth()->user(), (int) $studentClassId), 403);

        $studentClass = StudentClass::findOrFail($studentClassId);
        
        $assignments = StudentClassAssignment::with('student')
            ->where('student_class_id', $studentClassId)
            ->where('is_current', true)
            ->get();
        
        return view('attendance.take', compact('studentClass', 'assignments'));
    }

    /*
    |--------------------------------------------------------------------------
    | CHECK IF ATTENDANCE EXISTS
    |--------------------------------------------------------------------------
    */
    public function checkExists(Request $request)
    {
        $classId = $request->get('class_id');
        $date = $request->get('date');
        
        $session = AttendanceSession::where('student_class_id', $classId)
            ->where('attendance_date', $date)
            ->first();
        
        if ($session) {
            $attendances = Attendance::where('attendance_session_id', $session->id)
                ->get()
                ->pluck('status', 'student_class_assignment_id');
            
            return response()->json([
                'exists' => true,
                'session_id' => $session->id,
                'attendance' => $attendances
            ]);
        }
        
        return response()->json([
            'exists' => false
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | MONTHLY ATTENDANCE REPORT FOR A CLASS
    |--------------------------------------------------------------------------
    */
    public function monthlyReport(Request $request)
    {
        // Get all classes for dropdown
        $classes = StudentClass::orderBy('name')->get();
        
        // Get selected class or default to first
        $selectedClassId = $request->input('class_id');
        
        // If no class selected and classes exist, select the first one
        if (!$selectedClassId && $classes->isNotEmpty()) {
            $selectedClassId = $classes->first()->id;
        }
        
        // Handle month selection
        if ($request->has('month_year')) {
            $monthYear = $request->month_year;
            $year = (int) substr($monthYear, 0, 4);
            $month = (int) substr($monthYear, 5, 2);
        } else {
            $year = now()->year;
            $month = now()->month;
        }
        
        $selectedClass = null;
        $monthlyData = [];
        $days = [];
        $summary = [];
        
        if ($selectedClassId) {
            $selectedClass = StudentClass::findOrFail($selectedClassId);
            
            // Get all students in the class
            $students = StudentClassAssignment::with('student')
                ->where('student_class_id', $selectedClassId)
                ->where('is_current', true)
                ->orderBy('id')
                ->get();
            
            // Get attendance records for the selected month
            $startDate = Carbon::create($year, $month, 1)->startOfDay();
            $endDate = Carbon::create($year, $month, 1)->endOfMonth()->endOfDay();
            
            // Get all attendance sessions for this class in the month
            $sessions = AttendanceSession::where('student_class_id', $selectedClassId)
                ->whereBetween('attendance_date', [$startDate, $endDate])
                ->with(['attendances' => function($query) {
                    $query->with('student');
                }])
                ->get()
                ->groupBy('attendance_date');
            
            // Get days in month
            $daysInMonth = Carbon::create($year, $month, 1)->daysInMonth;
            $days = [];
            for ($i = 1; $i <= $daysInMonth; $i++) {
                $days[] = Carbon::create($year, $month, $i);
            }
            
            // Prepare monthly data
            $monthlyData = [];
            foreach ($students as $assignment) {
                $student = $assignment->student;
                $attendanceData = [];
                
                foreach ($days as $day) {
                    $dateString = $day->toDateString();
                    $session = $sessions->get($dateString);
                    $status = 'absent';
                    
                    if ($session) {
                        // Find attendance for this student
                        $attendance = $session->first()->attendances->firstWhere('student_id', $student->id);
                        if ($attendance) {
                            $status = $attendance->status;
                        }
                    }
                    
                    $attendanceData[$dateString] = [
                        'status' => $status,
                        'student_id' => $student->id,
                    ];
                }
                
                // Calculate statistics
                $present = collect($attendanceData)->filter(fn($data) => $data['status'] === 'present')->count();
                $late = collect($attendanceData)->filter(fn($data) => $data['status'] === 'late')->count();
                $absent = collect($attendanceData)->filter(fn($data) => $data['status'] === 'absent')->count();
                $excused = collect($attendanceData)->filter(fn($data) => $data['status'] === 'excused')->count();
                $totalDays = $daysInMonth;
                
                $monthlyData[] = [
                    'student' => $student,
                    'assignment' => $assignment,
                    'attendance' => $attendanceData,
                    'present' => $present,
                    'late' => $late,
                    'absent' => $absent,
                    'excused' => $excused,
                    'total' => $totalDays,
                    'attendance_rate' => $totalDays > 0 ? round(($present + $late) / $totalDays * 100, 2) : 0,
                ];
            }
            
            // Get summary statistics
            $summary = [
                'total_students' => $students->count(),
                'total_present' => collect($monthlyData)->sum('present'),
                'total_late' => collect($monthlyData)->sum('late'),
                'total_absent' => collect($monthlyData)->sum('absent'),
                'total_excused' => collect($monthlyData)->sum('excused'),
                'month_name' => Carbon::create($year, $month, 1)->format('F Y'),
                'class_name' => $selectedClass->name,
            ];
        }
        
        return view('attendance.monthly-report', compact(
            'classes',
            'selectedClass',
            'selectedClassId',
            'monthlyData',
            'days',
            'month',
            'year',
            'summary'
        ));
    }

    /*
    |--------------------------------------------------------------------------
    | EXPORT MONTHLY REPORT TO CSV
    |--------------------------------------------------------------------------
    */
    public function exportMonthlyReport(Request $request)
    {
        $classId = $request->input('class_id');
        
        if (!$classId) {
            return redirect()->back()->with('error', 'Please select a class first.');
        }
        
        // Handle month selection
        if ($request->has('month_year')) {
            $monthYear = $request->month_year;
            $year = (int) substr($monthYear, 0, 4);
            $month = (int) substr($monthYear, 5, 2);
        } else {
            $year = now()->year;
            $month = now()->month;
        }
        
        $selectedClass = StudentClass::findOrFail($classId);
        
        // Get all students in the class
        $students = StudentClassAssignment::with('student')
            ->where('student_class_id', $classId)
            ->where('is_current', true)
            ->orderBy('id')
            ->get();
        
        // Get attendance records for the selected month
        $startDate = Carbon::create($year, $month, 1)->startOfDay();
        $endDate = Carbon::create($year, $month, 1)->endOfMonth()->endOfDay();
        
        $sessions = AttendanceSession::where('student_class_id', $classId)
            ->whereBetween('attendance_date', [$startDate, $endDate])
            ->with(['attendances' => function($query) {
                $query->with('student');
            }])
            ->get()
            ->groupBy('attendance_date');
        
        $daysInMonth = Carbon::create($year, $month, 1)->daysInMonth;
        $days = [];
        for ($i = 1; $i <= $daysInMonth; $i++) {
            $days[] = Carbon::create($year, $month, $i);
        }
        
        // Create CSV data
        $csvData = [];
        
        // Header row
        $header = ['#', 'Student Name', 'Admission No.'];
        foreach ($days as $day) {
            $header[] = $day->format('d');
        }
        $header[] = 'Present';
        $header[] = 'Late';
        $header[] = 'Absent';
        $header[] = 'Excused';
        $header[] = 'Total';
        $header[] = 'Rate %';
        $csvData[] = $header;
        
        // Data rows
        foreach ($students as $index => $assignment) {
            $student = $assignment->student;
            $row = [
                $index + 1,
                $student->first_name . ' ' . $student->last_name,
                $student->student_id ?? 'N/A'
            ];
            
            $present = 0;
            $late = 0;
            $absent = 0;
            $excused = 0;
            
            foreach ($days as $day) {
                $dateString = $day->toDateString();
                $session = $sessions->get($dateString);
                $status = 'Absent';
                
                if ($session) {
                    $attendance = $session->first()->attendances->firstWhere('student_id', $student->id);
                    if ($attendance) {
                        $status = ucfirst($attendance->status);
                        
                        if ($attendance->status === 'present') $present++;
                        elseif ($attendance->status === 'late') $late++;
                        elseif ($attendance->status === 'excused') $excused++;
                        else $absent++;
                    } else {
                        $absent++;
                    }
                } else {
                    $absent++;
                }
                
                $row[] = $status;
            }
            
            $total = $daysInMonth;
            $row[] = $present;
            $row[] = $late;
            $row[] = $absent;
            $row[] = $excused;
            $row[] = $total;
            $row[] = $total > 0 ? round(($present + $late) / $total * 100, 2) : 0;
            
            $csvData[] = $row;
        }
        
        // Create CSV
        $filename = "monthly_attendance_" . str_replace(' ', '_', $selectedClass->name) . "_{$year}_{$month}.csv";
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        
        $output = fopen('php://output', 'w');
        foreach ($csvData as $row) {
            fputcsv($output, $row);
        }
        fclose($output);
        exit;
    }

    /*
    |--------------------------------------------------------------------------
    | DELETE ATTENDANCE SESSION
    |--------------------------------------------------------------------------
    */
    public function destroy($id)
    {
        $session = AttendanceSession::findOrFail($id);
        
        DB::transaction(function () use ($session) {
            // Delete all attendance records for this session
            Attendance::where('attendance_session_id', $session->id)->delete();
            // Delete the session
            $session->delete();
        });
        
        return redirect()->route('attendance-sessions.index')
            ->with('success', 'Attendance session deleted successfully.');
    }
}