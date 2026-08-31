{{-- resources/views/fee-payments/reports/index.blade.php --}}
@extends('layouts.master')

@section('title', 'Fee Payment Reports')

@section('content')

@php
    /*
    |--------------------------------------------------------------------------
    | REPORT VIEW STATE
    |--------------------------------------------------------------------------
    */

    $selectedYear = request('academic_year_id');
    $selectedClass = request('class_id');
    $selectedStatus = request('status');
    $selectedReport = request('report_type', 'summary');
    $selectedStudent = request('student_id');
    $selectedMethod = request('payment_method');

    $reportLabels = [
        'summary' => 'Summary',
        'detailed' => 'Detailed',
        'student' => 'By Student',
        'class' => 'By Class',
        'payment_summary' => 'Payment Summary',
    ];

    $reportLabel = $reportLabels[$selectedReport] ?? 'Summary';

    $yearLabel = $selectedYear
        ? ($academicYears[$selectedYear] ?? 'Selected Academic Year')
        : 'All Academic Years';

    $classLabel = $selectedClass
        ? ($classes[$selectedClass] ?? 'Selected Class')
        : 'All Classes';

    $statusLabel = $selectedStatus
        ? ($statusOptions[$selectedStatus] ?? ucfirst($selectedStatus))
        : 'All Statuses';

    $studentLabel = $selectedStudent
        ? ($students[$selectedStudent] ?? 'Selected Student')
        : 'All Students';

    $methodLabel = $selectedMethod
        ? ucfirst(str_replace('_', ' ', $selectedMethod))
        : 'All Methods';

    /*
    |--------------------------------------------------------------------------
    | SUMMARY VALUES
    |--------------------------------------------------------------------------
    */

    $totalStudents = (float) ($summary['total_students'] ?? 0);
    $totalFees = (float) ($summary['total_fees'] ?? 0);
    $totalPaid = (float) ($summary['total_paid'] ?? 0);
    $totalBalance = max(0, (float) ($summary['total_balance'] ?? 0));
    $totalDiscount = (float) ($summary['total_discount'] ?? 0);
    $totalWaiver = (float) ($summary['total_waiver'] ?? 0);

    $collectionRate = (float) ($summary['collection_rate'] ?? 0);

    $paidCount = (int) ($summary['paid_count'] ?? 0);
    $partialCount = (int) ($summary['partial_count'] ?? 0);
    $pendingCount = (int) ($summary['pending_count'] ?? 0);

    $reportCount = method_exists($reports, 'total')
        ? $reports->total()
        : (is_countable($reports) ? count($reports) : 0);

    /*
    |--------------------------------------------------------------------------
    | CHART DATA
    |--------------------------------------------------------------------------
    */

    $methodLabels = [];
    $methodAmounts = [];
    $methodCounts = [];

    foreach (($paymentSummary['by_method'] ?? []) as $method) {
        $methodLabels[] = ucfirst(str_replace('_', ' ', $method->payment_method ?? 'Other'));
        $methodAmounts[] = (float) ($method->total ?? 0);
        $methodCounts[] = (int) ($method->count ?? 0);
    }

    $dailyLabels = [];
    $dailyAmounts = [];
    $dailyCounts = [];

    foreach (($paymentSummary['daily_summary'] ?? []) as $day) {
        try {
            $dateLabel = \Carbon\Carbon::parse($day->date)->format('d M');
        } catch (\Throwable $e) {
            $dateLabel = (string) ($day->date ?? '');
        }

        $dailyLabels[] = $dateLabel;
        $dailyAmounts[] = (float) ($day->total ?? 0);
        $dailyCounts[] = (int) ($day->count ?? 0);
    }

    $statusChartLabels = ['Paid', 'Partial', 'Pending'];
    $statusChartValues = [$paidCount, $partialCount, $pendingCount];

    $collectionChartValues = [$totalPaid, $totalBalance];

    /*
    |--------------------------------------------------------------------------
    | SAFE CHART FALLBACKS
    |--------------------------------------------------------------------------
    */

    if (empty($methodLabels)) {
        $methodLabels = ['No Payment Data'];
        $methodAmounts = [0];
        $methodCounts = [0];
    }

    if (empty($dailyLabels)) {
        $dailyLabels = ['No Data'];
        $dailyAmounts = [0];
        $dailyCounts = [0];
    }
@endphp

