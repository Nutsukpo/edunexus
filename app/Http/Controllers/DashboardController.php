<?php

namespace App\Http\Controllers;

use App\Models\AcademicYear;
use App\Models\Attendance;
use App\Models\AttendanceSession;
use App\Models\FeePayment;
use App\Models\Staff;
use App\Models\Student;
use App\Models\StudentClass;
use App\Models\StudentClassAssignment;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

class DashboardController extends Controller
{
    /**
     * Main dashboard.
     *
     * Financial rule:
     *     Expected Fees =
     *     Class Bill Sheet Amount Per Student
     *     × Current Active Students In Class
     *
     * Daily class attendance is supplied to the Blade through the same
     * controller and can also be refreshed through getClassAttendance().
     */
    public function index()
    {
        /*
        |--------------------------------------------------------------------------
        | BASIC COUNTS
        |--------------------------------------------------------------------------
        */

        $totalStudents = Student::count();

        $totalStaff = Staff::count();

        $totalClasses = StudentClass::count();

        $activeClasses = StudentClass::where('is_active', true)->count();

        $maleCount = Student::where('gender', 'Male')->count();

        $femaleCount = Student::where('gender', 'Female')->count();

        $studentsThisYear = Student::whereYear(
            'created_at',
            now()->year
        )->count();

        $staffThisYear = Staff::whereYear(
            'created_at',
            now()->year
        )->count();

        /*
        |--------------------------------------------------------------------------
        | STUDENT ATTENDANCE RATE
        |--------------------------------------------------------------------------
        */

        $totalAttendanceRecords = Attendance::count();

        $presentAttendanceRecords = Attendance::query()
            ->whereRaw('LOWER(status) = ?', ['present'])
            ->count();

        $studentAttendanceRate = $totalAttendanceRecords > 0
            ? round(
                ($presentAttendanceRecords / $totalAttendanceRecords) * 100,
                1
            )
            : 0;

        /*
        |--------------------------------------------------------------------------
        | STAFF ATTENDANCE RATE
        |--------------------------------------------------------------------------
        */

        $staffAttendanceRate = 0;

        if (Schema::hasTable('staff_attendances')) {
            try {
                $staffTotal = DB::table('staff_attendances')->count();

                $staffPresent = DB::table('staff_attendances')
                    ->whereRaw('LOWER(status) = ?', ['present'])
                    ->count();

                $staffAttendanceRate = $staffTotal > 0
                    ? round(
                        ($staffPresent / $staffTotal) * 100,
                        1
                    )
                    : 0;
            } catch (\Throwable $e) {
                $staffAttendanceRate = 0;
            }
        }

        /*
        |--------------------------------------------------------------------------
        | CURRENT ACADEMIC YEAR
        |--------------------------------------------------------------------------
        */

        $latestAcademicYear = AcademicYear::query()
            ->orderByDesc('id')
            ->first();

        $dashboardAcademicYearId = $latestAcademicYear?->id;

        /*
        |--------------------------------------------------------------------------
        | SCHOOL FEE TOTALS
        |--------------------------------------------------------------------------
        |
        | The dashboard follows the requested school-fee rule:
        |
        | Expected Fees =
        |     CLASS BILL AMOUNT PER STUDENT
        |     ×
        |     NUMBER OF CURRENT ACTIVE STUDENTS IN THAT CLASS
        |
        | IMPORTANT:
        | Bill Sheets are generated per StudentClassAssignment and are stored
        | as DRAFT initially. Therefore expected-fee reporting must NOT require
        | approved/published status.
        |
        | The Bill Sheet itself contains the per-student amount in
        | net_amount / total_amount.
        |
        | Paid Fees =
        |     completed FeePayment records linked to the current assignments.
        |
        | Outstanding =
        |     Expected Fees - Paid Fees
        |--------------------------------------------------------------------------
        */

        $dashboardTotalFees = 0.0;
        $dashboardTotalFeesPaid = 0.0;
        $dashboardOutstandingFees = 0.0;
        $dashboardCollectionRate = 0.0;

        $dashboardCurrentMonthPaid = 0.0;
        $dashboardPreviousMonthPaid = 0.0;
        $dashboardMonthlyChange = 0.0;

        $dashboardPayments = collect();
        $classFeeData = collect();

        if ($dashboardAcademicYearId) {

            /*
            |--------------------------------------------------------------------------
            | CURRENT ACTIVE STUDENT ASSIGNMENTS
            |--------------------------------------------------------------------------
            */

            $currentAssignments = StudentClassAssignment::query()
                ->where(
                    'academic_year_id',
                    $dashboardAcademicYearId
                )
                ->where(
                    'is_current',
                    true
                )
                ->where(
                    'status',
                    'active'
                )
                ->get([
                    'id',
                    'student_id',
                    'student_class_id',
                    'academic_year_id',
                ]);

            $assignmentIds =
                $currentAssignments->pluck('id');

            if ($assignmentIds->isNotEmpty()) {

                /*
                |--------------------------------------------------------------------------
                | CURRENT STUDENT COUNT BY CLASS
                |--------------------------------------------------------------------------
                */

                $studentCountByClass =
                    $currentAssignments
                        ->groupBy('student_class_id')
                        ->map(function ($items) {
                            return $items
                                ->pluck('student_id')
                                ->filter()
                                ->unique()
                                ->count();
                        });

                /*
                |--------------------------------------------------------------------------
                | CLASS BILL AMOUNT
                |--------------------------------------------------------------------------
                |
                | BillSheetController creates one Bill Sheet for each current
                | StudentClassAssignment and stores:
                |
                |     total_amount
                |     net_amount
                |     status = draft
                |     is_active = true
                |
                | We intentionally remove the status restriction here.
                |
                | We also determine the class through the assignment table,
                | rather than depending on a model relationship.
                |--------------------------------------------------------------------------
                */

                $billRows = DB::table('bill_sheets as bs')
                    ->join(
                        'student_class_assignments as sca',
                        'sca.id',
                        '=',
                        'bs.student_class_assignment_id'
                    )
                    ->where(
                        'bs.academic_year_id',
                        $dashboardAcademicYearId
                    )
                    ->where(
                        'bs.is_active',
                        true
                    )
                    ->whereIn(
                        'bs.student_class_assignment_id',
                        $assignmentIds
                    )
                    ->orderByDesc(
                        'bs.generated_date'
                    )
                    ->orderByDesc(
                        'bs.id'
                    )
                    ->get([
                        'bs.id',
                        'bs.student_class_assignment_id',
                        'bs.total_amount',
                        'bs.net_amount',
                        'bs.generated_date',
                        'sca.student_class_id',
                    ]);

                $classBillAmount = collect();

                foreach ($billRows as $bill) {

                    $classId =
                        (int) $bill->student_class_id;

                    if (
                        !$classId ||
                        $classBillAmount->has($classId)
                    ) {
                        continue;
                    }

                    /*
                    | Prefer net_amount when it contains a real value;
                    | otherwise fall back to total_amount.
                    */
                    $netAmount =
                        $bill->net_amount !== null
                            ? (float) $bill->net_amount
                            : null;

                    $totalAmount =
                        $bill->total_amount !== null
                            ? (float) $bill->total_amount
                            : 0.0;

                    $perStudentBill =
                        $netAmount !== null
                            ? $netAmount
                            : $totalAmount;

                    $classBillAmount->put(
                        $classId,
                        round(
                            max(
                                0,
                                $perStudentBill
                            ),
                            2
                        )
                    );
                }

                /*
                |--------------------------------------------------------------------------
                | COMPLETED PAYMENTS
                |--------------------------------------------------------------------------
                */

                $dashboardPayments =
                    FeePayment::query()
                        ->whereIn(
                            'student_class_assignment_id',
                            $assignmentIds
                        )
                        ->whereRaw(
                            'LOWER(status) = ?',
                            ['completed']
                        )
                        ->get([
                            'id',
                            'student_id',
                            'student_class_assignment_id',
                            'amount',
                            'net_amount',
                            'payment_date',
                        ]);

                $classIdByAssignment =
                    $currentAssignments
                        ->mapWithKeys(
                            function ($assignment) {
                                return [
                                    $assignment->id =>
                                        $assignment->student_class_id,
                                ];
                            }
                        );

                $paidByClass = collect();

                foreach (
                    $dashboardPayments
                    as $payment
                ) {

                    $classId =
                        $classIdByAssignment->get(
                            $payment->student_class_assignment_id
                        );

                    if (!$classId) {
                        continue;
                    }

                    $paymentAmount =
                        $payment->net_amount !== null
                            ? (float) $payment->net_amount
                            : (float) (
                                $payment->amount ?? 0
                            );

                    $paidByClass->put(
                        $classId,
                        round(
                            (float) $paidByClass->get(
                                $classId,
                                0
                            ) + $paymentAmount,
                            2
                        )
                    );
                }

                /*
                |--------------------------------------------------------------------------
                | BUILD CLASS FINANCE DATA
                |--------------------------------------------------------------------------
                */

                $classes =
                    StudentClass::query()
                        ->where(
                            'is_active',
                            true
                        )
                        ->orderBy('name')
                        ->get([
                            'id',
                            'name',
                        ]);

                $classFeeData =
                    $classes
                        ->map(
                            function ($class) use (
                                $studentCountByClass,
                                $classBillAmount,
                                $paidByClass
                            ) {

                                $students =
                                    (int) $studentCountByClass->get(
                                        $class->id,
                                        0
                                    );

                                $billPerStudent =
                                    (float) $classBillAmount->get(
                                        $class->id,
                                        0
                                    );

                                /*
                                | REQUIRED FORMULA
                                */
                                $expected =
                                    round(
                                        $billPerStudent * $students,
                                        2
                                    );

                                $paid =
                                    round(
                                        (float) $paidByClass->get(
                                            $class->id,
                                            0
                                        ),
                                        2
                                    );

                                $outstanding =
                                    max(
                                        0,
                                        round(
                                            $expected - $paid,
                                            2
                                        )
                                    );

                                $collectionRate =
                                    $expected > 0
                                        ? min(
                                            100,
                                            round(
                                                (
                                                    $paid
                                                    / $expected
                                                ) * 100,
                                                1
                                            )
                                        )
                                        : 0;

                                return [
                                    'class_id' =>
                                        $class->id,

                                    'class_name' =>
                                        $class->name,

                                    'students' =>
                                        $students,

                                    'bill_per_student' =>
                                        $billPerStudent,

                                    'expected_fees' =>
                                        $expected,

                                    'fees_paid' =>
                                        $paid,

                                    'outstanding_fees' =>
                                        $outstanding,

                                    'collection_rate' =>
                                        $collectionRate,
                                ];
                            }
                        )
                        ->filter(
                            fn ($row) =>
                                $row['students'] > 0
                        )
                        ->sortByDesc(
                            'expected_fees'
                        )
                        ->values();

                /*
                |--------------------------------------------------------------------------
                | WHOLE SCHOOL FINANCIAL TOTALS
                |--------------------------------------------------------------------------
                */

                $dashboardTotalFees =
                    round(
                        (float) $classFeeData->sum(
                            'expected_fees'
                        ),
                        2
                    );

                $dashboardTotalFeesPaid =
                    round(
                        (float) $classFeeData->sum(
                            'fees_paid'
                        ),
                        2
                    );

                $dashboardOutstandingFees =
                    round(
                        max(
                            0,
                            $dashboardTotalFees
                            - $dashboardTotalFeesPaid
                        ),
                        2
                    );

                $dashboardCollectionRate =
                    $dashboardTotalFees > 0
                        ? min(
                            100,
                            round(
                                (
                                    $dashboardTotalFeesPaid
                                    / $dashboardTotalFees
                                ) * 100,
                                1
                            )
                        )
                        : 0;

                /*
                |--------------------------------------------------------------------------
                | MONTHLY COLLECTION CHANGE
                |--------------------------------------------------------------------------
                */

                $dashboardCurrentMonthPaid =
                    $dashboardPayments
                        ->filter(
                            function ($payment) {
                                return !empty(
                                    $payment->payment_date
                                )
                                && Carbon::parse(
                                    $payment->payment_date
                                )->isSameMonth(
                                    now()
                                );
                            }
                        )
                        ->sum(
                            function ($payment) {
                                return (float) (
                                    $payment->net_amount
                                    !== null
                                        ? $payment->net_amount
                                        : (
                                            $payment->amount
                                            ?? 0
                                        )
                                );
                            }
                        );

                $dashboardPreviousMonthPaid =
                    $dashboardPayments
                        ->filter(
                            function ($payment) {
                                return !empty(
                                    $payment->payment_date
                                )
                                && Carbon::parse(
                                    $payment->payment_date
                                )->isSameMonth(
                                    now()->copy()->subMonth()
                                );
                            }
                        )
                        ->sum(
                            function ($payment) {
                                return (float) (
                                    $payment->net_amount
                                    !== null
                                        ? $payment->net_amount
                                        : (
                                            $payment->amount
                                            ?? 0
                                        )
                                );
                            }
                        );

                $dashboardMonthlyChange =
                    $dashboardPreviousMonthPaid > 0
                        ? round(
                            (
                                (
                                    $dashboardCurrentMonthPaid
                                    - $dashboardPreviousMonthPaid
                                )
                                / $dashboardPreviousMonthPaid
                            ) * 100,
                            1
                        )
                        : (
                            $dashboardCurrentMonthPaid > 0
                                ? 100
                                : 0
                        );
            }
        }

        /*
        |--------------------------------------------------------------------------
        | DAILY ATTENDANCE
        |--------------------------------------------------------------------------
        */

        $today = now()->toDateString();

        /*
        |--------------------------------------------------------------------------
        | SELECTED ATTENDANCE DATE
        |--------------------------------------------------------------------------
        */

        $attendanceDate = request()->input(
            'attendance_date',
            $today
        );

        try {
            $attendanceDate = Carbon::parse(
                $attendanceDate
            )->toDateString();
        } catch (\Throwable $e) {
            $attendanceDate = $today;
        }

        $todayAttendance = $this->buildAttendanceSummary(
            $today
        );

        $todayTotal = $todayAttendance['total'];
        $todayPresent = $todayAttendance['present'];
        $todayLate = $todayAttendance['late'];

        $todayRate = $todayTotal > 0
            ? round(
                (
                    ($todayPresent + $todayLate)
                    / $todayTotal
                ) * 100,
                1
            )
            : 0;

        /*
        |--------------------------------------------------------------------------
        | INITIAL CLASS ATTENDANCE TABLE
        |--------------------------------------------------------------------------
        */

        $dashboardDailyAttendance =
            $this->buildClassAttendanceData(
                $attendanceDate
            );

        $dashboardAttendanceRows =
            $dashboardDailyAttendance->count();

        /*
        |--------------------------------------------------------------------------
        | RECENT ATTENDANCE ACTIVITY
        |--------------------------------------------------------------------------
        */

        $recentAttendance = DB::table('attendances')
            ->join(
                'students',
                'attendances.student_id',
                '=',
                'students.id'
            )
            ->join(
                'attendance_sessions',
                'attendances.attendance_session_id',
                '=',
                'attendance_sessions.id'
            )
            ->select(
                'attendances.id',
                'attendances.status',
                'attendances.created_at',
                'students.first_name',
                'students.last_name',
                'students.student_id as student_number',
                'attendance_sessions.attendance_date'
            )
            ->orderByDesc('attendances.created_at')
            ->limit(10)
            ->get();

        return view(
            'dashboard.index',
            compact(
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
                'latestAcademicYear',
                'dashboardAcademicYearId',
                'dashboardTotalFees',
                'dashboardTotalFeesPaid',
                'dashboardOutstandingFees',
                'dashboardCollectionRate',
                'dashboardCurrentMonthPaid',
                'dashboardPreviousMonthPaid',
                'dashboardMonthlyChange',
                'classFeeData',
                'todayTotal',
                'todayPresent',
                'todayLate',
                'todayRate',
                'attendanceDate',
                'recentAttendance',
                'dashboardDailyAttendance',
                'dashboardAttendanceRows'
            )
        );
    }

