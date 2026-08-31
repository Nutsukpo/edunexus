<?php

namespace App\Http\Controllers;

use App\Models\AcademicYear;
use App\Models\BillSheet;
use App\Models\StudentClassAssignment;
use App\Models\FeePayment;
use App\Models\Student;
use App\Models\StudentClass;
use App\Models\StudentFeeAccount;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class FeePaymentReportController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | INDEX
    |--------------------------------------------------------------------------
    */

    public function index(Request $request)
    {
        $filters = $this->getFilterParameters($request);
        $filterOptions = $this->getFilterOptions($filters);

        $query = $this->buildReportQuery($filters);

        $summary = $this->calculateSummary(clone $query);

        $reportData = $this->getReportData(
            clone $query,
            $filters['report_type']
        );

        $paymentSummary = $this->getPaymentSummary($filters);

        return view('fee-payments.reports.index', array_merge(
            $filters,
            $filterOptions,
            [
                'reports' => $reportData,
                'summary' => $summary,
                'paymentSummary' => $paymentSummary,
            ]
        ));
    }

    /*
    |--------------------------------------------------------------------------
    | SHOW
    |--------------------------------------------------------------------------
    */

    public function show($id)
    {
        $feeAccount = StudentFeeAccount::with([
            'student',
            'studentClass',
            'academicYear',
            'payments' => fn ($query) => $query->latest('payment_date'),
            'feeItems',
        ])->findOrFail($id);

        $paymentHistory = $feeAccount->payments;
        $totalPaid = $paymentHistory->where('status', 'completed')->sum(
            fn ($payment) => (float) ($payment->net_amount ?? $payment->amount ?? 0)
        );

        $balance = (float) ($feeAccount->balance ?? max(
            0,
            (float) ($feeAccount->total_fees ?? 0) - $totalPaid
        ));

        return view('fee-payments.reports.show', compact(
            'feeAccount',
            'paymentHistory',
            'totalPaid',
            'balance'
        ));
    }

    /*
    |--------------------------------------------------------------------------
    | EXPORT PDF
    |--------------------------------------------------------------------------
    */

    public function exportPdf(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'academic_year_id' => 'nullable|exists:academic_years,id',
            'class_id' => 'nullable|exists:student_classes,id',
            'student_id' => 'nullable|exists:students,id',
            'status' => 'nullable|in:paid,partial,pending',
            'payment_method' => 'nullable|string|max:100',
            'report_type' => 'nullable|in:summary,detailed,student,class,payment_summary',
            'date_from' => 'nullable|date',
            'date_to' => 'nullable|date|after_or_equal:date_from',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $filters = $this->getFilterParameters($request);
        $query = $this->buildReportQuery($filters);

        $data = $this->getExportData($filters);

        $summary = $this->calculateSummary(clone $query);

        $pdf = Pdf::loadView('fee-payments.reports.pdf', [
            'data' => $data,
            'filters' => $filters,
            'summary' => $summary,
            'generated_at' => now(),
            'generated_by' => auth()->user()->name ?? 'System',
        ])->setPaper('a4', 'landscape')
          ->setOptions([
              'defaultFont' => 'DejaVu Sans',
              'isHtml5ParserEnabled' => true,
              'isRemoteEnabled' => true,
          ]);

        return $pdf->download(
            'fee-payment-report-' . now()->format('Y-m-d-H-i-s') . '.pdf'
        );
    }

    /*
    |--------------------------------------------------------------------------
    | EXPORT EXCEL / CSV
    |--------------------------------------------------------------------------
    */

    public function exportExcel(Request $request)
    {
        $filters = $this->getFilterParameters($request);

        return $this->exportCsv(
            $this->getExportData($filters),
            $filters
        );
    }

    private function exportCsv($data, array $filters)
    {
        $filename = 'fee-payment-report-' . now()->format('Y-m-d') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        return response()->stream(function () use ($data) {
            $handle = fopen('php://output', 'w');

            fputcsv($handle, [
                'Student',
                'Student ID',
                'Class',
                'Academic Year',
                'Total Fees',
                'Amount Paid',
                'Balance',
                'Discount',
                'Waiver',
                'Status',
            ]);

            foreach ($data as $row) {
                $student = $row->student;

                fputcsv($handle, [
                    $this->studentName($student),
                    $student->student_id ?? 'N/A',
                    $this->className($row->studentClass),
                    $this->academicYearName($row->academicYear),
                    number_format((float) ($row->total_fees ?? 0), 2),
                    number_format((float) ($row->amount_paid ?? 0), 2),
                    number_format((float) ($row->balance ?? 0), 2),
                    number_format((float) ($row->discount_applied ?? 0), 2),
                    number_format((float) ($row->waiver_amount ?? 0), 2),
                    ucfirst((string) ($row->status ?? 'pending')),
                ]);
            }

            fclose($handle);
        }, 200, $headers);
    }

    /*
    |--------------------------------------------------------------------------
    | PAYMENT HISTORY
    |--------------------------------------------------------------------------
    */

    public function paymentHistory(Request $request)
    {
        $query = FeePayment::with([
            'student',
            'studentFeeAccount.studentClass',
            'studentFeeAccount.academicYear',
        ]);

        $query = $this->applyPaymentFilters($query, $request);

        $payments = $query
            ->latest('payment_date')
            ->paginate(50)
            ->withQueryString();

        $students = Student::query()
            ->orderBy('first_name')
            ->orderBy('last_name')
            ->get()
            ->mapWithKeys(fn ($student) => [
                $student->id => $this->studentName($student),
            ]);

        $paymentMethods = FeePayment::query()
            ->whereNotNull('payment_method')
            ->where('payment_method', '!=', '')
            ->distinct()
            ->orderBy('payment_method')
            ->pluck('payment_method');

        $academicYears = AcademicYear::query()
            ->orderByDesc('id')
            ->get()
            ->mapWithKeys(fn ($year) => [
                $year->id => $this->academicYearName($year),
            ]);

        $summaryQuery = clone $query;

        $summary = [
            'total_payments' => (clone $summaryQuery)->count(),
            'total_amount' => (clone $summaryQuery)->sum(
                DB::raw('COALESCE(net_amount, amount, 0)')
            ),
            'total_students' => (clone $summaryQuery)
                ->distinct('student_id')
                ->count('student_id'),
            'by_method' => (clone $summaryQuery)
                ->select(
                    'payment_method',
                    DB::raw('COUNT(*) as count'),
                    DB::raw('SUM(COALESCE(net_amount, amount, 0)) as total')
                )
                ->groupBy('payment_method')
                ->orderByDesc('total')
                ->get(),
        ];

        return view('fee-payments.reports.payment-history', compact(
            'payments',
            'students',
            'paymentMethods',
            'academicYears',
            'summary',
            'request'
        ));
    }

    /*
    |--------------------------------------------------------------------------
    | AJAX SUMMARY / CHARTS
    |--------------------------------------------------------------------------
    */

    public function getSummaryStats(Request $request)
    {
        $filters = $this->getFilterParameters($request);

        $summary = $this->calculateSummary(
            $this->buildReportQuery($filters)
        );

        return response()->json([
            'success' => true,
            'data' => $summary,
        ]);
    }

    public function getChartData(Request $request)
    {
        $filters = $this->getFilterParameters($request);

        $paymentQuery = FeePayment::query()
            ->where('status', 'completed')
            ->when($filters['date_from'], fn ($q, $date) =>
                $q->whereDate('payment_date', '>=', $date)
            )
            ->when($filters['date_to'], fn ($q, $date) =>
                $q->whereDate('payment_date', '<=', $date)
            )
            ->when($filters['payment_method'], fn ($q, $method) =>
                $q->where('payment_method', $method)
            )
            ->when($filters['student_id'], fn ($q, $studentId) =>
                $q->where('student_id', $studentId)
            )
            ->when($filters['academic_year_id'], function ($q) use ($filters) {
                $q->whereHas('studentFeeAccount', fn ($account) =>
                    $account->where('academic_year_id', $filters['academic_year_id'])
                );
            })
            ->when($filters['class_id'], function ($q) use ($filters) {
                $q->whereHas('studentFeeAccount', fn ($account) =>
                    $account->where('student_class_id', $filters['class_id'])
                );
            });

        $trends = (clone $paymentQuery)
            ->select(
                DB::raw('DATE(payment_date) as date'),
                DB::raw('COUNT(*) as count'),
                DB::raw('SUM(COALESCE(net_amount, amount, 0)) as total')
            )
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        $statusDistribution = StudentFeeAccount::query()
            ->when($filters['academic_year_id'], fn ($q, $id) =>
                $q->where('academic_year_id', $id)
            )
            ->when($filters['class_id'], fn ($q, $id) =>
                $q->where('student_class_id', $id)
            )
            ->when($filters['student_id'], fn ($q, $id) =>
                $q->where('student_id', $id)
            )
            ->select('status', DB::raw('COUNT(*) as count'))
            ->groupBy('status')
            ->get();

        $classPerformance = StudentFeeAccount::with('studentClass')
            ->when($filters['academic_year_id'], fn ($q, $id) =>
                $q->where('academic_year_id', $id)
            )
            ->select(
                'student_class_id',
                DB::raw('COUNT(*) as total_students'),
                DB::raw('SUM(total_fees) as total_fees'),
                DB::raw('SUM(amount_paid) as total_paid'),
                DB::raw('SUM(balance) as total_balance')
            )
            ->groupBy('student_class_id')
            ->get()
            ->map(function ($item) {
                $item->class_name = $this->className($item->studentClass);
                $item->collection_rate = (float) ($item->total_fees ?? 0) > 0
                    ? round(($item->total_paid / $item->total_fees) * 100, 2)
                    : 0;

                return $item;
            });

        return response()->json([
            'success' => true,
            'data' => [
                'trends' => $trends,
                'status_distribution' => $statusDistribution,
                'class_performance' => $classPerformance,
            ],
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | OUTSTANDING FEES
    |--------------------------------------------------------------------------
    */

    public function outstandingFees(Request $request)
    {
        $query = StudentFeeAccount::with([
            'student',
            'studentClass',
            'academicYear',
        ])
        ->where('balance', '>', 0)
        ->where('status', '!=', 'paid')
        ->when($request->get('academic_year_id'), fn ($q, $id) =>
            $q->where('academic_year_id', $id)
        )
        ->when($request->get('class_id'), fn ($q, $id) =>
            $q->where('student_class_id', $id)
        )
        ->orderByDesc('balance');

        $totalOutstanding = (clone $query)->sum('balance');
        $totalStudents = (clone $query)->distinct('student_id')->count('student_id');

        $outstandingFees = $query->paginate(50)->withQueryString();

        $academicYears = AcademicYear::query()
            ->orderByDesc('id')
            ->get()
            ->mapWithKeys(fn ($year) => [
                $year->id => $this->academicYearName($year),
            ]);

        $classes = StudentClass::query()
            ->where('is_active', true)
            ->orderBy('name')
            ->get()
            ->mapWithKeys(fn ($class) => [
                $class->id => $this->className($class),
            ]);

        return view('fee-payments.reports.outstanding', compact(
            'outstandingFees',
            'totalOutstanding',
            'totalStudents',
            'academicYears',
            'classes'
        ));
    }

    /*
    |--------------------------------------------------------------------------
    | FILTERS
    |--------------------------------------------------------------------------
    */

    private function getFilterParameters(Request $request): array
    {
        return [
            'academic_year_id' => $request->get('academic_year_id'),
            'class_id' => $request->get('class_id'),
            'status' => $request->get('status'),
            'date_from' => $request->get('date_from'),
            'date_to' => $request->get('date_to'),
            'student_id' => $request->get('student_id'),
            'report_type' => $request->get('report_type', 'summary'),
            'payment_method' => $request->get('payment_method'),
        ];
    }

    private function getFilterOptions(array $filters): array
    {
        $academicYears = AcademicYear::query()
            ->orderByDesc('id')
            ->get()
            ->mapWithKeys(fn ($year) => [
                $year->id => $this->academicYearName($year),
            ]);

        $classes = StudentClass::query()
            ->where('is_active', true)
            ->orderBy('name')
            ->get()
            ->mapWithKeys(fn ($class) => [
                $class->id => $this->className($class),
            ]);

        $studentsQuery = Student::query()
            ->orderBy('first_name')
            ->orderBy('last_name');

        if (!empty($filters['academic_year_id']) || !empty($filters['class_id'])) {
            $studentsQuery->whereHas('classAssignments', function ($q) use ($filters) {
                $q->where('status', 'active')
                    ->when(
                        !empty($filters['academic_year_id']),
                        fn ($query) => $query->where(
                            'academic_year_id',
                            $filters['academic_year_id']
                        )
                    )
                    ->when(
                        !empty($filters['class_id']),
                        fn ($query) => $query->where(
                            'student_class_id',
                            $filters['class_id']
                        )
                    );
            });
        }

        $students = $studentsQuery
            ->get()
            ->mapWithKeys(fn ($student) => [
                $student->id => $this->studentName($student),
            ]);

        $paymentMethods = FeePayment::query()
            ->whereNotNull('payment_method')
            ->where('payment_method', '!=', '')
            ->distinct()
            ->orderBy('payment_method')
            ->pluck('payment_method');

        return [
            'academicYears' => $academicYears,
            'classes' => $classes,
            'students' => $students,
            'paymentMethods' => $paymentMethods,
            'statusOptions' => [
                'paid' => 'Paid',
                'partial' => 'Partial',
                'pending' => 'Pending',
            ],
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | REPORT QUERY
    |--------------------------------------------------------------------------
    */

    private function buildReportQuery(array $filters): Builder
    {
        $query = StudentFeeAccount::with([
            'student',
            'studentClass',
            'academicYear',
            'payments',
        ]);

        $query
            ->when($filters['academic_year_id'], fn ($q, $id) =>
                $q->where('academic_year_id', $id)
            )
            ->when($filters['class_id'], fn ($q, $id) =>
                $q->where('student_class_id', $id)
            )
            ->when($filters['student_id'], fn ($q, $id) =>
                $q->where('student_id', $id)
            )
            ->when($filters['status'], fn ($q, $status) =>
                $q->where('status', $status)
            );

        /*
         * Date filters are payment-date filters. Accounts are included when
         * they have at least one completed payment inside the selected range.
         */
        if (!empty($filters['date_from']) || !empty($filters['date_to'])) {
            $query->whereHas('payments', function ($paymentQuery) use ($filters) {
                $paymentQuery->where('status', 'completed')
                    ->when(
                        $filters['date_from'],
                        fn ($q, $date) => $q->whereDate('payment_date', '>=', $date)
                    )
                    ->when(
                        $filters['date_to'],
                        fn ($q, $date) => $q->whereDate('payment_date', '<=', $date)
                    );
            });
        }

        if (!empty($filters['payment_method'])) {
            $query->whereHas('payments', function ($paymentQuery) use ($filters) {
                $paymentQuery
                    ->where('status', 'completed')
                    ->where('payment_method', $filters['payment_method']);
            });
        }

        return $query->latest('created_at');
    }

    /*
    |--------------------------------------------------------------------------
    | REPORT DATA
    |--------------------------------------------------------------------------
    */

    private function getReportData(Builder $query, string $reportType)
    {
        return match ($reportType) {
            'detailed' => $query
                ->with(['payments' => fn ($q) => $q->latest('payment_date')])
                ->paginate(50)
                ->withQueryString(),

            'student' => $query
                ->get()
                ->groupBy('student_id')
                ->map(function ($items) {
                    return (object) [
                        'student' => $items->first()->student,
                        'accounts' => $items,
                        'total_fees' => $items->sum('total_fees'),
                        'amount_paid' => $items->sum('amount_paid'),
                        'balance' => $items->sum('balance'),
                        'discount_applied' => $items->sum('discount_applied'),
                        'waiver_amount' => $items->sum('waiver_amount'),
                        'status' => $this->derivedStatus(
                            $items->sum('amount_paid'),
                            $items->sum('balance')
                        ),
                    ];
                })
                ->sortByDesc('amount_paid')
                ->values(),

            'class' => $query
                ->get()
                ->groupBy('student_class_id')
                ->map(function ($items) {
                    $fees = (float) $items->sum('total_fees');
                    $paid = (float) $items->sum('amount_paid');

                    return (object) [
                        'class' => $items->first()->studentClass,
                        'academicYear' => $items->first()->academicYear,
                        'total_students' => $items->count(),
                        'total_fees' => $fees,
                        'amount_paid' => $paid,
                        'balance' => $items->sum('balance'),
                        'collection_rate' => $fees > 0
                            ? round(($paid / $fees) * 100, 2)
                            : 0,
                    ];
                })
                ->sortByDesc('collection_rate')
                ->values(),

            'payment_summary' => $this->getPaymentSummaryByPeriod($query),

            default => $query
                ->paginate(50)
                ->withQueryString(),
        };
    }

    private function getPaymentSummaryByPeriod(Builder $query)
    {
        return $query
            ->select(
                DB::raw('DATE(created_at) as date'),
                DB::raw('COUNT(*) as count'),
                DB::raw('SUM(total_fees) as total_fees'),
                DB::raw('SUM(amount_paid) as total_paid'),
                DB::raw('SUM(balance) as total_balance')
            )
            ->groupBy(DB::raw('DATE(created_at)'))
            ->orderByDesc('date')
            ->get();
    }

    /*
    |--------------------------------------------------------------------------
    | SUMMARY
    |--------------------------------------------------------------------------
    */

    private function calculateSummary(Builder $query): array
    {
        $totalFees = (float) $query->sum('total_fees');
        $totalPaid = (float) $query->sum('amount_paid');

        return [
            'total_students' => (clone $query)->count(),
            'total_fees' => $totalFees,
            'total_paid' => $totalPaid,
            'total_balance' => (float) $query->sum('balance'),
            'total_discount' => (float) $query->sum('discount_applied'),
            'total_waiver' => (float) $query->sum('waiver_amount'),
            'collection_rate' => $totalFees > 0
                ? round(($totalPaid / $totalFees) * 100, 2)
                : 0,
            'paid_count' => (clone $query)->where('status', 'paid')->count(),
            'partial_count' => (clone $query)->where('status', 'partial')->count(),
            'pending_count' => (clone $query)->where('status', 'pending')->count(),
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | PAYMENT SUMMARY
    |--------------------------------------------------------------------------
    */

    private function getPaymentSummary(array $filters): array
    {
        $query = FeePayment::with([
            'student',
            'studentFeeAccount.studentClass',
            'studentFeeAccount.academicYear',
        ])
        ->where('status', 'completed');

        $query
            ->when($filters['academic_year_id'], function ($q, $id) {
                $q->whereHas(
                    'studentFeeAccount',
                    fn ($account) => $account->where('academic_year_id', $id)
                );
            })
            ->when($filters['class_id'], function ($q, $id) {
                $q->whereHas(
                    'studentFeeAccount',
                    fn ($account) => $account->where('student_class_id', $id)
                );
            })
            ->when($filters['student_id'], fn ($q, $id) =>
                $q->where('student_id', $id)
            )
            ->when($filters['payment_method'], fn ($q, $method) =>
                $q->where('payment_method', $method)
            )
            ->when($filters['date_from'], fn ($q, $date) =>
                $q->whereDate('payment_date', '>=', $date)
            )
            ->when($filters['date_to'], fn ($q, $date) =>
                $q->whereDate('payment_date', '<=', $date)
            );

        $totalPayments = (clone $query)->count();
        $totalAmount = (float) (clone $query)->sum(
            DB::raw('COALESCE(net_amount, amount, 0)')
        );

        return [
            'total_payments' => $totalPayments,
            'total_amount' => $totalAmount,

            'by_method' => (clone $query)
                ->select(
                    'payment_method',
                    DB::raw('COUNT(*) as count'),
                    DB::raw('SUM(COALESCE(net_amount, amount, 0)) as total')
                )
                ->groupBy('payment_method')
                ->orderByDesc('total')
                ->get(),

            'daily_summary' => (clone $query)
                ->select(
                    DB::raw('DATE(payment_date) as date'),
                    DB::raw('COUNT(*) as count'),
                    DB::raw('SUM(COALESCE(net_amount, amount, 0)) as total')
                )
                ->groupBy(DB::raw('DATE(payment_date)'))
                ->orderByDesc('date')
                ->limit(30)
                ->get(),
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | PAYMENT FILTERS
    |--------------------------------------------------------------------------
    */

    private function applyPaymentFilters($query, Request $request)
    {
        return $query
            ->when(
                $request->get('student_id'),
                fn ($q, $id) => $q->where('student_id', $id)
            )
            ->when(
                $request->get('academic_year_id'),
                fn ($q, $id) => $q->whereHas(
                    'studentFeeAccount',
                    fn ($account) => $account->where('academic_year_id', $id)
                )
            )
            ->when(
                $request->get('class_id'),
                fn ($q, $id) => $q->whereHas(
                    'studentFeeAccount',
                    fn ($account) => $account->where('student_class_id', $id)
                )
            )
            ->when(
                $request->get('date_from'),
                fn ($q, $date) => $q->whereDate('payment_date', '>=', $date)
            )
            ->when(
                $request->get('date_to'),
                fn ($q, $date) => $q->whereDate('payment_date', '<=', $date)
            )
            ->when(
                $request->get('payment_method'),
                fn ($q, $method) => $q->where('payment_method', $method)
            )
            ->when(
                $request->get('status'),
                fn ($q, $status) => $q->where('status', $status)
            );
    }

    /*
    |--------------------------------------------------------------------------
    | EXPORT DATA
    |--------------------------------------------------------------------------
    */

    private function getExportData(array $filters)
    {
        return $this->buildReportQuery($filters)->get();
    }

    /*
    |--------------------------------------------------------------------------
    | RECEIPT
    |--------------------------------------------------------------------------
    */

    public function generateReceipt($paymentId)
    {
        $payment = FeePayment::with([
            'student',
            'studentFeeAccount.studentClass',
            'studentFeeAccount.academicYear',
        ])->findOrFail($paymentId);

        $pdf = Pdf::loadView('fee-payments.receipt', [
            'payment' => $payment,
            'generated_at' => now(),
        ])->setPaper('a4', 'portrait');

        return $pdf->download(
            'receipt-' . ($payment->receipt_number ?? $payment->id) . '.pdf'
        );
    }

    /*
    |--------------------------------------------------------------------------
    | HELPERS
    |--------------------------------------------------------------------------
    */

    private function derivedStatus(float $paid, float $balance): string
    {
        if ($balance <= 0) {
            return 'paid';
        }

        if ($paid > 0) {
            return 'partial';
        }

        return 'pending';
    }

    private function studentName($student): string
    {
        if (!$student) {
            return 'N/A';
        }

        return trim(collect([
            $student->first_name ?? null,
            $student->middle_name ?? null,
            $student->last_name ?? null,
        ])->filter()->implode(' ')) ?: 'N/A';
    }

    private function className($class): string
    {
        if (!$class) {
            return 'N/A';
        }

        return $class->name
            ?? $class->class_name
            ?? 'N/A';
    }

    private function academicYearName($academicYear): string
    {
        if (!$academicYear) {
            return 'N/A';
        }

        return $academicYear->name
            ?? $academicYear->year_name
            ?? 'N/A';
    }
    
    /*
    |--------------------------------------------------------------------------
    | SCHOOL-WIDE FEE OVERVIEW
    |--------------------------------------------------------------------------
    |
    | EXPECTED FEES RULE
    |--------------------------------------------------------------------------
    |
    | Expected fees are NOT taken by summing student_fee_accounts.
    |
    | For each class:
    |
    |     Expected Fees =
    |         Class Bill Sheet Amount
    |         × Number of Active Students in the Class
    |
    | The class bill sheet amount is taken from an approved/published
    | student Bill Sheet generated for that class and academic year.
    |
    | Paid fees come from completed FeePayment records for the same
    | class + academic year.
    |
    | Outstanding fees =
    |     Expected Fees - Fees Paid
    |
    |--------------------------------------------------------------------------
    */
    public function schoolOverview(Request $request)
    {
        /*
        |--------------------------------------------------------------------------
        | SCHOOL-WIDE FEE OVERVIEW
        |--------------------------------------------------------------------------
        |
        | Expected Fees = Class Bill Sheet Amount × Active Student Count
        |
        | The BillSheetController creates individual Bill Sheets from the same
        | class fee template for each active/current StudentClassAssignment.
        |
        | For this management report we deliberately calculate the expected
        | amount from:
        |
        |     1. one approved/published bill amount for the class/year
        |     2. multiplied by the current active student count
        |
        | Paid fees come from completed FeePayment records.
        |--------------------------------------------------------------------------
        */

        $latestAcademicYear = AcademicYear::query()
            ->orderByDesc('id')
            ->first();

        $academicYearId = $request->get('academic_year_id');

        if ($academicYearId === null && $latestAcademicYear) {
            $academicYearId = $latestAcademicYear->id;
        }

        /*
        |--------------------------------------------------------------------------
        | ACTIVE CLASSES
        |--------------------------------------------------------------------------
        */

        $classes = StudentClass::query()
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        /*
        |--------------------------------------------------------------------------
        | ACTIVE / CURRENT STUDENTS
        |--------------------------------------------------------------------------
        */

        $assignments = StudentClassAssignment::query()
            ->when(
                $academicYearId && $academicYearId !== 'all',
                fn ($query) => $query->where(
                    'academic_year_id',
                    $academicYearId
                )
            )
            ->where('status', 'active')
            ->where('is_current', true)
            ->get([
                'id',
                'student_id',
                'student_class_id',
                'academic_year_id',
            ]);

        $studentCountsByClass = $assignments
            ->groupBy('student_class_id')
            ->map(fn ($items) => $items->count());

        /*
        |--------------------------------------------------------------------------
        | APPROVED / PUBLISHED BILL SHEETS
        |--------------------------------------------------------------------------
        |
        | Use the student Bill Sheet itself because your system generates one
        | Bill Sheet for each StudentClassAssignment. We only need one valid
        | Bill Sheet amount per class/year to obtain the standard class charge.
        |--------------------------------------------------------------------------
        */

        $billSheets = BillSheet::query()
            ->where('is_active', true)
            ->whereIn('status', ['approved', 'published'])
            ->when(
                $academicYearId && $academicYearId !== 'all',
                fn ($query) => $query->where(
                    'academic_year_id',
                    $academicYearId
                )
            )
            ->with('studentClassAssignment')
            ->orderByDesc('id')
            ->get([
                'id',
                'student_class_assignment_id',
                'academic_year_id',
                'total_amount',
                'net_amount',
            ]);

        /*
        |--------------------------------------------------------------------------
        | REPRESENTATIVE BILL AMOUNT BY CLASS
        |--------------------------------------------------------------------------
        |
        | Since batch generation copies the same fee template to each student,
        | the most recent approved/published student bill for the class is used.
        |
        | Key:
        |     student_class_id
        |
        | Value:
        |     bill amount per student
        |--------------------------------------------------------------------------
        */

        $billAmountByClass = collect();

        foreach ($billSheets as $billSheet) {
            $assignment = $billSheet->studentClassAssignment;

            if (!$assignment || !$assignment->student_class_id) {
                continue;
            }

            $classId = $assignment->student_class_id;

            if ($billAmountByClass->has($classId)) {
                continue;
            }

            $amount = (float) (
                $billSheet->net_amount
                ?? $billSheet->total_amount
                ?? 0
            );

            $billAmountByClass->put($classId, $amount);
        }

        /*
        |--------------------------------------------------------------------------
        | COMPLETED PAYMENTS
        |--------------------------------------------------------------------------
        */

        $paymentsQuery = FeePayment::query()
            ->where('status', 'completed')
            ->when(
                $academicYearId && $academicYearId !== 'all',
                function ($query) use ($academicYearId) {
                    $query->whereHas(
                        'studentClassAssignment',
                        fn ($assignment) => $assignment->where(
                            'academic_year_id',
                            $academicYearId
                        )
                    );
                }
            );

        $payments = $paymentsQuery->get([
            'id',
            'student_id',
            'student_class_assignment_id',
            'net_amount',
            'amount',
        ]);

        /*
        |--------------------------------------------------------------------------
        | MAP ASSIGNMENTS TO CLASS
        |--------------------------------------------------------------------------
        */

        $classIdByAssignmentId = $assignments
            ->mapWithKeys(function ($assignment) {
                return [
                    $assignment->id => $assignment->student_class_id,
                ];
            });

        /*
        |--------------------------------------------------------------------------
        | PAID AMOUNT BY CLASS
        |--------------------------------------------------------------------------
        */

        $feesPaidByClass = collect();

        foreach ($payments as $payment) {
            $classId = $classIdByAssignmentId->get(
                $payment->student_class_assignment_id
            );

            if (!$classId) {
                continue;
            }

            $paidAmount = (float) (
                $payment->net_amount
                ?? $payment->amount
                ?? 0
            );

            $feesPaidByClass->put(
                $classId,
                (float) $feesPaidByClass->get($classId, 0) + $paidAmount
            );
        }

        /*
        |--------------------------------------------------------------------------
        | BUILD CLASS REPORT
        |--------------------------------------------------------------------------
        */

        $classReports = $classes
            ->map(function ($class) use (
                $studentCountsByClass,
                $billAmountByClass,
                $feesPaidByClass
            ) {
                $classId = $class->id;

                $studentCount = (int) $studentCountsByClass->get(
                    $classId,
                    0
                );

                $billAmountPerStudent = (float) $billAmountByClass->get(
                    $classId,
                    0
                );

                /*
                | USER-REQUESTED FORMULA
                */
                $expectedFees = round(
                    $billAmountPerStudent * $studentCount,
                    2
                );

                $feesPaid = round(
                    (float) $feesPaidByClass->get($classId, 0),
                    2
                );

                $outstandingFees = max(
                    0,
                    round(
                        $expectedFees - $feesPaid,
                        2
                    )
                );

                $collectionRate = $expectedFees > 0
                    ? min(
                        100,
                        round(
                            ($feesPaid / $expectedFees) * 100,
                            2
                        )
                    )
                    : 0;

                return (object) [
                    'class_id' => $classId,
                    'class_name' => $class->name ?? 'N/A',
                    'student_accounts' => $studentCount,
                    'bill_amount_per_student' => $billAmountPerStudent,
                    'expected_fees' => $expectedFees,
                    'fees_paid' => $feesPaid,
                    'outstanding_fees' => $outstandingFees,
                    'collection_rate' => $collectionRate,
                ];
            })
            ->sortByDesc('expected_fees')
            ->values();

        /*
        |--------------------------------------------------------------------------
        | WHOLE-SCHOOL TOTALS
        |--------------------------------------------------------------------------
        */

        $totalExpectedFees = round(
            (float) $classReports->sum('expected_fees'),
            2
        );

        $totalFeesPaid = round(
            (float) $classReports->sum('fees_paid'),
            2
        );

        $totalOutstandingFees = round(
            (float) $classReports->sum('outstanding_fees'),
            2
        );

        $schoolCollectionRate = $totalExpectedFees > 0
            ? min(
                100,
                round(
                    ($totalFeesPaid / $totalExpectedFees) * 100,
                    2
                )
            )
            : 0;

        /*
        |--------------------------------------------------------------------------
        | ACADEMIC YEARS
        |--------------------------------------------------------------------------
        */

        $academicYears = AcademicYear::query()
            ->orderByDesc('id')
            ->get()
            ->mapWithKeys(function ($year) {
                return [
                    $year->id =>
                        $year->name
                        ?? $year->year_name
                        ?? 'N/A',
                ];
            });

        return view('fee-payments.reports.school-overview', [
            'academicYears' => $academicYears,
            'selectedAcademicYear' => $academicYearId,
            'latestAcademicYear' => $latestAcademicYear,
            'totalExpectedFees' => $totalExpectedFees,
            'totalFeesPaid' => $totalFeesPaid,
            'totalOutstandingFees' => $totalOutstandingFees,
            'schoolCollectionRate' => $schoolCollectionRate,
            'classReports' => $classReports,
            'totalClassCount' => $classReports->count(),
        ]);
    }


}
