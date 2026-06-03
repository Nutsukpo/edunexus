@extends('layouts.master')

@section('title', 'Receive Payment')

@section('content')

<div class="container py-4">

    <div class="row justify-content-center">
        <div class="col-lg-8">

            <div class="card shadow-sm border-0">

                <div class="card-header bg-white py-3">
                    <h5 class="fw-bold mb-0">
                        <i class="fas fa-money-bill-wave text-success me-2"></i>
                        Receive Payment
                    </h5>
                </div>

                <div class="card-body">

                    @if($errors->any())
                        <div class="alert alert-danger">
                            <ul class="mb-0">
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form action="{{ route('payments.store') }}" method="POST">
                        @csrf

                        {{-- Invoice --}}
                        <div class="mb-3">
                            <label class="form-label fw-semibold">
                                Invoice <span class="text-danger">*</span>
                            </label>

                            <select name="student_invoice_id"
                                    id="invoiceSelect"
                                    class="form-select"
                                    required>

                                <option value="">Select Invoice</option>

                                @foreach($invoices as $invoice)
                                    <option value="{{ $invoice->id }}"
                                            data-student-id="{{ $invoice->student_id }}"
                                            data-balance="{{ $invoice->balance }}"
                                            {{ old('student_invoice_id') == $invoice->id ? 'selected' : '' }}>

                                        {{ $invoice->invoice_number }}
                                        -
                                        {{ $invoice->student?->first_name }}
                                        {{ $invoice->student?->last_name }}
                                        (Balance: GH₵ {{ number_format($invoice->balance, 2) }})
                                    </option>
                                @endforeach

                            </select>
                        </div>

                        {{-- Student ID --}}
                        <div class="mb-3">
                            <label class="form-label fw-semibold">
                                Student ID <span class="text-danger">*</span>
                            </label>

                            <input type="text"
                                   name="student_id"
                                   id="student_id"
                                   class="form-control"
                                   value="{{ old('student_id') }}"
                                   readonly
                                   required>
                        </div>

                        {{-- Receipt Number --}}
                        <div class="mb-3">
                            <label class="form-label fw-semibold">
                                Receipt Number <span class="text-danger">*</span>
                            </label>

                            <input type="text"
                                   name="receipt_number"
                                   id="receipt_number"
                                   class="form-control"
                                   value="{{ old('receipt_number') }}"
                                   readonly
                                   required>
                        </div>

                        {{-- Amount --}}
                        <div class="mb-3">
                            <label class="form-label fw-semibold">
                                Amount (GH₵) <span class="text-danger">*</span>
                            </label>

                            <input type="number"
                                   step="0.01"
                                   name="amount"
                                   class="form-control"
                                   value="{{ old('amount') }}"
                                   required>
                        </div>

                        {{-- Payment Date --}}
                        <div class="mb-3">
                            <label class="form-label fw-semibold">
                                Payment Date <span class="text-danger">*</span>
                            </label>

                            <input type="date"
                                   name="payment_date"
                                   value="{{ old('payment_date', date('Y-m-d')) }}"
                                   class="form-control"
                                   required>
                        </div>

                        {{-- Payment Method --}}
                        <div class="mb-3">
                            <label class="form-label fw-semibold">
                                Payment Method <span class="text-danger">*</span>
                            </label>

                            <select name="payment_method"
                                    class="form-select"
                                    required>

                                <option value="Cash" {{ old('payment_method') == 'Cash' ? 'selected' : '' }}>Cash</option>
                                <option value="Mobile Money" {{ old('payment_method') == 'Mobile Money' ? 'selected' : '' }}>Mobile Money</option>
                                <option value="Bank Transfer" {{ old('payment_method') == 'Bank Transfer' ? 'selected' : '' }}>Bank Transfer</option>
                                <option value="Cheque" {{ old('payment_method') == 'Cheque' ? 'selected' : '' }}>Cheque</option>
                                <option value="POS" {{ old('payment_method') == 'POS' ? 'selected' : '' }}>POS</option>

                            </select>
                        </div>

                        {{-- Reference --}}
                        <div class="mb-3">
                            <label class="form-label fw-semibold">
                                Reference Number
                            </label>

                            <input type="text"
                                   name="reference_number"
                                   class="form-control"
                                   value="{{ old('reference_number') }}"
                                   placeholder="Transaction Reference">
                        </div>

                        {{-- Remarks --}}
                        <div class="mb-3">
                            <label class="form-label fw-semibold">
                                Remarks
                            </label>

                            <textarea name="remarks"
                                      rows="3"
                                      class="form-control">{{ old('remarks') }}</textarea>
                        </div>

                        {{-- Received By --}}
                        <div class="mb-3">
                            <label class="form-label fw-semibold">
                                Received By
                            </label>

                            <input type="text"
                                   class="form-control"
                                   value="{{ auth()->user()->name }}"
                                   readonly>

                            <input type="hidden"
                                   name="received_by"
                                   value="{{ auth()->id() }}">
                        </div>

                        <hr>

                        <div class="d-flex justify-content-end">

                            <a href="{{ route('payments.index') }}"
                               class="btn btn-secondary me-2">
                                Cancel
                            </a>

                            <button type="submit"
                                    class="btn btn-success">
                                <i class="fas fa-save me-1"></i>
                                Process Payment
                            </button>

                        </div>

                    </form>

                </div>

            </div>

        </div>
    </div>

</div>

@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const invoiceSelect = document.getElementById('invoiceSelect');
    const studentField = document.getElementById('student_id');
    const receiptField = document.getElementById('receipt_number');

    // Generate receipt number function
    function generateReceiptNumber() {
        const date = new Date();
        const timestamp = date.getFullYear() + 
                        String(date.getMonth() + 1).padStart(2, '0') + 
                        String(date.getDate()).padStart(2, '0') + 
                        String(date.getHours()).padStart(2, '0') + 
                        String(date.getMinutes()).padStart(2, '0') + 
                        String(date.getSeconds()).padStart(2, '0');
        return 'RCPT-' + timestamp + '-' + Math.floor(Math.random() * 10000);
    }

    // Set initial receipt number
    receiptField.value = generateReceiptNumber();

    invoiceSelect.addEventListener('change', function () {
        let selected = this.options[this.selectedIndex];
        
        if (selected.value) {
            studentField.value = selected.getAttribute('data-student-id') ?? '';
            receiptField.value = generateReceiptNumber();
        } else {
            studentField.value = '';
        }
    });
});
</script>
@endpush