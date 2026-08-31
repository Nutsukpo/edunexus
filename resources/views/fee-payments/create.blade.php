@extends('layouts.master')

@section('title', 'Record Fee Payment')

@section('content')

<div class="container-fluid">

    <div class="d-flex justify-content-between align-items-center mb-4 mt-3">

        <div>
            <h3 class="fw-bold mb-1">
                <i class="fas fa-money-bill-wave text-primary me-2"></i>
                Record Fee Payment
            </h3>

            <p class="text-muted mb-0">
                Select a student to automatically load their Bill Sheet.
            </p>
        </div>

        <a href="{{ route('fee-payments.index') }}"
           class="btn btn-outline-secondary">

            <i class="fas fa-arrow-left me-1"></i>
            Back
        </a>

    </div>


    @if($errors->any())

        <div class="alert alert-danger">

            <strong>
                Please fix the following:
            </strong>

            <ul class="mb-0 mt-2">

                @foreach($errors->all() as $error)

                    <li>{{ $error }}</li>

                @endforeach

            </ul>

        </div>

    @endif


    @if(session('error'))

        <div class="alert alert-danger">
            {{ session('error') }}
        </div>

    @endif


    <form action="{{ route('fee-payments.store') }}"
          method="POST"
          id="paymentForm">

        @csrf

        <div class="row">

            {{-- =====================================================
                 STUDENT SELECTION
                 ===================================================== --}}

            <div class="col-lg-8">

                <div class="card border-0 shadow-sm">

                    <div class="card-header bg-white py-3">

                        <h5 class="mb-0">
                            <i class="fas fa-user-graduate text-primary me-2"></i>
                            Student Selection
                        </h5>

                    </div>

                    <div class="card-body">

                        <div class="row">

                            {{-- Academic Year --}}

                            <div class="col-md-6 mb-3">

                                <label class="form-label fw-semibold">
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
                                            {{ old('academic_year_id') == $year->id ? 'selected' : '' }}>

                                            {{ $year->name }}

                                        </option>

                                    @endforeach

                                </select>

                            </div>


                            {{-- Class --}}

                            <div class="col-md-6 mb-3">

                                <label class="form-label fw-semibold">
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

                                        <option value="{{ $class->id }}">

                                            {{ $class->name }}

                                            @if($class->students_count)
                                                ({{ $class->students_count }} students)
                                            @endif

                                        </option>

                                    @endforeach

                                </select>

                            </div>


                            {{-- Term --}}

                            <div class="col-md-6 mb-3">

                                <label class="form-label fw-semibold">
                                    Term
                                    <span class="text-danger">*</span>
                                </label>

                                <select name="term_id"
                                        id="termSelect"
                                        class="form-select"
                                        required>

                                    <option value="">
                                        Select Term
                                    </option>

                                    @foreach($terms as $term)

                                        <option value="{{ $term->id }}">

                                            {{ $term->name }}

                                        </option>

                                    @endforeach

                                </select>

                            </div>


                            {{-- Student --}}

                            <div class="col-md-6 mb-3">

                                <label class="form-label fw-semibold">
                                    Student
                                    <span class="text-danger">*</span>
                                </label>

                                <select name="student_id"
                                        id="studentSelect"
                                        class="form-select"
                                        disabled
                                        required>

                                    <option value="">
                                        Select Academic Year and Class first
                                    </option>

                                </select>

                            </div>


                            {{-- Assignment ID --}}

                            <input type="hidden"
                                   name="student_class_assignment_id"
                                   id="studentAssignmentId">


                            {{-- Student Information --}}

                            <div class="col-md-12">

                                <div id="studentInfo"
                                     class="alert alert-info d-none mb-0">

                                    <div class="row">

                                        <div class="col-md-6">

                                            <strong>Student:</strong>

                                            <span id="studentName">
                                                -
                                            </span>

                                            <br>

                                            <strong>Student ID:</strong>

                                            <span id="studentNumber">
                                                -
                                            </span>

                                        </div>

                                        <div class="col-md-6">

                                            <strong>Class:</strong>

                                            <span id="studentClass">
                                                -
                                            </span>

                                            <br>

                                            <strong>Academic Year:</strong>

                                            <span id="studentYear">
                                                -
                                            </span>

                                        </div>

                                    </div>

                                </div>

                            </div>

                        </div>

                    </div>

                </div>


                {{-- =====================================================
                     BILL SHEET
                     ===================================================== --}}

                <div id="billCard"
                     class="card border-0 shadow-sm mt-4 d-none">

                    <div class="card-header bg-white py-3">

                        <h5 class="mb-0">

                            <i class="fas fa-file-invoice text-primary me-2"></i>

                            Student Bill Sheet

                        </h5>

                    </div>


                    <div class="card-body">

                        <div id="noBillMessage"
                             class="alert alert-warning d-none">

                            <i class="fas fa-exclamation-triangle me-2"></i>

                            No approved Bill Sheet was found for this student
                            for the selected term.

                        </div>


                        {{-- Bill selector --}}

                        <div id="billSelectorWrapper"
                             class="mb-3 d-none">

                            <label class="form-label fw-semibold">
                                Bill Sheet
                            </label>

                            <select id="billSheetSelect"
                                    class="form-select">

                            </select>

                        </div>


                        {{-- Hidden Bill Sheet ID --}}

                        <input type="hidden"
                               name="bill_sheet_id"
                               id="billSheetId">


                        {{-- Bill Summary --}}

                        <div id="billSummary"
                             class="d-none">

                            <div class="row g-3">

                                <div class="col-md-4">

                                    <div class="p-3 bg-light rounded">

                                        <small class="text-muted">
                                            Bill Amount
                                        </small>

                                        <h4 class="fw-bold text-primary mb-0"
                                            id="billAmount">

                                            GHS 0.00

                                        </h4>

                                    </div>

                                </div>


                                <div class="col-md-4">

                                    <div class="p-3 bg-light rounded">

                                        <small class="text-muted">
                                            Amount Paid
                                        </small>

                                        <h4 class="fw-bold text-success mb-0"
                                            id="billPaid">

                                            GHS 0.00

                                        </h4>

                                    </div>

                                </div>


                                <div class="col-md-4">

                                    <div class="p-3 bg-light rounded">

                                        <small class="text-muted">
                                            Outstanding Balance
                                        </small>

                                        <h4 class="fw-bold text-danger mb-0"
                                            id="billBalance">

                                            GHS 0.00

                                        </h4>

                                    </div>

                                </div>

                            </div>


                            <div class="alert alert-primary mt-3 mb-0">

                                <i class="fas fa-info-circle me-2"></i>

                                The cashier can record any amount up to the
                                outstanding balance.

                            </div>

                        </div>

                    </div>

                </div>


                {{-- =====================================================
                     PAYMENT
                     ===================================================== --}}

                <div id="paymentCard"
                     class="card border-0 shadow-sm mt-4 d-none">

                    <div class="card-header bg-white py-3">

                        <h5 class="mb-0">

                            <i class="fas fa-credit-card text-primary me-2"></i>

                            Payment Details

                        </h5>

                    </div>


                    <div class="card-body">

                        <div class="row">

                            {{-- Amount --}}

                            <div class="col-md-6 mb-3">

                                <label class="form-label fw-semibold">

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

                                <small class="text-muted">

                                    Maximum:

                                    <strong id="maximumAmount">
                                        GHS 0.00
                                    </strong>

                                </small>

                            </div>


                            {{-- Payment Method --}}

                            <div class="col-md-6 mb-3">

                                <label class="form-label fw-semibold">
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

                                        <option value="{{ $value }}">

                                            {{ $label }}

                                        </option>

                                    @endforeach

                                </select>

                            </div>


                            {{-- Payment Date --}}

                            <div class="col-md-6 mb-3">

                                <label class="form-label fw-semibold">
                                    Payment Date
                                    <span class="text-danger">*</span>
                                </label>

                                <input type="date"
                                       name="payment_date"
                                       class="form-control"
                                       value="{{ old('payment_date', date('Y-m-d')) }}"
                                       required>

                            </div>


                            {{-- Payment Type --}}

                            <div class="col-md-6 mb-3">

                                <label class="form-label fw-semibold">
                                    Payment Type
                                    <span class="text-danger">*</span>
                                </label>

                                <select name="payment_type"
                                        class="form-select"
                                        required>

                                    @foreach($paymentTypes as $value => $label)

                                        <option value="{{ $value }}">

                                            {{ $label }}

                                        </option>

                                    @endforeach

                                </select>

                            </div>


                            {{-- Recorded By --}}

                            <div class="col-md-6 mb-3">

                                <label class="form-label fw-semibold">
                                    Recorded By
                                </label>

                                <select name="recorded_by"
                                        class="form-select">

                                    @foreach($recordedBy as $value => $label)

                                        <option value="{{ $value }}"
                                            {{ $value === 'cashier' ? 'selected' : '' }}>

                                            {{ $label }}

                                        </option>

                                    @endforeach

                                </select>

                            </div>


                            {{-- Transaction --}}

                            <div class="col-md-6 mb-3">

                            <label class="form-label fw-semibold">
                                Transaction ID
                            </label>

                                <input type="text"
                                    name="transaction_id"
                                    class="form-control"
                                    placeholder="Leave blank to auto-generate">

                                <small class="text-muted">
                                    Leave blank and EDUNEXUS will automatically generate a transaction ID.
                                </small>

                            </div>


                            {{-- Bank --}}

                            <div class="col-md-6 mb-3 d-none"
                                 id="bankField">

                                <label class="form-label fw-semibold">
                                    Bank Name
                                </label>

                                <input type="text"
                                       name="bank_name"
                                       class="form-control">

                            </div>


                            {{-- Cheque --}}

                            <div class="col-md-6 mb-3 d-none"
                                 id="chequeField">

                                <label class="form-label fw-semibold">
                                    Cheque Number
                                </label>

                                <input type="text"
                                       name="cheque_number"
                                       class="form-control">

                            </div>


                            {{-- Discount --}}

                            <div class="col-md-6 mb-3">

                                <label class="form-label fw-semibold">
                                    Discount
                                </label>

                                <input type="number"
                                       name="discount_amount"
                                       id="discountAmount"
                                       class="form-control"
                                       min="0"
                                       step="0.01"
                                       value="0">

                            </div>


                            {{-- Penalty --}}

                            <div class="col-md-6 mb-3">

                                <label class="form-label fw-semibold">
                                    Penalty
                                </label>

                                <input type="number"
                                       name="penalty_amount"
                                       id="penaltyAmount"
                                       class="form-control"
                                       min="0"
                                       step="0.01"
                                       value="0">

                            </div>


                            {{-- Notes --}}

                            <div class="col-md-12 mb-3">

                                <label class="form-label fw-semibold">
                                    Notes
                                </label>

                                <textarea name="notes"
                                          class="form-control"
                                          rows="3"></textarea>

                            </div>

                        </div>

                    </div>

                </div>

            </div>


            {{-- =====================================================
                 RIGHT SUMMARY
                 ===================================================== --}}

            <div class="col-lg-4">

                <div class="card border-0 shadow-sm sticky-top"
                     style="top:20px;">

                    <div class="card-header bg-white py-3">

                        <h6 class="mb-0">

                            <i class="fas fa-calculator text-primary me-2"></i>

                            Payment Summary

                        </h6>

                    </div>


                    <div class="card-body">

                        <div class="d-flex justify-content-between mb-2">

                            <span class="text-muted">
                                Student
                            </span>

                            <strong id="summaryStudent">
                                Not selected
                            </strong>

                        </div>


                        <div class="d-flex justify-content-between mb-2">

                            <span class="text-muted">
                                Class
                            </span>

                            <strong id="summaryClass">
                                -
                            </strong>

                        </div>


                        <div class="d-flex justify-content-between mb-2">

                            <span class="text-muted">
                                Term
                            </span>

                            <strong id="summaryTerm">
                                -
                            </strong>

                        </div>

                        <hr>


                        <div class="d-flex justify-content-between mb-2">

                            <span class="text-muted">
                                Bill Amount
                            </span>

                            <strong id="summaryBill">
                                GHS 0.00
                            </strong>

                        </div>


                        <div class="d-flex justify-content-between mb-2">

                            <span class="text-muted">
                                Paid
                            </span>

                            <strong class="text-success"
                                    id="summaryPaid">

                                GHS 0.00

                            </strong>

                        </div>


                        <div class="d-flex justify-content-between mb-3">

                            <span class="text-muted">
                                Balance
                            </span>

                            <strong class="text-danger"
                                    id="summaryBalance">

                                GHS 0.00

                            </strong>

                        </div>


                        <hr>


                        <div class="d-flex justify-content-between">

                            <span class="fw-semibold">
                                Payment
                            </span>

                            <strong class="text-primary"
                                    id="summaryPayment">

                                GHS 0.00

                            </strong>

                        </div>


                        <button type="submit"
                                id="submitBtn"
                                class="btn btn-primary w-100 mt-4"
                                disabled>

                            <i class="fas fa-save me-2"></i>

                            Record Payment

                        </button>

                    </div>

                </div>

            </div>

        </div>

    </form>

