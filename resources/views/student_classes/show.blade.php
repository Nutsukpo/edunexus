@extends('layouts.master')

@section('title', 'Class Details')

@section('content')

@php

/*
|--------------------------------------------------------------------------
| CLASS DATA
|--------------------------------------------------------------------------
*/

$activeAssignments = $studentClass->assignments
    ->where('is_current', true)
    ->where('status', 'active')
    ->filter(fn ($assignment) => $assignment->student !== null)
    ->values();

$students = $activeAssignments->pluck('student');

$totalStudents = $students->count();

$maleCount = $students
    ->where('gender', 'Male')
    ->count();

$femaleCount = $students
    ->where('gender', 'Female')
    ->count();


/*
|--------------------------------------------------------------------------
| ROUTE AVAILABILITY
|--------------------------------------------------------------------------
*/

$hasAttendanceRoutes =
    Route::has('attendance.ajax') &&
    Route::has('attendance.store.class');

$hasBroadsheetRoute = Route::has('broadsheet.ajax');

$hasProgressionRoute = Route::has('student-progressions.index');


/*
|--------------------------------------------------------------------------
| FEES
|--------------------------------------------------------------------------
|
| Calculate the class fee rate from actual Bill Sheets and completed
| Fee Payments. This avoids relying on an undefined/stale controller
| variable such as $feesPaidPercentage.
|
*/

$assignmentIds = $activeAssignments
    ->pluck('id')
    ->filter()
    ->values();


$classBillSheets = collect();

$classFeePayments = collect();


if ($assignmentIds->isNotEmpty()) {

    $classBillSheets = \App\Models\BillSheet::query()
        ->whereIn(
            'student_class_assignment_id',
            $assignmentIds
        )
        ->where('is_active', true)
        ->whereIn('status', [
            'approved',
            'published'
        ])
        ->get();


    $classFeePayments = \App\Models\FeePayment::query()
        ->whereIn(
            'student_class_assignment_id',
            $assignmentIds
        )
        ->where('status', 'completed')
        ->get();
}


/*
|--------------------------------------------------------------------------
| CLASS FEE TOTALS
|--------------------------------------------------------------------------
*/

$totalClassFees = $classBillSheets->sum(function ($bill) {
    return (float) (
        $bill->net_amount
        ?? $bill->total_amount
        ?? 0
    );
});


$totalClassFeesPaid = $classFeePayments->sum(function ($payment) {
    return (float) (
        $payment->net_amount
        ?? $payment->amount
        ?? 0
    );
});


$totalClassFeesBalance = max(
    0,
    round(
        $totalClassFees - $totalClassFeesPaid,
        2
    )
);


/*
|--------------------------------------------------------------------------
| FEES PAID RATE
|--------------------------------------------------------------------------
*/

$feesPaidPercentage = $totalClassFees > 0
    ? min(
        100,
        round(
            ($totalClassFeesPaid / $totalClassFees) * 100,
            1
        )
    )
    : 0;


/*
|--------------------------------------------------------------------------
| STUDENT FEE DATA
|--------------------------------------------------------------------------
*/

$feeData = $activeAssignments->map(
    function ($assignment) use (
        $classBillSheets,
        $classFeePayments
    ) {

        $student = $assignment->student;


        $studentBills = $classBillSheets->where(
            'student_class_assignment_id',
            $assignment->id
        );


        $studentPayments = $classFeePayments->where(
            'student_class_assignment_id',
            $assignment->id
        );


        $totalFees = $studentBills->sum(
            function ($bill) {
                return (float) (
                    $bill->net_amount
                    ?? $bill->total_amount
                    ?? 0
                );
            }
        );


        $amountPaid = $studentPayments->sum(
            function ($payment) {
                return (float) (
                    $payment->net_amount
                    ?? $payment->amount
                    ?? 0
                );
            }
        );


        $balance = max(
            0,
            round(
                $totalFees - $amountPaid,
                2
            )
        );


        if ($totalFees <= 0) {

            $status = 'No Bill';

            $statusClass = 'secondary';

            $paymentPercentage = 0;

        } elseif ($balance <= 0) {

            $status = 'Paid';

            $statusClass = 'success';

            $paymentPercentage = 100;

        } elseif ($amountPaid > 0) {

            $status = 'Partially Paid';

            $statusClass = 'warning';

            $paymentPercentage = round(
                ($amountPaid / $totalFees) * 100,
                1
            );

        } else {

            $status = 'Unpaid';

            $statusClass = 'danger';

            $paymentPercentage = 0;
        }


        return [
            'assignment_id' => $assignment->id,

            'student' => $student,

            'total_fees' => $totalFees,

            'amount_paid' => $amountPaid,

            'balance' => $balance,

            'status' => $status,

            'status_class' => $statusClass,

            'payment_percentage' => $paymentPercentage,
        ];
    }
);


/*
|--------------------------------------------------------------------------
| FEE STUDENT COUNTS
|--------------------------------------------------------------------------
*/

$paidStudents = $feeData
    ->where('status', 'Paid')
    ->count();

$partialStudents = $feeData
    ->where('status', 'Partially Paid')
    ->count();

$unpaidStudents = $feeData
    ->where('status', 'Unpaid')
    ->count();


/*
|--------------------------------------------------------------------------
| CLASS SUBJECTS
|--------------------------------------------------------------------------
*/

$classSubjects = $studentClass->subjects ?? collect();

@endphp

<div class="container-fluid" id="app-container">

{{-- ================================================================
    PAGE HEADER
================================================================= --}}

<div class="d-flex justify-content-between align-items-center mb-4 mt-3">

    <div>

        <h5 class="fw-bold mb-1">
            {{ $studentClass->name }}
        </h5>

        <p class="text-muted mb-0">
            Manage class details, students, fees, attendance, results and more
        </p>

    </div>

</div>



{{-- ================================================================
    SUMMARY CARDS
================================================================= --}}

<div class="row mb-3">


    {{-- TOTAL STUDENTS --}}
    <div class="col-md-3 mb-3">

        <div class="card border-0 shadow-sm h-100">

            <div class="card-body">

                <div class="d-flex justify-content-between align-items-start">

                    <div>

                        <small class="text-muted text-uppercase fw-semibold">
                            Total Students
                        </small>

                        <h3 class="fw-bold mt-2 mb-2">
                            {{ $totalStudents }}
                        </h3>

                        <div class="d-flex gap-2 flex-wrap">

                            <span class="badge bg-light text-dark">
                                <i class="fas fa-male me-1"></i>
                                Male: {{ $maleCount }}
                            </span>

                            <span class="badge bg-light text-dark">
                                <i class="fas fa-female me-1"></i>
                                Female: {{ $femaleCount }}
                            </span>

                        </div>

                    </div>

                    <div class="bg-primary bg-opacity-10 rounded-circle p-3">

                        <i class="fas fa-user-graduate text-dark fs-4"></i>

                    </div>

                </div>

            </div>

        </div>

    </div>


    {{-- FEES PAID --}}
    <div class="col-md-3 mb-3">

        <div class="card border-0 shadow-sm h-100">

            <div class="card-body">

                <div class="d-flex justify-content-between align-items-start">

                    <div>

                        <small class="text-muted text-uppercase fw-semibold">
                            Class Fees Paid
                        </small>

                        <h3 class="fw-bold mt-2 mb-1">
                            {{ number_format($feesPaidPercentage, 1) }}%
                        </h3>

                        <small class="text-muted">
                            GHS {{ number_format($totalClassFeesPaid, 2) }}
                            of
                            GHS {{ number_format($totalClassFees, 2) }}
                        </small>

                    </div>

                    <div class="bg-success bg-opacity-10 rounded-circle p-3">

                        <i class="fas fa-wallet text-success fs-4"></i>

                    </div>

                </div>


                <div class="progress mt-3"
                     style="height: 7px;">

                    <div class="progress-bar bg-success"
                         role="progressbar"
                         style="width: {{ $feesPaidPercentage }}%;"
                         aria-valuenow="{{ $feesPaidPercentage }}"
                         aria-valuemin="0"
                         aria-valuemax="100">
                    </div>

                </div>

            </div>

        </div>

    </div>


    {{-- CLASS TEACHER --}}
    <div class="col-md-3 mb-3">

        <div class="card border-0 shadow-sm h-100">

            <div class="card-body">

                <div class="d-flex justify-content-between align-items-center">

                    <div>

                        <small class="text-muted text-uppercase fw-semibold">
                            Class Teacher
                        </small>

                        @if($studentClass->classTeacher)

                            <h6 class="fw-bold mt-2 mb-0">

                                {{ $studentClass->classTeacher->first_name }}
                                {{ $studentClass->classTeacher->last_name }}

                            </h6>

                        @else

                            <span class="badge bg-warning mt-2">
                                Not Assigned
                            </span>

                        @endif

                    </div>

                    <div class="bg-dark bg-opacity-10 rounded-circle p-3">

                        <i class="fas fa-chalkboard-teacher text-dark fs-4"></i>

                    </div>

                </div>

            </div>

        </div>

    </div>


    {{-- ATTENDANCE --}}
    <div class="col-md-3 mb-3">

        <div class="card border-0 shadow-sm h-100">

            <div class="card-body">

                <div class="d-flex justify-content-between align-items-center">

                    <div>

                        <small class="text-muted text-uppercase fw-semibold">
                            Attendance Rate
                        </small>

                        <h3 class="fw-bold mt-2 mb-0">

                            {{ number_format($attendanceRate ?? 0, 1) }}%

                        </h3>

                    </div>

                    <div class="bg-warning bg-opacity-10 rounded-circle p-3">

                        <i class="fas fa-chart-line text-warning fs-4"></i>

                    </div>

                </div>

            </div>

        </div>

    </div>

</div>



{{-- ================================================================
    NAVIGATION TABS
================================================================= --}}

<div class="card border-0 shadow-sm mb-2">

    <div class="card-body p-0">

        <ul class="nav nav-tabs nav-fill"
            id="classTabs"
            role="tablist">

            <li class="nav-item">

                <button class="nav-link active"
                        data-bs-toggle="tab"
                        data-bs-target="#overview"
                        type="button">

                    <i class="fas fa-chart-pie me-2"></i>
                    Overview

                </button>

            </li>


            <li class="nav-item">

                <button class="nav-link"
                        data-bs-toggle="tab"
                        data-bs-target="#students"
                        type="button">

                    <i class="fas fa-users me-2"></i>
                    Students

                </button>

            </li>


            <li class="nav-item">

                <button class="nav-link"
                        data-bs-toggle="tab"
                        data-bs-target="#fees"
                        type="button">

                    <i class="fas fa-money-bill-wave me-2"></i>
                    Fees

                </button>

            </li>


            <li class="nav-item">

                <button class="nav-link"
                        data-bs-toggle="tab"
                        data-bs-target="#attendance"
                        type="button">

                    <i class="fas fa-calendar-check me-2"></i>
                    Attendance

                </button>

            </li>


            <li class="nav-item">

                <button class="nav-link"
                        data-bs-toggle="tab"
                        data-bs-target="#subjects"
                        type="button">

                    <i class="fas fa-book-open me-2"></i>
                    Subjects

                </button>

            </li>


            <li class="nav-item">

                <button class="nav-link"
                        data-bs-toggle="tab"
                        data-bs-target="#prefect"
                        type="button">

                    <i class="fas fa-user-shield me-2"></i>
                    Prefect

                </button>

            </li>


            <li class="nav-item">

                <button class="nav-link"
                        data-bs-toggle="tab"
                        data-bs-target="#results"
                        type="button">

                    <i class="fas fa-chart-line me-2"></i>
                    Results

                </button>

            </li>


            <li class="nav-item">

                <button class="nav-link"
                        data-bs-toggle="tab"
                        data-bs-target="#classTimetable"
                        type="button">

                    <i class="fas fa-calendar-alt me-2"></i>
                    Timetable

                </button>

            </li>


            <li class="nav-item">

                <button class="nav-link"
                        data-bs-toggle="tab"
                        data-bs-target="#promotions"
                        type="button">

                    <i class="fas fa-graduation-cap me-2"></i>
                    Promotions

                </button>

            </li>

        </ul>

    </div>

