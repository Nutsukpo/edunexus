@extends('students.layouts.app')

@section('title', 'Student Dashboard')

@section('content')

<div class="student-dashboard">

    {{-- =========================================================
         HEADER
    ========================================================== --}}
    <section class="dashboard-header">

        <div class="container-fluid">

            <div class="header-content">

                <div class="student-introduction">

                    <div class="student-avatar">

                        @if(!empty($student->photo))

                            <img
                                src="{{ asset('storage/' . $student->photo) }}"
                                alt="Student Photo"
                            >

                        @else

                            <i class="fas fa-user-graduate"></i>

                        @endif

                    </div>


                    <div>

                        <div class="welcome-label">
                            STUDENT PORTAL
                        </div>

                        <h1>
                            Welcome back,
                            {{ $student->first_name ?? $student->student_id }}
                        </h1>


                        <div class="student-meta">

                            <span>
                                <i class="fas fa-id-card"></i>
                                {{ $student->student_id }}
                            </span>


                            <span>
                                <i class="fas fa-school"></i>
                                {{ $assignment?->studentClass?->name ?? 'Class not assigned' }}
                            </span>


                            <span>
                                <i class="fas fa-calendar"></i>
                                {{ $academicYear?->name ?? 'Academic year not configured' }}
                            </span>

                        </div>

                    </div>

                </div>


                {{-- =================================================
                     ADMIN CONTROLLED TERM
                ================================================== --}}
                <div class="term-display">

                    <span class="term-label">
                        CURRENT SCHOOL TERM
                    </span>

                    <strong>
                        {{ $term?->name ?? '—' }}
                    </strong>

                    <small>
                        {{ $academicYear?->name ?? '—' }}
                    </small>

                    <small class="portal-period-note">
                        <i class="fas fa-shield-halved"></i>
                        School period
                    </small>

                </div>

            </div>

        </div>

    </section>


    {{-- =========================================================
         MAIN CONTENT
    ========================================================== --}}
    <div class="container-fluid dashboard-container">


        {{-- =====================================================
             PERIOD STATUS
        ====================================================== --}}
        <section class="period-banner">

            <div class="period-banner-icon">
                <i class="fas fa-calendar-check"></i>
            </div>

            <div>

                <span>
                    SCHOOL OPERATIONAL PERIOD
                </span>

                <strong>
                    {{ $academicYear?->name ?? 'Not configured' }}
                    @if($term)
                        — {{ $term->name }}
                    @endif
                </strong>

            </div>

        </section>


        {{-- =====================================================
             KPI ROW
        ====================================================== --}}
        <section class="kpi-grid">

            {{-- Attendance --}}
            <div class="kpi-card attendance-card">

                <div class="kpi-top">

                    <div>

                        <span class="kpi-label">
                            ATTENDANCE RATE
                        </span>

                        <div class="kpi-value">

                            {{ number_format(
                                $attendancePercentage ?? 0,
                                1
                            ) }}<small>%</small>

                        </div>

                    </div>


                    <div class="kpi-icon">
                        <i class="fas fa-calendar-check"></i>
                    </div>

                </div>


                <div class="kpi-footer">

                    <span>
                        <strong>
                            {{ ($present ?? 0) + ($late ?? 0) }}
                        </strong>
                        attended
                    </span>

                    <span>
                        {{ $total ?? 0 }} sessions
                    </span>

                </div>


                <div class="kpi-progress">

                    <div
                        style="width: {{ min(
                            (float)($attendancePercentage ?? 0),
                            100
                        ) }}%">
                    </div>

                </div>

            </div>


            {{-- Fee Balance --}}
            <div class="kpi-card fee-card">

                <div class="kpi-top">

                    <div>

                        <span class="kpi-label">
                            FEE BALANCE
                        </span>

                        <div class="kpi-value currency
                            {{ ($feeBalance ?? 0) > 0
                                ? 'balance-due'
                                : 'balance-clear' }}">

                            GH₵
                            {{ number_format(
                                $feeBalance ?? 0,
                                2
                            ) }}

                        </div>

                    </div>


                    <div class="kpi-icon">
                        <i class="fas fa-wallet"></i>
                    </div>

                </div>


                <div class="kpi-footer">

                    <span>
                        Paid

                        <strong>
                            GH₵
                            {{ number_format(
                                $amountPaid ?? 0,
                                2
                            ) }}
                        </strong>

                    </span>


                    <a href="{{ route('students.fees') }}">

                        View account

                        <i class="fas fa-arrow-right"></i>

                    </a>

                </div>

            </div>


            {{-- Subjects --}}
            <div class="kpi-card subject-card">

                <div class="kpi-top">

                    <div>

                        <span class="kpi-label">
                            SUBJECTS
                        </span>

                        <div class="kpi-value">
                            {{ $subjects ?? 0 }}
                        </div>

                    </div>


                    <div class="kpi-icon">
                        <i class="fas fa-book-open"></i>
                    </div>

                </div>


                <div class="kpi-footer">

                    <span>
                        Currently enrolled
                    </span>


                    <a href="{{ route('students.results') }}">

                        Results

                        <i class="fas fa-arrow-right"></i>

                    </a>

                </div>

            </div>


            {{-- Sessions --}}
            <div class="kpi-card session-card">

                <div class="kpi-top">

                    <div>

                        <span class="kpi-label">
                            TOTAL SESSIONS
                        </span>

                        <div class="kpi-value">
                            {{ $total ?? 0 }}
                        </div>

                    </div>


                    <div class="kpi-icon">
                        <i class="fas fa-chart-column"></i>
                    </div>

                </div>


                <div class="kpi-footer">

                    <span>

                        <strong>
                            {{ $present ?? 0 }}
                        </strong>

                        present

                    </span>


                    <a href="{{ route('students.attendance') }}">

                        Attendance

                        <i class="fas fa-arrow-right"></i>

                    </a>

                </div>

            </div>

        </section>


        {{-- =====================================================
             MAIN TWO COLUMN AREA
        ====================================================== --}}
        <section class="dashboard-grid">


            {{-- =================================================
                 ATTENDANCE
            ================================================== --}}
            <div class="dashboard-panel">

                <div class="panel-header">

                    <div>

                        <span class="panel-eyebrow">
                            ATTENDANCE
                        </span>

                        <h2>
                            Attendance Overview
                        </h2>

                        <p>
                            Your attendance performance for
                            {{ $term?->name ?? 'the current period' }}.
                        </p>

                    </div>


                    <a
                        href="{{ route('students.attendance') }}"
                        class="panel-action"
                    >

                        View details

                        <i class="fas fa-arrow-right"></i>

                    </a>

                </div>


                <div class="attendance-content">

                    <div class="attendance-circle">

                        <div class="circle-inner">

                            <strong>
                                {{ number_format(
                                    $attendancePercentage ?? 0,
                                    1
                                ) }}%
                            </strong>

                            <span>
                                Attendance
                            </span>

                        </div>

                    </div>


                    <div class="attendance-stats">

                        <div class="attendance-stat present">

                            <span class="stat-dot"></span>

                            <div>

                                <strong>
                                    {{ $present ?? 0 }}
                                </strong>

                                <small>
                                    Present
                                </small>

                            </div>

                        </div>


                        <div class="attendance-stat late">

                            <span class="stat-dot"></span>

                            <div>

                                <strong>
                                    {{ $late ?? 0 }}
                                </strong>

                                <small>
                                    Late
                                </small>

                            </div>

                        </div>


                        <div class="attendance-stat absent">

                            <span class="stat-dot"></span>

                            <div>

                                <strong>
                                    {{ $absent ?? 0 }}
                                </strong>

                                <small>
                                    Absent
                                </small>

                            </div>

                        </div>


                        <div class="attendance-stat excused">

                            <span class="stat-dot"></span>

                            <div>

                                <strong>
                                    {{ $excused ?? 0 }}
                                </strong>

                                <small>
                                    Excused
                                </small>

                            </div>

                        </div>

                    </div>

                </div>

            </div>


            {{-- =================================================
                 FEE ACCOUNT
            ================================================== --}}
            <div class="dashboard-panel">

                <div class="panel-header">

                    <div>

                        <span class="panel-eyebrow">
                            FINANCIAL ACCOUNT
                        </span>

                        <h2>
                            Fee Account
                        </h2>

                        <p>
                            Current school fee position.
                        </p>

                    </div>


                    <a
                        href="{{ route('students.fees') }}"
                        class="panel-action"
                    >

                        Manage

                        <i class="fas fa-arrow-right"></i>

                    </a>

                </div>


                @if($feeAccount)

                    <div class="fee-summary">

                        <div class="fee-main">

                            <span>
                                OUTSTANDING BALANCE
                            </span>

                            <strong class="
                                {{ ($feeBalance ?? 0) > 0
                                    ? 'balance-due'
                                    : 'balance-clear' }}
                            ">

                                GH₵
                                {{ number_format(
                                    $feeBalance ?? 0,
                                    2
                                ) }}

                            </strong>


                            @if(($feeBalance ?? 0) <= 0)

                                <small class="status-clear">

                                    <i class="fas fa-circle-check"></i>

                                    Account fully settled

                                </small>

                            @else

                                <small class="status-due">

                                    <i class="fas fa-circle-exclamation"></i>

                                    Balance outstanding

                                </small>

                            @endif

                        </div>


                        <div class="fee-breakdown">

                            <div>

                                <span>
                                    Total Fees
                                </span>

                                <strong>
                                    GH₵
                                    {{ number_format(
                                        $totalFees ?? 0,
                                        2
                                    ) }}
                                </strong>

                            </div>


                            <div>

                                <span>
                                    Amount Paid
                                </span>

                                <strong class="paid-amount">
                                    GH₵
                                    {{ number_format(
                                        $amountPaid ?? 0,
                                        2
                                    ) }}
                                </strong>

                            </div>

                        </div>


                        @php

                            $feePercentage =
                                ($totalFees ?? 0) > 0

                                    ? min(
                                        100,
                                        (
                                            ($amountPaid ?? 0)
                                            / $totalFees
                                        ) * 100
                                    )

                                    : 0;

                        @endphp


                        <div class="fee-progress">

                            <div class="fee-progress-label">

                                <span>
                                    Payment progress
                                </span>

                                <strong>
                                    {{ number_format(
                                        $feePercentage,
                                        1
                                    ) }}%
                                </strong>

                            </div>


                            <div class="progress-track">

                                <div
                                    class="progress-fill"
                                    style="width: {{ $feePercentage }}%"
                                ></div>

                            </div>

                        </div>

                    </div>

                @else

                    <div class="empty-state">

                        <i class="fas fa-file-invoice-dollar"></i>

                        <h3>
                            No fee account
                        </h3>

                        <p>
                            No fee account has been allocated to you yet.
                        </p>

                    </div>

                @endif

            </div>

        </section>


        {{-- =====================================================
             LOWER GRID
        ====================================================== --}}
        <section class="lower-grid">


            {{-- =================================================
                 ACADEMIC INFORMATION
            ================================================== --}}
            <div class="dashboard-panel academic-panel">

                <div class="panel-header">

                    <div>

                        <span class="panel-eyebrow">
                            ACADEMIC PROFILE
                        </span>

                        <h2>
                            My Academic Information
                        </h2>

                    </div>

                </div>


                <div class="academic-list">

                    <div class="academic-row">

                        <div class="academic-icon">
                            <i class="fas fa-graduation-cap"></i>
                        </div>

                        <div>

                            <span>
                                Current Class
                            </span>

                            <strong>
                                {{ $assignment?->studentClass?->name ?? 'Not assigned' }}
                            </strong>

                        </div>

                    </div>


                    <div class="academic-row">

                        <div class="academic-icon">
                            <i class="fas fa-calendar-days"></i>
                        </div>

                        <div>

                            <span>
                                Academic Year
                            </span>

                            <strong>
                                {{ $academicYear?->name ?? '—' }}
                            </strong>

                        </div>

                    </div>


                    <div class="academic-row">

                        <div class="academic-icon">
                            <i class="fas fa-layer-group"></i>
                        </div>

                        <div>

                            <span>
                                Current Term
                            </span>

                            <strong>
                                {{ $term?->name ?? '—' }}
                            </strong>

                        </div>

                    </div>


                    <div class="academic-row">

                        <div class="academic-icon">
                            <i class="fas fa-book"></i>
                        </div>

                        <div>

                            <span>
                                Enrolled Subjects
                            </span>

                            <strong>
                                {{ $subjects ?? 0 }}
                            </strong>

                        </div>

                    </div>

                </div>

            </div>


            {{-- =================================================
                 ANNOUNCEMENTS
            ================================================== --}}
            <div class="dashboard-panel">

                <div class="panel-header">

                    <div>

                        <span class="panel-eyebrow">
                            SCHOOL UPDATES
                        </span>

                        <h2>
                            Announcements
                        </h2>

                    </div>

                </div>


                <div class="announcement-list">

                    @forelse($announcements as $announcement)

                        <div class="announcement-item">

                            <div class="announcement-icon">
                                <i class="fas fa-bullhorn"></i>
                            </div>


                            <div class="announcement-body">

                                <h3>
                                    {{ $announcement->title }}
                                </h3>


                                @if($announcement->content)

                                    <p>
                                        {{ \Illuminate\Support\Str::limit(
                                            strip_tags(
                                                $announcement->content
                                            ),
                                            110
                                        ) }}
                                    </p>

                                @endif


                                <small>

                                    <i class="far fa-calendar"></i>

                                    {{ $announcement->created_at?->format('d M Y') }}

                                </small>

                            </div>

                        </div>

                    @empty

                        <div class="empty-state compact">

                            <i class="fas fa-bell-slash"></i>

                            <p>
                                No announcements available.
                            </p>

                        </div>

                    @endforelse

                </div>

            </div>


            {{-- =================================================
                 RECENT RESULTS
            ================================================== --}}
            <div class="dashboard-panel">

                <div class="panel-header">

                    <div>

                        <span class="panel-eyebrow">
                            ACADEMIC PERFORMANCE
                        </span>

                        <h2>
                            Recent Results
                        </h2>

                    </div>


                    @if(
                        isset($recentResults) &&
                        count($recentResults)
                    )

                        <a
                            href="{{ route('students.results') }}"
                            class="panel-action"
                        >

                            All results

                            <i class="fas fa-arrow-right"></i>

                        </a>

                    @endif

                </div>


                <div class="results-list">

                    @if(
                        isset($recentResults) &&
                        count($recentResults)
                    )

                        @foreach($recentResults as $result)

                            @php

                                $score =
                                    $result->score ?? 0;

                                $grade =
                                    $result->grade ??
                                    (
                                        $score >= 80
                                            ? 'A'
                                            : (
                                                $score >= 70
                                                    ? 'B'
                                                    : (
                                                        $score >= 60
                                                            ? 'C'
                                                            : (
                                                                $score >= 50
                                                                    ? 'D'
                                                                    : 'F'
                                                            )
                                                    )
                                            )
                                    );

                            @endphp


                            <div class="result-item">

                                <div class="result-subject">

                                    <div class="subject-icon">
                                        <i class="fas fa-book-open"></i>
                                    </div>


                                    <div>

                                        <strong>
                                            {{ $result->subject?->name ?? 'Subject' }}
                                        </strong>

                                        <small>
                                            {{ $result->assessment?->name ?? 'Assessment' }}
                                        </small>

                                    </div>

                                </div>


                                <div class="result-score">

                                    <strong>
                                        {{ $score }}%
                                    </strong>


                                    <span class="
                                        grade
                                        grade-{{ strtolower($grade) }}
                                    ">
                                        {{ $grade }}
                                    </span>

                                </div>

                            </div>

                        @endforeach

                    @else

                        <div class="empty-state compact">

                            <i class="fas fa-chart-line"></i>

                            <p>
                                No results available yet.
                            </p>

                        </div>

                    @endif

                </div>

            </div>

        </section>

    </div>

