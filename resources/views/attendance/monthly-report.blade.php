@extends('layouts.master')

@section('title', 'Monthly Student Attendance Report')

@section('content')

<div class="container-fluid attendance-report-page">

    {{-- ============================================================
        HEADER
    ============================================================= --}}
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">

        <div>
            <h6 class="mb-1 fw-bold">
                <i class="fas fa-calendar-alt me-2 text-primary"></i>
                Monthly Attendance Report

                @if(!empty($summary['class_name']))
                    <span class="text-muted">
                        — {{ $summary['class_name'] }}
                    </span>
                @endif

                @if(!empty($summary['month_name']))
                    <span class="text-muted">
                        ({{ $summary['month_name'] }})
                    </span>
                @endif
            </h6>

            {{-- Current Academic Period --}}
            @if(!empty($currentAcademicYear) && !empty($currentTerm))

                <div class="small text-muted">

                    <i class="fas fa-calendar-check me-1"></i>

                    Academic Period:

                    <strong>
                        {{ $currentAcademicYear->name }}
                    </strong>

                    <span class="mx-1">•</span>

                    <strong>
                        {{ $currentTerm->name }}
                    </strong>

                </div>

            @endif

        </div>


        <div class="d-flex gap-2">

            {{-- Export --}}
            @if($selectedClass)

                <a
                    href="{{ route('attendance.export-monthly', [
                        'class_id' => $selectedClassId,
                        'month_year' => sprintf('%04d-%02d', $year, $month)
                    ]) }}"
                    class="btn btn-sm btn-success"
                >
                    <i class="fas fa-file-excel me-1"></i>
                    Export
                </a>

            @endif


            {{-- Print --}}
            <button
                type="button"
                onclick="window.print()"
                class="btn btn-sm btn-primary"
            >
                <i class="fas fa-print me-1"></i>
                Print
            </button>

        </div>

    </div>


    {{-- ============================================================
        CURRENT PERIOD INFORMATION
    ============================================================= --}}
    @if(!empty($currentAcademicYear) && !empty($currentTerm))

        <div class="alert alert-light border shadow-sm py-2 px-3 mb-3">

            <div class="d-flex align-items-center flex-wrap gap-2">

                <span class="text-muted small">
                    <i class="fas fa-info-circle me-1 text-primary"></i>
                    Attendance is being reported for:
                </span>

                <span class="badge bg-primary">
                    {{ $currentAcademicYear->name }}
                </span>

                <span class="badge bg-success">
                    {{ $currentTerm->name }}
                </span>

                @if($currentTerm->start_date && $currentTerm->end_date)

                    <span class="small text-muted">

                        {{ \Carbon\Carbon::parse($currentTerm->start_date)->format('d M Y') }}

                        —

                        {{ \Carbon\Carbon::parse($currentTerm->end_date)->format('d M Y') }}

                    </span>

                @endif

            </div>

        </div>

    @endif


    {{-- ============================================================
        FILTERS
    ============================================================= --}}
    <div class="card shadow-sm mb-3">

        <div class="card-body py-2">

            <form
                action="{{ route('attendance.monthly-report') }}"
                method="GET"
                class="row g-2 align-items-end"
            >

                {{-- Class --}}
                <div class="col-md-4">

                    <label class="form-label small fw-semibold mb-1">
                        Class
                    </label>

                    <select
                        name="class_id"
                        class="form-select form-select-sm"
                        required
                    >

                        <option value="">
                            Select Class
                        </option>

                        @foreach($classes as $class)

                            <option
                                value="{{ $class->id }}"
                                {{ (int) $selectedClassId === (int) $class->id ? 'selected' : '' }}
                            >
                                {{ $class->name }}
                            </option>

                        @endforeach

                    </select>

                </div>


                {{-- Month --}}
                <div class="col-md-4">

                    <label class="form-label small fw-semibold mb-1">
                        Month
                    </label>

                    <input
                        type="month"
                        name="month_year"
                        class="form-control form-control-sm"
                        value="{{ sprintf('%04d-%02d', $year, $month) }}"
                        required
                    >

                </div>


                {{-- Search --}}
                <div class="col-md-2">

                    <label class="form-label small fw-semibold mb-1 d-block">
                        &nbsp;
                    </label>

                    <button
                        type="submit"
                        class="btn btn-sm btn-primary w-100"
                    >
                        <i class="fas fa-search me-1"></i>
                        View
                    </button>

                </div>


                {{-- Reset --}}
                <div class="col-md-2">

                    <label class="form-label small fw-semibold mb-1 d-block">
                        &nbsp;
                    </label>

                    <a
                        href="{{ route('attendance.monthly-report') }}"
                        class="btn btn-sm btn-outline-secondary w-100"
                    >
                        <i class="fas fa-sync-alt me-1"></i>
                        Reset
                    </a>

                </div>

            </form>

        </div>

    </div>


    {{-- ============================================================
        SUMMARY STATISTICS
    ============================================================= --}}
    @if(!empty($summary) && isset($summary['total_students']))

        <div class="row g-2 mb-3">

            {{-- Students --}}
            <div class="col-6 col-md-2">

                <div class="card shadow-sm border-0 summary-card">

                    <div class="card-body">

                        <div class="summary-label">
                            Students
                        </div>

                        <div class="summary-value">
                            {{ $summary['total_students'] }}
                        </div>

                    </div>

                </div>

            </div>


            {{-- Recorded Days --}}
            <div class="col-6 col-md-2">

                <div class="card shadow-sm border-0 summary-card">

                    <div class="card-body">

                        <div class="summary-label">
                            Recorded Days
                        </div>

                        <div class="summary-value text-primary">
                            {{ $summary['recorded_days'] ?? 0 }}
                        </div>

                    </div>

                </div>

            </div>


            {{-- Present --}}
            <div class="col-6 col-md-2">

                <div class="card shadow-sm border-0 summary-card">

                    <div class="card-body">

                        <div class="summary-label">
                            Present
                        </div>

                        <div class="summary-value text-success">
                            {{ $summary['total_present'] ?? 0 }}
                        </div>

                    </div>

                </div>

            </div>


            {{-- Late --}}
            <div class="col-6 col-md-2">

                <div class="card shadow-sm border-0 summary-card">

                    <div class="card-body">

                        <div class="summary-label">
                            Late
                        </div>

                        <div class="summary-value text-warning">
                            {{ $summary['total_late'] ?? 0 }}
                        </div>

                    </div>

                </div>

            </div>


            {{-- Absent --}}
            <div class="col-6 col-md-2">

                <div class="card shadow-sm border-0 summary-card">

                    <div class="card-body">

                        <div class="summary-label">
                            Absent
                        </div>

                        <div class="summary-value text-danger">
                            {{ $summary['total_absent'] ?? 0 }}
                        </div>

                    </div>

                </div>

            </div>


            {{-- Excused --}}
            <div class="col-6 col-md-2">

                <div class="card shadow-sm border-0 summary-card">

                    <div class="card-body">

                        <div class="summary-label">
                            Excused
                        </div>

                        <div class="summary-value text-info">
                            {{ $summary['total_excused'] ?? 0 }}
                        </div>

                    </div>

                </div>

            </div>

        </div>

    @endif


    {{-- ============================================================
        ATTENDANCE TABLE
    ============================================================= --}}
    <div class="card shadow-sm attendance-table-card">

        <div class="card-body p-0">

            <div class="table-responsive attendance-scroll">

                @if(!empty($monthlyData) && count($monthlyData) > 0)

                    <table
                        class="table table-hover table-bordered mb-0 align-middle table-sm"
                        id="monthlyTable"
                    >

                        {{-- ==================================================
                            TABLE HEADER
                        =================================================== --}}
                        <thead class="table-primary text-dark">

                            <tr>

                                <th
                                    rowspan="2"
                                    class="sticky-column sticky-number text-center"
                                >
                                    #
                                </th>


                                <th
                                    rowspan="2"
                                    class="sticky-column sticky-name text-center"
                                >
                                    <i class="fas fa-user me-1"></i>
                                    Student Name
                                </th>


                                <th
                                    rowspan="2"
                                    class="sticky-column sticky-admission text-center"
                                >
                                    Admission No.
                                </th>


                                {{-- Days --}}
                                @foreach($days as $day)

                                    @php
                                        $dateString = $day->toDateString();

                                        $hasSessionForDay = false;

                                        foreach ($monthlyData as $studentData) {
                                            if (
                                                isset($studentData['attendance'][$dateString]) &&
                                                ($studentData['attendance'][$dateString]['status'] ?? 'no_record') !== 'no_record'
                                            ) {
                                                $hasSessionForDay = true;
                                                break;
                                            }
                                        }
                                    @endphp

                                    <th
                                        class="text-center day-header {{ $day->isWeekend() ? 'weekend-column' : '' }}"
                                        title="{{ $day->format('l, F d, Y') }}"
                                    >

                                        <div>
                                            {{ $day->format('d') }}
                                        </div>

                                        <small>
                                            {{ $day->format('D') }}
                                        </small>

                                    </th>

                                @endforeach


                                {{-- Totals --}}
                                <th class="summary-header">
                                    P
                                </th>

                                <th class="summary-header">
                                    L
                                </th>

                                <th class="summary-header">
                                    A
                                </th>

                                <th class="summary-header">
                                    E
                                </th>

                                <th class="summary-header total-header">
                                    Total
                                </th>

                                <th class="summary-header rate-header">
                                    Rate
                                </th>

                            </tr>


                            {{-- Second header row --}}
                            <tr>

                                @foreach($days as $day)

                                    <th
                                        class="text-center day-header-secondary {{ $day->isWeekend() ? 'weekend-column' : '' }}"
                                    >

                                        <small>
                                            {{ $day->format('D') }}
                                        </small>

                                    </th>

                                @endforeach

                            </tr>

                        </thead>


                        {{-- ==================================================
                            STUDENT ROWS
                        =================================================== --}}
                        <tbody>

                            @foreach($monthlyData as $data)

                                <tr>

                                    {{-- Number --}}
                                    <td class="sticky-body sticky-number">

                                        {{ $loop->iteration }}

                                    </td>


                                    {{-- Name --}}
                                    <td class="sticky-body sticky-name student-name-cell">

                                        <strong>

                                            {{ $data['student']->first_name }}

                                            @if(!empty($data['student']->middle_name))
                                                {{ $data['student']->middle_name }}
                                            @endif

                                            {{ $data['student']->last_name }}

                                        </strong>

                                    </td>


                                    {{-- Admission --}}
                                    <td class="sticky-body sticky-admission admission-cell">

                                        {{ $data['student']->student_id ?? 'N/A' }}

                                    </td>


                                    {{-- Daily attendance --}}
                                    @foreach($days as $day)

                                        @php

                                            $dateString =
                                                $day->toDateString();

                                            $attendance =
                                                $data['attendance'][$dateString]
                                                ?? [
                                                    'status' => 'no_record'
                                                ];

                                            $status =
                                                $attendance['status']
                                                ?? 'no_record';

                                            /*
                                            |--------------------------------------------------------------------------
                                            | Display configuration
                                            |--------------------------------------------------------------------------
                                            */

                                            $color = match($status) {

                                                'present' =>
                                                    'success',

                                                'late' =>
                                                    'warning',

                                                'excused' =>
                                                    'info',

                                                'absent' =>
                                                    'light',

                                                'no_record' =>
                                                    'secondary',

                                                default =>
                                                    'secondary',
                                            };


                                            $icon = match($status) {

                                                'present' =>
                                                    '✓',

                                                'late' =>
                                                    '⏰',

                                                'excused' =>
                                                    'ℹ',

                                                'absent' =>
                                                    '✗',

                                                'no_record' =>
                                                    '—',

                                                default =>
                                                    '—',
                                            };


                                            $textColor = match($status) {

                                                'present' =>
                                                    'text-success',

                                                'late' =>
                                                    'text-warning',

                                                'excused' =>
                                                    'text-info',

                                                'absent' =>
                                                    'text-muted',

                                                'no_record' =>
                                                    'text-muted',

                                                default =>
                                                    'text-muted',
                                            };


                                            $title = match($status) {

                                                'present' =>
                                                    'Present',

                                                'late' =>
                                                    'Late',

                                                'excused' =>
                                                    'Excused',

                                                'absent' =>
                                                    'Absent',

                                                'no_record' =>
                                                    'No attendance session recorded',

                                                default =>
                                                    'No Record',
                                            };

                                        @endphp


                                        <td
                                            class="text-center p-0 attendance-day-cell {{ $day->isWeekend() ? 'weekend-column' : '' }}"
                                        >

                                            <span
                                                class="badge bg-{{ $color }} p-1 {{ $textColor }} attendance-badge"
                                                title="{{ $title }} — {{ $day->format('F d, Y') }}"
                                            >
                                                {{ $icon }}
                                            </span>

                                        </td>

                                    @endforeach


                                    {{-- Present --}}
                                    <td class="text-center fw-bold text-success total-cell">

                                        {{ $data['present'] ?? 0 }}

                                    </td>


                                    {{-- Late --}}
                                    <td class="text-center fw-bold text-warning total-cell">

                                        {{ $data['late'] ?? 0 }}

                                    </td>


                                    {{-- Absent --}}
                                    <td class="text-center fw-bold text-danger total-cell">

                                        {{ $data['absent'] ?? 0 }}

                                    </td>


                                    {{-- Excused --}}
                                    <td class="text-center fw-bold text-info total-cell">

                                        {{ $data['excused'] ?? 0 }}

                                    </td>


                                    {{-- Recorded Total --}}
                                    <td class="text-center fw-bold total-cell">

                                        {{ $data['total'] ?? 0 }}

                                    </td>


                                    {{-- Rate --}}
                                    <td class="text-center rate-cell">

                                        @php

                                            $rate =
                                                (float) (
                                                    $data['attendance_rate']
                                                    ?? 0
                                                );

                                            $badgeColor =
                                                $rate >= 90
                                                    ? 'success'
                                                    : (
                                                        $rate >= 75
                                                            ? 'warning'
                                                            : 'danger'
                                                    );

                                        @endphp

                                        <span
                                            class="badge bg-{{ $badgeColor }} rate-badge"
                                        >
                                            {{ number_format($rate, 2) }}%
                                        </span>

                                    </td>

                                </tr>

                            @endforeach

                        </tbody>


                        {{-- ==================================================
                            DAILY SUMMARY
                        =================================================== --}}
                        <tfoot class="table-secondary">

                            <tr>

                                <th
                                    colspan="3"
                                    class="sticky-footer"
                                >
                                    <strong>
                                        Daily Summary
                                    </strong>
                                </th>


                                @foreach($days as $day)

                                    @php

                                        $dateString =
                                            $day->toDateString();

                                        $dayPresent = 0;
                                        $dayLate = 0;
                                        $dayAbsent = 0;
                                        $dayExcused = 0;

                                        $hasSession =
                                            false;

                                        foreach ($monthlyData as $data) {

                                            $att =
                                                $data['attendance'][$dateString]
                                                ?? [
                                                    'status' => 'no_record'
                                                ];

                                            $status =
                                                $att['status']
                                                ?? 'no_record';

                                            if ($status === 'present') {

                                                $dayPresent++;
                                                $hasSession = true;

                                            } elseif ($status === 'late') {

                                                $dayLate++;
                                                $hasSession = true;

                                            } elseif ($status === 'absent') {

                                                $dayAbsent++;
                                                $hasSession = true;

                                            } elseif ($status === 'excused') {

                                                $dayExcused++;
                                                $hasSession = true;

                                            }

                                        }

                                    @endphp


                                    <th
                                        class="text-center daily-summary-cell {{ $day->isWeekend() ? 'weekend-column' : '' }}"
                                    >

                                        @if($hasSession)

                                            <div>
                                                P: {{ $dayPresent }}
                                            </div>

                                            <div>
                                                L: {{ $dayLate }}
                                            </div>

                                            <div>
                                                A: {{ $dayAbsent }}
                                            </div>

                                            <div>
                                                E: {{ $dayExcused }}
                                            </div>

                                        @else

                                            <div class="no-record-summary">
                                                —
                                            </div>

                                            <small>
                                                No Record
                                            </small>

                                        @endif

                                    </th>

                                @endforeach


                                {{-- Overall totals --}}
                                <th class="text-center fw-bold summary-total">

                                    {{ $summary['total_present'] ?? 0 }}

                                </th>


                                <th class="text-center fw-bold summary-total">

                                    {{ $summary['total_late'] ?? 0 }}

                                </th>


                                <th class="text-center fw-bold summary-total">

                                    {{ $summary['total_absent'] ?? 0 }}

                                </th>


                                <th class="text-center fw-bold summary-total">

                                    {{ $summary['total_excused'] ?? 0 }}

                                </th>


                                <th class="text-center fw-bold summary-total">

                                    {{ $summary['total_records'] ?? 0 }}

                                </th>


                                <th class="text-center fw-bold summary-total">

                                    —

                                </th>

                            </tr>

                        </tfoot>

                    </table>

                @else

                    <div class="text-center py-5 text-muted">

                        <i class="fas fa-calendar-times fa-3x mb-3 d-block"></i>

                        <h6 class="fw-bold">
                            No Attendance Records
                        </h6>

                        <p class="mb-0">
                            Please select a class and month with attendance records.
                        </p>

                        @if(!empty($summary['class_name']))

                            <p class="small mt-2 mb-0">

                                No attendance records found for

                                <strong>
                                    {{ $summary['class_name'] }}
                                </strong>

                                in

                                <strong>
                                    {{ $summary['month_name'] ?? '' }}
                                </strong>.

                            </p>

                        @endif

                    </div>

                @endif

            </div>

        </div>

    </div>


    {{-- ============================================================
        LEGEND
    ============================================================= --}}
    @if(!empty($monthlyData) && count($monthlyData) > 0)

        <div class="card shadow-sm mt-2">

            <div class="card-body py-2 px-3">

                <div class="d-flex align-items-center flex-wrap gap-3">

                    <strong class="small me-2">
                        <i class="fas fa-info-circle me-1 text-primary"></i>
                        Legend
                    </strong>


                    <div class="legend-item">

                        <span class="badge bg-success">
                            ✓
                        </span>

                        Present

                    </div>


                    <div class="legend-item">

                        <span class="badge bg-warning text-dark">
                            ⏰
                        </span>

                        Late

                    </div>


                    <div class="legend-item">

                        <span class="badge bg-light text-muted border">
                            ✗
                        </span>

                        Absent

                    </div>


                    <div class="legend-item">

                        <span class="badge bg-info">
                            ℹ
                        </span>

                        Excused

                    </div>


                    <div class="legend-item">

                        <span class="badge bg-secondary">
                            —
                        </span>

                        No Record

                    </div>

                </div>

                <div class="small text-muted mt-2">

                    <i class="fas fa-calculator me-1"></i>

                    Attendance rate is calculated from actual recorded
                    attendance sessions only.

                    Days without an attendance session are not counted
                    as absences.

                </div>

            </div>

        </div>

    @endif

