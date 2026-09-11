@extends('layouts.master')

@section('title', 'Dashboard - EduNexus USMS')

@section('content')

@php
    /*
    |--------------------------------------------------------------------------
    | DASHBOARD DISPLAY DATA
    |--------------------------------------------------------------------------
    |
    | IMPORTANT:
    | All academic year, term, attendance and fee calculations are handled
    | by DashboardController.
    |
    | This Blade is presentation-only.
    |
    | The dashboard period comes from the central System Settings.
    |--------------------------------------------------------------------------
    */

    $dashboardToday = \Carbon\Carbon::today();

    /*
    |--------------------------------------------------------------------------
    | CENTRAL ACADEMIC PERIOD
    |--------------------------------------------------------------------------
    */

    $centralAcademicYear =
        $currentAcademicYear
        ?? $latestAcademicYear
        ?? null;

    $centralTerm =
        $currentTerm
        ?? $term
        ?? null;

    $academicYearName =
        $centralAcademicYear?->name
        ?? $centralAcademicYear?->year_name
        ?? 'No Academic Year Selected';

    $termName =
        $centralTerm?->name
        ?? 'No Term Selected';

    /*
    |--------------------------------------------------------------------------
    | ATTENDANCE DATE
    |--------------------------------------------------------------------------
    */

    $attendanceDate = !empty($attendanceDate)
        ? \Carbon\Carbon::parse($attendanceDate)
        : $dashboardToday->copy();

    /*
    |--------------------------------------------------------------------------
    | FEE VALUES
    |--------------------------------------------------------------------------
    */

    $dashboardTotalFees =
        (float) ($dashboardTotalFees ?? 0);

    $dashboardTotalFeesPaid =
        (float) ($dashboardTotalFeesPaid ?? 0);

    $dashboardOutstandingFees =
        (float) ($dashboardOutstandingFees ?? 0);

    $dashboardCollectionRate =
        (float) ($dashboardCollectionRate ?? 0);

    $dashboardMonthlyChange =
        (float) ($dashboardMonthlyChange ?? 0);

    $dashboardCurrentMonthPaid =
        (float) ($dashboardCurrentMonthPaid ?? 0);

    $dashboardPreviousMonthPaid =
        (float) ($dashboardPreviousMonthPaid ?? 0);

    /*
    |--------------------------------------------------------------------------
    | ATTENDANCE VALUES
    |--------------------------------------------------------------------------
    */

    $studentAttendanceRate =
        (float) ($studentAttendanceRate ?? 0);

    $staffAttendanceRate =
        (float) ($staffAttendanceRate ?? 0);

    $todayPresent =
        (int) ($todayPresent ?? 0);

    $todayAbsent =
        (int) ($todayAbsent ?? 0);

    $todayLate =
        (int) ($todayLate ?? 0);

    $todayExcused =
        (int) ($todayExcused ?? 0);

    $todayTotal =
        (int) ($todayTotal ?? 0);

    $todayAttendanceRate =
        (float) (
            $todayAttendanceRate
            ?? $todayRate
            ?? 0
        );

    /*
    |--------------------------------------------------------------------------
    | CLASS FEE DATA
    |--------------------------------------------------------------------------
    */

    $classFeeRows =
        collect($classFeeData ?? []);

    $classFeeLabels =
        $classFeeRows
            ->pluck('class_name')
            ->values();

    $classExpectedFees =
        $classFeeRows
            ->pluck('expected_fees')
            ->map(fn ($value) => (float) $value)
            ->values();

    $classPaidFees =
        $classFeeRows
            ->pluck('fees_paid')
            ->map(fn ($value) => (float) $value)
            ->values();

@endphp


{{-- =========================================================
     MAIN DASHBOARD
========================================================== --}}

