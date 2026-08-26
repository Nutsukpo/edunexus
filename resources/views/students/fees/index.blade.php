{{-- resources/views/student/fees/index.blade.php --}}
@extends('students.layouts.app')

@section('title', 'My Fees')

@section('content')
<div class="container">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h4>My Fee Summary</h4>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4">
                            <div class="info-box bg-info">
                                <span class="info-box-icon"><i class="fas fa-money-bill-wave"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Total Fees</span>
                                    <span class="info-box-number">{{ number_format($totalAmount, 2) }}</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="info-box bg-success">
                                <span class="info-box-icon"><i class="fas fa-check-circle"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Paid</span>
                                    <span class="info-box-number">{{ number_format($totalPaid, 2) }}</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="info-box bg-danger">
                                <span class="info-box-icon"><i class="fas fa-exclamation-triangle"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Balance</span>
                                    <span class="info-box-number">{{ number_format($totalBalance, 2) }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row mt-4">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h4>Fee Breakdown</h4>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>Description</th>
                                    <th>Academic Year</th>
                                    <th>Term</th>
                                    <th>Total</th>
                                    <th>Paid</th>
                                    <th>Balance</th>
                                    <th>Status</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($feeAllocations as $allocation)
                                <tr>
                                    <td>{{ $allocation->feeStructure->name ?? 'N/A' }}</td>
                                    <td>{{ $allocation->academicYear->name ?? 'N/A' }}</td>
                                    <td>{{ $allocation->term->name ?? 'N/A' }}</td>
                                    <td>{{ number_format($allocation->total_amount, 2) }}</td>
                                    <td>{{ number_format($allocation->paid_amount, 2) }}</td>
                                    <td>{{ number_format($allocation->balance, 2) }}</td>
                                    <td>
                                        @if($allocation->status == 'paid')
                                            <span class="badge badge-success">Paid</span>
                                        @elseif($allocation->status == 'partial')
                                            <span class="badge badge-warning">Partial</span>
                                        @elseif($allocation->status == 'overdue')
                                            <span class="badge badge-danger">Overdue</span>
                                        @else
                                            <span class="badge badge-secondary">Pending</span>
                                        @endif
                                    </td>
                                    <td>
                                        <a href="{{ route('student.fees.show', $allocation->id) }}" class="btn btn-sm btn-primary">
                                            View Details
                                        </a>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="8" class="text-center">No fee records found.</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection