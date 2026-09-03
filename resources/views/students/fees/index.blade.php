@extends('students.layouts.app')

@section('title', 'My School Fees')

@section('content')

<style>
    .fees-page {
        padding: 24px;
    }

    .page-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 24px;
        gap: 15px;
        flex-wrap: wrap;
    }

    .page-header h1 {
        margin: 0;
        font-size: 28px;
        font-weight: 700;
        color: #1f2937;
    }

    .page-header p {
        margin: 5px 0 0;
        color: #6b7280;
    }

    .payment-btn {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 11px 18px;
        border: none;
        border-radius: 9px;
        background: #198754;
        color: #fff;
        text-decoration: none;
        font-weight: 600;
        transition: .2s;
    }

    .payment-btn:hover {
        background: #157347;
        color: #fff;
        transform: translateY(-1px);
    }

    .alert {
        border-radius: 10px;
        border: none;
    }

    .summary-grid {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 18px;
        margin-bottom: 24px;
    }

    .summary-card {
        background: #fff;
        border-radius: 14px;
        padding: 22px;
        box-shadow: 0 3px 15px rgba(0,0,0,.06);
        border: 1px solid #eef0f2;
        position: relative;
        overflow: hidden;
    }

    .summary-card::after {
        content: '';
        position: absolute;
        right: -25px;
        bottom: -35px;
        width: 100px;
        height: 100px;
        background: rgba(25,135,84,.06);
        border-radius: 50%;
    }

    .summary-label {
        color: #6b7280;
        font-size: 14px;
        margin-bottom: 8px;
    }

    .summary-value {
        font-size: 27px;
        font-weight: 700;
        color: #1f2937;
    }

    .summary-card.paid .summary-value {
        color: #198754;
    }

    .summary-card.balance .summary-value {
        color: #dc3545;
    }

    .progress-card {
        background: #fff;
        border-radius: 14px;
        padding: 22px;
        box-shadow: 0 3px 15px rgba(0,0,0,.06);
        border: 1px solid #eef0f2;
        margin-bottom: 24px;
    }

    .progress-header {
        display: flex;
        justify-content: space-between;
        margin-bottom: 10px;
        font-weight: 600;
    }

    .progress {
        height: 12px;
        border-radius: 20px;
        background: #e9ecef;
        overflow: hidden;
    }

    .progress-bar {
        height: 100%;
        border-radius: 20px;
        background: #198754;
        transition: width .4s ease;
    }

    .section-card {
        background: #fff;
        border-radius: 14px;
        box-shadow: 0 3px 15px rgba(0,0,0,.06);
        border: 1px solid #eef0f2;
        margin-bottom: 24px;
        overflow: hidden;
    }

    .section-header {
        padding: 18px 22px;
        border-bottom: 1px solid #eef0f2;
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 10px;
    }

    .section-header h3 {
        margin: 0;
        font-size: 18px;
        font-weight: 700;
        color: #1f2937;
    }

    .section-body {
        padding: 22px;
    }

    .student-info {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 18px;
    }

    .info-label {
        font-size: 12px;
        color: #6b7280;
        margin-bottom: 4px;
        text-transform: uppercase;
        letter-spacing: .4px;
    }

    .info-value {
        font-weight: 600;
        color: #1f2937;
    }

    .table-responsive {
        overflow-x: auto;
    }

    .fees-table {
        width: 100%;
        border-collapse: collapse;
    }

    .fees-table th {
        background: #f8f9fa;
        color: #6b7280;
        font-size: 12px;
        text-transform: uppercase;
        letter-spacing: .4px;
        padding: 13px 15px;
        text-align: left;
        white-space: nowrap;
    }

    .fees-table td {
        padding: 15px;
        border-top: 1px solid #eef0f2;
        color: #374151;
        vertical-align: middle;
    }

    .fee-name {
        font-weight: 600;
        color: #1f2937;
    }

    .amount {
        font-weight: 600;
        white-space: nowrap;
    }

    .badge-status {
        display: inline-flex;
        align-items: center;
        padding: 5px 10px;
        border-radius: 20px;
        font-size: 12px;
        font-weight: 600;
        text-transform: capitalize;
    }

    .badge-paid {
        background: #d1e7dd;
        color: #0f5132;
    }

    .badge-partial {
        background: #fff3cd;
        color: #664d03;
    }

    .badge-pending {
        background: #e2e3e5;
        color: #41464b;
    }

    .badge-overdue {
        background: #f8d7da;
        color: #842029;
    }

    .empty-state {
        text-align: center;
        padding: 45px 20px;
        color: #6b7280;
    }

    .empty-state i {
        font-size: 38px;
        margin-bottom: 12px;
        opacity: .5;
    }

    .payment-reference {
        font-family: monospace;
        font-size: 13px;
        color: #374151;
    }

    .payment-method {
        text-transform: capitalize;
    }

    .receipt-btn {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 6px 10px;
        border: 1px solid #dee2e6;
        border-radius: 7px;
        color: #495057;
        text-decoration: none;
        font-size: 12px;
        font-weight: 600;
    }

    .receipt-btn:hover {
        background: #f8f9fa;
        color: #198754;
    }

    .pagination-wrapper {
        padding: 18px 22px;
        border-top: 1px solid #eef0f2;
    }

    @media (max-width: 900px) {
        .summary-grid,
        .student-info {
            grid-template-columns: 1fr;
        }
    }

    @media (max-width: 600px) {
        .fees-page {
            padding: 15px;
        }

        .page-header h1 {
            font-size: 23px;
        }

        .summary-value {
            font-size: 23px;
        }

        .section-body {
            padding: 15px;
        }
    }
