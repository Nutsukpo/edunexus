@extends('layouts.master')

@section('title', 'Create Payroll Period')

@section('content')

<div class="container-fluid px-4 py-4">

```
{{-- ============================================================
    PAGE HEADER
============================================================= --}}

<div class="card border-0 shadow-sm mb-4">
    <div class="card-body">
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-3">

            <div>
                <div class="d-flex align-items-center gap-3">
                    <div class="period-icon">
                        <i class="fas fa-calendar-plus"></i>
                    </div>

                    <div>
                        <h4 class="fw-bold mb-1">
                            Create Payroll Period
                        </h4>

                        <p class="text-muted mb-0">
                            Create a new payroll period for staff salary processing.
                        </p>
                    </div>
                </div>
            </div>

            <div>
                <a href="{{ route('payroll-periods.index') }}"
                   class="btn btn-outline-secondary">
                    <i class="fas fa-arrow-left me-1"></i>
                    Back to Payroll Periods
                </a>
            </div>

        </div>
    </div>
</div>


{{-- ============================================================
    VALIDATION ERRORS
============================================================= --}}

@if($errors->any())

    <div class="alert alert-danger alert-dismissible fade show shadow-sm border-0">

        <div class="d-flex">

            <i class="fas fa-exclamation-circle fa-lg me-2 mt-1"></i>

            <div>

                <strong>Please correct the following errors:</strong>

                <ul class="mb-0 mt-2">

                    @foreach($errors->all() as $error)

                        <li>{{ $error }}</li>

                    @endforeach

                </ul>

            </div>

        </div>

        <button type="button"
                class="btn-close"
                data-bs-dismiss="alert">
        </button>

    </div>

@endif


{{-- ============================================================
    CREATE FORM
============================================================= --}}

<form action="{{ route('payroll-periods.store') }}"
      method="POST"
      id="payrollPeriodForm">

    @csrf

    <div class="row g-4">

        {{-- ====================================================
            BASIC INFORMATION
        ===================================================== --}}

        <div class="col-lg-8">

            <div class="card border-0 shadow-sm">

                <div class="card-header bg-white py-3">

                    <h6 class="fw-bold mb-0">

                        <i class="fas fa-info-circle text-primary me-2"></i>

                        Payroll Period Information

                    </h6>

                </div>


                <div class="card-body">

                    <div class="row g-3">

                        {{-- PERIOD CODE --}}

                        <div class="col-md-6">

                            <label for="period_code"
                                   class="form-label fw-semibold">

                                Period Code
                                <span class="text-danger">*</span>

                            </label>

                            <div class="input-group">

                                <span class="input-group-text">
                                    <i class="fas fa-hashtag"></i>
                                </span>

                                <input type="text"
                                       name="period_code"
                                       id="period_code"
                                       class="form-control @error('period_code') is-invalid @enderror"
                                       value="{{ old('period_code', $periodCode ?? '') }}"
                                       required>

                            </div>

                            @error('period_code')
                                <div class="invalid-feedback d-block">
                                    {{ $message }}
                                </div>
                            @enderror

                        </div>


                        {{-- NAME --}}

                        <div class="col-md-6">

                            <label for="name"
                                   class="form-label fw-semibold">

                                Payroll Period Name
                                <span class="text-danger">*</span>

                            </label>

                            <div class="input-group">

                                <span class="input-group-text">
                                    <i class="fas fa-file-invoice"></i>
                                </span>

                                <input type="text"
                                       name="name"
                                       id="name"
                                       class="form-control @error('name') is-invalid @enderror"
                                       value="{{ old('name') }}"
                                       placeholder="e.g. August 2026 Payroll"
                                       required>

                            </div>

                            @error('name')
                                <div class="invalid-feedback d-block">
                                    {{ $message }}
                                </div>
                            @enderror

                        </div>


                        {{-- ACADEMIC YEAR --}}

                        <div class="col-md-6">

                            <label for="academic_year_id"
                                   class="form-label fw-semibold">

                                Academic Year
                                <span class="text-danger">*</span>

                            </label>

                            <div class="input-group">

                                <span class="input-group-text">
                                    <i class="fas fa-graduation-cap"></i>
                                </span>

                                <select name="academic_year_id"
                                        id="academic_year_id"
                                        class="form-select @error('academic_year_id') is-invalid @enderror"
                                        required>

                                    <option value="">
                                        -- Select Academic Year --
                                    </option>

                                    @foreach($academicYears ?? [] as $id => $academicYear)

                                        <option value="{{ $id }}"
                                            {{ old('academic_year_id') == $id ? 'selected' : '' }}>

                                            {{ $academicYear }}

                                        </option>

                                    @endforeach

                                </select>

                            </div>

                            @error('academic_year_id')
                                <div class="invalid-feedback d-block">
                                    {{ $message }}
                                </div>
                            @enderror

                        </div>


                        {{-- MONTH --}}

                        <div class="col-md-3">

                            <label for="month"
                                   class="form-label fw-semibold">

                                Month
                                <span class="text-danger">*</span>

                            </label>

                            <select name="month"
                                    id="month"
                                    class="form-select @error('month') is-invalid @enderror"
                                    required>

                                <option value="">
                                    -- Month --
                                </option>

                                @foreach($months ?? [] as $monthNumber => $monthName)

                                    <option value="{{ $monthNumber }}"
                                        {{ old('month') == $monthNumber ? 'selected' : '' }}>

                                        {{ $monthName }}

                                    </option>

                                @endforeach

                            </select>

                            @error('month')
                                <div class="invalid-feedback d-block">
                                    {{ $message }}
                                </div>
                            @enderror

                        </div>


                        {{-- YEAR --}}

                        <div class="col-md-3">

                            <label for="year"
                                   class="form-label fw-semibold">

                                Year
                                <span class="text-danger">*</span>

                            </label>

                            <select name="year"
                                    id="year"
                                    class="form-select @error('year') is-invalid @enderror"
                                    required>

                                <option value="">
                                    -- Year --
                                </option>

                                @foreach($years ?? [] as $yearValue => $yearLabel)

                                    <option value="{{ $yearValue }}"
                                        {{ old('year', date('Y')) == $yearValue ? 'selected' : '' }}>

                                        {{ $yearLabel }}

                                    </option>

                                @endforeach

                            </select>

                            @error('year')
                                <div class="invalid-feedback d-block">
                                    {{ $message }}
                                </div>
                            @enderror

                        </div>


                        {{-- START DATE --}}

                        <div class="col-md-6">

                            <label for="start_date"
                                   class="form-label fw-semibold">

                                Start Date
                                <span class="text-danger">*</span>

                            </label>

                            <div class="input-group">

                                <span class="input-group-text">
                                    <i class="fas fa-calendar-day"></i>
                                </span>

                                <input type="date"
                                       name="start_date"
                                       id="start_date"
                                       class="form-control @error('start_date') is-invalid @enderror"
                                       value="{{ old('start_date') }}"
                                       required>

                            </div>

                            @error('start_date')
                                <div class="invalid-feedback d-block">
                                    {{ $message }}
                                </div>
                            @enderror

                        </div>


                        {{-- END DATE --}}

                        <div class="col-md-6">

                            <label for="end_date"
                                   class="form-label fw-semibold">

                                End Date
                                <span class="text-danger">*</span>

                            </label>

                            <div class="input-group">

                                <span class="input-group-text">
                                    <i class="fas fa-calendar-check"></i>
                                </span>

                                <input type="date"
                                       name="end_date"
                                       id="end_date"
                                       class="form-control @error('end_date') is-invalid @enderror"
                                       value="{{ old('end_date') }}"
                                       required>

                            </div>

                            @error('end_date')
                                <div class="invalid-feedback d-block">
                                    {{ $message }}
                                </div>
                            @enderror

                        </div>


                        {{-- PAYMENT DATE --}}

                        <div class="col-md-6">

                            <label for="payment_date"
                                   class="form-label fw-semibold">

                                Payment Date

                                <span class="text-muted fw-normal">
                                    (Optional)
                                </span>

                            </label>

                            <div class="input-group">

                                <span class="input-group-text">
                                    <i class="fas fa-money-check-alt"></i>
                                </span>

                                <input type="date"
                                       name="payment_date"
                                       id="payment_date"
                                       class="form-control @error('payment_date') is-invalid @enderror"
                                       value="{{ old('payment_date') }}">

                            </div>

                            <small class="text-muted">
                                Payment date cannot be earlier than the payroll end date.
                            </small>

                            @error('payment_date')
                                <div class="invalid-feedback d-block">
                                    {{ $message }}
                                </div>
                            @enderror

                        </div>


                        {{-- STATUS --}}

                        <div class="col-md-6">

                            <label for="status"
                                   class="form-label fw-semibold">

                                Status
                                <span class="text-danger">*</span>

                            </label>

                            <div class="input-group">

                                <span class="input-group-text">
                                    <i class="fas fa-tasks"></i>
                                </span>

                                <select name="status"
                                        id="status"
                                        class="form-select @error('status') is-invalid @enderror"
                                        required>

                                    <option value="">
                                        -- Select Status --
                                    </option>

                                    @foreach($statuses ?? [] as $statusValue => $statusLabel)

                                        <option value="{{ $statusValue }}"
                                            {{ old('status', 'draft') == $statusValue ? 'selected' : '' }}>

                                            {{ $statusLabel }}

                                        </option>

                                    @endforeach

                                </select>

                            </div>

                            @error('status')
                                <div class="invalid-feedback d-block">
                                    {{ $message }}
                                </div>
                            @enderror

                        </div>


                        {{-- DESCRIPTION --}}

                        <div class="col-12">

                            <label for="description"
                                   class="form-label fw-semibold">

                                Description

                                <span class="text-muted fw-normal">
                                    (Optional)
                                </span>

                            </label>

                            <textarea name="description"
                                      id="description"
                                      rows="4"
                                      class="form-control @error('description') is-invalid @enderror"
                                      placeholder="Enter any additional information about this payroll period...">{{ old('description') }}</textarea>

                            @error('description')
                                <div class="invalid-feedback d-block">
                                    {{ $message }}
                                </div>
                            @enderror

                        </div>

                    </div>

                </div>

            </div>

        </div>


        {{-- ====================================================
            INFORMATION PANEL
        ===================================================== --}}

        <div class="col-lg-4">

            {{-- WORKFLOW INFORMATION --}}

            <div class="card border-0 shadow-sm mb-4">

                <div class="card-header bg-white py-3">

                    <h6 class="fw-bold mb-0">

                        <i class="fas fa-project-diagram text-primary me-2"></i>

                        Payroll Workflow

                    </h6>

                </div>

                <div class="card-body">

                    <div class="workflow-step active">

                        <div class="workflow-icon">
                            <i class="fas fa-file"></i>
                        </div>

                        <div>
                            <strong>Draft</strong>
                            <small class="d-block text-muted">
                                Create and review the payroll period.
                            </small>
                        </div>

                    </div>


                    <div class="workflow-line"></div>


                    <div class="workflow-step">

                        <div class="workflow-icon">
                            <i class="fas fa-paper-plane"></i>
                        </div>

                        <div>
                            <strong>Submit for Approval</strong>
                            <small class="d-block text-muted">
                                Submit the completed payroll for management review.
                            </small>
                        </div>

                    </div>


                    <div class="workflow-line"></div>


                    <div class="workflow-step">

                        <div class="workflow-icon">
                            <i class="fas fa-check-circle"></i>
                        </div>

                        <div>
                            <strong>Approval</strong>
                            <small class="d-block text-muted">
                                The payroll moves through the approval workflow.
                            </small>
                        </div>

                    </div>


                    <div class="workflow-line"></div>


                    <div class="workflow-step">

                        <div class="workflow-icon">
                            <i class="fas fa-money-bill-wave"></i>
                        </div>

                        <div>
                            <strong>Paid</strong>
                            <small class="d-block text-muted">
                                Mark the payroll as paid after processing.
                            </small>
                        </div>

                    </div>

                </div>

            </div>


            {{-- IMPORTANT INFORMATION --}}

            <div class="card border-0 shadow-sm">

                <div class="card-header bg-white py-3">

                    <h6 class="fw-bold mb-0">

                        <i class="fas fa-lightbulb text-warning me-2"></i>

                        Important Information

                    </h6>

                </div>

                <div class="card-body">

                    <div class="info-item">

                        <i class="fas fa-check-circle text-success"></i>

                        <span>
                            Payroll periods should have valid start and end dates.
                        </span>

                    </div>


                    <div class="info-item">

                        <i class="fas fa-check-circle text-success"></i>

                        <span>
                            The end date cannot be earlier than the start date.
                        </span>

                    </div>


                    <div class="info-item">

                        <i class="fas fa-check-circle text-success"></i>

                        <span>
                            Payment date must be on or after the payroll end date.
                        </span>

                    </div>


                    <div class="info-item">

                        <i class="fas fa-check-circle text-success"></i>

                        <span>
                            Staff can be assigned after the payroll period is created.
                        </span>

                    </div>


                    <div class="info-item mb-0">

                        <i class="fas fa-check-circle text-success"></i>

                        <span>
                            Keep the period in Draft status while preparing it.
                        </span>

                    </div>

                </div>

            </div>

        </div>

    </div>


    {{-- ============================================================
        FORM ACTIONS
    ============================================================= --}}

    <div class="card border-0 shadow-sm mt-4">

        <div class="card-body">

            <div class="d-flex flex-wrap justify-content-between align-items-center gap-2">

                <div class="text-muted small">

                    <i class="fas fa-info-circle me-1"></i>

                    Fields marked with
                    <span class="text-danger">*</span>
                    are required.

                </div>


                <div class="d-flex gap-2">

                    <a href="{{ route('payroll-periods.index') }}"
                       class="btn btn-outline-secondary">

                        <i class="fas fa-times me-1"></i>

                        Cancel

                    </a>


                    <button type="reset"
                            class="btn btn-outline-warning">

                        <i class="fas fa-undo me-1"></i>

                        Reset

                    </button>


                    <button type="submit"
                            class="btn btn-primary"
                            id="savePayrollBtn">

                        <i class="fas fa-save me-1"></i>

                        Create Payroll Period

                    </button>

                </div>

            </div>

        </div>

    </div>

</form>
```

