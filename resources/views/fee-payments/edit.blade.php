@extends('layouts.master')

@section('title', 'Edit Payment - ' . $payment->receipt_number)

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <div class="card shadow-sm">
                <div class="card-header bg-white d-flex justify-content-between align-items-center flex-wrap gap-2">
                    <h5 class="mb-0 fw-bold text-dark">
                        <i class="fas fa-edit text-warning me-1"></i> Edit Payment
                        <span class="badge bg-primary ms-2">{{ $payment->receipt_number }}</span>
                    </h5>
                    <div>
                        <a href="{{ route('fee-payments.show', $payment->id) }}" class="btn btn-secondary btn-sm">
                            <i class="fas fa-arrow-left me-1"></i> Back
                        </a>
                    </div>
                </div>

                <div class="card-body">
                    @if($errors->any())
                        <div class="alert alert-danger">
                            <i class="fas fa-exclamation-circle me-1"></i> Please fix the following errors:
                            <ul class="mb-0 mt-2">
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <div class="alert alert-info">
                        <i class="fas fa-info-circle me-2"></i>
                        You are editing a <strong>PENDING</strong> payment. Changes will update the payment record.
                    </div>

                    <form action="{{ route('fee-payments.update', $payment->id) }}" method="POST">
                        @csrf
                        @method('PUT')

                        <div class="row g-3">
                            {{-- Student Information (Read Only) --}}
                            <div class="col-md-6">
                                <label class="form-label fw-semibold text-dark">Student</label>
                                <input type="text" class="form-control" 
                                       value="{{ $payment->student->first_name ?? 'N/A' }} {{ $payment->student->last_name ?? '' }}" disabled>
                                <input type="hidden" name="student_id" value="{{ $payment->student_id }}">
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-semibold text-dark">Receipt Number</label>
                                <input type="text" class="form-control" 
                                       value="{{ $payment->receipt_number }}" disabled>
                            </div>

                            {{-- Payment Details --}}
                            <div class="col-md-4">
                                <label class="form-label fw-semibold text-dark">Amount <span class="text-danger">*</span></label>
                                <input type="number" name="amount" class="form-control @error('amount') is-invalid @enderror" 
                                       step="0.01" min="0.01" value="{{ old('amount', $payment->amount) }}" required>
                                @error('amount')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-4">
                                <label class="form-label fw-semibold text-dark">Penalty</label>
                                <input type="number" name="penalty_amount" class="form-control @error('penalty_amount') is-invalid @enderror" 
                                       step="0.01" min="0" value="{{ old('penalty_amount', $payment->penalty_amount) }}">
                                @error('penalty_amount')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-4">
                                <label class="form-label fw-semibold text-dark">Discount</label>
                                <input type="number" name="discount_amount" class="form-control @error('discount_amount') is-invalid @enderror" 
                                       step="0.01" min="0" value="{{ old('discount_amount', $payment->discount_amount) }}">
                                @error('discount_amount')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-4">
                                <label class="form-label fw-semibold text-dark">Payment Method <span class="text-danger">*</span></label>
                                <select name="payment_method" class="form-select @error('payment_method') is-invalid @enderror" required>
                                    <option value="">Select Method</option>
                                    @foreach($paymentMethods as $method)
                                        <option value="{{ $method }}" 
                                            {{ old('payment_method', $payment->payment_method) == $method ? 'selected' : '' }}>
                                            {{ ucfirst(str_replace('_', ' ', $method)) }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('payment_method')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-4">
                                <label class="form-label fw-semibold text-dark">Payment Type <span class="text-danger">*</span></label>
                                <select name="payment_type" class="form-select @error('payment_type') is-invalid @enderror" required>
                                    @foreach($paymentTypes as $type)
                                        <option value="{{ $type }}" 
                                            {{ old('payment_type', $payment->payment_type) == $type ? 'selected' : '' }}>
                                            {{ ucfirst($type) }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('payment_type')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-4">
                                <label class="form-label fw-semibold text-dark">Payment Date <span class="text-danger">*</span></label>
                                <input type="date" name="payment_date" class="form-control @error('payment_date') is-invalid @enderror" 
                                       value="{{ old('payment_date', $payment->payment_date->format('Y-m-d')) }}" required>
                                @error('payment_date')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            {{-- Additional Details --}}
                            <div class="col-md-4">
                                <label class="form-label fw-semibold text-dark">Reference Number</label>
                                <input type="text" name="reference_number" class="form-control @error('reference_number') is-invalid @enderror" 
                                       value="{{ old('reference_number', $payment->reference_number) }}" 
                                       placeholder="Enter reference number">
                                @error('reference_number')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-4">
                                <label class="form-label fw-semibold text-dark">Bank Name</label>
                                <input type="text" name="bank_name" class="form-control @error('bank_name') is-invalid @enderror" 
                                       value="{{ old('bank_name', $payment->bank_name) }}" 
                                       placeholder="Enter bank name">
                                @error('bank_name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-4">
                                <label class="form-label fw-semibold text-dark">Cheque Number</label>
                                <input type="text" name="cheque_number" class="form-control @error('cheque_number') is-invalid @enderror" 
                                       value="{{ old('cheque_number', $payment->cheque_number) }}" 
                                       placeholder="Enter cheque number">
                                @error('cheque_number')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-4">
                                <label class="form-label fw-semibold text-dark">Transaction ID</label>
                                <input type="text" name="transaction_id" class="form-control @error('transaction_id') is-invalid @enderror" 
                                       value="{{ old('transaction_id', $payment->transaction_id) }}" 
                                       placeholder="Enter transaction ID">
                                @error('transaction_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-12">
                                <label class="form-label fw-semibold text-dark">Notes</label>
                                <textarea name="notes" class="form-control @error('notes') is-invalid @enderror" 
                                          rows="3" placeholder="Additional notes">{{ old('notes', $payment->notes) }}</textarea>
                                @error('notes')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            {{-- Summary --}}
                            <div class="col-12">
                                <div class="alert alert-info">
                                    <div class="row">
                                        <div class="col-md-3">
                                            <strong>Amount:</strong> 
                                            <span id="amountDisplay">GHS {{ number_format($payment->amount, 2) }}</span>
                                        </div>
                                        <div class="col-md-3">
                                            <strong>Penalty:</strong> 
                                            <span id="penaltyDisplay">GHS {{ number_format($payment->penalty_amount, 2) }}</span>
                                        </div>
                                        <div class="col-md-3">
                                            <strong>Discount:</strong> 
                                            <span id="discountDisplay">GHS {{ number_format($payment->discount_amount, 2) }}</span>
                                        </div>
                                        <div class="col-md-3">
                                            <strong>Net Amount:</strong> 
                                            <span id="netDisplay" class="text-success fw-bold">
                                                GHS {{ number_format($payment->net_amount, 2) }}
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {{-- Submit Buttons --}}
                            <div class="col-12 text-end">
                                <a href="{{ route('fee-payments.show', $payment->id) }}" class="btn btn-secondary">
                                    <i class="fas fa-times me-1"></i> Cancel
                                </a>
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save me-1"></i> Update Payment
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const amountInput = document.querySelector('input[name="amount"]');
        const penaltyInput = document.querySelector('input[name="penalty_amount"]');
        const discountInput = document.querySelector('input[name="discount_amount"]');

        function updateTotals() {
            const amount = parseFloat(amountInput.value) || 0;
            const penalty = parseFloat(penaltyInput.value) || 0;
            const discount = parseFloat(discountInput.value) || 0;
            const net = amount + penalty - discount;

            document.getElementById('amountDisplay').textContent = `GHS ${amount.toFixed(2)}`;
            document.getElementById('penaltyDisplay').textContent = `GHS ${penalty.toFixed(2)}`;
            document.getElementById('discountDisplay').textContent = `GHS ${discount.toFixed(2)}`;
            document.getElementById('netDisplay').textContent = `GHS ${net.toFixed(2)}`;
        }

        [amountInput, penaltyInput, discountInput].forEach(input => {
            input.addEventListener('input', updateTotals);
        });

        // Form validation
        document.querySelector('form').addEventListener('submit', function(e) {
            const amount = parseFloat(amountInput.value) || 0;
            if (amount <= 0) {
                e.preventDefault();
                alert('Please enter a valid payment amount.');
                return false;
            }
            return true;
        });
    });
</script>
@endpush
@endsection