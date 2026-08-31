@extends('layouts.master')

@section('title', 'Dashboard - Kabore USMS')

@section('content')

@php
    /*
    |--------------------------------------------------------------------------
    | DASHBOARD DISPLAY DATA
    |--------------------------------------------------------------------------
    */

    $dashboardToday = \Carbon\Carbon::today();

    $attendanceDate = !empty($attendanceDate)
        ? \Carbon\Carbon::parse($attendanceDate)
        : $dashboardToday->copy();

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

    $dashboardAttendanceRows =
        (int) ($dashboardAttendanceRows ?? 0);

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


<div class="container-fluid py-4 px-4">

    {{-- =========================================================
         TOP METRICS
    ========================================================== --}}
    <div class="row g-4 mb-4">

        <div class="col-md-6 col-xl-3">

            <div class="card stats-card shadow-sm h-100 border-0">

                <div class="card-body p-4">

                    <div class="d-flex justify-content-between align-items-start mb-3">

                        <div>

                            <small class="text-uppercase fw-bold text-muted">
                                <i class="fas fa-chalkboard-user me-1 text-dark"></i>
                                Total Students
                            </small>

                            <h1 class="fw-bold display-5 mt-2 mb-1">
                                {{ number_format($totalStudents) }}
                            </h1>

                        </div>

                        <div class="bg-primary bg-opacity-10 rounded-circle p-3">
                            <i class="fas fa-user-graduate text-dark fs-3"></i>
                        </div>

                    </div>

                    <div class="d-flex gap-2 flex-wrap mt-3">

                        <span class="badge bg-light text-dark px-3 py-2">
                            <i class="fas fa-male me-1"></i>
                            Male: {{ number_format($maleCount) }}
                        </span>

                        <span class="badge bg-light text-dark px-3 py-2">
                            <i class="fas fa-female me-1"></i>
                            Female: {{ number_format($femaleCount) }}
                        </span>

                    </div>

                    <div class="mt-3">

                        <small class="text-primary fw-semibold">
                            <i class="fas fa-arrow-up me-1"></i>
                            +{{ number_format($studentsThisYear) }}
                            new this Academic Year
                        </small>

                    </div>

                </div>

            </div>

        </div>


        <div class="col-md-6 col-xl-3">

            <div class="card stats-card border-0 shadow-sm h-100">

                <div class="card-body p-4">

                    <div class="d-flex justify-content-between align-items-start">

                        <div>

                            <div class="text-uppercase fw-bold small text-muted">
                                <i class="fas fa-chalkboard-user me-1 text-primary"></i>
                                Total Staff
                            </div>

                            <h1 class="fw-bold display-4 mt-3 mb-1">
                                {{ number_format($totalStaff) }}
                            </h1>

                            <small class="fw-semibold text-primary">
                                <i class="fas fa-arrow-up me-1"></i>
                                +{{ number_format($staffThisYear) }}
                                new this Academic Year
                            </small>

                        </div>

                        <div
                            class="rounded-circle bg-primary bg-opacity-10 d-flex align-items-center justify-content-center"
                            style="width:70px;height:70px;">

                            <i class="fas fa-user-tie text-primary fs-2"></i>

                        </div>

                    </div>

                </div>

            </div>

        </div>


        <div class="col-md-6 col-xl-3">

            <div class="card stats-card border-0 shadow-sm h-100">

                <div class="card-body p-4">

                    <div class="d-flex justify-content-between align-items-start">

                        <div>

                            <div class="text-uppercase fw-bold small text-muted">
                                <i class="fas fa-chalkboard me-1 text-info"></i>
                                Active Classes
                            </div>

                            <h1 class="fw-bold display-4 mt-3 mb-1">
                                {{ number_format($activeClasses) }}
                            </h1>

                            <small class="fw-semibold text-dark">
                                <i class="fas fa-calendar-week me-1"></i>
                                Current Academic Term
                            </small>

                        </div>

                        <div
                            class="rounded-circle bg-info bg-opacity-10 d-flex align-items-center justify-content-center"
                            style="width:70px;height:70px;">

                            <i class="fas fa-school text-info fs-2"></i>

                        </div>

                    </div>

                </div>

            </div>

        </div>


        <div class="col-md-6 col-xl-3">

            <div class="card stats-card border-0 shadow-sm h-100">

                <div class="card-body p-4">

                    <div class="d-flex justify-content-between align-items-start">

                        <div>

                            <div class="text-uppercase fw-bold small text-muted">
                                <i class="fas fa-percent me-1 text-danger"></i>
                                Attendance Rate
                            </div>

                            <h1 class="fw-bold display-4 mt-3 mb-1">
                                {{ number_format($studentAttendanceRate ?? 0, 1) }}%
                            </h1>

                            <small class="fw-semibold text-dark">
                                <i class="fas fa-chart-line me-1"></i>
                                Student Attendance Performance
                            </small>

                        </div>

                        <div
                            class="rounded-circle bg-danger bg-opacity-10 d-flex align-items-center justify-content-center"
                            style="width:70px;height:70px;">

                            <i class="fas fa-calendar-check text-danger fs-2"></i>

                        </div>

                    </div>

                </div>

            </div>

        </div>

    </div>


    {{-- =========================================================
         DAILY ATTENDANCE CHART
    ========================================================== --}}
    <div class="row mb-4">

        <div class="col-12">

            <div class="card dashboard-card border-0 shadow-sm overflow-hidden">

                <div class="card-body p-4">

                    <div class="d-flex flex-wrap justify-content-between align-items-center mb-2">

                        <div>

                            <h2 class="fw-light mb-1 fs-3">
                                <i class="fas fa-chart-line text-primary me-2"></i>
                                Daily Attendance Analysis
                            </h2>

                            <small class="text-secondary">
                                Students present and late per day from database
                            </small>

                        </div>


                        <div class="d-flex gap-2 mt-2 mt-lg-0 flex-wrap no-print">

                            <select
                                class="form-select"
                                id="periodSelect"
                                style="width:auto;">

                                <option value="90">
                                    Last 90 Days
                                </option>

                                <option value="30" selected>
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


                    <div class="d-flex justify-content-between align-items-center flex-wrap">

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

                            <i class="fas fa-spinner fa-spin me-1 text-info"></i>
                            Loading attendance insight...

                        </div>

                    </div>


                    <div
                        class="chart-container mt-3"
                        style="height:500px;">

                        <canvas id="attendanceChartCanvas"></canvas>

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

    </div>


    {{-- =========================================================
         BOTTOM STAT CARDS
    ========================================================== --}}
    <div class="row g-4">

        <div class="col-md-6 col-xl-3">

            <div class="card stats-card shadow-sm h-100">

                <div class="card-body p-4">

                    <div class="analytics-header">
                        <i class="fas fa-coins me-1 text-success"></i>
                        TOTAL FEES PAID
                    </div>

                    <h1 class="fw-bold display-5 mt-2">
                        GHS {{ number_format($dashboardTotalFeesPaid, 2) }}
                    </h1>

                    <small class="{{ $dashboardMonthlyChange >= 0 ? 'text-success' : 'text-danger' }}">

                        <i class="fas {{ $dashboardMonthlyChange >= 0 ? 'fa-arrow-up' : 'fa-arrow-down' }} me-1"></i>

                        {{ $dashboardMonthlyChange >= 0 ? '+' : '' }}
                        {{ number_format($dashboardMonthlyChange, 1) }}%
                        vs previous month

                    </small>

                </div>

            </div>

        </div>


        <div class="col-md-6 col-xl-3">

            <div class="card stats-card shadow-sm h-100">

                <div class="card-body p-4">

                    <div class="analytics-header">
                        <i class="fas fa-exclamation-triangle me-1 text-warning"></i>
                        OUTSTANDING FEES
                    </div>

                    <h1 class="fw-bold display-5 mt-2">
                        GHS {{ number_format($dashboardOutstandingFees, 2) }}
                    </h1>

                    <small class="text-dark">

                        <i class="fas fa-clock me-1"></i>

                        Expected fees less completed payments

                    </small>

                    <div class="mt-3">

                        <a
                            href="{{ route('fee.payment.reports.outstanding') }}"
                            class="btn btn-sm btn-outline-danger no-print">

                            <i class="fas fa-arrow-right me-1"></i>
                            View Outstanding Fees

                        </a>

                    </div>

                </div>

            </div>

        </div>


        <div class="col-md-6 col-xl-3">

            <div class="card stats-card shadow-sm h-100">

                <div class="card-body p-4">

                    <div class="analytics-header">
                        <i class="fas fa-user-friends me-1 text-info"></i>
                        STAFF ATTENDANCE
                    </div>

                    <h1 class="fw-bold display-4 mt-2">
                        {{ number_format($staffAttendanceRate ?? 0, 1) }}%
                    </h1>

                    <small class="text-dark">
                        <i class="fas fa-arrow-up me-1"></i>
                        Staff Attendance Performance
                    </small>

                </div>

            </div>

        </div>


        <div class="col-md-6 col-xl-3">

            <div class="card stats-card shadow-sm h-100">

                <div class="card-body p-4">

                    <div class="analytics-header">
                        <i class="fas fa-chalkboard me-1 text-secondary"></i>
                        STUDENT/TEACHER RATIO
                    </div>

                    <h1 class="fw-bold display-4 mt-2">

                        {{ $totalStaff > 0
                            ? round($totalStudents / $totalStaff)
                            : 0 }}:1

                    </h1>

                    <small class="text-primary fs-6">

                        <i class="fas fa-graduation-cap me-1"></i>

                        {{ $totalStaff }} staff,
                        {{ $totalStudents }} students

                    </small>

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

                            <i class="fas fa-calendar-check text-primary me-2"></i>

                            Daily Attendance by Class

                            <span
                                class="badge bg-light text-dark ms-2"
                                id="attendanceDateDisplay">

                                {{ $attendanceDate->format('d M Y') }}

                            </span>

                        </h5>

                        <small class="text-muted">
                            Select a date and load live class attendance.
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

                                <tr>

                                    <td
                                        colspan="9"
                                        class="text-center py-5 text-muted">

                                        <i class="fas fa-spinner fa-spin me-2"></i>
                                        Loading attendance...

                                    </td>

                                </tr>

                            </tbody>

                        </table>

                    </div>


                    <div
                        class="mt-3 text-muted small"
                        id="attendanceSummaryFooter">

                        <i class="fas fa-info-circle me-1"></i>

                        Showing
                        <span id="totalClassesCount">
                            {{ $dashboardAttendanceRows }}
                        </span>

                        active classes |

                        Last updated:
                        <span id="lastUpdatedTime">
                            {{ now()->format('H:i:s') }}
                        </span>

                    </div>

                </div>

            </div>

        </div>

    </div>


    {{-- =========================================================
         FEE POSITION STRIP
    ========================================================== --}}
    <div class="row mt-4 g-3">

        <div class="col-md-4">

            <div class="card border-0 shadow-sm h-100">

                <div class="card-body">

                    <small class="text-muted text-uppercase fw-bold">
                        Expected Fees
                    </small>

                    <h4 class="fw-bold mt-2">
                        GHS {{ number_format($dashboardTotalFees, 2) }}
                    </h4>

                    <small class="text-muted">
                        Class Bill Sheet amount × current students
                    </small>

                </div>

            </div>

        </div>


        <div class="col-md-4">

            <div class="card border-0 shadow-sm h-100">

                <div class="card-body">

                    <small class="text-muted text-uppercase fw-bold">
                        Fees Paid
                    </small>

                    <h4 class="fw-bold mt-2 text-success">
                        GHS {{ number_format($dashboardTotalFeesPaid, 2) }}
                    </h4>

                    <div class="progress mt-2" style="height:7px;">

                        <div
                            class="progress-bar bg-success"
                            style="width:{{ min(100, max(0, $dashboardCollectionRate)) }}%;">
                        </div>

                    </div>

                    <small class="text-muted">
                        {{ number_format($dashboardCollectionRate, 1) }}% collected
                    </small>

                </div>

            </div>

        </div>


        <div class="col-md-4">

            <div class="card border-0 shadow-sm h-100">

                <div class="card-body">

                    <small class="text-muted text-uppercase fw-bold">
                        Outstanding Fees
                    </small>

                    <h4 class="fw-bold mt-2 text-danger">
                        GHS {{ number_format($dashboardOutstandingFees, 2) }}
                    </h4>

                    <a
                        href="{{ route('fee.payment.reports.school-overview') }}"
                        class="btn btn-sm btn-outline-primary mt-2 no-print">

                        <i class="fas fa-chart-pie me-1"></i>
                        School Fee Overview

                    </a>

                </div>

            </div>

        </div>

    </div>

