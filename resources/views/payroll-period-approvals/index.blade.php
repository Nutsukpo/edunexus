@extends('layouts.master')

@section('title', 'Payroll Period Approvals')

@section('content')

<div class="container-fluid py-4">

    {{-- ============================================================
         HEADER
    ============================================================ --}}
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4">

        <div>
            <h3 class="fw-bold text-primary mb-1">
                <i class="fas fa-file-invoice-dollar me-2"></i>
                Payroll Period Approvals
            </h3>

            <p class="text-muted mb-0">
                View and manage all payroll periods and their approval status.
            </p>
        </div>

        <div class="mt-3 mt-md-0">
            <a
                href="{{ route('payroll-periods.index') }}"
                class="btn btn-outline-secondary"
            >
                <i class="fas fa-arrow-left me-1"></i>
                Payroll Periods
            </a>
        </div>

    </div>


    {{-- ============================================================
         FLASH MESSAGES
    ============================================================ --}}
    @foreach(['success', 'error', 'warning'] as $type)

        @if(session($type))

            <div
                class="alert
                {{ $type === 'success'
                    ? 'alert-success'
                    : ($type === 'error'
                        ? 'alert-danger'
                        : 'alert-warning')
                }}
                alert-dismissible fade show"
            >

                <i
                    class="fas
                    {{ $type === 'success'
                        ? 'fa-check-circle'
                        : ($type === 'error'
                            ? 'fa-exclamation-circle'
                            : 'fa-exclamation-triangle')
                    }}
                    me-2"
                ></i>

                {{ session($type) }}

                <button
                    type="button"
                    class="btn-close"
                    aria-label="Close"
                    onclick="this.closest('.alert').remove()"
                ></button>

            </div>

        @endif

    @endforeach


    {{-- ============================================================
         FILTER CARD
    ============================================================ --}}
    <div class="card border-0 shadow-sm mb-4">

        <div class="card-header bg-white py-3">

            <div class="d-flex justify-content-between align-items-center">

                <div>
                    <h5 class="fw-bold mb-1">
                        <i class="fas fa-filter text-primary me-2"></i>
                        Payroll Periods
                    </h5>

                    <small class="text-muted">
                        Filter payroll periods by year, month or status.
                    </small>
                </div>

                <span class="badge bg-primary">
                    {{ $payrollPeriods->total() }}
                    {{ $payrollPeriods->total() == 1 ? 'Period' : 'Periods' }}
                </span>

            </div>

        </div>


        <div class="card-body">

            <form
                method="GET"
                action="{{ route('payroll-period-approvals.index') }}"
            >

                <div class="row g-3">

                    {{-- Academic Year --}}
                    <div class="col-xl-3 col-md-6">

                        <label class="form-label fw-semibold">
                            Academic Year
                        </label>

                        <select
                            name="academic_year_id"
                            class="form-select"
                        >

                            <option value="">
                                All Academic Years
                            </option>

                            @foreach(
                                \App\Models\AcademicYear::orderByDesc('name')->get()
                                as $academicYear
                            )

                                <option
                                    value="{{ $academicYear->id }}"
                                    {{ request('academic_year_id') == $academicYear->id ? 'selected' : '' }}
                                >
                                    {{ $academicYear->name }}
                                </option>

                            @endforeach

                        </select>

                    </div>


                    {{-- Year --}}
                    <div class="col-xl-2 col-md-6">

                        <label class="form-label fw-semibold">
                            Year
                        </label>

                        <select
                            name="year"
                            class="form-select"
                        >

                            <option value="">
                                All Years
                            </option>

                            @for($year = now()->year + 1; $year >= now()->year - 5; $year--)

                                <option
                                    value="{{ $year }}"
                                    {{ request('year') == $year ? 'selected' : '' }}
                                >
                                    {{ $year }}
                                </option>

                            @endfor

                        </select>

                    </div>


                    {{-- Month --}}
                    <div class="col-xl-2 col-md-6">

                        <label class="form-label fw-semibold">
                            Month
                        </label>

                        <select
                            name="month"
                            class="form-select"
                        >

                            <option value="">
                                All Months
                            </option>

                            @foreach(range(1, 12) as $month)

                                <option
                                    value="{{ $month }}"
                                    {{ request('month') == $month ? 'selected' : '' }}
                                >
                                    {{ DateTime::createFromFormat('!m', $month)->format('F') }}
                                </option>

                            @endforeach

                        </select>

                    </div>


                    {{-- Status --}}
                    <div class="col-xl-3 col-md-6">

                        <label class="form-label fw-semibold">
                            Status
                        </label>

                        <select
                            name="status"
                            class="form-select"
                        >

                            <option value="">
                                All Statuses
                            </option>

                            <option
                                value="draft"
                                {{ request('status') === 'draft' ? 'selected' : '' }}
                            >
                                Draft
                            </option>

                            <option
                                value="processing"
                                {{ request('status') === 'processing' ? 'selected' : '' }}
                            >
                                Processing
                            </option>

                            <option
                                value="pending_approval"
                                {{ request('status') === 'pending_approval' ? 'selected' : '' }}
                            >
                                Pending Approval
                            </option>

                            <option
                                value="approved"
                                {{ request('status') === 'approved' ? 'selected' : '' }}
                            >
                                Approved
                            </option>

                            <option
                                value="rejected"
                                {{ request('status') === 'rejected' ? 'selected' : '' }}
                            >
                                Rejected
                            </option>

                            <option
                                value="paid"
                                {{ request('status') === 'paid' ? 'selected' : '' }}
                            >
                                Paid
                            </option>

                        </select>

                    </div>


                    {{-- Buttons --}}
                    <div class="col-xl-2 col-md-6 d-flex align-items-end">

                        <div class="d-flex gap-2 w-100">

                            <button
                                type="submit"
                                class="btn btn-primary flex-grow-1"
                            >
                                <i class="fas fa-search me-1"></i>
                                Filter
                            </button>

                            <a
                                href="{{ route('payroll-period-approvals.index') }}"
                                class="btn btn-outline-secondary"
                                title="Clear Filters"
                            >
                                <i class="fas fa-sync-alt"></i>
                            </a>

                        </div>

                    </div>

                </div>

            </form>

        </div>

    </div>


    {{-- ============================================================
         PAYROLL TABLE
    ============================================================ --}}
    <div class="card border-0 shadow-sm">

        <div class="card-header bg-white py-3">

            <div class="d-flex flex-wrap justify-content-between align-items-center">

                <div>

                    <h5 class="fw-bold mb-1">
                        <i class="fas fa-list-alt text-primary me-2"></i>
                        Payroll Periods
                    </h5>

                    <small class="text-muted">
                        Showing
                        {{ $payrollPeriods->firstItem() ?? 0 }}
                        -
                        {{ $payrollPeriods->lastItem() ?? 0 }}
                        of
                        {{ $payrollPeriods->total() }}
                        payroll periods
                    </small>

                </div>

                @if(request()->hasAny([
                    'academic_year_id',
                    'year',
                    'month',
                    'status'
                ]))

                    <span class="badge bg-info text-dark">
                        <i class="fas fa-filter me-1"></i>
                        Filters Applied
                    </span>

                @endif

            </div>

        </div>


        <div class="card-body p-0">

            @if($payrollPeriods->count() > 0)

                <div class="table-responsive">

                    <table class="table table-hover align-middle mb-0">

                        <thead class="table-light">

                            <tr>

                                <th>
                                    Payroll Period
                                </th>

                                <th>
                                    Academic Year
                                </th>

                                <th>
                                    Date Range
                                </th>

                                <th class="text-center">
                                    Staff
                                </th>

                                <th class="text-end">
                                    Payroll Amount
                                </th>

                                <th>
                                    Status
                                </th>

                                <th class="text-end">
                                    Action
                                </th>

                            </tr>

                        </thead>


                        <tbody>

                            @foreach($payrollPeriods as $period)

                                @php

                                    /*
                                    |--------------------------------------------------------------------------
                                    | Payroll Amount
                                    |--------------------------------------------------------------------------
                                    |
                                    | IMPORTANT:
                                    | The controller calculates this from payroll_period_staff.net_pay
                                    | and exposes it as display_payroll_amount.
                                    |
                                    | There is NO reference to payroll_period_staff.amount here.
                                    |
                                    */

                                    $payrollAmount = (float) (
                                        $period->display_payroll_amount ?? 0
                                    );

                                    $status = strtolower(
                                        (string) ($period->status ?? 'draft')
                                    );

                                @endphp


                                <tr>

                                    {{-- Payroll Period --}}
                                    <td>

                                        <div class="fw-bold text-dark">
                                            {{ $period->name ?? 'Payroll Period' }}
                                        </div>

                                        @if($period->period_code)

                                            <small class="text-muted">
                                                {{ $period->period_code }}
                                            </small>

                                        @endif

                                    </td>


                                    {{-- Academic Year --}}
                                    <td>

                                        <span class="text-dark">
                                            {{ $period->academicYear?->name ?? 'N/A' }}
                                        </span>

                                    </td>


                                    {{-- Date Range --}}
                                    <td>

                                        <div>
                                            {{ $period->start_date
                                                ? $period->start_date->format('d M Y')
                                                : 'N/A'
                                            }}
                                        </div>

                                        <small class="text-muted">
                                            to
                                            {{ $period->end_date
                                                ? $period->end_date->format('d M Y')
                                                : 'N/A'
                                            }}
                                        </small>

                                    </td>


                                    {{-- Staff Count --}}
                                    <td class="text-center">

                                        <span class="badge bg-light text-dark border">

                                            <i class="fas fa-users me-1"></i>

                                            {{ number_format(
                                                $period->staff_count ?? 0
                                            ) }}

                                        </span>

                                    </td>


                                    {{-- PAYROLL AMOUNT --}}
                                    <td class="text-end">

                                        <div class="fw-bold text-success payroll-amount">

                                            GHS
                                            {{ number_format(
                                                $staff->pivot->net_pay ?? 0
                                            ) }}

                                        </div>

                                        <small class="text-muted">
                                            Net Payroll
                                        </small>

                                    </td>


                                    {{-- STATUS --}}
                                    <td>

                                        @switch($status)

                                            @case('draft')

                                                <span class="badge status-badge status-draft">
                                                    <i class="fas fa-file me-1"></i>
                                                    Draft
                                                </span>

                                                @break


                                            @case('processing')

                                                <span class="badge status-badge status-processing">
                                                    <i class="fas fa-cog me-1"></i>
                                                    Processing
                                                </span>

                                                @break


                                            @case('pending_approval')

                                                <span class="badge status-badge status-pending">
                                                    <i class="fas fa-clock me-1"></i>
                                                    Pending Approval
                                                </span>

                                                @break


                                            @case('approved')

                                                <span class="badge status-badge status-approved">
                                                    <i class="fas fa-check-circle me-1"></i>
                                                    Approved
                                                </span>

                                                @break


                                            @case('rejected')

                                                <span class="badge status-badge status-rejected">
                                                    <i class="fas fa-times-circle me-1"></i>
                                                    Rejected
                                                </span>

                                                @break


                                            @case('paid')

                                                <span class="badge status-badge status-paid">
                                                    <i class="fas fa-money-check-alt me-1"></i>
                                                    Paid
                                                </span>

                                                @break


                                            @case('cancelled')

                                                <span class="badge status-badge status-cancelled">
                                                    <i class="fas fa-ban me-1"></i>
                                                    Cancelled
                                                </span>

                                                @break


                                            @default

                                                <span class="badge bg-secondary">
                                                    {{ ucwords(
                                                        str_replace(
                                                            '_',
                                                            ' ',
                                                            $status
                                                        )
                                                    ) }}
                                                </span>

                                        @endswitch

                                    </td>


                                    {{-- ACTION --}}
                                    <td class="text-end">

                                        <a
                                            href="{{ route(
                                                'payroll-period-approvals.show',
                                                $period->id
                                            ) }}"
                                            class="btn btn-sm btn-primary"
                                        >

                                            <i class="fas fa-eye me-1"></i>
                                            Review

                                        </a>

                                    </td>

                                </tr>

                            @endforeach

                        </tbody>

                    </table>

                </div>


                {{-- ========================================================
                     PAGINATION
                ========================================================= --}}

                <div class="d-flex flex-wrap justify-content-between align-items-center p-3 border-top">

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

                    <div>
                        {{ $payrollPeriods->links() }}
                    </div>

                </div>


            @else

                {{-- ========================================================
                     EMPTY STATE
                ========================================================= --}}

                <div class="text-center py-5">

                    <div class="empty-icon mb-3">

                        <i class="fas fa-file-invoice-dollar"></i>

                    </div>

                    <h5 class="fw-bold">
                        No Payroll Periods Found
                    </h5>

                    <p class="text-muted mb-3">

                        @if(request()->hasAny([
                            'academic_year_id',
                            'year',
                            'month',
                            'status'
                        ]))

                            No payroll periods match the selected filters.

                        @else

                            There are currently no payroll periods available.

                        @endif

                    </p>


                    @if(request()->hasAny([
                        'academic_year_id',
                        'year',
                        'month',
                        'status'
                    ]))

                        <a
                            href="{{ route(
                                'payroll-period-approvals.index'
                            ) }}"
                            class="btn btn-outline-primary"
                        >
                            <i class="fas fa-sync-alt me-1"></i>
                            Clear Filters
                        </a>

                    @endif

                </div>

            @endif

        </div>

    </div>

