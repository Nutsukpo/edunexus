@extends('layouts.master')

@section('title', 'Class Details')

@section('content')

@php
    $activeAssignments = $studentClass->assignments->where('is_current', true);
    $students = $activeAssignments->pluck('student');
    $totalStudents = $students->count();
    $maleCount = $students->where('gender', 'Male')->count();
    $femaleCount = $students->where('gender', 'Female')->count();
    
    // Check if routes exist
    $hasAttendanceRoutes = Route::has('attendance.ajax') && Route::has('attendance.store.class');
    $hasBroadsheetRoute = Route::has('broadsheet.ajax');
    $hasProgressionRoute = Route::has('student-progressions.index');
@endphp

<div class="container-fluid" id="app-container">

    {{-- PAGE HEADER --}}
    <div class="d-flex justify-content-between align-items-center mb-4 mt-3">
        <div>
            <h5 class="fw-bold mb-1">{{ $studentClass->name }}</h5>
            <p class="text-muted mb-0">Manage class details, students, attendance and more</p>
        </div>
    </div>

    {{-- SUMMARY CARDS --}}
    <div class="row mb-0">
        <div class="col-md-3 mb-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <small class="text-muted text-uppercase fw-semibold">Total Students</small>
                            <h3 class="fw-bold mt-2 mb-2">{{ $totalStudents }}</h3>
                            <div class="d-flex gap-2 flex-wrap">
                                <span class="badge bg-light text-dark">
                                    <i class="fas fa-male me-1"></i> Male: {{ $maleCount }}
                                </span>
                                <span class="badge bg-light text-dark">
                                    <i class="fas fa-female me-1"></i> Female: {{ $femaleCount }}
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
                            <small class="text-muted text-uppercase fw-semibold">Fees Paid</small>
                            <h3 class="fw-bold text-dark mt-2 mb-0">{{ $feesPaidPercentage ?? 0 }}%</h3>
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
                            <small class="text-muted text-uppercase fw-semibold">Class Teacher</small>
                            @if($studentClass->classTeacher)
                                <h6 class="fw-bold mt-2 mb-0">
                                    {{ $studentClass->classTeacher->first_name }}
                                    {{ $studentClass->classTeacher->last_name }}
                                </h6>
                            @else
                                <span class="badge bg-warning mt-2">Not Assigned</span>
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
                        <small class="text-muted text-uppercase fw-semibold">Attendance Rate</small>
                        <h3 class="fw-bold mt-2 mb-0">
                            {{ $attendanceRate }}%
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
</div>
    </div>

    {{-- NAVIGATION TABS --}}
    <div class="card border-0 shadow-sm mb-1 mt-0">
        <div class="card-body p-0">
            <ul class="nav nav-tabs nav-fill" id="classTabs" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#overview" type="button">
                        <i class="fas fa-chart-pie me-2"></i> Overview
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" data-bs-toggle="tab" data-bs-target="#students" type="button">
                        <i class="fas fa-users me-2"></i> Students
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" data-bs-toggle="tab" data-bs-target="#fees" type="button">
                        <i class="fas fa-money-bill me-2"></i> Fees
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" data-bs-toggle="tab" data-bs-target="#attendance" type="button">
                        <i class="fas fa-calendar-check me-2"></i> Attendance
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" data-bs-toggle="tab" data-bs-target="#subjects" type="button">
                        <i class="fas fa-book-open me-2"></i> Subjects
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" data-bs-toggle="tab" data-bs-target="#prefect" type="button">
                        <i class="fas fa-user-shield me-2"></i> Class Prefect
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" data-bs-toggle="tab" data-bs-target="#results" type="button">
                        <i class="fas fa-chart-line me-2"></i> Results
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" data-bs-toggle="tab" data-bs-target="#classTimetable" type="button">
                        <i class="fas fa-calendar-alt me-2"></i> Timetable
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" data-bs-toggle="tab" data-bs-target="#promotions" type="button">
                        <i class="fas fa-graduation-cap me-2"></i> Promotions
                    </button>
                </li>
            </ul>
        </div>
    </div>

    {{-- TAB CONTENT --}}
    <div class="tab-content">

        {{-- OVERVIEW TAB --}}
        <div class="tab-pane fade show active" id="overview">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white">
                    <h5 class="fw-bold mb-0">Class Information</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <table class="table table-borderless">
                                <tr><th width="220">Class Name</th><td>{{ $studentClass->name }}</td></tr>
                                <tr><th>Education Type</th><td>{{ $studentClass->education_type ?? 'N/A' }}</td></tr>
                                <tr><th>Class Type</th><td>{{ $studentClass->class_type ?? 'N/A' }}</td></tr>
                                <tr><th>Stream</th><td>{{ $studentClass->stream ?? 'N/A' }}</td></tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <table class="table table-borderless">
                                <tr>
                                    <th width="220">Students</th>
                                    <td><span class="badge bg-dark">{{ $activeAssignments->count() }}</span></td>
                                </tr>
                                <tr>
                                    <th>Teacher</th>
                                    <td>
                                        @if($studentClass->classTeacher)
                                            {{ $studentClass->classTeacher->first_name }} {{ $studentClass->classTeacher->last_name }}
                                        @else
                                            <span class="badge bg-warning">Not Assigned</span>
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <th>Attendance</th>
                                    <td><span class="badge bg-success">{{ number_format($attendanceRate ?? 0, 1) }}%</span></td>
                                </tr>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- STUDENTS TAB --}}
        <div class="tab-pane fade" id="students">
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
                                    <td>{{ $loop->iteration }}</td>
                                    <td class="fw-semibold">
                                        {{ $assignment->student->first_name }}
                                        {{ $assignment->student->middle_name }}
                                        {{ $assignment->student->last_name }}
                                    </td>
                                    <td><span class="badge bg-light text-dark">{{ $assignment->student->student_id ?? 'N/A' }}</span></td>
                                    <td>
                                        @if($assignment->student->gender == 'Male')
                                            <span class="badge bg-light text-dark">Male</span>
                                        @elseif($assignment->student->gender == 'Female')
                                            <span class="badge bg-light text-dark">Female</span>
                                        @else
                                            <span class="badge bg-secondary">N/A</span>
                                        @endif
                                    </td>
                                    <td><span class="badge bg-success">Active</span></td>
                                    <td>
                                        <div class="btn-group btn-group-sm">
                                            <a href="{{ route('students.show', $assignment->student->id) }}" class="btn btn-light">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center py-5">No students assigned.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- FEES TAB --}}
        <div class="tab-pane fade" id="fees">
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center py-5">
                    <i class="fas fa-money-bill-wave fs-1 text-muted mb-3 d-block"></i>
                    <h5>Fees Management</h5>
                    <p class="text-muted">Fees module coming soon.</p>
                </div>
            </div>
        </div>

        {{-- ATTENDANCE TAB --}}
        <div class="tab-pane fade" id="attendance">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white d-flex justify-content-between align-items-center flex-wrap gap-2">
                    <h5 class="fw-bold mb-0">
                        <i class="fas fa-calendar-check me-2"></i> Class Attendance
                    </h5>
                    <div class="d-flex gap-2 flex-wrap">
                        <button type="button" class="btn btn-primary btn-sm take-attendance-btn" data-bs-toggle="modal" data-bs-target="#takeAttendanceModal">
                            <i class="fas fa-clipboard-check me-1"></i> Take Attendance
                        </button>
                        <button class="btn btn-outline-dark btn-sm" onclick="exportAttendanceToExcel()">
                            <i class="fas fa-download me-1"></i> Export
                        </button>
                        <button class="btn btn-outline-secondary btn-sm" onclick="printAttendance()">
                            <i class="fas fa-print me-1"></i> Print
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    {{-- Attendance Filters --}}
                    <div class="row mb-4">
                        <div class="col-md-3">
                            <label class="form-label fw-semibold">Date</label>
                            <input type="date" id="attendanceDate" class="form-control" 
                                   value="{{ date('Y-m-d') }}" max="{{ date('Y-m-d') }}">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-semibold">Month</label>
                            <select id="attendanceMonth" class="form-select">
                                <option value="">All Months</option>
                                @for($m = 1; $m <= 12; $m++)
                                    <option value="{{ $m }}" {{ date('m') == $m ? 'selected' : '' }}>
                                        {{ date('F', mktime(0, 0, 0, $m, 1)) }}
                                    </option>
                                @endfor
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-semibold">Year</label>
                            <select id="attendanceYear" class="form-select">
                                @php $currentYear = date('Y'); @endphp
                                @for($y = $currentYear; $y >= $currentYear - 2; $y--)
                                    <option value="{{ $y }}" {{ $currentYear == $y ? 'selected' : '' }}>{{ $y }}</option>
                                @endfor
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-semibold">&nbsp;</label>
                            <button id="loadAttendanceBtn" class="btn btn-primary d-block w-100">
                                <i class="fas fa-search me-1"></i> Load Attendance
                            </button>
                        </div>
                    </div>

                    {{-- Attendance Table --}}
                    <div id="attendanceContainer">
                        <div class="text-center py-5 text-muted">
                            <i class="fas fa-calendar-check fs-1 mb-3 d-block"></i>
                            <h5>No Attendance Records</h5>
                            <p>Select a date or month and click "Load Attendance" to view records.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- SUBJECTS TAB --}}
        <div class="tab-pane fade" id="subjects">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                    <h5 class="fw-bold mb-0"><i class="fas fa-book-open me-2"></i> Class Subjects</h5>
                    <button class="btn btn-light bg-light text-dark btn-sm" data-bs-toggle="modal" data-bs-target="#addSubjectModal">
                        <i class="fas fa-plus me-1"></i> Add Subject
                    </button>
                </div>
                <div class="card-body">
                    @if(session('subject_success'))
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <i class="fas fa-check-circle me-2"></i>{{ session('subject_success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif
                    @if(session('subject_error'))
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <i class="fas fa-exclamation-circle me-2"></i>{{ session('subject_error') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    @php $classSubjects = $studentClass->subjects ?? collect(); @endphp

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
                                            <td class="fw-semibold"><i class="fas fa-book text-dark me-2"></i>{{ $subject->name }}</td>
                                            <td>
                                                @if($subject->code)
                                                    <span class="badge bg-light text-dark">{{ $subject->code }}</span>
                                                @else
                                                    <span class="text-muted">—</span>
                                                @endif
                                            </td>
                                            <td>
                                                @if($subject->education_level)
                                                    <span class="badge bg-light text-dark">{{ $subject->education_level }}</span>
                                                @else
                                                    <span class="text-muted">—</span>
                                                @endif
                                            </td>
                                            <td>
                                                @if($subject->staff)
                                                    <span class="badge bg-light text-dark">{{ $subject->staff->first_name }} {{ $subject->staff->last_name }}</span>
                                                @else
                                                    <span class="text-muted">—</span>
                                                @endif
                                            </td>
                                            <td>
                                                <form action="{{ route('classes.subject.detach', [$studentClass->id, $subject->id]) }}" method="POST" class="d-inline"
                                                      onsubmit="return confirm('Are you sure you want to remove {{ addslashes($subject->name) }} from this class?');">
                                                    @csrf @method('DELETE')
                                                    <button type="submit" class="btn btn-sm btn-outline-dark"><i class="fas fa-trash-alt"></i></button>
                                                </form>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        <div class="mt-3 text-muted small">
                            <i class="fas fa-info-circle me-1"></i> Total subjects assigned: {{ $classSubjects->count() }}
                        </div>
                    @else
                        <div class="text-center py-5">
                            <i class="fas fa-book-open fs-1 text-muted mb-3 d-block"></i>
                            <h5 class="mb-2">No Subjects Assigned</h5>
                            <p class="text-muted mb-4">This class doesn't have any subjects assigned yet.</p>
                            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addSubjectModal">
                                <i class="fas fa-plus me-1"></i> Add First Subject
                            </button>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- PREFECT TAB --}}
        <div class="tab-pane fade" id="prefect">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                    <h5 class="fw-bold mb-0"><i class="fas fa-user-shield me-2"></i> Class Prefect</h5>
                    <button class="btn btn-light bg-light text-dark btn-sm" data-bs-toggle="modal" data-bs-target="#assignPrefectModal">
                        <i class="fas fa-user-check me-1"></i> Assign Prefect
                    </button>
                </div>
                <div class="card-body">
                    @if($studentClass->classPrefect && $activeAssignments->contains('student_id', $studentClass->classPrefect->id))
                        <div class="d-flex align-items-center">
                            <div class="bg-dark text-white rounded-circle d-flex align-items-center justify-content-center me-4" style="width: 80px; height: 80px;">
                                <i class="fas fa-user-shield fs-2"></i>
                            </div>
                            <div>
                                <h4 class="fw-bold mb-1">{{ $studentClass->classPrefect->first_name }} {{ $studentClass->classPrefect->last_name }}</h4>
                                <span class="badge bg-success">{{ $studentClass->classPrefect->student_id ?? 'N/A' }}</span>
                            </div>
                        </div>
                    @else
                        <div class="text-center py-5">
                            <i class="fas fa-user-slash fs-1 text-muted mb-3 d-block"></i>
                            <h5>No Class Prefect Assigned</h5>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- RESULTS TAB --}}
        <div class="tab-pane fade" id="results">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white">
                    <h5 class="fw-bold mb-0"><i class="fas fa-chart-line me-2"></i> Class Results</h5>
                </div>
                <div class="card-body">
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

        {{-- TIMETABLE TAB --}}
        <div class="tab-pane fade" id="classTimetable">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                    <h5 class="fw-bold mb-0"><i class="fas fa-calendar-alt me-2"></i> Class Timetable</h5>
                </div>
                <div class="card-body">
                    @php
                        $timetable = \App\Models\Timetable::where('student_class_id', $studentClass->id)
                            ->orderBy('created_at', 'desc')
                            ->first();
                    @endphp
                    @if($timetable)
                        @if(in_array($timetable->file_type, ['pdf']))
                            <iframe src="{{ asset('storage/' . $timetable->file_path) }}" width="100%" height="700px" 
                                    style="border: 1px solid #ddd; border-radius: 5px;"></iframe>
                        @elseif(in_array($timetable->file_type, ['jpg', 'jpeg', 'png', 'gif']))
                            <div class="text-center">
                                <img src="{{ asset('storage/' . $timetable->file_path) }}" class="img-fluid rounded shadow-sm" 
                                     alt="Timetable" style="max-height: 600px; width: auto;">
                            </div>
                        @else
                            <div class="alert alert-info text-center">
                                <i class="fas fa-file-alt fa-2x mb-2 d-block"></i>
                                <p>Preview not available for this file type.</p>
                                <a href="{{ route('timetables.download', $timetable->id) }}" class="btn btn-primary">
                                    <i class="fas fa-download me-1"></i> Download File
                                </a>
                            </div>
                        @endif
                    @else
                        <div class="text-center py-5">
                            <i class="fas fa-calendar-alt fs-1 text-muted mb-3 d-block"></i>
                            <h5>No Timetable Uploaded</h5>
                            <p class="text-muted mb-4">No timetable has been uploaded for this class yet.</p>
                            <a href="{{ route('timetables.create', ['class_id' => $studentClass->id]) }}" class="btn btn-primary">
                                <i class="fas fa-plus me-1"></i> Upload Timetable
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- PROMOTIONS TAB --}}
        <div class="tab-pane fade" id="promotions">
            <div class="card border-0 shadow-sm rounded-4">
                <div class="card-header bg-white py-3">
                    <h5 class="mb-0 fw-bold"><i class="fas fa-arrow-up me-2 text-danger"></i> Student Promotion & Graduation</h5>
                    <p class="text-muted mb-0 mt-1">Manage student promotions, repetitions, and graduations for this class</p>
                </div>
                <div class="card-body">
                    @if(session('success'))
                        <div class="alert alert-success alert-dismissible fade show shadow-sm mb-4">
                            <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif
                    @if(session('error'))
                        <div class="alert alert-danger alert-dismissible fade show shadow-sm mb-4">
                            <i class="fas fa-exclamation-circle me-2"></i>{{ session('error') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

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

                    <div class="alert alert-info">
                        <i class="fas fa-info-circle me-2"></i>
                        <strong>Current Class:</strong> {{ $studentClass->name }}<br>
                        Click "Manage Progressions" to go to the full student progression page.
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- TAKE ATTENDANCE MODAL --}}
<div class="modal fade" id="takeAttendanceModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content border-0 shadow">
            <form id="attendanceForm" method="POST" action="{{ route('attendance.store.class', $studentClass->id) }}">
                @csrf
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title fw-bold">
                        <i class="fas fa-clipboard-check me-2"></i> Take Attendance - {{ $studentClass->name }}
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" id="closeAttendanceModal"></button>
                </div>
                <div class="modal-body">
                    <div class="row mb-3">
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Date <span class="text-danger">*</span></label>
                            <input type="date" id="attendanceDateInput" name="attendance_date" class="form-control" 
                                   value="{{ date('Y-m-d') }}" max="{{ date('Y-m-d') }}" required>
                        </div>
                        <div class="col-md-8">
                            <label class="form-label fw-semibold">Quick Actions</label>
                            <div>
                                <button type="button" class="btn btn-sm btn-success me-2" onclick="markAllAttendance('present')">
                                    <i class="fas fa-check-double me-1"></i> All Present
                                </button>
                                <button type="button" class="btn btn-sm btn-danger me-2" onclick="markAllAttendance('absent')">
                                    <i class="fas fa-times-circle me-1"></i> All Absent
                                </button>
                                <button type="button" class="btn btn-sm btn-warning me-2" onclick="markAllAttendance('late')">
                                    <i class="fas fa-clock me-1"></i> All Late
                                </button>
                                <button type="button" class="btn btn-sm btn-info" onclick="markAllAttendance('excused')">
                                    <i class="fas fa-user-check me-1"></i> All Excused
                                </button>
                            </div>
                        </div>
                    </div>
                    
                    <div class="table-responsive" style="max-height: 500px; overflow-y: auto;">
                        <table class="table table-bordered table-hover">
                            <thead class="table-light sticky-top">
                                <tr>
                                    <th width="50">#</th>
                                    <th>Student Name</th>
                                    <th>Student ID</th>
                                    <th>Gender</th>
                                    <th width="150">Status</th>
                                    
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($activeAssignments as $index => $assignment)
                                    <tr>
                                        <td>{{ $index + 1 }}</td>
                                        <td class="fw-semibold">
                                            {{ $assignment->student->first_name }}
                                            {{ $assignment->student->last_name }}
                                        </td>
                                        <td>
                                            <span class="badge bg-light text-dark">
                                                {{ $assignment->student->student_id ?? 'N/A' }}
                                            </span>
                                        </td>
                                        <td>
                                            <span class="badge bg-{{ $assignment->student->gender == 'Male' ? 'primary' : 'danger' }} bg-opacity-10 text-dark">
                                                {{ $assignment->student->gender ?? 'N/A' }}
                                            </span>
                                        </td>
                                        <td>
                                            <select name="attendance[{{ $assignment->student->id }}][status]" 
                                                    class="form-select form-select-sm attendance-status" required>
                                                <option value="present" selected>Present</option>
                                                <option value="absent">Absent</option>
                                                <option value="late">Late</option>
                                                <option value="excused">Excused</option>
                                            </select>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center py-4">
                                            <i class="fas fa-user-slash fs-3 text-muted d-block mb-2"></i>
                                            No students found in this class
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    
                    <div class="mt-3 text-muted small">
                        <i class="fas fa-info-circle me-1"></i> 
                        Total Students: {{ $activeAssignments->count() }}
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal" id="cancelAttendanceBtn">
                        <i class="fas fa-times me-1"></i> Cancel
                    </button>
                    <button type="button" class="btn btn-primary" id="saveAttendanceBtn">
                        <i class="fas fa-save me-1"></i> Save Attendance
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection

{{-- MODALS --}}
@push('modals')
    @php
        $assignedSubjectIds = $studentClass->subjects->pluck('id')->toArray();
        $availableSubjects = \App\Models\Subject::whereNotIn('id', $assignedSubjectIds)->orderBy('name')->get();
    @endphp

    {{-- ADD SUBJECT MODAL --}}
    <div class="modal fade" id="addSubjectModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow">
                <form action="{{ route('classes.subject.attach', $studentClass->id) }}" method="POST">
                    @csrf
                    <div class="modal-header bg-primary text-white">
                        <h5 class="modal-title fw-bold"><i class="fas fa-book-open me-2"></i> Assign Subject</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        @if($availableSubjects->count() > 0)
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Select Subject</label>
                                <select name="subject_id" class="form-select" required>
                                    <option value="">-- Choose Subject --</option>
                                    @foreach($availableSubjects as $subject)
                                        <option value="{{ $subject->id }}">
                                            {{ $subject->name }}
                                            @if($subject->code) ({{ $subject->code }}) @endif
                                            @if($subject->education_level) - {{ $subject->education_level }} @endif
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="alert alert-info small mb-0">
                                <i class="fas fa-info-circle me-1"></i> Only subjects not already assigned to this class are shown.
                            </div>
                        @else
                            <div class="text-center py-4">
                                <i class="fas fa-book-open fs-1 text-muted mb-3 d-block"></i>
                                <h5>No Subjects Available</h5>
                                <p class="text-muted mb-0">All subjects are already assigned to this class.</p>
                                <a href="{{ route('subjects.create') }}" class="btn btn-sm btn-outline-primary mt-3">
                                    <i class="fas fa-plus me-1"></i> Create New Subject
                                </a>
                            </div>
                        @endif
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary" {{ $availableSubjects->count() == 0 ? 'disabled' : '' }}>
                            <i class="fas fa-plus me-1"></i> Assign Subject
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- ASSIGN PREFECT MODAL --}}
    <div class="modal fade" id="assignPrefectModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow">
                <form action="{{ route('student-classes.assign-prefect', $studentClass->id) }}" method="POST">
                    @csrf
                    <div class="modal-header bg-dark text-white">
                        <h5 class="modal-title fw-bold"><i class="fas fa-user-shield me-2"></i> Assign Class Prefect</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        @if($activeAssignments->count() > 0)
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Select Student</label>
                                <select name="student_id" class="form-select" required>
                                    <option value="">-- Choose Student --</option>
                                    @foreach($activeAssignments as $assignment)
                                        <option value="{{ $assignment->student->id }}"
                                            {{ ($studentClass->class_prefect_id ?? '') == $assignment->student->id ? 'selected' : '' }}>
                                            {{ $assignment->student->first_name }} {{ $assignment->student->last_name }}
                                            ({{ $assignment->student->student_id ?? 'N/A' }})
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        @else
                            <div class="text-center py-4">
                                <i class="fas fa-user-slash fs-1 text-muted mb-3 d-block"></i>
                                <h5>No Students Available</h5>
                                <p class="text-muted mb-0">No active students found in this class.</p>
                            </div>
                        @endif
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-dark" {{ $activeAssignments->count() == 0 ? 'disabled' : '' }}>
                            <i class="fas fa-save me-1"></i> Save Prefect
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endpush