</div>


<style>

.card {
    border-radius: 12px;
}

.form-control,
.form-select {
    border-radius: 8px;
}

</style>


@push('scripts')

<script>

$(document).ready(function () {

    const academicYear =
        $('#academicYearSelect');

    const classSelect =
        $('#classSelect');

    const termSelect =
        $('#termSelect');

    const studentSelect =
        $('#studentSelect');

    const assignmentInput =
        $('#studentAssignmentId');

    const billCard =
        $('#billCard');

    const paymentCard =
        $('#paymentCard');

    const billSummary =
        $('#billSummary');

    const billSelectorWrapper =
        $('#billSelectorWrapper');

    const billSheetSelect =
        $('#billSheetSelect');

    const billSheetId =
        $('#billSheetId');

    const noBillMessage =
        $('#noBillMessage');

    const amountInput =
        $('#amountInput');

    const fullPaymentBtn =
        $('#fullPaymentBtn');

    const submitBtn =
        $('#submitBtn');

    let billSheets = [];

    let selectedBill = null;


    /*
    |--------------------------------------------------------------------------
    | LOAD STUDENTS
    |--------------------------------------------------------------------------
    */

    function loadStudents() {

        const yearId = academicYear.val();
        const classId = classSelect.val();
        const termId = termSelect.val();

        resetStudent();

        // Students are loaded only after all three filters are selected.
        if (!yearId || !classId || !termId) {
            studentSelect
                .prop('disabled', true)
                .html('<option value="">Select Academic Year, Class and Term first</option>');
            return;
        }

        studentSelect
            .prop('disabled', true)
            .html('<option value="">Loading students...</option>');

        $.ajax({
            url: '{{ route("fee-payments.get-students-by-class") }}',
            type: 'GET',
            dataType: 'json',
            data: {
                class_id: classId,
                academic_year_id: yearId,
                term_id: termId
            },
            success: function (response) {
                studentSelect.empty();

                if (response && response.success && Array.isArray(response.students) && response.students.length > 0) {
                    studentSelect.append('<option value="">Select Student</option>');

                    response.students.forEach(function (student) {
                        const option = $('<option>')
                            .val(student.id)
                            .text((student.full_name || 'Unnamed Student') + ' (' + (student.student_id || '-') + ')')
                            .attr('data-assignment', student.assignment_id);

                        studentSelect.append(option);
                    });

                    studentSelect.prop('disabled', false);
                } else {
                    studentSelect
                        .append('<option value="">No students found for the selected Academic Year, Class and Term</option>')
                        .prop('disabled', true);
                }
            },
            error: function (xhr) {
                console.error('Student loading failed:', xhr.status, xhr.responseText);

                let message = 'Unable to load students.';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    message = xhr.responseJSON.message;
                } else if (xhr.status === 404) {
                    message = 'Student loading endpoint was not found. Check the fee-payment routes.';
                } else if (xhr.status === 422) {
                    message = 'The selected Academic Year, Class or Term is invalid.';
                }

                studentSelect
                    .html('<option value="">' + message + '</option>')
                    .prop('disabled', true);
            }
        });
    }

    /*
    |--------------------------------------------------------------------------
    | STUDENT SELECTED
    |--------------------------------------------------------------------------
    */

    studentSelect.on(
        'change',
        function () {

            const option =
                $(this).find(':selected');

            const studentId =
                $(this).val();

            const assignmentId =
                option.data('assignment');

            assignmentInput.val(
                assignmentId || ''
            );

            resetBill();

            if (!studentId || !assignmentId) {
                return;
            }

            showStudentInfo(
                option.text(),
                studentId,
                assignmentId
            );

            /*
            |--------------------------------------------------------------------------
            | REQUIRE TERM
            |--------------------------------------------------------------------------
            */

            if (!termSelect.val()) {

                billCard.removeClass('d-none');

                noBillMessage
                    .removeClass('d-none')
                    .text(
                        'Please select a term to load the student Bill Sheet.'
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
    | LOAD BILL SHEET
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

        billCard.removeClass('d-none');

        noBillMessage.addClass('d-none');

        billSummary.addClass('d-none');

        billSelectorWrapper.addClass('d-none');

        paymentCard.addClass('d-none');

        $.ajax({

            url:
                '{{ url("/fee-payments/get-student-details") }}/'
                +
                studentId,

            type:
                'GET',

            data: {

                academic_year_id:
                    yearId,

                student_class_assignment_id:
                    assignmentId,

                term_id:
                    termId

            },

            success:
                function (response) {

                    if (
                        !response.success ||
                        !response.bill_sheets.length
                    ) {

                        noBillMessage
                            .removeClass('d-none')
                            .text(
                                'No approved Bill Sheet exists for this student for the selected term.'
                            );

                        return;
                    }

                    billSheets =
                        response.bill_sheets;

                    /*
                    |--------------------------------------------------------------------------
                    | BUILD BILL SHEET SELECTOR
                    |--------------------------------------------------------------------------
                    */

                    billSheetSelect.empty();

                    billSheets.forEach(
                        function (bill) {

                            billSheetSelect.append(

                                $('<option>', {

                                    value:
                                        bill.id,

                                    text:
                                        bill.name
                                        +
                                        ' — '
                                        +
                                        bill.formatted_balance
                                        +
                                        ' outstanding'

                                })

                            );

                        }
                    );

                    if (billSheets.length > 1) {

                        billSelectorWrapper
                            .removeClass('d-none');

                    }

                    /*
                    |--------------------------------------------------------------------------
                    | SELECT FIRST UNPAID BILL
                    |--------------------------------------------------------------------------
                    */

                    let firstUnpaid =
                        billSheets.find(
                            function (bill) {

                                return bill.balance > 0;

                            }
                        );

                    if (!firstUnpaid) {

                        noBillMessage
                            .removeClass('d-none')
                            .text(
                                'This student has fully paid the Bill Sheet.'
                            );

                        return;
                    }

                    billSheetSelect.val(
                        firstUnpaid.id
                    );

                    selectBill(
                        firstUnpaid
                    );

                },

            error:
                function (xhr) {

                    console.error(xhr);

                    noBillMessage
                        .removeClass('d-none')
                        .text(
                            'Unable to load the student Bill Sheet.'
                        );

                }

        });

    }


    /*
    |--------------------------------------------------------------------------
    | BILL SHEET SELECTED
    |--------------------------------------------------------------------------
    */

    billSheetSelect.on(
        'change',
        function () {

            const id =
                parseInt(
                    $(this).val()
                );

            const bill =
                billSheets.find(
                    function (item) {
                        return item.id === id;
                    }
                );

            if (bill) {
                selectBill(bill);
            }

        }
    );


    /*
    |--------------------------------------------------------------------------
    | DISPLAY BILL
    |--------------------------------------------------------------------------
    */

    function selectBill(bill) {

        selectedBill =
            bill;

        billSheetId.val(
            bill.id
        );

        $('#billAmount').text(
            money(bill.total_amount)
        );

        $('#billPaid').text(
            money(bill.paid)
        );

        $('#billBalance').text(
            money(bill.balance)
        );

        $('#maximumAmount').text(
            money(bill.balance)
        );

        $('#summaryBill').text(
            money(bill.total_amount)
        );

        $('#summaryPaid').text(
            money(bill.paid)
        );

        $('#summaryBalance').text(
            money(bill.balance)
        );

        billSummary
            .removeClass('d-none');

        paymentCard
            .removeClass('d-none');

        submitBtn.prop(
            'disabled',
            bill.balance <= 0
        );

        amountInput.attr(
            'max',
            bill.balance
        );

        /*
        |--------------------------------------------------------------------------
        | AUTOMATICALLY SET FULL AMOUNT
        |--------------------------------------------------------------------------
        */

        amountInput.val(
            bill.balance > 0
                ? bill.balance.toFixed(2)
                : ''
        );

        updatePaymentSummary();
    }


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

            amountInput.val(
                selectedBill.balance
                    .toFixed(2)
            );

            updatePaymentSummary();
        }
    );


    /*
    |--------------------------------------------------------------------------
    | PAYMENT AMOUNT CHANGE
    |--------------------------------------------------------------------------
    */

    amountInput.on(
        'input',
        function () {

            if (!selectedBill) {
                return;
            }

            let amount =
                parseFloat(
                    $(this).val()
                ) || 0;

            if (
                amount >
                selectedBill.balance
            ) {

                $(this).val(
                    selectedBill.balance
                        .toFixed(2)
                );

                amount =
                    selectedBill.balance;
            }

            updatePaymentSummary();
        }
    );


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

            $('#bankField').toggleClass(
                'd-none',
                method !== 'bank_transfer'
            );

            $('#chequeField').toggleClass(
                'd-none',
                method !== 'cheque'
            );
        }
    );


    /*
    |--------------------------------------------------------------------------
    | SUMMARY
    |--------------------------------------------------------------------------
    */

    function updatePaymentSummary() {

        const amount =
            parseFloat(
                amountInput.val()
            ) || 0;

        $('#summaryPayment').text(
            money(amount)
        );
    }


    /*
    |--------------------------------------------------------------------------
    | STUDENT INFO
    |--------------------------------------------------------------------------
    */

    function showStudentInfo(
        text,
        studentId,
        assignmentId
    ) {

        const option =
            studentSelect.find(':selected');

        const student =
            option.text();

        $('#studentName').text(
            student
        );

        $('#studentNumber').text(
            studentId
        );

        $('#studentClass').text(
            $('#classSelect option:selected').text()
        );

        $('#studentYear').text(
            $('#academicYearSelect option:selected').text()
        );

        $('#summaryStudent').text(
            student
        );

        $('#summaryClass').text(
            $('#classSelect option:selected').text()
        );

        $('#summaryTerm').text(
            $('#termSelect option:selected').text()
        );

        $('#studentInfo')
            .removeClass('d-none');
    }


    /*
    |--------------------------------------------------------------------------
    | RESET
    |--------------------------------------------------------------------------
    */

    function resetStudent() {

        studentSelect
            .prop('disabled', true)
            .html(
                '<option value="">Select Academic Year and Class first</option>'
            );

        assignmentInput.val('');

        $('#studentInfo')
            .addClass('d-none');

        resetBill();
    }


    function resetBill() {

        selectedBill = null;

        billSheets = [];

        billSheetId.val('');

        billCard.addClass('d-none');

        billSummary.addClass('d-none');

        paymentCard.addClass('d-none');

        noBillMessage.addClass('d-none');

        billSelectorWrapper.addClass('d-none');

        amountInput.val('');

        $('#billAmount')
            .text('GHS 0.00');

        $('#billPaid')
            .text('GHS 0.00');

        $('#billBalance')
            .text('GHS 0.00');

        $('#maximumAmount')
            .text('GHS 0.00');

        $('#summaryBill')
            .text('GHS 0.00');

        $('#summaryPaid')
            .text('GHS 0.00');

        $('#summaryBalance')
            .text('GHS 0.00');

        $('#summaryPayment')
            .text('GHS 0.00');

        submitBtn.prop(
            'disabled',
            true
        );
    }


    /*
    |--------------------------------------------------------------------------
    | HELPERS
    |--------------------------------------------------------------------------
    */

    function money(value) {

        return 'GHS '
            +
            Number(value || 0)
                .toLocaleString(
                    'en-GH',
                    {
                        minimumFractionDigits: 2,
                        maximumFractionDigits: 2
                    }
                );
    }


    /*
    |--------------------------------------------------------------------------
    | EVENTS
    |--------------------------------------------------------------------------
    */

    academicYear.on('change', loadStudents);
    classSelect.on('change', loadStudents);
    termSelect.on('change', function () {
        loadStudents();

        const studentId = studentSelect.val();
        const assignmentId = assignmentInput.val();

        if (studentId && assignmentId && termSelect.val()) {
            loadStudentBill(studentId, assignmentId);
        }
    });


    /*
    |--------------------------------------------------------------------------
    | FORM VALIDATION
    |--------------------------------------------------------------------------
    */

    $('#paymentForm').on(
        'submit',
        function (event) {

            if (!selectedBill) {

                event.preventDefault();

                alert(
                    'Please select a student with an available Bill Sheet.'
                );

                return;
            }

            const amount =
                parseFloat(
                    amountInput.val()
                ) || 0;

            if (amount <= 0) {

                event.preventDefault();

                alert(
                    'Please enter a valid payment amount.'
                );

                return;
            }

            if (
                amount >
                selectedBill.balance
            ) {

                event.preventDefault();

                alert(
                    'Payment cannot exceed the outstanding Bill Sheet balance.'
                );

                return;
            }

            if (!billSheetId.val()) {

                event.preventDefault();

                alert(
                    'No Bill Sheet has been selected.'
                );

                return;
            }

            submitBtn
                .prop('disabled', true)
                .html(
                    '<span class="spinner-border spinner-border-sm me-2"></span>'
                    +
                    'Recording Payment...'
                );
        }
    );

});

</script>

@endpush

@endsection