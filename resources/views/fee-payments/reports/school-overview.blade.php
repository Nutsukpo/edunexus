{{-- resources/views/fee-payments/reports/school-overview.blade.php --}}
@extends('layouts.master')

@section('title', 'School Fee Overview')

@section('content')

@php
    /*
    |--------------------------------------------------------------------------
    | Safe page defaults
    |--------------------------------------------------------------------------
    | These values are supplied by FeePaymentReportController.
    */
    $academicYears = $academicYears ?? [];
    $terms = $terms ?? collect();
    $classReports = collect($classReports ?? []);

    $selectedAcademicYear = $selectedAcademicYear
        ?? request()->query('academic_year_id', 'all');

    $selectedTerm = $selectedTerm
        ?? request()->query('term_id', 'all');

    $totalExpectedFees = (float) ($totalExpectedFees ?? 0);
    $totalFeesPaid = (float) ($totalFeesPaid ?? 0);
    $totalOutstandingFees = (float) ($totalOutstandingFees ?? 0);
    $schoolCollectionRate = (float) ($schoolCollectionRate ?? 0);
    $totalClassCount = (int) ($totalClassCount ?? $classReports->count());

    $selectedYearLabel = 'All Academic Years';
    $selectedTermLabel = 'All Terms';

    if ($selectedAcademicYear !== null && $selectedAcademicYear !== 'all') {
        $selectedYearLabel = $academicYears[$selectedAcademicYear]
            ?? $academicYears[(string) $selectedAcademicYear]
            ?? 'Selected Academic Year';
    }

    if ($selectedTerm !== null && $selectedTerm !== 'all') {
        $selectedTermModel = $classReports->isNotEmpty()
            ? collect($terms)->first(
                fn ($term) => (string) ($term->id ?? '') === (string) $selectedTerm
            )
            : collect($terms)->first(
                fn ($term) => (string) ($term->id ?? '') === (string) $selectedTerm
            );

        if ($selectedTermModel) {
            $selectedTermLabel = $selectedTermModel->name
                ?? $selectedTermModel->term_name
                ?? ('Term ' . ($selectedTermModel->id ?? ''));
        }
    }

    $periodLabel = $selectedYearLabel;

    if ($selectedTerm !== null && $selectedTerm !== 'all') {
        $periodLabel .= ' • ' . $selectedTermLabel;
    }

    $classLabels = [];
    $classExpected = [];
    $classPaid = [];
    $classOutstanding = [];

    foreach ($classReports as $classReport) {
        $classLabels[] = $classReport->class_name ?? 'Unknown Class';
        $classExpected[] = (float) ($classReport->expected_fees ?? 0);
        $classPaid[] = (float) ($classReport->fees_paid ?? 0);
        $classOutstanding[] = (float) ($classReport->outstanding_fees ?? 0);
    }

    $schoolDonutValues = [
        $totalFeesPaid,
        $totalOutstandingFees,
    ];
@endphp