</div>


{{-- ================================================================
    STYLES
================================================================= --}}
<style>

.attendance-report-page {
    width: 100%;
}


/*
|--------------------------------------------------------------------------
| Summary Cards
|--------------------------------------------------------------------------
*/

.summary-card {
    border-radius: 8px;
}

.summary-card .card-body {
    padding: 8px 12px;
}

.summary-label {
    color: #6c757d;
    font-size: 10px;
    font-weight: 600;
    text-transform: uppercase;
}

.summary-value {
    font-size: 18px;
    font-weight: 700;
    line-height: 1.3;
}


/*
|--------------------------------------------------------------------------
| Attendance Table
|--------------------------------------------------------------------------
*/

.attendance-table-card {
    border-radius: 8px;
    overflow: hidden;
}

.attendance-scroll {
    max-height: 560px;
    overflow: auto;
}

#monthlyTable {
    min-width: max-content;
    margin-bottom: 0;
}

#monthlyTable th,
#monthlyTable td {
    font-size: 10px;
    padding: 2px 4px;
    white-space: nowrap;
}


/*
|--------------------------------------------------------------------------
| Header
|--------------------------------------------------------------------------
*/

#monthlyTable thead th {
    position: sticky;
    top: 0;
    z-index: 10;
    background: #e7f1ff;
}

