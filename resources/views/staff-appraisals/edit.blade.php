@extends('layouts.master')

@section('title', 'Edit Appraisal')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Edit Appraisal</h3>
                    <div class="card-tools">
                        <a href="{{ route('staff-appraisals.index') }}" class="btn btn-secondary btn-sm">
                            <i class="fas fa-arrow-left"></i> Back
                        </a>
                    </div>
                </div>
                <form action="{{ route('staff-appraisals.update', $appraisal->id) }}" method="POST" enctype="multipart/form-data">
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

                        <!-- Current File Info -->
                        <div class="row mb-3">
                            <div class="col-md-12">
                                <div class="alert alert-info">
                                    <i class="fas fa-file"></i>
                                    <strong>Current File:</strong> {{ $appraisal->file_name }}
                                    <span class="badge badge-secondary ml-2">{{ $appraisal->formatted_file_size }}</span>
                                    <span class="badge badge-info ml-1">{{ strtoupper($appraisal->file_type) }}</span>
                                    <div class="form-check mt-2">
                                        <input type="checkbox" name="remove_file" id="remove_file" value="1" class="form-check-input">
                                        <label for="remove_file" class="form-check-label text-danger">
                                            <i class="fas fa-times"></i> Remove current file
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="title">Title (Optional)</label>
                                    <input type="text" name="title" id="title" 
                                           class="form-control @error('title') is-invalid @enderror" 
                                           value="{{ old('title', $appraisal->title) }}" placeholder="Enter appraisal title">
                                    @error('title')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="staff_id">Staff</label>
                                    <input type="text" class="form-control" value="{{ $appraisal->staff->first_name ?? '' }} {{ $appraisal->staff->last_name ?? '' }}" disabled>
                                    <input type="hidden" name="staff_id" value="{{ $appraisal->staff_id }}">
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="academic_year_id">Academic Year <span class="text-danger">*</span></label>
                                    <select name="academic_year_id" id="academic_year_id" 
                                            class="form-control @error('academic_year_id') is-invalid @enderror" required>
                                        <option value="">Select Academic Year</option>
                                        @foreach($academicYears as $year)
                                            <option value="{{ $year->id }}" {{ old('academic_year_id', $appraisal->academic_year_id) == $year->id ? 'selected' : '' }}>
                                                {{ $year->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('academic_year_id')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="term_id">Term <span class="text-danger">*</span></label>
                                    <select name="term_id" id="term_id" 
                                            class="form-control @error('term_id') is-invalid @enderror" required>
                                        <option value="">Select Term</option>
                                        @foreach($terms as $term)
                                            <option value="{{ $term->id }}" {{ old('term_id', $appraisal->term_id) == $term->id ? 'selected' : '' }}>
                                                {{ $term->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('term_id')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label for="description">Description (Optional)</label>
                                    <textarea name="description" id="description" 
                                              class="form-control @error('description') is-invalid @enderror" 
                                              rows="3" placeholder="Brief description of the appraisal">{{ old('description', $appraisal->description) }}</textarea>
                                    @error('description')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                        </div>

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
                                        Accepted formats: PDF, DOC, DOCX, JPG, JPEG, PNG (Max 20MB)
                                    </small>
                                    @error('file')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="status">Status</label>
                                    <select name="status" id="status" class="form-control">
                                        <option value="draft" {{ old('status', $appraisal->status) == 'draft' ? 'selected' : '' }}>Draft</option>
                                        <option value="submitted" {{ old('status', $appraisal->status) == 'submitted' ? 'selected' : '' }}>Submitted</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="alert alert-secondary">
                                    <i class="fas fa-info-circle"></i>
                                    <strong>Created:</strong> {{ $appraisal->created_at->format('d/m/Y H:i') }}
                                    <br>
                                    <strong>Last Updated:</strong> {{ $appraisal->updated_at->format('d/m/Y H:i') }}
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card-footer">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Update Appraisal
                        </button>
                        <a href="{{ route('staff-appraisals.show', $appraisal->id) }}" class="btn btn-info">
                            <i class="fas fa-eye"></i> View
                        </a>
                        <a href="{{ route('staff-appraisals.index') }}" class="btn btn-secondary">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

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
});
</script>
@endpush

@push('styles')
<style>
    .custom-file-label::after {
        content: "Browse";
    }
</style>
@endpush
@endsection