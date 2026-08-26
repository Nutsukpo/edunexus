@extends('layouts.master')

@section('title', 'Payment Details - ' . ($payment->receipt_number ?? ''))

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <div class="card shadow-lg border-0 rounded-4">
                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0 fw-bold text-dark">
                        <i class="fas fa-receipt text-success me-1"></i> Payment Details
                        <span class="badge bg-primary ms-2">{{ $payment->receipt_number ?? 'N/A' }}</span>
                    </h5>
                    <div>
                        {{-- FIXED: Changed from fee-payments.receipt to fee-payments.print-receipt --}}
                        <a href="{{ route('fee-payments.print-receipt', $payment->id) }}" 
                           class="btn btn-success btn-sm" target="_blank">
                            <i class="fas fa-file-pdf me-1"></i> Download Receipt
                        </a>
                        @if($payment->status == 'pending')
                            <a href="{{ route('fee-payments.edit', $payment->id) }}" 
                               class="btn btn-warning btn-sm">
                                <i class="fas fa-edit me-1"></i> Edit
                            </a>
                        @endif
                        <a href="{{ route('fee-payments.index') }}" class="btn btn-secondary btn-sm">
                            <i class="fas fa-arrow-left me-1"></i> Back
                        </a>
                    </div>
                </div>

                <div class="card-body">
                    @if(session('success'))
                        <div class="alert alert-success alert-dismissible fade show">
                            {{ session('success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    @if(session('error'))
                        <div class="alert alert-danger alert-dismissible fade show">
                            {{ session('error') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    <div class="row">
                        {{-- Payment Information --}}
                        <div class="col-md-6">
                            <div class="card mb-3 border-0 shadow-sm">
                                <div class="card-header bg-light">
                                    <h6 class="mb-0 fw-bold text-dark">
                                        <i class="fas fa-info-circle text-primary me-1"></i> Payment Information
                                    </h6>
                                </div>
                                <div class="card-body">
                                    <table class="table table-bordered">
                                        <tr>
                                            <th style="width: 40%;">Receipt Number</th>
                                            <td><strong>{{ $payment->receipt_number ?? 'N/A' }}</strong></td>
                                        </tr>
                                        <tr>
                                            <th>Payment Date</th>
                                            <td>{{ $payment->payment_date ? $payment->payment_date->format('d M, Y') : 'N/A' }}</td>
                                        </tr>
                                        <tr>
                                            <th>Payment Method</th>
                                            <td><span class="badge bg-info">{{ ucfirst(str_replace('_', ' ', $payment->payment_method ?? 'N/A')) }}</span></td>
                                        </tr>
                                        <tr>
                                            <th>Payment Type</th>
                                            <td><span class="badge bg-primary">{{ ucfirst($payment->payment_type ?? 'N/A') }}</span></td>
                                        </tr>
                                        <tr>
                                            <th>Status</th>
                                            <td>
                                                @php
                                                    $statusColors = [
                                                        'pending' => 'warning',
                                                        'completed' => 'success',
                                                        'failed' => 'danger',
                                                        'refunded' => 'info',
                                                        'reversed' => 'secondary'
                                                    ];
                                                @endphp
                                                <span class="badge bg-{{ $statusColors[$payment->status] ?? 'secondary' }}">
                                                    {{ ucfirst($payment->status ?? 'N/A') }}
                                                </span>
                                            </td>
                                        </tr>
                                        <tr>
                                            <th>Recorded By</th>
                                            <td>{{ ucfirst($payment->recorded_by ?? 'N/A') }}</td>
                                        </tr>
                                        @if($payment->transaction_id)
                                        <tr>
                                            <th>Transaction ID</th>
                                            <td>{{ $payment->transaction_id }}</td>
                                        </tr>
                                        @endif
                                        @if($payment->bank_name)
                                        <tr>
                                            <th>Bank Name</th>
                                            <td>{{ $payment->bank_name }}</td>
                                        </tr>
                                        @endif
                                        @if($payment->cheque_number)
                                        <tr>
                                            <th>Cheque Number</th>
                                            <td>{{ $payment->cheque_number }}</td>
                                        </tr>
                                        @endif
                                        @if($payment->reference_number)
                                        <tr>
                                            <th>Reference Number</th>
                                            <td>{{ $payment->reference_number }}</td>
                                        </tr>
                                        @endif
                                        @if($payment->notes)
                                        <tr>
                                            <th>Notes</th>
                                            <td>{{ $payment->notes }}</td>
                                        </tr>
                                        @endif
                                    </table>
                                </div>
                            </div>
                        </div>

                        {{-- Student Information --}}
                        <div class="col-md-6">
                            <div class="card mb-3 border-0 shadow-sm">
                                <div class="card-header bg-light">
                                    <h6 class="mb-0 fw-bold text-dark">
                                        <i class="fas fa-user-graduate text-primary me-1"></i> Student Information
                                    </h6>
                                </div>
                                <div class="card-body">
                                    @if($payment->student)
                                        <table class="table table-bordered">
                                            <tr>
                                                <th style="width: 40%;">Student Name</th>
                                                <td><strong>{{ $payment->student->full_name ?? $payment->student->first_name . ' ' . $payment->student->last_name }}</strong></td>
                                            </tr>
                                            <tr>
                                                <th>Admission Number</th>
                                                <td>{{ $payment->student->student_id ?? $payment->student->admission_number ?? 'N/A' }}</td>
                                            </tr>
                                            <tr>
                                                <th>Email</th>
                                                <td>{{ $payment->student->father_email ?? 'N/A' }}</td>
                                            </tr>
                                            <tr>
                                                <th>Phone</th>
                                                <td>{{ $payment->student->father_phone ?? 'N/A' }}</td>
                                            </tr>
                                        </table>
                                    @else
                                        <p class="text-muted">Student information not available</p>
                                    @endif
                                </div>
                            </div>

                            {{-- Fee Account Information --}}
                            @if($payment->studentFeeAccount)
                            <div class="card mb-3 border-0 shadow-sm">
                                <div class="card-header bg-light">
                                    <h6 class="mb-0 fw-bold text-dark">
                                        <i class="fas fa-wallet text-primary me-1"></i> Fee Account
                                    </h6>
                                </div>
                                <div class="card-body">
                                    <table class="table table-bordered">
                                        <tr>
                                            <th style="width: 40%;">Total Fees</th>
                                            <td><strong>GHS {{ number_format($payment->studentFeeAccount->total_fees ?? 0, 2) }}</strong></td>
                                        </tr>
                                        <tr>
                                            <th>Amount Paid</th>
                                            <td><strong class="text-success">GHS {{ number_format($payment->studentFeeAccount->amount_paid ?? 0, 2) }}</strong></td>
                                        </tr>
                                        <tr>
                                            <th>Balance</th>
                                            <td><strong class="text-danger">GHS {{ number_format($payment->studentFeeAccount->balance ?? 0, 2) }}</strong></td>
                                        </tr>
                                        <tr>
                                            <th>Status</th>
                                            <td>
                                                @php
                                                    $statusColors = [
                                                        'paid' => 'success',
                                                        'partial' => 'warning',
                                                        'pending' => 'secondary',
                                                        'overdue' => 'danger',
                                                        'waived' => 'info'
                                                    ];
                                                @endphp
                                                <span class="badge bg-{{ $statusColors[$payment->studentFeeAccount->status] ?? 'secondary' }}">
                                                    {{ ucfirst($payment->studentFeeAccount->status ?? 'N/A') }}
                                                </span>
                                            </td>
                                        </tr>
                                    </table>
                                </div>
                            </div>
                            @endif
                        </div>
                    </div>

                    {{-- Payment Summary --}}
                    <div class="row mt-3">
                        <div class="col-md-12">
                            <div class="card border-0 shadow-sm">
                                <div class="card-header bg-light">
                                    <h6 class="mb-0 fw-bold text-dark">
                                        <i class="fas fa-calculator text-primary me-1"></i> Payment Summary
                                    </h6>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-3">
                                            <div class="text-center p-3 bg-light rounded">
                                                <small class="text-muted d-block">Amount</small>
                                                <h5 class="mb-0 text-primary">GHS {{ number_format($payment->amount ?? 0, 2) }}</h5>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="text-center p-3 bg-light rounded">
                                                <small class="text-muted d-block">Penalty</small>
                                                <h5 class="mb-0 text-danger">GHS {{ number_format($payment->penalty_amount ?? 0, 2) }}</h5>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="text-center p-3 bg-light rounded">
                                                <small class="text-muted d-block">Discount</small>
                                                <h5 class="mb-0 text-success">GHS {{ number_format($payment->discount_amount ?? 0, 2) }}</h5>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="text-center p-3 bg-success bg-opacity-10 rounded border border-success">
                                                <small class="text-muted d-block">Net Amount</small>
                                                <h5 class="mb-0 text-success fw-bold">GHS {{ number_format($payment->net_amount ?? 0, 2) }}</h5>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Action Buttons --}}
                    <div class="row mt-4">
                        <div class="col-12">
                            <div class="d-flex justify-content-between">
                                <div>
                                    @if($payment->status == 'pending')
                                        <form action="{{ route('fee-payments.update', $payment->id) }}" method="POST" class="d-inline">
                                            @csrf
                                            @method('PUT')
                                            <input type="hidden" name="status" value="completed">
                                            <input type="hidden" name="student_id" value="{{ $payment->student_id }}">
                                            <input type="hidden" name="amount" value="{{ $payment->amount }}">
                                            <input type="hidden" name="payment_method" value="{{ $payment->payment_method }}">
                                            <input type="hidden" name="payment_date" value="{{ $payment->payment_date }}">
                                            <button type="submit" class="btn btn-success">
                                                <i class="fas fa-check me-1"></i> Approve Payment
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection