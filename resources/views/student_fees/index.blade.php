@extends('layouts.master')

@section('title', 'Student Fees')

@section('content')

<div class="container-fluid">

    {{-- PAGE HEADER --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h3 class="fw-bold mb-1">
                <i class="fas fa-money-bill-wave me-2"></i>
                Student Fees
            </h3>
            <p class="text-muted mb-0">
                Manage all student fee payments
            </p>
        </div>

        <a href="{{ route('student-fees.create') }}"
           class="btn btn-primary">
            <i class="fas fa-plus-circle me-1"></i>
            Add Payment
        </a>
    </div>

    {{-- ALERTS --}}
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show">
            {{ session('success') }}

            <button type="button"
                    class="btn-close"
                    data-bs-dismiss="alert"></button>
        </div>
    @endif

    {{-- TABLE CARD --}}
    <div class="card border-0 shadow-sm">

        <div class="card-body">

            <div class="table-responsive">

                <table class="table table-hover align-middle">

                    <thead class="table-light">
                        <tr>
                            <th>#</th>
                            <th>Student</th>
                            <th>Total Fee</th>
                            <th>Paid</th>
                            <th>Balance</th>
                            <th>Status</th>
                            <th width="180">Actions</th>
                        </tr>
                    </thead>

                    <tbody>

                        @forelse($studentFees as $fee)

                            <tr>

                                <td>{{ $loop->iteration }}</td>

                                <td>
                                    {{ $fee->student->first_name ?? '' }}
                                    {{ $fee->student->last_name ?? '' }}
                                </td>

                                <td>
                                    GHS {{ number_format($fee->total_fee, 2) }}
                                </td>

                                <td>
                                    GHS {{ number_format($fee->paid_amount, 2) }}
                                </td>

                                <td>
                                    GHS {{ number_format($fee->balance, 2) }}
                                </td>

                                <td>

                                    @if($fee->status == 'Paid')

                                        <span class="badge bg-success">
                                            Paid
                                        </span>

                                    @elseif($fee->status == 'Partial')

                                        <span class="badge bg-warning text-dark">
                                            Partial
                                        </span>

                                    @else

                                        <span class="badge bg-danger">
                                            Unpaid
                                        </span>

                                    @endif

                                </td>

                                <td>

                                    <a href="{{ route('student-fees.show', $fee->id) }}"
                                       class="btn btn-sm btn-info">
                                        <i class="fas fa-eye"></i>
                                    </a>

                                    <a href="{{ route('student-fees.edit', $fee->id) }}"
                                       class="btn btn-sm btn-warning">
                                        <i class="fas fa-edit"></i>
                                    </a>

                                    <form action="{{ route('student-fees.destroy', $fee->id) }}"
                                          method="POST"
                                          class="d-inline">

                                        @csrf
                                        @method('DELETE')

                                        <button type="submit"
                                                class="btn btn-sm btn-danger"
                                                onclick="return confirm('Delete this payment?')">

                                            <i class="fas fa-trash"></i>

                                        </button>

                                    </form>

                                </td>

                            </tr>

                        @empty

                            <tr>
                                <td colspan="7" class="text-center text-muted py-4">
                                    No fee records found
                                </td>
                            </tr>

                        @endforelse

                    </tbody>

                </table>

            </div>

        </div>

    </div>

</div>

@endsection