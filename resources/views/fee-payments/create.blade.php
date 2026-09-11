@extends('layouts.master')

@section('title', 'Record Fee Payment')

@section('content')
<div class="container-fluid py-4 payment-page">

    {{-- =========================================================
         PAGE HEADER
    ========================================================== --}}
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">

        <div>
            <div class="small text-uppercase fw-bold text-primary mb-1">
                Finance & Fees
            </div>

            <h3 class="fw-bold mb-1">
                <i class="fas fa-money-bill-wave text-primary me-2"></i>
                Record Fee Payment
            </h3>

            <p class="text-muted mb-0">
                Select the academic year, class and term to load the correct
                student fee information.
            </p>
        </div>

        <a href="{{ route('fee-payments.index') }}"
           class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left me-1"></i>
            Back to Payments
        </a>

    </div>


    {{-- =========================================================
         FLASH / VALIDATION MESSAGES
    ========================================================== --}}

    @if($errors->any())

        <div class="alert alert-danger alert-dismissible fade show shadow-sm">

            <div class="fw-bold mb-1">
                <i class="fas fa-circle-exclamation me-2"></i>
                Please correct the following:
            </div>

            <ul class="mb-0">

                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach

            </ul>

            <button type="button"
                    class="btn-close"
                    data-bs-dismiss="alert">
            </button>

        </div>

    @endif


    @if(session('error'))

        <div class="alert alert-danger alert-dismissible fade show shadow-sm">

            <i class="fas fa-circle-exclamation me-2"></i>

            {{ session('error') }}

            <button type="button"
                    class="btn-close"
                    data-bs-dismiss="alert">
            </button>

        </div>

    @endif


    @if(session('success'))

        <div class="alert alert-success alert-dismissible fade show shadow-sm">

            <i class="fas fa-circle-check me-2"></i>

            {{ session('success') }}

            <button type="button"
                    class="btn-close"
                    data-bs-dismiss="alert">
            </button>

        </div>

    @endif


    {{-- =========================================================
         PAYMENT FORM
    ========================================================== --}}

    <form action="{{ route('fee-payments.store') }}"
          method="POST"
          id="paymentForm"
          novalidate>

        @csrf


        {{-- Hidden server-side values --}}

        <input type="hidden"
               name="student_class_assignment_id"
               id="studentAssignmentId"
               value="{{ old('student_class_assignment_id') }}">

        <input type="hidden"
               name="bill_sheet_id"
               id="billSheetId"
               value="{{ old('bill_sheet_id') }}">

        <input type="hidden"
               name="bill_sheet_item_id"
               id="billSheetItemId"
               value="{{ old('bill_sheet_item_id') }}">


        <div class="row g-4">


            {{-- =================================================
                 LEFT COLUMN
            ================================================== --}}

            <div class="col-xl-8">


                {{-- =================================================
                     STUDENT SELECTION
                ================================================== --}}

                <div class="card border-0 shadow-sm page-card">

                    <div class="card-header bg-white border-bottom py-3">

                        <div class="d-flex align-items-center gap-3">

                            <div class="section-icon bg-primary-subtle text-primary">
                                <i class="fas fa-user-graduate"></i>
                            </div>

                            <div>

                                <h5 class="fw-bold mb-0">
                                    Student Selection
                                </h5>

                                <small class="text-muted">
                                    Select the academic year, class and term.
                                </small>

                            </div>

                        </div>

                    </div>


                    <div class="card-body p-4">

                        <div class="row g-3">


                            {{-- =================================================
                                 ACADEMIC YEAR
                            ================================================== --}}

                            <div class="col-md-6">

                                <label for="academicYearSelect"
                                       class="form-label fw-semibold">

                                    Academic Year

                                    <span class="text-danger">*</span>

                                </label>


                                <select name="academic_year_id"
                                        id="academicYearSelect"
                                        class="form-select"
                                        required>

                                    <option value="">
                                        Select Academic Year
                                    </option>


                                    @foreach($academicYears as $year)

                                        <option value="{{ $year->id }}"
                                            {{ old('academic_year_id', $currentAcademicYearId ?? '') == $year->id ? 'selected' : '' }}>

                                            {{ $year->name }}

                                        </option>

                                    @endforeach

                                </select>


                                <div class="form-text">
                                    Changing the academic year will reload
                                    only the terms belonging to that year.
                                </div>

                            </div>


                            {{-- =================================================
                                 CLASS
                            ================================================== --}}

                            <div class="col-md-6">

                                <label for="classSelect"
                                       class="form-label fw-semibold">

                                    Class

                                    <span class="text-danger">*</span>

                                </label>


                                <select name="student_class_id"
                                        id="classSelect"
                                        class="form-select"
                                        required>

                                    <option value="">
                                        Select Class
                                    </option>


                                    @foreach($classes as $class)

                                        <option value="{{ $class->id }}"
                                            {{ old('student_class_id') == $class->id ? 'selected' : '' }}>

                                            {{ $class->name }}

                                            @if(isset($class->students_count))
                                                ({{ $class->students_count }} students)
                                            @endif

                                        </option>

                                    @endforeach

                                </select>

                            </div>


                            {{-- =================================================
                                 TERM
                            ================================================== --}}

                            <div class="col-md-6">

                                <label for="termSelect"
                                       class="form-label fw-semibold">

                                    Term

                                    <span class="text-danger">*</span>

                                </label>


                                <select name="term_id"
                                        id="termSelect"
                                        class="form-select"
                                        required
                                        disabled>

                                    <option value="">
                                        Select Academic Year first
                                    </option>

                                </select>


                                <div id="termLoading"
                                     class="small text-primary mt-2 d-none">

                                    <span class="spinner-border spinner-border-sm me-1"
                                          role="status">
                                    </span>

                                    Loading terms...

                                </div>


                                <div id="termError"
                                     class="small text-danger mt-2 d-none">
                                </div>


                                <div class="form-text">
                                    Only terms belonging to the selected
                                    academic year are displayed.
                                </div>

                            </div>


                            {{-- =================================================
                                 STUDENT
                            ================================================== --}}

                            <div class="col-md-6">

                                <label for="studentSelect"
                                       class="form-label fw-semibold">

                                    Student

                                    <span class="text-danger">*</span>

                                </label>


                                <select name="student_id"
                                        id="studentSelect"
                                        class="form-select"
                                        required
                                        disabled>

                                    <option value="">
                                        Select Academic Year, Class and Term first
                                    </option>

                                </select>


                                <div class="form-text">
                                    Only students currently assigned to the
                                    selected class, academic year and term
                                    are shown.
                                </div>

                            </div>


                            {{-- =================================================
                                 STUDENT INFORMATION
                            ================================================== --}}

                            <div class="col-12">

                                <div id="studentInfo"
                                     class="student-info d-none">

                                    <div class="row g-3">

                                        <div class="col-md-6">

                                            <div class="info-label">
                                                Student
                                            </div>

                                            <div class="info-value"
                                                 id="studentName">
                                                —
                                            </div>

                                        </div>


                                        <div class="col-md-6">

                                            <div class="info-label">
                                                Student ID
                                            </div>

                                            <div class="info-value"
                                                 id="studentNumber">
                                                —
                                            </div>

                                        </div>


                                        <div class="col-md-6">

                                            <div class="info-label">
                                                Current Class
                                            </div>

                                            <div class="info-value"
                                                 id="studentClass">
                                                —
                                            </div>

                                        </div>


                                        <div class="col-md-6">

                                            <div class="info-label">
                                                Academic Year
                                            </div>

                                            <div class="info-value"
                                                 id="studentYear">
                                                —
                                            </div>

                                        </div>


                                        <div class="col-md-6">

                                            <div class="info-label">
                                                Term
                                            </div>

                                            <div class="info-value"
                                                 id="studentTerm">
                                                —
                                            </div>

                                        </div>

                                    </div>

                                </div>

                            </div>

                        </div>

                    </div>

                </div>



                {{-- =================================================
                     APPROVED BILL SHEET
                ================================================== --}}

                <div id="billCard"
                     class="card border-0 shadow-sm page-card mt-4 d-none">


                    <div class="card-header bg-white border-bottom py-3">

                        <div class="d-flex align-items-center gap-3">

                            <div class="section-icon bg-success-subtle text-success">
                                <i class="fas fa-file-invoice-dollar"></i>
                            </div>

                            <div>

                                <h5 class="fw-bold mb-0">
                                    Approved Bill Sheet
                                </h5>

                                <small class="text-muted">
                                    Only approved and active Bill Sheets
                                    can be paid.
                                </small>

                            </div>

                        </div>

                    </div>


                    <div class="card-body p-4">


                        {{-- Loading --}}

                        <div id="billLoading"
                             class="text-center py-5 d-none">

                            <div class="spinner-border text-primary mb-3"
                                 role="status">

                                <span class="visually-hidden">
                                    Loading...
                                </span>

                            </div>

                            <div class="fw-semibold">
                                Loading approved Bill Sheet...
                            </div>

                            <small class="text-muted">
                                Please wait.
                            </small>

                        </div>


                        {{-- No Bill --}}

                        <div id="noBillMessage"
                             class="alert alert-warning d-none">

                            <i class="fas fa-triangle-exclamation me-2"></i>

                            <span id="noBillText">
                                No approved Bill Sheet was found for this
                                student for the selected term.
                            </span>

                        </div>


                        {{-- Multiple Bill Sheets --}}

                        <div id="billSelectorWrapper"
                             class="mb-4 d-none">

                            <label for="billSheetSelect"
                                   class="form-label fw-semibold">

                                Bill Sheet

                            </label>


                            <select id="billSheetSelect"
                                    class="form-select">
                            </select>


                            <div class="form-text">
                                Select the Bill Sheet against which this
                                payment should be recorded.
                            </div>

                        </div>


                        {{-- Bill Summary --}}

                        <div id="billSummary"
                             class="d-none">

                            <div class="row g-3">


                                <div class="col-md-4">

                                    <div class="amount-box">

                                        <div class="amount-label">
                                            Bill Amount
                                        </div>

                                        <div class="amount-value text-primary"
                                             id="billAmount">
                                            GHS 0.00
                                        </div>

                                    </div>

                                </div>


                                <div class="col-md-4">

                                    <div class="amount-box">

                                        <div class="amount-label">
                                            Amount Paid
                                        </div>

                                        <div class="amount-value text-success"
                                             id="billPaid">
                                            GHS 0.00
                                        </div>

                                    </div>

                                </div>


                                <div class="col-md-4">

                                    <div class="amount-box balance-box">

                                        <div class="amount-label">
                                            Outstanding Balance
                                        </div>

                                        <div class="amount-value text-danger"
                                             id="billBalance">
                                            GHS 0.00
                                        </div>

                                    </div>

                                </div>

                            </div>


                            {{-- Bill metadata --}}

                            <div class="bill-meta mt-4">

                                <div>

                                    <span>
                                        Bill Sheet
                                    </span>

                                    <strong id="billName">
                                        —
                                    </strong>

                                </div>


                                <div>

                                    <span>
                                        Generated
                                    </span>

                                    <strong id="billGeneratedDate">
                                        —
                                    </strong>

                                </div>


                                <div>

                                    <span>
                                        Due Date
                                    </span>

                                    <strong id="billDueDate">
                                        —
                                    </strong>

                                </div>


                                <div>

                                    <span>
                                        Approval
                                    </span>

                                    <span>

                                        <span class="badge bg-success"
                                              id="billStatus">
                                            Approved
                                        </span>

                                    </span>

                                </div>

                            </div>


                            {{-- Bill items --}}

                            <div id="billItemsWrapper"
                                 class="mt-4 d-none">

                                <div class="fw-bold mb-2">
                                    Bill Items
                                </div>


                                <div class="table-responsive">

                                    <table class="table table-sm align-middle mb-0">

                                        <thead>

                                            <tr>

                                                <th>
                                                    Item
                                                </th>

                                                <th class="text-center">
                                                    Qty
                                                </th>

                                                <th class="text-end">
                                                    Amount
                                                </th>

                                                <th class="text-end">
                                                    Total
                                                </th>

                                            </tr>

                                        </thead>


                                        <tbody id="billItemsBody">
                                        </tbody>

                                    </table>

                                </div>

                            </div>


                            <div class="alert alert-primary mt-4 mb-0">

                                <i class="fas fa-circle-info me-2"></i>

                                You can record a full or partial payment
                                up to the outstanding Bill Sheet balance.

                            </div>

                        </div>

                    </div>

                </div>



                {{-- =================================================
                     PAYMENT DETAILS
                ================================================== --}}

                <div id="paymentCard"
                     class="card border-0 shadow-sm page-card mt-4 d-none">


                    <div class="card-header bg-white border-bottom py-3">

                        <div class="d-flex align-items-center gap-3">

                            <div class="section-icon bg-info-subtle text-info">
                                <i class="fas fa-credit-card"></i>
                            </div>

                            <div>

                                <h5 class="fw-bold mb-0">
                                    Payment Details
                                </h5>

                                <small class="text-muted">
                                    Enter the payment information.
                                </small>

                            </div>

                        </div>

                    </div>


                    <div class="card-body p-4">

                        <div class="row g-3">


                            {{-- Amount --}}

                            <div class="col-md-6">

                                <label for="amountInput"
                                       class="form-label fw-semibold">

                                    Payment Amount (GHS)

                                    <span class="text-danger">*</span>

                                </label>


                                <div class="input-group">

                                    <span class="input-group-text">
                                        GHS
                                    </span>


                                    <input type="number"
                                           name="amount"
                                           id="amountInput"
                                           class="form-control"
                                           step="0.01"
                                           min="0.01"
                                           required>


                                    <button type="button"
                                            class="btn btn-outline-primary"
                                            id="fullPaymentBtn">

                                        Full

                                    </button>

                                </div>


                                <div class="form-text">

                                    Maximum:

                                    <strong id="maximumAmount">
                                        GHS 0.00
                                    </strong>

                                </div>

                            </div>


                            {{-- Payment Method --}}

                            <div class="col-md-6">

                                <label for="paymentMethod"
                                       class="form-label fw-semibold">

                                    Payment Method

                                    <span class="text-danger">*</span>

                                </label>


                                <select name="payment_method"
                                        id="paymentMethod"
                                        class="form-select"
                                        required>

                                    <option value="">
                                        Select Payment Method
                                    </option>


                                    @foreach($paymentMethods as $value => $label)

                                        <option value="{{ $value }}"
                                            {{ old('payment_method') == $value ? 'selected' : '' }}>

                                            {{ $label }}

                                        </option>

                                    @endforeach

                                </select>

                            </div>


                            {{-- Payment Date --}}

                            <div class="col-md-6">

                                <label for="paymentDate"
                                       class="form-label fw-semibold">

                                    Payment Date

                                    <span class="text-danger">*</span>

                                </label>


                                <input type="date"
                                       name="payment_date"
                                       id="paymentDate"
                                       class="form-control"
                                       value="{{ old('payment_date', now()->format('Y-m-d')) }}"
                                       required>

                            </div>


                            {{-- Payment Type --}}

                            <div class="col-md-6">

                                <label for="paymentType"
                                       class="form-label fw-semibold">

                                    Payment Type

                                    <span class="text-danger">*</span>

                                </label>


                                <select name="payment_type"
                                        id="paymentType"
                                        class="form-select"
                                        required>

                                    @foreach($paymentTypes as $value => $label)

                                        <option value="{{ $value }}"
                                            {{ old('payment_type', 'full') == $value ? 'selected' : '' }}>

                                            {{ $label }}

                                        </option>

                                    @endforeach

                                </select>

                            </div>


                            {{-- Recorded By --}}

                            <div class="col-md-6">

                                <label for="recordedBy"
                                       class="form-label fw-semibold">

                                    Recorded By

                                </label>


                                <select name="recorded_by"
                                        id="recordedBy"
                                        class="form-select">

                                    @foreach($recordedBy as $value => $label)

                                        <option value="{{ $value }}"
                                            {{ old('recorded_by', 'cashier') == $value ? 'selected' : '' }}>

                                            {{ $label }}

                                        </option>

                                    @endforeach

                                </select>

                            </div>


                            {{-- Transaction ID --}}

                            <div class="col-md-6">

                                <label for="transactionId"
                                       class="form-label fw-semibold">

                                    Transaction ID

                                </label>


                                <input type="text"
                                       name="transaction_id"
                                       id="transactionId"
                                       class="form-control"
                                       value="{{ old('transaction_id') }}"
                                       placeholder="Optional transaction ID">

                            </div>


                            {{-- Bank --}}

                            <div class="col-md-6 d-none"
                                 id="bankField">

                                <label for="bankName"
                                       class="form-label fw-semibold">

                                    Bank Name

                                </label>


                                <input type="text"
                                       name="bank_name"
                                       id="bankName"
                                       class="form-control"
                                       value="{{ old('bank_name') }}">

                            </div>


                            {{-- Cheque --}}

                            <div class="col-md-6 d-none"
                                 id="chequeField">

                                <label for="chequeNumber"
                                       class="form-label fw-semibold">

                                    Cheque Number

                                </label>


                                <input type="text"
                                       name="cheque_number"
                                       id="chequeNumber"
                                       class="form-control"
                                       value="{{ old('cheque_number') }}">

                            </div>


                            {{-- Discount --}}

                            <div class="col-md-6">

                                <label for="discountAmount"
                                       class="form-label fw-semibold">

                                    Discount

                                </label>


                                <input type="number"
                                       name="discount_amount"
                                       id="discountAmount"
                                       class="form-control"
                                       min="0"
                                       step="0.01"
                                       value="{{ old('discount_amount', 0) }}">

                            </div>


                            {{-- Penalty --}}

                            <div class="col-md-6">

                                <label for="penaltyAmount"
                                       class="form-label fw-semibold">

                                    Penalty

                                </label>


                                <input type="number"
                                       name="penalty_amount"
                                       id="penaltyAmount"
                                       class="form-control"
                                       min="0"
                                       step="0.01"
                                       value="{{ old('penalty_amount', 0) }}">

                            </div>


                            {{-- Reference --}}

                            <div class="col-md-6">

                                <label for="referenceNumber"
                                       class="form-label fw-semibold">

                                    Reference Number

                                </label>


                                <input type="text"
                                       name="reference_number"
                                       id="referenceNumber"
                                       class="form-control"
                                       value="{{ old('reference_number') }}"
                                       placeholder="Optional reference">

                            </div>


                            {{-- Notes --}}

                            <div class="col-12">

                                <label for="notes"
                                       class="form-label fw-semibold">

                                    Notes

                                </label>


                                <textarea name="notes"
                                          id="notes"
                                          class="form-control"
                                          rows="3"
                                          placeholder="Optional payment notes">{{ old('notes') }}</textarea>

                            </div>

                        </div>

                    </div>

                </div>

            </div>



            {{-- =================================================
                 RIGHT COLUMN
            ================================================== --}}

            <div class="col-xl-4">

                <div class="card border-0 shadow-sm summary-card">

                    <div class="card-header bg-white border-bottom py-3">

                        <div class="d-flex align-items-center gap-3">

                            <div class="section-icon bg-primary-subtle text-primary">
                                <i class="fas fa-calculator"></i>
                            </div>

                            <div>

                                <h5 class="fw-bold mb-0">
                                    Payment Summary
                                </h5>

                                <small class="text-muted">
                                    Review before recording payment.
                                </small>

                            </div>

                        </div>

                    </div>


                    <div class="card-body p-4">

                        <div class="summary-row">
                            <span>Student</span>

                            <strong id="summaryStudent">
                                Not selected
                            </strong>
                        </div>


                        <div class="summary-row">
                            <span>Class</span>

                            <strong id="summaryClass">
                                —
                            </strong>
                        </div>


                        <div class="summary-row">
                            <span>Academic Year</span>

                            <strong id="summaryYear">
                                —
                            </strong>
                        </div>


                        <div class="summary-row">
                            <span>Term</span>

                            <strong id="summaryTerm">
                                —
                            </strong>
                        </div>


                        <hr>


                        <div class="summary-row">
                            <span>Bill Amount</span>

                            <strong id="summaryBill">
                                GHS 0.00
                            </strong>
                        </div>


                        <div class="summary-row">
                            <span>Paid</span>

                            <strong class="text-success"
                                    id="summaryPaid">
                                GHS 0.00
                            </strong>
                        </div>


                        <div class="summary-row">
                            <span>Balance</span>

                            <strong class="text-danger"
                                    id="summaryBalance">
                                GHS 0.00
                            </strong>
                        </div>


                        <div class="payment-total-box mt-4">

                            <div class="small text-muted">
                                Amount to Record
                            </div>

                            <div class="payment-total"
                                 id="summaryPayment">
                                GHS 0.00
                            </div>

                        </div>


                        <button type="submit"
                                id="submitBtn"
                                class="btn btn-primary btn-lg w-100 mt-4"
                                disabled>

                            <i class="fas fa-save me-2"></i>

                            Record Payment

                        </button>


                        <div class="small text-muted text-center mt-3">

                            <i class="fas fa-shield-halved me-1"></i>

                            The server will revalidate the student,
                            assignment, academic year, term and Bill Sheet
                            before recording.

                        </div>

                    </div>

                </div>

            </div>

        </div>

    </form>

