{{-- resources/views/fee-payments/reports/show.blade.php --}}
@extends('layouts.master')

@section('title', 'Fee Account Details')

@section('content')

<div class="container-fluid py-3">

    {{-- HEADER --}}
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white border-0 py-3">
            <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-2">

                <div>
                    <h3 class="mb-1 fw-bold">
                        <i class="fas fa-file-invoice-dollar text-primary me-2"></i>
                        Fee Account Details
                    </h3>

                    <div class="text-muted small">
                        Student fee account and payment history
                        @if($selectedTerm)
                            · {{ $selectedTerm->name }}
                        @endif
                    </div>
                </div>

                <div>
                    <a
                        href="{{ route('fee.payment.reports.index', request()->only(['academic_year_id', 'term_id', 'class_id'])) }}"
                        class="btn btn-secondary btn-sm"
                    >
                        <i class="fas fa-arrow-left me-1"></i>
                        Back to Reports
                    </a>
                </div>

            </div>
        </div>
    </div>


    {{-- ACCOUNT SUMMARY --}}
    <div class="row g-4 mb-4">

        {{-- STUDENT INFORMATION --}}
        <div class="col-xl-6">

            <div class="card border-0 shadow-sm h-100">

                <div class="card-header bg-white border-bottom py-3">
                    <h5 class="mb-0 fw-bold">
                        <i class="fas fa-user-graduate text-primary me-2"></i>
                        Student Information
                    </h5>
                </div>

                <div class="card-body">

                    <div class="table-responsive">

                        <table class="table table-bordered align-middle mb-0">

                            <tbody>

                                <tr>
                                    <th style="width: 35%;">
                                        Student
                                    </th>

                                    <td class="fw-semibold">
                                        @if($feeAccount->student)
                                            {{ trim(collect([
                                                $feeAccount->student->first_name ?? null,
                                                $feeAccount->student->middle_name ?? null,
                                                $feeAccount->student->last_name ?? null,
                                            ])->filter()->implode(' ')) ?: 'N/A' }}
                                        @else
                                            N/A
                                        @endif
                                    </td>
                                </tr>

                                <tr>
                                    <th>
                                        Student ID
                                    </th>

                                    <td>
                                        {{ $feeAccount->student->student_id ?? 'N/A' }}
                                    </td>
                                </tr>

                                <tr>
                                    <th>
                                        Class
                                    </th>

                                    <td>
                                        {{ $feeAccount->studentClass->name ?? 'N/A' }}
                                    </td>
                                </tr>

                                <tr>
                                    <th>
                                        Academic Year
                                    </th>

                                    <td>
                                        {{ $feeAccount->academicYear->name
                                            ?? $feeAccount->academicYear->year_name
                                            ?? 'N/A' }}
                                    </td>
                                </tr>

                                <tr>
                                    <th>Term</th>
                                    <td>
                                        {{ $selectedTerm?->name ?? 'All Terms' }}
                                    </td>
                                </tr>

                                <tr>
                                    <th>
                                        Account Status
                                    </th>

                                    <td>

                                        @php
                                            $accountStatus = strtolower(
                                                (string) ($feeAccount->status ?? 'pending')
                                            );
                                        @endphp

                                        @if($accountStatus === 'paid')

                                            <span class="badge bg-success">
                                                <i class="fas fa-check-circle me-1"></i>
                                                Paid
                                            </span>

                                        @elseif($accountStatus === 'partial')

                                            <span class="badge bg-warning text-dark">
                                                <i class="fas fa-clock me-1"></i>
                                                Partial
                                            </span>

                                        @else

                                            <span class="badge bg-danger">
                                                <i class="fas fa-exclamation-circle me-1"></i>
                                                Pending
                                            </span>

                                        @endif

                                    </td>
                                </tr>

                            </tbody>

                        </table>

                    </div>

                </div>

            </div>

        </div>


        {{-- FEE SUMMARY --}}
        <div class="col-xl-6">

            <div class="card border-0 shadow-sm h-100">

                <div class="card-header bg-white border-bottom py-3">
                    <h5 class="mb-0 fw-bold">
                        <i class="fas fa-money-check-dollar text-success me-2"></i>
                        Fee Summary
                    </h5>
                </div>

                <div class="card-body">

                    <div class="row g-3">

                        {{-- TOTAL FEES --}}
                        <div class="col-md-6">

                            <div class="border rounded p-3 h-100">

                                <div class="text-muted small text-uppercase fw-bold">
                                    Total Fees
                                </div>

                                <div class="fs-5 fw-bold mt-1">
                                    GHS {{ number_format((float) ($totalFees ?? $feeAccount->total_fees ?? 0), 2) }}
                                </div>

                            </div>

                        </div>


                        {{-- AMOUNT PAID --}}
                        <div class="col-md-6">

                            <div class="border rounded p-3 h-100">

                                <div class="text-muted small text-uppercase fw-bold">
                                    Amount Paid
                                </div>

                                <div class="fs-5 fw-bold text-success mt-1">
                                    GHS {{ number_format((float) ($totalPaid ?? 0), 2) }}
                                </div>

                            </div>

                        </div>


                        {{-- BALANCE --}}
                        <div class="col-md-6">

                            <div class="border rounded p-3 h-100">

                                <div class="text-muted small text-uppercase fw-bold">
                                    Balance
                                </div>

                                <div class="fs-5 fw-bold text-danger mt-1">
                                    GHS {{ number_format((float) ($balance ?? 0), 2) }}
                                </div>

                            </div>

                        </div>


                        {{-- DISCOUNT --}}
                        <div class="col-md-6">

                            <div class="border rounded p-3 h-100">

                                <div class="text-muted small text-uppercase fw-bold">
                                    Discount
                                </div>

                                <div class="fs-5 fw-bold mt-1">
                                    GHS {{ number_format((float) ($discount ?? $feeAccount->discount_applied ?? 0), 2) }}
                                </div>

                            </div>

                        </div>


                        {{-- WAIVER --}}
                        <div class="col-md-6">

                            <div class="border rounded p-3 h-100">

                                <div class="text-muted small text-uppercase fw-bold">
                                    Waiver
                                </div>

                                <div class="fs-5 fw-bold mt-1">
                                    GHS {{ number_format((float) ($waiver ?? $feeAccount->waiver_amount ?? 0), 2) }}
                                </div>

                            </div>

                        </div>


                        {{-- COLLECTION RATE --}}
                        <div class="col-md-6">

                            @php
                                $displayTotalFees = (float) ($totalFees ?? $feeAccount->total_fees ?? 0);
                                $paidAmount = (float) ($totalPaid ?? 0);

                                $collectionRate = $totalFees > 0
                                    ? min(100, max(0, ($paidAmount / $totalFees) * 100))
                                    : 0;
                            @endphp

                            <div class="border rounded p-3 h-100">

                                <div class="text-muted small text-uppercase fw-bold">
                                    Collection Rate
                                </div>

                                <div class="fs-5 fw-bold text-primary mt-1">
                                    {{ number_format($collectionRate, 1) }}%
                                </div>

                                <div class="progress mt-2" style="height: 7px;">
                                    <div
                                        class="progress-bar bg-primary"
                                        role="progressbar"
                                        style="width: {{ $collectionRate }}%;"
                                        aria-valuenow="{{ $collectionRate }}"
                                        aria-valuemin="0"
                                        aria-valuemax="100"
                                    ></div>
                                </div>

                            </div>

                        </div>

                    </div>

                </div>

            </div>

        </div>

    </div>


    {{-- PAYMENT HISTORY --}}
    <div class="card border-0 shadow-sm">

        <div class="card-header bg-white border-bottom py-3">

            <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-2">

                <div>
                    <h5 class="mb-1 fw-bold">
                        <i class="fas fa-clock-rotate-left text-primary me-2"></i>
                        Payment History
                    </h5>

                    <div class="text-muted small">
                        Completed and recorded transactions for this fee account
                    </div>
                </div>

                <div class="small text-muted">
                    {{ $paymentHistory->count() }} transaction(s)
                </div>

            </div>

        </div>


        <div class="card-body p-0">

            <div class="table-responsive">

                <table class="table table-hover table-bordered align-middle mb-0">

                    <thead class="table-light">

                        <tr>
                            <th>#</th>
                            <th>Date</th>
                            <th>Amount</th>
                            <th>Method</th>
                            <th>Reference</th>
                            <th>Status</th>
                            <th class="text-center">Receipt</th>
                        </tr>

                    </thead>


                    <tbody>

                        @forelse($paymentHistory as $payment)

                            <tr>

                                <td>
                                    {{ $loop->iteration }}
                                </td>


                                <td>

                                    @php
                                        $paymentDate = $payment->payment_date ?? null;
                                    @endphp

                                    @if($paymentDate)

                                        {{ \Carbon\Carbon::parse($paymentDate)->format('d M Y, H:i') }}

                                    @else

                                        N/A

                                    @endif

                                </td>


                                <td class="fw-semibold text-success">

                                    GHS
                                    {{ number_format(
                                        (float) ($payment->net_amount ?? $payment->amount ?? 0),
                                        2
                                    ) }}

                                </td>


                                <td>

                                    {{ ucfirst(
                                        str_replace(
                                            '_',
                                            ' ',
                                            $payment->payment_method ?? 'N/A'
                                        )
                                    ) }}

                                </td>


                                <td>

                                    {{ $payment->transaction_id
                                        ?? $payment->receipt_number
                                        ?? 'N/A' }}

                                </td>


                                <td>

                                    @php
                                        $paymentStatus = strtolower(
                                            (string) ($payment->status ?? 'pending')
                                        );
                                    @endphp

                                    @if($paymentStatus === 'completed')

                                        <span class="badge bg-success">
                                            <i class="fas fa-check-circle me-1"></i>
                                            Completed
                                        </span>

                                    @elseif($paymentStatus === 'pending')

                                        <span class="badge bg-warning text-dark">
                                            <i class="fas fa-clock me-1"></i>
                                            Pending
                                        </span>

                                    @else

                                        <span class="badge bg-secondary">
                                            {{ ucfirst($paymentStatus) }}
                                        </span>

                                    @endif

                                </td>


                                <td class="text-center">

                                    <a
                                        href="{{ route(
                                            'fee.payment.reports.receipt',
                                            $payment->id
                                        ) }}"
                                        class="btn btn-sm btn-outline-primary"
                                        title="Download Receipt"
                                    >
                                        <i class="fas fa-file-pdf"></i>
                                    </a>

                                </td>

                            </tr>

                        @empty

                            <tr>

                                <td colspan="7">

                                    <div class="text-center py-5 text-muted">

                                        <i class="fas fa-receipt fa-2x mb-3 d-block"></i>

                                        <div class="fw-bold text-dark mb-1">
                                            No Payments Recorded
                                        </div>

                                        <div>
                                            No payment transactions are currently recorded
                                            for this fee account.
                                        </div>

                                    </div>

                                </td>

                            </tr>

                        @endforelse

                    </tbody>


                    @if($paymentHistory->count() > 0)

                        <tfoot>

                            <tr class="fw-bold table-light">

                                <td colspan="2" class="text-end">
                                    TOTAL PAID
                                </td>

                                <td class="text-success">
                                    GHS {{ number_format((float) ($totalPaid ?? 0), 2) }}
                                </td>

                                <td colspan="4"></td>

                            </tr>

                        </tfoot>

                    @endif

                </table>

            </div>

        </div>

    </div>

</div>

@endsection