<style>
    .school-fee-page {
        --primary: #1456a0;
        --primary-dark: #0b3567;
        --success: #198754;
        --danger: #dc3545;
        --warning: #c58a00;
        --text: #172033;
        --muted: #667085;
        --border: #e5eaf0;
        --soft: #f7f9fc;
    }

    .overview-hero {
        background:
            radial-gradient(circle at 85% 15%, rgba(255,255,255,.16), transparent 27%),
            linear-gradient(135deg, var(--primary-dark), var(--primary), #2376bd);
        color: #fff;
        border-radius: 20px;
        padding: 1.6rem;
        box-shadow: 0 14px 35px rgba(20,86,160,.18);
    }

    .overview-hero .eyebrow {
        font-size: .68rem;
        text-transform: uppercase;
        letter-spacing: .14em;
        opacity: .72;
        font-weight: 800;
    }

    .overview-hero h1 {
        margin: .35rem 0 .35rem;
        font-size: clamp(1.55rem, 3vw, 2.15rem);
        font-weight: 800;
    }

    .overview-hero p {
        margin: 0;
        max-width: 760px;
        font-size: .88rem;
        opacity: .8;
    }

    .overview-card,
    .chart-card,
    .class-card {
        background: #fff;
        border: 1px solid var(--border);
        border-radius: 16px;
        box-shadow: 0 6px 20px rgba(16,24,40,.055);
    }

    .metric-card {
        height: 100%;
    }

    .metric-icon {
        width: 45px;
        height: 45px;
        border-radius: 13px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 1rem;
    }

    .metric-label {
        color: var(--muted);
        font-size: .68rem;
        text-transform: uppercase;
        letter-spacing: .06em;
        font-weight: 800;
    }

    .metric-value {
        color: var(--text);
        font-size: 1.38rem;
        font-weight: 800;
        margin-top: .3rem;
    }

    .metric-note {
        color: var(--muted);
        font-size: .7rem;
        margin-top: .25rem;
    }

    .section-title {
        color: var(--text);
        font-size: .98rem;
        font-weight: 800;
    }

    .section-subtitle {
        color: var(--muted);
        font-size: .72rem;
    }

    .chart-wrapper {
        position: relative;
        height: 315px;
    }

    .table-report {
        margin: 0;
    }

    .table-report thead th {
        background: #f7f9fc;
        color: #475467;
        font-size: .67rem;
        text-transform: uppercase;
        letter-spacing: .04em;
        white-space: nowrap;
        border-bottom: 1px solid var(--border);
        padding: .85rem .75rem;
    }

    .table-report td {
        padding: .8rem .75rem;
        vertical-align: middle;
        color: #344054;
        font-size: .8rem;
    }

    .table-report tbody tr:hover {
        background: #fbfcfe;
    }

    .rate-wrap {
        min-width: 130px;
    }

    .rate-wrap .progress {
        height: 7px;
        background: #edf1f5;
        border-radius: 99px;
    }

    .rate-value {
        font-size: .7rem;
        font-weight: 700;
    }

    .status-badge {
        display: inline-flex;
        align-items: center;
        border-radius: 999px;
        padding: .35rem .65rem;
        font-size: .65rem;
        font-weight: 800;
    }

    .status-excellent {
        color: #137333;
        background: #e9f7ef;
    }

    .status-good {
        color: #2563eb;
        background: #eaf2ff;
    }

    .status-attention {
        color: #a16207;
        background: #fff5d9;
    }

    .status-critical {
        color: #b42318;
        background: #fdecec;
    }

    .legend-box {
        border: 1px solid var(--border);
        border-radius: 12px;
        padding: .9rem;
        background: #fbfcfe;
    }

    .legend-dot {
        width: 10px;
        height: 10px;
        border-radius: 50%;
        display: inline-block;
        margin-right: .4rem;
    }

    .filter-panel {
        background: #fff;
        border: 1px solid var(--border);
        border-radius: 16px;
        box-shadow: 0 6px 20px rgba(16,24,40,.04);
    }

    .filter-panel .form-label {
        font-size: .74rem;
        font-weight: 700;
        color: var(--text);
    }

    .filter-panel .form-select {
        min-height: 43px;
        border-radius: 10px;
        border-color: var(--border);
    }

    .top-class {
        background: linear-gradient(180deg, #fff, #fbfdff);
        border: 1px solid var(--border);
        border-radius: 13px;
        padding: .9rem;
    }

    .top-class-name {
        font-weight: 750;
        color: var(--text);
    }

    .top-class-rate {
        font-size: .78rem;
        font-weight: 800;
    }

    @media (max-width: 767.98px) {
        .overview-hero {
            padding: 1.2rem;
        }

        .chart-wrapper {
            height: 270px;
        }
    }

    @media print {
        .no-print,
        .filter-panel,
        .overview-hero .hero-actions {
            display: none !important;
        }

        .overview-hero {
            color: #111 !important;
            background: #fff !important;
            border: 1px solid #aaa;
            box-shadow: none !important;
        }

        .overview-card,
        .chart-card,
        .class-card {
            box-shadow: none !important;
        }
    }
</style>


<div class="container-fluid py-3 school-fee-page">

    {{-- =========================================================
         HEADER
    ========================================================== --}}
    <div class="overview-hero mb-4">

        <div class="d-flex flex-column flex-xl-row justify-content-between align-items-xl-center gap-3">

            <div>
                <div class="eyebrow">School Finance • Fee Collection</div>

                <h1>School Fee Overview</h1>

                <p>
                    Monitor the school's total expected fees, collections and
                    outstanding balances, with a complete class-by-class breakdown.
                </p>
            </div>

            <div class="hero-actions d-flex flex-wrap gap-2 no-print">

                <button
                    type="button"
                    class="btn btn-light btn-sm"
                    onclick="window.print()">

                    <i class="fas fa-print me-1"></i>
                    Print Report

                </button>

                <a
                    href="{{ route('fee.payment.reports.export.pdf', request()->query()) }}"
                    class="btn btn-light btn-sm">

                    <i class="fas fa-file-pdf text-danger me-1"></i>
                    Export PDF

                </a>

                <a
                    href="{{ route('fee.payment.reports.export.excel', request()->query()) }}"
                    class="btn btn-light btn-sm">

                    <i class="fas fa-file-excel text-success me-1"></i>
                    Export Excel

                </a>

            </div>

        </div>

    </div>


    {{-- =========================================================
         ACADEMIC PERIOD FILTER
    ========================================================== --}}
    <div class="filter-panel mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('fee.payment.reports.school-overview') }}" id="periodFilterForm">
                <div class="row g-3 align-items-end">
                    <div class="col-lg-4 col-md-6">
                        <label for="academic_year_id" class="form-label">Academic Year</label>
                        <select name="academic_year_id" id="academic_year_id" class="form-select">
                            <option value="all" {{ (string) $selectedAcademicYear === 'all' ? 'selected' : '' }}>
                                All Academic Years
                            </option>
                            @foreach($academicYears as $id => $year)
                                <option value="{{ $id }}" {{ (string) $selectedAcademicYear === (string) $id ? 'selected' : '' }}>
                                    {{ $year }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-lg-4 col-md-6">
                        <label for="term_id" class="form-label">Term</label>
                        <select name="term_id" id="term_id" class="form-select"
                            {{ (!$selectedAcademicYear || $selectedAcademicYear === 'all') ? 'disabled' : '' }}>
                            <option value="all">All Terms</option>
                            @foreach(($terms ?? collect()) as $term)
                                @php
                                    $termName = $term->name ?? $term->term_name ?? ('Term ' . $term->id);
                                @endphp
                                <option value="{{ $term->id }}" {{ (string) $selectedTerm === (string) $term->id ? 'selected' : '' }}>
                                    {{ $termName }}
                                </option>
                            @endforeach
                        </select>
                        <div class="form-text" id="termHelp">
                            Select an academic year to view its configured terms.
                        </div>
                    </div>

                    <div class="col-lg-4 col-md-12">
                        <div class="small text-muted mb-1">Report scope</div>
                        <div class="fw-semibold mb-2">{{ $periodLabel }}</div>
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="fas fa-filter me-1"></i>
                            Apply Academic Period
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    {{-- =========================================================
         SCHOOL-WIDE KPI CARDS
    ========================================================== --}}
    <div class="row g-3 mb-4">

        <div class="col-xl-3 col-md-6">

            <div class="overview-card metric-card">

                <div class="card-body p-4">

                    <div class="d-flex justify-content-between align-items-start">

                        <div>

                            <div class="metric-label">
                                Total Expected Fees
                            </div>

                            <div class="metric-value">
                                GHS {{ number_format($totalExpectedFees, 2) }}
                            </div>

                            <div class="metric-note">
                                Fees billed for the selected academic period
                            </div>

                        </div>

                        <span class="metric-icon bg-primary-subtle text-primary">
                            <i class="fas fa-file-invoice-dollar"></i>
                        </span>

                    </div>

                </div>

            </div>

        </div>


        <div class="col-xl-3 col-md-6">

            <div class="overview-card metric-card">

                <div class="card-body p-4">

                    <div class="d-flex justify-content-between align-items-start">

                        <div>

                            <div class="metric-label">
                                Total Fees Paid
                            </div>

                            <div class="metric-value text-success">
                                GHS {{ number_format($totalFeesPaid, 2) }}
                            </div>

                            <div class="metric-note">
                                {{ number_format($schoolCollectionRate, 1) }}% collected
                            </div>

                        </div>

                        <span class="metric-icon bg-success-subtle text-success">
                            <i class="fas fa-circle-check"></i>
                        </span>

                    </div>

                </div>

            </div>

        </div>


        <div class="col-xl-3 col-md-6">

            <div class="overview-card metric-card">

                <div class="card-body p-4">

                    <div class="d-flex justify-content-between align-items-start">

                        <div>

                            <div class="metric-label">
                                Outstanding Fees
                            </div>

                            <div class="metric-value text-danger">
                                GHS {{ number_format($totalOutstandingFees, 2) }}
                            </div>

                            <div class="metric-note">
                                Still to be collected
                            </div>

                        </div>

                        <span class="metric-icon bg-danger-subtle text-danger">
                            <i class="fas fa-triangle-exclamation"></i>
                        </span>

                    </div>

                </div>

            </div>

        </div>


        <div class="col-xl-3 col-md-6">

            <div class="overview-card metric-card">

                <div class="card-body p-4">

                    <div class="d-flex justify-content-between align-items-start">

                        <div>

                            <div class="metric-label">
                                Collection Rate
                            </div>

                            <div class="metric-value">
                                {{ number_format($schoolCollectionRate, 1) }}%
                            </div>

                            <div class="metric-note">
                                {{ number_format($totalClassCount) }} active classes
                            </div>

                        </div>

                        <span class="metric-icon bg-info-subtle text-info">
                            <i class="fas fa-chart-line"></i>
                        </span>

                    </div>

                </div>

            </div>

        </div>

    </div>


    {{-- =========================================================
         VISUAL ANALYTICS
    ========================================================== --}}
    <div class="row g-4 mb-4">

        {{-- SCHOOL DONUT --}}
        <div class="col-xl-4 col-lg-6">

            <div class="chart-card h-100">

                <div class="card-header bg-white border-0 p-3">

                    <div class="section-title">
                        <i class="fas fa-chart-pie text-primary me-2"></i>
                        School Collection Position
                    </div>

                    <div class="section-subtitle">
                        Fees paid against outstanding fees
                    </div>

                </div>

                <div class="card-body">

                    <div class="chart-wrapper">
                        <canvas id="schoolCollectionDonut"></canvas>
                    </div>

                    <div class="row g-2">

                        <div class="col-6">
                            <div class="legend-box">
                                <div class="small text-muted">
                                    Total Paid
                                </div>

                                <div class="fw-bold text-success">
                                    GHS {{ number_format($totalFeesPaid, 2) }}
                                </div>
                            </div>
                        </div>

                        <div class="col-6">
                            <div class="legend-box">
                                <div class="small text-muted">
                                    Outstanding
                                </div>

                                <div class="fw-bold text-danger">
                                    GHS {{ number_format($totalOutstandingFees, 2) }}
                                </div>
                            </div>
                        </div>

                    </div>

                </div>

            </div>

        </div>


        {{-- CLASS BAR --}}
        <div class="col-xl-8 col-lg-6">

            <div class="chart-card h-100">

                <div class="card-header bg-white border-0 p-3">

                    <div class="section-title">
                        <i class="fas fa-chart-column text-primary me-2"></i>
                        Class-by-Class Fee Performance
                    </div>

                    <div class="section-subtitle">
                        Expected fees, paid fees and outstanding balances
                    </div>

                </div>

                <div class="card-body">

                    <div class="chart-wrapper">
                        <canvas id="classFeeChart"></canvas>
                    </div>

                </div>

            </div>

        </div>

    </div>


    {{-- =========================================================
         TOP / LOW COLLECTION CLASSES
    ========================================================== --}}
    @php
        $rankedClasses = collect($classReports)
            ->filter(fn ($class) => (float) ($class->expected_fees ?? 0) > 0)
            ->sortByDesc(fn ($class) => (float) ($class->collection_rate ?? 0))
            ->values();

        $topClasses = $rankedClasses->take(5);
        $attentionClasses = $rankedClasses->sortBy('collection_rate')->take(5);
    @endphp

    <div class="row g-4 mb-4">

        <div class="col-xl-6">

            <div class="class-card h-100">

                <div class="card-header bg-white border-0 p-3">

                    <div class="section-title">
                        <i class="fas fa-arrow-trend-up text-success me-2"></i>
                        Highest Collection Rates
                    </div>

                    <div class="section-subtitle">
                        Classes with the strongest fee collection performance
                    </div>

                </div>

                <div class="card-body">

                    <div class="d-grid gap-2">

                        @forelse($topClasses as $class)

                            <div class="top-class">

                                <div class="d-flex justify-content-between align-items-center gap-3">

                                    <div>
                                        <div class="top-class-name">
                                            {{ $class->class_name }}
                                        </div>

                                        <small class="text-muted">
                                            Paid:
                                            GHS {{ number_format($class->fees_paid, 2) }}
                                        </small>
                                    </div>

                                    <div class="top-class-rate text-success">
                                        {{ number_format($class->collection_rate, 1) }}%
                                    </div>

                                </div>

                            </div>

                        @empty

                            <div class="text-center text-muted py-4">
                                No class collection data available.
                            </div>

                        @endforelse

                    </div>

                </div>

            </div>

        </div>


        <div class="col-xl-6">

            <div class="class-card h-100">

                <div class="card-header bg-white border-0 p-3">

                    <div class="section-title">
                        <i class="fas fa-triangle-exclamation text-warning me-2"></i>
                        Classes Requiring Attention
                    </div>

                    <div class="section-subtitle">
                        Classes with the lowest collection rates
                    </div>

                </div>

                <div class="card-body">

                    <div class="d-grid gap-2">

                        @forelse($attentionClasses as $class)

                            <div class="top-class">

                                <div class="d-flex justify-content-between align-items-center gap-3">

                                    <div>
                                        <div class="top-class-name">
                                            {{ $class->class_name }}
                                        </div>

                                        <small class="text-muted">
                                            Outstanding:
                                            GHS {{ number_format($class->outstanding_fees, 2) }}
                                        </small>
                                    </div>

                                    <div class="top-class-rate text-danger">
                                        {{ number_format($class->collection_rate, 1) }}%
                                    </div>

                                </div>

                            </div>

                        @empty

                            <div class="text-center text-muted py-4">
                                No class collection data available.
                            </div>

                        @endforelse

                    </div>

                </div>

            </div>

        </div>

    </div>


    {{-- =========================================================
         CLASS-BY-CLASS MASTER TABLE
    ========================================================== --}}
    <div class="overview-card mb-4">

        <div class="card-header bg-white border-0 p-3">

            <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-2">

                <div>

                    <div class="section-title">
                        <i class="fas fa-table text-primary me-2"></i>
                        Class-by-Class Fee Summary
                    </div>

                    <div class="section-subtitle">
                        Expected fees are calculated from the selected academic period and the applicable class bill amount multiplied by the active student count.
                    </div>

                </div>

                <div class="small text-muted">
                    {{ number_format($totalClassCount) }} active classes
                </div>

            </div>

        </div>


        <div class="table-responsive">

            <table class="table table-hover table-report align-middle mb-0">

                <thead>

                    <tr>

                        <th>#</th>
                        <th>Class</th>
                        <th class="text-end">Student Accounts</th>
                        <th class="text-end">Bill / Student</th>
                        <th class="text-end">Expected Fees</th>
                        <th class="text-end">Fees Paid</th>
                        <th class="text-end">Outstanding</th>
                        <th>Collection Rate</th>
                        <th>Status</th>

                    </tr>

                </thead>


                <tbody>

                @forelse($classReports as $class)

                    @php
                        $rate = (float) $class->collection_rate;

                        if ($rate >= 80) {
                            $statusText = 'Excellent';
                            $statusClass = 'status-excellent';
                        } elseif ($rate >= 60) {
                            $statusText = 'Good';
                            $statusClass = 'status-good';
                        } elseif ($rate >= 40) {
                            $statusText = 'Attention';
                            $statusClass = 'status-attention';
                        } else {
                            $statusText = 'Critical';
                            $statusClass = 'status-critical';
                        }
                    @endphp

                    <tr>

                        <td>
                            {{ $loop->iteration }}
                        </td>

                        <td class="fw-semibold">
                            {{ $class->class_name }}
                        </td>

                        <td class="text-end">
                            {{ number_format($class->student_accounts) }}
                        </td>

                        <td class="text-end">
                            GHS {{ number_format($class->bill_amount_per_student, 2) }}
                        </td>

                        <td class="text-end fw-semibold">
                            GHS {{ number_format($class->expected_fees, 2) }}
                        </td>

                        <td class="text-end text-success fw-semibold">
                            GHS {{ number_format($class->fees_paid, 2) }}
                        </td>

                        <td class="text-end text-danger fw-semibold">
                            GHS {{ number_format($class->outstanding_fees, 2) }}
                        </td>

                        <td class="rate-wrap">

                            <div class="d-flex justify-content-between mb-1">

                                <span class="rate-value">
                                    {{ number_format($rate, 1) }}%
                                </span>

                            </div>

                            <div class="progress">

                                <div
                                    class="progress-bar
                                        {{ $rate >= 80
                                            ? 'bg-success'
                                            : ($rate >= 60
                                                ? 'bg-primary'
                                                : ($rate >= 40
                                                    ? 'bg-warning'
                                                    : 'bg-danger')) }}"
                                    style="width: {{ min(100, max(0, $rate)) }}%;">
                                </div>

                            </div>

                        </td>

                        <td>

                            <span class="status-badge {{ $statusClass }}">
                                {{ $statusText }}
                            </span>

                        </td>

                    </tr>

                @empty

                    <tr>

                        <td colspan="9">

                            <div class="text-center py-5 text-muted">

                                <i class="fas fa-school fs-1 mb-3 d-block"></i>

                                <div class="fw-bold text-dark mb-1">
                                    No Fee Records Found
                                </div>

                                <div>
                                    There are no fee records for the selected academic year and term.
                                </div>

                            </div>

                        </td>

                    </tr>

                @endforelse

                </tbody>


                <tfoot>

                    <tr class="fw-bold">

                        <th colspan="3" class="text-end">
                            SCHOOL TOTAL
                        </th>

                        <th class="text-end">—</th>

                        <th class="text-end">
                            GHS {{ number_format($totalExpectedFees, 2) }}
                        </th>

                        <th class="text-end text-success">
                            GHS {{ number_format($totalFeesPaid, 2) }}
                        </th>

                        <th class="text-end text-danger">
                            GHS {{ number_format($totalOutstandingFees, 2) }}
                        </th>

                        <th>
                            {{ number_format($schoolCollectionRate, 1) }}%
                        </th>

                        <th>
                            <span class="status-badge status-good">
                                Overall
                            </span>
                        </th>

                    </tr>

                </tfoot>

            </table>

        </div>

    </div>


    {{-- =========================================================
         QUICK ACTIONS
    ========================================================== --}}
    <div class="overview-card no-print">

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
                    href="{{ route('fee-payments.index') }}"
                    class="btn btn-outline-secondary">

                    <i class="fas fa-money-bill-wave me-1"></i>
                    All Payments

                </a>

            </div>

        </div>

    </div>

</div>


@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const academicYearSelect = document.getElementById('academic_year_id');
    const termSelect = document.getElementById('term_id');
    const termHelp = document.getElementById('termHelp');

    if (!academicYearSelect || !termSelect) {
        return;
    }

    const termsUrlTemplate = @json(
        route('settings.academic-years.terms', ['academicYear' => '__YEAR__'])
    );

    function setAllTerms(disabled) {
        termSelect.innerHTML = '<option value="all">All Terms</option>';
        termSelect.value = 'all';
        termSelect.disabled = disabled;

        if (termHelp) {
            termHelp.textContent = disabled
                ? 'Select an academic year to view its configured terms.'
                : 'All terms are included in this report.';
        }
    }

    function loadTerms(academicYearId) {
        if (!academicYearId || academicYearId === 'all') {
            setAllTerms(true);
            return;
        }

        termSelect.disabled = true;
        termSelect.innerHTML = '<option value="">Loading terms...</option>';

        if (termHelp) {
            termHelp.textContent = 'Loading terms for the selected academic year...';
        }

        const url = termsUrlTemplate.replace(
            '__YEAR__',
            encodeURIComponent(academicYearId)
        );

        fetch(url, {
            method: 'GET',
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
            .then(function (response) {
                if (!response.ok) {
                    throw new Error('Unable to load terms.');
                }

                return response.json();
            })
            .then(function (payload) {
                const loadedTerms = Array.isArray(payload)
                    ? payload
                    : (
                        Array.isArray(payload.terms)
                            ? payload.terms
                            : (
                                Array.isArray(payload.data)
                                    ? payload.data
                                    : []
                            )
                    );

                termSelect.innerHTML =
                    '<option value="all">All Terms</option>';

                loadedTerms.forEach(function (item) {
                    if (!item || item.id === undefined || item.id === null) {
                        return;
                    }

                    const option = document.createElement('option');
                    option.value = item.id;
                    option.textContent =
                        item.name ||
                        item.term_name ||
                        ('Term ' + item.id);

                    termSelect.appendChild(option);
                });

                termSelect.disabled = false;

                if (termHelp) {
                    termHelp.textContent = loadedTerms.length
                        ? 'Select a term or keep All Terms.'
                        : 'No terms are configured for this academic year.';
                }
            })
            .catch(function (error) {
                console.error('Academic terms could not be loaded:', error);

                setAllTerms(false);

                if (termHelp) {
                    termHelp.textContent =
                        'Terms could not be loaded. You can still view all terms.';
                }
            });
    }

    academicYearSelect.addEventListener('change', function () {
        loadTerms(this.value);
    });

    // Terms supplied by the controller are already rendered on first load.
    if (!academicYearSelect.value || academicYearSelect.value === 'all') {
        termSelect.disabled = true;
    } else {
        termSelect.disabled = false;
    }
});</script>

<script>
document.addEventListener('DOMContentLoaded', function () {

    if (typeof Chart === 'undefined') {
        console.warn('Chart.js is unavailable.');
        return;
    }

    const fontFamily =
        'Inter, Arial, sans-serif';

    const currencyFormatter =
        function (value) {
            return 'GHS ' +
                Number(value || 0).toLocaleString(
                    undefined,
                    {
                        minimumFractionDigits: 2,
                        maximumFractionDigits: 2
                    }
                );
        };


    /*
    |--------------------------------------------------------------------------
    | SCHOOL COLLECTION DONUT
    |--------------------------------------------------------------------------
    */

    const schoolDonut =
        document.getElementById(
            'schoolCollectionDonut'
        );

    if (schoolDonut) {

        new Chart(
            schoolDonut,
            {
                type: 'doughnut',

                data: {
                    labels: [
                        'Fees Paid',
                        'Outstanding Fees'
                    ],

                    datasets: [{
                        data:
                            @json($schoolDonutValues),

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
                                padding: 16,
                                font: {
                                    family: fontFamily
                                }
                            }
                        },

                        tooltip: {
                            callbacks: {
                                label: function (context) {
                                    return (
                                        context.label +
                                        ': ' +
                                        currencyFormatter(
                                            context.raw
                                        )
                                    );
                                }
                            }
                        }

                    }

                }
            }
        );

    }


    /*
    |--------------------------------------------------------------------------
    | CLASS BAR CHART
    |--------------------------------------------------------------------------
    */

    const classCanvas =
        document.getElementById(
            'classFeeChart'
        );

    if (classCanvas) {

        new Chart(
            classCanvas,
            {
                type: 'bar',

                data: {
                    labels:
                        @json($classLabels),

                    datasets: [

                        {
                            label: 'Expected Fees',

                            data:
                                @json($classExpected),

                            backgroundColor:
                                '#1456a0',

                            borderRadius: 5,

                            borderSkipped: false
                        },

                        {
                            label: 'Fees Paid',

                            data:
                                @json($classPaid),

                            backgroundColor:
                                '#198754',

                            borderRadius: 5,

                            borderSkipped: false
                        },

                        {
                            label: 'Outstanding',

                            data:
                                @json($classOutstanding),

                            backgroundColor:
                                '#dc3545',

                            borderRadius: 5,

                            borderSkipped: false
                        }

                    ]
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
                                callback:
                                    function (value) {
                                        return 'GHS ' +
                                            Number(value)
                                                .toLocaleString();
                                    }
                            },

                            grid: {
                                color:
                                    'rgba(0,0,0,.06)'
                            }
                        },

                        x: {
                            grid: {
                                display: false
                            },

                            ticks: {
                                autoSkip: true,
                                maxRotation: 45,
                                minRotation: 0
                            }
                        }

                    },

                    plugins: {

                        legend: {
                            position: 'bottom',

                            labels: {
                                usePointStyle: true,
                                padding: 16,
                                font: {
                                    family: fontFamily
                                }
                            }
                        },

                        tooltip: {

                            callbacks: {

                                label:
                                    function (context) {

                                        return (
                                            context.dataset.label +
                                            ': ' +
                                            currencyFormatter(
                                                context.raw
                                            )
                                        );

                                    }

                            }

                        }

                    }

                }

            }
        );

    }

});
</script>
@endpush

@endsection
