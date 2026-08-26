@extends('layouts.master')

@section('title', 'Payment Details - ' . $payment->invoice_number)

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <div class="card shadow-sm">
                <div class="card-header bg-white d-flex justify-content-between align-items-center flex-wrap gap-2">
                    <h5 class="mb-0 fw-bold text-dark">
                        <i class="fas fa-credit-card text-primary me-1"></i> Payment Details
                    </h5>
                    <div>
                        <a href="{{ route('payments.index') }}" class="btn btn-secondary btn-sm">
                            <i class="fas fa-arrow-left me-1"></i> Back
                        </a>
                        @if($payment->payment_status != 'paid')
                            <button type="button" class="btn btn-success btn-sm pay-now" 
                                    data-payment-id="{{ $payment->id }}"
                                    data-balance="{{ $payment->balance }}">
                                <i class="fas fa-money-bill-wave me-1"></i> Process Payment
                            </button>
                        @endif
                        <button type="button" class="btn btn-primary btn-sm" onclick="window.print()">
                            <i class="fas fa-print me-1"></i> Print
                        </button>
                    </div>
                </div>

                <div class="card-body">
                    {{-- Status Banner --}}
                    <div class="alert alert-{{ $payment->payment_status == 'paid' ? 'success' : 
                        ($payment->payment_status == 'overdue' ? 'danger' : 'info') }} mb-4">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h5 class="mb-0">
                                    <i class="fas fa-{{ $payment->payment_status == 'paid' ? 'check-circle' : 
                                        ($payment->payment_status == 'overdue' ? 'exclamation-triangle' : 'clock') }} me-2"></i>
                                    Payment Status: <strong>{{ ucfirst($payment->payment_status) }}</strong>
                                </h5>
                                @if($payment->payment_status == 'paid')
                                    <small>Paid on: {{ $payment->paid_date->format('d-m-Y H:i') }}</small>
                                @elseif($payment->payment_status == 'overdue')
                                    <small>Overdue by {{ now()->diffInDays($payment->due_date) }} days</small>
                                @else
                                    <small>Due: {{ $payment->due_date->format('d-m-Y') }}</small>
                                @endif
                            </div>
                            <div>
                                <span class="badge bg-{{ $payment->status_badge }} fs-6 p-2">
                                    {{ ucfirst($payment->payment_status) }}
                                </span>
                            </div>
                        </div>
                    </div>

                    {{-- Student Information --}}
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <div class="card border-0 shadow-sm">
                                <div class="card-header bg-light">
                                    <h6 class="mb-0 fw-bold">
                                        <i class="fas fa-user text-primary me-1"></i> Student Information
                                    </h6>
                                </div>
                                <div class="card-body">
                                    <div class="d-flex align-items-center mb-3">
                                        @if($payment->student && $payment->student->user)
                                            <img src="{{ $payment->student->user->avatar ?? asset('images/default-avatar.png') }}" 
                                                 alt="Avatar" class="rounded-circle me-3" width="60" height="60">
                                        @endif
                                        <div>
                                            <h5 class="mb-0">{{ $payment->student->name ?? 'N/A' }}</h5>
                                            <small class="text-muted">Admission: {{ $payment->student->admission_number ?? 'N/A' }}</small>
                                        </div>
                                    </div>
                                    <table class="table table-sm table-borderless">
                                        <tr>
                                            <td width="30%"><strong>Class</strong></td>
                                            <td>{{ $payment->student->studentClass->name ?? 'N/A' }}</td>
                                        </tr>
                                        <tr>
                                            <td><strong>Parent/Guardian</strong></td>
                                            <td>{{ $payment->student->parent_name ?? 'N/A' }}</td>
                                        </tr>
                                        <tr>
                                            <td><strong>Contact</strong></td>
                                            <td>{{ $payment->student->phone ?? 'N/A' }}</td>
                                        </tr>
                                        <tr>
                                            <td><strong>Email</strong></td>
                                            <td>{{ $payment->student->user->email ?? 'N/A' }}</td>
                                        </tr>
                                    </table>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card border-0 shadow-sm">
                                <div class="card-header bg-light">
                                    <h6 class="mb-0 fw-bold">
                                        <i class="fas fa-file-invoice text-primary me-1"></i> Invoice Information
                                    </h6>
                                </div>
                                <div class="card-body">
                                    <table class="table table-sm table-borderless">
                                        <tr>
                                            <td width="40%"><strong>Invoice Number</strong></td>
                                            <td class="fw-bold">{{ $payment->invoice_number }}</td>
                                        </tr>
                                        <tr>
                                            <td><strong>Academic Year</strong></td>
                                            <td>{{ $payment->academicYear->name ?? 'N/A' }}</td>
                                        </tr>
                                        <tr>
                                            <td><strong>Term</strong></td>
                                            <td>{{ $payment->term->name ?? 'N/A' }}</td>
                                        </tr>
                                        <tr>
                                            <td><strong>Bill Sheet</strong></td>
                                            <td>
                                                <a href="{{ route('bill-sheets.show', $payment->billSheet->id ?? 0) }}">
                                                    {{ $payment->billSheet->name ?? 'N/A' }}
                                                </a>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td><strong>Due Date</strong></td>
                                            <td class="fw-bold {{ $payment->isOverdue() ? 'text-danger' : '' }}">
                                                {{ $payment->due_date->format('d-m-Y') }}
                                                @if($payment->isOverdue())
                                                    <span class="badge bg-danger ms-1">Overdue</span>
                                                @endif
                                            </td>
                                        </tr>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Payment Summary --}}
                    <div class="row mb-4">
                        <div class="col-md-12">
                            <div class="card border-0 shadow-sm">
                                <div class="card-header bg-light">
                                    <h6 class="mb-0 fw-bold">
                                        <i class="fas fa-calculator text-primary me-1"></i> Payment Summary
                                    </h6>
                                </div>
                                <div class="card-body">
                                    <div class="row text-center">
                                        <div class="col-md-3">
                                            <div class="border-end py-3">
                                                <h6 class="text-muted">Total Due</h6>
                                                <h3 class="text-primary">GHS {{ number_format($payment->amount_due, 2) }}</h3>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="border-end py-3">
                                                <h6 class="text-muted">Amount Paid</h6>
                                                <h3 class="text-success">GHS {{ number_format($payment->amount_paid, 2) }}</h3>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="border-end py-3">
                                                <h6 class="text-muted">Balance</h6>
                                                <h3 class="text-{{ $payment->balance > 0 ? 'danger' : 'success' }}">
                                                    GHS {{ number_format($payment->balance, 2) }}
                                                </h3>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="py-3">
                                                <h6 class="text-muted">Payment Progress</h6>
                                                <h3>{{ $payment->payment_progress }}%</h3>
                                                <div class="progress" style="height: 8px;">
                                                    <div class="progress-bar bg-{{ $payment->payment_progress == 100 ? 'success' : 'warning' }}" 
                                                         style="width: {{ $payment->payment_progress }}%"></div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    @if($payment->late_fee > 0)
                                        <div class="alert alert-warning mt-3">
                                            <i class="fas fa-clock me-1"></i>
                                            <strong>Late Fee:</strong> GHS {{ number_format($payment->late_fee, 2) }} 
                                            has been applied to this payment.
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Payment Items --}}
                    <div class="card border-0 shadow-sm mb-4">
                        <div class="card-header bg-light">
                            <h6 class="mb-0 fw-bold">
                                <i class="fas fa-list text-primary me-1"></i> Bill Items
                            </h6>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead class="table-light">
                                        <tr>
                                            <th>#</th>
                                            <th>Item Name</th>
                                            <th>Category</th>
                                            <th class="text-end">Amount</th>
                                            <th class="text-center">Qty</th>
                                            <th class="text-end">Total</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($payment->billSheet->items ?? [] as $index => $item)
                                            <tr>
                                                <td>{{ $index + 1 }}</td>
                                                <td>{{ $item->name }}</td>
                                                <td>{{ $item->feeCategory->name ?? 'N/A' }}</td>
                                                <td class="text-end">GHS {{ number_format($item->amount, 2) }}</td>
                                                <td class="text-center">{{ $item->quantity }}</td>
                                                <td class="text-end">GHS {{ number_format($item->total_amount, 2) }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                    <tfoot>
                                        <tr class="table-light">
                                            <td colspan="5" class="text-end fw-bold">Sub Total:</td>
                                            <td class="text-end fw-bold">GHS {{ number_format($payment->billSheet->total_amount ?? 0, 2) }}</td>
                                        </tr>
                                        <tr class="table-light">
                                            <td colspan="5" class="text-end fw-bold">Discount:</td>
                                            <td class="text-end text-danger">- GHS {{ number_format($payment->billSheet->discount_amount ?? 0, 2) }}</td>
                                        </tr>
                                        <tr class="table-light">
                                            <td colspan="5" class="text-end fw-bold">Tax:</td>
                                            <td class="text-end text-warning">+ GHS {{ number_format($payment->billSheet->tax_amount ?? 0, 2) }}</td>
                                        </tr>
                                        <tr class="table-success">
                                            <td colspan="5" class="text-end fw-bold fs-5">Grand Total:</td>
                                            <td class="text-end fw-bold fs-5">GHS {{ number_format($payment->billSheet->net_amount ?? 0, 2) }}</td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        </div>
                    </div>

                    {{-- Transaction History --}}
                    <div class="card border-0 shadow-sm">
                        <div class="card-header bg-light d-flex justify-content-between align-items-center">
                            <h6 class="mb-0 fw-bold">
                                <i class="fas fa-history text-primary me-1"></i> Transaction History
                            </h6>
                            <span class="badge bg-primary">{{ $payment->transactions->count() }} transactions</span>
                        </div>
                        <div class="card-body">
                            @if($payment->transactions->count() > 0)
                                <div class="table-responsive">
                                    <table class="table table-hover">
                                        <thead class="table-light">
                                            <tr>
                                                <th>Reference</th>
                                                <th>Amount</th>
                                                <th>Method</th>
                                                <th>Status</th>
                                                <th>Date</th>
                                                <th>Processed By</th>
                                                <th>Notes</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($payment->transactions as $transaction)
                                                <tr>
                                                    <td>
                                                        <span class="fw-bold">{{ $transaction->transaction_reference }}</span>
                                                    </td>
                                                    <td>GHS {{ number_format($transaction->amount, 2) }}</td>
                                                    <td>
                                                        <span class="badge bg-{{ $transaction->payment_method == 'cash' ? 'success' : 
                                                            ($transaction->payment_method == 'bank_transfer' ? 'info' : 
                                                            ($transaction->payment_method == 'mobile_money' ? 'warning' : 'secondary')) }}">
                                                            {{ ucfirst(str_replace('_', ' ', $transaction->payment_method)) }}
                                                        </span>
                                                    </td>
                                                    <td>
                                                        <span class="badge bg-{{ $transaction->transaction_status == 'completed' ? 'success' : 
                                                            ($transaction->transaction_status == 'pending' ? 'warning' : 'danger') }}">
                                                            {{ ucfirst($transaction->transaction_status) }}
                                                        </span>
                                                    </td>
                                                    <td>{{ $transaction->transaction_date->format('d-m-Y H:i') }}</td>
                                                    <td>{{ $transaction->user->name ?? 'N/A' }}</td>
                                                    <td>{{ $transaction->notes ?? '-' }}</td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                        <tfoot class="table-light">
                                            <tr>
                                                <td colspan="7" class="text-end">
                                                    <strong>Total Paid:</strong> GHS {{ number_format($payment->transactions->sum('amount'), 2) }}
                                                </td>
                                            </tr>
                                        </tfoot>
                                    </table>
                                </div>
                            @else
                                <div class="text-center text-muted py-4">
                                    <i class="fas fa-receipt fa-2x d-block mb-2"></i>
                                    No transactions recorded yet
                                </div>
                            @endif
                        </div>
                    </div>

                    {{-- Description --}}
                    @if($payment->billSheet->description ?? false)
                        <div class="card border-0 shadow-sm mt-4">
                            <div class="card-header bg-light">
                                <h6 class="mb-0 fw-bold">
                                    <i class="fas fa-info-circle text-primary me-1"></i> Description
                                </h6>
                            </div>
                            <div class="card-body">
                                <p>{{ $payment->billSheet->description }}</p>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Payment Modal --}}