</div>



{{-- ================================================================
    TAB CONTENT
================================================================= --}}

<div class="tab-content">


    {{-- ================================================================
        OVERVIEW TAB
    ================================================================= --}}

    <div class="tab-pane fade show active"
         id="overview">

        <div class="card border-0 shadow-sm">

            <div class="card-header bg-white">

                <h5 class="fw-bold mb-0">
                    Class Information
                </h5>

            </div>

            <div class="card-body">

                <div class="row">

                    <div class="col-md-6">

                        <table class="table table-borderless">

                            <tr>
                                <th width="220">Class Name</th>
                                <td>{{ $studentClass->name }}</td>
                            </tr>

                            <tr>
                                <th>Education Type</th>
                                <td>{{ $studentClass->education_type ?? 'N/A' }}</td>
                            </tr>

                            <tr>
                                <th>Class Type</th>
                                <td>{{ $studentClass->class_type ?? 'N/A' }}</td>
                            </tr>

                            <tr>
                                <th>Stream</th>
                                <td>{{ $studentClass->stream ?? 'N/A' }}</td>
                            </tr>

                        </table>

                    </div>


                    <div class="col-md-6">

                        <table class="table table-borderless">

                            <tr>

                                <th width="220">
                                    Students
                                </th>

                                <td>

                                    <span class="badge bg-dark">
                                        {{ $totalStudents }}
                                    </span>

                                </td>

                            </tr>


                            <tr>

                                <th>
                                    Class Teacher
                                </th>

                                <td>

                                    @if($studentClass->classTeacher)

                                        {{ $studentClass->classTeacher->first_name }}
                                        {{ $studentClass->classTeacher->last_name }}

                                    @else

                                        <span class="badge bg-warning">
                                            Not Assigned
                                        </span>

                                    @endif

                                </td>

                            </tr>


                            <tr>

                                <th>
                                    Fees Paid
                                </th>

                                <td>

                                    <span class="badge bg-success">

                                        {{ number_format($feesPaidPercentage, 1) }}%

                                    </span>

                                </td>

                            </tr>


                            <tr>

                                <th>
                                    Attendance
                                </th>

                                <td>

                                    <span class="badge bg-success">

                                        {{ number_format($attendanceRate ?? 0, 1) }}%

                                    </span>

                                </td>

                            </tr>

                        </table>

                    </div>

                </div>


                {{-- FEE SUMMARY --}}
                <div class="row mt-3">

                    <div class="col-md-4 mb-3">

                        <div class="border rounded p-3 h-100">

                            <small class="text-muted d-block">
                                Total Class Fees
                            </small>

                            <h5 class="fw-bold text-dark mb-0">

                                GHS {{ number_format($totalClassFees, 2) }}

                            </h5>

                        </div>

                    </div>


                    <div class="col-md-4 mb-3">

                        <div class="border rounded p-3 h-100">

                            <small class="text-muted d-block">
                                Total Paid
                            </small>

                            <h5 class="fw-bold text-success mb-0">

                                GHS {{ number_format($totalClassFeesPaid, 2) }}

                            </h5>

                        </div>

                    </div>


                    <div class="col-md-4 mb-3">

                        <div class="border rounded p-3 h-100">

                            <small class="text-muted d-block">
                                Outstanding Balance
                            </small>

                            <h5 class="fw-bold text-danger mb-0">

                                GHS {{ number_format($totalClassFeesBalance, 2) }}

                            </h5>

                        </div>

                    </div>

                </div>

            </div>

        </div>

    </div>



    {{-- ================================================================
        STUDENTS TAB
    ================================================================= --}}

    <div class="tab-pane fade"
         id="students">

        <div class="card border-0 shadow-sm">

            <div class="card-header bg-white">

                <h5 class="fw-bold mb-0">

                    <i class="fas fa-users me-2"></i>
                    Class Students

                </h5>

            </div>

            <div class="card-body">

                <div class="table-responsive">

                    <table class="table table-hover align-middle">

                        <thead class="table-light">

                            <tr>

                                <th>#</th>
                                <th>Student</th>
                                <th>Student ID</th>
                                <th>Gender</th>
                                <th>Status</th>
                                <th>Actions</th>

                            </tr>

                        </thead>

                        <tbody>

                            @forelse($activeAssignments as $assignment)

                                @php
                                    $student = $assignment->student;
                                @endphp

                                <tr>

                                    <td>
                                        {{ $loop->iteration }}
                                    </td>

                                    <td class="fw-semibold">

                                        {{ $student->first_name }}

                                        @if($student->middle_name)
                                            {{ $student->middle_name }}
                                        @endif

                                        {{ $student->last_name }}

                                    </td>

                                    <td>

                                        <span class="badge bg-light text-dark">

                                            {{ $student->student_id ?? 'N/A' }}

                                        </span>

                                    </td>

                                    <td>

                                        @if($student->gender === 'Male')

                                            <span class="badge bg-light text-dark">
                                                Male
                                            </span>

                                        @elseif($student->gender === 'Female')

                                            <span class="badge bg-light text-dark">
                                                Female
                                            </span>

                                        @else

                                            <span class="badge bg-secondary">
                                                N/A
                                            </span>

                                        @endif

                                    </td>

                                    <td>

                                        <span class="badge bg-success">
                                            Active
                                        </span>

                                    </td>

                                    <td>

                                        <a href="{{ route('students.show', $student->id) }}"
                                           class="btn btn-sm btn-light">

                                            <i class="fas fa-eye"></i>

                                        </a>

                                    </td>

                                </tr>

                            @empty

                                <tr>

                                    <td colspan="6"
                                        class="text-center py-5">

                                        <i class="fas fa-users-slash fs-1 text-muted mb-3 d-block"></i>

                                        <h5>
                                            No Students Assigned
                                        </h5>

                                        <p class="text-muted mb-0">
                                            There are currently no active students in this class.
                                        </p>

                                    </td>

                                </tr>

                            @endforelse

                        </tbody>

                    </table>

                </div>

            </div>

        </div>

    </div>



    {{-- ================================================================
        FEES TAB
    ================================================================= --}}

    <div class="tab-pane fade"
         id="fees">

        <div class="card border-0 shadow-sm">

            <div class="card-header bg-white">

                <div class="d-flex justify-content-between align-items-center">

                    <div>

                        <h5 class="fw-bold mb-1">

                            <i class="fas fa-money-bill-wave text-success me-2"></i>

                            Class Fee Payments

                        </h5>

                        <small class="text-muted">

                            Student payment status and outstanding balances

                        </small>

                    </div>

                    <div>

                        <span class="badge bg-success me-1">
                            Paid: {{ $paidStudents }}
                        </span>

                        <span class="badge bg-warning text-dark me-1">
                            Partial: {{ $partialStudents }}
                        </span>

                        <span class="badge bg-danger">
                            Unpaid: {{ $unpaidStudents }}
                        </span>

                    </div>

                </div>

            </div>


            <div class="card-body">


                {{-- Fee Summary --}}
                <div class="row mb-4">

                    <div class="col-md-4 mb-3">

                        <div class="border rounded p-3">

                            <small class="text-muted">
                                Total Fees
                            </small>

                            <h5 class="fw-bold mb-0">

                                GHS {{ number_format($totalClassFees, 2) }}

                            </h5>

                        </div>

                    </div>


                    <div class="col-md-4 mb-3">

                        <div class="border rounded p-3">

                            <small class="text-muted">
                                Amount Paid
                            </small>

                            <h5 class="fw-bold text-success mb-0">

                                GHS {{ number_format($totalClassFeesPaid, 2) }}

                            </h5>

                        </div>

                    </div>


                    <div class="col-md-4 mb-3">

                        <div class="border rounded p-3">

                            <small class="text-muted">
                                Outstanding
                            </small>

                            <h5 class="fw-bold text-danger mb-0">

                                GHS {{ number_format($totalClassFeesBalance, 2) }}

                            </h5>

                        </div>

                    </div>

                </div>


                @if($feeData->count() > 0)

                    <div class="table-responsive">

                        <table class="table table-bordered table-hover align-middle">

                            <thead class="table-light">

                                <tr>

                                    <th width="50">#</th>

                                    <th>Student</th>

                                    <th>Student ID</th>

                                    <th class="text-end">
                                        Total Fees
                                    </th>

                                    <th class="text-end">
                                        Amount Paid
                                    </th>

                                    <th class="text-end">
                                        Balance
                                    </th>

                                    <th width="160">
                                        Payment Progress
                                    </th>

                                    <th class="text-center">
                                        Status
                                    </th>

                                </tr>

                            </thead>

                            <tbody>

                                @foreach($feeData as $fee)

                                    @php
                                        $student = $fee['student'];
                                    @endphp

                                    <tr>

                                        <td>
                                            {{ $loop->iteration }}
                                        </td>

                                        <td class="fw-semibold">

                                            {{ $student->first_name }}

                                            @if($student->middle_name)
                                                {{ $student->middle_name }}
                                            @endif

                                            {{ $student->last_name }}

                                        </td>

                                        <td>

                                            <span class="badge bg-light text-dark">

                                                {{ $student->student_id ?? 'N/A' }}

                                            </span>

                                        </td>

                                        <td class="text-end">

                                            GHS
                                            {{ number_format($fee['total_fees'], 2) }}

                                        </td>

                                        <td class="text-end text-success fw-bold">

                                            GHS
                                            {{ number_format($fee['amount_paid'], 2) }}

                                        </td>

                                        <td class="text-end text-danger fw-bold">

                                            GHS
                                            {{ number_format($fee['balance'], 2) }}

                                        </td>

                                        <td>

                                            <div class="d-flex justify-content-between small mb-1">

                                                <span>
                                                    Paid
                                                </span>

                                                <span class="fw-bold">

                                                    {{ number_format($fee['payment_percentage'], 1) }}%

                                                </span>

                                            </div>

                                            <div class="progress"
                                                 style="height: 7px;">

                                                <div class="progress-bar bg-{{ $fee['status_class'] }}"
                                                     style="width: {{ $fee['payment_percentage'] }}%;">
                                                </div>

                                            </div>

                                        </td>

                                        <td class="text-center">

                                            <span class="badge bg-{{ $fee['status_class'] }}">

                                                {{ $fee['status'] }}

                                            </span>

                                        </td>

                                    </tr>

                                @endforeach

                            </tbody>

                            <tfoot class="table-light fw-bold">

                                <tr>

                                    <td colspan="3"
                                        class="text-end">

                                        CLASS TOTAL

                                    </td>

                                    <td class="text-end">

                                        GHS
                                        {{ number_format($totalClassFees, 2) }}

                                    </td>

                                    <td class="text-end text-success">

                                        GHS
                                        {{ number_format($totalClassFeesPaid, 2) }}

                                    </td>

                                    <td class="text-end text-danger">

                                        GHS
                                        {{ number_format($totalClassFeesBalance, 2) }}

                                    </td>

                                    <td colspan="2"
                                        class="text-center">

                                        {{ number_format($feesPaidPercentage, 1) }}% Paid

                                    </td>

                                </tr>

                            </tfoot>

                        </table>

                    </div>

                @else

                    <div class="text-center py-5">

                        <i class="fas fa-file-invoice-dollar fs-1 text-muted mb-3 d-block"></i>

                        <h5>
                            No Fee Records Found
                        </h5>

                        <p class="text-muted mb-0">

                            No active fee bills have been generated
                            for the students in this class.

                        </p>

                    </div>

                @endif

            </div>

        </div>

    </div>



    {{-- ================================================================
        ATTENDANCE TAB
    ================================================================= --}}

    <div class="tab-pane fade"
         id="attendance">

        <div class="card border-0 shadow-sm">

            <div class="card-header bg-white d-flex justify-content-between align-items-center flex-wrap gap-2">

                <h5 class="fw-bold mb-0">

                    <i class="fas fa-calendar-check me-2"></i>

                    Class Attendance

                </h5>

                <div class="d-flex gap-2 flex-wrap">

                    <button type="button"
                            class="btn btn-primary btn-sm take-attendance-btn"
                            data-bs-toggle="modal"
                            data-bs-target="#takeAttendanceModal">

                        <i class="fas fa-clipboard-check me-1"></i>
                        Take Attendance

                    </button>

                    <button class="btn btn-outline-dark btn-sm"
                            onclick="exportAttendanceToExcel()">

                        <i class="fas fa-download me-1"></i>
                        Export

                    </button>

                    <button class="btn btn-outline-secondary btn-sm"
                            onclick="printAttendance()">

                        <i class="fas fa-print me-1"></i>
                        Print

                    </button>

                </div>

            </div>


            <div class="card-body">


                {{-- Attendance Filters --}}

                <div class="row mb-4">

                    <div class="col-md-3">

                        <label class="form-label fw-semibold">
                            Date
                        </label>

                        <input type="date"
                               id="attendanceDate"
                               class="form-control"
                               value="{{ date('Y-m-d') }}"
                               max="{{ date('Y-m-d') }}">

                    </div>


                    <div class="col-md-3">

                        <label class="form-label fw-semibold">
                            Month
                        </label>

                        <select id="attendanceMonth"
                                class="form-select">

                            <option value="">
                                All Months
                            </option>

                            @for($m = 1; $m <= 12; $m++)

                                <option value="{{ $m }}"
                                    {{ date('m') == str_pad($m, 2, '0', STR_PAD_LEFT) ? 'selected' : '' }}>

                                    {{ date('F', mktime(0, 0, 0, $m, 1)) }}

                                </option>

                            @endfor

                        </select>

                    </div>


                    <div class="col-md-3">

                        <label class="form-label fw-semibold">
                            Year
                        </label>

                        <select id="attendanceYear"
                                class="form-select">

                            @php
                                $currentYear = date('Y');
                            @endphp

                            @for(
                                $y = $currentYear;
                                $y >= $currentYear - 2;
                                $y--
                            )

                                <option value="{{ $y }}"
                                    {{ $currentYear == $y ? 'selected' : '' }}>

                                    {{ $y }}

                                </option>

                            @endfor

                        </select>

                    </div>


                    <div class="col-md-3">

                        <label class="form-label fw-semibold">
                            &nbsp;
                        </label>

                        <button id="loadAttendanceBtn"
                                class="btn btn-primary d-block w-100">

                            <i class="fas fa-search me-1"></i>
                            Load Attendance

                        </button>

                    </div>

                </div>


                <div id="attendanceContainer">

                    <div class="text-center py-5 text-muted">

                        <i class="fas fa-calendar-check fs-1 mb-3 d-block"></i>

                        <h5>
                            No Attendance Records Loaded
                        </h5>

                        <p>
                            Select a date or month and click
                            "Load Attendance".
                        </p>

                    </div>

                </div>

            </div>

        </div>

    </div>



    {{-- ================================================================
        SUBJECTS TAB
    ================================================================= --}}

    <div class="tab-pane fade"
         id="subjects">

        <div class="card border-0 shadow-sm">

            <div class="card-header bg-white d-flex justify-content-between align-items-center">

                <h5 class="fw-bold mb-0">

                    <i class="fas fa-book-open me-2"></i>
                    Class Subjects

                </h5>

                <button class="btn btn-light bg-light text-dark btn-sm"
                        data-bs-toggle="modal"
                        data-bs-target="#addSubjectModal">

                    <i class="fas fa-plus me-1"></i>
                    Add Subject

                </button>

            </div>


            <div class="card-body">

                @if(session('subject_success'))

                    <div class="alert alert-success alert-dismissible fade show">

                        <i class="fas fa-check-circle me-2"></i>

                        {{ session('subject_success') }}

                        <button type="button"
                                class="btn-close"
                                data-bs-dismiss="alert">
                        </button>

                    </div>

                @endif


                @if(session('subject_error'))

                    <div class="alert alert-danger alert-dismissible fade show">

                        <i class="fas fa-exclamation-circle me-2"></i>

                        {{ session('subject_error') }}

                        <button type="button"
                                class="btn-close"
                                data-bs-dismiss="alert">
                        </button>

                    </div>

                @endif


                @if($classSubjects->count() > 0)

                    <div class="table-responsive">

                        <table class="table table-bordered align-middle">

                            <thead class="table-light">

                                <tr>

                                    <th width="50">#</th>
                                    <th>Subject Name</th>
                                    <th>Subject Code</th>
                                    <th>Education Level</th>
                                    <th>Subject Staff</th>
                                    <th width="100">Actions</th>

                                </tr>

                            </thead>

                            <tbody>

                                @foreach($classSubjects as $subject)

                                    <tr>

                                        <td>
                                            {{ $loop->iteration }}
                                        </td>

                                        <td class="fw-semibold">

                                            <i class="fas fa-book text-dark me-2"></i>

                                            {{ $subject->name }}

                                        </td>

                                        <td>

                                            @if($subject->code)

                                                <span class="badge bg-light text-dark">
                                                    {{ $subject->code }}
                                                </span>

                                            @else

                                                <span class="text-muted">
                                                    —
                                                </span>

                                            @endif

                                        </td>

                                        <td>

                                            @if($subject->education_level)

                                                <span class="badge bg-light text-dark">
                                                    {{ $subject->education_level }}
                                                </span>

                                            @else

                                                <span class="text-muted">
                                                    —
                                                </span>

                                            @endif

                                        </td>

                                        <td>

                                            @if($subject->staff)

                                                <span class="badge bg-light text-dark">

                                                    {{ $subject->staff->first_name }}
                                                    {{ $subject->staff->last_name }}

                                                </span>

                                            @else

                                                <span class="text-muted">
                                                    —
                                                </span>

                                            @endif

                                        </td>

                                        <td>

                                            <form
                                                action="{{ route('classes.subject.detach', [$studentClass->id, $subject->id]) }}"
                                                method="POST"
                                                class="d-inline"
                                                onsubmit="return confirm('Are you sure you want to remove {{ addslashes($subject->name) }} from this class?');">

                                                @csrf

                                                @method('DELETE')

                                                <button type="submit"
                                                        class="btn btn-sm btn-outline-dark">

                                                    <i class="fas fa-trash-alt"></i>

                                                </button>

                                            </form>

                                        </td>

                                    </tr>

                                @endforeach

                            </tbody>

                        </table>

                    </div>

                    <div class="mt-3 text-muted small">

                        <i class="fas fa-info-circle me-1"></i>

                        Total subjects assigned:
                        {{ $classSubjects->count() }}

                    </div>

                @else

                    <div class="text-center py-5">

                        <i class="fas fa-book-open fs-1 text-muted mb-3 d-block"></i>

                        <h5>
                            No Subjects Assigned
                        </h5>

                        <p class="text-muted mb-4">
                            This class doesn't have any subjects assigned yet.
                        </p>

                        <button class="btn btn-primary"
                                data-bs-toggle="modal"
                                data-bs-target="#addSubjectModal">

                            <i class="fas fa-plus me-1"></i>
                            Add First Subject

                        </button>

                    </div>

                @endif

            </div>

        </div>

    </div>



    {{-- ================================================================
        PREFECT TAB
    ================================================================= --}}

    <div class="tab-pane fade"
         id="prefect">

        <div class="card border-0 shadow-sm">

            <div class="card-header bg-white d-flex justify-content-between align-items-center">

                <h5 class="fw-bold mb-0">

                    <i class="fas fa-user-shield me-2"></i>
                    Class Prefect

                </h5>

                <button class="btn btn-light bg-light text-dark btn-sm"
                        data-bs-toggle="modal"
                        data-bs-target="#assignPrefectModal">

                    <i class="fas fa-user-check me-1"></i>
                    Assign Prefect

                </button>

            </div>


            <div class="card-body">

                @if(
                    $studentClass->classPrefect &&
                    $activeAssignments->contains(
                        'student_id',
                        $studentClass->classPrefect->id
                    )
                )

                    <div class="d-flex align-items-center">

                        <div class="bg-dark text-white rounded-circle d-flex align-items-center justify-content-center me-4"
                             style="width: 80px; height: 80px;">

                            <i class="fas fa-user-shield fs-2"></i>

                        </div>

                        <div>

                            <h4 class="fw-bold mb-1">

                                {{ $studentClass->classPrefect->first_name }}
                                {{ $studentClass->classPrefect->last_name }}

                            </h4>

                            <span class="badge bg-success">

                                {{ $studentClass->classPrefect->student_id ?? 'N/A' }}

                            </span>

                        </div>

                    </div>

                @else

                    <div class="text-center py-5">

                        <i class="fas fa-user-slash fs-1 text-muted mb-3 d-block"></i>

                        <h5>
                            No Class Prefect Assigned
                        </h5>

                        <p class="text-muted mb-0">
                            Assign an active student as class prefect.
                        </p>

                    </div>

                @endif

            </div>

        </div>

    </div>



    {{-- ================================================================
        RESULTS TAB
    ================================================================= --}}

    <div class="tab-pane fade"
         id="results">

        <div class="card border-0 shadow-sm">

            <div class="card-header bg-white">

                <h5 class="fw-bold mb-1">

                    <i class="fas fa-chart-line me-2"></i>
                    Class Results

                </h5>

                <small class="text-muted">
                    Select an academic year and term to load the class broadsheet.
                </small>

            </div>


            <div class="card-body">


                {{-- Academic Year and Term Selection --}}

                <div class="row g-3 mb-4">

                    <div class="col-md-4">

                        <label for="academicYearSelect"
                               class="form-label fw-semibold">

                            Academic Year

                        </label>

                        <select id="academicYearSelect"
                                class="form-select">

                            <option value="">
                                Select Academic Year
                            </option>

                            @foreach(
                                \App\Models\AcademicYear::orderBy('name', 'desc')->get()
                                as $year
                            )

                                <option value="{{ $year->id }}">

                                    {{ $year->name }}

                                </option>

                            @endforeach

                        </select>

                    </div>


                    <div class="col-md-4">

                        <label for="termSelect"
                               class="form-label fw-semibold">

                            Term

                        </label>

                        <select id="termSelect"
                                class="form-select">

                            <option value="">
                                Select Term
                            </option>

                            @foreach(
                                \App\Models\Term::orderBy('name')->get()
                                as $term
                            )

                                <option value="{{ $term->id }}">

                                    {{ $term->name }}

                                </option>

                            @endforeach

                        </select>

                    </div>


                    <div class="col-md-4 d-flex align-items-end">

                        <button id="loadResultsBtn"
                                type="button"
                                class="btn btn-primary w-100">

                            <i class="fas fa-chart-line me-1"></i>

                            Load Results

                        </button>

                    </div>

                </div>


                {{-- Search and Filter Section --}}

                <div id="filterSection"
                     style="display: none;">

                    <div class="card bg-light border-0 mb-3">

                        <div class="card-body">

                            <div class="row g-3">

                                <div class="col-md-4">

                                    <label class="form-label">
                                        Search
                                    </label>

                                    <input type="text"
                                           id="searchInput"
                                           class="form-control"
                                           placeholder="Search student name or ID...">

                                </div>


                                <div class="col-md-3">

                                    <label class="form-label">
                                        Position
                                    </label>

                                    <select id="positionFilter"
                                            class="form-select">

                                        <option value="">
                                            All Positions
                                        </option>

                                        <option value="top3">
                                            Top 3
                                        </option>

                                        <option value="top10">
                                            Top 10
                                        </option>

                                        <option value="others">
                                            Others
                                        </option>

                                    </select>

                                </div>


                                <div class="col-md-3">

                                    <label class="form-label">
                                        Performance
                                    </label>

                                    <select id="performanceFilter"
                                            class="form-select">

                                        <option value="">
                                            All Performance
                                        </option>

                                        <option value="excellent">
                                            Excellent (80+)
                                        </option>

                                        <option value="good">
                                            Good (70-79)
                                        </option>

                                        <option value="average">
                                            Average (50-69)
                                        </option>

                                        <option value="poor">
                                            Poor (&lt;50)
                                        </option>

                                    </select>

                                </div>


                                <div class="col-md-2 d-flex align-items-end">

                                    <button type="button"
                                            class="btn btn-secondary w-100"
                                            onclick="resetResultFilters()">

                                        Reset

                                    </button>

                                </div>

                            </div>

                        </div>

                    </div>

                </div>


                {{-- Results Container --}}

                <div id="resultsContainer">

                    <div class="text-center py-5 text-muted">

                        <i class="fas fa-chart-line fs-1 mb-3 d-block"></i>

                        <h5>
                            No Results Loaded
                        </h5>

                        <p>
                            Select Academic Year and Term to view results.
                        </p>

                    </div>

                </div>

            </div>

        </div>

    </div>



    {{-- ================================================================
        TIMETABLE TAB
    ================================================================= --}}

    <div class="tab-pane fade"
         id="classTimetable">

        <div class="card border-0 shadow-sm">

            {{-- Header --}}

            <div class="card-header bg-white d-flex justify-content-between align-items-center">

                <div>

                    <h5 class="fw-bold mb-1">

                        <i class="fas fa-calendar-alt me-2 text-primary"></i>

                        Class Timetable

                    </h5>

                    <small class="text-muted">

                        View the current timetable for this class

                    </small>

                </div>


                <!-- @if(isset($timetable) && $timetable)
                    <a href="{{ route('timetables.download', $timetable) }}"
                       class="btn btn-primary btn-sm">

                        <i class="fas fa-download me-1"></i>
                        Download

                    </a>
                @endif -->

            </div>


            {{-- Body --}}

            <div class="card-body">

                @php

                    /*
                    |--------------------------------------------------------------------------
                    | Get Latest Timetable
                    |--------------------------------------------------------------------------
                    |
                    | This keeps the existing behaviour of showing the most recently
                    | uploaded timetable for the selected class.
                    |
                    */

                    $classTimetable =
                        \App\Models\Timetable::where(
                            'student_class_id',
                            $studentClass->id
                        )
                        ->where('status', 'active')
                        ->latest('created_at')
                        ->first();

                    $fileType = $classTimetable
                        ? strtolower(
                            $classTimetable->file_type ?? ''
                        )
                        : null;

                @endphp


                {{-- TIMETABLE EXISTS --}}

                @if($classTimetable)


                    {{-- Timetable Information --}}

                    <!-- <div class="alert alert-light border mb-4">

                        <div class="row align-items-center">

                            <div class="col-md-8">

                                <div class="d-flex align-items-center">

                                    <div class="me-3">

                                        @if($fileType === 'pdf')

                                            <i class="fas fa-file-pdf fa-2x text-danger"></i>

                                        @elseif(in_array($fileType, ['jpg', 'jpeg', 'png', 'gif', 'webp']))

                                            <i class="fas fa-file-image fa-2x text-success"></i>

                                        @elseif(in_array($fileType, ['xls', 'xlsx']))

                                            <i class="fas fa-file-excel fa-2x text-success"></i>

                                        @else

                                            <i class="fas fa-file fa-2x text-secondary"></i>

                                        @endif

                                    </div>

                                    <div>

                                        <h6 class="fw-bold mb-1">
                                            {{ $classTimetable->title }}
                                        </h6>

                                        <div class="small text-muted">

                                            <span class="me-3">

                                                <i class="fas fa-file me-1"></i>

                                                {{ strtoupper($fileType ?? 'FILE') }}

                                            </span>

                                            @if($classTimetable->file_name)

                                                <span>

                                                    <i class="fas fa-paperclip me-1"></i>

                                                    {{ $classTimetable->file_name }}

                                                </span>

                                            @endif

                                        </div>

                                    </div>

                                </div>

                            </div>

                            <div class="col-md-4 text-md-end mt-3 mt-md-0">

                                <a href="{{ route('timetables.download', $classTimetable) }}"
                                   class="btn btn-outline-primary btn-sm">

                                    <i class="fas fa-download me-1"></i>
                                    Download Timetable

                                </a>

                            </div>

                        </div>

                    </div> -->


                    <!-- {{-- Description --}}
                    @if($classTimetable->description)

                        <div class="mb-4">

                            <h6 class="fw-bold mb-2">

                                <i class="fas fa-align-left me-2"></i>

                                Description

                            </h6>

                            <div class="bg-light border rounded p-3">

                                {{ $classTimetable->description }}

                            </div>

                        </div>

                    @endif -->


                    {{-- PDF PREVIEW --}}

                    @if($fileType === 'pdf')

                        <div class="card border shadow-sm">

                            <div class="card-header bg-light d-flex justify-content-between align-items-center">

                                <strong>

                                    <i class="fas fa-file-pdf text-danger me-2"></i>

                                    PDF Timetable

                                </strong>

                                <a href="{{ route('timetables.download', $classTimetable) }}"
                                   class="btn btn-sm btn-primary">

                                    <i class="fas fa-download me-1"></i>

                                    Download

                                </a>

                            </div>

                            <div class="card-body p-0">

                                @if(Route::has('timetables.preview'))

                                    <iframe
                                        src="{{ route('timetables.preview', $classTimetable) }}"
                                        width="100%"
                                        height="800"
                                        style="border: 0; display: block; border-radius: 0 0 6px 6px;"
                                        title="Class Timetable PDF">
                                    </iframe>

                                @else

                                    <div class="p-4 text-center">
                                        <i class="fas fa-file-pdf text-danger fs-1 mb-3"></i>
                                        <h6 class="fw-bold">PDF preview route is not configured</h6>
                                        <p class="text-muted mb-3">
                                            The timetable is uploaded, but the application does not currently expose an inline preview endpoint.
                                        </p>
                                        <a href="{{ route('timetables.download', $classTimetable) }}" class="btn btn-primary">
                                            <i class="fas fa-download me-1"></i> Download PDF
                                        </a>
                                    </div>

                                @endif

                            </div>

                        </div>


                    {{-- IMAGE PREVIEW --}}

                    @elseif(
                        in_array(
                            $fileType,
                            [
                                'jpg',
                                'jpeg',
                                'png',
                                'gif',
                                'webp'
                            ]
                        )
                    )

                        <div class="card border shadow-sm">

                            <div class="card-header bg-light">

                                <strong>

                                    <i class="fas fa-image text-success me-2"></i>

                                    Timetable Image

                                </strong>

                            </div>

                            <div class="card-body text-center bg-light">

                                @if(Route::has('timetables.preview'))

                                    <img
                                        src="{{ route('timetables.preview', $classTimetable) }}"
                                        alt="{{ $classTimetable->title }}"
                                        class="img-fluid rounded shadow-sm"
                                        style="max-width: 100%; max-height: 800px; width: auto;"
                                    >

                                @else

                                    <div class="py-4">
                                        <i class="fas fa-image text-success fs-1 mb-3"></i>
                                        <p class="text-muted">Preview route is not configured.</p>
                                        <a href="{{ route('timetables.download', $classTimetable) }}" class="btn btn-primary">
                                            <i class="fas fa-download me-1"></i> Download File
                                        </a>
                                    </div>

                                @endif

                            </div>

                        </div>


                    {{-- EXCEL --}}

                    @elseif(
                        in_array(
                            $fileType,
                            [
                                'xls',
                                'xlsx',
                                'csv'
                            ]
                        )
                    )

                        <div class="text-center py-5">

                            <i class="fas fa-file-excel fa-4x text-success mb-3"></i>

                            <h5 class="fw-bold">
                                Spreadsheet Timetable
                            </h5>

                            <p class="text-muted">
                                This spreadsheet cannot be previewed directly in the browser.
                            </p>

                            <a href="{{ route('timetables.download', $classTimetable) }}"
                               class="btn btn-success">

                                <i class="fas fa-download me-1"></i>

                                Download Timetable

                            </a>

                        </div>


                    {{-- OTHER FILE TYPES --}}

                    @else

                        <div class="text-center py-5">

                            <i class="fas fa-file fa-4x text-secondary mb-3"></i>

                            <h5 class="fw-bold">
                                Preview Not Available
                            </h5>

                            <p class="text-muted">
                                This file type cannot be previewed in the browser.
                            </p>

                            <a href="{{ route('timetables.download', $classTimetable) }}"
                               class="btn btn-primary">

                                <i class="fas fa-download me-1"></i>

                                Download File

                            </a>

                        </div>

                    @endif


                @else

                    <div class="text-center py-5">

                        <i class="fas fa-calendar-times fa-4x text-muted mb-3"></i>

                        <h5 class="fw-bold">
                            No Timetable Uploaded
                        </h5>

                        <p class="text-muted mb-4">
                            No active timetable has been uploaded for this class yet.
                        </p>

                        <a href="{{ route('timetables.create', [
                            'class_id' => $studentClass->id
                        ]) }}"
                           class="btn btn-primary">

                            <i class="fas fa-plus me-1"></i>

                            Upload Timetable

                        </a>

                    </div>

                @endif

            </div>

        </div>

    </div>



    {{-- ================================================================
        PROMOTIONS TAB
    ================================================================= --}}

    <div class="tab-pane fade"
         id="promotions">

        <div class="card border-0 shadow-sm rounded-4">

            <div class="card-header bg-white py-3">

                <h5 class="mb-1 fw-bold">

                    <i class="fas fa-arrow-up me-2 text-danger"></i>

                    Student Promotion & Graduation

                </h5>

                <p class="text-muted mb-0">

                    Manage student promotions, repetitions and graduations for this class.

                </p>

            </div>


            <div class="card-body">

                @if(session('success'))

                    <div class="alert alert-success alert-dismissible fade show shadow-sm">

                        <i class="fas fa-check-circle me-2"></i>

                        {{ session('success') }}

                        <button type="button"
                                class="btn-close"
                                data-bs-dismiss="alert">
                        </button>

                    </div>

                @endif


                @if(session('error'))

                    <div class="alert alert-danger alert-dismissible fade show shadow-sm">

                        <i class="fas fa-exclamation-circle me-2"></i>

                        {{ session('error') }}

                        <button type="button"
                                class="btn-close"
                                data-bs-dismiss="alert">
                        </button>

                    </div>

                @endif


                <div class="row mb-4">

                    <div class="col-md-6">

                        <label class="form-label fw-semibold">

                            Select Academic Year
                            <span class="text-danger">*</span>

                        </label>

                        <select id="progressionAcademicYear"
                                class="form-select">

                            <option value="">
                                -- Select Academic Year --
                            </option>

                            @foreach(
                                \App\Models\AcademicYear::orderBy('name', 'desc')->get()
                                as $year
                            )

                                <option value="{{ $year->id }}">

                                    {{ $year->name }}

                                </option>

                            @endforeach

                        </select>

                    </div>


                    <div class="col-md-4 d-flex align-items-end">

                        <button id="goToProgressionsBtn"
                                class="btn btn-danger w-100"
                                type="button">

                            <i class="fas fa-arrow-right me-1"></i>

                            Manage Progressions

                        </button>

                    </div>

                </div>


                <div class="alert alert-info">

                    <i class="fas fa-info-circle me-2"></i>

                    <strong>Current Class:</strong>

                    {{ $studentClass->name }}

                    <br>

                    Select the academic year and click
                    "Manage Progressions" to promote, repeat or graduate students.

                </div>


                <div class="row mt-4">

                    <div class="col-md-4 mb-3">

                        <div class="card bg-light border-0">

                            <div class="card-body text-center">

                                <h3 class="text-primary mb-0">
                                    {{ $totalStudents }}
                                </h3>

                                <small class="text-muted">
                                    Total Students
                                </small>

                            </div>

                        </div>

                    </div>


                    <div class="col-md-4 mb-3">

                        <div class="card bg-light border-0">

                            <div class="card-body text-center">

                                <h3 class="text-success mb-0">
                                    {{ $maleCount }}
                                </h3>

                                <small class="text-muted">
                                    Male Students
                                </small>

                            </div>

                        </div>

                    </div>


                    <div class="col-md-4 mb-3">

                        <div class="card bg-light border-0">

                            <div class="card-body text-center">

                                <h3 class="text-info mb-0">
                                    {{ $femaleCount }}
                                </h3>

                                <small class="text-muted">
                                    Female Students
                                </small>

                            </div>

                        </div>

                    </div>

                </div>

            </div>

        </div>

    </div>