</div>

@endsection

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

    .card {
        border-radius: 12px;
    }

    .card-header {
        border-radius: 12px 12px 0 0 !important;
    }

    .form-label {
        font-size: 13px;
        color: #343a40;
        margin-bottom: 7px;
    }

    .form-control,
    .form-select,
    .input-group-text {
        min-height: 42px;
    }

    .input-group-text {
        background: #f8f9fa;
        color: #6c757d;
        border-color: #dee2e6;
    }

    textarea.form-control {
        min-height: 110px;
    }

    .form-control:focus,
    .form-select:focus {
        border-color: #86b7fe;
        box-shadow: 0 0 0 0.2rem rgba(13, 110, 253, 0.15);
    }

    .workflow-step {
        display: flex;
        align-items: flex-start;
        gap: 12px;
    }

    .workflow-icon {
        width: 36px;
        height: 36px;
        min-width: 36px;
        border-radius: 50%;
        background: #e9ecef;
        color: #6c757d;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 14px;
    }

    .workflow-step.active .workflow-icon {
        background: #e7f1ff;
        color: #0d6efd;
    }

    .workflow-step strong {
        display: block;
        font-size: 13px;
        margin-bottom: 2px;
    }

    .workflow-step small {
        font-size: 11px;
        line-height: 1.4;
    }

    .workflow-line {
        width: 1px;
        height: 22px;
        background: #dee2e6;
        margin: 4px 0 4px 17px;
    }

    .info-item {
        display: flex;
        align-items: flex-start;
        gap: 9px;
        margin-bottom: 14px;
        font-size: 12px;
        line-height: 1.5;
        color: #6c757d;
    }

    .info-item i {
        margin-top: 2px;
    }

    .btn {
        font-weight: 500;
    }

    @media (max-width: 768px) {

        .container-fluid {
            padding-left: 12px !important;
            padding-right: 12px !important;
        }

        .card-body {
            padding: 15px;
        }

        .d-flex.justify-content-between {
            align-items: flex-start !important;
        }

    }

