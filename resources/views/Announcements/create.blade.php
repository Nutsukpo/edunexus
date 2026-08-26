@extends('layouts.master')

@section('title', 'Create Announcement')

@section('content')
<div class="container-fluid py-4">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-bold mb-0">
                <i class="fas fa-plus-circle text-primary me-2"></i>
                Create Announcement
            </h4>
            <small class="text-muted">Create a new announcement or notice</small>
        </div>
        <a href="{{ route('announcements.index') }}" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left me-2"></i>
            Back to List
        </a>
    </div>

    <!-- Form -->
    <div class="card border-0 shadow-lg">
        <div class="card-body p-4">
            <form action="{{ route('announcements.store') }}" method="POST" enctype="multipart/form-data">
                @csrf

                <div class="row g-4">
                    <!-- Left Column -->
                    <div class="col-lg-8">
                        <!-- Title -->
                        <div class="mb-3">
                            <label for="title" class="form-label fw-bold">Title <span class="text-danger">*</span></label>
                            <input type="text" class="form-control form-control-lg @error('title') is-invalid @enderror" 
                                   id="title" name="title" value="{{ old('title') }}" 
                                   placeholder="Enter announcement title" required>
                            @error('title')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Content -->
                        <div class="mb-3">
                            <label for="content" class="form-label fw-bold">Content <span class="text-danger">*</span></label>
                            <textarea class="form-control @error('content') is-invalid @enderror" 
                                      id="content" name="content" rows="8" 
                                      placeholder="Enter announcement content" required>{{ old('content') }}</textarea>
                            @error('content')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Link -->
                        <div class="mb-3">
                            <label for="link" class="form-label fw-bold">External Link</label>
                            <input type="url" class="form-control @error('link') is-invalid @enderror" 
                                   id="link" name="link" value="{{ old('link') }}" 
                                   placeholder="https://example.com">
                            @error('link')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <!-- Right Column -->
                    <div class="col-lg-4">
                        <!-- Type -->
                        <div class="mb-3">
                            <label for="type" class="form-label fw-bold">Type <span class="text-danger">*</span></label>
                            <select class="form-select @error('type') is-invalid @enderror" id="type" name="type" required>
                                <option value="">Select Type</option>
                                <option value="general" {{ old('type') == 'general' ? 'selected' : '' }}>General</option>
                                <option value="academic" {{ old('type') == 'academic' ? 'selected' : '' }}>Academic</option>
                                <option value="event" {{ old('type') == 'event' ? 'selected' : '' }}>Event</option>
                                <option value="urgent" {{ old('type') == 'urgent' ? 'selected' : '' }}>Urgent</option>
                                <option value="exam" {{ old('type') == 'exam' ? 'selected' : '' }}>Exam</option>
                            </select>
                            @error('type')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Audience -->
                        <div class="mb-3">
                            <label for="audience" class="form-label fw-bold">Audience <span class="text-danger">*</span></label>
                            <select class="form-select @error('audience') is-invalid @enderror" id="audience" name="audience" required>
                                <option value="">Select Audience</option>
                                <option value="all" {{ old('audience') == 'all' ? 'selected' : '' }}>Everyone</option>
                                <option value="students" {{ old('audience') == 'students' ? 'selected' : '' }}>Students</option>
                                <option value="staff" {{ old('audience') == 'staff' ? 'selected' : '' }}>Staff</option>
                                <option value="parents" {{ old('audience') == 'parents' ? 'selected' : '' }}>Parents</option>
                                <option value="teachers" {{ old('audience') == 'teachers' ? 'selected' : '' }}>Teachers</option>
                            </select>
                            @error('audience')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Priority -->
                        <div class="mb-3">
                            <label for="priority" class="form-label fw-bold">Priority <span class="text-danger">*</span></label>
                            <select class="form-select @error('priority') is-invalid @enderror" id="priority" name="priority" required>
                                <option value="">Select Priority</option>
                                <option value="low" {{ old('priority') == 'low' ? 'selected' : '' }}>Low</option>
                                <option value="normal" {{ old('priority') == 'normal' ? 'selected' : '' }}>Normal</option>
                                <option value="high" {{ old('priority') == 'high' ? 'selected' : '' }}>High</option>
                                <option value="urgent" {{ old('priority') == 'urgent' ? 'selected' : '' }}>Urgent</option>
                            </select>
                            @error('priority')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Dates -->
                        <div class="mb-3">
                            <label for="publish_date" class="form-label fw-bold">Publish Date</label>
                            <input type="date" class="form-control @error('publish_date') is-invalid @enderror" 
                                   id="publish_date" name="publish_date" value="{{ old('publish_date') }}">
                            @error('publish_date')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="expiry_date" class="form-label fw-bold">Expiry Date</label>
                            <input type="date" class="form-control @error('expiry_date') is-invalid @enderror" 
                                   id="expiry_date" name="expiry_date" value="{{ old('expiry_date') }}">
                            @error('expiry_date')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Image Upload -->
                        <div class="mb-3">
                            <label for="image" class="form-label fw-bold">Featured Image</label>
                            <input type="file" class="form-control @error('image') is-invalid @enderror" 
                                   id="image" name="image" accept="image/*">
                            <small class="text-muted">Max 2MB. Supported: JPG, PNG, GIF</small>
                            @error('image')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- ======================================== -->
                        <!-- PUBLISH AND FEATURED CHECKBOXES -->
                        <!-- ======================================== -->
                        <div class="card bg-light border-0 mb-3">
                            <div class="card-body">
                                <h6 class="fw-bold mb-3">
                                    <i class="fas fa-cog text-primary me-2"></i>
                                    Announcement Settings
                                </h6>

                                <!-- Publish Checkbox -->
                                <div class="form-check form-switch mb-3">
                                    <input type="checkbox" class="form-check-input" id="is_published" 
                                           name="is_published" value="1" 
                                           {{ old('is_published') ? 'checked' : '' }}
                                           style="width: 50px; height: 25px; cursor: pointer;">
                                    <label class="form-check-label fw-bold ms-2" for="is_published">
                                        <span class="badge {{ old('is_published') ? 'bg-success' : 'bg-secondary' }} px-3 py-2" id="publishStatus">
                                            <i class="fas {{ old('is_published') ? 'fa-check-circle' : 'fa-clock' }} me-1"></i>
                                            {{ old('is_published') ? 'Published' : 'Draft' }}
                                        </span>
                                    </label>
                                    <br>
                                    <small class="text-muted ms-2">
                                        <i class="fas fa-info-circle me-1"></i>
                                        Toggle to publish or save as draft
                                    </small>
                                </div>

                                <!-- Featured Checkbox -->
                                <div class="form-check form-switch">
                                    <input type="checkbox" class="form-check-input" id="is_featured" 
                                           name="is_featured" value="1" 
                                           {{ old('is_featured') ? 'checked' : '' }}
                                           style="width: 50px; height: 25px; cursor: pointer;">
                                    <label class="form-check-label fw-bold ms-2" for="is_featured">
                                        <span class="badge {{ old('is_featured') ? 'bg-warning text-dark' : 'bg-secondary' }} px-3 py-2" id="featuredStatus">
                                            <i class="fas {{ old('is_featured') ? 'fa-star' : 'fa-star-o' }} me-1"></i>
                                            {{ old('is_featured') ? 'Featured' : 'Not Featured' }}
                                        </span>
                                    </label>
                                    <br>
                                    <small class="text-muted ms-2">
                                        <i class="fas fa-info-circle me-1"></i>
                                        Featured announcements appear prominently
                                    </small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Submit Buttons -->
                <div class="d-flex gap-2 mt-4 pt-3 border-top">
                    <button type="submit" class="btn btn-primary btn-lg">
                        <i class="fas fa-save me-2"></i>
                        Create Announcement
                    </button>
                    <button type="reset" class="btn btn-outline-secondary btn-lg">
                        <i class="fas fa-undo me-2"></i>
                        Reset
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    .form-switch .form-check-input {
        width: 50px !important;
        height: 25px !important;
        cursor: pointer;
        transition: all 0.3s ease;
    }
    
    .form-switch .form-check-input:checked {
        background-color: #198754;
        border-color: #198754;
    }
    
    .form-switch .form-check-input:not(:checked) {
        background-color: #6c757d;
        border-color: #6c757d;
    }
    
    .form-switch .form-check-input:focus {
        box-shadow: 0 0 0 0.25rem rgba(25, 135, 84, 0.25);
    }
    
    .badge {
        transition: all 0.3s ease;
    }