</div>
@endsection


{{-- =============================================================
     STYLES
============================================================== --}}

@push('styles')

<style>

    .payment-page {
        --payment-border: #e7ebf0;
        --payment-muted: #667085;
        --payment-text: #1d2939;
    }

    .page-card,
    .summary-card {
        border-radius: 16px;
        overflow: hidden;
    }

    .summary-card {
        position: sticky;
        top: 20px;
    }

    .section-icon {
        width: 42px;
        height: 42px;
        min-width: 42px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 12px;
    }

    .payment-page .form-select,
    .payment-page .form-control {
        min-height: 44px;
        border-color: #d9dee7;
        border-radius: 10px;
    }

    .payment-page textarea.form-control {
        min-height: 100px;
    }

    .payment-page .input-group-text {
        border-color: #d9dee7;
    }

    .payment-page .form-select:focus,
    .payment-page .form-control:focus {
        border-color: #86b7fe;
        box-shadow: 0 0 0 .2rem rgba(13, 110, 253, .10);
    }

    .student-info {
        background: #f5f9ff;
        border: 1px solid #dbeafe;
        border-radius: 12px;
        padding: 1rem 1.15rem;
    }

    .info-label,
    .amount-label,
    .bill-meta span:first-child {
        color: var(--payment-muted);
        font-size: .68rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: .04em;
    }

    .info-value {
        color: var(--payment-text);
        font-size: .9rem;
        font-weight: 700;
        margin-top: .2rem;
    }

    .amount-box {
        height: 100%;
        padding: 1rem;
        border: 1px solid var(--payment-border);
        border-radius: 12px;
        background: #fbfcfe;
    }

    .balance-box {
        background: #fff8f8;
        border-color: #f3d1d1;
    }

    .amount-value {
        font-size: 1.25rem;
        font-weight: 800;
        margin-top: .35rem;
    }

    .bill-meta {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: .75rem;
        padding: 1rem;
        border: 1px solid var(--payment-border);
        border-radius: 12px;
        background: #fbfcfe;
    }

    .bill-meta > div {
        display: flex;
        flex-direction: column;
        gap: .3rem;
        min-width: 0;
    }

    .bill-meta strong {
        color: var(--payment-text);
        font-size: .82rem;
        overflow-wrap: anywhere;
    }

    .payment-page .table {
        border: 1px solid var(--payment-border);
        border-radius: 10px;
        overflow: hidden;
    }

    .payment-page .table th {
        color: var(--payment-muted);
        font-size: .7rem;
        text-transform: uppercase;
        letter-spacing: .03em;
        background: #f8fafc;
        white-space: nowrap;
    }

    .payment-page .table td {
        font-size: .82rem;
    }

    .summary-row {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        gap: 1rem;
        padding: .65rem 0;
    }

    .summary-row span {
        color: var(--payment-muted);
        font-size: .78rem;
    }

    .summary-row strong {
        color: var(--payment-text);
        font-size: .8rem;
        text-align: right;
        max-width: 65%;
        overflow-wrap: anywhere;
    }

    .payment-total-box {
        padding: 1rem;
        border-radius: 12px;
        background: #f4f8ff;
        border: 1px solid #dbe8ff;
        text-align: center;
    }

    .payment-total {
        color: #0d6efd;
        font-size: 1.8rem;
        font-weight: 800;
        margin-top: .2rem;
    }

    #submitBtn {
        border-radius: 10px;
        font-weight: 700;
    }

    #submitBtn:disabled {
        cursor: not-allowed;
    }

    #termSelect.term-loading {
        background-image: none;
        opacity: .7;
    }

    @media (max-width: 1199.98px) {

        .summary-card {
            position: static;
        }

    }

    @media (max-width: 767.98px) {

        .bill-meta {
            grid-template-columns: repeat(2, 1fr);
        }

    }

    @media (max-width: 575.98px) {

        .bill-meta {
            grid-template-columns: 1fr;
        }

        .payment-page .card-body {
            padding: 1rem !important;
        }

        .summary-row {
            flex-direction: column;
            gap: .2rem;
        }

        .summary-row strong {
            max-width: 100%;
            text-align: left;
        }

    }

