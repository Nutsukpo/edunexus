@extends('layouts.master')

@section('title', 'Payment Details - ' . ($payment->receipt_number ?? ''))

@section('content')

@php
    /*
    |--------------------------------------------------------------------------
    | SAFE DISPLAY VALUES
    |--------------------------------------------------------------------------
    | Keep the view independent of database columns that may not exist,
    | such as students.full_name.
    */
    $student = $payment->student;
    $feeAccount = $payment->studentFeeAccount;

    $studentName = $student
        ? trim(collect([
            $student->first_name ?? null,
            $student->middle_name ?? null,
            $student->last_name ?? null,
        ])->filter()->implode(' '))
        : 'N/A';

    if ($studentName === '') {
        $studentName = 'N/A';
    }

    $status = strtolower((string) ($payment->status ?? ''));
    $statusColors = [
        'pending' => 'warning',
        'completed' => 'success',
        'failed' => 'danger',
        'refunded' => 'info',
        'reversed' => 'secondary',
        'cancelled' => 'dark',
    ];

    $accountStatus = strtolower((string) ($feeAccount->status ?? ''));
    $accountStatusColors = [
        'paid' => 'success',
        'partial' => 'warning',
        'pending' => 'secondary',
        'overdue' => 'danger',
        'waived' => 'info',
    ];

    $totalFees = (float) ($feeAccount->total_fees ?? 0);
    $amountPaid = (float) ($feeAccount->amount_paid ?? 0);
    $balance = max(0, $totalFees - $amountPaid);

    $paymentAmount = (float) ($payment->amount ?? 0);
    $penaltyAmount = (float) ($payment->penalty_amount ?? 0);
    $discountAmount = (float) ($payment->discount_amount ?? 0);
    $netAmount = (float) ($payment->net_amount ?? 0);

    /*
    |--------------------------------------------------------------------------
    | PRINT / RECEIPT URL
    |--------------------------------------------------------------------------
    | Do not call a route that may not exist. The page can always be printed
    | by the browser, so this action remains functional without an extra route.
    */
@endphp

<style>
    .payment-show .info-label {
        font-weight: 600;
        color: #495057;
        width: 40%;
    }

    .payment-show .summary-card {
        transition: transform .15s ease, box-shadow .15s ease;
    }

    .payment-show .summary-card:hover {
        transform: translateY(-2px);
    }

    .payment-show .receipt-badge {
        font-size: .8rem;
        vertical-align: middle;
    }

    @media print {
        body * {
            visibility: hidden !important;
        }

        #paymentReceipt,
        #paymentReceipt * {
            visibility: visible !important;
        }

        #paymentReceipt {
            position: absolute;
            left: 0;
            top: 0;
            width: 100%;
        }

        .no-print {
            display: none !important;
        }

        .card {
            box-shadow: none !important;
            border: 1px solid #ddd !important;
        }
    }
</style>

