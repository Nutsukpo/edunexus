@extends('students.layouts.app')

@section('title', 'My Attendance')

@section('content')

<div class="container-fluid py-4">

    {{-- ============================================================
         HEADER
    ============================================================ --}}
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4">

        <div>
            <h4 class="fw-bold mb-1">
                <i class="fas fa-calendar-check me-2"></i>
                My Attendance
            </h4>

            <p class="text-muted mb-0">
                View your attendance history and performance.
            </p>
        </div>

        <div class="mt-2 mt-md-0">
            <span class="badge bg-light text-dark border px-3 py-2">
                Student ID:
                <strong>{{ $student->student_id }}</strong>
            </span>
        </div>

    </div>


    {{-- ============================================================
         SUMMARY CARDS
    ============================================================ --}}
    <div class="row g-3 mb-4">

        {{-- Total --}}
        <div class="col-6 col-xl-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">

                    <div class="d-flex justify-content-between align-items-center">

                        <div>
                            <small class="text-muted">
                                Total Records
                            </small>

                            <h3 class="fw-bold mb-0 mt-1">
                                {{ $total }}
                            </h3>
                        </div>

                        <div class="bg-primary bg-opacity-10 text-primary rounded-circle p-3">
                            <i class="fas fa-calendar-alt fa-lg"></i>
                        </div>

                    </div>

                </div>
            </div>
        </div>


        {{-- Present --}}
        <div class="col-6 col-xl-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">

                    <div class="d-flex justify-content-between align-items-center">

                        <div>
                            <small class="text-muted">
                                Present
                            </small>

                            <h3 class="fw-bold text-success mb-0 mt-1">
                                {{ $present }}
                            </h3>
                        </div>

                        <div class="bg-success bg-opacity-10 text-success rounded-circle p-3">
                            <i class="fas fa-check-circle fa-lg"></i>
                        </div>

                    </div>

                </div>
            </div>
        </div>


        {{-- Absent --}}
        <div class="col-6 col-xl-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">

                    <div class="d-flex justify-content-between align-items-center">

                        <div>
                            <small class="text-muted">
                                Absent
                            </small>

                            <h3 class="fw-bold text-danger mb-0 mt-1">
                                {{ $absent }}
                            </h3>
                        </div>

                        <div class="bg-danger bg-opacity-10 text-danger rounded-circle p-3">
                            <i class="fas fa-times-circle fa-lg"></i>
                        </div>

                    </div>

                </div>
            </div>
        </div>


        {{-- Attendance Rate --}}
        <div class="col-6 col-xl-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">

                    <div class="d-flex justify-content-between align-items-center">

                        <div>
                            <small class="text-muted">
                                Attendance Rate
                            </small>

                            <h3 class="fw-bold mb-0 mt-1">
                                {{ number_format($attendancePercentage, 1) }}%
                            </h3>
                        </div>

                        <div class="bg-info bg-opacity-10 text-info rounded-circle p-3">
                            <i class="fas fa-chart-pie fa-lg"></i>
                        </div>

                    </div>

                </div>
            </div>
        </div>

    </div>


    {{-- ============================================================
         PERFORMANCE
    ============================================================ --}}
    <div class="card border-0 shadow-sm mb-4">

        <div class="card-body">

            <div class="row align-items-center">

                <div class="col-lg-7">

                    <h6 class="fw-bold mb-1">
                        Attendance Performance
                    </h6>

                    <p class="text-muted small mb-3">
                        Your attendance rate is based on your recorded
                        attendance sessions.
                    </p>

                    <div class="progress" style="height: 10px;">

                        <div
                            class="progress-bar
                            @if($attendancePercentage >= 80)
                                bg-success
                            @elseif($attendancePercentage >= 60)
                                bg-warning
                            @else
                                bg-danger
                            @endif"
                            role="progressbar"
                            style="width: {{ min($attendancePercentage, 100) }}%;"
                            aria-valuenow="{{ $attendancePercentage }}"
                            aria-valuemin="0"
                            aria-valuemax="100"
                        ></div>

                    </div>

                    <div class="d-flex justify-content-between mt-2">
                        <small class="text-muted">0%</small>

                        <small class="fw-semibold">
                            {{ number_format($attendancePercentage, 1) }}%
                        </small>

                        <small class="text-muted">100%</small>
                    </div>

                </div>


                <div class="col-lg-5 mt-4 mt-lg-0">

                    <div class="row text-center">

                        <div class="col-4">
                            <div class="text-warning fw-bold fs-5">
                                {{ $late }}
                            </div>

                            <small class="text-muted">
                                Late
                            </small>
                        </div>

                        <div class="col-4">
                            <div class="text-info fw-bold fs-5">
                                {{ $excused }}
                            </div>

                            <small class="text-muted">
                                Excused
                            </small>
                        </div>

                        <div class="col-4">
                            <div class="text-danger fw-bold fs-5">
                                {{ $absent }}
                            </div>

                            <small class="text-muted">
                                Absent
                            </small>
                        </div>

                    </div>

                </div>

            </div>

        </div>

    </div>


    {{-- ============================================================
         ATTENDANCE HISTORY
    ============================================================ --}}
    {{-- ============================================================
     ATTENDANCE HISTORY
============================================================= --}}
<div class="card border-0 shadow-sm">

