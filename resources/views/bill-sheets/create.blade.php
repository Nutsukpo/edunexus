@extends('layouts.master')

@section('title', 'Generate Bill Sheets')

@section('content')

<div class="container-fluid">

    {{-- =========================================================
         PAGE HEADER
    ========================================================== --}}
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-4 mt-3">

        <div>
            <h4 class="mb-1 fw-bold">
                <i class="fas fa-file-invoice-dollar text-primary me-2"></i>
                Generate Bill Sheets
            </h4>

            <p class="text-muted mb-0">
                Generate individual Bill Sheets for all active students
                in a selected class.
            </p>
        </div>

        <a href="{{ route('bill-sheets.index') }}"
           class="btn btn-secondary">
            <i class="fas fa-arrow-left me-1"></i>
            Back
        </a>

    </div>


    {{-- =========================================================
         VALIDATION ERRORS
    ========================================================== --}}
    @if($errors->any())

        <div class="alert alert-danger alert-dismissible fade show shadow-sm">

            <div class="fw-bold mb-2">
                <i class="fas fa-exclamation-triangle me-1"></i>
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


    {{-- =========================================================
         SESSION ERROR
    ========================================================== --}}
    @if(session('error'))

        <div class="alert alert-danger alert-dismissible fade show shadow-sm">

            <i class="fas fa-exclamation-circle me-1"></i>

            {{ session('error') }}

            <button type="button"
                    class="btn-close"
                    data-bs-dismiss="alert">
            </button>

        </div>

    @endif


    {{-- =========================================================
         SESSION SUCCESS
    ========================================================== --}}
    @if(session('success'))

        <div class="alert alert-success alert-dismissible fade show shadow-sm">

            <i class="fas fa-check-circle me-1"></i>

            {{ session('success') }}

            <button type="button"
                    class="btn-close"
                    data-bs-dismiss="alert">
            </button>

        </div>

    @endif


    {{-- =========================================================
         MAIN FORM
    ========================================================== --}}
    <form action="{{ route('bill-sheets.store') }}"
          method="POST"
          id="billSheetForm">

        @csrf


        {{-- =====================================================
             CLASS / ACADEMIC YEAR / TERM
        ====================================================== --}}
        <div class="card shadow-sm border-0 mb-4">

            <div class="card-header bg-white border-bottom py-3">

                <h5 class="mb-0 fw-bold">

                    <i class="fas fa-users text-primary me-2"></i>

                    Select Class and Billing Period

                </h5>

            </div>


            <div class="card-body">

                <div class="row g-3">


                    {{-- =================================================
                         ACADEMIC YEAR
                    ================================================== --}}
                    <div class="col-md-4">

                        <label for="academic_year_id"
                               class="form-label fw-semibold">

                            Academic Year

                            <span class="text-danger">*</span>

                        </label>


                        <select name="academic_year_id"
                                id="academic_year_id"
                                class="form-select @error('academic_year_id') is-invalid @enderror"
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


                        @error('academic_year_id')

                            <div class="invalid-feedback">
                                {{ $message }}
                            </div>

                        @enderror

                    </div>


                    {{-- =================================================
                         CLASS
                    ================================================== --}}
                    <div class="col-md-4">

                        <label for="student_class_id"
                               class="form-label fw-semibold">

                            Class

                            <span class="text-danger">*</span>

                        </label>


                        <select name="student_class_id"
                                id="student_class_id"
                                class="form-select @error('student_class_id') is-invalid @enderror"
                                required>

                            <option value="">
                                Select Class
                            </option>

                            @foreach($classes as $class)

                                <option value="{{ $class->id }}"
                                    {{ old('student_class_id') == $class->id ? 'selected' : '' }}>

                                    {{ $class->name }}

                                </option>

                            @endforeach

                        </select>


                        @error('student_class_id')

                            <div class="invalid-feedback">
                                {{ $message }}
                            </div>

                        @enderror

                    </div>


                    {{-- =================================================
                         TERM
                    ================================================== --}}
                    <div class="col-md-4">

                        <label for="term_id"
                               class="form-label fw-semibold">

                            Term

                            <span class="text-danger">*</span>

                        </label>


                        <select name="term_id"
                                id="term_id"
                                class="form-select @error('term_id') is-invalid @enderror"
                                required>

                            <option value="">
                                Select Term
                            </option>

                            @foreach($terms as $term)

                                <option value="{{ $term->id }}"
                                    {{ old('term_id') == $term->id ? 'selected' : '' }}>

                                    {{ $term->name }}

                                </option>

                            @endforeach

                        </select>


                        @error('term_id')

                            <div class="invalid-feedback">
                                {{ $message }}
                            </div>

                        @enderror

                    </div>

                </div>


                {{-- =================================================
                     STUDENT SUMMARY
                ================================================== --}}
                <div id="studentSummary"
                     class="mt-4 d-none">

                    <div class="alert alert-info mb-0">

                        <div class="d-flex align-items-center">

                            <div class="me-3">

                                <i class="fas fa-users fa-2x"></i>

                            </div>


                            <div>

                                <div class="fw-bold">
                                    Students to be billed
                                </div>

                                <div>

                                    The system found

                                    <strong id="studentCount">
                                        0
                                    </strong>

                                    active student assignment(s).

                                </div>

                            </div>

                        </div>

                    </div>

                </div>

            </div>

        </div>


        {{-- =====================================================
             BILL INFORMATION
        ====================================================== --}}
        <div class="card shadow-sm border-0 mb-4">

            <div class="card-header bg-white border-bottom py-3">

                <h5 class="mb-0 fw-bold">

                    <i class="fas fa-file-alt text-primary me-2"></i>

                    Bill Sheet Information

                </h5>

            </div>


            <div class="card-body">

                <div class="row g-3">


                    {{-- BILL NAME --}}
                    <div class="col-md-6">

                        <label for="name"
                               class="form-label fw-semibold">

                            Bill Sheet Name

                            <span class="text-danger">*</span>

                        </label>


                        <input type="text"
                               name="name"
                               id="name"
                               class="form-control @error('name') is-invalid @enderror"
                               value="{{ old('name') }}"
                               placeholder="e.g. First Term Fees"
                               required>


                        <small class="text-muted">

                            This will be used as the base name.
                            The student's name will automatically be added
                            to each generated Bill Sheet.

                        </small>


                        @error('name')

                            <div class="invalid-feedback">
                                {{ $message }}
                            </div>

                        @enderror

                    </div>


                    {{-- GENERATED DATE --}}
                    <div class="col-md-3">

                        <label for="generated_date"
                               class="form-label fw-semibold">

                            Generated Date

                            <span class="text-danger">*</span>

                        </label>


                        <input type="date"
                               name="generated_date"
                               id="generated_date"
                               class="form-control"
                               value="{{ old('generated_date', now()->format('Y-m-d')) }}"
                               required>

                    </div>


                    {{-- DUE DATE --}}
                    <div class="col-md-3">

                        <label for="due_date"
                               class="form-label fw-semibold">

                            Due Date

                        </label>


                        <input type="date"
                               name="due_date"
                               id="due_date"
                               class="form-control"
                               value="{{ old('due_date') }}">

                    </div>


                    {{-- DESCRIPTION --}}
                    <div class="col-12">

                        <label for="description"
                               class="form-label fw-semibold">

                            Description

                        </label>


                        <textarea name="description"
                                  id="description"
                                  rows="3"
                                  class="form-control"
                                  placeholder="Optional description...">{{ old('description') }}</textarea>

                    </div>

                </div>

            </div>

        </div>


        {{-- =====================================================
             FEE ITEMS
        ====================================================== --}}
        <div class="card shadow-sm border-0 mb-4">

            <div class="card-header bg-white border-bottom py-3">

                <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">

                    <div>

                        <h5 class="mb-1 fw-bold">

                            <i class="fas fa-list text-primary me-2"></i>

                            Fee Items

                            <span class="badge bg-primary ms-1"
                                  id="itemCount">

                                1

                            </span>

                        </h5>


                        <small class="text-muted">

                            These items will be copied to every student's
                            generated Bill Sheet.

                        </small>

                    </div>


                    {{-- IMPORTANT:
                         type="button" prevents this button from submitting
                         the main form.
                    --}}
                    <button type="button"
                            class="btn btn-primary btn-sm"
                            id="addItemBtn">

                        <i class="fas fa-plus me-1"></i>

                        Add Fee Item

                    </button>

                </div>

            </div>


            <div class="card-body">

                <div class="table-responsive">

                    <table class="table table-bordered align-middle mb-0"
                           id="itemsTable">

                        <thead class="table-light">

                            <tr>

                                <th style="width:5%;">
                                    #
                                </th>

                                <th style="width:20%;">
                                    Fee Category
                                </th>

                                <th style="width:25%;">
                                    Item Name
                                </th>

                                <th style="width:15%;">
                                    Amount
                                </th>

                                <th style="width:12%;">
                                    Quantity
                                </th>

                                <th style="width:13%;">
                                    Total
                                </th>

                                <th style="width:6%;"
                                    class="text-center">

                                    Optional

                                </th>

                                <th style="width:4%;"
                                    class="text-center">

                                </th>

                            </tr>

                        </thead>


                        <tbody id="itemsBody">


                            {{-- =================================================
                                 INITIAL ITEM
                            ================================================== --}}
                            <tr class="item-row"
                                data-index="0">


                                {{-- NUMBER --}}
                                <td class="row-number text-center fw-semibold">

                                    1

                                </td>


                                {{-- CATEGORY --}}
                                <td>

                                    <select name="items[0][fee_category_id]"
                                            class="form-select">

                                        <option value="">
                                            Select Category
                                        </option>

                                        @foreach($feeCategories as $category)

                                            <option value="{{ $category->id }}">

                                                {{ $category->name }}

                                            </option>

                                        @endforeach

                                    </select>

                                </td>


                                {{-- ITEM NAME --}}
                                <td>

                                    <input type="text"
                                           name="items[0][name]"
                                           class="form-control item-name"
                                           placeholder="e.g. Tuition Fees"
                                           required>

                                </td>


                                {{-- AMOUNT --}}
                                <td>

                                    <input type="number"
                                           name="items[0][amount]"
                                           class="form-control item-amount text-end"
                                           value="0"
                                           min="0"
                                           step="0.01"
                                           required>

                                </td>


                                {{-- QUANTITY --}}
                                <td>

                                    <input type="number"
                                           name="items[0][quantity]"
                                           class="form-control item-quantity text-center"
                                           value="1"
                                           min="1"
                                           step="1"
                                           required>

                                </td>


                                {{-- TOTAL --}}
                                <td>

                                    <input type="text"
                                           class="form-control item-total text-end"
                                           value="0.00"
                                           readonly>

                                </td>


                                {{-- OPTIONAL --}}
                                <td class="text-center">

                                    <input type="checkbox"
                                           name="items[0][is_optional]"
                                           value="1"
                                           class="form-check-input">

                                </td>


                                {{-- REMOVE --}}
                                <td class="text-center">

                                    <button type="button"
                                            class="btn btn-outline-danger btn-sm remove-item"
                                            title="Remove item"
                                            disabled>

                                        <i class="fas fa-trash"></i>

                                    </button>

                                </td>

                            </tr>

                        </tbody>


                        {{-- =====================================================
                             TOTALS
                        ====================================================== --}}
                        <tfoot>


                            {{-- SUBTOTAL --}}
                            <tr class="table-light">

                                <td colspan="5"
                                    class="text-end fw-bold">

                                    Subtotal:

                                </td>

                                <td>

                                    <input type="text"
                                           id="subtotalDisplay"
                                           class="form-control text-end fw-bold"
                                           value="0.00"
                                           readonly>

                                </td>

                                <td colspan="2"></td>

                            </tr>


                            {{-- DISCOUNT --}}
                            <tr>

                                <td colspan="5"
                                    class="text-end fw-bold">

                                    Discount:

                                </td>

                                <td>

                                    <input type="number"
                                           name="discount_amount"
                                           id="discount_amount"
                                           class="form-control text-end"
                                           value="{{ old('discount_amount', 0) }}"
                                           min="0"
                                           step="0.01">

                                </td>

                                <td colspan="2"></td>

                            </tr>


                            {{-- TAX --}}
                            <tr>

                                <td colspan="5"
                                    class="text-end fw-bold">

                                    Tax:

                                </td>

                                <td>

                                    <input type="number"
                                           name="tax_amount"
                                           id="tax_amount"
                                           class="form-control text-end"
                                           value="{{ old('tax_amount', 0) }}"
                                           min="0"
                                           step="0.01">

                                </td>

                                <td colspan="2"></td>

                            </tr>


                            {{-- GRAND TOTAL --}}
                            <tr class="table-success">

                                <td colspan="5"
                                    class="text-end fw-bold fs-5">

                                    Grand Total:

                                </td>

                                <td>

                                    <input type="text"
                                           id="netTotalDisplay"
                                           class="form-control text-end fw-bold"
                                           value="0.00"
                                           readonly>

                                </td>

                                <td colspan="2"></td>

                            </tr>


                        </tfoot>

                    </table>

                </div>

            </div>

        </div>


        {{-- =====================================================
             INFORMATION
        ====================================================== --}}
        <div class="alert alert-primary shadow-sm">

            <div class="d-flex">

                <div class="me-3">

                    <i class="fas fa-info-circle fa-2x"></i>

                </div>


                <div>

                    <h6 class="fw-bold mb-1">

                        How Bill Generation Works

                    </h6>


                    <p class="mb-0">

                        You are generating bills for an entire class.

                        EDUNEXUS will automatically find all active
                        students assigned to the selected class and
                        academic year and create

                        <strong>
                            one Bill Sheet per student assignment
                        </strong>.

                        Students who already have a Bill Sheet for the
                        selected academic year and term will be skipped
                        automatically.

                    </p>

                </div>

            </div>

        </div>


        {{-- =====================================================
             FORM BUTTONS
        ====================================================== --}}
        <div class="d-flex justify-content-end gap-2 mb-5">

            <a href="{{ route('bill-sheets.index') }}"
               class="btn btn-secondary">

                <i class="fas fa-times me-1"></i>

                Cancel

            </a>


            <button type="submit"
                    id="generateBtn"
                    class="btn btn-primary">

                <i class="fas fa-file-invoice-dollar me-1"></i>

                Generate Bill Sheets

            </button>

        </div>

    </form>

