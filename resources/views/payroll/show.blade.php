@extends('layouts.master')

@section('title', 'Payroll Period Details')

@section('content')

<div class="container-fluid px-4 py-4">

    {{-- ============================================================
        FLASH MESSAGES
    ============================================================= --}}

    @if(session('success'))

        <div class="alert alert-success alert-dismissible fade show shadow-sm border-0">

            <i class="fas fa-check-circle me-2"></i>

            <strong>Success:</strong>

            {{ session('success') }}

            <button
                type="button"
                class="btn-close"
                data-bs-dismiss="alert">
            </button>

        </div>

    @endif


    @if(session('error'))

        <div class="alert alert-danger alert-dismissible fade show shadow-sm border-0">

            <i class="fas fa-exclamation-circle me-2"></i>

            <strong>Error:</strong>

            {{ session('error') }}

            <button
                type="button"
                class="btn-close"
                data-bs-dismiss="alert">
            </button>

        </div>

    @endif


    @if($errors->any())

        <div class="alert alert-danger alert-dismissible fade show shadow-sm border-0">

            <div class="d-flex align-items-start">

                <i class="fas fa-exclamation-triangle me-2 mt-1"></i>

                <div>

                    <strong>
                        Please correct the following:
                    </strong>

                    <ul class="mb-0 mt-2">

                        @foreach($errors->all() as $error)

                            <li>{{ $error }}</li>

                        @endforeach

                    </ul>

                </div>

            </div>

            <button
                type="button"
                class="btn-close"
                data-bs-dismiss="alert">
            </button>

        </div>

    @endif


    {{-- ============================================================
        PAGE HEADER
    ============================================================= --}}

    <div class="card border-0 shadow-sm mb-4">

        <div class="card-body">

            <div class="row align-items-center">

                <div class="col-lg-7">

                    <div class="d-flex align-items-center">

                        <div class="period-icon me-3">

                            <i class="fas fa-calendar-alt"></i>

                        </div>

                        <div>

                            <h4 class="fw-bold mb-1">

                                {{ $payrollPeriod->name }}

                            </h4>

                            <div class="text-muted small">

                                <span class="me-3">

                                    <i class="fas fa-hashtag me-1"></i>

                                    {{ $payrollPeriod->period_code }}

                                </span>

                                <span>

                                    {{ $payrollPeriod->month_name }}

                                    {{ $payrollPeriod->year }}

                                </span>

                            </div>

                        </div>

                    </div>

                </div>


                {{-- =================================================
                    PAGE ACTIONS

                    ONLY SUBMIT FOR APPROVAL.

                    NO APPROVE.
                    NO REJECT.
                ================================================== --}}

                <div class="col-lg-5 mt-3 mt-lg-0">

                    <div class="d-flex flex-wrap justify-content-lg-end gap-2">


                        {{-- ASSIGN STAFF --}}

                        @if($payrollPeriod->isEditable())

                            <a
                                href="{{ route(
                                    'payroll.assign-staff',
                                    $payrollPeriod->id
                                ) }}"
                                class="btn btn-primary">

                                <i class="fas fa-user-plus me-1"></i>

                                Assign Staff

                            </a>

                        @endif


                        {{-- EDIT --}}

                        @if($payrollPeriod->isEditable())

                            <a
                                href="{{ route(
                                    'payroll-periods.edit',
                                    $payrollPeriod->id
                                ) }}"
                                class="btn btn-warning text-white">

                                <i class="fas fa-edit me-1"></i>

                                Edit

                            </a>

                        @endif


                        {{-- SUBMIT FOR APPROVAL --}}

                        @if($payrollPeriod->canBeSubmittedForApproval())

                            <form
                                action="{{ route(
                                    'payroll-periods.submit',
                                    $payrollPeriod->id
                                ) }}"
                                method="POST"
                                class="submit-payroll-form d-inline">

                                @csrf

                                <button
                                    type="submit"
                                    class="btn btn-success">

                                    <i class="fas fa-paper-plane me-1"></i>

                                    Submit for Approval

                                </button>

                            </form>

                        @endif


                        {{-- EXPORT --}}

                        @if(Route::has('payroll-periods.export'))

                            <a
                                href="{{ route(
                                    'payroll-periods.export',
                                    $payrollPeriod->id
                                ) }}"
                                class="btn btn-outline-success">

                                <i class="fas fa-file-export me-1"></i>

                                Export

                            </a>

                        @endif


                        {{-- BACK --}}

                        <a
                            href="{{ route(
                                'payroll-periods.index'
                            ) }}"
                            class="btn btn-outline-secondary">

                            <i class="fas fa-arrow-left me-1"></i>

                            Back

                        </a>

                    </div>

                </div>

            </div>

        </div>

    </div>


    {{-- ============================================================
        STATUS
    ============================================================= --}}

    @php

        $status = strtolower(
            $payrollPeriod->status ?? 'draft'
        );

        $statusConfig = [

            'draft' => [
                'label' => 'Draft',
                'class' => 'status-draft',
                'icon' => 'fa-file'
            ],

            'processing' => [
                'label' => 'Processing',
                'class' => 'status-processing',
                'icon' => 'fa-cog'
            ],

            'pending_approval' => [
                'label' => 'Pending Approval',
                'class' => 'status-pending',
                'icon' => 'fa-clock'
            ],

            'approved' => [
                'label' => 'Approved',
                'class' => 'status-approved',
                'icon' => 'fa-check-circle'
            ],

            'rejected' => [
                'label' => 'Rejected',
                'class' => 'status-rejected',
                'icon' => 'fa-times-circle'
            ],

            'paid' => [
                'label' => 'Paid',
                'class' => 'status-paid',
                'icon' => 'fa-money-bill-wave'
            ],

            'cancelled' => [
                'label' => 'Cancelled',
                'class' => 'status-cancelled',
                'icon' => 'fa-ban'
            ],

        ];

        $currentStatus =
            $statusConfig[$status]
            ?? $statusConfig['draft'];

    @endphp


    <div class="card border-0 shadow-sm mb-4">

        <div class="card-body">

            <div class="d-flex flex-wrap align-items-center gap-4">


                {{-- STATUS --}}

                <div>

                    <span class="text-muted small d-block">
                        Current Status
                    </span>

                    <span class="status-badge {{ $currentStatus['class'] }}">

                        <i class="fas {{ $currentStatus['icon'] }} me-1"></i>

                        {{ $currentStatus['label'] }}

                    </span>

                </div>


                <div class="vr"></div>


                {{-- DATE RANGE --}}

                <div>

                    <span class="text-muted small d-block">
                        Payroll Period
                    </span>

                    <strong>

                        {{ $payrollPeriod->start_date->format('M d, Y') }}

                        -

                        {{ $payrollPeriod->end_date->format('M d, Y') }}

                    </strong>

                </div>


                <div class="vr"></div>


                {{-- STAFF COUNT --}}

                <div>

                    <span class="text-muted small d-block">
                        Staff
                    </span>

                    <strong id="staffCount">

                        {{ $summary['total_staff'] ?? 0 }}

                    </strong>

                </div>

            </div>

        </div>

    </div>


    {{-- ============================================================
        PENDING APPROVAL NOTICE

        NO APPROVE BUTTON
        NO REJECT BUTTON
    ============================================================= --}}

    @if($payrollPeriod->isPendingApproval())

        <div class="alert alert-warning border-0 shadow-sm mb-4">

            <div class="d-flex align-items-center">

                <i class="fas fa-clock fa-2x me-3"></i>

                <div>

                    <strong>
                        Payroll Pending Approval
                    </strong>

                    <div class="small mt-1">

                        This payroll period has been submitted
                        and is waiting for management approval.

                    </div>

                </div>

            </div>

        </div>

    @endif


    {{-- ============================================================
        REJECTED NOTICE
    ============================================================= --}}

    @if($payrollPeriod->isRejected())

        <div class="alert alert-danger border-0 shadow-sm mb-4">

            <div class="d-flex align-items-center">

                <i class="fas fa-times-circle fa-2x me-3"></i>

                <div>

                    <strong>
                        Payroll Period Rejected
                    </strong>

                    <div class="small mt-1">

                        Review the payroll, make the necessary
                        corrections, and submit it again.

                    </div>

                </div>

            </div>

        </div>

    @endif


    {{-- ============================================================
        BASIC INFORMATION
    ============================================================= --}}

    <div class="row g-4">


        {{-- BASIC INFORMATION --}}

        <div class="col-lg-6">

            <div class="card border-0 shadow-sm h-100">

                <div class="card-header bg-white">

                    <h6 class="fw-bold mb-0">

                        <i class="fas fa-info-circle text-primary me-2"></i>

                        Basic Information

                    </h6>

                </div>


                <div class="card-body">

                    <div class="row g-3">


                        <div class="col-md-6">

                            <label class="field-label">
                                Period Code
                            </label>

                            <div class="field-value">

                                {{ $payrollPeriod->period_code }}

                            </div>

                        </div>


                        <div class="col-md-6">

                            <label class="field-label">
                                Name
                            </label>

                            <div class="field-value">

                                {{ $payrollPeriod->name }}

                            </div>

                        </div>


                        <div class="col-md-6">

                            <label class="field-label">
                                Academic Year
                            </label>

                            <div class="field-value">

                                {{ $payrollPeriod->academicYear->name ?? 'N/A' }}

                            </div>

                        </div>


                        <div class="col-md-6">

                            <label class="field-label">
                                Month / Year
                            </label>

                            <div class="field-value">

                                {{ $payrollPeriod->month_name }}

                                {{ $payrollPeriod->year }}

                            </div>

                        </div>


                        @if($payrollPeriod->description)

                            <div class="col-12">

                                <label class="field-label">
                                    Description
                                </label>

                                <div class="description-box">

                                    {{ $payrollPeriod->description }}

                                </div>

                            </div>

                        @endif

                    </div>

                </div>

            </div>

        </div>


        {{-- DATE INFORMATION --}}

        <div class="col-lg-6">

            <div class="card border-0 shadow-sm h-100">

                <div class="card-header bg-white">

                    <h6 class="fw-bold mb-0">

                        <i class="fas fa-calendar text-info me-2"></i>

                        Date Information

                    </h6>

                </div>


                <div class="card-body">

                    <div class="row g-3">


                        <div class="col-md-6">

                            <label class="field-label">
                                Start Date
                            </label>

                            <div class="field-value">

                                {{ $payrollPeriod->start_date->format('F d, Y') }}

                            </div>

                        </div>


                        <div class="col-md-6">

                            <label class="field-label">
                                End Date
                            </label>

                            <div class="field-value">

                                {{ $payrollPeriod->end_date->format('F d, Y') }}

                            </div>

                        </div>


                        <div class="col-md-6">

                            <label class="field-label">
                                Payment Date
                            </label>

                            <div class="field-value">

                                @if($payrollPeriod->payment_date)

                                    {{ $payrollPeriod->payment_date->format('F d, Y') }}

                                @else

                                    <span class="text-muted">
                                        Not set
                                    </span>

                                @endif

                            </div>

                        </div>


                        <div class="col-md-6">

                            <label class="field-label">
                                Duration
                            </label>

                            <div class="field-value">

                                {{ $payrollPeriod->start_date->diffInDays(
                                    $payrollPeriod->end_date
                                ) + 1 }}

                                days

                            </div>

                        </div>

                    </div>

                </div>

            </div>

        </div>

    </div>


    {{-- ============================================================
        PAYROLL SUMMARY
    ============================================================= --}}

    <div class="card border-0 shadow-sm mt-4">

        <div class="card-header bg-white">

            <h6 class="fw-bold mb-0">

                <i class="fas fa-chart-pie text-success me-2"></i>

                Payroll Summary

            </h6>

        </div>


        <div class="card-body">

            <div class="row g-3">


                <div class="col-md-3">

                    <div class="summary-card">

                        <span>
                            Total Staff
                        </span>

                        <strong>

                            {{ $summary['total_staff'] ?? 0 }}

                        </strong>

                    </div>

                </div>


                <div class="col-md-3">

                    <div class="summary-card">

                        <span>
                            Gross Pay
                        </span>

                        <strong>

                            {{ number_format(
                                $summary['total_gross'] ?? 0,
                                2
                            ) }}

                        </strong>

                    </div>

                </div>


                <div class="col-md-3">

                    <div class="summary-card">

                        <span>
                            Total Deductions
                        </span>

                        <strong>

                            {{ number_format(
                                $summary['total_deductions'] ?? 0,
                                2
                            ) }}

                        </strong>

                    </div>

                </div>


                <div class="col-md-3">

                    <div class="summary-card summary-net">

                        <span>
                            Net Pay
                        </span>

                        <strong>

                            {{ number_format(
                                $summary['total_net'] ?? 0,
                                2
                            ) }}

                        </strong>

                    </div>

                </div>

            </div>

        </div>

    </div>


    {{-- ============================================================
        STAFF SALARY DETAILS
    ============================================================= --}}

    <div class="card border-0 shadow-sm mt-4">

        <div class="card-header bg-white">

            <div class="row align-items-center">

                <div class="col-md-7">

                    <h6 class="fw-bold mb-1">

                        <i class="fas fa-users text-primary me-2"></i>

                        Staff Salary Details

                        <span
                            class="badge bg-light text-dark ms-2"
                            id="staffTableCount">

                            {{ count($staffWithSalaries ?? []) }}

                        </span>

                    </h6>


                    @if($payrollPeriod->isEditable())

                        <small class="text-muted">

                            You can remove staff from this payroll
                            before submitting it for approval.

                        </small>

                    @endif

                </div>


                <div class="col-md-5 mt-2 mt-md-0">

                    <div class="input-group input-group-sm">

                        <span class="input-group-text bg-white">

                            <i class="fas fa-search"></i>

                        </span>

                        <input
                            type="text"
                            id="staffSearch"
                            class="form-control"
                            placeholder="Search staff...">

                    </div>

                </div>

            </div>

        </div>


        <div class="card-body p-0">

            <div class="table-responsive">

                <table
                    class="table table-hover mb-0"
                    id="staffTable">

                    <thead class="table-light">

                        <tr>

                            <th>#</th>

                            <th>Staff Name</th>

                            <th>Position</th>

                            <th class="text-end">
                                Basic Salary
                            </th>

                            <th class="text-end">
                                Allowances
                            </th>

                            <th class="text-end">
                                Gross Pay
                            </th>

                            <th class="text-end">
                                Tax
                            </th>

                            <th class="text-end">
                                Pension
                            </th>

                            <th class="text-end">
                                Deductions
                            </th>

                            <th class="text-end">
                                Net Pay
                            </th>

                            <th class="text-center">
                                Worked Days
                            </th>

                            @if($payrollPeriod->isEditable())

                                <th class="text-center">
                                    Action
                                </th>

                            @endif

                        </tr>

                    </thead>


                    <tbody id="staffTableBody">

                        @forelse(
                            $staffWithSalaries ?? []
                            as $item
                        )

                            @php

                                $staff = $item['staff'];

                                $staffId = $staff->id;

                                $staffName = trim(
                                    ($staff->last_name ?? '') .
                                    ' ' .
                                    ($staff->first_name ?? '')
                                );

                            @endphp


                            <tr
                                data-staff-row="{{ $staffId }}"
                                data-staff-id="{{ $staffId }}">


                                {{-- NUMBER --}}

                                <td class="row-number">

                                    {{ $loop->iteration }}

                                </td>


                                {{-- STAFF NAME --}}

                                <td>

                                    <div class="d-flex align-items-center">

                                        <div class="staff-avatar me-2">

                                            {{ strtoupper(
                                                substr(
                                                    $staff->first_name ?? 'S',
                                                    0,
                                                    1
                                                )
                                            ) }}

                                        </div>

                                        <strong class="staff-name">

                                            {{ $staffName }}

                                        </strong>

                                    </div>

                                </td>


                                {{-- POSITION --}}

                                <td>

                                    {{ $staff->position ?? 'N/A' }}

                                </td>


                                {{-- BASIC SALARY --}}

                                <td class="text-end">

                                    {{ number_format(
                                        $item['basic_salary'] ?? 0,
                                        2
                                    ) }}

                                </td>


                                {{-- ALLOWANCES --}}

                                <td class="text-end">

                                    {{ number_format(
                                        $item['allowances'] ?? 0,
                                        2
                                    ) }}

                                </td>


                                {{-- GROSS PAY --}}

                                <td class="text-end fw-bold">

                                    {{ number_format(
                                        $item['gross_pay'] ?? 0,
                                        2
                                    ) }}

                                </td>


                                {{-- TAX --}}

                                <td class="text-end">

                                    {{ number_format(
                                        $item['tax'] ?? 0,
                                        2
                                    ) }}

                                </td>


                                {{-- PENSION --}}

                                <td class="text-end">

                                    {{ number_format(
                                        $item['pension'] ?? 0,
                                        2
                                    ) }}

                                </td>


                                {{-- DEDUCTIONS --}}

                                <td class="text-end">

                                    {{ number_format(
                                        $item['deductions'] ?? 0,
                                        2
                                    ) }}

                                </td>


                                {{-- NET PAY --}}

                                <td class="text-end fw-bold text-success">

                                    {{ number_format(
                                        $item['net_pay'] ?? 0,
                                        2
                                    ) }}

                                </td>


                                {{-- WORKED DAYS --}}

                                <td class="text-center">

                                    {{ $item['worked_days'] ?? 0 }}

                                </td>


                                {{-- =================================================
                                    REMOVE STAFF

                                    IMPORTANT:
                                    THIS IS NOW POST.

                                    THERE IS NO @method('DELETE')
                                    AND NO _method FIELD.
                                ================================================== --}}

                                @if($payrollPeriod->isEditable())

                                    <td class="text-center">

                                        <form
                                            action="{{ route(
                                                'payroll-periods.remove-staff',
                                                $payrollPeriod->id
                                            ) }}"
                                            method="POST"
                                            class="remove-staff-form d-inline">

                                            @csrf

                                            <input
                                                type="hidden"
                                                name="staff_ids[]"
                                                value="{{ $staffId }}">

                                            <button
                                                type="submit"
                                                class="btn btn-sm btn-outline-danger remove-staff-btn"
                                                title="Remove {{ $staffName }}">

                                                <i class="fas fa-user-minus me-1"></i>

                                                Remove

                                            </button>

                                        </form>

                                    </td>

                                @endif

                            </tr>

                        @empty

                            <tr id="noStaffRow">

                                <td
                                    colspan="{{ $payrollPeriod->isEditable() ? 12 : 11 }}"
                                    class="text-center py-5">

                                    <i class="fas fa-users fa-3x text-muted mb-3"></i>

                                    <h6 class="fw-bold">
                                        No Staff Assigned
                                    </h6>

                                    <p class="text-muted">

                                        There are currently no staff
                                        members assigned to this payroll.

                                    </p>


                                    @if($payrollPeriod->isEditable())

                                        <a
                                            href="{{ route(
                                                'payroll.assign-staff',
                                                $payrollPeriod->id
                                            ) }}"
                                            class="btn btn-primary btn-sm">

                                            <i class="fas fa-user-plus me-1"></i>

                                            Assign Staff

                                        </a>

                                    @endif

                                </td>

                            </tr>

                        @endforelse

                    </tbody>


                    {{-- ========================================================
                        TOTAL ROW
                    ========================================================= --}}

                    @if(count($staffWithSalaries ?? []) > 0)

                        <tfoot
                            class="table-light"
                            id="staffTotals">

                            <tr>

                                <th
                                    colspan="3"
                                    class="text-end">

                                    TOTAL

                                </th>


                                <th class="text-end">

                                    {{ number_format(
                                        $summary['total_basic_salary'] ?? 0,
                                        2
                                    ) }}

                                </th>


                                <th class="text-end">

                                    {{ number_format(
                                        $summary['total_allowances'] ?? 0,
                                        2
                                    ) }}

                                </th>


                                <th class="text-end">

                                    {{ number_format(
                                        $summary['total_gross'] ?? 0,
                                        2
                                    ) }}

                                </th>


                                <th class="text-end">

                                    {{ number_format(
                                        $summary['total_tax'] ?? 0,
                                        2
                                    ) }}

                                </th>


                                <th class="text-end">

                                    {{ number_format(
                                        $summary['total_pension'] ?? 0,
                                        2
                                    ) }}

                                </th>


                                <th class="text-end">

                                    {{ number_format(
                                        $summary['total_deductions'] ?? 0,
                                        2
                                    ) }}

                                </th>


                                <th class="text-end text-success">

                                    {{ number_format(
                                        $summary['total_net'] ?? 0,
                                        2
                                    ) }}

                                </th>


                                <th class="text-center">

                                    {{ number_format(
                                        $summary['total_worked_days'] ?? 0,
                                        0
                                    ) }}

                                </th>


                                @if($payrollPeriod->isEditable())

                                    <th></th>

                                @endif

                            </tr>

                        </tfoot>

                    @endif

                </table>

            </div>

        </div>

    </div>


    {{-- ============================================================
        SUBMIT FOR APPROVAL

        ONLY SUBMIT.
        NO APPROVE.
        NO REJECT.
    ============================================================= --}}

    <div class="card border-0 shadow-sm mt-4">

        <div class="card-body">

            <div class="row align-items-center">

                <div class="col-md-8">

                    <div class="d-flex align-items-center">

                        <div class="workflow-icon me-3">

                            <i class="fas fa-paper-plane"></i>

                        </div>

                        <div>

                            <h6 class="fw-bold mb-1">

                                Submit Payroll for Approval

                            </h6>


                            @if($payrollPeriod->isPendingApproval())

                                <p class="text-muted small mb-0">

                                    This payroll has already been
                                    submitted and is awaiting approval.

                                </p>

                            @elseif($payrollPeriod->isApproved())

                                <p class="text-muted small mb-0">

                                    This payroll has already been approved.

                                </p>

                            @elseif($payrollPeriod->isRejected())

                                <p class="text-muted small mb-0">

                                    Review the payroll and submit it
                                    again when it is ready.

                                </p>

                            @else

                                <p class="text-muted small mb-0">

                                    Review all staff salary details
                                    before submitting this payroll.

                                </p>

                            @endif

                        </div>

                    </div>

                </div>


                <div class="col-md-4 text-md-end mt-3 mt-md-0">


                    {{-- ONLY SUBMIT BUTTON --}}

                    @if($payrollPeriod->canBeSubmittedForApproval())

                        <form
                            action="{{ route(
                                'payroll-periods.submit',
                                $payrollPeriod->id
                            ) }}"
                            method="POST"
                            class="submit-payroll-form d-inline">

                            @csrf

                            <button
                                type="submit"
                                class="btn btn-success btn-lg">

                                <i class="fas fa-paper-plane me-2"></i>

                                Submit for Approval

                            </button>

                        </form>


                    @elseif($payrollPeriod->isPendingApproval())

                        <span class="badge bg-warning text-dark px-3 py-2">

                            <i class="fas fa-clock me-1"></i>

                            Awaiting Approval

                        </span>


                    @elseif($payrollPeriod->isApproved())

                        <span class="badge bg-success px-3 py-2">

                            <i class="fas fa-check-circle me-1"></i>

                            Approved

                        </span>

                    @endif

                </div>

            </div>

        </div>

    </div>

</div>

@endsection


{{-- ================================================================
    STYLES
================================================================ --}}

@push('styles')

<style>

.period-icon {

    width: 48px;

    height: 48px;

    border-radius: 12px;

    background: #e7f1ff;

    color: #0d6efd;

    display: flex;

    align-items: center;

    justify-content: center;

    font-size: 20px;

}


.status-badge {

    display: inline-flex;

    align-items: center;

    padding: 7px 12px;

    border-radius: 20px;

    font-size: 12px;

    font-weight: 600;

}


.status-draft {

    background: #e9ecef;

    color: #495057;

}


.status-processing {

    background: #fff3cd;

    color: #856404;

}


.status-pending {

    background: #fff3cd;

    color: #856404;

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

    background: #cff4fc;

    color: #055160;

}


.status-cancelled {

    background: #f8d7da;

    color: #842029;

}


.field-label {

    display: block;

    color: #6c757d;

    font-size: 11px;

    font-weight: 600;

    text-transform: uppercase;

    margin-bottom: 4px;

}


.field-value {

    font-size: 14px;

    font-weight: 600;

    color: #212529;

}


.description-box {

    background: #f8f9fa;

    border-radius: 8px;

    padding: 12px;

    font-size: 13px;

}


.summary-card {

    background: #f8f9fa;

    border-radius: 10px;

    padding: 16px;

    height: 100%;

}


