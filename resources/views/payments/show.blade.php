@extends('layouts.master')

@section('title', 'Payment Receipt')

@section('content')

<div class="container-fluid">

    <div class="card">

        <div class="card-body">

            <h3 class="mb-4">
                PAYMENT RECEIPT
            </h3>

            <p>
                Receipt:
                {{ $payment->receipt_number }}
            </p>

            <p>
                Student:
                {{ $payment->student?->full_name }}
            </p>

            <p>
                Invoice:
                {{ $payment->invoice?->invoice_number }}
            </p>

            <p>
                Amount:
                GH₵{{ number_format($payment->amount,2) }}
            </p>

            <p>
                Method:
                {{ $payment->payment_method }}
            </p>

            <p>
                Date:
                {{ $payment->payment_date }}
            </p>

            <button onclick="window.print()"
                    class="btn btn-dark">

                Print Receipt

            </button>

        </div>

    </div>

</div>

@endsection