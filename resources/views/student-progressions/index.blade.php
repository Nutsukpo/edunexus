@extends('layouts.master')

@section('title', 'Student Promotion & Graduation')

@section('content')

<div class="container-fluid py-4">

    {{-- ============================================================
         PAGE HEADER
    ============================================================= --}}
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">

        <div>
            <h4 class="fw-bold mb-1">
                <i class="fas fa-user-graduate text-primary me-2"></i>
                Student Promotion & Graduation
            </h4>

            <p class="text-muted mb-0">
                Promote, repeat, or graduate students at the end of the academic year.
            </p>
        </div>

        <a href="{{ route('settings.index') }}"
           class="btn btn-outline-primary">

            <i class="fas fa-cog me-1"></i>
            System Settings

        </a>

    </div>


    {{-- ============================================================
         SESSION ALERTS
    ============================================================= --}}

    @if(session('success'))

        <div class="alert alert-success alert-dismissible fade show shadow-sm border-0"
             role="alert">

            <div class="d-flex align-items-start">

                <i class="fas fa-check-circle fs-5 me-2 mt-1"></i>

                <div>

                    <strong>Success</strong>

                    <div>
                        {{ session('success') }}
                    </div>

                </div>

            </div>

            <button type="button"
                    class="btn-close"
                    data-bs-dismiss="alert">
            </button>

        </div>

    @endif


    @if(session('error'))

        <div class="alert alert-danger alert-dismissible fade show shadow-sm border-0"
             role="alert">

            <div class="d-flex align-items-start">

                <i class="fas fa-exclamation-circle fs-5 me-2 mt-1"></i>

                <div>

                    <strong>Unable to process</strong>

                    <div>
                        {{ session('error') }}
                    </div>

                </div>

            </div>

            <button type="button"
                    class="btn-close"
                    data-bs-dismiss="alert">
            </button>

        </div>

    @endif


    {{-- ============================================================
         PROCESSING DETAILS
    ============================================================= --}}

    @if(session('progression_details'))

        <div class="alert alert-info shadow-sm border-0">

            <div class="fw-semibold mb-2">
                <i class="fas fa-list-check me-2"></i>
                Processing Details
            </div>

            <ul class="mb-0">

                @foreach(session('progression_details') as $detail)

                    <li>
                        {{ $detail }}
                    </li>

                @endforeach

            </ul>

        </div>

    @endif


    @if(session('progression_errors'))

        <div class="alert alert-warning shadow-sm border-0">

            <div class="fw-semibold mb-2">
                <i class="fas fa-triangle-exclamation me-2"></i>
                Students Skipped
            </div>

            <ul class="mb-0">

                @foreach(session('progression_errors') as $error)

                    <li>
                        {{ $error }}
                    </li>

                @endforeach

            </ul>

        </div>

    @endif


    {{-- ============================================================
         CURRENT ACADEMIC PERIOD
    ============================================================= --}}

    <div class="row g-4 mb-4">

        {{-- Current Period --}}
        <div class="col-lg-6">

            <div class="card border-0 shadow-sm rounded-4 h-100">

                <div class="card-body p-4">

                    <div class="d-flex align-items-center mb-3">

                        <div class="period-icon bg-primary-subtle text-primary">
                            <i class="fas fa-calendar-check"></i>
                        </div>

                        <div class="ms-3">

                            <div class="small text-uppercase text-muted fw-semibold">
                                Current System Period
                            </div>

                            <h5 class="fw-bold mb-0">
                                Current Academic Period
                            </h5>

                        </div>

                    </div>


                    @if($currentAcademicYear && $currentTerm)

                        <div class="period-display">

                            <div>
                                <span class="period-label">
                                    Academic Year
                                </span>

                                <span class="period-value">
                                    {{ $currentAcademicYear->name }}
                                </span>
                            </div>


                            <div class="period-divider"></div>


                            <div>

                                <span class="period-label">
                                    Term
                                </span>

                                <span class="period-value">
                                    {{ $currentTerm->name }}
                                </span>

                            </div>

                        </div>

                        @if(strtolower(trim($currentTerm->name)) === 'third term')

                            <div class="alert alert-success mt-3 mb-0 py-2">

                                <i class="fas fa-circle-check me-1"></i>

                                Promotion processing is currently available.

                            </div>

                        @else

                            <div class="alert alert-warning mt-3 mb-0 py-2">

                                <i class="fas fa-lock me-1"></i>

                                Promotion, repetition, and graduation are available
                                only during Third Term.

                            </div>

                        @endif

                    @else

                        <div class="alert alert-danger mb-0">

                            <i class="fas fa-triangle-exclamation me-2"></i>

                            <strong>Academic period not configured.</strong>

                            <div class="mt-1">
                                Please configure the current academic year and term
                                in System Settings before processing students.
                            </div>

                        </div>

                    @endif

                </div>

            </div>

        </div>


        {{-- Next Period --}}
        <div class="col-lg-6">

            <div class="card border-0 shadow-sm rounded-4 h-100">

                <div class="card-body p-4">

                    <div class="d-flex align-items-center mb-3">

                        <div class="period-icon bg-success-subtle text-success">
                            <i class="fas fa-arrow-right"></i>
                        </div>

                        <div class="ms-3">

                            <div class="small text-uppercase text-muted fw-semibold">
                                Promotion Destination
                            </div>

                            <h5 class="fw-bold mb-0">
                                Next Academic Period
                            </h5>

                        </div>

                    </div>


                    @if($nextAcademicYear && $nextFirstTerm)

                        <div class="period-display">

                            <div>

                                <span class="period-label">
                                    Academic Year
                                </span>

                                <span class="period-value">
                                    {{ $nextAcademicYear->name }}
                                </span>

                            </div>


                            <div class="period-divider"></div>


                            <div>

                                <span class="period-label">
                                    Term
                                </span>

                                <span class="period-value">
                                    {{ $nextFirstTerm->name }}
                                </span>

                            </div>

                        </div>


                        <div class="alert alert-success mt-3 mb-0 py-2">

                            <i class="fas fa-check-circle me-1"></i>

                            Students promoted or repeated will be assigned to
                            <strong>
                                {{ $nextAcademicYear->name }}
                            </strong>
                            -
                            <strong>
                                {{ $nextFirstTerm->name }}
                            </strong>.

                        </div>

                    @elseif($nextAcademicYear)

                        <div class="alert alert-warning mb-0">

                            <i class="fas fa-triangle-exclamation me-2"></i>

                            <strong>
                                {{ $nextAcademicYear->name }}
                            </strong>
                            exists, but First Term has not been configured.

                            <div class="mt-2">

                                <a href="{{ route('settings.index') }}"
                                   class="btn btn-sm btn-outline-warning">

                                    <i class="fas fa-cog me-1"></i>
                                    Configure Academic Terms

                                </a>

                            </div>

                        </div>

                    @else

                        <div class="alert alert-warning mb-0">

                            <i class="fas fa-triangle-exclamation me-2"></i>

                            No next academic year has been configured.

                            <div class="mt-2">

                                <a href="{{ route('settings.index') }}"
                                   class="btn btn-sm btn-outline-warning">

                                    <i class="fas fa-cog me-1"></i>
                                    Configure Academic Year

                                </a>

                            </div>

                        </div>

                    @endif

                </div>

            </div>

        </div>

    </div>


    {{-- ============================================================
         PROMOTION RULES
    ============================================================= --}}

    <div class="card border-0 shadow-sm rounded-4 mb-4">

        <div class="card-body p-4">

            <div class="d-flex align-items-start">

                <div class="rules-icon">
                    <i class="fas fa-shield-halved"></i>
                </div>

                <div class="ms-3">

                    <h6 class="fw-bold mb-2">
                        Promotion Rules
                    </h6>

                    <div class="row g-3">

                        <div class="col-md-4">

                            <div class="rule-item">

                                <i class="fas fa-check text-success me-2"></i>

                                Processing is allowed only in
                                <strong>Third Term</strong>.

                            </div>

                        </div>

                        <div class="col-md-4">

                            <div class="rule-item">

                                <i class="fas fa-arrow-right text-primary me-2"></i>

                                Promotion moves students to the
                                <strong>next academic year</strong>.

                            </div>

                        </div>

                        <div class="col-md-4">

                            <div class="rule-item">

                                <i class="fas fa-clock text-info me-2"></i>

                                New assignments begin in
                                <strong>First Term</strong>.

                            </div>

                        </div>

                        <div class="col-md-4">

                            <div class="rule-item">

                                <i class="fas fa-clock-rotate-left text-secondary me-2"></i>

                                Previous assignments remain in
                                <strong>history</strong>.

                            </div>

                        </div>

                        <div class="col-md-4">

                            <div class="rule-item">

                                <i class="fas fa-repeat text-warning me-2"></i>

                                Repeating students remain in the
                                <strong>same class</strong>.

                            </div>

                        </div>

                        <div class="col-md-4">

                            <div class="rule-item">

                                <i class="fas fa-graduation-cap text-dark me-2"></i>

                                Only eligible graduating students can be
                                <strong>graduated</strong>.

                            </div>

                        </div>

                    </div>

                </div>

            </div>

        </div>

    </div>


    {{-- ============================================================
         SELECT CLASS
    ============================================================= --}}

    <div class="card border-0 shadow-sm rounded-4 mb-4">

        <div class="card-header bg-white border-0 py-3 px-4">

            <div class="d-flex align-items-center">

                <div class="section-icon bg-primary-subtle text-primary">
                    <i class="fas fa-filter"></i>
                </div>

                <div class="ms-3">

                    <h5 class="fw-bold mb-0">
                        Select Class
                    </h5>

                    <small class="text-muted">
                        Select a class to load students currently assigned to it.
                    </small>

                </div>

            </div>

        </div>


        <div class="card-body px-4 pb-4">

            <form method="GET"
                  action="{{ route('student-progressions.index') }}"
                  id="filterForm">

                <div class="row g-3 align-items-end">

                    <div class="col-lg-9">

                        <label for="class_id"
                               class="form-label fw-semibold">

                            Class

                            <span class="text-danger">*</span>

                        </label>

                        <select name="class_id"
                                id="class_id"
                                class="form-select form-select-lg"
                                required>

                            <option value="">
                                -- Select Class --
                            </option>

                            @foreach($classes as $class)

                                <option value="{{ $class->id }}"
                                    {{ request('class_id') == $class->id ? 'selected' : '' }}>

                                    {{ $class->name }}

                                    @if(!empty($class->stream))
                                        — {{ $class->stream }}
                                    @endif

                                </option>

                            @endforeach

                        </select>

                    </div>


                    <div class="col-lg-3">

                        <button type="submit"
                                class="btn btn-primary btn-lg w-100">

                            <i class="fas fa-users me-1"></i>

                            Load Students

                        </button>

                    </div>

                </div>

            </form>

        </div>

    </div>


    {{-- ============================================================
         STUDENT LIST
    ============================================================= --}}

    @if(request('class_id'))

        @if($students->count() > 0)

            <form method="POST"
                  action="{{ route('student-progressions.process') }}"
                  id="progressionForm">

                @csrf


                <div class="card border-0 shadow-sm rounded-4">

                    {{-- Header --}}
                    <div class="card-header bg-white border-0 py-3 px-4">

                        <div class="d-flex flex-wrap justify-content-between align-items-center gap-3">

                            <div>

                                <h5 class="fw-bold mb-1">

                                    <i class="fas fa-users text-primary me-2"></i>

                                    {{ $selectedClass->name ?? 'Selected Class' }}

                                </h5>

                                <small class="text-muted">

                                    {{ $students->count() }}

                                    {{ Str::plural('student', $students->count()) }}

                                    currently assigned during

                                    <strong>
                                        {{ $currentAcademicYear->name ?? 'N/A' }}
                                    </strong>

                                    —

                                    <strong>
                                        {{ $currentTerm->name ?? 'N/A' }}
                                    </strong>

                                </small>

                            </div>


                            <div class="d-flex flex-wrap gap-2">

                                <button type="button"
                                        id="selectAllButton"
                                        class="btn btn-sm btn-outline-primary">

                                    <i class="fas fa-check-double me-1"></i>

                                    Select All

                                </button>


                                <button type="button"
                                        id="clearAllButton"
                                        class="btn btn-sm btn-outline-secondary">

                                    <i class="fas fa-xmark me-1"></i>

                                    Clear

                                </button>

                            </div>

                        </div>

                    </div>


                    {{-- Table --}}
                    <div class="card-body p-0">

                        <div class="table-responsive">

                            <table class="table table-hover align-middle mb-0"
                                   id="studentsTable">

                                <thead class="table-light">

                                    <tr>

                                        <th class="text-center"
                                            style="width:55px;">

                                            <input type="checkbox"
                                                   id="selectAllCheckbox"
                                                   class="form-check-input"
                                                   title="Select all">

                                        </th>

                                        <th style="width:55px;">
                                            #
                                        </th>

                                        <th>
                                            Student ID
                                        </th>

                                        <th>
                                            Student Name
                                        </th>

                                        <th>
                                            Current Class
                                        </th>

                                        <th style="min-width:170px;">
                                            Action
                                        </th>

                                        <th style="min-width:220px;">
                                            Destination
                                        </th>

                                        <th style="min-width:220px;">
                                            Remarks
                                        </th>

                                    </tr>

                                </thead>


                                <tbody>

                                    @foreach($students as $index => $student)

                                        @php

                                            $assignment = $student->classAssignments->first();

                                            $studentClass = $assignment?->studentClass
                                                ?? $selectedClass;

                                        @endphp

                                        <tr>

                                            {{-- Select --}}
                                            <td class="text-center">

                                                <input type="checkbox"
                                                       name="students[{{ $student->id }}][selected]"
                                                       value="1"
                                                       class="form-check-input student-checkbox">

                                            </td>


                                            {{-- Number --}}
                                            <td class="text-muted">

                                                {{ $index + 1 }}

                                            </td>


                                            {{-- Student ID --}}
                                            <td>

                                                <span class="student-id">

                                                    {{ $student->student_id }}

                                                </span>

                                            </td>


                                            {{-- Name --}}
                                            <td>

                                                <div class="d-flex align-items-center">

                                                    @if(!empty($student->photo))

                                                        <img src="{{ asset('storage/' . $student->photo) }}"
                                                             alt="{{ $student->first_name }}"
                                                             class="student-avatar me-2">

                                                    @else

                                                        <div class="student-avatar-placeholder me-2">

                                                            {{ strtoupper(
                                                                substr(
                                                                    $student->first_name ?? 'S',
                                                                    0,
                                                                    1
                                                                )
                                                            ) }}

                                                        </div>

                                                    @endif


                                                    <div>

                                                        <div class="fw-semibold">

                                                            {{ $student->first_name }}

                                                            @if(!empty($student->middle_name))
                                                                {{ $student->middle_name }}
                                                            @endif

                                                            {{ $student->last_name }}

                                                        </div>

                                                        @if(!empty($student->gender))

                                                            <small class="text-muted">

                                                                {{ $student->gender }}

                                                            </small>

                                                        @endif

                                                    </div>

                                                </div>

                                            </td>


                                            {{-- Current Class --}}
                                            <td>

                                                <span class="badge bg-light text-dark border">

                                                    {{ $studentClass->name ?? 'N/A' }}

                                                </span>

                                            </td>


                                            {{-- Action --}}
                                            <td>

                                                <select name="students[{{ $student->id }}][action]"
                                                        class="form-select form-select-sm action-select"
                                                        data-student-id="{{ $student->id }}">

                                                    <option value="promoted">
                                                        🎓 Promote
                                                    </option>

                                                    <option value="repeated">
                                                        🔄 Repeat
                                                    </option>

                                                    @if(
                                                        $studentClass &&
                                                        str_contains(
                                                            strtoupper(trim($studentClass->name)),
                                                            'JHS 3'
                                                        )
                                                    )

                                                        <option value="graduated">
                                                            🎉 Graduate
                                                        </option>

                                                    @endif

                                                </select>

                                            </td>


                                            {{-- Destination --}}
                                            <td>

                                                <div class="destination-display"
                                                     data-student-id="{{ $student->id }}">

                                                    <div class="promote-destination">

                                                        @if($nextAcademicYear && $nextFirstTerm)

                                                            <div class="destination-main">

                                                                <i class="fas fa-arrow-right text-success me-1"></i>

                                                                {{ $nextAcademicYear->name }}

                                                            </div>

                                                            <small class="text-muted">

                                                                {{ $nextFirstTerm->name }}

                                                            </small>

                                                        @else

                                                            <span class="text-danger">

                                                                Destination unavailable

                                                            </span>

                                                        @endif

                                                    </div>

                                                    <div class="repeat-destination d-none">

                                                        <div class="destination-main">

                                                            <i class="fas fa-repeat text-warning me-1"></i>

                                                            Same Class

                                                        </div>

                                                        <small class="text-muted">

                                                            {{ $nextAcademicYear->name ?? 'Next Year' }}

                                                            —

                                                            {{ $nextFirstTerm->name ?? 'First Term' }}

                                                        </small>

                                                    </div>

                                                    <div class="graduate-destination d-none">

                                                        <div class="destination-main text-dark">

                                                            <i class="fas fa-graduation-cap me-1"></i>

                                                            Graduate

                                                        </div>

                                                        <small class="text-muted">

                                                            Graduation Record

                                                        </small>

                                                    </div>

                                                </div>

                                            </td>


                                            {{-- Remarks --}}
                                            <td>

                                                <input type="text"
                                                       name="students[{{ $student->id }}][remarks]"
                                                       class="form-control form-control-sm"
                                                       placeholder="Optional remarks"
                                                       maxlength="1000">

                                            </td>

                                        </tr>

                                    @endforeach

                                </tbody>

                            </table>

                        </div>

                    </div>


                    {{-- Footer --}}
                    <div class="card-footer bg-white border-0 px-4 py-3">

                        <div class="d-flex flex-wrap justify-content-between align-items-center gap-3">

                            <div class="text-muted small">

                                <i class="fas fa-info-circle me-1"></i>

                                Select the students you want to process.

                            </div>


                            <button type="submit"
                                    class="btn btn-success btn-lg"
                                    id="processButton">

                                <i class="fas fa-check-double me-1"></i>

                                Process Selected Students

                            </button>

                        </div>

                    </div>

                </div>

            </form>

        @else

            {{-- No students --}}
            <div class="card border-0 shadow-sm rounded-4">

                <div class="card-body text-center py-5">

                    <div class="empty-icon mb-3">

                        <i class="fas fa-users-slash"></i>

                    </div>

                    <h5 class="fw-bold">
                        No Students Found
                    </h5>

                    <p class="text-muted mb-0">

                        There are no active students assigned to

                        <strong>
                            {{ $selectedClass->name ?? 'this class' }}
                        </strong>

                        for the current academic period.

                    </p>

                </div>

            </div>

        @endif

    @endif

