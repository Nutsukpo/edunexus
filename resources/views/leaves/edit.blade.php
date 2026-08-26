@extends('layouts.master')

@section('title', 'Edit Leave Application')

@section('content')
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Leave</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary-color: #1a73e8;
            --primary-light: #e8f0fe;
            --primary-dark: #1557b0;
            --primary-gradient: linear-gradient(135deg, #1a73e8 0%, #0d47a1 100%);
            --text-primary: #202124;
            --text-secondary: #5f6368;
            --bg-light: #f5f9ff;
            --shadow-color: rgba(26, 115, 232, 0.15);
            --border-color: #d4e4ff;
        }
        
        body {
            background-color: var(--bg-light);
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        .card {
            border: none;
            border-radius: 16px;
            box-shadow: 0 4px 30px var(--shadow-color);
            overflow: hidden;
        }
        
        .card-header {
            background: var(--primary-gradient);
            color: white;
            border: none;
            padding: 1.5rem 2rem;
        }
        
        .card-header h4 i {
            margin-right: 0.5rem;
        }
        
        .card-body {
            padding: 2rem;
        }
        
        .section-title {
            color: var(--primary-color);
            font-weight: 600;
            font-size: 1rem;
            margin-bottom: 1.5rem;
            padding-bottom: 0.5rem;
            border-bottom: 2px solid var(--primary-light);
            position: relative;
        }
        
        .section-title::after {
            content: '';
            position: absolute;
            bottom: -2px;
            left: 0;
            width: 40px;
            height: 3px;
            background: var(--primary-gradient);
            border-radius: 3px;
        }
        
        .section-title i {
            margin-right: 0.5rem;
        }
        
        .form-label {
            font-weight: 500;
            font-size: 0.9rem;
            color: var(--text-primary);
        }
        
        .form-label.required::after {
            content: " *";
            color: #dc3545;
        }
        
        .form-control, .form-select {
            border-radius: 10px;
            padding: 0.7rem 1rem;
            border: 1.5px solid var(--border-color);
            transition: all 0.3s ease;
            font-size: 0.95rem;
        }
        
        .form-control:focus, .form-select:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 4px rgba(26, 115, 232, 0.15);
        }
        
        .form-control-lg {
            padding: 0.8rem 1.2rem;
            font-size: 1rem;
        }
        
        .input-group-text {
            background-color: #f8faff;
            border: 1.5px solid var(--border-color);
            color: var(--text-secondary);
        }
        
        .input-group .form-control {
            border-left: none;
        }
        
        .btn-primary {
            background: var(--primary-gradient);
            border: none;
            border-radius: 10px;
            padding: 0.7rem 2rem;
            font-weight: 600;
            transition: all 0.3s ease;
            color: white;
        }
        
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(26, 115, 232, 0.35);
            color: white;
        }
        
        .btn-secondary {
            border-radius: 10px;
            padding: 0.7rem 2rem;
            font-weight: 500;
            border: 1.5px solid var(--border-color);
            color: var(--text-secondary);
            background: white;
        }
        
        .btn-secondary:hover {
            border-color: var(--primary-color);
            color: var(--primary-color);
            background: var(--primary-light);
        }
        
        .btn-danger {
            border-radius: 10px;
            padding: 0.7rem 2rem;
            font-weight: 600;
        }
        
        .info-box {
            background: var(--primary-light);
            border-radius: 10px;
            padding: 1rem 1.5rem;
            border-left: 4px solid var(--primary-color);
            margin-bottom: 1.5rem;
        }
        
        .info-box i {
            color: var(--primary-color);
            margin-right: 0.5rem;
        }
        
        .calculation-hint {
            font-size: 0.8rem;
            color: var(--text-secondary);
            margin-top: 0.25rem;
        }
        
        .calculation-hint i {
            color: var(--primary-color);
        }
        
        @media (max-width: 768px) {
            .card-body {
                padding: 1.25rem;
            }
            
            .card-header {
                padding: 1.25rem;
            }
            
            .btn {
                width: 100%;
                margin-bottom: 0.5rem;
            }
            
            .d-flex.gap-3 {
                flex-direction: column;
            }
        }
    </style>
