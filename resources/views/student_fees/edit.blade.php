@extends('layouts.master')

@section('title', 'Edit Student Fee')

@section('content')

<div class="container-fluid">

    <div class="card border-0 shadow-sm">

        <div class="card-header bg-white">
            <h4 class="mb-0">
                Edit Student Fee
            </h4>
        </div>

        <div class="card-body">

            <form action="{{ route('student-fees.update', $studentFee->id) }}"
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
                                    {{ $studentFee->student_id == $student->id ? 'selected' : '' }}>

                                    {{ $student->first_name }}
                                    {{ $student->last_name }}

                                </option>

                            @endforeach

                        </select>

                    </div>

                    {{-- TOTAL FEE --}}
                    <div class="col-md-6 mb-3">

                        <label class="form-label">
                            Total Fee
                        </label>

                        <input type="number"
                               step="0.01"
                               name="total_fee"
                               class="form-control"
                               value="{{ $studentFee->total_fee }}"
                               required>

                    </div>

                    {{-- PAID --}}
                    <div class="col-md-6 mb-3">

                        <label class="form-label">
                            Paid Amount
                        </label>

                        <input type="number"
                               step="0.01"
                               name="paid_amount"
                               class="form-control"
                               value="{{ $studentFee->paid_amount }}"
                               required>

                    </div>

                    {{-- STATUS --}}
                    <div class="col-md-6 mb-3">

                        <label class="form-label">
                            Status
                        </label>

                        <select name="status"
                                class="form-select"
                                required>

                            <option value="Paid"
                                {{ $studentFee->status == 'Paid' ? 'selected' : '' }}>
                                Paid
                            </option>

                            <option value="Partial"
                                {{ $studentFee->status == 'Partial' ? 'selected' : '' }}>
                                Partial
                            </option>

                            <option value="Unpaid"
                                {{ $studentFee->status == 'Unpaid' ? 'selected' : '' }}>
                                Unpaid
                            </option>

                        </select>

                    </div>

                </div>

                <div class="mt-4">

                    <button type="submit"
                            class="btn btn-primary">

                        Update Payment

                    </button>

                    <a href="{{ route('student-fees.index') }}"
                       class="btn btn-secondary">

                        Cancel

                    </a>

                </div>

            </form>

        </div>

    </div>

</div>

@endsection