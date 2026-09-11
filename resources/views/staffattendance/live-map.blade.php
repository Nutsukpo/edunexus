@extends('layouts.master')

@section('title', 'Live Attendance Map')

@section('content')

<div class="container-fluid py-4">

    {{-- ============================================================
         PAGE HEADER
    ============================================================= --}}
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">

                <div>
                    <h3 class="fw-bold mb-1">
                        <i class="fas fa-map-marker-alt text-primary me-2"></i>
                        Live Staff Location Map
                    </h3>

                    <p class="text-muted mb-0">
                        Staff GPS attendance locations for the selected academic period.
                    </p>
                </div>

                <div class="d-flex gap-2 flex-wrap">
                    <button type="button"
                            class="btn btn-outline-primary btn-sm"
                            id="refreshMapBtn">
                        <i class="fas fa-sync-alt me-1"></i>
                        Refresh
                    </button>

                    <button type="button"
                            class="btn btn-outline-secondary btn-sm"
                            id="fitMapBtn">
                        <i class="fas fa-expand-alt me-1"></i>
                        Fit All
                    </button>

                    <button type="button"
                            class="btn btn-outline-primary btn-sm"
                            id="toggleMarkersBtn">
                        <i class="fas fa-eye me-1"></i>
                        Toggle Markers
                    </button>

                    <button type="button"
                            class="btn btn-outline-success btn-sm"
                            id="exportBtn">
                        <i class="fas fa-download me-1"></i>
                        Export
                    </button>
                </div>

            </div>
        </div>
    </div>


    {{-- ============================================================
         FLASH MESSAGES
    ============================================================= --}}
    @foreach (['success' => 'success', 'error' => 'danger', 'warning' => 'warning', 'info' => 'info'] as $msg => $type)
        @if(session($msg))
            <div class="alert alert-{{ $type }} alert-dismissible fade show shadow-sm">
                {{ session($msg) }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif
    @endforeach


    {{-- ============================================================
         ACADEMIC YEAR / TERM FILTER
    ============================================================= --}}
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white py-3">
            <h5 class="mb-0">
                <i class="fas fa-calendar-alt text-primary me-2"></i>
                Academic Period
            </h5>
        </div>

        <div class="card-body">
            <form method="GET"
                  action="{{ url()->current() }}"
                  id="periodFilterForm">

                <div class="row g-3 align-items-end">

                    <div class="col-lg-5 col-md-6">
                        <label for="academic_year_id" class="form-label fw-semibold">
                            Academic Year
                        </label>

                        <select name="academic_year_id"
                                id="academic_year_id"
                                class="form-select">

                            <option value="">-- All Academic Years --</option>

                            @foreach(collect($academicYears ?? []) as $year)
                                <option value="{{ $year->id }}"
                                    @selected((string)($selectedAcademicYearId ?? request('academic_year_id')) === (string)$year->id)>
                                    {{ $year->name }}
                                </option>
                            @endforeach

                        </select>
                    </div>


                    <div class="col-lg-5 col-md-6">
                        <label for="term_id" class="form-label fw-semibold">
                            Term
                        </label>

                        <select name="term_id"
                                id="term_id"
                                class="form-select"
                                {{ empty($selectedAcademicYearId ?? request('academic_year_id')) ? 'disabled' : '' }}>

                            <option value="">-- All Terms --</option>

                            @foreach(collect($terms ?? []) as $term)
                                <option value="{{ $term->id }}"
                                    @selected((string)($selectedTermId ?? request('term_id')) === (string)$term->id)>
                                    {{ $term->name }}
                                </option>
                            @endforeach

                        </select>
                    </div>


                    <div class="col-lg-2 col-md-12">
                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary flex-grow-1">
                                <i class="fas fa-filter me-1"></i>
                                Apply
                            </button>

                            <a href="{{ url()->current() }}"
                               class="btn btn-outline-secondary"
                               title="Clear academic period">
                                <i class="fas fa-times"></i>
                            </a>
                        </div>
                    </div>

                </div>

            </form>
        </div>
    </div>


    {{-- ============================================================
         PERIOD SUMMARY
    ============================================================= --}}
    @php
        $academicYearId = $selectedAcademicYearId ?? request('academic_year_id');
        $termId = $selectedTermId ?? request('term_id');

        $selectedYear = collect($academicYears ?? [])->firstWhere('id', $academicYearId);
        $selectedTerm = collect($terms ?? [])->firstWhere('id', $termId);

        $attendanceCollection = collect($attendances ?? []);
        $locationCollection = collect($locations ?? []);

        $gpsLocations = $locationCollection->filter(function ($attendance) {
            return is_numeric($attendance->clock_in_latitude ?? null)
                && is_numeric($attendance->clock_in_longitude ?? null);
        })->values();

        $clockedInCount = $attendanceCollection->filter(
            fn ($attendance) => !empty($attendance->clock_in_time)
        )->count();

        $workingCount = $attendanceCollection->filter(
            fn ($attendance) => !empty($attendance->clock_in_time) && empty($attendance->clock_out_time)
        )->count();

        $completedCount = $attendanceCollection->filter(
            fn ($attendance) => !empty($attendance->clock_out_time)
        )->count();
    @endphp

    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body py-3">

            <div class="d-flex align-items-center flex-wrap gap-2">

                <strong class="me-1">
                    <i class="fas fa-calendar-check text-primary me-1"></i>
                    Viewing:
                </strong>

                @if($selectedYear)
                    <span class="badge bg-primary px-3 py-2">
                        {{ $selectedYear->name }}
                    </span>
                @else
                    <span class="badge bg-secondary px-3 py-2">
                        All Academic Years
                    </span>
                @endif

                @if($selectedTerm)
                    <span class="badge bg-info text-dark px-3 py-2">
                        {{ $selectedTerm->name }}
                    </span>
                @else
                    <span class="badge bg-secondary px-3 py-2">
                        All Terms
                    </span>
                @endif

                <span class="text-muted ms-auto">
                    <i class="fas fa-map-marker-alt me-1"></i>
                    <strong>{{ $gpsLocations->count() }}</strong>
                    GPS location(s)
                </span>

            </div>

        </div>
    </div>


    {{-- ============================================================
         ATTENDANCE RECORDS
    ============================================================= --}}
    <div class="card border-0 shadow-sm mb-4">

        <div class="card-header bg-white py-3">
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">

                <h5 class="mb-0">
                    <i class="fas fa-clock text-primary me-2"></i>
                    Staff Attendance Records
                    <span class="badge bg-dark ms-2">
                        {{ $attendanceCollection->count() }}
                    </span>
                </h5>

                <span class="small text-muted">
                    {{ now()->format('l, d M Y') }}
                </span>

            </div>
        </div>

        <div class="card-body p-0">

            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">

                    <thead class="table-light">
                        <tr>
                            <th class="text-nowrap">#</th>
                            <th class="text-nowrap">Staff</th>
                            <th class="text-nowrap">Academic Year</th>
                            <th class="text-nowrap">Term</th>
                            <th class="text-nowrap">Date</th>
                            <th class="text-nowrap">Clock In</th>
                            <th class="text-nowrap">Clock Out</th>
                            <th class="text-nowrap">Duration</th>
                            <th class="text-nowrap">Status</th>
                            <th class="text-nowrap">GPS</th>
                            <th class="text-nowrap">Action</th>
                        </tr>
                    </thead>

                    <tbody>

                        @forelse($attendanceCollection as $index => $attendance)

                            @php
                                $clockIn = $attendance->clock_in_time
                                    ? \Carbon\Carbon::parse($attendance->clock_in_time)
                                    : null;

                                $clockOut = $attendance->clock_out_time
                                    ? \Carbon\Carbon::parse($attendance->clock_out_time)
                                    : null;

                                $duration = '--';

                                if ($clockIn) {
                                    $end = $clockOut ?? now();
                                    $totalMinutes = max(0, $clockIn->diffInMinutes($end));
                                    $hours = intdiv($totalMinutes, 60);
                                    $minutes = $totalMinutes % 60;

                                    $duration = $hours > 0
                                        ? $hours . 'h ' . $minutes . 'm'
                                        : $minutes . ' min';
                                }

                                $status = $clockOut
                                    ? 'Clocked Out'
                                    : ($clockIn ? 'Working' : 'Not Clocked In');

                                $statusClass = $clockOut
                                    ? 'secondary'
                                    : ($clockIn ? 'success' : 'warning');

                                $hasGps = is_numeric($attendance->clock_in_latitude ?? null)
                                    && is_numeric($attendance->clock_in_longitude ?? null);
                            @endphp

                            <tr>

                                <td>{{ $index + 1 }}</td>

                                <td>
                                    <div class="fw-semibold text-nowrap">
                                        {{ $attendance->staff->first_name ?? 'Unknown' }}
                                        {{ $attendance->staff->last_name ?? '' }}
                                    </div>
                                </td>

                                <td>
                                    <span class="badge bg-light text-dark border">
                                        {{ $attendance->academicYear->name ?? '—' }}
                                    </span>
                                </td>

                                <td>
                                    <span class="badge bg-light text-dark border">
                                        {{ $attendance->term->name ?? '—' }}
                                    </span>
                                </td>

                                <td class="text-nowrap">
                                    {{ $attendance->date
                                        ? \Carbon\Carbon::parse($attendance->date)->format('d M Y')
                                        : '—' }}
                                </td>

                                <td class="text-nowrap">
                                    @if($clockIn)
                                        <span class="badge bg-light text-dark border px-3 py-2">
                                            <i class="fas fa-sign-in-alt text-success me-1"></i>
                                            {{ $clockIn->format('h:i A') }}
                                        </span>
                                    @else
                                        —
                                    @endif
                                </td>

                                <td class="text-nowrap">
                                    @if($clockOut)
                                        <span class="badge bg-light text-dark border px-3 py-2">
                                            <i class="fas fa-sign-out-alt text-secondary me-1"></i>
                                            {{ $clockOut->format('h:i A') }}
                                        </span>
                                    @else
                                        —
                                    @endif
                                </td>

                                <td class="text-nowrap fw-semibold">
                                    {{ $duration }}
                                </td>

                                <td>
                                    <span class="badge bg-{{ $statusClass }}">
                                        {{ $status }}
                                    </span>
                                </td>

                                <td>
                                    @if($hasGps)
                                        <span class="badge bg-success">
                                            <i class="fas fa-check me-1"></i>
                                            GPS
                                        </span>
                                    @else
                                        <span class="badge bg-light text-muted border">
                                            No GPS
                                        </span>
                                    @endif
                                </td>

                                <td>
                                    @if($hasGps)
                                        <button type="button"
                                                class="btn btn-sm btn-outline-primary locate-btn"
                                                data-lat="{{ $attendance->clock_in_latitude }}"
                                                data-lng="{{ $attendance->clock_in_longitude }}">
                                            <i class="fas fa-map-marker-alt me-1"></i>
                                            Locate
                                        </button>
                                    @else
                                        —
                                    @endif
                                </td>

                            </tr>

                        @empty

                            <tr>
                                <td colspan="11" class="text-center py-5">

                                    <i class="fas fa-calendar-times fa-3x text-muted mb-3 d-block"></i>

                                    <h6 class="fw-semibold">
                                        No Attendance Records Found
                                    </h6>

                                    <p class="text-muted mb-0">
                                        There are no staff attendance records for the selected academic period.
                                    </p>

                                </td>
                            </tr>

                        @endforelse

                    </tbody>

                </table>
            </div>

        </div>
    </div>


    {{-- ============================================================
         STATISTICS
    ============================================================= --}}
    <div class="row g-3 mb-4">

        <div class="col-xl-3 col-md-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <small class="text-muted d-block">Attendance Records</small>
                            <h2 class="fw-bold mb-0">
                                {{ $attendanceCollection->count() }}
                            </h2>
                        </div>
                        <i class="fas fa-users fa-2x text-primary opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <small class="text-muted d-block">Clocked In</small>
                            <h2 class="fw-bold mb-0">
                                {{ $clockedInCount }}
                            </h2>
                        </div>
                        <i class="fas fa-sign-in-alt fa-2x text-success opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <small class="text-muted d-block">Working Now</small>
                            <h2 class="fw-bold mb-0">
                                {{ $workingCount }}
                            </h2>
                        </div>
                        <i class="fas fa-clock fa-2x text-warning opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <small class="text-muted d-block">Completed</small>
                            <h2 class="fw-bold mb-0">
                                {{ $completedCount }}
                            </h2>
                        </div>
                        <i class="fas fa-check-circle fa-2x text-secondary opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>

    </div>


    {{-- ============================================================
         MAP + STAFF LIST
    ============================================================= --}}
    <div class="row g-3">

        <div class="col-12 col-xl-8">

            <div class="card border-0 shadow-sm">

                <div class="card-header bg-white py-3">
                    <div class="d-flex justify-content-between align-items-center">

                        <h5 class="mb-0">
                            <i class="fas fa-map-marked-alt text-primary me-2"></i>
                            Staff Location Map
                        </h5>

                        <span class="badge bg-light text-dark border">
                            <i class="fas fa-map-marker-alt text-primary me-1"></i>
                            <span id="markerCount">{{ $gpsLocations->count() }}</span>
                            Located
                        </span>

                    </div>
                </div>

                <div class="card-body p-0">

                    <div id="map">
                        <div id="mapLoading"
                             class="map-message">
                            <div class="spinner-border text-primary mb-3"
                                 role="status">
                            </div>

                            <div class="fw-semibold">
                                Loading map...
                            </div>
                        </div>
                    </div>

                </div>

                <div class="card-footer bg-white py-2">
                    <div class="d-flex justify-content-center gap-4 flex-wrap small">

                        <span>
                            <i class="fas fa-circle text-success me-1"></i>
                            Working
                        </span>

                        <span>
                            <i class="fas fa-circle text-secondary me-1"></i>
                            Clocked Out
                        </span>

                        <span>
                            <i class="fas fa-info-circle text-info me-1"></i>
                            Click a marker for details
                        </span>

                    </div>
                </div>

            </div>

        </div>


        <div class="col-12 col-xl-4">

            <div class="card border-0 shadow-sm h-100">

                <div class="card-header bg-white py-3">

                    <div class="d-flex justify-content-between align-items-center gap-2">

                        <h5 class="mb-0">
                            <i class="fas fa-users text-primary me-2"></i>
                            Staff on Map
                        </h5>

                        <input type="search"
                               id="staffSearchInput"
                               class="form-control form-control-sm"
                               style="max-width:180px;"
                               placeholder="Search staff...">

                    </div>

                </div>

                <div id="staffListContainer"
                     class="staff-list-container">

                    {{-- Populated by JavaScript --}}

                </div>

            </div>

        </div>

    </div>

</div>


{{-- ================================================================
     LEAFLET
================================================================ --}}
<link rel="stylesheet"
      href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css">

<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>


<style>
    /*
     * IMPORTANT:
     * The map has an explicit width and height. This prevents the
     * Leaflet tiles from being calculated against a collapsed parent.
     */
    #map {
        position: relative;
        width: 100%;
        height: 560px;
        min-height: 420px;
        overflow: hidden;
        background: #e9ecef;
        z-index: 1;
    }

    #map .leaflet-container {
        width: 100%;
        height: 100%;
        font-family: inherit;
    }

    .map-message {
        position: absolute;
        inset: 0;
        z-index: 500;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        background: #f8f9fa;
        color: #6c757d;
    }

    .staff-list-container {
        height: 560px;
        max-height: 560px;
        overflow-y: auto;
        overflow-x: hidden;
    }

    .staff-list-empty {
        min-height: 300px;
        display: flex;
        flex-direction: column;
        justify-content: center;
        align-items: center;
        text-align: center;
        padding: 30px;
        color: #6c757d;
    }

    .staff-list-item {
        cursor: pointer;
        transition: background-color .2s ease, border-left-color .2s ease;
        border-left: 4px solid transparent !important;
    }

    .staff-list-item:hover {
        background-color: #f8f9fa;
    }

    .staff-list-item.active {
        background-color: #f1f5ff;
        border-left-color: #0d6efd !important;
    }

    .staff-avatar {
        width: 42px;
        height: 42px;
        min-width: 42px;
        border-radius: 50%;
        background: #0d6efd;
        color: #fff;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 700;
        font-size: 16px;
    }

    .status-dot {
        width: 9px;
        height: 9px;
        display: inline-block;
        border-radius: 50%;
        margin-right: 4px;
    }

    .status-working {
        color: #198754;
    }

    .status-working .status-dot {
        background: #198754;
    }

    .status-clockedout {
        color: #6c757d;
    }

    .status-clockedout .status-dot {
        background: #6c757d;
    }

    .custom-staff-marker {
        background: transparent !important;
        border: 0 !important;
    }

    .working-marker {
        animation: markerPulse 2s infinite;
    }

    @keyframes markerPulse {
        0% {
            box-shadow: 0 0 0 0 rgba(25, 135, 84, .55);
        }

        70% {
            box-shadow: 0 0 0 10px rgba(25, 135, 84, 0);
        }

        100% {
            box-shadow: 0 0 0 0 rgba(25, 135, 84, 0);
        }
    }

    .toast-notification {
        position: fixed;
        top: 20px;
        right: 20px;
        z-index: 10000;
        background: #fff;
        border-radius: 8px;
        padding: 12px 18px;
        box-shadow: 0 4px 15px rgba(0,0,0,.15);
        font-size: 13px;
    }

    .toast-success {
        border-left: 4px solid #198754;
    }

    .toast-warning {
        border-left: 4px solid #ffc107;
    }

    .toast-info {
        border-left: 4px solid #0dcaf0;
    }

    @media (max-width: 1199.98px) {
        .staff-list-container {
            height: 420px;
            max-height: 420px;
        }
    }

    @media (max-width: 767.98px) {
        #map {
            height: 430px;
        }

        .staff-list-container {
            height: 380px;
            max-height: 380px;
        }
    }