<div class="container-fluid py-4 px-4">


    {{-- =========================================================
         CENTRAL ACADEMIC PERIOD
    ========================================================== --}}

    <div class="card border-0 shadow-sm mb-4">

        <div class="card-body py-3">

            <div class="row g-3 align-items-center">

                <div class="col-md-5 col-lg-4">

                    <div class="text-uppercase fw-bold small text-muted">

                        <i class="fas fa-calendar-alt text-primary me-1"></i>

                        Academic Year

                    </div>

                    <div class="fw-bold text-dark mt-1">

                        {{ $academicYearName }}

                    </div>

                </div>


                <div class="col-md-4 col-lg-3">

                    <div class="text-uppercase fw-bold small text-muted">

                        <i class="fas fa-calendar-week text-info me-1"></i>

                        Current Term

                    </div>

                    <div class="fw-bold text-dark mt-1">

                        {{ $termName }}

                    </div>

                </div>


                <div class="col-md-3 col-lg-5">

                    <div class="d-flex justify-content-md-end align-items-center">

                        <span
                            class="badge rounded-pill bg-success bg-opacity-10 text-success border border-success-subtle px-3 py-2">

                            <i class="fas fa-lock me-1"></i>

                            Central System Period

                        </span>

                        <small class="text-muted ms-2">

                            Controlled by System Settings

                        </small>

                    </div>

                </div>

            </div>

        </div>

    </div>


    {{-- =========================================================
         TOP METRICS
    ========================================================== --}}

    <div class="row g-4 mb-4">


        {{-- TOTAL STUDENTS --}}

        <div class="col-md-6 col-xl-3">

            <div class="card stats-card shadow-sm h-100 border-0">

                <div class="card-body p-4">

                    <div
                        class="d-flex justify-content-between align-items-start mb-3">

                        <div>

                            <small class="text-uppercase fw-bold text-muted">

                                <i class="fas fa-chalkboard-user me-1 text-dark"></i>

                                Total Students

                            </small>

                            <h1 class="fw-bold display-5 mt-2 mb-1">

                                {{ number_format($totalStudents ?? 0) }}

                            </h1>

                        </div>


                        <div
                            class="bg-primary bg-opacity-10 rounded-circle p-3">

                            <i
                                class="fas fa-user-graduate text-dark fs-3">
                            </i>

                        </div>

                    </div>


                    <div class="d-flex gap-2 flex-wrap mt-3">

                        <span class="badge bg-light text-dark">

                            <i class="fas fa-mars me-1 text-primary"></i>

                            {{ number_format($maleCount ?? 0) }} Male

                        </span>


                        <span class="badge bg-light text-dark">

                            <i class="fas fa-venus me-1 text-danger"></i>

                            {{ number_format($femaleCount ?? 0) }} Female

                        </span>

                    </div>


                    <small class="text-muted d-block mt-3">

                        <i class="fas fa-user-plus me-1"></i>

                        {{ number_format($studentsThisYear ?? 0) }}

                        registered this year

                    </small>

                </div>

            </div>

        </div>


        {{-- TOTAL STAFF --}}

        <div class="col-md-6 col-xl-3">

            <div class="card stats-card shadow-sm h-100 border-0">

                <div class="card-body p-4">

                    <div
                        class="d-flex justify-content-between align-items-start mb-3">

                        <div>

                            <small class="text-uppercase fw-bold text-muted">

                                <i class="fas fa-users me-1 text-success"></i>

                                Total Staff

                            </small>

                            <h1 class="fw-bold display-5 mt-2 mb-1">

                                {{ number_format($totalStaff ?? 0) }}

                            </h1>

                        </div>


                        <div
                            class="bg-success bg-opacity-10 rounded-circle p-3">

                            <i class="fas fa-user-tie text-success fs-3"></i>

                        </div>

                    </div>


                    <div class="mt-3">

                        <div
                            class="d-flex justify-content-between small mb-1">

                            <span class="text-muted">
                                Attendance Rate
                            </span>

                            <strong>
                                {{ number_format($staffAttendanceRate, 1) }}%
                            </strong>

                        </div>


                        <div
                            class="progress"
                            style="height:7px;">

                            <div
                                class="progress-bar bg-success"
                                style="
                                    width:
                                    {{ min(100, max(0, $staffAttendanceRate)) }}%;
                                ">
                            </div>

                        </div>

                    </div>


                    <small class="text-muted d-block mt-3">

                        <i class="fas fa-user-plus me-1"></i>

                        {{ number_format($staffThisYear ?? 0) }}

                        registered this year

                    </small>

                </div>

            </div>

        </div>


        {{-- ACTIVE CLASSES --}}

        <div class="col-md-6 col-xl-3">

            <div class="card stats-card shadow-sm h-100 border-0">

                <div class="card-body p-4">

                    <div
                        class="d-flex justify-content-between align-items-start mb-3">

                        <div>

                            <small class="text-uppercase fw-bold text-muted">

                                <i class="fas fa-school me-1 text-info"></i>

                                Active Classes

                            </small>

                            <h1 class="fw-bold display-5 mt-2 mb-1">

                                {{ number_format($activeClasses ?? 0) }}

                            </h1>

                        </div>


                        <div
                            class="bg-info bg-opacity-10 rounded-circle p-3">

                            <i class="fas fa-chalkboard text-info fs-3"></i>

                        </div>

                    </div>


                    <small class="fw-semibold text-dark">

                        <i class="fas fa-calendar-week me-1"></i>

                        Current Academic Term

                    </small>


                    <div class="mt-3">

                        <span class="badge bg-light text-dark">

                            {{ number_format($totalClasses ?? 0) }}

                            total classes

                        </span>

                    </div>

                </div>

            </div>

        </div>


        {{-- ATTENDANCE RATE --}}

        <div class="col-md-6 col-xl-3">

            <div class="card stats-card shadow-sm h-100 border-0">

                <div class="card-body p-4">

                    <div
                        class="d-flex justify-content-between align-items-start mb-3">

                        <div>

                            <small class="text-uppercase fw-bold text-muted">

                                <i class="fas fa-percent me-1 text-danger"></i>

                                Attendance Rate

                            </small>

                            <h1 class="fw-bold display-4 mt-3 mb-1">

                                {{ number_format($studentAttendanceRate, 1) }}%

                            </h1>

                            <small class="fw-semibold text-dark">

                                <i class="fas fa-chart-line me-1"></i>

                                Student Attendance Performance

                            </small>

                        </div>


                        <div
                            class="rounded-circle bg-danger bg-opacity-10 d-flex align-items-center justify-content-center"
                            style="width:70px;height:70px;">

                            <i
                                class="fas fa-calendar-check text-danger fs-2">
                            </i>

                        </div>

                    </div>

                </div>

            </div>

        </div>

    </div>


    {{-- =========================================================
         FEE SUMMARY CARDS
    ========================================================== --}}

    <div class="row g-4 mb-4">


        {{-- TOTAL FEES EXPECTED --}}

        <div class="col-md-6 col-xl-3">

            <div class="card stats-card shadow-sm h-100 border-0">

                <div class="card-body p-4">

                    <div class="analytics-header">

                        <i class="fas fa-file-invoice-dollar me-1 text-primary"></i>

                        TOTAL FEES EXPECTED

                    </div>


                    <h1 class="fw-bold display-5 mt-2">

                        GHS
                        {{ number_format($dashboardTotalFees, 2) }}

                    </h1>


                    <small class="text-muted">

                        Current academic period

                    </small>

                </div>

            </div>

        </div>


        {{-- TOTAL FEES PAID --}}

        <div class="col-md-6 col-xl-3">

            <div class="card stats-card shadow-sm h-100 border-0">

                <div class="card-body p-4">

                    <div class="analytics-header">

                        <i class="fas fa-coins me-1 text-success"></i>

                        TOTAL FEES PAID

                    </div>


                    <h1 class="fw-bold display-5 mt-2">

                        GHS
                        {{ number_format($dashboardTotalFeesPaid, 2) }}

                    </h1>


                    <small
                        class="
                            {{ $dashboardMonthlyChange >= 0
                                ? 'text-success'
                                : 'text-danger' }}
                        ">

                        <i
                            class="
                                fas
                                {{
                                    $dashboardMonthlyChange >= 0
                                        ? 'fa-arrow-up'
                                        : 'fa-arrow-down'
                                }}
                                me-1
                            ">
                        </i>

                        {{ number_format(abs($dashboardMonthlyChange), 1) }}%

                        monthly change

                    </small>

                </div>

            </div>

        </div>


        {{-- OUTSTANDING FEES --}}

        <div class="col-md-6 col-xl-3">

            <div class="card stats-card shadow-sm h-100 border-0">

                <div class="card-body p-4">

                    <div class="analytics-header">

                        <i class="fas fa-triangle-exclamation me-1 text-danger"></i>

                        OUTSTANDING FEES

                    </div>


                    <h1 class="fw-bold display-5 mt-2 text-danger">

                        GHS
                        {{ number_format($dashboardOutstandingFees, 2) }}

                    </h1>


                    <small class="text-muted">

                        Amount still to be collected

                    </small>

                </div>

            </div>

        </div>


        {{-- COLLECTION RATE --}}

        <div class="col-md-6 col-xl-3">

            <div class="card stats-card shadow-sm h-100 border-0">

                <div class="card-body p-4">

                    <div class="analytics-header">

                        <i class="fas fa-chart-pie me-1 text-info"></i>

                        COLLECTION RATE

                    </div>


                    <h1 class="fw-bold display-5 mt-2">

                        {{ number_format($dashboardCollectionRate, 1) }}%

                    </h1>


                    <div
                        class="progress mt-3"
                        style="height:8px;">

                        <div
                            class="progress-bar bg-success"
                            style="
                                width:
                                {{ min(
                                    100,
                                    max(
                                        0,
                                        $dashboardCollectionRate
                                    )
                                ) }}%;
                            ">
                        </div>

                    </div>

                </div>

            </div>

        </div>

    </div>


    {{-- =========================================================
         DAILY ATTENDANCE CHART + SCHOOL FEE POSITION
    ========================================================== --}}

    <div class="row g-4 mb-4">


        {{-- ATTENDANCE --}}

        <div class="col-xl-8">

            <div
                class="card dashboard-card border-0 shadow-sm overflow-hidden">

                <div class="card-body p-4">


                    <div
                        class="d-flex flex-wrap justify-content-between align-items-center mb-2">

                        <div>

                            <h2 class="fw-light mb-1 fs-3">

                                <i
                                    class="fas fa-chart-line text-primary me-2">
                                </i>

                                Daily Attendance Analysis

                            </h2>


                            <small class="text-secondary">

                                Students present and late per day
                                from the current academic term

                            </small>

                        </div>


                        <div
                            class="d-flex gap-2 mt-2 mt-lg-0 flex-wrap no-print">


                            <select
                                class="form-select"
                                id="periodSelect"
                                style="width:auto;">

                                <option value="90">
                                    Last 90 Days
                                </option>

                                <option
                                    value="30"
                                    selected>

                                    Last 30 Days

                                </option>

                                <option value="7">
                                    Last 7 Days
                                </option>

                            </select>


                            <select
                                class="form-select"
                                id="chartTypeSelect"
                                style="width:auto;">

                                <option value="line">
                                    📈 Line Chart
                                </option>

                                <option value="bar">
                                    📊 Bar Chart
                                </option>

                                <option value="spline">
                                    〰️ Spline Chart
                                </option>

                                <option value="area">
                                    📉 Area Chart
                                </option>

                                <option value="splineArea">
                                    🌀 Spline Area Chart
                                </option>

                            </select>


                            <div class="color-picker-group">

                                <label>

                                    <i class="fas fa-palette me-1"></i>

                                    Color:

                                </label>


                                <input
                                    type="color"
                                    id="lineColorPicker"
                                    value="#1a4b8c"
                                    title="Chart Color">

                            </div>

                        </div>

                    </div>


                    <div
                        class="d-flex justify-content-between align-items-center flex-wrap">

                        <div class="custom-legend">


                            <div>

                                <span
                                    class="legend-dot-blue"
                                    id="legendColorDot"
                                    style="background:#1a4b8c;">
                                </span>

                                <span class="fw-semibold">

                                    Daily attendance

                                </span>

                            </div>


                            <div>

                                <span class="legend-dot-lightblue"></span>

                                <span class="fw-semibold">

                                    Average benchmark

                                </span>

                                <span class="text-muted small">

                                    (average)

                                </span>

                            </div>

                        </div>


                        <div
                            class="attendance-insight mt-2 mt-sm-0"
                            id="attendanceInsight">

                            <i
                                class="fas fa-spinner fa-spin me-1 text-info">
                            </i>

                            Loading attendance insight...

                        </div>

                    </div>


                    <div
                        class="chart-container mt-3"
                        style="height:500px;">

                        <canvas
                            id="attendanceChartCanvas">
                        </canvas>

                    </div>


                    <div
                        class="text-muted small mt-3 text-end border-top pt-2">

                        <i class="fas fa-info-circle me-1"></i>

                        Y-axis: number of students present/late |

                        <span id="chartTypeIndicator">

                            Line Chart

                        </span>

                        |

                        Live attendance data from database

                    </div>

                </div>

            </div>

        </div>


        {{-- SCHOOL FEE POSITION --}}

        <div class="col-xl-4">

            <div class="card dashboard-card border-0 shadow-sm h-100">

                <div class="card-body p-4">


                    <div
                        class="d-flex justify-content-between align-items-start mb-3">

                        <div>

                            <h5 class="fw-bold mb-1">

                                <i
                                    class="fas fa-chart-pie text-success me-2">
                                </i>

                                School Fee Position

                            </h5>


                            <small class="text-muted">

                                Paid versus outstanding fees

                            </small>

                        </div>


                        <span
                            class="badge bg-success bg-opacity-10 text-success">

                            {{ number_format($dashboardCollectionRate, 1) }}%

                        </span>

                    </div>


                    <div
                        class="chart-container"
                        style="height:280px;">

                        <canvas id="feePositionChart"></canvas>

                    </div>


                    <div class="row g-2 mt-2">


                        <div class="col-6">

                            <div class="summary-box">

                                <div class="label">
                                    Paid
                                </div>

                                <div class="value text-success">

                                    GHS
                                    {{ number_format(
                                        $dashboardTotalFeesPaid,
                                        2
                                    ) }}

                                </div>

                            </div>

                        </div>


                        <div class="col-6">

                            <div class="summary-box">

                                <div class="label">
                                    Outstanding
                                </div>

                                <div class="value text-danger">

                                    GHS
                                    {{ number_format(
                                        $dashboardOutstandingFees,
                                        2
                                    ) }}

                                </div>

                            </div>

                        </div>

                    </div>


                    <div class="mt-3">

                        <div
                            class="d-flex justify-content-between small mb-1">

                            <span>
                                Collection Rate
                            </span>

                            <strong>

                                {{ number_format(
                                    $dashboardCollectionRate,
                                    1
                                ) }}%

                            </strong>

                        </div>


                        <div
                            class="progress fee-progress">

                            <div
                                class="progress-bar bg-success"
                                style="
                                    width:
                                    {{ min(
                                        100,
                                        max(
                                            0,
                                            $dashboardCollectionRate
                                        )
                                    ) }}%;
                                ">
                            </div>

                        </div>

                    </div>

                </div>

            </div>

        </div>

    </div>


    {{-- =========================================================
         CLASS FEE ANALYTICS + RECENT ATTENDANCE
    ========================================================== --}}

    <div class="row g-4 mb-4">


        {{-- CLASS FEE ANALYTICS --}}

        <div class="col-xl-8">

            <div class="dash-card">

                <div class="section-header">

                    <div class="section-title">

                        <i
                            class="fas fa-chart-column text-primary me-2">
                        </i>

                        Fee Performance by Class

                    </div>


                    <div class="section-subtitle">

                        Expected fees compared with actual collections
                        for the current academic term

                    </div>

                </div>


                <div class="card-body">

                    @if($classFeeLabels->isNotEmpty())

                        <div
                            class="chart-wrapper large">

                            <canvas
                                id="classFeeChart">
                            </canvas>

                        </div>

                    @else

                        <div class="empty-state">

                            <div class="empty-icon">

                                <i
                                    class="fas fa-file-invoice-dollar">
                                </i>

                            </div>


                            <strong class="text-dark">

                                No class fee data

                            </strong>


                            <small>

                                No current bill sheets are available
                                for the dashboard.

                            </small>

                        </div>

                    @endif

                </div>

            </div>

        </div>


        {{-- RECENT ATTENDANCE --}}

        <div class="col-xl-4">

            <div class="dash-card h-100">

                <div class="section-header">

                    <div class="section-title">

                        <i
                            class="fas fa-clock-rotate-left text-info me-2">
                        </i>

                        Recent Attendance Activity

                    </div>


                    <div class="section-subtitle">

                        Latest recorded attendance events

                    </div>

                </div>


                <div class="card-body">

                    @forelse(($recentAttendance ?? collect()) as $record)

                        @php

                            $recordStatus =
                                strtolower(
                                    (string) (
                                        $record->status ?? ''
                                    )
                                );

                            $recordClass =
                                match ($recordStatus) {

                                    'present'
                                        => 'text-success',

                                    'late'
                                        => 'text-warning',

                                    'absent'
                                        => 'text-danger',

                                    'excused'
                                        => 'text-info',

                                    default
                                        => 'text-info',

                                };

                        @endphp


                        <div class="activity-item">


                            <div class="activity-icon">

                                <i class="fas fa-user-check"></i>

                            </div>


                            <div class="flex-grow-1">

                                <div class="activity-name">

                                    {{
                                        trim(
                                            ($record->first_name ?? '') .
                                            ' ' .
                                            ($record->last_name ?? '')
                                        )
                                        ?: 'Student'
                                    }}

                                </div>


                                <div class="activity-meta">

                                    ID:

                                    {{
                                        $record->student_number
                                        ?? $record->student_id
                                        ?? 'N/A'
                                    }}


                                    <span class="mx-1">
                                        •
                                    </span>


                                    <span class="{{ $recordClass }} fw-semibold">

                                        {{
                                            ucfirst(
                                                $recordStatus
                                                ?: 'Recorded'
                                            )
                                        }}

                                    </span>


                                    @if(!empty($record->attendance_date))

                                        <span class="mx-1">
                                            •
                                        </span>

                                        {{
                                            \Carbon\Carbon::parse(
                                                $record->attendance_date
                                            )->format('d M Y')
                                        }}

                                    @endif

                                </div>

                            </div>

                        </div>


                    @empty

                        <div class="empty-state">

                            <div class="empty-icon">

                                <i
                                    class="fas fa-calendar-xmark">
                                </i>

                            </div>


                            <strong class="text-dark">

                                No recent attendance

                            </strong>


                            <small class="mt-1">

                                No attendance activity has been
                                recorded for the current period.

                            </small>

                        </div>

                    @endforelse

                </div>

            </div>

        </div>

    </div>


    {{-- =========================================================
         CLASS ATTENDANCE TABLE
    ========================================================== --}}

    <div class="row mt-4">

        <div class="col-12">

            <div class="card border-0 shadow-sm">


                <div
                    class="card-header bg-white d-flex justify-content-between align-items-center py-3">


                    <div>

                        <h5 class="fw-bold mb-1">

                            <i
                                class="fas fa-calendar-check text-primary me-2">
                            </i>

                            Daily Attendance by Class


                            <span
                                class="badge bg-light text-dark ms-2"
                                id="attendanceDateDisplay">

                                {{ $attendanceDate->format('d M Y') }}

                            </span>

                        </h5>


                        <small class="text-muted">

                            Attendance for the selected date within
                            the central academic period.

                        </small>

                    </div>


                    <div class="d-flex gap-2 no-print">


                        <input
                            type="date"
                            id="attendanceDateFilter"
                            class="form-control form-control-sm"
                            style="width:auto;"
                            value="{{ $attendanceDate->format('Y-m-d') }}">


                        <button
                            class="btn btn-sm btn-primary"
                            id="loadClassAttendanceBtn"
                            type="button">

                            <i class="fas fa-sync me-1"></i>

                            Load

                        </button>


                        <button
                            class="btn btn-sm btn-outline-success"
                            type="button"
                            onclick="exportClassAttendance()">

                            <i class="fas fa-file-excel me-1"></i>

                            Export

                        </button>

                    </div>

                </div>


                <div class="card-body">


                    <div
                        id="attendanceTableAlert"
                        class="alert d-none"
                        role="alert">
                    </div>


                    <div class="table-responsive">


                        <table
                            class="table table-bordered table-hover align-middle"
                            id="classAttendanceTable">


                            <thead class="table-light">

                                <tr>

                                    <th width="50">
                                        #
                                    </th>

                                    <th>
                                        Class Name
                                    </th>

                                    <th class="text-center">
                                        Total Students
                                    </th>

                                    <th class="text-center">
                                        Present
                                    </th>

                                    <th class="text-center">
                                        Absent
                                    </th>

                                    <th class="text-center">
                                        Late
                                    </th>

                                    <th class="text-center">
                                        Excused
                                    </th>

                                    <th class="text-center">
                                        Attendance Rate
                                    </th>

                                    <th class="text-center">
                                        Status
                                    </th>

                                </tr>

                            </thead>


                            <tbody id="classAttendanceBody">

                                @forelse(($dashboardDailyAttendance ?? []) as $attendance)

                                    @php

                                        $rate =
                                            (float) (
                                                $attendance['rate']
                                                ?? 0
                                            );

                                        $safeRate =
                                            min(
                                                100,
                                                max(
                                                    0,
                                                    $rate
                                                )
                                            );

                                        $status =
                                            $attendance['status']
                                            ?? 'Not Taken';

                                        $statusClass =
                                            $attendance['status_class']
                                            ?? 'secondary';

                                        $allowedClasses = [
                                            'success',
                                            'info',
                                            'warning',
                                            'danger',
                                            'secondary',
                                        ];

                                        if (
                                            !in_array(
                                                $statusClass,
                                                $allowedClasses,
                                                true
                                            )
                                        ) {
                                            $statusClass =
                                                'secondary';
                                        }

                                    @endphp


                                    <tr>

                                        <td>
                                            {{ $loop->iteration }}
                                        </td>


                                        <td class="fw-semibold">

                                            {{
                                                $attendance['class_name']
                                                ?? 'N/A'
                                            }}

                                        </td>


                                        <td class="text-center">

                                            <span
                                                class="badge bg-light text-dark">

                                                {{
                                                    number_format(
                                                        $attendance[
                                                            'total_students'
                                                        ] ?? 0
                                                    )
                                                }}

                                            </span>

                                        </td>


                                        <td class="text-center">

                                            <span
                                                class="badge bg-success-subtle text-success">

                                                {{
                                                    number_format(
                                                        $attendance[
                                                            'present'
                                                        ] ?? 0
                                                    )
                                                }}

                                            </span>

                                        </td>


                                        <td class="text-center">

                                            <span
                                                class="badge bg-danger-subtle text-danger">

                                                {{
                                                    number_format(
                                                        $attendance[
                                                            'absent'
                                                        ] ?? 0
                                                    )
                                                }}

                                            </span>

                                        </td>


                                        <td class="text-center">

                                            <span
                                                class="badge bg-warning-subtle text-warning">

                                                {{
                                                    number_format(
                                                        $attendance[
                                                            'late'
                                                        ] ?? 0
                                                    )
                                                }}

                                            </span>

                                        </td>


                                        <td class="text-center">

                                            <span
                                                class="badge bg-info-subtle text-info">

                                                {{
                                                    number_format(
                                                        $attendance[
                                                            'excused'
                                                        ] ?? 0
                                                    )
                                                }}

                                            </span>

                                        </td>


                                        <td
                                            class="text-center"
                                            style="min-width:170px;">

                                            <div
                                                class="d-flex align-items-center justify-content-center gap-2">

                                                <div
                                                    class="progress flex-grow-1"
                                                    style="
                                                        height:8px;
                                                        max-width:90px;
                                                    ">

                                                    <div
                                                        class="
                                                            progress-bar
                                                            bg-{{ $statusClass }}
                                                        "
                                                        role="progressbar"
                                                        style="
                                                            width:
                                                            {{ $safeRate }}%;
                                                        "
                                                        aria-valuenow="{{ $safeRate }}"
                                                        aria-valuemin="0"
                                                        aria-valuemax="100">
                                                    </div>

                                                </div>


                                                <span class="fw-bold">

                                                    {{ number_format(
                                                        $safeRate,
                                                        1
                                                    ) }}%

                                                </span>

                                            </div>

                                        </td>


                                        <td class="text-center">

                                            <span
                                                class="badge bg-{{ $statusClass }}">

                                                @php

                                                    $statusIcon =
                                                        match ($status) {

                                                            'Excellent'
                                                                => 'fa-check-circle',

                                                            'Good'
                                                                => 'fa-thumbs-up',

                                                            'Average'
                                                                => 'fa-exclamation-triangle',

                                                            'Poor'
                                                                => 'fa-times-circle',

                                                            'Needs Attention'
                                                                => 'fa-exclamation-triangle',

                                                            'Critical'
                                                                => 'fa-times-circle',

                                                            'Not Taken'
                                                                => 'fa-minus-circle',

                                                            default
                                                                => 'fa-info-circle',

                                                        };

                                                @endphp


                                                <i
                                                    class="
                                                        fas
                                                        {{ $statusIcon }}
                                                        me-1
                                                    ">
                                                </i>


                                                {{ $status }}

                                            </span>

                                        </td>

                                    </tr>


                                @empty

                                    <tr>

                                        <td
                                            colspan="9"
                                            class="text-center py-5 text-muted">

                                            <i
                                                class="
                                                    fas
                                                    fa-school
                                                    fs-2
                                                    d-block
                                                    mb-2
                                                ">
                                            </i>


                                            No attendance data found
                                            for the selected date.

                                        </td>

                                    </tr>

                                @endforelse

                            </tbody>

                        </table>

                    </div>


                    <div
                        class="
                            d-flex
                            flex-wrap
                            justify-content-between
                            gap-2
                            mt-3
                            small
                            text-muted
                        ">


                        <span>

                            <i class="fas fa-school me-1"></i>

                            Active classes with current students:

                            <strong id="totalClassesCount">

                                {{ count($dashboardDailyAttendance ?? []) }}

                            </strong>

                        </span>


                        <span>

                            Last updated:

                            <strong id="lastUpdatedTime">

                                {{ now()->format('H:i:s') }}

                            </strong>

                        </span>

                    </div>

                </div>

            </div>

        </div>

    </div>



    {{-- =========================================================
         DASHBOARD FOOTER
    ========================================================== --}}

    <div class="card border-0 shadow-sm mt-4">

        <div class="card-body py-3">

            <div
                class="
                    d-flex
                    flex-wrap
                    justify-content-between
                    align-items-center
                    gap-3
                    small
                ">


                <div>

                    <i class="fas fa-chart-pie text-primary me-2"></i>

                    <strong>
                        Attendance dashboard:
                    </strong>

                    <span class="fw-bold text-primary">

                        Live data enabled

                    </span>

                </div>


                <div>

                    <i class="fas fa-database text-info me-1"></i>

                    <span class="text-muted">

                        Real-time class attendance

                    </span>

                </div>


                <div>

                    <i class="fas fa-calendar-alt text-success me-1"></i>

                    <span class="text-muted">

                        {{ $academicYearName }}

                        <span class="mx-1">•</span>

                        {{ $termName }}

                    </span>

                </div>


                <div>

                    <small class="text-muted">

                        <i class="far fa-calendar-alt"></i>

                        Data as of

                        {{ \Carbon\Carbon::now()->format(
                            'd-M-Y | H:i'
                        ) }}

                    </small>

                </div>

            </div>

        </div>

    </div>