.summary-card span {

    display: block;

    color: #6c757d;

    font-size: 12px;

    margin-bottom: 5px;

}


.summary-card strong {

    display: block;

    font-size: 20px;

}


.summary-net {

    background: #e8f5e9;

}


.summary-net strong {

    color: #198754;

}


.workflow-icon {

    width: 44px;

    height: 44px;

    border-radius: 10px;

    background: #e7f1ff;

    color: #0d6efd;

    display: flex;

    align-items: center;

    justify-content: center;

    flex-shrink: 0;

}


.staff-avatar {

    width: 34px;

    height: 34px;

    border-radius: 50%;

    background: #e7f1ff;

    color: #0d6efd;

    display: flex;

    align-items: center;

    justify-content: center;

    font-size: 12px;

    font-weight: 700;

    flex-shrink: 0;

}


.staff-name {

    white-space: nowrap;

}


.table th {

    white-space: nowrap;

    font-size: 11px;

    text-transform: uppercase;

    vertical-align: middle;

}


.table td {

    vertical-align: middle;

    font-size: 13px;

}


.remove-staff-btn {

    min-width: 90px;

}


.remove-staff-btn:hover {

    transform: translateY(-1px);

}


.btn {

    font-weight: 500;

}


@media (max-width: 992px) {

    .table {

        min-width: 1200px;

    }

}


