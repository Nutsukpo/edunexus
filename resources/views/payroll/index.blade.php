@extends('layouts.master')

@section('title', 'Payroll Periods')

@section('content')

<div class="container-fluid py-4">

    {{-- ============================================================
        PAGE HEADER
    ============================================================= --}}

    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4">

        <div>
            <h3 class="mb-1 fw-bold text-primary">
                <i class="fas fa-calendar-alt me-2"></i>
                Payroll Periods
            </h3>

            <p class="text-muted mb-0">
                Manage payroll periods, staff assignments and payroll processing.
            </p>
        </div>

        <div class="mt-3 mt-md-0">

            <a
                href="{{ route('payroll-periods.create') }}"
                class="btn btn-primary"
            >
                <i class="fas fa-plus-circle me-1"></i>
                Create Payroll Period
            </a>

        </div>

    </div>


    {{-- ============================================================
        FLASH MESSAGES
    ============================================================= --}}

    @if(session('success'))

        <div class="alert alert-success alert-dismissible fade show shadow-sm">

            <i class="fas fa-check-circle me-2"></i>

            <strong>Success:</strong>
            {{ session('success') }}

            <button
                type="button"
                class="btn-close"
                data-bs-dismiss="alert"
            ></button>

        </div>

    @endif


    @if(session('error'))

        <div class="alert alert-danger alert-dismissible fade show shadow-sm">

            <i class="fas fa-exclamation-circle me-2"></i>

            <strong>Error:</strong>
            {{ session('error') }}

            <button
                type="button"
                class="btn-close"
                data-bs-dismiss="alert"
            ></button>

        </div>

    @endif


    @if($errors->any())

        <div class="alert alert-danger alert-dismissible fade show shadow-sm">

            <strong>
                Please correct the following:
            </strong>

            <ul class="mb-0 mt-2">

                @foreach($errors->all() as $error)

                    <li>
                        {{ $error }}
                    </li>

                @endforeach

            </ul>

            <button
                type="button"
                class="btn-close"
                data-bs-dismiss="alert"
            ></button>

        </div>

    @endif


    {{-- ============================================================
        SUMMARY CARDS
    ============================================================= --}}

    @php

        /*
        |--------------------------------------------------------------------------
        | These counts are based on the records available on the
        | current pagination page.
        |--------------------------------------------------------------------------
        */

        $pagePeriods = collect($payrollPeriods->items());

        $draftCount = $pagePeriods
            ->where('status', 'draft')
            ->count();

        $processingCount = $pagePeriods
            ->where('status', 'processing')
            ->count();

        $pendingApprovalCount = $pagePeriods
            ->where('status', 'pending_approval')
            ->count();

        $approvedCount = $pagePeriods
            ->where('status', 'approved')
            ->count();

        $rejectedCount = $pagePeriods
            ->where('status', 'rejected')
            ->count();

        $paidCount = $pagePeriods
            ->where('status', 'paid')
            ->count();

        $cancelledCount = $pagePeriods
            ->where('status', 'cancelled')
            ->count();

    @endphp


    <div class="row g-3 mb-4">

        {{-- TOTAL --}}

        <div class="col-xl-3 col-md-6">

            <div class="summary-card summary-primary">

                <div>
                    <span class="summary-label">
                        Total Periods
                    </span>

                    <h3>
                        {{ $payrollPeriods->total() }}
                    </h3>
                </div>

                <div class="summary-icon">
                    <i class="fas fa-calendar-alt"></i>
                </div>

            </div>

        </div>




     


        {{-- PENDING APPROVAL --}}

        <div class="col-xl-3 col-md-6">

            <div class="summary-card summary-orange">

                <div>
                    <span class="summary-label">
                        Pending Approval
                    </span>

                    <h3>
                        {{ $pendingApprovalCount }}
                    </h3>
                </div>

                <div class="summary-icon">
                    <i class="fas fa-hourglass-half"></i>
                </div>

            </div>

        </div>


        {{-- APPROVED --}}

        <div class="col-xl-3 col-md-6">

            <div class="summary-card summary-success">

                <div>
                    <span class="summary-label">
                        Approved
                    </span>

                    <h3>
                        {{ $approvedCount }}
                    </h3>
                </div>

                <div class="summary-icon">
                    <i class="fas fa-check-circle"></i>
                </div>

            </div>

        </div>


        {{-- REJECTED --}}

        <div class="col-xl-3 col-md-6">

            <div class="summary-card summary-danger">

                <div>
                    <span class="summary-label">
                        Rejected
                    </span>

                    <h3>
                        {{ $rejectedCount }}
                    </h3>
                </div>

                <div class="summary-icon">
                    <i class="fas fa-times-circle"></i>
                </div>

            </div>

        </div>


        {{-- PAID --}}
