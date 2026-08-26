@extends('layouts.master')

@section('title', isset($staff) ? 'Edit Staff' : 'Add Staff')

@section('content')

<div class="container-fluid py-4">
    
    <!-- PAGE HEADER -->
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-4">
        <div>
            <h5 class="fw-bold mb-1">
                <i class="fas fa-user-plus me-2 text-danger"></i>
                {{ isset($staff) ? 'Edit Staff Member' : 'Add New Staff Member' }}
            </h5>
            <p class="text-muted mb-0">Fill in the staff information below</p>
        </div>
        <a href="{{ route('staff.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left me-1"></i> Back to List
        </a>
    </div>

    <!-- FLASH MESSAGES -->
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show">
            <i class="fas fa-check-circle me-2"></i>
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show">
            <i class="fas fa-exclamation-circle me-2"></i>
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <form action="{{ isset($staff) ? route('staff.update', $staff->id) : route('staff.store') }}" 
          method="POST" 
          enctype="multipart/form-data">
        
        @csrf
        @if(isset($staff))
            @method('PUT')
        @endif

        <div class="row g-4">
            
            <!-- Personal Information Section -->
            <div class="col-12">
                <div class="card border-0 shadow-sm rounded-3">
                    <div class="card-header bg-white py-3">
                        <h6 class="fw-bold mb-0">
                            <i class="fas fa-user-circle me-2 text-dark"></i>Personal Information
                        </h6>
                    </div>
                    <div class="card-body bg-light">
                        <div class="row g-3">
                            <div class="col-md-3">
                                <label class="form-label fw-semibold">
                                    <i class="fas fa-qrcode me-1 text-muted"></i>Staff ID
                                </label>
                                <input type="text" name="staff_id"
                                       class="form-control bg-white"
                                       value="{{ old('staff_id', $staff->staff_id ?? '') }}"
                                       {{ isset($staff) ? 'readonly' : '' }}
                                       placeholder="Auto-generated">
                                @if(!isset($staff))
                                    <div class="form-text text-muted small">Auto-generated if left empty</div>
                                @endif
                            </div>
                            
                            <div class="col-md-3">
                                <label class="form-label fw-semibold">
                                    <i class="fas fa-user me-1 text-muted"></i>First Name <span class="text-danger">*</span>
                                </label>
                                <input type="text" name="first_name"
                                       class="form-control @error('first_name') is-invalid @enderror"
                                       value="{{ old('first_name', $staff->first_name ?? '') }}"
                                       placeholder="Enter first name">
                                @error('first_name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="col-md-3">
                                <label class="form-label fw-semibold">
                                    <i class="fas fa-user me-1 text-muted"></i>Last Name <span class="text-danger">*</span>
                                </label>
                                <input type="text" name="last_name"
                                       class="form-control @error('last_name') is-invalid @enderror"
                                       value="{{ old('last_name', $staff->last_name ?? '') }}"
                                       placeholder="Enter last name">
                                @error('last_name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="col-md-3">
                                <label class="form-label fw-semibold">
                                    <i class="fas fa-user-tag me-1 text-muted"></i>Other Name
                                </label>
                                <input type="text" name="other_name"
                                       class="form-control"
                                       value="{{ old('other_name', $staff->other_name ?? '') }}"
                                       placeholder="Enter other name">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Demographic Information -->
            <div class="col-12">
                <div class="card border-0 shadow-sm rounded-3">
                    <div class="card-header bg-white py-3">
                        <h6 class="fw-bold mb-0">
                            <i class="fas fa-democrat me-2 text-dark"></i>Demographic Information
                        </h6>
                    </div>
                    <div class="card-body bg-light">
                        <div class="row g-3">
                            <div class="col-md-3">
                                <label class="form-label fw-semibold">
                                    <i class="fas fa-venus-mars me-1 text-muted"></i>Gender
                                </label>
                                <select name="gender" class="form-select @error('gender') is-invalid @enderror">
                                    <option value="">Select Gender</option>
                                    <option value="Male" {{ old('gender', $staff->gender ?? '') == 'Male' ? 'selected' : '' }}>Male</option>
                                    <option value="Female" {{ old('gender', $staff->gender ?? '') == 'Female' ? 'selected' : '' }}>Female</option>
                                    <option value="Other" {{ old('gender', $staff->gender ?? '') == 'Other' ? 'selected' : '' }}>Other</option>
                                </select>
                                @error('gender')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="col-md-3">
                                <label class="form-label fw-semibold">
                                    <i class="fas fa-cake-candles me-1 text-muted"></i>Date of Birth
                                </label>
                                <input type="date" name="date_of_birth"
                                       class="form-control @error('date_of_birth') is-invalid @enderror"
                                       value="{{ old('date_of_birth', $staff->date_of_birth ?? '') }}">
                                @error('date_of_birth')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="col-md-3">
                                <label class="form-label fw-semibold">
                                    <i class="fas fa-phone me-1 text-muted"></i>Phone
                                </label>
                                <input type="text" name="phone"
                                       class="form-control @error('phone') is-invalid @enderror"
                                       value="{{ old('phone', $staff->phone ?? '') }}"
                                       placeholder="Enter phone number">
                                @error('phone')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="col-md-3">
                                <label class="form-label fw-semibold">
                                    <i class="fas fa-envelope me-1 text-muted"></i>Email
                                </label>
                                <input type="email" name="email"
                                       class="form-control @error('email') is-invalid @enderror"
                                       value="{{ old('email', $staff->email ?? '') }}"
                                       placeholder="Enter email address">
                                @error('email')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Employment Information - FIXED -->
            <div class="col-12">
                <div class="card border-0 shadow-sm rounded-3">
                    <div class="card-header bg-white py-3">
                        <h6 class="fw-bold mb-0">
                            <i class="fas fa-briefcase me-2 text-dark"></i>Employment Information
                        </h6>
                    </div>
                    <div class="card-body bg-light">
                        <div class="row g-3">
                        <div class="col-md-3">
                        <div class="col-md-8">
                            <label class="form-label fw-semibold small text-muted">Department</label>
                            <select name="department" class="form-select">
                                <option value="">Select Department</option>
                                <option value="Administration" >Administration</option>
                                <option value="Science">Science</option>
                                <option value="Mathematics">Mathematics</option>
                                <option value="English" >English</option>
                                <option value="IT Department" >IT Department</option>
                                <option value="Accounts">Accounts</option>
                                <option value="Human Resources">Human Resources</option>
                                <option value="Library">Library</option>
                                <option value="Sports">Sports</option>
                            </select>
                        </div>
                                <div class="form-text text-muted small">
                                    <i class="fas fa-info-circle me-1"></i> 
                                    Select the department this staff belongs to
                                </div>
                                @error('department_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="col-md-3">
                                <label class="form-label fw-semibold">
                                    <i class="fas fa-badge me-1 text-muted"></i>Position
                                </label>
                                <input type="text" name="position"
                                       class="form-control"
                                       value="{{ old('position', $staff->position ?? '') }}"
                                       placeholder="Enter position">
                            </div>
                            
                            <div class="col-md-3">
                                <label class="form-label fw-semibold">
                                    <i class="fas fa-calendar-alt me-1 text-muted"></i>Date Employed <span class="text-danger">*</span>
                                </label>
                                <input type="date" name="date_employed"
                                       class="form-control @error('date_employed') is-invalid @enderror"
                                       value="{{ old('date_employed', $staff->date_employed ?? '') }}"
                                       required>
                                @error('date_employed')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="col-md-3">
                                <label class="form-label fw-semibold">
                                    <i class="fas fa-money-bill-wave me-1 text-muted"></i>Salary
                                </label>
                                <div class="input-group">
                                    <span class="input-group-text">GHS</span>
                                    <input type="number" step="0.01" name="salary"
                                           class="form-control"
                                           value="{{ old('salary', $staff->salary ?? '') }}"
                                           placeholder="0.00">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Address & Classification - FIXED Staff Type -->
            <div class="col-12">
                <div class="card border-0 shadow-sm rounded-3">
                    <div class="card-header bg-white py-3">
                        <h6 class="fw-bold mb-0">
                            <i class="fas fa-location-dot me-2 text-dark"></i>Address & Classification
                        </h6>
                    </div>
                    <div class="card-body bg-light">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">
                                    <i class="fas fa-location-dot me-1 text-muted"></i>Address
                                </label>
                                <textarea name="address" rows="3"
                                          class="form-control"
                                          placeholder="Enter full address">{{ old('address', $staff->address ?? '') }}</textarea>
                            </div>
                            
                            <div class="col-md-3">
                                <label class="form-label fw-semibold">
                                    <i class="fas fa-tag me-1 text-muted"></i>Staff Type
                                </label>
                                <select name="staff_type" class="form-select @error('staff_type') is-invalid @enderror">
                                    <option value="">Select Staff Type</option>
                                    <option value="Teaching" {{ old('staff_type', $staff->staff_type ?? '') == 'Teaching' ? 'selected' : '' }}>Teaching</option>
                                    <option value="Non-Teaching" {{ old('staff_type', $staff->staff_type ?? '') == 'Non-Teaching' ? 'selected' : '' }}>Non-Teaching</option>
                                    <option value="Permanent" {{ old('staff_type', $staff->staff_type ?? '') == 'Permanent' ? 'selected' : '' }}>Permanent</option>
                                    <option value="Contract" {{ old('staff_type', $staff->staff_type ?? '') == 'Contract' ? 'selected' : '' }}>Contract</option>
                                    <option value="Temporary" {{ old('staff_type', $staff->staff_type ?? '') == 'Temporary' ? 'selected' : '' }}>Temporary</option>
                                    <option value="Intern" {{ old('staff_type', $staff->staff_type ?? '') == 'Intern' ? 'selected' : '' }}>Intern</option>
                                    <option value="Consultant" {{ old('staff_type', $staff->staff_type ?? '') == 'Consultant' ? 'selected' : '' }}>Consultant</option>
                                </select>
                                @error('staff_type')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="col-md-3">
                                <label class="form-label fw-semibold">
                                    <i class="fas fa-flag-checkered me-1 text-muted"></i>Status <span class="text-danger">*</span>
                                </label>
                                <select name="status" class="form-select @error('status') is-invalid @enderror" required>
                                    <option value="">Select Status</option>
                                    <option value="Active" {{ old('status', $staff->status ?? '') == 'Active' ? 'selected' : '' }}>Active</option>
                                    <option value="Inactive" {{ old('status', $staff->status ?? '') == 'Inactive' ? 'selected' : '' }}>Inactive</option>
                                    <option value="On Leave" {{ old('status', $staff->status ?? '') == 'On Leave' ? 'selected' : '' }}>On Leave</option>
                                    <option value="Suspended" {{ old('status', $staff->status ?? '') == 'Suspended' ? 'selected' : '' }}>Suspended</option>
                                    <option value="Terminated" {{ old('status', $staff->status ?? '') == 'Terminated' ? 'selected' : '' }}>Terminated</option>
                                </select>
                                @error('status')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Photo Upload Section -->
            <div class="col-12">
                <div class="card border-0 shadow-sm rounded-3">
                    <div class="card-header bg-white py-3">
                        <h6 class="fw-bold mb-0">
                            <i class="fas fa-camera me-2 text-dark"></i>Profile Photo
                        </h6>
                    </div>
                    <div class="card-body bg-light">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">
                                    <i class="fas fa-image me-1 text-muted "></i>Upload Photo
                                </label>
                                <input type="file" name="photo" class="form-control @error('photo') is-invalid @enderror" accept="image/*">
                                <div class="form-text text-muted small">
                                    <i class="fas fa-info-circle me-1"></i> 
                                    Allowed formats: JPG, JPEG, PNG. Max size: 2MB
                                </div>
                                @error('photo')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            @if(isset($staff) && $staff->photo)
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">Current Photo</label>
                                    <div>
                                        <img src="{{ asset('storage/uploads/staff/'.$staff->photo) }}" 
                                             alt="Current Photo" 
                                             class="rounded-3 border"
                                             style="width: 80px; height: 80px; object-fit: cover;">
                                        <div class="form-text text-muted small mt-1">
                                            <i class="fas fa-info-circle me-1"></i> 
                                            Leave blank to keep current photo
                                        </div>
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- FORM BUTTONS -->
            <div class="col-12">
                <div class="d-flex justify-content-end gap-2">
                    <a href="{{ route('staff.index') }}" class="btn btn-secondary">
                        <i class="fas fa-times me-1"></i> Cancel
                    </a>
                    <button type="submit" class="btn btn-danger">
                        <i class="fas fa-save me-1"></i> 
                        {{ isset($staff) ? 'Update Staff' : 'Save Staff' }}
                    </button>
                </div>
            </div>
            
        </div>
    </form>
</div>

<style>
    .form-label {
        font-size: 0.85rem;
        margin-bottom: 0.4rem;
        font-weight: 600;
    }
    
    .card {
        transition: all 0.3s ease;
    }
    
    .card:hover {
        box-shadow: 0 4px 15px rgba(0,0,0,0.08);
    }
    
    .form-control:focus, .form-select:focus {
        border-color: #86b7fe;
        box-shadow: 0 0 0 0.2rem rgba(13,110,253,0.15);
    }
    
    .input-group-text {
        background-color: #f8f9fa;
    }
    
    .bg-light {
        background-color: #f9fafb !important;
    }
    
    .card-header {
        border-bottom: 1px solid rgba(0,0,0,0.08);
    }
    
    .text-danger {
        font-size: 0.75rem;
    }
    
    @media (max-width: 768px) {
        .row.g-4 {
            --bs-gutter-y: 1rem;
        }
    }
</style>

@endsection