{{-- SCRIPTS --}}
@push('scripts')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        (function() {
            'use strict';

            // ============================================================
            // 1. ROUTES CONFIGURATION
            // ============================================================
            const ROUTES = {
                attendanceAjax: @if($hasAttendanceRoutes) '{{ route("attendance.ajax", $studentClass->id) }}' @else null @endif,
                attendanceStore: @if($hasAttendanceRoutes) '{{ route("attendance.store.class", $studentClass->id) }}' @else null @endif,
                broadsheetAjax: @if($hasBroadsheetRoute) '{{ route("broadsheet.ajax") }}' @else null @endif,
                progressionIndex: @if($hasProgressionRoute) '{{ route("student-progressions.index") }}' @else null @endif,
                csrfToken: '{{ csrf_token() }}',
                classId: {{ $studentClass->id }},
                className: '{{ addslashes($studentClass->name) }}'
            };

            console.log('ROUTES configured:', ROUTES);

            // ============================================================
            // 2. FORCE REMOVE STUCK MODAL BACKDROPS
            // ============================================================
            function forceRemoveModalBackdrops() {
                document.querySelectorAll('.modal-backdrop').forEach(function(el) {
                    el.remove();
                });
                document.body.classList.remove('modal-open');
                document.body.style.removeProperty('overflow');
                document.body.style.removeProperty('padding-right');
                document.body.style.removeProperty('position');
                document.body.style.position = '';
                document.body.style.top = '';
                document.body.style.width = '';
                console.log('Force removed modal backdrops');
            }

            forceRemoveModalBackdrops();
            setTimeout(forceRemoveModalBackdrops, 100);
            setTimeout(forceRemoveModalBackdrops, 500);

            // ============================================================
            // 3. MONITOR FOR MODAL BACKDROPS
            // ============================================================
            const backdropObserver = new MutationObserver(function(mutations) {
                mutations.forEach(function(mutation) {
                    if (mutation.type === 'childList') {
                        const backdrops = document.querySelectorAll('.modal-backdrop');
                        const visibleModals = document.querySelectorAll('.modal.show');
                        if (backdrops.length > 0 && visibleModals.length === 0) {
                            console.log('Found orphaned backdrop, removing...');
                            forceRemoveModalBackdrops();
                        }
                    }
                });
            });
            backdropObserver.observe(document.body, { childList: true, subtree: true });

            document.addEventListener('hidden.bs.modal', function(e) {
                setTimeout(forceRemoveModalBackdrops, 50);
            });

            // ============================================================
            // 4. DOM READY - INITIALIZE ALL FUNCTIONALITY
            // ============================================================
            document.addEventListener('DOMContentLoaded', function() {
                console.log('DOM loaded - initializing class details page');
                
                forceRemoveModalBackdrops();
                initializeAttendance();
                initializeResults();
                initializeProgressions();
                initializeAttendanceSave();
                initializeModalHandlers();
            });

            // ============================================================
            // 5. ATTENDANCE SAVE - FIXED WITH FORM CHECK
            // ============================================================
            function initializeAttendanceSave() {
                console.log('Initializing attendance save...');
                
                // Wait a moment for DOM to fully render
                setTimeout(function() {
                    const saveBtn = document.getElementById('saveAttendanceBtn');
                    
                    if (!saveBtn) {
                        console.error('Save Attendance button not found!');
                        return;
                    }

                    console.log('Save button found:', saveBtn);

                    // Check if form exists
                    const form = document.getElementById('attendanceForm');
                    if (!form) {
                        console.error('Attendance form not found!');
                        return;
                    }
                    console.log('Form found:', form);

                    // Remove any existing listeners by cloning
                    const newSaveBtn = saveBtn.cloneNode(true);
                    saveBtn.parentNode.replaceChild(newSaveBtn, saveBtn);

                    // Get the fresh reference
                    const finalSaveBtn = document.getElementById('saveAttendanceBtn');
                    
                    // Add click event
                    finalSaveBtn.addEventListener('click', function(e) {
                        e.preventDefault();
                        e.stopPropagation();
                        
                        console.log('Save Attendance button clicked!');
                        saveAttendance();
                    });

                    console.log('Save attendance handler attached successfully');
                }, 100);
            }

            function saveAttendance() {
                console.log('saveAttendance() called');
                
                const form = document.getElementById('attendanceForm');
                if (!form) {
                    console.error('Attendance form not found!');
                    Swal.fire({ 
                        icon: 'error', 
                        title: 'Error', 
                        text: 'Attendance form not found. Please refresh the page and try again.' 
                    });
                    return;
                }

                console.log('Form found, validating...');

                // Validate all statuses are selected
                const selects = form.querySelectorAll('.attendance-status');
                let allFilled = true;
                selects.forEach(select => {
                    if (!select.value) {
                        allFilled = false;
                        select.style.borderColor = 'red';
                    } else {
                        select.style.borderColor = '';
                    }
                });

                if (!allFilled) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Incomplete Attendance',
                        text: 'Please select a status for all students before saving.'
                    });
                    return;
                }

                // Get form data
                const formData = new FormData(form);
                const saveBtn = document.getElementById('saveAttendanceBtn');
                
                // Show loading state
                if (saveBtn) {
                    saveBtn.disabled = true;
                    saveBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i> Saving...';
                }

                console.log('Sending attendance data to:', ROUTES.attendanceStore);

                // Send AJAX request
                fetch(ROUTES.attendanceStore, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': ROUTES.csrfToken,
                        'Accept': 'application/json'
                    },
                    body: formData
                })
                .then(response => {
                    console.log('Response status:', response.status);
                    if (!response.ok) {
                        return response.json().then(err => {
                            throw new Error(err.message || 'HTTP error ' + response.status);
                        });
                    }
                    return response.json();
                })
                .then(data => {
                    console.log('Response data:', data);
                    
                    // Reset button state
                    if (saveBtn) {
                        saveBtn.disabled = false;
                        saveBtn.innerHTML = '<i class="fas fa-save me-1"></i> Save Attendance';
                    }
                    
                    if (data.success) {
                        // Close modal
                        const modal = document.getElementById('takeAttendanceModal');
                        const bsModal = bootstrap.Modal.getInstance(modal);
                        if (bsModal) {
                            bsModal.hide();
                        }
                        forceRemoveModalBackdrops();
                        
                        // Show success message
                        Swal.fire({
                            icon: 'success',
                            title: 'Success!',
                            text: 'Attendance saved successfully!',
                            timer: 2000,
                            showConfirmButton: false
                        });
                        
                        // Reload attendance data
                        setTimeout(function() {
                            const savedDate = document.getElementById('attendanceDateInput')?.value;
                            if (savedDate) {
                                const dateInput = document.getElementById('attendanceDate');
                                if (dateInput) dateInput.value = savedDate;
                            }
                            // Trigger attendance load
                            if (typeof window.loadAttendanceRecords === 'function') {
                                window.loadAttendanceRecords();
                            } else {
                                // Fallback: reload page
                                location.reload();
                            }
                        }, 300);
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: data.message || 'Failed to save attendance.'
                        });
                    }
                })
                .catch(error => {
                    console.error('Attendance save error:', error);
                    if (saveBtn) {
                        saveBtn.disabled = false;
                        saveBtn.innerHTML = '<i class="fas fa-save me-1"></i> Save Attendance';
                    }
                    
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: error.message || 'Failed to save attendance. Please try again.'
                    });
                });
            }

            // ============================================================
            // 6. MODAL HANDLERS
            // ============================================================
            function initializeModalHandlers() {
                // Close button
                const closeBtn = document.getElementById('closeAttendanceModal');
                if (closeBtn) {
                    const newCloseBtn = closeBtn.cloneNode(true);
                    closeBtn.parentNode.replaceChild(newCloseBtn, closeBtn);
                    newCloseBtn.addEventListener('click', function() {
                        const modal = document.getElementById('takeAttendanceModal');
                        const bsModal = bootstrap.Modal.getInstance(modal);
                        if (bsModal) bsModal.hide();
                        setTimeout(forceRemoveModalBackdrops, 100);
                    });
                }

                // Cancel button
                const cancelBtn = document.getElementById('cancelAttendanceBtn');
                if (cancelBtn) {
                    const newCancelBtn = cancelBtn.cloneNode(true);
                    cancelBtn.parentNode.replaceChild(newCancelBtn, cancelBtn);
                    newCancelBtn.addEventListener('click', function() {
                        const modal = document.getElementById('takeAttendanceModal');
                        const bsModal = bootstrap.Modal.getInstance(modal);
                        if (bsModal) bsModal.hide();
                        setTimeout(forceRemoveModalBackdrops, 100);
                    });
                }

                // Take Attendance button
                document.querySelectorAll('.take-attendance-btn, [data-bs-target="#takeAttendanceModal"]').forEach(function(btn) {
                    const newBtn = btn.cloneNode(true);
                    btn.parentNode.replaceChild(newBtn, btn);
                    newBtn.addEventListener('click', function(e) {
                        forceRemoveModalBackdrops();
                        const modal = document.getElementById('takeAttendanceModal');
                        if (modal) {
                            const bsModal = bootstrap.Modal.getInstance(modal) || new bootstrap.Modal(modal);
                            bsModal.show();
                        }
                    });
                });
            }

            // ============================================================
            // 7. ATTENDANCE FUNCTIONALITY
            // ============================================================
            function initializeAttendance() {
                console.log('Initializing attendance functionality');
                
                const loadBtn = document.getElementById('loadAttendanceBtn');
                if (!loadBtn) {
                    console.warn('Load attendance button not found');
                    return;
                }

                const newLoadBtn = loadBtn.cloneNode(true);
                loadBtn.parentNode.replaceChild(newLoadBtn, loadBtn);

                newLoadBtn.addEventListener('click', function(e) {
                    e.preventDefault();
                    loadAttendanceRecords();
                });

                const dateInput = document.getElementById('attendanceDate');
                if (dateInput && dateInput.value) {
                    setTimeout(loadAttendanceRecords, 500);
                }
            }

            window.loadAttendanceRecords = function() {
                const date = document.getElementById('attendanceDate')?.value || '';
                const month = document.getElementById('attendanceMonth')?.value || '';
                const year = document.getElementById('attendanceYear')?.value || '';
                
                if (!date && !month) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Missing Selection',
                        text: 'Please select either a date or month to view attendance'
                    });
                    return;
                }
                
                const container = document.getElementById('attendanceContainer');
                const summaryDiv = document.getElementById('attendanceSummary');
                
                if (!container) return;
                
                container.innerHTML = `
                    <div class="text-center py-5">
                        <div class="spinner-border text-primary mb-3" role="status"></div>
                        <h5>Loading Attendance...</h5>
                    </div>
                `;
                
                if (summaryDiv) {
                    summaryDiv.style.display = 'none';
                }
                
                const ajaxUrl = ROUTES.attendanceAjax;
                
                if (!ajaxUrl) {
                    container.innerHTML = `
                        <div class="text-center py-5 text-muted">
                            <i class="fas fa-exclamation-circle fs-1 mb-3 d-block text-danger"></i>
                            <h5>Route Not Available</h5>
                            <p>Attendance AJAX route is not configured.</p>
                        </div>`;
                    return;
                }
                
                fetch(ajaxUrl, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': ROUTES.csrfToken,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({ 
                        date: date, 
                        month: month, 
                        year: year
                    })
                })
                .then(response => {
                    if (!response.ok) {
                        throw new Error('HTTP error ' + response.status);
                    }
                    return response.json();
                })
                .then(data => {
                    if (data.success) {
                        renderAttendanceTable(data);
                        updateAttendanceSummary(data);
                        if (summaryDiv) {
                            summaryDiv.style.display = 'flex';
                        }
                    } else {
                        container.innerHTML = `
                            <div class="text-center py-5 text-muted">
                                <i class="fas fa-calendar-times fs-1 mb-3 d-block"></i>
                                <h5>No Attendance Records</h5>
                                <p>${data.message || 'No records found for the selected criteria.'}</p>
                            </div>`;
                        if (summaryDiv) {
                            summaryDiv.style.display = 'none';
                        }
                    }
                })
                .catch(error => {
                    console.error('Attendance load error:', error);
                    container.innerHTML = `
                        <div class="text-center py-5 text-muted">
                            <i class="fas fa-exclamation-circle fs-1 mb-3 d-block text-danger"></i>
                            <h5>Error Loading Attendance</h5>
                            <p class="text-danger">${error.message}</p>
                            <button class="btn btn-outline-primary mt-3" onclick="loadAttendanceRecords()">
                                <i class="fas fa-redo me-1"></i> Retry
                            </button>
                        </div>`;
                    if (summaryDiv) {
                        summaryDiv.style.display = 'none';
                    }
                });
            };

            function renderAttendanceTable(data) {
                const container = document.getElementById('attendanceContainer');
                if (!container) return;
                
                let html = `
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover align-middle" id="attendanceTable">
                            <thead class="table-light">
                                <tr>
                                    <th width="50">#</th>
                                    <th>Date</th>
                                    <th>Student Name</th>
                                    <th>Student ID</th>
                                    <th>Gender</th>
                                    <th width="120">Status</th>
                                    <th>Remarks</th>
                                </tr>
                            </thead>
                            <tbody>`;
                
                if (data.records && data.records.length > 0) {
                    data.records.forEach((record, index) => {
                        let statusBadge = '';
                        const status = (record.status || '').toLowerCase();
                        switch(status) {
                            case 'present': 
                                statusBadge = '<span class="badge bg-success px-3 py-2"><i class="fas fa-check me-1"></i>Present</span>'; 
                                break;
                            case 'absent': 
                                statusBadge = '<span class="badge bg-danger px-3 py-2"><i class="fas fa-times me-1"></i>Absent</span>'; 
                                break;
                            case 'late': 
                                statusBadge = '<span class="badge bg-warning text-dark px-3 py-2"><i class="fas fa-clock me-1"></i>Late</span>'; 
                                break;
                            case 'excused': 
                                statusBadge = '<span class="badge bg-info px-3 py-2"><i class="fas fa-check-circle me-1"></i>Excused</span>'; 
                                break;
                            default: 
                                statusBadge = '<span class="badge bg-secondary">N/A</span>';
                        }
                        
                        const studentName = record.student_name || record.student?.first_name + ' ' + record.student?.last_name || 'N/A';
                        const studentId = record.student_id || record.student?.student_id || 'N/A';
                        const gender = record.gender || record.student?.gender || 'N/A';
                        const dateDisplay = record.date || record.attendance_date || '-';
                        const remarks = record.remarks || '-';
                        
                        html += `<tr>
                            <td>${index + 1}</td>
                            <td><span class="badge bg-light text-dark">${dateDisplay}</span></td>
                            <td class="fw-semibold">${studentName}</td>
                            <td><span class="badge bg-light text-dark">${studentId}</span></td>
                            <td>${gender}</td>
                            <td>${statusBadge}</td>
                            <td>${remarks}</td>
                        </tr>`;
                    });
                } else {
                    html += `<tr><td colspan="7" class="text-center py-4">
                        <i class="fas fa-inbox fs-3 text-muted d-block mb-2"></i>
                        No records found
                    </td></tr>`;
                }
                
                html += `</tbody></table></div>
                    <div class="mt-2 text-muted small">
                        <i class="fas fa-info-circle me-1"></i> 
                        Total Records: ${data.records ? data.records.length : 0}
                    </div>`;
                
                container.innerHTML = html;
            }

            function updateAttendanceSummary(data) {
                let presentCount = 0, absentCount = 0, lateCount = 0, excusedCount = 0;
                
                if (data.records) {
                    data.records.forEach(record => {
                        const status = (record.status || '').toLowerCase();
                        switch(status) {
                            case 'present': presentCount++; break;
                            case 'absent': absentCount++; break;
                            case 'late': lateCount++; break;
                            case 'excused': excusedCount++; break;
                        }
                    });
                }
                
                const summary = data.summary || {};
                const presentEl = document.getElementById('presentCount');
                const absentEl = document.getElementById('absentCount');
                const lateEl = document.getElementById('lateCount');
                const excusedEl = document.getElementById('excusedCount');
                
                if (presentEl) presentEl.textContent = summary.present || presentCount;
                if (absentEl) absentEl.textContent = summary.absent || absentCount;
                if (lateEl) lateEl.textContent = summary.late || lateCount;
                if (excusedEl) excusedEl.textContent = summary.excused || excusedCount;
            }

            // ============================================================
            // 8. ATTENDANCE MODAL FUNCTIONS
            // ============================================================
            window.markAllAttendance = function(status) {
                const selects = document.querySelectorAll('.attendance-status');
                if (selects.length === 0) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'No Students',
                        text: 'No students found in this class to mark attendance.'
                    });
                    return;
                }
                
                selects.forEach(select => { 
                    select.value = status; 
                });
                
                const statusNames = {
                    'present': 'Present',
                    'absent': 'Absent',
                    'late': 'Late',
                    'excused': 'Excused'
                };
                
                Swal.fire({ 
                    icon: 'success', 
                    title: 'Marked!', 
                    text: `All students marked as ${statusNames[status] || status}`, 
                    timer: 1500, 
                    showConfirmButton: false 
                });
            };

            // ============================================================
            // 9. EXPORT FUNCTIONS
            // ============================================================
            window.exportAttendanceToExcel = function() {
                const table = document.getElementById('attendanceTable');
                if (!table) {
                    Swal.fire({ 
                        icon: 'warning', 
                        title: 'No Data', 
                        text: 'Please load attendance records first' 
                    });
                    return;
                }
                
                try {
                    const wb = XLSX.utils.book_new();
                    const ws = XLSX.utils.table_to_sheet(table);
                    ws['!cols'] = Array.from({length: table.rows[0]?.cells.length || 0}, () => ({ wch: 20 }));
                    XLSX.utils.book_append_sheet(wb, ws, 'Attendance');
                    XLSX.writeFile(wb, `Attendance_${ROUTES.className}_${new Date().toISOString().slice(0,10)}.xlsx`);
                    Swal.fire({ icon: 'success', title: 'Exported!', timer: 1500, showConfirmButton: false });
                } catch(error) {
                    console.error('Export error:', error);
                    Swal.fire({ icon: 'error', title: 'Export Failed', text: error.message });
                }
            };

            window.printAttendance = function() {
                const table = document.getElementById('attendanceTable');
                if (!table) {
                    Swal.fire({ icon: 'warning', title: 'No Data', text: 'Please load attendance records first' });
                    return;
                }
                
                const container = document.getElementById('attendanceContainer');
                if (!container) return;
                
                const printWindow = window.open('', '_blank', 'width=1200,height=800');
                if (!printWindow) {
                    Swal.fire({ icon: 'error', title: 'Popup Blocked', text: 'Please allow popups for this site.' });
                    return;
                }
                
                printWindow.document.write(`
                    <html>
                        <head>
                            <title>Attendance Report - ${ROUTES.className}</title>
                            <style>
                                body { font-family: Arial, sans-serif; padding: 20px; }
                                h2 { text-align: center; margin-bottom: 20px; color: #333; }
                                table { width: 100%; border-collapse: collapse; margin-top: 20px; }
                                th, td { border: 1px solid #ddd; padding: 8px 12px; text-align: left; }
                                th { background-color: #f5f5f5; font-weight: bold; }
                                .badge { padding: 3px 10px; border-radius: 4px; font-size: 12px; }
                                .badge-success { background: #28a745; color: white; }
                                .badge-danger { background: #dc3545; color: white; }
                                .badge-warning { background: #ffc107; color: black; }
                                .badge-info { background: #17a2b8; color: white; }
                                .badge-secondary { background: #6c757d; color: white; }
                                .badge-light { background: #f8f9fa; color: #333; border: 1px solid #ddd; }
                                .text-muted { color: #6c757d; }
                                .fw-bold { font-weight: bold; }
                                .text-center { text-align: center; }
                                @media print {
                                    .no-print { display: none; }
                                }
                            </style>
                        </head>
                        <body>
                            <h2>Attendance Report - ${ROUTES.className}</h2>
                            <p><strong>Generated:</strong> ${new Date().toLocaleString()}</p>
                            ${container.innerHTML}
                            <p class="text-muted" style="margin-top: 20px; font-size: 12px;">Generated from School Management System</p>
                        </body>
                    </html>
                `);
                printWindow.document.close();
                setTimeout(() => {
                    printWindow.print();
                }, 500);
            };

            // ============================================================
            // 10. RESULTS FUNCTIONALITY (simplified)
            // ============================================================
            function initializeResults() {
                console.log('Initializing results functionality');
                const loadBtn = document.getElementById('loadResultsBtn');
                if (!loadBtn) return;
                const newLoadBtn = loadBtn.cloneNode(true);
                loadBtn.parentNode.replaceChild(newLoadBtn, loadBtn);
                newLoadBtn.addEventListener('click', function() {
                    Swal.fire({ icon: 'info', title: 'Results', text: 'Results functionality loaded' });
                });
            }

            // ============================================================
            // 11. PROGRESSIONS FUNCTIONALITY
            // ============================================================
            function initializeProgressions() {
                console.log('Initializing progressions functionality');
                const goBtn = document.getElementById('goToProgressionsBtn');
                if (!goBtn) return;
                const newGoBtn = goBtn.cloneNode(true);
                goBtn.parentNode.replaceChild(newGoBtn, goBtn);
                newGoBtn.addEventListener('click', function(e) {
                    e.preventDefault();
                    const academicYearSelect = document.getElementById('progressionAcademicYear');
                    const academicYearId = academicYearSelect?.value;
                    if (!academicYearId) {
                        Swal.fire({ icon: 'warning', title: 'Missing Selection', text: 'Please select Academic Year first' });
                        return;
                    }
                    if (!ROUTES.progressionIndex) {
                        Swal.fire({ icon: 'error', title: 'Route Not Available' });
                        return;
                    }
                    const url = `${ROUTES.progressionIndex}?class_id=${ROUTES.classId}&academic_year_id=${academicYearId}`;
                    window.location.href = url;
                });
            }

            console.log('Class details page initialized successfully');

        })();
    </script>
