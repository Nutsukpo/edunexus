@extends('layouts.master')

@section('title', 'Submit Student Grievance')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center flex-wrap">
                        <div>
                            <h3 class="card-title mb-0">
                                <i class="fas fa-plus-circle text-primary"></i> Submit Student Grievance
                            </h3>
                            <small class="text-muted d-block mt-1">
                                <i class="fas fa-info-circle"></i> 
                                Please provide detailed information about your grievance
                            </small>
                        </div>
                        <div class="mt-2 mt-md-0">
                            <a href="{{ route('student-grievance.index') }}" class="btn btn-secondary btn-sm">
                                <i class="fas fa-arrow-left"></i> Back
                            </a>
                        </div>
                    </div>
                </div>
                <!-- /.card-header -->
                
                <form action="{{ route('student-grievance.store') }}" method="POST" enctype="multipart/form-data" id="grievanceForm">
                    @csrf
                    <div class="card-body">
                        @if($errors->any())
                            <div class="alert alert-danger alert-dismissible fade show">
                                <h6><i class="fas fa-exclamation-triangle"></i> Please fix the following errors:</h6>
                                <ul class="mb-0">
                                    @foreach($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                                <button type="button" class="close" data-dismiss="alert">
                                    <span>&times;</span>
                                </button>
                            </div>
                        @endif

                        @if(session('success'))
                            <div class="alert alert-success alert-dismissible fade show">
                                <i class="fas fa-check-circle"></i> {{ session('success') }}
                                <button type="button" class="close" data-dismiss="alert">
                                    <span>&times;</span>
                                </button>
                            </div>
                        @endif

                        @if(session('error'))
                            <div class="alert alert-danger alert-dismissible fade show">
                                <i class="fas fa-exclamation-circle"></i> {{ session('error') }}
                                <button type="button" class="close" data-dismiss="alert">
                                    <span>&times;</span>
                                </button>
                            </div>
                        @endif

                        <!-- Grievance Information -->
                        <div class="row">
                            <div class="col-md-8">
                                <div class="form-group">
                                    <label for="title">Grievance Title <span class="text-danger">*</span></label>
                                    <input type="text" name="title" id="title" 
                                           class="form-control @error('title') is-invalid @enderror" 
                                           value="{{ old('title') }}" 
                                           placeholder="Enter a clear and concise title" required>
                                    @error('title')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                    <small class="form-text text-muted">
                                        <i class="fas fa-info-circle"></i> Use a clear, descriptive title that summarizes your grievance
                                    </small>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="priority">Priority Level <span class="text-danger">*</span></label>
                                    <select name="priority" id="priority" 
                                            class="form-control @error('priority') is-invalid @enderror" required>
                                        <option value="low" {{ old('priority') == 'low' ? 'selected' : '' }}>Low</option>
                                        <option value="medium" {{ old('priority') == 'medium' ? 'selected' : '' }}>Medium</option>
                                        <option value="high" {{ old('priority') == 'high' ? 'selected' : '' }}>High</option>
                                        <option value="urgent" {{ old('priority') == 'urgent' ? 'selected' : '' }}>Urgent</option>
                                    </select>
                                    @error('priority')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                    <small class="form-text text-muted">
                                        <i class="fas fa-info-circle"></i> 
                                        <span class="text-{{ old('priority', 'medium') == 'urgent' ? 'danger' : (old('priority', 'medium') == 'high' ? 'warning' : 'muted') }}">
                                            {{ old('priority', 'medium') == 'urgent' ? '⚠️ Urgent grievances will be prioritized' : (old('priority', 'medium') == 'high' ? '⚠️ High priority issues need immediate attention' : 'Select urgency level') }}
                                        </span>
                                    </small>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="category_id">Category</label>
                                    <select name="category_id" id="category_id" 
                                            class="form-control @error('category_id') is-invalid @enderror">
                                        <option value="">Select Category</option>
                                        @foreach($categories as $category)
                                            <option value="{{ $category->id }}" {{ old('category_id') == $category->id ? 'selected' : '' }}>
                                                {{ $category->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('category_id')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                    <small class="form-text text-muted">
                                        <i class="fas fa-info-circle"></i> Select the most appropriate category for your grievance
                                    </small>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="class_id">Class</label>
                                    <select name="class_id" id="class_id" 
                                            class="form-control @error('class_id') is-invalid @enderror">
                                        <option value="">Select Class</option>
                                        @foreach($classes as $class)
                                            <option value="{{ $class->id }}" {{ old('class_id') == $class->id ? 'selected' : '' }}>
                                                {{ $class->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('class_id')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label for="description">Detailed Description <span class="text-danger">*</span></label>
                                    <textarea name="description" id="description" 
                                              class="form-control @error('description') is-invalid @enderror" 
                                              rows="8" 
                                              placeholder="Provide a detailed description of your grievance. Include all relevant facts, dates, and individuals involved." required>{{ old('description') }}</textarea>
                                    @error('description')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                    <div class="d-flex justify-content-between">
                                        <small class="form-text text-muted">
                                            <i class="fas fa-info-circle"></i> Be as detailed as possible. This will help us investigate thoroughly.
                                        </small>
                                        <small class="form-text text-muted">
                                            <span id="charCount">0</span> / 5000 characters
                                        </small>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Privacy Settings -->
                        <div class="row">
                            <div class="col-md-12">
                                <div class="card card-outline card-info">
                                    <div class="card-header">
                                        <h5 class="card-title">
                                            <i class="fas fa-lock text-info"></i> Privacy Settings
                                        </h5>
                                    </div>
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <div class="custom-control custom-switch">
                                                        <input type="checkbox" name="is_confidential" 
                                                               class="custom-control-input" id="is_confidential" 
                                                               value="1" {{ old('is_confidential', true) ? 'checked' : '' }}>
                                                        <label class="custom-control-label" for="is_confidential">
                                                            <i class="fas fa-shield-alt text-success"></i> 
                                                            <strong>Confidential</strong>
                                                            <br>
                                                            <small class="text-muted">Your identity will be protected and only shared with authorized personnel</small>
                                                        </label>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <div class="custom-control custom-switch">
                                                        <input type="checkbox" name="is_anonymous" 
                                                               class="custom-control-input" id="is_anonymous" 
                                                               value="1" {{ old('is_anonymous') ? 'checked' : '' }}>
                                                        <label class="custom-control-label" for="is_anonymous">
                                                            <i class="fas fa-user-secret text-warning"></i> 
                                                            <strong>Submit Anonymously</strong>
                                                            <br>
                                                            <small class="text-muted">Your name will not be disclosed to anyone</small>
                                                        </label>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Attachments -->
                        <div class="row mt-3">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="attachment">Single Attachment</label>
                                    <div class="custom-file">
                                        <input type="file" name="attachment" id="attachment" 
                                               class="custom-file-input @error('attachment') is-invalid @enderror">
                                        <label class="custom-file-label" for="attachment">Choose file</label>
                                    </div>
                                    <small class="form-text text-muted">
                                        <i class="fas fa-info-circle"></i> Max size: 10MB. Allowed: PDF, DOC, DOCX, JPG, PNG
                                    </small>
                                    @error('attachment')
                                        <span class="invalid-feedback d-block">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="attachments">Multiple Attachments</label>
                                    <div class="custom-file">
                                        <input type="file" name="attachments[]" id="attachments" 
                                               class="custom-file-input @error('attachments') is-invalid @enderror" multiple>
                                        <label class="custom-file-label" for="attachments">Choose multiple files</label>
                                    </div>
                                    <small class="form-text text-muted">
                                        <i class="fas fa-info-circle"></i> Max size: 10MB per file
                                    </small>
                                    @error('attachments')
                                        <span class="invalid-feedback d-block">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <!-- Information Box -->
                        <div class="row mt-4">
                            <div class="col-md-12">
                                <div class="alert alert-info border-left-info">
                                    <div class="d-flex">
                                        <div class="mr-3">
                                            <i class="fas fa-info-circle fa-2x text-info"></i>
                                        </div>
                                        <div>
                                            <h6 class="alert-heading">What happens next?</h6>
                                            <ol class="mb-0 pl-3">
                                                <li>Your grievance will be reviewed by the relevant department</li>
                                                <li>You will receive a confirmation with your grievance code</li>
                                                <li>An assigned officer will investigate and respond within 5-7 working days</li>
                                                <li>You can track the progress of your grievance in your dashboard</li>
                                            </ol>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- /.card-body -->

                    <div class="card-footer">
                        <button type="submit" class="btn btn-primary" id="submitBtn">
                            <i class="fas fa-paper-plane"></i> Submit Grievance
                        </button>
                        <button type="button" class="btn btn-secondary" onclick="saveDraft()">
                            <i class="fas fa-save"></i> Save as Draft
                        </button>
                        <a href="{{ route('student-grievance.index') }}" class="btn btn-danger">
                            <i class="fas fa-times"></i> Cancel
                        </a>
                    </div>
                </form>
            </div>
            <!-- /.card -->
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    $(document).ready(function() {
        // File input display for single file
        $('.custom-file-input').on('change', function() {
            var fileName = $(this).val().split('\\').pop();
            $(this).siblings('.custom-file-label').addClass('selected').html(fileName);
        });

        // Multiple file input display
        $('#attachments').on('change', function() {
            var files = $(this).prop('files');
            var names = [];
            $.each(files, function(index, file) {
                names.push(file.name);
            });
            $(this).siblings('.custom-file-label').addClass('selected').html(names.join(', '));
        });

        // Character counter for description
        var maxLength = 5000;
        $('#description').on('input', function() {
            var currentLength = $(this).val().length;
            var remaining = maxLength - currentLength;
            
            $('#charCount').text(currentLength);
            
            if (remaining < 0) {
                $(this).val($(this).val().substring(0, maxLength));
                $('#charCount').text(maxLength);
                return;
            }
            
            // Change color when approaching limit
            if (remaining < 100) {
                $('#charCount').css('color', '#dc3545');
            } else if (remaining < 500) {
                $('#charCount').css('color', '#ffc107');
            } else {
                $('#charCount').css('color', '#6c757d');
            }
        });

        // Trigger character count on load
        $('#description').trigger('input');

        // Priority change visual feedback
        $('#priority').on('change', function() {
            var priority = $(this).val();
            var label = $(this).closest('.form-group').find('.form-text .text-muted');
            
            if (priority === 'urgent') {
                label.html('<span class="text-danger">⚠️ Urgent grievances will be prioritized</span>');
            } else if (priority === 'high') {
                label.html('<span class="text-warning">⚠️ High priority issues need immediate attention</span>');
            } else {
                label.html('Select urgency level');
            }
        });

        // Form validation before submit
        $('#grievanceForm').on('submit', function(e) {
            var title = $('#title').val().trim();
            var description = $('#description').val().trim();
            
            if (!title) {
                e.preventDefault();
                $('#title').addClass('is-invalid');
                if (typeof toastr !== 'undefined') {
                    toastr.error('Please enter a grievance title.');
                }
                return false;
            }
            
            if (!description) {
                e.preventDefault();
                $('#description').addClass('is-invalid');
                if (typeof toastr !== 'undefined') {
                    toastr.error('Please provide a detailed description.');
                }
                return false;
            }
            
            return true;
        });

        // Remove error state on input
        $('#title, #description').on('input', function() {
            $(this).removeClass('is-invalid');
        });
    });

    function saveDraft() {
        var form = $('#grievanceForm');
        
        // Add hidden field to indicate draft
        $('<input>')
            .attr('type', 'hidden')
            .attr('name', 'is_draft')
            .val('1')
            .appendTo(form);
        
        // Submit the form
        form.submit();
    }
</script>
@endpush

@push('styles')
<style>
    /* Custom File Input */
    .custom-file-label::after {
        content: "Browse";
    }
    
    .custom-file-label.selected {
        background-color: #e9ecef;
    }
    
    /* Custom Switch */
    .custom-control-label {
        cursor: pointer;
        user-select: none;
    }
    
    .custom-control-label small {
        display: block;
        padding-left: 0;
        font-weight: normal;
    }
    
    .custom-control-label .fas {
        font-size: 1.1rem;
        margin-right: 5px;
    }
    
    /* Alert Styles */
    .border-left-info {
        border-left: 4px solid #17a2b8 !important;
    }
    
    /* Form Styles */
    .form-control:focus {
        border-color: #80bdff;
        box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
    }
    
    .form-control.is-invalid:focus {
        border-color: #dc3545;
        box-shadow: 0 0 0 0.2rem rgba(220, 53, 69, 0.25);
    }
    
    /* Card Styles */
    .card-outline {
        border: 1px solid #e9ecef;
    }
    
    .card-outline .card-header {
        background-color: #f8f9fa;
        border-bottom: 1px solid #e9ecef;
    }
    
    /* Footer Button Styles */
    .card-footer .btn {
        margin-right: 5px;
    }
    
    /* Responsive */
    @media (max-width: 768px) {
        .card-footer .btn {
            width: 100%;
            margin-bottom: 5px;
        }
        
        .card-footer .btn:last-child {
            margin-bottom: 0;
        }
    }
</style>
@endpush