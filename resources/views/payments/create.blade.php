@extends('layouts.master')

@section('title', 'Add Payment')

@section('content')

<div class="container-fluid">

    <div class="card shadow-sm border-0">

        <div class="card-header bg-white">
            <h4 class="mb-0">Add Payment</h4>
        </div>

        <div class="card-body">

            <form action="{{ route('payments.store') }}" method="POST">
                @csrf

                <div class="row">

                    {{-- STUDENT --}}
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Student</label>

                        <select id="studentSelect" class="form-select" required>
                            <option value="">-- Select Student --</option>

                            @foreach($students as $student)
                                <option value="{{ $student->id }}">
                                    {{ $student->first_name }} {{ $student->last_name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- STUDENT FEE --}}
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Student Fee</label>

                        <select name="student_fee_id" id="feeSelect" class="form-select" required>
                            <option value="">-- Select Fee --</option>

                            @foreach($students as $student)
                                @foreach($student->studentFees as $fee)
                                    <option value="{{ $fee->id }}" data-student="{{ $student->id }}">
                                        {{ $student->first_name }} {{ $student->last_name }}
                                        | Balance: {{ $fee->balance }}
                                    </option>
                                @endforeach
                            @endforeach
                        </select>
                    </div>

                    {{-- AMOUNT --}}
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Amount Paid</label>

                        <input type="number"
                               step="0.01"
                               name="amount_paid"
                               class="form-control"
                               required>
                    </div>

                    {{-- PAYMENT METHOD --}}
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Payment Method</label>

                        <select name="payment_method" class="form-select" required>
                            <option value="Cash">Cash</option>
                            <option value="Mobile Money">Mobile Money</option>
                            <option value="Bank Transfer">Bank Transfer</option>
                        </select>
                    </div>

                    {{-- DATE --}}
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Payment Date</label>

                        <input type="date"
                               name="payment_date"
                               class="form-control"
                               required>
                    </div>

                    {{-- NOTES --}}
                    <div class="col-md-12 mb-3">
                        <label class="form-label">Notes</label>

                        <textarea name="notes"
                                  class="form-control"
                                  rows="3"></textarea>
                    </div>

                </div>

                <button type="submit" class="btn btn-primary">
                    Save Payment
                </button>

            </form>

        </div>

    </div>

</div>

@endsection