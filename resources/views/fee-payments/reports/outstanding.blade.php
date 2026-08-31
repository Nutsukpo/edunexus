@extends('layouts.master')

@section('title', 'Outstanding Fees')

@section('content')

@php
    $totalOutstanding = (float) ($totalOutstanding ?? 0);
    $totalStudents = (int) ($totalStudents ?? 0);

    $selectedYear = request('academic_year_id');
    $selectedClass = request('class_id');

    $recordCount = method_exists($outstandingFees, 'total')
        ? $outstandingFees->total()
        : (is_countable($outstandingFees) ? count($outstandingFees) : 0);
@endphp

<style>
    .outstanding-page {
        --primary: #1456a0;
        --primary-dark: #0b3567;
        --danger: #b42318;
        --success: #15803d;
        --warning: #b7791f;
        --text: #172033;
        --muted: #667085;
        --border: #e5eaf0;
        --soft: #f7f9fc;
    }

    .outstanding-hero {
        padding: 1.6rem;
        border-radius: 20px;
        color: #fff;
        background:
            radial-gradient(circle at 85% 15%, rgba(255,255,255,.16), transparent 28%),
            linear-gradient(135deg, var(--primary-dark), var(--primary), #2376bd);
        box-shadow: 0 14px 35px rgba(20,86,160,.16);
    }

    .outstanding-hero .eyebrow {
        font-size: .68rem;
        font-weight: 800;
        letter-spacing: .14em;
        text-transform: uppercase;
        opacity: .7;
    }

    .outstanding-hero h1 {
        margin: .35rem 0;
        font-size: clamp(1.6rem, 3vw, 2.15rem);
        font-weight: 800;
    }

    .outstanding-hero p {
        margin: 0;
        max-width: 760px;
        font-size: .88rem;
        opacity: .8;
    }

    .outstanding-card,
    .filter-card,
    .table-card {
        border: 1px solid var(--border);
        border-radius: 16px;
        background: #fff;
        box-shadow: 0 6px 20px rgba(16,24,40,.05);
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
    }

    .metric-label {
        color: var(--muted);
        font-size: .68rem;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: .06em;
    }

    .metric-value {
        margin-top: .3rem;
        color: var(--text);
        font-size: 1.35rem;
        font-weight: 800;
    }

    .metric-note {
        margin-top: .25rem;
        color: var(--muted);
        font-size: .7rem;
    }

    .filter-card .form-label {
        color: var(--text);
        font-size: .75rem;
        font-weight: 700;
    }

    .filter-card .form-select {
        min-height: 43px;
        border-color: var(--border);
        border-radius: 10px;
    }

    .table-card-header {
        padding: 1rem 1.15rem;
        border-bottom: 1px solid var(--border);
    }

    .table-card-header h5 {
        margin: 0;
        color: var(--text);
        font-size: .98rem;
        font-weight: 800;
    }

    .table-card-header p {
        margin: .2rem 0 0;
        color: var(--muted);
        font-size: .72rem;
    }

    .table-report {
        margin: 0;
    }

    .table-report thead th {
        padding: .85rem .75rem;
        color: #475467;
        background: var(--soft);
        border-bottom: 1px solid var(--border);
        font-size: .67rem;
        font-weight: 800;
        letter-spacing: .04em;
        text-transform: uppercase;
        white-space: nowrap;
    }

    .table-report td {
        padding: .8rem .75rem;
        color: #344054;
        font-size: .8rem;
        vertical-align: middle;
    }

    .table-report tbody tr:hover {
        background: #fbfcfe;
    }

    .student-name {
        color: var(--text);
        font-weight: 700;
    }

    .student-id {
        margin-top: 2px;
        color: var(--muted);
        font-size: .68rem;
    }

    .status-pill {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        padding: .4rem .68rem;
        border-radius: 999px;
        font-size: .66rem;
        font-weight: 800;
        white-space: nowrap;
    }

    .status-outstanding {
        color: var(--danger);
        background: #fdecec;
    }

    .status-overdue {
        color: #8a6116;
        background: #fff5db;
    }

    .progress-wrap {
        min-width: 125px;
    }

    .progress-wrap .progress {
        height: 7px;
        border-radius: 99px;
        background: #edf1f5;
    }

    .progress-wrap .small {
        font-size: .67rem;
    }

    .empty-state {
        padding: 4.5rem 1rem;
        color: var(--muted);
        text-align: center;
    }

    .empty-icon {
        width: 65px;
        height: 65px;
        margin: 0 auto 1rem;
        border-radius: 18px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        color: var(--success);
        background: #eaf7ef;
        font-size: 1.6rem;
    }

    .pagination-wrap {
        padding: 1rem 1.15rem;
        border-top: 1px solid var(--border);
    }

    @media (max-width: 767.98px) {
        .outstanding-hero {
            padding: 1.2rem;
        }

        .outstanding-hero .btn {
            flex: 1 1 100%;
        }
    }

    @media print {
        .no-print,
        .filter-card,
        .hero-actions {
            display: none !important;
        }

        .outstanding-hero,
        .outstanding-card,
        .table-card {
            box-shadow: none !important;
        }

        .outstanding-hero {
            color: #111 !important;
            background: #fff !important;
            border: 1px solid #aaa;
        }
    }
</style>

<div class="container-fluid py-3 outstanding-page">

    <div class="outstanding-hero mb-4">
        <div class="d-flex flex-column flex-xl-row justify-content-between align-items-xl-center gap-3">

            <div>
                <div class="eyebrow">Finance • Receivables</div>
                <h1>Outstanding Fees</h1>
                <p>
                    Review students with unpaid or partially paid fees and identify
                    outstanding balances requiring follow-up.
                </p>
            </div>

            <div class="hero-actions d-flex flex-wrap gap-2 no-print">
                <button type="button" class="btn btn-light btn-sm" onclick="window.print()">
                    <i class="fas fa-print me-1"></i> Print
                </button>

                <a href="{{ route('fee.payment.reports.school-overview') }}"
                   class="btn btn-outline-light btn-sm">
                    <i class="fas fa-chart-pie me-1"></i> School Fee Overview
                </a>

                <a href="{{ route('fee.payment.reports.index') }}"
                   class="btn btn-outline-light btn-sm">
                    <i class="fas fa-chart-column me-1"></i> Fee Reports
                </a>
            </div>

        </div>
    </div>

    <div class="row g-3 mb-4">

        <div class="col-xl-4 col-md-6">
            <div class="outstanding-card metric-card">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <div class="metric-label">Total Outstanding</div>
                            <div class="metric-value text-danger">
                                GHS {{ number_format($totalOutstanding, 2) }}
                            </div>
                            <div class="metric-note">Current amount due</div>
                        </div>
                        <span class="metric-icon bg-danger-subtle text-danger">
                            <i class="fas fa-triangle-exclamation"></i>
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-4 col-md-6">
            <div class="outstanding-card metric-card">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <div class="metric-label">Students With Balance</div>
                            <div class="metric-value">
                                {{ number_format($totalStudents) }}
                            </div>
                            <div class="metric-note">Students requiring follow-up</div>
                        </div>
                        <span class="metric-icon bg-warning-subtle text-warning">
                            <i class="fas fa-user-clock"></i>
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-4 col-md-12">
            <div class="outstanding-card metric-card">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <div class="metric-label">Report Records</div>
                            <div class="metric-value">
                                {{ number_format($recordCount) }}
                            </div>
                            <div class="metric-note">Records matching current filters</div>
                        </div>
                        <span class="metric-icon bg-primary-subtle text-primary">
                            <i class="fas fa-list-check"></i>
                        </span>
                    </div>
                </div>
            </div>
        </div>

    </div>

    <div class="filter-card mb-4 no-print">
        <div class="card-body">

            <form method="GET"
                  action="{{ route('fee.payment.reports.outstanding') }}">

                <div class="row g-3 align-items-end">

                    <div class="col-lg-4 col-md-6">
                        <label for="academic_year_id" class="form-label">
                            Academic Year
                        </label>

                        <select name="academic_year_id"
                                id="academic_year_id"
                                class="form-select">

                            <option value="">All Academic Years</option>

                            @foreach($academicYears as $id => $year)
                                <option value="{{ $id }}"
                                    {{ (string) $selectedYear === (string) $id ? 'selected' : '' }}>
                                    {{ $year }}
                                </option>
                            @endforeach

                        </select>
                    </div>

                    <div class="col-lg-4 col-md-6">
                        <label for="class_id" class="form-label">
                            Class
                        </label>

                        <select name="class_id"
                                id="class_id"
                                class="form-select">

                            <option value="">All Classes</option>

                            @foreach($classes as $id => $class)
                                <option value="{{ $id }}"
                                    {{ (string) $selectedClass === (string) $id ? 'selected' : '' }}>
                                    {{ $class }}
                                </option>
                            @endforeach

                        </select>
                    </div>

                    <div class="col-lg-2 col-md-6">
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="fas fa-filter me-1"></i>
                            Filter
                        </button>
                    </div>

                    <div class="col-lg-2 col-md-6">
                        <a href="{{ route('fee.payment.reports.outstanding') }}"
                           class="btn btn-outline-secondary w-100">
                            <i class="fas fa-rotate-left me-1"></i>
                            Reset
                        </a>
                    </div>

                </div>

            </form>

        </div>
    </div>

    <div class="table-card">

        <div class="table-card-header">
            <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-2">

                <div>
                    <h5>
                        <i class="fas fa-money-bill-wave text-danger me-2"></i>
                        Outstanding Fee Accounts
                    </h5>

                    <p>
                        Students whose fee account has an outstanding balance.
                    </p>
                </div>

                <div class="small text-muted">
                    {{ number_format($recordCount) }} record(s)
                </div>

            </div>
        </div>

        <div class="table-responsive">

            <table class="table table-hover table-report align-middle mb-0">

                <thead>
                    <tr>
                        <th>#</th>
                        <th>Student</th>
                        <th>Class</th>
                        <th>Academic Year</th>
                        <th class="text-end">Total Fees</th>
                        <th class="text-end">Amount Paid</th>
                        <th class="text-end">Outstanding</th>
                        <th>Payment Progress</th>
                        <th>Status</th>
                        <th class="text-center no-print">Action</th>
                    </tr>
                </thead>

                <tbody>

                @forelse($outstandingFees as $account)

                    @php
                        $student = $account->student;

                        $studentName = $student
                            ? trim(collect([
                                $student->first_name ?? null,
                                $student->middle_name ?? null,
                                $student->last_name ?? null,
                            ])->filter()->implode(' '))
                            : 'N/A';

                        $studentName = $studentName ?: 'N/A';

                        $totalFees = (float) ($account->total_fees ?? 0);
                        $amountPaid = (float) ($account->amount_paid ?? 0);
                        $balance = max(0, (float) ($account->balance ?? 0));

                        $paymentRate = $totalFees > 0
                            ? min(100, max(0, ($amountPaid / $totalFees) * 100))
                            : 0;

                        $status = strtolower((string) ($account->status ?? 'pending'));

                        $statusText = match ($status) {
                            'overdue' => 'Overdue',
                            'partial' => 'Partial',
                            'pending' => 'Pending',
                            default => ucfirst($status),
                        };

                        $statusClass = $status === 'overdue'
                            ? 'status-overdue'
                            : 'status-outstanding';

                        $statusIcon = $status === 'overdue'
                            ? 'fa-triangle-exclamation'
                            : 'fa-clock';

                        $className = $account->studentClass->name
                            ?? $account->studentClass->class_name
                            ?? 'N/A';

                        $yearName = $account->academicYear->name
                            ?? $account->academicYear->year_name
                            ?? 'N/A';
                    @endphp

                    <tr>

                        <td>
                            {{ method_exists($outstandingFees, 'firstItem')
                                ? (($outstandingFees->firstItem() ?? 1) + $loop->index)
                                : $loop->iteration }}
                        </td>

                        <td>
                            <div class="student-name">{{ $studentName }}</div>
                            <div class="student-id">
                                Student ID: {{ $student->student_id ?? 'N/A' }}
                            </div>
                        </td>

                        <td>{{ $className }}</td>

                        <td>{{ $yearName }}</td>

                        <td class="text-end">
                            GHS {{ number_format($totalFees, 2) }}
                        </td>

                        <td class="text-end text-success fw-semibold">
                            GHS {{ number_format($amountPaid, 2) }}
                        </td>

                        <td class="text-end text-danger fw-bold">
                            GHS {{ number_format($balance, 2) }}
                        </td>

                        <td class="progress-wrap">

                            <div class="d-flex justify-content-between mb-1">
                                <span class="small">
                                    {{ number_format($paymentRate, 1) }}%
                                </span>
                            </div>

                            <div class="progress">
                                <div class="progress-bar bg-success"
                                     role="progressbar"
                                     style="width: {{ $paymentRate }}%;"
                                     aria-valuenow="{{ $paymentRate }}"
                                     aria-valuemin="0"
                                     aria-valuemax="100">
                                </div>
                            </div>

                        </td>

                        <td>
                            <span class="status-pill {{ $statusClass }}">
                                <i class="fas {{ $statusIcon }}"></i>
                                {{ $statusText }}
                            </span>
                        </td>

                        <td class="text-center no-print">

                            <a href="{{ route('fee.payment.reports.show', $account->id) }}"
                               class="btn btn-sm btn-outline-primary"
                               title="View fee account">
                                <i class="fas fa-eye"></i>
                            </a>

                        </td>

                    </tr>

                @empty

                    <tr>
                        <td colspan="10">
                            <div class="empty-state">

                                <div class="empty-icon">
                                    <i class="fas fa-circle-check"></i>
                                </div>

                                <div class="fw-bold text-dark mb-1">
                                    No Outstanding Fees
                                </div>

                                <div>
                                    No students with outstanding balances were found
                                    for the selected filters.
                                </div>

                            </div>
                        </td>
                    </tr>

                @endforelse

                </tbody>

                @if($recordCount > 0)

                    <tfoot>
                        <tr class="fw-bold">

                            <th colspan="4" class="text-end">
                                TOTAL
                            </th>

                            <th class="text-end">
                                GHS {{ number_format($outstandingFees->sum('total_fees'), 2) }}
                            </th>

                            <th class="text-end text-success">
                                GHS {{ number_format($outstandingFees->sum('amount_paid'), 2) }}
                            </th>

                            <th class="text-end text-danger">
                                GHS {{ number_format($outstandingFees->sum('balance'), 2) }}
                            </th>

                            <th colspan="3"></th>

                        </tr>
                    </tfoot>

                @endif

            </table>

        </div>

        @if(method_exists($outstandingFees, 'links'))

            <div class="pagination-wrap d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">

                <div class="small text-muted">
                    Showing
                    {{ $outstandingFees->firstItem() ?? 0 }}
                    to
                    {{ $outstandingFees->lastItem() ?? 0 }}
                    of
                    {{ $outstandingFees->total() ?? 0 }}
                    records
                </div>

                <div>
                    {{ $outstandingFees->appends(request()->query())->links('pagination::bootstrap-5') }}
                </div>

            </div>

        @endif

    </div>

</div>

@endsection