<!-- 
        <div class="col-xl-3 col-md-6">

            <div class="summary-card summary-paid">

                <div>
                    <span class="summary-label">
                        Paid
                    </span>

                    <h3>
                        {{ $paidCount }}
                    </h3>
                </div>

                <div class="summary-icon">
                    <i class="fas fa-money-check-alt"></i>
                </div>

            </div>

        </div> -->



    </div>


    {{-- ============================================================
        MAIN CARD
    ============================================================= --}}

    <div class="card border-0 shadow-sm">

        {{-- CARD HEADER --}}

        <div class="card-header bg-white border-0 py-3">

            <div class="d-flex flex-wrap justify-content-between align-items-center">

                <div>

                    <h5 class="mb-1 fw-bold">
                        Payroll Period Register
                    </h5>

                    <small class="text-muted">
                        View and manage all payroll periods.
                    </small>

                </div>

            </div>

        </div>


        {{-- ========================================================
            SEARCH / FILTER BAR
        ========================================================= --}}

        <div class="card-body border-bottom">

            <div class="row g-2">

                <div class="col-lg-6">

                    <div class="input-group">

                        <span class="input-group-text bg-light">

                            <i class="fas fa-search text-primary"></i>

                        </span>

                        <input
                            type="text"
                            id="searchInput"
                            class="form-control"
                            placeholder="Search payroll code, name, month, year or staff..."
                        >

                    </div>

                </div>


                <div class="col-lg-3">

                    <select
                        id="statusFilter"
                        class="form-select"
                    >

                        <option value="all">
                            All Statuses
                        </option>

                        <option value="draft">
                            Draft
                        </option>

                        <option value="processing">
                            Processing
                        </option>

                        <option value="pending_approval">
                            Pending Approval
                        </option>

                        <option value="approved">
                            Approved
                        </option>

                        <option value="rejected">
                            Rejected
                        </option>

                        <option value="paid">
                            Paid
                        </option>

                        <option value="cancelled">
                            Cancelled
                        </option>

                    </select>

                </div>


                <div class="col-lg-3">

                    <button
                        type="button"
                        id="resetFilters"
                        class="btn btn-outline-secondary w-100"
                    >

                        <i class="fas fa-sync-alt me-1"></i>
                        Reset Filters

                    </button>

                </div>

            </div>

        </div>


        {{-- ========================================================
            TABLE
        ========================================================= --}}

        <div class="card-body p-0">

            <div class="table-responsive">

                <table
                    class="table table-hover align-middle mb-0"
                    id="payrollTable"
                >

                    <thead class="table-light">

                        <tr>

                            <th class="text-center">
                                #
                            </th>

                            <th>
                                Payroll Period
                            </th>

                            <th>
                                Month
                            </th>

                            <th>
                                Year
                            </th>

                            <th class="text-center">
                                Staff
                            </th>

                            <th>
                                Status
                            </th>

                            <th>
                                Created By
                            </th>

                            <th class="text-end">
                                Actions
                            </th>

                        </tr>

                    </thead>


                    <tbody>

                        @forelse($payrollPeriods as $period)

                            @php

                                $periodStatus =
                                    strtolower(
                                        trim(
                                            $period->status ?? 'unknown'
                                        )
                                    );

                                $statusConfig = [

                                    'draft' => [
                                        'class' => 'status-draft',
                                        'icon' => 'fa-file-alt',
                                        'label' => 'Draft',
                                    ],

                                    'processing' => [
                                        'class' => 'status-processing',
                                        'icon' => 'fa-cog',
                                        'label' => 'Processing',
                                    ],

                                    'pending_approval' => [
                                        'class' => 'status-pending',
                                        'icon' => 'fa-hourglass-half',
                                        'label' => 'Pending Approval',
                                    ],

                                    'approved' => [
                                        'class' => 'status-approved',
                                        'icon' => 'fa-check-circle',
                                        'label' => 'Approved',
                                    ],

                                    'rejected' => [
                                        'class' => 'status-rejected',
                                        'icon' => 'fa-times-circle',
                                        'label' => 'Rejected',
                                    ],

                                    'paid' => [
                                        'class' => 'status-paid',
                                        'icon' => 'fa-money-check-alt',
                                        'label' => 'Paid',
                                    ],

                                    'cancelled' => [
                                        'class' => 'status-cancelled',
                                        'icon' => 'fa-ban',
                                        'label' => 'Cancelled',
                                    ],

                                ];

                                $status =
                                    $statusConfig[$periodStatus]
                                    ??
                                    [
                                        'class' => 'status-default',
                                        'icon' => 'fa-question-circle',
                                        'label' => ucfirst(
                                            str_replace(
                                                '_',
                                                ' ',
                                                $periodStatus
                                            )
                                        ),
                                    ];

                                $createdBy =
                                    $period->createdBy;

                                $creatorName =
                                    trim(
                                        ($createdBy->first_name ?? '')
                                        . ' '
                                        .
                                        ($createdBy->last_name ?? '')
                                    );

                                if (
                                    empty($creatorName)
                                    &&
                                    $createdBy
                                ) {
                                    $creatorName =
                                        $createdBy->name
                                        ?? 'N/A';
                                }

                            @endphp


                            <tr
                                data-status="{{ $periodStatus }}"
                                data-search="
                                    {{ strtolower(
                                        trim(
                                            ($period->period_code ?? '')
                                            . ' '
                                            . ($period->name ?? '')
                                            . ' '
                                            . ($period->month_name ?? '')
                                            . ' '
                                            . ($period->year ?? '')
                                            . ' '
                                            . ($creatorName ?? '')
                                        )
                                    ) }}
                                "
                            >

                                {{-- NUMBER --}}

                                <td class="text-center">

                                    <span class="row-number">
                                        {{ $payrollPeriods->firstItem() + $loop->index }}
                                    </span>

                                </td>


                                {{-- PERIOD --}}

                                <td>

                                    <div class="fw-semibold text-dark">

                                        {{ $period->name
                                            ?? 'Payroll Period' }}

                                    </div>

                                    <div class="small text-muted">

                                        <i class="fas fa-hashtag me-1"></i>

                                        {{ $period->period_code
                                            ?? 'N/A' }}

                                    </div>

                                    @if($period->description)

                                        <div class="small text-muted mt-1">

                                            <i class="fas fa-info-circle me-1"></i>

                                            {{ \Illuminate\Support\Str::limit(
                                                $period->description,
                                                60
                                            ) }}

                                        </div>

                                    @endif

                                </td>


                                {{-- MONTH --}}

                                <td>

                                    @if($period->month)

                                        {{ \Carbon\Carbon::create()
                                            ->month(
                                                (int) $period->month
                                            )
                                            ->format('F') }}

                                    @else

                                        N/A

                                    @endif

                                </td>


                                {{-- YEAR --}}

                                <td>

                                    {{ $period->year ?? 'N/A' }}

                                </td>


                                {{-- STAFF --}}

                                <td class="text-center">

                                    <span class="staff-count">

                                        <i class="fas fa-users me-1"></i>

                                        {{ $period->staff_count ?? 0 }}

                                    </span>

                                </td>


                                {{-- STATUS --}}

                                <td>

                                    <span
                                        class="status-badge {{ $status['class'] }}"
                                    >

                                        <i
                                            class="fas {{ $status['icon'] }} me-1"
                                        ></i>

                                        {{ $status['label'] }}

                                    </span>

                                </td>


                                {{-- CREATED BY --}}

                                <td>

                                    <div class="fw-semibold">

                                        {{ $creatorName ?: 'N/A' }}

                                    </div>

                                    @if($period->created_at)

                                        <small class="text-muted">

                                            {{ $period->created_at->format(
                                                'd M Y'
                                            ) }}

                                        </small>

                                    @endif

                                </td>


                                {{-- ACTIONS --}}

                                <td class="text-end">

                                    <div class="btn-group">

                                        {{-- VIEW --}}

                                        <a
                                            href="{{ route(
                                                'payroll-periods.show',
                                                $period->id
                                            ) }}"
                                            class="btn btn-sm btn-outline-primary"
                                            title="View Payroll Period"
                                        >

                                            <i class="fas fa-eye"></i>

                                        </a>


                                        {{-- EDIT --}}

                                        @if(
                                            method_exists(
                                                $period,
                                                'isEditable'
                                            )
                                            &&
                                            $period->isEditable()
                                        )

                                            <a
                                                href="{{ route(
                                                    'payroll-periods.edit',
                                                    $period->id
                                                ) }}"
                                                class="btn btn-sm btn-outline-secondary"
                                                title="Edit Payroll Period"
                                            >

                                                <i class="fas fa-edit"></i>

                                            </a>

                                        @endif


                                        {{-- DELETE --}}

                                        @if(
                                            method_exists(
                                                $period,
                                                'isDraft'
                                            )
                                            &&
                                            $period->isDraft()
                                        )

                                            <form
                                                action="{{ route(
                                                    'payroll-periods.destroy',
                                                    $period->id
                                                ) }}"
                                                method="POST"
                                                class="d-inline delete-period-form"
                                            >

                                                @csrf

                                                @method('DELETE')

                                                <button
                                                    type="submit"
                                                    class="btn btn-sm btn-outline-danger"
                                                    title="Delete Payroll Period"
                                                >

                                                    <i class="fas fa-trash"></i>

                                                </button>

                                            </form>

                                        @endif

                                    </div>

                                </td>

                            </tr>

                        @empty

                            <tr>

                                <td
                                    colspan="8"
                                    class="text-center py-5"
                                >

                                    <div class="empty-state">

                                        <div class="empty-icon">

                                            <i class="fas fa-calendar-times"></i>

                                        </div>

                                        <h5 class="mt-3 mb-2">
                                            No Payroll Periods Found
                                        </h5>

                                        <p class="text-muted mb-3">
                                            There are currently no payroll
                                            periods available.
                                        </p>

                                        <a
                                            href="{{ route(
                                                'payroll-periods.create'
                                            ) }}"
                                            class="btn btn-primary"
                                        >

                                            <i class="fas fa-plus-circle me-1"></i>

                                            Create Payroll Period

                                        </a>

                                    </div>

                                </td>

                            </tr>

                        @endforelse


                        {{-- NO SEARCH RESULTS --}}

                        <tr
                            id="noSearchResults"
                            style="display:none;"
                        >

                            <td
                                colspan="8"
                                class="text-center py-5"
                            >

                                <i
                                    class="fas fa-search-minus fa-2x text-muted mb-3"
                                ></i>

                                <h6>
                                    No Matching Payroll Periods
                                </h6>

                                <p class="text-muted mb-0">
                                    Try changing your search or status filter.
                                </p>

                            </td>

                        </tr>

                    </tbody>

                </table>

            </div>

        </div>


        {{-- ========================================================
            PAGINATION
        ========================================================= --}}

        @if($payrollPeriods->hasPages())

            <div class="card-footer bg-white border-0">

                <div class="d-flex flex-wrap justify-content-between align-items-center">

                    <div class="text-muted small">

                        Showing
                        <strong>
                            {{ $payrollPeriods->firstItem() ?? 0 }}
                        </strong>

                        to

                        <strong>
                            {{ $payrollPeriods->lastItem() ?? 0 }}
                        </strong>

                        of

                        <strong>
                            {{ $payrollPeriods->total() }}
                        </strong>

                        payroll periods

                    </div>


                    <div class="mt-2 mt-md-0">

                        {{ $payrollPeriods->links() }}

                    </div>

                </div>

            </div>

        @else

            @if($payrollPeriods->count())

                <div class="card-footer bg-white border-0">

                    <div class="text-muted small">

                        Showing
                        <strong>
                            {{ $payrollPeriods->count() }}
                        </strong>

                        payroll period(s)

                    </div>

                </div>

            @endif

        @endif

    </div>

