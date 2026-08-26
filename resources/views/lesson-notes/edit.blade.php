@extends('layouts.master')

@section('title', 'Edit Lesson Note')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Edit Lesson Note</h3>
                    <div class="card-tools">
                        <a href="{{ route('lesson-notes.index') }}" class="btn btn-secondary btn-sm">
                            <i class="fas fa-arrow-left"></i> Back
                        </a>
                    </div>
                </div>
                <!-- /.card-header -->
                <form action="{{ route('lesson-notes.update', $lessonNote->id) }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')
                    <div class="card-body">
                        <div class="row">
                            <!-- Left Column -->
                            <div class="col-md-6">
                                <!-- Staff -->
                                <div class="form-group">
                                    <label for="staff_id">Staff <span class="text-danger">*</span></label>
                                    <select name="staff_id" id="staff_id" class="form-control @error('staff_id') is-invalid @enderror" required>
                                        <option value="">Select Staff</option>
                                        @foreach($staffs as $staff)
                                            <option value="{{ $staff->id }}" {{ old('staff_id', $lessonNote->staff_id) == $staff->id ? 'selected' : '' }}>
                                                {{ $staff->full_name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('staff_id')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>

                                <!-- Class -->
                                <div class="form-group">
                                    <label for="student_class_id">Class <span class="text-danger">*</span></label>
                                    <select name="student_class_id" id="student_class_id" class="form-control @error('student_class_id') is-invalid @enderror" required>
                                        <option value="">Select Class</option>
                                        @foreach($classes as $class)
                                            <option value="{{ $class->id }}" {{ old('student_class_id', $lessonNote->student_class_id) == $class->id ? 'selected' : '' }}>
                                                {{ $class->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('student_class_id')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>

                                <!-- Subject -->
                                <div class="form-group">
                                    <label for="subject_id">Subject <span class="text-danger">*</span></label>
                                    <select name="subject_id" id="subject_id" class="form-control @error('subject_id') is-invalid @enderror" required>
                                        <option value="">Select Subject</option>
                                        @foreach($subjects as $subject)
                                            <option value="{{ $subject->id }}" {{ old('subject_id', $lessonNote->subject_id) == $subject->id ? 'selected' : '' }}>
                                                {{ $subject->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('subject_id')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>

                                <!-- Academic Year -->
                                <div class="form-group">
                                    <label for="academic_year_id">Academic Year <span class="text-danger">*</span></label>
                                    <select name="academic_year_id" id="academic_year_id" class="form-control @error('academic_year_id') is-invalid @enderror" required>
                                        <option value="">Select Academic Year</option>
                                        @foreach($academicYears as $year)
                                            <option value="{{ $year->id }}" {{ old('academic_year_id', $lessonNote->academic_year_id) == $year->id ? 'selected' : '' }}>
                                                {{ $year->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('academic_year_id')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>

                                <!-- Term -->
                                <div class="form-group">
                                    <label for="term_id">Term <span class="text-danger">*</span></label>
                                    <select name="term_id" id="term_id" class="form-control @error('term_id') is-invalid @enderror" required>
                                        <option value="">Select Term</option>
                                        @foreach($terms as $term)
                                            <option value="{{ $term->id }}" {{ old('term_id', $lessonNote->term_id) == $term->id ? 'selected' : '' }}>
                                                {{ $term->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('term_id')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>

                                <!-- Type -->
                                <div class="form-group">
                                    <label for="type">Type <span class="text-danger">*</span></label>
                                    <select name="type" id="type" class="form-control @error('type') is-invalid @enderror" required>
                                        <option value="daily" {{ old('type', $lessonNote->type) == 'daily' ? 'selected' : '' }}>Daily</option>
                                        <option value="weekly" {{ old('type', $lessonNote->type) == 'weekly' ? 'selected' : '' }}>Weekly</option>
                                        <option value="monthly" {{ old('type', $lessonNote->type) == 'monthly' ? 'selected' : '' }}>Monthly</option>
                                        <option value="termly" {{ old('type', $lessonNote->type) == 'termly' ? 'selected' : '' }}>Termly</option>
                                    </select>
                                    @error('type')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>

                                <!-- Lesson Date -->
                                <div class="form-group">
                                    <label for="lesson_date">Lesson Date <span class="text-danger">*</span></label>
                                    <input type="date" name="lesson_date" id="lesson_date" class="form-control @error('lesson_date') is-invalid @enderror" value="{{ old('lesson_date', $lessonNote->lesson_date->format('Y-m-d')) }}" required>
                                    @error('lesson_date')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>

                                <!-- Time -->
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="start_time">Start Time</label>
                                            <input type="time" name="start_time" id="start_time" class="form-control @error('start_time') is-invalid @enderror" value="{{ old('start_time', $lessonNote->start_time ? $lessonNote->start_time->format('H:i') : '') }}">
                                            @error('start_time')
                                                <span class="invalid-feedback">{{ $message }}</span>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="end_time">End Time</label>
                                            <input type="time" name="end_time" id="end_time" class="form-control @error('end_time') is-invalid @enderror" value="{{ old('end_time', $lessonNote->end_time ? $lessonNote->end_time->format('H:i') : '') }}">
                                            @error('end_time')
                                                <span class="invalid-feedback">{{ $message }}</span>
                                            @enderror
                                        </div>
                                    </div>
                                </div>

                                <!-- Duration -->
                                <div class="form-group">
                                    <label for="duration">Duration</label>
                                    <input type="text" name="duration" id="duration" class="form-control @error('duration') is-invalid @enderror" placeholder="e.g., 45 minutes" value="{{ old('duration', $lessonNote->duration) }}">
                                    @error('duration')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>

                            <!-- Right Column -->
                            <div class="col-md-6">
                                <!-- Topic -->
                                <div class="form-group">
                                    <label for="topic">Topic <span class="text-danger">*</span></label>
                                    <input type="text" name="topic" id="topic" class="form-control @error('topic') is-invalid @enderror" placeholder="Enter lesson topic" value="{{ old('topic', $lessonNote->topic) }}" required>
                                    @error('topic')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>

                                <!-- Sub Topic -->
                                <div class="form-group">
                                    <label for="sub_topic">Sub Topic</label>
                                    <input type="text" name="sub_topic" id="sub_topic" class="form-control @error('sub_topic') is-invalid @enderror" placeholder="Enter sub topic" value="{{ old('sub_topic', $lessonNote->sub_topic) }}">
                                    @error('sub_topic')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>

                                <!-- Description -->
                                <div class="form-group">
                                    <label for="description">Description</label>
                                    <textarea name="description" id="description" rows="3" class="form-control @error('description') is-invalid @enderror" placeholder="Brief description">{{ old('description', $lessonNote->description) }}</textarea>
                                    @error('description')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>

                                <!-- Content -->
                                <div class="form-group">
                                    <label for="content">Content <span class="text-danger">*</span></label>
                                    <textarea name="content" id="content" rows="6" class="form-control @error('content') is-invalid @enderror" placeholder="Lesson content" required>{{ old('content', $lessonNote->content) }}</textarea>
                                    @error('content')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>

                                <!-- Delivery Method -->
                                <div class="form-group">
                                    <label for="delivery_method">Delivery Method</label>
                                    <select name="delivery_method" id="delivery_method" class="form-control @error('delivery_method') is-invalid @enderror">
                                        <option value="">Select Method</option>
                                        <option value="lecture" {{ old('delivery_method', $lessonNote->delivery_method) == 'lecture' ? 'selected' : '' }}>Lecture</option>
                                        <option value="practical" {{ old('delivery_method', $lessonNote->delivery_method) == 'practical' ? 'selected' : '' }}>Practical</option>
                                        <option value="group_work" {{ old('delivery_method', $lessonNote->delivery_method) == 'group_work' ? 'selected' : '' }}>Group Work</option>
                                        <option value="discussion" {{ old('delivery_method', $lessonNote->delivery_method) == 'discussion' ? 'selected' : '' }}>Discussion</option>
                                        <option value="demonstration" {{ old('delivery_method', $lessonNote->delivery_method) == 'demonstration' ? 'selected' : '' }}>Demonstration</option>
                                        <option value="blended" {{ old('delivery_method', $lessonNote->delivery_method) == 'blended' ? 'selected' : '' }}>Blended</option>
                                    </select>
                                    @error('delivery_method')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>

                                <!-- Teaching Aids -->
                                <div class="form-group">
                                    <label for="teaching_aids">Teaching Aids</label>
                                    <input type="text" name="teaching_aids" id="teaching_aids" class="form-control @error('teaching_aids') is-invalid @enderror" placeholder="Comma separated: whiteboard, projector, etc." value="{{ old('teaching_aids', is_array($lessonNote->teaching_aids) ? implode(', ', $lessonNote->teaching_aids) : $lessonNote->teaching_aids) }}">
                                    <small class="text-muted">Separate with commas</small>
                                    @error('teaching_aids')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>

                                <!-- Homework -->
                                <div class="form-group">
                                    <label for="homework">Homework/Assignment</label>
                                    <textarea name="homework" id="homework" rows="2" class="form-control @error('homework') is-invalid @enderror" placeholder="Homework details">{{ old('homework', $lessonNote->homework) }}</textarea>
                                    @error('homework')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <!-- Additional Fields -->
                        <div class="row">
                            <div class="col-md-12">
                                <div class="card card-outline card-info">
                                    <div class="card-header">
                                        <h5 class="card-title">Additional Information</h5>
                                        <div class="card-tools">
                                            <button type="button" class="btn btn-tool" data-card-widget="collapse">
                                                <i class="fas fa-minus"></i>
                                            </button>
                                        </div>
                                    </div>
                                    <div class="card-body">
                                        <div class="row">
                                            <!-- Learning Objectives -->
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label for="learning_objectives">Learning Objectives</label>
                                                    <textarea name="learning_objectives" id="learning_objectives" rows="3" class="form-control @error('learning_objectives') is-invalid @enderror" placeholder="Enter learning objectives (one per line)">{{ old('learning_objectives', is_array($lessonNote->learning_objectives) ? implode("\n", $lessonNote->learning_objectives) : $lessonNote->learning_objectives) }}</textarea>
                                                    <small class="text-muted">One objective per line</small>
                                                    @error('learning_objectives')
                                                        <span class="invalid-feedback">{{ $message }}</span>
                                                    @enderror
                                                </div>
                                            </div>

                                            <!-- Learning Outcomes -->
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label for="learning_outcomes">Learning Outcomes</label>
                                                    <textarea name="learning_outcomes" id="learning_outcomes" rows="3" class="form-control @error('learning_outcomes') is-invalid @enderror" placeholder="Enter learning outcomes (one per line)">{{ old('learning_outcomes', is_array($lessonNote->learning_outcomes) ? implode("\n", $lessonNote->learning_outcomes) : $lessonNote->learning_outcomes) }}</textarea>
                                                    <small class="text-muted">One outcome per line</small>
                                                    @error('learning_outcomes')
                                                        <span class="invalid-feedback">{{ $message }}</span>
                                                    @enderror
                                                </div>
                                            </div>
                                        </div>

                                        <div class="row">
                                            <!-- Assessment Methods -->
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label for="assessment_methods">Assessment Methods</label>
                                                    <input type="text" name="assessment_methods" id="assessment_methods" class="form-control @error('assessment_methods') is-invalid @enderror" placeholder="Quiz, test, assignment, etc." value="{{ old('assessment_methods', is_array($lessonNote->assessment_methods) ? implode(', ', $lessonNote->assessment_methods) : $lessonNote->assessment_methods) }}">
                                                    <small class="text-muted">Separate with commas</small>
                                                    @error('assessment_methods')
                                                        <span class="invalid-feedback">{{ $message }}</span>
                                                    @enderror
                                                </div>
                                            </div>

                                            <!-- Resources -->
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label for="resources">Resources (URLs)</label>
                                                    <input type="text" name="resources" id="resources" class="form-control @error('resources') is-invalid @enderror" placeholder="https://example.com, https://another.com" value="{{ old('resources', is_array($lessonNote->resources) ? implode(', ', $lessonNote->resources) : $lessonNote->resources) }}">
                                                    <small class="text-muted">Separate with commas</small>
                                                    @error('resources')
                                                        <span class="invalid-feedback">{{ $message }}</span>
                                                    @enderror
                                                </div>
                                            </div>
                                        </div>

                                        <div class="row">
                                            <!-- Expected Students -->
                                            <div class="col-md-3">
                                                <div class="form-group">
                                                    <label for="expected_students">Expected Students</label>
                                                    <input type="number" name="expected_students" id="expected_students" class="form-control @error('expected_students') is-invalid @enderror" placeholder="Expected" value="{{ old('expected_students', $lessonNote->expected_students) }}">
                                                    @error('expected_students')
                                                        <span class="invalid-feedback">{{ $message }}</span>
                                                    @enderror
                                                </div>
                                            </div>

                                            <!-- Actual Students -->
                                            <div class="col-md-3">
                                                <div class="form-group">
                                                    <label for="actual_students">Actual Students</label>
                                                    <input type="number" name="actual_students" id="actual_students" class="form-control @error('actual_students') is-invalid @enderror" placeholder="Actual" value="{{ old('actual_students', $lessonNote->actual_students) }}">
                                                    @error('actual_students')
                                                        <span class="invalid-feedback">{{ $message }}</span>
                                                    @enderror
                                                </div>
                                            </div>

                                            <!-- Remarks -->
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label for="remarks">Remarks</label>
                                                    <textarea name="remarks" id="remarks" rows="2" class="form-control @error('remarks') is-invalid @enderror" placeholder="Additional remarks">{{ old('remarks', $lessonNote->remarks) }}</textarea>
                                                    @error('remarks')
                                                        <span class="invalid-feedback">{{ $message }}</span>
                                                    @enderror
                                                </div>
                                            </div>
                                        </div>

                                        <div class="row">
                                            <!-- Challenges -->
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label for="challenges">Challenges Faced</label>
                                                    <textarea name="challenges" id="challenges" rows="2" class="form-control @error('challenges') is-invalid @enderror" placeholder="Challenges during the lesson">{{ old('challenges', $lessonNote->challenges) }}</textarea>
                                                    @error('challenges')
                                                        <span class="invalid-feedback">{{ $message }}</span>
                                                    @enderror
                                                </div>
                                            </div>

                                            <!-- Recommendations -->
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label for="recommendations">Recommendations</label>
                                                    <textarea name="recommendations" id="recommendations" rows="2" class="form-control @error('recommendations') is-invalid @enderror" placeholder="Recommendations for improvement">{{ old('recommendations', $lessonNote->recommendations) }}</textarea>
                                                    @error('recommendations')
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
                                                    <select name="status" id="status" class="form-control @error('status') is-invalid @enderror">
                                                        <option value="">None</option>
                                                        <option value="draft" {{ old('status', $lessonNote->status) == 'draft' ? 'selected' : '' }}>Draft</option>
                                                        <option value="published" {{ old('status', $lessonNote->status) == 'published' ? 'selected' : '' }}>Published</option>
                                                        <option value="archived" {{ old('status', $lessonNote->status) == 'archived' ? 'selected' : '' }}>Archived</option>
                                                    </select>
                                                    @error('status')
                                                        <span class="invalid-feedback">{{ $message }}</span>
                                                    @enderror
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Attachments -->
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label for="attachment">Single Attachment</label>
                                                    @if($lessonNote->attachment)
                                                        <div class="mb-2">
                                                            <a href="{{ Storage::url($lessonNote->attachment) }}" target="_blank" class="btn btn-sm btn-info">
                                                                <i class="fas fa-file"></i> View Current
                                                            </a>
                                                            <small class="text-muted">Upload new to replace</small>
                                                        </div>
                                                    @endif
                                                    <input type="file" name="attachment" id="attachment" class="form-control-file @error('attachment') is-invalid @enderror">
                                                    <small class="text-muted">Max size: 10MB</small>
                                                    @error('attachment')
                                                        <span class="invalid-feedback">{{ $message }}</span>
                                                    @enderror
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label for="attachments">Multiple Attachments</label>
                                                    @if($lessonNote->attachments && count($lessonNote->attachments) > 0)
                                                        <div class="mb-2">
                                                            @foreach($lessonNote->attachments as $file)
                                                                <a href="{{ Storage::url($file) }}" target="_blank" class="btn btn-sm btn-info mr-1 mb-1">
                                                                    <i class="fas fa-file"></i> {{ basename($file) }}
                                                                </a>
                                                            @endforeach
                                                            <br>
                                                            <small class="text-muted">Upload new to replace all</small>
                                                        </div>
                                                    @endif
                                                    <input type="file" name="attachments[]" id="attachments" class="form-control-file @error('attachments') is-invalid @enderror" multiple>
                                                    <small class="text-muted">Max size: 10MB per file</small>
                                                    @error('attachments')
                                                        <span class="invalid-feedback">{{ $message }}</span>
                                                    @enderror
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- /.card-body -->

                    <div class="card-footer">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Update Lesson Note
                        </button>
                        <a href="{{ route('lesson-notes.index') }}" class="btn btn-secondary">Cancel</a>
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
    // Auto-calculate duration from start and end time
    document.getElementById('start_time').addEventListener('change', calculateDuration);
    document.getElementById('end_time').addEventListener('change', calculateDuration);

    function calculateDuration() {
        const start = document.getElementById('start_time').value;
        const end = document.getElementById('end_time').value;
        
        if (start && end) {
            const startTime = new Date(`1970-01-01T${start}:00`);
            const endTime = new Date(`1970-01-01T${end}:00`);
            
            if (endTime > startTime) {
                const diffMinutes = Math.round((endTime - startTime) / 60000);
                const hours = Math.floor(diffMinutes / 60);
                const minutes = diffMinutes % 60;
                
                let duration = '';
                if (hours > 0) {
                    duration += hours + ' hour' + (hours > 1 ? 's' : '');
                }
                if (minutes > 0) {
                    if (duration) duration += ' ';
                    duration += minutes + ' minute' + (minutes > 1 ? 's' : '');
                }
                
                document.getElementById('duration').value = duration;
            }
        }
    }
</script>
@endpush