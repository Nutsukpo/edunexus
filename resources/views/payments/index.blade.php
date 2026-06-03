@extends('layouts.master')

@section('title', 'Payments')

@section('content')

<div class="container-fluid">

    <div class="d-flex justify-content-between mb-3 mt-3">

        <h4>Payments</h4>

        <a href="{{ route('payments.create') }}"
           class="btn btn-white text-dark">

            Receive Payment

        </a>

    </div>

    <div class="card">

        <div class="card-body">

            <table class="table table-bordered">

                <thead>
                    <tr>
                        <th>Receipt</th>
                        <th>Student</th>
                        <th>Amount</th>
                        <th>Date</th>
                        <th>Method</th>
                        <th>Action</th>
                        <th></th>
                    </tr>
                </thead>

                <tbody>

                @foreach($payments as $payment)

                    <tr>

                        <td>
                            {{ $payment->receipt_number }}
                        </td>

                        <td>
                            {{ $payment->student?->full_name }}
                        </td>

                        <td>
                            GH₵{{ number_format($payment->amount,2) }}
                        </td>

                        <td>
                            {{ $payment->payment_date }}
                        </td>

                        <td>
                            {{ $payment->payment_method }}
                        </td>

                        <td>

                            <a href="{{ route('payments.show',$payment) }}"
                               class="btn btn-whit text-dark">

                                View

                            </a>

                        </td>

                    </tr>

                @endforeach

                </tbody>

            </table>

            {{ $payments->links() }}

        </div>

    </div>

</div>

@endsection