</div>

</div>

{{-- ================================================================
TAKE ATTENDANCE MODAL
================================================================= --}}

<div class="modal fade"
     id="takeAttendanceModal"
     tabindex="-1"
     aria-hidden="true"
     data-bs-backdrop="static"
     data-bs-keyboard="false">

<div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">

    <div class="modal-content border-0 shadow">

        <form id="attendanceForm"
              method="POST"
              action="{{ route('attendance.store.class', $studentClass->id) }}">

            @csrf

            <div class="modal-header bg-primary text-white">

                <h5 class="modal-title fw-bold">

                    <i class="fas fa-clipboard-check me-2"></i>

                    Take Attendance -
                    {{ $studentClass->name }}

                </h5>

                <button type="button"
                        class="btn-close btn-close-white"
                        data-bs-dismiss="modal"
                        id="closeAttendanceModal">
                </button>

            </div>


            <div class="modal-body">

                <div class="row mb-3">

                    <div class="col-md-4">

                        <label class="form-label fw-semibold">

                            Date
                            <span class="text-danger">*</span>

                        </label>

                        <input type="date"
                               id="attendanceDateInput"
                               name="attendance_date"
                               class="form-control"
                               value="{{ date('Y-m-d') }}"
                               max="{{ date('Y-m-d') }}"
                               required>

                    </div>


                    <div class="col-md-8">

                        <label class="form-label fw-semibold">
                            Quick Actions
                        </label>

                        <div>

                            <button type="button"
                                    class="btn btn-sm btn-success me-2"
                                    onclick="markAllAttendance('present')">

                                <i class="fas fa-check-double me-1"></i>
                                All Present

                            </button>

                            <button type="button"
                                    class="btn btn-sm btn-danger me-2"
                                    onclick="markAllAttendance('absent')">

                                <i class="fas fa-times-circle me-1"></i>
                                All Absent

                            </button>

                            <button type="button"
                                    class="btn btn-sm btn-warning me-2"
                                    onclick="markAllAttendance('late')">

                                <i class="fas fa-clock me-1"></i>
                                All Late

                            </button>

                            <button type="button"
                                    class="btn btn-sm btn-info"
                                    onclick="markAllAttendance('excused')">

                                <i class="fas fa-user-check me-1"></i>
                                All Excused

                            </button>

                        </div>

                    </div>

                </div>


                <div class="table-responsive"
                     style="max-height: 500px; overflow-y: auto;">

                    <table class="table table-bordered table-hover">

                        <thead class="table-light sticky-top">

                            <tr>

                                <th width="50">#</th>
                                <th>Student Name</th>
                                <th>Student ID</th>
                                <th>Gender</th>
                                <th width="150">Status</th>

                            </tr>

                        </thead>

                        <tbody>

                            @forelse($activeAssignments as $index => $assignment)

                                <tr>

                                    <td>
                                        {{ $index + 1 }}
                                    </td>

                                    <td class="fw-semibold">

                                        {{ $assignment->student->first_name }}
                                        {{ $assignment->student->last_name }}

                                    </td>

                                    <td>

                                        <span class="badge bg-light text-dark">

                                            {{ $assignment->student->student_id ?? 'N/A' }}

                                        </span>

                                    </td>

                                    <td>

                                        {{ $assignment->student->gender ?? 'N/A' }}

                                    </td>

                                    <td>

                                        <select
                                            name="attendance[{{ $assignment->student->id }}][status]"
                                            class="form-select form-select-sm attendance-status"
                                            required>

                                            <option value="present" selected>
                                                Present
                                            </option>

                                            <option value="absent">
                                                Absent
                                            </option>

                                            <option value="late">
                                                Late
                                            </option>

                                            <option value="excused">
                                                Excused
                                            </option>

                                        </select>

                                    </td>

                                </tr>

                            @empty

                                <tr>

                                    <td colspan="5"
                                        class="text-center py-4">

                                        <i class="fas fa-user-slash fs-3 text-muted d-block mb-2"></i>

                                        No students found in this class.

                                    </td>

                                </tr>

                            @endforelse

                        </tbody>

                    </table>

                </div>


                <div class="mt-3 text-muted small">

                    <i class="fas fa-info-circle me-1"></i>

                    Total Students:
                    {{ $activeAssignments->count() }}

                </div>

            </div>


            <div class="modal-footer">

                <button type="button"
                        class="btn btn-light"
                        data-bs-dismiss="modal"
                        id="cancelAttendanceBtn">

                    <i class="fas fa-times me-1"></i>
                    Cancel

                </button>

                <button type="button"
                        class="btn btn-primary"
                        id="saveAttendanceBtn">

                    <i class="fas fa-save me-1"></i>
                    Save Attendance

                </button>

            </div>

        </form>

    </div>