    /**
     * Attendance chart data.
     */
    public function getAttendanceData(Request $request): JsonResponse
    {
        try {
            $days = max(
                1,
                min(
                    365,
                    (int) $request->get('days', 30)
                )
            );

            $start = now()
                ->copy()
                ->subDays($days - 1)
                ->startOfDay();

            $end = now()
                ->copy()
                ->endOfDay();

            $rows = DB::table('attendance_sessions')
                ->leftJoin(
                    'attendances',
                    'attendance_sessions.id',
                    '=',
                    'attendances.attendance_session_id'
                )
                ->whereDate(
                    'attendance_sessions.attendance_date',
                    '>=',
                    $start->toDateString()
                )
                ->whereDate(
                    'attendance_sessions.attendance_date',
                    '<=',
                    $end->toDateString()
                )
                ->select(
                    DB::raw(
                        'DATE(attendance_sessions.attendance_date) as date'
                    ),
                    DB::raw(
                        'COUNT(attendances.id) as total_records'
                    ),
                    DB::raw(
                        "SUM(
                            CASE
                                WHEN LOWER(attendances.status)
                                    IN ('present', 'late')
                                THEN 1
                                ELSE 0
                            END
                        ) as attended_count"
                    )
                )
                ->groupBy(
                    DB::raw(
                        'DATE(attendance_sessions.attendance_date)'
                    )
                )
                ->orderBy(
                    DB::raw(
                        'DATE(attendance_sessions.attendance_date)'
                    )
                )
                ->get();

            $map = [];

            foreach ($rows as $row) {
                $map[$row->date] = [
                    'total' => (int) $row->total_records,
                    'attended' => (int) $row->attended_count,
                ];
            }

            $labels = [];
            $attendance = [];
            $marked = [];

            $cursor = $start->copy();

            while ($cursor <= $end) {
                $key = $cursor->format('Y-m-d');

                $labels[] = $cursor->format(
                    'M d, D'
                );

                $attendance[] =
                    $map[$key]['attended'] ?? 0;

                $marked[] =
                    $map[$key]['total'] ?? 0;

                $cursor->addDay();
            }

            $daysWithData = count(
                array_filter(
                    $marked,
                    fn ($value) =>
                        $value > 0
                )
            );

            $totalAttended =
                array_sum($attendance);

            $average = $daysWithData > 0
                ? round(
                    $totalAttended /
                    $daysWithData,
                    1
                )
                : 0;

            $peak = !empty($attendance)
                ? max($attendance)
                : 0;

            $peakIndex = $peak > 0
                ? array_search(
                    $peak,
                    $attendance,
                    true
                )
                : false;

            $peakDay =
                $peakIndex !== false &&
                isset($labels[$peakIndex])
                    ? $labels[$peakIndex]
                    : 'N/A';

            return response()->json([
                'status' => 'success',
                'labels' => $labels,
                'attendance' => $attendance,
                'benchmark' => array_fill(
                    0,
                    count($attendance),
                    $average
                ),
                'marked' => $marked,
                'total_students' => $marked,
                'total' => $totalAttended,
                'average' => $average,
                'peak' => $peak,
                'peak_day' => $peakDay,
                'days_with_data' => $daysWithData,
            ]);
        } catch (\Throwable $e) {
            Log::error(
                'Dashboard attendance chart error',
                [
                    'message' => $e->getMessage(),
                ]
            );

            return response()->json([
                'status' => 'error',
                'labels' => [],
                'attendance' => [],
                'benchmark' => [],
                'marked' => [],
                'total_students' => [],
                'total' => 0,
                'average' => 0,
                'peak' => 0,
                'peak_day' => 'N/A',
                'days_with_data' => 0,
            ], 500);
        }
    }

    /**
     * Daily attendance summary.
     */
    public function getAttendanceSummary(
        Request $request
    ): JsonResponse {
        try {
            $date = $request->get(
                'date',
                now()->toDateString()
            );

            $summary =
                $this->buildAttendanceSummary(
                    $date
                );

            return response()->json([
                'success' => true,
                'date' => $date,
                'summary' => $summary,
            ]);
        } catch (\Throwable $e) {
            Log::error(
                'Dashboard attendance summary error',
                [
                    'message' => $e->getMessage(),
                ]
            );

            return response()->json([
                'success' => false,
                'message' =>
                    'Unable to load attendance summary.',
            ], 500);
        }
    }

    /**
     * Daily attendance by class.
     *
     * This endpoint is the single source used by the dashboard table.
     */
    public function getClassAttendance(
        Request $request
    ): JsonResponse {
        try {
            $date = $request->get(
                'date',
                now()->toDateString()
            );

            $rows =
                $this->buildClassAttendanceData(
                    $date
                );

            return response()->json([
                'success' => true,
                'date' => $date,
                'classes' => $rows->values(),
                'total_classes' => $rows->count(),
            ]);
        } catch (\Throwable $e) {
            Log::error(
                'Dashboard class attendance error',
                [
                    'message' => $e->getMessage(),
                    'date' => $request->get('date'),
                ]
            );

            return response()->json([
                'success' => false,
                'message' =>
                    'Unable to load class attendance.',
            ], 500);
        }
    }

    /**
     * Build daily attendance summary from the actual attendance tables.
     */
    private function buildAttendanceSummary(
        string $date
    ): array {
        $row = DB::table('attendances')
            ->join(
                'attendance_sessions',
                'attendances.attendance_session_id',
                '=',
                'attendance_sessions.id'
            )
            ->whereDate(
                'attendance_sessions.attendance_date',
                $date
            )
            ->select(
                DB::raw('COUNT(*) as total'),
                DB::raw(
                    "SUM(
                        CASE
                            WHEN LOWER(attendances.status) = 'present'
                            THEN 1
                            ELSE 0
                        END
                    ) as present"
                ),
                DB::raw(
                    "SUM(
                        CASE
                            WHEN LOWER(attendances.status) = 'absent'
                            THEN 1
                            ELSE 0
                        END
                    ) as absent"
                ),
                DB::raw(
                    "SUM(
                        CASE
                            WHEN LOWER(attendances.status) = 'late'
                            THEN 1
                            ELSE 0
                        END
                    ) as late"
                ),
                DB::raw(
                    "SUM(
                        CASE
                            WHEN LOWER(attendances.status) = 'excused'
                            THEN 1
                            ELSE 0
                        END
                    ) as excused"
                )
            )
            ->first();

        return [
            'total' => (int) ($row->total ?? 0),
            'present' => (int) ($row->present ?? 0),
            'absent' => (int) ($row->absent ?? 0),
            'late' => (int) ($row->late ?? 0),
            'excused' => (int) ($row->excused ?? 0),
        ];
    }

    /**
     * Build class attendance rows for one date.
     */
    private function buildClassAttendanceData(
        string $date
    ) {
        $classes = StudentClass::query()
            ->where('is_active', true)
            ->orderBy('name')
            ->get([
                'id',
                'name',
            ]);

        if ($classes->isEmpty()) {
            return collect();
        }

        $classIds = $classes->pluck('id');

        /*
        |--------------------------------------------------------------------------
        | CURRENT STUDENTS BY CLASS
        |--------------------------------------------------------------------------
        */

        $studentCounts = StudentClassAssignment::query()
            ->whereIn(
                'student_class_id',
                $classIds
            )
            ->where('is_current', true)
            ->where('status', 'active')
            ->select(
                'student_class_id',
                DB::raw(
                    'COUNT(DISTINCT student_id) as student_count'
                )
            )
            ->groupBy('student_class_id')
            ->pluck(
                'student_count',
                'student_class_id'
            );

        /*
        |--------------------------------------------------------------------------
        | LATEST SESSION FOR EACH CLASS
        |--------------------------------------------------------------------------
        */

        $sessions = AttendanceSession::query()
            ->whereIn(
                'student_class_id',
                $classIds
            )
            ->whereDate(
                'attendance_date',
                $date
            )
            ->orderByDesc('id')
            ->get([
                'id',
                'student_class_id',
                'attendance_date',
            ])
            ->groupBy('student_class_id')
            ->map(
                fn ($items) =>
                    $items->first()
            );

        $sessionIds = $sessions
            ->pluck('id')
            ->filter()
            ->values();

        /*
        |--------------------------------------------------------------------------
        | ATTENDANCE RECORDS
        |--------------------------------------------------------------------------
        */

        $records = $sessionIds->isNotEmpty()
            ? Attendance::query()
                ->whereIn(
                    'attendance_session_id',
                    $sessionIds
                )
                ->get([
                    'attendance_session_id',
                    'student_id',
                    'status',
                ])
            : collect();

        $recordsBySession =
            $records->groupBy(
                'attendance_session_id'
            );

        /*
        |--------------------------------------------------------------------------
        | BUILD RESPONSE
        |--------------------------------------------------------------------------
        */

        return $classes
            ->map(function ($class) use (
                $studentCounts,
                $sessions,
                $recordsBySession
            ) {
                $session = $sessions->get(
                    $class->id
                );

                $totalStudents = (int) $studentCounts->get(
                    $class->id,
                    0
                );

                $sessionRecords = $session
                    ? $recordsBySession->get(
                        $session->id,
                        collect()
                    )
                    : collect();

                $present = $sessionRecords
                    ->filter(
                        fn ($record) =>
                            strtolower(
                                trim(
                                    (string) $record->status
                                )
                            ) === 'present'
                    )
                    ->count();

                $absent = $sessionRecords
                    ->filter(
                        fn ($record) =>
                            strtolower(
                                trim(
                                    (string) $record->status
                                )
                            ) === 'absent'
                    )
                    ->count();

                $late = $sessionRecords
                    ->filter(
                        fn ($record) =>
                            strtolower(
                                trim(
                                    (string) $record->status
                                )
                            ) === 'late'
                    )
                    ->count();

                $excused = $sessionRecords
                    ->filter(
                        fn ($record) =>
                            strtolower(
                                trim(
                                    (string) $record->status
                                )
                            ) === 'excused'
                    )
                    ->count();

                /*
                | A student marked present/late is considered attended.
                | Excused is excluded from the denominator.
                */
                $marked =
                    $present +
                    $absent +
                    $late;

                $attended =
                    $present +
                    $late;

                $rate = $marked > 0
                    ? round(
                        ($attended / $marked) * 100,
                        1
                    )
                    : 0;

                if (!$session) {
                    $status = 'Not Taken';
                    $statusClass = 'secondary';
                } elseif ($marked === 0) {
                    $status = 'No Records';
                    $statusClass = 'warning';
                } elseif ($rate >= 80) {
                    $status = 'Excellent';
                    $statusClass = 'success';
                } elseif ($rate >= 60) {
                    $status = 'Good';
                    $statusClass = 'info';
                } elseif ($rate >= 40) {
                    $status = 'Average';
                    $statusClass = 'warning';
                } else {
                    $status = 'Poor';
                    $statusClass = 'danger';
                }

                return [
                    'class_name' => $class->name,
                    'total_students' => $totalStudents,
                    'present' => $present,
                    'absent' => $absent,
                    'late' => $late,
                    'excused' => $excused,
                    'rate' => $rate,
                    'status' => $status,
                    'status_class' => $statusClass,
                ];
            })
            ->filter(
                fn ($row) =>
                    $row['total_students'] > 0
            )
            ->values();
    }
}