</div>


{{-- =============================================================
     DESIGN CSS
============================================================== --}}
@push('styles')

<style>

:root {
    --dashboard-primary: #173f5f;
    --dashboard-secondary: #20639b;
    --dashboard-success: #2e8b57;
    --dashboard-warning: #d99a00;
    --dashboard-danger: #c0392b;
    --dashboard-text: #172b4d;
    --dashboard-muted: #6b778c;
    --dashboard-border: #e6e9ef;
    --dashboard-background: #f5f7fa;
}


/* =============================================================
   BASE
============================================================= */

.student-dashboard {
    background: var(--dashboard-background);
    min-height: calc(100vh - 70px);
    color: var(--dashboard-text);
}


/* =============================================================
   HEADER
============================================================= */

.dashboard-header {
    background: #ffffff;
    border-bottom: 1px solid var(--dashboard-border);
}

.header-content {
    min-height: 190px;
    padding: 36px 10px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 30px;
}

.student-introduction {
    display: flex;
    align-items: center;
    gap: 20px;
}

.student-avatar {
    width: 76px;
    height: 76px;
    border-radius: 18px;
    background: var(--dashboard-primary);
    color: #ffffff;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 28px;
    overflow: hidden;
    flex-shrink: 0;
}

.student-avatar img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.welcome-label {
    font-size: 11px;
    letter-spacing: 1.5px;
    font-weight: 700;
    color: var(--dashboard-secondary);
    margin-bottom: 5px;
}

