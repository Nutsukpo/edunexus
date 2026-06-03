@extends('layouts.master')

@section('title', 'Assign Student To Class')

@section('content')

<div class="container-fluid">

    {{-- HEADER --}}
    <div class="mb-4">

        <h3 class="fw-bold mb-1">
            Assign Student To Class
        </h3>

        <p class="text-muted mb-0">
            Create new student class assignment
        </p>

    </div>

    {{-- ERRORS --}}
    @if ($errors->any())

        <div class="alert alert-danger">

            <ul class="mb-0">

                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach

            </ul>

        </div>

    @endif

    <form action="{{ route('student-class-assignments.store') }}"
          method="POST">

        @csrf

        <div class="card border-0 shadow-sm">

            <div class="card-body">

                <div class="row">

                    {{-- STUDENT --}}
                    <div class="col-md-6 mb-3">

                        <label class="form-label fw-semibold">
                            Student
                        </label>

                        <select name="student_id"
                                class="form-select"
                                required>

                            <option value="">
                                Select Student
                            </option>

                            @foreach($students as $student)

                                <option value="{{ $student->id }}">

                                    {{ $student->first_name }}
                                    {{ $student->last_name }}

                                </option>

                            @endforeach

                        </select>

                    </div>

                    {{-- CLASS --}}
                    <div class="col-md-6 mb-3">

                        <label class="form-label fw-semibold">
                            Class
                        </label>

                        <select name="student_class_id"
                                class="form-select"
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

                    {{-- ACADEMIC YEAR --}}
                    <div class="col-md-6 mb-3">

                        <label class="form-label fw-semibold">
                            Academic Year
                        </label>

                        <select name="academic_year_id"
                                class="form-select">

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

                    {{-- STATUS --}}
                    <div class="col-md-6 mb-3">

                        <label class="form-label fw-semibold">
                            Status
                        </label>

                        <select name="status"
                                class="form-select">

                            <option value="active">
                                Active
                            </option>

                            <option value="repeated">
                                Repeated
                            </option>

                        </select>

                    </div>

                    {{-- ASSIGNED DATE --}}
                    <div class="col-md-6 mb-3">

                        <label class="form-label fw-semibold">
                            Assigned Date
                        </label>

                        <input type="date"
                               name="assigned_date"
                               class="form-control"
                               value="{{ date('Y-m-d') }}">

                    </div>

                </div>

            </div>

            <div class="card-footer bg-white text-end">

                <button type="submit"
                        class="btn btn-primary">

                    <i class="fas fa-save me-1"></i>
                    Save Assignment

                </button>

            </div>

        </div>

    </form>

</div>

@endsection