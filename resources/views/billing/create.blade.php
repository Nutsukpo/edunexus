@extends('layouts.master')

@section('title', 'Generate Invoice')

@section('content')

<div class="container-fluid">

    <div class="card border-0 shadow-sm">

        <div class="card-header bg-white">
            <h5 class="mb-0 fw-bold">Generate Student Invoice</h5>
        </div>

        <div class="card-body">

            {{-- SUCCESS / ERROR MESSAGES --}}
            @if(session('success'))
                <div class="alert alert-success">
                    {{ session('success') }}
                </div>
            @endif

            @if(session('error'))
                <div class="alert alert-danger">
                    {{ session('error') }}
                </div>
            @endif

            {{-- VALIDATION ERRORS --}}
            @if ($errors->any())
                <div class="alert alert-danger">
                    <ul class="mb-0">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="POST" action="{{ route('billing.store') }}">
                @csrf

                <div class="row">

                    {{-- STUDENT --}}
                    <div class="col-md-4 mb-3">
                        <label class="form-label fw-semibold">Student</label>

                        <select name="student_id" class="form-select" required>
                            <option value="">-- Select Student --</option>

                            @foreach($students as $student)
                                <option value="{{ $student->id }}">
                                    {{ $student->student_id }} - {{ $student->first_name }} {{ $student->last_name }}
                                </option>
                            @endforeach

                        </select>
                    </div>

                    {{-- ACADEMIC YEAR --}}
                    <div class="col-md-4 mb-3">
                        <label class="form-label fw-semibold">Academic Year</label>

                        <select name="academic_year_id" class="form-select" required>
                            <option value="">-- Select Academic Year --</option>

                            @foreach($academicYears as $year)
                                <option value="{{ $year->id }}">
                                    {{ $year->name }}
                                </option>
                            @endforeach

                        </select>
                    </div>

                    {{-- TERM --}}
                    <div class="col-md-4 mb-3">
                        <label class="form-label fw-semibold">Term</label>

                        <select name="term_id" class="form-select" required>
                            <option value="">-- Select Term --</option>

                            @foreach($terms as $term)
                                <option value="{{ $term->id }}">
                                    {{ $term->name }}
                                </option>
                            @endforeach

                        </select>
                    </div>

                </div>

                <div class="mt-4 d-flex justify-content-between">

                    {{-- INFO NOTE --}}
                    <small class="text-muted">
                        Invoice will be generated based on the student’s current class assignment.
                    </small>

                    <button type="submit" class="btn btn-white text-dark px-4">
                        <i class="fas fa-file-invoice me-1"></i>
                        Generate Invoice
                    </button>

                </div>

            </form>

        </div>

    </div>

</div>

@endsection