#monthlyTable thead tr:nth-child(2) th {
    top: 28px;
    z-index: 9;
}

.day-header {
    min-width: 32px;
    width: 32px;
    text-align: center;
    vertical-align: middle;
    font-size: 9px !important;
}

.day-header small {
    display: block;
    color: #6c757d;
    font-size: 7px;
    font-weight: 500;
}

.day-header-secondary {
    min-width: 32px;
    width: 32px;
    height: 18px;
}

.summary-header {
    min-width: 28px;
    text-align: center;
    vertical-align: middle;
}

.total-header {
    min-width: 45px;
}

.rate-header {
    min-width: 55px;
}


/*
|--------------------------------------------------------------------------
| Sticky Student Columns
|--------------------------------------------------------------------------
*/

.sticky-column {
    position: sticky !important;
    background: #e7f1ff !important;
    z-index: 15 !important;
}

.sticky-number {
    left: 0;
    min-width: 35px;
    width: 35px;
}

.sticky-name {
    left: 35px;
    min-width: 160px;
    width: 160px;
}

.sticky-admission {
    left: 195px;
    min-width: 90px;
    width: 90px;
}


/*
|--------------------------------------------------------------------------
| Sticky Body Columns
|--------------------------------------------------------------------------
*/

.sticky-body {
    position: sticky;
    background: #fff;
    z-index: 5;
}