</div>

</div>

@endsection

{{-- ================================================================
MODALS
================================================================= --}}

@push('modals')

@php

$assignedStudentIds = $activeAssignments
    ->pluck('student_id')
    ->toArray();

$availableStudents =
    \App\Models\Student::whereNotIn(
        'id',
        $assignedStudentIds
    )
    ->orderBy('first_name')
    ->orderBy('last_name')
    ->get();


$assignedSubjectIds =
    $studentClass->subjects
        ->pluck('id')
        ->toArray();

$availableSubjects =
    \App\Models\Subject::whereNotIn(
        'id',
        $assignedSubjectIds
    )
    ->orderBy('name')
    ->get();

@endphp

{{-- ADD SUBJECT MODAL --}}

<div class="modal fade"
     id="addSubjectModal"
     tabindex="-1"
     aria-hidden="true">

<div class="modal-dialog modal-dialog-centered">

    <div class="modal-content border-0 shadow">

        <form action="{{ route('classes.subject.attach', $studentClass->id) }}"
              method="POST">

            @csrf

            <div class="modal-header bg-primary text-white">

                <h5 class="modal-title fw-bold">

                    <i class="fas fa-book-open me-2"></i>

                    Assign Subject

                </h5>

                <button type="button"
                        class="btn-close btn-close-white"
                        data-bs-dismiss="modal">
                </button>

            </div>


            <div class="modal-body">

                @if($availableSubjects->count() > 0)

                    <div class="mb-3">

                        <label class="form-label fw-semibold">
                            Select Subject
                        </label>

                        <select name="subject_id"
                                class="form-select"
                                required>

                            <option value="">
                                -- Choose Subject --
                            </option>

                            @foreach($availableSubjects as $subject)

                                <option value="{{ $subject->id }}">

                                    {{ $subject->name }}

                                    @if($subject->code)
                                        ({{ $subject->code }})
                                    @endif

                                    @if($subject->education_level)
                                        - {{ $subject->education_level }}
                                    @endif

                                </option>

                            @endforeach

                        </select>

                    </div>

                    <div class="alert alert-info small mb-0">

                        <i class="fas fa-info-circle me-1"></i>

                        Only subjects not already assigned to this class are shown.

                    </div>

                @else

                    <div class="text-center py-4">

                        <i class="fas fa-book-open fs-1 text-muted mb-3 d-block"></i>

                        <h5>
                            No Subjects Available
                        </h5>

                        <p class="text-muted mb-0">

                            All subjects are already assigned to this class.

                        </p>

                        <a href="{{ route('subjects.create') }}"
                           class="btn btn-sm btn-outline-primary mt-3">

                            <i class="fas fa-plus me-1"></i>

                            Create New Subject

                        </a>

                    </div>

                @endif

            </div>


            <div class="modal-footer">

                <button type="button"
                        class="btn btn-light"
                        data-bs-dismiss="modal">

                    Cancel

                </button>

                <button type="submit"
                        class="btn btn-primary"
                        {{ $availableSubjects->count() === 0 ? 'disabled' : '' }}>

                    <i class="fas fa-plus me-1"></i>

                    Assign Subject

                </button>

            </div>

        </form>

    </div>