<div class="container-fluid payment-show" id="paymentReceipt">

    {{-- PAGE HEADER --}}
    <div class="card shadow-sm border-0 rounded-4 mb-4">
        <div class="card-body py-3">
            <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-3">

                <div>
                    <div class="d-flex align-items-center gap-2 flex-wrap">
                        <h4 class="fw-bold mb-0">
                            <i class="fas fa-receipt text-success me-2"></i>
                            Payment Details
                        </h4>

                        <span class="badge bg-primary receipt-badge">
                            {{ $payment->receipt_number ?? 'N/A' }}
                        </span>
                    </div>

                    <small class="text-muted">
                        Detailed record of this fee payment
                    </small>
                </div>

                <div class="d-flex flex-wrap gap-2 no-print">
                                                
                    <a href="{{ route('fee-payments.receipt', $payment->id) }}" 
                         class="btn btn-success btn-sm" target="_blank">
                        <i class="fas fa-print"></i>
                            Print Receipt
                        </a>

                    @if($payment->status === 'pending')
                        <a href="{{ route('fee-payments.edit', $payment->id) }}"
                           class="btn btn-warning btn-sm">
                            <i class="fas fa-edit me-1"></i>
                            Edit
                        </a>
                    @endif

                    <a href="{{ route('fee-payments.index') }}"
                       class="btn btn-secondary btn-sm">
                        <i class="fas fa-arrow-left me-1"></i>
                        Back
                    </a>

                </div>
            </div>
        </div>
    </div>

    {{-- FLASH MESSAGES --}}
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show no-print" role="alert">
            <i class="fas fa-check-circle me-2"></i>
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show no-print" role="alert">
            <i class="fas fa-exclamation-circle me-2"></i>
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    {{-- PAYMENT + STUDENT INFORMATION --}}
    <div class="row g-4">

        {{-- PAYMENT INFORMATION --}}
        <div class="col-xl-6">
            <div class="card border-0 shadow-sm rounded-4 h-100">
                <div class="card-header bg-light border-0 py-3">
                    <h6 class="fw-bold mb-0">
                        <i class="fas fa-info-circle text-primary me-2"></i>
                        Payment Information
                    </h6>
                </div>

                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered align-middle mb-0">
                            <tbody>

                                <tr>
                                    <th class="info-label">Receipt Number</th>
                                    <td>
                                        <strong>
                                            {{ $payment->receipt_number ?? 'N/A' }}
                                        </strong>
                                    </td>
                                </tr>

                                <tr>
                                    <th class="info-label">Payment Date</th>
                                    <td>
                                        @if($payment->payment_date)
                                            {{ \Carbon\Carbon::parse($payment->payment_date)->format('d M, Y') }}
                                        @else
                                            N/A
                                        @endif
                                    </td>
                                </tr>

                                <tr>
                                    <th class="info-label">Payment Method</th>
                                    <td>
                                        <span class="badge bg-info text-dark">
                                            {{ ucfirst(str_replace('_', ' ', $payment->payment_method ?? 'N/A')) }}
                                        </span>
                                    </td>
                                </tr>

                                <tr>
                                    <th class="info-label">Payment Type</th>
                                    <td>
                                        <span class="badge bg-primary">
                                            {{ ucfirst($payment->payment_type ?? 'N/A') }}
                                        </span>
                                    </td>
                                </tr>

                                <tr>
                                    <th class="info-label">Status</th>
                                    <td>
                                        <span class="badge bg-{{ $statusColors[$status] ?? 'secondary' }}">
                                            {{ ucfirst($payment->status ?? 'N/A') }}
                                        </span>
                                    </td>
                                </tr>

                                <tr>
                                    <th class="info-label">Recorded By</th>
                                    <td>
                                        {{ ucfirst($payment->recorded_by ?? 'N/A') }}
                                    </td>
                                </tr>

                                @if($payment->transaction_id)
                                    <tr>
                                        <th class="info-label">Transaction ID</th>
                                        <td>{{ $payment->transaction_id }}</td>
                                    </tr>
                                @endif

                                @if($payment->bank_name)
                                    <tr>
                                        <th class="info-label">Bank Name</th>
                                        <td>{{ $payment->bank_name }}</td>
                                    </tr>
                                @endif

                                @if($payment->cheque_number)
                                    <tr>
                                        <th class="info-label">Cheque Number</th>
                                        <td>{{ $payment->cheque_number }}</td>
                                    </tr>
                                @endif

                                @if($payment->reference_number)
                                    <tr>
                                        <th class="info-label">Reference Number</th>
                                        <td>{{ $payment->reference_number }}</td>
                                    </tr>
                                @endif

                                @if($payment->notes)
                                    <tr>
                                        <th class="info-label">Notes</th>
                                        <td class="text-wrap">{{ $payment->notes }}</td>
                                    </tr>
                                @endif

                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        {{-- STUDENT INFORMATION --}}
        <div class="col-xl-6">
            <div class="card border-0 shadow-sm rounded-4 h-100">
                <div class="card-header bg-light border-0 py-3">
                    <h6 class="fw-bold mb-0">
                        <i class="fas fa-user-graduate text-primary me-2"></i>
                        Student Information
                    </h6>
                </div>

                <div class="card-body">

                    @if($student)

                        <div class="table-responsive">
                            <table class="table table-bordered align-middle mb-0">
                                <tbody>

                                    <tr>
                                        <th class="info-label">Student Name</th>
                                        <td>
                                            <strong>{{ $studentName }}</strong>
                                        </td>
                                    </tr>

                                    <tr>
                                        <th class="info-label">Student ID</th>
                                        <td>
                                            {{ $student->student_id ?? 'N/A' }}
                                        </td>
                                    </tr>

                                    @if(isset($payment->studentClassAssignment) && $payment->studentClassAssignment)
                                        <tr>
                                            <th class="info-label">Class</th>
                                            <td>
                                                {{ $payment->studentClassAssignment->studentClass->name ?? 'N/A' }}
                                            </td>
                                        </tr>

                                        <tr>
                                            <th class="info-label">Academic Year</th>
                                            <td>
                                                {{ $payment->studentClassAssignment->academicYear->name ?? 'N/A' }}
                                            </td>
                                        </tr>
                                    @endif

                                    @if($student->father_email)
                                        <tr>
                                            <th class="info-label">Parent Email</th>
                                            <td>{{ $student->father_email }}</td>
                                        </tr>
                                    @endif

                                    @if($student->father_phone)
                                        <tr>
                                            <th class="info-label">Parent Phone</th>
                                            <td>{{ $student->father_phone }}</td>
                                        </tr>
                                    @endif

                                </tbody>
                            </table>
                        </div>

                    @else
                        <div class="alert alert-warning mb-0">
                            <i class="fas fa-exclamation-triangle me-2"></i>
                            Student information is not available for this payment.
                        </div>
                    @endif

                </div>
            </div>
        </div>

    </div>

    {{-- FEE ACCOUNT --}}
    @if($feeAccount)
        <div class="card border-0 shadow-sm rounded-4 mt-4">
            <div class="card-header bg-light border-0 py-3">
                <h6 class="fw-bold mb-0">
                    <i class="fas fa-wallet text-primary me-2"></i>
                    Student Fee Account
                </h6>
            </div>

            <div class="card-body">

                <div class="row g-3">

                    <div class="col-md-3">
                        <div class="summary-card bg-light rounded-4 p-3 text-center h-100">
                            <small class="text-muted d-block mb-1">Total Fees</small>
                            <h5 class="fw-bold mb-0">
                                GHS {{ number_format($totalFees, 2) }}
                            </h5>
                        </div>
                    </div>

                    <div class="col-md-3">
                        <div class="summary-card bg-success bg-opacity-10 rounded-4 p-3 text-center h-100 border border-success-subtle">
                            <small class="text-muted d-block mb-1">Amount Paid</small>
                            <h5 class="fw-bold text-success mb-0">
                                GHS {{ number_format($amountPaid, 2) }}
                            </h5>
                        </div>
                    </div>

                    <div class="col-md-3">
                        <div class="summary-card bg-danger bg-opacity-10 rounded-4 p-3 text-center h-100 border border-danger-subtle">
                            <small class="text-muted d-block mb-1">Outstanding</small>
                            <h5 class="fw-bold text-danger mb-0">
                                GHS {{ number_format($balance, 2) }}
                            </h5>
                        </div>
                    </div>

                    <div class="col-md-3">
                        <div class="summary-card bg-light rounded-4 p-3 text-center h-100">
                            <small class="text-muted d-block mb-1">Account Status</small>
                            <h5 class="mb-0">
                                <span class="badge bg-{{ $accountStatusColors[$accountStatus] ?? 'secondary' }}">
                                    {{ ucfirst($feeAccount->status ?? 'N/A') }}
                                </span>
                            </h5>
                        </div>
                    </div>

                </div>

                @php
                    $paymentProgress = $totalFees > 0
                        ? min(100, max(0, ($amountPaid / $totalFees) * 100))
                        : 0;
                @endphp

                <div class="mt-4">
                    <div class="d-flex justify-content-between mb-1">
                        <span class="fw-semibold">Payment Progress</span>
                        <span class="fw-bold">{{ number_format($paymentProgress, 1) }}%</span>
                    </div>

                    <div class="progress" style="height: 12px;">
                        <div class="progress-bar bg-success"
                             role="progressbar"
                             style="width: {{ $paymentProgress }}%;"
                             aria-valuenow="{{ $paymentProgress }}"
                             aria-valuemin="0"
                             aria-valuemax="100">
                        </div>
                    </div>
                </div>

            </div>
        </div>
    @endif

    {{-- PAYMENT SUMMARY --}}
    <div class="card border-0 shadow-sm rounded-4 mt-4">
        <div class="card-header bg-light border-0 py-3">
            <h6 class="fw-bold mb-0">
                <i class="fas fa-calculator text-primary me-2"></i>
                Payment Summary
            </h6>
        </div>

        <div class="card-body">
            <div class="row g-3">

                <div class="col-sm-6 col-xl-3">
                    <div class="summary-card bg-light rounded-4 p-4 text-center h-100">
                        <small class="text-muted d-block">Amount</small>
                        <h5 class="fw-bold text-primary mb-0 mt-1">
                            GHS {{ number_format($paymentAmount, 2) }}
                        </h5>
                    </div>
                </div>

                <div class="col-sm-6 col-xl-3">
                    <div class="summary-card bg-danger bg-opacity-10 rounded-4 p-4 text-center h-100">
                        <small class="text-muted d-block">Penalty</small>
                        <h5 class="fw-bold text-danger mb-0 mt-1">
                            GHS {{ number_format($penaltyAmount, 2) }}
                        </h5>
                    </div>
                </div>

                <div class="col-sm-6 col-xl-3">
                    <div class="summary-card bg-success bg-opacity-10 rounded-4 p-4 text-center h-100">
                        <small class="text-muted d-block">Discount</small>
                        <h5 class="fw-bold text-success mb-0 mt-1">
                            GHS {{ number_format($discountAmount, 2) }}
                        </h5>
                    </div>
                </div>

                <div class="col-sm-6 col-xl-3">
                    <div class="summary-card bg-success bg-opacity-10 rounded-4 p-4 text-center h-100 border border-success">
                        <small class="text-muted d-block">Net Amount</small>
                        <h5 class="fw-bold text-success mb-0 mt-1">
                            GHS {{ number_format($netAmount, 2) }}
                        </h5>
                    </div>
                </div>

            </div>
        </div>
    </div>

    {{-- ACTIONS --}}
    <div class="card border-0 shadow-sm rounded-4 mt-4 mb-4 no-print">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">

             

                <div class="d-flex gap-2">

                    @if($payment->status === 'pending')
                        <form action="{{ route('fee-payments.update', $payment->id) }}"
                              method="POST"
                              class="d-inline">
                            @csrf
                            @method('PUT')

                            <input type="hidden" name="status" value="completed">
                            <input type="hidden" name="student_id" value="{{ $payment->student_id }}">
                            <input type="hidden" name="amount" value="{{ $payment->amount }}">
                            <input type="hidden" name="payment_method" value="{{ $payment->payment_method }}">
                            <input type="hidden" name="payment_date" value="{{ $payment->payment_date }}">

                            <button type="submit"
                                    class="btn btn-success"
                                    onclick="return confirm('Approve this payment?');">
                                <i class="fas fa-check me-1"></i>
                                Approve Payment
                            </button>
                        </form>
                    @endif

                    

                </div>
            </div>
        </div>
    </div>

</div>

@endsection