</style>
@endpush

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // ========================================
        // PUBLISH CHECKBOX HANDLER
        // ========================================
        const publishCheckbox = document.getElementById('is_published');
        const publishStatus = document.getElementById('publishStatus');

        if (publishCheckbox && publishStatus) {
            publishCheckbox.addEventListener('change', function() {
                if (this.checked) {
                    publishStatus.className = 'badge bg-success px-3 py-2';
                    publishStatus.innerHTML = '<i class="fas fa-check-circle me-1"></i> Published';
                } else {
                    publishStatus.className = 'badge bg-secondary px-3 py-2';
                    publishStatus.innerHTML = '<i class="fas fa-clock me-1"></i> Draft';
                }
            });
        }

        // ========================================
        // FEATURED CHECKBOX HANDLER
        // ========================================
        const featuredCheckbox = document.getElementById('is_featured');
        const featuredStatus = document.getElementById('featuredStatus');

        if (featuredCheckbox && featuredStatus) {
            featuredCheckbox.addEventListener('change', function() {
                if (this.checked) {
                    featuredStatus.className = 'badge bg-warning text-dark px-3 py-2';
                    featuredStatus.innerHTML = '<i class="fas fa-star me-1"></i> Featured';
                } else {
                    featuredStatus.className = 'badge bg-secondary px-3 py-2';
                    featuredStatus.innerHTML = '<i class="fas fa-star-o me-1"></i> Not Featured';
                }
            });
        }
    });
</script>
@endpush