</style>

    @php
    /*
    |--------------------------------------------------------------------------
    | Safe financial values
    |--------------------------------------------------------------------------
    */


$totalFees = (float) ($totalFees ?? $feeAccount?->total_fees ?? 0);
$amountPaid = (float) ($amountPaid ?? $feeAccount?->amount_paid ?? 0);

$balance = max(0, $totalFees - $amountPaid);

$percentagePaid = $totalFees > 0
    ? min(100, ($amountPaid / $totalFees) * 100)
    : 0;

$percentagePaid = round($percentagePaid, 1);


@endphp

<div class="fees-page">


{{-- PAGE HEADER --}}
<div class="page-header">
    <div>
        <h1>
            <i class="fas fa-file-invoice-dollar me-2"></i>
            My School Fees
        </h1>

        <p>
            View your fees, payments, balance and payment history.
        </p>
    </div>

    @if ($balance > 0)
        <a href="{{ route('students.fees.payment') }}" class="payment-btn">
            <i class="fas fa-mobile-alt"></i>
            Pay Fees
        </a>
    @endif
</div>


{{-- SUCCESS / ERROR MESSAGES --}}
@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="fas fa-check-circle me-2"></i>
        {{ session('success') }}

        <button type="button"
                class="btn-close"
                data-bs-dismiss="alert"></button>
    </div>
@endif

@if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <i class="fas fa-exclamation-circle me-2"></i>
        {{ session('error') }}

        <button type="button"
                class="btn-close"
                data-bs-dismiss="alert"></button>
    </div>
@endif


{{-- STUDENT INFORMATION --}}
<div class="section-card">

    <div class="section-header">
        <h3>
            <i class="fas fa-user-graduate me-2"></i>
            Student Information
        </h3>
    </div>

    <div class="section-body">

        <div class="student-info">

            <div>
                <div class="info-label">Student ID</div>

                <div class="info-value">
                    {{ $student->student_id ?? 'N/A' }}
                </div>
            </div>

            <div>
                <div class="info-label">Student Name</div>

                <div class="info-value">
                    {{ trim(($student->first_name ?? '') . ' ' . ($student->middle_name ?? '') . ' ' . ($student->last_name ?? '')) }}
                </div>
            </div>

            <div>
                <div class="info-label">Class</div>

                <div class="info-value">
                    {{ $assignment?->studentClass?->name ?? $feeAccount?->studentClass?->name ?? 'N/A' }}
                </div>
            </div>

            <div>
                <div class="info-label">Academic Year</div>

                <div class="info-value">
                    {{ $assignment?->academicYear?->name ?? $feeAccount?->academicYear?->name ?? 'N/A' }}
                </div>
            </div>

            <div>
                <div class="info-label">Fee Account</div>

                <div class="info-value">
                    #{{ $feeAccount?->id ?? 'N/A' }}
                </div>
            </div>

            <div>
                <div class="info-label">Account Status</div>

                <div class="info-value">
                    @php
                        $accountStatus = $feeAccount?->calculateStatus() ?? 'pending';
                    @endphp

                    <span class="badge-status
                        @if($accountStatus === 'paid') badge-paid
                        @elseif($accountStatus === 'partial') badge-partial
                        @else badge-pending
                        @endif">
                        {{ $accountStatus }}
                    </span>
                </div>
            </div>

        </div>

    </div>