<style>
    .fee-report-page {
        --report-primary: #1456a0;
        --report-primary-dark: #0b3567;
        --report-success: #15803d;
        --report-danger: #b42318;
        --report-warning: #b7791f;
        --report-info: #087ea4;
        --report-text: #172033;
        --report-muted: #667085;
        --report-border: #e5eaf0;
        --report-soft: #f7f9fc;
    }

    .report-hero {
        border: 1px solid rgba(255,255,255,.08);
        border-radius: 20px;
        background:
            radial-gradient(circle at top right, rgba(255,255,255,.16), transparent 28%),
            linear-gradient(135deg, #0b3567 0%, #1456a0 55%, #2376bd 100%);
        color: #fff;
        padding: 1.6rem;
        box-shadow: 0 15px 35px rgba(20,86,160,.18);
    }

    .report-hero .eyebrow {
        font-size: .69rem;
        letter-spacing: .14em;
        text-transform: uppercase;
        opacity: .7;
        font-weight: 800;
    }

    .report-hero h1 {
        font-size: clamp(1.6rem, 3vw, 2.1rem);
        font-weight: 800;
        margin: .35rem 0 .3rem;
    }

    .report-hero p {
        margin: 0;
        max-width: 740px;
        opacity: .78;
        font-size: .9rem;
    }

    .hero-actions .btn {
        border-radius: 10px;
        font-weight: 600;
    }

    .metric-card,
    .report-card {
        border: 1px solid var(--report-border);
        border-radius: 16px;
        background: #fff;
        box-shadow: 0 6px 20px rgba(16,24,40,.055);
    }

    .metric-card {
        height: 100%;
    }

    .metric-card .card-body {
        padding: 1.1rem;
    }

    .metric-icon {
        width: 44px;
        height: 44px;
        border-radius: 13px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 1rem;
    }

    .metric-label {
        color: var(--report-muted);
        font-size: .69rem;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: .065em;
    }

    .metric-value {
        color: var(--report-text);
        font-size: 1.25rem;
        line-height: 1.2;
        font-weight: 800;
        margin-top: .32rem;
    }

    .metric-note {
        color: var(--report-muted);
        font-size: .68rem;
        margin-top: .3rem;
    }

    .report-card-header {
        padding: 1rem 1.15rem;
        border-bottom: 1px solid var(--report-border);
        background: #fff;
        border-radius: 16px 16px 0 0;
    }

    .report-card-header h5 {
        margin: 0;
        color: var(--report-text);
        font-weight: 800;
        font-size: .98rem;
    }

    .report-card-header p {
        margin: .2rem 0 0;
        color: var(--report-muted);
        font-size: .74rem;
    }

    .filter-grid .form-label {
        color: var(--report-text);
        font-size: .76rem;
        font-weight: 700;
    }

    .filter-grid .form-control,
    .filter-grid .form-select {
        min-height: 43px;
        border-color: var(--report-border);
        border-radius: 10px;
        color: #212529;
        background-color: #fff;
    }

    .filter-grid .form-control:focus,
    .filter-grid .form-select:focus {
        border-color: #86b7fe;
        box-shadow: 0 0 0 .18rem rgba(13,110,253,.11);
    }

    .filter-pills {
        display: flex;
        flex-wrap: wrap;
        gap: .45rem;
    }

    .filter-pills .badge {
        padding: .5rem .75rem;
        font-size: .68rem;
        font-weight: 650;
    }

    .chart-card {
        min-height: 100%;
    }

    .chart-wrapper {
        position: relative;
        height: 290px;
    }

    .chart-wrapper.tall {
        height: 320px;
    }

    .chart-empty {
        height: 290px;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-direction: column;
        color: var(--report-muted);
    }

    .chart-summary {
        border: 1px solid var(--report-border);
        border-radius: 12px;
        padding: .85rem;
        background: #fbfcfe;
    }

    .chart-summary .label {
        color: var(--report-muted);
        font-size: .69rem;
        text-transform: uppercase;
        font-weight: 800;
    }

    .chart-summary .value {
        font-size: 1rem;
        font-weight: 800;
        color: var(--report-text);
        margin-top: .15rem;
    }

    .table-report {
        margin: 0;
    }

    .table-report thead th {
        background: #f7f9fc;
        border-bottom: 1px solid var(--report-border);
        color: #475467;
        font-size: .68rem;
        text-transform: uppercase;
        letter-spacing: .045em;
        white-space: nowrap;
        padding: .8rem .75rem;
    }

    .table-report td {
        color: #344054;
        vertical-align: middle;
        padding: .75rem;
        font-size: .8rem;
    }

    .table-report tbody tr:hover {
        background: #fbfcfe;
    }

    .student-cell-name {
        font-weight: 700;
        color: var(--report-text);
    }

    .student-cell-id {
        color: var(--report-muted);
        font-size: .69rem;
        margin-top: 2px;
    }

    .status-pill {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        border-radius: 999px;
        padding: .4rem .68rem;
        font-size: .66rem;
        font-weight: 800;
        white-space: nowrap;
    }

    .status-paid {
        background: #eaf7ef;
        color: #137333;
    }

    .status-partial {
        background: #fff5db;
        color: #8a6116;
    }

    .status-pending {
        background: #fdecec;
        color: #b42318;
    }

    .collection-progress {
        min-width: 125px;
    }

    .collection-progress .progress {
        height: 7px;
        border-radius: 99px;
        background: #eef2f6;
    }

    .collection-progress .small {
        font-size: .67rem;
    }

    .empty-report {
        padding: 4rem 1rem;
        text-align: center;
        color: var(--report-muted);
    }

    .empty-report-icon {
        width: 62px;
        height: 62px;
        margin: 0 auto 1rem;
        border-radius: 17px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        background: #f2f4f7;
        color: #98a2b3;
        font-size: 1.5rem;
    }

    .quick-actions .btn {
        border-radius: 10px;
        font-weight: 600;
    }

    .summary-mini {
        border: 1px solid var(--report-border);
        border-radius: 12px;
        padding: 1rem;
        background: #fff;
        height: 100%;
    }

    .summary-mini .label {
        font-size: .68rem;
        color: var(--report-muted);
        text-transform: uppercase;
        font-weight: 800;
    }

    .summary-mini .value {
        font-size: 1.1rem;
        font-weight: 800;
        color: var(--report-text);
    }

    .insight-card {
        border: 1px solid var(--report-border);
        border-radius: 13px;
        padding: .95rem;
        background: linear-gradient(180deg, #fff, #fbfcff);
    }

    .insight-title {
        color: var(--report-muted);
        font-size: .69rem;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: .05em;
    }

    .insight-value {
        color: var(--report-text);
        font-size: 1.15rem;
        font-weight: 800;
        margin-top: .25rem;
    }

    @media (max-width: 767.98px) {
        .report-hero {
            padding: 1.2rem;
        }

        .chart-wrapper,
        .chart-wrapper.tall {
            height: 255px;
        }
    }

    @media print {
        .no-print,
        .report-filters,
        .quick-actions,
        .hero-actions {
            display: none !important;
        }

        .report-hero {
            background: #fff !important;
            color: #111 !important;
            box-shadow: none !important;
            border: 1px solid #bbb;
        }

        .report-card,
        .metric-card {
            box-shadow: none !important;
        }

        .chart-wrapper {
            height: 250px !important;
        }
    }
</style>

<div class="container-fluid py-3 fee-report-page">

    {{-- =========================================================
         HEADER
    ========================================================== --}}
    <div class="report-hero mb-4">

        <div class="d-flex flex-column flex-xl-row justify-content-between align-items-xl-center gap-3">

            <div>
                <div class="eyebrow">Finance &amp; Collections</div>

                <h1>Fee Payment Reports</h1>

                <p>
                    A complete view of student billing, collections, outstanding balances,
                    payment methods and collection performance.
                </p>
            </div>

            <div class="hero-actions d-flex flex-wrap gap-2 no-print">

                <a
                    href="{{ route('fee.payment.reports.export.pdf', request()->query()) }}"
                    class="btn btn-light btn-sm">
                    <i class="fas fa-file-pdf me-1 text-danger"></i>
                    Export PDF
                </a>

                <a
                    href="{{ route('fee.payment.reports.export.excel', request()->query()) }}"
                    class="btn btn-light btn-sm">
                    <i class="fas fa-file-excel me-1 text-success"></i>
                    Export Excel
                </a>

                <button
                    type="button"
                    class="btn btn-outline-light btn-sm"
                    onclick="window.print()">
                    <i class="fas fa-print me-1"></i>
                    Print
                </button>

            </div>

        </div>

    </div>


    {{-- =========================================================
         ACTIVE FILTERS
    ========================================================== --}}
    <div class="filter-pills mb-3">

        <span class="badge rounded-pill bg-primary-subtle text-primary-emphasis">
            <i class="fas fa-chart-pie me-1"></i>
            {{ $reportLabel }}
        </span>

        <span class="badge rounded-pill bg-light text-dark border">
            {{ $yearLabel }}
        </span>

        <span class="badge rounded-pill bg-light text-dark border">
            {{ $classLabel }}
        </span>

        <span class="badge rounded-pill bg-light text-dark border">
            {{ $statusLabel }}
        </span>

        @if($selectedStudent)
            <span class="badge rounded-pill bg-light text-dark border">
                {{ $studentLabel }}
            </span>
        @endif

        @if($selectedMethod)
            <span class="badge rounded-pill bg-light text-dark border">
                {{ $methodLabel }}
            </span>
        @endif

    </div>


    {{-- =========================================================
         KPI CARDS
    ========================================================== --}}
    <div class="row g-3 mb-4">

        <div class="col-xl-3 col-md-6">
            <div class="metric-card">

                <div class="card-body">

                    <div class="d-flex justify-content-between align-items-start">

                        <div>
                            <div class="metric-label">Student Accounts</div>

                            <div class="metric-value">
                                {{ number_format($totalStudents) }}
                            </div>

                            <div class="metric-note">
                                Accounts matching current filters
                            </div>
                        </div>

                        <span class="metric-icon bg-primary-subtle text-primary">
                            <i class="fas fa-users"></i>
                        </span>

                    </div>

                </div>

            </div>
        </div>


        <div class="col-xl-3 col-md-6">
            <div class="metric-card">

                <div class="card-body">

                    <div class="d-flex justify-content-between align-items-start">

                        <div>
                            <div class="metric-label">Total Fees</div>

                            <div class="metric-value">
                                GHS {{ number_format($totalFees, 2) }}
                            </div>

                            <div class="metric-note">
                                Gross billed amount
                            </div>
                        </div>

                        <span class="metric-icon bg-success-subtle text-success">
                            <i class="fas fa-file-invoice-dollar"></i>
                        </span>

                    </div>

                </div>

            </div>
        </div>


        <div class="col-xl-3 col-md-6">
            <div class="metric-card">

                <div class="card-body">

                    <div class="d-flex justify-content-between align-items-start">

                        <div>
                            <div class="metric-label">Amount Collected</div>

                            <div class="metric-value">
                                GHS {{ number_format($totalPaid, 2) }}
                            </div>

                            <div class="metric-note">
                                {{ number_format($collectionRate, 1) }}% collection rate
                            </div>
                        </div>

                        <span class="metric-icon bg-info-subtle text-info">
                            <i class="fas fa-circle-check"></i>
                        </span>

                    </div>

                </div>

            </div>
        </div>


        <div class="col-xl-3 col-md-6">
            <div class="metric-card">

                <div class="card-body">

                    <div class="d-flex justify-content-between align-items-start">

                        <div>
                            <div class="metric-label">Outstanding</div>

                            <div class="metric-value text-danger">
                                GHS {{ number_format($totalBalance, 2) }}
                            </div>

                            <div class="metric-note">
                                Balance still to be collected
                            </div>
                        </div>

                        <span class="metric-icon bg-danger-subtle text-danger">
                            <i class="fas fa-triangle-exclamation"></i>
                        </span>

                    </div>

                </div>

            </div>
        </div>

    </div>


    {{-- =========================================================
         ANALYTICS ROW
    ========================================================== --}}
    <div class="row g-4 mb-4">

        {{-- FEE COLLECTION DONUT --}}
        <div class="col-xl-4 col-lg-6">

            <div class="report-card chart-card h-100">

                <div class="report-card-header">

                    <h5>
                        <i class="fas fa-chart-pie text-primary me-2"></i>
                        Collection Position
                    </h5>

                    <p>
                        Paid versus outstanding balance
                    </p>

                </div>

                <div class="card-body">

                    <div class="chart-wrapper">
                        <canvas id="collectionDonutChart"></canvas>
                    </div>

                    <div class="row g-2 mt-1">

                        <div class="col-6">
                            <div class="chart-summary">
                                <div class="label">Paid</div>
                                <div class="value text-success">
                                    GHS {{ number_format($totalPaid, 2) }}
                                </div>
                            </div>
                        </div>

                        <div class="col-6">
                            <div class="chart-summary">
                                <div class="label">Outstanding</div>
                                <div class="value text-danger">
                                    GHS {{ number_format($totalBalance, 2) }}
                                </div>
                            </div>
                        </div>

                    </div>

                </div>

            </div>

        </div>


        {{-- PAYMENT METHOD DONUT --}}
        <div class="col-xl-4 col-lg-6">

            <div class="report-card chart-card h-100">

                <div class="report-card-header">

                    <h5>
                        <i class="fas fa-wallet text-success me-2"></i>
                        Payment Methods
                    </h5>

                    <p>
                        Distribution of completed collections
                    </p>

                </div>

                <div class="card-body">

                    <div class="chart-wrapper">
                        <canvas id="paymentMethodChart"></canvas>
                    </div>

                    <div class="table-responsive mt-2">

                        <table class="table table-sm table-report">

                            <thead>
                                <tr>
                                    <th>Method</th>
                                    <th class="text-end">Count</th>
                                    <th class="text-end">Amount</th>
                                </tr>
                            </thead>

                            <tbody>

                                @forelse(($paymentSummary['by_method'] ?? []) as $method)

                                    <tr>

                                        <td>
                                            {{ ucfirst(str_replace('_', ' ', $method->payment_method ?? 'Other')) }}
                                        </td>

                                        <td class="text-end">
                                            {{ number_format($method->count ?? 0) }}
                                        </td>

                                        <td class="text-end fw-semibold">
                                            GHS {{ number_format($method->total ?? 0, 2) }}
                                        </td>

                                    </tr>

                                @empty

                                    <tr>
                                        <td colspan="3" class="text-center text-muted py-3">
                                            No payment method data available.
                                        </td>
                                    </tr>

                                @endforelse

                            </tbody>

                        </table>

                    </div>

                </div>

            </div>

        </div>


        {{-- STATUS DOUGHNUT --}}
        <div class="col-xl-4 col-lg-12">

            <div class="report-card chart-card h-100">

                <div class="report-card-header">

                    <h5>
                        <i class="fas fa-chart-donut text-warning me-2"></i>
                        Account Status
                    </h5>

                    <p>
                        Paid, partial and pending accounts
                    </p>

                </div>

                <div class="card-body">

                    <div class="chart-wrapper">
                        <canvas id="statusDonutChart"></canvas>
                    </div>

                    <div class="row g-2 mt-1">

                        <div class="col-4">
                            <div class="chart-summary text-center">
                                <div class="label">Paid</div>
                                <div class="value text-success">
                                    {{ number_format($paidCount) }}
                                </div>
                            </div>
                        </div>

                        <div class="col-4">
                            <div class="chart-summary text-center">
                                <div class="label">Partial</div>
                                <div class="value text-warning">
                                    {{ number_format($partialCount) }}
                                </div>
                            </div>
                        </div>

                        <div class="col-4">
                            <div class="chart-summary text-center">
                                <div class="label">Pending</div>
                                <div class="value text-danger">
                                    {{ number_format($pendingCount) }}
                                </div>
                            </div>
                        </div>

                    </div>

                </div>

            </div>

        </div>

    </div>


    {{-- =========================================================
         COLLECTION TREND + INSIGHTS
    ========================================================== --}}
    <div class="row g-4 mb-4">

        <div class="col-xl-8">

            <div class="report-card h-100">

                <div class="report-card-header d-flex flex-wrap justify-content-between align-items-center gap-2">

                    <div>

                        <h5>
                            <i class="fas fa-chart-line text-primary me-2"></i>
                            Collection Trend
                        </h5>

                        <p>
                            Completed payment activity by day
                        </p>

                    </div>

                    <span class="badge bg-primary-subtle text-primary-emphasis">
                        Last 30 available days
                    </span>

                </div>

                <div class="card-body">

                    <div class="chart-wrapper tall">
                        <canvas id="collectionTrendChart"></canvas>
                    </div>

                </div>

            </div>

        </div>


        <div class="col-xl-4">

            <div class="report-card h-100">

                <div class="report-card-header">

                    <h5>
                        <i class="fas fa-lightbulb text-warning me-2"></i>
                        Collection Insights
                    </h5>

                    <p>
                        Key indicators from the current selection
                    </p>

                </div>

                <div class="card-body">

                    <div class="d-grid gap-3">

                        <div class="insight-card">

                            <div class="insight-title">
                                Collection Rate
                            </div>

                            <div class="insight-value text-success">
                                {{ number_format($collectionRate, 1) }}%
                            </div>

                            <div class="progress mt-2" style="height: 7px;">
                                <div
                                    class="progress-bar bg-success"
                                    style="width: {{ min(100, max(0, $collectionRate)) }}%;">
                                </div>
                            </div>

                        </div>


                        <div class="insight-card">

                            <div class="insight-title">
                                Outstanding Ratio
                            </div>

                            <div class="insight-value text-danger">

                                @php
                                    $outstandingRate = $totalFees > 0
                                        ? min(100, ($totalBalance / $totalFees) * 100)
                                        : 0;
                                @endphp

                                {{ number_format($outstandingRate, 1) }}%

                            </div>

                        </div>


                        <div class="insight-card">

                            <div class="insight-title">
                                Total Discount
                            </div>

                            <div class="insight-value">
                                GHS {{ number_format($totalDiscount, 2) }}
                            </div>

                        </div>


                        <div class="insight-card">

                            <div class="insight-title">
                                Total Waiver
                            </div>

                            <div class="insight-value">
                                GHS {{ number_format($totalWaiver, 2) }}
                            </div>

                        </div>


                        <div class="insight-card">

                            <div class="insight-title">
                                Payment Transactions
                            </div>

                            <div class="insight-value">
                                {{ number_format($paymentSummary['total_payments'] ?? 0) }}
                            </div>

                        </div>

                    </div>

                </div>

            </div>

        </div>

    </div>


    {{-- =========================================================
         FILTERS
    ========================================================== --}}
    <div class="report-card report-filters mb-4">

        <div class="report-card-header d-flex justify-content-between align-items-center gap-2">

            <div>

                <h5>
                    <i class="fas fa-sliders text-primary me-2"></i>
                    Report Filters
                </h5>

                <p>
                    Refine the report before generating tables or exports.
                </p>

            </div>

            <a
                href="{{ route('fee.payment.reports.index') }}"
                class="btn btn-outline-secondary btn-sm no-print">

                <i class="fas fa-rotate-left me-1"></i>

                Reset

            </a>

        </div>


        <div class="card-body filter-grid">

            <form
                method="GET"
                action="{{ route('fee.payment.reports.index') }}">

                <div class="row g-3">

                    <div class="col-xl-3 col-md-6">

                        <label for="academic_year_id" class="form-label">
                            Academic Year
                        </label>

                        <select
                            name="academic_year_id"
                            id="academic_year_id"
                            class="form-select">

                            <option value="">
                                All Academic Years
                            </option>

                            @foreach($academicYears as $id => $year)

                                <option
                                    value="{{ $id }}"
                                    {{ (string) $selectedYear === (string) $id ? 'selected' : '' }}>

                                    {{ $year }}

                                </option>

                            @endforeach

                        </select>

                    </div>


                    <div class="col-xl-3 col-md-6">

                        <label for="class_id" class="form-label">
                            Class
                        </label>

                        <select
                            name="class_id"
                            id="class_id"
                            class="form-select">

                            <option value="">
                                All Classes
                            </option>

                            @foreach($classes as $id => $class)

                                <option
                                    value="{{ $id }}"
                                    {{ (string) $selectedClass === (string) $id ? 'selected' : '' }}>

                                    {{ $class }}

                                </option>

                            @endforeach

                        </select>

                    </div>


                    <div class="col-xl-3 col-md-6">

                        <label for="student_id" class="form-label">
                            Student
                        </label>

                        <select
                            name="student_id"
                            id="student_id"
                            class="form-select">

                            <option value="">
                                All Students
                            </option>

                            @foreach($students as $id => $name)

                                <option
                                    value="{{ $id }}"
                                    {{ (string) $selectedStudent === (string) $id ? 'selected' : '' }}>

                                    {{ $name }}

                                </option>

                            @endforeach

                        </select>

                    </div>


                    <div class="col-xl-3 col-md-6">

                        <label for="status" class="form-label">
                            Payment Status
                        </label>

                        <select
                            name="status"
                            id="status"
                            class="form-select">

                            <option value="">
                                All Statuses
                            </option>

                            @foreach($statusOptions as $value => $label)

                                <option
                                    value="{{ $value }}"
                                    {{ (string) $selectedStatus === (string) $value ? 'selected' : '' }}>

                                    {{ $label }}

                                </option>

                            @endforeach

                        </select>

                    </div>


                    <div class="col-xl-3 col-md-6">

                        <label for="report_type" class="form-label">
                            Report Type
                        </label>

                        <select
                            name="report_type"
                            id="report_type"
                            class="form-select">

                            @foreach($reportLabels as $value => $label)

                                <option
                                    value="{{ $value }}"
                                    {{ $selectedReport === $value ? 'selected' : '' }}>

                                    {{ $label }}

                                </option>

                            @endforeach

                        </select>

                    </div>


                    <div class="col-xl-3 col-md-6">

                        <label for="payment_method" class="form-label">
                            Payment Method
                        </label>

                        <select
                            name="payment_method"
                            id="payment_method"
                            class="form-select">

                            <option value="">
                                All Methods
                            </option>

                            @foreach($paymentMethods as $method)

                                <option
                                    value="{{ $method }}"
                                    {{ $selectedMethod === $method ? 'selected' : '' }}>

                                    {{ ucfirst(str_replace('_', ' ', $method)) }}

                                </option>

                            @endforeach

                        </select>

                    </div>


                    <div class="col-xl-3 col-md-6">

                        <label for="date_from" class="form-label">
                            Date From
                        </label>

                        <input
                            type="date"
                            name="date_from"
                            id="date_from"
                            class="form-control"
                            value="{{ request('date_from') }}">

                    </div>


                    <div class="col-xl-3 col-md-6">

                        <label for="date_to" class="form-label">
                            Date To
                        </label>

                        <input
                            type="date"
                            name="date_to"
                            id="date_to"
                            class="form-control"
                            value="{{ request('date_to') }}">

                    </div>


                    <div class="col-12 d-flex justify-content-end gap-2 pt-2 no-print">

                        <a
                            href="{{ route('fee.payment.reports.index') }}"
                            class="btn btn-outline-secondary">

                            Clear

                        </a>

                        <button
                            type="submit"
                            class="btn btn-primary px-4">

                            <i class="fas fa-filter me-1"></i>

                            Apply Filters

                        </button>

                    </div>

                </div>

            </form>

        </div>

    </div>


    {{-- =========================================================
         PAYMENT SUMMARY
    ========================================================== --}}
    <div class="row g-4 mb-4">

        <div class="col-xl-6">

            <div class="report-card h-100">

                <div class="report-card-header">

                    <h5>
                        <i class="fas fa-credit-card text-primary me-2"></i>
                        Payment Method Breakdown
                    </h5>

                    <p>
                        Completed transactions grouped by method
                    </p>

                </div>

                <div class="card-body">

                    <div class="row g-3 mb-3">

                        <div class="col-sm-6">

                            <div class="summary-mini">

                                <div class="label">
                                    Transactions
                                </div>

                                <div class="value">
                                    {{ number_format($paymentSummary['total_payments'] ?? 0) }}
                                </div>

                            </div>

                        </div>


                        <div class="col-sm-6">

                            <div class="summary-mini">

                                <div class="label">
                                    Collected
                                </div>

                                <div class="value text-success">
                                    GHS {{ number_format($paymentSummary['total_amount'] ?? 0, 2) }}
                                </div>

                            </div>

                        </div>

                    </div>


                    <div class="table-responsive">

                        <table class="table table-sm table-report">

                            <thead>
                                <tr>
                                    <th>Method</th>
                                    <th class="text-end">Transactions</th>
                                    <th class="text-end">Amount</th>
                                </tr>
                            </thead>

                            <tbody>

                                @forelse(($paymentSummary['by_method'] ?? []) as $method)

                                    <tr>

                                        <td>
                                            {{ ucfirst(str_replace('_', ' ', $method->payment_method ?? 'Other')) }}
                                        </td>

                                        <td class="text-end">
                                            {{ number_format($method->count ?? 0) }}
                                        </td>

                                        <td class="text-end fw-semibold text-success">
                                            GHS {{ number_format($method->total ?? 0, 2) }}
                                        </td>

                                    </tr>

                                @empty

                                    <tr>
                                        <td colspan="3" class="text-center text-muted py-4">
                                            No payment data matches the selected filters.
                                        </td>
                                    </tr>

                                @endforelse

                            </tbody>

                        </table>

                    </div>

                </div>

            </div>

        </div>


        <div class="col-xl-6">

            <div class="report-card h-100">

                <div class="report-card-header">

                    <h5>
                        <i class="fas fa-calendar-day text-primary me-2"></i>
                        Recent Daily Collections
                    </h5>

                    <p>
                        Daily payment activity for the current selection
                    </p>

                </div>

                <div class="card-body">

                    <div class="table-responsive">

                        <table class="table table-sm table-report">

                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th class="text-end">Transactions</th>
                                    <th class="text-end">Amount</th>
                                </tr>
                            </thead>

                            <tbody>

                                @forelse(($paymentSummary['daily_summary'] ?? []) as $day)

                                    <tr>

                                        <td>
                                            {{ \Carbon\Carbon::parse($day->date)->format('d M Y') }}
                                        </td>

                                        <td class="text-end">
                                            {{ number_format($day->count ?? 0) }}
                                        </td>

                                        <td class="text-end fw-semibold text-success">
                                            GHS {{ number_format($day->total ?? 0, 2) }}
                                        </td>

                                    </tr>

                                @empty

                                    <tr>
                                        <td colspan="3" class="text-center text-muted py-4">
                                            No daily collection data available.
                                        </td>
                                    </tr>

                                @endforelse

                            </tbody>

                        </table>

                    </div>

                </div>

            </div>

        </div>

    </div>


    {{-- =========================================================
         REPORT DATA
    ========================================================== --}}
    <div class="report-card">

        <div class="report-card-header d-flex flex-wrap justify-content-between align-items-center gap-2">

            <div>

                <h5>
                    <i class="fas fa-table text-primary me-2"></i>
                    {{ $reportLabel }} Report
                </h5>

                <p>
                    {{ number_format($reportCount) }} record(s) match the selected criteria.
                </p>

            </div>

            <div class="small text-muted">
                Generated {{ now()->format('d M Y, H:i') }}
            </div>

        </div>


        <div class="card-body p-0">


            {{-- =====================================================
                 SUMMARY / DETAILED
            ====================================================== --}}

            @if(in_array($selectedReport, ['summary', 'detailed'], true))

                <div class="table-responsive">

                    <table class="table table-hover table-report align-middle mb-0">

                        <thead>

                            <tr>
                                <th>#</th>
                                <th>Student</th>
                                <th>Class</th>
                                <th>Academic Year</th>
                                <th class="text-end">Total Fees</th>
                                <th class="text-end">Paid</th>
                                <th class="text-end">Balance</th>
                                <th class="text-end">Discount</th>
                                <th class="text-end">Waiver</th>
                                <th>Status</th>
                                <th class="text-center no-print">Actions</th>
                            </tr>

                        </thead>


                        <tbody>

                        @forelse($reports as $report)

                            @php
                                $student = $report->student;

                                $studentName = $student
                                    ? trim(collect([
                                        $student->first_name ?? null,
                                        $student->middle_name ?? null,
                                        $student->last_name ?? null,
                                    ])->filter()->implode(' '))
                                    : 'N/A';

                                $studentName = $studentName ?: 'N/A';

                                $className = $report->studentClass->name
                                    ?? $report->studentClass->class_name
                                    ?? 'N/A';

                                $yearName = $report->academicYear->name
                                    ?? $report->academicYear->year_name
                                    ?? 'N/A';

                                $status = strtolower($report->status ?? 'pending');

                                $statusClass = match($status) {
                                    'paid' => 'status-paid',
                                    'partial' => 'status-partial',
                                    default => 'status-pending',
                                };

                                $statusIcon = match($status) {
                                    'paid' => 'fa-check-circle',
                                    'partial' => 'fa-clock',
                                    default => 'fa-circle-exclamation',
                                };
                            @endphp

                            <tr>

                                <td>
                                    {{ method_exists($reports, 'firstItem')
                                        ? (($reports->firstItem() ?? 1) + $loop->index)
                                        : $loop->iteration }}
                                </td>

                                <td>

                                    <div class="student-cell-name">
                                        {{ $studentName }}
                                    </div>

                                    <div class="student-cell-id">
                                        ID: {{ $student->student_id ?? 'N/A' }}
                                    </div>

                                </td>

                                <td>
                                    {{ $className }}
                                </td>

                                <td>
                                    {{ $yearName }}
                                </td>

                                <td class="text-end">
                                    GHS {{ number_format($report->total_fees ?? 0, 2) }}
                                </td>

                                <td class="text-end text-success fw-semibold">
                                    GHS {{ number_format($report->amount_paid ?? 0, 2) }}
                                </td>

                                <td class="text-end {{ ($report->balance ?? 0) > 0 ? 'text-danger' : 'text-success' }}">
                                    GHS {{ number_format($report->balance ?? 0, 2) }}
                                </td>

                                <td class="text-end">
                                    GHS {{ number_format($report->discount_applied ?? 0, 2) }}
                                </td>

                                <td class="text-end">
                                    GHS {{ number_format($report->waiver_amount ?? 0, 2) }}
                                </td>

                                <td>

                                    <span class="status-pill {{ $statusClass }}">

                                        <i class="fas {{ $statusIcon }}"></i>

                                        {{ ucfirst($status) }}

                                    </span>

                                </td>

                                <td class="text-center no-print">

                                    <div class="btn-group btn-group-sm">

                                        <a
                                            href="{{ route('fee.payment.reports.show', $report->id) }}"
                                            class="btn btn-outline-primary"
                                            title="View fee account">

                                            <i class="fas fa-eye"></i>

                                        </a>

                                        @if(isset($report->payments) && $report->payments->isNotEmpty())

                                            <a
                                                href="{{ route('fee-payments.receipt', $report->payments->first()->id) }}"
                                                class="btn btn-outline-secondary"
                                                title="View receipt">

                                                <i class="fas fa-receipt"></i>

                                            </a>

                                        @endif

                                    </div>

                                </td>

                            </tr>

                        @empty

                            <tr>

                                <td colspan="11">

                                    <div class="empty-report">

                                        <div class="empty-report-icon">
                                            <i class="fas fa-file-invoice"></i>
                                        </div>

                                        <div class="fw-bold text-dark mb-1">
                                            No Report Data
                                        </div>

                                        <div>
                                            No fee-account records match the selected filters.
                                        </div>

                                    </div>

                                </td>

                            </tr>

                        @endforelse

                        </tbody>


                        <tfoot>

                            <tr class="fw-bold">

                                <th colspan="4" class="text-end">
                                    Totals
                                </th>

                                <th class="text-end">
                                    GHS {{ number_format($totalFees, 2) }}
                                </th>

                                <th class="text-end text-success">
                                    GHS {{ number_format($totalPaid, 2) }}
                                </th>

                                <th class="text-end text-danger">
                                    GHS {{ number_format($totalBalance, 2) }}
                                </th>

                                <th class="text-end">
                                    GHS {{ number_format($totalDiscount, 2) }}
                                </th>

                                <th class="text-end">
                                    GHS {{ number_format($totalWaiver, 2) }}
                                </th>

                                <th>
                                    {{ number_format($collectionRate, 1) }}%
                                </th>

                                <th class="no-print"></th>

                            </tr>

                        </tfoot>

                    </table>

                </div>


            {{-- =====================================================
                 BY STUDENT
            ====================================================== --}}

            @elseif($selectedReport === 'student')

                <div class="table-responsive">

                    <table class="table table-hover table-report align-middle mb-0">

                        <thead>

                            <tr>
                                <th>#</th>
                                <th>Student</th>
                                <th class="text-end">Total Fees</th>
                                <th class="text-end">Amount Paid</th>
                                <th class="text-end">Balance</th>
                                <th>Collection</th>
                                <th>Status</th>
                            </tr>

                        </thead>

                        <tbody>

                        @forelse($reports as $report)

                            @php
                                $student = $report->student;

                                $studentName = $student
                                    ? trim(collect([
                                        $student->first_name ?? null,
                                        $student->middle_name ?? null,
                                        $student->last_name ?? null,
                                    ])->filter()->implode(' '))
                                    : 'N/A';

                                $studentName = $studentName ?: 'N/A';

                                $fees = (float) ($report->total_fees ?? 0);
                                $paid = (float) ($report->amount_paid ?? 0);
                                $rate = $fees > 0
                                    ? min(100, ($paid / $fees) * 100)
                                    : 0;

                                $status = $report->status ?? 'pending';
                            @endphp

                            <tr>

                                <td>{{ $loop->iteration }}</td>

                                <td>

                                    <div class="student-cell-name">
                                        {{ $studentName }}
                                    </div>

                                    <div class="student-cell-id">
                                        ID: {{ $student->student_id ?? 'N/A' }}
                                    </div>

                                </td>

                                <td class="text-end">
                                    GHS {{ number_format($fees, 2) }}
                                </td>

                                <td class="text-end text-success fw-semibold">
                                    GHS {{ number_format($paid, 2) }}
                                </td>

                                <td class="text-end text-danger">
                                    GHS {{ number_format($report->balance ?? 0, 2) }}
                                </td>

                                <td class="collection-progress">

                                    <div class="d-flex justify-content-between small mb-1">
                                        <span>{{ number_format($rate, 1) }}%</span>
                                    </div>

                                    <div class="progress">

                                        <div
                                            class="progress-bar bg-success"
                                            style="width: {{ $rate }}%">
                                        </div>

                                    </div>

                                </td>

                                <td>

                                    <span class="status-pill
                                        {{ $status === 'paid'
                                            ? 'status-paid'
                                            : ($status === 'partial'
                                                ? 'status-partial'
                                                : 'status-pending') }}">

                                        {{ ucfirst($status) }}

                                    </span>

                                </td>

                            </tr>

                        @empty

                            <tr>

                                <td colspan="7">

                                    <div class="empty-report">

                                        <div class="empty-report-icon">
                                            <i class="fas fa-user-graduate"></i>
                                        </div>

                                        <div class="fw-bold text-dark mb-1">
                                            No Student Results
                                        </div>

                                        <div>
                                            No students match the selected report filters.
                                        </div>

                                    </div>

                                </td>

                            </tr>

                        @endforelse

                        </tbody>

                    </table>

                </div>


            {{-- =====================================================
                 BY CLASS
            ====================================================== --}}

            @elseif($selectedReport === 'class')

                <div class="table-responsive">

                    <table class="table table-hover table-report align-middle mb-0">

                        <thead>

                            <tr>
                                <th>#</th>
                                <th>Class</th>
                                <th>Academic Year</th>
                                <th class="text-end">Students</th>
                                <th class="text-end">Total Fees</th>
                                <th class="text-end">Paid</th>
                                <th class="text-end">Balance</th>
                                <th>Collection Rate</th>
                            </tr>

                        </thead>

                        <tbody>

                        @forelse($reports as $report)

                            @php
                                $fees = (float) ($report->total_fees ?? 0);
                                $paid = (float) ($report->amount_paid ?? 0);
                                $rate = (float) ($report->collection_rate ?? 0);
                            @endphp

                            <tr>

                                <td>{{ $loop->iteration }}</td>

                                <td class="fw-semibold">

                                    {{ $report->class->name
                                        ?? $report->class->class_name
                                        ?? 'N/A' }}

                                </td>

                                <td>

                                    {{ $report->academicYear->name
                                        ?? $report->academicYear->year_name
                                        ?? 'N/A' }}

                                </td>

                                <td class="text-end">
                                    {{ number_format($report->total_students ?? 0) }}
                                </td>

                                <td class="text-end">
                                    GHS {{ number_format($fees, 2) }}
                                </td>

                                <td class="text-end text-success fw-semibold">
                                    GHS {{ number_format($paid, 2) }}
                                </td>

                                <td class="text-end text-danger">
                                    GHS {{ number_format($report->balance ?? 0, 2) }}
                                </td>

                                <td class="collection-progress">

                                    <div class="d-flex justify-content-between small mb-1">
                                        <span>{{ number_format($rate, 1) }}%</span>
                                    </div>

                                    <div class="progress">

                                        <div
                                            class="progress-bar bg-primary"
                                            style="width: {{ min(100, $rate) }}%">
                                        </div>

                                    </div>

                                </td>

                            </tr>

                        @empty

                            <tr>

                                <td colspan="8">

                                    <div class="empty-report">

                                        <div class="empty-report-icon">
                                            <i class="fas fa-school"></i>
                                        </div>

                                        <div class="fw-bold text-dark mb-1">
                                            No Class Results
                                        </div>

                                        <div>
                                            No class-level fee data is available.
                                        </div>

                                    </div>

                                </td>

                            </tr>

                        @endforelse

                        </tbody>

                    </table>

                </div>


            {{-- =====================================================
                 PAYMENT SUMMARY
            ====================================================== --}}

            @elseif($selectedReport === 'payment_summary')

                <div class="table-responsive">

                    <table class="table table-hover table-report align-middle mb-0">

                        <thead>

                            <tr>
                                <th>#</th>
                                <th>Date</th>
                                <th class="text-end">Accounts</th>
                                <th class="text-end">Total Fees</th>
                                <th class="text-end">Paid</th>
                                <th class="text-end">Balance</th>
                            </tr>

                        </thead>

                        <tbody>

                        @forelse($reports as $report)

                            <tr>

                                <td>{{ $loop->iteration }}</td>

                                <td>
                                    {{ \Carbon\Carbon::parse($report->date)->format('d M Y') }}
                                </td>

                                <td class="text-end">
                                    {{ number_format($report->count ?? 0) }}
                                </td>

                                <td class="text-end">
                                    GHS {{ number_format($report->total_fees ?? 0, 2) }}
                                </td>

                                <td class="text-end text-success fw-semibold">
                                    GHS {{ number_format($report->total_paid ?? 0, 2) }}
                                </td>

                                <td class="text-end text-danger">
                                    GHS {{ number_format($report->total_balance ?? 0, 2) }}
                                </td>

                            </tr>

                        @empty

                            <tr>

                                <td colspan="6">

                                    <div class="empty-report">

                                        <div class="empty-report-icon">
                                            <i class="fas fa-calendar-xmark"></i>
                                        </div>

                                        <div class="fw-bold text-dark mb-1">
                                            No Period Data
                                        </div>

                                        <div>
                                            No payment summary data is available.
                                        </div>

                                    </div>

                                </td>

                            </tr>

                        @endforelse

                        </tbody>

                    </table>

                </div>

            @endif


            {{-- =====================================================
                 PAGINATION
            ====================================================== --}}

            @if(method_exists($reports, 'links'))

                <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 p-3 border-top">

                    <div class="small text-muted">

                        Showing
                        {{ $reports->firstItem() ?? 0 }}
                        to
                        {{ $reports->lastItem() ?? 0 }}
                        of
                        {{ $reports->total() ?? 0 }}
                        records

                    </div>

                    <div>

                        {{ $reports->appends(request()->query())->links('pagination::bootstrap-5') }}

                    </div>

                </div>

            @endif

        </div>

    </div>


    {{-- =========================================================
         QUICK ACTIONS
    ========================================================== --}}
    <div class="report-card quick-actions mt-4 no-print">

        <div class="report-card-header">

            <h5>
                <i class="fas fa-bolt text-warning me-2"></i>
                Quick Actions
            </h5>

            <p>
                Jump directly to common fee-management reports.
            </p>

        </div>

        <div class="card-body">

            <div class="d-flex flex-wrap gap-2">

                <a
                    href="{{ route('fee.payment.reports.outstanding') }}"
                    class="btn btn-outline-danger">

                    <i class="fas fa-triangle-exclamation me-1"></i>
                    Outstanding Fees

                </a>

                <a
                    href="{{ route('fee.payment.reports.payment-history') }}"
                    class="btn btn-outline-info">

                    <i class="fas fa-clock-rotate-left me-1"></i>
                    Payment History

                </a>

                <a
                    href="{{ route('fee.payment.reports.index', ['report_type' => 'detailed']) }}"
                    class="btn btn-outline-secondary">

                    <i class="fas fa-list me-1"></i>
                    

                </a>
                <a
                    href="{{ route('fee.payment.reports.school-overview') }}"
                    class="btn btn-primary">
                    <i class="fas fa-chart-pie me-1"></i>
                    Detailed Report
                </a>

                <a
                    href="{{ route('fee-payments.index') }}"
                    class="btn btn-outline-primary">

                    <i class="fas fa-money-bill-wave me-1"></i>
                    All Payments

                </a>

                <button
                    type="button"
                    class="btn btn-outline-success"
                    onclick="window.location.reload()">

                    <i class="fas fa-sync-alt me-1"></i>
                    Refresh

                </button>

            </div>

        </div>

    </div>

</div>


{{-- =========================================================
     CHARTS
     Chart.js is already loaded by layouts.master.
========================================================= --}}
@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {

    if (typeof Chart === 'undefined') {
        console.warn('Chart.js is not available.');
        return;
    }

    const chartFont = {
        family: 'Inter, Arial, sans-serif'
    };

    const tooltipCurrency = function(value) {
        return 'GHS ' + Number(value || 0).toLocaleString(undefined, {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2
        });
    };

    /*
    |--------------------------------------------------------------------------
    | COLLECTION POSITION DONUT
    |--------------------------------------------------------------------------
    */

    const collectionCanvas =
        document.getElementById('collectionDonutChart');

    if (collectionCanvas) {

        new Chart(collectionCanvas, {
            type: 'doughnut',

            data: {
                labels: ['Paid', 'Outstanding'],

                datasets: [{
                    data: @json($collectionChartValues),
                    backgroundColor: [
                        '#198754',
                        '#dc3545'
                    ],
                    borderWidth: 0,
                    hoverOffset: 7
                }]
            },

            options: {
                responsive: true,
                maintainAspectRatio: false,

                cutout: '68%',

                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            usePointStyle: true,
                            padding: 18,
                            font: chartFont
                        }
                    },

                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return context.label + ': ' +
                                    tooltipCurrency(context.raw);
                            }
                        }
                    }
                }
            }
        });
    }


    /*
    |--------------------------------------------------------------------------
    | PAYMENT METHOD DONUT
    |--------------------------------------------------------------------------
    */

    const methodCanvas =
        document.getElementById('paymentMethodChart');

    if (methodCanvas) {

        new Chart(methodCanvas, {
            type: 'doughnut',

            data: {
                labels: @json($methodLabels),

                datasets: [{
                    data: @json($methodAmounts),

                    backgroundColor: [
                        '#0d6efd',
                        '#198754',
                        '#ffc107',
                        '#6f42c1',
                        '#fd7e14',
                        '#20c997',
                        '#dc3545',
                        '#6c757d'
                    ],

                    borderWidth: 0,
                    hoverOffset: 6
                }]
            },

            options: {
                responsive: true,
                maintainAspectRatio: false,

                cutout: '65%',

                plugins: {

                    legend: {
                        position: 'bottom',
                        labels: {
                            usePointStyle: true,
                            padding: 14,
                            font: chartFont
                        }
                    },

                    tooltip: {
                        callbacks: {
                            label: function(context) {

                                const value =
                                    Number(context.raw || 0);

                                return context.label + ': ' +
                                    tooltipCurrency(value);

                            }
                        }
                    }
                }
            }
        });
    }


    /*
    |--------------------------------------------------------------------------
    | ACCOUNT STATUS DONUT
    |--------------------------------------------------------------------------
    */

    const statusCanvas =
        document.getElementById('statusDonutChart');

    if (statusCanvas) {

        new Chart(statusCanvas, {
            type: 'doughnut',

            data: {
                labels: @json($statusChartLabels),

                datasets: [{
                    data: @json($statusChartValues),

                    backgroundColor: [
                        '#198754',
                        '#ffc107',
                        '#dc3545'
                    ],

                    borderWidth: 0,
                    hoverOffset: 7
                }]
            },

            options: {
                responsive: true,
                maintainAspectRatio: false,

                cutout: '65%',

                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            usePointStyle: true,
                            padding: 14,
                            font: chartFont
                        }
                    },

                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return context.label + ': ' +
                                    Number(context.raw || 0)
                                        .toLocaleString();
                            }
                        }
                    }
                }
            }
        });
    }


    /*
    |--------------------------------------------------------------------------
    | DAILY COLLECTION TREND
    |--------------------------------------------------------------------------
    */

    const trendCanvas =
        document.getElementById('collectionTrendChart');

    if (trendCanvas) {

        new Chart(trendCanvas, {
            type: 'line',

            data: {
                labels: @json($dailyLabels),

                datasets: [{
                    label: 'Collections',

                    data: @json($dailyAmounts),

                    borderColor: '#1456a0',

                    backgroundColor: 'rgba(20, 86, 160, 0.10)',

                    fill: true,

                    tension: .35,

                    pointRadius: 3,

                    pointHoverRadius: 5,

                    borderWidth: 2
                }]
            },

            options: {
                responsive: true,
                maintainAspectRatio: false,

                interaction: {
                    intersect: false,
                    mode: 'index'
                },

                scales: {
                    y: {
                        beginAtZero: true,

                        ticks: {
                            callback: function(value) {
                                return 'GHS ' +
                                    Number(value)
                                        .toLocaleString();
                            }
                        },

                        grid: {
                            color: 'rgba(0,0,0,.06)'
                        }
                    },

                    x: {
                        grid: {
                            display: false
                        }
                    }
                },

                plugins: {

                    legend: {
                        display: false
                    },

                    tooltip: {
                        callbacks: {
                            title: function(context) {
                                return context[0]?.label || '';
                            },

                            label: function(context) {
                                return 'Collected: ' +
                                    tooltipCurrency(context.raw);
                            }
                        }
                    }
                }
            }
        });
    }

});
</script>
@endpush

@endsection
