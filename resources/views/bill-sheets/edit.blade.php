@extends('layouts.master')

@section('title', 'Edit Bill Sheet')

@section('content')
<div class="container-fluid py-3">

    {{-- PAGE HEADER --}}
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-4">
        <div>
            <h4 class="mb-1 fw-bold">
                <i class="fas fa-file-invoice-dollar text-warning me-2"></i>
                Edit Bill Sheet
            </h4>
            <p class="text-muted mb-0">
                Update this student's bill sheet. Changes made here affect this BillSheet only.
            </p>
        </div>

        <div class="d-flex gap-2">
            <a href="{{ route('bill-sheets.show', $billSheet->id) }}" class="btn btn-secondary btn-sm">
                <i class="fas fa-eye me-1"></i> View
            </a>

            <a href="{{ route('bill-sheets.index') }}" class="btn btn-outline-secondary btn-sm">
                <i class="fas fa-arrow-left me-1"></i> Back
            </a>
        </div>
    </div>

    {{-- ERRORS --}}
    @if($errors->any())
        <div class="alert alert-danger alert-dismissible fade show shadow-sm" role="alert">
            <div class="fw-bold mb-2">
                <i class="fas fa-exclamation-triangle me-1"></i>
                Please correct the following errors:
            </div>

            <ul class="mb-0">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>

            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    {{-- PAYMENT SAFETY WARNING --}}
    @php
        $hasPayments = method_exists($billSheet, 'payments')
            ? $billSheet->payments()->exists()
            : \App\Models\FeePayment::where('bill_sheet_id', $billSheet->id)->exists();

        $assignment = $billSheet->studentClassAssignment;
        $student = $assignment?->student;
        $studentClass = $assignment?->studentClass;
        $academicYear = $billSheet->academicYear;
        $term = $billSheet->term;
    @endphp

    @if($hasPayments)
        <div class="alert alert-warning shadow-sm">
            <div class="fw-bold">
                <i class="fas fa-lock me-2"></i>
                This BillSheet has payment records.
            </div>
            <div class="small mt-1">
                Existing payment history must be protected. Review the bill carefully before changing
                amounts or items.
            </div>
        </div>
    @endif

    <form action="{{ route('bill-sheets.update', $billSheet->id) }}"
          method="POST"
          id="billSheetForm"
          novalidate>

        @csrf
        @method('PUT')

        {{-- =========================================================
             STUDENT / ASSIGNMENT INFORMATION
        ========================================================== --}}
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white border-bottom py-3">
                <h6 class="mb-0 fw-bold">
                    <i class="fas fa-user-graduate text-primary me-2"></i>
                    Student & Bill Information
                </h6>
            </div>

            <div class="card-body">
                <div class="row g-3">

                    {{-- BILL NAME --}}
                    <div class="col-lg-6">
                        <label for="name" class="form-label fw-semibold">
                            Bill Sheet Name <span class="text-danger">*</span>
                        </label>

                        <input type="text"
                               name="name"
                               id="name"
                               class="form-control @error('name') is-invalid @enderror"
                               value="{{ old('name', $billSheet->name) }}"
                               required>

                        @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- STUDENT --}}
                    <div class="col-lg-6">
                        <label class="form-label fw-semibold">Student</label>

                        <div class="form-control bg-light">
                            <i class="fas fa-user text-primary me-2"></i>
                            {{ $student?->full_name ?? trim(($student?->first_name ?? '') . ' ' . ($student?->middle_name ?? '') . ' ' . ($student?->last_name ?? '')) ?: 'N/A' }}
                        </div>
                    </div>

                    {{-- ASSIGNMENT --}}
                    <div class="col-lg-4">
                        <label class="form-label fw-semibold">
                            Student Class Assignment
                        </label>

                        {{-- Do NOT allow changing the assignment from this page.
                             A BillSheet belongs to a specific assignment. --}}
                        <input type="hidden"
                               name="student_class_assignment_id"
                               value="{{ old('student_class_assignment_id', $billSheet->student_class_assignment_id) }}">

                        <div class="form-control bg-light">
                            <i class="fas fa-link text-primary me-2"></i>
                            Assignment #{{ $billSheet->student_class_assignment_id ?? 'N/A' }}
                        </div>
                    </div>

                    {{-- CLASS --}}
                    <div class="col-lg-4">
                        <label class="form-label fw-semibold">Class</label>

                        <div class="form-control bg-light">
                            <i class="fas fa-school text-primary me-2"></i>
                            {{ $studentClass?->name ?? 'N/A' }}
                        </div>
                    </div>

                    {{-- ACADEMIC YEAR --}}
                    <div class="col-lg-4">
                        <label class="form-label fw-semibold">
                            Academic Year <span class="text-danger">*</span>
                        </label>

                        <select name="academic_year_id"
                                id="academicYear"
                                class="form-select @error('academic_year_id') is-invalid @enderror"
                                required>
                            <option value="">Select Academic Year</option>

                            @foreach($academicYears as $year)
                                <option value="{{ $year->id }}"
                                    {{ old('academic_year_id', $billSheet->academic_year_id) == $year->id ? 'selected' : '' }}>
                                    {{ $year->name }}
                                </option>
                            @endforeach
                        </select>

                        @error('academic_year_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- TERM --}}
                    <div class="col-lg-4">
                        <label class="form-label fw-semibold">
                            Term <span class="text-danger">*</span>
                        </label>

                        <select name="term_id"
                                id="term"
                                class="form-select @error('term_id') is-invalid @enderror"
                                required>
                            <option value="">Select Term</option>

                            @foreach($terms as $termOption)
                                <option value="{{ $termOption->id }}"
                                    {{ old('term_id', $billSheet->term_id) == $termOption->id ? 'selected' : '' }}>
                                    {{ $termOption->name }}
                                </option>
                            @endforeach
                        </select>

                        @error('term_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- GENERATED DATE --}}
                    <div class="col-lg-4">
                        <label for="generated_date" class="form-label fw-semibold">
                            Generated Date <span class="text-danger">*</span>
                        </label>

                        <input type="date"
                               name="generated_date"
                               id="generated_date"
                               class="form-control @error('generated_date') is-invalid @enderror"
                               value="{{ old('generated_date', optional($billSheet->generated_date)->format('Y-m-d')) }}"
                               required>

                        @error('generated_date')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- DUE DATE --}}
                    <div class="col-lg-4">
                        <label for="due_date" class="form-label fw-semibold">
                            Due Date
                        </label>

                        <input type="date"
                               name="due_date"
                               id="due_date"
                               class="form-control @error('due_date') is-invalid @enderror"
                               value="{{ old('due_date', optional($billSheet->due_date)->format('Y-m-d')) }}">

                        @error('due_date')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- STATUS --}}
                    <div class="col-lg-4">
                        <label class="form-label fw-semibold">Status</label>

                        <div class="form-control bg-light">
                            <span class="badge
                                @if($billSheet->status === 'draft') bg-secondary
                                @elseif($billSheet->status === 'pending') bg-warning text-dark
                                @elseif($billSheet->status === 'approved') bg-success
                                @elseif($billSheet->status === 'published') bg-info
                                @elseif($billSheet->status === 'rejected') bg-danger
                                @else bg-dark
                                @endif">
                                {{ ucfirst($billSheet->status) }}
                            </span>
                        </div>

                        {{-- Preserve current status --}}
                        <input type="hidden" name="status" value="{{ $billSheet->status }}">
                    </div>

                    {{-- DESCRIPTION --}}
                    <div class="col-12">
                        <label for="description" class="form-label fw-semibold">
                            Description
                        </label>

                        <textarea name="description"
                                  id="description"
                                  rows="3"
                                  class="form-control @error('description') is-invalid @enderror"
                                  placeholder="Optional description">{{ old('description', $billSheet->description) }}</textarea>

                        @error('description')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- ACTIVE --}}
                    <div class="col-12">
                        <div class="form-check form-switch">
                            <input type="hidden" name="is_active" value="0">

                            <input type="checkbox"
                                   name="is_active"
                                   id="isActive"
                                   value="1"
                                   class="form-check-input"
                                   {{ old('is_active', $billSheet->is_active) ? 'checked' : '' }}>

                            <label for="isActive" class="form-check-label fw-semibold">
                                Active BillSheet
                            </label>
                        </div>
                    </div>

                </div>
            </div>
        </div>

        {{-- =========================================================
             BILL ITEMS
        ========================================================== --}}
        <div class="card border-0 shadow-sm mb-4">

            <div class="card-header bg-white border-bottom py-3">
                <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">

                    <div>
                        <h6 class="mb-1 fw-bold">
                            <i class="fas fa-list-ul text-primary me-2"></i>
                            Bill Sheet Items
                            <span class="badge bg-primary ms-2" id="itemCount">
                                {{ $billSheet->items->count() }}
                            </span>
                        </h6>

                        <small class="text-muted">
                            These are the charges assigned to this student for this BillSheet.
                        </small>
                    </div>

                    <div class="d-flex gap-2">
                        <button type="button"
                                class="btn btn-outline-danger btn-sm"
                                id="clearItemsBtn">
                            <i class="fas fa-trash-alt me-1"></i>
                            Clear All
                        </button>

                        <button type="button"
                                class="btn btn-primary btn-sm"
                                id="addItemBtn">
                            <i class="fas fa-plus me-1"></i>
                            Add Item
                        </button>
                    </div>

                </div>
            </div>

            <div class="card-body">

                <div class="alert alert-info small">
                    <i class="fas fa-info-circle me-1"></i>
                    If this BillSheet is being used as the template for
                    <strong>Regenerate Class Bills</strong>, make sure the items and amounts are correct
                    before regenerating the class.
                </div>

                <div class="table-responsive">
                    <table class="table table-bordered table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th style="min-width:220px;">Item Name</th>
                                <th style="min-width:180px;">Fee Category</th>
                                <th style="min-width:150px;">Amount</th>
                                <th style="min-width:100px;">Qty</th>
                                <th style="min-width:150px;">Total</th>
                                <th style="width:90px;" class="text-center">Optional</th>
                                <th style="width:80px;" class="text-center">Action</th>
                            </tr>
                        </thead>

                        <tbody id="itemsContainer">

                            @forelse($billSheet->items as $index => $item)

                                <tr class="bill-item-row" data-index="{{ $index }}">

                                    <td>
                                        <input type="hidden"
                                               name="items[{{ $index }}][id]"
                                               value="{{ $item->id }}">

                                        <input type="hidden"
                                               name="items[{{ $index }}][fee_structure_id]"
                                               value="{{ $item->fee_structure_id ?? '' }}">

                                        <input type="text"
                                               name="items[{{ $index }}][name]"
                                               class="form-control form-control-sm item-name"
                                               value="{{ old("items.$index.name", $item->name) }}"
                                               placeholder="Item name"
                                               required>
                                    </td>

                                    <td>
                                        <select name="items[{{ $index }}][fee_category_id]"
                                                class="form-select form-select-sm">
                                            <option value="">Select Category</option>

                                            @foreach($feeCategories as $category)
                                                <option value="{{ $category->id }}"
                                                    {{ old("items.$index.fee_category_id", $item->fee_category_id) == $category->id ? 'selected' : '' }}>
                                                    {{ $category->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </td>

                                    <td>
                                        <div class="input-group input-group-sm">
                                            <span class="input-group-text">GHS</span>

                                            <input type="number"
                                                   name="items[{{ $index }}][amount]"
                                                   class="form-control item-amount"
                                                   value="{{ old("items.$index.amount", $item->amount) }}"
                                                   min="0"
                                                   step="0.01"
                                                   required>
                                        </div>
                                    </td>

                                    <td>
                                        <input type="number"
                                               name="items[{{ $index }}][quantity]"
                                               class="form-control form-control-sm item-quantity"
                                               value="{{ old("items.$index.quantity", $item->quantity ?: 1) }}"
                                               min="1"
                                               step="1"
                                               required>
                                    </td>

                                    <td>
                                        <span class="item-total fw-semibold">
                                            GHS {{ number_format((float)$item->total_amount, 2) }}
                                        </span>

                                        <input type="hidden"
                                               name="items[{{ $index }}][total_amount]"
                                               class="item-total-input"
                                               value="{{ number_format((float)$item->total_amount, 2, '.', '') }}">
                                    </td>

                                    <td class="text-center">
                                        <input type="hidden"
                                               name="items[{{ $index }}][is_optional]"
                                               value="0">

                                        <div class="form-check d-flex justify-content-center">
                                            <input type="checkbox"
                                                   name="items[{{ $index }}][is_optional]"
                                                   value="1"
                                                   class="form-check-input item-optional"
                                                   {{ old("items.$index.is_optional", $item->is_optional) ? 'checked' : '' }}>
                                        </div>
                                    </td>

                                    <td class="text-center">
                                        <button type="button"
                                                class="btn btn-outline-danger btn-sm remove-item"
                                                title="Remove item">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </td>

                                </tr>

                            @empty

                                <tr id="emptyItemsRow">
                                    <td colspan="7" class="text-center text-muted py-4">
                                        <i class="fas fa-receipt fa-2x mb-2 d-block"></i>
                                        No bill items have been added.
                                    </td>
                                </tr>

                            @endforelse

                        </tbody>

                        <tfoot>

                            <tr class="table-light">
                                <td colspan="4" class="text-end fw-bold">
                                    Sub Total
                                </td>

                                <td colspan="3" class="fw-bold text-primary" id="subTotal">
                                    GHS {{ number_format((float)$billSheet->total_amount, 2) }}
                                </td>
                            </tr>

                            <tr>
                                <td colspan="4" class="text-end fw-bold">
                                    Discount
                                </td>

                                <td colspan="3">
                                    <div class="input-group input-group-sm">
                                        <span class="input-group-text">GHS</span>

                                        <input type="number"
                                               name="discount_amount"
                                               id="discountAmount"
                                               class="form-control"
                                               min="0"
                                               step="0.01"
                                               value="{{ old('discount_amount', $billSheet->discount_amount ?? 0) }}">
                                    </div>
                                </td>
                            </tr>

                            <tr>
                                <td colspan="4" class="text-end fw-bold">
                                    Tax
                                </td>

                                <td colspan="3">
                                    <div class="input-group input-group-sm">
                                        <span class="input-group-text">GHS</span>

                                        <input type="number"
                                               name="tax_amount"
                                               id="taxAmount"
                                               class="form-control"
                                               min="0"
                                               step="0.01"
                                               value="{{ old('tax_amount', $billSheet->tax_amount ?? 0) }}">
                                    </div>
                                </td>
                            </tr>

                            <tr class="table-success">
                                <td colspan="4" class="text-end fw-bold fs-5">
                                    Grand Total
                                </td>

                                <td colspan="3" class="fw-bold fs-5 text-success" id="grandTotal">
                                    GHS {{ number_format((float)$billSheet->net_amount, 2) }}
                                </td>
                            </tr>

                        </tfoot>
                    </table>
                </div>

                <input type="hidden"
                       name="total_amount"
                       id="totalAmount"
                       value="{{ number_format((float)$billSheet->total_amount, 2, '.', '') }}">

                <input type="hidden"
                       name="net_amount"
                       id="netAmount"
                       value="{{ number_format((float)$billSheet->net_amount, 2, '.', '') }}">

            </div>
        </div>

        {{-- =========================================================
             REGENERATION INFORMATION
        ========================================================== --}}
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-body">

                <div class="d-flex align-items-start gap-3">
                    <div class="text-warning fs-4">
                        <i class="fas fa-sync-alt"></i>
                    </div>

                    <div>
                        <h6 class="fw-bold mb-1">Class Regeneration</h6>

                        <p class="text-muted mb-2 small">
                            After correcting this BillSheet, you can use
                            <strong>Regenerate Class Bills</strong> from the BillSheet index.
                            This BillSheet can serve as the corrected template for eligible draft
                            BillSheets in the same class, academic year and term.
                        </p>

                        <div class="small">
                            <span class="badge bg-light text-dark border me-1">
                                Class: {{ $studentClass?->name ?? 'N/A' }}
                            </span>

                            <span class="badge bg-light text-dark border me-1">
                                Academic Year: {{ $academicYear?->name ?? 'N/A' }}
                            </span>

                            <span class="badge bg-light text-dark border">
                                Term: {{ $term?->name ?? 'N/A' }}
                            </span>
                        </div>
                    </div>
                </div>

            </div>
        </div>

        {{-- ACTIONS --}}
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-4">

            <a href="{{ route('bill-sheets.show', $billSheet->id) }}"
               class="btn btn-outline-secondary">
                <i class="fas fa-times me-1"></i>
                Cancel
            </a>

            <button type="submit"
                    class="btn btn-warning px-4"
                    id="submitBtn">
                <i class="fas fa-save me-1"></i>
                Update Bill Sheet
            </button>

        </div>

    </form>
</div>
@endsection

@push('styles')
<style>
    #itemsContainer .form-control:focus,
    #itemsContainer .form-select:focus {
        box-shadow: 0 0 0 .15rem rgba(13, 110, 253, .10);
    }

    .bill-item-row td {
        vertical-align: middle;
    }

    .item-total {
        white-space: nowrap;
    }

    .bg-light.form-control {
        min-height: 38px;
        display: flex;
        align-items: center;
    }
</style>
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {

    const form = document.getElementById('billSheetForm');
    const itemsContainer = document.getElementById('itemsContainer');
    const addItemBtn = document.getElementById('addItemBtn');
    const clearItemsBtn = document.getElementById('clearItemsBtn');
    const submitBtn = document.getElementById('submitBtn');

    let nextIndex = {{ $billSheet->items->count() }};

    const feeCategories = @json($feeCategories ?? []);

    function money(value) {
        return Number(value || 0).toFixed(2);
    }

    function escapeHtml(value) {
        const div = document.createElement('div');
        div.textContent = value ?? '';
        return div.innerHTML;
    }

    function getRows() {
        return itemsContainer.querySelectorAll('.bill-item-row');
    }

    function updateItemCount() {
        const count = getRows().length;
        document.getElementById('itemCount').textContent = count;
    }

    function removeEmptyRow() {
        const emptyRow = document.getElementById('emptyItemsRow');

        if (emptyRow) {
            emptyRow.remove();
        }
    }

    function showEmptyRowIfNecessary() {
        if (getRows().length === 0) {
            itemsContainer.innerHTML = `
                <tr id="emptyItemsRow">
                    <td colspan="7" class="text-center text-muted py-4">
                        <i class="fas fa-receipt fa-2x mb-2 d-block"></i>
                        No bill items have been added.
                    </td>
                </tr>
            `;
        }
    }

    function calculateItemTotal(row) {
        const amount = parseFloat(
            row.querySelector('.item-amount')?.value
        ) || 0;

        const quantity = parseInt(
            row.querySelector('.item-quantity')?.value
        ) || 1;

        const total = amount * quantity;

        const display = row.querySelector('.item-total');
        const hidden = row.querySelector('.item-total-input');

        if (display) {
            display.textContent = `GHS ${money(total)}`;
        }

        if (hidden) {
            hidden.value = money(total);
        }

        return total;
    }

    function calculateGrandTotal() {

        let subtotal = 0;

        getRows().forEach(row => {
            subtotal += calculateItemTotal(row);
        });

        const discount =
            parseFloat(document.getElementById('discountAmount')?.value) || 0;

        const tax =
            parseFloat(document.getElementById('taxAmount')?.value) || 0;

        const net = Math.max(0, subtotal - discount + tax);

        document.getElementById('subTotal').textContent =
            `GHS ${money(subtotal)}`;

        document.getElementById('grandTotal').textContent =
            `GHS ${money(net)}`;

        document.getElementById('totalAmount').value =
            money(subtotal);

        document.getElementById('netAmount').value =
            money(net);
    }

    function buildCategoryOptions() {

        let options = '<option value="">Select Category</option>';

        feeCategories.forEach(category => {
            options += `
                <option value="${category.id}">
                    ${escapeHtml(category.name)}
                </option>
            `;
        });

        return options;
    }

    function addItem() {

        removeEmptyRow();

        const index = nextIndex++;

        const row = document.createElement('tr');

        row.className = 'bill-item-row';
        row.dataset.index = index;

        row.innerHTML = `
            <td>

                <input type="hidden"
                       name="items[${index}][id]"
                       value="">

                <input type="hidden"
                       name="items[${index}][fee_structure_id]"
                       value="">

                <input type="text"
                       name="items[${index}][name]"
                       class="form-control form-control-sm item-name"
                       placeholder="Item name"
                       required>

            </td>

            <td>

                <select name="items[${index}][fee_category_id]"
                        class="form-select form-select-sm">

                    ${buildCategoryOptions()}

                </select>

            </td>

            <td>

                <div class="input-group input-group-sm">

                    <span class="input-group-text">GHS</span>

                    <input type="number"
                           name="items[${index}][amount]"
                           class="form-control item-amount"
                           value="0.00"
                           min="0"
                           step="0.01"
                           required>

                </div>

            </td>

            <td>

                <input type="number"
                       name="items[${index}][quantity]"
                       class="form-control form-control-sm item-quantity"
                       value="1"
                       min="1"
                       step="1"
                       required>

            </td>

            <td>

                <span class="item-total fw-semibold">
                    GHS 0.00
                </span>

                <input type="hidden"
                       name="items[${index}][total_amount]"
                       class="item-total-input"
                       value="0.00">

            </td>

            <td class="text-center">

                <input type="hidden"
                       name="items[${index}][is_optional]"
                       value="0">

                <div class="form-check d-flex justify-content-center">

                    <input type="checkbox"
                           name="items[${index}][is_optional]"
                           value="1"
                           class="form-check-input item-optional">

                </div>

            </td>

            <td class="text-center">

                <button type="button"
                        class="btn btn-outline-danger btn-sm remove-item"
                        title="Remove item">

                    <i class="fas fa-trash"></i>

                </button>

            </td>
        `;

        itemsContainer.appendChild(row);

        updateItemCount();
        calculateGrandTotal();

        row.querySelector('.item-name')?.focus();
    }

    function clearItems() {

        const count = getRows().length;

        if (count === 0) {
            return;
        }

        if (!confirm(
            'Are you sure you want to remove all bill items? This will set the bill total to zero until you add new items.'
        )) {
            return;
        }

        itemsContainer.innerHTML = '';

        showEmptyRowIfNecessary();

        updateItemCount();
        calculateGrandTotal();
    }

    addItemBtn.addEventListener('click', addItem);

    clearItemsBtn.addEventListener('click', clearItems);

    itemsContainer.addEventListener('click', function (event) {

        const button = event.target.closest('.remove-item');

        if (!button) {
            return;
        }

        const row = button.closest('.bill-item-row');

        if (!row) {
            return;
        }

        if (!confirm('Remove this bill item?')) {
            return;
        }

        row.remove();

        showEmptyRowIfNecessary();
        updateItemCount();
        calculateGrandTotal();
    });

    itemsContainer.addEventListener('input', function (event) {

        if (
            event.target.classList.contains('item-amount') ||
            event.target.classList.contains('item-quantity')
        ) {
            const row = event.target.closest('.bill-item-row');

            if (row) {
                calculateItemTotal(row);
                calculateGrandTotal();
            }
        }

    });

    document.getElementById('discountAmount')
        ?.addEventListener('input', calculateGrandTotal);

    document.getElementById('taxAmount')
        ?.addEventListener('input', calculateGrandTotal);

    form.addEventListener('submit', function (event) {

        calculateGrandTotal();

        const rows = getRows();

        if (rows.length === 0) {

            event.preventDefault();

            alert('Please add at least one bill item before updating the BillSheet.');

            return;
        }

        let valid = true;

        rows.forEach(row => {

            const name = row.querySelector('.item-name');
            const amount = row.querySelector('.item-amount');
            const quantity = row.querySelector('.item-quantity');

            if (!name?.value.trim()) {
                name?.classList.add('is-invalid');
                valid = false;
            } else {
                name?.classList.remove('is-invalid');
            }

            if ((parseFloat(amount?.value) || 0) < 0) {
                amount?.classList.add('is-invalid');
                valid = false;
            } else {
                amount?.classList.remove('is-invalid');
            }

            if ((parseInt(quantity?.value) || 0) < 1) {
                quantity?.classList.add('is-invalid');
                valid = false;
            } else {
                quantity?.classList.remove('is-invalid');
            }
        });

        if (!valid) {
            event.preventDefault();

            alert('Please correct the highlighted bill items.');

            return;
        }

        submitBtn.disabled = true;

        submitBtn.innerHTML = `
            <span class="spinner-border spinner-border-sm me-1"></span>
            Updating...
        `;
    });

    // Initial calculation
    calculateGrandTotal();

});
</script>
@endpush