@media (max-width: 768px) {

    .vr {

        display: none;

    }

}

</style>

@endpush


{{-- ================================================================
    JAVASCRIPT
================================================================ --}}

@push('scripts')

<script>

document.addEventListener('DOMContentLoaded', function () {


    /* ============================================================
       STAFF SEARCH
    ============================================================ */

    const searchInput =
        document.getElementById('staffSearch');

    const staffTable =
        document.getElementById('staffTable');


    if (searchInput && staffTable) {

        searchInput.addEventListener('input', function () {

            const search =
                this.value
                    .toLowerCase()
                    .trim();


            const rows =
                staffTable.querySelectorAll(
                    'tbody tr[data-staff-row]'
                );


            rows.forEach(function (row) {

                const text =
                    row.textContent.toLowerCase();


                row.style.display =
                    text.includes(search)
                        ? ''
                        : 'none';

            });

        });

    }


    /* ============================================================
       REMOVE STAFF
       
       IMPORTANT:
       
       The corrected route is POST:
       
       POST /payroll-periods/{id}/remove-staff
       
       Therefore there is NO:
       
       @method('DELETE')
       
       and there is NO:
       
       _method=DELETE
       
       The form submits:
       
       staff_ids[]
    ============================================================ */

    document
        .querySelectorAll('.remove-staff-form')
        .forEach(function (form) {


            form.addEventListener(
                'submit',
                function (event) {


                    event.preventDefault();


                    const row =
                        form.closest('tr');


                    if (!row) {

                        return;

                    }


                    const staffName =
                        row
                            .querySelector('.staff-name')
                            ?.textContent
                            ?.trim()
                            || 'this staff member';


                    const confirmed =
                        confirm(
                            'Are you sure you want to remove ' +
                            staffName +
                            ' from this payroll period?\n\n' +
                            'The staff member will be removed from ' +
                            'this payroll only. The staff record itself ' +
                            'will NOT be deleted.'
                        );


                    if (!confirmed) {

                        return;

                    }


                    const button =
                        form.querySelector(
                            '.remove-staff-btn'
                        );


                    if (button) {

                        button.disabled = true;

                        button.innerHTML =
                            '<i class="fas fa-spinner fa-spin me-1"></i> Removing...';

                    }


                    /*
                    |--------------------------------------------------------------------------
                    | Submit the form using fetch()
                    |--------------------------------------------------------------------------
                    |
                    | This allows us to wait for the server response before removing
                    | the row from the table.
                    |
                    */

                    const formData =
                        new FormData(form);


                    fetch(
                        form.action,
                        {
                            method: 'POST',

                            body: formData,

                            headers: {
                                'X-Requested-With': 'XMLHttpRequest',
                                'Accept': 'application/json'
                            },

                            credentials: 'same-origin'
                        }
                    )
                    .then(function (response) {


                        /*
                        |--------------------------------------------------------------------------
                        | Successful response
                        |--------------------------------------------------------------------------
                        */

                        if (
                            response.ok ||
                            response.redirected
                        ) {

                            return response;

                        }


                        throw new Error(
                            'Unable to remove staff member.'
                        );

                    })
                    .then(function () {


                        /*
                        |--------------------------------------------------------------------------
                        | Remove the row immediately from the table
                        |--------------------------------------------------------------------------
                        */

                        row.remove();


                        /*
                        |--------------------------------------------------------------------------
                        | Update visible row numbers
                        |--------------------------------------------------------------------------
                        */

                        renumberStaffRows();


                        /*
                        |--------------------------------------------------------------------------
                        | Update staff counters
                        |--------------------------------------------------------------------------
                        */

                        updateStaffCounters();


                        /*
                        |--------------------------------------------------------------------------
                        | If there are no staff remaining,
                        | display the empty-table message.
                        |--------------------------------------------------------------------------
                        */

                        showEmptyStaffMessageIfNeeded();


                        /*
                        |--------------------------------------------------------------------------
                        | Show success message
                        |--------------------------------------------------------------------------
                        */

                        showRemovalMessage(
                            staffName
                        );


                        /*
                        |--------------------------------------------------------------------------
                        | IMPORTANT
                        |--------------------------------------------------------------------------
                        |
                        | Reload after a short delay.
                        |
                        | This makes the page pull the actual data from
                        | the database again and refresh:
                        |
                        | - Staff list
                        | - Staff count
                        | - Gross pay
                        | - Deductions
                        | - Net pay
                        | - Total salary
                        |
                        */

                        setTimeout(function () {

                            window.location.reload();

                        }, 700);

                    })
                    .catch(function (error) {


                        console.error(
                            'Payroll staff removal error:',
                            error
                        );


                        if (button) {

                            button.disabled = false;

                            button.innerHTML =
                                '<i class="fas fa-user-minus me-1"></i> Remove';

                        }


                        alert(
                            'The staff member could not be removed. ' +
                            'Please try again.'
                        );

                    });

                }
            );

        });


    /* ============================================================
       RENUMBER STAFF ROWS
    ============================================================ */

    function renumberStaffRows() {

        const rows =
            document.querySelectorAll(
                '#staffTableBody tr[data-staff-row]'
            );


        rows.forEach(function (row, index) {

            const numberCell =
                row.querySelector('.row-number');


            if (numberCell) {

                numberCell.textContent =
                    index + 1;

            }

        });

    }


    /* ============================================================
       UPDATE STAFF COUNTERS
    ============================================================ */

    function updateStaffCounters() {

        const rows =
            document.querySelectorAll(
                '#staffTableBody tr[data-staff-row]'
            );


        const count =
            rows.length;


        const staffCount =
            document.getElementById(
                'staffCount'
            );


        const staffTableCount =
            document.getElementById(
                'staffTableCount'
            );


        if (staffCount) {

            staffCount.textContent =
                count;

        }


        if (staffTableCount) {

            staffTableCount.textContent =
                count;

        }

    }


    /* ============================================================
       EMPTY TABLE MESSAGE
    ============================================================ */

    function showEmptyStaffMessageIfNeeded() {

        const tbody =
            document.getElementById(
                'staffTableBody'
            );


        if (!tbody) {

            return;

        }


        const rows =
            tbody.querySelectorAll(
                'tr[data-staff-row]'
            );


        if (rows.length > 0) {

            return;

        }


        const existingEmptyRow =
            document.getElementById(
                'noStaffRow'
            );


        if (existingEmptyRow) {

            existingEmptyRow.style.display =
                '';

            return;

        }


        const emptyRow =
            document.createElement('tr');


        emptyRow.id =
            'noStaffRow';


        emptyRow.innerHTML = `

            <td
                colspan="{{ $payrollPeriod->isEditable() ? 12 : 11 }}"
                class="text-center py-5">

                <i class="fas fa-users fa-3x text-muted mb-3"></i>

                <h6 class="fw-bold">
                    No Staff Assigned
                </h6>

                <p class="text-muted">
                    There are currently no staff members
                    assigned to this payroll.
                </p>

                @if($payrollPeriod->isEditable())

                    <a
                        href="{{ route(
                            'payroll.assign-staff',
                            $payrollPeriod->id
                        ) }}"
                        class="btn btn-primary btn-sm">

                        <i class="fas fa-user-plus me-1"></i>

                        Assign Staff

                    </a>

                @endif

            </td>

        `;


        tbody.appendChild(
            emptyRow
        );

    }


    /* ============================================================
       SUCCESS MESSAGE
    ============================================================ */

    function showRemovalMessage(staffName) {

        const container =
            document.querySelector(
                '.container-fluid'
            );


        if (!container) {

            return;

        }


        const message =
            document.createElement('div');


        message.className =
            'alert alert-success alert-dismissible fade show shadow-sm border-0';


        message.innerHTML = `

            <i class="fas fa-check-circle me-2"></i>

            <strong>Success:</strong>

            ${escapeHtml(staffName)}
            has been removed from this payroll period.

            <button
                type="button"
                class="btn-close"
                data-bs-dismiss="alert">
            </button>

        `;


        container.insertBefore(
            message,
            container.firstElementChild
        );

    }


    /* ============================================================
       HTML ESCAPE
    ============================================================ */

    function escapeHtml(value) {

        const div =
            document.createElement('div');

        div.textContent =
            value;

        return div.innerHTML;

    }


    /* ============================================================
       SUBMIT FOR APPROVAL
    ============================================================ */

    document
        .querySelectorAll('.submit-payroll-form')
        .forEach(function (form) {


            form.addEventListener(
                'submit',
                function (event) {


                    const confirmed =
                        confirm(
                            'Are you sure you want to submit this payroll period for approval?\n\n' +
                            'Please make sure all staff salary details are correct before continuing.'
                        );


                    if (!confirmed) {

                        event.preventDefault();

                        return false;

                    }


                    const button =
                        form.querySelector(
                            'button[type="submit"]'
                        );


                    if (button) {

                        button.disabled = true;

                        button.innerHTML =
                            '<i class="fas fa-spinner fa-spin me-2"></i> Submitting...';

                    }

                }
            );

        });


});

</script>

@endpush