@extends('layouts.master')

@section('title','Score Entry')

@section('content')

<div class="card shadow">

    <div class="card-header">
        <h4>Score Entry</h4>
    </div>

    <div class="card-body">

        <form action="{{ route('scores.load-students') }}"
              method="POST">

            @csrf

            <div class="row">

                <div class="col-md-3">

                    <label>Academic Year</label>

                    <select name="academic_year_id"
                            class="form-control"
                            required>

                        <option value="">
                            Select Academic Year
                        </option>

                        @foreach($academicYears as $year)

                        <option value="{{ $year->id }}">
                            {{ $year->name }}
                        </option>

                        @endforeach

                    </select>

                </div>

                <div class="col-md-3">

                    <label>Term</label>

                    <select name="term_id"
                            class="form-control"
                            required>

                        <option value="">
                            Select Term
                        </option>

                        @foreach($terms as $term)

                        <option value="{{ $term->id }}">
                            {{ $term->name }}
                        </option>

                        @endforeach

                    </select>

                </div>

                <div class="col-md-3">

                    <label>Class</label>

                    <select name="student_class_id"
                            class="form-control"
                            required>

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

                <div class="col-md-3">

                    <label>Subject</label>

                    <select name="subject_id"
                            class="form-control"
                            required>

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

            <button class="btn btn-primary mt-3">
                Load Students
            </button>

        </form>

    </div>

</div>

@endsection