</div>


{{-- ================================================================
     STYLES
================================================================ --}}
<style>

    .card {
        border-radius: 16px !important;
    }

    .period-icon {
        width: 48px;
        height: 48px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 20px;
    }

    .period-display {
        display: flex;
        align-items: center;
        gap: 25px;
        padding: 15px 18px;
        background: #f8f9fa;
        border-radius: 12px;
    }

    .period-display > div {
        flex: 1;
    }

    .period-label {
        display: block;
        font-size: 11px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: .04em;
        color: #6c757d;
        margin-bottom: 4px;
    }

    .period-value {
        display: block;
        font-size: 17px;
        font-weight: 700;
        color: #212529;
    }

    .period-divider {
        width: 1px;
        height: 40px;
        background: #dee2e6;
        flex: 0 0 1px !important;
    }

    .rules-icon {
        width: 42px;
        height: 42px;
        border-radius: 10px;
        background: #eef3ff;
        color: #0d6efd;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
    }

    .rule-item {
        font-size: 13px;
        color: #495057;
        line-height: 1.5;
    }

    .section-icon {
        width: 42px;
        height: 42px;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .student-id {
        font-family: monospace;
        font-size: 13px;
        font-weight: 600;
    }

    .student-avatar {
        width: 38px;
        height: 38px;
        object-fit: cover;
        border-radius: 50%;
    }

    .student-avatar-placeholder {
        width: 38px;
        height: 38px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        background: #e9ecef;
        color: #495057;
        font-weight: 700;
        flex-shrink: 0;
    }

    .destination-main {
        font-weight: 600;
        font-size: 13px;
    }

    .table th {
        font-size: 12px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: .02em;
        white-space: nowrap;
        color: #495057;
    }

    .table td {
        font-size: 13px;
    }

    .empty-icon {
        width: 70px;
        height: 70px;
        margin: 0 auto;
        border-radius: 50%;
        background: #f1f3f5;
        color: #adb5bd;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 28px;
    }

    .action-select {
        min-width: 155px;
    }

    @media (max-width: 767.98px) {

        .period-display {
            flex-direction: column;
            align-items: flex-start;
            gap: 12px;
        }

        .period-display > div {
            width: 100%;
        }

        .period-divider {
            width: 100%;
            height: 1px;
        }

        .table {
            min-width: 1050px;
        }

    }

</style>


{{-- ================================================================
     JAVASCRIPT
================================================================ --}}
<script>

document.addEventListener('DOMContentLoaded', function () {

    const selectAllCheckbox = document.getElementById('selectAllCheckbox');
    const selectAllButton = document.getElementById('selectAllButton');
    const clearAllButton = document.getElementById('clearAllButton');
    const progressionForm = document.getElementById('progressionForm');
    const processButton = document.getElementById('processButton');

    const getStudentCheckboxes = () => {
        return document.querySelectorAll('.student-checkbox');
    };


    /* ============================================================
       SELECT ALL CHECKBOX
    ============================================================ */

    selectAllCheckbox?.addEventListener('change', function () {

        getStudentCheckboxes().forEach(function (checkbox) {
            checkbox.checked = selectAllCheckbox.checked;
        });

    });


    /* ============================================================
       SELECT ALL BUTTON
    ============================================================ */

    selectAllButton?.addEventListener('click', function () {

        getStudentCheckboxes().forEach(function (checkbox) {
            checkbox.checked = true;
        });

        if (selectAllCheckbox) {
            selectAllCheckbox.checked = true;
        }

    });


    /* ============================================================
       CLEAR ALL BUTTON
    ============================================================ */

    clearAllButton?.addEventListener('click', function () {

        getStudentCheckboxes().forEach(function (checkbox) {
            checkbox.checked = false;
        });

        if (selectAllCheckbox) {
            selectAllCheckbox.checked = false;
        }

    });


    /* ============================================================
       KEEP MASTER CHECKBOX SYNCHRONIZED
    ============================================================ */

    getStudentCheckboxes().forEach(function (checkbox) {

        checkbox.addEventListener('change', function () {

            const checkboxes = Array.from(
                getStudentCheckboxes()
            );

            const checked = checkboxes.filter(
                checkbox => checkbox.checked
            );

            if (selectAllCheckbox) {

                selectAllCheckbox.checked =
                    checkboxes.length > 0 &&
                    checked.length === checkboxes.length;

                selectAllCheckbox.indeterminate =
                    checked.length > 0 &&
                    checked.length < checkboxes.length;

            }

        });

    });


    /* ============================================================
       ACTION DESTINATION DISPLAY
    ============================================================ */

    document.querySelectorAll('.action-select').forEach(function (select) {

        select.addEventListener('change', function () {

            const studentId = this.dataset.studentId;

            const destination =
                document.querySelector(
                    '.destination-display[data-student-id="' +
                    studentId +
                    '"]'
                );

            if (!destination) {
                return;
            }

            const promote =
                destination.querySelector('.promote-destination');

            const repeat =
                destination.querySelector('.repeat-destination');

            const graduate =
                destination.querySelector('.graduate-destination');


            promote?.classList.add('d-none');
            repeat?.classList.add('d-none');
            graduate?.classList.add('d-none');


            if (this.value === 'promoted') {

                promote?.classList.remove('d-none');

            } else if (this.value === 'repeated') {

                repeat?.classList.remove('d-none');

            } else if (this.value === 'graduated') {

                graduate?.classList.remove('d-none');

            }

        });

    });


    /* ============================================================
       FORM SUBMISSION VALIDATION
    ============================================================ */

    progressionForm?.addEventListener('submit', function (event) {

        const checkedStudents =
            document.querySelectorAll(
                '.student-checkbox:checked'
            );


        if (checkedStudents.length === 0) {

            event.preventDefault();

            alert(
                'Please select at least one student to process.'
            );

            return;

        }


        /*
        |--------------------------------------------------------------------------
        | Confirm processing
        |--------------------------------------------------------------------------
        */

        const count = checkedStudents.length;

        const confirmed = confirm(
            'You are about to process ' +
            count +
            (count === 1 ? ' student.' : ' students.') +
            '\n\n' +
            'Promotion and repetition will create new assignments for the next academic year. ' +
            'The existing assignments will remain in the student history.\n\n' +
            'Do you want to continue?'
        );


        if (!confirmed) {

            event.preventDefault();

            return;

        }


        /*
        |--------------------------------------------------------------------------
        | Disable button to prevent double submission
        |--------------------------------------------------------------------------
        */

        if (processButton) {

            processButton.disabled = true;

            processButton.innerHTML =
                '<span class="spinner-border spinner-border-sm me-2"></span>' +
                'Processing...';

        }

    });

});

</script>

@endsection