.student-introduction h1 {
    font-size: 28px;
    font-weight: 700;
    margin: 0 0 12px;
}

.student-meta {
    display: flex;
    flex-wrap: wrap;
    gap: 18px;
}

.student-meta span {
    color: var(--dashboard-muted);
    font-size: 13px;
}

.student-meta i {
    color: var(--dashboard-secondary);
    margin-right: 5px;
}

.term-display {
    text-align: right;
    min-width: 190px;
}

.term-label {
    display: block;
    color: var(--dashboard-muted);
    font-size: 10px;
    letter-spacing: 1.3px;
    font-weight: 700;
    margin-bottom: 4px;
}

.term-display strong {
    display: block;
    font-size: 17px;
}

.term-display small {
    display: block;
    color: var(--dashboard-muted);
    font-size: 12px;
    margin-top: 2px;
}

.portal-period-note {
    color: var(--dashboard-secondary) !important;
    font-size: 10px !important;
    margin-top: 7px !important;
}


/* =============================================================
   PERIOD BANNER
============================================================= */

.period-banner {
    display: flex;
    align-items: center;
    gap: 14px;
    background: #ffffff;
    border: 1px solid var(--dashboard-border);
    padding: 14px 18px;
    margin-bottom: 22px;
}

.period-banner-icon {
    width: 42px;
    height: 42px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: #edf5fb;
    color: var(--dashboard-secondary);
    flex-shrink: 0;
}

.period-banner span {
    display: block;
    color: var(--dashboard-muted);
    font-size: 9px;
    letter-spacing: 1.2px;
    font-weight: 700;
}

.period-banner strong {
    display: block;
    margin-top: 3px;
    font-size: 14px;
}


/* =============================================================
   CONTAINER
============================================================= */

.dashboard-container {
    padding-top: 28px;
    padding-bottom: 50px;
}


/* =============================================================
   KPI
============================================================= */

.kpi-grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 18px;
    margin-bottom: 22px;
}

.kpi-card {
    background: #ffffff;
    border: 1px solid var(--dashboard-border);
    padding: 22px;
    position: relative;
    min-height: 155px;
    transition: .2s ease;
}

.kpi-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 24px rgba(23, 43, 77, .08);
}

.kpi-card::before {
    content: "";
    position: absolute;
    left: 0;
    top: 0;
    bottom: 0;
    width: 4px;
}

.attendance-card::before {
    background: var(--dashboard-success);
}

.fee-card::before {
    background: var(--dashboard-warning);
}

.subject-card::before {
    background: var(--dashboard-secondary);
}

.session-card::before {
    background: #6857c7;
}

.kpi-top {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
}

.kpi-label {
    color: var(--dashboard-muted);
    font-size: 10px;
    letter-spacing: 1px;
    font-weight: 700;
}

