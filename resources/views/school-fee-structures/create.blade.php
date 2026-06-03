@extends('layouts.master')

@section('title', 'Create Fee Structure')

@section('content')

<div class="container-fluid py-4">

    <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-4">
        <div>
            <h5 class="fw-bold mb-1">
                <i class="fas fa-plus-circle me-2 text-danger"></i>
                Create Fee Structure
            </h5>
            <p class="text-muted mb-0">Add a new school fee structure</p>
        </div>
        <a href="{{ route('school-fee-structures.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left me-1"></i> Back to List
        </a>
    </div>

    <div class="card border-0 shadow-sm rounded-4">
        <div class="card-header bg-white py-3">
            <h5 class="mb-0">Fee Structure Information</h5>
        </div>
        <div class="card-body">
            <form method="POST" action="{{ route('school-fee-structures.store') }}">
                @csrf

                <div class="row g-3">
                    {{-- NAME --}}
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Fee Name <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" 
                               value="{{ old('name') }}" required>
                        @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- CODE --}}
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Fee Code <span class="text-danger">*</span></label>
                        <input type="text" name="code" class="form-control @error('code') is-invalid @enderror" 
                               value="{{ old('code') }}" placeholder="e.g., TUITION-2024" required>
                        @error('code')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- ACADEMIC YEAR --}}
                    <div class="col-md-3">
                        <label class="form-label fw-semibold">Academic Year <span class="text-danger">*</span></label>
                        <select name="academic_year_id" class="form-select @error('academic_year_id') is-invalid @enderror" required>
                            <option value="">Select Academic Year</option>
                            @foreach($academicYears as $year)
                                <option value="{{ $year->id }}" {{ old('academic_year_id') == $year->id ? 'selected' : '' }}>
                                    {{ $year->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('academic_year_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- TERM --}}
                    <div class="col-md-3">
                        <label class="form-label fw-semibold">Term <span class="text-danger">*</span></label>
                        <select name="term_id" class="form-select @error('term_id') is-invalid @enderror" required>
                            <option value="">Select Term</option>
                            @foreach($terms as $term)
                                <option value="{{ $term->id }}" {{ old('term_id') == $term->id ? 'selected' : '' }}>
                                    {{ $term->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('term_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- CLASS --}}
                    <div class="col-md-3">
                        <label class="form-label fw-semibold">Class</label>
                        <select name="student_class_id" class="form-select @error('student_class_id') is-invalid @enderror">
                            <option value="">All Classes</option>
                            @foreach($classes as $class)
                                <option value="{{ $class->id }}" {{ old('student_class_id') == $class->id ? 'selected' : '' }}>
                                    {{ $class->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('student_class_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- FEE CATEGORY --}}
                    <div class="col-md-3">
                        <label class="form-label fw-semibold">Fee Category <span class="text-danger">*</span></label>
                        <select name="fee_category_id" class="form-select @error('fee_category_id') is-invalid @enderror" required>
                            <option value="">Select Category</option>
                            @foreach($categories as $cat)
                                <option value="{{ $cat->id }}" {{ old('fee_category_id') == $cat->id ? 'selected' : '' }}>
                                    {{ $cat->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('fee_category_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- AMOUNT --}}
                    <div class="col-md-3">
                        <label class="form-label fw-semibold">Amount (GHS) <span class="text-danger">*</span></label>
                        <input type="number" step="0.01" name="amount" class="form-control @error('amount') is-invalid @enderror" 
                               value="{{ old('amount') }}" required>
                        @error('amount')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- FEE TYPE --}}
                    <div class="col-md-3">
                        <label class="form-label fw-semibold">Fee Type</label>
                        <select name="fee_type" class="form-select">
                            <option value="tuition">Tuition</option>
                            <option value="registration">Registration</option>
                            <option value="exam">Exam</option>
                            <option value="library">Library</option>
                            <option value="sports">Sports</option>
                            <option value="transport">Transport</option>
                            <option value="other">Other</option>
                        </select>
                    </div>

                    {{-- PAYMENT FREQUENCY --}}
                    <div class="col-md-3">
                        <label class="form-label fw-semibold">Payment Frequency</label>
                        <select name="payment_frequency" class="form-select">
                            <option value="one-time">One Time</option>
                            <option value="termly">Termly</option>
                            <option value="monthly">Monthly</option>
                            <option value="quarterly">Quarterly</option>
                        </select>
                    </div>

                    {{-- DUE DATE --}}
                    <div class="col-md-3">
                        <label class="form-label fw-semibold">Due Date</label>
                        <input type="date" name="due_date" class="form-control" value="{{ old('due_date') }}">
                    </div>

                    {{-- DESCRIPTION --}}
                    <div class="col-12">
                        <label class="form-label fw-semibold">Description</label>
                        <textarea name="description" class="form-control" rows="3">{{ old('description') }}</textarea>
                    </div>

                    {{-- CHECKBOXES --}}
                    <div class="col-12">
                        <div class="form-check">
                            <input type="checkbox" name="is_mandatory" class="form-check-input" id="isMandatory" checked>
                            <label class="form-check-label-dark" for="isMandatory">Mandatory Fee</label>
                        </div>
                        <div class="form-check text-dark ">
                            <input type="checkbox" name="is_active" class="form-check-input" id="isActive" checked>
                            <label class="form-check-label text-dark" for="isActive">Active</label>
                        </div>
                    </div>
                </div>

                <div class="d-flex justify-content-end gap-2 mt-4 pt-3 border-top">
                    <a href="{{ route('school-fee-structures.index') }}" class="btn btn-outline-secondary px-4">
                        <i class="fas fa-times me-1"></i> Cancel
                    </a>
                    <button type="submit" class="btn btn-white text-dark px-5">
                        <i class="fas fa-save me-1"></i> Save Fee Structure
                    </button>
                </div>

            </form>
        </div>
    </div>
</div>

<style>
    .form-label {
        font-size: 0.85rem;
        font-weight: 600;
        margin-bottom: 0.4rem;
    }
    .form-control, .form-select {
        border-radius: 8px;
        border: 1px solid #dee2e6;
    }
    .form-control:focus, .form-select:focus {
        border-color: #86b7fe;
        box-shadow: 0 0 0 0.2rem rgba(13,110,253,0.15);
    }
</style>

@endsection