</div>


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

    .color-picker-group {
        background: #f8fafc;
        padding: 4px 14px;
        border-radius: 36px;
        display: flex;
        align-items: center;
        gap: 12px;
        border: 1px solid #eef2ff;
    }

    .color-picker-group label {
        font-size: 0.75rem;
        font-weight: 600;
        color: #1e293b;
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

    .chart-container {
        position: relative;
        width: 100%;
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
            dot.style.backgroundColor = color;
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

        if (!data.labels.length) {

            insight.innerHTML = `
                <i class="fas fa-exclamation-circle me-1 text-danger"></i>
                No attendance data found for this period
            `;

            return;
        }

        insight.innerHTML = `
            <i class="fas fa-chart-simple me-1 text-info"></i>
            Peak attendance:
            ${Number(data.peak || 0).toLocaleString()}
            students (${escapeHtml(data.peak_day)})
            · Average:
            ${Number(data.average || 0).toLocaleString()}
            students/day
        `;

    }


    async function loadAttendanceData(days = 30) {

        const insight =
            document.getElementById(
                'attendanceInsight'
            );

        if (insight) {

            insight.innerHTML = `
                <i class="fas fa-spinner fa-spin me-1 text-info"></i>
                Loading attendance data...
            `;

        }

        try {

            const response =
                await fetch(
                    `{{ route('dashboard.attendance-data') }}?days=${encodeURIComponent(days)}`,
                    {
                        headers: {
                            'Accept':
                                'application/json'
                        },
                        credentials:
                            'same-origin'
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
                data.status !== 'success'
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
                    <i class="fas fa-exclamation-circle me-1 text-danger"></i>
                    Failed to load attendance data
                `;

            }

        }

    }


    function renderAttendanceChart(
        chartType = currentChartType,
        lineColor = currentColor
    ) {

        const canvas =
            document.getElementById(
                'attendanceChartCanvas'
            );

        if (!canvas) {
            return;
        }

        if (typeof Chart === 'undefined') {
            return;
        }

        const ctx =
            canvas.getContext('2d');

        if (currentChart) {
            currentChart.destroy();
        }

        const labels =
            chartDataFromServer.labels;

        const attendanceData =
            chartDataFromServer.attendance;

        const benchmarkData =
            chartDataFromServer.benchmark;

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

                            datasets: [{
                                label:
                                    'Daily Attendance',

                                data: [0],

                                borderColor:
                                    lineColor,

                                backgroundColor:
                                    `${lineColor}20`
                            }]

                        },

                        options: {

                            responsive: true,

                            maintainAspectRatio:
                                false,

                            plugins: {

                                legend: {
                                    display: false
                                }

                            },

                            scales: {

                                y: {
                                    beginAtZero:
                                        true
                                }

                            }

                        }

                    }
                );

            return;
        }

        const isBar =
            chartType === 'bar';

        const fillArea =
            shouldFill(chartType);

        const tension =
            getChartTension(chartType);

        const baseType =
            getBaseChartType(chartType);

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
                                    'Daily Attendance (students attended)',

                                data:
                                    attendanceData,

                                borderColor:
                                    lineColor,

                                backgroundColor:
                                    fillArea
                                        ? `${lineColor}30`
                                        : (
                                            isBar
                                                ? lineColor
                                                : `${lineColor}10`
                                        ),

                                pointBackgroundColor:
                                    lineColor,

                                pointBorderColor:
                                    '#ffffff',

                                pointBorderWidth: 2,

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
                                    'Average benchmark',

                                data:
                                    benchmarkData,

                                borderColor:
                                    '#3b82f6',

                                backgroundColor:
                                    'transparent',

                                borderWidth: 2.2,

                                borderDash: [
                                    8,
                                    5
                                ],

                                pointRadius: 3,

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

                        maintainAspectRatio:
                            false,

                        interaction: {

                            intersect: false,

                            mode: 'index'

                        },

                        plugins: {

                            tooltip: {

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
                                                Number(value)
                                                    .toLocaleString() +
                                                ' students'
                                            );

                                        }

                                }

                            },

                            legend: {

                                display: false

                            }

                        },

                        scales: {

                            y: {

                                beginAtZero: true,

                                ticks: {

                                    callback:
                                        value =>
                                            Number(value)
                                                .toLocaleString()
                                            + ' students'

                                }

                            },

                            x: {

                                ticks: {

                                    maxRotation: 35

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

        if (!body) {
            return;
        }

        if (
            !Array.isArray(classes)
            || classes.length === 0
        ) {

            body.innerHTML = `
                <tr>
                    <td colspan="9"
                        class="text-center py-5 text-muted">

                        <i class="fas fa-school fs-2 d-block mb-2"></i>

                        No attendance data found for the selected date.

                    </td>
                </tr>
            `;

            if (count) {
                count.textContent = '0';
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
                        || 'No Records';

                    const statusClass =
                        [
                            'success',
                            'info',
                            'warning',
                            'danger',
                            'secondary'
                        ].includes(
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
                                ${escapeHtml(row.class_name)}
                            </td>

                            <td class="text-center">
                                <span class="badge bg-light text-dark">
                                    ${Number(row.total_students || 0).toLocaleString()}
                                </span>
                            </td>

                            <td class="text-center">
                                <span class="badge bg-success-subtle text-success">
                                    ${Number(row.present || 0).toLocaleString()}
                                </span>
                            </td>

                            <td class="text-center">
                                <span class="badge bg-danger-subtle text-danger">
                                    ${Number(row.absent || 0).toLocaleString()}
                                </span>
                            </td>

                            <td class="text-center">
                                <span class="badge bg-warning-subtle text-warning">
                                    ${Number(row.late || 0).toLocaleString()}
                                </span>
                            </td>

                            <td class="text-center">
                                <span class="badge bg-info-subtle text-info">
                                    ${Number(row.excused || 0).toLocaleString()}
                                </span>
                            </td>

                            <td class="text-center"
                                style="min-width:170px;">

                                <div class="d-flex align-items-center justify-content-center gap-2">

                                    <div
                                        class="progress flex-grow-1"
                                        style="height:8px;max-width:90px;">

                                        <div
                                            class="progress-bar bg-${statusClass}"
                                            role="progressbar"
                                            style="width:${safeRate}%;"
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

                                <span class="badge bg-${statusClass}">

                                    <i class="fas ${getStatusIcon(status)} me-1"></i>

                                    ${escapeHtml(status)}

                                </span>

                            </td>

                        </tr>
                    `;

                }
            ).join('');

        if (count) {
            count.textContent =
                Number(classes.length)
                    .toLocaleString();
        }

    }


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

        if (!date && input) {
            date = input.value;
        }

        if (!date || !body) {
            return;
        }

        if (button) {

            button.disabled = true;

            button.innerHTML =
                '<i class="fas fa-spinner fa-spin me-1"></i> Loading';

        }

        body.innerHTML = `
            <tr>
                <td colspan="9"
                    class="text-center py-5 text-muted">

                    <i class="fas fa-spinner fa-spin me-2"></i>
                    Loading attendance...

                </td>
            </tr>
        `;

        try {

            const response =
                await fetch(
                    `{{ route('dashboard.class-attendance') }}?date=${encodeURIComponent(date)}`,
                    {
                        headers: {
                            'Accept':
                                'application/json',
                            'X-Requested-With':
                                'XMLHttpRequest'
                        },
                        credentials:
                            'same-origin'
                    }
                );

            if (!response.ok) {
                throw new Error(
                    `HTTP ${response.status}`
                );
            }

            const data =
                await response.json();

            if (!data.success) {
                throw new Error(
                    data.message
                    || 'Unable to load attendance.'
                );
            }

            renderClassAttendance(
                data.classes || []
            );

            if (dateDisplay) {

                const selected =
                    new Date(
                        `${date}T00:00:00`
                    );

                dateDisplay.textContent =
                    selected.toLocaleDateString(
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

            if (lastUpdated) {
                lastUpdated.textContent =
                    new Date()
                        .toLocaleTimeString();
            }

        } catch (error) {

            console.error(
                'Class attendance error:',
                error
            );

            body.innerHTML = `
                <tr>
                    <td colspan="9"
                        class="text-center py-5 text-danger">

                        <i class="fas fa-exclamation-circle fs-2 d-block mb-2"></i>

                        Failed to load attendance records.

                        <div class="small text-muted mt-1">
                            Please try again.
                        </div>

                    </td>
                </tr>
            `;

        } finally {

            if (button) {

                button.disabled = false;

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

            if (typeof Swal !== 'undefined') {

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
                { wch: 12 },
                { wch: 12 },
                { wch: 12 },
                { wch: 12 },
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
                || new Date()
                    .toISOString()
                    .slice(
                        0,
                        10
                    );

            XLSX.writeFile(
                workbook,
                `Class_Attendance_${selectedDate}.xlsx`
            );

            if (typeof Swal !== 'undefined') {

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

            if (typeof Swal !== 'undefined') {

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
    | INITIALIZATION
    |--------------------------------------------------------------------------
    */

    document.addEventListener(
        'DOMContentLoaded',
        function () {

            updateLegendDot(
                currentColor
            );

            loadAttendanceData(
                30
            );

            loadClassAttendance(
                document.getElementById(
                    'attendanceDateFilter'
                )?.value
            );


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

            const loadButton =
                document.getElementById(
                    'loadClassAttendanceBtn'
                );

            const dateInput =
                document.getElementById(
                    'attendanceDateFilter'
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