tbody .sticky-number {
    left: 0;
}

tbody .sticky-name {
    left: 35px;
}

tbody .sticky-admission {
    left: 195px;
}

.student-name-cell {
    font-size: 10px !important;
    overflow: hidden;
    text-overflow: ellipsis;
}

.admission-cell {
    font-size: 10px !important;
}


/*
|--------------------------------------------------------------------------
| Daily Attendance Cells
|--------------------------------------------------------------------------
*/

.attendance-day-cell {
    min-width: 32px;
    width: 32px;
    height: 28px;
}

.attendance-badge {
    min-width: 22px;
    font-size: 9px !important;
    line-height: 1.2;
    border-radius: 3px;
}

.weekend-column {
    background-color: #f8f9fa !important;
}


/*
|--------------------------------------------------------------------------
| Totals
|--------------------------------------------------------------------------
*/

.total-cell {
    min-width: 28px;
    font-size: 10px !important;
}

.rate-cell {
    min-width: 55px;
}

.rate-badge {
    min-width: 42px;
    font-size: 9px !important;
    padding: 3px 5px !important;
}


/*
|--------------------------------------------------------------------------
| Footer
|--------------------------------------------------------------------------
*/

#monthlyTable tfoot th {
    position: sticky;
    bottom: 0;
    z-index: 10;
    background: #e9ecef;
}

