@extends('layouts.master')

@section('title', 'Billing Engine')

@section('content')

<div class="container-fluid">

    <div class="d-flex justify-content-between mb-4">
        <h4>Student Invoices</h4>

        <a href="{{ route('billing.create') }}" class="btn btn-primary">
            Generate Invoice
        </a>
    </div>

    <div class="card">
        <div class="card-body p-0">

            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Invoice No</th>
                        <th>Student</th>
                        <th>Total</th>
                        <th>Paid</th>
                        <th>Balance</th>
                        <th>Status</th>
                    </tr>
                </thead>

                <tbody>
                    @foreach($invoices as $key => $invoice)
                        <tr>
                            <td>{{ $key + 1 }}</td>
                            <td>{{ $invoice->invoice_number }}</td>
                            <td>{{ $invoice->student->name ?? '' }}</td>
                            <td>{{ number_format($invoice->total_amount, 2) }}</td>
                            <td>{{ number_format($invoice->paid_amount, 2) }}</td>
                            <td>{{ number_format($invoice->balance, 2) }}</td>
                            <td>
                                <span class="badge bg-secondary">
                                    {{ $invoice->status }}
                                </span>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

        </div>
    </div>

</div>

@endsection