<div class="card-header bg-white border-0 py-3">

    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2">

        <div>
            <h6 class="fw-bold mb-1">
                Attendance History
            </h6>

            <small class="text-muted">
                Filter and view your attendance records
            </small>
        </div>

        <i class="fas fa-history text-muted"></i>

    </div>

</div>


{{-- ========================================================
     FILTERS
========================================================= --}}
<div class="card-body border-top">

    <form
        method="GET"
        action="{{ route('students.attendance') }}"
    >

        <div class="row g-3">

            {{-- Status --}}
            <div class="col-md-6 col-lg-3">

                <label class="form-label small fw-semibold">
                    Status
                </label>

                <select
                    name="status"
                    class="form-select"
                >

                    <option
                        value="all"
                        {{ request('status', 'all') === 'all' ? 'selected' : '' }}
                    >
                        All Statuses
                    </option>

                    <option
                        value="present"
                        {{ request('status') === 'present' ? 'selected' : '' }}
                    >
                        Present
                    </option>

                    <option
                        value="absent"
                        {{ request('status') === 'absent' ? 'selected' : '' }}
                    >
                        Absent
                    </option>

                    <option
                        value="late"
                        {{ request('status') === 'late' ? 'selected' : '' }}
                    >
                        Late
                    </option>

                    <option
                        value="excused"
                        {{ request('status') === 'excused' ? 'selected' : '' }}
                    >
                        Excused
                    </option>

                </select>

            </div>


            {{-- Month --}}
            <div class="col-md-6 col-lg-3">

                <label class="form-label small fw-semibold">
                    Month
                </label>

                <select
                    name="month"
                    class="form-select"
                >

                    <option value="">
                        All Months
                    </option>

                    @foreach($months as $month)

                        <option
                            value="{{ $month }}"
                            {{ request('month') === $month ? 'selected' : '' }}
                        >
                            {{ \Carbon\Carbon::createFromFormat('Y-m', $month)->format('F Y') }}
                        </option>

                    @endforeach

                </select>

            </div>


            {{-- Class --}}
            <div class="col-md-6 col-lg-3">

                <label class="form-label small fw-semibold">
                    Class
                </label>

                <select
                    name="class_id"
                    class="form-select"
                >

                    <option value="">
                        All Classes
                    </option>

                    @foreach($classes as $class)

                        <option
                            value="{{ $class->id }}"
                            {{ (string) request('class_id') === (string) $class->id ? 'selected' : '' }}
                        >
                            {{ $class->name }}
                        </option>

                    @endforeach

                </select>

            </div>


            {{-- Date From --}}
            <div class="col-md-6 col-lg-3">

                <label class="form-label small fw-semibold">
                    From Date
                </label>

                <input
                    type="date"
                    name="date_from"
                    value="{{ request('date_from') }}"
                    class="form-control"
                >

            </div>


            {{-- Date To --}}
            <div class="col-md-6 col-lg-3">

                <label class="form-label small fw-semibold">
                    To Date
                </label>

                <input
                    type="date"
                    name="date_to"
                    value="{{ request('date_to') }}"
                    class="form-control"
                >

            </div>


            {{-- Buttons --}}
            <div class="col-md-6 col-lg-3 d-flex align-items-end">

                <button
                    type="submit"
                    class="btn btn-primary w-100"
                >
                    <i class="fas fa-filter me-1"></i>
                    Apply Filters
                </button>

            </div>


            <div class="col-md-6 col-lg-3 d-flex align-items-end">

                <a
                    href="{{ route('students.attendance') }}"
                    class="btn btn-light border w-100"
                >
                    <i class="fas fa-undo me-1"></i>
                    Clear Filters
                </a>

            </div>

        </div>

    </form>

