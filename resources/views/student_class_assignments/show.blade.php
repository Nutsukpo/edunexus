@extends('layouts.master')

@section('title', 'Assignment Details')

@section('content')

<div class="container-fluid py-4">

    {{-- Header --}}
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4">

        <div>
            <h4 class="fw-bold mb-1">
                <i class="fas fa-file-alt me-2 text-primary"></i>
                Assignment Details
            </h4>

            <p class="text-muted mb-0">
                View the student's class assignment and academic period.
            </p>
        </div>

        <div class="d-flex gap-2">

            <a href="{{ route('student-class-assignments.index') }}"
               class="btn btn-light border">

                <i class="fas fa-arrow-left me-1"></i>
                Back

            </a>

            @if($assignment->status !== 'graduated')

                <a href="{{ route(
                    'student-class-assignments.edit',
                    $assignment->id
                ) }}"
                   class="btn btn-primary">

                    <i class="fas fa-edit me-1"></i>
                    Edit

                </a>

            @endif

        </div>

    </div>


    {{-- Alerts --}}
    @if(session('success'))

        <div class="alert alert-success alert-dismissible fade show">

            <i class="fas fa-check-circle me-2"></i>

            {{ session('success') }}

            <button type="button"
                    class="btn-close"
                    data-bs-dismiss="alert"></button>

        </div>

    @endif

    @if(session('error'))

        <div class="alert alert-danger alert-dismissible fade show">

            <i class="fas fa-exclamation-circle me-2"></i>

            {{ session('error') }}

            <button type="button"
                    class="btn-close"
                    data-bs-dismiss="alert"></button>

        </div>

    @endif


    {{-- Assignment Status Banner --}}
    <div class="card border-0 shadow-sm mb-4">

        <div class="card-body">

            <div class="row align-items-center">

                <div class="col-md-8">

                    <div class="d-flex align-items-center">

                        <div class="student-avatar me-3">

                            {{ strtoupper(
                                substr(
                                    $assignment->student->first_name ?? 'S',
                                    0,
                                    1
                                )
                            ) }}

                        </div>

                        <div>

                            <h5 class="fw-bold mb-1">

                                {{ $assignment->student->first_name ?? '' }}
                                {{ $assignment->student->middle_name ?? '' }}
                                {{ $assignment->student->last_name ?? '' }}

                            </h5>

                            <div class="text-muted">

                                Student ID:
                                <strong>
                                    {{ $assignment->student->student_id ?? 'N/A' }}
                                </strong>

                            </div>

                        </div>

                    </div>

                </div>


                <div class="col-md-4 text-md-end mt-3 mt-md-0">

                    @switch($assignment->status)

                        @case('active')

                            <span class="badge bg-success status-badge">
                                <i class="fas fa-check-circle me-1"></i>
                                Active
                            </span>

                            @break

                        @case('repeated')

                            <span class="badge bg-warning text-dark status-badge">
                                <i class="fas fa-redo me-1"></i>
                                Repeated
                            </span>

                            @break

                        @case('inactive')

                            <span class="badge bg-secondary status-badge">
                                <i class="fas fa-pause-circle me-1"></i>
                                Inactive
                            </span>

                            @break

                        @case('promoted')

                            <span class="badge bg-info text-dark status-badge">
                                <i class="fas fa-arrow-up me-1"></i>
                                Promoted
                            </span>

                            @break

                        @case('graduated')

                            <span class="badge bg-dark status-badge">
                                <i class="fas fa-graduation-cap me-1"></i>
                                Graduated
                            </span>

                            @break

                        @default

                            <span class="badge bg-secondary status-badge">
                                {{ ucfirst($assignment->status ?? 'Unknown') }}
                            </span>

                    @endswitch


                    <div class="mt-2">

                        @if($assignment->is_current)

                            <span class="text-success small fw-semibold">

                                <i class="fas fa-circle me-1"
                                   style="font-size: 7px;"></i>

                                Current Assignment

                            </span>

                        @else

                            <span class="text-muted small">

                                <i class="fas fa-history me-1"></i>

                                Historical Assignment

                            </span>

                        @endif

                    </div>

                </div>

            </div>

        </div>

    </div>


    <div class="row g-4">

        {{-- Assignment Information --}}
        <div class="col-lg-8">

            <div class="card border-0 shadow-sm h-100">

                <div class="card-header bg-white border-bottom py-3">

                    <h6 class="fw-bold mb-0">

                        <i class="fas fa-school me-2 text-primary"></i>

                        Assignment Information

                    </h6>

                </div>

                <div class="card-body">

                    <div class="row g-4">

                        {{-- Class --}}
                        <div class="col-md-6">

                            <div class="info-item">

                                <small class="text-muted d-block mb-1">
                                    Class
                                </small>

                                <div class="fw-semibold fs-6">

                                    {{ $assignment->studentClass->name ?? 'N/A' }}

                                </div>

                                @if(!empty($assignment->studentClass->stream))

                                    <small class="text-muted">
                                        Stream:
                                        {{ $assignment->studentClass->stream }}
                                    </small>

                                @endif

                            </div>

                        </div>


                        {{-- Academic Year --}}
                        <div class="col-md-6">

                            <div class="info-item">

                                <small class="text-muted d-block mb-1">
                                    Academic Year
                                </small>

                                <div class="fw-semibold">

                                    {{ $assignment->academicYear->name ?? 'N/A' }}

                                </div>

                            </div>

                        </div>


                        {{-- Term --}}
                        <div class="col-md-6">

                            <div class="info-item">

                                <small class="text-muted d-block mb-1">
                                    Academic Term
                                </small>

                                @if($assignment->term)

                                    <div class="fw-semibold">

                                        {{ $assignment->term->name }}

                                    </div>

                                @else

                                    <div class="text-warning fw-semibold">

                                        <i class="fas fa-exclamation-triangle me-1"></i>

                                        Not recorded

                                    </div>

                                    <small class="text-muted">
                                        This is a historical record created
                                        before term tracking was enabled.
                                    </small>

                                @endif

                            </div>

                        </div>


                        {{-- Assigned Date --}}
                        <div class="col-md-6">

                            <div class="info-item">

                                <small class="text-muted d-block mb-1">
                                    Assigned Date
                                </small>

                                <div class="fw-semibold">

                                    @if($assignment->assigned_date)

                                        {{ $assignment->assigned_date->format('d M Y') }}

                                    @else

                                        N/A

                                    @endif

                                </div>

                            </div>

                        </div>


                        {{-- Promotion Date --}}
                        <div class="col-md-6">

                            <div class="info-item">

                                <small class="text-muted d-block mb-1">
                                    Promotion Date
                                </small>

                                <div class="fw-semibold">

                                    @if($assignment->promotion_date)

                                        {{ $assignment->promotion_date->format('d M Y') }}

                                    @else

                                        <span class="text-muted">
                                            Not promoted
                                        </span>

                                    @endif

                                </div>

                            </div>

                        </div>


                        {{-- Current Assignment --}}
                        <div class="col-md-6">

                            <div class="info-item">

                                <small class="text-muted d-block mb-1">
                                    Assignment State
                                </small>

                                @if($assignment->is_current)

                                    <span class="badge bg-success-subtle text-success">

                                        <i class="fas fa-check-circle me-1"></i>
                                        Current

                                    </span>

                                @else

                                    <span class="badge bg-light text-muted border">

                                        <i class="fas fa-history me-1"></i>
                                        Historical

                                    </span>

                                @endif

                            </div>

                        </div>

                    </div>

                </div>

            </div>

        </div>


        {{-- Academic Period Card --}}
        <div class="col-lg-4">

            <div class="card border-0 shadow-sm h-100">

                <div class="card-header bg-white border-bottom py-3">

                    <h6 class="fw-bold mb-0">

                        <i class="fas fa-calendar-alt me-2 text-primary"></i>

                        Academic Period

                    </h6>

                </div>

                <div class="card-body">

                    <div class="period-box mb-3">

                        <small class="text-muted d-block mb-1">
                            Academic Year
                        </small>

                        <div class="fw-bold">

                            {{ $assignment->academicYear->name ?? 'N/A' }}

                        </div>

                    </div>


                    <div class="period-box">

                        <small class="text-muted d-block mb-1">
                            Term
                        </small>

                        <div class="fw-bold">

                            @if($assignment->term)

                                {{ $assignment->term->name }}

                            @else

                                <span class="text-muted">
                                    Not recorded
                                </span>

                            @endif

                        </div>

                    </div>


                    {{-- Compare with System Setting --}}
                    <hr class="my-4">

                    <small class="text-muted d-block mb-2">
                        Current System Period
                    </small>

                    @php
                        $settings = \App\Models\SystemSetting::instance();
                    @endphp

                    @if($settings->currentAcademicYear)

                        <div class="small mb-1">

                            <strong>
                                {{ $settings->currentAcademicYear->name }}
                            </strong>

                        </div>

                    @else

                        <div class="small text-danger">
                            Academic year not configured.
                        </div>

                    @endif

                    @if($settings->currentTerm)

                        <div class="small text-muted">

                            {{ $settings->currentTerm->name }}

                        </div>

                    @else

                        <div class="small text-danger">
                            Term not configured.
                        </div>

                    @endif


                    @if(
                        $settings->current_academic_year_id == $assignment->academic_year_id &&
                        $settings->current_term_id == $assignment->term_id
                    )

                        <div class="alert alert-success mt-3 mb-0 py-2 small">

                            <i class="fas fa-check-circle me-1"></i>

                            This assignment belongs to the current
                            system period.

                        </div>

                    @elseif($assignment->is_current)

                        <div class="alert alert-warning mt-3 mb-0 py-2 small">

                            <i class="fas fa-info-circle me-1"></i>

                            This is the student's current assignment,
                            but its academic period differs from the
                            system's configured current period.

                        </div>

                    @endif

                </div>

            </div>

        </div>

    </div>


    {{-- Student Actions --}}
    <div class="card border-0 shadow-sm mt-4">

        <div class="card-header bg-white border-bottom py-3">

            <h6 class="fw-bold mb-0">

                <i class="fas fa-tools me-2 text-primary"></i>

                Assignment Actions

            </h6>

        </div>

        <div class="card-body">

            <div class="d-flex flex-wrap gap-2">

                @if($assignment->status !== 'graduated')

                    @if($assignment->is_current)

                        <form method="POST"
                              action="{{ route(
                                  'student-class-assignments.deactivate',
                                  $assignment->id
                              ) }}">

                            @csrf
                            @method('PATCH')

                            <button type="submit"
                                    class="btn btn-outline-warning"
                                    onclick="return confirm('Deactivate this assignment?')">

                                <i class="fas fa-pause-circle me-1"></i>

                                Deactivate

                            </button>

                        </form>

                    @else

                        <form method="POST"
                              action="{{ route(
                                  'student-class-assignments.reactivate',
                                  $assignment->id
                              ) }}">

                            @csrf
                            @method('PATCH')

                            <button type="submit"
                                    class="btn btn-outline-success"
                                    onclick="return confirm('Reactivate this assignment?')">

                                <i class="fas fa-play-circle me-1"></i>

                                Reactivate

                            </button>

                        </form>

                    @endif


                    <form method="POST"
                          action="{{ route(
                              'student-class-assignments.destroy',
                              $assignment->id
                          ) }}">

                        @csrf
                        @method('DELETE')

                        <button type="submit"
                                class="btn btn-outline-danger"
                                onclick="return confirm('Delete this assignment? This action cannot be undone.')">

                            <i class="fas fa-trash-alt me-1"></i>

                            Delete

                        </button>

                    </form>

                @else

                    <div class="alert alert-dark mb-0 w-100">

                        <i class="fas fa-graduation-cap me-2"></i>

                        This assignment belongs to a graduated student
                        and cannot be modified.

                    </div>

                @endif

            </div>

        </div>

    </div>

</div>

@endsection


@push('styles')

<style>

    .card {
        border-radius: 12px;
    }

    .student-avatar {
        width: 52px;
        height: 52px;
        min-width: 52px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        background: rgba(13, 110, 253, .10);
        color: #0d6efd;
        font-weight: 700;
        font-size: 18px;
    }

    .status-badge {
        font-size: .85rem;
        padding: .55rem .8rem;
    }

    .info-item {
        min-height: 70px;
    }

    .period-box {
        padding: 14px;
        border: 1px solid #e9ecef;
        border-radius: 10px;
        background: #fafbfc;
    }

    .btn {
        border-radius: 8px;
    }

</style>

@endpush