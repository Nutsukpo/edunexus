{{-- resources/views/fee-payments/reports/outstanding.blade.php --}}
@extends('layouts.app')

@section('title', 'Outstanding Fees Report')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Outstanding Fees</h3>
                </div>
                <div class="card-body">
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <div class="small-box bg-danger">
                                <div class="inner">
                                    <h3>Ghc {{ number_format($totalOutstanding, 2) }}</h3>
                                    <p>Total Outstanding Balance</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="small-box bg-warning">
                                <div class="inner">
                                    <h3>{{ $totalStudents }}</h3>
                                    <p>Students with Outstanding Fees</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>Student</th>
                                <th>Class</th>
                                <th>Academic Year</th>
                                <th>Total Fees</th>
                                <th>Amount Paid</th>
                                <th>Balance</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($outstandingFees as $fee)
                                <tr>
                                    <td>{{ $fee->student->name ?? 'N/A' }}</td>
                                    <td>{{ $fee->studentClass->name ?? 'N/A' }}</td>
                                    <td>{{ $fee->academicYear->name ?? 'N/A' }}</td>
                                    <td>Ghc {{ number_format($fee->total_fees, 2) }}</td>
                                    <td>Ghc {{ number_format($fee->amount_paid, 2) }}</td>
                                    <td>Ghc {{ number_format($fee->balance, 2) }}</td>
                                    <td>
                                        @if($fee->status == 'partial')
                                            <span class="badge badge-warning">Partial</span>
                                        @else
                                            <span class="badge badge-danger">Pending</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center">No outstanding fees found</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                    
                    {{ $outstandingFees->links() }}
                </div>
            </div>
        </div>
    </div>
</div>
@endsection