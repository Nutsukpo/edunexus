<?php

namespace App\Http\Controllers;

use App\Models\AcademicYear;
use App\Models\Term;
use App\Models\SystemSetting;
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
use Illuminate\Pagination\LengthAwarePaginator;
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
    $summary = $this->calculateSummary(clone $query, $filters);

    $reportData = $this->getReportData(
        clone $query,
        $filters['report_type'],
        $filters
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



public function show($id)
{
    $feeAccount = StudentFeeAccount::with([
        'student',
        'studentClass',
        'academicYear',
        'studentClassAssignment',
        'payments' => fn ($query) => $query->latest('payment_date'),
        'feeItems',
    ])->findOrFail($id);

    $termId = request('term_id');

    $selectedTerm = null;
    $termBillSheets = collect();
    $paymentHistory = $feeAccount->payments;

    if ($termId) {
        $selectedTerm = Term::query()
            ->whereKey($termId)
            ->where('academic_year_id', $feeAccount->academic_year_id)
            ->firstOrFail();

        $termBillSheets = $this->getAuthoritativeBillSheets(
            $feeAccount->student_class_assignment_id,
            $feeAccount->academic_year_id,
            (int) $termId
        );

        $billIds = $termBillSheets->pluck('id');

        $paymentHistory = $feeAccount->payments
            ->whereIn('bill_sheet_id', $billIds)
            ->values();
    } else {
        $termBillSheets = $this->getAuthoritativeBillSheets(
            $feeAccount->student_class_assignment_id,
            $feeAccount->academic_year_id
        );
    }

    $metrics = $this->calculateAccountMetrics(
        $feeAccount->student_class_assignment_id,
        $feeAccount->academic_year_id,
        $termId ? (int) $termId : null
    );

    $totalFees = $metrics['total_fees'];
    $totalPaid = $metrics['amount_paid'];
    $balance = $metrics['balance'];
    $discount = $metrics['discount'];
    $waiver = $metrics['waiver'];

    return view('fee-payments.reports.show', compact(
        'feeAccount',
        'paymentHistory',
        'totalPaid',
        'balance',
        'totalFees',
        'discount',
        'waiver',
        'selectedTerm',
        'termBillSheets'
    ));
}



public function exportPdf(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'academic_year_id' => 'nullable|exists:academic_years,id',
            'term_id' => 'nullable|exists:terms,id',
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
        'billSheet',
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

    $terms = collect();
    if ($request->filled('academic_year_id')) {
        $terms = Term::query()
            ->where('academic_year_id', $request->academic_year_id)
            ->where('is_active', true)
            ->orderBy('start_date')
            ->get();
    }

    $summaryQuery = clone $query;

    $summary = [
        'total_payments' => (clone $summaryQuery)->count(),
        'total_amount' => (float) (clone $summaryQuery)->sum(
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
        'terms',
        'summary',
        'request'
    ));
}


    public function getSummaryStats(Request $request)
    {
        $filters = $this->getFilterParameters($request);

        $summary = $this->calculateSummary(
            $this->buildReportQuery($filters),
            $filters
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
            })
            ->when($filters['term_id'], function ($q) use ($filters) {
                $q->whereHas('billSheet', fn ($bill) =>
                    $bill->where('term_id', $filters['term_id'])
                        ->where('academic_year_id', $filters['academic_year_id'])
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
            ->when($filters['term_id'], function ($q) use ($filters) {
                $assignmentIds = BillSheet::query()
                    ->when($filters['academic_year_id'], fn ($q, $id) => $q->where('academic_year_id', $id))
                    ->where('term_id', $filters['term_id'])
                    ->where('is_active', true)
                    ->whereIn('status', ['approved', 'published'])
                    ->pluck('student_class_assignment_id');
                $q->whereIn('student_class_assignment_id', $assignmentIds);
            })
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
    $filters = [
        'academic_year_id' => $request->get('academic_year_id'),
        'term_id' => $request->get('term_id'),
        'class_id' => $request->get('class_id'),
    ];

    $query = StudentFeeAccount::with([
        'student',
        'studentClass',
        'academicYear',
        'studentClassAssignment',
    ])
    ->where('balance', '>', 0)
    ->where('status', '!=', 'paid')
    ->when($filters['academic_year_id'], fn ($q, $id) =>
        $q->where('academic_year_id', $id)
    )
    ->when($filters['class_id'], fn ($q, $id) =>
        $q->where('student_class_id', $id)
    );

    if ($filters['term_id']) {
        $billAssignmentIds = BillSheet::query()
            ->where('term_id', $filters['term_id'])
            ->where('academic_year_id', $filters['academic_year_id'])
            ->where('is_active', true)
            ->whereIn('status', ['approved', 'published'])
            ->pluck('student_class_assignment_id')
            ->unique();

        $query->whereIn('student_class_assignment_id', $billAssignmentIds);
    }

    $accounts = $query->orderByDesc('balance')->get();

    $termMetrics = $filters['term_id']
        ? $this->getTermMetrics($accounts, $filters['term_id'])
        : collect();

    $outstandingRows = $accounts->map(function ($account) use ($termMetrics) {
        if ($termMetrics->has($account->id)) {
            $metrics = $termMetrics->get($account->id);
            foreach ($metrics as $key => $value) {
                $account->{$key} = $value;
            }
        }
        return $account;
    })->filter(fn ($account) => (float) $account->balance > 0)->values();

    $totalOutstanding = (float) $outstandingRows->sum('balance');
    $totalStudents = $outstandingRows->pluck('student_id')->filter()->unique()->count();

    $outstandingFees = new \Illuminate\Pagination\LengthAwarePaginator(
        $outstandingRows->forPage(
            LengthAwarePaginator::resolveCurrentPage(),
            50
        ),
        $outstandingRows->count(),
        50,
        LengthAwarePaginator::resolveCurrentPage(),
        ['path' => LengthAwarePaginator::resolveCurrentPath(), 'query' => $request->query()]
    );

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

    $terms = $filters['academic_year_id']
        ? Term::query()
            ->where('academic_year_id', $filters['academic_year_id'])
            ->where('is_active', true)
            ->orderBy('start_date')
            ->get()
        : collect();

    return view('fee-payments.reports.outstanding', compact(
        'outstandingFees',
        'totalOutstanding',
        'totalStudents',
        'academicYears',
        'classes',
        'terms'
    ));
}



private function getFilterParameters(Request $request): array
{
    $settings = SystemSetting::instance();

    $academicYearId = $request->filled('academic_year_id')
        ? $request->get('academic_year_id')
        : $settings->current_academic_year_id;

    $termId = $request->filled('term_id')
        ? $request->get('term_id')
        : $settings->current_term_id;

    if ($academicYearId && $termId) {
        $validTerm = Term::query()
            ->whereKey($termId)
            ->where('academic_year_id', $academicYearId)
            ->where('is_active', true)
            ->exists();

        if (!$validTerm) {
            $termId = null;
        }
    }

    return [
        'academic_year_id' => $academicYearId,
        'term_id' => $termId,
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
        ->orderByDesc('start_date')
        ->get()
        ->mapWithKeys(fn ($year) => [
            $year->id => $this->academicYearName($year),
        ]);

    $terms = collect();
    if (!empty($filters['academic_year_id'])) {
        $terms = Term::query()
            ->where('academic_year_id', $filters['academic_year_id'])
            ->where('is_active', true)
            ->orderBy('start_date')
            ->get();
    }

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
            $q->when(
                !empty($filters['academic_year_id']),
                fn ($query) => $query->where('academic_year_id', $filters['academic_year_id'])
            )->when(
                !empty($filters['class_id']),
                fn ($query) => $query->where('student_class_id', $filters['class_id'])
            );
        });
    }

    $students = $studentsQuery->get()
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
        'terms' => $terms,
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



private function buildReportQuery(array $filters): Builder
{
    $query = StudentFeeAccount::with([
        'student',
        'studentClass',
        'academicYear',
        'studentClassAssignment',
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
        ->when($filters['status'] && !$filters['term_id'], fn ($q, $status) =>
            $q->where('status', $status)
        );

    if (!empty($filters['term_id'])) {
        $assignmentIds = BillSheet::query()
            ->where('academic_year_id', $filters['academic_year_id'])
            ->where('term_id', $filters['term_id'])
            ->where('is_active', true)
            ->whereIn('status', ['approved', 'published'])
            ->pluck('student_class_assignment_id')
            ->unique();

        $query->whereIn('student_class_assignment_id', $assignmentIds);
    }

    if (!empty($filters['date_from']) || !empty($filters['date_to'])) {
        $query->whereHas('payments', function ($paymentQuery) use ($filters) {
            $paymentQuery->where('status', 'completed')
                ->when($filters['date_from'], fn ($q, $date) =>
                    $q->whereDate('payment_date', '>=', $date)
                )
                ->when($filters['date_to'], fn ($q, $date) =>
                    $q->whereDate('payment_date', '<=', $date)
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



private function getReportData(Builder $query, string $reportType, array $filters = [])
{
    if ($reportType === 'detailed') {
        $paginator = $query
            ->with(['payments' => fn ($q) => $q->latest('payment_date')])
            ->paginate(50)
            ->withQueryString();

        $this->applyTermMetricsToCollection($paginator->getCollection(), $filters);
        $this->applyStatusFilterToCollection($paginator, $filters);

        return $paginator;
    }

    $items = $query->get();
    $this->applyTermMetricsToCollection($items, $filters);

    if (!empty($filters['status'])) {
        $items = $items->filter(fn ($item) => strtolower((string) $item->status) === strtolower($filters['status']))->values();
    }

    return match ($reportType) {
        'student' => $items
            ->groupBy('student_id')
            ->map(function ($group) {
                return (object) [
                    'student' => $group->first()->student,
                    'accounts' => $group,
                    'total_fees' => $group->sum('total_fees'),
                    'amount_paid' => $group->sum('amount_paid'),
                    'balance' => $group->sum('balance'),
                    'discount_applied' => $group->sum('discount_applied'),
                    'waiver_amount' => $group->sum('waiver_amount'),
                    'status' => $this->derivedStatus(
                        (float) $group->sum('amount_paid'),
                        (float) $group->sum('balance')
                    ),
                ];
            })
            ->sortByDesc('amount_paid')
            ->values(),

        'class' => $items
            ->groupBy('student_class_id')
            ->map(function ($group) {
                $fees = (float) $group->sum('total_fees');
                $paid = (float) $group->sum('amount_paid');

                return (object) [
                    'class' => $group->first()->studentClass,
                    'academicYear' => $group->first()->academicYear,
                    'termName' => $group->first()->report_term_name ?? 'All Terms',
                    'total_students' => $group->pluck('student_id')->unique()->count(),
                    'total_fees' => $fees,
                    'amount_paid' => $paid,
                    'balance' => $group->sum('balance'),
                    'collection_rate' => $fees > 0 ? round(($paid / $fees) * 100, 2) : 0,
                ];
            })
            ->sortByDesc('collection_rate')
            ->values(),

        'payment_summary' => $this->getPaymentSummaryByPeriod($query, $filters),

        default => $query->paginate(50)->withQueryString(),
    };
}



private function getPaymentSummaryByPeriod(Builder $query, array $filters = [])
{
    $items = $query->get();
    $this->applyTermMetricsToCollection($items, $filters);

    return $items
        ->groupBy(function ($item) {
            $date = $item->created_at;
            return $date ? Carbon::parse($date)->format('Y-m-d') : 'N/A';
        })
        ->map(function ($group, $date) {
            return (object) [
                'date' => $date,
                'count' => $group->count(),
                'total_fees' => $group->sum('total_fees'),
                'total_paid' => $group->sum('amount_paid'),
                'total_balance' => $group->sum('balance'),
            ];
        })
        ->sortByDesc('date')
        ->values();
}



private function calculateSummary(Builder $query, array $filters = []): array
{
    $items = $query->get();
    $this->applyTermMetricsToCollection($items, $filters);

    $totalFees = (float) $items->sum('total_fees');
    $totalPaid = (float) $items->sum('amount_paid');
    $totalBalance = max(
        0,
        round($totalFees - $totalPaid, 2)
    );

    return [
        'total_students' => $items->pluck('student_id')->filter()->unique()->count(),
        'total_fees' => $totalFees,
        'total_paid' => $totalPaid,
        'total_balance' => $totalBalance,
        'total_discount' => (float) $items->sum('discount_applied'),
        'total_waiver' => (float) $items->sum('waiver_amount'),
        'collection_rate' => $totalFees > 0
            ? round(min(100, ($totalPaid / $totalFees) * 100), 2)
            : 0,
        'paid_count' => $items->where('status', 'paid')->count(),
        'partial_count' => $items->where('status', 'partial')->count(),
        'pending_count' => $items->where('status', 'pending')->count(),
    ];
}



private function getPaymentSummary(array $filters): array
{
    $query = FeePayment::query()
        ->where('status', 'completed')
        ->when($filters['student_id'], fn ($q, $id) => $q->where('student_id', $id))
        ->when($filters['payment_method'], fn ($q, $method) => $q->where('payment_method', $method))
        ->when($filters['date_from'], fn ($q, $date) => $q->whereDate('payment_date', '>=', $date))
        ->when($filters['date_to'], fn ($q, $date) => $q->whereDate('payment_date', '<=', $date));

    if ($filters['academic_year_id']) {
        $query->whereHas('studentFeeAccount', fn ($q) =>
            $q->where('academic_year_id', $filters['academic_year_id'])
        );
    }

    if ($filters['class_id']) {
        $query->whereHas('studentFeeAccount', fn ($q) =>
            $q->where('student_class_id', $filters['class_id'])
        );
    }

    if ($filters['term_id']) {
        $query->whereHas('billSheet', fn ($q) =>
            $q->where('academic_year_id', $filters['academic_year_id'])
              ->where('term_id', $filters['term_id'])
        );
    }

    $totalPayments = (clone $query)->count();
    $totalAmount = (float) (clone $query)->sum(DB::raw('COALESCE(net_amount, amount, 0)'));

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



private function applyPaymentFilters($query, Request $request)
{
    $query
        ->when($request->get('student_id'), fn ($q, $id) => $q->where('student_id', $id))
        ->when($request->get('academic_year_id'), fn ($q, $id) =>
            $q->whereHas('studentFeeAccount', fn ($account) => $account->where('academic_year_id', $id))
        )
        ->when($request->get('class_id'), fn ($q, $id) =>
            $q->whereHas('studentFeeAccount', fn ($account) => $account->where('student_class_id', $id))
        )
        ->when($request->get('term_id'), function ($q, $termId) use ($request) {
            $q->whereHas('billSheet', fn ($bill) =>
                $bill->where('term_id', $termId)
                     ->when($request->get('academic_year_id'),
                         fn ($b) => $b->where('academic_year_id', $request->get('academic_year_id')))
            );
        })
        ->when($request->get('date_from'), fn ($q, $date) => $q->whereDate('payment_date', '>=', $date))
        ->when($request->get('date_to'), fn ($q, $date) => $q->whereDate('payment_date', '<=', $date))
        ->when($request->get('payment_method'), fn ($q, $method) => $q->where('payment_method', $method))
        ->when($request->get('status'), fn ($q, $status) => $q->where('status', $status));

    return $query;
}



private function getExportData(array $filters)
{
    $items = $this->buildReportQuery($filters)->get();
    $this->applyTermMetricsToCollection($items, $filters);

    if (!empty($filters['status'])) {
        $items = $items->filter(fn ($item) =>
            strtolower((string) $item->status) === strtolower($filters['status'])
        )->values();
    }

    return $items;
}


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

    /**
     * Apply term-specific Bill Sheet values to StudentFeeAccount report rows.
     *
     * StudentFeeAccount is annual in the current schema; BillSheet is the
     * authoritative academic-year + term fee document.
     */
    private function applyTermMetricsToCollection($items, array $filters): void
{
    if ($items->isEmpty()) {
        return;
    }

    $assignmentIds = $items
        ->pluck('student_class_assignment_id')
        ->filter()
        ->unique()
        ->values();

    if ($assignmentIds->isEmpty()) {
        return;
    }

    $academicYearId = $filters['academic_year_id'] ?? null;
    $termId = $filters['term_id'] ?? null;

    /*
     * BILL SHEET IS THE AUTHORITY FOR EXPECTED FEES.
     *
     * total_amount is the gross amount shown as "Total Fees".
     * We do NOT substitute net_amount here. net_amount may already
     * contain a discount and was causing the report to show GHS 1,500
     * instead of the GHS 2,000 gross bill.
     */
    $billQuery = BillSheet::query()
        ->with('term')
        ->whereIn('student_class_assignment_id', $assignmentIds)
        ->where('is_active', true)
        ->whereIn('status', ['approved', 'published']);

    if ($academicYearId) {
        $billQuery->where('academic_year_id', $academicYearId);
    }

    if ($termId) {
        $billQuery->where('term_id', $termId);
    }

    $billSheets = $billQuery
        ->orderByDesc('id')
        ->get();

    /*
     * If a bill was regenerated, use only the newest bill for the
     * same student assignment + academic year + term.
     */
    $billSheets = $billSheets
        ->groupBy(function ($bill) {
            return $bill->student_class_assignment_id
                . '|'
                . $bill->academic_year_id
                . '|'
                . $bill->term_id;
        })
        ->map(fn ($group) => $group->first())
        ->values();

    $billIds = $billSheets->pluck('id')->unique()->values();

    /*
     * ONLY COMPLETED PAYMENTS REDUCE THE BALANCE.
     */
    $paymentQuery = FeePayment::query()
        ->whereIn('bill_sheet_id', $billIds)
        ->where('status', 'completed');

    if (!empty($filters['date_from'])) {
        $paymentQuery->whereDate(
            'payment_date',
            '>=',
            $filters['date_from']
        );
    }

    if (!empty($filters['date_to'])) {
        $paymentQuery->whereDate(
            'payment_date',
            '<=',
            $filters['date_to']
        );
    }

    if (!empty($filters['payment_method'])) {
        $paymentQuery->where(
            'payment_method',
            $filters['payment_method']
        );
    }

    $payments = $paymentQuery
        ->get([
            'bill_sheet_id',
            'net_amount',
            'amount',
        ])
        ->groupBy('bill_sheet_id')
        ->map(fn ($group) => round(
            (float) $group->sum(
                fn ($payment) => $this->paymentAmount($payment)
            ),
            2
        ));

    $billsByAssignment = $billSheets
        ->groupBy('student_class_assignment_id');

    foreach ($items as $item) {
        $assignmentBills = $billsByAssignment->get(
            $item->student_class_assignment_id,
            collect()
        );

        $totalFees = 0.0;
        $amountPaid = 0.0;
        $discount = 0.0;
        $tax = 0.0;

        foreach ($assignmentBills as $bill) {
            $totalFees += $this->reportBillGrossTotal($bill);
            $discount += (float) ($bill->discount_amount ?? 0);
            $tax += (float) ($bill->tax_amount ?? 0);
            $amountPaid += (float) $payments->get($bill->id, 0);
        }

        $totalFees = round($totalFees, 2);
        $amountPaid = round($amountPaid, 2);
        $discount = round($discount, 2);
        $tax = round($tax, 2);

        /*
         * REPORT BALANCE:
         *
         *     Total Fees (gross billed) - Completed Payments
         *
         * Discount is exposed separately and is NOT deducted again.
         * This prevents the previous net_amount/double-discount problem.
         */
        $balance = max(
            0,
            round($totalFees - $amountPaid, 2)
        );

        $item->total_fees = $totalFees;
        $item->amount_paid = $amountPaid;
        $item->balance = $balance;
        $item->discount_applied = $discount;
        $item->tax_amount = $tax;
        $item->waiver_amount = 0.0;
        $item->status = $this->derivedStatus(
            $amountPaid,
            $balance
        );

        if ($termId) {
            $firstBill = $assignmentBills->first();

            $item->report_term_name = $firstBill?->term?->name
                ?? 'Selected Term';

            $item->report_bill_sheet_id = $firstBill?->id;
        } else {
            $item->report_term_name = 'All Terms';
        }
    }
}



private function applyStatusFilterToCollection($paginator, array $filters): void
    {
        if (empty($filters['status'])) {
            return;
        }

        $collection = $paginator->getCollection()
            ->filter(fn ($item) =>
                strtolower((string) $item->status) === strtolower($filters['status'])
            )
            ->values();

        $paginator->setCollection($collection);
    }

    private function getTermMetrics($accounts, $termId)
{
    if ($accounts->isEmpty()) {
        return collect();
    }

    $results = collect();

    foreach ($accounts as $account) {
        $single = collect([$account]);

        $this->applyTermMetricsToCollection($single, [
            'academic_year_id' => $account->academic_year_id,
            'term_id' => $termId,
            'date_from' => null,
            'date_to' => null,
            'payment_method' => null,
        ]);

        $updated = $single->first();

        $results->put($account->id, [
            'total_fees' => (float) ($updated->total_fees ?? 0),
            'amount_paid' => (float) ($updated->amount_paid ?? 0),
            'balance' => (float) ($updated->balance ?? 0),
            'discount_applied' => (float) ($updated->discount_applied ?? 0),
            'waiver_amount' => (float) ($updated->waiver_amount ?? 0),
            'status' => $updated->status ?? 'pending',
            'report_term_name' => $updated->report_term_name ?? 'Selected Term',
        ]);
    }

    return $results;
}

/*
|--------------------------------------------------------------------------
| AUTHORITATIVE BILL / PAYMENT HELPERS
|--------------------------------------------------------------------------
*/

private function getAuthoritativeBillSheets(
    int $assignmentId,
    int $academicYearId,
    ?int $termId = null
) {
    $query = BillSheet::query()
        ->with('term')
        ->where('student_class_assignment_id', $assignmentId)
        ->where('academic_year_id', $academicYearId)
        ->where('is_active', true)
        ->whereIn('status', ['approved', 'published']);

    if ($termId !== null) {
        $query->where('term_id', $termId);
    }

    return $query
        ->orderByDesc('id')
        ->get()
        ->groupBy(function ($bill) {
            return $bill->student_class_assignment_id
                . '|'
                . $bill->academic_year_id
                . '|'
                . $bill->term_id;
        })
        ->map(fn ($group) => $group->first())
        ->values();
}

/**
 * Gross billed amount used by the report's "Total Fees" field.
 */
private function reportBillGrossTotal(BillSheet $bill): float
{
    return round(
        max(0, (float) ($bill->total_amount ?? 0)),
        2
    );
}

/**
 * Amount from a payment that counts as collected.
 */
private function paymentAmount(FeePayment $payment): float
{
    if ($payment->net_amount !== null && $payment->net_amount !== '') {
        return round((float) $payment->net_amount, 2);
    }

    return round((float) ($payment->amount ?? 0), 2);
}

/**
 * Calculate one student's authoritative report position.
 */
private function calculateAccountMetrics(
    int $assignmentId,
    int $academicYearId,
    ?int $termId = null
): array {
    $bills = $this->getAuthoritativeBillSheets(
        $assignmentId,
        $academicYearId,
        $termId
    );

    $totalFees = round(
        (float) $bills->sum(
            fn ($bill) => $this->reportBillGrossTotal($bill)
        ),
        2
    );

    $discount = round(
        (float) $bills->sum(
            fn ($bill) => (float) ($bill->discount_amount ?? 0)
        ),
        2
    );

    $billIds = $bills->pluck('id');

    $amountPaid = $billIds->isEmpty()
        ? 0.0
        : round(
            (float) FeePayment::query()
                ->whereIn('bill_sheet_id', $billIds)
                ->where('status', 'completed')
                ->get(['net_amount', 'amount'])
                ->sum(
                    fn ($payment) => $this->paymentAmount($payment)
                ),
            2
        );

    $balance = max(
        0,
        round($totalFees - $amountPaid, 2)
    );

    return [
        'total_fees' => $totalFees,
        'amount_paid' => $amountPaid,
        'balance' => $balance,
        'discount' => $discount,
        'waiver' => 0.0,
        'status' => $this->derivedStatus(
            $amountPaid,
            $balance
        ),
    ];
}

/**
     * School-wide fee overview.
     *
     * IMPORTANT:
     * A historical term must never depend on is_current.
     * StudentClassAssignment is the historical academic-period link and
     * BillSheet is the authoritative student/term fee document.
     */
    public function schoolOverview(Request $request)
{
    $settings = SystemSetting::instance();

    $academicYearId = $request->has('academic_year_id')
        ? $request->input('academic_year_id')
        : ($settings->current_academic_year_id ?? null);

    $termId = $request->has('term_id')
        ? $request->input('term_id')
        : ($settings->current_term_id ?? null);

    $academicYearId = ($academicYearId === null || $academicYearId === '')
        ? 'all'
        : (string) $academicYearId;

    $termId = ($termId === null || $termId === '')
        ? 'all'
        : (string) $termId;

    if ($academicYearId === 'all') {
        $termId = 'all';
    }

    $latestAcademicYear = AcademicYear::query()
        ->orderByDesc('start_date')
        ->orderByDesc('id')
        ->first();

    $academicYears = AcademicYear::query()
        ->orderByDesc('start_date')
        ->orderByDesc('id')
        ->get()
        ->mapWithKeys(fn ($year) => [
            $year->id => $this->academicYearName($year),
        ]);

    $terms = collect();

    if ($academicYearId !== 'all') {
        $terms = Term::query()
            ->where('academic_year_id', $academicYearId)
            ->where('is_active', true)
            ->orderBy('start_date')
            ->orderBy('id')
            ->get();

        if (
            $termId !== 'all'
            && !$terms->contains(
                fn ($term) => (string) $term->id === (string) $termId
            )
        ) {
            $termId = 'all';
        }
    }

    /*
     * BILL SHEETS ARE THE AUTHORITY FOR EXPECTED FEES.
     * StudentFeeAccount values are not used for the school totals because
     * they can become stale after promotion or bill regeneration.
     */
    $billQuery = BillSheet::query()
        ->with([
            'studentClassAssignment.student',
            'studentClassAssignment.studentClass',
        ])
        ->where('is_active', true)
        ->whereIn('status', ['approved', 'published']);

    if ($academicYearId !== 'all') {
        $billQuery->where('academic_year_id', $academicYearId);
    }

    if ($termId !== 'all') {
        $billQuery->where('term_id', $termId);
    }

    $billSheets = $billQuery
        ->orderByDesc('id')
        ->get([
            'id',
            'student_class_assignment_id',
            'academic_year_id',
            'term_id',
            'total_amount',
            'net_amount',
            'discount_amount',
            'tax_amount',
            'status',
            'is_active',
        ]);

    /*
     * Prevent duplicate billing when a BillSheet was regenerated.
     */
    $billSheets = $billSheets
        ->groupBy(function ($bill) {
            return $bill->student_class_assignment_id
                . '|'
                . $bill->academic_year_id
                . '|'
                . $bill->term_id;
        })
        ->map(fn ($group) => $group->first())
        ->values();

    $billIds = $billSheets->pluck('id')->unique()->values();

    $payments = $billIds->isEmpty()
        ? collect()
        : FeePayment::query()
            ->where('status', 'completed')
            ->whereIn('bill_sheet_id', $billIds)
            ->get([
                'id',
                'student_id',
                'student_class_assignment_id',
                'bill_sheet_id',
                'net_amount',
                'amount',
            ]);

    $paidByBillSheet = $payments
        ->groupBy('bill_sheet_id')
        ->map(fn ($group) => round(
            (float) $group->sum(
                fn ($payment) => $this->paymentAmount($payment)
            ),
            2
        ));

    $classes = StudentClass::query()
        ->orderBy('name')
        ->get();

    $classReports = $classes
        ->map(function ($class) use ($billSheets, $paidByBillSheet) {
            $classBills = $billSheets->filter(function ($bill) use ($class) {
                return optional($bill->studentClassAssignment)
                    && (string) $bill->studentClassAssignment->student_class_id
                        === (string) $class->id;
            });

            $studentIds = $classBills
                ->map(fn ($bill) =>
                    optional($bill->studentClassAssignment)->student_id
                )
                ->filter()
                ->unique()
                ->values();

            $expectedFees = round(
                (float) $classBills->sum(
                    fn ($bill) => $this->reportBillGrossTotal($bill)
                ),
                2
            );

            $feesPaid = round(
                (float) $classBills->sum(
                    fn ($bill) => $paidByBillSheet->get($bill->id, 0)
                ),
                2
            );

            $outstandingFees = max(
                0,
                round($expectedFees - $feesPaid, 2)
            );

            $collectionRate = $expectedFees > 0
                ? round(
                    min(100, ($feesPaid / $expectedFees) * 100),
                    2
                )
                : 0;

            return (object) [
                'class_id' => $class->id,
                'class_name' => $this->className($class),
                'student_accounts' => $studentIds->count(),
                'bill_amount_per_student' => $studentIds->count() > 0
                    ? round(
                        $expectedFees / $studentIds->count(),
                        2
                    )
                    : 0,
                'expected_fees' => $expectedFees,
                'fees_paid' => $feesPaid,
                'outstanding_fees' => $outstandingFees,
                'collection_rate' => $collectionRate,
            ];
        })
        ->filter(fn ($report) =>
            $report->expected_fees > 0
            || $report->fees_paid > 0
            || $report->student_accounts > 0
        )
        ->sortBy('class_name', SORT_NATURAL | SORT_FLAG_CASE)
        ->values();

    $totalExpectedFees = round(
        (float) $classReports->sum('expected_fees'),
        2
    );

    $totalFeesPaid = round(
        (float) $classReports->sum('fees_paid'),
        2
    );

    $totalOutstandingFees = max(
        0,
        round($totalExpectedFees - $totalFeesPaid, 2)
    );

    $schoolCollectionRate = $totalExpectedFees > 0
        ? round(
            min(100, ($totalFeesPaid / $totalExpectedFees) * 100),
            2
        )
        : 0;

    return view('fee-payments.reports.school-overview', [
        'academicYears' => $academicYears,
        'selectedAcademicYear' => $academicYearId,
        'selectedTerm' => $termId,
        'terms' => $terms,
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
