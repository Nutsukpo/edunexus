@extends('layouts.master')

@section('title', 'Billing Engine')

@section('content')

<div class="container-fluid">

    {{-- PAGE HEADER --}}
    <div class="d-flex justify-content-between align-items-center mb-4 mt-3">
        <div>
            <h5 class="fw-bold mb-1">Student Invoices</h5>
        </div>

        <a href="{{ route('billing.create') }}"
           class="btn btn-white text-dark">
            <i class="fas fa-plus-circle me-1"></i>
            Generate Invoice
        </a>
    </div>

    {{-- SUCCESS MESSAGE --}}
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show">
            {{ session('success') }}

            <button type="button"
                    class="btn-close"
                    data-bs-dismiss="alert">
            </button>
        </div>
    @endif

    {{-- TABLE CARD --}}
    <div class="card shadow-sm border-0">

        <div class="card-body">

            <div class="table-responsive">

                <table class="table table-hover align-middle">

                    <thead class="table-light">
                        <tr>
                            <th>#</th>
                            <th>Invoice No.</th>
                            <th>Student</th>
                            <th>Total</th>
                            <th>Paid</th>
                            <th>Balance</th>
                            <th>Status</th>
                            <th width="180">Actions</th>
                        </tr>
                    </thead>

                    <tbody>

                        @forelse($invoices as $key => $invoice)

                            <tr>

                                <td>{{ $key + 1 }}</td>

                                <td>
                                    <strong>
                                        {{ $invoice->invoice_number }}
                                    </strong>
                                </td>

                                <td>
                                    {{ $invoice->student?->full_name }}
                                </td>

                                <td>
                                    GH₵ {{ number_format($invoice->total_amount, 2) }}
                                </td>

                                <td>
                                    GH₵ {{ number_format($invoice->amount_paid ?? 0, 2) }}
                                </td>

                                <td>
                                    GH₵ {{ number_format($invoice->balance, 2) }}
                                </td>

                                <td>

                                    @if($invoice->status == 'Paid')
                                        <span class="badge bg-success">
                                            Paid
                                        </span>

                                    @elseif($invoice->status == 'Partially Paid')
                                        <span class="badge bg-warning text-dark">
                                            Partially Paid
                                        </span>

                                    @else
                                        <span class="badge bg-danger">
                                            Unpaid
                                        </span>
                                    @endif

                                </td>

                                <td>

                                    <div class="btn-group">

                                        {{-- SHOW --}}
                                        <a href="{{ route('billing.show', $invoice->id) }}"
                                           class="btn btn-sm btn-white text-dark">

                                            <i class="fas fa-eye"></i>
                                        </a>

                                        {{-- EDIT --}}
                                        <a href="{{ route('billing.edit', $invoice->id) }}"
                                           class="btn btn-sm btn-white text-dark">

                                            <i class="fas fa-edit"></i>
                                        </a>

                                        {{-- DELETE --}}
                                        <form action="{{ route('billing.destroy', $invoice->id) }}"
                                              method="POST"
                                              onsubmit="return confirm('Delete this invoice?')"
                                              class="d-inline">

                                            @csrf
                                            @method('DELETE')

                                            <button type="submit"
                                                    class="btn btn-sm btn-white text-dark">

                                                <i class="fas fa-trash"></i>
                                            </button>

                                        </form>

                                    </div>

                                </td>

                            </tr>

                        @empty

                            <tr>
                                <td colspan="8"
                                    class="text-center py-4">

                                    <div class="text-muted">
                                        No invoices found.
                                    </div>

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