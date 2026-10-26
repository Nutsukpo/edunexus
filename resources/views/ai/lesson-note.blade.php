@extends('layouts.master')

@section('title', 'AI Lesson Note Generator')

@section('content')
<div class="container-fluid py-4">

    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4">
        <div>
            <h3 class="mb-1">
                <i class="fas fa-robot text-primary me-2"></i>
                AI Lesson Note Generator
            </h3>

            <p class="text-muted mb-0">
                Generate a professional lesson note using EdunexusAI.
            </p>
        </div>

        <a href="{{ route('lesson-notes.index') }}" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left me-1"></i>
            Back to Lesson Notes
        </a>
    </div>

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show">
            <i class="fas fa-exclamation-triangle me-2"></i>
            {{ session('error') }}

            <button type="button"
                    class="btn-close"
                    data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if(session('ai_notice'))
        <div class="alert alert-success alert-dismissible fade show">
            <i class="fas fa-check-circle me-2"></i>
            {{ session('ai_notice') }}

            <button type="button"
                    class="btn-close"
                    data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="row g-4">

        <div class="col-lg-5">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">
                        <i class="fas fa-sliders-h me-2"></i>
                        Lesson Information
                    </h5>
                </div>

                <div class="card-body">

                    <form method="POST"
                          action="{{ route('ai.lesson-notes.generate') }}">

                        @csrf

                        <div class="mb-3">
                            <label class="form-label fw-semibold">
                                Lesson Topic <span class="text-danger">*</span>
                            </label>

                            <input type="text"
                                   name="topic"
                                   class="form-control @error('topic') is-invalid @enderror"
                                   value="{{ old('topic', $submittedData['topic'] ?? '') }}"
                                   placeholder="e.g. Parts of a Computer"
                                   required>

                            @error('topic')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold">
                                Subject <span class="text-danger">*</span>
                            </label>

                            <select name="subject_id"
                                    class="form-select @error('subject_id') is-invalid @enderror"
                                    required>

                                <option value="">Select subject</option>

                                @foreach($subjects as $subject)
                                    <option value="{{ $subject->id }}"
                                        @selected(old('subject_id', $submittedData['subject_id'] ?? '') == $subject->id)>
                                        {{ $subject->name }}
                                    </option>
                                @endforeach
                            </select>

                            @error('subject_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold">
                                Class <span class="text-danger">*</span>
                            </label>

                            <select name="student_class_id"
                                    class="form-select @error('student_class_id') is-invalid @enderror"
                                    required>

                                <option value="">Select class</option>

                                @foreach($studentClasses as $studentClass)
                                    <option value="{{ $studentClass->id }}"
                                        @selected(old('student_class_id', $submittedData['student_class_id'] ?? '') == $studentClass->id)>
                                        {{ $studentClass->name }}
                                    </option>
                                @endforeach
                            </select>

                            @error('student_class_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold">
                                Academic Year
                            </label>

                            <select name="academic_year_id"
                                    class="form-select">

                                <option value="">Select academic year</option>

                                @foreach($academicYears as $academicYear)
                                    <option value="{{ $academicYear->id }}"
                                        @selected(old('academic_year_id', $submittedData['academic_year_id'] ?? '') == $academicYear->id)>
                                        {{ $academicYear->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold">
                                Term
                            </label>

                            <select name="term_id"
                                    class="form-select">

                                <option value="">Select term</option>

                                @foreach($terms as $term)
                                    <option value="{{ $term->id }}"
                                        @selected(old('term_id', $submittedData['term_id'] ?? '') == $term->id)>
                                        {{ $term->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold">
                                Lesson Duration
                            </label>

                            <select name="duration" class="form-select">
                                @php
                                    $selectedDuration = old(
                                        'duration',
                                        $submittedData['duration'] ?? '40 minutes'
                                    );
                                @endphp

                                <option value="30 minutes" @selected($selectedDuration === '30 minutes')>
                                    30 minutes
                                </option>

                                <option value="40 minutes" @selected($selectedDuration === '40 minutes')>
                                    40 minutes
                                </option>

                                <option value="45 minutes" @selected($selectedDuration === '45 minutes')>
                                    45 minutes
                                </option>

                                <option value="60 minutes" @selected($selectedDuration === '60 minutes')>
                                    60 minutes
                                </option>

                                <option value="80 minutes" @selected($selectedDuration === '80 minutes')>
                                    80 minutes
                                </option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold">
                                Teaching Style
                            </label>

                            <select name="teaching_style" class="form-select">
                                @php
                                    $selectedStyle = old(
                                        'teaching_style',
                                        $submittedData['teaching_style'] ?? 'Learner-centred'
                                    );
                                @endphp

                                <option value="Learner-centred" @selected($selectedStyle === 'Learner-centred')>
                                    Learner-centred
                                </option>

                                <option value="Discussion-based" @selected($selectedStyle === 'Discussion-based')>
                                    Discussion-based
                                </option>

                                <option value="Practical activity" @selected($selectedStyle === 'Practical activity')>
                                    Practical activity
                                </option>

                                <option value="Demonstration" @selected($selectedStyle === 'Demonstration')>
                                    Demonstration
                                </option>

                                <option value="Group work" @selected($selectedStyle === 'Group work')>
                                    Group work
                                </option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold">
                                Language
                            </label>

                            <input type="text"
                                   name="language"
                                   class="form-control"
                                   value="{{ old('language', $submittedData['language'] ?? 'English') }}"
                                   placeholder="English">
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold">
                                Additional Instructions
                            </label>

                            <textarea name="extra_instructions"
                                      rows="4"
                                      class="form-control"
                                      placeholder="Add any special instructions for the AI...">{{ old('extra_instructions', $submittedData['extra_instructions'] ?? '') }}</textarea>
                        </div>

                        <button type="submit"
                                class="btn btn-primary w-100"
                                id="generateButton">

                            <i class="fas fa-robot me-2"></i>
                            Generate Lesson Note
                        </button>

                    </form>
                </div>
            </div>
        </div>

        <div class="col-lg-7">

            @if(isset($generatedLessonNote))

                <div class="card shadow-sm border-0">
                    <div class="card-header bg-success text-white">
                        <h5 class="mb-0">
                            <i class="fas fa-file-alt me-2"></i>
                            Generated Lesson Note
                        </h5>
                    </div>

                    <div class="card-body">

                        <div class="alert alert-warning">
                            <i class="fas fa-info-circle me-2"></i>
                            Review and edit the generated content before saving it.
                        </div>

                        <h5 class="fw-bold">
                            {{ $generatedLessonNote['description'] ?? 'Generated Lesson Note' }}
                        </h5>

                        <hr>

                        <h6 class="fw-bold">Lesson Content</h6>

                        <div class="border rounded p-3 bg-light mb-4"
                             style="white-space: pre-line;">
                            {{ $generatedLessonNote['content'] ?? '' }}
                        </div>

                        <h6 class="fw-bold">Learning Objectives</h6>

                        <ul>
                            @foreach($generatedLessonNote['learning_objectives'] ?? [] as $objective)
                                <li>{{ $objective }}</li>
                            @endforeach
                        </ul>

                        <h6 class="fw-bold">Learning Outcomes</h6>

                        <ul>
                            @foreach($generatedLessonNote['learning_outcomes'] ?? [] as $outcome)
                                <li>{{ $outcome }}</li>
                            @endforeach
                        </ul>

                        <h6 class="fw-bold">Teaching Aids</h6>

                        <ul>
                            @foreach($generatedLessonNote['teaching_aids'] ?? [] as $aid)
                                <li>{{ $aid }}</li>
                            @endforeach
                        </ul>

                        <h6 class="fw-bold">Assessment Methods</h6>

                        <ul>
                            @foreach($generatedLessonNote['assessment_methods'] ?? [] as $assessment)
                                <li>{{ $assessment }}</li>
                            @endforeach
                        </ul>

                        <h6 class="fw-bold">Homework</h6>

                        <p>
                            {{ $generatedLessonNote['homework'] ?? '' }}
                        </p>

                        <h6 class="fw-bold">Remarks</h6>

                        <p>
                            {{ $generatedLessonNote['remarks'] ?? '' }}
                        </p>

                        <h6 class="fw-bold">Recommendations</h6>

                        <p>
                            {{ $generatedLessonNote['recommendations'] ?? '' }}
                        </p>

                        <h6 class="fw-bold">Possible Challenges</h6>

                        <p>
                            {{ $generatedLessonNote['challenges'] ?? '' }}
                        </p>

                        <h6 class="fw-bold">Delivery Method</h6>

                        <p>
                            {{ $generatedLessonNote['delivery_method'] ?? '' }}
                        </p>

                        <form method="POST"
                              action="{{ route('ai.lesson-notes.use-generated') }}"
                              class="mt-4">

                            @csrf

                            <input type="hidden"
                                   name="generated_json"
                                   value="{{ json_encode($generatedLessonNote) }}">

                            <input type="hidden"
                                   name="topic"
                                   value="{{ $submittedData['topic'] ?? '' }}">

                            <input type="hidden"
                                   name="subject_id"
                                   value="{{ $submittedData['subject_id'] ?? '' }}">

                            <input type="hidden"
                                   name="student_class_id"
                                   value="{{ $submittedData['student_class_id'] ?? '' }}">

                            <input type="hidden"
                                   name="academic_year_id"
                                   value="{{ $submittedData['academic_year_id'] ?? '' }}">

                            <input type="hidden"
                                   name="term_id"
                                   value="{{ $submittedData['term_id'] ?? '' }}">

                            <button type="submit"
                                    class="btn btn-success w-100">

                                <i class="fas fa-file-import me-2"></i>
                                Use This in Lesson Note Form
                            </button>
                        </form>

                    </div>
                </div>

            @else

                <div class="card shadow-sm border-0 h-100">
                    <div class="card-body d-flex flex-column justify-content-center align-items-center text-center p-5">

                        <i class="fas fa-robot fa-4x text-primary mb-4"></i>

                        <h4>Generate Your Lesson Note</h4>

                        <p class="text-muted">
                            Complete the lesson information on the left and click
                            <strong>Generate Lesson Note</strong>.
                        </p>

                        <p class="small text-muted mb-0">
                            The generated content will be transferred to your existing
                            Lesson Note creation form for review and editing.
                        </p>

                    </div>
                </div>

            @endif

        </div>

    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const form = document.querySelector('form[action="{{ route('ai.lesson-notes.generate') }}"]');
    const button = document.getElementById('generateButton');

    if (form && button) {
        form.addEventListener('submit', function () {
            button.disabled = true;

            button.innerHTML = `
                <span class="spinner-border spinner-border-sm me-2"></span>
                Generating lesson note...
            `;
        });
    }
});
</script>
@endpush