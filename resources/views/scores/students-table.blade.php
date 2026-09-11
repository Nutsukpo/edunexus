@extends('layouts.master')

@section('title', 'Enter Scores')

@section('content')

<div class="container-fluid">

    <div class="card shadow-sm border-0">

        <div class="card-header bg-primary text-white">

            <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">

                <div>

                    <h5 class="mb-1 fw-bold">
                        <i class="fas fa-clipboard-list me-2"></i>
                        Score Entry Sheet
                    </h5>

                    <div class="small opacity-75">

                        {{ $studentClass->name }}
                        <span class="mx-1">•</span>
                        {{ $subject->name }}

                    </div>

                </div>

                <div class="text-end">

                    <div>
                        <span class="badge bg-light text-dark">
                            {{ $academicYear->name }}
                        </span>

                        <span class="badge bg-success">
                            {{ $term->name }}
                        </span>
                    </div>

                </div>

            </div>

        </div>

        <div class="card-body">

            <div class="alert alert-light border">

                <div class="d-flex align-items-center gap-2">

                    <i class="fas fa-info-circle text-primary"></i>

                    <span>
                        You are entering scores for
                        <strong>{{ $studentClass->name }}</strong>
                        —
                        <strong>{{ $subject->name }}</strong>
                        —
                        <strong>{{ $academicYear->name }}</strong>
                        —
                        <strong>{{ $term->name }}</strong>.
                    </span>

                </div>

                <div class="small text-muted mt-2">

                    Class Score:
                    <strong>30 marks</strong>

                    <span class="mx-2">+</span>

                    Examination:
                    <strong>70 marks</strong>

                    <span class="mx-2">=</span>

                    Total:
                    <strong>100 marks</strong>

                </div>

            </div>

            @if($students->count() > 0)

                <form
                    action="{{ route('scores.save') }}"
                    method="POST"
                >

                    @csrf

                    {{-- IMPORTANT: PERIOD IS DISPLAYED BUT NOT TRUSTED FROM CLIENT --}}

                    <input
                        type="hidden"
                        name="student_class_id"
                        value="{{ $student_class_id }}"
                    >

                    <input
                        type="hidden"
                        name="subject_id"
                        value="{{ $subject_id }}"
                    >

                    <div class="table-responsive">

                        <table class="table table-bordered table-hover align-middle">

                            <thead class="table-light">

                                <tr>

                                    <th style="width:60px;">
                                        #
                                    </th>

                                    <th>
                                        Student
                                    </th>

                                    <th style="width:180px;">
                                        Class Score
                                        <small class="text-muted">
                                            / 30
                                        </small>
                                    </th>

                                    <th style="width:180px;">
                                        Exam Score
                                        <small class="text-muted">
                                            / 70
                                        </small>
                                    </th>

                                    <th style="width:120px;">
                                        Existing Total
                                    </th>

                                </tr>

                            </thead>

                            <tbody>

                                @foreach($students as $student)

                                    @php
                                        $existing = $existingResults[$student->id] ?? null;
                                    @endphp

                                    <tr>

                                        <td>
                                            {{ $loop->iteration }}
                                        </td>

                                        <td>

                                            <div class="fw-semibold">

                                                {{ $student->first_name }}

                                                @if($student->middle_name)
                                                    {{ $student->middle_name }}
                                                @endif

                                                {{ $student->last_name }}

                                            </div>

                                            @if($student->student_id)

                                                <small class="text-muted">
                                                    {{ $student->student_id }}
                                                </small>

                                            @endif

                                            <input
                                                type="hidden"
                                                name="results[{{ $loop->index }}][student_id]"
                                                value="{{ $student->id }}"
                                            >

                                        </td>

                                        <td>

                                            <input
                                                type="number"
                                                name="results[{{ $loop->index }}][class_score]"
                                                class="form-control score-input"
                                                min="0"
                                                max="30"
                                                step="0.01"
                                                value="{{ old(
                                                    'results.' . $loop->index . '.class_score',
                                                    $existing?->class_score ?? ''
                                                ) }}"
                                                required
                                            >

                                        </td>

                                        <td>

                                            <input
                                                type="number"
                                                name="results[{{ $loop->index }}][exam_score]"
                                                class="form-control score-input"
                                                min="0"
                                                max="70"
                                                step="0.01"
                                                value="{{ old(
                                                    'results.' . $loop->index . '.exam_score',
                                                    $existing?->exam_score ?? ''
                                                ) }}"
                                                required
                                            >

                                        </td>

                                        <td>

                                            @if($existing)

                                                <span class="badge bg-primary">

                                                    {{ number_format(
                                                        (float) $existing->total_score,
                                                        2
                                                    ) }}

                                                </span>

                                                <div class="small text-muted mt-1">

                                                    Grade:
                                                    {{ $existing->grade }}

                                                </div>

                                            @else

                                                <span class="text-muted">
                                                    Not entered
                                                </span>

                                            @endif

                                        </td>

                                    </tr>

                                @endforeach

                            </tbody>

                        </table>

                    </div>

                    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mt-4">

                        <a
                            href="{{ route('scores.index') }}"
                            class="btn btn-outline-secondary"
                        >

                            <i class="fas fa-arrow-left me-2"></i>

                            Back

                        </a>

                        <button
                            type="submit"
                            class="btn btn-success"
                        >

                            <i class="fas fa-save me-2"></i>

                            Save Scores

                        </button>

                    </div>

                </form>

            @else

                <div class="text-center py-5">

                    <i class="fas fa-user-slash fa-3x text-muted mb-3"></i>

                    <h5>
                        No Students Found
                    </h5>

                    <p class="text-muted mb-3">

                        There are no active students currently assigned to
                        <strong>{{ $studentClass->name }}</strong>
                        for
                        <strong>{{ $academicYear->name }}</strong>
                        —
                        <strong>{{ $term->name }}</strong>.

                    </p>

                    <a
                        href="{{ route('scores.index') }}"
                        class="btn btn-outline-primary"
                    >

                        <i class="fas fa-arrow-left me-2"></i>

                        Back to Score Entry

                    </a>

                </div>

            @endif

        </div>

    </div>

</div>

<style>

.score-input {
    min-width: 120px;
}

</style>

@endsection