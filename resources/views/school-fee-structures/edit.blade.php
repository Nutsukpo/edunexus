@extends('layouts.master')

@section('title', 'Edit Fee Structure')

@section('content')
<div class="container-fluid">

    {{-- HEADER --}}
    <div class="d-flex justify-content-between align-items-center mb-4 mt-3">
        <div>
            <h3 class="fw-bold mb-0">Edit Fee Structure</h3>
            <small class="text-muted">Update fee structure details</small>
        </div>

        <a href="{{ route('school-fee-structures.index') }}"
           class="btn btn-secondary">
            <i class="fas fa-arrow-left me-1"></i>
            Back
        </a>
    </div>

    <div class="card border-0 shadow-sm">

        <div class="card-body">

            <form method="POST"
                  action="{{ route('school-fee-structures.update', $schoolFeeStructure->id) }}">
                @csrf
                @method('PUT')

                <div class="row">

                    {{-- NAME --}}
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-semibold">Name</label>
                        <input type="text"
                               name="name"
                               value="{{ old('name', $schoolFeeStructure->name) }}"
                               class="form-control"
                               required>
                    </div>

                    {{-- CODE --}}
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-semibold">Code</label>
                        <input type="text"
                               name="code"
                               value="{{ old('code', $schoolFeeStructure->code) }}"
                               class="form-control"
                               required>
                    </div>

                    {{-- ACADEMIC YEAR --}}
                    <div class="col-md-3 mb-3">
                        <label class="form-label fw-semibold">Academic Year</label>
                        <select name="academic_year_id" class="form-control">
                            @foreach($academicYears as $year)
                                <option value="{{ $year->id }}"
                                    {{ $schoolFeeStructure->academic_year_id == $year->id ? 'selected' : '' }}>
                                    {{ $year->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- TERM --}}
                    <div class="col-md-3 mb-3">
                        <label class="form-label fw-semibold">Term</label>
                        <select name="term_id" class="form-control">
                            @foreach($terms as $term)
                                <option value="{{ $term->id }}"
                                    {{ $schoolFeeStructure->term_id == $term->id ? 'selected' : '' }}>
                                    {{ $term->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- CLASS --}}
                    <div class="col-md-3 mb-3">
                        <label class="form-label fw-semibold">Class</label>
                        <select name="student_class_id" class="form-control">
                            <option value="">All Classes</option>
                            @foreach($classes as $class)
                                <option value="{{ $class->id }}"
                                    {{ $schoolFeeStructure->student_class_id == $class->id ? 'selected' : '' }}>
                                    {{ $class->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- CATEGORY --}}
                    <div class="col-md-3 mb-3">
                        <label class="form-label fw-semibold">Fee Category</label>
                        <select name="fee_category_id" class="form-control">
                            @foreach($categories as $cat)
                                <option value="{{ $cat->id }}"
                                    {{ $schoolFeeStructure->fee_category_id == $cat->id ? 'selected' : '' }}>
                                    {{ $cat->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- AMOUNT --}}
                    <div class="col-md-4 mb-3">
                        <label class="form-label fw-semibold">Amount</label>
                        <input type="number"
                               step="0.01"
                               name="amount"
                               value="{{ old('amount', $schoolFeeStructure->amount) }}"
                               class="form-control"
                               required>
                    </div>

                    {{-- FEE TYPE --}}
                    <div class="col-md-4 mb-3">
                        <label class="form-label fw-semibold">Fee Type</label>
                        <select name="fee_type" class="form-control">
                            @foreach(['tuition','registration','exam','library','sports','transport','other'] as $type)
                                <option value="{{ $type }}"
                                    {{ $schoolFeeStructure->fee_type == $type ? 'selected' : '' }}>
                                    {{ ucfirst($type) }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- PAYMENT FREQUENCY --}}
                    <div class="col-md-4 mb-3">
                        <label class="form-label fw-semibold">Payment Frequency</label>
                        <select name="payment_frequency" class="form-control">
                            @foreach(['one-time','termly','monthly','quarterly'] as $freq)
                                <option value="{{ $freq }}"
                                    {{ $schoolFeeStructure->payment_frequency == $freq ? 'selected' : '' }}>
                                    {{ ucfirst(str_replace('-', ' ', $freq)) }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- DESCRIPTION --}}
                    <div class="col-md-12 mb-3">
                        <label class="form-label fw-semibold">Description</label>
                        <input type="text"
                               name="description"
                               value="{{ old('description', $schoolFeeStructure->description) }}"
                               class="form-control">
                    </div>

                    {{-- CHECKBOXES --}}
                    <div class="col-md-6 mb-3">

                        <div class="form-check form-switch">
                            <input type="hidden" name="is_mandatory" value="0">

                            <input class="form-check-input"
                                   type="checkbox"
                                   name="is_mandatory"
                                   value="1"
                                   {{ $schoolFeeStructure->is_mandatory ? 'checked' : '' }}>

                            <label class="form-check-label">Mandatory</label>
                        </div>

                    </div>

                    <div class="col-md-6 mb-3">

                        <div class="form-check form-switch">
                            <input type="hidden" name="is_active" value="0">

                            <input class="form-check-input"
                                   type="checkbox"
                                   name="is_active"
                                   value="1"
                                   {{ $schoolFeeStructure->is_active ? 'checked' : '' }}>

                            <label class="form-check-label">Active</label>
                        </div>

                    </div>

                </div>

                <button class="btn btn-success">
                    Update Structure
                </button>

            </form>

        </div>

    </div>

</div>
@endsection