<div class="modal fade" id="paymentModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Process Payment</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="paymentForm">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Student</label>
                        <input type="text" id="paymentStudent" class="form-control" value="{{ $payment->student->name ?? 'N/A' }}" readonly>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Balance Due</label>
                        <input type="text" id="paymentBalance" class="form-control" 
                               value="GHS {{ number_format($payment->balance, 2) }}" readonly>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Amount to Pay <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text">GHS</span>
                            <input type="number" name="amount" id="paymentAmount" 
                                   class="form-control" step="0.01" min="0.01" 
                                   max="{{ $payment->balance }}" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Payment Method <span class="text-danger">*</span></label>
                        <select name="payment_method" id="paymentMethod" class="form-select" required>
                            <option value="">Select Method</option>
                            <option value="cash">Cash</option>
                            <option value="bank_transfer">Bank Transfer</option>
                            <option value="mobile_money">Mobile Money</option>
                            <option value="credit_card">Credit Card</option>
                            <option value="debit_card">Debit Card</option>
                            <option value="cheque">Cheque</option>
                            <option value="online">Online</option>
                        </select>
                    </div>
                    <input type="hidden" name="payment_id" id="paymentId" value="{{ $payment->id }}">
                    <div class="alert alert-warning">
                        <i class="fas fa-info-circle me-1"></i>
                        <span id="paymentInfo">Maximum payment: GHS {{ number_format($payment->balance, 2) }}</span>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success">
                        <i class="fas fa-check me-1"></i> Process Payment
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
    // Pay Now button
    document.querySelector('.pay-now').addEventListener('click', function() {
        const modal = new bootstrap.Modal(document.getElementById('paymentModal'));
        modal.show();
    });

    // Payment Form Submit
    document.getElementById('paymentForm').addEventListener('submit', function(e) {
        e.preventDefault();
        
        const paymentId = document.getElementById('paymentId').value;
        const amount = document.getElementById('paymentAmount').value;
        const method = document.getElementById('paymentMethod').value;
        
        if (!amount || amount <= 0) {
            toastr.error('Please enter a valid amount');
            return;
        }
        
        if (!method) {
            toastr.error('Please select a payment method');
            return;
        }
        
        const formData = new FormData();
        formData.append('amount', amount);
        formData.append('payment_method', method);
        formData.append('_token', '{{ csrf_token() }}');
        
        // Disable submit button
        const submitBtn = this.querySelector('button[type="submit"]');
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i> Processing...';
        
        fetch(`/payments/${paymentId}/process`, {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                toastr.success(data.message);
                bootstrap.Modal.getInstance(document.getElementById('paymentModal')).hide();
                setTimeout(() => location.reload(), 1500);
            } else {
                toastr.error(data.message);
                submitBtn.disabled = false;
                submitBtn.innerHTML = '<i class="fas fa-check me-1"></i> Process Payment';
            }
        })
        .catch(error => {
            toastr.error('An error occurred while processing payment');
            submitBtn.disabled = false;
            submitBtn.innerHTML = '<i class="fas fa-check me-1"></i> Process Payment';
        });
    });

    // Toastr configuration
    toastr.options = {
        "closeButton": true,
        "progressBar": true,
        "positionClass": "toast-top-right",
        "timeOut": "5000",
    };
</script>
@endpush
@endsection