</div>

</div>

{{-- ASSIGN PREFECT MODAL --}}

<div class="modal fade"
     id="assignPrefectModal"
     tabindex="-1"
     aria-hidden="true">

<div class="modal-dialog modal-dialog-centered">

    <div class="modal-content border-0 shadow">

        <form action="{{ route('student-classes.assign-prefect', $studentClass->id) }}"
              method="POST">

            @csrf

            <div class="modal-header bg-dark text-white">

                <h5 class="modal-title fw-bold">

                    <i class="fas fa-user-shield me-2"></i>

                    Assign Class Prefect

                </h5>

                <button type="button"
                        class="btn-close btn-close-white"
                        data-bs-dismiss="modal">
                </button>

            </div>


            <div class="modal-body">

                @if($activeAssignments->count() > 0)

                    <div class="mb-3">

                        <label class="form-label fw-semibold">
                            Select Student
                        </label>

                        <select name="student_id"
                                class="form-select"
                                required>

                            <option value="">
                                -- Choose Student --
                            </option>

                            @foreach($activeAssignments as $assignment)

                                <option value="{{ $assignment->student->id }}"
                                    {{ ($studentClass->class_prefect_id ?? '') == $assignment->student->id ? 'selected' : '' }}>

                                    {{ $assignment->student->first_name }}
                                    {{ $assignment->student->last_name }}

                                    ({{ $assignment->student->student_id ?? 'N/A' }})

                                </option>

                            @endforeach

                        </select>

                    </div>

                @else

                    <div class="text-center py-4">

                        <i class="fas fa-user-slash fs-1 text-muted mb-3 d-block"></i>

                        <h5>
                            No Students Available
                        </h5>

                        <p class="text-muted mb-0">
                            No active students found in this class.
                        </p>

                    </div>

                @endif

            </div>


            <div class="modal-footer">

                <button type="button"
                        class="btn btn-light"
                        data-bs-dismiss="modal">

                    Cancel

                </button>

                <button type="submit"
                        class="btn btn-dark"
                        {{ $activeAssignments->count() === 0 ? 'disabled' : '' }}>

                    <i class="fas fa-save me-1"></i>

                    Save Prefect

                </button>

            </div>

        </form>

    </div>

