@extends('layouts.master')

@section('title', 'Subject Results')

@section('content')

<div class="container-fluid">

    {{-- HEADER --}}

    <div class="card shadow-sm border-0 mb-3">

        <div class="card-header bg-primary text-white">

            <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">

                <div>

                    <h5 class="mb-1 fw-bold">
                        <i class="fas fa-chart-bar me-2"></i>
                        Subject Results
                    </h5>

                    <small class="opacity-75">
                        View and review student academic results.
                    </small>

                </div>

                @if($currentAcademicYear && $currentTerm)

                    <div class="text-end">

                        <div class="small opacity-75">
                            Current Academic Period
                        </div>

                        <strong>
                            {{ $currentAcademicYear->name }}
                            <span class="mx-1">•</span>
                            {{ $currentTerm->name }}
                        </strong>

                    </div>

                @endif

            </div>

        </div>

        <div class="card-body">

            {{-- CURRENT PERIOD --}}

            @if($currentAcademicYear && $currentTerm)

                <div class="alert alert-light border mb-4">

                    <div class="d-flex align-items-center flex-wrap gap-2">

                        <i class="fas fa-calendar-check text-primary"></i>

                        <span>
                            Viewing:
                        </span>

                        <span class="badge bg-primary">
                            {{ $currentAcademicYear->name }}
                        </span>

                        <span class="badge bg-success">
                            {{ $currentTerm->name }}
                        </span>

                    </div>

                    <div class="small text-muted mt-2">

                        The current academic year and term are automatically
                        selected from System Settings. You may use the filters
                        below to intentionally view historical results.

                    </div>

                </div>

            @endif

            {{-- FILTERS --}}

            <form
                method="GET"
                action="{{ route('subject-results.index') }}"
            >

                <div class="row g-3">

                    {{-- CLASS --}}

                    <div class="col-md-3">

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
                        >

                            <option value="">
                                All Classes
                            </option>

                            @foreach($classes as $class)

                                <option
                                    value="{{ $class->id }}"
                                    {{ (string) request('student_class_id') === (string) $class->id ? 'selected' : '' }}
                                >

                                    {{ $class->name }}

                                </option>

                            @endforeach

                        </select>

                    </div>

                    {{-- SUBJECT --}}

                    <div class="col-md-3">

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
                        >

                            <option value="">
                                All Subjects
                            </option>

                            @foreach($subjects as $subject)

                                <option
                                    value="{{ $subject->id }}"
                                    {{ (string) request('subject_id') === (string) $subject->id ? 'selected' : '' }}
                                >

                                    {{ $subject->name }}

                                </option>

                            @endforeach

                        </select>

                    </div>

                    {{-- ACADEMIC YEAR --}}

                    <div class="col-md-3">

                        <label
                            for="academic_year_id"
                            class="form-label fw-semibold"
                        >
                            Academic Year
                        </label>

                        <select
                            name="academic_year_id"
                            id="academic_year_id"
                            class="form-select"
                        >

                            @foreach($academicYears as $academicYear)

                                <option
                                    value="{{ $academicYear->id }}"
                                    {{ (int) $selectedAcademicYearId === (int) $academicYear->id ? 'selected' : '' }}
                                >

                                    {{ $academicYear->name }}

                                </option>

                            @endforeach

                        </select>

                    </div>

                    {{-- TERM --}}

                    <div class="col-md-3">

                        <label
                            for="term_id"
                            class="form-label fw-semibold"
                        >
                            Term
                        </label>

                        <select
                            name="term_id"
                            id="term_id"
                            class="form-select"
                        >

                            <option value="">
                                All Terms
                            </option>

                            @foreach($terms as $term)

                                <option
                                    value="{{ $term->id }}"
                                    {{ (int) $selectedTermId === (int) $term->id ? 'selected' : '' }}
                                >

                                    {{ $term->name }}

                                </option>

                            @endforeach

                        </select>

                    </div>

                </div>

                <div class="mt-3">

                    <button
                        type="submit"
                        class="btn btn-primary"
                    >

                        <i class="fas fa-filter me-2"></i>

                        Filter Results

                    </button>

                    <a
                        href="{{ route('subject-results.index') }}"
                        class="btn btn-outline-secondary"
                    >

                        <i class="fas fa-sync-alt me-2"></i>

                        Current Period

                    </a>

                </div>

            </form>

        </div>

    </div>

    {{-- RESULTS --}}

    <div class="card shadow-sm border-0">

        <div class="card-body">

            <div class="table-responsive">

                <table
                    class="table table-bordered table-hover align-middle"
                    id="dataTable"
                >

                    <thead class="table-light">

                        <tr>

                            <th>#</th>

                            <th>
                                Student
                            </th>

                            <th>
                                Class
                            </th>

                            <th>
                                Subject
                            </th>

                            <th>
                                Class Score
                            </th>

                            <th>
                                Exam Score
                            </th>

                            <th>
                                Total
                            </th>

                            <th>
                                Grade
                            </th>

                            <th>
                                Remark
                            </th>

                            <th>
                                Position
                            </th>

                        </tr>

                    </thead>

                    <tbody>

                        @forelse($results as $result)

                            <tr>

                                <td>
                                    {{ $results->firstItem() + $loop->index }}
                                </td>

                                <td>

                                    <div class="fw-semibold">

                                        {{ $result->student->first_name }}

                                        @if($result->student->middle_name)
                                            {{ $result->student->middle_name }}
                                        @endif

                                        {{ $result->student->last_name }}

                                    </div>

                                    @if($result->student->student_id)

                                        <small class="text-muted">
                                            {{ $result->student->student_id }}
                                        </small>

                                    @endif

                                </td>

                                <td>
                                    {{ $result->studentClass->name ?? '—' }}
                                </td>

                                <td>
                                    {{ $result->subject->name ?? '—' }}
                                </td>

                                <td>
                                    {{ number_format((float) $result->class_score, 2) }}
                                </td>

                                <td>
                                    {{ number_format((float) $result->exam_score, 2) }}
                                </td>

                                <td>

                                    <strong>
                                        {{ number_format((float) $result->total_score, 2) }}
                                    </strong>

                                </td>

                                <td>

                                    <span class="badge bg-primary">
                                        {{ $result->grade }}
                                    </span>

                                </td>

                                <td>
                                    {{ $result->remark }}
                                </td>

                                <td>

                                    @if($result->position == 1)

                                        <span class="badge bg-warning text-dark">
                                            🏆 1
                                        </span>

                                    @elseif($result->position == 2)

                                        <span class="badge bg-secondary">
                                            🥈 2
                                        </span>

                                    @elseif($result->position == 3)

                                        <span class="badge bg-info text-dark">
                                            🥉 3
                                        </span>

                                    @elseif($result->position)

                                        <span class="badge bg-light text-dark border">
                                            {{ $result->position }}
                                        </span>

                                    @else

                                        —

                                    @endif

                                </td>

                            </tr>

                        @empty

                            <tr>

                                <td
                                    colspan="10"
                                    class="text-center py-5 text-muted"
                                >

                                    <i class="fas fa-chart-bar fa-3x mb-3 d-block"></i>

                                    <h6 class="fw-bold">
                                        No Results Found
                                    </h6>

                                    <p class="mb-0">
                                        There are no results matching the
                                        selected academic period and filters.
                                    </p>

                                </td>

                            </tr>

                        @endforelse

                    </tbody>

                </table>

            </div>

            @if($results->hasPages())

                <div class="mt-3">

                    {{ $results->withQueryString()->links() }}

                </div>

            @endif

        </div>

    </div>

</div>

<script>

document.addEventListener('DOMContentLoaded', function () {

    const academicYearSelect =
        document.getElementById('academic_year_id');

    const termSelect =
        document.getElementById('term_id');

    if (!academicYearSelect || !termSelect) {
        return;
    }

    academicYearSelect.addEventListener(
        'change',
        function () {

            const academicYearId = this.value;

            termSelect.innerHTML =
                '<option value="">Loading terms...</option>';

            if (!academicYearId) {

                termSelect.innerHTML =
                    '<option value="">All Terms</option>';

                return;
            }

            const url =
                "{{ url('/settings/academic-years') }}"
                + "/"
                + academicYearId
                + "/terms";

            fetch(url, {
                headers: {
                    'Accept': 'application/json'
                }
            })
            .then(response => response.json())
            .then(terms => {

                termSelect.innerHTML =
                    '<option value="">All Terms</option>';

                terms.forEach(term => {

                    const option =
                        document.createElement('option');

                    option.value = term.id;
                    option.textContent = term.name;

                    termSelect.appendChild(option);

                });

            })
            .catch(() => {

                termSelect.innerHTML =
                    '<option value="">Unable to load terms</option>';

            });

        }
    );

});

</script>

@endsection