.kpi-value {
    font-size: 29px;
    font-weight: 750;
    margin-top: 5px;
    line-height: 1.2;
}

.kpi-value small {
    font-size: 16px;
}

.currency {
    font-size: 21px;
}

.kpi-icon {
    width: 43px;
    height: 43px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: #f1f4f8;
    color: var(--dashboard-primary);
}

.kpi-footer {
    margin-top: 17px;
    display: flex;
    justify-content: space-between;
    gap: 10px;
    color: var(--dashboard-muted);
    font-size: 11px;
}

.kpi-footer strong {
    color: var(--dashboard-text);
}

.kpi-footer a {
    color: var(--dashboard-secondary);
    text-decoration: none;
    font-weight: 600;
}

.kpi-footer i {
    font-size: 9px;
}

.kpi-progress {
    margin-top: 10px;
    height: 4px;
    background: #edf0f3;
}

.kpi-progress div {
    height: 100%;
    background: var(--dashboard-success);
}

.balance-due {
    color: var(--dashboard-danger);
}

.balance-clear {
    color: var(--dashboard-success);
}


/* =============================================================
   PANELS
============================================================= */

.dashboard-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 22px;
    margin-bottom: 22px;
}

.dashboard-panel {
    background: #ffffff;
    border: 1px solid var(--dashboard-border);
    padding: 25px;
}

.panel-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    gap: 15px;
    margin-bottom: 25px;
}

