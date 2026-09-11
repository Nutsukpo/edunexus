@extends('layouts.master')

@section('title', 'Allocate Student Fees')

@section('content')

<div class="container-fluid py-4">

    {{-- Page Header --}}
    <div class="d-flex justify-content-between align-items-center mb-4">

        <div>
            <h4 class="fw-bold mb-1">
                <i class="fas fa-file-invoice-dollar text-primary me-2"></i>
                Allocate Student Fees
            </h4>

            <p class="text-muted mb-0">
                Build and manage the student's individual fee account.
            </p>
        </div>

        <a href="{{ route('student-fee-allocations.index') }}"
           class="btn btn-outline-secondary">

            <i class="fas fa-arrow-left me-1"></i>
            Back

        </a>

    </div>


    {{-- Flash Messages --}}
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show">
            <i class="fas fa-check-circle me-2"></i>
            {{ session('success') }}

            <button type="button"
                    class="btn-close"
                    data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show">
            <i class="fas fa-exclamation-circle me-2"></i>
            {{ session('error') }}

            <button type="button"
                    class="btn-close"
                    data-bs-dismiss="alert"></button>
        </div>
    @endif


    {{-- Validation Errors --}}
    @if($errors->any())

        <div class="alert alert-danger">

            <div class="fw-semibold mb-2">
                <i class="fas fa-exclamation-triangle me-2"></i>
                Please correct the following:
            </div>

            <ul class="mb-0">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>

        </div>

    @endif


    {{-- Student Search --}}
    <div class="card border-0 shadow-sm mb-4">

        <div class="card-body">

            <form method="GET"
                  action="{{ route('student-fee-allocations.create') }}">

                <label class="form-label fw-semibold">
                    Select Student
                </label>

                <div class="row g-2">

                    <div class="col-md-10">

                        <input type="text"
                               id="studentSearch"
                               class="form-control"
                               placeholder="Enter student ID or name"
                               autocomplete="off"
                               value="{{ $student ? trim($student->first_name . ' ' . ($student->middle_name ?? '') . ' ' . $student->last_name) . ' (' . $student->student_id . ')' : '' }}">

                        <input type="hidden"
                               name="student_id"
                               id="studentId"
                               value="{{ $student->id ?? '' }}">

                        <div id="studentResults"
                             class="list-group mt-2"></div>

                    </div>

                    <div class="col-md-2">

                        <button type="submit"
                                class="btn btn-primary w-100">

                            <i class="fas fa-search me-1"></i>
                            Load

                        </button>

                    </div>

                </div>

            </form>

        </div>

    </div>


    {{-- Student Allocation --}}
    @if($student && $feeAccount)

        @php

            $studentName = trim(
                $student->first_name . ' ' .
                ($student->middle_name ?? '') . ' ' .
                $student->last_name
            );

            $accountTotal = (float) ($feeAccount->total_fees ?? 0);
            $accountBalance = (float) ($feeAccount->balance ?? 0);

        @endphp


        {{-- Student Summary --}}
        <div class="card border-0 shadow-sm mb-4">

            <div class="card-body">

                <div class="row align-items-center">

                    <div class="col-md-8">

                        <h5 class="fw-bold mb-1">
                            {{ $studentName }}
                        </h5>

                        <div class="text-muted mb-1">
                            Student ID:
                            <strong>
                                {{ $student->student_id }}
                            </strong>
                        </div>

                        <div class="text-muted mb-1">
                            Class:
                            <strong>
                                {{ $assignment->studentClass->name ?? '—' }}
                            </strong>
                        </div>

                        <div class="text-muted">
                            Academic Year:
                            <strong>
                                {{ $assignment->academicYear->name ?? '—' }}
                            </strong>
                        </div>

                    </div>


                    <div class="col-md-4 text-md-end mt-3 mt-md-0">

                        <small class="text-muted d-block">
                            Current Account Total
                        </small>

                        <h3 class="fw-bold text-primary mb-1">

                            GH₵
                            {{ number_format($accountTotal, 2) }}

                        </h3>

                        <small class="text-danger">

                            Current Balance:
                            GH₵
                            {{ number_format($accountBalance, 2) }}

                        </small>

                    </div>

                </div>

            </div>

        </div>


        {{-- Allocation Form --}}
        <form method="POST"
              action="{{ route('student-fee-allocations.store') }}"
              id="allocationForm">

            @csrf


            <input type="hidden"
                   name="student_id"
                   value="{{ $student->id }}">

            <input type="hidden"
                   name="student_fee_account_id"
                   value="{{ $feeAccount->id }}">


            {{-- Standard Class Fees --}}
            <div class="card border-0 shadow-sm mb-4">

                <div class="card-header bg-white border-0 py-3">

                    <h6 class="fw-bold mb-1">
                        <i class="fas fa-list-check text-primary me-2"></i>
                        Standard Class Fees
                    </h6>

                    <small class="text-muted">
                        Select the class fees that apply to this student.
                    </small>

                </div>


                <div class="table-responsive">

                    <table class="table align-middle mb-0">

                        <thead class="table-light">

                            <tr>

                                <th width="60">
                                    Select
                                </th>

                                <th>
                                    Fee
                                </th>

                                <th>
                                    Type
                                </th>

                                <th>
                                    Required
                                </th>

                                <th class="text-end">
                                    Amount
                                </th>

                            </tr>

                        </thead>


                        <tbody>

                        @forelse($classFeeStructures as $structure)

                            @php

                                $existing = $feeItems->firstWhere(
                                    'class_fee_structure_id',
                                    $structure->id
                                );

                            @endphp


                            <tr>

                                <td>

                                    <input type="checkbox"
                                           class="form-check-input fee-checkbox"
                                           name="items[{{ $structure->id }}][class_fee_structure_id]"
                                           value="{{ $structure->id }}"
                                           data-amount="{{ (float) $structure->amount }}"
                                           @checked($existing)>

                                </td>


                                <td>

                                    <div class="fw-semibold">
                                        {{ $structure->fee_name }}
                                    </div>

                                    @if($structure->description)

                                        <small class="text-muted">
                                            {{ $structure->description }}
                                        </small>

                                    @endif

                                </td>


                                <td>

                                    {{ ucfirst(
                                        str_replace(
                                            '_',
                                            ' ',
                                            $structure->fee_type
                                        )
                                    ) }}

                                </td>


                                <td>

                                    @if($structure->is_required)

                                        <span class="badge bg-danger">
                                            Required
                                        </span>

                                    @else

                                        <span class="badge bg-light text-dark">
                                            Optional
                                        </span>

                                    @endif

                                </td>


                                <td class="fw-semibold text-end">

                                    GH₵
                                    {{ number_format(
                                        $structure->amount,
                                        2
                                    ) }}

                                </td>

                            </tr>

                        @empty

                            <tr>

                                <td colspan="5"
                                    class="text-center py-5 text-muted">

                                    <i class="fas fa-inbox fa-2x mb-2 d-block"></i>

                                    No active class fee structures are available
                                    for this student's class and academic year.

                                </td>

                            </tr>

                        @endforelse

                        </tbody>

                    </table>

                </div>

            </div>


            {{-- Custom Fees --}}
            <div class="card border-0 shadow-sm mb-4">

                <div class="card-header bg-white border-0 py-3">

                    <div class="d-flex justify-content-between align-items-center">

                        <div>

                            <h6 class="fw-bold mb-1">

                                <i class="fas fa-plus-circle text-primary me-2"></i>
                                Custom Fees / Adjustments

                            </h6>

                            <small class="text-muted">
                                Add student-specific charges where necessary.
                            </small>

                        </div>


                        <button type="button"
                                class="btn btn-sm btn-outline-primary"
                                id="addCustomFee">

                            <i class="fas fa-plus me-1"></i>
                            Add Item

                        </button>

                    </div>

                </div>


                <div class="card-body">

                    <div id="customFees"></div>


                    <div id="noCustomFees"
                         class="text-center text-muted py-3">

                        <i class="fas fa-receipt fa-lg d-block mb-2"></i>

                        No custom items added.

                    </div>

                </div>

            </div>


            {{-- Allocation Summary --}}
            <div class="card border-0 shadow-sm mb-4">

                <div class="card-header bg-white border-0">

                    <h6 class="fw-bold mb-0">
                        Allocation Summary
                    </h6>

                </div>


                <div class="card-body">

                    <div class="row g-4">

                        <div class="col-md-4">

                            <small class="text-muted d-block">
                                Current Account Total
                            </small>

                            <h5 class="fw-bold mb-0">

                                GH₵
                                <span id="currentAccountTotal">
                                    {{ number_format($accountTotal, 2) }}
                                </span>

                            </h5>

                        </div>


                        <div class="col-md-4">

                            <small class="text-muted d-block">
                                New Allocation Total
                            </small>

                            <h5 class="fw-bold text-primary mb-0">

                                GH₵
                                <span id="allocationTotal">
                                    0.00
                                </span>

                            </h5>

                        </div>


                        <div class="col-md-4">

                            <small class="text-muted d-block">
                                Change
                            </small>

                            <h5 class="fw-bold mb-0"
                                id="difference">

                                GH₵ 0.00

                            </h5>

                        </div>

                    </div>


                    <div id="allocationInfo"
                         class="alert alert-info mt-4 mb-0">

                        <i class="fas fa-info-circle me-2"></i>

                        The new allocation will replace the student's
                        unpaid fee items. Completed payments will be preserved.

                    </div>

                </div>

            </div>


            {{-- Form Actions --}}
            <div class="d-flex justify-content-end gap-2">

                <a href="{{ route('student-fee-allocations.index') }}"
                   class="btn btn-light">

                    Cancel

                </a>


                <button type="submit"
                        class="btn btn-primary"
                        id="saveAllocation">

                    <i class="fas fa-save me-1"></i>

                    Save Allocation

                </button>

            </div>

        </form>

    @elseif($student)

        <div class="alert alert-warning">

            <i class="fas fa-exclamation-triangle me-2"></i>

            A fee account could not be loaded for this student.

        </div>

    @else

        <div class="card border-0 shadow-sm">

            <div class="card-body text-center py-5">

                <i class="fas fa-user-graduate fa-3x text-muted mb-3"></i>

                <h5 class="fw-bold">
                    Select a Student
                </h5>

                <p class="text-muted mb-0">
                    Search for a student above to load their fee account.
                </p>

            </div>

        </div>

    @endif