.sticky-footer {
    left: 0;
    z-index: 16 !important;
    text-align: left;
}

.daily-summary-cell {
    min-width: 32px;
    width: 32px;
    font-size: 7px !important;
    line-height: 1.15;
    padding: 2px !important;
}

.daily-summary-cell small {
    font-size: 6px;
    color: #6c757d;
}

.no-record-summary {
    font-size: 12px;
    color: #6c757d;
}

.summary-total {
    min-width: 30px;
    font-size: 9px !important;
}


/*
|--------------------------------------------------------------------------
| Legend
|--------------------------------------------------------------------------
*/

.legend-item {
    display: flex;
    align-items: center;
    gap: 4px;
    font-size: 11px;
}

.legend-item .badge {
    min-width: 20px;
    font-size: 9px;
}


/*
|--------------------------------------------------------------------------
| Cards / Forms
|--------------------------------------------------------------------------
*/

.card {
    border-radius: 8px;
}

.form-control-sm,
.form-select-sm {
    font-size: 11px;
    padding: 4px 8px;
}

.btn-sm {
    font-size: 11px;
    padding: 4px 10px;
}


/*
|--------------------------------------------------------------------------
| Scrollbars
|--------------------------------------------------------------------------
*/

.attendance-scroll::-webkit-scrollbar {
    width: 6px;
    height: 6px;
}

