@extends('layouts.master')

@section('title', 'Upload Assessment Form')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <!-- Page Header -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h4 class="mb-0 text-dark font-weight-bold">
                        <i class="fas fa-upload text-primary mr-2"></i>Upload Assessment Form
                    </h4>
                    <small class="text-muted">Create and publish new assessment forms for students</small>
                </div>
                <div>
                    <a href="{{ route('assessment-forms.index') }}" class="btn btn-secondary">
                        <i class="fas fa-arrow-left mr-2"></i>Back to List
                    </a>
                </div>
            </div>

            <div class="card shadow-sm">
                <div class="card-body">
                    <form action="{{ route('assessment-forms.store') }}" method="POST" enctype="multipart/form-data" id="uploadForm">
                        @csrf

                        <!-- Error Alert -->
                        @if($errors->any())
                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                <h6 class="mb-2"><i class="fas fa-exclamation-circle mr-2"></i>Please fix the following errors:</h6>
                                <ul class="mb-0 pl-3">
                                    @foreach($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                            </div>
                        @endif

                        <!-- Progress Indicator -->
                        <div class="row mb-4">
                            <div class="col-12">
                                <div class="d-flex justify-content-between">
                                    <div class="step-item active">
                                        <span class="step-number">1</span>
                                        <span class="step-label">Basic Info</span>
                                    </div>
                                    <div class="step-item">
                                        <span class="step-number">2</span>
                                        <span class="step-label">Assessment Details</span>
                                    </div>
                                    <div class="step-item">
                                        <span class="step-number">3</span>
                                        <span class="step-label">Upload File</span>
                                    </div>
                                </div>
                                <div class="progress" style="height: 4px;">
                                    <div class="progress-bar bg-primary" role="progressbar" style="width: 33%;" aria-valuenow="33" aria-valuemin="0" aria-valuemax="100"></div>
                                </div>
                            </div>
                        </div>

                        <!-- Section 1: Basic Information -->
                        <div class="card bg-light mb-4">
                            <div class="card-header bg-transparent">
                                <h5 class="mb-0">
                                    <i class="fas fa-info-circle text-primary mr-2"></i>
                                    Basic Information
                                </h5>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="staff_id" class="font-weight-bold">
                                                Staff <span class="text-danger">*</span>
                                            </label>
                                            <div class="input-group">
                                                <div class="input-group-prepend">
                                                    <span class="input-group-text bg-white">
                                                        <i class="fas fa-user-tie text-primary"></i>
                                                    </span>
                                                </div>
                                                <select name="staff_id" id="staff_id" 
                                                        class="form-control @error('staff_id') is-invalid @enderror" required>
                                                    <option value="">Select Staff</option>
                                                    @foreach($staff as $s)
                                                        <option value="{{ $s->id }}" {{ old('staff_id') == $s->id ? 'selected' : '' }}>
                                                            {{ $s->full_name ?? $s->name }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            @error('staff_id')
                                                <span class="invalid-feedback d-block">{{ $message }}</span>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="student_class_id" class="font-weight-bold">
                                                Class <span class="text-danger">*</span>
                                            </label>
                                            <div class="input-group">
                                                <div class="input-group-prepend">
                                                    <span class="input-group-text bg-white">
                                                        <i class="fas fa-users text-primary"></i>
                                                    </span>
                                                </div>
                                                <select name="student_class_id" id="student_class_id" 
                                                        class="form-control @error('student_class_id') is-invalid @enderror" required>
                                                    <option value="">Select Class</option>
                                                    @foreach($classes as $class)
                                                        <option value="{{ $class->id }}" {{ old('student_class_id') == $class->id ? 'selected' : '' }}>
                                                            {{ $class->name }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            @error('student_class_id')
                                                <span class="invalid-feedback d-block">{{ $message }}</span>
                                            @enderror
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Section 2: Academic Information -->
                        <div class="card bg-light mb-4">
                            <div class="card-header bg-transparent">
                                <h5 class="mb-0">
                                    <i class="fas fa-graduation-cap text-primary mr-2"></i>
                                    Academic Information
                                </h5>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="academic_year_id" class="font-weight-bold">
                                                Academic Year <span class="text-danger">*</span>
                                            </label>
                                            <div class="input-group">
                                                <div class="input-group-prepend">
                                                    <span class="input-group-text bg-white">
                                                        <i class="fas fa-calendar text-primary"></i>
                                                    </span>
                                                </div>
                                                <select name="academic_year_id" id="academic_year_id" 
                                                        class="form-control @error('academic_year_id') is-invalid @enderror" required>
                                                    <option value="">Select Year</option>
                                                    @foreach($academicYears as $year)
                                                        <option value="{{ $year->id }}" {{ old('academic_year_id') == $year->id ? 'selected' : '' }}>
                                                            {{ $year->name }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            @error('academic_year_id')
                                                <span class="invalid-feedback d-block">{{ $message }}</span>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="term_id" class="font-weight-bold">
                                                Term <span class="text-danger">*</span>
                                            </label>
                                            <div class="input-group">
                                                <div class="input-group-prepend">
                                                    <span class="input-group-text bg-white">
                                                        <i class="fas fa-clock text-primary"></i>
                                                    </span>
                                                </div>
                                                <select name="term_id" id="term_id" 
                                                        class="form-control @error('term_id') is-invalid @enderror" required>
                                                    <option value="">Select Term</option>
                                                    @foreach($terms as $term)
                                                        <option value="{{ $term->id }}" {{ old('term_id') == $term->id ? 'selected' : '' }}>
                                                            {{ $term->name }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            @error('term_id')
                                                <span class="invalid-feedback d-block">{{ $message }}</span>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="subject_id" class="font-weight-bold">Subject</label>
                                            <div class="input-group">
                                                <div class="input-group-prepend">
                                                    <span class="input-group-text bg-white">
                                                        <i class="fas fa-book text-primary"></i>
                                                    </span>
                                                </div>
                                                <select name="subject_id" id="subject_id" 
                                                        class="form-control @error('subject_id') is-invalid @enderror">
                                                    <option value="">Select Subject (Optional)</option>
                                                    @foreach($subjects as $subject)
                                                        <option value="{{ $subject->id }}" {{ old('subject_id') == $subject->id ? 'selected' : '' }}>
                                                            {{ $subject->name }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            @error('subject_id')
                                                <span class="invalid-feedback d-block">{{ $message }}</span>
                                            @enderror
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Section 3: Assessment Details -->
                        <div class="card bg-light mb-4">
                            <div class="card-header bg-transparent">
                                <h5 class="mb-0">
                                    <i class="fas fa-file-alt text-primary mr-2"></i>
                                    Assessment Details
                                </h5>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="assessment_type" class="font-weight-bold">
                                                Assessment Type <span class="text-danger">*</span>
                                            </label>
                                            <div class="input-group">
                                                <div class="input-group-prepend">
                                                    <span class="input-group-text bg-white">
                                                        <i class="fas fa-tag text-primary"></i>
                                                    </span>
                                                </div>
                                                <select name="assessment_type" id="assessment_type" 
                                                        class="form-control @error('assessment_type') is-invalid @enderror" required>
                                                    <option value="">Select Type</option>
                                                    <option value="quiz" {{ old('assessment_type') == 'quiz' ? 'selected' : '' }}>📝 Quiz</option>
                                                    <option value="test" {{ old('assessment_type') == 'test' ? 'selected' : '' }}>📋 Test</option>
                                                    <option value="exam" {{ old('assessment_type') == 'exam' ? 'selected' : '' }}>📊 Exam</option>
                                                    <option value="assignment" {{ old('assessment_type') == 'assignment' ? 'selected' : '' }}>📄 Assignment</option>
                                                    <option value="project" {{ old('assessment_type') == 'project' ? 'selected' : '' }}>📁 Project</option>
                                                </select>
                                            </div>
                                            @error('assessment_type')
                                                <span class="invalid-feedback d-block">{{ $message }}</span>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="title" class="font-weight-bold">Title</label>
                                            <div class="input-group">
                                                <div class="input-group-prepend">
                                                    <span class="input-group-text bg-white">
                                                        <i class="fas fa-heading text-primary"></i>
                                                    </span>
                                                </div>
                                                <input type="text" name="title" id="title" 
                                                       class="form-control @error('title') is-invalid @enderror" 
                                                       value="{{ old('title') }}" 
                                                       placeholder="Enter a descriptive title">
                                            </div>
                                            <small class="form-text text-muted">Optional but recommended for better organization</small>
                                            @error('title')
                                                <span class="invalid-feedback d-block">{{ $message }}</span>
                                            @enderror
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="assessment_date" class="font-weight-bold">
                                                Assessment Date <span class="text-danger">*</span>
                                            </label>
                                            <div class="input-group">
                                                <div class="input-group-prepend">
                                                    <span class="input-group-text bg-white">
                                                        <i class="fas fa-calendar-day text-primary"></i>
                                                    </span>
                                                </div>
                                                <input type="date" name="assessment_date" id="assessment_date" 
                                                       class="form-control @error('assessment_date') is-invalid @enderror" 
                                                       value="{{ old('assessment_date', date('Y-m-d')) }}" required>
                                            </div>
                                            @error('assessment_date')
                                                <span class="invalid-feedback d-block">{{ $message }}</span>
                                            @enderror
                                        </div>
                                    </div>
                                   
                                </div>

                                
                            </div>
                        </div>

                        <!-- Section 4: File Upload -->
                        <div class="card bg-light mb-4">
                            <div class="card-header bg-transparent">
                                <h5 class="mb-0">
                                    <i class="fas fa-cloud-upload-alt text-primary mr-2"></i>
                                    File Upload
                                </h5>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-8">
                                        <div class="form-group">
                                            <label for="file" class="font-weight-bold">
                                                File <span class="text-danger">*</span>
                                            </label>
                                            <div class="custom-file">
                                                <input type="file" name="file" id="file" 
                                                       class="custom-file-input @error('file') is-invalid @enderror" 
                                                       accept=".pdf,.jpg,.jpeg,.png,.gif,.doc,.docx" required>
                                                <label class="custom-file-label" for="file">
                                                    <i class="fas fa-cloud-upload-alt mr-2"></i>Choose file...
                                                </label>
                                            </div>
                                            <div id="filePreview" class="mt-2" style="display: none;">
                                                <div class="d-flex align-items-center">
                                                    <i class="fas fa-file-pdf fa-2x text-danger mr-2"></i>
                                                    <div>
                                                        <span id="fileName" class="font-weight-bold"></span>
                                                        <br>
                                                        <small id="fileSize" class="text-muted"></small>
                                                    </div>
                                                </div>
                                            </div>
                                            <small class="form-text text-muted">
                                                <i class="fas fa-info-circle text-primary mr-1"></i>
                                                <strong>Accepted formats:</strong> PDF, JPG, JPEG, PNG, GIF, DOC, DOCX 
                                                <span class="mx-2">|</span> 
                                                <i class="fas fa-weight-hanging text-primary mr-1"></i>
                                                <strong>Max size:</strong> 20MB
                                            </small>
                                            @error('file')
                                                <span class="invalid-feedback d-block">{{ $message }}</span>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="status" class="font-weight-bold">Status</label>
                                            <div class="btn-group w-100" role="group" aria-label="Status selection">
                                                <input type="radio" class="btn-check" name="status" id="status_draft" 
                                                       value="draft" {{ old('status', 'draft') == 'draft' ? 'checked' : '' }} autocomplete="off">
                                                <label class="btn btn-outline-secondary w-50" for="status_draft">
                                                    <i class="fas fa-pencil-alt mr-1"></i>Draft
                                                </label>
                                                
                                                <input type="radio" class="btn-check" name="status" id="status_published" 
                                                       value="published" {{ old('status') == 'published' ? 'checked' : '' }} autocomplete="off">
                                                <label class="btn btn-outline-success w-50" for="status_published">
                                                    <i class="fas fa-check-circle mr-1"></i>Publish
                                                </label>
                                            </div>
                                            <small class="form-text text-muted">
                                                <i class="fas fa-lightbulb text-warning mr-1"></i>
                                                Draft forms are only visible to you. Published forms are available to students.
                                            </small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Form Actions -->
                        <div class="card-footer bg-white border-top">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <span class="text-muted small">
                                        <i class="fas fa-asterisk text-danger mr-1"></i> Required fields
                                    </span>
                                </div>
                                <div>
                                    <a href="{{ route('assessment-forms.index') }}" class="btn btn-light mr-2">
                                        <i class="fas fa-times mr-2"></i>Cancel
                                    </a>
                                    <button type="submit" class="btn btn-primary" id="submitBtn">
                                        <i class="fas fa-upload mr-2"></i>
                                        <span id="btnText">Upload Form</span>
                                        <span id="btnSpinner" class="spinner-border spinner-border-sm d-none" role="status" aria-hidden="true"></span>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

@push('styles')
<style>
    /* Page Header */
    .page-header {
        border-bottom: 1px solid #dee2e6;
        padding-bottom: 1rem;
    }

    /* Step Indicator */
    .step-item {
        display: flex;
        flex-direction: column;
        align-items: center;
        flex: 1;
        position: relative;
    }
    .step-item .step-number {
        width: 35px;
        height: 35px;
        border-radius: 50%;
        background: #e9ecef;
        color: #6c757d;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: bold;
        margin-bottom: 5px;
        transition: all 0.3s;
    }
    .step-item.active .step-number {
        background: #007bff;
        color: white;
        box-shadow: 0 0 0 5px rgba(0, 123, 255, 0.2);
    }
    .step-item .step-label {
        font-size: 0.75rem;
        color: #6c757d;
        font-weight: 500;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }
    .step-item.active .step-label {
        color: #007bff;
        font-weight: 600;
    }

    /* Custom File Input */
    .custom-file-label {
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
        cursor: pointer;
    }
    .custom-file-label::after {
        content: "Browse";
    }
    .custom-file-input:lang(en) ~ .custom-file-label::after {
        content: "Browse";
    }

    /* Card Improvements */
    .card {
        border: none;
    }
    .card.bg-light {
        background-color: #f8f9fa !important;
        border: 1px solid #e9ecef;
        border-radius: 8px;
    }
    .card-header.bg-transparent {
        border-bottom: 1px solid #dee2e6;
        padding: 0.75rem 1.25rem;
    }

    /* Form Controls */
    .input-group-text {
        background-color: white;
        border-right: none;
    }
    .input-group .form-control {
        border-left: none;
    }
    .input-group .form-control:focus {
        border-color: #ced4da;
        box-shadow: none;
    }
    .input-group .form-control:focus + .input-group-append .input-group-text,
    .input-group .form-control:focus ~ .input-group-prepend .input-group-text {
        border-color: #80bdff;
    }

    /* Radio Button Group */
    .btn-check:checked + .btn-outline-secondary {
        background-color: #6c757d;
        color: white;
    }
    .btn-check:checked + .btn-outline-success {
        background-color: #28a745;
        color: white;
    }
    .btn-group .btn {
        border-radius: 0;
    }
    .btn-group .btn:first-child {
        border-radius: 0.25rem 0 0 0.25rem;
    }
    .btn-group .btn:last-child {
        border-radius: 0 0.25rem 0.25rem 0;
    }

    /* Responsive adjustments */
    @media (max-width: 768px) {
        .step-item .step-label {
            font-size: 0.6rem;
        }
        .step-item .step-number {
            width: 30px;
            height: 30px;
            font-size: 0.8rem;
        }
    }

    /* Animation for file preview */
    #filePreview {
        animation: slideDown 0.3s ease-out;
    }
    @keyframes slideDown {
        from {
            opacity: 0;
            transform: translateY(-10px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }
</style>
@endpush

@push('scripts')
<script>
$(document).ready(function() {
    // File input display with preview
    $('.custom-file-input').on('change', function() {
        var file = this.files[0];
        if (file) {
            var fileName = file.name;
            var fileSize = (file.size / 1024 / 1024).toFixed(2) + ' MB';
            var fileExtension = fileName.split('.').pop().toLowerCase();
            
            // Update label
            $(this).siblings('.custom-file-label').addClass('selected').html(
                '<i class="fas fa-file mr-2"></i>' + fileName
            );

            // Show preview
            var iconClass = 'fa-file-' + (fileExtension === 'pdf' ? 'pdf' : 
                            ['jpg','jpeg','png','gif'].includes(fileExtension) ? 'image' : 
                            'doc');
            $('#filePreview').show();
            $('#fileName').text(fileName);
            $('#fileSize').text(fileSize + ' | ' + fileExtension.toUpperCase());
            $('#filePreview i').attr('class', 'fas ' + iconClass + ' fa-2x mr-2');
        } else {
            $(this).siblings('.custom-file-label').html('Choose file...');
            $('#filePreview').hide();
        }
    });

    // Form submission with loading state
    $('#uploadForm').on('submit', function() {
        var btn = $('#submitBtn');
        var text = $('#btnText');
        var spinner = $('#btnSpinner');
        
        btn.prop('disabled', true);
        text.text('Uploading...');
        spinner.removeClass('d-none');
        
        // Re-enable after 30 seconds (safety net)
        setTimeout(function() {
            btn.prop('disabled', false);
            text.text('Upload Form');
            spinner.addClass('d-none');
        }, 30000);
    });

    // Auto-populate class based on staff selection (optional)
    $('#staff_id').on('change', function() {
        var staffId = $(this).val();
        if (staffId) {
            // You can add AJAX call here to load classes assigned to this staff
            // For now, just highlight the selection
            $('#student_class_id').focus();
        }
    });

    // Validation for due date (must be after assessment date)
    $('#assessment_date').on('change', function() {
        var assessmentDate = $(this).val();
        var dueDateInput = $('#due_date');
        if (dueDateInput.val() && dueDateInput.val() < assessmentDate) {
            dueDateInput.val(assessmentDate);
        }
        dueDateInput.attr('min', assessmentDate);
    });

    // Set min date for due date on page load
    var today = new Date().toISOString().split('T')[0];
    $('#assessment_date').attr('min', today);
    $('#due_date').attr('min', today);
});
</script>
@endpush
@endsection