</div>


{{-- FINANCIAL SUMMARY --}}
<div class="summary-grid">

    <div class="summary-card">
        <div class="summary-label">
            Total Fees
        </div>

        <div class="summary-value">
            GH₵ {{ number_format($totalFees, 2) }}
        </div>
    </div>


    <div class="summary-card paid">
        <div class="summary-label">
            Amount Paid
        </div>

        <div class="summary-value">
            GH₵ {{ number_format($amountPaid, 2) }}
        </div>
    </div>


    <div class="summary-card balance">
        <div class="summary-label">
            Outstanding Balance
        </div>

        <div class="summary-value">
            GH₵ {{ number_format($balance, 2) }}
        </div>
    </div>

</div>


{{-- PAYMENT PROGRESS --}}
<div class="progress-card">

    <div class="progress-header">
        <span>Payment Progress</span>

        <span>{{ $percentagePaid }}%</span>
    </div>

    <div class="progress">
        <div class="progress-bar"
             style="width: {{ $percentagePaid }}%;">
        </div>
    </div>

    <div class="d-flex justify-content-between mt-2 text-muted small">
        <span>
            Paid: GH₵ {{ number_format($amountPaid, 2) }}
        </span>

        <span>
            Balance: GH₵ {{ number_format($balance, 2) }}
        </span>
    </div>

</div>


{{-- FEE BREAKDOWN --}}
<div class="section-card">

    <div class="section-header">
        <h3>
            <i class="fas fa-list-alt me-2"></i>
            Fee Breakdown
        </h3>
    </div>

    <div class="section-body p-0">

        @if(isset($feeItems) && $feeItems->count())

            <div class="table-responsive">

                <table class="fees-table">

                    <thead>
                        <tr>
                            <th>Fee</th>
                            <th>Type</th>
                            <th>Amount</th>
                            <th>Paid</th>
                            <th>Balance</th>
                            <th>Status</th>
                            <th>Due Date</th>
                        </tr>
                    </thead>

                    <tbody>

                        @foreach($feeItems as $item)

                            @php
                                $itemAmount = (float) ($item->amount ?? 0);
                                $itemPaid = (float) ($item->paid_amount ?? 0);
                                $itemBalance = max(0, $itemAmount - $itemPaid);

                                $itemStatus = $item->status ?? 'pending';
                            @endphp

                            <tr>

                                <td>
                                    <div class="fee-name">
                                        {{ $item->fee_name ?? 'Fee' }}
                                    </div>

                                    @if(!empty($item->description))
                                        <small class="text-muted">
                                            {{ $item->description }}
                                        </small>
                                    @endif
                                </td>

                                <td>
                                    {{ $item->fee_type ?? 'N/A' }}
                                </td>

                                <td class="amount">
                                    GH₵ {{ number_format($itemAmount, 2) }}
                                </td>

                                <td class="amount text-success">
                                    GH₵ {{ number_format($itemPaid, 2) }}
                                </td>

                                <td class="amount text-danger">
                                    GH₵ {{ number_format($itemBalance, 2) }}
                                </td>

                                <td>
                                    <span class="badge-status
                                        @if($itemStatus === 'paid') badge-paid
                                        @elseif($itemStatus === 'partial') badge-partial
                                        @elseif($itemStatus === 'overdue') badge-overdue
                                        @else badge-pending
                                        @endif">

                                        {{ $itemStatus }}

                                    </span>
                                </td>

                                <td>
                                    {{ $item->due_date
                                        ? \Carbon\Carbon::parse($item->due_date)->format('M d, Y')
                                        : 'N/A'
                                    }}
                                </td>

                            </tr>

                        @endforeach

                    </tbody>

                </table>

            </div>

        @else

            <div class="empty-state">

                <i class="fas fa-receipt d-block"></i>

                <h5>No Fee Items Found</h5>

                <p class="mb-0">
                    No individual fee items have been assigned to your account yet.
                </p>

            </div>

        @endif

    </div>