</div>


{{-- ========================================================
     RESULTS
========================================================= --}}
<div class="card-body p-0">

    @if($attendance->count())

        <div class="table-responsive">

            <table class="table table-hover align-middle mb-0">

                <thead class="table-light">

                    <tr>

                        <th class="px-4">
                            #
                        </th>

                        <th>
                            Date
                        </th>

                        <th>
                            Class
                        </th>

                        <th>
                            Status
                        </th>

                        <th>
                            Check-in
                        </th>

                        <th>
                            Remarks
                        </th>

                    </tr>

                </thead>


                <tbody>

                    @foreach($attendance as $record)

                        <tr>

                            <td class="px-4 text-muted">
                                {{ $loop->iteration }}
                            </td>


                            {{-- Date --}}
                            <td>

                                @if($record->session)

                                    <div class="fw-semibold">

                                        {{ \Carbon\Carbon::parse(
                                            $record->session->attendance_date
                                        )->format('d M Y') }}

                                    </div>

                                    <small class="text-muted">

                                        {{ \Carbon\Carbon::parse(
                                            $record->session->attendance_date
                                        )->format('l') }}

                                    </small>

                                @else

                                    <span class="text-muted">
                                        —
                                    </span>

                                @endif

                            </td>


                            {{-- Class --}}
                            <td>

                                @if($record->session?->studentClass)

                                    <span class="fw-semibold">
                                        {{ $record->session->studentClass->name }}
                                    </span>

                                @else

                                    <span class="text-muted">
                                        —
                                    </span>

                                @endif

                            </td>


                            {{-- Status --}}
                            <td>

                                @switch($record->status)

                                    @case('present')

                                        <span class="badge bg-success px-3 py-2">
                                            <i class="fas fa-check me-1"></i>
                                            Present
                                        </span>

                                        @break


                                    @case('absent')

                                        <span class="badge bg-danger px-3 py-2">
                                            <i class="fas fa-times me-1"></i>
                                            Absent
                                        </span>

                                        @break


                                    @case('late')

                                        <span class="badge bg-warning text-dark px-3 py-2">
                                            <i class="fas fa-clock me-1"></i>
                                            Late
                                        </span>

                                        @break


                                    @case('excused')

                                        <span class="badge bg-info px-3 py-2">
                                            <i class="fas fa-info-circle me-1"></i>
                                            Excused
                                        </span>

                                        @break


                                    @default

                                        <span class="badge bg-secondary px-3 py-2">
                                            {{ ucfirst($record->status) }}
                                        </span>

                                @endswitch

                            </td>


                            {{-- Check-in --}}
                            <td>

                                @if($record->created_at)

                                    <span class="text-muted">

                                        <i class="far fa-clock me-1"></i>

                                        {{ \Carbon\Carbon::parse(
                                            $record->created_at
                                        )->format('h:i A') }}

                                    </span>

                                @else

                                    <span class="text-muted">
                                        —
                                    </span>

                                @endif

                            </td>


                            {{-- Remarks --}}
                            <td>

                                @if($record->remarks)

                                    <span
                                        class="text-muted"
                                        title="{{ $record->remarks }}"
                                    >
                                        {{ \Illuminate\Support\Str::limit(
                                            $record->remarks,
                                            50
                                        ) }}
                                    </span>

                                @else

                                    <span class="text-muted">
                                        —
                                    </span>

                                @endif

                            </td>

                        </tr>

                    @endforeach

                </tbody>

            </table>

        </div>


        {{-- Result count --}}
        <div class="px-4 py-3 border-top">

            <small class="text-muted">

                Showing
                <strong>{{ $attendance->count() }}</strong>
                attendance
                {{ $attendance->count() === 1 ? 'record' : 'records' }}

            </small>

        </div>


    @else

        <div class="text-center py-5">

            <div class="mb-3">
                <i class="fas fa-calendar-times fa-3x text-muted"></i>
            </div>

            <h6 class="fw-bold">
                No attendance records found
            </h6>

            <p class="text-muted mb-0">
                No attendance records match your selected filters.
            </p>

        </div>

    @endif

</div>

</div>

</div>

@endsection