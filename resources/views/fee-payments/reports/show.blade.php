{{-- resources/views/fee-payments/reports/show.blade.php --}}
@extends('layouts.master')

@section('title', 'Fee Account Details')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Fee Account Details</h3>
                    <div class="card-tools">
                        <a href="{{ route('fee.payment.reports.index') }}" class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i> Back
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <table class="table table-bordered">
                                <tr>
                                    <th>Student</th>
                                    <td>{{ $feeAccount->student->full_name ?? 'N/A' }}</td>
                                </tr>
                                <tr>
                                    <th>Class</th>
                                    <td>{{ $feeAccount->studentClass->name ?? 'N/A' }}</td>
                                </tr>
                                <tr>
                                    <th>Academic Year</th>
                                    <td>{{ $feeAccount->academicYear->name ?? 'N/A' }}</td>
                                </tr>
                                <tr>
                                    <th>Total Fees</th>
                                    <td>Ghc {{ number_format($feeAccount->total_fees, 2) }}</td>
                                </tr>
                                <tr>
                                    <th>Amount Paid</th>
                                    <td>Ghc {{ number_format($feeAccount->amount_paid, 2) }}</td>
                                </tr>
                                <tr>
                                    <th>Balance</th>
                                    <td>Ghc {{ number_format($feeAccount->balance, 2) }}</td>
                                </tr>
                                <tr>
                                    <th>Status</th>
                                    <td>
                                        @if($feeAccount->status == 'paid')
                                            <span class="badge badge-success">Paid</span>
                                        @elseif($feeAccount->status == 'partial')
                                            <span class="badge badge-warning">Partial</span>
                                        @else
                                            <span class="badge badge-danger">Pending</span>
                                        @endif
                                    </td>
                                </tr>
                            </table>
                        </div>
                    </div>

                    <div class="mt-4">
                        <h4>Payment History</h4>
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Amount</th>
                                    <th>Method</th>
                                    <th>Reference</th>
                                    <th>Receipt</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($paymentHistory as $payment)
                                    <tr>
                                        <td>{{ $payment->payment_date->format('Y-m-d H:i') }}</td>
                                        <td>Ghc {{ number_format($payment->amount, 2) }}</td>
                                        <td>{{ ucfirst(str_replace('_', ' ', $payment->payment_method)) }}</td>
                                        <td>{{ $payment->transaction_id ?? 'N/A' }}</td>
                                        <td>
                                            <a href="{{ route('fee.payment.reports.receipt', $payment->id) }}" 
                                               class="btn btn-sm btn-primary">
                                                <i class="fas fa-file-pdf"></i>
                                            </a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center">No payments recorded</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection