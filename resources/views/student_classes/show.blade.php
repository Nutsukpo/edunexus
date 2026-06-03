@extends('layouts.master')

@section('title', 'Class Details')

@section('content')

@php
    $activeAssignments = $studentClass->assignments
        ->where('is_current', true);
@endphp

<div class="container-fluid">

    {{-- PAGE HEADER --}}
    <div class="d-flex justify-content-between align-items-center mb-4 mt-3">

        <div>
            <h5 class="fw-bold mb-1">
                {{ $studentClass->name }}
            </h5>

            <p class="text-muted mb-0">
                Manage class details, students, attendance and more
            </p>
        </div>

    </div>

    {{-- SUMMARY CARDS --}}
    <div class="row mb-0">

    @php
    $students = $activeAssignments->pluck('student');
    $totalStudents = $students->count();
    $maleCount = $students->where('gender', 'Male')->count();
    $femaleCount = $students->where('gender', 'Female')->count();
    @endphp

    <div class="col-md-3 mb-3">

        <div class="card border-0 shadow-sm h-100">

            <div class="card-body">

                <div class="d-flex justify-content-between align-items-start">

                    <div>

                        <small class="text-muted text-uppercase fw-semibold">
                            Total Students
                        </small>

                        <h3 class="fw-bold mt-2 mb-2">
                            {{ $totalStudents }}
                        </h3>

                        {{-- GENDER BREAKDOWN --}}
                        <div class="d-flex gap-2 flex-wrap">

                            <span class="badge bg-light text-dark">
                                <i class="fas fa-male me-1"></i>
                                Male: {{ $maleCount }}
                            </span>

                            <span class="badge bg-light text-dark">
                                <i class="fas fa-female me-1"></i>
                                Female: {{ $femaleCount }}
                            </span>

                        </div>

                    </div>

                    <div class="bg-primary bg-opacity-10 rounded-circle p-3">

                        <i class="fas fa-user-graduate text-dark fs-4"></i>

                    </div>

                </div>

            </div>

        </div>

    </div>

        <div class="col-md-3 mb-3">

            <div class="card border-0 shadow-sm h-100">

                <div class="card-body">

                    <div class="d-flex justify-content-between align-items-center">

                        <div>

                            <small class="text-muted text-uppercase fw-semibold">
                                Fees Paid
                            </small>

                            <h3 class="fw-bold text-dark mt-2 mb-0">
                                {{ $feesPaidPercentage ?? 0 }}%
                            </h3>

                        </div>

                        <div class="bg-success bg-opacity-10 rounded-circle p-3">

                            <i class="fas fa-wallet text-success fs-4"></i>

                        </div>

                    </div>

                </div>

            </div>

        </div>

        <div class="col-md-3 mb-3">

            <div class="card border-0 shadow-sm h-100">

                <div class="card-body">

                    <div class="d-flex justify-content-between align-items-center">

                        <div>

                            <small class="text-muted text-uppercase fw-semibold">
                                Class Teacher
                            </small>

                            @if($studentClass->classTeacher)

                                <h6 class="fw-bold mt-2 mb-0">

                                    {{ $studentClass->classTeacher->first_name }}
                                    {{ $studentClass->classTeacher->last_name }}

                                </h6>

                            @else

                                <span class="badge bg-warning mt-2">
                                    Not Assigned
                                </span>

                            @endif

                        </div>

                        <div class="bg-dark bg-opacity-10 rounded-circle p-3">

                            <i class="fas fa-chalkboard-teacher text-dark fs-4"></i>

                        </div>

                    </div>

                </div>

            </div>

        </div>

        <div class="col-md-3 mb-3">

            <div class="card border-0 shadow-sm h-100">

                <div class="card-body">

                    <div class="d-flex justify-content-between align-items-center">

                        <div>

                            <small class="text-muted text-uppercase fw-semibold">
                                Attendance Rate
                            </small>

                            <h3 class="fw-bold mt-2 mb-0">
                                {{ $attendanceRate ?? 0 }}%
                            </h3>

                        </div>

                        <div class="bg-warning bg-opacity-10 rounded-circle p-3">

                            <i class="fas fa-chart-line text-warning fs-4"></i>

                        </div>

                    </div>

                </div>

            </div>

        </div>

    </div>

    {{-- NAVIGATION TABS --}}
    <div class="card border-0 shadow-sm mb-1 mt-0 ">

        <div class="card-body p-0">

            <ul class="nav nav-tabs nav-fill">

                <li class="nav-item">
                    <button class="nav-link active bg-light text-dark"
                            data-bs-toggle="tab"
                            data-bs-target="#overview">

                        <i class="fas fa-chart-pie me-2"></i>
                        Overview

                    </button>
                </li>

                <li class="nav-item bg-light text-dark">
                    <button class="nav-link bg-light text-dark"
                            data-bs-toggle="tab"
                            data-bs-target="#students">

                        <i class="fas fa-users me-2"></i>
                        Students

                    </button>
                </li>
                <li class="nav-item bg-light text-dark">
                    <button class="nav-link bg-light text-dark"
                            data-bs-toggle="tab"
                            data-bs-target="#students">

                        <i class="fas fa-users me-2"></i>
                        Fees
                    </button>
                </li>

                <li class="nav-item">
                    <button class="nav-link bg-light text-dark"
                            data-bs-toggle="tab"
                            data-bs-target="#attendance">

                        <i class="fas fa-calendar-check me-2"></i>
                        Attendance

                    </button>
                </li>

                <li class="nav-item">
                    <button class="nav-link bg-light text-dark"
                            data-bs-toggle="tab"
                            data-bs-target="#subjects">

                        <i class="fas fa-book-open me-2"></i>
                        Subjects

                    </button>
                </li>

                <li class="nav-item">
                    <button class="nav-link bg-light text-dark"
                            data-bs-toggle="tab"
                            data-bs-target="#prefect">

                        <i class="fas fa-user-shield me-2"></i>
                        Prefect

                    </button>
                </li>

                <li class="nav-item">
                    <button class="nav-link bg-light text-dark"
                            data-bs-toggle="tab"
                            data-bs-target="#prefect">

                        <i class="fas fa-user-shield me-2"></i>
                        Results

                    </button>
                </li>
                <li class="nav-item">
                    <button class="nav-link bg-light text-dark"
                            data-bs-toggle="tab"
                            data-bs-target="#prefect">

                        <i class="fas fa-user-shield me-2"></i>
                        Class Exercise

                    </button>
                </li>

                <li class="nav-item">
                    <button class="nav-link bg-light text-dark"
                            data-bs-toggle="tab"
                            data-bs-target="#prefect">

                        <i class="fas fa-user-shield me-2"></i>
                        Assignment

                    </button>
                </li>

            </ul>

        </div>

    </div>

    {{-- TAB CONTENT --}}
    <div class="tab-content">

        {{-- OVERVIEW --}}
        <div class="tab-pane fade show active"
             id="overview">

            <div class="card border-0 shadow-sm">

                <div class="card-header bg-white">

                    <h5 class="fw-bold mb-0">
                        Class Information
                    </h5>

                </div>

                <div class="card-body">

                    <div class="row">

                        <div class="col-md-6">

                            <table class="table table-borderless">

                                <tr>
                                    <th width="220">Class Name</th>
                                    <td>{{ $studentClass->name }}</td>
                                </tr>

                                <tr>
                                    <th>Education Type</th>
                                    <td>{{ $studentClass->education_type ?? 'N/A' }}</td>
                                </tr>

                                <tr>
                                    <th>Class Type</th>
                                    <td>{{ $studentClass->class_type ?? 'N/A' }}</td>
                                </tr>

                                <tr>
                                    <th>Stream</th>
                                    <td>{{ $studentClass->stream ?? 'N/A' }}</td>
                                </tr>

                            </table>

                        </div>

                        <div class="col-md-6">

                            <table class="table table-borderless">

                                <tr>
                                    <th width="220">Students</th>
                                    <td>
                                        <span class="badge bg-dark">
                                            {{ $activeAssignments->count() }}
                                        </span>
                                    </td>
                                </tr>

                                <tr>
                                    <th>Teacher</th>

                                    <td>

                                        @if($studentClass->classTeacher)

                                            {{ $studentClass->classTeacher->first_name }}
                                            {{ $studentClass->classTeacher->last_name }}

                                        @else

                                            <span class="badge bg-warning">
                                                Not Assigned
                                            </span>

                                        @endif

                                    </td>

                                </tr>

                                <tr>
                                    <th>Attendance</th>

                                    <td>

                                        <span class="badge bg-success">
                                            {{ $attendanceRate ?? 0 }}%
                                        </span>

                                    </td>

                                </tr>

                            </table>

                        </div>

                    </div>

                </div>

            </div>

        </div>

        {{-- STUDENTS --}}
        <div class="tab-pane fade"
             id="students">

            <div class="card border-0 shadow-sm">

                <div class="card-body table-responsive">

                    <table class="table table-hover align-middle">

                        <thead class="table-light">

                            <tr>

                                <th>#</th>
                                <th>Student</th>
                                <th>Student ID</th>
                                <th>Gender</th>
                                <!-- <th>DOB</th> -->
                                <th>Status</th>
                                <th>Actions</th>

                            </tr>

                        </thead>

                        <tbody>

                            @forelse($activeAssignments as $assignment)

                                <tr>

                                    <td>
                                        {{ $loop->iteration }}
                                    </td>

                                    <td class="fw-semibold">

                                        {{ $assignment->student->first_name }}
                                        {{ $assignment->student->middle_name }}
                                        {{ $assignment->student->last_name }}

                                    </td>

                                    <td>

                                        <span class="badge bg-light text-dark">

                                            {{ $assignment->student->student_id ?? 'N/A' }}

                                        </span>

                                    </td>

                                    <td>

                                        @if($assignment->student->gender == 'Male')

                                            <span class="badge bg-light text-dark">
                                                Male
                                            </span>

                                        @elseif($assignment->student->gender == 'Female')

                                            <span class="badge bg-light text-dark">
                                                Female
                                            </span>

                                        @else

                                            <span class="badge bg-secondary">
                                                N/A
                                            </span>

                                        @endif

                                    </td>

                                    <!-- <td>

                                        {{ optional($assignment->student->date_of_birth)->format('d M Y') ?? 'N/A' }}

                                    </td> -->

                                    <td>

                                        <span class="badge bg-success">
                                            Active
                                        </span>

                                    </td>

                                    <td>

                                        <div class="btn-group btn-group-sm">

                                            <a href="{{ route('students.show', $assignment->student->id) }}"
                                               class="btn btn-light">

                                                <i class="fas fa-eye"></i>

                                            </a>
