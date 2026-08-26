@extends('layouts.master')

@section('title', 'Edit Fee Structure')

@section('content')
<div class="container-fluid">
    <div class="mb-4 mt-3">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h3 class="fw-bold mb-1">
                    <i class="fas fa-edit me-2 text-primary"></i>
                    Edit Fee Structure
                </h3>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('class-fee-structures.index') }}">Fee Structures</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Edit</li>
                    </ol>
                </nav>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <form action="{{ route('class-fee-structures.update', $feeStructure->id) }}" method="POST" id="feeStructureForm">
                @csrf
                @method('PUT')

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
                                        class="form-select @error('student_class_id') is-invalid @enderror" 
                                        required>
                                    <option value="">Select Class</option>
                                    @foreach($classes as $class)
                                        <option value="{{ $class->id }}" {{ old('student_class_id', $feeStructure->student_class_id) == $class->id ? 'selected' : '' }}>
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
                                        <option value="{{ $year->id }}" {{ old('academic_year_id', $feeStructure->academic_year_id) == $year->id ? 'selected' : '' }}>
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
                                       value="{{ old('fee_name', $feeStructure->fee_name) }}"
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
                                    <option value="tuition" {{ old('fee_type', $feeStructure->fee_type) == 'tuition' ? 'selected' : '' }}>Tuition Fee</option>
                                    <option value="registration" {{ old('fee_type', $feeStructure->fee_type) == 'registration' ? 'selected' : '' }}>Registration Fee</option>
                                    <option value="development" {{ old('fee_type', $feeStructure->fee_type) == 'development' ? 'selected' : '' }}>Development Fee</option>
                                    <option value="library" {{ old('fee_type', $feeStructure->fee_type) == 'library' ? 'selected' : '' }}>Library Fee</option>
                                    <option value="sports" {{ old('fee_type', $feeStructure->fee_type) == 'sports' ? 'selected' : '' }}>Sports Fee</option>
                                    <option value="medical" {{ old('fee_type', $feeStructure->fee_type) == 'medical' ? 'selected' : '' }}>Medical Fee</option>
                                    <option value="insurance" {{ old('fee_type', $feeStructure->fee_type) == 'insurance' ? 'selected' : '' }}>Insurance Fee</option>
                                    <option value="transport" {{ old('fee_type', $feeStructure->fee_type) == 'transport' ? 'selected' : '' }}>Transport Fee</option>
                                    <option value="boarding" {{ old('fee_type', $feeStructure->fee_type) == 'boarding' ? 'selected' : '' }}>Boarding Fee</option>
                                    <option value="uniform" {{ old('fee_type', $feeStructure->fee_type) == 'uniform' ? 'selected' : '' }}>Uniform Fee</option>
                                    <option value="books" {{ old('fee_type', $feeStructure->fee_type) == 'books' ? 'selected' : '' }}>Books Fee</option>
                                    <option value="exam" {{ old('fee_type', $feeStructure->fee_type) == 'exam' ? 'selected' : '' }}>Examination Fee</option>
                                    <option value="graduation" {{ old('fee_type', $feeStructure->fee_type) == 'graduation' ? 'selected' : '' }}>Graduation Fee</option>
                                    <option value="other" {{ old('fee_type', $feeStructure->fee_type) == 'other' ? 'selected' : '' }}>Other Fee</option>
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
                                           value="{{ old('amount', $feeStructure->amount) }}"
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
                                       value="{{ old('due_date', $feeStructure->due_date ? $feeStructure->due_date->format('Y-m-d') : '') }}">
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
                                          placeholder="Enter a brief description of this fee">{{ old('description', $feeStructure->description) }}</textarea>
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
                                           {{ old('is_required', $feeStructure->is_required) ? 'checked' : '' }}>
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
                                           {{ old('is_active', $feeStructure->is_active) ? 'checked' : '' }}>
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
                                          placeholder='{"key": "value"}'>{{ old('metadata', $feeStructure->metadata ? json_encode($feeStructure->metadata) : '') }}</textarea>
                                @error('metadata')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <div class="form-text">Optional JSON data for additional settings.</div>
                            </div>
                        </div>

                        {{-- Created Info --}}
                        <div class="row mt-3">
                            <div class="col-12">
                                <hr>
                                <div class="d-flex justify-content-between text-muted small">
                                    <span>Created: {{ $feeStructure->created_at ? $feeStructure->created_at->format('M d, Y H:i') : 'N/A' }}</span>
                                    <span>Last Updated: {{ $feeStructure->updated_at ? $feeStructure->updated_at->format('M d, Y H:i') : 'N/A' }}</span>
                                    @if($feeStructure->creator)
                                        <span>Created By: {{ $feeStructure->creator->name }}</span>
                                    @endif
                                </div>
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
                        Update Fee Structure
                    </button>
                    <a href="{{ route('class-fee-structures.index') }}" class="btn btn-outline-secondary w-100 mt-2">
                        <i class="fas fa-times me-2"></i>
                        Cancel
                    </a>
                    <hr>
                    <button type="button" 
                            class="btn btn-outline-danger w-100" 
                            onclick="confirmDelete('{{ $feeStructure->id }}', '{{ $feeStructure->fee_name }}')">
                        <i class="fas fa-trash me-2"></i>
                        Delete Fee Structure
                    </button>
                </div>
            </div>

            {{-- Current Fee Info --}}
            <div class="card border-0 shadow-sm mt-3">
                <div class="card-header bg-white py-3 border-bottom">
                    <h6 class="mb-0">
                        <i class="fas fa-info-circle me-2 text-primary"></i>
                        Current Fee Info
                    </h6>
                </div>
                <div class="card-body">
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">Fee Name:</span>
                        <span class="fw-semibold">{{ $feeStructure->fee_name }}</span>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">Type:</span>
                        <span>{{ $feeStructure->fee_type_label }}</span>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">Amount:</span>
                        <span class="fw-bold text-primary">{{ $feeStructure->formatted_amount }}</span>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">Class:</span>
                        <span>{{ $feeStructure->studentClass->name ?? 'N/A' }}</span>
                    </div>
                    <div class="d-flex justify-content-between">
                        <span class="text-muted">Status:</span>
                        @if($feeStructure->is_active)
                            <span class="badge bg-success">Active</span>
                        @else
                            <span class="badge bg-danger">Inactive</span>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Tip --}}
            <div class="card border-0 shadow-sm mt-3">
                <div class="card-body">
                    <h6 class="mb-2">
                        <i class="fas fa-lightbulb me-2 text-warning"></i>
                        Tip
                    </h6>
                    <p class="small text-muted mb-0">
                        Updating a fee structure will affect all students in this class 
                        for the selected academic year. Please ensure the changes are correct.
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Delete Confirmation Modal --}}
<div class="modal fade" id="deleteModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Confirm Delete</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to delete the fee structure: <strong id="deleteFeeName"></strong>?</p>
                <p class="text-danger"><small>This action cannot be undone. All associated student fee items will also be deleted.</small></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <form id="deleteForm" action="{{ route('class-fee-structures.destroy', $feeStructure->id) }}" method="POST">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger">Delete</button>
                </form>
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
</style>

@push('scripts')
<script>
function confirmDelete(id, name) {
    $('#deleteFeeName').text(name);
    $('#deleteModal').modal('show');
}
</script>
@endpush

@endsection