</div>

</div>

@endpush

{{-- ================================================================
SCRIPTS
================================================================= --}}

@push('scripts')

<script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>

<script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>

(function () {

    'use strict';


    // ============================================================
    // ROUTES CONFIGURATION
    // ============================================================

    const ROUTES = {

        attendanceAjax:
            @if($hasAttendanceRoutes)
                '{{ route("attendance.ajax", $studentClass->id) }}'
            @else
                null
            @endif,

        attendanceStore:
            @if($hasAttendanceRoutes)
                '{{ route("attendance.store.class", $studentClass->id) }}'
            @else
                null
            @endif,

        broadsheetAjax:
            @if($hasBroadsheetRoute)
                '{{ route("broadsheet.ajax") }}'
            @else
                null
            @endif,

        progressionIndex:
            @if($hasProgressionRoute)
                '{{ route("student-progressions.index") }}'
            @else
                null
            @endif,

        csrfToken:
            '{{ csrf_token() }}',

        classId:
            {{ $studentClass->id }},

        className:
            @json($studentClass->name)

    };


    // ============================================================
    // DOM READY
    // ============================================================

    document.addEventListener(
        'DOMContentLoaded',
        function () {

            initializeResults();

            initializeAttendance();

            initializeAttendanceSave();

            initializeModalHandlers();

            initializeProgressions();

        }
    );



    // ============================================================
    // RESULTS
    // ============================================================

    function initializeResults() {

        const loadBtn =
            document.getElementById('loadResultsBtn');

        if (!loadBtn) {
            return;
        }


        loadBtn.addEventListener(
            'click',
            loadClassResults
        );


        const searchInput =
            document.getElementById('searchInput');

        const positionFilter =
            document.getElementById('positionFilter');

        const performanceFilter =
            document.getElementById('performanceFilter');


        if (searchInput) {

            searchInput.addEventListener(
                'input',
                filterResultRows
            );

        }


        if (positionFilter) {

            positionFilter.addEventListener(
                'change',
                filterResultRows
            );

        }


        if (performanceFilter) {

            performanceFilter.addEventListener(
                'change',
                filterResultRows
            );

        }

    }



    function loadClassResults() {

        const academicYearId =
            document.getElementById(
                'academicYearSelect'
            )?.value;

        const termId =
            document.getElementById(
                'termSelect'
            )?.value;


        if (!academicYearId || !termId) {

            Swal.fire({

                icon: 'warning',

                title: 'Missing Selection',

                text:
                    'Please select both Academic Year and Term.'

            });

            return;

        }


        const container =
            document.getElementById(
                'resultsContainer'
            );


        if (!container) {
            return;
        }


        container.innerHTML = `

            <div class="text-center py-5">

                <div class="spinner-border text-primary mb-3"
                     role="status">

                    <span class="visually-hidden">
                        Loading...
                    </span>

                </div>

                <h5>
                    Loading Results...
                </h5>

                <p class="text-muted">
                    Please wait while the class results are loaded.
                </p>

            </div>

        `;


        document.getElementById(
            'filterSection'
        ).style.display = 'none';


        if (!ROUTES.broadsheetAjax) {

            container.innerHTML = `

                <div class="text-center py-5">

                    <i class="fas fa-exclamation-triangle
                              text-danger fs-1 mb-3 d-block"></i>

                    <h5>
                        Results Route Not Available
                    </h5>

                    <p class="text-muted">
                        The broadsheet AJAX route is not configured.
                    </p>

                </div>

            `;

            return;

        }


        fetch(
            ROUTES.broadsheetAjax,
            {

                method: 'POST',

                headers: {

                    'Content-Type':
                        'application/json',

                    'X-CSRF-TOKEN':
                        ROUTES.csrfToken,

                    'Accept':
                        'application/json'

                },

                body: JSON.stringify({

                    academic_year_id:
                        academicYearId,

                    term_id:
                        termId,

                    student_class_id:
                        ROUTES.classId

                })

            }

        )

        .then(async response => {

            const data =
                await response.json();

            if (!response.ok) {

                throw new Error(
                    data.message ||
                    `Request failed with status ${response.status}`
                );

            }

            return data;

        })

        .then(data => {

            if (
                !data.success ||
                !Array.isArray(data.students)
            ) {

                container.innerHTML = `

                    <div class="text-center py-5 text-muted">

                        <i class="fas fa-chart-line fs-1 mb-3 d-block"></i>

                        <h5>
                            No Results Found
                        </h5>

                        <p>
                            ${escapeHtml(
                                data.message ||
                                'No results are available for the selected Academic Year and Term.'
                            )}
                        </p>

                    </div>

                `;

                return;

            }


            renderResults(data);


            document.getElementById(
                'filterSection'
            ).style.display = 'block';

        })

        .catch(error => {

            console.error(
                'Results loading error:',
                error
            );


            container.innerHTML = `

                <div class="text-center py-5">

                    <i class="fas fa-exclamation-circle
                              text-danger fs-1 mb-3 d-block"></i>

                    <h5>
                        Error Loading Results
                    </h5>

                    <p class="text-danger">
                        ${escapeHtml(error.message)}
                    </p>

                    <button type="button"
                            class="btn btn-outline-primary mt-2"
                            onclick="document.getElementById('loadResultsBtn').click()">

                        <i class="fas fa-redo me-1"></i>

                        Retry

                    </button>

                </div>

            `;

        });

    }



    function renderResults(data) {

        const container =
            document.getElementById(
                'resultsContainer'
            );


        const subjects =
            Array.isArray(data.subjects)
                ? data.subjects
                : [];


        const students =
            Array.isArray(data.students)
                ? data.students
                : [];


        const results =
            data.results || {};


        const rankings =
            data.rankings || {};


        const positions =
            data.positions || {};


        if (
            subjects.length === 0 ||
            students.length === 0
        ) {

            container.innerHTML = `

                <div class="text-center py-5 text-muted">

                    <i class="fas fa-inbox fs-1 mb-3 d-block"></i>

                    <h5>
                        No Results Available
                    </h5>

                    <p>
                        No results were found for the selected criteria.
                    </p>

                </div>

            `;

            return;

        }


        let html = `

            <div class="table-responsive">

                <table
                    class="table table-bordered table-hover align-middle"
                    id="broadsheet-table">

                    <thead class="table-primary">

                        <tr>

                            <th class="text-center"
                                style="min-width:100px;">

                                Student ID

                            </th>

                            <th style="min-width:230px;">

                                Student Name

                            </th>

        `;


        subjects.forEach(subject => {

            html += `

                <th class="text-center"
                    style="min-width:110px;">

                    ${escapeHtml(subject.name || 'Subject')}

                </th>

            `;

        });


        html += `

                            <th class="text-center bg-light">
                                Total
                            </th>

                            <th class="text-center bg-light">
                                Average
                            </th>

                            <th class="text-center bg-light">
                                Position
                            </th>

                        </tr>

                    </thead>

                    <tbody id="broadsheet-tbody">

        `;


        students.forEach(student => {

            const studentId =
                student.id;


            const studentNumber =
                student.student_id ||
                student.id ||
                'N/A';


            const studentName =
                student.full_name ||
                student.name ||
                [
                    student.first_name,
                    student.middle_name,
                    student.last_name
                ]
                .filter(Boolean)
                .join(' ') ||
                'N/A';


            const ranking =
                rankings[studentId] || {};


            const total =
                Number(
                    ranking.total || 0
                );


            const average =
                Number(
                    ranking.average || 0
                );


            const position =
                Number(
                    positions[studentId] || 0
                );


            html += `

                <tr class="student-result-row"

                    data-name="${escapeHtml(
                        studentName.toLowerCase()
                    )}"

                    data-id="${escapeHtml(
                        String(studentNumber).toLowerCase()
                    )}"

                    data-position="${position}"

                    data-average="${average}">

                    <td class="text-center">

                        <span class="badge bg-light text-dark">

                            ${escapeHtml(
                                String(studentNumber)
                            )}

                        </span>

                    </td>

                    <td class="fw-semibold">

                        ${escapeHtml(studentName)}

                    </td>

            `;


            subjects.forEach(subject => {

                const key =
                    `${studentId}_${subject.id}`;


                const result =
                    results[key] || {};


                const mark =
                    result.total_score ??
                    result.score ??
                    0;


                const grade =
                    result.grade ||
                    '';


                html += `

                    <td class="text-center">

                        <span class="badge bg-light text-dark border">

                            ${escapeHtml(
                                String(mark)
                            )}

                        </span>

                        ${
                            grade
                                ? `
                                    <small class="d-block
                                                  text-muted
                                                  mt-1">

                                        ${escapeHtml(
                                            String(grade)
                                        )}

                                    </small>
                                  `
                                : ''
                        }

                    </td>

                `;

            });


            html += `

                    <td class="text-center fw-bold">

                        ${total.toLocaleString()}

                    </td>

                    <td class="text-center">

                        <span class="badge bg-light text-dark border">

                            ${average.toFixed(1)}

                        </span>

                    </td>

                    <td class="text-center">

                        ${renderPosition(position)}

                    </td>

                </tr>

            `;

        });


        const studentCount =
            Number(
                data.studentCount ??
                students.length
            );


        const subjectCount =
            Number(
                data.subjectCount ??
                subjects.length
            );


        const classAverage =
            Number(
                data.classAverage || 0
            );


        const passRate =
            Number(
                data.passRate || 0
            );


        html += `

                    </tbody>

                    <tfoot>

                        <tr class="table-light fw-bold">

                            <td colspan="${subjects.length + 5}">

                                Students:
                                ${studentCount}

                                &nbsp; | &nbsp;

                                Subjects:
                                ${subjectCount}

                                &nbsp; | &nbsp;

                                Class Average:
                                ${classAverage.toFixed(1)}

                                &nbsp; | &nbsp;

                                Pass Rate:
                                ${passRate.toFixed(1)}%

                            </td>

                        </tr>

                    </tfoot>

                </table>

            </div>


            <div id="resultFilterStats"
                 class="small text-muted mt-2">

                Showing
                ${students.length}
                of
                ${students.length}
                students

            </div>


            <div class="mt-3 d-flex gap-2">

                <button type="button"
                        class="btn btn-sm btn-danger"
                        onclick="exportResultsToPDF()">

                    <i class="fas fa-file-pdf me-1"></i>

                    Export PDF

                </button>


                <button type="button"
                        class="btn btn-sm btn-success"
                        onclick="exportResultsToExcel()">

                    <i class="fas fa-file-excel me-1"></i>

                    Export Excel

                </button>


                <button type="button"
                        class="btn btn-sm btn-secondary"
                        onclick="printResults()">

                    <i class="fas fa-print me-1"></i>

                    Print

                </button>

            </div>

        `;


        container.innerHTML = html;

    }



    function renderPosition(position) {

        if (!position) {

            return `
                <span class="badge bg-light text-dark border">
                    —
                </span>
            `;

        }


        if (position === 1) {

            return `
                <span class="badge bg-warning text-dark">
                    🏆 1st
                </span>
            `;

        }


        if (position === 2) {

            return `
                <span class="badge bg-secondary">
                    🥈 2nd
                </span>
            `;

        }


        if (position === 3) {

            return `
                <span class="badge bg-danger">
                    🥉 3rd
                </span>
            `;

        }


        let suffix = 'th';


        if (
            position % 100 < 11 ||
            position % 100 > 13
        ) {

            switch(position % 10) {

                case 1:
                    suffix = 'st';
                    break;

                case 2:
                    suffix = 'nd';
                    break;

                case 3:
                    suffix = 'rd';
                    break;

            }

        }


        return `

            <span class="badge bg-light text-dark border">

                ${position}${suffix}

            </span>

        `;

    }



    function filterResultRows() {

        const search =
            (
                document.getElementById(
                    'searchInput'
                )?.value || ''
            )
            .toLowerCase()
            .trim();


        const position =
            document.getElementById(
                'positionFilter'
            )?.value || '';


        const performance =
            document.getElementById(
                'performanceFilter'
            )?.value || '';


        const rows =
            document.querySelectorAll(
                '#broadsheet-tbody .student-result-row'
            );


        let visible = 0;


        rows.forEach(row => {

            const name =
                row.dataset.name || '';


            const id =
                row.dataset.id || '';


            const rank =
                Number(
                    row.dataset.position || 999
                );


            const average =
                Number(
                    row.dataset.average || 0
                );


            let show = true;


            if (
                search &&
                !name.includes(search) &&
                !id.includes(search)
            ) {

                show = false;

            }


            if (
                show &&
                position === 'top3' &&
                rank > 3
            ) {

                show = false;

            }


            if (
                show &&
                position === 'top10' &&
                rank > 10
            ) {

                show = false;

            }


            if (
                show &&
                position === 'others' &&
                rank <= 10
            ) {

                show = false;

            }


            if (
                show &&
                performance === 'excellent' &&
                average < 80
            ) {

                show = false;

            }


            if (
                show &&
                performance === 'good' &&
                (
                    average < 70 ||
                    average >= 80
                )
            ) {

                show = false;

            }


            if (
                show &&
                performance === 'average' &&
                (
                    average < 50 ||
                    average >= 70
                )
            ) {

                show = false;

            }


            if (
                show &&
                performance === 'poor' &&
                average >= 50
            ) {

                show = false;

            }


            row.style.display =
                show ? '' : 'none';


            if (show) {
                visible++;
            }

        });


        const stats =
            document.getElementById(
                'resultFilterStats'
            );


        if (stats) {

            stats.textContent =
                `Showing ${visible} of ${rows.length} students`;

        }

    }



    window.resetResultFilters = function () {

        const searchInput =
            document.getElementById(
                'searchInput'
            );

        const positionFilter =
            document.getElementById(
                'positionFilter'
            );

        const performanceFilter =
            document.getElementById(
                'performanceFilter'
            );


        if (searchInput) {
            searchInput.value = '';
        }


        if (positionFilter) {
            positionFilter.value = '';
        }


        if (performanceFilter) {
            performanceFilter.value = '';
        }


        filterResultRows();

    };



    window.exportResultsToPDF = function () {

        const element =
            document.querySelector(
                '#resultsContainer .table-responsive'
            );


        if (!element) {

            Swal.fire({

                icon: 'warning',

                title: 'No Results',

                text: 'Load results before exporting.'

            });

            return;

        }


        if (
            typeof html2pdf ===
            'undefined'
        ) {

            Swal.fire({

                icon: 'error',

                title: 'Export Error',

                text: 'PDF export library is not available.'

            });

            return;

        }


        html2pdf()
            .set({

                margin: 0.3,

                filename:
                    `Class_Broadsheet_${ROUTES.classId}.pdf`,

                image: {
                    type: 'jpeg',
                    quality: 0.98
                },

                html2canvas: {
                    scale: 2
                },

                jsPDF: {
                    unit: 'in',
                    format: 'a3',
                    orientation: 'landscape'
                }

            })
            .from(element)
            .save();

    };



    window.exportResultsToExcel = function () {

        const table =
            document.getElementById(
                'broadsheet-table'
            );


        if (!table) {

            Swal.fire({

                icon: 'warning',

                title: 'No Results',

                text: 'Load results before exporting.'

            });

            return;

        }


        if (
            typeof XLSX ===
            'undefined'
        ) {

            Swal.fire({

                icon: 'error',

                title: 'Export Error',

                text: 'Excel export library is not available.'

            });

            return;

        }


        const workbook =
            XLSX.utils.book_new();


        const worksheet =
            XLSX.utils.table_to_sheet(
                table
            );


        XLSX.utils.book_append_sheet(
            workbook,
            worksheet,
            'Class Results'
        );


        XLSX.writeFile(

            workbook,

            `Class_Results_${ROUTES.classId}.xlsx`

        );

    };



    window.printResults = function () {

        const table =
            document.getElementById(
                'broadsheet-table'
            );


        if (!table) {

            Swal.fire({

                icon: 'warning',

                title: 'No Results',

                text: 'Load results before printing.'

            });

            return;

        }


        const printWindow =
            window.open(
                '',
                '_blank',
                'width=1400,height=900'
            );


        if (!printWindow) {

            Swal.fire({

                icon: 'error',

                title: 'Popup Blocked',

                text:
                    'Please allow popups for this site.'

            });

            return;

        }


        printWindow.document.write(`

            <html>

                <head>

                    <title>
                        Class Results - ${escapeHtml(ROUTES.className)}
                    </title>

                    <style>

                        body {
                            font-family: Arial, sans-serif;
                            padding: 20px;
                        }

                        h2 {
                            text-align: center;
                            margin-bottom: 20px;
                        }

                        table {
                            width: 100%;
                            border-collapse: collapse;
                        }

                        th,
                        td {
                            border: 1px solid #ddd;
                            padding: 8px;
                            text-align: center;
                        }

                        th {
                            background: #f5f5f5;
                        }

                    </style>

            </head>

            <body>

                <h2>
                    Class Results -
                    ${escapeHtml(ROUTES.className)}
                </h2>

                ${table.outerHTML}

            </body>

        </html>

    `);


    printWindow.document.close();


    setTimeout(
        () => printWindow.print(),
        500
    );

};



// ============================================================
// ATTENDANCE
// ============================================================

function initializeAttendance() {

    const loadBtn =
        document.getElementById(
            'loadAttendanceBtn'
        );


    if (!loadBtn) {
        return;
    }


    loadBtn.addEventListener(
        'click',
        loadAttendanceRecords
    );


    setTimeout(
        loadAttendanceRecords,
        500
    );

}



window.loadAttendanceRecords =
    function () {

        const date =
            document.getElementById(
                'attendanceDate'
            )?.value || '';


        const month =
            document.getElementById(
                'attendanceMonth'
            )?.value || '';


        const year =
            document.getElementById(
                'attendanceYear'
            )?.value || '';


        const container =
            document.getElementById(
                'attendanceContainer'
            );


        if (!container) {
            return;
        }


        if (!date && !month) {

            container.innerHTML = `

                <div class="text-center py-5 text-muted">

                    <i class="fas fa-calendar-times fs-1 mb-3 d-block"></i>

                    <h5>
                        Select Attendance Criteria
                    </h5>

                    <p>
                        Select a date or month.
                    </p>

                </div>

            `;

            return;

        }


        if (!ROUTES.attendanceAjax) {

            container.innerHTML = `

                <div class="text-center py-5">

                    <i class="fas fa-exclamation-circle
                              text-danger fs-1 mb-3 d-block"></i>

                    <h5>
                        Attendance Route Not Available
                    </h5>

                </div>

            `;

            return;

        }


        container.innerHTML = `

            <div class="text-center py-5">

                <div class="spinner-border text-primary mb-3">
                </div>

                <h5>
                    Loading Attendance...
                </h5>

            </div>

        `;


        fetch(

            ROUTES.attendanceAjax,

            {

                method: 'POST',

                headers: {

                    'Content-Type':
                        'application/json',

                    'X-CSRF-TOKEN':
                        ROUTES.csrfToken,

                    'Accept':
                        'application/json'

                },

                body: JSON.stringify({

                    date,
                    month,
                    year

                })

            }

        )

        .then(response => response.json())

        .then(data => {

            if (data.success) {

                renderAttendanceTable(
                    data
                );

            } else {

                container.innerHTML = `

                    <div class="text-center py-5 text-muted">

                        <i class="fas fa-calendar-times fs-1 mb-3 d-block"></i>

                        <h5>
                            No Attendance Records
                        </h5>

                        <p>
                            ${escapeHtml(
                                data.message ||
                                'No attendance records found.'
                            )}
                        </p>

                    </div>

                `;

            }

        })

        .catch(error => {

            console.error(
                'Attendance error:',
                error
            );


            container.innerHTML = `

                <div class="text-center py-5">

                    <i class="fas fa-exclamation-circle
                              text-danger fs-1 mb-3 d-block"></i>

                    <h5>
                        Error Loading Attendance
                    </h5>

                    <p class="text-danger">
                        ${escapeHtml(error.message)}
                    </p>

                </div>

            `;

        });

    };



function renderAttendanceTable(data) {

    const container =
        document.getElementById(
            'attendanceContainer'
        );


    let html = `

        <div class="table-responsive">

            <table
                class="table table-bordered table-hover align-middle"
                id="attendanceTable">

                <thead class="table-light">

                    <tr>

                        <th>#</th>
                        <th>Date</th>
                        <th>Student Name</th>
                        <th>Student ID</th>
                        <th>Gender</th>
                        <th>Status</th>
                        <th>Remarks</th>

                    </tr>

                </thead>

                <tbody>

    `;


    const records =
        data.records || [];


    records.forEach(
        (record, index) => {

            const status =
                (
                    record.status ||
                    ''
                )
                .toLowerCase();


            let badge =
                '<span class="badge bg-secondary">N/A</span>';


            if (status === 'present') {

                badge =
                    '<span class="badge bg-success">Present</span>';

            } else if (status === 'absent') {

                badge =
                    '<span class="badge bg-danger">Absent</span>';

            } else if (status === 'late') {

                badge =
                    '<span class="badge bg-warning text-dark">Late</span>';

            } else if (status === 'excused') {

                badge =
                    '<span class="badge bg-info">Excused</span>';

            }


            const studentName =
                record.student_name ||
                [
                    record.student?.first_name,
                    record.student?.middle_name,
                    record.student?.last_name
                ]
                .filter(Boolean)
                .join(' ') ||
                'N/A';


            const studentId =
                record.student_id ||
                record.student?.student_id ||
                'N/A';


            const gender =
                record.gender ||
                record.student?.gender ||
                'N/A';


            const attendanceDate =
                record.date ||
                record.attendance_date ||
                '-';


            const remarks =
                record.remarks ||
                '-';


            html += `

                <tr>

                    <td>
                        ${index + 1}
                    </td>

                    <td>
                        ${escapeHtml(
                            attendanceDate
                        )}
                    </td>

                    <td class="fw-semibold">
                        ${escapeHtml(
                            studentName
                        )}
                    </td>

                    <td>
                        ${escapeHtml(
                            studentId
                        )}
                    </td>

                    <td>
                        ${escapeHtml(
                            gender
                        )}
                    </td>

                    <td>
                        ${badge}
                    </td>

                    <td>
                        ${escapeHtml(
                            remarks
                        )}
                    </td>

                </tr>

            `;

        }
    );


    if (records.length === 0) {

        html += `

            <tr>

                <td colspan="7"
                    class="text-center py-4">

                    No records found.

                </td>

            </tr>

        `;

    }


    html += `

                </tbody>

            </table>

        </div>

        <div class="small text-muted mt-2">

            Total Records:
            ${records.length}

        </div>

    `;


    container.innerHTML =
        html;

}



window.markAllAttendance =
    function (status) {

        const selects =
            document.querySelectorAll(
                '.attendance-status'
            );


        selects.forEach(
            select => {
                select.value = status;
            }
        );

    };



window.exportAttendanceToExcel =
    function () {

        const table =
            document.getElementById(
                'attendanceTable'
            );


        if (!table) {

            Swal.fire({

                icon: 'warning',

                title: 'No Data',

                text:
                    'Load attendance records first.'

            });

            return;

        }


        const workbook =
            XLSX.utils.book_new();


        const worksheet =
            XLSX.utils.table_to_sheet(
                table
            );


        XLSX.utils.book_append_sheet(
            workbook,
            worksheet,
            'Attendance'
        );


        XLSX.writeFile(

            workbook,

            `Attendance_${ROUTES.className}.xlsx`

        );

    };



window.printAttendance =
    function () {

        const table =
            document.getElementById(
                'attendanceTable'
            );


        if (!table) {

            Swal.fire({

                icon: 'warning',

                title: 'No Data',

                text:
                    'Load attendance records first.'

            });

            return;

        }


        const printWindow =
            window.open(
                '',
                '_blank',
                'width=1200,height=800'
            );


        if (!printWindow) {
            return;
        }


        printWindow.document.write(`

            <html>

                <head>

                    <title>
                        Attendance -
                        ${escapeHtml(ROUTES.className)}
                    </title>

                    <style>

                        body {
                            font-family: Arial;
                            padding: 20px;
                        }

                        table {
                            width: 100%;
                            border-collapse: collapse;
                        }

                        th,
                        td {
                            border: 1px solid #ddd;
                            padding: 8px;
                        }

                        th {
                            background: #f5f5f5;
                        }

                    </style>

                </head>

                <body>

                    <h2>
                        Attendance -
                        ${escapeHtml(ROUTES.className)}
                    </h2>

                    ${table.outerHTML}

                </body>

            </html>

        `);


        printWindow.document.close();


        setTimeout(
            () => printWindow.print(),
            500
        );

    };



// ============================================================
// ATTENDANCE SAVE
// ============================================================

function initializeAttendanceSave() {

    const saveBtn =
        document.getElementById(
            'saveAttendanceBtn'
        );


    if (!saveBtn) {
        return;
    }


    saveBtn.addEventListener(
        'click',
        saveAttendance
    );

}



function saveAttendance() {

    const form =
        document.getElementById(
            'attendanceForm'
        );


    if (!form) {
        return;
    }


    const selects =
        form.querySelectorAll(
            '.attendance-status'
        );


    let valid = true;


    selects.forEach(
        select => {

            if (!select.value) {

                valid = false;

                select.classList.add(
                    'is-invalid'
                );

            } else {

                select.classList.remove(
                    'is-invalid'
                );

            }

        }
    );


    if (!valid) {

        Swal.fire({

            icon: 'warning',

            title: 'Incomplete Attendance',

            text:
                'Please select a status for all students.'

        });

        return;

    }


    if (!ROUTES.attendanceStore) {

        Swal.fire({

            icon: 'error',

            title: 'Route Not Available',

            text:
                'Attendance save route is not configured.'

        });

        return;

    }


    const saveBtn =
        document.getElementById(
            'saveAttendanceBtn'
        );


    saveBtn.disabled = true;


    saveBtn.innerHTML = `

        <i class="fas fa-spinner fa-spin me-1"></i>

        Saving...

    `;


    fetch(

        ROUTES.attendanceStore,

        {

            method: 'POST',

            headers: {

                'X-CSRF-TOKEN':
                    ROUTES.csrfToken,

                'Accept':
                    'application/json'

            },

            body:
                new FormData(form)

        }

    )

    .then(response => response.json())

    .then(data => {

        saveBtn.disabled = false;


        saveBtn.innerHTML = `

            <i class="fas fa-save me-1"></i>

            Save Attendance

        `;


        if (data.success) {

            const modal =
                document.getElementById(
                    'takeAttendanceModal'
                );


            const instance =
                bootstrap.Modal.getInstance(
                    modal
                );


            if (instance) {
                instance.hide();
            }


            Swal.fire({

                icon: 'success',

                title: 'Attendance Saved',

                text:
                    'Attendance has been saved successfully.',

                timer: 1800,

                showConfirmButton: false

            });


            setTimeout(
                loadAttendanceRecords,
                500
            );

        } else {

            throw new Error(
                data.message ||
                'Unable to save attendance.'
            );

        }

    })

    .catch(error => {

        saveBtn.disabled = false;


        saveBtn.innerHTML = `

            <i class="fas fa-save me-1"></i>

            Save Attendance

        `;


        Swal.fire({

            icon: 'error',

            title: 'Attendance Error',

            text:
                error.message

        });

    });

}



// ============================================================
// MODAL HANDLERS
// ============================================================

function forceRemoveModalBackdrops() {

    document
        .querySelectorAll(
            '.modal-backdrop'
        )
        .forEach(
            element => element.remove()
        );


    document.body.classList.remove(
        'modal-open'
    );


    document.body.style.removeProperty(
        'overflow'
    );


    document.body.style.removeProperty(
        'padding-right'
    );

}



function initializeModalHandlers() {

    document.addEventListener(
        'hidden.bs.modal',
        function () {

            setTimeout(
                forceRemoveModalBackdrops,
                100
            );

        }
    );

}



// ============================================================
// PROGRESSIONS
// ============================================================

function initializeProgressions() {

    const button =
        document.getElementById(
            'goToProgressionsBtn'
        );


    if (!button) {
        return;
    }


    button.addEventListener(
        'click',
        function () {

            const academicYear =
                document.getElementById(
                    'progressionAcademicYear'
                )?.value;


            if (!academicYear) {

                Swal.fire({

                    icon: 'warning',

                    title: 'Missing Selection',

                    text:
                        'Please select an Academic Year first.'

                });

                return;

            }


            if (!ROUTES.progressionIndex) {

                Swal.fire({

                    icon: 'error',

                    title: 'Route Not Available',

                    text:
                        'Student progression route is not configured.'

                });

                return;

            }


            window.location.href =
                `${ROUTES.progressionIndex}?class_id=${ROUTES.classId}&academic_year_id=${academicYear}`;

        }
    );

}



// ============================================================
// HTML ESCAPING
// ============================================================

function escapeHtml(value) {

    const div =
        document.createElement('div');


    div.textContent =
        value ?? '';


    return div.innerHTML;

}

})();

