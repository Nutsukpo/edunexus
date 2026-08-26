@extends('layouts.master')

@section('title', 'Add Fee Structure')

@section('content')
<div class="container-fluid">
    <div class="mb-4 mt-3">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h3 class="fw-bold mb-1">
                    <i class="fas fa-plus-circle me-2 text-primary"></i>
                    Add Fee Structure
                </h3>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('class-fee-structures.index') }}">Fee Structures</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Add New</li>
                    </ol>
                </nav>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <form action="{{ route('class-fee-structures.store') }}" method="POST" id="feeStructureForm">
                @csrf

                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white py-3 border-bottom">
                        <h5 class="mb-0">
                            <i class="fas fa-info-circle me-2 text-primary"></i>
                            Fee Structure Details
                        </h5>
                    </div>
                    <div class="card-body">
                        {{-- Class and Academic Year --}}
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-semibold required-field">
                                    <i class="fas fa-chalkboard me-1 text-primary"></i>
                                    Class
                                </label>
                                <select name="student_class_id" 
                                        id="classSelect"
                                        class="form-select @error('student_class_id') is-invalid @enderror" 
                                        required>
                                    <option value="">Select Class</option>
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

                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-semibold required-field">
                                    <i class="fas fa-calendar-alt me-1 text-primary"></i>
                                    Academic Year
                                </label>
                                <select name="academic_year_id" 
                                        class="form-select @error('academic_year_id') is-invalid @enderror" 
                                        required>
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
                        </div>

                        {{-- Fee Details --}}
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-semibold required-field">
                                    <i class="fas fa-tag me-1 text-primary"></i>
                                    Fee Name
                                </label>
                                <input type="text" 
                                       name="fee_name" 
                                       class="form-control @error('fee_name') is-invalid @enderror" 
                                       placeholder="e.g., Tuition Fee"
                                       value="{{ old('fee_name') }}"
                                       required>
                                @error('fee_name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-semibold required-field">
                                    <i class="fas fa-tags me-1 text-primary"></i>
                                    Fee Type
                                </label>
                                <select name="fee_type" 
                                        class="form-select @error('fee_type') is-invalid @enderror" 
                                        required>
                                    <option value="">Select Fee Type</option>
                                    <option value="tuition" {{ old('fee_type') == 'tuition' ? 'selected' : '' }}>Tuition Fee</option>
                                    <option value="registration" {{ old('fee_type') == 'registration' ? 'selected' : '' }}>Registration Fee</option>
                                    <option value="development" {{ old('fee_type') == 'development' ? 'selected' : '' }}>Development Fee</option>
                                    <option value="library" {{ old('fee_type') == 'library' ? 'selected' : '' }}>Library Fee</option>
                                    <option value="sports" {{ old('fee_type') == 'sports' ? 'selected' : '' }}>Sports Fee</option>
                                    <option value="medical" {{ old('fee_type') == 'medical' ? 'selected' : '' }}>Medical Fee</option>
                                    <option value="insurance" {{ old('fee_type') == 'insurance' ? 'selected' : '' }}>Insurance Fee</option>
                                    <option value="transport" {{ old('fee_type') == 'transport' ? 'selected' : '' }}>Transport Fee</option>
                                    <option value="boarding" {{ old('fee_type') == 'boarding' ? 'selected' : '' }}>Boarding Fee</option>
                                    <option value="uniform" {{ old('fee_type') == 'uniform' ? 'selected' : '' }}>Uniform Fee</option>
                                    <option value="books" {{ old('fee_type') == 'books' ? 'selected' : '' }}>Books Fee</option>
                                    <option value="exam" {{ old('fee_type') == 'exam' ? 'selected' : '' }}>Examination Fee</option>
                                    <option value="graduation" {{ old('fee_type') == 'graduation' ? 'selected' : '' }}>Graduation Fee</option>
                                    <option value="other" {{ old('fee_type') == 'other' ? 'selected' : '' }}>Other Fee</option>
                                </select>
                                @error('fee_type')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-semibold required-field">
                                    <i class="fas fa-money-bill-wave me-1 text-primary"></i>
                                    Amount (GHS)
                                </label>
                                <div class="input-group">
                                    <span class="input-group-text">GHS</span>
                                    <input type="number" 
                                           name="amount" 
                                           class="form-control @error('amount') is-invalid @enderror" 
                                           step="0.01"
                                           min="0"
                                           placeholder="0.00"
                                           value="{{ old('amount') }}"
                                           required>
                                </div>
                                @error('amount')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-semibold">
                                    <i class="fas fa-calendar-day me-1 text-primary"></i>
                                    Due Date
                                </label>
                                <input type="date" 
                                       name="due_date" 
                                       class="form-control @error('due_date') is-invalid @enderror" 
                                       value="{{ old('due_date') }}">
                                @error('due_date')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-12 mb-3">
                                <label class="form-label fw-semibold">
                                    <i class="fas fa-align-left me-1 text-primary"></i>
                                    Description
                                </label>
                                <textarea name="description" 
                                          class="form-control @error('description') is-invalid @enderror" 
                                          rows="3"
                                          placeholder="Enter a brief description of this fee">{{ old('description') }}</textarea>
                                @error('description')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <div class="form-check form-switch">
                                    <input type="checkbox" 
                                           name="is_required" 
                                           class="form-check-input @error('is_required') is-invalid @enderror" 
                                           id="isRequired"
                                           value="1"
                                           {{ old('is_required', true) ? 'checked' : '' }}>
                                    <label class="form-check-label fw-semibold" for="isRequired">
                                        <i class="fas fa-exclamation-circle me-1 text-danger"></i>
                                        Required Fee
                                    </label>
                                    <div class="form-text">If checked, this fee is mandatory for all students in the class.</div>
                                </div>
                                @error('is_required')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <div class="form-check form-switch">
                                    <input type="checkbox" 
                                           name="is_active" 
                                           class="form-check-input @error('is_active') is-invalid @enderror" 
                                           id="isActive"
                                           value="1"
                                           {{ old('is_active', true) ? 'checked' : '' }}>
                                    <label class="form-check-label fw-semibold" for="isActive">
                                        <i class="fas fa-check-circle me-1 text-success"></i>
                                        Active
                                    </label>
                                    <div class="form-text">If checked, this fee structure is active and visible.</div>
                                </div>
                                @error('is_active')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-12 mb-3">
                                <label class="form-label fw-semibold">
                                    <i class="fas fa-cog me-1 text-primary"></i>
                                    Metadata (JSON)
                                </label>
                                <textarea name="metadata" 
                                          class="form-control @error('metadata') is-invalid @enderror" 
                                          rows="2"
                                          placeholder='{"key": "value"}'>{{ old('metadata') }}</textarea>
                                @error('metadata')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <div class="form-text">Optional JSON data for additional settings.</div>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>

        {{-- Sidebar --}}
        <div class="col-lg-4">
            {{-- Action Buttons --}}
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <button type="submit" form="feeStructureForm" class="btn btn-primary w-100 py-2">
                        <i class="fas fa-save me-2"></i>
                        Save Fee Structure
                    </button>
                    <a href="{{ route('class-fee-structures.index') }}" class="btn btn-outline-secondary w-100 mt-2">
                        <i class="fas fa-times me-2"></i>
                        Cancel
                    </a>
                </div>
            </div>

            {{-- Quick Guide --}}
            <div class="card border-0 shadow-sm mt-3">
                <div class="card-header bg-white py-3 border-bottom">
                    <h6 class="mb-0">
                        <i class="fas fa-lightbulb me-2 text-warning"></i>
                        Quick Guide
                    </h6>
                </div>
                <div class="card-body">
                    <ul class="list-unstyled small">
                        <li class="mb-2">
                            <i class="fas fa-check-circle text-success me-1"></i>
                            Choose the class and academic year
                        </li>
                        <li class="mb-2">
                            <i class="fas fa-check-circle text-success me-1"></i>
                            Enter a descriptive fee name
                        </li>
                        <li class="mb-2">
                            <i class="fas fa-check-circle text-success me-1"></i>
                            Select the appropriate fee type
                        </li>
                        <li class="mb-2">
                            <i class="fas fa-check-circle text-success me-1"></i>
                            Set the correct amount
                        </li>
                        <li class="mb-2">
                            <i class="fas fa-check-circle text-success me-1"></i>
                            Mark as required if mandatory
                        </li>
                        <li>
                            <i class="fas fa-check-circle text-success me-1"></i>
                            Keep active to make it visible
                        </li>
                    </ul>
                </div>
            </div>

            {{-- Fee Type Reference --}}
            <div class="card border-0 shadow-sm mt-3">
                <div class="card-header bg-white py-3 border-bottom">
                    <h6 class="mb-0">
                        <i class="fas fa-tags me-2 text-primary"></i>
                        Fee Types Reference
                    </h6>
                </div>
                <div class="card-body">
                    <div class="row small">
                        <div class="col-6">
                            <span class="badge bg-info text-dark d-block mb-1">Tuition</span>
                            <span class="badge bg-info text-dark d-block mb-1">Registration</span>
                            <span class="badge bg-info text-dark d-block mb-1">Development</span>
                            <span class="badge bg-info text-dark d-block mb-1">Library</span>
                            <span class="badge bg-info text-dark d-block mb-1">Sports</span>
                            <span class="badge bg-info text-dark d-block mb-1">Medical</span>
                        </div>
                        <div class="col-6">
                            <span class="badge bg-info text-dark d-block mb-1">Insurance</span>
                            <span class="badge bg-info text-dark d-block mb-1">Transport</span>
                            <span class="badge bg-info text-dark d-block mb-1">Boarding</span>
                            <span class="badge bg-info text-dark d-block mb-1">Uniform</span>
                            <span class="badge bg-info text-dark d-block mb-1">Books</span>
                            <span class="badge bg-info text-dark d-block mb-1">Exam</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .required-field::after {
        content: '*';
        color: #dc3545;
        margin-left: 4px;
    }
    
    .card {
        border-radius: 10px;
        overflow: hidden;
    }
    
    .card-header {
        background-color: #f8f9fa;
    }
    
    .btn-primary {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        border: none;
    }
    
    .btn-primary:hover {
        background: linear-gradient(135deg, #5a6fd6 0%, #6a4292 100%);
        transform: translateY(-1px);
        box-shadow: 0 4px 15px rgba(102, 126, 234, 0.4);
    }
    
    .form-select, .form-control {
        border-radius: 8px;
    }
    
    .form-select:focus, .form-control:focus {
        border-color: #667eea;
        box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
    }
    
    .form-check-input:checked {
        background-color: #667eea;
        border-color: #667eea;
    }
</style>

@push('scripts')
<script>
$(document).ready(function() {
    // Auto-calculate amount preview
    $('#amount').on('input', function() {
        const amount = parseFloat($(this).val()) || 0;
        $('#amountPreview').text('GHS ' + amount.toFixed(2));
    });

    // Preview fee name
    $('#fee_name').on('input', function() {
        const name = $(this).val() || 'Fee Name';
        $('#feeNamePreview').text(name);
    });

    // Update fee type badge
    $('#fee_type').on('change', function() {
        const type = $(this).find('option:selected').text() || 'Fee Type';
        $('#feeTypePreview').text(type);
    });
});
</script>
@endpush

@endsection