</div>

@endsection


{{-- ================================================================
    STYLES
================================================================ --}}

@push('styles')

<style>

    .summary-card {
        min-height: 120px;
        border-radius: 12px;
        padding: 20px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        background: #fff;
        box-shadow: 0 3px 12px rgba(0,0,0,.06);
        border-left: 4px solid;
        transition: .2s ease;
    }

    .summary-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 18px rgba(0,0,0,.09);
    }

    .summary-card h3 {
        margin: 4px 0 0;
        font-size: 26px;
        font-weight: 700;
    }

    .summary-label {
        font-size: 13px;
        color: #6c757d;
        font-weight: 600;
    }

    .summary-icon {
        width: 48px;
        height: 48px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 20px;
    }


    .summary-primary {
        border-color: #1565c0;
    }

    .summary-primary h3,
    .summary-primary .summary-icon {
        color: #1565c0;
    }


    .summary-secondary {
        border-color: #6c757d;
    }

    .summary-secondary h3,
    .summary-secondary .summary-icon {
        color: #495057;
    }


    .summary-warning {
        border-color: #e65100;
    }

    .summary-warning h3,
    .summary-warning .summary-icon {
        color: #e65100;
    }


    .summary-orange {
        border-color: #f57c00;
    }

    .summary-orange h3,
    .summary-orange .summary-icon {
        color: #f57c00;
    }


    .summary-success {
        border-color: #2e7d32;
    }

    .summary-success h3,
    .summary-success .summary-icon {
        color: #2e7d32;
    }


    .summary-danger {
        border-color: #c62828;
    }

    .summary-danger h3,
    .summary-danger .summary-icon {
        color: #c62828;
    }


    .summary-paid {
        border-color: #00897b;
    }

    .summary-paid h3,
    .summary-paid .summary-icon {
        color: #00897b;
    }


    .summary-cancelled {
        border-color: #6a1b9a;
    }

    .summary-cancelled h3,
    .summary-cancelled .summary-icon {
        color: #6a1b9a;
    }


    .status-badge {
        display: inline-flex;
        align-items: center;
        padding: 6px 10px;
        border-radius: 20px;
        font-size: 12px;
        font-weight: 600;
        white-space: nowrap;
    }

    .status-draft {
        background: #e3f2fd;
        color: #0d47a1;
    }

    .status-processing {
        background: #fff3e0;
        color: #e65100;
    }

    .status-pending {
        background: #fff8e1;
        color: #8d6e00;
    }

    .status-approved {
        background: #e8f5e9;
        color: #2e7d32;
    }

    .status-rejected {
        background: #ffebee;
        color: #c62828;
    }

    .status-paid {
        background: #e0f2f1;
        color: #00695c;
    }

    .status-cancelled {
        background: #f3e5f5;
        color: #6a1b9a;
    }

    .status-default {
        background: #f1f3f5;
        color: #495057;
    }


    .staff-count {
        display: inline-flex;
        align-items: center;
        padding: 5px 9px;
        background: #f8f9fa;
        border-radius: 20px;
        color: #495057;
        font-weight: 600;
        font-size: 12px;
    }


    .table th {
        white-space: nowrap;
        font-size: 12px;
        text-transform: uppercase;
        color: #495057;
        border-top: none;
        background: #f8f9fa;
    }

    .table td {
        font-size: 13px;
        vertical-align: middle;
    }


    .table-hover tbody tr:hover {
        background: #f8fbff;
    }


    .btn {
        transition: .15s ease;
    }

    .btn:hover {
        transform: translateY(-1px);
    }


    .empty-state {
        padding: 25px;
    }

    .empty-icon {
        width: 70px;
        height: 70px;
        margin: auto;
        border-radius: 50%;
        background: #e3f2fd;
        color: #1565c0;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 28px;
    }


    .form-control,
    .form-select {
        min-height: 40px;
    }


    .form-control:focus,
    .form-select:focus {
        border-color: #42a5f5;
        box-shadow: 0 0 0 .2rem rgba(66,165,245,.15);
    }


    @media (max-width: 768px) {

        .summary-card {
            min-height: 100px;
            padding: 15px;
        }

        .summary-card h3 {
            font-size: 22px;
        }

        .table {
            min-width: 1100px;
        }

    }

