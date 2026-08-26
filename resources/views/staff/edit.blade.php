@extends('layouts.master')

@section('title', 'Edit Staff - ' . ($staff->first_name ?? '') . ' ' . ($staff->last_name ?? ''))

@section('content')

<div class="container-fluid py-4">

    {{-- PAGE HEADER --}}
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-4">
        <div>
            <h5 class="fw-bold mb-1">
                <i class="fas fa-user-edit me-2 text-danger"></i>
                Edit Staff Member
            </h5>
            <p class="text-muted mb-0">Update staff information and records</p>
        </div>
        <a href="{{ route('staff.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left me-1"></i> Back 
        </a>
    </div>

    {{-- Validation Errors --}}
    @if ($errors->any())
        <div class="alert alert-danger alert-dismissible fade show shadow-sm rounded-3 border-0 mb-4">
            <div class="fw-semibold mb-2">
                <i class="fas fa-exclamation-triangle me-2"></i>Please fix the following errors:
            </div>
            <ul class="mb-0 ps-3">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    {{-- FLASH MESSAGES --}}
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show shadow-sm mb-4">
            <i class="fas fa-check-circle me-2"></i>
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    {{-- Main Card --}}
    <div class="card border-0 shadow-sm rounded-4 overflow-hidden">

        {{-- Header with Staff Info --}}
        <div class="card-header bg-white py-4 border-bottom">
            <div class="d-flex align-items-center gap-3 flex-wrap">
                {{-- Avatar --}}
                <div>
                    @if($staff->photo && file_exists(public_path('storage/uploads/staff/'.$staff->photo)))
                        <img src="{{ asset('storage/uploads/staff/'.$staff->photo) }}"
                             class="rounded-circle shadow-sm border"
                             width="70"
                             height="70"
                             style="object-fit: cover;">
                    @else
                        <!-- <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center fw-bold shadow-sm"
                             style="width:70px;height:70px;font-size:24px;">
                            {{ strtoupper(substr($staff->first_name ?? 'S',0,1)) }}{{ strtoupper(substr($staff->last_name ?? 'T',0,1)) }}
                        </div> -->
                    @endif
                </div>

                {{-- Staff Info --}}
                <div>
                    <h4 class="fw-semibold text-dark mb-1">
                        {{ $staff->first_name }} {{ $staff->last_name }}
                    </h4>
                    <div class="d-flex flex-wrap gap-2 mt-1">
                        <span class="badge bg-light text-dark border px-3 py-1 rounded-pill">
                            <i class="fas fa-id-card me-1"></i>ID: {{ $staff->staff_id ?? 'N/A' }}
                        </span>
                        @if($staff->staff_type)
                            <span class="badge bg-light text-dark border px-3 py-1 rounded-pill">
                                <i class="fas fa-user-tag me-1"></i>{{ $staff->staff_type }}
                            </span>
                        @endif
                        @if($staff->status)
                            <span class="badge bg-{{ $staff->status == 'Active' ? 'success' : ($staff->status == 'On Leave' ? 'warning' : 'secondary') }} text-white px-3 py-1 rounded-pill">
                                <i class="fas fa-flag-checkered me-1"></i>{{ $staff->status }}
                            </span>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        {{-- Form Body --}}
        <div class="card-body p-4">
            <form action="{{ route('staff.update', $staff->id) }}"
                  method="POST"
                  enctype="multipart/form-data"
                  id="editStaffForm">

                @csrf
                @method('PUT')

                {{-- PERSONAL INFORMATION --}}
                <div class="mb-4">
                    <h6 class="fw-semibold text-dark border-bottom pb-2 mb-3">
                        <i class="fas fa-user-circle me-2 text-dark"></i>Personal Information
                    </h6>
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label fw-semibold small text-muted">
                                First Name <span class="text-danger">*</span>
                            </label>
                            <input type="text" name="first_name"
                                   class="form-control @error('first_name') is-invalid @enderror"
                                   value="{{ old('first_name', $staff->first_name) }}" 
                                   required>
                            @error('first_name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-4">
                            <label class="form-label fw-semibold small text-muted">
                                Last Name <span class="text-danger">*</span>
                            </label>
                            <input type="text" name="last_name"
                                   class="form-control @error('last_name') is-invalid @enderror"
                                   value="{{ old('last_name', $staff->last_name) }}" 
                                   required>
                            @error('last_name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-4">
                            <label class="form-label fw-semibold small text-muted">Other Name</label>
                            <input type="text" name="other_name"
                                   class="form-control"
                                   value="{{ old('other_name', $staff->other_name) }}">
                        </div>

                        <div class="col-md-3">
                            <label class="form-label fw-semibold small text-muted">Gender</label>
                            <select name="gender" class="form-select">
                                <option value="">Select Gender</option>
                                <option value="Male" {{ old('gender', $staff->gender) == 'Male' ? 'selected' : '' }}>Male</option>
                                <option value="Female" {{ old('gender', $staff->gender) == 'Female' ? 'selected' : '' }}>Female</option>
                                <option value="Other" {{ old('gender', $staff->gender) == 'Other' ? 'selected' : '' }}>Other</option>
                            </select>
                        </div>

                        <div class="col-md-3">
                            <label class="form-label fw-semibold small text-muted">Date of Birth</label>
                            <input type="date" name="date_of_birth"
                                   class="form-control"
                                   value="{{ old('date_of_birth', $staff->date_of_birth) }}">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold small text-muted">Phone</label>
                            <input type="text" name="phone"
                                   class="form-control"
                                   value="{{ old('phone', $staff->phone) }}"
                                   placeholder="Enter phone number">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold small text-muted">Email</label>
                            <input type="email" name="email"
                                   class="form-control @error('email') is-invalid @enderror"
                                   value="{{ old('email', $staff->email) }}"
                                   placeholder="Enter email address">
                            @error('email')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-12">
                            <label class="form-label fw-semibold small text-muted">Address</label>
                            <textarea name="address" class="form-control" rows="2" 
                                      placeholder="Enter full address">{{ old('address', $staff->address) }}</textarea>
                        </div>
                    </div>
                </div>

                {{-- EMPLOYMENT INFORMATION --}}
                <div class="mb-4">
                    <h6 class="fw-semibold text-dark border-bottom pb-2 mb-3">
                        <i class="fas fa-briefcase me-2 text-dark"></i>Employment Information
                    </h6>
                    <div class="row g-3">
                        <div class="col-md-3">
                            <label class="form-label fw-semibold small text-muted">
                                Staff ID <span class="text-danger">*</span>
                            </label>
                            <input type="text" name="staff_id"
                                   class="form-control @error('staff_id') is-invalid @enderror"
                                   value="{{ old('staff_id', $staff->staff_id) }}" 
                                   required>
                            @error('staff_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-3">
                            <label class="form-label fw-semibold small text-muted">Department</label>
                            <select name="department" class="form-select">
                                <option value="">Select Department</option>
                                <option value="Administration" {{ old('department', $staff->department) == 'Administration' ? 'selected' : '' }}>Administration</option>
                                <option value="Science" {{ old('department', $staff->department) == 'Science' ? 'selected' : '' }}>Science</option>
                                <option value="Mathematics" {{ old('department', $staff->department) == 'Mathematics' ? 'selected' : '' }}>Mathematics</option>
                                <option value="English" {{ old('department', $staff->department) == 'English' ? 'selected' : '' }}>English</option>
                                <option value="IT Department" {{ old('department', $staff->department) == 'IT Department' ? 'selected' : '' }}>IT Department</option>
                                <option value="Accounts" {{ old('department', $staff->department) == 'Accounts' ? 'selected' : '' }}>Accounts</option>
                                <option value="Human Resources" {{ old('department', $staff->department) == 'Human Resources' ? 'selected' : '' }}>Human Resources</option>
                                <option value="Library" {{ old('department', $staff->department) == 'Library' ? 'selected' : '' }}>Library</option>
                                <option value="Sports" {{ old('department', $staff->department) == 'Sports' ? 'selected' : '' }}>Sports</option>
                            </select>
                        </div>

                        <div class="col-md-3">
                            <label class="form-label fw-semibold small text-muted">Position</label>
                            <input type="text" name="position"
                                   class="form-control"
                                   value="{{ old('position', $staff->position) }}"
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
                            <label class="form-label fw-semibold small text-muted">Staff Type</label>
                            <select name="staff_type" class="form-select">
                                <option value="">Select Staff Type</option>
                                <option value="Teaching" {{ old('staff_type', $staff->staff_type) == 'Teaching' ? 'selected' : '' }}>Teaching</option>
                                <option value="Non-Teaching" {{ old('staff_type', $staff->staff_type) == 'Non-Teaching' ? 'selected' : '' }}>Non-Teaching</option>
                                <option value="Permanent" {{ old('staff_type', $staff->staff_type) == 'Permanent' ? 'selected' : '' }}>Permanent</option>
                                <option value="Contract" {{ old('staff_type', $staff->staff_type) == 'Contract' ? 'selected' : '' }}>Contract</option>
                                <option value="Temporary" {{ old('staff_type', $staff->staff_type) == 'Temporary' ? 'selected' : '' }}>Temporary</option>
                                <option value="Intern" {{ old('staff_type', $staff->staff_type) == 'Intern' ? 'selected' : '' }}>Intern</option>
                            </select>
                        </div>

                        <div class="col-md-3">
                            <label class="form-label fw-semibold small text-muted">
                                Status <span class="text-danger">*</span>
                            </label>
                            <select name="status" class="form-select" required>
                                <option value="">Select Status</option>
                                <option value="Active" {{ old('status', $staff->status) == 'Active' ? 'selected' : '' }}>Active</option>
                                <option value="Inactive" {{ old('status', $staff->status) == 'Inactive' ? 'selected' : '' }}>Inactive</option>
                                <option value="On Leave" {{ old('status', $staff->status) == 'On Leave' ? 'selected' : '' }}>On Leave</option>
                                <option value="Suspended" {{ old('status', $staff->status) == 'Suspended' ? 'selected' : '' }}>Suspended</option>
                                <option value="Terminated" {{ old('status', $staff->status) == 'Terminated' ? 'selected' : '' }}>Terminated</option>
                            </select>
                        </div>

                        <div class="col-md-3">
                            <label class="form-label fw-semibold small text-muted">Salary (GHS)</label>
                            <div class="input-group">
                                <span class="input-group-text">₵</span>
                                <input type="number" step="0.01" name="salary"
                                       class="form-control"
                                       value="{{ old('salary', $staff->salary) }}"
                                       placeholder="0.00">
                            </div>
                        </div>
                    </div>
                </div>

                {{-- PROFILE PHOTO --}}
                <div class="mb-4">
                    <h6 class="fw-semibold text-dark border-bottom pb-2 mb-3">
                        <i class="fas fa-camera me-2 text-dark"></i>Profile Photo
                    </h6>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold small text-muted">Upload New Photo</label>
                            <input type="file" name="photo" class="form-control" accept="image/*">
                            <div class="form-text text-muted small">
                                <i class="fas fa-info-circle me-1"></i> 
                                Allowed formats: JPG, JPEG, PNG. Max size: 2MB
                            </div>
                        </div>
                        
                        @if($staff->photo && file_exists(public_path('storage/uploads/staff/'.$staff->photo)))
                            <div class="col-md-6">
                                <label class="form-label fw-semibold small text-muted">Current Photo</label>
                                <div>
                                    <img src="{{ asset('storage/uploads/staff/'.$staff->photo) }}" 
                                         alt="Current Photo" 
                                         class="rounded-3 border shadow-sm"
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

                {{-- ACTION BUTTONS --}}
                <div class="d-flex justify-content-end gap-2 border-top pt-4 mt-3">
                    <button type="submit" class="btn btn-danger text-white px-5">
                        <i class="fas fa-save me-1"></i> Update Staff
                    </button>
                </div>

            </form>
        </div>
    </div>
</div>

<style>
    .form-label {
        font-size: 0.8rem;
        margin-bottom: 0.3rem;
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
    
    .card-header {
        background: linear-gradient(135deg, #f8f9fa 0%, #ffffff 100%);
    }
    
    .btn {
        border-radius: 8px;
        font-weight: 500;
    }
    
    .alert {
        border-radius: 12px;
    }
</style>

@endsection