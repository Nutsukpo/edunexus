@extends('layouts.master')

@section('title', 'Student Fee Account')

@section('content')

<div class="container-fluid py-4">

    @php
        $student = $studentFeeAccount->student;

        $studentName = $student
            ? trim(
                $student->first_name . ' ' .
                ($student->middle_name ?? '') . ' ' .
                $student->last_name
            )
            : 'Unknown Student';

        $total = (float) $studentFeeAccount->total_fees;
        $paid = (float) $studentFeeAccount->amount_paid;
        $balance = (float) $studentFeeAccount->balance;

        $paymentPercentage = $total > 0
            ? min(100, ($paid / $total) * 100)
            : 0;
    @endphp

    {{-- Header --}}
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">

        <div>

            <div class="d-flex align-items-center gap-2 mb-2">

                <a href="{{ route('student-fee-allocations.index') }}"
                   class="btn btn-sm btn-outline-secondary">

                    <i class="fas fa-arrow-left"></i>

                </a>

                <span class="text-muted">
                    Student Fee Accounts
                </span>

                <i class="fas fa-chevron-right text-muted small"></i>

                <span class="fw-semibold">
                    Account #{{ $studentFeeAccount->id }}
                </span>

            </div>

            <h4 class="fw-bold mb-1">
                {{ $studentName }}
            </h4>

            <p class="text-muted mb-0">
                {{ $student->student_id ?? '—' }}
            </p>

        </div>

        <a href="{{ route(
            'student-fee-allocations.create',
            ['student_id' => $studentFeeAccount->student_id]
        ) }}"
           class="btn btn-primary">

            <i class="fas fa-edit me-1"></i>
            Edit Allocation

        </a>

    </div>


    {{-- Account overview --}}
    <div class="row g-4 mb-4">

        {{-- Total --}}
        <div class="col-xl-4 col-md-6">

            <div class="card border-0 shadow-sm h-100">

                <div class="card-body">

                    <div class="d-flex justify-content-between">

                        <div>

                            <small class="text-muted d-block mb-1">
                                Total Fees
                            </small>

                            <h3 class="fw-bold mb-0">
                                GH₵ {{ number_format($total, 2) }}
                            </h3>

                        </div>

                        <div class="rounded-circle bg-primary bg-opacity-10
                                    text-primary d-flex align-items-center
                                    justify-content-center"
                             style="width:48px;height:48px;">

                            <i class="fas fa-file-invoice-dollar"></i>

                        </div>

                    </div>

                </div>

            </div>

        </div>


        {{-- Paid --}}
        <div class="col-xl-4 col-md-6">

            <div class="card border-0 shadow-sm h-100">

                <div class="card-body">

                    <div class="d-flex justify-content-between">

                        <div>

                            <small class="text-muted d-block mb-1">
                                Amount Paid
                            </small>

                            <h3 class="fw-bold text-success mb-0">
                                GH₵ {{ number_format($paid, 2) }}
                            </h3>

                        </div>

                        <div class="rounded-circle bg-success bg-opacity-10
                                    text-success d-flex align-items-center
                                    justify-content-center"
                             style="width:48px;height:48px;">

                            <i class="fas fa-check-circle"></i>

                        </div>

                    </div>

                </div>

            </div>

        </div>


        {{-- Balance --}}
        <div class="col-xl-4 col-md-6">

            <div class="card border-0 shadow-sm h-100">

                <div class="card-body">

                    <div class="d-flex justify-content-between">

                        <div>

                            <small class="text-muted d-block mb-1">
                                Outstanding Balance
                            </small>

                            <h3 class="fw-bold text-danger mb-0">
                                GH₵ {{ number_format($balance, 2) }}
                            </h3>

                        </div>

                        <div class="rounded-circle bg-danger bg-opacity-10
                                    text-danger d-flex align-items-center
                                    justify-content-center"
                             style="width:48px;height:48px;">

                            <i class="fas fa-wallet"></i>

                        </div>

                    </div>

                </div>

            </div>

        </div>

    </div>


    {{-- Student / account information --}}
    <div class="row g-4 mb-4">

        <div class="col-lg-7">

            <div class="card border-0 shadow-sm h-100">

                <div class="card-header bg-white border-0 py-3">

                    <h6 class="fw-bold mb-0">
                        <i class="fas fa-user-graduate text-primary me-2"></i>
                        Student Information
                    </h6>

                </div>

                <div class="card-body">

                    <div class="row g-4">

                        <div class="col-md-6">

                            <small class="text-muted d-block">
                                Student Name
                            </small>

                            <div class="fw-semibold">
                                {{ $studentName }}
                            </div>

                        </div>

                        <div class="col-md-6">

                            <small class="text-muted d-block">
                                Student ID
                            </small>

                            <div class="fw-semibold">
                                {{ $student->student_id ?? '—' }}
                            </div>

                        </div>

                        <div class="col-md-6">

                            <small class="text-muted d-block">
                                Class
                            </small>

                            <div class="fw-semibold">
                                {{ $studentFeeAccount->studentClass->name ?? '—' }}
                            </div>

                        </div>

                        <div class="col-md-6">

                            <small class="text-muted d-block">
                                Academic Year
                            </small>

                            <div class="fw-semibold">
                                {{ $studentFeeAccount->academicYear->name ?? '—' }}
                            </div>

                        </div>

                        <div class="col-md-6">

                            <small class="text-muted d-block">
                                Assignment
                            </small>

                            <div class="fw-semibold">
                                #{{ $studentFeeAccount->student_class_assignment_id ?? '—' }}
                            </div>

                        </div>

                        <div class="col-md-6">

                            <small class="text-muted d-block">
                                Account Status
                            </small>

                            @if($studentFeeAccount->status === 'paid')

                                <span class="badge bg-success">
                                    Paid
                                </span>

                            @elseif($studentFeeAccount->status === 'partial')

                                <span class="badge bg-warning text-dark">
                                    Partially Paid
                                </span>

                            @elseif($studentFeeAccount->status === 'overdue')

                                <span class="badge bg-danger">
                                    Overdue
                                </span>

                            @else

                                <span class="badge bg-secondary">
                                    Pending
                                </span>

                            @endif

                        </div>

                    </div>

                </div>

            </div>

        </div>


        {{-- Payment progress --}}
        <div class="col-lg-5">

            <div class="card border-0 shadow-sm h-100">

                <div class="card-header bg-white border-0 py-3">

                    <h6 class="fw-bold mb-0">
                        <i class="fas fa-chart-pie text-primary me-2"></i>
                        Payment Progress
                    </h6>

                </div>

                <div class="card-body">

                    <div class="d-flex justify-content-between mb-2">

                        <span class="text-muted">
                            Payment Progress
                        </span>

                        <strong>
                            {{ number_format($paymentPercentage, 1) }}%
                        </strong>

                    </div>

                    <div class="progress"
                         style="height:10px;">

                        <div class="progress-bar bg-success"
                             role="progressbar"
                             style="width: {{ $paymentPercentage }}%;"
                             aria-valuenow="{{ $paymentPercentage }}"
                             aria-valuemin="0"
                             aria-valuemax="100">
                        </div>

                    </div>

                    <div class="row mt-4">

                        <div class="col-6">

                            <small class="text-muted d-block">
                                Paid
                            </small>

                            <strong class="text-success">
                                GH₵ {{ number_format($paid, 2) }}
                            </strong>

                        </div>

                        <div class="col-6">

                            <small class="text-muted d-block">
                                Remaining
                            </small>

                            <strong class="text-danger">
                                GH₵ {{ number_format($balance, 2) }}
                            </strong>

                        </div>

                    </div>

                </div>

            </div>

        </div>

    </div>


    {{-- Fee items --}}
    <div class="card border-0 shadow-sm mb-4">

        <div class="card-header bg-white border-0 py-3">

            <div class="d-flex flex-wrap justify-content-between
                        align-items-center gap-2">

                <div>

                    <h6 class="fw-bold mb-1">
                        <i class="fas fa-list-check text-primary me-2"></i>
                        Allocated Fee Items
                    </h6>

                    <small class="text-muted">
                        Individual charges assigned to this student's account.
                    </small>

                </div>

                <span class="badge bg-light text-dark">
                    {{ $studentFeeAccount->feeItems->count() }} item(s)
                </span>

            </div>

        </div>

        <div class="table-responsive">

            <table class="table table-hover align-middle mb-0">

                <thead class="table-light">

                    <tr>

                        <th>#</th>
                        <th>Fee</th>
                        <th>Type</th>
                        <th>Required</th>
                        <th>Amount</th>
                        <th>Paid</th>
                        <th>Balance</th>
                        <th>Status</th>
                        <th class="text-end">Action</th>

                    </tr>

                </thead>

                <tbody>

                @forelse($studentFeeAccount->feeItems as $index => $item)

                    <tr>

                        <td>
                            {{ $index + 1 }}
                        </td>

                        <td>

                            <div class="fw-semibold">
                                {{ $item->fee_name }}
                            </div>

                            @if($item->description)

                                <small class="text-muted">
                                    {{ $item->description }}
                                </small>

                            @endif

                        </td>

                        <td>
                            {{ ucfirst(
                                str_replace('_', ' ', $item->fee_type)
                            ) }}
                        </td>

                        <td>

                            @if($item->is_required)

                                <span class="badge bg-danger">
                                    Required
                                </span>

                            @else

                                <span class="badge bg-light text-dark">
                                    Optional
                                </span>

                            @endif

                        </td>

                        <td class="fw-semibold">
                            GH₵ {{ number_format($item->amount, 2) }}
                        </td>

                        <td class="text-success fw-semibold">
                            GH₵ {{ number_format($item->paid_amount, 2) }}
                        </td>

                        <td class="text-danger fw-semibold">
                            GH₵ {{ number_format(
                                $item->remaining_amount,
                                2
                            ) }}
                        </td>

                        <td>

                            @if($item->status === 'paid')

                                <span class="badge bg-success">
                                    Paid
                                </span>

                            @elseif($item->status === 'partial')

                                <span class="badge bg-warning text-dark">
                                    Partial
                                </span>

                            @elseif($item->status === 'overdue')

                                <span class="badge bg-danger">
                                    Overdue
                                </span>

                            @else

                                <span class="badge bg-secondary">
                                    Pending
                                </span>

                            @endif

                        </td>

                        <td class="text-end">

                            @if(!in_array($item->status, ['paid', 'partial']))

                                <form method="POST"
                                      action="{{ route(
                                          'student-fee-allocations.items.destroy',
                                          $item
                                      ) }}"
                                      class="d-inline"
                                      onsubmit="return confirm(
                                          'Remove this fee item?'
                                      );">

                                    @csrf
                                    @method('DELETE')

                                    <button type="submit"
                                            class="btn btn-sm btn-outline-danger"
                                            title="Remove">

                                        <i class="fas fa-trash"></i>

                                    </button>

                                </form>

                            @else

                                <span class="text-muted small">
                                    Protected
                                </span>

                            @endif

                        </td>

                    </tr>

                @empty

                    <tr>

                        <td colspan="9"
                            class="text-center py-5">

                            <i class="fas fa-receipt fa-2x text-muted mb-3"></i>

                            <h6 class="fw-semibold">
                                No Fee Items Allocated
                            </h6>

                            <p class="text-muted mb-3">
                                This account has not yet been broken down
                                into individual fee items.
                            </p>

                            <a href="{{ route(
                                'student-fee-allocations.create',
                                ['student_id' => $studentFeeAccount->student_id]
                            ) }}"
                               class="btn btn-primary btn-sm">

                                <i class="fas fa-plus me-1"></i>
                                Allocate Fees

                            </a>

                        </td>

                    </tr>

                @endforelse

                </tbody>

                @if($studentFeeAccount->feeItems->count())

                    <tfoot class="table-light">

                        <tr>

                            <th colspan="4"
                                class="text-end">
                                Total
                            </th>

                            <th>
                                GH₵ {{ number_format(
                                    $studentFeeAccount->feeItems->sum('amount'),
                                    2
                                ) }}
                            </th>

                            <th class="text-success">
                                GH₵ {{ number_format(
                                    $studentFeeAccount->feeItems->sum('paid_amount'),
                                    2
                                ) }}
                            </th>

                            <th class="text-danger">
                                GH₵ {{ number_format(
                                    $studentFeeAccount->feeItems->sum('remaining_amount'),
                                    2
                                ) }}
                            </th>

                            <th colspan="2"></th>

                        </tr>

                    </tfoot>

                @endif

            </table>

        </div>

    </div>


    {{-- Payment history --}}
    <div class="card border-0 shadow-sm">

        <div class="card-header bg-white border-0 py-3">

            <div class="d-flex justify-content-between align-items-center">

                <div>

                    <h6 class="fw-bold mb-1">
                        <i class="fas fa-money-bill-wave text-success me-2"></i>
                        Payment History
                    </h6>

                    <small class="text-muted">
                        Completed payments recorded against this account.
                    </small>

                </div>

                <span class="badge bg-light text-dark">
                    {{ $studentFeeAccount->payments->count() }} payment(s)
                </span>

            </div>

        </div>

        <div class="table-responsive">

            <table class="table table-hover align-middle mb-0">

                <thead class="table-light">

                    <tr>
                        <th>Reference</th>
                        <th>Date</th>
                        <th>Method</th>
                        <th>Amount</th>
                        <th>Status</th>
                    </tr>

                </thead>

                <tbody>

                @forelse($studentFeeAccount->payments as $payment)

                    <tr>

                        <td>

                            <span class="fw-semibold">
                                {{ $payment->payment_reference
                                    ?? $payment->reference
                                    ?? '—' }}
                            </span>

                        </td>

                        <td>
                            {{ optional(
                                $payment->created_at
                            )->format('d M Y, h:i A') }}
                        </td>

                        <td>

                            @php
                                $method = $payment->payment_method
                                    ?? 'unknown';
                            @endphp

                            <span class="badge bg-light text-dark">

                                {{ ucfirst(
                                    str_replace('_', ' ', $method)
                                ) }}

                            </span>

                        </td>

                        <td class="fw-semibold">

                            GH₵ {{ number_format(
                                $payment->net_amount ?? 0,
                                2
                            ) }}

                        </td>

                        <td>

                            <span class="badge bg-success">
                                Completed
                            </span>

                        </td>

                    </tr>

                @empty

                    <tr>

                        <td colspan="5"
                            class="text-center py-4 text-muted">

                            No completed payments have been recorded
                            for this account.

                        </td>

                    </tr>

                @endforelse

                </tbody>

            </table>

        </div>

    </div>

</div>

@endsection