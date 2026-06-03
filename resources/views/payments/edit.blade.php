@extends('layouts.master')

@section('title', 'Edit Payment')

@section('content')

<div class="container-fluid">

    <div class="card border-0 shadow-sm">

        <div class="card-header bg-white">

            <h4 class="mb-0">
                Edit Payment
            </h4>

        </div>

        <div class="card-body">

            <form action="{{ route('payments.update', $payment->id) }}"
                  method="POST">

                @csrf
                @method('PUT')

                <div class="row">

                    {{-- STUDENT --}}
                    <div class="col-md-6 mb-3">

                        <label class="form-label">
                            Student
                        </label>

                        <select name="student_id"
                                class="form-select"
                                required>

                            @foreach($students as $student)

                                <option value="{{ $student->id }}"
                                    {{ $payment->student_id == $student->id ? 'selected' : '' }}>

                                    {{ $student->first_name }}
                                    {{ $student->last_name }}

                                </option>

                            @endforeach

                        </select>

                    </div>

                    {{-- AMOUNT --}}
                    <div class="col-md-6 mb-3">

                        <label class="form-label">
                            Amount
                        </label>

                        <input type="number"
                               step="0.01"
                               name="amount"
                               class="form-control"
                               value="{{ $payment->amount }}"
                               required>

                    </div>

                    {{-- PAYMENT METHOD --}}
                    <div class="col-md-6 mb-3">

                        <label class="form-label">
                            Payment Method
                        </label>

                        <select name="payment_method"
                                class="form-select"
                                required>

                            <option value="Cash"
                                {{ $payment->payment_method == 'Cash' ? 'selected' : '' }}>
                                Cash
                            </option>

                            <option value="Mobile Money"
                                {{ $payment->payment_method == 'Mobile Money' ? 'selected' : '' }}>
                                Mobile Money
                            </option>

                            <option value="Bank Transfer"
                                {{ $payment->payment_method == 'Bank Transfer' ? 'selected' : '' }}>
                                Bank Transfer
                            </option>

                        </select>

                    </div>

                    {{-- PAYMENT DATE --}}
                    <div class="col-md-6 mb-3">

                        <label class="form-label">
                            Payment Date
                        </label>

                        <input type="date"
                               name="payment_date"
                               class="form-control"
                               value="{{ $payment->payment_date }}"
                               required>

                    </div>

                    {{-- NOTE --}}
                    <div class="col-md-12 mb-3">

                        <label class="form-label">
                            Note
                        </label>

                        <textarea name="note"
                                  rows="3"
                                  class="form-control">{{ $payment->note }}</textarea>

                    </div>

                </div>

                <div class="mt-4">

                    <button type="submit"
                            class="btn btn-primary">

                        Update Payment

                    </button>

                    <a href="{{ route('payments.index') }}"
                       class="btn btn-secondary">

                        Cancel

                    </a>

                </div>

            </form>

        </div>

    </div>

</div>

@endsection