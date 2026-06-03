@extends('layouts.master')

@section('title', 'Payments')

@section('content')

<div class="container-fluid">

    {{-- PAGE HEADER --}}
    <div class="d-flex justify-content-between align-items-center mb-4">

        <div>
            <h3 class="fw-bold mb-1">
                <i class="fas fa-money-check-alt me-2"></i>
                Payments
            </h3>

            <p class="text-muted mb-0">
                Manage all student payments
            </p>
        </div>

        <a href="{{ route('payments.create') }}"
           class="btn btn-primary">

            <i class="fas fa-plus-circle me-1"></i>
            Add Payment

        </a>

    </div>

    {{-- SUCCESS ALERT --}}
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
                            <th>Amount</th>
                            <th>Method</th>
                            <th>Date</th>
                            <th width="180">Actions</th>
                        </tr>

                    </thead>

                    <tbody>

                        @forelse($payments as $payment)

                            <tr>

                                <td>{{ $loop->iteration }}</td>

                                <td>
                                    {{ $payment->student->first_name ?? '' }}
                                    {{ $payment->student->last_name ?? '' }}
                                </td>

                                <td>
                                    GHS {{ number_format($payment->amount, 2) }}
                                </td>

                                <td>
                                    {{ $payment->payment_method }}
                                </td>

                                <td>
                                    {{ $payment->payment_date }}
                                </td>

                                <td>

                                    <a href="{{ route('payments.show', $payment->id) }}"
                                       class="btn btn-sm btn-info">

                                        <i class="fas fa-eye"></i>

                                    </a>

                                    <a href="{{ route('payments.edit', $payment->id) }}"
                                       class="btn btn-sm btn-warning">

                                        <i class="fas fa-edit"></i>

                                    </a>

                                    <form action="{{ route('payments.destroy', $payment->id) }}"
                                          method="POST"
                                          class="d-inline">

                                        @csrf
                                        @method('DELETE')

                                        <button type="submit"
                                                class="btn btn-sm btn-danger"
                                                onclick="return confirm('Delete payment?')">

                                            <i class="fas fa-trash"></i>

                                        </button>

                                    </form>

                                </td>

                            </tr>

                        @empty

                            <tr>

                                <td colspan="6"
                                    class="text-center text-muted py-4">

                                    No payments found

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