@extends('layouts.master')

@section('title', 'Create Lesson Note')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Create New Lesson Note</h3>
                    <div class="card-tools">
                        <a href="{{ route('lesson-notes.index') }}" class="btn btn-secondary btn-sm">
                            <i class="fas fa-arrow-left"></i> Back
                        </a>
                    </div>
                </div>
                <form action="{{ route('lesson-notes.store') }}" method="POST" enctype="multipart/form-data" id="lessonNoteForm">
                    @csrf
                    <div class="card-body">
                        @if($errors->any())
                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                <h6><i class="fas fa-exclamation-triangle"></i> Please fix the following errors:</h6>
                                <ul class="mb-0">
                                    @foreach($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                            </div>
                        @endif

                        @if(session('success'))
                            <div class="alert alert-success alert-dismissible fade show" role="alert">
                                <i class="fas fa-check-circle"></i> {{ session('success') }}
                                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                            </div>
                        @endif

                        <!-- Note Code Information -->
                        <div class="row mb-3">
                            <div class="col-md-12">
                                <div class="alert alert-info">
                                    <i class="fas fa-info-circle"></i>
                                    <strong>Note Code:</strong> A unique code will be automatically generated upon saving.
                                </div>
                            </div>
                        </div>

                        <!-- Required Fields - Staff, Topic, Lesson Date -->
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="staff_id">Staff <span class="text-danger">*</span></label>
                                    <select name="staff_id" id="staff_id" 
                                            class="form-control @error('staff_id') is-invalid @enderror" required>
                                        <option value="">Select Staff</option>
                                        @foreach($staffs as $staff)
                                            <option value="{{ $staff->id }}" {{ old('staff_id') == $staff->id ? 'selected' : '' }}>
                                                {{ $staff->full_name ?? $staff->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('staff_id')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="topic">Topic <span class="text-danger">*</span></label>
                                    <input type="text" name="topic" id="topic" 
                                           class="form-control @error('topic') is-invalid @enderror" 
                                           value="{{ old('topic') }}" 
                                           placeholder="Enter lesson topic" required>
                                    @error('topic')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="lesson_date">Lesson Date <span class="text-danger">*</span></label>
                                    <input type="date" name="lesson_date" id="lesson_date" 
                                           class="form-control @error('lesson_date') is-invalid @enderror" 
                                           value="{{ old('lesson_date', date('Y-m-d')) }}" required>
                                    @error('lesson_date')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <!-- Type and Sub Topic -->
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="type">Type <span class="text-danger">*</span></label>
                                    <select name="type" id="type" class="form-control @error('type') is-invalid @enderror" required>
                                        <option value="daily" {{ old('type') == 'daily' ? 'selected' : '' }}>Daily</option>
                                        <option value="weekly" {{ old('type') == 'weekly' ? 'selected' : '' }}>Weekly</option>
                                        <option value="monthly" {{ old('type') == 'monthly' ? 'selected' : '' }}>Monthly</option>
                                        <option value="termly" {{ old('type') == 'termly' ? 'selected' : '' }}>Termly</option>
                                    </select>
                                    @error('type')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="sub_topic">Sub Topic</label>
                                    <input type="text" name="sub_topic" id="sub_topic" 
                                           class="form-control @error('sub_topic') is-invalid @enderror" 
                                           value="{{ old('sub_topic') }}" 
                                           placeholder="Enter sub topic">
                                    @error('sub_topic')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <!-- Academic References -->
                        <div class="row">
                            <div class="col-md-3">
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
                            <div class="col-md-3">
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
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="subject_id">Subject <span class="text-danger">*</span></label>
                                    <select name="subject_id" id="subject_id" 
                                            class="form-control @error('subject_id') is-invalid @enderror" required>
                                        <option value="">Select Subject</option>
                                        @foreach($subjects as $subject)
                                            <option value="{{ $subject->id }}" {{ old('subject_id') == $subject->id ? 'selected' : '' }}>
                                                {{ $subject->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('subject_id')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="student_class_id">Class <span class="text-danger">*</span></label>
                                    <select name="student_class_id" id="student_class_id" 
                                            class="form-control @error('student_class_id') is-invalid @enderror" required>
                                        <option value="">Select Class</option>
                                        @foreach($classes as $class)
                                            <option value="{{ $class->id }}" {{ old('student_class_id') == $class->id ? 'selected' : '' }}>
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

                        <!-- Time Information -->
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="start_time">Start Time</label>
                                    <input type="time" name="start_time" id="start_time" 
                                           class="form-control @error('start_time') is-invalid @enderror" 
                                           value="{{ old('start_time') }}">
                                    @error('start_time')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="end_time">End Time</label>
                                    <input type="time" name="end_time" id="end_time" 
                                           class="form-control @error('end_time') is-invalid @enderror" 
                                           value="{{ old('end_time') }}">
                                    @error('end_time')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="duration">Duration</label>
                                    <input type="text" name="duration" id="duration" 
                                           class="form-control @error('duration') is-invalid @enderror" 
                                           value="{{ old('duration') }}" 
                                           placeholder="e.g., 45 minutes" readonly>
                                    @error('duration')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <!-- Description -->
                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label for="description">Description</label>
                                    <textarea name="description" id="description" 
                                              class="form-control @error('description') is-invalid @enderror" 
                                              rows="3" placeholder="Brief description of the lesson note">{{ old('description') }}</textarea>
                                    @error('description')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <!-- Content -->
                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label for="content">Lesson Content <span class="text-danger">*</span></label>
                                    <textarea name="content" id="content" 
                                              class="form-control @error('content') is-invalid @enderror" 
                                              rows="10" placeholder="Detailed lesson content" required>{{ old('content') }}</textarea>
                                    @error('content')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <!-- Learning Objectives -->
                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label for="learning_objectives">Learning Objectives</label>
                                    <div id="objectives_container">
                                        @if(old('learning_objectives'))
                                            @foreach(old('learning_objectives') as $objective)
                                                <div class="input-group mb-2">
                                                    <input type="text" name="learning_objectives[]" class="form-control" value="{{ $objective }}" placeholder="Enter learning objective">
                                                    <div class="input-group-append">
                                                        <button type="button" class="btn btn-danger remove-item">
                                                            <i class="fas fa-times"></i>
                                                        </button>
                                                    </div>
                                                </div>
                                            @endforeach
                                        @else
                                            <div class="input-group mb-2">
                                                <input type="text" name="learning_objectives[]" class="form-control" placeholder="Enter learning objective">
                                                <div class="input-group-append">
                                                    <button type="button" class="btn btn-danger remove-item">
                                                        <i class="fas fa-times"></i>
                                                    </button>
                                                </div>
                                            </div>
                                        @endif
                                    </div>
                                    <button type="button" class="btn btn-sm btn-info add-item" data-target="objectives_container">
                                        <i class="fas fa-plus"></i> Add Objective
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- Learning Outcomes -->
                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label for="learning_outcomes">Learning Outcomes</label>
                                    <div id="outcomes_container">
                                        @if(old('learning_outcomes'))
                                            @foreach(old('learning_outcomes') as $outcome)
                                                <div class="input-group mb-2">
                                                    <input type="text" name="learning_outcomes[]" class="form-control" value="{{ $outcome }}" placeholder="Enter learning outcome">
                                                    <div class="input-group-append">
                                                        <button type="button" class="btn btn-danger remove-item">
                                                            <i class="fas fa-times"></i>
                                                        </button>
                                                    </div>
                                                </div>
                                            @endforeach
                                        @else
                                            <div class="input-group mb-2">
                                                <input type="text" name="learning_outcomes[]" class="form-control" placeholder="Enter learning outcome">
                                                <div class="input-group-append">
                                                    <button type="button" class="btn btn-danger remove-item">
                                                        <i class="fas fa-times"></i>
                                                    </button>
                                                </div>
                                            </div>
                                        @endif
                                    </div>
                                    <button type="button" class="btn btn-sm btn-info add-item" data-target="outcomes_container">
                                        <i class="fas fa-plus"></i> Add Outcome
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- Teaching Aids & Assessment -->
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="teaching_aids">Teaching Aids</label>
                                    <div id="aids_container">
                                        @if(old('teaching_aids'))
                                            @foreach(old('teaching_aids') as $aid)
                                                <div class="input-group mb-2">
                                                    <input type="text" name="teaching_aids[]" class="form-control" value="{{ $aid }}" placeholder="Enter teaching aid">
                                                    <div class="input-group-append">
                                                        <button type="button" class="btn btn-danger remove-item">
                                                            <i class="fas fa-times"></i>
                                                        </button>
                                                    </div>
                                                </div>
                                            @endforeach
                                        @else
                                            <div class="input-group mb-2">
                                                <input type="text" name="teaching_aids[]" class="form-control" placeholder="Enter teaching aid">
                                                <div class="input-group-append">
                                                    <button type="button" class="btn btn-danger remove-item">
                                                        <i class="fas fa-times"></i>
                                                    </button>
                                                </div>
                                            </div>
                                        @endif
                                    </div>
                                    <button type="button" class="btn btn-sm btn-info add-item" data-target="aids_container">
                                        <i class="fas fa-plus"></i> Add Teaching Aid
                                    </button>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="assessment_methods">Assessment Methods</label>
                                    <div id="assessment_container">
                                        @if(old('assessment_methods'))
                                            @foreach(old('assessment_methods') as $method)
                                                <div class="input-group mb-2">
                                                    <input type="text" name="assessment_methods[]" class="form-control" value="{{ $method }}" placeholder="Enter assessment method">
                                                    <div class="input-group-append">
                                                        <button type="button" class="btn btn-danger remove-item">
                                                            <i class="fas fa-times"></i>
                                                        </button>
                                                    </div>
                                                </div>
                                            @endforeach
                                        @else
                                            <div class="input-group mb-2">
                                                <input type="text" name="assessment_methods[]" class="form-control" placeholder="Enter assessment method">
                                                <div class="input-group-append">
                                                    <button type="button" class="btn btn-danger remove-item">
                                                        <i class="fas fa-times"></i>
                                                    </button>
                                                </div>
                                            </div>
                                        @endif
                                    </div>
                                    <button type="button" class="btn btn-sm btn-info add-item" data-target="assessment_container">
                                        <i class="fas fa-plus"></i> Add Assessment Method
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- Homework & Delivery Method -->
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="homework">Homework/Assignment</label>
                                    <textarea name="homework" id="homework" 
                                              class="form-control @error('homework') is-invalid @enderror" 
                                              rows="3" placeholder="Homework details">{{ old('homework') }}</textarea>
                                    @error('homework')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="delivery_method">Delivery Method</label>
                                    <select name="delivery_method" id="delivery_method" 
                                            class="form-control @error('delivery_method') is-invalid @enderror">
                                        <option value="">Select Method</option>
                                        <option value="lecture" {{ old('delivery_method') == 'lecture' ? 'selected' : '' }}>Lecture</option>
                                        <option value="practical" {{ old('delivery_method') == 'practical' ? 'selected' : '' }}>Practical</option>
                                        <option value="group_work" {{ old('delivery_method') == 'group_work' ? 'selected' : '' }}>Group Work</option>
                                        <option value="discussion" {{ old('delivery_method') == 'discussion' ? 'selected' : '' }}>Discussion</option>
                                        <option value="demonstration" {{ old('delivery_method') == 'demonstration' ? 'selected' : '' }}>Demonstration</option>
                                        <option value="blended" {{ old('delivery_method') == 'blended' ? 'selected' : '' }}>Blended</option>
                                    </select>
                                    @error('delivery_method')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <!-- Student Engagement -->
                        <div class="row">
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="expected_students">Expected Students</label>
                                    <input type="number" name="expected_students" id="expected_students" 
                                           class="form-control @error('expected_students') is-invalid @enderror" 
                                           value="{{ old('expected_students') }}" placeholder="Expected">
                                    @error('expected_students')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="actual_students">Actual Students</label>
                                    <input type="number" name="actual_students" id="actual_students" 
                                           class="form-control @error('actual_students') is-invalid @enderror" 
                                           value="{{ old('actual_students') }}" placeholder="Actual">
                                    @error('actual_students')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="student_participation">Student Participation</label>
                                    <input type="text" name="student_participation" id="student_participation" 
                                           class="form-control @error('student_participation') is-invalid @enderror" 
                                           value="{{ old('student_participation') }}" 
                                           placeholder="e.g., Active: 20, Passive: 5">
                                    @error('student_participation')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <!-- Additional Information -->
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="remarks">Remarks</label>
                                    <textarea name="remarks" id="remarks" 
                                              class="form-control @error('remarks') is-invalid @enderror" 
                                              rows="2" placeholder="Additional remarks">{{ old('remarks') }}</textarea>
                                    @error('remarks')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="recommendations">Recommendations</label>
                                    <textarea name="recommendations" id="recommendations" 
                                              class="form-control @error('recommendations') is-invalid @enderror" 
                                              rows="2" placeholder="Recommendations for improvement">{{ old('recommendations') }}</textarea>
                                    @error('recommendations')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <!-- Challenges -->
                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label for="challenges">Challenges Faced</label>
                                    <textarea name="challenges" id="challenges" 
                                              class="form-control @error('challenges') is-invalid @enderror" 
                                              rows="2" placeholder="Challenges during the lesson">{{ old('challenges') }}</textarea>
                                    @error('challenges')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <!-- Resources & Attachments -->
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="resources">Resources (URLs)</label>
                                    <input type="text" name="resources" id="resources" 
                                           class="form-control @error('resources') is-invalid @enderror" 
                                           value="{{ old('resources') }}" 
                                           placeholder="https://example.com, https://another.com">
                                    <small class="form-text text-muted">Separate multiple URLs with commas</small>
                                    @error('resources')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="attachment">Attachment</label>
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
                        </div>

                        <!-- Multiple Attachments -->
                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label for="attachments">Multiple Attachments</label>
                                    <div class="custom-file">
                                        <input type="file" name="attachments[]" id="attachments" 
                                               class="custom-file-input @error('attachments') is-invalid @enderror" multiple>
                                        <label class="custom-file-label" for="attachments">Choose multiple files</label>
                                    </div>
                                    <small class="form-text text-muted">
                                        <i class="fas fa-info-circle"></i> Max size: 10MB per file. Allowed: PDF, DOC, DOCX, JPG, PNG
                                    </small>
                                    @error('attachments')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <!-- REMOVED: Hidden staff_id field that was causing the conflict -->
                    </div>

                    <div class="card-footer">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Save Lesson Note
                        </button>
                        <a href="{{ route('lesson-notes.index') }}" class="btn btn-secondary">
                            <i class="fas fa-times"></i> Cancel
                        </a>
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

    // Multiple file input display
    $('#attachments').on('change', function() {
        var files = $(this).prop('files');
        var names = [];
        $.each(files, function(index, file) {
            names.push(file.name);
        });
        $(this).siblings('.custom-file-label').addClass('selected').html(names.join(', '));
    });

    // Add item to container
    $('.add-item').click(function() {
        var container = $('#' + $(this).data('target'));
        var inputGroup = container.find('.input-group:first').clone();
        inputGroup.find('input').val('');
        container.append(inputGroup);
    });

    // Remove item
    $(document).on('click', '.remove-item', function() {
        var container = $(this).closest('.input-group').parent();
        if (container.children().length > 1) {
            $(this).closest('.input-group').remove();
        }
    });

    // Calculate duration from start and end time
    function calculateDuration() {
        var start = $('#start_time').val();
        var end = $('#end_time').val();
        
        if (start && end) {
            var startTime = new Date('1970-01-01T' + start + ':00');
            var endTime = new Date('1970-01-01T' + end + ':00');
            
            if (endTime > startTime) {
                var diffMinutes = Math.round((endTime - startTime) / 60000);
                var hours = Math.floor(diffMinutes / 60);
                var minutes = diffMinutes % 60;
                
                var duration = '';
                if (hours > 0) {
                    duration += hours + ' hour' + (hours > 1 ? 's' : '');
                }
                if (minutes > 0) {
                    if (duration) duration += ' ';
                    duration += minutes + ' minute' + (minutes > 1 ? 's' : '');
                }
                
                $('#duration').val(duration);
            } else {
                $('#duration').val('');
            }
        } else {
            $('#duration').val('');
        }
    }

    $('#start_time, #end_time').on('change', calculateDuration);

    // Set default date to today if not set
    if (!$('#lesson_date').val()) {
        var today = new Date().toISOString().split('T')[0];
        $('#lesson_date').val(today);
    }

    // Auto-set staff_id from logged-in user
    @if(auth()->check() && auth()->user()->staff_id)
        $('#staff_id').val('{{ auth()->user()->staff_id }}');
    @endif
});
</script>
@endpush

@push('styles')
<style>
    .custom-file-label::after {
        content: "Browse";
    }
    .input-group .form-control {
        border-top-right-radius: 0;
        border-bottom-right-radius: 0;
    }
    .input-group .input-group-append .btn {
        border-top-left-radius: 0;
        border-bottom-left-radius: 0;
    }
    .input-group.mb-2 {
        margin-bottom: 0.5rem !important;
    }
    .required-field::after {
        content: "*";
        color: red;
        margin-left: 4px;
    }
    .alert-dismissible .close {
        position: absolute;
        top: 0;
        right: 0;
        padding: 0.75rem 1.25rem;
        color: inherit;
    }
</style>
@endpush
@endsection