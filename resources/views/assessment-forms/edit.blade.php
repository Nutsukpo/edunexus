@extends('layouts.master')

@section('title', 'Edit Assessment Form')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Edit Assessment Form</h3>
                    <div class="card-tools">
                        <a href="{{ route('assessment-forms.index') }}" class="btn btn-secondary btn-sm">
                            <i class="fas fa-arrow-left"></i> Back
                        </a>
                    </div>
                </div>
                <form action="{{ route('assessment-forms.update', $assessmentForm->id) }}" method="POST" enctype="multipart/form-data" id="editForm">
                    @csrf
                    @method('PUT')
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

                        @if(session('success'))
                            <div class="alert alert-success">
                                {{ session('success') }}
                            </div>
                        @endif

                        <!-- Current File Info -->
                        <div class="row mb-3">
                            <div class="col-md-12">
                                <div class="alert alert-info">
                                    <i class="fas fa-file"></i>
                                    <strong>Current File:</strong> {{ $assessmentForm->file_name }}
                                    <span class="badge badge-secondary ml-2">{{ $assessmentForm->formatted_file_size ?? 'N/A' }}</span>
                                    <span class="badge badge-info ml-1">{{ strtoupper($assessmentForm->file_type ?? 'N/A') }}</span>
                                    <div class="form-check mt-2">
                                        <input type="checkbox" name="remove_file" id="remove_file" value="1" class="form-check-input">
                                        <label for="remove_file" class="form-check-label text-danger">
                                            <i class="fas fa-times"></i> Remove current file
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Staff Selection -->
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="staff_id">Staff <span class="text-danger">*</span></label>
                                    <select name="staff_id" id="staff_id" 
                                            class="form-control @error('staff_id') is-invalid @enderror" required>
                                        <option value="">Select Staff</option>
                                        @foreach($staff as $s)
                                            <option value="{{ $s->id }}" {{ old('staff_id', $assessmentForm->staff_id) == $s->id ? 'selected' : '' }}>
                                                {{ $s->first_name ?? 'N/A' }} {{ $s->last_name ?? '' }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('staff_id')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="student_class_id">Class <span class="text-danger">*</span></label>
                                    <select name="student_class_id" id="student_class_id" 
                                            class="form-control @error('student_class_id') is-invalid @enderror" required>
                                        <option value="">Select Class</option>
                                        @foreach($classes as $class)
                                            <option value="{{ $class->id }}" {{ old('student_class_id', $assessmentForm->student_class_id) == $class->id ? 'selected' : '' }}>
                                                {{ $class->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('student_class_id')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <!-- Academic Info -->
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="academic_year_id">Academic Year <span class="text-danger">*</span></label>
                                    <select name="academic_year_id" id="academic_year_id" 
                                            class="form-control @error('academic_year_id') is-invalid @enderror" required>
                                        <option value="">Select Academic Year</option>
                                        @foreach($academicYears as $year)
                                            <option value="{{ $year->id }}" {{ old('academic_year_id', $assessmentForm->academic_year_id) == $year->id ? 'selected' : '' }}>
                                                {{ $year->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('academic_year_id')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="term_id">Term <span class="text-danger">*</span></label>
                                    <select name="term_id" id="term_id" 
                                            class="form-control @error('term_id') is-invalid @enderror" required>
                                        <option value="">Select Term</option>
                                        @foreach($terms as $term)
                                            <option value="{{ $term->id }}" {{ old('term_id', $assessmentForm->term_id) == $term->id ? 'selected' : '' }}>
                                                {{ $term->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('term_id')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="subject_id">Subject</label>
                                    <select name="subject_id" id="subject_id" 
                                            class="form-control @error('subject_id') is-invalid @enderror">
                                        <option value="">Select Subject (Optional)</option>
                                        @foreach($subjects as $subject)
                                            <option value="{{ $subject->id }}" {{ old('subject_id', $assessmentForm->subject_id) == $subject->id ? 'selected' : '' }}>
                                                {{ $subject->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('subject_id')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <!-- Dates -->
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="assessment_date">Assessment Date <span class="text-danger">*</span></label>
                                    <input type="date" name="assessment_date" id="assessment_date" 
                                           class="form-control @error('assessment_date') is-invalid @enderror" 
                                           value="{{ old('assessment_date', $assessmentForm->assessment_date ? $assessmentForm->assessment_date->format('Y-m-d') : date('Y-m-d')) }}" required>
                                    @error('assessment_date')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="due_date">Due Date</label>
                                    <input type="date" name="due_date" id="due_date" 
                                           class="form-control @error('due_date') is-invalid @enderror" 
                                           value="{{ old('due_date', $assessmentForm->due_date ? $assessmentForm->due_date->format('Y-m-d') : '') }}">
                                    @error('due_date')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <!-- Assessment Type & Title -->
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="assessment_type">Assessment Type <span class="text-danger">*</span></label>
                                    <select name="assessment_type" id="assessment_type" 
                                            class="form-control @error('assessment_type') is-invalid @enderror" required>
                                        <option value="">Select Type</option>
                                        <option value="quiz" {{ old('assessment_type', $assessmentForm->assessment_type) == 'quiz' ? 'selected' : '' }}>Quiz</option>
                                        <option value="test" {{ old('assessment_type', $assessmentForm->assessment_type) == 'test' ? 'selected' : '' }}>Test</option>
                                        <option value="exam" {{ old('assessment_type', $assessmentForm->assessment_type) == 'exam' ? 'selected' : '' }}>Exam</option>
                                        <option value="assignment" {{ old('assessment_type', $assessmentForm->assessment_type) == 'assignment' ? 'selected' : '' }}>Assignment</option>
                                        <option value="project" {{ old('assessment_type', $assessmentForm->assessment_type) == 'project' ? 'selected' : '' }}>Project</option>
                                    </select>
                                    @error('assessment_type')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="title">Title (Optional)</label>
                                    <input type="text" name="title" id="title" 
                                           class="form-control @error('title') is-invalid @enderror" 
                                           value="{{ old('title', $assessmentForm->title) }}" placeholder="Enter a title for the assessment">
                                    @error('title')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <!-- Description -->
                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label for="description">Description (Optional)</label>
                                    <textarea name="description" id="description" 
                                              class="form-control @error('description') is-invalid @enderror" 
                                              rows="3" placeholder="Brief description of the assessment">{{ old('description', $assessmentForm->description) }}</textarea>
                                    @error('description')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <!-- File Upload -->
                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label for="file">Replace File (Optional)</label>
                                    <div class="custom-file">
                                        <input type="file" name="file" id="file" 
                                               class="custom-file-input @error('file') is-invalid @enderror">
                                        <label class="custom-file-label" for="file">Choose new file</label>
                                    </div>
                                    <small class="form-text text-muted">
                                        <i class="fas fa-info-circle"></i> 
                                        Accepted formats: PDF, JPG, JPEG, PNG, GIF, DOC, DOCX (Max 20MB)
                                    </small>
                                    @error('file')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <!-- Status -->
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="status">Status</label>
                                    <select name="status" id="status" class="form-control">
                                        <option value="draft" {{ old('status', $assessmentForm->status) == 'draft' ? 'selected' : '' }}>Draft</option>
                                        <option value="published" {{ old('status', $assessmentForm->status) == 'published' ? 'selected' : '' }}>Published</option>
                                        <option value="archived" {{ old('status', $assessmentForm->status) == 'archived' ? 'selected' : '' }}>Archived</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="alert alert-secondary">
                                    <i class="fas fa-info-circle"></i>
                                    <strong>Created:</strong> {{ $assessmentForm->created_at ? $assessmentForm->created_at->format('d/m/Y H:i') : 'N/A' }}
                                    <br>
                                    <strong>Last Updated:</strong> {{ $assessmentForm->updated_at ? $assessmentForm->updated_at->format('d/m/Y H:i') : 'N/A' }}
                                </div>
                            </div>
                        </div>

                        <!-- Metadata -->
                        <div class="row">
                            <div class="col-md-12">
                                <div class="alert alert-light">
                                    <i class="fas fa-chart-bar"></i>
                                    <strong>Views:</strong> {{ $assessmentForm->views_count ?? 0 }}
                                    <span class="ml-3"><i class="fas fa-download"></i> <strong>Downloads:</strong> {{ $assessmentForm->downloads_count ?? 0 }}</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card-footer">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Update Form
                        </button>
                        <a href="{{ route('assessment-forms.show', $assessmentForm->id) }}" class="btn btn-info">
                            <i class="fas fa-eye"></i> View
                        </a>
                        <a href="{{ route('assessment-forms.index') }}" class="btn btn-secondary">Cancel</a>
                        
                        @if(auth()->user()->hasRole('admin') || (auth()->user()->staff && $assessmentForm->staff_id == auth()->user()->staff->id))
                            <button type="button" class="btn btn-danger float-right" onclick="confirmDelete()">
                                <i class="fas fa-trash"></i> Delete
                            </button>
                        @endif
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Delete Form -->
<form id="delete-form" action="{{ route('assessment-forms.destroy', $assessmentForm->id) }}" method="POST" style="display: none;">
    @csrf
    @method('DELETE')
</form>

@push('scripts')
<script>
$(document).ready(function() {
    // File input display
    $('.custom-file-input').on('change', function() {
        var fileName = $(this).val().split('\\').pop();
        $(this).siblings('.custom-file-label').addClass('selected').html(fileName);
    });

    // Remove file confirmation
    $('#remove_file').change(function() {
        if ($(this).is(':checked')) {
            if (!confirm('Are you sure you want to remove the current file?')) {
                $(this).prop('checked', false);
            }
        }
    });

    // Delete confirmation
    window.confirmDelete = function() {
        if (confirm('Are you sure you want to delete this assessment form? This action cannot be undone.')) {
            document.getElementById('delete-form').submit();
        }
    };

    // Form validation before submit
    $('#editForm').on('submit', function(e) {
        var staffId = $('#staff_id').val();
        var classId = $('#student_class_id').val();
        var academicYearId = $('#academic_year_id').val();
        var termId = $('#term_id').val();
        var assessmentDate = $('#assessment_date').val();
        var assessmentType = $('#assessment_type').val();

        if (!staffId) {
            alert('Please select a staff member.');
            e.preventDefault();
            return false;
        }
        if (!classId) {
            alert('Please select a class.');
            e.preventDefault();
            return false;
        }
        if (!academicYearId) {
            alert('Please select an academic year.');
            e.preventDefault();
            return false;
        }
        if (!termId) {
            alert('Please select a term.');
            e.preventDefault();
            return false;
        }
        if (!assessmentDate) {
            alert('Please select an assessment date.');
            e.preventDefault();
            return false;
        }
        if (!assessmentType) {
            alert('Please select an assessment type.');
            e.preventDefault();
            return false;
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
    .card-header .btn {
        margin-right: 5px;
    }
    .card-header .btn:last-child {
        margin-right: 0;
    }
</style>
@endpush
@endsection