@endpush

@push('styles')
    <style>
        .form-label { font-size: 0.85rem; font-weight: 600; margin-bottom: 0.4rem; }
        .table th { font-size: 13px; font-weight: 600; white-space: nowrap; }
        .table td { font-size: 13px; vertical-align: middle; }
        .card { border-radius: 12px; }
        select:disabled { background-color: #e9ecef; cursor: not-allowed; }
        .badge { font-weight: 500; padding: 5px 10px; }
        .table tbody tr:hover { background-color: rgba(0,0,0,0.02); transition: background-color 0.3s ease; }
        #goToProgressionsBtn { cursor: pointer; transition: all 0.3s ease; }
        #goToProgressionsBtn:hover { transform: translateY(-2px); box-shadow: 0 4px 12px rgba(220, 53, 69, 0.3); }
        .sticky-top { position: sticky; top: 0; z-index: 10; }
        .modal-xl { max-width: 1200px; }
        
        .modal-backdrop {
            z-index: 1040 !important;
        }
        .modal {
            z-index: 1050 !important;
        }
        body:not(.modal-open) {
            overflow: auto !important;
        }
        body:not(.modal-open) #app-container {
            pointer-events: all !important;
        }
        
        @media print {
            .no-print { display: none !important; }
            .btn { display: none !important; }
            .card { border: none !important; box-shadow: none !important; }
        }
    </style>
@endpush