</div>


{{-- =========================================================
     STYLES
========================================================== --}}

@push('styles')

<style>

    .display-4 {
        font-size: 2.5rem;
        font-weight: 700;
    }


    .display-5 {
        font-size: 2rem;
        font-weight: 700;
    }


    .analytics-header {
        font-size: .72rem;
        font-weight: 800;
        letter-spacing: .5px;
        text-transform: uppercase;
        color: #6c7283;
    }


    .custom-legend {
        display: flex;
        gap: 28px;
        align-items: center;
        margin-top: 6px;
        justify-content: flex-end;
        flex-wrap: wrap;
    }


    .legend-dot-blue,
    .legend-dot-lightblue {
        width: 12px;
        height: 12px;
        border-radius: 20px;
        display: inline-block;
        margin-right: 8px;
    }


    .legend-dot-blue {
        background: #1a4b8c;
    }


    .legend-dot-lightblue {
        background: #3b82f6;
    }


    .attendance-insight {
        background: #eff6ff;
        border-radius: 40px;
        padding: 5px 18px;
        font-size: 0.75rem;
        font-weight: 500;
        color: #1a4b8c;
        border: 1px solid #bfdbfe;
    }


    .dashboard-card {
        border-radius: 14px;
    }


    .stats-card {
        border-radius: 14px;
        transition: transform .2s ease, box-shadow .2s ease;
    }


    .stats-card:hover {
        transform: translateY(-2px);
    }


    .summary-box {
        background: #f8fafc;
        border: 1px solid #edf1f5;
        border-radius: 10px;
        padding: 10px;
    }


    .summary-box .label {
        font-size: .68rem;
        font-weight: 700;
        color: #6c7283;
        text-transform: uppercase;
        letter-spacing: .4px;
    }


    .summary-box .value {
        margin-top: 4px;
        font-size: .92rem;
        font-weight: 800;
    }


    .chart-container {
        position: relative;
        width: 100%;
    }


    .chart-wrapper {
        position: relative;
        width: 100%;
        height: 300px;
    }


    .chart-wrapper.large {
        height: 360px;
    }


    .color-picker-group {
        display: flex;
        align-items: center;
        gap: 6px;
        border: 1px solid #dee2e6;
        border-radius: 8px;
        padding: 2px 7px;
        background: #fff;
    }


    .color-picker-group label {
        font-size: .72rem;
        font-weight: 600;
        color: #6c7283;
        margin: 0;
    }


    #lineColorPicker {
        width: 34px;
        height: 34px;
        border: 2px solid white;
        border-radius: 12px;
        cursor: pointer;
        background: #1a4b8c;
        box-shadow: 0 1px 4px rgba(0,0,0,0.1);
    }


    #classAttendanceTable th {
        font-size: 0.75rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }


    #classAttendanceTable td {
        vertical-align: middle;
        font-size: 0.9rem;
    }


    .attendance-badge {
        padding: 4px 12px;
        border-radius: 20px;
        font-weight: 600;
        font-size: 0.75rem;
    }


    .activity-item {
        display: flex;
        gap: 10px;
        align-items: center;
        padding: .72rem 0;
        border-bottom: 1px solid #eef2f6;
    }


    .activity-item:last-child {
        border-bottom: 0;
    }


    .activity-icon {
        flex: 0 0 36px;
        width: 36px;
        height: 36px;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #1a4b8c;
        background: #eef5ff;
    }


    .activity-name {
        color: #172033;
        font-size: .76rem;
        font-weight: 700;
    }


    .activity-meta {
        margin-top: 2px;
        color: #6c7283;
        font-size: .66rem;
    }


    .empty-state {
        min-height: 255px;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-direction: column;
        color: #6c7283;
        text-align: center;
    }


    .empty-icon {
        width: 58px;
        height: 58px;
        margin-bottom: .8rem;
        border-radius: 16px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        background: #f2f4f7;
        color: #98a2b3;
        font-size: 1.5rem;
    }


    .fee-progress {
        height: 8px;
        border-radius: 20px;
        background: #edf1f5;
    }


    .fee-progress .progress-bar {
        border-radius: 20px;
    }


    @media (max-width: 768px) {

        .display-4 {
            font-size: 1.8rem;
        }


        .display-5 {
            font-size: 1.5rem;
        }


        .custom-legend {
            justify-content: flex-start;
        }


        #classAttendanceTable {
            min-width: 920px;
        }


        .chart-wrapper.large {
            height: 300px;
        }

    }


    @media print {

        .no-print {
            display: none !important;
        }


        .dashboard-card,
        .stats-card,
        .card {
            box-shadow: none !important;
        }

    }

