<?php

namespace App\Http\Controllers;

use App\Models\AcademicYear;
use App\Models\Attendance;
use App\Models\AttendanceSession;
use App\Models\StudentClass;
use App\Models\StudentClassAssignment;
use App\Models\SystemSetting;
use App\Models\Term;
use App\Services\EdunexusAuthorizationService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class AttendanceSessionController extends Controller
{
    public function __construct(
        private EdunexusAuthorizationService $authorization
    ) {
    }

    /*
    |--------------------------------------------------------------------------
    | CURRENT ACADEMIC PERIOD
    |--------------------------------------------------------------------------
    */

    private function currentPeriod(): array
    {
        $settings = SystemSetting::instance();

        $academicYear = $settings->currentAcademicYear;
        $term = $settings->currentTerm;

        if (!$academicYear || !$term) {
            throw ValidationException::withMessages([
                'academic_period' =>
                    'The current academic year and term have not been configured in System Settings.',
            ]);
        }

        if ((int) $term->academic_year_id !== (int) $academicYear->id) {
            throw ValidationException::withMessages([
                'academic_period' =>
                    'The current term does not belong to the current academic year.',
            ]);
        }

        return [
            'settings' => $settings,
            'academic_year' => $academicYear,
            'term' => $term,
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | DISPLAY ALL ATTENDANCE SESSIONS
    |--------------------------------------------------------------------------
    */

    public function index()
    {
        abort_unless(
            auth()->user()->can('attendance.view'),
            403
        );

        $period = $this->currentPeriod();

        $sessions = AttendanceSession::with([
            'studentClass',
            'academicYear',
            'term',
            'takenBy',
        ])
            ->where('academic_year_id', $period['academic_year']->id)
            ->where('term_id', $period['term']->id)
            ->when(
                $this->authorization->isScopedStaff(auth()->user()),
                function ($query) {
                    $query->whereIn(
                        'student_class_id',
                        $this->authorization
                            ->accessibleClasses(auth()->user())
                            ->select('student_classes.id')
                    );
                }
            )
            ->latest('attendance_date')
            ->latest('id')
            ->paginate(20);

        return view('attendance.index', [
            'sessions' => $sessions,
            'settings' => $period['settings'],
            'currentAcademicYear' => $period['academic_year'],
            'currentTerm' => $period['term'],
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | SHOW CREATE FORM
    |--------------------------------------------------------------------------
    */

    public function create()
    {
        abort_unless(
            auth()->user()->can('attendance.create'),
            403
        );

        $period = $this->currentPeriod();

        $classes = $this->authorization
            ->accessibleClasses(auth()->user())
            ->orderBy('name')
            ->get();

        return view('attendance.create', [
            'classes' => $classes,
            'settings' => $period['settings'],
            'currentAcademicYear' => $period['academic_year'],
            'currentTerm' => $period['term'],
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | STORE ATTENDANCE
    |--------------------------------------------------------------------------
    */

    public function store(Request $request)
    {
        abort_unless(
            auth()->user()->can('attendance.create'),
            403
        );

        $period = $this->currentPeriod();

        $validated = $request->validate([
            'student_class_id' => [
                'required',
                'integer',
                'exists:student_classes,id',
            ],
            'attendance_date' => [
                'required',
                'date',
            ],
            'attendance' => [
                'required',
                'array',
            ],
        ]);

        $classId = (int) $validated['student_class_id'];

        abort_unless(
            $this->authorization->canAccessClass(
                auth()->user(),
                $classId
            ),
            403
        );

        /*
        |--------------------------------------------------------------------------
        | Validate Attendance Date Against Current Term
        |--------------------------------------------------------------------------
        */

        $attendanceDate = Carbon::parse(
            $validated['attendance_date']
        )->startOfDay();

        $term = $period['term'];

        if (
            $term->start_date
            && $attendanceDate->lt(
                Carbon::parse($term->start_date)->startOfDay()
            )
        ) {
            return back()
                ->withInput()
                ->with(
                    'error',
                    'The attendance date is before the start of the current term.'
                );
        }

        if (
            $term->end_date
            && $attendanceDate->gt(
                Carbon::parse($term->end_date)->endOfDay()
            )
        ) {
            return back()
                ->withInput()
                ->with(
                    'error',
                    'The attendance date is after the end of the current term.'
                );
        }

        /*
        |--------------------------------------------------------------------------
        | Prevent Duplicate Attendance
        |--------------------------------------------------------------------------
        */

        $exists = AttendanceSession::query()
            ->where('student_class_id', $classId)
            ->where('academic_year_id', $period['academic_year']->id)
            ->where('term_id', $period['term']->id)
            ->whereDate(
                'attendance_date',
                $attendanceDate->toDateString()
            )
            ->exists();

        if ($exists) {
            return back()
                ->withInput()
                ->with(
                    'error',
                    'Attendance has already been taken for this class on this date.'
                );
        }

        DB::transaction(function () use (
            $validated,
            $classId,
            $attendanceDate,
            $period
        ) {
            /*
            |--------------------------------------------------------------------------
            | Get Only Current Students For Current Academic Period
            |--------------------------------------------------------------------------
            */

            $assignments = StudentClassAssignment::query()
                ->where('student_class_id', $classId)
                ->where(
                    'academic_year_id',
                    $period['academic_year']->id
                )
                ->where(
                    'term_id',
                    $period['term']->id
                )
                ->where('is_current', true)
                ->whereIn('status', [
                    'active',
                    'repeated',
                ])
                ->get()
                ->keyBy('id');

            /*
            |--------------------------------------------------------------------------
            | Create Attendance Session
            |--------------------------------------------------------------------------
            */

            $session = AttendanceSession::create([
                'student_class_id' => $classId,
                'academic_year_id' => $period['academic_year']->id,
                'term_id' => $period['term']->id,
                'attendance_date' => $attendanceDate->toDateString(),
                'taken_by' => auth()->id(),
                'status' => 'completed',
            ]);

            /*
            |--------------------------------------------------------------------------
            | Save Attendance Records
            |--------------------------------------------------------------------------
            */

            foreach ($validated['attendance'] as $assignmentId => $status) {
                $assignment = $assignments->get(
                    (int) $assignmentId
                );

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
            ->route(
                'student-classes.show',
                $classId
            )
            ->with(
                'success',
                'Attendance submitted successfully.'
            );
    }

    /*
    |--------------------------------------------------------------------------
    | STORE ATTENDANCE FOR SPECIFIC CLASS (AJAX)
    |--------------------------------------------------------------------------
    */

    public function storeForClass(
        Request $request,
        $classId
    ) {
        abort_unless(
            auth()->user()->can('attendance.create'),
            403
        );

        $classId = (int) $classId;

        abort_unless(
            $this->authorization->canAccessClass(
                auth()->user(),
                $classId
            ),
            403
        );

        $period = $this->currentPeriod();

        $validated = $request->validate([
            'attendance_date' => [
                'required',
                'date',
            ],
            'attendance' => [
                'required',
                'array',
            ],
        ]);

        $attendanceDate = Carbon::parse(
            $validated['attendance_date']
        )->startOfDay();

        /*
        |--------------------------------------------------------------------------
        | Validate Date Against Current Term
        |--------------------------------------------------------------------------
        */

        if (
            $period['term']->start_date
            && $attendanceDate->lt(
                Carbon::parse(
                    $period['term']->start_date
                )->startOfDay()
            )
        ) {
            return response()->json([
                'success' => false,
                'message' =>
                    'The attendance date is before the start of the current term.',
            ], 422);
        }

        if (
            $period['term']->end_date
            && $attendanceDate->gt(
                Carbon::parse(
                    $period['term']->end_date
                )->endOfDay()
            )
        ) {
            return response()->json([
                'success' => false,
                'message' =>
                    'The attendance date is after the end of the current term.',
            ], 422);
        }

        /*
        |--------------------------------------------------------------------------
        | Duplicate Check
        |--------------------------------------------------------------------------
        */

        $exists = AttendanceSession::query()
            ->where('student_class_id', $classId)
            ->where(
                'academic_year_id',
                $period['academic_year']->id
            )
            ->where(
                'term_id',
                $period['term']->id
            )
            ->whereDate(
                'attendance_date',
                $attendanceDate->toDateString()
            )
            ->exists();

        if ($exists) {
            return response()->json([
                'success' => false,
                'message' =>
                    'Attendance has already been taken for this class on this date.',
            ], 422);
        }

        try {
            DB::transaction(function () use (
                $validated,
                $classId,
                $attendanceDate,
                $period
            ) {
                /*
                |--------------------------------------------------------------------------
                | Current Assignments Only
                |--------------------------------------------------------------------------
                */

                $assignments = StudentClassAssignment::query()
                    ->where('student_class_id', $classId)
                    ->where(
                        'academic_year_id',
                        $period['academic_year']->id
                    )
                    ->where(
                        'term_id',
                        $period['term']->id
                    )
                    ->where('is_current', true)
                    ->whereIn('status', [
                        'active',
                        'repeated',
                    ])
                    ->get()
                    ->keyBy('student_id');

                $session = AttendanceSession::create([
                    'student_class_id' => $classId,
                    'academic_year_id' =>
                        $period['academic_year']->id,
                    'term_id' =>
                        $period['term']->id,
                    'attendance_date' =>
                        $attendanceDate->toDateString(),
                    'taken_by' => auth()->id(),
                    'status' => 'completed',
                ]);

                foreach ($validated['attendance'] as $studentId => $data) {
                    $assignment = $assignments->get(
                        (int) $studentId
                    );

                    if (!$assignment) {
                        continue;
                    }

                    Attendance::create([
                        'attendance_session_id' => $session->id,
                        'student_class_assignment_id' =>
                            $assignment->id,
                        'student_id' =>
                            $assignment->student_id,
                        'status' =>
                            is_array($data)
                                ? ($data['status'] ?? 'present')
                                : $data,
                    ]);
                }
            });

            return response()->json([
                'success' => true,
                'message' =>
                    'Attendance saved successfully.',
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' =>
                    'Error saving attendance: '
                    . $e->getMessage(),
            ], 500);
        }
    }

    /*
    |--------------------------------------------------------------------------
    | GET ATTENDANCE DATA (AJAX)
    |--------------------------------------------------------------------------
    */

    public function getAttendanceData(
        Request $request,
        $classId
    ) {
        abort_unless(
            auth()->user()->can('attendance.view'),
            403
        );

        $classId = (int) $classId;

        abort_unless(
            $this->authorization->canAccessClass(
                auth()->user(),
                $classId
            ),
            403
        );

        $period = $this->currentPeriod();

        $date = $request->get('date');
        $month = $request->get('month');
        $year = $request->get('year');

        $query = AttendanceSession::with([
            'attendances.student',
            'studentClass',
            'academicYear',
            'term',
        ])
            ->where('student_class_id', $classId)
            ->where(
                'academic_year_id',
                $period['academic_year']->id
            )
            ->where(
                'term_id',
                $period['term']->id
            );

        if ($date) {
            $query->whereDate(
                'attendance_date',
                $date
            );
        } else {
            if ($month) {
                $query->whereMonth(
                    'attendance_date',
                    $month
                );
            }

            if ($year) {
                $query->whereYear(
                    'attendance_date',
                    $year
                );
            }
        }

        $sessions = $query
            ->orderBy(
                'attendance_date',
                'desc'
            )
            ->get();

        if ($sessions->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' =>
                    'No attendance records found for the selected criteria.',
            ]);
        }

        $records = [];

        foreach ($sessions as $session) {
            foreach ($session->attendances as $attendance) {
                if (!$attendance->student) {
                    continue;
                }

                $records[] = [
                    'date' =>
                        $session->attendance_date
                            ? $session->attendance_date->toDateString()
                            : null,

                    'student_name' =>
                        trim(
                            $attendance->student->first_name
                            . ' '
                            . $attendance->student->last_name
                        ),

                    'student_id' =>
                        $attendance->student->student_id,

                    'gender' =>
                        $attendance->student->gender,

                    'status' =>
                        $attendance->status,

                    'remarks' =>
                        $attendance->remarks ?? '',
                ];
            }
        }

        $summary = [
            'present' =>
                collect($records)
                    ->where('status', 'present')
                    ->count(),

            'absent' =>
                collect($records)
                    ->where('status', 'absent')
                    ->count(),

            'late' =>
                collect($records)
                    ->where('status', 'late')
                    ->count(),

            'excused' =>
                collect($records)
                    ->where('status', 'excused')
                    ->count(),
        ];

        return response()->json([
            'success' => true,
            'records' => $records,
            'summary' => $summary,
            'total' => count($records),

            'academic_year' =>
                $period['academic_year']->name,

            'term' =>
                $period['term']->name,
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | SHOW SINGLE SESSION
    |--------------------------------------------------------------------------
    */

    public function show(
        AttendanceSession $attendanceSession
    ) {
        abort_unless(
            auth()->user()->can('attendance.view'),
            403
        );

        abort_unless(
            $this->authorization->canAccessClass(
                auth()->user(),
                (int) $attendanceSession->student_class_id
            ),
            403
        );

        $attendanceSession->load([
            'studentClass',
            'academicYear',
            'term',
            'attendances.student',
            'attendances.classAssignment',
            'takenBy',
        ]);

        return view(
            'attendance.show',
            compact('attendanceSession')
        );
    }

    /*
    |--------------------------------------------------------------------------
    | AJAX LOAD CLASS STUDENTS
    |--------------------------------------------------------------------------
    */

    public function getStudents($classId)
    {
        abort_unless(
            auth()->user()->can('attendance.view')
            || auth()->user()->can('attendance.create'),
            403
        );

        $classId = (int) $classId;

        abort_unless(
            $this->authorization->canAccessClass(
                auth()->user(),
                $classId
            ),
            403
        );

        $period = $this->currentPeriod();

        $students = StudentClassAssignment::with('student')
            ->where(
                'student_class_id',
                $classId
            )
            ->where(
                'academic_year_id',
                $period['academic_year']->id
            )
            ->where(
                'term_id',
                $period['term']->id
            )
            ->where('is_current', true)
            ->whereIn('status', [
                'active',
                'repeated',
            ])
            ->orderBy('id')
            ->get();

        return response()->json($students);
    }

    /*
    |--------------------------------------------------------------------------
    | CREATE ATTENDANCE FOR CLASS
    |--------------------------------------------------------------------------
    */

    public function createForClass(
        $studentClassId
    ) {
        abort_unless(
            auth()->user()->can('attendance.create'),
            403
        );

        $studentClassId = (int) $studentClassId;

        abort_unless(
            $this->authorization->canAccessClass(
                auth()->user(),
                $studentClassId
            ),
            403
        );

        $period = $this->currentPeriod();

        $studentClass = StudentClass::findOrFail(
            $studentClassId
        );

        $assignments = StudentClassAssignment::with('student')
            ->where(
                'student_class_id',
                $studentClassId
            )
            ->where(
                'academic_year_id',
                $period['academic_year']->id
            )
            ->where(
                'term_id',
                $period['term']->id
            )
            ->where('is_current', true)
            ->whereIn('status', [
                'active',
                'repeated',
            ])
            ->orderBy('id')
            ->get();

        return view('attendance.take', [
            'studentClass' => $studentClass,
            'assignments' => $assignments,
            'settings' => $period['settings'],
            'currentAcademicYear' =>
                $period['academic_year'],
            'currentTerm' =>
                $period['term'],
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | CHECK IF ATTENDANCE EXISTS
    |--------------------------------------------------------------------------
    */

    public function checkExists(
        Request $request
    ) {
        abort_unless(
            auth()->user()->can('attendance.view')
            || auth()->user()->can('attendance.create'),
            403
        );

        $classId = (int) $request->get(
            'class_id'
        );

        $date = $request->get('date');

        abort_unless(
            $this->authorization->canAccessClass(
                auth()->user(),
                $classId
            ),
            403
        );

        $period = $this->currentPeriod();

        $session = AttendanceSession::query()
            ->where(
                'student_class_id',
                $classId
            )
            ->where(
                'academic_year_id',
                $period['academic_year']->id
            )
            ->where(
                'term_id',
                $period['term']->id
            )
            ->whereDate(
                'attendance_date',
                $date
            )
            ->first();

        if ($session) {
            $attendances = Attendance::query()
                ->where(
                    'attendance_session_id',
                    $session->id
                )
                ->get()
                ->pluck(
                    'status',
                    'student_class_assignment_id'
                );

            return response()->json([
                'exists' => true,
                'session_id' => $session->id,
                'attendance' => $attendances,

                'academic_year' =>
                    $period['academic_year']->name,

                'term' =>
                    $period['term']->name,
            ]);
        }

        return response()->json([
            'exists' => false,
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | MONTHLY ATTENDANCE REPORT
    |--------------------------------------------------------------------------
    */

    public function monthlyReport(Request $request)
    {
        abort_unless(
            auth()->user()->can('attendance.view'),
            403
        );

        $period = $this->currentPeriod();

        $classes = $this->authorization
            ->accessibleClasses(auth()->user())
            ->orderBy('name')
            ->get();

        $selectedClassId = $request->input('class_id');

        if (!$selectedClassId && $classes->isNotEmpty()) {
            $selectedClassId = $classes->first()->id;
        }

        /*
        |--------------------------------------------------------------------------
        | Determine Month
        |--------------------------------------------------------------------------
        */

        if ($request->filled('month_year')) {
            $monthYear = $request->input('month_year');

            if (!preg_match('/^\d{4}-\d{2}$/', $monthYear)) {
                return redirect()
                    ->route('attendance.monthly-report')
                    ->with('error', 'Invalid month selected.');
            }

            $year = (int) substr($monthYear, 0, 4);
            $month = (int) substr($monthYear, 5, 2);

            if ($month < 1 || $month > 12) {
                return redirect()
                    ->route('attendance.monthly-report')
                    ->with('error', 'Invalid month selected.');
            }
        } else {
            $referenceDate = $period['term']->start_date
                ? Carbon::parse($period['term']->start_date)
                : now();

            $year = $referenceDate->year;
            $month = $referenceDate->month;
        }

        $selectedClass = null;
        $monthlyData = [];
        $days = [];
        $summary = [];

        /*
        |--------------------------------------------------------------------------
        | Month Range
        |--------------------------------------------------------------------------
        */

        $startDate = Carbon::create(
            $year,
            $month,
            1
        )->startOfDay();

        $endDate = Carbon::create(
            $year,
            $month,
            1
        )->endOfMonth()
            ->endOfDay();

        /*
        |--------------------------------------------------------------------------
        | Days In Selected Month
        |--------------------------------------------------------------------------
        */

        $daysInMonth = $startDate->daysInMonth;

        for ($i = 1; $i <= $daysInMonth; $i++) {
            $days[] = Carbon::create(
                $year,
                $month,
                $i
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Selected Class
        |--------------------------------------------------------------------------
        */

        if ($selectedClassId) {

            $selectedClass = $classes->firstWhere(
                'id',
                (int) $selectedClassId
            );

            abort_unless(
                $selectedClass,
                404
            );

            /*
            |--------------------------------------------------------------------------
            | Current Students
            |--------------------------------------------------------------------------
            */

            $students = StudentClassAssignment::with('student')
                ->where(
                    'student_class_id',
                    $selectedClass->id
                )
                ->where(
                    'academic_year_id',
                    $period['academic_year']->id
                )
                ->where(
                    'term_id',
                    $period['term']->id
                )
                ->where(
                    'is_current',
                    true
                )
                ->whereIn(
                    'status',
                    [
                        'active',
                        'repeated',
                    ]
                )
                ->orderBy('id')
                ->get();

            /*
            |--------------------------------------------------------------------------
            | Attendance Sessions
            |--------------------------------------------------------------------------
            |
            | Only sessions belonging to the current System Settings
            | academic year and term are included.
            |
            */

            $sessions = AttendanceSession::query()
                ->where(
                    'student_class_id',
                    $selectedClass->id
                )
                ->where(
                    'academic_year_id',
                    $period['academic_year']->id
                )
                ->where(
                    'term_id',
                    $period['term']->id
                )
                ->whereBetween(
                    'attendance_date',
                    [
                        $startDate,
                        $endDate,
                    ]
                )
                ->with([
                    'attendances.student',
                ])
                ->orderBy('attendance_date')
                ->get()
                ->keyBy(
                    fn ($session) =>
                        Carbon::parse(
                            $session->attendance_date
                        )->toDateString()
                );

            /*
            |--------------------------------------------------------------------------
            | Recorded Attendance Days
            |--------------------------------------------------------------------------
            */

            $recordedDates = $sessions
                ->keys()
                ->map(
                    fn ($date) => Carbon::parse($date)->toDateString()
                )
                ->values();

            $recordedDays = $recordedDates->count();

            /*
            |--------------------------------------------------------------------------
            | Student Attendance Data
            |--------------------------------------------------------------------------
            */

            foreach ($students as $assignment) {

                $student = $assignment->student;

                if (!$student) {
                    continue;
                }

                $attendanceData = [];

                $present = 0;
                $late = 0;
                $absent = 0;
                $excused = 0;

                foreach ($days as $day) {

                    $dateString = $day->toDateString();

                    $session = $sessions->get($dateString);

                    /*
                    |--------------------------------------------------------------------------
                    | No Session = No Record
                    |--------------------------------------------------------------------------
                    */

                    if (!$session) {

                        $attendanceData[$dateString] = [
                            'status' => 'no_record',
                            'student_id' => $student->id,
                        ];

                        continue;
                    }

                    /*
                    |--------------------------------------------------------------------------
                    | Session Exists
                    |--------------------------------------------------------------------------
                    */

                    $attendance = $session
                        ->attendances
                        ->firstWhere(
                            'student_class_assignment_id',
                            $assignment->id
                        );

                    /*
                    |--------------------------------------------------------------------------
                    | Session Exists But Student Has No Record
                    |--------------------------------------------------------------------------
                    |
                    | A completed class session means a student without an
                    | attendance record is considered absent.
                    |
                    */

                    if (!$attendance) {

                        $status = 'absent';

                    } else {

                        $status = $attendance->status;
                    }

                    $attendanceData[$dateString] = [
                        'status' => $status,
                        'student_id' => $student->id,
                    ];

                    /*
                    |--------------------------------------------------------------------------
                    | Count Only Recorded Sessions
                    |--------------------------------------------------------------------------
                    */

                    switch ($status) {

                        case 'present':
                            $present++;
                            break;

                        case 'late':
                            $late++;
                            break;

                        case 'excused':
                            $excused++;
                            break;

                        case 'absent':
                            $absent++;
                            break;
                    }
                }

                /*
                |--------------------------------------------------------------------------
                | Student Recorded Total
                |--------------------------------------------------------------------------
                */

                $studentRecordedDays =
                    $present
                    + $late
                    + $absent
                    + $excused;

                /*
                |--------------------------------------------------------------------------
                | Attendance Rate
                |--------------------------------------------------------------------------
                |
                | Present and Late count as attendance.
                | Excused and Absent do not.
                |
                */

                $attendanceRate =
                    $studentRecordedDays > 0
                        ? round(
                            (
                                $present
                                + $late
                            )
                            / $studentRecordedDays
                            * 100,
                            2
                        )
                        : 0;

                $monthlyData[] = [

                    'student' =>
                        $student,

                    'assignment' =>
                        $assignment,

                    'attendance' =>
                        $attendanceData,

                    'present' =>
                        $present,

                    'late' =>
                        $late,

                    'absent' =>
                        $absent,

                    'excused' =>
                        $excused,

                    'total' =>
                        $studentRecordedDays,

                    'attendance_rate' =>
                        $attendanceRate,
                ];
            }

            /*
            |--------------------------------------------------------------------------
            | Summary
            |--------------------------------------------------------------------------
            */

            $summary = [

                'total_students' =>
                    $students->count(),

                'recorded_days' =>
                    $recordedDays,

                'total_present' =>
                    collect($monthlyData)
                        ->sum('present'),

                'total_late' =>
                    collect($monthlyData)
                        ->sum('late'),

                'total_absent' =>
                    collect($monthlyData)
                        ->sum('absent'),

                'total_excused' =>
                    collect($monthlyData)
                        ->sum('excused'),

                'total_records' =>
                    collect($monthlyData)
                        ->sum('total'),

                'month_name' =>
                    Carbon::create(
                        $year,
                        $month,
                        1
                    )->format('F Y'),

                'class_name' =>
                    $selectedClass->name,

                'academic_year' =>
                    $period['academic_year']->name,

                'term' =>
                    $period['term']->name,
            ];
        }

        return view(
            'attendance.monthly-report',
            [
                'classes' =>
                    $classes,

                'selectedClass' =>
                    $selectedClass,

                'selectedClassId' =>
                    $selectedClassId,

                'monthlyData' =>
                    $monthlyData,

                'days' =>
                    $days,

                'month' =>
                    $month,

                'year' =>
                    $year,

                'summary' =>
                    $summary,

                'settings' =>
                    $period['settings'],

                'currentAcademicYear' =>
                    $period['academic_year'],

                'currentTerm' =>
                    $period['term'],
            ]
        );
    }

    /*
    |--------------------------------------------------------------------------
    | EXPORT MONTHLY REPORT TO CSV
    |--------------------------------------------------------------------------
    */

    public function exportMonthlyReport(Request $request)
    {
        abort_unless(
            auth()->user()->can('attendance.view'),
            403
        );

        $period = $this->currentPeriod();

        $classId = $request->input('class_id');

        if (!$classId) {
            return redirect()
                ->back()
                ->with(
                    'error',
                    'Please select a class first.'
                );
        }

        $classId = (int) $classId;

        abort_unless(
            $this->authorization->canAccessClass(
                auth()->user(),
                $classId
            ),
            403
        );

        /*
        |--------------------------------------------------------------------------
        | Month
        |--------------------------------------------------------------------------
        */

        if ($request->filled('month_year')) {

            $monthYear =
                $request->input('month_year');

            if (!preg_match('/^\d{4}-\d{2}$/', $monthYear)) {
                return redirect()
                    ->back()
                    ->with(
                        'error',
                        'Invalid month selected.'
                    );
            }

            $year = (int) substr(
                $monthYear,
                0,
                4
            );

            $month = (int) substr(
                $monthYear,
                5,
                2
            );

        } else {

            $referenceDate =
                $period['term']->start_date
                    ? Carbon::parse(
                        $period['term']->start_date
                    )
                    : now();

            $year =
                $referenceDate->year;

            $month =
                $referenceDate->month;
        }

        if ($month < 1 || $month > 12) {
            return redirect()
                ->back()
                ->with(
                    'error',
                    'Invalid month selected.'
                );
        }

        /*
        |--------------------------------------------------------------------------
        | Class
        |--------------------------------------------------------------------------
        */

        $selectedClass =
            $this->authorization
                ->accessibleClasses(auth()->user())
                ->where('student_classes.id', $classId)
                ->first();

        abort_unless(
            $selectedClass,
            404
        );

        /*
        |--------------------------------------------------------------------------
        | Current Students
        |--------------------------------------------------------------------------
        */

        $students =
            StudentClassAssignment::with('student')
                ->where(
                    'student_class_id',
                    $classId
                )
                ->where(
                    'academic_year_id',
                    $period['academic_year']->id
                )
                ->where(
                    'term_id',
                    $period['term']->id
                )
                ->where(
                    'is_current',
                    true
                )
                ->whereIn(
                    'status',
                    [
                        'active',
                        'repeated',
                    ]
                )
                ->orderBy('id')
                ->get();

        /*
        |--------------------------------------------------------------------------
        | Month Range
        |--------------------------------------------------------------------------
        */

        $startDate =
            Carbon::create(
                $year,
                $month,
                1
            )->startOfDay();

        $endDate =
            Carbon::create(
                $year,
                $month,
                1
            )->endOfMonth()
                ->endOfDay();

        /*
        |--------------------------------------------------------------------------
        | Attendance Sessions
        |--------------------------------------------------------------------------
        */

        $sessions =
            AttendanceSession::query()
                ->where(
                    'student_class_id',
                    $classId
                )
                ->where(
                    'academic_year_id',
                    $period['academic_year']->id
                )
                ->where(
                    'term_id',
                    $period['term']->id
                )
                ->whereBetween(
                    'attendance_date',
                    [
                        $startDate,
                        $endDate,
                    ]
                )
                ->with([
                    'attendances.student',
                ])
                ->orderBy('attendance_date')
                ->get()
                ->keyBy(
                    fn ($session) =>
                        Carbon::parse(
                            $session->attendance_date
                        )->toDateString()
                );

        /*
        |--------------------------------------------------------------------------
        | Days
        |--------------------------------------------------------------------------
        */

        $daysInMonth =
            $startDate->daysInMonth;

        $days = [];

        for (
            $i = 1;
            $i <= $daysInMonth;
            $i++
        ) {
            $days[] =
                Carbon::create(
                    $year,
                    $month,
                    $i
                );
        }

        /*
        |--------------------------------------------------------------------------
        | CSV
        |--------------------------------------------------------------------------
        */

        return response()->streamDownload(
            function () use (
                $students,
                $days,
                $sessions
            ) {

                $output =
                    fopen(
                        'php://output',
                        'w'
                    );

                /*
                |--------------------------------------------------------------------------
                | Report Header
                |--------------------------------------------------------------------------
                */

                $header = [
                    '#',
                    'Student Name',
                    'Admission No.',
                ];

                foreach ($days as $day) {
                    $header[] =
                        $day->format('d');
                }

                $header[] = 'Present';
                $header[] = 'Late';
                $header[] = 'Absent';
                $header[] = 'Excused';
                $header[] = 'Total Recorded';
                $header[] = 'Rate %';

                fputcsv(
                    $output,
                    $header
                );

                /*
                |--------------------------------------------------------------------------
                | Student Rows
                |--------------------------------------------------------------------------
                */

                foreach (
                    $students as $index => $assignment
                ) {

                    $student =
                        $assignment->student;

                    if (!$student) {
                        continue;
                    }

                    $row = [

                        $index + 1,

                        trim(
                            $student->first_name
                            . ' '
                            . $student->middle_name
                            . ' '
                            . $student->last_name
                        ),

                        $student->student_id
                            ?? 'N/A',
                    ];

                    $present = 0;
                    $late = 0;
                    $absent = 0;
                    $excused = 0;

                    foreach ($days as $day) {

                        $dateString =
                            $day->toDateString();

                        $session =
                            $sessions->get(
                                $dateString
                            );

                        /*
                        |--------------------------------------------------------------------------
                        | No Session
                        |--------------------------------------------------------------------------
                        */

                        if (!$session) {

                            $row[] =
                                'No Record';

                            continue;
                        }

                        /*
                        |--------------------------------------------------------------------------
                        | Find Student Attendance
                        |--------------------------------------------------------------------------
                        */

                        $attendance =
                            $session
                                ->attendances
                                ->firstWhere(
                                    'student_class_assignment_id',
                                    $assignment->id
                                );

                        /*
                        |--------------------------------------------------------------------------
                        | Session Exists But No Student Record
                        |--------------------------------------------------------------------------
                        */

                        if (!$attendance) {

                            $status =
                                'absent';

                        } else {

                            $status =
                                $attendance->status;
                        }

                        /*
                        |--------------------------------------------------------------------------
                        | Count
                        |--------------------------------------------------------------------------
                        */

                        switch ($status) {

                            case 'present':
                                $present++;
                                break;

                            case 'late':
                                $late++;
                                break;

                            case 'excused':
                                $excused++;
                                break;

                            case 'absent':
                                $absent++;
                                break;
                        }

                        $row[] =
                            ucfirst(
                                $status
                            );
                    }

                    /*
                    |--------------------------------------------------------------------------
                    | Totals
                    |--------------------------------------------------------------------------
                    */

                    $total =
                        $present
                        + $late
                        + $absent
                        + $excused;

                    $rate =
                        $total > 0
                            ? round(
                                (
                                    $present
                                    + $late
                                )
                                / $total
                                * 100,
                                2
                            )
                            : 0;

                    $row[] = $present;
                    $row[] = $late;
                    $row[] = $absent;
                    $row[] = $excused;
                    $row[] = $total;
                    $row[] = $rate;

                    fputcsv(
                        $output,
                        $row
                    );
                }

                fclose($output);

            },
            'monthly_attendance_'
            . str_replace(
                ' ',
                '_',
                $selectedClass->name
            )
            . '_'
            . $year
            . '_'
            . str_pad(
                $month,
                2,
                '0',
                STR_PAD_LEFT
            )
            . '.csv',
            [
                'Content-Type' =>
                    'text/csv; charset=UTF-8',
            ]
        );
    }

    /*
    |--------------------------------------------------------------------------
    | DELETE ATTENDANCE SESSION
    |--------------------------------------------------------------------------
    */

    public function destroy($id)
    {
        abort_unless(
            auth()->user()->can('attendance.delete'),
            403
        );

        $session =
            AttendanceSession::findOrFail(
                $id
            );

        abort_unless(
            $this->authorization->canAccessClass(
                auth()->user(),
                (int) $session->student_class_id
            ),
            403
        );

        DB::transaction(
            function () use ($session) {
                Attendance::where(
                    'attendance_session_id',
                    $session->id
                )->delete();

                $session->delete();
            }
        );

        return redirect()
            ->route(
                'attendance-sessions.index'
            )
            ->with(
                'success',
                'Attendance session deleted successfully.'
            );
    }
}