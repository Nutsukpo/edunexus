{{-- resources/views/fee-payments/reports/index.blade.php --}}
@extends('layouts.master')

@section('title', 'Fee Payment Reports')

@section('content')
<div class="container-fluid py-3 fee-report-page">

    {{-- Header --}}
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div>
            <h1 class="h3 fw-bold mb-1 text-dark">Fee Payment Reports</h1>
            <p class="text-secondary mb-0">Comprehensive fee collection and payment analysis.</p>
        </div>

        <div class="d-flex flex-wrap gap-2">
            <a class="btn btn-outline-danger" href="{{ route('fee.payment.reports.export.pdf', request()->query()) }}">
                <i class="bi bi-file-pdf me-1"></i> Export PDF
            </a>
            <a class="btn btn-outline-success" href="{{ route('fee.payment.reports.export.excel', request()->query()) }}">
                <i class="bi bi-file-earmark-spreadsheet me-1"></i> Export Excel
            </a>
            <button type="button" class="btn btn-outline-secondary" onclick="window.print()">
                <i class="bi bi-printer me-1"></i> Print
            </button>
        </div>
    </div>

    {{-- Summary --}}
    <div class="row g-3 mb-4">
        @php
            $cards = [
                ['label' => 'Total Students', 'value' => number_format($summary['total_students'] ?? 0), 'icon' => 'people', 'color' => 'primary'],
                ['label' => 'Total Fees', 'value' => 'GHS ' . number_format($summary['total_fees'] ?? 0, 2), 'icon' => 'cash-stack', 'color' => 'success'],
                ['label' => 'Amount Paid', 'value' => 'GHS ' . number_format($summary['total_paid'] ?? 0, 2), 'icon' => 'check2-circle', 'color' => 'info'],
                ['label' => 'Outstanding Balance', 'value' => 'GHS ' . number_format($summary['total_balance'] ?? 0, 2), 'icon' => 'exclamation-triangle', 'color' => 'danger'],
            ];
        @endphp

        @foreach($cards as $card)
            <div class="col-xl-3 col-md-6">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <div class="text-secondary small mb-1">{{ $card['label'] }}</div>
                                <div class="h4 fw-bold text-dark mb-0">{{ $card['value'] }}</div>
                            </div>
                            <span class="bg-{{ $card['color'] }} bg-opacity-10 text-{{ $card['color'] }} rounded p-2">
                                <i class="bi bi-{{ $card['icon'] }} fs-5"></i>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    {{-- Filter --}}
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-light border-bottom d-flex justify-content-between align-items-center py-3">
            <h5 class="fw-bold text-dark mb-0">
                <i class="bi bi-funnel me-2"></i>Report Filters
            </h5>
            <a href="{{ route('fee.payment.reports.index') }}" class="btn btn-sm btn-outline-secondary">
                <i class="bi bi-arrow-counterclockwise me-1"></i> Reset
            </a>
        </div>

        <div class="card-body">
            <form action="{{ route('fee.payment.reports.index') }}" method="GET">
                @php
                    $selectedYear = request('academic_year_id');
                    $selectedClass = request('class_id');
                    $selectedStatus = request('status');
                    $selectedReport = request('report_type', 'summary');
                    $selectedStudent = request('student_id');
                    $selectedMethod = request('payment_method');

                    $yearLabel = $selectedYear && isset($academicYears[$selectedYear]) ? $academicYears[$selectedYear] : 'All Academic Years';
                    $classLabel = $selectedClass && isset($classes[$selectedClass]) ? $classes[$selectedClass] : 'All Classes';
                    $statusLabel = $selectedStatus && isset($statusOptions[$selectedStatus]) ? $statusOptions[$selectedStatus] : 'All Statuses';
                    $reportLabels = [
                        'summary' => 'Summary',
                        'detailed' => 'Detailed',
                        'student' => 'By Student',
                        'class' => 'By Class',
                        'payment_summary' => 'Payment Summary',
                    ];
                    $reportLabel = $reportLabels[$selectedReport] ?? 'Summary';
                    $studentLabel = $selectedStudent && isset($students[$selectedStudent]) ? $students[$selectedStudent] : 'All Students';
                    $methodLabel = $selectedMethod ? ucfirst(str_replace('_', ' ', $selectedMethod)) : 'All Methods';
                @endphp


                <div class="row g-3">

                    <div class="col-xl-3 col-md-6">
                        <label for="academic_year_id" class="form-label fw-semibold report-label">Academic Year</label>
                        <select name="academic_year_id" id="academic_year_id" class="form-select report-select">
                            <option value="">All Academic Years</option>
                            @foreach($academicYears as $id => $year)
                                <option value="{{ $id }}" {{ (string)$selectedYear === (string)$id ? 'selected' : '' }}>{{ $year }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-xl-3 col-md-6">
                        <label for="class_id" class="form-label fw-semibold report-label">Class</label>
                        <select name="class_id" id="class_id" class="form-select report-select">
                            <option value="">All Classes</option>
                            @foreach($classes as $id => $class)
                                <option value="{{ $id }}" {{ (string)$selectedClass === (string)$id ? 'selected' : '' }}>{{ $class }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-xl-3 col-md-6">
                        <label for="status" class="form-label fw-semibold report-label">Payment Status</label>
                        <select name="status" id="status" class="form-select report-select">
                            <option value="">All Statuses</option>
                            @foreach($statusOptions as $value => $label)
                                <option value="{{ $value }}" {{ (string)$selectedStatus === (string)$value ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-xl-3 col-md-6">
                        <label for="report_type" class="form-label fw-semibold report-label">Report Type</label>
                        <select name="report_type" id="report_type" class="form-select report-select">
                            @foreach($reportLabels as $value => $label)
                                <option value="{{ $value }}" {{ $selectedReport === $value ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Date from --}}
                    <div class="col-xl-3 col-md-6">
                        <label for="date_from" class="form-label fw-semibold text-dark">Date From</label>
                        <input type="date" name="date_from" id="date_from"
                               class="form-control bg-light text-dark border"
                               value="{{ request('date_from') }}">
                    </div>

                    {{-- Date to --}}
                    <div class="col-xl-3 col-md-6">
                        <label for="date_to" class="form-label fw-semibold text-dark">Date To</label>
                        <input type="date" name="date_to" id="date_to"
                               class="form-control bg-light text-dark border"
                               value="{{ request('date_to') }}">
                    </div>

                    {{-- Student --}}
                    <div class="col-xl-3 col-md-6">
                        <label class="form-label fw-semibold text-dark">Student</label>
                        <div class="dropdown w-100">
                            <button class="btn btn-light border text-dark w-100 text-start dropdown-toggle"
                                    type="button" data-bs-toggle="dropdown" data-bs-display="static">
                                <span id="student_label">{{ $studentLabel }}</span>
                            </button>
                            <ul class="dropdown-menu w-100 bg-light border shadow-sm">
                                <li><button type="button" class="dropdown-item text-dark bg-light"
                                            onclick="chooseFilter('student_id','','All Students','student_label')">
                                    All Students
                                </button></li>
                                @foreach($students as $id => $name)
                                    <li><button type="button" class="dropdown-item text-dark bg-light"
                                                onclick="chooseFilter('student_id','{{ $id }}','{{ addslashes($name) }}','student_label')">
                                        {{ $name }}
                                    </button></li>
                                @endforeach
                            </ul>
                        </div>
                    </div>

                    {{-- Payment method --}}
                    <div class="col-xl-3 col-md-6">
                        <label class="form-label fw-semibold text-dark">Payment Method</label>
                        <div class="dropdown w-100">
                            <button class="btn btn-light border text-dark w-100 text-start dropdown-toggle"
                                    type="button" data-bs-toggle="dropdown" data-bs-display="static">
                                <span id="payment_method_label">{{ $methodLabel }}</span>
                            </button>
                            <ul class="dropdown-menu w-100 bg-light border shadow-sm">
                                <li><button type="button" class="dropdown-item text-dark bg-light"
                                            onclick="chooseFilter('payment_method','','All Methods','payment_method_label')">
                                    All Methods
                                </button></li>
                                @foreach($paymentMethods as $method)
                                    @php $methodText = ucfirst(str_replace('_', ' ', $method)); @endphp
                                    <li><button type="button" class="dropdown-item text-dark bg-light"
                                                onclick="chooseFilter('payment_method','{{ $method }}','{{ addslashes($methodText) }}','payment_method_label')">
                                        {{ $methodText }}
                                    </button></li>
                                @endforeach
                            </ul>
                        </div>
                    </div>

                    <div class="col-12 d-flex justify-content-end gap-2 pt-2">
                        <button type="submit" class="btn btn-primary px-4">
                            <i class="bi bi-search me-1"></i> Apply Filters
                        </button>
                        <a href="{{ route('fee.payment.reports.index') }}" class="btn btn-outline-secondary">
                            <i class="bi bi-x-circle me-1"></i> Clear
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    {{-- Payment summary --}}
    @if(isset($paymentSummary))
        <div class="row g-3 mb-4">
            <div class="col-lg-6">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-header bg-light text-dark fw-bold">Payment Method Breakdown</div>
                    <div class="card-body">
                        @if(isset($paymentSummary['by_method']) && $paymentSummary['by_method']->isNotEmpty())
                            <div class="table-responsive">
                                <table class="table table-sm align-middle mb-0">
                                    <thead>
                                        <tr>
                                            <th class="text-dark">Method</th>
                                            <th class="text-dark text-end">Count</th>
                                            <th class="text-dark text-end">Amount</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($paymentSummary['by_method'] as $method)
                                            <tr>
                                                <td>{{ ucfirst(str_replace('_', ' ', $method->payment_method ?? 'N/A')) }}</td>
                                                <td class="text-end">{{ $method->count }}</td>
                                                <td class="text-end">GHS {{ number_format($method->total ?? 0, 2) }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @else
                            <div class="text-secondary">No payment data available.</div>
                        @endif
                    </div>
                </div>
            </div>

            <div class="col-lg-6">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-header bg-light text-dark fw-bold">Recent Daily Payments</div>
                    <div class="card-body">
                        @if(isset($paymentSummary['daily_summary']) && $paymentSummary['daily_summary']->isNotEmpty())
                            <div class="table-responsive">
                                <table class="table table-sm align-middle mb-0">
                                    <thead>
                                        <tr>
                                            <th class="text-dark">Date</th>
                                            <th class="text-dark text-end">Transactions</th>
                                            <th class="text-dark text-end">Amount</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($paymentSummary['daily_summary'] as $day)
                                            <tr>
                                                <td>{{ \Carbon\Carbon::parse($day->date)->format('M d, Y') }}</td>
                                                <td class="text-end">{{ $day->count }}</td>
                                                <td class="text-end">GHS {{ number_format($day->total ?? 0, 2) }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @else
                            <div class="text-secondary">No daily payment data available.</div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    @endif

    {{-- Report table --}}
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-light d-flex flex-wrap justify-content-between align-items-center gap-2 py-3">
            <h5 class="fw-bold text-dark mb-0">
                <i class="bi bi-table me-2"></i>Report Data
                <span class="badge bg-secondary">{{ method_exists($reports, 'total') ? $reports->total() : $reports->count() }}</span>
            </h5>
            <span class="small text-secondary">Generated {{ now()->format('M d, Y H:i') }}</span>
        </div>

        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="text-dark">#</th>
                            <th class="text-dark">Student</th>
                            <th class="text-dark">Class</th>
                            <th class="text-dark">Academic Year</th>
                            <th class="text-dark text-end">Total Fees</th>
                            <th class="text-dark text-end">Paid</th>
                            <th class="text-dark text-end">Balance</th>
                            <th class="text-dark text-end">Discount</th>
                            <th class="text-dark text-end">Waiver</th>
                            <th class="text-dark text-center">Status</th>
                            <th class="text-dark text-center">Actions</th>
                        </tr>
                    </thead>

                    <tbody>
                    @forelse($reports as $report)
                        @php
                            $student = $report->student ?? null;
                            $studentName = $student?->full_name
                                ?? trim(($student?->first_name ?? '') . ' ' . ($student?->middle_name ?? '') . ' ' . ($student?->last_name ?? ''))
                                ?: 'N/A';

                            $className = $report->studentClass->class_name
                                ?? $report->studentClass->name
                                ?? 'N/A';

                            $yearName = $report->academicYear->year_name
                                ?? $report->academicYear->name
                                ?? 'N/A';

                            $status = $report->status ?? 'pending';
                            $statusClass = match($status) {
                                'paid' => 'success',
                                'partial' => 'warning',
                                'pending' => 'danger',
                                default => 'secondary',
                            };
                        @endphp

                        <tr>
                            <td>{{ method_exists($reports, 'firstItem') ? ($reports->firstItem() + $loop->index) : $loop->iteration }}</td>
                            <td>
                                <div class="fw-semibold text-dark">{{ $studentName }}</div>
                                @if($student?->student_id)
                                    <small class="text-secondary">ID: {{ $student->student_id }}</small>
                                @endif
                            </td>
                            <td>{{ $className }}</td>
                            <td>{{ $yearName }}</td>
                            <td class="text-end">GHS {{ number_format($report->total_fees ?? 0, 2) }}</td>
                            <td class="text-end text-success">GHS {{ number_format($report->amount_paid ?? 0, 2) }}</td>
                            <td class="text-end {{ ($report->balance ?? 0) > 0 ? 'text-danger' : 'text-success' }}">
                                GHS {{ number_format($report->balance ?? 0, 2) }}
                            </td>
                            <td class="text-end">GHS {{ number_format($report->discount_applied ?? 0, 2) }}</td>
                            <td class="text-end">GHS {{ number_format($report->waiver_amount ?? 0, 2) }}</td>
                            <td class="text-center">
                                <span class="badge bg-{{ $statusClass }}">
                                    {{ ucfirst($status) }}
                                </span>
                            </td>
                            <td class="text-center">
                                <div class="btn-group btn-group-sm">
                                    <a href="{{ route('fee.payment.reports.show', $report->id) }}"
                                       class="btn btn-outline-primary" title="View Report">
                                        <i class="bi bi-eye"></i>
                                    </a>

                                    @if(isset($report->payments) && $report->payments->isNotEmpty())
                                        <a href="{{ route('fee-payments.receipt', $report->payments->first()->id) }}"
                                           class="btn btn-outline-secondary" title="Receipt">
                                            <i class="bi bi-receipt"></i>
                                        </a>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="11" class="text-center py-5 text-secondary">
                                <i class="bi bi-inbox fs-1 d-block mb-2"></i>
                                No report data found matching your filters.
                            </td>
                        </tr>
                    @endforelse
                    </tbody>

                    <tfoot class="table-light">
                        <tr>
                            <th colspan="4" class="text-end text-dark">Totals:</th>
                            <th class="text-end text-dark">GHS {{ number_format($summary['total_fees'] ?? 0, 2) }}</th>
                            <th class="text-end text-dark">GHS {{ number_format($summary['total_paid'] ?? 0, 2) }}</th>
                            <th class="text-end text-dark">GHS {{ number_format($summary['total_balance'] ?? 0, 2) }}</th>
                            <th class="text-end text-dark">GHS {{ number_format($summary['total_discount'] ?? 0, 2) }}</th>
                            <th class="text-end text-dark">GHS {{ number_format($summary['total_waiver'] ?? 0, 2) }}</th>
                            <th colspan="2"></th>
                        </tr>
                    </tfoot>
                </table>
            </div>

            @if(method_exists($reports, 'links'))
                <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 p-3">
                    <div class="small text-secondary">
                        Showing {{ $reports->firstItem() ?? 0 }}
                        to {{ $reports->lastItem() ?? 0 }}
                        of {{ $reports->total() ?? 0 }} entries
                    </div>
                    <div>{{ $reports->appends(request()->query())->links('pagination::bootstrap-5') }}</div>
                </div>
            @endif
        </div>
    </div>

    {{-- Quick actions --}}
    <div class="card border-0 shadow-sm mt-4">
        <div class="card-body">
            <h6 class="fw-bold text-dark mb-3">
                <i class="bi bi-lightning-charge me-2"></i>Quick Actions
            </h6>
            <div class="d-flex flex-wrap gap-2">
                <a href="{{ route('fee.payment.reports.outstanding') }}" class="btn btn-outline-danger">
                    <i class="bi bi-exclamation-triangle me-1"></i> Outstanding Fees
                </a>
                <a href="{{ route('fee.payment.reports.payment-history') }}" class="btn btn-outline-info">
                    <i class="bi bi-clock-history me-1"></i> Payment History
                </a>
                <a href="{{ route('fee.payment.reports.index', ['report_type' => 'detailed']) }}" class="btn btn-outline-secondary">
                    <i class="bi bi-list-ul me-1"></i> Detailed Report
                </a>
                <a href="{{ route('fee-payments.index') }}" class="btn btn-outline-primary">
                    <i class="bi bi-credit-card me-1"></i> All Payments
                </a>
                <button type="button" class="btn btn-outline-success" onclick="window.location.reload()">
                    <i class="bi bi-arrow-repeat me-1"></i> Refresh
                </button>
            </div>
        </div>
    </div>
</div>

@push('styles')
<style>
    /* Fee report: native browser selects only. No Bootstrap dropdown component is used. */
    .fee-report-page {
        --report-text: #212529;
        --report-muted: #6c757d;
        --report-border: #ced4da;
        --report-bg: #ffffff;
    }
    .fee-report-page .report-label { color: var(--report-text) !important; }
    .fee-report-page .report-select {
        display: block !important;
        width: 100% !important;
        min-height: 42px !important;
        padding: .55rem 2.25rem .55rem .75rem !important;
        background-color: #fff !important;
        color: #212529 !important;
        border: 1px solid var(--report-border) !important;
        border-radius: .375rem !important;
        opacity: 1 !important;
        font: inherit !important;
        line-height: 1.5 !important;
        appearance: auto !important;
        -webkit-appearance: auto !important;
        box-shadow: none !important;
    }
    .fee-report-page .report-select option {
        background: #fff !important;
        color: #212529 !important;
        font-weight: 400 !important;
    }
    .fee-report-page .report-select:focus {
        background-color: #fff !important;
        color: #212529 !important;
        border-color: #86b7fe !important;
        outline: 0 !important;
        box-shadow: 0 0 0 .2rem rgba(13,110,253,.15) !important;
    }
    .fee-report-page .report-select:disabled { opacity: .65 !important; }
    .fee-report-page .form-control { color: #212529 !important; background-color: #fff !important; }
    .fee-report-page .card-header,
    .fee-report-page .table-light { background-color: #f8f9fa !important; }
    .fee-report-page .text-dark { color: #212529 !important; }
    .fee-report-page .text-secondary { color: #6c757d !important; }
    @media (prefers-color-scheme: dark) {
        /* Deliberately keep report controls light and readable. */
        .fee-report-page .report-select,
        .fee-report-page .report-select option,
        .fee-report-page .form-control { background-color:#fff !important; color:#212529 !important; }
    }
    @media print {
        .fee-report-page .report-filter-card,
        .fee-report-page .quick-actions { display:none !important; }
    }
</style>
@endpush

@endsection

