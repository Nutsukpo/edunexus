@extends('layouts.master')

@section('title', 'Add Student Fee')

@section('content')

<div class="container-fluid">

    <div class="card border-0 shadow-sm">

        <div class="card-header bg-white">
            <h4 class="mb-0">
                Add Student Fee Payment
            </h4>
        </div>

        <div class="card-body">

        <form action="{{ route('student-fees.store') }}" method="POST">
    @csrf

    <div class="row">

        {{-- STUDENT --}}
        <div class="col-md-6 mb-3">
            <label>Student</label>
            <select name="student_id" class="form-select" required>
                <option value="">Select Student</option>

                @foreach($students as $student)
                    <option value="{{ $student->id }}">
                        {{ $student->first_name }} {{ $student->last_name }}
                    </option>
                @endforeach
            </select>
        </div>

        {{-- FEE STRUCTURE --}}
        <div class="col-md-6 mb-3">
            <label>Fee Structure</label>
            <select name="fee_structure_id" class="form-select" required>
                <option value="">Select Fee</option>

                @foreach($feeStructures as $fee)
                    <option value="{{ $fee->id }}">
                        {{ $fee->name }} - {{ $fee->amount }}
                    </option>
                @endforeach
            </select>
        </div>

        {{-- ACADEMIC YEAR --}}
        <div class="col-md-6 mb-3">
            <label>Academic Year</label>
            <select name="academic_year_id" class="form-select" required>
                <option value="">Select Year</option>

                @foreach($academicYears as $year)
                    <option value="{{ $year->id }}">
                        {{ $year->year }}
                    </option>
                @endforeach
            </select>
        </div>

        {{-- TERM --}}
        <div class="col-md-6 mb-3">
            <label>Term</label>
            <select name="term_id" class="form-select" required>
                <option value="">Select Term</option>

                @foreach($terms as $term)
                    <option value="{{ $term->id }}">
                        {{ $term->name }}
                    </option>
                @endforeach
            </select>
        </div>

        {{-- STUDENT CLASS --}}
        <div class="col-md-6 mb-3">
            <label>Student Class (Optional)</label>
            <select name="student_class_id" class="form-select">
                <option value="">Select Class (Optional)</option>

                @foreach($classes as $class)
                    <option value="{{ $class->id }}">
                        {{ $class->name }}
                    </option>
                @endforeach
            </select>
        </div>

        {{-- AMOUNT PAID --}}
        <div class="col-md-6 mb-3">
            <label>Amount Paid</label>
            <input type="number"
                   step="0.01"
                   name="amount_paid"
                   class="form-control"
                   required>
        </div>

        {{-- PAYMENT DATE --}}
        <div class="col-md-6 mb-3">
            <label>Payment Date</label>
            <input type="date" name="payment_date" class="form-control" required>
        </div>

    </div>

    <button class="btn btn-primary">
        Save
    </button>
</form>
        </div>

    </div>

</div>

@endsection