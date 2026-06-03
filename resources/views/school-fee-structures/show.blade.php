@extends('layouts.master')

@section('title', 'Fee Structure Details')

@section('content')
<div class="container-fluid">

    <div class="card shadow-sm">

        <div class="card-body">

            <h5>Fee Structure Details</h5>
            <hr>

            <p><strong>Academic Year:</strong> {{ $schoolFeeStructure->academicYear->name }}</p>
            <p><strong>Term:</strong> {{ $schoolFeeStructure->term->name }}</p>
            <p><strong>Class:</strong> {{ $schoolFeeStructure->studentClass->name }}</p>
            <p><strong>Category:</strong> {{ $schoolFeeStructure->feeCategory->name }}</p>
            <p><strong>Amount:</strong> {{ number_format($schoolFeeStructure->amount, 2) }}</p>
            <p><strong>Status:</strong>
                {{ $schoolFeeStructure->is_active ? 'Active' : 'Inactive' }}
            </p>

        </div>

    </div>

</div>
@endsection