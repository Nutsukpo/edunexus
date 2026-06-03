@extends('layouts.master')

@section('title', 'Student Fee Details')

@section('content')

<div class="container-fluid">

    <div class="card border-0 shadow-sm">

        <div class="card-header bg-white d-flex justify-content-between align-items-center">

            <h4 class="mb-0">
                Student Fee Details
            </h4>

            <a href="{{ route('student-fees.index') }}"
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
                            {{ $studentFee->student->first_name ?? '' }}
                            {{ $studentFee->student->last_name ?? '' }}
                        </h5>

                    </div>

                </div>

                <div class="col-md-6">

                    <div class="border rounded p-3">

                        <h6 class="text-muted">
                            Total Fee
                        </h6>

                        <h5>
                            GHS {{ number_format($studentFee->total_fee, 2) }}
                        </h5>

                    </div>

                </div>

                <div class="col-md-6">

                    <div class="border rounded p-3">

                        <h6 class="text-muted">
                            Paid Amount
                        </h6>

                        <h5>
                            GHS {{ number_format($studentFee->paid_amount, 2) }}
                        </h5>

                    </div>

                </div>

                <div class="col-md-6">

                    <div class="border rounded p-3">

                        <h6 class="text-muted">
                            Balance
                        </h6>

                        <h5>
                            GHS {{ number_format($studentFee->balance, 2) }}
                        </h5>

                    </div>

                </div>

                <div class="col-md-6">

                    <div class="border rounded p-3">

                        <h6 class="text-muted">
                            Status
                        </h6>

                        <h5>

                            @if($studentFee->status == 'Paid')

                                <span class="badge bg-success">
                                    Paid
                                </span>

                            @elseif($studentFee->status == 'Partial')

                                <span class="badge bg-warning text-dark">
                                    Partial
                                </span>

                            @else

                                <span class="badge bg-danger">
                                    Unpaid
                                </span>

                            @endif

                        </h5>

                    </div>

                </div>

            </div>

        </div>

    </div>

</div>

@endsection