</div>


{{-- PAYMENT HISTORY --}}
<div class="section-card">

    <div class="section-header">

        <h3>
            <i class="fas fa-history me-2"></i>
            Payment History
        </h3>

        @if($payments instanceof \Illuminate\Contracts\Pagination\LengthAwarePaginator)
            <span class="text-muted small">
                {{ $payments->total() }} payment(s)
            </span>
        @endif

    </div>

    <div class="section-body p-0">

        @if(isset($payments) && $payments->count())

            <div class="table-responsive">

                <table class="fees-table">

                    <thead>

                        <tr>
                            <th>Receipt</th>
                            <th>Date</th>
                            <th>Amount</th>
                            <th>Method</th>
                            <th>Reference</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>

                    </thead>

                    <tbody>

                        @foreach($payments as $payment)

                            <tr>

                                <td>
                                    <strong>
                                        {{ $payment->receipt_number ?? 'N/A' }}
                                    </strong>
                                </td>

                                <td>
                                    {{ $payment->payment_date
                                        ? \Carbon\Carbon::parse($payment->payment_date)->format('M d, Y')
                                        : 'N/A'
                                    }}
                                </td>

                                <td class="amount">
                                    GH₵ {{ number_format((float) ($payment->net_amount ?? $payment->amount ?? 0), 2) }}
                                </td>

                                <td class="payment-method">
                                    {{ str_replace('_', ' ', $payment->payment_method ?? 'N/A') }}
                                </td>

                                <td>

                                    @if($payment->reference_number)
                                        <span class="payment-reference">
                                            {{ $payment->reference_number }}
                                        </span>
                                    @elseif($payment->transaction_id)
                                        <span class="payment-reference">
                                            {{ $payment->transaction_id }}
                                        </span>
                                    @else
                                        —
                                    @endif

                                </td>

                                <td>

                                    @php
                                        $paymentStatus = strtolower($payment->status ?? 'pending');
                                    @endphp

                                    <span class="badge-status
                                        @if($paymentStatus === 'completed') badge-paid
                                        @elseif($paymentStatus === 'failed' || $paymentStatus === 'refunded') badge-overdue
                                        @elseif($paymentStatus === 'pending') badge-partial
                                        @else badge-pending
                                        @endif">

                                        {{ $paymentStatus }}

                                    </span>

                                </td>

                                <td>

                                    @if($payment->id)
                                        <a href="{{ route('students.fees.receipt', $payment->id) }}"
                                           class="receipt-btn">

                                            <i class="fas fa-receipt"></i>
                                            Receipt

                                        </a>
                                    @endif

                                </td>

                            </tr>

                        @endforeach

                    </tbody>

                </table>

            </div>


            {{-- PAGINATION --}}
            @if(method_exists($payments, 'links'))

                <div class="pagination-wrapper">
                    {{ $payments->links() }}
                </div>

            @endif

        @else

            <div class="empty-state">

                <i class="fas fa-history d-block"></i>

                <h5>No Payment History</h5>

                <p class="mb-0">
                    You have not made any fee payments yet.
                </p>

            </div>

        @endif

    </div>

</div>


{{-- PAYMENT CALL TO ACTION --}}
@if($balance > 0)

    <div class="section-card">

        <div class="section-body">

            <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">

                <div>
                    <h5 class="mb-1">
                        <i class="fas fa-mobile-alt me-2"></i>
                        Pay Your Outstanding Fees
                    </h5>

                    <p class="text-muted mb-0">
                        Outstanding balance:
                        <strong class="text-danger">
                            GH₵ {{ number_format($balance, 2) }}
                        </strong>
                    </p>
                </div>

                <a href="{{ route('students.fees.payment') }}"
                   class="payment-btn">

                    <i class="fas fa-credit-card"></i>
                    Make Payment

                </a>

            </div>

        </div>

    </div>

@else

    <div class="alert alert-success">

        <i class="fas fa-check-circle me-2"></i>

        <strong>Fees Fully Paid.</strong>

        Your current school fee account has no outstanding balance.

    </div>

@endif

</div>

@endsection
