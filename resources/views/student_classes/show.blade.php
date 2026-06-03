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
                                {{ number_format($attendanceRate ?? 0, 1) }}%
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
                            data-bs-target="#fees">

                        <i class="fas fa-money-bill me-2"></i>
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
                        Class Prefect

                    </button>
                </li>

                <li class="nav-item">
                    <button class="nav-link bg-light text-dark"
                            data-bs-toggle="tab"
                            data-bs-target="#results">

                        <i class="fas fa-chart-line me-2"></i>
                        Results

                    </button>
                </li>
                
                <li class="nav-item">
                    <button class="nav-link bg-light text-dark"
                            data-bs-toggle="tab"
                            data-bs-target="#classTimetable">

                        <i class="fas fa-calendar-alt me-2"></i>
                        Timetable

                    </button>
                </li>

                <li class="nav-item">
                    <button class="nav-link bg-light text-dark"
                            data-bs-toggle="tab"
                            data-bs-target="#promotions">

                        <i class="fas fa-graduation-cap me-2"></i>
                        Promotions

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
                                            {{ number_format($attendanceRate ?? 0, 1) }}%
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

                                        </div>

                                    </td>

                                </tr>

                            @empty

                                <tr>

                                    <td colspan="6"
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

        {{-- FEES TAB --}}
        <div class="tab-pane fade"
             id="fees">

            <div class="card border-0 shadow-sm">

                <div class="card-body text-center py-5">

                    <i class="fas fa-money-bill-wave fs-1 text-muted mb-3 d-block"></i>

                    <h5>Fees Management</h5>

                    <p class="text-muted">
                        Fees module coming soon.
                    </p>

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
                                        $presentCount = $session->attendances->where('status', 'present')->count();
                                        $absentCount = $session->attendances->where('status', 'absent')->count();
                                        $lateCount = $session->attendances->where('status', 'late')->count();
                                        $excusedCount = $session->attendances->where('status', 'excused')->count();
                                        
                                        $totalCount = $presentCount + $absentCount + $lateCount + $excusedCount;
                                        
                                        $presentAndLate = $presentCount + $lateCount;
                                        $rate = $totalCount > 0
                                            ? round(($presentAndLate / $totalCount) * 100, 1)
                                            : 0;
                                        
                                        if ($rate >= 90) {
                                            $badgeClass = 'bg-success';
                                        } elseif ($rate >= 75) {
                                            $badgeClass = 'bg-info';
                                        } elseif ($rate >= 60) {
                                            $badgeClass = 'bg-warning text-dark';
                                        } else {
                                            $badgeClass = 'bg-danger';
                                        }
                                    @endphp

                                    <tr>
                                        <td>{{ $loop->iteration }}</td>
                                        <td>
                                            <span class="fw-semibold">
                                                {{ \Carbon\Carbon::parse($session->attendance_date)->format('d M Y') }}
                                            </span>
                                        </td>
                                        <td>
                                            <span class="badge bg-success text-white">
                                                {{ $presentCount }}
                                            </span>
                                        </td>
                                        <td>
                                            <span class="badge bg-danger text-white">
                                                {{ $absentCount }}
                                            </span>
                                        </td>
                                        <td>
                                            <span class="badge bg-warning text-dark">
                                                {{ $lateCount }}
                                            </span>
                                        </td>
                                        <td>
                                            <span class="badge bg-secondary text-white">
                                                {{ $excusedCount }}
                                            </span>
                                        </td>
                                        <td>
                                            <span class="fw-bold">{{ $totalCount }}</span>
                                        </td>
                                        <td>
                                            <span class="badge {{ $badgeClass }} p-2">
                                                <i class="fas fa-chart-line me-1"></i>
                                                {{ $rate }}%
                                            </span>
                                        </td>
                                        <td>
                                            <small>{{ $session->takenBy->name ?? 'System' }}</small>
                                        </td>
                                        <td>
                                            <a href="{{ route('attendance-sessions.show', $session->id) }}"
                                               class="btn btn-sm btn-light">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                        </td>
                                    </tr>

                                @empty

                                    <tr>
                                        <td colspan="10" class="text-center py-5 text-muted">
                                            <i class="fas fa-calendar-times fs-3 mb-2 d-block"></i>
                                            <h6>No Attendance Records Found</h6>
                                            <p class="mb-3">No attendance sessions have been recorded for this class yet.</p>
                                            <a href="{{ route('attendance.create-for-class', $studentClass->id) }}"
                                               class="btn btn-primary btn-sm">
                                                <i class="fas fa-plus-circle me-1"></i>
                                                Take First Attendance
                                            </a>
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
                                    </td>

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

        {{-- RESULTS TAB WITH BROADSHEET --}}
        <div class="tab-pane fade"
             id="results">

            <div class="card border-0 shadow-sm">

                <div class="card-header bg-white">

                    <h5 class="fw-bold mb-0">

                        <i class="fas fa-chart-line me-2"></i>
                        Class Results 

                    </h5>

                </div>

                <div class="card-body">

                    {{-- Academic Year and Term Selection --}}
                    <div class="row mb-4">
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Academic Year</label>
                            <select id="academicYearSelect" class="form-select">
                                <option value="">Select Academic Year</option>
                                @foreach(\App\Models\AcademicYear::orderBy('name', 'desc')->get() as $year)
                                    <option value="{{ $year->id }}">{{ $year->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Term</label>
                            <select id="termSelect" class="form-select">
                                <option value="">Select Term</option>
                                @foreach(\App\Models\Term::orderBy('name')->get() as $term)
                                    <option value="{{ $term->id }}">{{ $term->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">&nbsp;</label>
                            <button id="loadResultsBtn" class="btn btn-primary d-block w-100">
                                <i class="fas fa-chart-line me-1"></i> Load Results
                            </button>
                        </div>
                    </div>

                    {{-- Search and Filter Section (shown after results load) --}}
                    <div id="filterSection" style="display: none;">
                        <div class="card bg-light mb-3">
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-4">
                                        <input type="text" id="searchInput" class="form-control" placeholder="Search Student Name or ID...">
                                    </div>
                                    <div class="col-md-3">
                                        <select id="positionFilter" class="form-control">
                                            <option value="">All Positions</option>
                                            <option value="top3">Top 3</option>
                                            <option value="top10">Top 10</option>
                                            <option value="others">Others</option>
                                        </select>
                                    </div>
                                    <div class="col-md-3">
                                        <select id="performanceFilter" class="form-control">
                                            <option value="">All Performance</option>
                                            <option value="excellent">Excellent (80+)</option>
                                            <option value="good">Good (70-79)</option>
                                            <option value="average">Average (50-69)</option>
                                            <option value="poor">Poor (&lt;50)</option>
                                        </select>
                                    </div>
                                    <div class="col-md-2">
                                        <button class="btn btn-secondary w-100" onclick="resetFilters()">Reset</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Results Container --}}
                    <div id="resultsContainer">
                        <div class="text-center py-5 text-muted">
                            <i class="fas fa-chart-line fs-1 mb-3 d-block"></i>
                            <h5>No Results Loaded</h5>
                            <p>Please select Academic Year and Term to view results.</p>
                        </div>
                    </div>

                </div>

            </div>

        </div>

        {{-- CLASS TIMETABLE TAB --}}
        <div class="tab-pane fade"
             id="classTimetable">

            <div class="card border-0 shadow-sm">

                <div class="card-header bg-white d-flex justify-content-between align-items-center">

                    <h5 class="fw-bold mb-0">

                        <i class="fas fa-calendar-alt me-2"></i>
                        Class Timetable

                    </h5>

                </div>

                <div class="card-body">

                    @php
                        $timetable = \App\Models\Timetable::where('student_class_id', $studentClass->id)
                            ->orderBy('created_at', 'desc')
                            ->first();
                    @endphp

                    @if($timetable)

                        {{-- Timetable Preview --}}
                        @if(in_array($timetable->file_type, ['pdf']))

                            <iframe
                                src="{{ asset('storage/' . $timetable->file_path) }}"
                                width="100%"
                                height="700px"
                                style="border: 1px solid #ddd; border-radius: 5px;">
                            </iframe>

                        @elseif(in_array($timetable->file_type, ['jpg', 'jpeg', 'png', 'gif']))

                            <div class="text-center">
                                <img
                                    src="{{ asset('storage/' . $timetable->file_path) }}"
                                    class="img-fluid rounded shadow-sm"
                                    alt="Timetable"
                                    style="max-height: 600px; width: auto;">
                            </div>

                        @else

                            <div class="alert alert-info text-center">
                                <i class="fas fa-file-alt fa-2x mb-2 d-block"></i>
                                <p>Preview not available for this file type.</p>
                                <a href="{{ route('timetables.download', $timetable->id) }}" 
                                   class="btn btn-primary">
                                    <i class="fas fa-download me-1"></i> Download File
                                </a>
                            </div>

                        @endif

                    @else

                        <div class="text-center py-5">

                            <i class="fas fa-calendar-alt fs-1 text-muted mb-3 d-block"></i>

                            <h5>No Timetable Uploaded</h5>

                            <p class="text-muted mb-4">
                                No timetable has been uploaded for this class yet.
                            </p>

                            <a href="{{ route('timetables.create', ['class_id' => $studentClass->id]) }}" 
                               class="btn btn-primary">

                                <i class="fas fa-plus me-1"></i>
                                Upload Timetable

                            </a>

                        </div>

                    @endif

                </div>

            </div>

        </div>

        {{-- PROMOTIONS TAB - FIXED BUTTON --}}
        <div class="tab-pane fade"
             id="promotions">

            <div class="card border-0 shadow-sm rounded-4">
                <div class="card-header bg-white py-3">
                    <h5 class="mb-0 fw-bold">
                        <i class="fas fa-arrow-up me-2 text-danger"></i>
                        Student Promotion & Graduation
                    </h5>
                    <p class="text-muted mb-0 mt-1">Manage student promotions, repetitions, and graduations for this class</p>
                </div>
                <div class="card-body">
                    
                    @if(session('success'))
                        <div class="alert alert-success alert-dismissible fade show shadow-sm mb-4">
                            <i class="fas fa-check-circle me-2"></i>
                            {{ session('success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    @if(session('error'))
                        <div class="alert alert-danger alert-dismissible fade show shadow-sm mb-4">
                            <i class="fas fa-exclamation-circle me-2"></i>
                            {{ session('error') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    <!-- Academic Year Selection -->
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Select Academic Year <span class="text-danger">*</span></label>
                            <select id="progressionAcademicYear" class="form-select">
                                <option value="">-- Select Academic Year --</option>
                                @foreach(\App\Models\AcademicYear::orderBy('name', 'desc')->get() as $year)
                                    <option value="{{ $year->id }}">{{ $year->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">&nbsp;</label>
                            <button id="goToProgressionsBtn" class="btn btn-danger w-100" type="button">
                                <i class="fas fa-arrow-right me-1"></i> Manage Progressions
                            </button>
                        </div>
                    </div>

                    <!-- Current Class Info -->
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle me-2"></i>
                        <strong>Current Class:</strong> {{ $studentClass->name }}<br>
                        Click "Manage Progressions" to go to the full student progression page where you can promote, repeat, or graduate students from this class to the next academic level.
                    </div>

                    <!-- Quick Stats -->
                    <div class="row mt-4">
                        <div class="col-md-4">
                            <div class="card bg-light">
                                <div class="card-body text-center">
                                    <h3 class="text-primary mb-0">{{ $totalStudents }}</h3>
                                    <small class="text-muted">Total Students</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card bg-light">
                                <div class="card-body text-center">
                                    <h3 class="text-success mb-0">{{ $maleCount }}</h3>
                                    <small class="text-muted">Male Students</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card bg-light">
                                <div class="card-body text-center">
                                    <h3 class="text-info mb-0">{{ $femaleCount }}</h3>
                                    <small class="text-muted">Female Students</small>
                                </div>
                            </div>
                        </div>
                    </div>

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

<script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>

    // Results loading functionality
    document.getElementById('loadResultsBtn').addEventListener('click', function() {
        const academicYearId = document.getElementById('academicYearSelect').value;
        const termId = document.getElementById('termSelect').value;
        
        if (!academicYearId || !termId) {
            Swal.fire({
                icon: 'warning',
                title: 'Missing Selection',
                text: 'Please select both Academic Year and Term'
            });
            return;
        }
        
        // Show loading state
        const container = document.getElementById('resultsContainer');
        container.innerHTML = `
            <div class="text-center py-5">
                <div class="spinner-border text-primary mb-3" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
                <h5>Loading Results...</h5>
                <p class="text-muted">Please wait while we fetch the data</p>
            </div>
        `;
        
        // Hide filter section initially
        document.getElementById('filterSection').style.display = 'none';
        
        // Make AJAX request to your existing broadsheet controller
        fetch('{{ route("broadsheet.ajax") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
            },
            body: JSON.stringify({
                academic_year_id: academicYearId,
                term_id: termId,
                student_class_id: {{ $studentClass->id }}
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                renderBroadsheet(data);
                document.getElementById('filterSection').style.display = 'block';
                initializeFilters();
            } else {
                container.innerHTML = `
                    <div class="text-center py-5 text-muted">
                        <i class="fas fa-exclamation-triangle fs-1 mb-3 d-block text-warning"></i>
                        <h5>No Results Found</h5>
                        <p>${data.message || 'No results available for the selected criteria.'}</p>
                    </div>
                `;
                document.getElementById('filterSection').style.display = 'none';
            }
        })
        .catch(error => {
            console.error('Error:', error);
            container.innerHTML = `
                <div class="text-center py-5 text-muted">
                    <i class="fas fa-exclamation-circle fs-1 mb-3 d-block text-danger"></i>
                    <h5>Error Loading Results</h5>
                    <p>There was an error loading the results. Please try again.</p>
                </div>
            `;
            document.getElementById('filterSection').style.display = 'none';
        });
    });
    
    function renderBroadsheet(data) {
        const container = document.getElementById('resultsContainer');
        
        let html = `
            <div class="table-responsive">
                <table class="table table-bordered table-hover mb-0" id="broadsheet-table">
                    <thead class="table-primary">
                        <tr>
                            <th class="text-center">#</th>
                            <th style="min-width:220px;">Student Name</th>
        `;
        
        // Subject headers
        data.subjects.forEach(subject => {
            html += `<th class="text-center" style="min-width:90px;">${subject.name}</th>`;
        });
        
        html += `
                            <th class="text-center bg-light">Total</th>
                            <th class="text-center bg-light">Average</th>
                            <th class="text-center bg-light">Position</th>
                        </tr>
                    </thead>
                    <tbody id="broadsheet-tbody">
        `;
        
        // Student rows
        data.students.forEach((student, index) => {
            const studentRank = data.positions[student.id] || '-';
            const averageScore = data.rankings[student.id]?.average || 0;
            const totalScore = data.rankings[student.id]?.total || 0;
            const studentName = student.full_name || student.name || (student.first_name + ' ' + student.last_name);
            
            html += `
                <tr class="student-row" 
                    data-name="${studentName.toLowerCase()}" 
                    data-id="${student.student_id || student.id}" 
                    data-position="${studentRank}" 
                    data-average="${averageScore}">
                    <td class="text-center align-middle">
                        <span class="fw-bold p-2">${student.student_id || student.id}</span>
                    </td>
                    <td class="align-middle">
                        <div class="fw-bold p-2">${studentName}</div>
                    </td>
            `;
            
            // Subject scores
            data.subjects.forEach(subject => {
                const key = student.id + '_' + subject.id;
                const mark = data.results[key]?.total_score || 0;
                const grade = data.results[key]?.grade || '';
                
                html += `
                    <td class="text-center align-middle">
                        <div>
                            <span class="badge bg-white text-dark p-2">${mark}</span>
                        </div>
                        ${grade ? `<small class="d-block text-muted mt-1">${grade}</small>` : ''}
                    </td>
                `;
            });
            
            // Total, Average, Position
            html += `
                    <td class="text-center align-middle">
                        <span class="fw-bold bg-white text-dark">${parseInt(totalScore).toLocaleString()}</span>
                    </td>
                    <td class="text-center align-middle">
                        <span class="badge bg-white text-dark p-2">${averageScore.toFixed(1)}</span>
                    </td>
                    <td class="text-center align-middle">
            `;
            
            if (studentRank == 1) {
                html += `<span class="badge bg-warning text-dark p-2">🏆 1st</span>`;
            } else if (studentRank == 2) {
                html += `<span class="badge bg-secondary p-2">🥈 2nd</span>`;
            } else if (studentRank == 3) {
                html += `<span class="badge bg-danger p-2">🥉 3rd</span>`;
            } else {
                let suffix = 'th';
                if (studentRank == 1) suffix = 'st';
                else if (studentRank == 2) suffix = 'nd';
                else if (studentRank == 3) suffix = 'rd';
                html += `<span class="badge bg-light text-dark border p-2">${studentRank}${suffix}</span>`;
            }
            
            html += `
                    <td>
                </table>
            `;
        });
        
        // Footer
        html += `
                    </tbody>
                    <tfoot>
                        <tr class="table-light fw-bold">
                            <td colspan="${data.subjects.length + 5}">
                                Total Students: ${data.studentCount} |
                                Subjects: ${data.subjectCount} |
                                Class Average: ${data.classAverage.toFixed(1)} |
                                Pass Rate: ${data.passRate}%
                            </td>
                        </tr>
                    </tfoot>
                </table>
            </div>
            <div class="mt-3">
                <button onclick="exportToPDF()" class="btn btn-sm btn-danger">
                    <i class="fas fa-file-pdf"></i> Export PDF
                </button>
                <button onclick="exportToExcel()" class="btn btn-sm btn-success">
                    <i class="fas fa-file-excel"></i> Export Excel
                </button>
            </div>
        `;
        
        container.innerHTML = html;
    }
    
    function initializeFilters() {
        const searchInput = document.getElementById('searchInput');
        const positionFilter = document.getElementById('positionFilter');
        const performanceFilter = document.getElementById('performanceFilter');
        
        if (searchInput) searchInput.addEventListener('keyup', filterRows);
        if (positionFilter) positionFilter.addEventListener('change', filterRows);
        if (performanceFilter) performanceFilter.addEventListener('change', filterRows);
    }
    
    function filterRows() {
        const search = document.getElementById('searchInput')?.value.toLowerCase() || '';
        const position = document.getElementById('positionFilter')?.value || '';
        const performance = document.getElementById('performanceFilter')?.value || '';
        
        const rows = document.querySelectorAll('#broadsheet-tbody .student-row');
        let visibleCount = 0;
        
        rows.forEach(row => {
            let visible = true;
            
            const name = row.getAttribute('data-name') || '';
            const id = row.getAttribute('data-id') || '';
            const rank = parseInt(row.getAttribute('data-position')) || 999;
            const average = parseFloat(row.getAttribute('data-average')) || 0;
            
            // Search filter
            if (search && !name.includes(search) && !id.includes(search)) {
                visible = false;
            }
            
            // Position filter
            if (visible && position) {
                if (position === 'top3' && rank > 3) visible = false;
                if (position === 'top10' && rank > 10) visible = false;
                if (position === 'others' && rank <= 10) visible = false;
            }
            
            // Performance filter
            if (visible && performance) {
                if (performance === 'excellent' && average < 80) visible = false;
                if (performance === 'good' && (average < 70 || average >= 80)) visible = false;
                if (performance === 'average' && (average < 50 || average >= 70)) visible = false;
                if (performance === 'poor' && average >= 50) visible = false;
            }
            
            row.style.display = visible ? '' : 'none';
            if (visible) visibleCount++;
        });
        
        // Update stats if needed
        const totalRows = rows.length;
        const statsDiv = document.getElementById('filterStats');
        if (statsDiv) {
            statsDiv.innerHTML = `Showing ${visibleCount} of ${totalRows} students`;
        }
    }
    
    function resetFilters() {
        const searchInput = document.getElementById('searchInput');
        const positionFilter = document.getElementById('positionFilter');
        const performanceFilter = document.getElementById('performanceFilter');
        
        if (searchInput) searchInput.value = '';
        if (positionFilter) positionFilter.value = '';
        if (performanceFilter) performanceFilter.value = '';
        
        filterRows();
        
        Swal.fire({
            icon: 'success',
            title: 'Filters Reset',
            text: 'All filters have been cleared',
            timer: 1500,
            showConfirmButton: false
        });
    }
    
    function exportToPDF() {
        const element = document.querySelector('#resultsContainer .table-responsive');
        
        if (!element || !element.querySelector('table')) {
            Swal.fire({ icon: 'error', title: 'No Data', text: 'No results to export' });
            return;
        }
        
        Swal.fire({
            title: 'Generating PDF...',
            text: 'Please wait',
            allowOutsideClick: false,
            didOpen: () => Swal.showLoading()
        });
        
        const options = {
            margin: 0.3,
            filename: 'Class_Broadsheet_{{ date("Ymd_His") }}.pdf',
            image: { type: 'jpeg', quality: 0.98 },
            html2canvas: { scale: 2 },
            jsPDF: { unit: 'in', format: 'a3', orientation: 'landscape' }
        };
        
        html2pdf().set(options).from(element).save()
            .then(() => {
                Swal.fire({ icon: 'success', title: 'PDF Downloaded', timer: 1500, showConfirmButton: false });
            })
            .catch(() => {
                Swal.fire({ icon: 'error', title: 'Export Failed', text: 'Error generating PDF' });
            });
    }
    
    function exportToExcel() {
        const table = document.getElementById('broadsheet-table');
        
        if (!table) {
            Swal.fire({ icon: 'error', title: 'No Data', text: 'No results to export' });
            return;
        }
        
        try {
            Swal.fire({
                title: 'Exporting...',
                text: 'Please wait',
                allowOutsideClick: false,
                didOpen: () => Swal.showLoading()
            });
            
            const wb = XLSX.utils.book_new();
            const ws = XLSX.utils.table_to_sheet(table);
            
            // Auto-size columns
            ws['!cols'] = [];
            for (let i = 0; i < table.rows[0]?.cells.length || 0; i++) {
                ws['!cols'].push({ wch: 18 });
            }
            
            XLSX.utils.book_append_sheet(wb, ws, 'Broadsheet');
            XLSX.writeFile(wb, `Class_Broadsheet_{{ date("Ymd_His") }}.xlsx`);
            
            Swal.fire({ icon: 'success', title: 'Excel Downloaded', timer: 1500, showConfirmButton: false });
        } catch(error) {
            Swal.fire({ icon: 'error', title: 'Export Failed', text: error.message });
        }
    }

    // ==================== PROMOTIONS TAB - FIXED BUTTON ====================
    
    // Wait for DOM to be fully loaded
    document.addEventListener('DOMContentLoaded', function() {
        const goToProgressionsBtn = document.getElementById('goToProgressionsBtn');
        
        if (goToProgressionsBtn) {
            // Remove any existing event listeners by cloning and replacing
            const newBtn = goToProgressionsBtn.cloneNode(true);
            goToProgressionsBtn.parentNode.replaceChild(newBtn, goToProgressionsBtn);
            
            // Add click event to the new button
            newBtn.addEventListener('click', function(e) {
                e.preventDefault();
                e.stopPropagation();
                
                const academicYearId = document.getElementById('progressionAcademicYear').value;
                
                if (!academicYearId) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Missing Selection',
                        text: 'Please select Academic Year first',
                        confirmButtonColor: '#d33'
                    });
                    return;
                }
                
                // Build the URL
                const url = '{{ route("student-progressions.index") }}?class_id={{ $studentClass->id }}&academic_year_id=' + academicYearId;
                
                // Show loading state and redirect
                Swal.fire({
                    title: 'Redirecting...',
                    text: 'Taking you to the progression management page',
                    icon: 'info',
                    showConfirmButton: false,
                    timer: 800,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });
                
                // Redirect after a short delay
                setTimeout(function() {
                    window.location.href = url;
                }, 500);
            });
        }
    });

</script>

@endpush

@push('styles')
<style>
    .form-label {
        font-size: 0.85rem;
        font-weight: 600;
        margin-bottom: 0.4rem;
    }
    
    .table th {
        font-size: 13px;
        font-weight: 600;
        white-space: nowrap;
    }
    
    .table td {
        font-size: 13px;
        vertical-align: middle;
    }
    
    .card {
        border-radius: 12px;
    }
    
    select:disabled {
        background-color: #e9ecef;
        cursor: not-allowed;
    }
    
    .badge {
        font-weight: 500;
        padding: 5px 10px;
    }
    
    .table tbody tr:hover {
        background-color: rgba(0,0,0,0.02);
        transition: background-color 0.3s ease;
    }
    
    #goToProgressionsBtn {
        cursor: pointer;
        transition: all 0.3s ease;
    }
    
    #goToProgressionsBtn:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(220, 53, 69, 0.3);
    }
</style>
@endpush