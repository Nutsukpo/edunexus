@extends('layouts.master')

@section('title', 'Open Academic Period')

@section('content')
<div class="container-fluid py-4">

    {{-- ================================================================
         PAGE HEADER
    ================================================================= --}}
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4">
        <div>
            <h4 class="font-weight-bold text-dark mb-1">
                <i class="fas fa-calendar-check text-primary mr-2"></i>
                Open Academic Period
            </h4>

            <p class="text-muted mb-0">
                Review and activate student assignments for the operational period.
            </p>
        </div>

        <a href="{{ route('student-class-assignments.index') }}"
           class="btn btn-outline-secondary">
            <i class="fas fa-users mr-1"></i>
            Student Assignments
        </a>
    </div>


    {{-- ================================================================
         FLASH MESSAGES
    ================================================================= --}}
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show">
            <i class="fas fa-check-circle mr-2"></i>
            {{ session('success') }}

            <button type="button"
                    class="close"
                    data-dismiss="alert">
                <span>&times;</span>
            </button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show">
            <i class="fas fa-exclamation-circle mr-2"></i>
            {{ session('error') }}

            <button type="button"
                    class="close"
                    data-dismiss="alert">
                <span>&times;</span>
            </button>
        </div>
    @endif


    {{-- ================================================================
         OPERATIONAL PERIOD
    ================================================================= --}}
    <div class="card shadow-sm border-0 mb-4">

        <div class="card-header bg-white py-3">
            <h6 class="font-weight-bold mb-0">
                <i class="fas fa-calendar-alt text-primary mr-2"></i>
                Current Operational Period
            </h6>
        </div>

        <div class="card-body">

            <div class="row">

                {{-- Academic Year --}}
                <div class="col-md-4 mb-3 mb-md-0">
                    <small class="text-muted d-block text-uppercase font-weight-bold">
                        Academic Year
                    </small>

                    <div class="h5 font-weight-bold mb-0">
                        {{ $academicYear->name }}
                    </div>
                </div>


                {{-- Term --}}
                <div class="col-md-4 mb-3 mb-md-0">
                    <small class="text-muted d-block text-uppercase font-weight-bold">
                        Term
                    </small>

                    <div class="h5 font-weight-bold mb-0">
                        {{ $term->name }}
                    </div>
                </div>


                {{-- Dates --}}
                <div class="col-md-4">
                    <small class="text-muted d-block text-uppercase font-weight-bold">
                        Term Dates
                    </small>

                    <div class="font-weight-bold">
                        {{ $term->start_date
                            ? \Carbon\Carbon::parse($term->start_date)->format('d M Y')
                            : '—'
                        }}

                        –

                        {{ $term->end_date
                            ? \Carbon\Carbon::parse($term->end_date)->format('d M Y')
                            : '—'
                        }}
                    </div>
                </div>

            </div>

        </div>
    </div>


    {{-- ================================================================
         COUNTS
    ================================================================= --}}
    @php
        $pendingCount = $pendingAssignments->count();
        $rolloverCount = $currentAssignments->count();
        $totalAffected = $pendingCount + $rolloverCount;
    @endphp

    <div class="row mb-4">

        {{-- Prepared Progressions --}}
        <div class="col-lg-4 col-md-6 mb-3 mb-lg-0">

            <div class="card shadow-sm border-0 h-100">
                <div class="card-body">

                    <small class="text-muted text-uppercase font-weight-bold">
                        Prepared Progressions
                    </small>

                    <div class="display-4 font-weight-bold text-warning">
                        {{ $pendingCount }}
                    </div>

                    <small class="text-muted">
                        Promotions or repetitions waiting for activation.
                    </small>

                </div>
            </div>

        </div>


        {{-- Normal Term Rollovers --}}
        <div class="col-lg-4 col-md-6 mb-3 mb-lg-0">

            <div class="card shadow-sm border-0 h-100">
                <div class="card-body">

                    <small class="text-muted text-uppercase font-weight-bold">
                        Normal Term Rollovers
                    </small>

                    <div class="display-4 font-weight-bold text-primary">
                        {{ $rolloverCount }}
                    </div>

                    <small class="text-muted">
                        Students continuing in their current class.
                    </small>

                </div>
            </div>

        </div>


        {{-- Total Students --}}
        <div class="col-lg-4 col-md-12">

            <div class="card shadow-sm border-0 h-100">
                <div class="card-body">

                    <small class="text-muted text-uppercase font-weight-bold">
                        Students Affected
                    </small>

                    <div class="display-4 font-weight-bold text-success">
                        {{ $totalAffected }}
                    </div>

                    <small class="text-muted">
                        Student assignments to be activated or created.
                    </small>

                </div>
            </div>

        </div>

    </div>


    {{-- ================================================================
         INFORMATION / SAFETY NOTICE
    ================================================================= --}}
    <div class="card shadow-sm border-0 mb-4">

        <div class="card-body">

            <div class="d-flex align-items-start">

                <i class="fas fa-shield-alt fa-2x text-warning mr-3"></i>

                <div>

                    <h6 class="font-weight-bold">
                        What happens when you open this period?
                    </h6>

                    <ul class="text-muted mb-0">

                        <li>
                            Prepared promotion/repetition assignments become
                            <strong>Active / Current</strong>.
                        </li>

                        <li>
                            Students without a prepared assignment receive a
                            new assignment for the selected term in their
                            <strong>current class</strong>.
                        </li>

                        <li>
                            Previous active/current assignments become
                            <strong>Completed / Not Current</strong>.
                        </li>

                        <li>
                            Historical assignments are
                            <strong>not deleted</strong>.
                        </li>

                        <li>
                            Bill Sheets remain attached to their original
                            student assignments.
                        </li>

                        <li>
                            Payments and historical financial records are
                            <strong>not changed</strong>.
                        </li>

                    </ul>

                </div>

            </div>

        </div>
    </div>


    {{-- ================================================================
         PREPARED PROMOTION / REPETITION ASSIGNMENTS
    ================================================================= --}}
    @if($pendingAssignments->isNotEmpty())

        <div class="card shadow-sm border-0 mb-4">

            <div class="card-header bg-white py-3">

                <div class="d-flex justify-content-between align-items-center">

                    <h6 class="font-weight-bold mb-0">
                        <i class="fas fa-user-graduate text-warning mr-2"></i>
                        Prepared Promotion / Repetition Assignments
                    </h6>

                    <span class="badge badge-warning">
                        {{ $pendingCount }} Pending
                    </span>

                </div>

            </div>


            <div class="card-body p-0">

                <div class="table-responsive">

                    <table class="table table-hover mb-0">

                        <thead class="thead-light">

                            <tr>
                                <th>#</th>
                                <th>Student</th>
                                <th>Student ID</th>
                                <th>Current / Target Class</th>
                                <th>Period</th>
                                <th>Status</th>
                            </tr>

                        </thead>

                        <tbody>

                        @foreach($pendingAssignments as $assignment)

                            <tr>

                                <td>
                                    {{ $loop->iteration }}
                                </td>

                                <td class="font-weight-bold">

                                    {{ trim(
                                        ($assignment->student->first_name ?? '') . ' ' .
                                        ($assignment->student->middle_name ?? '') . ' ' .
                                        ($assignment->student->last_name ?? '')
                                    ) }}

                                </td>

                                <td>
                                    {{ $assignment->student->student_id ?? '—' }}
                                </td>

                                <td>

                                    <span class="font-weight-bold">
                                        {{ $assignment->studentClass->name ?? '—' }}
                                    </span>

                                    <br>

                                    <small class="text-muted">
                                        Prepared destination class
                                    </small>

                                </td>

                                <td>

                                    {{ $academicYear->name }}

                                    <br>

                                    <small class="text-muted">
                                        {{ $term->name }}
                                    </small>

                                </td>

                                <td>

                                    <span class="badge badge-warning">
                                        Pending
                                    </span>

                                    <span class="badge badge-light border">
                                        Not Current
                                    </span>

                                </td>

                            </tr>

                        @endforeach

                        </tbody>

                    </table>

                </div>

            </div>

        </div>

    @endif


    {{-- ================================================================
         NORMAL TERM ROLLOVER
    ================================================================= --}}
    @if($currentAssignments->isNotEmpty())

        <div class="card shadow-sm border-0 mb-4">

            <div class="card-header bg-white py-3">

                <div class="d-flex justify-content-between align-items-center">

                    <h6 class="font-weight-bold mb-0">

                        <i class="fas fa-sync-alt text-primary mr-2"></i>

                        Normal Term Rollover

                    </h6>

                    <span class="badge badge-primary">
                        {{ $rolloverCount }} Students
                    </span>

                </div>

            </div>


            <div class="card-body">

                <div class="alert alert-info mb-3">

                    <i class="fas fa-info-circle mr-2"></i>

                    These students do not have an assignment for
                    <strong>
                        {{ $academicYear->name }} — {{ $term->name }}
                    </strong>.
                    They will continue in their existing class for the new term.

                </div>


                <div class="table-responsive">

                    <table class="table table-hover mb-0">

                        <thead class="thead-light">

                            <tr>
                                <th>#</th>
                                <th>Student</th>
                                <th>Student ID</th>
                                <th>Current Class</th>
                                <th>Current Period</th>
                                <th>New Period</th>
                                <th>Action</th>
                            </tr>

                        </thead>

                        <tbody>

                        @foreach($currentAssignments as $assignment)

                            <tr>

                                <td>
                                    {{ $loop->iteration }}
                                </td>

                                <td class="font-weight-bold">

                                    {{ trim(
                                        ($assignment->student->first_name ?? '') . ' ' .
                                        ($assignment->student->middle_name ?? '') . ' ' .
                                        ($assignment->student->last_name ?? '')
                                    ) }}

                                </td>

                                <td>
                                    {{ $assignment->student->student_id ?? '—' }}
                                </td>

                                <td>

                                    <span class="text-dark">
                                        {{ $assignment->studentClass->name ?? '—' }}
                                    </span>

                                </td>

                                <td>

                                    @if($assignment->academicYear)
                                        {{ $assignment->academicYear->name }}
                                    @else
                                        —
                                    @endif

                                    <br>

                                    <small class="text-muted">

                                        {{ $assignment->term->name ?? '—' }}

                                    </small>

                                </td>

                                <td>

                                    {{ $academicYear->name }}

                                    <br>

                                    <small class="text-muted">
                                        {{ $term->name }}
                                    </small>

                                </td>

                                <td>

                                    <span class="">
                                        Continue Same Class
                                    </span>

                                </td>

                            </tr>

                        @endforeach

                        </tbody>

                    </table>

                </div>

            </div>

        </div>

    @endif


    {{-- ================================================================
         NOTHING TO PROCESS
    ================================================================= --}}
    @if($pendingAssignments->isEmpty() && $currentAssignments->isEmpty())

        <div class="card shadow-sm border-0 mb-4">

            <div class="card-body text-center py-5">

                <i class="fas fa-check-circle fa-3x text-success mb-3"></i>

                <h6 class="font-weight-bold">
                    No Student Assignments Require Processing
                </h6>

                <p class="text-muted mb-0">
                    There are no prepared assignments and no current students
                    waiting for rollover into
                    {{ $academicYear->name }} — {{ $term->name }}.
                </p>

            </div>

        </div>

    @endif


    {{-- ================================================================
         OPEN PERIOD FORM
    ================================================================= --}}
    @if($totalAffected > 0)

        <div class="card shadow-sm border-0">

            <div class="card-body">

                <div class="alert alert-warning">

                    <div class="d-flex align-items-start">

                        <i class="fas fa-exclamation-triangle fa-lg mr-3 mt-1"></i>

                        <div>

                            <strong>Review before opening</strong>

                            <p class="mb-0 mt-1">

                                You are about to open
                                <strong>
                                    {{ $academicYear->name }} —
                                    {{ $term->name }}
                                </strong>.

                                This will activate prepared assignments and
                                create normal same-class term assignments where
                                required.

                                Previous assignments will be preserved as
                                historical records.

                            </p>

                        </div>

                    </div>

                </div>


                <form method="POST"
                      action="{{ route('academic-period.activate') }}"
                      id="activatePeriodForm">

                    @csrf


                    {{-- Confirmation --}}
                    <div class="custom-control custom-checkbox mb-3">

                        <input type="checkbox"
                               class="custom-control-input"
                               id="confirmation"
                               name="confirmation"
                               value="1"
                               required>

                        <label class="custom-control-label font-weight-bold"
                               for="confirmation">

                            I confirm that
                            {{ $academicYear->name }} —
                            {{ $term->name }}
                            is the operational school period and I want to
                            process the student assignments.

                        </label>

                    </div>


                    {{-- Summary --}}
                    <div class="row mb-3">

                        @if($pendingCount > 0)

                            <div class="col-md-4 mb-2">

                                <div class="border rounded p-3 bg-light">

                                    <small class="text-muted d-block">
                                        PREPARED PROGRESSIONS
                                    </small>

                                    <strong class="text-warning">
                                        {{ $pendingCount }}
                                    </strong>

                                </div>

                            </div>

                        @endif


                        @if($rolloverCount > 0)

                            <div class="col-md-4 mb-2">

                                <div class="border rounded p-3 bg-light">

                                    <small class="text-muted d-block">
                                        NORMAL ROLLOVERS
                                    </small>

                                    <strong class="text-primary">
                                        {{ $rolloverCount }}
                                    </strong>

                                </div>

                            </div>

                        @endif


                        <div class="col-md-4 mb-2">

                            <div class="border rounded p-3 bg-light">

                                <small class="text-muted d-block">
                                    TOTAL AFFECTED
                                </small>

                                <strong class="text-success">
                                    {{ $totalAffected }}
                                </strong>

                            </div>

                        </div>

                    </div>


                    {{-- Open button --}}
                    <button type="submit"
                            class="btn btn-primary btn-lg"
                            id="activateButton">

                        <i class="fas fa-play-circle mr-1"></i>

                        Open {{ $term->name }}

                    </button>

                </form>

            </div>

        </div>

    @endif

</div>
@endsection


@push('scripts')

<script>
document.addEventListener('DOMContentLoaded', function () {

    const form = document.getElementById('activatePeriodForm');
    const button = document.getElementById('activateButton');

    if (!form || !button) {
        return;
    }

    form.addEventListener('submit', function (event) {

        const confirmed = window.confirm(
            'Open {{ addslashes($academicYear->name) }} - {{ addslashes($term->name) }}?\n\n' +

            'This will:\n' +

            '• Activate prepared promotion/repetition assignments.\n' +

            '• Create same-class assignments for normal term rollover students.\n' +

            '• Mark previous current assignments as completed/not current.\n\n' +

            'Historical assignments will be preserved.\n' +

            'Financial records, payments and fee balances will not be changed.'
        );

        if (!confirmed) {

            event.preventDefault();

            return;
        }

        button.disabled = true;

        button.innerHTML =
            '<i class="fas fa-spinner fa-spin mr-1"></i> Opening Period...';

    });

});
</script>

@endpush