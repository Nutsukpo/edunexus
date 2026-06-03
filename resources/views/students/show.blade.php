@extends('layouts.master')

@section('title', 'Student Profile')

@section('content')
<div class="container-fluid">

    <div class="card shadow-sm">
        <div class="card-body">

            <div class="row">

                {{-- ================= PHOTO ================= --}}
                <div class="col-md-3 text-center">
            @if($student->photo)
                <img src="{{ asset('storage/' . $student->photo) }}"
                    class="img-fluid rounded shadow-sm mb-3"
                    style="max-height:180px; object-fit:cover;">
            @else
                <div class="bg-light rounded d-flex align-items-center justify-content-center mb-3"
                    style="height:180px;">
                    <span class="text-muted">No Photo</span>
                </div>
            @endif

                {{-- STUDENT NAME --}}
                <h5 class="fw-bold mb-1">{{ $student->full_name }}</h5>

                {{-- STUDENT ID --}}
                <p class="text-muted mb-2">
                    {{ $student->student_id }}
                </p>

                {{-- STATUS --}}
                <span class="badge bg-{{ $student->is_active ? 'success' : 'danger' }}">
                    {{ $student->is_active ? 'Active' : 'Inactive' }}
                </span>

                {{-- CURRENT CLASS --}}
                @php
                    $currentAssignment = $student->classAssignments
                        ->where('is_current', true)
                        ->first();
                @endphp

                <div class="mt-3 bg-white">
                @if($currentAssignment && $currentAssignment->studentClass)
                    <div class="card border-0 bg-light shadow-sm">
                        <div class="card-body py-2">

                            <small class="text-muted d-block">
                                Current Class
                            </small>

                            <strong>
                                {{ $currentAssignment->studentClass->name }}
                            </strong>

                        </div>
                    </div>
                @else
                    <div class="alert alert-warning py-2">
                        No class assigned
                    </div>
                @endif
                

            </div>

    </div>

                {{-- ================= DETAILS ================= --}}
                <div class="col-md-9">

                    {{-- PERSONAL INFO --}}
                    <h5 class="text-dark">Personal Information</h5>
                    <hr>

                    <div class="row">

                        <div class="col-md-6">
                            <p><strong>First Name:</strong> {{ $student->first_name }}</p>
                        </div>

                        <div class="col-md-6">
                            <p><strong>Middle Name:</strong> {{ $student->middle_name ?? '-' }}</p>
                        </div>

                        <div class="col-md-6">
                            <p><strong>Last Name:</strong> {{ $student->last_name }}</p>
                        </div>

                        <div class="col-md-6">
                            <p><strong>Gender:</strong> {{ $student->gender }}</p>
                        </div>

                        <div class="col-md-6">
                            <p><strong>Date of Birth:</strong> {{ $student->date_of_birth ?? '-' }}</p>
                        </div>

                        <div class="col-md-6">
                            <p><strong>Nationality:</strong> {{ $student->nationality ?? '-' }}</p>
                        </div>

                        <div class="col-md-6">
                            <p><strong>Religion:</strong> {{ $student->religion ?? '-' }}</p>
                        </div>

                        <div class="col-md-12">
                            <p><strong>Address:</strong> {{ $student->address ?? '-' }}</p>
                        </div>

                    </div>

                    {{-- DISABILITY --}}
                    <h5 class="text-dark mt-3">Disability Information</h5>
                    <hr>

                    <div class="row">

                        <div class="col-md-6">
                            <p><strong>Has Disability:</strong>
                                {{ $student->has_disability ? 'Yes' : 'No' }}
                            </p>
                        </div>

                        <div class="col-md-6">
                            <p><strong>Disability Type:</strong>
                                {{ $student->disability_type ?? 'None' }}
                            </p>
                        </div>

                    </div>

                    {{-- FATHER --}}
                    <h5 class="text-dark mt-3">Father Information</h5>
                    <hr>

                    <div class="row">

                        <div class="col-md-6">
                            <p><strong>Name:</strong> {{ $student->father_name ?? '-' }}</p>
                        </div>

                        <div class="col-md-6">
                            <p><strong>Phone:</strong> {{ $student->father_phone ?? '-' }}</p>
                        </div>

                        <div class="col-md-6">
                            <p><strong>Email:</strong> {{ $student->father_email ?? '-' }}</p>
                        </div>

                        <div class="col-md-6">
                            <p><strong>Occupation:</strong> {{ $student->father_occupation ?? '-' }}</p>
                        </div>

                    </div>

                    {{-- MOTHER --}}
                    <h5 class="text-dark mt-3">Mother Information</h5>
                    <hr>

                    <div class="row">

                        <div class="col-md-6">
                            <p><strong>Name:</strong> {{ $student->mother_name ?? '-' }}</p>
                        </div>

                        <div class="col-md-6">
                            <p><strong>Phone:</strong> {{ $student->mother_phone ?? '-' }}</p>
                        </div>

                        <div class="col-md-6">
                            <p><strong>Email:</strong> {{ $student->mother_email ?? '-' }}</p>
                        </div>

                        <div class="col-md-6">
                            <p><strong>Occupation:</strong> {{ $student->mother_occupation ?? '-' }}</p>
                        </div>

                    </div>

                    {{-- GUARDIAN --}}
                    <h5 class="text-dark mt-3">Guardian Information</h5>
                    <hr>

                    <div class="row">

                        <div class="col-md-6">
                            <p><strong>Name:</strong> {{ $student->guardian_name ?? '-' }}</p>
                        </div>

                        <div class="col-md-6">
                            <p><strong>Phone:</strong> {{ $student->guardian_phone ?? '-' }}</p>
                        </div>

                        <div class="col-md-6">
                            <p><strong>Email:</strong> {{ $student->guardian_email ?? '-' }}</p>
                        </div>

                    </div>

                    {{-- SCHOOL INFO --}}
                    <h5 class="text-dark mt-3">School Information</h5>
                    <hr>

                    <div class="row">

                        <div class="col-md-6">
                            <p><strong>Student ID:</strong> {{ $student->student_id }}</p>
                        </div>

                        <div class="col-md-6">
                            <p><strong>Admission Date:</strong> {{ $student->admission_date ?? '-' }}</p>
                        </div>

                    </div>

                </div>

            </div>

        </div>
    </div>

</div>
@endsection