.panel-eyebrow {
    display: block;
    color: var(--dashboard-secondary);
    font-size: 9px;
    font-weight: 750;
    letter-spacing: 1.5px;
    margin-bottom: 5px;
}

.panel-header h2 {
    font-size: 18px;
    font-weight: 700;
    margin: 0;
}

.panel-header p {
    color: var(--dashboard-muted);
    font-size: 12px;
    margin: 5px 0 0;
}

.panel-action {
    color: var(--dashboard-secondary);
    font-size: 11px;
    font-weight: 650;
    text-decoration: none;
    white-space: nowrap;
}

.panel-action i {
    font-size: 9px;
    margin-left: 4px;
}


/* =============================================================
   ATTENDANCE
============================================================= */

.attendance-content {
    display: flex;
    align-items: center;
    gap: 45px;
}

.attendance-circle {
    width: 155px;
    height: 155px;
    border-radius: 50%;
    background:
        conic-gradient(
            var(--dashboard-success)
            {{ min(
                (float)($attendancePercentage ?? 0),
                100
            ) }}%,
            #edf1f4 0
        );
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}

.circle-inner {
    width: 118px;
    height: 118px;
    border-radius: 50%;
    background: #ffffff;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
}

.circle-inner strong {
    font-size: 25px;
    font-weight: 750;
}

.circle-inner span {
    color: var(--dashboard-muted);
    font-size: 10px;
}

.attendance-stats {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 18px 25px;
    width: 100%;
}

.attendance-stat {
    display: flex;
    align-items: center;
    gap: 10px;
}

.stat-dot {
    width: 9px;
    height: 9px;
    border-radius: 50%;
    flex-shrink: 0;
}

.present .stat-dot {
    background: var(--dashboard-success);
}

.late .stat-dot {
    background: var(--dashboard-warning);
}

.absent .stat-dot {
    background: var(--dashboard-danger);
}

.excused .stat-dot {
    background: #3688b8;
}

.attendance-stat strong {
    display: block;
    font-size: 18px;
}

.attendance-stat small {
    color: var(--dashboard-muted);
    font-size: 10px;
}


/* =============================================================
   FEES
============================================================= */

.fee-main {
    padding: 17px;
    background: #f7f9fb;
    border-left: 3px solid var(--dashboard-warning);
}

.fee-main > span {
    display: block;
    color: var(--dashboard-muted);
    font-size: 9px;
    font-weight: 700;
    letter-spacing: 1px;
}

.fee-main strong {
    display: block;
    font-size: 28px;
    margin: 5px 0;
}

.fee-main small {
    font-size: 11px;
}

.status-clear {
    color: var(--dashboard-success);
}

.status-due {
    color: var(--dashboard-danger);
}

.fee-breakdown {
    display: grid;
    grid-template-columns: 1fr 1fr;
    border: 1px solid var(--dashboard-border);
    border-top: 0;
}

.fee-breakdown > div {
    padding: 15px;
}

.fee-breakdown > div + div {
    border-left: 1px solid var(--dashboard-border);
}

.fee-breakdown span {
    display: block;
    color: var(--dashboard-muted);
    font-size: 10px;
}

.fee-breakdown strong {
    display: block;
    margin-top: 4px;
    font-size: 14px;
}

.paid-amount {
    color: var(--dashboard-success);
}

.fee-progress {
    margin-top: 20px;
}

.fee-progress-label {
    display: flex;
    justify-content: space-between;
    margin-bottom: 7px;
    font-size: 10px;
    color: var(--dashboard-muted);
}

.fee-progress-label strong {
    color: var(--dashboard-text);
}

.progress-track {
    height: 6px;
    background: #e9edf1;
}

.progress-fill {
    height: 100%;
    background: var(--dashboard-success);
}


/* =============================================================
   LOWER GRID
============================================================= */

.lower-grid {
    display: grid;
    grid-template-columns: 1fr 1fr 1fr;
    gap: 22px;
}


/* =============================================================
   ACADEMIC
============================================================= */

.academic-list {
    border-top: 1px solid var(--dashboard-border);
}

.academic-row {
    display: flex;
    align-items: center;
    gap: 14px;
    padding: 14px 0;
    border-bottom: 1px solid var(--dashboard-border);
}

.academic-icon {
    width: 36px;
    height: 36px;
    background: #f1f5f9;
    color: var(--dashboard-secondary);
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}

.academic-row span {
    display: block;
    color: var(--dashboard-muted);
    font-size: 10px;
}

.academic-row strong {
    display: block;
    margin-top: 3px;
    font-size: 13px;
}


/* =============================================================
   ANNOUNCEMENTS
============================================================= */

.announcement-item {
    display: flex;
    gap: 13px;
    padding: 13px 0;
    border-bottom: 1px solid var(--dashboard-border);
}

.announcement-item:first-child {
    padding-top: 0;
}

.announcement-item:last-child {
    border-bottom: 0;
}

.announcement-icon {
    width: 35px;
    height: 35px;
    background: #fff5dc;
    color: #bd8200;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}

.announcement-body h3 {
    font-size: 13px;
    font-weight: 650;
    margin: 0 0 4px;
}

.announcement-body p {
    color: var(--dashboard-muted);
    font-size: 11px;
    margin: 0 0 5px;
    line-height: 1.5;
}

.announcement-body small {
    color: #8993a4;
    font-size: 9px;
}


/* =============================================================
   RESULTS
============================================================= */

.result-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 13px 0;
    border-bottom: 1px solid var(--dashboard-border);
}

.result-item:first-child {
    padding-top: 0;
}

.result-item:last-child {
    border-bottom: 0;
}

.result-subject {
    display: flex;
    align-items: center;
    gap: 11px;
}

.subject-icon {
    width: 34px;
    height: 34px;
    background: #edf5fb;
    color: var(--dashboard-secondary);
    display: flex;
    align-items: center;
    justify-content: center;
}

.result-subject strong {
    display: block;
    font-size: 12px;
}

.result-subject small {
    display: block;
    color: var(--dashboard-muted);
    font-size: 9px;
    margin-top: 3px;
}

.result-score {
    display: flex;
    align-items: center;
    gap: 10px;
}

.result-score > strong {
    font-size: 12px;
}

.grade {
    min-width: 28px;
    padding: 5px 7px;
    text-align: center;
    font-size: 10px;
    font-weight: 700;
}

.grade-a,
.grade-b {
    background: #e5f5eb;
    color: var(--dashboard-success);
}

.grade-c,
.grade-d {
    background: #fff4d9;
    color: #a97500;
}

.grade-f {
    background: #fde8e7;
    color: var(--dashboard-danger);
}


/* =============================================================
   EMPTY STATE
============================================================= */

.empty-state {
    min-height: 190px;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    text-align: center;
}

.empty-state i {
    color: #aeb7c3;
    font-size: 27px;
    margin-bottom: 10px;
}

.empty-state h3 {
    font-size: 14px;
    margin: 0 0 4px;
}

.empty-state p {
    color: var(--dashboard-muted);
    font-size: 11px;
    margin: 0;
}

.empty-state.compact {
    min-height: 130px;
}


/* =============================================================
   RESPONSIVE
============================================================= */

@media (max-width: 1200px) {

    .kpi-grid {
        grid-template-columns: repeat(2, 1fr);
    }

    .lower-grid {
        grid-template-columns: 1fr 1fr;
    }

    .academic-panel {
        grid-column: span 2;
    }

}


@media (max-width: 900px) {

    .dashboard-grid {
        grid-template-columns: 1fr;
    }

    .lower-grid {
        grid-template-columns: 1fr;
    }

    .academic-panel {
        grid-column: auto;
    }

    .header-content {
        align-items: flex-start;
        flex-direction: column;
    }

    .term-display {
        text-align: left;
    }

}


@media (max-width: 600px) {

    .dashboard-container {
        padding-top: 18px;
    }

    .header-content {
        padding: 25px 5px;
    }

    .student-introduction {
        align-items: flex-start;
    }

    .student-avatar {
        width: 58px;
        height: 58px;
        font-size: 21px;
    }

    .student-introduction h1 {
        font-size: 21px;
    }

    .student-meta {
        display: block;
    }

    .student-meta span {
        display: block;
        margin-bottom: 5px;
    }

    .kpi-grid {
        grid-template-columns: 1fr;
    }

    .dashboard-panel {
        padding: 19px;
    }

    .attendance-content {
        flex-direction: column;
        gap: 25px;
    }

    .attendance-stats {
        width: 100%;
    }

    .panel-header {
        flex-direction: column;
    }

    .period-banner {
        align-items: flex-start;
    }

}

</style>

@endpush

@endsection