<!-- 
                                            <button type="button"
                                                    class="btn btn-outline-danger"
                                                    data-bs-toggle="modal"
                                                    data-bs-target="#removeStudentModal"
                                                    data-student-id="{{ $assignment->student->id }}"
                                                    data-student-name="{{ $assignment->student->first_name }} {{ $assignment->student->last_name }}">

                                                <i class="fas fa-trash"></i>

                                            </button> -->

                                        </div>

                                    </td>

                                </tr>

                            @empty

                                <tr>

                                    <td colspan="7"
                                        class="text-center py-5">

                                        No students assigned.

                                    </td>

                                </tr>

                            @endforelse

                        </tbody>

                    </table>

                </div>

            </div>

        </div>

            {{-- ATTENDANCE --}}
    <div class="tab-pane fade" id="attendance">

    <div class="d-flex justify-content-between align-items-center mb-3">

        <div>
            <small class="text-muted">
                All attendance records for this class
            </small>
        </div>

        <a href="{{ route('attendance.create-for-class', $studentClass->id) }}"
        class="btn btn-light text-dark mt-2">

            <i class="fas fa-plus-circle me-1"></i>
            Take Attendance

        </a>

        </div>

            <div class="card border-0 shadow-sm">

                <div class="card-body p-0">

                    <div class="table-responsive">

                        <table class="table table-hover align-middle mb-0">

                            <thead class="table-light">

                                <tr>
                                    <th>#</th>
                                    <th>Date</th>
                                    <th>Present</th>
                                    <th>Absent</th>
                                    <th>Late</th>
                                    <th>Excused</th>
                                    <th>Total Students</th>
                                    <th>Attendance Rate</th>
                                    <th>Taken By</th>
                                    <th width="120">Action</th>
                                </tr>

                            </thead>

                            <tbody>

                                @forelse($attendanceSessions ?? [] as $session)

                                    @php
                                        // Calculate statistics from the session's attendances relationship
                                        // This assumes $session->attendances is loaded
                                        
                                        $presentCount = $session->attendances->where('status', 'present')->count();
                                        $absentCount = $session->attendances->where('status', 'absent')->count();
                                        $lateCount = $session->attendances->where('status', 'late')->count();
                                        $excusedCount = $session->attendances->where('status', 'excused')->count();
                                        
                                        $totalCount = $presentCount + $absentCount + $lateCount + $excusedCount;
                                        
                                        // Calculate rate: Present + Late are considered present for rate
                                        $presentAndLate = $presentCount + $lateCount;
                                        $rate = $totalCount > 0
                                            ? round(($presentAndLate / $totalCount) * 100, 1)
                                            : 0;
                                    @endphp

                                    <tr>
                                        <td>{{ $loop->iteration }}</td>
                                        <td>
                                            <span class="fw-semibold">
                                                {{ \Carbon\Carbon::parse($session->attendance_date)->format('d M Y') }}
                                            </span>
                                        </td>
                                        <td>
                                            <span class="badge bg-light text-dark">
                                                {{ $presentCount }}
                                            </span>
                                        </td>
                                        <td>
                                            <span class="badge bg-light text-dark">
                                                {{ $absentCount }}
                                            </span>
                                        </td>
                                        <td>
                                            <span class="badge bg-light text-dark">
                                                {{ $lateCount }}
                                            </span>
                                        </td>
                                        <td>
                                            <span class="badge bg-light text-dark">
                                                {{ $excusedCount }}
                                            </span>
                                        </td>
                                        <td>
                                            {{ $totalCount }}
                                        </td>
                                        <td>

                                            @php
                                                $badgeClass = 'bg-danger';

                                                if ($rate >= 80) {
                                                    $badgeClass = 'bg-success';
                                                } elseif ($rate >= 60) {
                                                    $badgeClass = 'bg-warning text-dark';
                                                } elseif ($rate >= 40) {
                                                    $badgeClass = 'bg-info';
                                                } else {
                                                    $badgeClass = 'bg-danger';
                                                }
                                            @endphp

                                            <span class="badge {{ $badgeClass }}">
                                                {{ $rate }}%
                                            </span>

                                            @if(isset($excused) && $excused > 0)
                                                <span class="badge bg-info ms-1">
                                                    Excused: {{ $excused }}
                                                </span>
                                            @endif

                                        </td>
                                        <td>
                                            {{ $session->takenBy->name ?? 'System' }}
                                        </td>
                                        <td class="d-flex gap-1">
                                            <a href="{{ route('attendance-sessions.show', $session->id) }}"
                                            class="btn btn-sm btn-light">
                                                <i class="fas fa-eye"></i>
                                            </a>

                                            <!-- <form action="{{ route('attendance-sessions.destroy', $session->id) }}"
                                                method="POST"
                                                onsubmit="return confirm('Delete this attendance session?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-danger">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form> -->
                                        </td>
                                    </tr>

                                @empty

                                    <tr>
                                        <td colspan="9" class="text-center py-5 text-muted">
                                            <i class="fas fa-calendar-times fs-3 mb-2 d-block"></i>
                                            No attendance sessions found for this class.
                                            <div class="mt-3">
                                                <a href="{{ route('attendance.create-for-class', $studentClass->id) }}"
                                                class="btn btn-sm btn-primary">
                                                    <i class="fas fa-plus-circle me-1"></i>
                                                    Take First Attendance
                                                </a>
                                            </div>
                                        </td>
                                    </tr>

                                @endforelse

                            </tbody>

                        </table>

                    </div>

                </div>

            </div>

        </div>

        {{-- SUBJECTS TAB --}}
        <div class="tab-pane fade"
             id="subjects">

            <div class="card border-0 shadow-sm">

                <div class="card-header bg-white d-flex justify-content-between align-items-center">

                    <h5 class="fw-bold mb-0">

                        <i class="fas fa-book-open me-2"></i>
                        Class Subjects

                    </h5>

                    <button class="btn btn-light bg-light text-dark btn-sm"
                            data-bs-toggle="modal"
                            data-bs-target="#addSubjectModal">

                        <i class="fas fa-plus me-1"></i>
                        Add Subject

                    </button>

                </div>

                <div class="card-body">

                    @if(session('subject_success'))
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <i class="fas fa-check-circle me-2"></i>
                            {{ session('subject_success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    @if(session('subject_error'))
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <i class="fas fa-exclamation-circle me-2"></i>
                            {{ session('subject_error') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    @php
                        $classSubjects = $studentClass->subjects ?? collect();
                    @endphp

                    @if($classSubjects->count() > 0)

                        <div class="table-responsive">

                            <table class="table table-bordered align-middle">

                                <thead class="table-light">

                                    <tr>
                                        <th width="50">#</th>
                                        <th>Subject Name</th>
                                        <th>Subject Code</th>
                                        <th>Education Level</th>
                                        <th>Subject Teacher</th>
                                        <th width="100">Actions</th>
                                    </tr>

                                </thead>

                                <tbody>

                                    @foreach($classSubjects as $subject)

                                        <tr>
                                            <td>{{ $loop->iteration }}</td>
                                            <td class="fw-semibold">
                                                <i class="fas fa-book text-dark me-2"></i>
                                                {{ $subject->name }}
                                            </td>
                                            <td>
                                                @if($subject->code)
                                                    <span class="badge bg-light text-dark">
                                                        {{ $subject->code }}
                                                    </span>
                                                @else
                                                    <span class="text-muted">—</span>
                                                @endif
                                            </td>
                                            <td>
                                                @if($subject->education_level)
                                                    <span class="badge bg-light text-dark">
                                                        {{ $subject->education_level }}
                                                    </span>
                                                @else
                                                    <span class="text-muted">—</span>
                                                @endif
                                            </td>
                                            <td>
                                                @if($subject->staff)
                                                    <span class="badge bg-light text-dark">
                                                        {{ $subject->staff->first_name }}
                                                        {{ $subject->staff->last_name }}
                                                    </span>
                                                @else
                                                    <span class="text-muted">—</span>
                                                @endif
                                            </td>
                                    
                                            <td>
                                                <form action="{{ route('classes.subject.detach', [$studentClass->id, $subject->id]) }}"
                                                      method="POST"
                                                      class="d-inline"
                                                      onsubmit="return confirm('Are you sure you want to remove {{ addslashes($subject->name) }} from this class?');">

                                                    @csrf
                                                    @method('DELETE')

                                                    <button type="submit"
                                                            class="btn btn-sm btn-outline-dark">

                                                        <i class="fas fa-trash-alt"></i>

                                                    </button>

                                                </form>
                                            </td>
                                        </tr>

                                    @endforeach

                                </tbody>

                            </table>

                        </div>

                        <div class="mt-3 text-muted small">
                            <i class="fas fa-info-circle me-1"></i>
                            Total subjects assigned: {{ $classSubjects->count() }}
                        </div>

                    @else

                        <div class="text-center py-5">

                            <i class="fas fa-book-open fs-1 text-muted mb-3 d-block"></i>

                            <h5 class="mb-2">
                                No Subjects Assigned
                            </h5>

                            <p class="text-muted mb-4">
                                This class doesn't have any subjects assigned yet.
                            </p>

                            <button class="btn btn-primary"
                                    data-bs-toggle="modal"
                                    data-bs-target="#addSubjectModal">

                                <i class="fas fa-plus me-1"></i>
                                Add First Subject

                            </button>

                        </div>

                    @endif

                </div>

            </div>

        </div>

        {{-- PREFECT --}}
        <div class="tab-pane fade"
             id="prefect">

            <div class="card border-0 shadow-sm">

                <div class="card-header bg-white d-flex justify-content-between align-items-center">

                    <h5 class="fw-bold mb-0">

                        <i class="fas fa-user-shield me-2"></i>
                        Class Prefect

                    </h5>

                    <button class="btn btn-light bg-light text-dark btn-sm"
                            data-bs-toggle="modal"
                            data-bs-target="#assignPrefectModal">

                        <i class="fas fa-user-check me-1"></i>
                        Assign Prefect

                    </button>

                </div>

                <div class="card-body">

                    @if($studentClass->classPrefect &&
                        $activeAssignments->contains('student_id', $studentClass->classPrefect->id))

                        <div class="d-flex align-items-center">

                            <div class="bg-dark text-white rounded-circle d-flex align-items-center justify-content-center me-4"
                                 style="width: 80px; height: 80px;">

                                <i class="fas fa-user-shield fs-2"></i>

                            </div>

                            <div>

                                <h4 class="fw-bold mb-1">

                                    {{ $studentClass->classPrefect->first_name }}
                                    {{ $studentClass->classPrefect->last_name }}

                                </h4>

                                <span class="badge bg-success">

                                    {{ $studentClass->classPrefect->student_id ?? 'N/A' }}

                                </span>

                            </div>

                        </div>

                    @else

                        <div class="text-center py-5">

                            <i class="fas fa-user-slash fs-1 text-muted mb-3 d-block"></i>

                            <h5>
                                No Class Prefect Assigned
                            </h5>

                        </div>

                    @endif

                </div>

            </div>

        </div>

    </div>

</div>

@endsection

@push('modals')

@php

    $assignedStudentIds = $activeAssignments
        ->pluck('student_id')
        ->toArray();

    $availableStudents = \App\Models\Student::whereNotIn('id', $assignedStudentIds)
        ->orderBy('first_name')
        ->orderBy('last_name')
        ->get();

    $assignedSubjectIds = $studentClass->subjects->pluck('id')->toArray();

    $availableSubjects = \App\Models\Subject::whereNotIn('id', $assignedSubjectIds)
        ->orderBy('name')
        ->get();

@endphp

{{-- ADD SUBJECT MODAL --}}
<div class="modal fade"
     id="addSubjectModal"
     tabindex="-1"
     aria-hidden="true">

    <div class="modal-dialog modal-dialog-centered">

        <div class="modal-content border-0 shadow">

            <form action="{{ route('classes.subject.attach', $studentClass->id) }}"
                  method="POST">

                @csrf

                <div class="modal-header bg-primary text-white">

                    <h5 class="modal-title fw-bold">

                        <i class="fas fa-book-open me-2"></i>
                        Assign Subject

                    </h5>

                    <button type="button"
                            class="btn-close btn-close-white"
                            data-bs-dismiss="modal">
                    </button>

                </div>

                <div class="modal-body">

                    @if($availableSubjects->count() > 0)

                        <div class="mb-3">

                            <label class="form-label fw-semibold">
                                Select Subject
                            </label>

                            <select name="subject_id"
                                    class="form-select"
                                    required>

                                <option value="">
                                    -- Choose Subject --
                                </option>

                                @foreach($availableSubjects as $subject)

                                    <option value="{{ $subject->id }}">

                                        {{ $subject->name }}

                                        @if($subject->code)
                                            ({{ $subject->code }})
                                        @endif

                                        @if($subject->education_level)
                                            - {{ $subject->education_level }}
                                        @endif

                                    </option>

                                @endforeach

                            </select>

                        </div>

                        <div class="alert alert-info small mb-0">
                            <i class="fas fa-info-circle me-1"></i>
                            Only subjects not already assigned to this class are shown.
                        </div>

                    @else

                        <div class="text-center py-4">

                            <i class="fas fa-book-open fs-1 text-muted mb-3 d-block"></i>

                            <h5>
                                No Subjects Available
                            </h5>

                            <p class="text-muted mb-0">
                                All subjects are already assigned to this class.
                            </p>

                            <a href="{{ route('subjects.create') }}"
                               class="btn btn-sm btn-outline-primary mt-3">

                                <i class="fas fa-plus me-1"></i>
                                Create New Subject

                            </a>

                        </div>

                    @endif

                </div>

                <div class="modal-footer">

                    <button type="button"
                            class="btn btn-light"
                            data-bs-dismiss="modal">

                        Cancel

                    </button>

                    <button type="submit"
                            class="btn btn-primary"
                            {{ $availableSubjects->count() == 0 ? 'disabled' : '' }}>

                        <i class="fas fa-plus me-1"></i>
                        Assign Subject

                    </button>

                </div>

            </form>

        </div>

    </div>

</div>

{{-- ASSIGN PREFECT MODAL --}}
<div class="modal fade"
     id="assignPrefectModal"
     tabindex="-1"
     aria-hidden="true">

    <div class="modal-dialog modal-dialog-centered">

        <div class="modal-content border-0 shadow">

            <form action="{{ route('student-classes.assign-prefect', $studentClass->id) }}"
                  method="POST">

                @csrf

                <div class="modal-header bg-dark text-white">

                    <h5 class="modal-title fw-bold">

                        <i class="fas fa-user-shield me-2"></i>
                        Assign Class Prefect

                    </h5>

                    <button type="button"
                            class="btn-close btn-close-white"
                            data-bs-dismiss="modal">
                    </button>

                </div>

                <div class="modal-body">

                    @if($activeAssignments->count() > 0)

                        <div class="mb-3">

                            <label class="form-label fw-semibold">
                                Select Student
                            </label>

                            <select name="student_id"
                                    class="form-select"
                                    required>

                                <option value="">
                                    -- Choose Student --
                                </option>

                                @foreach($activeAssignments as $assignment)

                                    <option value="{{ $assignment->student->id }}"
                                        {{ ($studentClass->class_prefect_id ?? '') == $assignment->student->id ? 'selected' : '' }}>

                                        {{ $assignment->student->first_name }}
                                        {{ $assignment->student->last_name }}

                                        ({{ $assignment->student->student_id ?? 'N/A' }})

                                    </option>

                                @endforeach

                            </select>

                        </div>

                    @else

                        <div class="text-center py-4">

                            <i class="fas fa-user-slash fs-1 text-muted mb-3 d-block"></i>

                            <h5>
                                No Students Available
                            </h5>

                            <p class="text-muted mb-0">
                                No active students found in this class.
                            </p>

                        </div>

                    @endif

                </div>

                <div class="modal-footer">

                    <button type="button"
                            class="btn btn-light"
                            data-bs-dismiss="modal">

                        Cancel

                    </button>

                    <button type="submit"
                            class="btn btn-dark"
                            {{ $activeAssignments->count() == 0 ? 'disabled' : '' }}>

                        <i class="fas fa-save me-1"></i>
                        Save Prefect

                    </button>

                </div>

            </form>

        </div>

    </div>

</div>

@endpush

@push('scripts')

<script>

    const removeStudentModal = document.getElementById('removeStudentModal');

    if (removeStudentModal) {

        removeStudentModal.addEventListener('show.bs.modal', function (event) {

            const button = event.relatedTarget;

            const studentId = button.getAttribute('data-student-id');

            const studentName = button.getAttribute('data-student-name');

            document.getElementById('studentNameToRemove').textContent = studentName;

            document.getElementById('removeStudentForm').action =
                `/classes/{{ $studentClass->id }}/students/${studentId}/remove`;

        });

    }

</script>

@endpush