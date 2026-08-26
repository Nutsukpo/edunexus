@extends('layouts.master')

@section('title', 'Upload Appraisal')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h3 class="card-title mb-0">
                        <i class="fas fa-upload text-primary mr-2"></i>Upload Appraisal Form
                    </h3>
                    <div class="card-tools">
                        <a href="{{ route('staff-appraisals.index') }}" class="btn btn-secondary btn-sm">
                            <i class="fas fa-arrow-left"></i> Back
                        </a>
                    </div>
                </div>
                <form action="{{ route('staff-appraisals.store') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="card-body">
                        @if($errors->any())
                            <div class="alert alert-danger">
                                <h5><i class="fas fa-exclamation-circle"></i> Please fix the following errors:</h5>
                                <ul class="mb-0">
                                    @foreach($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="title">Title (Optional)</label>
                                    <input type="text" name="title" id="title" 
                                           class="form-control @error('title') is-invalid @enderror" 
                                           value="{{ old('title') }}" placeholder="Enter appraisal title">
                                    @error('title')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="staff_id">Staff <span class="text-danger">*</span></label>
                                    <select name="staff_id" id="staff_id" 
                                            class="form-control @error('staff_id') is-invalid @enderror" required>
                                        <option value="">Select Staff</option>
                                        @foreach($staffMembers as $member)
                                            <option value="{{ $member->id }}" {{ old('staff_id') == $member->id ? 'selected' : '' }}>
                                                {{ $member->first_name ?? '' }} {{ $member->last_name ?? '' }} 
                                                ({{ $member->email ?? '' }})
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('staff_id')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
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
                                            <option value="{{ $year->id }}" {{ old('academic_year_id') == $year->id ? 'selected' : '' }}>
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
                                            <option value="{{ $term->id }}" {{ old('term_id') == $term->id ? 'selected' : '' }}>
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
                                              rows="3" placeholder="Brief description of the appraisal">{{ old('description') }}</textarea>
                                    @error('description')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label for="file">Appraisal File <span class="text-danger">*</span></label>
                                    <div class="custom-file">
                                        <input type="file" name="file" id="file" 
                                               class="custom-file-input @error('file') is-invalid @enderror" required>
                                        <label class="custom-file-label" for="file">Choose file</label>
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
                                        <option value="draft" {{ old('status') == 'draft' ? 'selected' : '' }}>Draft</option>
                                        <option value="submitted" {{ old('status') == 'submitted' ? 'selected' : '' }}>Submitted</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card-footer">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-upload"></i> Upload Appraisal
                        </button>
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