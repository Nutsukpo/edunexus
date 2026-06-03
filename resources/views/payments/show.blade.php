@extends('layouts.master')

@section('title', 'Payment Details')

@section('content')

<div class="container-fluid">

    <div class="card border-0 shadow-sm">

        <div class="card-header bg-white d-flex justify-content-between align-items-center">

            <h4 class="mb-0">
                Payment Details
            </h4>

            <a href="{{ route('payments.index') }}"
               class="btn btn-secondary btn-sm">

                Back

            </a>

        </div>

        <div class="card-body">

            <div class="row g-4">

                <div class="col-md-6">

                    <div class="border rounded p-3">

                        <h6 class="text-muted">
                            Student
                        </h6>

                        <h5>
                            {{ $payment->student->first_name ?? '' }}
                            {{ $payment->student->last_name ?? '' }}
                        </h5>

                    </div>

                </div>

                <div class="col-md-6">

                    <div class="border rounded p-3">

                        <h6 class="text-muted">
                            Amount
                        </h6>

                        <h5>
                            GHS {{ number_format($payment->amount, 2) }}
                        </h5>

                    </div>

                </div>

                <div class="col-md-6">

                    <div class="border rounded p-3">

                        <h6 class="text-muted">
                            Payment Method
                        </h6>

                        <h5>
                            {{ $payment->payment_method }}
                        </h5>

                    </div>

                </div>

                <div class="col-md-6">

                    <div class="border rounded p-3">

                        <h6 class="text-muted">
                            Payment Date
                        </h6>

                        <h5>
                            {{ $payment->payment_date }}
                        </h5>

                    </div>

                </div>

                <div class="col-md-12">

                    <div class="border rounded p-3">

                        <h6 class="text-muted">
                            Note
                        </h6>

                        <p class="mb-0">
                            {{ $payment->note ?? 'No note added' }}
                        </p>

                    </div>

                </div>

            </div>

        </div>

    </div>

</div>

@endsection