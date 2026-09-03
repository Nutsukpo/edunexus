@extends('students.layouts.app')

@section('title', 'Make Fee Payment')

@section('content')

<style>
    .payment-page {
        max-width: 1100px;
        margin: 0 auto;
    }

    .payment-header {
        margin-bottom: 25px;
    }

    .payment-header h2 {
        font-weight: 700;
        color: #1f2937;
        margin-bottom: 6px;
    }

    .payment-header p {
        color: #6b7280;
        margin: 0;
    }

    .payment-grid {
        display: grid;
        grid-template-columns: 1fr 380px;
        gap: 24px;
    }

    .payment-card,
    .summary-card {
        background: #fff;
        border-radius: 16px;
        border: 1px solid #e5e7eb;
        box-shadow: 0 5px 20px rgba(0, 0, 0, .05);
    }

    .card-header-custom {
        padding: 20px 24px;
        border-bottom: 1px solid #eef0f2;
    }

    .card-header-custom h5 {
        margin: 0;
        font-weight: 700;
        color: #1f2937;
    }

    .card-body-custom {
        padding: 24px;
    }

    .summary-card {
        overflow: hidden;
        height: fit-content;
    }

    .summary-top {
        padding: 24px;
        background: linear-gradient(135deg, #0f8b57, #087443);
        color: #fff;
    }

    .summary-top .label {
        font-size: 14px;
        opacity: .85;
    }

    .summary-top .balance {
        font-size: 32px;
        font-weight: 800;
        margin-top: 5px;
    }

    .summary-body {
        padding: 22px;
    }

    .summary-row {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 12px 0;
        border-bottom: 1px solid #f0f1f3;
    }

    .summary-row:last-child {
        border-bottom: 0;
    }

    .summary-row span:first-child {
        color: #6b7280;
    }

    .summary-row strong {
        color: #1f2937;
    }

    .form-label {
        font-weight: 600;
        color: #374151;
        margin-bottom: 8px;
    }

    .form-control,
    .form-select {
        min-height: 48px;
        border-radius: 10px;
        border: 1px solid #d1d5db;
        padding: 10px 14px;
    }

    .form-control:focus,
    .form-select:focus {
        border-color: #198754;
        box-shadow: 0 0 0 .2rem rgba(25, 135, 84, .12);
    }

    .amount-wrapper {
        position: relative;
    }

    .amount-wrapper .currency {
        position: absolute;
        left: 14px;
        top: 50%;
        transform: translateY(-50%);
        font-weight: 700;
        color: #6b7280;
        z-index: 2;
    }

    .amount-wrapper input {
        padding-left: 58px;
        font-size: 20px;
        font-weight: 700;
    }

    .quick-amounts {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
        margin-top: 10px;
    }

    .quick-amount {
        border: 1px solid #d1d5db;
        background: #fff;
        color: #374151;
        border-radius: 8px;
        padding: 7px 12px;
        cursor: pointer;
        font-size: 13px;
        transition: .2s;
    }

    .quick-amount:hover {
        border-color: #198754;
        color: #198754;
        background: #f0fdf4;
    }

    .network-options {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 10px;
    }

    .network-option {
        position: relative;
    }

    .network-option input {
        position: absolute;
        opacity: 0;
        pointer-events: none;
    }

    .network-option label {
        display: block;
        text-align: center;
        padding: 13px 8px;
        border: 1px solid #d1d5db;
        border-radius: 10px;
        cursor: pointer;
        font-weight: 600;
        color: #374151;
        transition: .2s;
    }

    .network-option input:checked + label {
        border-color: #198754;
        background: #ecfdf5;
        color: #087443;
        box-shadow: 0 0 0 2px rgba(25, 135, 84, .08);
    }

    .payment-info {
        background: #f8fafc;
        border-radius: 12px;
        padding: 16px;
        margin-top: 20px;
    }

    .payment-info h6 {
        font-weight: 700;
        margin-bottom: 10px;
    }

    .payment-info ul {
        margin: 0;
        padding-left: 20px;
        color: #6b7280;
    }

    .payment-info li {
        margin-bottom: 6px;
    }

    .btn-pay {
        width: 100%;
        min-height: 52px;
        border: 0;
        border-radius: 10px;
        background: #198754;
        color: #fff;
        font-size: 16px;
        font-weight: 700;
        margin-top: 22px;
        transition: .2s;
    }

    .btn-pay:hover {
        background: #157347;
    }

    .btn-pay:disabled {
        opacity: .65;
        cursor: not-allowed;
    }

    .back-link {
        display: inline-flex;
        align-items: center;
        gap: 7px;
        color: #6b7280;
        text-decoration: none;
        margin-bottom: 18px;
    }

    .back-link:hover {
        color: #198754;
    }

    .alert {
        border-radius: 10px;
    }

    .student-info {
        background: #f8fafc;
        border-radius: 12px;
        padding: 16px;
        margin-bottom: 22px;
    }

    .student-info strong {
        color: #1f2937;
    }

    .student-info small {
        color: #6b7280;
    }

    @media (max-width: 850px) {
        .payment-grid {
            grid-template-columns: 1fr;
        }

        .summary-card {
            order: -1;
        }
    }

    @media (max-width: 500px) {
        .network-options {
            grid-template-columns: 1fr;
        }
    }
</style>

<div class="payment-page">

    <a href="{{ route('students.fees') }}" class="back-link">
        <i class="fas fa-arrow-left"></i>
        Back to School Fees
    </a>

    <div class="payment-header">
        <h2>Make Fee Payment</h2>
        <p>Pay your outstanding school fees securely using Mobile Money.</p>
    </div>

    {{-- Success message --}}
    @if(session('success'))
        <div class="alert alert-success mb-4">
            <i class="fas fa-check-circle me-2"></i>
            {{ session('success') }}
        </div>
    @endif

    {{-- Error message --}}
    @if(session('error'))
        <div class="alert alert-danger mb-4">
            <i class="fas fa-exclamation-circle me-2"></i>
            {{ session('error') }}
        </div>
    @endif

    {{-- Validation errors --}}
    @if($errors->any())
        <div class="alert alert-danger mb-4">
            <strong>Please correct the following:</strong>
            <ul class="mb-0 mt-2">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="payment-grid">

        {{-- PAYMENT FORM --}}
        <div class="payment-card">

            <div class="card-header-custom">
                <h5>
                    <i class="fas fa-mobile-alt me-2 text-success"></i>
                    Mobile Money Payment
                </h5>
            </div>

            <div class="card-body-custom">

                <div class="student-info">
                    <div class="row">
                        <div class="col-md-6 mb-2 mb-md-0">
                            <small>Student</small><br>
                            <strong>
                                {{ $student->first_name }}
                                {{ $student->middle_name ?? '' }}
                                {{ $student->last_name }}
                            </strong>
                        </div>

                        <div class="col-md-6">
                            <small>Student ID</small><br>
                            <strong>{{ $student->student_id }}</strong>
                        </div>
                    </div>

                    @if($assignment)
                        <div class="row mt-3">
                            <div class="col-md-6">
                                <small>Class</small><br>
                                <strong>
                                    {{ $assignment->studentClass->name ?? 'N/A' }}
                                </strong>
                            </div>

                            <div class="col-md-6">
                                <small>Academic Year</small><br>
                                <strong>
                                    {{ $assignment->academicYear->name ?? 'N/A' }}
                                </strong>
                            </div>
                        </div>
                    @endif
                </div>

                <form
                    method="POST"
                    action="{{ route('students.fees.payment.initiate') }}"
                    id="paymentForm"
                >
                    @csrf

                    {{-- Amount --}}
                    <div class="mb-4">
                        <label for="amount" class="form-label">
                            Payment Amount
                        </label>

                        <div class="amount-wrapper">
                            <span class="currency">GH₵</span>

                            <input
                                type="number"
                                class="form-control @error('amount') is-invalid @enderror"
                                id="amount"
                                name="amount"
                                min="1"
                                max="{{ number_format((float) $balance, 2, '.', '') }}"
                                step="0.01"
                                value="{{ old('amount') }}"
                                placeholder="0.00"
                                required
                            >
                        </div>

                        @error('amount')
                            <div class="invalid-feedback d-block">
                                {{ $message }}
                            </div>
                        @enderror

                        <div class="quick-amounts">
                            @php
                                $currentBalance = (float) $balance;
                                $quickAmounts = [
                                    100,
                                    200,
                                    500,
                                    1000
                                ];
                            @endphp

                            @foreach($quickAmounts as $quickAmount)
                                @if($quickAmount <= $currentBalance)
                                    <button
                                        type="button"
                                        class="quick-amount"
                                        data-amount="{{ $quickAmount }}"
                                    >
                                        GH₵ {{ number_format($quickAmount, 2) }}
                                    </button>
                                @endif
                            @endforeach

                            @if($currentBalance > 0)
                                <button
                                    type="button"
                                    class="quick-amount"
                                    data-amount="{{ number_format($currentBalance, 2, '.', '') }}"
                                >
                                    Pay Full Balance
                                </button>
                            @endif
                        </div>

                        <small class="text-muted d-block mt-2">
                            Maximum payment:
                            <strong>
                                GH₵ {{ number_format($currentBalance, 2) }}
                            </strong>
                        </small>
                    </div>

                    {{-- Mobile Network --}}
                    <div class="mb-4">

                        <label class="form-label">
                            Mobile Network
                        </label>

                        <div class="network-options">

                            <div class="network-option">
                                <input
                                    type="radio"
                                    name="network"
                                    id="network_mtn"
                                    value="mtn"
                                    {{ old('network') === 'mtn' ? 'checked' : '' }}
                                    required
                                >

                                <label for="network_mtn">
                                    MTN
                                </label>
                            </div>

                            <div class="network-option">
                                <input
                                    type="radio"
                                    name="network"
                                    id="network_vodafone"
                                    value="vodafone"
                                    {{ old('network') === 'vodafone' ? 'checked' : '' }}
                                >

                                <label for="network_vodafone">
                                    Vodafone
                                </label>
                            </div>

                            <div class="network-option">
                                <input
                                    type="radio"
                                    name="network"
                                    id="network_airteltigo"
                                    value="airteltigo"
                                    {{ old('network') === 'airteltigo' ? 'checked' : '' }}
                                >

                                <label for="network_airteltigo">
                                    AirtelTigo
                                </label>
                            </div>

                        </div>

                        @error('network')
                            <div class="text-danger small mt-2">
                                {{ $message }}
                            </div>
                        @enderror

                    </div>

                    {{-- Mobile Number --}}
                    <div class="mb-4">

                        <label for="phone" class="form-label">
                            Mobile Money Number
                        </label>

                        <input
                            type="tel"
                            class="form-control @error('phone') is-invalid @enderror"
                            id="phone"
                            name="phone"
                            value="{{ old('phone', $student->father_phone ?? $student->mother_phone ?? '') }}"
                            placeholder="e.g. 0241234567"
                            autocomplete="tel"
                            required
                        >

                        @error('phone')
                            <div class="invalid-feedback">
                                {{ $message }}
                            </div>
                        @enderror

                        <small class="text-muted">
                            Enter the number registered with your selected
                            Mobile Money network.
                        </small>

                    </div>

                    {{-- Payment Description --}}
                    <div class="mb-4">

                        <label for="notes" class="form-label">
                            Payment Note
                            <span class="text-muted fw-normal">(Optional)</span>
                        </label>

                        <textarea
                            class="form-control"
                            id="notes"
                            name="notes"
                            rows="3"
                            maxlength="500"
                            placeholder="e.g. Term 1 school fees"
                        >{{ old('notes') }}</textarea>

                    </div>

                    {{-- Hidden payment method --}}
                    <input
                        type="hidden"
                        name="payment_method"
                        value="mobile_money"
                    >

                    <div class="payment-info">

                        <h6>
                            <i class="fas fa-shield-alt text-success me-2"></i>
                            Secure Payment
                        </h6>

                        <ul>
                            <li>Your payment will be processed securely.</li>
                            <li>You will receive a Mobile Money authorization prompt.</li>
                            <li>Do not share your Mobile Money PIN with anyone.</li>
                            <li>Your fee balance will update after successful confirmation.</li>
                        </ul>

                    </div>

                    <button
                        type="submit"
                        class="btn-pay"
                        id="payButton"
                    >
                        <i class="fas fa-mobile-alt me-2"></i>
                        Continue to Mobile Money
                    </button>

                </form>

            </div>
        </div>


        {{-- PAYMENT SUMMARY --}}
        <div class="summary-card">

            <div class="summary-top">

                <div class="label">
                    Outstanding Balance
                </div>

                <div class="balance">
                    GH₵ {{ number_format((float) $balance, 2) }}
                </div>

            </div>

            <div class="summary-body">

                <div class="summary-row">
                    <span>Total Fees</span>

                    <strong>
                        GH₵ {{ number_format((float) $totalFees, 2) }}
                    </strong>
                </div>

                <div class="summary-row">
                    <span>Amount Paid</span>

                    <strong class="text-success">
                        GH₵ {{ number_format((float) $amountPaid, 2) }}
                    </strong>
                </div>

                <div class="summary-row">
                    <span>Outstanding</span>

                    <strong class="text-danger">
                        GH₵ {{ number_format((float) $balance, 2) }}
                    </strong>
                </div>

                @if($assignment)
                    <div class="summary-row">
                        <span>Class</span>

                        <strong>
                            {{ $assignment->studentClass->name ?? 'N/A' }}
                        </strong>
                    </div>
                @endif

            </div>

        </div>

    </div>

</div>

@endsection


@section('scripts')

<script>
document.addEventListener('DOMContentLoaded', function () {

    const amountInput = document.getElementById('amount');
    const paymentForm = document.getElementById('paymentForm');
    const payButton = document.getElementById('payButton');

    /*
    |--------------------------------------------------------------------------
    | QUICK AMOUNT BUTTONS
    |--------------------------------------------------------------------------
    */

    document.querySelectorAll('.quick-amount').forEach(function (button) {

        button.addEventListener('click', function () {

            const amount = this.dataset.amount;

            amountInput.value = amount;

            amountInput.dispatchEvent(
                new Event('input', { bubbles: true })
            );

            amountInput.focus();
        });

    });


    /*
    |--------------------------------------------------------------------------
    | PREVENT PAYMENT ABOVE BALANCE
    |--------------------------------------------------------------------------
    */

    amountInput.addEventListener('input', function () {

        const max = parseFloat(this.max);
        const value = parseFloat(this.value);

        if (!isNaN(value) && value > max) {
            this.value = max.toFixed(2);
        }

        if (value < 0) {
            this.value = '';
        }

    });


    /*
    |--------------------------------------------------------------------------
    | FORM SUBMISSION
    |--------------------------------------------------------------------------
    */

    paymentForm.addEventListener('submit', function (event) {

        const amount = parseFloat(amountInput.value);

        if (!amount || amount <= 0) {

            event.preventDefault();

            alert('Please enter a valid payment amount.');

            amountInput.focus();

            return;
        }

        const max = parseFloat(amountInput.max);

        if (amount > max) {

            event.preventDefault();

            alert(
                'The payment amount cannot be greater than your outstanding balance.'
            );

            amountInput.focus();

            return;
        }

        const network = document.querySelector(
            'input[name="network"]:checked'
        );

        if (!network) {

            event.preventDefault();

            alert('Please select your Mobile Money network.');

            return;
        }

        const phone = document.getElementById('phone').value.trim();

        if (!phone) {

            event.preventDefault();

            alert('Please enter your Mobile Money number.');

            document.getElementById('phone').focus();

            return;
        }


        /*
        |--------------------------------------------------------------------------
        | Prevent double submission
        |--------------------------------------------------------------------------
        */

        payButton.disabled = true;

        payButton.innerHTML =
            '<span class="spinner-border spinner-border-sm me-2"></span>' +
            'Processing...';

    });

});
</script>

@endsection