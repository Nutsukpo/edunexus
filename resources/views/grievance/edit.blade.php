@extends('layouts.master')

@section('title', 'Edit Grievance')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <h3 class="card-title mb-0">
                            <i class="fas fa-edit text-warning"></i> Edit Grievance
                        </h3>
                        <div>
                            <a href="{{ route('grievance.show', $grievance->id) }}" class="btn btn-secondary btn-sm">
                                <i class="fas fa-arrow-left"></i> Back
                            </a>
                        </div>
                    </div>
                </div>
                <!-- /.card-header -->
                <form action="{{ route('grievance.update', $grievance->id) }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')
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

                        <!-- Grievance Information -->
                        <div class="row">
                            <div class="col-md-8">
                                <div class="form-group">
                                    <label for="title">Grievance Title <span class="text-danger">*</span></label>
                                    <input type="text" name="title" id="title" 
                                           class="form-control @error('title') is-invalid @enderror" 
                                           value="{{ old('title', $grievance->title) }}" 
                                           placeholder="Enter a clear and concise title" required>
                                    @error('title')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="priority">Priority Level <span class="text-danger">*</span></label>
                                    <select name="priority" id="priority" 
                                            class="form-control @error('priority') is-invalid @enderror" required>
                                        <option value="low" {{ old('priority', $grievance->priority) == 'low' ? 'selected' : '' }}>Low</option>
                                        <option value="medium" {{ old('priority', $grievance->priority) == 'medium' ? 'selected' : '' }}>Medium</option>
                                        <option value="high" {{ old('priority', $grievance->priority) == 'high' ? 'selected' : '' }}>High</option>
                                        <option value="urgent" {{ old('priority', $grievance->priority) == 'urgent' ? 'selected' : '' }}>Urgent</option>
                                    </select>
                                    @error('priority')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label for="category_id">Category</label>
                                    <select name="category_id" id="category_id" 
                                            class="form-control @error('category_id') is-invalid @enderror">
                                        <option value="">Select Category</option>
                                        @foreach($categories as $category)
                                            <option value="{{ $category->id }}" {{ old('category_id', $grievance->category_id) == $category->id ? 'selected' : '' }}>
                                                {{ $category->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('category_id')
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
                                              placeholder="Provide a detailed description of your grievance." required>{{ old('description', $grievance->description) }}</textarea>
                                    @error('description')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                    <small class="form-text text-muted">
                                        <i class="fas fa-info-circle"></i> Be as detailed as possible. This will help us investigate thoroughly.
                                    </small>
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
                                                               value="1" {{ old('is_confidential', $grievance->is_confidential) ? 'checked' : '' }}>
                                                        <label class="custom-control-label" for="is_confidential">
                                                            <i class="fas fa-shield-alt text-success"></i> 
                                                            <strong>Confidential</strong>
                                                            <br>
                                                            <small class="text-muted">Your identity will be protected</small>
                                                        </label>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <div class="custom-control custom-switch">
                                                        <input type="checkbox" name="is_anonymous" 
                                                               class="custom-control-input" id="is_anonymous" 
                                                               value="1" {{ old('is_anonymous', $grievance->is_anonymous) ? 'checked' : '' }}>
                                                        <label class="custom-control-label" for="is_anonymous">
                                                            <i class="fas fa-user-secret text-warning"></i> 
                                                            <strong>Submit Anonymously</strong>
                                                            <br>
                                                            <small class="text-muted">Your name will not be disclosed</small>
                                                        </label>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Current Attachments -->
                        @if($grievance->attachment || ($grievance->attachments && count($grievance->attachments) > 0))
                        <div class="row mt-3">
                            <div class="col-md-12">
                                <div class="alert alert-info">
                                    <i class="fas fa-paperclip"></i>
                                    <strong>Current Attachments:</strong>
                                    <div class="mt-2">
                                        @if($grievance->attachment)
                                            <a href="{{ Storage::url($grievance->attachment) }}" target="_blank" class="btn btn-sm btn-info">
                                                <i class="fas fa-file"></i> {{ basename($grievance->attachment) }}
                                            </a>
                                        @endif
                                        @if($grievance->attachments && count($grievance->attachments) > 0)
                                            @foreach($grievance->attachments as $file)
                                                <a href="{{ Storage::url($file) }}" target="_blank" class="btn btn-sm btn-info ml-1">
                                                    <i class="fas fa-file"></i> {{ basename($file) }}
                                                </a>
                                            @endforeach
                                        @endif
                                    </div>
                                    <small class="text-muted mt-2 d-block">
                                        <i class="fas fa-info-circle"></i> Upload new files to replace existing ones
                                    </small>
                                </div>
                            </div>
                        </div>
                        @endif

                        <!-- Attachments -->
                        <div class="row mt-3">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="attachment">Single Attachment (Optional)</label>
                                    <div class="custom-file">
                                        <input type="file" name="attachment" id="attachment" 
                                               class="custom-file-input @error('attachment') is-invalid @enderror">
                                        <label class="custom-file-label" for="attachment">Choose file</label>
                                    </div>
                                    <small class="form-text text-muted">
                                        <i class="fas fa-info-circle"></i> Max size: 10MB. Allowed: PDF, DOC, DOCX, JPG, PNG
                                    </small>
                                    @error('attachment')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="attachments">Multiple Attachments (Optional)</label>
                                    <div class="custom-file">
                                        <input type="file" name="attachments[]" id="attachments" 
                                               class="custom-file-input @error('attachments') is-invalid @enderror" multiple>
                                        <label class="custom-file-label" for="attachments">Choose multiple files</label>
                                    </div>
                                    <small class="form-text text-muted">
                                        <i class="fas fa-info-circle"></i> Max size: 10MB per file
                                    </small>
                                    @error('attachments')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <!-- Status Information -->
                        <div class="row mt-4">
                            <div class="col-md-12">
                                <div class="alert alert-secondary">
                                    <i class="fas fa-info-circle"></i>
                                    <strong>Current Status:</strong> 
                                    <span class="badge badge-{{ $grievance->status_badge }} badge-lg">
                                        {{ $grievance->status_label }}
                                    </span>
                                    <span class="ml-3">
                                        <i class="fas fa-calendar-alt"></i>
                                        Submitted: {{ $grievance->submission_date ? $grievance->submission_date->format('d/m/Y') : 'N/A' }}
                                    </span>
                                    @if($grievance->assigned_to)
                                        <span class="ml-3">
                                            <i class="fas fa-user-check"></i>
                                            Assigned To: {{ $grievance->assignedTo->full_name ?? 'N/A' }}
                                        </span>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- /.card-body -->

                    <div class="card-footer">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Update Grievance
                        </button>
                        <a href="{{ route('grievance.show', $grievance->id) }}" class="btn btn-secondary">
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
        $('#description').on('input', function() {
            var maxLength = 5000;
            var currentLength = $(this).val().length;
            if (currentLength > maxLength) {
                $(this).val($(this).val().substring(0, maxLength));
            }
        });
    });
</script>
@endpush

@push('styles')
<style>
    .custom-file-label::after {
        content: "Browse";
    }
    .custom-control-label {
        cursor: pointer;
    }
    .custom-control-label small {
        display: block;
        padding-left: 0;
    }
    .custom-control-label .fas {
        font-size: 1.1rem;
        margin-right: 5px;
    }
    .badge-lg {
        padding: 8px 12px;
        font-size: 0.9rem;
    }
</style>
@endpush