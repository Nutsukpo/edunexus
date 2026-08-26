{{-- resources/views/fee-payments/index.blade.php --}}
@extends('layouts.master')

@section('title', 'Fee Payments')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <div class="card shadow-sm">
                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0 fw-bold text-dark">
                        <i class="fas fa-money-bill-wave text-primary me-1"></i> Fee Payments
                    </h5>
                    <a href="{{ route('fee-payments.create') }}" class="btn btn-primary btn-sm">
                        <i class="fas fa-plus-circle me-1"></i> New Payment
                    </a>
                </div>

                <div class="card-body">
                    {{-- Filters --}}
                    <div class="card mb-4 border-0 shadow-sm">
                        <div class="card-body">
                            <form method="GET" action="{{ route('fee-payments.index') }}" class="row g-3">
                                <div class="col-md-2">
                                    <label class="form-label fw-semibold text-dark">Student</label>
                                    <select name="student_id" class="form-select">
                                        <option value="">All Students</option>
                                        @foreach($students as $student)
                                            <option value="{{ $student->id }}" 
                                                {{ request('student_id') == $student->id ? 'selected' : '' }}>
                                                {{ $student->first_name }} {{ $student->last_name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="col-md-2">
                                    <label class="form-label fw-semibold text-dark">Payment Method</label>
                                    <select name="payment_method" class="form-select">
                                        <option value="">All Methods</option>
                                        @foreach($paymentMethods as $method)
                                            <option value="{{ $method }}" 
                                                {{ request('payment_method') == $method ? 'selected' : '' }}>
                                                {{ ucfirst(str_replace('_', ' ', $method)) }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="col-md-2">
                                    <label class="form-label fw-semibold text-dark">Status</label>
                                    <select name="status" class="form-select">
                                        <option value="">All Status</option>
                                        @foreach($statuses as $status)
                                            <option value="{{ $status }}" 
                                                {{ request('status') == $status ? 'selected' : '' }}>
                                                {{ ucfirst($status) }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="col-md-2">
                                    <label class="form-label fw-semibold text-dark">Date From</label>
                                    <input type="date" name="date_from" class="form-control" 
                                           value="{{ request('date_from') }}">
                                </div>

                                <div class="col-md-2">
                                    <label class="form-label fw-semibold text-dark">Date To</label>
                                    <input type="date" name="date_to" class="form-control" 
                                           value="{{ request('date_to') }}">
                                </div>

                                <div class="col-md-2 d-flex align-items-end">
                                    <button type="submit" class="btn btn-primary w-100">
                                        <i class="fas fa-filter me-1"></i> Filter
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>

                    {{-- Payments Table --}}
                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>Receipt #</th>
                                    <th>Student</th>
                                    <th>Class</th>
                                    <th>Amount</th>
                                    <th>Method</th>
                                    <th>Date</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($payments as $payment)
                                    <tr>
                                        <td>
                                            <span class="fw-bold">{{ $payment->receipt_number ?? 'N/A' }}</span>
                                        </td>
                                        <td>
                                            @if($payment->student)
                                                {{ $payment->student->first_name }} {{ $payment->student->last_name }}
                                            @else
                                                <span class="text-muted">N/A</span>
                                            @endif
                                        </td>
                                        <td>
                                            {{-- FIXED: Get class from studentFeeAccount relationship --}}
                                            @if($payment->studentFeeAccount && $payment->studentFeeAccount->studentClass)
                                                {{ $payment->studentFeeAccount->studentClass->name }}
                                            @elseif($payment->student && $payment->student->studentClass)
                                                {{ $payment->student->studentClass->name }}
                                            @else
                                                <span class="text-muted">N/A</span>
                                            @endif
                                        </td>
                                        <td>
                                            <span class="fw-bold text-primary">
                                                GHS {{ number_format($payment->amount, 2) }}
                                            </span>
                                        </td>
                                        <td>
                                            <span class="badge bg-info">
                                                {{ ucfirst(str_replace('_', ' ', $payment->payment_method ?? 'N/A')) }}
                                            </span>
                                        </td>
                                        <td>{{ $payment->payment_date ? $payment->payment_date->format('M d, Y') : 'N/A' }}</td>
                                        <td>
                                            @php
                                                $statusColors = [
                                                    'pending' => 'warning',
                                                    'completed' => 'success',
                                                    'failed' => 'danger',
                                                    'refunded' => 'info',
                                                    'reversed' => 'secondary'
                                                ];
                                            @endphp
                                            <span class="badge bg-{{ $statusColors[$payment->status] ?? 'secondary' }}">
                                                {{ ucfirst($payment->status ?? 'N/A') }}
                                            </span>
                                        </td>
                                        <td>
                                            <div class="btn-group" role="group">
                                                <a href="{{ route('fee-payments.show', $payment->id) }}" 
                                                   class="btn btn-sm btn-info">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                <a href="{{ route('fee-payments.print-receipt', $payment->id) }}" 
                                                   class="btn btn-sm btn-secondary" target="_blank">
                                                    <i class="fas fa-print"></i>
                                                </a>
                                                @if($payment->status == 'pending')
                                                    <a href="{{ route('fee-payments.edit', $payment->id) }}" 
                                                       class="btn btn-sm btn-primary">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="8" class="text-center py-4">
                                            <i class="fas fa-info-circle me-1"></i> No payments found.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    {{-- Pagination --}}
                    <div class="d-flex justify-content-between align-items-center mt-3">
                        <div>
                            Showing {{ $payments->firstItem() ?? 0 }} to {{ $payments->lastItem() ?? 0 }} 
                            of {{ $payments->total() }} payments
                        </div>
                        <div>
                            {{ $payments->appends(request()->query())->links() }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection