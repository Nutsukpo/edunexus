<?php

namespace App\Http\Controllers;

use App\Models\AcademicYear;
use App\Models\Attendance;
use App\Models\AttendanceSession;
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
     *
     * Expected Fees =
     * Class Bill Sheet Amount Per Student
     * × Current Active Students In Class
     *
     * Daily class attendance is supplied to the Blade through this
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

        $activeClasses = StudentClass::where(
            'is_active',
            true
        )->count();

        $maleCount = Student::where(
            'gender',
            'Male'
        )->count();

        $femaleCount = Student::where(
            'gender',
            'Female'
        )->count();

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
            ->whereRaw(
                'LOWER(status) = ?',
                ['present']
            )
            ->count();

        $studentAttendanceRate =
            $totalAttendanceRecords > 0
                ? round(
                    (
                        $presentAttendanceRecords
                        / $totalAttendanceRecords
                    ) * 100,
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
                $staffTotal = DB::table(
                    'staff_attendances'
                )->count();

                $staffPresent = DB::table(
                    'staff_attendances'
                )
                    ->whereRaw(
                        'LOWER(status) = ?',
                        ['present']
                    )
                    ->count();

                $staffAttendanceRate =
                    $staffTotal > 0
                        ? round(
                            (
                                $staffPresent
                                / $staffTotal
                            ) * 100,
                            1
                        )
                        : 0;
            } catch (\Throwable $e) {
                Log::warning(
                    'Unable to calculate staff attendance rate.',
                    [
                        'message' => $e->getMessage(),
                    ]
                );

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

        $academicYears = AcademicYear::query()
            ->orderByDesc('id')
            ->get();

        $requestedAcademicYearId =
            request()->input(
                'academic_year_id'
            );

        $selectedAcademicYear = null;

        /*
        |--------------------------------------------------------------------------
        | USER-SELECTED ACADEMIC YEAR
        |--------------------------------------------------------------------------
        */

        if (
            $requestedAcademicYearId !== null
            && $requestedAcademicYearId !== ''
        ) {
            $selectedAcademicYear =
                $academicYears->firstWhere(
                    'id',
                    (int) $requestedAcademicYearId
                );
        }

        /*
        |--------------------------------------------------------------------------
        | FALLBACK TO CURRENT ACTIVE ASSIGNMENT YEAR
        |--------------------------------------------------------------------------
        */

        if (!$selectedAcademicYear) {
            $activeAssignmentAcademicYearId =
                StudentClassAssignment::query()
                    ->where(
                        'status',
                        'active'
                    )
                    ->where(
                        'is_current',
                        true
                    )
                    ->orderByDesc(
                        'academic_year_id'
                    )
                    ->value(
                        'academic_year_id'
                    );

            $selectedAcademicYear =
                $academicYears->firstWhere(
                    'id',
                    $activeAssignmentAcademicYearId
                )
                ?? $latestAcademicYear;
        }

        $dashboardAcademicYearId =
            $selectedAcademicYear?->id;

        /*
        |--------------------------------------------------------------------------
        | SCHOOL FEE DEFAULTS
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

        /*
        |--------------------------------------------------------------------------
        | SCHOOL FEE CALCULATIONS
        |--------------------------------------------------------------------------
        |
        | Expected Fees:
        |
        | Latest active Bill Sheet amount for each class
        | × current active/current student count in that class.
        |
        | Fees Paid:
        |
        | Completed payments belonging to the selected academic year.
        |
        |--------------------------------------------------------------------------
        */

        if ($dashboardAcademicYearId) {

            /*
            |--------------------------------------------------------------------------
            | CURRENT STUDENTS
            |--------------------------------------------------------------------------
            */

            $assignments = StudentClassAssignment::query()
                ->where(
                    'academic_year_id',
                    $dashboardAcademicYearId
                )
                ->where(
                    'status',
                    'active'
                )
                ->where(
                    'is_current',
                    true
                )
                ->get([
                    'id',
                    'student_id',
                    'student_class_id',
                    'academic_year_id',
                ]);

            /*
            |--------------------------------------------------------------------------
            | STUDENT COUNT BY CLASS
            |--------------------------------------------------------------------------
            */

            $studentCountsByClass = $assignments
                ->groupBy(
                    'student_class_id'
                )
                ->map(
                    fn ($items) =>
                        $items
                            ->pluck('student_id')
                            ->filter()
                            ->unique()
                            ->count()
                );

            /*
            |--------------------------------------------------------------------------
            | ACTIVE CLASSES
            |--------------------------------------------------------------------------
            */

            $classes = StudentClass::query()
                ->where(
                    'is_active',
                    true
                )
                ->orderBy('name')
                ->get([
                    'id',
                    'name',
                ]);

            /*
            |--------------------------------------------------------------------------
            | ACTIVE BILL SHEETS
            |--------------------------------------------------------------------------
            |
            | Bill Sheets are not restricted to approved/published.
            |
            | Newly generated Bill Sheets may be stored as:
            |
            | status = draft
            | is_active = true
            |
            |--------------------------------------------------------------------------
            */

            $billSheets = \App\Models\BillSheet::query()
                ->where(
                    'academic_year_id',
                    $dashboardAcademicYearId
                )
                ->where(
                    'is_active',
                    true
                )
                ->with(
                    'studentClassAssignment'
                )
                ->orderByDesc(
                    'generated_date'
                )
                ->orderByDesc('id')
                ->get([
                    'id',
                    'student_class_assignment_id',
                    'academic_year_id',
                    'total_amount',
                    'net_amount',
                    'generated_date',
                    'status',
                ]);

            /*
            |--------------------------------------------------------------------------
            | REPRESENTATIVE BILL AMOUNT BY CLASS
            |--------------------------------------------------------------------------
            |
            | The newest active Bill Sheet for each class is used.
            |
            |--------------------------------------------------------------------------
            */

            $billAmountByClass = collect();

            foreach ($billSheets as $billSheet) {

                $assignment =
                    $billSheet->studentClassAssignment;

                if (
                    !$assignment
                    || !$assignment->student_class_id
                ) {
                    continue;
                }

                $classId =
                    $assignment->student_class_id;

                /*
                |--------------------------------------------------------------------------
                | NEWEST BILL SHEET WINS
                |--------------------------------------------------------------------------
                */

                if (
                    $billAmountByClass->has(
                        $classId
                    )
                ) {
                    continue;
                }

                $billAmount =
                    $billSheet->net_amount !== null
                        ? (float) $billSheet->net_amount
                        : (float) (
                            $billSheet->total_amount ?? 0
                        );

                if ($billAmount > 0) {
                    $billAmountByClass->put(
                        $classId,
                        round(
                            $billAmount,
                            2
                        )
                    );
                }
            }

            /*
            |--------------------------------------------------------------------------
            | COMPLETED PAYMENTS
            |--------------------------------------------------------------------------
            |
            | Join payments to their class assignment so that payment
            | amounts can be calculated by class.
            |
            |--------------------------------------------------------------------------
            */

            $dashboardPayments =
                DB::table('fee_payments as fp')
                    ->join(
                        'student_class_assignments as sca',
                        'sca.id',
                        '=',
                        'fp.student_class_assignment_id'
                    )
                    ->where(
                        'fp.status',
                        'completed'
                    )
                    ->where(
                        'sca.academic_year_id',
                        $dashboardAcademicYearId
                    )
                    ->select([
                        'fp.id',
                        'fp.student_id',
                        'fp.student_class_assignment_id',
                        'fp.amount',
                        'fp.net_amount',
                        'fp.payment_date',
                        'sca.student_class_id',
                    ])
                    ->get();

            /*
            |--------------------------------------------------------------------------
            | PAID BY CLASS
            |--------------------------------------------------------------------------
            */

            $feesPaidByClass = collect();

            foreach (
                $dashboardPayments as $payment
            ) {

                $classId =
                    $payment->student_class_id;

                if (!$classId) {
                    continue;
                }

                $paidAmount =
                    $payment->net_amount !== null
                        ? (float) $payment->net_amount
                        : (float) (
                            $payment->amount ?? 0
                        );

                $feesPaidByClass->put(
                    $classId,
                    (
                        (float) $feesPaidByClass->get(
                            $classId,
                            0
                        )
                    ) + $paidAmount
                );
            }

            /*
            |--------------------------------------------------------------------------
            | CLASS FINANCIAL POSITION
            |--------------------------------------------------------------------------
            */

            $classFeeData = $classes
                ->map(
                    function ($class) use (
                        $studentCountsByClass,
                        $billAmountByClass,
                        $feesPaidByClass
                    ) {

                        $classId =
                            $class->id;

                        $studentCount =
                            (int) $studentCountsByClass->get(
                                $classId,
                                0
                            );

                        $billAmountPerStudent =
                            (float) $billAmountByClass->get(
                                $classId,
                                0
                            );

                        /*
                        |--------------------------------------------------------------------------
                        | EXPECTED FEES
                        |--------------------------------------------------------------------------
                        */

                        $expectedFees =
                            round(
                                $billAmountPerStudent
                                * $studentCount,
                                2
                            );

                        /*
                        |--------------------------------------------------------------------------
                        | FEES PAID
                        |--------------------------------------------------------------------------
                        */

                        $feesPaid =
                            round(
                                (
                                    (float) $feesPaidByClass->get(
                                        $classId,
                                        0
                                    )
                                ),
                                2
                            );

                        /*
                        |--------------------------------------------------------------------------
                        | OUTSTANDING
                        |--------------------------------------------------------------------------
                        */

                        $outstandingFees =
                            max(
                                0,
                                round(
                                    $expectedFees
                                    - $feesPaid,
                                    2
                                )
                            );

                        /*
                        |--------------------------------------------------------------------------
                        | COLLECTION RATE
                        |--------------------------------------------------------------------------
                        */

                        $collectionRate =
                            $expectedFees > 0
                                ? min(
                                    100,
                                    round(
                                        (
                                            $feesPaid
                                            / $expectedFees
                                        ) * 100,
                                        1
                                    )
                                )
                                : 0;

                        return [
                            'class_id' =>
                                $classId,

                            'class_name' =>
                                $class->name ?? 'N/A',

                            'students' =>
                                $studentCount,

                            'bill_per_student' =>
                                $billAmountPerStudent,

                            'expected_fees' =>
                                $expectedFees,

                            'fees_paid' =>
                                $feesPaid,

                            'outstanding_fees' =>
                                $outstandingFees,

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
            | SCHOOL TOTALS
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

            /*
            |--------------------------------------------------------------------------
            | OUTSTANDING FEES
            |--------------------------------------------------------------------------
            |
            | Sum the class outstanding amounts rather than allowing
            | the school total to become negative.
            |
            |--------------------------------------------------------------------------
            */

            $dashboardOutstandingFees =
                round(
                    (float) $classFeeData->sum(
                        'outstanding_fees'
                    ),
                    2
                );

            /*
            |--------------------------------------------------------------------------
            | COLLECTION RATE
            |--------------------------------------------------------------------------
            */

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
            | CURRENT MONTH PAYMENTS
            |--------------------------------------------------------------------------
            */

            $dashboardCurrentMonthPaid =
                $dashboardPayments
                    ->filter(
                        function ($payment) {

                            if (
                                empty(
                                    $payment->payment_date
                                )
                            ) {
                                return false;
                            }

                            try {
                                return Carbon::parse(
                                    $payment->payment_date
                                )->isSameMonth(
                                    now()
                                );
                            } catch (\Throwable $e) {
                                return false;
                            }
                        }
                    )
                    ->sum(
                        function ($payment) {

                            return (float) (
                                $payment->net_amount !== null
                                    ? $payment->net_amount
                                    : (
                                        $payment->amount
                                        ?? 0
                                    )
                            );
                        }
                    );

            /*
            |--------------------------------------------------------------------------
            | PREVIOUS MONTH PAYMENTS
            |--------------------------------------------------------------------------
            */

            $dashboardPreviousMonthPaid =
                $dashboardPayments
                    ->filter(
                        function ($payment) {

                            if (
                                empty(
                                    $payment->payment_date
                                )
                            ) {
                                return false;
                            }

                            try {
                                return Carbon::parse(
                                    $payment->payment_date
                                )->isSameMonth(
                                    now()->copy()->subMonth()
                                );
                            } catch (\Throwable $e) {
                                return false;
                            }
                        }
                    )
                    ->sum(
                        function ($payment) {

                            return (float) (
                                $payment->net_amount !== null
                                    ? $payment->net_amount
                                    : (
                                        $payment->amount
                                        ?? 0
                                    )
                            );
                        }
                    );

            /*
            |--------------------------------------------------------------------------
            | MONTHLY PAYMENT CHANGE
            |--------------------------------------------------------------------------
            */

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

        /*
        |--------------------------------------------------------------------------
        | DAILY ATTENDANCE
        |--------------------------------------------------------------------------
        */

        $today = now()->toDateString();

        $attendanceDate = request()->input(
            'attendance_date',
            $today
        );

        /*
        |--------------------------------------------------------------------------
        | VALIDATE ATTENDANCE DATE
        |--------------------------------------------------------------------------
        */

        try {
            $attendanceDate =
                Carbon::parse(
                    $attendanceDate
                )->toDateString();
        } catch (\Throwable $e) {
            $attendanceDate = $today;
        }

        /*
        |--------------------------------------------------------------------------
        | TODAY'S ATTENDANCE SUMMARY
        |--------------------------------------------------------------------------
        */

        $todayAttendance =
            $this->buildAttendanceSummary(
                $today
            );

        $todayTotal =
            $todayAttendance['total'];

        $todayPresent =
            $todayAttendance['present'];

        $todayLate =
            $todayAttendance['late'];

        /*
        |--------------------------------------------------------------------------
        | TODAY'S ATTENDANCE RATE
        |--------------------------------------------------------------------------
        |
        | Present + Late are considered attended.
        |
        |--------------------------------------------------------------------------
        */

        $todayRate =
            $todayTotal > 0
                ? round(
                    (
                        (
                            $todayPresent
                            + $todayLate
                        )
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

        $recentAttendance = collect();

        try {

            if (
                Schema::hasTable(
                    'attendances'
                )
                && Schema::hasTable(
                    'attendance_sessions'
                )
            ) {

                $recentAttendance =
                    DB::table('attendances')
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
                        ->orderByDesc(
                            'attendances.created_at'
                        )
                        ->limit(10)
                        ->get();
            }

        } catch (\Throwable $e) {

            Log::warning(
                'Unable to load recent attendance.',
                [
                    'message' => $e->getMessage(),
                ]
            );

            $recentAttendance = collect();
        }

        /*
        |--------------------------------------------------------------------------
        | DASHBOARD VIEW
        |--------------------------------------------------------------------------
        */

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
                'academicYears',
                'selectedAcademicYear',
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
    public function getAttendanceData(
        Request $request
    ): JsonResponse {
        try {

            $days = max(
                1,
                min(
                    365,
                    (int) $request->get(
                        'days',
                        30
                    )
                )
            );

            $start = now()
                ->copy()
                ->subDays(
                    $days - 1
                )
                ->startOfDay();

            $end = now()
                ->copy()
                ->endOfDay();

            /*
            |--------------------------------------------------------------------------
            | ATTENDANCE DATA
            |--------------------------------------------------------------------------
            */

            $rows = DB::table(
                'attendance_sessions'
            )
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

            /*
            |--------------------------------------------------------------------------
            | CREATE DATE MAP
            |--------------------------------------------------------------------------
            */

            $map = [];

            foreach ($rows as $row) {

                $map[$row->date] = [
                    'total' =>
                        (int) $row->total_records,

                    'attended' =>
                        (int) $row->attended_count,
                ];
            }

            /*
            |--------------------------------------------------------------------------
            | BUILD CHART ARRAYS
            |--------------------------------------------------------------------------
            */

            $labels = [];

            $attendance = [];

            $marked = [];

            $cursor = $start->copy();

            while ($cursor <= $end) {

                $key =
                    $cursor->format(
                        'Y-m-d'
                    );

                $labels[] =
                    $cursor->format(
                        'M d, D'
                    );

                $attendance[] =
                    $map[$key]['attended']
                    ?? 0;

                $marked[] =
                    $map[$key]['total']
                    ?? 0;

                $cursor->addDay();
            }

            /*
            |--------------------------------------------------------------------------
            | CHART STATISTICS
            |--------------------------------------------------------------------------
            */

            $daysWithData =
                count(
                    array_filter(
                        $marked,
                        fn ($value) =>
                            $value > 0
                    )
                );

            $totalAttended =
                array_sum(
                    $attendance
                );

            $average =
                $daysWithData > 0
                    ? round(
                        $totalAttended
                        / $daysWithData,
                        1
                    )
                    : 0;

            $peak =
                !empty($attendance)
                    ? max($attendance)
                    : 0;

            $peakIndex =
                $peak > 0
                    ? array_search(
                        $peak,
                        $attendance,
                        true
                    )
                    : false;

            $peakDay =
                $peakIndex !== false
                && isset(
                    $labels[$peakIndex]
                )
                    ? $labels[$peakIndex]
                    : 'N/A';

            /*
            |--------------------------------------------------------------------------
            | JSON RESPONSE
            |--------------------------------------------------------------------------
            */

            return response()->json([
                'status' => 'success',

                'labels' =>
                    $labels,

                'attendance' =>
                    $attendance,

                'benchmark' =>
                    array_fill(
                        0,
                        count($attendance),
                        $average
                    ),

                'marked' =>
                    $marked,

                'total_students' =>
                    $marked,

                'total' =>
                    $totalAttended,

                'average' =>
                    $average,

                'peak' =>
                    $peak,

                'peak_day' =>
                    $peakDay,

                'days_with_data' =>
                    $daysWithData,
            ]);

        } catch (\Throwable $e) {

            Log::error(
                'Dashboard attendance chart error',
                [
                    'message' =>
                        $e->getMessage(),
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

                'message' =>
                    'Unable to load attendance chart data.',
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

            /*
            |--------------------------------------------------------------------------
            | NORMALIZE DATE
            |--------------------------------------------------------------------------
            */

            try {
                $date =
                    Carbon::parse(
                        $date
                    )->toDateString();
            } catch (\Throwable $e) {
                $date =
                    now()->toDateString();
            }

            $summary =
                $this->buildAttendanceSummary(
                    $date
                );

            return response()->json([
                'success' => true,

                'date' =>
                    $date,

                'summary' =>
                    $summary,
            ]);

        } catch (\Throwable $e) {

            Log::error(
                'Dashboard attendance summary error',
                [
                    'message' =>
                        $e->getMessage(),
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

            /*
            |--------------------------------------------------------------------------
            | NORMALIZE DATE
            |--------------------------------------------------------------------------
            */

            try {
                $date =
                    Carbon::parse(
                        $date
                    )->toDateString();
            } catch (\Throwable $e) {
                $date =
                    now()->toDateString();
            }

            $rows =
                $this->buildClassAttendanceData(
                    $date
                );

            return response()->json([
                'success' => true,

                'date' =>
                    $date,

                'classes' =>
                    $rows->values(),

                'total_classes' =>
                    $rows->count(),
            ]);

        } catch (\Throwable $e) {

            Log::error(
                'Dashboard class attendance error',
                [
                    'message' =>
                        $e->getMessage(),

                    'date' =>
                        $request->get('date'),
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
        try {

            $row = DB::table(
                'attendances'
            )
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
                    DB::raw(
                        'COUNT(*) as total'
                    ),

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
                'total' =>
                    (int) (
                        $row->total ?? 0
                    ),

                'present' =>
                    (int) (
                        $row->present ?? 0
                    ),

                'absent' =>
                    (int) (
                        $row->absent ?? 0
                    ),

                'late' =>
                    (int) (
                        $row->late ?? 0
                    ),

                'excused' =>
                    (int) (
                        $row->excused ?? 0
                    ),
            ];

        } catch (\Throwable $e) {

            Log::error(
                'Dashboard attendance summary build error',
                [
                    'date' =>
                        $date,

                    'message' =>
                        $e->getMessage(),
                ]
            );

            return [
                'total' => 0,
                'present' => 0,
                'absent' => 0,
                'late' => 0,
                'excused' => 0,
            ];
        }
    }

    /**
     * Build class attendance rows for one date.
     */
    private function buildClassAttendanceData(
        string $date
    ) {
        /*
        |--------------------------------------------------------------------------
        | ACTIVE CLASSES
        |--------------------------------------------------------------------------
        */

        $classes = StudentClass::query()
            ->where(
                'is_active',
                true
            )
            ->orderBy('name')
            ->get([
                'id',
                'name',
            ]);

        if ($classes->isEmpty()) {
            return collect();
        }

        $classIds =
            $classes->pluck('id');

        /*
        |--------------------------------------------------------------------------
        | CURRENT STUDENTS BY CLASS
        |--------------------------------------------------------------------------
        */

        $studentCounts =
            StudentClassAssignment::query()
                ->whereIn(
                    'student_class_id',
                    $classIds
                )
                ->where(
                    'is_current',
                    true
                )
                ->where(
                    'status',
                    'active'
                )
                ->select(
                    'student_class_id',
                    DB::raw(
                        'COUNT(DISTINCT student_id) as student_count'
                    )
                )
                ->groupBy(
                    'student_class_id'
                )
                ->pluck(
                    'student_count',
                    'student_class_id'
                );

        /*
        |--------------------------------------------------------------------------
        | LATEST ATTENDANCE SESSION FOR EACH CLASS
        |--------------------------------------------------------------------------
        */

        $sessions =
            AttendanceSession::query()
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
                ->groupBy(
                    'student_class_id'
                )
                ->map(
                    fn ($items) =>
                        $items->first()
                );

        /*
        |--------------------------------------------------------------------------
        | SESSION IDS
        |--------------------------------------------------------------------------
        */

        $sessionIds =
            $sessions
                ->pluck('id')
                ->filter()
                ->values();

        /*
        |--------------------------------------------------------------------------
        | ATTENDANCE RECORDS
        |--------------------------------------------------------------------------
        */

        $records =
            $sessionIds->isNotEmpty()
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

        /*
        |--------------------------------------------------------------------------
        | GROUP RECORDS BY SESSION
        |--------------------------------------------------------------------------
        */

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
            ->map(
                function ($class) use (
                    $studentCounts,
                    $sessions,
                    $recordsBySession
                ) {

                    $session =
                        $sessions->get(
                            $class->id
                        );

                    $totalStudents =
                        (int) $studentCounts->get(
                            $class->id,
                            0
                        );

                    $sessionRecords =
                        $session
                            ? $recordsBySession->get(
                                $session->id,
                                collect()
                            )
                            : collect();

                    /*
                    |--------------------------------------------------------------------------
                    | PRESENT
                    |--------------------------------------------------------------------------
                    */

                    $present =
                        $sessionRecords
                            ->filter(
                                fn ($record) =>
                                    strtolower(
                                        trim(
                                            (string)
                                                $record->status
                                        )
                                    ) === 'present'
                            )
                            ->count();

                    /*
                    |--------------------------------------------------------------------------
                    | ABSENT
                    |--------------------------------------------------------------------------
                    */

                    $absent =
                        $sessionRecords
                            ->filter(
                                fn ($record) =>
                                    strtolower(
                                        trim(
                                            (string)
                                                $record->status
                                        )
                                    ) === 'absent'
                            )
                            ->count();

                    /*
                    |--------------------------------------------------------------------------
                    | LATE
                    |--------------------------------------------------------------------------
                    */

                    $late =
                        $sessionRecords
                            ->filter(
                                fn ($record) =>
                                    strtolower(
                                        trim(
                                            (string)
                                                $record->status
                                        )
                                    ) === 'late'
                            )
                            ->count();

                    /*
                    |--------------------------------------------------------------------------
                    | EXCUSED
                    |--------------------------------------------------------------------------
                    */

                    $excused =
                        $sessionRecords
                            ->filter(
                                fn ($record) =>
                                    strtolower(
                                        trim(
                                            (string)
                                                $record->status
                                        )
                                    ) === 'excused'
                            )
                            ->count();

                    /*
                    |--------------------------------------------------------------------------
                    | MARKED
                    |--------------------------------------------------------------------------
                    |
                    | Excused students are excluded from the denominator.
                    |
                    |--------------------------------------------------------------------------
                    */

                    $marked =
                        $present
                        + $absent
                        + $late;

                    /*
                    |--------------------------------------------------------------------------
                    | ATTENDED
                    |--------------------------------------------------------------------------
                    */

                    $attended =
                        $present
                        + $late;

                    /*
                    |--------------------------------------------------------------------------
                    | ATTENDANCE RATE
                    |--------------------------------------------------------------------------
                    */

                    $rate =
                        $marked > 0
                            ? round(
                                (
                                    $attended
                                    / $marked
                                ) * 100,
                                1
                            )
                            : 0;

                    /*
                    |--------------------------------------------------------------------------
                    | STATUS
                    |--------------------------------------------------------------------------
                    */

                    if (!$session) {

                        $status =
                            'Not Taken';

                        $statusClass =
                            'secondary';

                    } elseif (
                        $marked === 0
                    ) {

                        $status =
                            'No Records';

                        $statusClass =
                            'warning';

                    } elseif (
                        $rate >= 80
                    ) {

                        $status =
                            'Excellent';

                        $statusClass =
                            'success';

                    } elseif (
                        $rate >= 60
                    ) {

                        $status =
                            'Good';

                        $statusClass =
                            'info';

                    } elseif (
                        $rate >= 40
                    ) {

                        $status =
                            'Average';

                        $statusClass =
                            'warning';

                    } else {

                        $status =
                            'Poor';

                        $statusClass =
                            'danger';
                    }

                    return [
                        'class_name' =>
                            $class->name,

                        'total_students' =>
                            $totalStudents,

                        'present' =>
                            $present,

                        'absent' =>
                            $absent,

                        'late' =>
                            $late,

                        'excused' =>
                            $excused,

                        'rate' =>
                            $rate,

                        'status' =>
                            $status,

                        'status_class' =>
                            $statusClass,
                    ];
                }
            )
            ->filter(
                fn ($row) =>
                    $row['total_students'] > 0
            )
            ->values();
    }
}