</div>

@endsection


@push('scripts')

<script>

document.addEventListener('DOMContentLoaded', function () {

    /*
     * ---------------------------------------------------------
     * STUDENT SEARCH
     * ---------------------------------------------------------
     */

    const searchInput =
        document.getElementById('studentSearch');

    const studentIdInput =
        document.getElementById('studentId');

    const studentResults =
        document.getElementById('studentResults');


    if (searchInput && studentIdInput && studentResults) {

        let searchTimer = null;


        searchInput.addEventListener('input', function () {

            clearTimeout(searchTimer);

            const query =
                this.value.trim();


            if (query.length < 2) {

                studentResults.innerHTML = '';

                return;
            }


            searchTimer = setTimeout(function () {

                fetch(
                    `{{ route('student-fee-allocations.students') }}?q=${encodeURIComponent(query)}`,
                    {
                        headers: {
                            'Accept': 'application/json'
                        }
                    }
                )
                .then(function (response) {

                    if (!response.ok) {
                        throw new Error(
                            'Unable to search students.'
                        );
                    }

                    return response.json();

                })
                .then(function (data) {

                    studentResults.innerHTML = '';


                    if (!data.length) {

                        studentResults.innerHTML = `
                            <div class="list-group-item text-muted">
                                No students found.
                            </div>
                        `;

                        return;
                    }


                    data.forEach(function (student) {

                        const button =
                            document.createElement('button');


                        button.type = 'button';

                        button.className =
                            'list-group-item list-group-item-action';


                        const name =
                            document.createElement('strong');

                        name.textContent =
                            student.name;


                        const id =
                            document.createElement('small');

                        id.className =
                            'text-muted d-block';

                        id.textContent =
                            student.student_id;


                        button.appendChild(name);

                        button.appendChild(id);


                        button.addEventListener(
                            'click',
                            function () {

                                searchInput.value =
                                    `${student.name} (${student.student_id})`;

                                studentIdInput.value =
                                    student.id;

                                studentResults.innerHTML =
                                    '';

                            }
                        );


                        studentResults.appendChild(button);

                    });

                })
                .catch(function () {

                    studentResults.innerHTML = `
                        <div class="list-group-item text-danger">
                            Unable to search students.
                        </div>
                    `;

                });

            }, 300);

        });

    }


    /*
     * ---------------------------------------------------------
     * ALLOCATION CALCULATION
     * ---------------------------------------------------------
     */

    const allocationTotalElement =
        document.getElementById('allocationTotal');

    const differenceElement =
        document.getElementById('difference');

    const currentAccountTotal =
        {{ isset($accountTotal) ? (float) $accountTotal : 0 }};


    function updateTotals() {

        let total = 0;


        /*
         * Standard fees.
         */
        document
            .querySelectorAll('.fee-checkbox:checked')
            .forEach(function (checkbox) {

                const amount =
                    parseFloat(
                        checkbox.dataset.amount || 0
                    );

                if (!isNaN(amount)) {
                    total += amount;
                }

            });


        /*
         * Custom fees.
         */
        document
            .querySelectorAll('.custom-amount')
            .forEach(function (input) {

                const amount =
                    parseFloat(input.value || 0);

                if (!isNaN(amount)) {
                    total += amount;
                }

            });


        total =
            Math.round(total * 100) / 100;


        const difference =
            Math.round(
                (total - currentAccountTotal) * 100
            ) / 100;


        if (allocationTotalElement) {

            allocationTotalElement.textContent =
                total.toFixed(2);

        }


        if (differenceElement) {

            const prefix =
                difference > 0
                    ? '+'
                    : '';

            differenceElement.textContent =
                'GH₵ ' +
                prefix +
                difference.toFixed(2);

        }

    }


    /*
     * Standard fee checkbox events.
     */
    document
        .querySelectorAll('.fee-checkbox')
        .forEach(function (checkbox) {

            checkbox.addEventListener(
                'change',
                updateTotals
            );

        });


    /*
     * ---------------------------------------------------------
     * CUSTOM FEES
     * ---------------------------------------------------------
     */

    const customFeesContainer =
        document.getElementById('customFees');

    const noCustomFees =
        document.getElementById('noCustomFees');

    const addCustomFeeButton =
        document.getElementById('addCustomFee');


    let customIndex = 0;


    if (addCustomFeeButton) {

        addCustomFeeButton.addEventListener(
            'click',
            function () {

                if (noCustomFees) {
                    noCustomFees.classList.add('d-none');
                }


                const row =
                    document.createElement('div');


                row.className =
                    'border rounded p-3 mb-3 custom-fee-row';


                row.innerHTML = `

                    <div class="row g-3">

                        <div class="col-md-3">

                            <label class="form-label">
                                Fee Name
                            </label>

                            <input type="text"
                                   name="custom_items[${customIndex}][fee_name]"
                                   class="form-control"
                                   placeholder="e.g. PTA Levy"
                                   required>

                        </div>


                        <div class="col-md-2">

                            <label class="form-label">
                                Fee Type
                            </label>

                            <input type="text"
                                   name="custom_items[${customIndex}][fee_type]"
                                   class="form-control"
                                   value="adjustment"
                                   required>

                        </div>


                        <div class="col-md-2">

                            <label class="form-label">
                                Amount
                            </label>

                            <input type="number"
                                   name="custom_items[${customIndex}][amount]"
                                   class="form-control custom-amount"
                                   min="0.01"
                                   step="0.01"
                                   placeholder="0.00"
                                   required>

                        </div>


                        <div class="col-md-2">

                            <label class="form-label">
                                Due Date
                            </label>

                            <input type="date"
                                   name="custom_items[${customIndex}][due_date]"
                                   class="form-control">

                        </div>


                        <div class="col-md-2">

                            <label class="form-label">
                                Description
                            </label>

                            <input type="text"
                                   name="custom_items[${customIndex}][description]"
                                   class="form-control"
                                   placeholder="Optional">

                        </div>


                        <div class="col-md-1 d-flex align-items-end">

                            <button type="button"
                                    class="btn btn-outline-danger remove-custom w-100">

                                <i class="fas fa-trash"></i>

                            </button>

                        </div>

                    </div>

                `;


                if (customFeesContainer) {

                    customFeesContainer.appendChild(row);

                }


                const amountInput =
                    row.querySelector('.custom-amount');


                if (amountInput) {

                    amountInput.addEventListener(
                        'input',
                        updateTotals
                    );

                }


                const removeButton =
                    row.querySelector('.remove-custom');


                if (removeButton) {

                    removeButton.addEventListener(
                        'click',
                        function () {

                            row.remove();


                            if (
                                customFeesContainer &&
                                customFeesContainer.children.length === 0
                            ) {

                                if (noCustomFees) {
                                    noCustomFees.classList.remove(
                                        'd-none'
                                    );
                                }

                            }


                            updateTotals();

                        }
                    );

                }


                customIndex++;

                updateTotals();

            }
        );

    }


    /*
     * Initial calculation.
     */
    updateTotals();

});

</script>

@endpush
