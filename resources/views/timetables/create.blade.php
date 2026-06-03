@extends('layouts.master')

@section('title','Upload Timetable')

@section('content')

<div class="container-fluid">

    <div class="card shadow">

        <div class="card-header">

            <h4>
                Upload Timetable
            </h4>

        </div>

        <div class="card-body">

            <form action="{{ route('timetables.store') }}"
                  method="POST"
                  enctype="multipart/form-data">

                @csrf

                <div class="row">

                    <div class="col-md-4 mb-3">

                        <label>
                            Academic Year
                        </label>

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

                    <div class="col-md-4 mb-3">

                        <label>
                            Class
                        </label>

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

                </div>

                <div class="mb-3">

                    <label>
                        Title
                    </label>

                    <input type="text"
                           name="title"
                           class="form-control"
                           required>

                </div>

                <div class="mb-3">

                    <label>
                        Description
                    </label>

                    <textarea
                        name="description"
                        rows="4"
                        class="form-control"></textarea>

                </div>

                <div class="mb-3">

                    <label>
                        Upload File
                    </label>

                    <input type="file"
                           name="file"
                           class="form-control"
                           required>

                    <small class="text-muted">
                        PDF, Excel, JPG, PNG
                    </small>

                </div>

                <button class="btn btn-primary">
                    Upload Timetable
                </button>

                <a href="{{ route('timetables.index') }}"
                   class="btn btn-secondary">
                    Cancel
                </a>

            </form>

        </div>

    </div>

</div>

@endsection