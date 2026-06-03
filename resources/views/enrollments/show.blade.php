@extends('layouts.master')

@section('title', 'Enrollment Details')

@section('content')

<div class="container-fluid">

    <div class="card shadow-sm">

        <div class="card-header">
            <h4>Enrollment Details</h4>
        </div>

        <div class="card-body">

            <div class="row">

                <div class="col-md-6 mb-3">
                    <strong>Student:</strong><br>
                    {{ $enrollment->student->full_name ?? '-' }}
                </div>

                <div class="col-md-6 mb-3">
                    <strong>Student ID:</strong><br>
                    {{ $enrollment->student->student_id ?? '-' }}
                </div>

                <div class="col-md-6 mb-3">
                    <strong>Class:</strong><br>
                    {{ $enrollment->studentClass->name ?? '-' }}
                </div>

                <div class="col-md-6 mb-3">
                    <strong>Academic Year:</strong><br>
                    {{ $enrollment->academicYear->name ?? '-' }}
                </div>

                <div class="col-md-6 mb-3">
                    <strong>Enrollment Date:</strong><br>
                    {{ $enrollment->enrollment_date ?? '-' }}
                </div>

                <div class="col-md-6 mb-3">
                    <strong>Status:</strong><br>

                    <span class="badge bg-{{ $enrollment->is_active ? 'success' : 'danger' }}">
                        {{ $enrollment->is_active ? 'Active' : 'Inactive' }}
                    </span>
                </div>

                <div class="col-md-12 mb-3">
                    <strong>Remarks:</strong><br>
                    {{ $enrollment->remarks ?? '-' }}
                </div>

            </div>

            <a href="{{ route('enrollments.index') }}"
               class="btn btn-secondary">
                Back
            </a>

        </div>

    </div>

</div>

@endsection