</style>

@endpush


{{-- ================================================================
    JAVASCRIPT
================================================================ --}}

@push('scripts')

<script>

document.addEventListener(
    'DOMContentLoaded',
    function () {

        const searchInput =
            document.getElementById(
                'searchInput'
            );

        const statusFilter =
            document.getElementById(
                'statusFilter'
            );

        const resetButton =
            document.getElementById(
                'resetFilters'
            );

        const table =
            document.getElementById(
                'payrollTable'
            );

        const noResults =
            document.getElementById(
                'noSearchResults'
            );


        if (!table) {
            return;
        }


        const rows =
            Array.from(
                table.querySelectorAll(
                    'tbody tr[data-status]'
                )
            );


        function applyFilters() {

            const search =
                (
                    searchInput?.value
                    || ''
                )
                .toLowerCase()
                .trim();


            const selectedStatus =
                statusFilter?.value
                || 'all';


            let visibleRows = 0;


            rows.forEach(
                function (row) {

                    const rowText =
                        (
                            row.dataset.search
                            || ''
                        )
                        .toLowerCase();


                    const rowStatus =
                        (
                            row.dataset.status
                            || ''
                        )
                        .toLowerCase();


                    const matchesSearch =
                        !search
                        ||
                        rowText.includes(
                            search
                        );


                    const matchesStatus =
                        selectedStatus === 'all'
                        ||
                        rowStatus === selectedStatus;


                    const visible =
                        matchesSearch
                        &&
                        matchesStatus;


                    row.style.display =
                        visible
                            ? ''
                            : 'none';


                    if (visible) {
                        visibleRows++;
                    }

                }
            );


            if (noResults) {

                noResults.style.display =
                    visibleRows === 0
                        ? ''
                        : 'none';

            }

        }


        if (searchInput) {

            searchInput.addEventListener(
                'input',
                applyFilters
            );

        }


        if (statusFilter) {

            statusFilter.addEventListener(
                'change',
                applyFilters
            );

        }


        if (resetButton) {

            resetButton.addEventListener(
                'click',
                function () {

                    if (searchInput) {
                        searchInput.value = '';
                    }

                    if (statusFilter) {
                        statusFilter.value = 'all';
                    }

                    applyFilters();

                }
            );

        }


        /*
        |--------------------------------------------------------------------------
        | DELETE CONFIRMATION
        |--------------------------------------------------------------------------
        */

        document
            .querySelectorAll(
                '.delete-period-form'
            )
            .forEach(
                function (form) {

                    form.addEventListener(
                        'submit',
                        function (event) {

                            const confirmed =
                                confirm(
                                    'Are you sure you want to delete this payroll period?\n\n' +
                                    'This action cannot be undone.'
                                );


                            if (!confirmed) {

                                event.preventDefault();

                                return;

                            }


                            const button =
                                form.querySelector(
                                    'button[type="submit"]'
                                );


                            if (button) {

                                button.disabled = true;

                                button.innerHTML =
                                    '<i class="fas fa-spinner fa-spin"></i>';

                            }

                        }
                    );

                }
            );

    }
);

</script>

@endpush