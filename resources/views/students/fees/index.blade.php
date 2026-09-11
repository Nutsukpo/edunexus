@extends('students.layouts.app')

@section('title', 'My School Fees')

@section('content')

@php
    $totalFees = round((float) ($totalFees ?? 0), 2);
    $amountPaid = round((float) ($amountPaid ?? 0), 2);
    $balance = round(max(0, (float) ($balance ?? ($totalFees - $amountPaid))), 2);

    $percentagePaid = $totalFees > 0
        ? round(min(100, ($amountPaid / $totalFees) * 100), 1)
        : 0;

    $billSheets = $billSheets ?? collect();
    $billSummary = $billSummary ?? collect();
    $feeItems = $feeItems ?? collect();
    $payments = $payments ?? collect();

    $studentName = trim(collect([
        $student->first_name ?? null,
        $student->middle_name ?? null,
        $student->last_name ?? null,
    ])->filter()->implode(' '));
@endphp

<style>
    .fees-page { padding: 24px; background: #f5f7fa; min-height: calc(100vh - 70px); }
    .page-header { display:flex; justify-content:space-between; align-items:center; gap:15px; flex-wrap:wrap; margin-bottom:20px; }
    .page-header h1 { margin:0; font-size:28px; font-weight:700; color:#1f2937; }
    .page-header p { margin:5px 0 0; color:#6b7280; }
    .period-badge { display:inline-flex; align-items:center; gap:8px; padding:9px 13px; background:#eef6ff; color:#1d5f91; border:1px solid #d8eafa; border-radius:9px; font-size:12px; font-weight:700; }
    .payment-btn { display:inline-flex; align-items:center; gap:8px; padding:11px 18px; border:0; border-radius:9px; background:#198754; color:#fff; text-decoration:none; font-weight:600; }
    .payment-btn:hover { background:#157347; color:#fff; }
    .alert { border-radius:10px; border:0; }
    .summary-grid { display:grid; grid-template-columns:repeat(3,1fr); gap:18px; margin-bottom:22px; }
    .summary-card, .section-card, .progress-card { background:#fff; border:1px solid #e7ebef; border-radius:14px; box-shadow:0 3px 15px rgba(0,0,0,.05); }
    .summary-card { padding:22px; }
    .summary-label { color:#6b7280; font-size:13px; margin-bottom:8px; }
    .summary-value { font-size:27px; font-weight:700; color:#1f2937; }
    .summary-card.paid .summary-value { color:#198754; }
    .summary-card.balance .summary-value { color:#dc3545; }
    .progress-card { padding:22px; margin-bottom:22px; }
    .progress-header { display:flex; justify-content:space-between; margin-bottom:10px; font-weight:600; }
    .progress { height:12px; border-radius:20px; background:#e9ecef; overflow:hidden; }
    .progress-bar { height:100%; border-radius:20px; background:#198754; }
    .section-card { margin-bottom:22px; overflow:hidden; }
    .section-header { padding:18px 22px; border-bottom:1px solid #eef0f2; display:flex; justify-content:space-between; align-items:center; gap:10px; }
    .section-header h3 { margin:0; font-size:18px; font-weight:700; color:#1f2937; }
    .section-body { padding:22px; }
    .student-info { display:grid; grid-template-columns:repeat(3,1fr); gap:18px; }
    .info-label { font-size:11px; color:#6b7280; margin-bottom:4px; text-transform:uppercase; letter-spacing:.4px; }
    .info-value { font-weight:600; color:#1f2937; }
    .table-responsive { overflow-x:auto; }
    .fees-table { width:100%; border-collapse:collapse; }
    .fees-table th { background:#f8f9fa; color:#6b7280; font-size:11px; text-transform:uppercase; letter-spacing:.4px; padding:13px 15px; text-align:left; white-space:nowrap; }
    .fees-table td { padding:14px 15px; border-top:1px solid #eef0f2; color:#374151; vertical-align:middle; }
    .amount { font-weight:600; white-space:nowrap; }
    .fee-name { font-weight:600; color:#1f2937; }
    .badge-status { display:inline-flex; align-items:center; padding:5px 10px; border-radius:20px; font-size:11px; font-weight:600; text-transform:capitalize; }
    .badge-paid { background:#d1e7dd; color:#0f5132; }
    .badge-partial { background:#fff3cd; color:#664d03; }
    .badge-pending { background:#e2e3e5; color:#41464b; }
    .badge-overdue { background:#f8d7da; color:#842029; }
    .empty-state { text-align:center; padding:45px 20px; color:#6b7280; }
    .empty-state i { font-size:38px; margin-bottom:12px; opacity:.5; }
    .payment-reference { font-family:monospace; font-size:12px; }
    .payment-method { text-transform:capitalize; }
    .receipt-btn { display:inline-flex; align-items:center; gap:6px; padding:6px 10px; border:1px solid #dee2e6; border-radius:7px; color:#495057; text-decoration:none; font-size:12px; font-weight:600; }
    .receipt-btn:hover { background:#f8f9fa; color:#198754; }
    .bill-name { font-weight:650; color:#1f2937; }
    .bill-period { font-size:11px; color:#6b7280; }
    .pagination-wrapper { padding:18px 22px; border-top:1px solid #eef0f2; }
    @media(max-width:900px){ .summary-grid,.student-info{grid-template-columns:1fr;} }
    @media(max-width:600px){ .fees-page{padding:15px;} .page-header h1{font-size:23px;} .section-body{padding:15px;} }
</style>

<div class="fees-page">

    <div class="page-header">
        <div>
            <h1><i class="fas fa-file-invoice-dollar me-2"></i>My School Fees</h1>
            <p>Fees and payments for the school's current operational academic period.</p>
        </div>

        <div class="d-flex align-items-center gap-2 flex-wrap">
            <div class="period-badge">
                <i class="fas fa-calendar-check"></i>
                {{ $academicYear?->name ?? 'Academic Year Not Set' }}
                @if($term) — {{ $term->name }} @endif
            </div>

            @if($balance > 0 && $billSheets->count())
                <a href="{{ route('students.fees.payment') }}" class="payment-btn">
                    <i class="fas fa-credit-card"></i> Pay Fees
                </a>
            @endif
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-circle me-2"></i>{{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="section-card">
        <div class="section-header">
            <h3><i class="fas fa-user-graduate me-2"></i>Student Information</h3>
        </div>
        <div class="section-body">
            <div class="student-info">
                <div><div class="info-label">Student ID</div><div class="info-value">{{ $student->student_id ?? 'N/A' }}</div></div>
                <div><div class="info-label">Student Name</div><div class="info-value">{{ $studentName ?: 'N/A' }}</div></div>
                <div><div class="info-label">Class</div><div class="info-value">{{ $assignment?->studentClass?->name ?? 'N/A' }}</div></div>
                <div><div class="info-label">Academic Year</div><div class="info-value">{{ $academicYear?->name ?? 'N/A' }}</div></div>
                <div><div class="info-label">Current Term</div><div class="info-value">{{ $term?->name ?? 'N/A' }}</div></div>
                <div><div class="info-label">Fee Account</div><div class="info-value">#{{ $feeAccount?->id ?? 'Bill Sheet Based' }}</div></div>
            </div>
        </div>
    </div>

    <div class="summary-grid">
        <div class="summary-card">
            <div class="summary-label">Total Bill Sheet Amount</div>
            <div class="summary-value">GH₵ {{ number_format($totalFees, 2) }}</div>
        </div>
        <div class="summary-card paid">
            <div class="summary-label">Amount Paid</div>
            <div class="summary-value">GH₵ {{ number_format($amountPaid, 2) }}</div>
        </div>
        <div class="summary-card balance">
            <div class="summary-label">Outstanding Balance</div>
            <div class="summary-value">GH₵ {{ number_format($balance, 2) }}</div>
        </div>
    </div>

    <div class="progress-card">
        <div class="progress-header">
            <span>Payment Progress</span>
            <span>{{ number_format($percentagePaid, 1) }}%</span>
        </div>
        <div class="progress">
            <div class="progress-bar" style="width:{{ $percentagePaid }}%"></div>
        </div>
        <div class="d-flex justify-content-between mt-2 text-muted small">
            <span>Paid: GH₵ {{ number_format($amountPaid, 2) }}</span>
            <span>Balance: GH₵ {{ number_format($balance, 2) }}</span>
        </div>
    </div>

    <div class="section-card">
        <div class="section-header">
            <h3><i class="fas fa-file-invoice me-2"></i>Current Bill Sheets</h3>
            <span class="text-muted small">{{ $billSheets->count() }} Bill Sheet(s)</span>
        </div>
        <div class="section-body p-0">
            @if($billSummary->count())
                <div class="table-responsive">
                    <table class="fees-table">
                        <thead>
                            <tr>
                                <th>Bill Sheet</th>
                                <th>Academic Year</th>
                                <th>Term</th>
                                <th>Total</th>
                                <th>Paid</th>
                                <th>Balance</th>
                                <th>Status</th>
                                <th>Due Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($billSummary as $bill)
                                <tr>
                                    <td><div class="bill-name">{{ $bill->name }}</div></td>
                                    <td>{{ $academicYear?->name ?? 'N/A' }}</td>
                                    <td>{{ $term?->name ?? 'N/A' }}</td>
                                    <td class="amount">GH₵ {{ number_format($bill->total, 2) }}</td>
                                    <td class="amount text-success">GH₵ {{ number_format($bill->paid, 2) }}</td>
                                    <td class="amount text-danger">GH₵ {{ number_format($bill->balance, 2) }}</td>
                                    <td>
                                        <span class="badge-status {{ $bill->status === 'paid' ? 'badge-paid' : ($bill->status === 'partial' ? 'badge-partial' : 'badge-pending') }}">
                                            {{ $bill->status }}
                                        </span>
                                    </td>
                                    <td>{{ $bill->due_date ? \Carbon\Carbon::parse($bill->due_date)->format('M d, Y') : 'N/A' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="empty-state">
                    <i class="fas fa-file-invoice"></i>
                    <h5>No Approved Bill Sheet Yet</h5>
                    <p class="mb-0">Your school has not published an approved Bill Sheet for the current operational term yet.</p>
                </div>
            @endif
        </div>
    </div>

    <div class="section-card">
        <div class="section-header">
            <h3><i class="fas fa-list-alt me-2"></i>Fee Breakdown</h3>
        </div>
        <div class="section-body p-0">
            @if($feeItems->count())
                <div class="table-responsive">
                    <table class="fees-table">
                        <thead>
                            <tr><th>Fee</th><th>Type</th><th>Amount</th><th>Status</th><th>Due Date</th></tr>
                        </thead>
                        <tbody>
                            @foreach($feeItems as $item)
                                <tr>
                                    <td><div class="fee-name">{{ $item->fee_name }}</div></td>
                                    <td>{{ $item->fee_type }}</td>
                                    <td class="amount">GH₵ {{ number_format((float)$item->amount, 2) }}</td>
                                    <td><span class="badge-status badge-pending">{{ $item->status }}</span></td>
                                    <td>{{ $item->due_date ? \Carbon\Carbon::parse($item->due_date)->format('M d, Y') : 'N/A' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="empty-state"><i class="fas fa-receipt"></i><h5>No Bill Items Found</h5><p class="mb-0">The current Bill Sheet does not contain individual fee items.</p></div>
            @endif
        </div>
    </div>

    <div class="section-card">
        <div class="section-header">
            <h3><i class="fas fa-history me-2"></i>Payment History</h3>
            @if(is_object($payments) && method_exists($payments, 'total'))
                <span class="text-muted small">{{ $payments->total() }} payment(s)</span>
            @endif
        </div>
        <div class="section-body p-0">
            @if($payments->count())
                <div class="table-responsive">
                    <table class="fees-table">
                        <thead><tr><th>Receipt</th><th>Date</th><th>Bill Sheet</th><th>Amount</th><th>Method</th><th>Reference</th><th>Status</th><th>Action</th></tr></thead>
                        <tbody>
                            @foreach($payments as $payment)
                                <tr>
                                    <td><strong>{{ $payment->receipt_number ?? 'N/A' }}</strong></td>
                                    <td>{{ $payment->payment_date ? \Carbon\Carbon::parse($payment->payment_date)->format('M d, Y') : 'N/A' }}</td>
                                    <td>{{ $payment->billSheet?->name ?? 'N/A' }}</td>
                                    <td class="amount">GH₵ {{ number_format((float)($payment->net_amount ?? $payment->amount ?? 0), 2) }}</td>
                                    <td class="payment-method">{{ str_replace('_',' ',$payment->payment_method ?? 'N/A') }}</td>
                                    <td>@if($payment->reference_number)<span class="payment-reference">{{ $payment->reference_number }}</span>@elseif($payment->transaction_id)<span class="payment-reference">{{ $payment->transaction_id }}</span>@else — @endif</td>
                                    <td><span class="badge-status {{ strtolower($payment->status ?? '') === 'completed' ? 'badge-paid' : (strtolower($payment->status ?? '') === 'pending' ? 'badge-partial' : 'badge-overdue') }}">{{ $payment->status ?? 'pending' }}</span></td>
                                    <td><a href="{{ route('students.fees.receipt', $payment->id) }}" class="receipt-btn"><i class="fas fa-receipt"></i> Receipt</a></td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @if(method_exists($payments, 'links'))<div class="pagination-wrapper">{{ $payments->withQueryString()->links() }}</div>@endif
            @else
                <div class="empty-state"><i class="fas fa-history"></i><h5>No Payment History</h5><p class="mb-0">No payments have been recorded for this Bill Sheet and operational period.</p></div>
            @endif
        </div>
    </div>

    @if($billSheets->count() && $balance > 0)
        <div class="section-card">
            <div class="section-body">
                <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
                    <div>
                        <h5 class="mb-1"><i class="fas fa-credit-card me-2"></i>Outstanding Fees</h5>
                        <p class="text-muted mb-0">Current balance: <strong class="text-danger">GH₵ {{ number_format($balance, 2) }}</strong></p>
                    </div>
                    <a href="{{ route('students.fees.payment') }}" class="payment-btn"><i class="fas fa-credit-card"></i> Make Payment</a>
                </div>
            </div>
        </div>
    @elseif($billSheets->count() && $balance <= 0 && $totalFees > 0)
        <div class="alert alert-success"><i class="fas fa-check-circle me-2"></i><strong>Fees Fully Paid.</strong> Your current operational-period Bill Sheet has no outstanding balance.</div>
    @endif

</div>
@endsection