</head>
<body>
    <div class="container-fluid py-4">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <div>
                            <h4 class="mb-0 fw-bold">
                                <i class="fas fa-edit me-2"></i>Edit Leave Application
                            </h4>
                            <p class="mb-0 mt-1 opacity-75">Reference: #LEAVE-{{ str_pad($leave->id, 6, '0', STR_PAD_LEFT) }}</p>
                        </div>
                        <div>
                            <span class="badge bg-light text-dark">
                                <i class="fas fa-circle me-1" style="color: {{ $leave->status === 'draft' ? '#6c757d' : '#ffc107' }};"></i>
                                {{ ucfirst($leave->status) }}
                            </span>
                        </div>
                    </div>

                    <div class="card-body">
                        @if($errors->any())
                            <div class="alert alert-danger">
                                <ul class="mb-0">
                                    @foreach($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        <div class="info-box">
                            <i class="fas fa-info-circle"></i>
                            <strong>Note:</strong> You can only edit draft or pending leave applications.
                            @if($leave->status === 'pending')
                                <span class="ms-2 text-warning">This application is pending approval.</span>
                            @endif
                        </div>

                        <form action="{{ route('leaves.update', $leave->id) }}" method="POST">
                            @csrf
                            @method('PUT')

                            <!-- Personal Information -->
                            <div class="mb-4">
                                <h6 class="section-title"><i class="fas fa-user-circle"></i>Personal Information</h6>
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label for="fullName" class="form-label required">Full Name</label>
                                        <input type="text" id="fullName" name="full_name" 
                                               class="form-control form-control-lg @error('full_name') is-invalid @enderror" 
                                               value="{{ old('full_name', $leave->full_name) }}" required>
                                        @error('full_name')
                                            <span class="invalid-feedback">{{ $message }}</span>
                                        @enderror
                                    </div>
                                    <div class="col-md-6">
                                        <label for="designation" class="form-label">Designation</label>
                                        <input type="text" id="designation" name="designation" 
                                               class="form-control form-control-lg @error('designation') is-invalid @enderror" 
                                               value="{{ old('designation', $leave->designation) }}">
                                        @error('designation')
                                            <span class="invalid-feedback">{{ $message }}</span>
                                        @enderror
                                    </div>
                                    <div class="col-md-6">
                                        <label for="contactNumber" class="form-label">Contact Number</label>
                                        <div class="input-group">
                                            <span class="input-group-text">+233</span>
                                            <input type="tel" id="contactNumber" name="contact_number" 
                                                   class="form-control form-control-lg @error('contact_number') is-invalid @enderror" 
                                                   value="{{ old('contact_number', $leave->contact_number) }}">
                                        </div>
                                        @error('contact_number')
                                            <span class="invalid-feedback">{{ $message }}</span>
                                        @enderror
                                    </div>
                                    <div class="col-md-6">
                                        <label for="leaveType" class="form-label required">Leave Type</label>
                                        <select id="leaveType" name="leave_type" 
                                                class="form-select form-select-lg @error('leave_type') is-invalid @enderror" required>
                                            <option value="" disabled>— Select leave type —</option>
                                            <option value="Annual leave" {{ old('leave_type', $leave->leave_type) == 'Annual leave' ? 'selected' : '' }}>Annual leave</option>
                                            <option value="Part leave" {{ old('leave_type', $leave->leave_type) == 'Part leave' ? 'selected' : '' }}>Part leave</option>
                                            <option value="Maternity leave" {{ old('leave_type', $leave->leave_type) == 'Maternity leave' ? 'selected' : '' }}>Maternity leave</option>
                                            <option value="Sick leave" {{ old('leave_type', $leave->leave_type) == 'Sick leave' ? 'selected' : '' }}>Sick leave</option>
                                            <option value="Unpaid leave" {{ old('leave_type', $leave->leave_type) == 'Unpaid leave' ? 'selected' : '' }}>Unpaid leave</option>
                                            <option value="Leave of Absence" {{ old('leave_type', $leave->leave_type) == 'Leave of Absence' ? 'selected' : '' }}>Leave of Absence</option>
                                            <option value="Funeral-Relationship" {{ old('leave_type', $leave->leave_type) == 'Funeral-Relationship' ? 'selected' : '' }}>Funeral-Relationship</option>
                                            <option value="Vacation leave" {{ old('leave_type', $leave->leave_type) == 'Vacation leave' ? 'selected' : '' }}>Vacation leave</option>
                                        </select>
                                        @error('leave_type')
                                            <span class="invalid-feedback">{{ $message }}</span>
                                        @enderror
                                    </div>
                                    <div class="col-12">
                                        <label for="reason" class="form-label">Reason for Leave</label>
                                        <textarea id="reason" name="reason" 
                                                  class="form-control @error('reason') is-invalid @enderror" 
                                                  rows="2">{{ old('reason', $leave->reason) }}</textarea>
                                        @error('reason')
                                            <span class="invalid-feedback">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            <!-- Leave Period -->
                            <div class="mb-4">
                                <h6 class="section-title"><i class="fas fa-calendar-alt"></i>Leave Period</h6>
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label for="dateCommencement" class="form-label required">Date of Commencement</label>
                                        <input type="date" id="dateCommencement" name="date_commencement" 
                                               class="form-control form-control-lg @error('date_commencement') is-invalid @enderror" 
                                               value="{{ old('date_commencement', $leave->date_commencement ? $leave->date_commencement->format('Y-m-d') : '') }}" required>
                                        @error('date_commencement')
                                            <span class="invalid-feedback">{{ $message }}</span>
                                        @enderror
                                    </div>
                                    <div class="col-md-6">
                                        <label for="dateResumption" class="form-label required">Date of Resumption</label>
                                        <input type="date" id="dateResumption" name="date_resumption" 
                                               class="form-control form-control-lg @error('date_resumption') is-invalid @enderror" 
                                               value="{{ old('date_resumption', $leave->date_resumption ? $leave->date_resumption->format('Y-m-d') : '') }}" required>
                                        @error('date_resumption')
                                            <span class="invalid-feedback">{{ $message }}</span>
                                        @enderror
                                    </div>
                                    <div class="col-md-6">
                                        <label for="daysApplied" class="form-label">Days Applied For</label>
                                        <input type="number" id="daysApplied" name="days_applied_for" 
                                               class="form-control form-control-lg bg-light @error('days_applied_for') is-invalid @enderror" 
                                               value="{{ old('days_applied_for', $leave->days_applied_for) }}" readonly>
                                        <div class="calculation-hint"><i class="fas fa-calculator me-1"></i>Auto-calculated based on dates</div>
                                        @error('days_applied_for')
                                            <span class="invalid-feedback">{{ $message }}</span>
                                        @enderror
                                    </div>
                                    <div class="col-md-6">
                                        <label for="date_of_application" class="form-label required">Date of Application</label>
                                        <input type="date" id="date_of_application" name="date_of_application" 
                                               class="form-control form-control-lg @error('date_of_application') is-invalid @enderror" 
                                               value="{{ old('date_of_application', $leave->date_of_application ? $leave->date_of_application->format('Y-m-d') : date('Y-m-d')) }}" required>
                                        @error('date_of_application')
                                            <span class="invalid-feedback">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            <!-- Previous Leave Info -->
                            <div class="mb-4">
                                <h6 class="section-title"><i class="fas fa-history"></i>Previous Leave Information</h6>
                                <div class="row g-3">
                                    <div class="col-md-4">
                                        <label for="dateLastLeave" class="form-label">Date of Last Leave</label>
                                        <input type="date" id="dateLastLeave" name="date_last_leave" 
                                               class="form-control form-control-lg @error('date_last_leave') is-invalid @enderror" 
                                               value="{{ old('date_last_leave', $leave->date_last_leave ? $leave->date_last_leave->format('Y-m-d') : '') }}">
                                        @error('date_last_leave')
                                            <span class="invalid-feedback">{{ $message }}</span>
                                        @enderror
                                    </div>
                                    <div class="col-md-4">
                                        <label for="daysEntitled" class="form-label">Days Entitled</label>
                                        <input type="number" id="daysEntitled" name="days_entitled" 
                                               class="form-control form-control-lg @error('days_entitled') is-invalid @enderror" 
                                               value="{{ old('days_entitled', $leave->days_entitled) }}" min="0">
                                        @error('days_entitled')
                                            <span class="invalid-feedback">{{ $message }}</span>
                                        @enderror
                                    </div>
                                    <div class="col-md-4">
                                        <label for="daysUtilized" class="form-label">Days Already Utilized</label>
                                        <input type="number" id="daysUtilized" name="days_already_utilized" 
                                               class="form-control form-control-lg @error('days_already_utilized') is-invalid @enderror" 
                                               value="{{ old('days_already_utilized', $leave->days_already_utilized) }}" min="0">
                                        @error('days_already_utilized')
                                            <span class="invalid-feedback">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            <div class="d-flex justify-content-end gap-3 mt-4">
                                <a href="{{ route('leaves.watch', $leave->id) }}" class="btn btn-secondary btn-lg px-4">
                                    <i class="fas fa-times me-2"></i>Cancel
                                </a>
                                <button type="submit" class="btn btn-primary btn-lg px-4">
                                    <i class="fas fa-save me-2"></i>Update Application
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Auto-calculate days applied
        const commencementDate = document.getElementById('dateCommencement');
        const resumptionDate = document.getElementById('dateResumption');
        const daysApplied = document.getElementById('daysApplied');

        function calculateDays() {
            if (commencementDate.value && resumptionDate.value) {
                const start = new Date(commencementDate.value);
                const end = new Date(resumptionDate.value);
                
                if (end <= start) {
                    alert('Resumption date must be after commencement date');
                    resumptionDate.value = '';
                    daysApplied.value = '';
                    return;
                }
                
                const diffTime = Math.abs(end - start);
                daysApplied.value = Math.ceil(diffTime / (1000 * 60 * 60 * 24));
            }
        }

        commencementDate.addEventListener('change', calculateDays);
        resumptionDate.addEventListener('change', calculateDays);
    </script>
</body>
</html>
@endsection