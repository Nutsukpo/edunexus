@extends('layouts.master')

@section('title', 'Edit Invoice')

@section('content')

<div class="container-fluid">

    <div class="d-flex justify-content-between align-items-center mb-4">

        <div>
            <h4 class="fw-bold">
                Edit Invoice
            </h4>

            <p class="text-muted mb-0">
                Update invoice information
            </p>
        </div>

        <a href="{{ route('billing.index') }}"
           class="btn btn-secondary">
            Back
        </a>

    </div>

    <div class="card shadow-sm border-0">

        <div class="card-body">

            <form action="{{ route('billing.update', $billing->id) }}"
                  method="POST">

                @csrf
                @method('PUT')

                <div class="row">

                    <div class="col-md-6 mb-3">

                        <label class="form-label">
                            Invoice Number
                        </label>

                        <input type="text"
                               name="invoice_number"
                               class="form-control"
                               value="{{ old('invoice_number', $billing->invoice_number) }}"
                               readonly>

                    </div>

                    <div class="col-md-6 mb-3">

                        <label class="form-label">
                            Status
                        </label>

                        <select name="status"
                                class="form-select">

                            <option value="Unpaid"
                                {{ $billing->status == 'Unpaid' ? 'selected' : '' }}>
                                Unpaid
                            </option>

                            <option value="Partially Paid"
                                {{ $billing->status == 'Partially Paid' ? 'selected' : '' }}>
                                Partially Paid
                            </option>

                            <option value="Paid"
                                {{ $billing->status == 'Paid' ? 'selected' : '' }}>
                                Paid
                            </option>

                        </select>

                    </div>

                    <div class="col-md-4 mb-3">

                        <label class="form-label">
                            Total Amount
                        </label>

                        <input type="number"
                               step="0.01"
                               name="total_amount"
                               class="form-control"
                               value="{{ old('total_amount', $billing->total_amount) }}">

                    </div>

                    <div class="col-md-4 mb-3">

                        <label class="form-label">
                            Amount Paid
                        </label>

                        <input type="number"
                               step="0.01"
                               name="amount_paid"
                               class="form-control"
                               value="{{ old('amount_paid', $billing->amount_paid) }}">

                    </div>

                    <div class="col-md-4 mb-3">

                        <label class="form-label">
                            Balance
                        </label>

                        <input type="number"
                               step="0.01"
                               name="balance"
                               class="form-control"
                               value="{{ old('balance', $billing->balance) }}">

                    </div>

                    <div class="col-md-6 mb-3">

                        <label class="form-label">
                            Invoice Date
                        </label>

                        <input type="date"
                               name="invoice_date"
                               class="form-control"
                               value="{{ optional($billing->invoice_date)->format('Y-m-d') }}">

                    </div>

                    <div class="col-md-6 mb-3">

                        <label class="form-label">
                            Due Date
                        </label>

                        <input type="date"
                               name="due_date"
                               class="form-control"
                               value="{{ optional($billing->due_date)->format('Y-m-d') }}">

                    </div>

                </div>

                <div class="text-end">

                    <button type="submit"
                            class="btn btn-primary">

                        <i class="fas fa-save me-1"></i>
                        Update Invoice

                    </button>

                </div>

            </form>

        </div>

    </div>

</div>

@endsection