</script>

@endpush

{{-- ================================================================
STYLES
================================================================= --}}

@push('styles')

<style>

    .form-label {

        font-size: 0.85rem;

        font-weight: 600;

        margin-bottom: 0.4rem;

    }


    .table th {

        font-size: 13px;

        font-weight: 600;

        white-space: nowrap;

    }


    .table td {

        font-size: 13px;

        vertical-align: middle;

    }


    .card {

        border-radius: 12px;

    }


    .badge {

        font-weight: 500;

        padding: 5px 10px;

    }


    .table tbody tr:hover {

        background-color:
            rgba(0, 0, 0, 0.02);

        transition:
            background-color 0.2s ease;

    }


    .progress {

        background-color:
            #e9ecef;

    }


    .nav-tabs .nav-link {

        font-weight: 500;

        color: #495057;

    }


    .nav-tabs .nav-link.active {

        font-weight: 700;

    }


    #broadsheet-table th {

        vertical-align: middle;

    }


    #broadsheet-table td {

        vertical-align: middle;

    }


    .student-result-row {

        transition:
            background-color 0.2s ease;

    }


    .student-result-row:hover {

        background-color:
            rgba(13, 110, 253, 0.03);

    }


    .sticky-top {

        position: sticky;

        top: 0;

        z-index: 10;

    }


    .modal-xl {

        max-width: 1200px;

    }


    .modal-backdrop {

        z-index: 1040 !important;

    }


    .modal {

        z-index: 1050 !important;

    }


    body:not(.modal-open) {

        overflow: auto !important;

    }


    body:not(.modal-open) #app-container {

        pointer-events: all !important;

    }


    @media print {

        .no-print {

            display: none !important;

        }

        .btn {

            display: none !important;

        }

        .card {

            border: none !important;

            box-shadow: none !important;

        }

    }

</style>

@endpush
