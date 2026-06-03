@extends('layouts.master')

@section('title', 'Broadsheet')

@section('content')

<div class="container-fluid">

<div class="card shadow">

    <div class="card-header">
        <h4>Generate Broadsheet</h4>
    </div>

    <div class="card-body">

        <form method="POST" action="{{ route('broadsheet.generate') }}">
            @csrf

            <div class="row">

                <div class="col-md-4">
                    <label>Academic Year</label>

                    <select name="academic_year_id" class="form-control" required>
                        <option value="">Select Academic Year</option>

                        @foreach($academicYears as $year)
                            <option value="{{ $year->id }}">
                                {{ $year->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-4">
                    <label>Term</label>

                    <select name="term_id" class="form-control" required>
                        <option value="">Select Term</option>

                        @foreach($terms as $term)
                            <option value="{{ $term->id }}">
                                {{ $term->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-4">
                    <label>Class</label>

                    <select name="student_class_id" class="form-control" required>
                        <option value="">Select Class</option>

                        @foreach($classes as $class)
                            <option value="{{ $class->id }}">
                                {{ $class->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

            </div>

            <div class="mt-3">
                <button type="submit" class="btn btn-primary">
                    Generate Class Results
                </button>
            </div>

        </form>

    </div>

</div>

</div>

@endsection
