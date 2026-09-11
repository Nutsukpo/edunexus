@extends('layouts.master')

@section('title', 'Score Entry')

@section('content')

<div class="container-fluid">

    <div class="card shadow-sm border-0">

        <div class="card-header bg-primary text-white py-3">

            <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">

                <div>
                    <h5 class="mb-1 fw-bold">
                        <i class="fas fa-edit me-2"></i>
                        Score Entry
                    </h5>

                    <small class="opacity-75">
                        Enter student scores for the current academic period.
                    </small>
                </div>

                @if($academicYear && $term)

                    <div class="text-end">

                        <div class="small opacity-75">
                            Current Academic Period
                        </div>

                        <strong>
                            {{ $academicYear->name }}
                            <span class="mx-1">•</span>
                            {{ $term->name }}
                        </strong>

                    </div>

                @endif

            </div>

        </div>

        <div class="card-body">

            @if(!$academicYear || !$term)

                <div class="alert alert-danger">

                    <i class="fas fa-exclamation-triangle me-2"></i>

                    <strong>Academic period not configured.</strong>

                    Please configure the current Academic Year and Term under
                    <strong>System Settings</strong> before entering scores.

                </div>

            @else

                <div class="alert alert-light border mb-4">

                    <div class="d-flex align-items-center gap-2 flex-wrap">

                        <i class="fas fa-calendar-check text-primary"></i>

                        <span>
                            Scores will be entered for:
                        </span>

                        <span class="badge bg-primary">
                            {{ $academicYear->name }}
                        </span>

                        <span class="badge bg-success">
                            {{ $term->name }}
                        </span>

                    </div>

                    @if($term->start_date && $term->end_date)

                        <div class="small text-muted mt-2">

                            <i class="fas fa-clock me-1"></i>

                            {{ \Carbon\Carbon::parse($term->start_date)->format('d M Y') }}
                            —
                            {{ \Carbon\Carbon::parse($term->end_date)->format('d M Y') }}

                        </div>

                    @endif

                </div>

                <form
                    action="{{ route('scores.load-students') }}"
                    method="POST"
                >

                    @csrf

                    <div class="row g-3">

                        {{-- ACADEMIC YEAR --}}

                        <div class="col-md-4">

                            <label class="form-label fw-semibold">
                                Academic Year
                            </label>

                            <div class="form-control bg-light">

                                <i class="fas fa-calendar-alt text-primary me-2"></i>

                                {{ $academicYear->name }}

                            </div>

                            <input
                                type="hidden"
                                name="academic_year_id"
                                value="{{ $academicYear->id }}"
                            >

                            <div class="form-text">
                                Controlled by System Settings.
                            </div>

                        </div>

                        {{-- TERM --}}

                        <div class="col-md-4">

                            <label class="form-label fw-semibold">
                                Term
                            </label>

                            <div class="form-control bg-light">

                                <i class="fas fa-clock text-success me-2"></i>

                                {{ $term->name }}

                            </div>

                            <input
                                type="hidden"
                                name="term_id"
                                value="{{ $term->id }}"
                            >

                            <div class="form-text">
                                Controlled by System Settings.
                            </div>

                        </div>

                        {{-- CLASS --}}

                        <div class="col-md-4">

                            <label
                                for="student_class_id"
                                class="form-label fw-semibold"
                            >
                                Class
                            </label>

                            <select
                                name="student_class_id"
                                id="student_class_id"
                                class="form-select"
                                required
                            >

                                <option value="">
                                    Select Class
                                </option>

                                @foreach($classes as $class)

                                    <option value="{{ $class->id }}">
                                        {{ $class->name }}
                                    </option>

                                @endforeach

                            </select>

                        </div>

                        {{-- SUBJECT --}}

                        <div class="col-md-6">

                            <label
                                for="subject_id"
                                class="form-label fw-semibold"
                            >
                                Subject
                            </label>

                            <select
                                name="subject_id"
                                id="subject_id"
                                class="form-select"
                                required
                            >

                                <option value="">
                                    Select Subject
                                </option>

                                @foreach($subjects as $subject)

                                    <option value="{{ $subject->id }}">
                                        {{ $subject->name }}
                                    </option>

                                @endforeach

                            </select>

                        </div>

                    </div>

                    <div class="mt-4">

                        <button
                            type="submit"
                            class="btn btn-primary"
                        >

                            <i class="fas fa-users me-2"></i>

                            Load Students

                        </button>

                    </div>

                </form>

            @endif

        </div>

    </div>

</div>

@endsection