@extends('layouts.master')

@section('title', 'Edit Department - ' . ($department->name ?? ''))

@section('content')

<div class="container-fluid py-4">

    {{-- PAGE HEADER --}}
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-4">
        <div>
            <h5 class="fw-bold mb-1">
                <i class="fas fa-edit me-2 text-primary"></i>
                Edit Department
            </h5>
            <p class="text-muted mb-0">Update department information</p>
        </div>
        <a href="{{ route('departments.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left me-1"></i> Back to Departments
        </a>
    </div>

    {{-- FLASH MESSAGES --}}
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show shadow-sm mb-4">
            <i class="fas fa-exclamation-circle me-2"></i>
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    {{-- EDIT FORM --}}
    <div class="card border-0 shadow-sm rounded-4">
        <div class="card-header bg-white py-3">
            <h5 class="mb-0">
                <i class="fas fa-building me-2 text-primary"></i>
                {{ $department->name }}
            </h5>
        </div>
        <div class="card-body p-4">
            <form action="{{ route('departments.update', $department->id) }}" method="POST">
                @csrf
                @method('PUT')

                <div class="row g-4">
                    
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">
                            Department Name <span class="text-danger">*</span>
                        </label>
                        <input type="text" 
                               name="name" 
                               class="form-control @error('name') is-invalid @enderror" 
                               value="{{ old('name', $department->name) }}"
                               placeholder="e.g., Information Technology"
                               required>
                        @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fw-semibold">
                            Department Code
                        </label>
                        <input type="text" 
                               name="code" 
                               class="form-control @error('code') is-invalid @enderror" 
                               value="{{ old('code', $department->code) }}"
                               placeholder="e.g., IT, HR, FIN">
                        <div class="form-text text-muted small">Optional unique code for the department</div>
                        @error('code')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-12">
                        <label class="form-label fw-semibold">
                            Description
                        </label>
                        <textarea name="description" 
                                  class="form-control @error('description') is-invalid @enderror" 
                                  rows="3"
                                  placeholder="Enter department description">{{ old('description', $department->description) }}</textarea>
                        @error('description')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fw-semibold">
                            <i class="fas fa-user-tie me-1"></i>Head of Department
                        </label>
                        <select name="head_of_department" class="form-select @error('head_of_department') is-invalid @enderror">
                            <option value="">Select Staff Member</option>
                            @if(isset($staff) && $staff->count() > 0)
                                @foreach($staff as $staffMember)
                                    <option value="{{ $staffMember->id }}" 
                                        {{ old('head_of_department', $department->head_of_department) == $staffMember->id ? 'selected' : '' }}>
                                        {{ $staffMember->first_name }} {{ $staffMember->last_name }} 
                                        @if($staffMember->staff_id)
                                            ({{ $staffMember->staff_id }})
                                        @endif
                                        @if($staffMember->position)
                                            - {{ $staffMember->position }}
                                        @endif
                                    </option>
                                @endforeach
                            @else
                                <option value="" disabled>No staff members available</option>
                            @endif
                        </select>
                        <div class="form-text text-muted small">
                            <i class="fas fa-info-circle me-1"></i> 
                            Select the staff member who heads this department
                        </div>
                        @error('head_of_department')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fw-semibold">
                            Status <span class="text-danger">*</span>
                        </label>
                        <select name="status" class="form-select @error('status') is-invalid @enderror" required>
                            <option value="active" {{ old('status', $department->status) == 'active' ? 'selected' : '' }}>Active</option>
                            <option value="inactive" {{ old('status', $department->status) == 'inactive' ? 'selected' : '' }}>Inactive</option>
                        </select>
                        @error('status')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                </div>

                {{-- Current HOD Display --}}
                @if($department->hod)
                    <div class="alert alert-info mt-4">
                        <div class="d-flex align-items-center gap-2">
                            <i class="fas fa-info-circle"></i>
                            <div>
                                <strong>Current Head of Department:</strong> 
                                {{ $department->hod->first_name }} {{ $department->hod->last_name }}
                                @if($department->hod->staff_id)
                                    ({{ $department->hod->staff_id }})
                                @endif
                            </div>
                        </div>
                    </div>
                @endif

                {{-- FORM BUTTONS --}}
                <div class="d-flex justify-content-end gap-2 mt-4 pt-3 border-top">
                    <a href="{{ route('departments.index') }}" class="btn btn-outline-secondary px-4">
                        <i class="fas fa-times me-1"></i> Cancel
                    </a>
                    <button type="reset" class="btn btn-outline-warning px-4">
                        <i class="fas fa-undo-alt me-1"></i> Reset
                    </button>
                    <button type="submit" class="btn btn-primary px-5">
                        <i class="fas fa-save me-1"></i> Update Department
                    </button>
                </div>

            </form>
        </div>
    </div>
</div>

<style>
    .form-label {
        font-size: 0.85rem;
        margin-bottom: 0.4rem;
    }
    
    .form-control, .form-select {
        border-radius: 8px;
        border: 1px solid #dee2e6;
        transition: all 0.2s ease;
    }
    
    .form-control:focus, .form-select:focus {
        border-color: #86b7fe;
        box-shadow: 0 0 0 0.2rem rgba(13,110,253,0.15);
    }
    
    .card {
        border-radius: 12px;
    }
    
    .alert {
        border-radius: 10px;
    }
</style>

@endsection