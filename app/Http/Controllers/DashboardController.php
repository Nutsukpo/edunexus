<?php

namespace App\Http\Controllers;

use App\Models\AcademicYear;
use App\Models\Attendance;
use App\Models\AttendanceSession;
use App\Models\BillSheet;
use App\Models\Staff;
use App\Models\Student;
use App\Models\StudentClass;
use App\Models\StudentClassAssignment;
use App\Models\SystemSetting;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

class DashboardController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | MAIN DASHBOARD
    |--------------------------------------------------------------------------
    |
    | IMPORTANT:
    |
    | The dashboard period comes ONLY from System Settings.
    |
    | No request parameter can override the central academic year or term.
    |
    |--------------------------------------------------------------------------
    */
    public function index()
    {
        /*
        |--------------------------------------------------------------------------
        | CENTRAL ACADEMIC PERIOD
        |--------------------------------------------------------------------------
        */

        $settings = SystemSetting::instance()->load([
            'currentAcademicYear',
            'currentTerm',
        ]);

        $currentAcademicYear = $settings->currentAcademicYear;
        $currentTerm = $settings->currentTerm;

        $dashboardAcademicYearId =
            $currentAcademicYear?->id;

        $dashboardTermId =
            $currentTerm?->id;

        /*
        |--------------------------------------------------------------------------
        | BASIC SCHOOL COUNTS
        |--------------------------------------------------------------------------
        |
        | These remain system-wide counts, preserving the existing dashboard
        | functionality.
        |
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
        |
        | STRICT CENTRAL PERIOD:
        |
        | academic_year_id = System Settings current year
        | term_id          = System Settings current term
        |
        |--------------------------------------------------------------------------
        */

        $totalAttendanceRecords = 0;
        $presentAttendanceRecords = 0;
        $lateAttendanceRecords = 0;

        if (
            $dashboardAcademicYearId
            && $dashboardTermId
        ) {
            $attendanceBaseQuery =
                Attendance::query()
                    ->whereHas(
                        'attendanceSession',
                        function ($query) use (
                            $dashboardAcademicYearId,
                            $dashboardTermId
                        ) {
                            $query
                                ->where(
                                    'academic_year_id',
                                    $dashboardAcademicYearId
                                )
                                ->where(
                                    'term_id',
                                    $dashboardTermId
                                );
                        }
                    );

            $totalAttendanceRecords =
                (clone $attendanceBaseQuery)->count();

            $presentAttendanceRecords =
                (clone $attendanceBaseQuery)
                    ->whereRaw(
                        'LOWER(status) = ?',
                        ['present']
                    )
                    ->count();

            $lateAttendanceRecords =
                (clone $attendanceBaseQuery)
                    ->whereRaw(
                        'LOWER(status) = ?',
                        ['late']
                    )
                    ->count();
        }

        $studentAttendanceRate =
            $totalAttendanceRecords > 0
                ? round(
                    (
                        (
                            $presentAttendanceRecords
                            + $lateAttendanceRecords
                        )
                        / $totalAttendanceRecords
                    ) * 100,
                    1
                )
                : 0;

        /*
        |--------------------------------------------------------------------------
        | STAFF ATTENDANCE RATE
        |--------------------------------------------------------------------------
        |
        | STRICT CENTRAL PERIOD:
        |
        | Staff attendance must respect the Academic Year and Term configured
        | in System Settings, exactly like student attendance.
        |
        | IMPORTANT:
        | - We filter the stored academic_year_id and term_id on each staff
        |   attendance record.
        | - We DO NOT use the current date, created_at, or a current-record flag
        |   to determine the academic period.
        | - We preserve the existing calculation: PRESENT records divided by
        |   all staff attendance records in the selected period.
        | - Historical attendance remains available because records are filtered
        |   by their stored academic period rather than whether the period is
        |   currently active.
        |--------------------------------------------------------------------------
        */

        $staffAttendanceRate = 0;

        if (
            Schema::hasTable(
                'staff_attendances'
            )
        ) {
            try {

                /*
                |--------------------------------------------------------------------------
                | No valid central period = no staff attendance rate.
                |--------------------------------------------------------------------------
                */

                if (
                    $dashboardAcademicYearId
                    && $dashboardTermId
                ) {

                    /*
                    |--------------------------------------------------------------------------
                    | BASE STAFF ATTENDANCE QUERY
                    |--------------------------------------------------------------------------
                    */

                    $staffAttendanceBaseQuery =
                        DB::table(
                            'staff_attendances'
                        )
                            ->where(
                                'academic_year_id',
                                $dashboardAcademicYearId
                            )
                            ->where(
                                'term_id',
                                $dashboardTermId
                            );

                    /*
                    |--------------------------------------------------------------------------
                    | TOTAL STAFF ATTENDANCE RECORDS
                    |--------------------------------------------------------------------------
                    |
                    | This is intentionally the same denominator used by the
                    | previous dashboard calculation, but now restricted to
                    | the central Academic Year + Term.
                    |--------------------------------------------------------------------------
                    */

                    $staffTotal =
                        (clone $staffAttendanceBaseQuery)
                            ->count();

                    /*
                    |--------------------------------------------------------------------------
                    | PRESENT STAFF ATTENDANCE RECORDS
                    |--------------------------------------------------------------------------
                    */

                    $staffPresent =
                        (clone $staffAttendanceBaseQuery)
                            ->whereRaw(
                                'LOWER(TRIM(status)) = ?',
                                ['present']
                            )
                            ->count();

                    /*
                    |--------------------------------------------------------------------------
                    | STAFF ATTENDANCE RATE
                    |--------------------------------------------------------------------------
                    */

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
                }

            } catch (\Throwable $e) {

                Log::warning(
                    'Unable to calculate period-specific staff attendance rate.',
                    [
                        'academic_year_id' =>
                            $dashboardAcademicYearId,

                        'term_id' =>
                            $dashboardTermId,

                        'message' =>
                            $e->getMessage(),
                    ]
                );

                $staffAttendanceRate = 0;
            }
        }

        /*
        |--------------------------------------------------------------------------
        | ACADEMIC YEARS
        |--------------------------------------------------------------------------
        |
        | Kept available for other dashboard functionality.
        |
        |--------------------------------------------------------------------------
        */

        $academicYears =
            AcademicYear::query()
                ->orderByDesc('start_date')
                ->orderByDesc('id')
                ->get();

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
        | SELECTED PERIOD FEES
        |--------------------------------------------------------------------------
        |
        | IMPORTANT:
        |
        | Fee calculations are based on the exact academic year + term
        | selected in System Settings and on the Bill Sheets belonging to
        | that period.
        |
        | We deliberately DO NOT require StudentClassAssignment::is_current
        | here. Once a term is closed, its assignments correctly become
        | completed/not-current, but its financial records remain historical
        | and must continue to display.
        |
        | Draft Bill Sheets are included in expected fees because bills can
        | be prepared before the operational period is opened. This allows
        | administrators to prepare future-term bills without advancing the
        | operational period.
        |--------------------------------------------------------------------------
        */

        if (
            $dashboardAcademicYearId
            && $dashboardTermId
        ) {

            /*
            |--------------------------------------------------------------------------
            | CLASSES
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

            /*
            |--------------------------------------------------------------------------
            | SELECTED-PERIOD BILL SHEETS
            |--------------------------------------------------------------------------
            |
            | Exact period isolation:
            |
            | academic_year_id + term_id
            |
            | We do not filter by assignment status or is_current.
            | Historical bills therefore remain visible after rollover.
            |
            |--------------------------------------------------------------------------
            */

            $billSheets =
                BillSheet::query()
                    ->where(
                        'academic_year_id',
                        $dashboardAcademicYearId
                    )
                    ->where(
                        'term_id',
                        $dashboardTermId
                    )
                    ->where(
                        'is_active',
                        true
                    )
                    ->with(
                        'studentClassAssignment:id,student_id,student_class_id,academic_year_id,term_id,status,is_current'
                    )
                    ->orderByDesc(
                        'generated_date'
                    )
                    ->orderByDesc('id')
                    ->get([
                        'id',
                        'student_class_assignment_id',
                        'academic_year_id',
                        'term_id',
                        'total_amount',
                        'net_amount',
                        'generated_date',
                        'status',
                    ]);

            /*
            |--------------------------------------------------------------------------
            | VALID BILL SHEETS BY CLASS
            |--------------------------------------------------------------------------
            |
            | Every Bill Sheet is authoritative for its own student.
            | We do not multiply one representative bill by the number of
            | current students because that can lose historical assignments
            | and can produce incorrect totals where students have different
            | discounts/taxes/fee amounts.
            |
            |--------------------------------------------------------------------------
            */

            $classFinancialData = collect();

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
                    (int) $assignment->student_class_id;

                $billAmount =
                    $billSheet->net_amount !== null
                        ? (float) $billSheet->net_amount
                        : (float) (
                            $billSheet->total_amount ?? 0
                        );

                if ($billAmount < 0) {
                    $billAmount = 0;
                }

                if (!$classFinancialData->has($classId)) {

                    $classFinancialData->put(
                        $classId,
                        [
                            'student_ids' => collect(),
                            'expected_fees' => 0.0,
                            'bill_sheet_count' => 0,
                        ]
                    );
                }

                $row =
                    $classFinancialData->get(
                        $classId
                    );

                if ($assignment->student_id) {

                    $row['student_ids']->push(
                        $assignment->student_id
                    );
                }

                $row['expected_fees'] =
                    round(
                        $row['expected_fees']
                        + $billAmount,
                        2
                    );

                $row['bill_sheet_count']++;

                $classFinancialData->put(
                    $classId,
                    $row
                );
            }

            /*
            |--------------------------------------------------------------------------
            | CURRENT-PERIOD COMPLETED PAYMENTS
            |--------------------------------------------------------------------------
            |
            | Payments are isolated through their Bill Sheet AND assignment.
            | The selected term therefore cannot inherit payments from another
            | term.
            |
            | We intentionally do not require the assignment to be current.
            | Historical payments must remain visible when an administrator
            | views a historical term.
            |--------------------------------------------------------------------------
            */

            $dashboardPayments =
                DB::table('fee_payments as fp')
                    ->join(
                        'bill_sheets as bs',
                        'bs.id',
                        '=',
                        'fp.bill_sheet_id'
                    )
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
                        'bs.academic_year_id',
                        $dashboardAcademicYearId
                    )
                    ->where(
                        'bs.term_id',
                        $dashboardTermId
                    )
                    ->where(
                        'bs.is_active',
                        true
                    )
                    ->where(
                        'sca.academic_year_id',
                        $dashboardAcademicYearId
                    )
                    ->where(
                        'sca.term_id',
                        $dashboardTermId
                    )
                    ->select([
                        'fp.id',
                        'fp.student_id',
                        'fp.student_class_assignment_id',
                        'fp.bill_sheet_id',
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
                    (int) $payment->student_class_id;

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
                    round(
                        (
                            (float) $feesPaidByClass->get(
                                $classId,
                                0
                            )
                        ) + $paidAmount,
                        2
                    )
                );
            }

            /*
            |--------------------------------------------------------------------------
            | BUILD CLASS FINANCIAL POSITION
            |--------------------------------------------------------------------------
            */

            $classFeeData =
                $classes
                    ->map(
                        function ($class) use (
                            $classFinancialData,
                            $feesPaidByClass
                        ) {

                            $classId =
                                (int) $class->id;

                            $financial =
                                $classFinancialData->get(
                                    $classId,
                                    [
                                        'student_ids' => collect(),
                                        'expected_fees' => 0.0,
                                        'bill_sheet_count' => 0,
                                    ]
                                );

                            $studentCount =
                                $financial['student_ids']
                                    ->filter()
                                    ->unique()
                                    ->count();

                            $expectedFees =
                                round(
                                    (float) $financial['expected_fees'],
                                    2
                                );

                            $feesPaid =
                                round(
                                    (float) $feesPaidByClass->get(
                                        $classId,
                                        0
                                    ),
                                    2
                                );

                            $outstandingFees =
                                max(
                                    0,
                                    round(
                                        $expectedFees
                                        - $feesPaid,
                                        2
                                    )
                                );

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
                                    $class->name
                                    ?? 'N/A',

                                'students' =>
                                    $studentCount,

                                'bill_per_student' =>
                                    $studentCount > 0
                                        ? round(
                                            $expectedFees
                                            / $studentCount,
                                            2
                                        )
                                        : 0,

                                'expected_fees' =>
                                    $expectedFees,

                                'fees_paid' =>
                                    $feesPaid,

                                'outstanding_fees' =>
                                    $outstandingFees,

                                'collection_rate' =>
                                    $collectionRate,

                                'bill_sheet_count' =>
                                    (int) (
                                        $financial[
                                            'bill_sheet_count'
                                        ]
                                        ?? 0
                                    ),
                            ];
                        }
                    )
                    ->filter(
                        fn ($row) =>
                            $row['bill_sheet_count'] > 0
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
            | CURRENT MONTH PAID
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
            | PREVIOUS MONTH PAID
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
            | MONTHLY CHANGE
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
        | ATTENDANCE DATE
        |--------------------------------------------------------------------------
        */

        $today =
            now()->toDateString();

        $attendanceDate =
            request()->input(
                'attendance_date',
                $today
            );

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
        | CURRENT TERM DATE SAFETY
        |--------------------------------------------------------------------------
        |
        | If the selected dashboard date is outside the central term,
        | attendance displays no records rather than leaking another term.
        |
        |--------------------------------------------------------------------------
        */

        if ($currentTerm) {

            if (
                $currentTerm->start_date
                && Carbon::parse(
                    $attendanceDate
                )->lt(
                    Carbon::parse(
                        $currentTerm->start_date
                    )->startOfDay()
                )
            ) {
                $attendanceDate =
                    Carbon::parse(
                        $currentTerm->start_date
                    )->toDateString();
            }

            if (
                $currentTerm->end_date
                && Carbon::parse(
                    $attendanceDate
                )->gt(
                    Carbon::parse(
                        $currentTerm->end_date
                    )->endOfDay()
                )
            ) {
                $attendanceDate =
                    Carbon::parse(
                        $currentTerm->end_date
                    )->toDateString();
            }
        }

        /*
        |--------------------------------------------------------------------------
        | TODAY'S ATTENDANCE
        |--------------------------------------------------------------------------
        */

        $todayAttendance =
            $this->buildAttendanceSummary(
                $today,
                $dashboardAcademicYearId,
                $dashboardTermId
            );

        $todayTotal =
            $todayAttendance['total'];

        $todayPresent =
            $todayAttendance['present'];

        $todayLate =
            $todayAttendance['late'];

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
        | CLASS ATTENDANCE
        |--------------------------------------------------------------------------
        */

        $dashboardDailyAttendance =
            $this->buildClassAttendanceData(
                $attendanceDate,
                $dashboardAcademicYearId,
                $dashboardTermId
            );

        $dashboardAttendanceRows =
            $dashboardDailyAttendance->count();

        /*
        |--------------------------------------------------------------------------
        | RECENT ATTENDANCE
        |--------------------------------------------------------------------------
        |
        | Recent records are also restricted to the central academic
        | period.
        |
        |--------------------------------------------------------------------------
        */

        $recentAttendance = collect();

        try {

            if (
                $dashboardAcademicYearId
                && $dashboardTermId
                && Schema::hasTable('attendances')
                && Schema::hasTable('attendance_sessions')
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
                        ->where(
                            'attendance_sessions.academic_year_id',
                            $dashboardAcademicYearId
                        )
                        ->where(
                            'attendance_sessions.term_id',
                            $dashboardTermId
                        )
                        ->select([
                            'attendances.id',
                            'attendances.status',
                            'attendances.created_at',
                            'students.first_name',
                            'students.last_name',
                            'students.student_id as student_number',
                            'attendance_sessions.attendance_date',
                        ])
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
                    'message' =>
                        $e->getMessage(),
                ]
            );
        }

        /*
        |--------------------------------------------------------------------------
        | DASHBOARD VIEW
        |--------------------------------------------------------------------------
        */

        return view(
            'dashboard.index',
            compact(
                'settings',
                'currentAcademicYear',
                'currentTerm',
                'academicYears',
                'dashboardAcademicYearId',
                'dashboardTermId',
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


    /*
    |--------------------------------------------------------------------------
    | ATTENDANCE CHART DATA
    |--------------------------------------------------------------------------
    */

    public function getAttendanceData(
        Request $request
    ): JsonResponse {
        try {

            $period =
                $this->currentPeriod();

            if (
                !$period['academic_year_id']
                || !$period['term_id']
            ) {
                return response()->json([
                    'status' => 'success',
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
                    'academic_year' => null,
                    'term' => null,
                ]);
            }

            $days =
                max(
                    1,
                    min(
                        365,
                        (int) $request->get(
                            'days',
                            30
                        )
                    )
                );

            $end =
                now()
                    ->copy()
                    ->endOfDay();

            $start =
                now()
                    ->copy()
                    ->subDays(
                        $days - 1
                    )
                    ->startOfDay();

            /*
            |--------------------------------------------------------------------------
            | NEVER CROSS CENTRAL TERM
            |--------------------------------------------------------------------------
            */

            if (
                $period['term']->start_date
            ) {

                $termStart =
                    Carbon::parse(
                        $period['term']->start_date
                    )->startOfDay();

                if ($start->lt($termStart)) {
                    $start = $termStart;
                }
            }

            if (
                $period['term']->end_date
            ) {

                $termEnd =
                    Carbon::parse(
                        $period['term']->end_date
                    )->endOfDay();

                if ($end->gt($termEnd)) {
                    $end = $termEnd;
                }
            }

            $rows =
                DB::table(
                    'attendance_sessions'
                )
                    ->leftJoin(
                        'attendances',
                        'attendance_sessions.id',
                        '=',
                        'attendances.attendance_session_id'
                    )
                    ->where(
                        'attendance_sessions.academic_year_id',
                        $period['academic_year_id']
                    )
                    ->where(
                        'attendance_sessions.term_id',
                        $period['term_id']
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
                    ->select([
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
                        ),
                    ])
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
                    'total' =>
                        (int) $row->total_records,

                    'attended' =>
                        (int) $row->attended_count,
                ];
            }

            $labels = [];

            $attendance = [];

            $marked = [];

            $cursor =
                $start->copy();

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

            return response()->json([
                'status' => 'success',
                'labels' => $labels,
                'attendance' => $attendance,
                'benchmark' =>
                    array_fill(
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
                'days_with_data' =>
                    $daysWithData,
                'academic_year' =>
                    $period['academic_year']->name,
                'term' =>
                    $period['term']->name,
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


    /*
    |--------------------------------------------------------------------------
    | DAILY ATTENDANCE SUMMARY
    |--------------------------------------------------------------------------
    */

    public function getAttendanceSummary(
        Request $request
    ): JsonResponse {
        try {

            $period =
                $this->currentPeriod();

            $date =
                $request->get(
                    'date',
                    now()->toDateString()
                );

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
                    $date,
                    $period['academic_year_id'],
                    $period['term_id']
                );

            return response()->json([
                'success' => true,
                'date' => $date,
                'summary' => $summary,
                'academic_year' =>
                    $period['academic_year']?->name,
                'term' =>
                    $period['term']?->name,
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


    /*
    |--------------------------------------------------------------------------
    | DAILY CLASS ATTENDANCE
    |--------------------------------------------------------------------------
    */

    public function getClassAttendance(
        Request $request
    ): JsonResponse {
        try {

            $period =
                $this->currentPeriod();

            $date =
                $request->get(
                    'date',
                    now()->toDateString()
                );

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
                    $date,
                    $period['academic_year_id'],
                    $period['term_id']
                );

            return response()->json([
                'success' => true,
                'date' => $date,
                'classes' =>
                    $rows->values(),
                'total_classes' =>
                    $rows->count(),
                'academic_year' =>
                    $period['academic_year']?->name,
                'term' =>
                    $period['term']?->name,
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


    /*
    |--------------------------------------------------------------------------
    | CENTRAL PERIOD HELPER
    |--------------------------------------------------------------------------
    */

    private function currentPeriod(): array
    {
        $settings =
            SystemSetting::instance()->load([
                'currentAcademicYear',
                'currentTerm',
            ]);

        return [
            'settings' =>
                $settings,

            'academic_year' =>
                $settings->currentAcademicYear,

            'term' =>
                $settings->currentTerm,

            'academic_year_id' =>
                $settings->current_academic_year_id,

            'term_id' =>
                $settings->current_term_id,
        ];
    }


    /*
    |--------------------------------------------------------------------------
    | ATTENDANCE SUMMARY BUILDER
    |--------------------------------------------------------------------------
    */

    private function buildAttendanceSummary(
        string $date,
        ?int $academicYearId,
        ?int $termId
    ): array {
        if (
            !$academicYearId
            || !$termId
        ) {
            return [
                'total' => 0,
                'present' => 0,
                'absent' => 0,
                'late' => 0,
                'excused' => 0,
            ];
        }

        try {

            $row =
                DB::table(
                    'attendances'
                )
                    ->join(
                        'attendance_sessions',
                        'attendances.attendance_session_id',
                        '=',
                        'attendance_sessions.id'
                    )
                    ->where(
                        'attendance_sessions.academic_year_id',
                        $academicYearId
                    )
                    ->where(
                        'attendance_sessions.term_id',
                        $termId
                    )
                    ->whereDate(
                        'attendance_sessions.attendance_date',
                        $date
                    )
                    ->select([
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
                        ),
                    ])
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
                'Dashboard attendance summary build error.',
                [
                    'date' =>
                        $date,

                    'academic_year_id' =>
                        $academicYearId,

                    'term_id' =>
                        $termId,

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


    /*
    |--------------------------------------------------------------------------
    | CLASS ATTENDANCE BUILDER
    |--------------------------------------------------------------------------
    */

    private function buildClassAttendanceData(
        string $date,
        ?int $academicYearId,
        ?int $termId
    ) {
        if (
            !$academicYearId
            || !$termId
        ) {
            return collect();
        }

        /*
        |--------------------------------------------------------------------------
        | ACTIVE CLASSES
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

        if ($classes->isEmpty()) {
            return collect();
        }

        $classIds =
            $classes->pluck('id');

        /*
        |--------------------------------------------------------------------------
        | CURRENT PERIOD STUDENT COUNTS
        |--------------------------------------------------------------------------
        */

        $studentCounts =
            StudentClassAssignment::query()
                ->whereIn(
                    'student_class_id',
                    $classIds
                )
                ->where(
                    'academic_year_id',
                    $academicYearId
                )
                ->where(
                    'term_id',
                    $termId
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
        | CURRENT PERIOD ATTENDANCE SESSIONS
        |--------------------------------------------------------------------------
        */

        $sessions =
            AttendanceSession::query()
                ->whereIn(
                    'student_class_id',
                    $classIds
                )
                ->where(
                    'academic_year_id',
                    $academicYearId
                )
                ->where(
                    'term_id',
                    $termId
                )
                ->whereDate(
                    'attendance_date',
                    $date
                )
                ->orderByDesc('id')
                ->get([
                    'id',
                    'student_class_id',
                    'academic_year_id',
                    'term_id',
                    'attendance_date',
                ])
                ->groupBy(
                    'student_class_id'
                )
                ->map(
                    fn ($items) =>
                        $items->first()
                );

        $sessionIds =
            $sessions
                ->pluck('id')
                ->filter()
                ->values();

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

        $recordsBySession =
            $records->groupBy(
                'attendance_session_id'
            );

        /*
        |--------------------------------------------------------------------------
        | BUILD CLASS ROWS
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

                    $marked =
                        $present
                        + $absent
                        + $late;

                    $attended =
                        $present
                        + $late;

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

                    if (!$session) {

                        $status = 'Not Taken';

                        $statusClass = 'secondary';

                    } elseif (
                        $marked === 0
                    ) {

                        $status = 'No Records';

                        $statusClass = 'warning';

                    } elseif (
                        $rate >= 80
                    ) {

                        $status = 'Excellent';

                        $statusClass = 'success';

                    } elseif (
                        $rate >= 60
                    ) {

                        $status = 'Good';

                        $statusClass = 'info';

                    } elseif (
                        $rate >= 40
                    ) {

                        $status = 'Average';

                        $statusClass = 'warning';

                    } else {

                        $status = 'Poor';

                        $statusClass = 'danger';
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