.attendance-scroll::-webkit-scrollbar-track {
    background: #f1f1f1;
}

.attendance-scroll::-webkit-scrollbar-thumb {
    background: #888;
    border-radius: 4px;
}

.attendance-scroll::-webkit-scrollbar-thumb:hover {
    background: #555;
}


/*
|--------------------------------------------------------------------------
| Responsive
|--------------------------------------------------------------------------
*/

@media (max-width: 768px) {

    .summary-label {
        font-size: 8px;
    }

    .summary-value {
        font-size: 15px;
    }

    #monthlyTable th,
    #monthlyTable td {
        font-size: 8px !important;
        padding: 1px 2px !important;
    }

    .sticky-name {
        min-width: 130px;
        width: 130px;
    }

    .sticky-admission {
        left: 165px;
        min-width: 75px;
        width: 75px;
    }

    tbody .sticky-admission {
        left: 165px;
    }

    .sticky-name {
        left: 35px;
    }

    .day-header,
    .day-header-secondary,
    .attendance-day-cell,
    .daily-summary-cell {
        min-width: 27px;
        width: 27px;
    }

    .attendance-badge {
        min-width: 18px;
        font-size: 7px !important;
    }

}


/*
|--------------------------------------------------------------------------
| Print
|--------------------------------------------------------------------------
*/

