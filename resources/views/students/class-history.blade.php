@extends('students.layouts.app')

@section('title', 'Class History')

@section('content')

<style>
    .history-page {
        padding: 28px 0 50px;
    }

    /* Header */
    .history-header {
        background: linear-gradient(135deg, #0d6efd 0%, #084298 100%);
        border-radius: 18px;
        padding: 30px;
        color: #fff;
        margin-bottom: 24px;
        position: relative;
        overflow: hidden;
    }

    .history-header::after {
        content: "";
        position: absolute;
        width: 220px;
        height: 220px;
        border-radius: 50%;
        background: rgba(255,255,255,.08);
        right: -70px;
        top: -90px;
    }

    .history-header-content {
        position: relative;
        z-index: 2;
    }

    .history-header h3 {
        font-weight: 700;
        margin-bottom: 6px;
    }

    .history-header p {
        margin-bottom: 0;
        opacity: .85;
    }

    .student-badge {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        background: rgba(255,255,255,.15);
        border: 1px solid rgba(255,255,255,.2);
        padding: 8px 14px;
        border-radius: 30px;
        font-size: 13px;
        margin-top: 16px;
    }

    /* Summary cards */
    .summary-card {
        border: 0;
        border-radius: 16px;
        background: #fff;
        box-shadow: 0 5px 22px rgba(0,0,0,.06);
        height: 100%;
        transition: .2s ease;
    }

    .summary-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 28px rgba(0,0,0,.09);
    }

    .summary-card .card-body {
        padding: 22px;
    }

    .summary-icon {
        width: 48px;
        height: 48px;
        border-radius: 14px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 19px;
    }

    .summary-label {
        color: #6c757d;
        font-size: 13px;
        margin-bottom: 5px;
    }

    .summary-number {
        font-size: 27px;
        font-weight: 700;
        line-height: 1;
    }

    /* Main card */
    .history-card {
        border: 0;
        border-radius: 18px;
        box-shadow: 0 5px 25px rgba(0,0,0,.06);
        overflow: hidden;
        background: #fff;
    }

    .history-card-header {
        padding: 22px 24px;
        border-bottom: 1px solid #edf0f3;
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 15px;
    }

    .history-title {
        font-weight: 700;
        font-size: 17px;
        margin-bottom: 4px;
    }

    .history-subtitle {
        color: #6c757d;
        font-size: 13px;
    }

    .record-count {
        background: #f1f5f9;
        color: #475569;
        padding: 7px 12px;
        border-radius: 20px;
        font-size: 12px;
        font-weight: 600;
        white-space: nowrap;
    }

    /* Current class */
    .current-class {
        margin: 22px 24px;
        padding: 20px;
        border-radius: 15px;
        background: #f8fbff;
        border: 1px solid #dbeafe;
    }

    .current-label {
        color: #0d6efd;
        font-size: 11px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: .7px;
        margin-bottom: 7px;
    }

    .current-class-name {
        font-size: 21px;
        font-weight: 700;
        color: #172033;
    }

    .current-meta {
        color: #6c757d;
        font-size: 13px;
        margin-top: 4px;
    }

    /* Timeline */
    .history-list {
        padding: 5px 24px 28px;
    }

    .history-item {
        position: relative;
        display: grid;
        grid-template-columns: 52px 1fr auto;
        gap: 18px;
        padding: 20px 0;
        border-bottom: 1px solid #edf0f3;
    }

    .history-item:last-child {
        border-bottom: 0;
    }

    .history-marker {
        width: 46px;
        height: 46px;
        border-radius: 14px;
        background: #eef5ff;
        color: #0d6efd;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 17px;
    }

    .history-item.current .history-marker {
        background: #e9f8ef;
        color: #198754;
    }

    .history-class {
        font-size: 16px;
        font-weight: 700;
        color: #1f2937;
        margin-bottom: 5px;
    }

    .history-details {
        display: flex;
        flex-wrap: wrap;
        gap: 8px 18px;
        color: #6c757d;
        font-size: 12px;
    }

    .history-details span {
        display: inline-flex;
        align-items: center;
        gap: 5px;
    }

    .history-status {
        text-align: right;
    }

    .status-badge {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        padding: 7px 11px;
        border-radius: 20px;
        font-size: 11px;
        font-weight: 700;
    }

    .status-current {
        background: #e9f8ef;
        color: #198754;
    }

    .status-completed {
        background: #f1f3f5;
        color: #495057;
    }

    /* Empty state */
    .empty-history {
        padding: 70px 25px;
        text-align: center;
    }

    .empty-icon {
        width: 76px;
        height: 76px;
        border-radius: 22px;
        background: #f1f5f9;
        color: #94a3b8;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 18px;
        font-size: 30px;
    }

    .empty-history h5 {
        font-weight: 700;
        color: #334155;
    }

    .empty-history p {
        color: #64748b;
        font-size: 14px;
        margin-bottom: 0;
    }

    /* Responsive */
    @media (max-width: 767px) {

        .history-page {
            padding-top: 18px;
        }

        .history-header {
            padding: 23px;
            border-radius: 15px;
        }

        .history-header h3 {
            font-size: 21px;
        }

        .history-card-header {
            padding: 18px;
        }

        .current-class {
            margin: 18px;
        }

        .history-list {
            padding-left: 18px;
            padding-right: 18px;
        }

        .history-item {
            grid-template-columns: 44px 1fr;
            gap: 12px;
        }

        .history-marker {
            width: 42px;
            height: 42px;
        }

        .history-status {
            grid-column: 2;
            text-align: left;
            margin-top: -4px;
        }

        .history-details {
            gap: 7px 12px;
        }

        .summary-card .card-body {
            padding: 18px;
        }
    }