</div>

@endsection


@push('styles')

<style>

    /*
    |--------------------------------------------------------------------------
    | FEE ITEM TABLE
    |--------------------------------------------------------------------------
    */

    #itemsTable th,
    #itemsTable td {
        vertical-align: middle;
    }


    /*
    |--------------------------------------------------------------------------
    | ITEM TOTAL
    |--------------------------------------------------------------------------
    */

    .item-total {
        background-color: #f8f9fa;
    }


    /*
    |--------------------------------------------------------------------------
    | INVALID FIELDS
    |--------------------------------------------------------------------------
    */

    .is-invalid {
        border-color: #dc3545 !important;
    }

</style>

@endpush


@push('scripts')

<script>
(function () {

    'use strict';


    /*
    |--------------------------------------------------------------------------
    | IMPORTANT INITIALIZATION GUARD
    |--------------------------------------------------------------------------
    |
    | This prevents the script from being initialized twice.
    |
    | If layouts.master accidentally renders this script twice, the second
    | execution will immediately stop.
    |
    */

    if (window.__EDUNEXUS_BILL_SHEET_CREATE_INITIALIZED === true) {
        return;
    }

    window.__EDUNEXUS_BILL_SHEET_CREATE_INITIALIZED = true;


    /*
    |--------------------------------------------------------------------------
    | DOM READY
    |--------------------------------------------------------------------------
    */

    document.addEventListener('DOMContentLoaded', function () {


        /*
        |--------------------------------------------------------------------------
        | ELEMENTS
        |--------------------------------------------------------------------------
        */

        const form =
            document.getElementById('billSheetForm');

        const itemsBody =
            document.getElementById('itemsBody');

        const addItemBtn =
            document.getElementById('addItemBtn');

        const discountInput =
            document.getElementById('discount_amount');

        const taxInput =
            document.getElementById('tax_amount');

        const generateBtn =
            document.getElementById('generateBtn');

        const classSelect =
            document.getElementById('student_class_id');

        const academicYearSelect =
            document.getElementById('academic_year_id');

        const studentSummary =
            document.getElementById('studentSummary');

        const studentCount =
            document.getElementById('studentCount');


        /*
        |--------------------------------------------------------------------------
        | SAFETY CHECK
        |--------------------------------------------------------------------------
        */

        if (
            !form ||
            !itemsBody ||
            !addItemBtn
        ) {
            return;
        }


        /*
        |--------------------------------------------------------------------------
        | FEE CATEGORY DATA
        |--------------------------------------------------------------------------
        */

        const feeCategories = @json(
            $feeCategories->map(function ($category) {

                return [
                    'id' => $category->id,
                    'name' => $category->name,
                ];

            })->values()
        );


        /*
        |--------------------------------------------------------------------------
        | ITEM INDEX
        |--------------------------------------------------------------------------
        |
        | The first row uses index 0.
        | New rows use 1, 2, 3, etc.
        |
        */

        let itemIndex = 1;


        /*
        |--------------------------------------------------------------------------
        | ESCAPE HTML
        |--------------------------------------------------------------------------
        */

        function escapeHtml(value) {

            const div =
                document.createElement('div');

            div.textContent =
                value ?? '';

            return div.innerHTML;
        }


        /*
        |--------------------------------------------------------------------------
        | BUILD CATEGORY OPTIONS
        |--------------------------------------------------------------------------
        */

        function buildCategoryOptions() {

            let html =
                '<option value="">Select Category</option>';


            feeCategories.forEach(function (category) {

                html +=
                    '<option value="' +
                    category.id +
                    '">' +
                    escapeHtml(category.name) +
                    '</option>';

            });


            return html;
        }


        /*
        |--------------------------------------------------------------------------
        | ADD ONE FEE ITEM
        |--------------------------------------------------------------------------
        */

        function addFeeItem() {

            /*
            |--------------------------------------------------------------------------
            | Capture and increment the index ONCE.
            |--------------------------------------------------------------------------
            */

            const index =
                itemIndex++;


            /*
            |--------------------------------------------------------------------------
            | Create row
            |--------------------------------------------------------------------------
            */

            const row =
                document.createElement('tr');


            row.className =
                'item-row';


            row.dataset.index =
                index;


            row.innerHTML = `

                <td class="row-number text-center fw-semibold">
                    0
                </td>


                <td>

                    <select
                        name="items[${index}][fee_category_id]"
                        class="form-select">

                        ${buildCategoryOptions()}

                    </select>

                </td>


                <td>

                    <input
                        type="text"
                        name="items[${index}][name]"
                        class="form-control item-name"
                        placeholder="e.g. Tuition Fees"
                        required>

                </td>


                <td>

                    <input
                        type="number"
                        name="items[${index}][amount]"
                        class="form-control item-amount text-end"
                        value="0"
                        min="0"
                        step="0.01"
                        required>

                </td>


                <td>

                    <input
                        type="number"
                        name="items[${index}][quantity]"
                        class="form-control item-quantity text-center"
                        value="1"
                        min="1"
                        step="1"
                        required>

                </td>


                <td>

                    <input
                        type="text"
                        class="form-control item-total text-end"
                        value="0.00"
                        readonly>

                </td>


                <td class="text-center">

                    <input
                        type="checkbox"
                        name="items[${index}][is_optional]"
                        value="1"
                        class="form-check-input">

                </td>


                <td class="text-center">

                    <button
                        type="button"
                        class="btn btn-outline-danger btn-sm remove-item"
                        title="Remove item">

                        <i class="fas fa-trash"></i>

                    </button>

                </td>

            `;


            /*
            |--------------------------------------------------------------------------
            | Add the row exactly ONCE
            |--------------------------------------------------------------------------
            */

            itemsBody.appendChild(row);


            /*
            |--------------------------------------------------------------------------
            | Refresh UI
            |--------------------------------------------------------------------------
            */

            updateRowNumbers();

            updateRemoveButtons();

            calculateTotals();


            /*
            |--------------------------------------------------------------------------
            | Focus item name
            |--------------------------------------------------------------------------
            */

            const nameInput =
                row.querySelector('.item-name');


            if (nameInput) {

                nameInput.focus();

            }

        }


        /*
        |--------------------------------------------------------------------------
        | ADD BUTTON
        |--------------------------------------------------------------------------
        |
        | THIS IS THE ONLY ADD BUTTON EVENT HANDLER.
        |
        | There is deliberately no jQuery handler anywhere on this page.
        |
        */

        addItemBtn.addEventListener(
            'click',
            function (event) {

                event.preventDefault();

                event.stopPropagation();

                addFeeItem();

            },
            false
        );


        /*
        |--------------------------------------------------------------------------
        | REMOVE FEE ITEM
        |--------------------------------------------------------------------------
        */

        itemsBody.addEventListener(
            'click',
            function (event) {

                const button =
                    event.target.closest('.remove-item');


                if (!button) {
                    return;
                }


                event.preventDefault();


                const rows =
                    itemsBody.querySelectorAll('.item-row');


                /*
                |--------------------------------------------------------------------------
                | Always keep at least one row.
                |--------------------------------------------------------------------------
                */

                if (rows.length <= 1) {

                    return;

                }


                const row =
                    button.closest('.item-row');


                if (row) {

                    row.remove();

                }


                updateRowNumbers();

                updateRemoveButtons();

                calculateTotals();

            }
        );


        /*
        |--------------------------------------------------------------------------
        | UPDATE ROW NUMBERS
        |--------------------------------------------------------------------------
        */

        function updateRowNumbers() {

            const rows =
                itemsBody.querySelectorAll('.item-row');


            rows.forEach(
                function (row, index) {

                    const number =
                        row.querySelector('.row-number');


                    if (number) {

                        number.textContent =
                            index + 1;

                    }

                }
            );


            const itemCount =
                document.getElementById('itemCount');


            if (itemCount) {

                itemCount.textContent =
                    rows.length;

            }

        }


        /*
        |--------------------------------------------------------------------------
        | UPDATE REMOVE BUTTONS
        |--------------------------------------------------------------------------
        */

        function updateRemoveButtons() {

            const rows =
                itemsBody.querySelectorAll('.item-row');


            const buttons =
                itemsBody.querySelectorAll('.remove-item');


            const disable =
                rows.length <= 1;


            buttons.forEach(
                function (button) {

                    button.disabled =
                        disable;

                }
            );

        }


        /*
        |--------------------------------------------------------------------------
        | CALCULATE ONE ITEM
        |--------------------------------------------------------------------------
        */

        function calculateItemTotal(row) {

            const amountInput =
                row.querySelector('.item-amount');


            const quantityInput =
                row.querySelector('.item-quantity');


            const totalInput =
                row.querySelector('.item-total');


            const amount =
                parseFloat(
                    amountInput?.value
                ) || 0;


            const quantity =
                parseInt(
                    quantityInput?.value
                ) || 0;


            const total =
                amount * quantity;


            if (totalInput) {

                totalInput.value =
                    total.toFixed(2);

            }


            return total;

        }


        /*
        |--------------------------------------------------------------------------
        | CALCULATE ALL TOTALS
        |--------------------------------------------------------------------------
        */

        function calculateTotals() {

            let subtotal = 0;


            const rows =
                itemsBody.querySelectorAll('.item-row');


            rows.forEach(
                function (row) {

                    subtotal +=
                        calculateItemTotal(row);

                }
            );


            const discount =
                parseFloat(
                    discountInput?.value
                ) || 0;


            const tax =
                parseFloat(
                    taxInput?.value
                ) || 0;


            let netTotal =
                subtotal
                - discount
                + tax;


            if (netTotal < 0) {

                netTotal = 0;

            }


            const subtotalDisplay =
                document.getElementById(
                    'subtotalDisplay'
                );


            const netTotalDisplay =
                document.getElementById(
                    'netTotalDisplay'
                );


            if (subtotalDisplay) {

                subtotalDisplay.value =
                    subtotal.toFixed(2);

            }


            if (netTotalDisplay) {

                netTotalDisplay.value =
                    netTotal.toFixed(2);

            }

        }


        /*
        |--------------------------------------------------------------------------
        | FEE ITEM INPUT EVENTS
        |--------------------------------------------------------------------------
        */

        itemsBody.addEventListener(
            'input',
            function (event) {

                if (
                    event.target.classList.contains(
                        'item-amount'
                    ) ||
                    event.target.classList.contains(
                        'item-quantity'
                    )
                ) {

                    calculateTotals();

                }

            }
        );


        /*
        |--------------------------------------------------------------------------
        | DISCOUNT
        |--------------------------------------------------------------------------
        */

        if (discountInput) {

            discountInput.addEventListener(
                'input',
                calculateTotals
            );

        }


        /*
        |--------------------------------------------------------------------------
        | TAX
        |--------------------------------------------------------------------------
        */

        if (taxInput) {

            taxInput.addEventListener(
                'input',
                calculateTotals
            );

        }


        /*
        |--------------------------------------------------------------------------
        | LOAD ACTIVE STUDENT COUNT
        |--------------------------------------------------------------------------
        */

        let activeRequest = null;


        async function loadStudentCount() {

            const classId =
                classSelect?.value;


            const academicYearId =
                academicYearSelect?.value;


            if (!studentSummary || !studentCount) {

                return;

            }


            studentSummary.classList.add(
                'd-none'
            );


            if (
                !classId ||
                !academicYearId
            ) {

                studentCount.textContent =
                    '0';

                return;

            }


            /*
            |--------------------------------------------------------------------------
            | Cancel previous request.
            |--------------------------------------------------------------------------
            */

            if (activeRequest) {

                activeRequest.abort();

            }


            activeRequest =
                new AbortController();


            studentSummary.classList.remove(
                'd-none'
            );


            studentCount.textContent =
                'Loading...';


            const params =
                new URLSearchParams({

                    student_class_id:
                        classId,

                    academic_year_id:
                        academicYearId

                });


            try {

                const response =
                    await fetch(
                        `{{ route('bill-sheets.assignments') }}?${params.toString()}`,
                        {
                            method: 'GET',

                            headers: {
                                'Accept':
                                    'application/json',

                                'X-Requested-With':
                                    'XMLHttpRequest'
                            },

                            signal:
                                activeRequest.signal
                        }
                    );


                if (!response.ok) {

                    throw new Error(
                        'Unable to load student assignments.'
                    );

                }


                const data =
                    await response.json();


                if (data.success) {

                    studentCount.textContent =
                        data.count ?? 0;

                } else {

                    studentSummary.classList.add(
                        'd-none'
                    );

                }


            } catch (error) {

                /*
                |--------------------------------------------------------------------------
                | Ignore intentionally cancelled requests.
                |--------------------------------------------------------------------------
                */

                if (
                    error.name ===
                    'AbortError'
                ) {

                    return;

                }


                console.error(
                    'Student count error:',
                    error
                );


                studentSummary.classList.add(
                    'd-none'
                );

            }

        }


        /*
        |--------------------------------------------------------------------------
        | CLASS CHANGE
        |--------------------------------------------------------------------------
        */

        if (classSelect) {

            classSelect.addEventListener(
                'change',
                loadStudentCount
            );

        }


        /*
        |--------------------------------------------------------------------------
        | ACADEMIC YEAR CHANGE
        |--------------------------------------------------------------------------
        */

        if (academicYearSelect) {

            academicYearSelect.addEventListener(
                'change',
                loadStudentCount
            );

        }


        /*
        |--------------------------------------------------------------------------
        | FORM SUBMISSION
        |--------------------------------------------------------------------------
        */

        form.addEventListener(
            'submit',
            function (event) {

                const classId =
                    classSelect?.value;


                const academicYearId =
                    academicYearSelect?.value;


                const termId =
                    document.getElementById(
                        'term_id'
                    )?.value;


                /*
                |--------------------------------------------------------------------------
                | Validate billing period.
                |--------------------------------------------------------------------------
                */

                if (
                    !classId ||
                    !academicYearId ||
                    !termId
                ) {

                    event.preventDefault();


                    alert(
                        'Please select the Academic Year, Class and Term.'
                    );


                    return;

                }


                /*
                |--------------------------------------------------------------------------
                | Validate at least one item.
                |--------------------------------------------------------------------------
                */

                const rows =
                    itemsBody.querySelectorAll(
                        '.item-row'
                    );


                if (rows.length === 0) {

                    event.preventDefault();


                    alert(
                        'Please add at least one fee item.'
                    );


                    return;

                }


                /*
                |--------------------------------------------------------------------------
                | Validate item fields.
                |--------------------------------------------------------------------------
                */

                let valid =
                    true;


                rows.forEach(
                    function (row) {

                        const name =
                            row.querySelector(
                                '.item-name'
                            );


                        const amount =
                            row.querySelector(
                                '.item-amount'
                            );


                        const quantity =
                            row.querySelector(
                                '.item-quantity'
                            );


                        if (
                            !name ||
                            !name.value.trim()
                        ) {

                            valid = false;

                            name?.classList.add(
                                'is-invalid'
                            );

                        } else {

                            name.classList.remove(
                                'is-invalid'
                            );

                        }


                        if (
                            !amount ||
                            amount.value === '' ||
                            parseFloat(
                                amount.value
                            ) < 0
                        ) {

                            valid = false;

                            amount?.classList.add(
                                'is-invalid'
                            );

                        } else {

                            amount.classList.remove(
                                'is-invalid'
                            );

                        }


                        if (
                            !quantity ||
                            quantity.value === '' ||
                            parseInt(
                                quantity.value
                            ) < 1
                        ) {

                            valid = false;

                            quantity?.classList.add(
                                'is-invalid'
                            );

                        } else {

                            quantity.classList.remove(
                                'is-invalid'
                            );

                        }

                    }
                );


                if (!valid) {

                    event.preventDefault();


                    alert(
                        'Please correct the highlighted fee items.'
                    );


                    return;

                }


                /*
                |--------------------------------------------------------------------------
                | Recalculate before submission.
                |--------------------------------------------------------------------------
                */

                calculateTotals();


                /*
                |--------------------------------------------------------------------------
                | Prevent double submission.
                |--------------------------------------------------------------------------
                */

                if (generateBtn) {

                    generateBtn.disabled =
                        true;


                    generateBtn.innerHTML = `

                        <span
                            class="spinner-border spinner-border-sm me-1">
                        </span>

                        Generating Bill Sheets...

                    `;

                }

            }
        );


        /*
        |--------------------------------------------------------------------------
        | INITIAL PAGE SETUP
        |--------------------------------------------------------------------------
        */

        updateRowNumbers();

        updateRemoveButtons();

        calculateTotals();

        loadStudentCount();

    });

})();
</script>

@endpush