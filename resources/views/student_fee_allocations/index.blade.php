@extends('layouts.master')

@section('title', 'Student Fee Allocations')

@section('content')

<div class="container-fluid py-4">

    {{-- Header --}}
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">

        <div>
            <h4 class="fw-bold mb-1">
                <i class="fas fa-file-invoice-dollar me-2 text-primary"></i>
                Student Fee Allocations
            </h4>

            <p class="text-muted mb-0">
                Manage individual student fee items and account balances.
            </p>
        </div>

        <a href="{{ route('student-fee-allocations.create') }}"
           class="btn btn-primary">
            <i class="fas fa-plus me-1"></i>
            Allocate Fees
        </a>

    </div>

    {{-- Alerts --}}
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show">
            <i class="fas fa-check-circle me-2"></i>
            {{ session('success') }}

            <button type="button"
                    class="btn-close"
                    data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show">
            <i class="fas fa-exclamation-circle me-2"></i>
            {{ session('error') }}

            <button type="button"
                    class="btn-close"
                    data-bs-dismiss="alert"></button>
        </div>
    @endif

    {{-- Filters --}}
    <div class="card border-0 shadow-sm mb-4">

        <div class="card-body">

            <form method="GET"
                  action="{{ route('student-fee-allocations.index') }}">

                <div class="row g-3">

                    <div class="col-md-5">

                        <label class="form-label fw-semibold">
                            Search Student
                        </label>

                        <input type="text"
                               name="search"
                               value="{{ request('search') }}"
                               class="form-control"
                               placeholder="Student ID or student name">

                    </div>

                    <div class="col-md-4">

                        <label class="form-label fw-semibold">
                            Academic Year
                        </label>

                        <select name="academic_year_id"
                                class="form-select">

                            <option value="">
                                All Academic Years
                            </option>

                            @foreach($academicYears as $year)

                                <option value="{{ $year->id }}"
                                    @selected(request('academic_year_id') == $year->id)>
                                    {{ $year->name }}
                                </option>

                            @endforeach

                        </select>

                    </div>

                    <div class="col-md-3 d-flex align-items-end">

                        <button class="btn btn-primary w-100">
                            <i class="fas fa-search me-1"></i>
                            Search
                        </button>

                    </div>

                </div>

            </form>

        </div>

    </div>

    {{-- Accounts --}}
    <div class="card border-0 shadow-sm">

        <div class="card-header bg-white border-0 py-3">

            <div class="d-flex justify-content-between">

                <div>
                    <h6 class="fw-bold mb-1">
                        Student Accounts
                    </h6>

                    <small class="text-muted">
                        {{ $accounts->total() }} account(s)
                    </small>
                </div>

            </div>

        </div>

        <div class="table-responsive">

            <table class="table table-hover align-middle mb-0">

                <thead class="table-light">

                    <tr>
                        <th>Student</th>
                        <th>Class</th>
                        <th>Academic Year</th>
                        <th>Fee Items</th>
                        <th>Total</th>
                        <th>Paid</th>
                        <th>Balance</th>
                        <th>Status</th>
                        <th class="text-end">Action</th>
                    </tr>

                </thead>

                <tbody>

                @forelse($accounts as $account)

                    @php
                        $student = $account->student;

                        $studentName = $student
                            ? trim(
                                $student->first_name . ' ' .
                                ($student->middle_name ?? '') . ' ' .
                                $student->last_name
                            )
                            : 'Unknown Student';
                    @endphp

                    <tr>

                        <td>

                            <div class="fw-semibold">
                                {{ $studentName }}
                            </div>

                            <small class="text-muted">
                                {{ $student->student_id ?? '—' }}
                            </small>

                        </td>

                        <td>
                            {{ $account->studentClass->name ?? '—' }}
                        </td>

                        <td>
                            {{ $account->academicYear->name ?? '—' }}
                        </td>

                        <td>
                            <span class="badge bg-light text-dark">
                                {{ $account->feeItems->count() }}
                            </span>
                        </td>

                        <td class="fw-semibold">
                            GH₵ {{ number_format($account->total_fees, 2) }}
                        </td>

                        <td class="text-success fw-semibold">
                            GH₵ {{ number_format($account->amount_paid, 2) }}
                        </td>

                        <td class="text-danger fw-semibold">
                            GH₵ {{ number_format($account->balance, 2) }}
                        </td>

                        <td>

                            @if($account->status === 'paid')

                                <span class="badge bg-success">
                                    Paid
                                </span>

                            @elseif($account->status === 'partial')

                                <span class="badge bg-warning text-dark">
                                    Partial
                                </span>

                            @else

                                <span class="badge bg-secondary">
                                    Pending
                                </span>

                            @endif

                        </td>

                        <td class="text-end">

                            <a href="{{ route(
                                'student-fee-allocations.show',
                                $account
                            ) }}"
                               class="btn btn-sm btn-outline-primary">

                                <i class="fas fa-eye me-1"></i>
                                View

                            </a>

                        </td>

                    </tr>

                @empty

                    <tr>

                        <td colspan="9"
                            class="text-center py-5">

                            <i class="fas fa-file-invoice-dollar fa-2x text-muted mb-3"></i>

                            <h6 class="fw-semibold">
                                No student fee accounts found
                            </h6>

                            <p class="text-muted mb-0">
                                Create or assign a student fee account to begin.
                            </p>

                        </td>

                    </tr>

                @endforelse

                </tbody>

            </table>

        </div>

        @if($accounts->hasPages())

            <div class="card-footer bg-white border-0">
                {{ $accounts->links() }}
            </div>

        @endif

    </div>

</div>

@endsection