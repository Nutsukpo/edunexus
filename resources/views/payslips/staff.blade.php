@extends('layouts.master')

@section('content')
<div class="container">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4>Payslips for {{ $staff->name }}</h4>
                    <a href="{{ route('payslips.create') }}?staff_id={{ $staff->id }}" 
                       class="btn btn-primary">
                        <i class="fas fa-plus"></i> Generate Payslip
                    </a>
                </div>

                <div class="card-body">
                    @if($payslips->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Month</th>
                                        <th>Year</th>
                                        <th>Basic Salary</th>
                                        <th>Allowances</th>
                                        <th>Deductions</th>
                                        <th>Net Pay</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($payslips as $payslip)
                                    <tr>
                                        <td>{{ $loop->iteration }}</td>
                                        <td>{{ $payslip->month_name }}</td>
                                        <td>{{ $payslip->year }}</td>
                                        <td>${{ number_format($payslip->basic_salary, 2) }}</td>
                                        <td>${{ number_format($payslip->allowances, 2) }}</td>
                                        <td>${{ number_format($payslip->deductions, 2) }}</td>
                                        <td><strong>${{ number_format($payslip->net_pay, 2) }}</strong></td>
                                        <td>
                                            <div class="btn-group">
                                                <a href="{{ route('payslips.show', $payslip) }}" 
                                                   class="btn btn-sm btn-info">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                <a href="{{ route('payslips.pdf', $payslip) }}" 
                                                   class="btn btn-sm btn-danger">
                                                    <i class="fas fa-file-pdf"></i>
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-center py-5">
                            <h5>No payslips found for {{ $staff->name }}</h5>
                            <a href="{{ route('payslips.create') }}?staff_id={{ $staff->id }}" 
                               class="btn btn-primary mt-3">
                                Generate First Payslip
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection