@extends('layouts.master')

@section('title','Enter Scores')

@section('content')

<div class="card shadow">

    <div class="card-header">
        <h4>Score Entry Sheet</h4>
    </div>

    <div class="card-body">

        <form action="{{ route('scores.save') }}"
              method="POST">

            @csrf

            <input type="hidden"
                   name="academic_year_id"
                   value="{{ $academic_year_id }}">

            <input type="hidden"
                   name="term_id"
                   value="{{ $term_id }}">

            <input type="hidden"
                   name="student_class_id"
                   value="{{ $student_class_id }}">

            <input type="hidden"
                   name="subject_id"
                   value="{{ $subject_id }}">

            <div class="table-responsive">

                <table class="table table-bordered">

                    <thead>

                        <tr>
                            <th>#</th>
                            <th>Student Name</th>
                            <th>Class Score</th>
                            <th>Exam Score</th>
                        </tr>

                    </thead>

                    <tbody>

                        @foreach($students as $student)

                        <tr>

                            <td>
                                {{ $loop->iteration }}
                            </td>

                            <td>
                                {{ $student->first_name }}
                                {{ $student->last_name }}


                                <input
                                    type="hidden"
                                    name="results[{{ $loop->index }}][student_id]"
                                    value="{{ $student->id }}">

                            </td>

                            <td>

                                <input
                                    type="number"
                                    class="form-control"
                                    name="results[{{ $loop->index }}][class_score]"
                                    min="0"
                                    max="30"
                                    required>

                            </td>

                            <td>

                                <input
                                    type="number"
                                    class="form-control"
                                    name="results[{{ $loop->index }}][exam_score]"
                                    min="0"
                                    max="70"
                                    required>

                            </td>

                        </tr>

                        @endforeach

                    </tbody>

                </table>

            </div>

            <button class="btn btn-success">
                Save Scores
            </button>

        </form>

    </div>

</div>

@endsection