</style>

@endpush


{{-- =============================================================
     JAVASCRIPT
============================================================== --}}

@push('scripts')

<script>

$(function () {

    'use strict';


    /*
    |--------------------------------------------------------------------------
    | DOM ELEMENTS
    |--------------------------------------------------------------------------
    */

    const academicYear =
        $('#academicYearSelect');

    const classSelect =
        $('#classSelect');

    const termSelect =
        $('#termSelect');

    const studentSelect =
        $('#studentSelect');

    const termLoading =
        $('#termLoading');

    const termError =
        $('#termError');

    const assignmentInput =
        $('#studentAssignmentId');

    const billCard =
        $('#billCard');

    const billLoading =
        $('#billLoading');

    const noBillMessage =
        $('#noBillMessage');

    const noBillText =
        $('#noBillText');

    const billSelectorWrapper =
        $('#billSelectorWrapper');

    const billSheetSelect =
        $('#billSheetSelect');

    const billSummary =
        $('#billSummary');

    const paymentCard =
        $('#paymentCard');

    const billSheetId =
        $('#billSheetId');

    const billSheetItemId =
        $('#billSheetItemId');

    const amountInput =
        $('#amountInput');

    const fullPaymentBtn =
        $('#fullPaymentBtn');

    const submitBtn =
        $('#submitBtn');


    /*
    |--------------------------------------------------------------------------
    | STATE
    |--------------------------------------------------------------------------
    */

    let billSheets = [];

    let selectedBill = null;

    let termRequestId = 0;

    let studentRequestId = 0;

    let billRequestId = 0;


    /*
    |--------------------------------------------------------------------------
    | OLD VALUES
    |--------------------------------------------------------------------------
    |
    | These are only used after validation failure.
    |
    */

    const oldTermId =
        @json(old('term_id'));

    const oldStudentId =
        @json(old('student_id'));


    /*
    |--------------------------------------------------------------------------
    | HELPERS
    |--------------------------------------------------------------------------
    */

    function money(value) {

        const amount =
            Number(value || 0);

        return 'GHS ' +
            amount.toLocaleString(
                'en-GH',
                {
                    minimumFractionDigits: 2,
                    maximumFractionDigits: 2
                }
            );
    }


    function numberValue(value) {

        const number =
            Number(value);

        return Number.isFinite(number)
            ? number
            : 0;
    }


    function formatDate(value) {

        if (!value) {
            return '—';
        }

        const date =
            new Date(value);

        if (
            Number.isNaN(
                date.getTime()
            )
        ) {
            return value;
        }

        return date.toLocaleDateString(
            'en-GB',
            {
                day: '2-digit',
                month: 'short',
                year: 'numeric'
            }
        );
    }


    /*
    |--------------------------------------------------------------------------
    | RESET BILL AREA
    |--------------------------------------------------------------------------
    */

    function resetBillArea() {

        billRequestId++;

        billSheets = [];

        selectedBill = null;

        billSheetId.val('');

        billSheetItemId.val('');

        billSheetSelect.empty();

        billCard.addClass('d-none');

        billLoading.addClass('d-none');

        noBillMessage.addClass('d-none');

        billSelectorWrapper.addClass('d-none');

        billSummary.addClass('d-none');

        paymentCard.addClass('d-none');

        amountInput.val('');

        amountInput.removeAttr('max');

        fullPaymentBtn.prop(
            'disabled',
            true
        );

        submitBtn.prop(
            'disabled',
            true
        );

        $('#billAmount')
            .text('GHS 0.00');

        $('#billPaid')
            .text('GHS 0.00');

        $('#billBalance')
            .text('GHS 0.00');

        $('#maximumAmount')
            .text('GHS 0.00');

        $('#billName')
            .text('—');

        $('#billGeneratedDate')
            .text('—');

        $('#billDueDate')
            .text('—');

        $('#billItemsBody')
            .empty();

        $('#billItemsWrapper')
            .addClass('d-none');

        $('#summaryBill')
            .text('GHS 0.00');

        $('#summaryPaid')
            .text('GHS 0.00');

        $('#summaryBalance')
            .text('GHS 0.00');

        $('#summaryPayment')
            .text('GHS 0.00');
    }


    /*
    |--------------------------------------------------------------------------
    | RESET STUDENT
    |--------------------------------------------------------------------------
    */

    function resetStudentArea() {

        studentRequestId++;

        assignmentInput.val('');

        studentSelect
            .prop(
                'disabled',
                true
            )
            .html(
                '<option value="">Select Academic Year, Class and Term first</option>'
            );

        $('#studentInfo')
            .addClass('d-none');

        $('#summaryStudent')
            .text('Not selected');

        $('#summaryClass')
            .text('—');

        $('#summaryYear')
            .text('—');

        $('#summaryTerm')
            .text('—');

        resetBillArea();
    }


    /*
    |--------------------------------------------------------------------------
    | UPDATE SUMMARY PERIOD
    |--------------------------------------------------------------------------
    */

    function updatePeriodSummary() {

        const yearName =
            academicYear
                .find(':selected')
                .text()
                .trim() || '—';

        const className =
            classSelect
                .find(':selected')
                .text()
                .trim() || '—';

        const termName =
            termSelect
                .find(':selected')
                .text()
                .trim() || '—';


        $('#summaryYear')
            .text(
                academicYear.val()
                    ? yearName
                    : '—'
            );

        $('#summaryClass')
            .text(
                classSelect.val()
                    ? className
                    : '—'
            );

        $('#summaryTerm')
            .text(
                termSelect.val()
                    ? termName
                    : '—'
            );
    }


    /*
    |--------------------------------------------------------------------------
    | LOAD TERMS FOR SELECTED ACADEMIC YEAR
    |--------------------------------------------------------------------------
    |
    | THIS IS THE IMPORTANT FIX.
    |
    | Every time the academic year changes:
    |
    | 1. Existing terms are removed.
    | 2. Term selection is reset.
    | 3. Terms are requested using ONLY the selected academic year ID.
    | 4. Returned terms are checked again against the selected year.
    | 5. Only matching terms are displayed.
    |
    |--------------------------------------------------------------------------
    */

    function loadTerms(restoreOldTerm = false) {

        const yearId =
            academicYear.val();


        /*
        |--------------------------------------------------------------------------
        | Invalidate previous term request
        |--------------------------------------------------------------------------
        */

        const requestId =
            ++termRequestId;


        /*
        |--------------------------------------------------------------------------
        | Clear EVERYTHING immediately
        |--------------------------------------------------------------------------
        */

        termSelect
            .prop(
                'disabled',
                true
            )
            .empty()
            .append(
                $('<option>', {
                    value: '',
                    text: yearId
                        ? 'Loading terms...'
                        : 'Select Academic Year first'
                })
            );


        termError
            .addClass('d-none')
            .text('');


        termLoading
            .toggleClass(
                'd-none',
                !yearId
            );


        /*
        |--------------------------------------------------------------------------
        | Reset dependent fields
        |--------------------------------------------------------------------------
        */

        resetStudentArea();

        updatePeriodSummary();


        /*
        |--------------------------------------------------------------------------
        | No academic year selected
        |--------------------------------------------------------------------------
        */

        if (!yearId) {

            termLoading.addClass(
                'd-none'
            );

            return;
        }


        /*
        |--------------------------------------------------------------------------
        | AJAX REQUEST
        |--------------------------------------------------------------------------
        */

        $.ajax({

            url:
                @json(route('fee-payments.get-terms')),

            method:
                'GET',

            dataType:
                'json',

            data: {
                academic_year_id:
                    yearId
            },


            success: function (response) {

                /*
                |--------------------------------------------------------------------------
                | Ignore stale response
                |--------------------------------------------------------------------------
                */

                if (
                    requestId !==
                    termRequestId
                ) {
                    return;
                }


                termLoading.addClass(
                    'd-none'
                );


                termSelect
                    .empty()
                    .append(
                        $('<option>', {
                            value: '',
                            text: 'Select Term'
                        })
                    );


                /*
                |--------------------------------------------------------------------------
                | Validate response
                |--------------------------------------------------------------------------
                */

                if (
                    !response ||
                    response.success !== true ||
                    !Array.isArray(
                        response.terms
                    )
                ) {

                    termSelect
                        .prop(
                            'disabled',
                            true
                        );

                    termError
                        .removeClass(
                            'd-none'
                        )
                        .text(
                            'No terms were returned for the selected academic year.'
                        );

                    return;
                }


                /*
                |--------------------------------------------------------------------------
                | CRITICAL YEAR FILTER
                |--------------------------------------------------------------------------
                |
                | Even if the endpoint accidentally returns a term belonging
                | to another academic year, it is NOT displayed.
                |
                |--------------------------------------------------------------------------
                */

                const selectedYearId =
                    String(yearId);


                const matchingTerms =
                    response.terms.filter(
                        function (term) {

                            return String(
                                term.academic_year_id
                            ) === selectedYearId;

                        }
                    );


                /*
                |--------------------------------------------------------------------------
                | No matching terms
                |--------------------------------------------------------------------------
                */

                if (
                    matchingTerms.length === 0
                ) {

                    termSelect
                        .prop(
                            'disabled',
                            true
                        )
                        .empty()
                        .append(
                            $('<option>', {
                                value: '',
                                text:
                                    'No active terms available for this academic year'
                            })
                        );

                    return;
                }


                /*
                |--------------------------------------------------------------------------
                | DISPLAY ONLY MATCHING TERMS
                |--------------------------------------------------------------------------
                */

                matchingTerms.forEach(
                    function (term) {

                        termSelect.append(
                            $('<option>', {

                                value:
                                    term.id,

                                text:
                                    term.name

                            })
                        );

                    }
                );


                /*
                |--------------------------------------------------------------------------
                | Enable dropdown
                |--------------------------------------------------------------------------
                */

                termSelect.prop(
                    'disabled',
                    false
                );


                /*
                |--------------------------------------------------------------------------
                | Restore old term ONLY if it belongs to this year
                |--------------------------------------------------------------------------
                */

                if (
                    restoreOldTerm &&
                    oldTermId
                ) {

                    const oldTerm =
                        matchingTerms.find(
                            function (term) {

                                return String(
                                    term.id
                                ) ===
                                String(
                                    oldTermId
                                );

                            }
                        );


                    if (oldTerm) {

                        termSelect.val(
                            oldTerm.id
                        );

                    }

                }


                updatePeriodSummary();


                /*
                |--------------------------------------------------------------------------
                | If a term was restored, load students.
                |--------------------------------------------------------------------------
                */

                if (
                    termSelect.val() &&
                    classSelect.val()
                ) {

                    loadStudents();
                }

            },


            error: function (xhr) {

                if (
                    requestId !==
                    termRequestId
                ) {
                    return;
                }


                termLoading.addClass(
                    'd-none'
                );


                termSelect
                    .prop(
                        'disabled',
                        true
                    )
                    .empty()
                    .append(
                        $('<option>', {
                            value: '',
                            text:
                                'Unable to load terms'
                        })
                    );


                let message =
                    'Unable to load terms for the selected academic year.';


                if (
                    xhr.responseJSON &&
                    xhr.responseJSON.message
                ) {

                    message =
                        xhr.responseJSON.message;

                } else if (
                    xhr.status === 403
                ) {

                    message =
                        'You do not have permission to load terms.';

                } else if (
                    xhr.status === 422
                ) {

                    message =
                        'The selected academic year is invalid.';
                }


                termError
                    .removeClass(
                        'd-none'
                    )
                    .text(
                        message
                    );
            }
        });
    }


    /*
    |--------------------------------------------------------------------------
    | LOAD STUDENTS
    |--------------------------------------------------------------------------
    */

    function loadStudents() {

        const yearId =
            academicYear.val();

        const classId =
            classSelect.val();

        const termId =
            termSelect.val();


        resetStudentArea();


        if (
            !yearId ||
            !classId ||
            !termId
        ) {

            updatePeriodSummary();

            return;
        }


        const requestId =
            ++studentRequestId;


        studentSelect
            .prop(
                'disabled',
                true
            )
            .html(
                '<option value="">Loading students...</option>'
            );


        $.ajax({

            url:
                @json(route('fee-payments.get-students-by-class')),

            method:
                'GET',

            dataType:
                'json',

            data: {

                class_id:
                    classId,

                academic_year_id:
                    yearId,

                term_id:
                    termId

            },


            success: function (response) {

                if (
                    requestId !==
                    studentRequestId
                ) {
                    return;
                }


                studentSelect.empty();


                if (
                    response &&
                    response.success === true &&
                    Array.isArray(
                        response.students
                    ) &&
                    response.students.length > 0
                ) {

                    studentSelect.append(
                        $('<option>', {
                            value: '',
                            text: 'Select Student'
                        })
                    );


                    response.students.forEach(
                        function (student) {

                            const option =
                                $('<option>');


                            option.val(
                                student.id
                            );


                            option.text(
                                (
                                    student.full_name ||
                                    'Unnamed Student'
                                ) +
                                ' (' +
                                (
                                    student.student_id ||
                                    '-'
                                ) +
                                ')'
                            );


                            option.attr(
                                'data-assignment',
                                student.assignment_id || ''
                            );


                            option.attr(
                                'data-class-name',
                                student.class_name || ''
                            );


                            option.attr(
                                'data-academic-year',
                                student.academic_year || ''
                            );


                            option.attr(
                                'data-term',
                                student.term_name ||
                                student.term ||
                                ''
                            );


                            studentSelect.append(
                                option
                            );

                        }
                    );


                    studentSelect.prop(
                        'disabled',
                        false
                    );


                    /*
                    |--------------------------------------------------------------------------
                    | Restore old student after validation error
                    |--------------------------------------------------------------------------
                    */

                    if (oldStudentId) {

                        const exists =
                            studentSelect
                                .find(
                                    'option[value="' +
                                    oldStudentId +
                                    '"]'
                                )
                                .length > 0;


                        if (exists) {

                            studentSelect.val(
                                oldStudentId
                            );

                            studentSelect.trigger(
                                'change'
                            );
                        }
                    }

                } else {

                    studentSelect
                        .html(
                            '<option value="">No students found for the selected class, academic year and term</option>'
                        )
                        .prop(
                            'disabled',
                            true
                        );
                }

            },


            error: function (xhr) {

                if (
                    requestId !==
                    studentRequestId
                ) {
                    return;
                }


                console.error(
                    'get-students-by-class failed:',
                    xhr.status,
                    xhr.responseText
                );


                let message =
                    'Unable to load students.';


                if (
                    xhr.responseJSON &&
                    xhr.responseJSON.message
                ) {

                    message =
                        xhr.responseJSON.message;

                } else if (
                    xhr.status === 403
                ) {

                    message =
                        'You do not have permission to load students.';

                } else if (
                    xhr.status === 422
                ) {

                    message =
                        'The selected Academic Year, Class or Term is invalid.';
                }


                studentSelect
                    .html(
                        $('<option>', {
                            value: '',
                            text: message
                        })
                    )
                    .prop(
                        'disabled',
                        true
                    );
            }
        });
    }


    /*
    |--------------------------------------------------------------------------
    | SHOW STUDENT INFORMATION
    |--------------------------------------------------------------------------
    */

    function showStudentInfo() {

        const option =
            studentSelect.find(
                ':selected'
            );


        const studentText =
            option.text().trim() ||
            '—';


        const studentId =
            studentSelect.val() ||
            '—';


        const className =
            option.attr(
                'data-class-name'
            ) ||
            classSelect
                .find(':selected')
                .text()
                .trim() ||
            '—';


        const yearName =
            option.attr(
                'data-academic-year'
            ) ||
            academicYear
                .find(':selected')
                .text()
                .trim() ||
            '—';


        const termName =
            option.attr(
                'data-term'
            ) ||
            termSelect
                .find(':selected')
                .text()
                .trim() ||
            '—';


        $('#studentName')
            .text(
                studentText
            );


        $('#studentNumber')
            .text(
                studentId
            );


        $('#studentClass')
            .text(
                className
            );


        $('#studentYear')
            .text(
                yearName
            );


        $('#studentTerm')
            .text(
                termName
            );


        $('#summaryStudent')
            .text(
                studentText
            );


        $('#summaryClass')
            .text(
                className
            );


        $('#summaryYear')
            .text(
                yearName
            );


        $('#summaryTerm')
            .text(
                termName
            );


        $('#studentInfo')
            .removeClass(
                'd-none'
            );
    }


    /*
    |--------------------------------------------------------------------------
    | SHOW NO BILL
    |--------------------------------------------------------------------------
    */

    function showNoBill(message) {

        billSheets = [];

        selectedBill = null;

        billSheetId.val('');

        billSheetItemId.val('');

        billSheetSelect.empty();

        billLoading.addClass(
            'd-none'
        );

        billSummary.addClass(
            'd-none'
        );

        paymentCard.addClass(
            'd-none'
        );

        billSelectorWrapper.addClass(
            'd-none'
        );

        noBillText.text(
            message ||
            'No approved Bill Sheet was found for this student for the selected term.'
        );

        noBillMessage.removeClass(
            'd-none'
        );

        billCard.removeClass(
            'd-none'
        );

        amountInput.val('');

        amountInput.removeAttr(
            'max'
        );

        fullPaymentBtn.prop(
            'disabled',
            true
        );

        submitBtn.prop(
            'disabled',
            true
        );

        $('#summaryBill')
            .text('GHS 0.00');

        $('#summaryPaid')
            .text('GHS 0.00');

        $('#summaryBalance')
            .text('GHS 0.00');

        $('#summaryPayment')
            .text('GHS 0.00');
    }


    /*
    |--------------------------------------------------------------------------
    | LOAD STUDENT BILL SHEETS
    |--------------------------------------------------------------------------
    */

    function loadStudentBill(
        studentId,
        assignmentId
    ) {

        const yearId =
            academicYear.val();

        const termId =
            termSelect.val();


        if (
            !studentId ||
            !assignmentId ||
            !yearId ||
            !termId
        ) {
            return;
        }


        const requestId =
            ++billRequestId;


        billSheets = [];

        selectedBill = null;

        billSheetId.val('');

        billSheetItemId.val('');

        billCard.removeClass(
            'd-none'
        );

        billLoading.removeClass(
            'd-none'
        );

        noBillMessage.addClass(
            'd-none'
        );

        billSelectorWrapper.addClass(
            'd-none'
        );

        billSummary.addClass(
            'd-none'
        );

        paymentCard.addClass(
            'd-none'
        );


        $.ajax({

            url:
                @json(route('fee-payments.get-student-bill-sheets')),

            method:
                'GET',

            dataType:
                'json',

            data: {

                student_id:
                    studentId,

                student_class_assignment_id:
                    assignmentId,

                academic_year_id:
                    yearId,

                term_id:
                    termId

            },


            success: function (response) {

                if (
                    requestId !==
                    billRequestId
                ) {
                    return;
                }


                billLoading.addClass(
                    'd-none'
                );


                if (
                    !response ||
                    response.success !== true
                ) {

                    showNoBill(
                        response &&
                        response.message
                            ? response.message
                            : 'No approved Bill Sheet was found for this student for the selected term.'
                    );

                    return;
                }


                billSheets =
                    Array.isArray(
                        response.bill_sheets
                    )
                        ? response.bill_sheets
                        : [];


                const payableBills =
                    billSheets.filter(
                        function (bill) {

                            return numberValue(
                                bill.balance
                            ) > 0;

                        }
                    );


                if (
                    payableBills.length === 0
                ) {

                    if (
                        billSheets.length > 0
                    ) {

                        showNoBill(
                            'Approved Bill Sheets were found, but there is no outstanding balance to pay.'
                        );

                    } else {

                        const nonPayable =
                            Array.isArray(
                                response.non_payable_bill_sheets
                            )
                                ? response.non_payable_bill_sheets
                                : [];

                        if (nonPayable.length > 0) {

                            const statuses =
                                nonPayable
                                    .map(function (bill) {
                                        return (
                                            (bill.name || ('Bill Sheet #' + bill.id)) +
                                            ' [' +
                                            String(bill.status || 'unknown') +
                                            ']'
                                        );
                                    })
                                    .join(', ');

                            showNoBill(
                                'A Bill Sheet exists for this student for the selected Academic Year and Term, but it is not approved for payment. Current status: ' +
                                statuses +
                                '.'
                            );

                        } else {

                            showNoBill(
                                'No approved Bill Sheet was found for this student for the selected Academic Year and Term.'
                            );

                        }

                    }

                    return;
                }


                billSheets =
                    payableBills;


                billSheetSelect.empty();


                billSheets.forEach(
                    function (bill) {

                        const option =
                            $('<option>');


                        option.val(
                            bill.id
                        );


                        option.text(
                            (
                                bill.name ||
                                (
                                    'Bill Sheet #' +
                                    bill.id
                                )
                            ) +
                            ' — ' +
                            (
                                bill.formatted_balance ||
                                money(
                                    bill.balance
                                )
                            ) +
                            ' outstanding'
                        );


                        billSheetSelect.append(
                            option
                        );

                    }
                );


                if (
                    billSheets.length > 1
                ) {

                    billSelectorWrapper
                        .removeClass(
                            'd-none'
                        );
                }


                let billToSelect =
                    null;


                if (
                    response.selected_bill_sheet_id
                ) {

                    billToSelect =
                        billSheets.find(
                            function (bill) {

                                return String(
                                    bill.id
                                ) ===
                                String(
                                    response.selected_bill_sheet_id
                                );

                            }
                        );
                }


                if (!billToSelect) {

                    billToSelect =
                        billSheets[0];
                }


                billSheetSelect.val(
                    billToSelect.id
                );


                selectBill(
                    billToSelect
                );

            },


            error: function (xhr) {

                if (
                    requestId !==
                    billRequestId
                ) {
                    return;
                }


                console.error(
                    'get-student-bill-sheets failed:',
                    xhr.status,
                    xhr.responseText
                );


                billLoading.addClass(
                    'd-none'
                );


                let message =
                    'Unable to load the approved Bill Sheet.';


                if (
                    xhr.responseJSON &&
                    xhr.responseJSON.message
                ) {

                    message =
                        xhr.responseJSON.message;

                } else if (
                    xhr.status === 403
                ) {

                    message =
                        'You do not have permission to view Bill Sheets for payment.';

                } else if (
                    xhr.status === 422
                ) {

                    message =
                        'The selected student assignment, academic year or term is invalid.';
                }


                showNoBill(
                    message
                );
            }

        });
    }


    /*
    |--------------------------------------------------------------------------
    | SELECT BILL
    |--------------------------------------------------------------------------
    */

    function selectBill(bill) {

        selectedBill =
            bill;


        const total =
            numberValue(
                bill.total_amount
            );


        const paid =
            numberValue(
                bill.paid
            );


        const balance =
            Math.max(
                0,
                numberValue(
                    bill.balance
                )
            );


        billSheetId.val(
            bill.id
        );


        billSheetItemId.val('');


        $('#billAmount')
            .text(
                money(total)
            );


        $('#billPaid')
            .text(
                money(paid)
            );


        $('#billBalance')
            .text(
                money(balance)
            );


        $('#maximumAmount')
            .text(
                money(balance)
            );


        $('#summaryBill')
            .text(
                money(total)
            );


        $('#summaryPaid')
            .text(
                money(paid)
            );


        $('#summaryBalance')
            .text(
                money(balance)
            );


        $('#billName')
            .text(
                bill.name ||
                (
                    'Bill Sheet #' +
                    bill.id
                )
            );


        $('#billGeneratedDate')
            .text(
                formatDate(
                    bill.generated_date
                )
            );


        $('#billDueDate')
            .text(
                formatDate(
                    bill.due_date
                )
            );


        const billSheetStatus =
            String(
                bill.bill_sheet_status ||
                'approved'
            ).toLowerCase();


        $('#billStatus')
            .removeClass(
                'bg-success bg-primary bg-secondary bg-warning'
            )
            .addClass(
                billSheetStatus === 'published'
                    ? 'bg-primary'
                    : 'bg-success'
            )
            .text(
                billSheetStatus === 'published'
                    ? 'Published'
                    : 'Approved'
            );


        renderBillItems(
            bill.items || []
        );


        amountInput
            .attr(
                'max',
                balance
            )
            .val(
                balance > 0
                    ? balance.toFixed(2)
                    : ''
            );


        fullPaymentBtn.prop(
            'disabled',
            balance <= 0
        );


        submitBtn.prop(
            'disabled',
            balance <= 0
        );


        billSummary.removeClass(
            'd-none'
        );


        paymentCard.removeClass(
            'd-none'
        );


        noBillMessage.addClass(
            'd-none'
        );


        updatePaymentSummary();
    }


    /*
    |--------------------------------------------------------------------------
    | BILL ITEMS
    |--------------------------------------------------------------------------
    */

    function renderBillItems(items) {

        const body =
            $('#billItemsBody');


        body.empty();


        if (
            !Array.isArray(items) ||
            items.length === 0
        ) {

            $('#billItemsWrapper')
                .addClass(
                    'd-none'
                );

            return;
        }


        items.forEach(
            function (item) {

                const name =
                    item.name ||
                    item.description ||
                    'Bill Item';


                const quantity =
                    numberValue(
                        item.quantity || 1
                    );


                const amount =
                    numberValue(
                        item.amount
                    );


                const itemTotal =
                    numberValue(
                        item.total_amount
                    );


                const row =
                    $('<tr>');


                row.append(
                    $('<td>').text(
                        name
                    )
                );


                row.append(
                    $('<td>')
                        .addClass(
                            'text-center'
                        )
                        .text(
                            quantity
                        )
                );


                row.append(
                    $('<td>')
                        .addClass(
                            'text-end'
                        )
                        .text(
                            money(amount)
                        )
                );


                row.append(
                    $('<td>')
                        .addClass(
                            'text-end fw-semibold'
                        )
                        .text(
                            money(itemTotal)
                        )
                );


                body.append(
                    row
                );

            }
        );


        $('#billItemsWrapper')
            .removeClass(
                'd-none'
            );
    }


    /*
    |--------------------------------------------------------------------------
    | BILL SHEET SELECTOR
    |--------------------------------------------------------------------------
    */

    billSheetSelect.on(
        'change',
        function () {

            const id =
                String(
                    $(this).val()
                );


            const bill =
                billSheets.find(
                    function (item) {

                        return String(
                            item.id
                        ) === id;

                    }
                );


            if (bill) {

                selectBill(
                    bill
                );
            }
        }
    );


    /*
    |--------------------------------------------------------------------------
    | FULL PAYMENT
    |--------------------------------------------------------------------------
    */

    fullPaymentBtn.on(
        'click',
        function () {

            if (!selectedBill) {
                return;
            }


            const balance =
                numberValue(
                    selectedBill.balance
                );


            if (balance <= 0) {
                return;
            }


            amountInput.val(
                balance.toFixed(2)
            );


            updatePaymentSummary();
        }
    );


    /*
    |--------------------------------------------------------------------------
    | AMOUNT
    |--------------------------------------------------------------------------
    */

    amountInput.on(
        'input',
        function () {

            if (!selectedBill) {
                return;
            }


            const balance =
                numberValue(
                    selectedBill.balance
                );


            let amount =
                parseFloat(
                    $(this).val()
                );


            if (
                !Number.isFinite(
                    amount
                )
            ) {
                amount = 0;
            }


            if (amount < 0) {
                amount = 0;
            }


            if (
                amount > balance
            ) {

                amount =
                    balance;

                $(this).val(
                    balance.toFixed(2)
                );
            }


            updatePaymentSummary();
        }
    );


    /*
    |--------------------------------------------------------------------------
    | PAYMENT SUMMARY
    |--------------------------------------------------------------------------
    */

    function updatePaymentSummary() {

        const amount =
            parseFloat(
                amountInput.val()
            );


        $('#summaryPayment')
            .text(
                money(
                    Number.isFinite(
                        amount
                    )
                        ? amount
                        : 0
                )
            );
    }


    /*
    |--------------------------------------------------------------------------
    | PAYMENT METHOD
    |--------------------------------------------------------------------------
    */

    $('#paymentMethod').on(
        'change',
        function () {

            const method =
                $(this).val();


            $('#bankField')
                .toggleClass(
                    'd-none',
                    method !== 'bank_transfer'
                );


            $('#chequeField')
                .toggleClass(
                    'd-none',
                    method !== 'cheque'
                );
        }
    );


    /*
    |--------------------------------------------------------------------------
    | ACADEMIC YEAR CHANGE
    |--------------------------------------------------------------------------
    |
    | THIS IS THE MAIN FIX.
    |--------------------------------------------------------------------------
    */

    academicYear.on(
        'change',
        function () {

            /*
            | Never retain a term from the previous academic year.
            */

            termSelect
                .val('')
                .prop(
                    'disabled',
                    true
                );


            /*
            | Reload terms exclusively for the new academic year.
            */

            loadTerms(
                false
            );
        }
    );


    /*
    |--------------------------------------------------------------------------
    | CLASS CHANGE
    |--------------------------------------------------------------------------
    */

    classSelect.on(
        'change',
        function () {

            resetStudentArea();

            updatePeriodSummary();


            if (
                academicYear.val() &&
                termSelect.val()
            ) {

                loadStudents();
            }
        }
    );


    /*
    |--------------------------------------------------------------------------
    | TERM CHANGE
    |--------------------------------------------------------------------------
    */

    termSelect.on(
        'change',
        function () {

            resetStudentArea();

            updatePeriodSummary();


            if (
                academicYear.val() &&
                classSelect.val() &&
                termSelect.val()
            ) {

                loadStudents();
            }
        }
    );


    /*
    |--------------------------------------------------------------------------
    | STUDENT CHANGE
    |--------------------------------------------------------------------------
    */

    studentSelect.on(
        'change',
        function () {

            const option =
                $(this).find(
                    ':selected'
                );


            const studentId =
                $(this).val();


            const assignmentId =
                option.attr(
                    'data-assignment'
                ) || '';


            assignmentInput.val(
                assignmentId
            );


            resetBillArea();


            if (!studentId) {

                $('#studentInfo')
                    .addClass(
                        'd-none'
                    );

                return;
            }


            showStudentInfo();


            if (!assignmentId) {

                showNoBill(
                    'The selected student does not have a valid class assignment for this academic year and term.'
                );

                return;
            }


            loadStudentBill(
                studentId,
                assignmentId
            );
        }
    );


    /*
    |--------------------------------------------------------------------------
    | FORM SUBMISSION
    |--------------------------------------------------------------------------
    */

    $('#paymentForm').on(
        'submit',
        function (event) {

            if (!academicYear.val()) {

                event.preventDefault();

                alert(
                    'Please select an academic year.'
                );

                academicYear.focus();

                return;
            }


            if (!termSelect.val()) {

                event.preventDefault();

                alert(
                    'Please select a valid term for the selected academic year.'
                );

                termSelect.focus();

                return;
            }


            if (!classSelect.val()) {

                event.preventDefault();

                alert(
                    'Please select a class.'
                );

                classSelect.focus();

                return;
            }


            if (!studentSelect.val()) {

                event.preventDefault();

                alert(
                    'Please select a student.'
                );

                studentSelect.focus();

                return;
            }


            if (!assignmentInput.val()) {

                event.preventDefault();

                alert(
                    'The selected student does not have a valid class assignment for the selected academic year and term.'
                );

                return;
            }


            if (
                !selectedBill ||
                !billSheetId.val()
            ) {

                event.preventDefault();

                alert(
                    'Please select a student with an approved Bill Sheet.'
                );

                return;
            }


            const amount =
                parseFloat(
                    amountInput.val()
                );


            const balance =
                numberValue(
                    selectedBill.balance
                );


            if (
                !Number.isFinite(amount) ||
                amount <= 0
            ) {

                event.preventDefault();

                alert(
                    'Please enter a valid payment amount.'
                );

                amountInput.focus();

                return;
            }


            if (
                amount > balance
            ) {

                event.preventDefault();

                alert(
                    'Payment cannot exceed the outstanding Bill Sheet balance of ' +
                    money(balance) +
                    '.'
                );

                amountInput.val(
                    balance.toFixed(2)
                );

                updatePaymentSummary();

                return;
            }


            if (
                !$('#paymentMethod').val()
            ) {

                event.preventDefault();

                alert(
                    'Please select a payment method.'
                );

                $('#paymentMethod').focus();

                return;
            }


            /*
            |--------------------------------------------------------------------------
            | Prevent double submission
            |--------------------------------------------------------------------------
            */

            submitBtn
                .prop(
                    'disabled',
                    true
                )
                .html(
                    '<span class="spinner-border spinner-border-sm me-2"></span>' +
                    'Recording Payment...'
                );
        }
    );


    /*
    |--------------------------------------------------------------------------
    | INITIAL PAGE LOAD
    |--------------------------------------------------------------------------
    |
    | Do NOT trust the server-rendered $terms collection.
    |
    | Always fetch terms using the selected Academic Year.
    |
    |--------------------------------------------------------------------------
    */

    if (
        academicYear.val()
    ) {

        loadTerms(
            true
        );

    } else {

        termSelect
            .prop(
                'disabled',
                true
            )
            .html(
                '<option value="">Select Academic Year first</option>'
            );
    }


    /*
    |--------------------------------------------------------------------------
    | INITIAL PERIOD SUMMARY
    |--------------------------------------------------------------------------
    */

    updatePeriodSummary();


    /*
    |--------------------------------------------------------------------------
    | INITIAL PAYMENT METHOD STATE
    |--------------------------------------------------------------------------
    */

    $('#bankField').addClass(
        'd-none'
    );

    $('#chequeField').addClass(
        'd-none'
    );

});

</script>

@endpush