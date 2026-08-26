@extends('layouts.master')

@section('title', 'Subject Results')

@section('content')

<div class="container-fluid">

    <div class="card shadow mb-4">

        <div class="card-header">
            <h4 class="mb-0">
                Subject Results
            </h4>
        </div>

        <div class="card-body">

            <form method="GET">

                <div class="row">

                    <div class="col-md-3">

                        <label>Class</label>

                        <select name="student_class_id"
                                class="form-control">

                            <option value="">
                                All Classes
                            </option>

                            @foreach($classes as $class)

                                <option
                                    value="{{ $class->id }}"
                                    {{ request('student_class_id') == $class->id ? 'selected' : '' }}>

                                    {{ $class->name }}

                                </option>

                            @endforeach

                        </select>

                    </div>

                    <div class="col-md-3">

                        <label>Subject</label>

                        <select name="subject_id"
                                class="form-control">

                            <option value="">
                                All Subjects
                            </option>

                            @foreach($subjects as $subject)

                                <option
                                    value="{{ $subject->id }}"
                                    {{ request('subject_id') == $subject->id ? 'selected' : '' }}>

                                    {{ $subject->name }}

                                </option>

                            @endforeach

                        </select>

                    </div>

                    <div class="col-md-3">

                        <label>Academic Year</label>

                        <select name="academic_year_id"
                                class="form-control">

                            <option value="">
                                All Academic Years
                            </option>

                            @foreach($academicYears as $academicYear)

                                <option
                                    value="{{ $academicYear->id }}"
                                    {{ request('academic_year_id') == $academicYear->id ? 'selected' : '' }}>

                                    {{ $academicYear->name }}

                                </option>

                            @endforeach

                        </select>

                    </div>

                    <div class="col-md-3">

                        <label>Term</label>

                        <select name="term_id"
                                class="form-control">

                            <option value="">
                                All Terms
                            </option>

                            @foreach($terms as $term)

                                <option
                                    value="{{ $term->id }}"
                                    {{ request('term_id') == $term->id ? 'selected' : '' }}>

                                    {{ $term->name }}

                                </option>

                            @endforeach

                        </select>

                    </div>

                </div>

                <div class="row mt-3">

                    <div class="col-md-12">

                        <button class="btn btn-primary">
                            Filter Results
                        </button>

                        <a href="{{ route('subject-results.index') }}" class="btn btn-secondary">
                            Reset Filters
                        </a>

                    </div>

                </div>

            </form>

        </div>

    </div>

    <div class="card shadow">

        <div class="card-body">

            <div class="table-responsive">

                <table class="table table-bordered table-striped" id="dataTable">

                    <thead class="table-white text-dark">

                        <tr>

                            <th>#</th>
                            <th>Student</th>
                            <th>Class</th>
                            <th>Subject</th>
                            <th>Class Score</th>
                            <th>Exam Score</th>
                            <th>Total</th>
                            <th>Grade</th>
                            <th>Remark</th>
                            <th>Position</th>
                            <th>Actions</th>

                        </tr>

                    </thead>

                    <tbody>

                        @forelse($results as $result)

                            <tr>

                                <td>{{ $loop->iteration }}</td>

                                <td>
                                    {{ $result->student->first_name }}
                                    {{ $result->student->last_name }}
                                </td>

                                <td>
                                    {{ $result->studentClass->name }}
                                </td>

                                <td>
                                    {{ $result->subject->name }}
                                </td>

                                <td>
                                    {{ $result->class_score }}
                                </td>

                                <td>
                                    {{ $result->exam_score }}
                                </td>

                                <td>
                                    <strong>
                                        {{ $result->total_score }}
                                    </strong>
                                </td>

                                <td>

                                    <span class="badge bg-white text-dark">
                                        {{ $result->grade }}
                                    </span>

                                </td>

                                <td>
                                    {{ $result->remark }}
                                </td>

                                <td>
                                    @if($result->position == 1)
                                        <span class="badge bg-white text-dark">
                                            🏆 {{ $result->position }}
                                        </span>
                                    @elseif($result->position == 2)
                                        <span class="badge bg-white text-dark">
                                            🥈 {{ $result->position }}
                                        </span>
                                    @elseif($result->position == 3)
                                        <span class="badge bg-white text-dark">
                                            🥉 {{ $result->position }}
                                        </span>
                                    @else
                                        <span class="badge bg-white text-dark">
                                            {{ $result->position }}
                                        </span>
                                    @endif
                                </td>

                            </tr>

                        @empty

                            <tr>

                                <td colspan="10" class="text-center">
                                    No results found for the selected filters
                                <td>

                            </tr>

                        @endforelse

                    </tbody>

                </table>

            </div>

            <!-- Pagination -->
            <div class="mt-4">
                {{ $results->withQueryString()->links() }}
            </div>

        </div>

    </div>

</div>

@endsection