</style>


<script>
(function () {

    'use strict';

    /*
     * ------------------------------------------------------------
     * SERVER DATA
     * ------------------------------------------------------------
     */
    const staffLocations = @json($locations ?? []);
    const selectedAcademicYearId =
        @json($selectedAcademicYearId ?? request('academic_year_id'));

    const selectedTermId =
        @json($selectedTermId ?? request('term_id'));


    /*
     * ------------------------------------------------------------
     * MAP STATE
     * ------------------------------------------------------------
     */
    let map = null;
    let markerLayer = null;
    let markers = [];
    let markersVisible = true;


    /*
     * ------------------------------------------------------------
     * ELEMENTS
     * ------------------------------------------------------------
     */
    const mapElement = document.getElementById('map');
    const mapLoading = document.getElementById('mapLoading');
    const markerCount = document.getElementById('markerCount');
    const staffListContainer =
        document.getElementById('staffListContainer');

    const academicYearSelect =
        document.getElementById('academic_year_id');

    const termSelect =
        document.getElementById('term_id');


    /*
     * ------------------------------------------------------------
     * HELPERS
     * ------------------------------------------------------------
     */
    function escapeHtml(value) {

        const div = document.createElement('div');

        div.textContent =
            value === null || value === undefined
                ? ''
                : String(value);

        return div.innerHTML;
    }


    function getStaffName(attendance) {

        const staff = attendance.staff || {};

        const name = (
            (staff.first_name || '') +
            ' ' +
            (staff.last_name || '')
        ).trim();

        return name || 'Unknown Staff';
    }


    function getAcademicYearName(attendance) {

        return attendance.academicYear?.name ||
               attendance.academic_year?.name ||
               '—';
    }


    function getTermName(attendance) {

        return attendance.term?.name || '—';
    }


    function getAttendanceDate(attendance) {

        if (!attendance.date) {
            return new Date().toISOString().slice(0, 10);
        }

        return String(attendance.date).slice(0, 10);
    }


    function formatDate(attendance) {

        const value = getAttendanceDate(attendance);

        const date = new Date(value + 'T00:00:00');

        if (Number.isNaN(date.getTime())) {
            return value;
        }

        return date.toLocaleDateString(undefined, {
            day: '2-digit',
            month: 'short',
            year: 'numeric'
        });
    }


    function formatTime(value) {

        if (!value) {
            return '—';
        }

        const parts = String(value).split(':');

        if (parts.length < 2) {
            return String(value);
        }

        let hours = parseInt(parts[0], 10);

        if (Number.isNaN(hours)) {
            return String(value);
        }

        const minutes = parts[1];
        const suffix = hours >= 12 ? 'PM' : 'AM';

        hours = hours % 12 || 12;

        return String(hours).padStart(2, '0') +
               ':' +
               minutes +
               ' ' +
               suffix;
    }


    function toDateTime(attendance, time) {

        if (!time) {
            return null;
        }

        const date = getAttendanceDate(attendance);

        const result = new Date(
            date + 'T' + String(time).slice(0, 8)
        );

        return Number.isNaN(result.getTime())
            ? null
            : result;
    }


    function duration(attendance) {

        const clockIn = toDateTime(
            attendance,
            attendance.clock_in_time
        );

        if (!clockIn) {
            return '—';
        }

        const clockOut = toDateTime(
            attendance,
            attendance.clock_out_time
        ) || new Date();

        const milliseconds =
            Math.max(0, clockOut.getTime() - clockIn.getTime());

        const minutes =
            Math.floor(milliseconds / 60000);

        const hours =
            Math.floor(minutes / 60);

        const remainingMinutes =
            minutes % 60;

        if (hours > 0) {
            return hours + 'h ' + remainingMinutes + 'm';
        }

        return remainingMinutes + ' min';
    }


    function timeAgo(attendance) {

        const clockIn = toDateTime(
            attendance,
            attendance.clock_in_time
        );

        if (!clockIn) {
            return '';
        }

        const difference =
            Math.floor((Date.now() - clockIn.getTime()) / 60000);

        if (difference <= 0) {
            return 'Just now';
        }

        if (difference < 60) {
            return difference + ' min ago';
        }

        const hours = Math.floor(difference / 60);
        const minutes = difference % 60;

        if (hours < 24) {
            return hours + ' hr ' + minutes + ' min ago';
        }

        return Math.floor(hours / 24) + ' days ago';
    }


    function hasGps(attendance) {

        return Number.isFinite(
            parseFloat(attendance.clock_in_latitude)
        ) && Number.isFinite(
            parseFloat(attendance.clock_in_longitude)
        );
    }


    function getGpsRecords() {

        return staffLocations.filter(function (attendance) {
            return hasGps(attendance);
        });
    }


    /*
     * ------------------------------------------------------------
     * ACADEMIC YEAR -> TERM
     * ------------------------------------------------------------
     */
    function loadTerms(yearId, preserveSelectedTerm) {

        if (!termSelect) {
            return;
        }

        if (!yearId) {
            termSelect.innerHTML =
                '<option value="">-- All Terms --</option>';

            termSelect.disabled = true;

            return;
        }

        termSelect.disabled = true;

        termSelect.innerHTML =
            '<option value="">Loading terms...</option>';

        const template =
            @json(route('settings.academic-years.terms', ['academicYear' => '__YEAR__']));

        const url =
            template.replace(
                '__YEAR__',
                encodeURIComponent(yearId)
            );

        fetch(url, {
            method: 'GET',
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(function (response) {

            if (!response.ok) {
                throw new Error('Unable to load terms.');
            }

            return response.json();
        })
        .then(function (data) {

            const terms = Array.isArray(data)
                ? data
                : (data.terms || data.data || []);

            termSelect.innerHTML =
                '<option value="">-- All Terms --</option>';

            terms.forEach(function (term) {

                const option =
                    document.createElement('option');

                option.value = term.id;
                option.textContent = term.name;

                if (
                    preserveSelectedTerm &&
                    selectedTermId !== null &&
                    selectedTermId !== '' &&
                    String(selectedTermId) === String(term.id)
                ) {
                    option.selected = true;
                }

                termSelect.appendChild(option);
            });

            termSelect.disabled = false;
        })
        .catch(function (error) {

            console.error(
                'Academic term loading error:',
                error
            );

            termSelect.innerHTML =
                '<option value="">Unable to load terms</option>';

            termSelect.disabled = true;
        });
    }


    if (academicYearSelect && termSelect) {

        academicYearSelect.addEventListener(
            'change',
            function () {

                loadTerms(
                    this.value,
                    false
                );
            }
        );
    }


    /*
     * ------------------------------------------------------------
     * STAFF LIST
     * ------------------------------------------------------------
     */
    function renderStaffList() {

        if (!staffListContainer) {
            return;
        }

        const gpsRecords = getGpsRecords();

        if (gpsRecords.length === 0) {

            staffListContainer.innerHTML = `
                <div class="staff-list-empty">
                    <i class="fas fa-map-marker-alt fa-3x mb-3"></i>
                    <h6 class="fw-semibold">No GPS Location Data</h6>
                    <p class="small mb-0">
                        No staff attendance record with GPS coordinates
                        was found for the selected academic period.
                    </p>
                </div>
            `;

            return;
        }

        let html = '';

        gpsRecords.forEach(function (attendance, index) {

            const name = getStaffName(attendance);
            const firstLetter =
                name.charAt(0).toUpperCase();

            const clockedOut =
                Boolean(attendance.clock_out_time);

            const status =
                clockedOut
                    ? 'Clocked Out'
                    : 'Working';

            const statusClass =
                clockedOut
                    ? 'status-clockedout'
                    : 'status-working';

            html += `
                <div class="list-group-item staff-list-item"
                     data-marker-index="${index}">

                    <div class="d-flex align-items-center gap-3">

                        <div class="staff-avatar">
                            ${escapeHtml(firstLetter)}
                        </div>

                        <div class="flex-grow-1 overflow-hidden">

                            <div class="fw-semibold text-truncate">
                                ${escapeHtml(name)}
                            </div>

                            <small class="text-muted d-block">
                                <i class="fas fa-clock me-1"></i>
                                ${escapeHtml(formatTime(attendance.clock_in_time))}
                            </small>

                            <small class="text-muted d-block">
                                <i class="fas fa-hourglass-half me-1"></i>
                                ${escapeHtml(duration(attendance))}
                            </small>

                        </div>

                        <div class="text-end text-nowrap">

                            <div class="${statusClass} small fw-semibold">
                                <span class="status-dot"></span>
                                ${escapeHtml(status)}
                            </div>

                            <small class="text-muted">
                                ${escapeHtml(timeAgo(attendance))}
                            </small>

                        </div>

                    </div>

                </div>
            `;
        });

        staffListContainer.innerHTML = html;

        staffListContainer
            .querySelectorAll('[data-marker-index]')
            .forEach(function (item) {

                item.addEventListener('click', function () {

                    const index =
                        parseInt(
                            this.dataset.markerIndex,
                            10
                        );

                    focusMarker(index);
                });
            });
    }


    /*
     * ------------------------------------------------------------
     * MAP MARKERS
     * ------------------------------------------------------------
     */
    function createMarker(attendance, index) {

        const latitude =
            parseFloat(attendance.clock_in_latitude);

        const longitude =
            parseFloat(attendance.clock_in_longitude);

        const name = getStaffName(attendance);

        const firstLetter =
            name.charAt(0).toUpperCase();

        const clockedOut =
            Boolean(attendance.clock_out_time);

        const color =
            clockedOut
                ? '#6c757d'
                : '#198754';

        const markerClass =
            clockedOut
                ? ''
                : 'working-marker';

        const markerHtml = `
            <div class="${markerClass}"
                 style="
                    width:34px;
                    height:34px;
                    border-radius:50%;
                    background:${color};
                    border:3px solid #fff;
                    box-shadow:0 2px 8px rgba(0,0,0,.35);
                    display:flex;
                    align-items:center;
                    justify-content:center;
                    color:#fff;
                    font-weight:700;
                    font-size:13px;
                 ">
                ${escapeHtml(firstLetter)}
            </div>
        `;

        const marker =
            L.marker(
                [latitude, longitude],
                {
                    icon: L.divIcon({
                        html: markerHtml,
                        className: 'custom-staff-marker',
                        iconSize: [34, 34],
                        iconAnchor: [17, 17],
                        popupAnchor: [0, -17]
                    })
                }
            );

        const popup = `
            <div style="min-width:280px;">

                <div style="
                    font-size:16px;
                    font-weight:700;
                    padding-bottom:8px;
                    margin-bottom:8px;
                    border-bottom:2px solid #0d6efd;
                ">
                    <i class="fas fa-user-circle me-1"></i>
                    ${escapeHtml(name)}
                </div>

                <div style="font-size:13px;line-height:1.9;">

                    <div>
                        <i class="fas fa-calendar-alt text-primary me-1"></i>
                        <strong>Academic Year:</strong>
                        ${escapeHtml(getAcademicYearName(attendance))}
                    </div>

                    <div>
                        <i class="fas fa-calendar-check text-primary me-1"></i>
                        <strong>Term:</strong>
                        ${escapeHtml(getTermName(attendance))}
                    </div>

                    <div>
                        <i class="fas fa-calendar-day text-primary me-1"></i>
                        <strong>Date:</strong>
                        ${escapeHtml(formatDate(attendance))}
                    </div>

                    <div>
                        <i class="fas fa-sign-in-alt text-success me-1"></i>
                        <strong>Clock In:</strong>
                        ${escapeHtml(formatTime(attendance.clock_in_time))}
                        <small class="text-muted">
                            (${escapeHtml(timeAgo(attendance))})
                        </small>
                    </div>

                    <div>
                        <i class="fas fa-sign-out-alt text-secondary me-1"></i>
                        <strong>Clock Out:</strong>
                        ${escapeHtml(formatTime(attendance.clock_out_time))}
                    </div>

                    <div>
                        <i class="fas fa-hourglass-half text-warning me-1"></i>
                        <strong>Duration:</strong>
                        ${escapeHtml(duration(attendance))}
                    </div>

                    <div>
                        <i class="fas fa-tag me-1"></i>
                        <strong>Status:</strong>
                        <span style="color:${color};font-weight:600;">
                            ${clockedOut ? 'Clocked Out' : 'Working'}
                        </span>
                    </div>

                    <div>
                        <i class="fas fa-map-marker-alt text-danger me-1"></i>
                        <strong>GPS:</strong><br>
                        <code>
                            ${latitude.toFixed(6)},
                            ${longitude.toFixed(6)}
                        </code>
                    </div>

                </div>

            </div>
        `;

        marker.bindPopup(popup);

        marker.on('click', function () {
            highlightStaff(index);
        });

        return marker;
    }


    function renderMarkers() {

        if (!map || !markerLayer) {
            return;
        }

        markerLayer.clearLayers();
        markers = [];

        const gpsRecords = getGpsRecords();

        const bounds = [];

        gpsRecords.forEach(function (attendance, index) {

            const marker =
                createMarker(
                    attendance,
                    index
                );

            marker.addTo(markerLayer);

            markers.push(marker);

            bounds.push([
                parseFloat(attendance.clock_in_latitude),
                parseFloat(attendance.clock_in_longitude)
            ]);
        });

        if (markerCount) {
            markerCount.textContent =
                gpsRecords.length;
        }

        if (bounds.length > 0) {
            map.fitBounds(bounds, {
                padding: [40, 40],
                maxZoom: 16,
                animate: false
            });
        }
    }


    /*
     * ------------------------------------------------------------
     * MAP INITIALIZATION
     * ------------------------------------------------------------
     */
    function showMapMessage(message, icon) {

        if (!mapElement) {
            return;
        }

        mapElement.innerHTML = `
            <div class="map-message">
                <i class="fas ${icon} fa-3x text-muted mb-3"></i>
                <h6 class="fw-semibold mb-1">
                    ${escapeHtml(message)}
                </h6>
                <p class="text-muted small mb-0">
                    No GPS attendance coordinates are available.
                </p>
            </div>
        `;
    }


    function initializeMap() {

        if (!mapElement) {
            return;
        }

        /*
         * Always initialize Leaflet, even when there are zero records.
         * This keeps the page stable and lets the user see the map area.
         */
        map =
            L.map(
                mapElement,
                {
                    center: [5.6037, -0.1870],
                    zoom: 12,
                    zoomControl: true,
                    attributionControl: true
                }
            );

        L.tileLayer(
            'https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png',
            {
                maxZoom: 19,
                attribution: '&copy; OpenStreetMap contributors'
            }
        ).addTo(map);

        markerLayer =
            L.layerGroup().addTo(map);

        renderMarkers();
        renderStaffList();

        /*
         * Leaflet can calculate the map width incorrectly when the
         * master layout/card is still settling. Recalculate several
         * times after the page has become visible.
         */
        const resizeMap = function () {

            if (!map) {
                return;
            }

            map.invalidateSize({
                pan: false,
                animate: false
            });
        };

        requestAnimationFrame(resizeMap);

        setTimeout(resizeMap, 100);
        setTimeout(resizeMap, 400);
        setTimeout(resizeMap, 800);
        setTimeout(resizeMap, 1500);

        window.addEventListener(
            'resize',
            resizeMap
        );

        if (mapLoading) {
            mapLoading.remove();
        }

        /*
         * If there are no GPS records, keep the map tiles visible and
         * show the message as an overlay instead of destroying Leaflet.
         */
        if (getGpsRecords().length === 0) {

            const message =
                document.createElement('div');

            message.className = 'map-message';

            message.innerHTML = `
                <i class="fas fa-map-marker-alt fa-3x text-muted mb-3"></i>
                <h6 class="fw-semibold mb-1">
                    No GPS Location Data
                </h6>
                <p class="text-muted small mb-0 px-4 text-center">
                    No staff attendance record with GPS coordinates
                    was found for the selected academic period.
                </p>
            `;

            mapElement.appendChild(message);

            setTimeout(function () {
                if (message.parentNode) {
                    message.remove();
                }
            }, 3500);
        }
    }


    /*
     * ------------------------------------------------------------
     * MAP CONTROLS
     * ------------------------------------------------------------
     */
    function highlightStaff(index) {

        if (!staffListContainer) {
            return;
        }

        staffListContainer
            .querySelectorAll('.staff-list-item')
            .forEach(function (item) {
                item.classList.remove('active');
            });

        const selected =
            staffListContainer.querySelector(
                '[data-marker-index="' + index + '"]'
            );

        if (selected) {

            selected.classList.add('active');

            selected.scrollIntoView({
                behavior: 'smooth',
                block: 'nearest'
            });
        }
    }


    function focusMarker(index) {

        if (!map || !markers[index]) {
            return;
        }

        const marker = markers[index];

        map.setView(
            marker.getLatLng(),
            18,
            {
                animate: true
            }
        );

        marker.openPopup();

        highlightStaff(index);
    }


    function locateOnMap(latitude, longitude) {

        if (!map) {
            return;
        }

        const lat = parseFloat(latitude);
        const lng = parseFloat(longitude);

        if (!Number.isFinite(lat) || !Number.isFinite(lng)) {
            return;
        }

        map.setView(
            [lat, lng],
            18,
            {
                animate: true
            }
        );

        markers.forEach(function (marker, index) {

            const position =
                marker.getLatLng();

            if (
                Math.abs(position.lat - lat) < 0.00001 &&
                Math.abs(position.lng - lng) < 0.00001
            ) {
                marker.openPopup();
                highlightStaff(index);
            }
        });
    }


    function fitAllMarkers(showNotification) {

        if (!map || markers.length === 0) {

            if (showNotification) {
                showToast(
                    'There are no GPS markers to display.',
                    'warning'
                );
            }

            return;
        }

        const bounds =
            L.latLngBounds(
                markers.map(function (marker) {
                    return marker.getLatLng();
                })
            );

        map.fitBounds(
            bounds,
            {
                padding: [40, 40],
                maxZoom: 16,
                animate: true
            }
        );

        if (
            markers.length === 1
        ) {
            map.setZoom(16);
        }

        if (showNotification) {
            showToast(
                'Map fitted to all staff locations.',
                'info'
            );
        }
    }


    function toggleMarkers() {

        if (!map || !markerLayer) {
            return;
        }

        if (markersVisible) {

            markerLayer.removeFrom(map);

            markersVisible = false;

            document.getElementById(
                'toggleMarkersBtn'
            ).innerHTML =
                '<i class="fas fa-eye-slash me-1"></i> Show Markers';

            showToast(
                'Markers hidden.',
                'info'
            );

        } else {

            markerLayer.addTo(map);

            markersVisible = true;

            document.getElementById(
                'toggleMarkersBtn'
            ).innerHTML =
                '<i class="fas fa-eye me-1"></i> Hide Markers';

            showToast(
                'Markers displayed.',
                'info'
            );
        }
    }


    /*
     * ------------------------------------------------------------
     * SEARCH
     * ------------------------------------------------------------
     */
    function searchStaff(value) {

        const term =
            String(value || '')
                .trim()
                .toLowerCase();

        if (!staffListContainer) {
            return;
        }

        staffListContainer
            .querySelectorAll('.staff-list-item')
            .forEach(function (item) {

                const text =
                    item.textContent.toLowerCase();

                item.style.display =
                    !term || text.includes(term)
                        ? ''
                        : 'block';
            });
    }


    /*
     * ------------------------------------------------------------
     * CSV EXPORT
     * ------------------------------------------------------------
     */
    function csvEscape(value) {

        if (
            value === null ||
            value === undefined
        ) {
            return '""';
        }

        return '"' +
            String(value).replace(/"/g, '""') +
            '"';
    }


    function exportData() {

        const gpsRecords =
            getGpsRecords();

        if (gpsRecords.length === 0) {

            showToast(
                'There is no GPS attendance data to export.',
                'warning'
            );

            return;
        }

        let csv =
            'Staff Name,Academic Year,Term,Date,Clock In,Clock Out,Duration,Status,Latitude,Longitude\n';

        gpsRecords.forEach(function (attendance) {

            const status =
                attendance.clock_out_time
                    ? 'Clocked Out'
                    : 'Working';

            csv += [
                csvEscape(getStaffName(attendance)),
                csvEscape(getAcademicYearName(attendance)),
                csvEscape(getTermName(attendance)),
                csvEscape(getAttendanceDate(attendance)),
                csvEscape(formatTime(attendance.clock_in_time)),
                csvEscape(
                    attendance.clock_out_time
                        ? formatTime(attendance.clock_out_time)
                        : 'Not clocked out'
                ),
                csvEscape(duration(attendance)),
                csvEscape(status),
                csvEscape(attendance.clock_in_latitude),
                csvEscape(attendance.clock_in_longitude)
            ].join(',') + '\n';
        });

        const blob =
            new Blob(
                [csv],
                {
                    type: 'text/csv;charset=utf-8;'
                }
            );

        const url =
            URL.createObjectURL(blob);

        const link =
            document.createElement('a');

        link.href = url;

        link.download =
            'staff_attendance_locations_' +
            new Date().toISOString().slice(0, 10) +
            '.csv';

        document.body.appendChild(link);

        link.click();

        link.remove();

        URL.revokeObjectURL(url);

        showToast(
            'Attendance data exported successfully.',
            'success'
        );
    }


    /*
     * ------------------------------------------------------------
     * TOAST
     * ------------------------------------------------------------
     */
    function showToast(message, type) {

        const toast =
            document.createElement('div');

        const icon =
            type === 'success'
                ? 'check-circle'
                : type === 'warning'
                    ? 'exclamation-triangle'
                    : 'info-circle';

        toast.className =
            'toast-notification toast-' + type;

        toast.innerHTML =
            '<i class="fas fa-' + icon + ' me-2"></i>' +
            escapeHtml(message);

        document.body.appendChild(toast);

        setTimeout(function () {
            toast.remove();
        }, 3000);
    }


    /*
     * ------------------------------------------------------------
     * BUTTON EVENTS
     * ------------------------------------------------------------
     */
    const refreshButton =
        document.getElementById('refreshMapBtn');

    if (refreshButton) {
        refreshButton.addEventListener(
            'click',
            function () {

                const form =
                    document.getElementById(
                        'periodFilterForm'
                    );

                if (form) {
                    form.submit();
                } else {
                    window.location.reload();
                }
            }
        );
    }


    const fitButton =
        document.getElementById('fitMapBtn');

    if (fitButton) {
        fitButton.addEventListener(
            'click',
            function () {
                fitAllMarkers(true);
            }
        );
    }


    const toggleButton =
        document.getElementById('toggleMarkersBtn');

    if (toggleButton) {
        toggleButton.addEventListener(
            'click',
            toggleMarkers
        );
    }


    const exportButton =
        document.getElementById('exportBtn');

    if (exportButton) {
        exportButton.addEventListener(
            'click',
            exportData
        );
    }


    document
        .querySelectorAll('.locate-btn')
        .forEach(function (button) {

            button.addEventListener(
                'click',
                function () {

                    locateOnMap(
                        this.dataset.lat,
                        this.dataset.lng
                    );
                }
            );
        });


    const searchInput =
        document.getElementById(
            'staffSearchInput'
        );

    if (searchInput) {
        searchInput.addEventListener(
            'input',
            function () {
                searchStaff(this.value);
            }
        );
    }


    /*
     * ------------------------------------------------------------
     * INITIAL LOAD
     * ------------------------------------------------------------
     */
    function boot() {

        if (
            typeof L === 'undefined'
        ) {

            if (mapElement) {
                showMapMessage(
                    'Map library could not be loaded.',
                    'fa-exclamation-triangle'
                );
            }

            return;
        }

        initializeMap();
    }


    if (
        document.readyState === 'loading'
    ) {
        document.addEventListener(
            'DOMContentLoaded',
            boot
        );
    } else {
        boot();
    }

})();
</script>

@endsection