@media print {

    .btn,
    form,
    .no-print,
    .attendance-report-page > .alert {
        display: none !important;
    }

    .attendance-report-page {
        padding: 0 !important;
    }

    .card {
        border: 1px solid #ddd !important;
        box-shadow: none !important;
        margin-bottom: 4px !important;
    }

    .summary-card {
        display: none !important;
    }

    .attendance-scroll {
        max-height: none !important;
        overflow: visible !important;
    }

    #monthlyTable {
        font-size: 6px !important;
    }

    #monthlyTable th,
    #monthlyTable td {
        font-size: 6px !important;
        padding: 1px 2px !important;
    }

    #monthlyTable thead th {
        position: static !important;
    }

    #monthlyTable tfoot th {
        position: static !important;
    }

    .sticky-column,
    .sticky-body {
        position: static !important;
    }

    .attendance-badge {
        border: 1px solid #999 !important;
        font-size: 6px !important;
        padding: 1px 2px !important;
        background: #fff !important;
        color: #000 !important;
    }

    .table-primary {
        background: #e9ecef !important;
    }

    .table-secondary {
        background: #e9ecef !important;
    }

    .text-success,
    .text-warning,
    .text-danger,
    .text-info,
    .text-primary {
        color: #000 !important;
    }

}

</style>


{{-- ================================================================
    JAVASCRIPT
================================================================= --}}
<script>

document.addEventListener(
    'DOMContentLoaded',
    function () {

        /*
        |--------------------------------------------------------------------------
        | Auto-submit class filter
        |--------------------------------------------------------------------------
        */

        const classSelect =
            document.querySelector(
                'select[name="class_id"]'
            );

        const monthInput =
            document.querySelector(
                'input[name="month_year"]'
            );


        if (classSelect) {

            classSelect.addEventListener(
                'change',
                function () {

                    this.closest(
                        'form'
                    ).submit();

                }
            );

        }


        if (monthInput) {

            monthInput.addEventListener(
                'change',
                function () {

                    this.closest(
                        'form'
                    ).submit();

                }
            );

        }

    }
);


/*
|--------------------------------------------------------------------------
| Print
|--------------------------------------------------------------------------
*/

function printReport()
{
    window.print();
}

</script>

@endsection