</div>

@endsection


@push('styles')

<style>

    .payroll-amount {
        font-size: 15px;
        white-space: nowrap;
    }

    .status-badge {
        padding: 7px 10px;
        font-weight: 600;
        font-size: 11px;
        white-space: nowrap;
    }

    .status-draft {
        background: #e9ecef;
        color: #495057;
    }

    .status-processing {
        background: #cff4fc;
        color: #055160;
    }

    .status-pending {
        background: #fff3cd;
        color: #664d03;
    }

    .status-approved {
        background: #d1e7dd;
        color: #0f5132;
    }

    .status-rejected {
        background: #f8d7da;
        color: #842029;
    }

    .status-paid {
        background: #cfe2ff;
        color: #084298;
    }

    .status-cancelled {
        background: #e2e3e5;
        color: #41464b;
    }

    .empty-icon {
        width: 80px;
        height: 80px;
        margin: 0 auto;
        border-radius: 50%;
        background: #e7f1ff;
        color: #0d6efd;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 32px;
    }

    .table th {
        font-size: 11px;
        text-transform: uppercase;
        letter-spacing: .3px;
        white-space: nowrap;
    }

    .table td {
        font-size: 13px;
        vertical-align: middle;
    }

    .table-hover tbody tr:hover {
        background-color: #f8fbff;
    }

    .card {
        border-radius: 10px;
    }

    .form-label {
        font-size: 13px;
        margin-bottom: 6px;
    }

    .form-select,
    .form-control {
        font-size: 13px;
    }

    @media (max-width: 768px) {

        .table {
            min-width: 1100px;
        }

        .payroll-amount {
            font-size: 13px;
        }

    }

</style>

@endpush