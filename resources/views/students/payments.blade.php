<!-- resources/views/student/payments.blade.php -->
@extends('students.layouts.app')

@section('content')
<div class="container">
    <div class="card">
        <div class="card-header">
            <h5>My Fee Payments</h5>
        </div>
        <div class="card-body">
            <div class="row mb-4">
                <div class="col-md-3">
                    <div class="card bg-info text-white">
                        <div class="card-body">
                            <h6>Total Due</h6>
                            <h3>GHS {{ number_format($totalDue, 2) }}</h3>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-success text-white">
                        <div class="card-body">
                            <h6>Amount Paid</h6>
                            <h3>GHS {{ number_format($amountPaid, 2) }}</h3>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-warning text-white">
                        <div class="card-body">
                            <h6>Balance</h6>
                            <h3>GHS {{ number_format($balance, 2) }}</h3>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-danger text-white">
                        <div class="card-body">
                            <h6>Overdue</h6>
                            <h3>GHS {{ number_format($overdue, 2) }}</h3>
                        </div>
                    </div>
                </div>
            </div>

            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Invoice #</th>
                            <th>Term</th>
                            <th>Amount Due</th>
                            <th>Amount Paid</th>
                            <th>Balance</th>
                            <th>Due Date</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($payments as $payment)
                            <tr>
                                <td>{{ $payment->invoice_number }}</td>
                                <td>{{ $payment->term->name ?? 'N/A' }}</td>
                                <td>GHS {{ number_format($payment->amount_due, 2) }}</td>
                                <td>GHS {{ number_format($payment->amount_paid, 2) }}</td>
                                <td>GHS {{ number_format($payment->balance, 2) }}</td>
                                <td>{{ $payment->due_date->format('d-m-Y') }}</td>
                                <td>
                                    <span class="badge bg-{{ $payment->status_badge }}">
                                        {{ ucfirst($payment->payment_status) }}
                                    </span>
                                </td>
                                <td>
                                    <a href="{{ route('payments.show', $payment->id) }}" 
                                       class="btn btn-sm btn-info">View</a>
                                    @if($payment->payment_status != 'paid')
                                        <button class="btn btn-sm btn-success pay-now" 
                                                data-payment-id="{{ $payment->id }}"
                                                data-amount="{{ $payment->balance }}">
                                            Pay Now
                                        </button>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            {{ $payments->links() }}
        </div>
    </div>
</div>
@endsection