</style>


<div class="container-fluid history-page">

    {{-- ============================================================
         PAGE HEADER
    ============================================================= --}}
    <div class="history-header">

        <div class="history-header-content">

            <h3>
                <i class="fas fa-graduation-cap me-2"></i>
                Class History
            </h3>

            <p>
                View your academic class progression and current placement.
            </p>

            <div class="student-badge">
                <i class="fas fa-id-card"></i>
                Student ID: {{ $student->student_id }}
            </div>

        </div>

    </div>


    {{-- ============================================================
         SUMMARY
    ============================================================= --}}
    <div class="row g-3 mb-4">

        {{-- Total Classes --}}
        <div class="col-6 col-xl-3">

            <div class="summary-card">

                <div class="card-body">

                    <div class="d-flex justify-content-between align-items-center">

                        <div>

                            <div class="summary-label">
                                Class Records
                            </div>

                            <div class="summary-number">
                                {{ $totalClasses }}
                            </div>

                        </div>

                        <div class="summary-icon bg-primary bg-opacity-10 text-primary">
                            <i class="fas fa-layer-group"></i>
                        </div>

                    </div>

                </div>

            </div>

        </div>


        {{-- Current --}}
        <div class="col-6 col-xl-3">

            <div class="summary-card">

                <div class="card-body">

                    <div class="d-flex justify-content-between align-items-center">

                        <div>

                            <div class="summary-label">
                                Current Class
                            </div>

                            <div class="summary-number text-success">
                                {{ $currentClasses }}
                            </div>

                        </div>

                        <div class="summary-icon bg-success bg-opacity-10 text-success">
                            <i class="fas fa-school"></i>
                        </div>

                    </div>

                </div>

            </div>

        </div>


        {{-- Completed --}}
        <div class="col-6 col-xl-3">

            <div class="summary-card">

                <div class="card-body">

                    <div class="d-flex justify-content-between align-items-center">

                        <div>

                            <div class="summary-label">
                                Completed
                            </div>

                            <div class="summary-number">
                                {{ $completedClasses }}
                            </div>

                        </div>

                        <div class="summary-icon bg-secondary bg-opacity-10 text-secondary">
                            <i class="fas fa-check-double"></i>
                        </div>

                    </div>

                </div>

            </div>

        </div>


        {{-- Academic Years --}}
        <div class="col-6 col-xl-3">

            <div class="summary-card">

                <div class="card-body">

                    <div class="d-flex justify-content-between align-items-center">

                        <div>

                            <div class="summary-label">
                                Academic Years
                            </div>

                            <div class="summary-number text-info">
                                {{ $totalYears }}
                            </div>

                        </div>

                        <div class="summary-icon bg-info bg-opacity-10 text-info">
                            <i class="fas fa-calendar-alt"></i>
                        </div>

                    </div>

                </div>

            </div>

        </div>

    </div>


    {{-- ============================================================
         HISTORY CARD
    ============================================================= --}}
    <div class="history-card">

        <div class="history-card-header">

            <div>

                <div class="history-title">
                    Academic Progression
                </div>

                <div class="history-subtitle">
                    Your complete class assignment history
                </div>

            </div>

            <div class="record-count">
                {{ $classHistory->count() }}
                {{ $classHistory->count() === 1 ? 'Record' : 'Records' }}
            </div>

        </div>


        {{-- ========================================================
             CURRENT CLASS
        ========================================================= --}}
        @if($currentAssignment)

            <div class="current-class">

                <div class="current-label">
                    <i class="fas fa-circle me-1"></i>
                    Current Class
                </div>

                <div class="current-class-name">

                    {{ optional($currentAssignment->studentClass)->name ?? 'Class Not Assigned' }}

                </div>

                <div class="current-meta">

                    @if(optional($currentAssignment->academicYear)->name)

                        <span class="me-3">
                            <i class="fas fa-calendar me-1"></i>
                            {{ $currentAssignment->academicYear->name }}
                        </span>

                    @endif

                    @if($currentAssignment->assigned_date)

                        <span>
                            <i class="fas fa-clock me-1"></i>
                            Assigned
                            {{ \Carbon\Carbon::parse($currentAssignment->assigned_date)->format('d M Y') }}
                        </span>

                    @endif

                </div>

            </div>

        @endif


        {{-- ========================================================
             HISTORY LIST
        ========================================================= --}}
        @if($classHistory->isNotEmpty())

            <div class="history-list">

                @foreach($classHistory as $assignment)

                    @php
                        $isCurrent = (bool) $assignment->is_current;
                        $className = optional($assignment->studentClass)->name ?? 'Class Not Assigned';
                        $academicYear = optional($assignment->academicYear)->name ?? 'Academic Year Not Set';
                    @endphp

                    <div class="history-item {{ $isCurrent ? 'current' : '' }}">

                        {{-- Icon --}}
                        <div class="history-marker">

                            <i class="fas {{ $isCurrent ? 'fa-school' : 'fa-graduation-cap' }}"></i>

                        </div>


                        {{-- Information --}}
                        <div>

                            <div class="history-class">
                                {{ $className }}
                            </div>

                            <div class="history-details">

                                <span>
                                    <i class="fas fa-calendar"></i>
                                    {{ $academicYear }}
                                </span>

                                @if($assignment->assigned_date)

                                    <span>
                                        <i class="fas fa-calendar-plus"></i>
                                        Assigned:
                                        {{ \Carbon\Carbon::parse($assignment->assigned_date)->format('d M Y') }}
                                    </span>

                                @endif

                                @if($assignment->promotion_date)

                                    <span>
                                        <i class="fas fa-arrow-up"></i>
                                        Promotion:
                                        {{ \Carbon\Carbon::parse($assignment->promotion_date)->format('d M Y') }}
                                    </span>

                                @endif

                            </div>

                        </div>


                        {{-- Status --}}
                        <div class="history-status">

                            @if($isCurrent)

                                <span class="status-badge status-current">
                                    <i class="fas fa-circle"></i>
                                    Current
                                </span>

                            @else

                                <span class="status-badge status-completed">
                                    <i class="fas fa-check"></i>
                                    Completed
                                </span>

                            @endif

                        </div>

                    </div>

                @endforeach

            </div>

        @else

            {{-- ====================================================
                 EMPTY STATE
            ==================================================== --}}
            <div class="empty-history">

                <div class="empty-icon">
                    <i class="fas fa-school"></i>
                </div>

                <h5>
                    No Class History Available
                </h5>

                <p>
                    Your class assignments will appear here once they are recorded.
                </p>

            </div>

        @endif

    </div>

</div>

@endsection