</style>

@endpush


{{-- =========================================================
     JAVASCRIPT
========================================================== --}}

@push('scripts')

<script>

    /*
    |--------------------------------------------------------------------------
    | CHART CONFIGURATION
    |--------------------------------------------------------------------------
    */

    let currentChart = null;

    let currentChartType = 'line';

    let currentColor = '#1a4b8c';

    let chartDataFromServer = {

        labels: [],

        attendance: [],

        benchmark: [],

        peak: 0,

        peak_day: 'N/A',

        average: 0,

        total: 0

    };


    /*
    |--------------------------------------------------------------------------
    | HELPERS
    |--------------------------------------------------------------------------
    */

    function formatNumber(value) {

        return Number(
            value || 0
        ).toLocaleString();

    }


    function formatCurrency(value) {

        return 'GHS ' +
            Number(
                value || 0
            ).toLocaleString(
                undefined,
                {
                    minimumFractionDigits: 2,
                    maximumFractionDigits: 2
                }
            );

    }


    function escapeHtml(value) {

        return String(
            value ?? ''
        )
            .replace(
                /&/g,
                '&amp;'
            )
            .replace(
                /</g,
                '&lt;'
            )
            .replace(
                />/g,
                '&gt;'
            )
            .replace(
                /"/g,
                '&quot;'
            )
            .replace(
                /'/g,
                '&#039;'
            );

    }


    function getChartTension(chartType) {

        return (
            chartType === 'spline' ||
            chartType === 'splineArea'
        )
            ? 0.4
            : 0.2;

    }


    function shouldFill(chartType) {

        return (
            chartType === 'area' ||
            chartType === 'splineArea'
        );

    }


    function getBaseChartType(chartType) {

        return chartType === 'bar'
            ? 'bar'
            : 'line';

    }


    function updateLegendDot(color) {

        const dot =
            document.getElementById(
                'legendColorDot'
            );

        if (dot) {

            dot.style.backgroundColor =
                color;

        }

    }


    function updateChartTypeIndicator(chartType) {

        const indicator =
            document.getElementById(
                'chartTypeIndicator'
            );

        if (!indicator) {

            return;

        }


        const names = {

            line:
                'Line Chart',

            bar:
                'Bar Chart',

            spline:
                'Spline Chart (Smooth Curve)',

            area:
                'Area Chart',

            splineArea:
                'Spline Area Chart'

        };


        indicator.textContent =
            names[chartType]
            || 'Line Chart';

    }


    function updateAttendanceInsight(data) {

        const insight =
            document.getElementById(
                'attendanceInsight'
            );

        if (!insight) {

            return;

        }


        if (
            !data.labels ||
            !data.labels.length
        ) {

            insight.innerHTML = `

                <i
                    class="fas fa-exclamation-circle me-1 text-danger">
                </i>

                No attendance data found for this period

            `;

            return;

        }


        insight.innerHTML = `

            <i
                class="fas fa-chart-simple me-1 text-info">
            </i>

            Peak attendance:

            <strong>
                ${formatNumber(data.peak)}
            </strong>

            students

            <span class="mx-1">
                (
            </span>

            <strong>
                ${escapeHtml(data.peak_day)}
            </strong>

            <span class="mx-1">
                )
            </span>

            · Daily average:

            <strong>
                ${formatNumber(data.average)}
            </strong>

        `;

    }


    /*
    |--------------------------------------------------------------------------
    | LOAD ATTENDANCE CHART DATA
    |--------------------------------------------------------------------------
    */

    async function loadAttendanceData(days = 30) {

        const insight =
            document.getElementById(
                'attendanceInsight'
            );


        if (insight) {

            insight.innerHTML = `

                <i
                    class="fas fa-spinner fa-spin me-1 text-info">
                </i>

                Loading attendance data...

            `;

        }


        try {

            const response =
                await fetch(
                    `{{ route(
                        'dashboard.attendance-data'
                    ) }}?days=${encodeURIComponent(days)}`,
                    {
                        headers: {
                            'Accept':
                                'application/json',

                            'X-Requested-With':
                                'XMLHttpRequest'
                        }
                    }
                );


            if (!response.ok) {

                throw new Error(
                    `HTTP ${response.status}`
                );

            }


            const data =
                await response.json();


            if (
                !data ||
                data.status === 'error'
            ) {

                throw new Error(
                    'Attendance data could not be loaded.'
                );

            }


            chartDataFromServer = {

                labels:
                    data.labels || [],

                attendance:
                    data.attendance || [],

                benchmark:
                    data.benchmark || [],

                peak:
                    data.peak || 0,

                peak_day:
                    data.peak_day || 'N/A',

                average:
                    data.average || 0,

                total:
                    data.total || 0

            };


            updateAttendanceInsight(
                chartDataFromServer
            );


            renderAttendanceChart(
                currentChartType,
                currentColor
            );

        } catch (error) {

            console.error(
                'Error loading attendance data:',
                error
            );


            if (insight) {

                insight.innerHTML = `

                    <i
                        class="fas fa-exclamation-circle me-1 text-danger">
                    </i>

                    Failed to load attendance data

                `;

            }

        }

    }


    /*
    |--------------------------------------------------------------------------
    | RENDER ATTENDANCE CHART
    |--------------------------------------------------------------------------
    */

    function renderAttendanceChart(
        chartType = currentChartType,
        lineColor = currentColor
    ) {

        const canvas =
            document.getElementById(
                'attendanceChartCanvas'
            );


        if (
            !canvas ||
            typeof Chart === 'undefined'
        ) {

            return;

        }


        const ctx =
            canvas.getContext('2d');


        if (currentChart) {

            currentChart.destroy();

        }


        const labels =
            chartDataFromServer.labels
            || [];


        const attendanceData =
            chartDataFromServer.attendance
            || [];


        const benchmarkData =
            chartDataFromServer.benchmark
            || [];


        const baseType =
            getBaseChartType(
                chartType
            );


        const tension =
            getChartTension(
                chartType
            );


        const fillArea =
            shouldFill(
                chartType
            );


        const isBar =
            baseType === 'bar';


        updateChartTypeIndicator(
            chartType
        );


        if (
            !labels.length ||
            !attendanceData.length
        ) {

            currentChart =
                new Chart(
                    ctx,
                    {

                        type: 'line',

                        data: {

                            labels: [
                                'No data'
                            ],

                            datasets: [

                                {

                                    label:
                                        'Attendance',

                                    data: [0],

                                    borderColor:
                                        '#adb5bd',

                                    backgroundColor:
                                        'rgba(173,181,189,.08)',

                                    fill: true

                                }

                            ]

                        },

                        options: {

                            responsive: true,

                            maintainAspectRatio: false,

                            plugins: {

                                legend: {
                                    display: false
                                }

                            }

                        }

                    }
                );

            return;

        }


        currentChart =
            new Chart(
                ctx,
                {

                    type: baseType,

                    data: {

                        labels: labels,

                        datasets: [

                            {

                                label:
                                    'Daily attendance',

                                data:
                                    attendanceData,

                                borderColor:
                                    lineColor,

                                backgroundColor:
                                    isBar
                                        ? lineColor
                                        : (
                                            fillArea
                                                ? `${lineColor}30`
                                                : `${lineColor}10`
                                        ),

                                pointBackgroundColor:
                                    lineColor,

                                pointBorderColor:
                                    '#ffffff',

                                pointBorderWidth:
                                    2,

                                pointRadius:
                                    isBar
                                        ? 0
                                        : 4,

                                pointHoverRadius:
                                    isBar
                                        ? 4
                                        : 7,

                                fill:
                                    fillArea,

                                tension:
                                    tension,

                                borderWidth:
                                    isBar
                                        ? 0
                                        : 2.8,

                                borderRadius:
                                    isBar
                                        ? 8
                                        : 0,

                                barPercentage:
                                    isBar
                                        ? 0.7
                                        : 0.65,

                                categoryPercentage:
                                    0.8

                            },


                            {

                                label:
                                    'Current term benchmark (avg)',

                                data:
                                    benchmarkData,

                                borderColor:
                                    '#3b82f6',

                                backgroundColor:
                                    'transparent',

                                borderWidth:
                                    2.2,

                                borderDash: [
                                    8,
                                    5
                                ],

                                pointRadius:
                                    3,

                                pointBackgroundColor:
                                    '#3b82f6',

                                pointBorderColor:
                                    '#fff',

                                pointBorderWidth:
                                    1.5,

                                fill:
                                    false,

                                tension:
                                    0.1,

                                type:
                                    'line'

                            }

                        ]

                    },


                    options: {

                        responsive: true,

                        maintainAspectRatio: false,

                        interaction: {

                            intersect:
                                false,

                            mode:
                                'index'

                        },


                        plugins: {

                            tooltip: {

                                backgroundColor:
                                    '#1e293b',

                                titleColor:
                                    '#f8fafc',

                                bodyColor:
                                    '#cbd5e1',

                                padding:
                                    10,

                                cornerRadius:
                                    12,

                                callbacks: {

                                    label:
                                        function (
                                            context
                                        ) {

                                            const label =
                                                context.dataset.label
                                                || '';

                                            const value =
                                                context.raw
                                                || 0;

                                            return (
                                                `${label}: ` +
                                                Number(
                                                    value
                                                ).toLocaleString() +
                                                ' students'
                                            );

                                        }

                                }

                            },


                            legend: {

                                display:
                                    false

                            }

                        },


                        scales: {

                            y: {

                                beginAtZero:
                                    true,

                                ticks: {

                                    color:
                                        '#6c7283',

                                    callback:
                                        value =>
                                            Number(
                                                value
                                            ).toLocaleString()
                                            +
                                            ' students'

                                },

                                grid: {

                                    color:
                                        '#eef2f6'

                                }

                            },


                            x: {

                                ticks: {

                                    color:
                                        '#6c7283',

                                    font: {
                                        size: 11
                                    },

                                    maxRotation:
                                        35

                                },

                                grid: {

                                    display:
                                        false

                                }

                            }

                        }

                    }

                }
            );

    }


    /*
    |--------------------------------------------------------------------------
    | FEE POSITION CHART
    |--------------------------------------------------------------------------
    */

    function initializeFeePositionChart() {

        const canvas =
            document.getElementById(
                'feePositionChart'
            );


        if (
            !canvas ||
            typeof Chart === 'undefined'
        ) {

            return;

        }


        new Chart(
            canvas,
            {

                type: 'doughnut',

                data: {

                    labels: [
                        'Fees Paid',
                        'Outstanding'
                    ],

                    datasets: [

                        {

                            data: [

                                {{ $dashboardTotalFeesPaid }},

                                {{ $dashboardOutstandingFees }}

                            ],

                            backgroundColor: [

                                '#198754',

                                '#dc3545'

                            ],

                            borderWidth:
                                0,

                            hoverOffset:
                                7

                        }

                    ]

                },


                options: {

                    responsive:
                        true,

                    maintainAspectRatio:
                        false,

                    cutout:
                        '68%',

                    plugins: {

                        legend: {

                            position:
                                'bottom',

                            labels: {

                                usePointStyle:
                                    true,

                                padding:
                                    16

                            }

                        },


                        tooltip: {

                            callbacks: {

                                label:
                                    function (
                                        context
                                    ) {

                                        return (
                                            context.label +
                                            ': ' +
                                            formatCurrency(
                                                context.raw
                                            )
                                        );

                                    }

                            }

                        }

                    }

                }

            }
        );

    }


    /*
    |--------------------------------------------------------------------------
    | CLASS FEE CHART
    |--------------------------------------------------------------------------
    */

    function initializeClassFeeChart() {

        const canvas =
            document.getElementById(
                'classFeeChart'
            );


        if (
            !canvas ||
            typeof Chart === 'undefined'
        ) {

            return;

        }


        const labels =
            @json(
                $classFeeLabels->values()
            );


        const expectedFees =
            @json(
                $classExpectedFees->values()
            );


        const paidFees =
            @json(
                $classPaidFees->values()
            );


        if (!labels.length) {

            return;

        }


        new Chart(
            canvas,
            {

                type: 'bar',

                data: {

                    labels: labels,

                    datasets: [

                        {

                            label:
                                'Expected Fees',

                            data:
                                expectedFees,

                            backgroundColor:
                                '#1a4b8c',

                            borderRadius:
                                7

                        },


                        {

                            label:
                                'Fees Paid',

                            data:
                                paidFees,

                            backgroundColor:
                                '#198754',

                            borderRadius:
                                7

                        }

                    ]

                },


                options: {

                    responsive:
                        true,

                    maintainAspectRatio:
                        false,

                    interaction: {

                        intersect:
                            false,

                        mode:
                            'index'

                    },


                    plugins: {

                        legend: {

                            position:
                                'bottom',

                            labels: {

                                usePointStyle:
                                    true,

                                padding:
                                    14

                            }

                        },


                        tooltip: {

                            callbacks: {

                                label:
                                    function (
                                        context
                                    ) {

                                        return (
                                            context.dataset.label +
                                            ': ' +
                                            formatCurrency(
                                                context.raw
                                            )
                                        );

                                    }

                            }

                        }

                    },


                    scales: {

                        y: {

                            beginAtZero:
                                true,

                            ticks: {

                                callback:
                                    value =>
                                        'GHS ' +
                                        Number(
                                            value
                                        ).toLocaleString()

                            },

                            grid: {

                                color:
                                    'rgba(0,0,0,.06)'

                            }

                        },


                        x: {

                            grid: {

                                display:
                                    false

                            }

                        }

                    }

                }

            }
        );

    }


    /*
    |--------------------------------------------------------------------------
    | CLASS ATTENDANCE TABLE
    |--------------------------------------------------------------------------
    */

    function getStatusIcon(status) {

        switch (status) {

            case 'Excellent':
                return 'fa-check-circle';

            case 'Good':
                return 'fa-thumbs-up';

            case 'Average':
                return 'fa-exclamation-triangle';

            case 'Poor':
                return 'fa-times-circle';

            case 'Needs Attention':
                return 'fa-exclamation-triangle';

            case 'Critical':
                return 'fa-times-circle';

            case 'Not Taken':
                return 'fa-minus-circle';

            default:
                return 'fa-info-circle';

        }

    }


    function renderClassAttendance(
        classes
    ) {

        const body =
            document.getElementById(
                'classAttendanceBody'
            );


        const count =
            document.getElementById(
                'totalClassesCount'
            );


        const dateDisplay =
            document.getElementById(
                'attendanceDateDisplay'
            );


        const lastUpdated =
            document.getElementById(
                'lastUpdatedTime'
            );


        if (!body) {

            return;

        }


        if (
            !Array.isArray(classes)
            ||
            classes.length === 0
        ) {

            body.innerHTML = `

                <tr>

                    <td
                        colspan="9"
                        class="text-center py-5 text-muted">

                        <i
                            class="
                                fas
                                fa-school
                                fs-2
                                d-block
                                mb-2
                            ">
                        </i>

                        No attendance data found
                        for the selected date.

                    </td>

                </tr>

            `;


            if (count) {

                count.textContent =
                    '0';

            }

            return;

        }


        body.innerHTML =
            classes.map(
                (row, index) => {

                    const rate =
                        Number(
                            row.rate || 0
                        );


                    const safeRate =
                        Math.min(
                            100,
                            Math.max(
                                0,
                                rate
                            )
                        );


                    const status =
                        row.status
                        ||
                        'No Records';


                    const allowedClasses = [
                        'success',
                        'info',
                        'warning',
                        'danger',
                        'secondary',
                    ];


                    const statusClass =
                        allowedClasses.includes(
                            row.status_class
                        )
                            ? row.status_class
                            : 'secondary';


                    return `

                        <tr>

                            <td>
                                ${index + 1}
                            </td>


                            <td class="fw-semibold">

                                ${escapeHtml(
                                    row.class_name
                                    || 'N/A'
                                )}

                            </td>


                            <td class="text-center">

                                <span
                                    class="badge bg-light text-dark">

                                    ${formatNumber(
                                        row.total_students
                                    )}

                                </span>

                            </td>


                            <td class="text-center">

                                <span
                                    class="badge bg-success-subtle text-success">

                                    ${formatNumber(
                                        row.present
                                    )}

                                </span>

                            </td>


                            <td class="text-center">

                                <span
                                    class="badge bg-danger-subtle text-danger">

                                    ${formatNumber(
                                        row.absent
                                    )}

                                </span>

                            </td>


                            <td class="text-center">

                                <span
                                    class="badge bg-warning-subtle text-warning">

                                    ${formatNumber(
                                        row.late
                                    )}

                                </span>

                            </td>


                            <td class="text-center">

                                <span
                                    class="badge bg-info-subtle text-info">

                                    ${formatNumber(
                                        row.excused
                                    )}

                                </span>

                            </td>


                            <td
                                class="text-center"
                                style="min-width:170px;">

                                <div
                                    class="
                                        d-flex
                                        align-items-center
                                        justify-content-center
                                        gap-2
                                    ">


                                    <div
                                        class="progress flex-grow-1"
                                        style="
                                            height:8px;
                                            max-width:90px;
                                        ">

                                        <div
                                            class="
                                                progress-bar
                                                bg-${statusClass}
                                            "
                                            role="progressbar"
                                            style="
                                                width:${safeRate}%;
                                            "
                                            aria-valuenow="${safeRate}"
                                            aria-valuemin="0"
                                            aria-valuemax="100">
                                        </div>

                                    </div>


                                    <span class="fw-bold">

                                        ${safeRate.toFixed(1)}%

                                    </span>

                                </div>

                            </td>


                            <td class="text-center">

                                <span
                                    class="
                                        badge
                                        bg-${statusClass}
                                    ">

                                    <i
                                        class="
                                            fas
                                            ${getStatusIcon(status)}
                                            me-1
                                        ">
                                    </i>

                                    ${escapeHtml(status)}

                                </span>

                            </td>

                        </tr>

                    `;

                }
            ).join('');


        if (count) {

            count.textContent =
                Number(
                    classes.length
                ).toLocaleString();

        }


        if (lastUpdated) {

            lastUpdated.textContent =
                new Date()
                    .toLocaleTimeString();

        }

    }


    /*
    |--------------------------------------------------------------------------
    | LOAD CLASS ATTENDANCE
    |--------------------------------------------------------------------------
    */

    async function loadClassAttendance(
        date = null
    ) {

        const input =
            document.getElementById(
                'attendanceDateFilter'
            );


        const button =
            document.getElementById(
                'loadClassAttendanceBtn'
            );


        const body =
            document.getElementById(
                'classAttendanceBody'
            );


        const dateDisplay =
            document.getElementById(
                'attendanceDateDisplay'
            );


        const lastUpdated =
            document.getElementById(
                'lastUpdatedTime'
            );


        const alertBox =
            document.getElementById(
                'attendanceTableAlert'
            );


        if (!date && input) {

            date =
                input.value;

        }


        if (
            !date ||
            !body
        ) {

            return;

        }


        if (button) {

            button.disabled =
                true;

            button.innerHTML =
                '<i class="fas fa-spinner fa-spin me-1"></i> Loading';

        }


        body.innerHTML = `

            <tr>

                <td
                    colspan="9"
                    class="text-center py-5 text-muted">

                    <i
                        class="
                            fas
                            fa-spinner
                            fa-spin
                            me-2
                        ">
                    </i>

                    Loading attendance...

                </td>

            </tr>

        `;


        try {

            const response =
                await fetch(
                    `{{ route(
                        'dashboard.class-attendance'
                    ) }}?date=${encodeURIComponent(date)}`,
                    {
                        headers: {

                            'Accept':
                                'application/json',

                            'X-Requested-With':
                                'XMLHttpRequest'

                        }
                    }
                );


            if (!response.ok) {

                throw new Error(
                    `HTTP ${response.status}`
                );

            }


            const data =
                await response.json();


            if (
                !data ||
                data.status === 'error'
            ) {

                throw new Error(
                    data.message
                    ||
                    'Attendance data could not be loaded.'
                );

            }


            const classes =
                Array.isArray(data)
                    ? data
                    : (
                        data.data
                        ||
                        data.classes
                        ||
                        []
                    );


            renderClassAttendance(
                classes
            );


            if (dateDisplay) {

                const parsedDate =
                    new Date(
                        date + 'T00:00:00'
                    );


                dateDisplay.textContent =
                    parsedDate.toLocaleDateString(
                        undefined,
                        {
                            day:
                                '2-digit',

                            month:
                                'short',

                            year:
                                'numeric'
                        }
                    );

            }


            if (alertBox) {

                alertBox.className =
                    'alert alert-success';


                alertBox.innerHTML =
                    '<i class="fas fa-check-circle me-1"></i>' +
                    'Attendance data refreshed successfully.';


                alertBox.classList.remove(
                    'd-none'
                );


                setTimeout(
                    function () {

                        alertBox.classList.add(
                            'd-none'
                        );

                    },
                    2500
                );

            }

        } catch (error) {

            console.error(
                'Class attendance error:',
                error
            );


            body.innerHTML = `

                <tr>

                    <td
                        colspan="9"
                        class="text-center py-5 text-danger">

                        <i
                            class="
                                fas
                                fa-exclamation-circle
                                fs-2
                                d-block
                                mb-2
                            ">
                        </i>

                        Failed to load attendance records.

                        <div class="small text-muted mt-1">

                            ${escapeHtml(
                                error.message
                                || 'Please try again.'
                            )}

                        </div>

                    </td>

                </tr>

            `;


            if (alertBox) {

                alertBox.className =
                    'alert alert-danger';


                alertBox.innerHTML =
                    '<i class="fas fa-exclamation-circle me-1"></i>' +
                    'Failed to load attendance data.';


                alertBox.classList.remove(
                    'd-none'
                );

            }

        } finally {

            if (button) {

                button.disabled =
                    false;

                button.innerHTML =
                    '<i class="fas fa-sync me-1"></i> Load';

            }

        }

    }


    /*
    |--------------------------------------------------------------------------
    | EXCEL EXPORT
    |--------------------------------------------------------------------------
    */

    function exportClassAttendance() {

        const table =
            document.getElementById(
                'classAttendanceTable'
            );


        if (
            !table ||
            typeof XLSX === 'undefined'
        ) {

            if (
                typeof Swal !== 'undefined'
            ) {

                Swal.fire({

                    icon:
                        'warning',

                    title:
                        'Export Unavailable',

                    text:
                        'The Excel export library is not available.'

                });

            }

            return;

        }


        try {

            const workbook =
                XLSX.utils.book_new();


            const worksheet =
                XLSX.utils.table_to_sheet(
                    table
                );


            worksheet['!cols'] = [

                { wch: 5 },

                { wch: 25 },

                { wch: 15 },

                { wch: 15 },

                { wch: 15 },

                { wch: 15 },

                { wch: 15 },

                { wch: 20 },

                { wch: 18 }

            ];


            XLSX.utils.book_append_sheet(
                workbook,
                worksheet,
                'Class Attendance'
            );


            const selectedDate =
                document.getElementById(
                    'attendanceDateFilter'
                )?.value
                ||
                new Date()
                    .toISOString()
                    .slice(
                        0,
                        10
                    );


            XLSX.writeFile(
                workbook,
                `Class_Attendance_${selectedDate}.xlsx`
            );


            if (
                typeof Swal !== 'undefined'
            ) {

                Swal.fire({

                    icon:
                        'success',

                    title:
                        'Exported',

                    timer:
                        1400,

                    showConfirmButton:
                        false

                });

            }

        } catch (error) {

            console.error(
                'Export error:',
                error
            );


            if (
                typeof Swal !== 'undefined'
            ) {

                Swal.fire({

                    icon:
                        'error',

                    title:
                        'Export Failed',

                    text:
                        error.message

                });

            }

        }

    }


    /*
    |--------------------------------------------------------------------------
    | MAKE EXPORT AVAILABLE GLOBALLY
    |--------------------------------------------------------------------------
    */

    window.exportClassAttendance =
        exportClassAttendance;


    /*
    |--------------------------------------------------------------------------
    | INITIALIZATION
    |--------------------------------------------------------------------------
    */

    document.addEventListener(
        'DOMContentLoaded',
        function () {


            /*
            |--------------------------------------------------------------------------
            | ATTENDANCE CHART
            |--------------------------------------------------------------------------
            */

            updateLegendDot(
                currentColor
            );


            loadAttendanceData(
                30
            );


            /*
            |--------------------------------------------------------------------------
            | FEE CHARTS
            |--------------------------------------------------------------------------
            */

            initializeFeePositionChart();

            initializeClassFeeChart();


            /*
            |--------------------------------------------------------------------------
            | CHART CONTROLS
            |--------------------------------------------------------------------------
            */

            const chartTypeSelect =
                document.getElementById(
                    'chartTypeSelect'
                );


            const colorPicker =
                document.getElementById(
                    'lineColorPicker'
                );


            const periodSelect =
                document.getElementById(
                    'periodSelect'
                );


            if (chartTypeSelect) {

                chartTypeSelect.addEventListener(
                    'change',
                    function (event) {

                        currentChartType =
                            event.target.value;


                        renderAttendanceChart(
                            currentChartType,
                            currentColor
                        );

                    }
                );

            }


            if (colorPicker) {

                colorPicker.addEventListener(
                    'input',
                    function (event) {

                        currentColor =
                            event.target.value;


                        updateLegendDot(
                            currentColor
                        );


                        renderAttendanceChart(
                            currentChartType,
                            currentColor
                        );

                    }
                );

            }


            if (periodSelect) {

                periodSelect.addEventListener(
                    'change',
                    function (event) {

                        loadAttendanceData(
                            event.target.value
                        );

                    }
                );

            }


            /*
            |--------------------------------------------------------------------------
            | CLASS ATTENDANCE
            |--------------------------------------------------------------------------
            */

            const loadButton =
                document.getElementById(
                    'loadClassAttendanceBtn'
                );


            const dateInput =
                document.getElementById(
                    'attendanceDateFilter'
                );


            if (loadButton) {

                loadButton.addEventListener(
                    'click',
                    function () {

                        loadClassAttendance(
                            dateInput?.value
                        );

                    }
                );

            }


            if (dateInput) {

                dateInput.addEventListener(
                    'change',
                    function () {

                        loadClassAttendance(
                            this.value
                        );

                    }
                );

            }


        }
    );

</script>

@endpush

@endsection