</style>

@endpush

@push('scripts')

<script>

document.addEventListener('DOMContentLoaded', function () {

    const form = document.getElementById('payrollPeriodForm');

    const startDate = document.getElementById('start_date');

    const endDate = document.getElementById('end_date');

    const paymentDate = document.getElementById('payment_date');

    const saveButton = document.getElementById('savePayrollBtn');


    /*
    |--------------------------------------------------------------------------
    | Validate Date Sequence
    |--------------------------------------------------------------------------
    */

    function validateDates() {

        if (startDate.value && endDate.value) {

            if (endDate.value < startDate.value) {

                endDate.setCustomValidity(
                    'The end date must be on or after the start date.'
                );

            } else {

                endDate.setCustomValidity('');

            }

        } else {

            endDate.setCustomValidity('');

        }


        if (paymentDate.value && endDate.value) {

            if (paymentDate.value < endDate.value) {

                paymentDate.setCustomValidity(
                    'The payment date must be on or after the payroll end date.'
                );

            } else {

                paymentDate.setCustomValidity('');

            }

        } else {

            paymentDate.setCustomValidity('');

        }

    }


    startDate.addEventListener('change', function () {

        endDate.min = this.value;

        validateDates();

    });


    endDate.addEventListener('change', function () {

        paymentDate.min = this.value;

        validateDates();

    });


    paymentDate.addEventListener('change', validateDates);


    /*
    |--------------------------------------------------------------------------
    | Generate Name Automatically
    |--------------------------------------------------------------------------
    */

    const monthSelect = document.getElementById('month');

    const yearSelect = document.getElementById('year');

    const nameInput = document.getElementById('name');


    function generatePayrollName() {

        if (!monthSelect.value || !yearSelect.value) {
            return;
        }

        const monthName =
            monthSelect.options[monthSelect.selectedIndex].text;

        const year =
            yearSelect.value;

        if (
            !nameInput.value ||
            nameInput.dataset.generated === 'true'
        ) {

            nameInput.value =
                monthName + ' ' + year + ' Payroll';

            nameInput.dataset.generated = 'true';

        }

    }


    monthSelect.addEventListener(
        'change',
        generatePayrollName
    );


    yearSelect.addEventListener(
        'change',
        generatePayrollName
    );


    nameInput.addEventListener(
        'input',
        function () {

            this.dataset.generated = 'false';

        }
    );


    /*
    |--------------------------------------------------------------------------
    | Prevent Double Submission
    |--------------------------------------------------------------------------
    */

    form.addEventListener('submit', function (event) {

        validateDates();

        if (!form.checkValidity()) {

            return;

        }

        saveButton.disabled = true;

        saveButton.innerHTML =
            '<i class="fas fa-spinner fa-spin me-1"></i> Creating...';

    });

});

</script>

@endpush
