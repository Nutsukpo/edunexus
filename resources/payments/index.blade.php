@extends('layouts.master')

@section('title', 'Fee Payments Management')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            {{-- Summary Cards --}}
            <div class="row mb-4">
                <div class="col-md-3">
                    <div class="card bg-primary text-white shadow-sm">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="card-title mb-0">Total Due</h6>
                                    <h3 class="mt-2">GHS {{ number_format($totalDue ?? 0, 2) }}</h3>
                                </div>
                                <i class="fas fa-file-invoice fa-3x opacity-50"></i>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-success text-white shadow-sm">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="card-title mb-0">Total Paid</h6>
                                    <h3 class="mt-2">GHS {{ number_format($totalPaid ?? 0, 2) }}</h3>
                                </div>
                                <i class="fas fa-check-circle fa-3x opacity-50"></i>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-warning text-white shadow-sm">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="card-title mb-0">Pending</h6>
                                    <h3 class="mt-2">{{ $pendingCount ?? 0 }}</h3>
                                </div>
                                <i class="fas fa-clock fa-3x opacity-50"></i>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-danger text-white shadow-sm">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="card-title mb-0">Overdue</h6>
                                    <h3 class="mt-2">{{ $overdueCount ?? 0 }}</h3>
                                </div>
                                <i class="fas fa-exclamation-triangle fa-3x opacity-50"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Main Card --}}
            <div class="card shadow-sm">
                <div class="card-header bg-white d-flex justify-content-between align-items-center flex-wrap gap-2">
                    <h5 class="mb-0 fw-bold text-dark">
                        <i class="fas fa-credit-card text-primary me-1"></i> Fee Payments
                    </h5>
                    <div>
                        <button type="button" class="btn btn-success btn-sm" data-bs-toggle="modal" data-bs-target="#generatePaymentModal">
                            <i class="fas fa-plus-circle me-1"></i> Generate Payments
                        </button>
                        <button type="button" class="btn btn-warning btn-sm" onclick="applyLateFees()">
                            <i class="fas fa-clock me-1"></i> Apply Late Fees
                        </button>
                        <button type="button" class="btn btn-info btn-sm" onclick="sendReminders()">
                            <i class="fas fa-bell me-1"></i> Send Reminders
                        </button>
                        <a href="{{ route('payments.export-report') }}" class="btn btn-secondary btn-sm">
                            <i class="fas fa-file-export me-1"></i> Export
                        </a>
                    </div>
                </div>

                <div class="card-body">
                    {{-- Filters --}}
                    <form method="GET" action="{{ route('payments.index') }}" class="row g-3 mb-4">
                        <div class="col-md-3">
                            <label class="form-label fw-semibold">Student</label>
                            <select name="student_id" class="form-select">
                                <option value="">All Students</option>
                                @foreach($students ?? [] as $student)
                                    <option value="{{ $student->id }}" 
                                        {{ request('student_id') == $student->id ? 'selected' : '' }}>
                                        {{ $student->name }} ({{ $student->admission_number ?? '' }})
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-semibold">Academic Year</label>
                            <select name="academic_year_id" class="form-select">
                                <option value="">All Years</option>
                                @foreach($academicYears ?? [] as $year)
                                    <option value="{{ $year->id }}" 
                                        {{ request('academic_year_id') == $year->id ? 'selected' : '' }}>
                                        {{ $year->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-semibold">Status</label>
                            <select name="payment_status" class="form-select">
                                <option value="">All Status</option>
                                <option value="pending" {{ request('payment_status') == 'pending' ? 'selected' : '' }}>Pending</option>
                                <option value="partial" {{ request('payment_status') == 'partial' ? 'selected' : '' }}>Partial</option>
                                <option value="paid" {{ request('payment_status') == 'paid' ? 'selected' : '' }}>Paid</option>
                                <option value="overdue" {{ request('payment_status') == 'overdue' ? 'selected' : '' }}>Overdue</option>
                                <option value="cancelled" {{ request('payment_status') == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                                <option value="refunded" {{ request('payment_status') == 'refunded' ? 'selected' : '' }}>Refunded</option>
                            </select>
                        </div>
                        <div class="col-md-3 d-flex align-items-end">
                            <button type="submit" class="btn btn-primary w-100">
                                <i class="fas fa-search me-1"></i> Filter
                            </button>
                        </div>
                    </form>

                    {{-- Results Table --}}
                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>
                                        <input type="checkbox" id="selectAll" class="form-check-input">
                                    </th>
                                    <th>Invoice #</th>
                                    <th>Student</th>
                                    <th>Class</th>
                                    <th>Term</th>
                                    <th>Amount Due</th>
                                    <th>Amount Paid</th>
                                    <th>Balance</th>
                                    <th>Due Date</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($payments as $payment)
                                    <tr>
                                        <td>
                                            <input type="checkbox" class="form-check-input payment-checkbox" 
                                                   value="{{ $payment->id }}">
                                        </td>
                                        <td>
                                            <span class="fw-bold">{{ $payment->invoice_number }}</span>
                                        </td>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                @if($payment->student && $payment->student->user)
                                                    <img src="{{ $payment->student->user->avatar ?? asset('images/default-avatar.png') }}" 
                                                         alt="Avatar" class="rounded-circle me-2" width="35" height="35">
                                                @endif
                                                <div>
                                                    <div class="fw-bold">{{ $payment->student->name ?? 'N/A' }}</div>
                                                    <small class="text-muted">{{ $payment->student->admission_number ?? '' }}</small>
                                                </div>
                                            </div>
                                        </td>
                                        <td>{{ $payment->student->studentClass->name ?? 'N/A' }}</td>
                                        <td>{{ $payment->term->name ?? 'N/A' }}</td>
                                        <td>GHS {{ number_format($payment->amount_due, 2) }}</td>
                                        <td>GHS {{ number_format($payment->amount_paid, 2) }}</td>
                                        <td>
                                            @if($payment->balance > 0)
                                                <span class="text-danger fw-bold">GHS {{ number_format($payment->balance, 2) }}</span>
                                            @else
                                                <span class="text-success fw-bold">GHS 0.00</span>
                                            @endif
                                        </td>
                                        <td>
                                            {{ $payment->due_date->format('d-m-Y') }}
                                            @if($payment->isOverdue())
                                                <span class="badge bg-danger ms-1">Overdue</span>
                                            @endif
                                        </td>
                                        <td>
                                            <span class="badge bg-{{ $payment->status_badge }}">
                                                {{ ucfirst($payment->payment_status) }}
                                            </span>
                                            <div class="progress mt-1" style="height: 4px; width: 80px;">
                                                <div class="progress-bar bg-{{ $payment->payment_progress == 100 ? 'success' : 'warning' }}" 
                                                     style="width: {{ $payment->payment_progress }}%"></div>
                                            </div>
                                            <small>{{ $payment->payment_progress }}%</small>
                                        </td>
                                        <td>
                                            <div class="btn-group btn-group-sm">
                                                <a href="{{ route('payments.show', $payment->id) }}" 
                                                   class="btn btn-info text-white" title="View">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                @if($payment->payment_status != 'paid')
                                                    <button type="button" class="btn btn-success pay-now" 
                                                            data-payment-id="{{ $payment->id }}"
                                                            data-student="{{ $payment->student->name ?? 'Student' }}"
                                                            data-balance="{{ $payment->balance }}"
                                                            title="Pay Now">
                                                        <i class="fas fa-money-bill-wave"></i>
                                                    </button>
                                                @endif
                                                <button type="button" class="btn btn-primary" 
                                                        onclick="sendIndividualReminder({{ $payment->id }})" title="Send Reminder">
                                                    <i class="fas fa-bell"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="11" class="text-center text-muted py-4">
                                            <i class="fas fa-credit-card fa-2x d-block mb-2"></i>
                                            No payments found
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
                            of {{ $payments->total() }} results
                        </div>
                        <div>
                            {{ $payments->appends(request()->query())->links() }}
                        </div>
                    </div>

                    {{-- Bulk Actions --}}
                    <div class="mt-3 p-3 bg-light rounded">
                        <div class="row align-items-center">
                            <div class="col-md-3">
                                <strong>Bulk Actions:</strong>
                            </div>
                            <div class="col-md-9">
                                <div class="d-flex gap-2 flex-wrap">
                                    <button type="button" class="btn btn-success btn-sm" onclick="bulkPayment()">
                                        <i class="fas fa-money-bill-wave me-1"></i> Process Payment
                                    </button>
                                    <button type="button" class="btn btn-info btn-sm" onclick="bulkReminder()">
                                        <i class="fas fa-bell me-1"></i> Send Reminders
                                    </button>
                                    <button type="button" class="btn btn-warning btn-sm" onclick="bulkExport()">
                                        <i class="fas fa-file-export me-1"></i> Export Selected
                                    </button>
                                    <button type="button" class="btn btn-danger btn-sm" onclick="bulkDelete()">
                                        <i class="fas fa-trash me-1"></i> Delete Selected
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Generate Payment Modal --}}
<div class="modal fade" id="generatePaymentModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Generate Student Payments</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('payments.generate-from-bill-sheet') }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Bill Sheet <span class="text-danger">*</span></label>
                        <select name="bill_sheet_id" class="form-select" required>
                            <option value="">Select Bill Sheet</option>
                            @foreach($billSheets ?? [] as $billSheet)
                                <option value="{{ $billSheet->id }}">
                                    {{ $billSheet->name }} - {{ $billSheet->studentClass->name ?? '' }}
                                    (GHS {{ number_format($billSheet->net_amount, 2) }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle me-1"></i>
                        This will generate fee payment records for all students in the selected bill sheet's class.
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-sync me-1"></i> Generate
                    </button>
                </div>
            </form>
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
                        <input type="text" id="paymentStudent" class="form-control" readonly>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Balance Due</label>
                        <input type="text" id="paymentBalance" class="form-control" readonly>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Amount to Pay <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text">GHS</span>
                            <input type="number" name="amount" id="paymentAmount" 
                                   class="form-control" step="0.01" min="0.01" required>
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
                    <input type="hidden" name="payment_id" id="paymentId">
                    <div class="alert alert-warning">
                        <i class="fas fa-info-circle me-1"></i>
                        <span id="paymentInfo">Please enter the amount to pay.</span>
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
    // Select All checkbox
    document.getElementById('selectAll').addEventListener('change', function() {
        document.querySelectorAll('.payment-checkbox').forEach(cb => {
            cb.checked = this.checked;
        });
    });

    // Pay Now button
    document.querySelectorAll('.pay-now').forEach(button => {
        button.addEventListener('click', function() {
            const paymentId = this.dataset.paymentId;
            const student = this.dataset.student;
            const balance = this.dataset.balance;
            
            document.getElementById('paymentId').value = paymentId;
            document.getElementById('paymentStudent').value = student;
            document.getElementById('paymentBalance').value = `GHS ${parseFloat(balance).toFixed(2)}`;
            document.getElementById('paymentAmount').value = balance;
            document.getElementById('paymentAmount').max = balance;
            document.getElementById('paymentInfo').textContent = 
                `Maximum payment amount is GHS ${parseFloat(balance).toFixed(2)}`;
            
            const modal = new bootstrap.Modal(document.getElementById('paymentModal'));
            modal.show();
        });
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
        
        fetch(`/payments/${paymentId}/process`, {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                toastr.success(data.message);
                bootstrap.Modal.getInstance(document.getElementById('paymentModal')).hide();
                location.reload();
            } else {
                toastr.error(data.message);
            }
        })
        .catch(error => {
            toastr.error('An error occurred while processing payment');
        });
    });

    // Apply Late Fees
    function applyLateFees() {
        if (!confirm('Are you sure you want to apply late fees to all overdue payments?')) {
            return;
        }
        
        fetch('{{ route("payments.apply-late-fees") }}', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                toastr.success(data.message);
                location.reload();
            } else {
                toastr.error(data.message);
            }
        })
        .catch(error => {
            toastr.error('An error occurred while applying late fees');
        });
    }

    // Send Reminders
    function sendReminders() {
        if (!confirm('Send payment reminders to all students with pending payments?')) {
            return;
        }
        
        const days = prompt('How many days before due date? (Default: 7)', '7');
        if (days === null) return;
        
        const formData = new FormData();
        formData.append('days_before_due', days);
        formData.append('_token', '{{ csrf_token() }}');
        
        fetch('{{ route("payments.send-reminders") }}', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                toastr.success(data.message);
            } else {
                toastr.error(data.message);
            }
        })
        .catch(error => {
            toastr.error('An error occurred while sending reminders');
        });
    }

    // Send Individual Reminder
    function sendIndividualReminder(paymentId) {
        if (!confirm('Send payment reminder to this student?')) {
            return;
        }
        
        const formData = new FormData();
        formData.append('_token', '{{ csrf_token() }}');
        formData.append('payment_id', paymentId);
        
        fetch('{{ route("payments.send-reminders") }}', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                toastr.success('Reminder sent successfully!');
            } else {
                toastr.error(data.message);
            }
        })
        .catch(error => {
            toastr.error('An error occurred while sending reminder');
        });
    }

    // Bulk Payment
    function bulkPayment() {
        const selected = document.querySelectorAll('.payment-checkbox:checked');
        if (selected.length === 0) {
            toastr.warning('Please select at least one payment');
            return;
        }
        
        // Implement bulk payment logic
        toastr.info('Bulk payment feature coming soon');
    }

    // Bulk Reminder
    function bulkReminder() {
        const selected = document.querySelectorAll('.payment-checkbox:checked');
        if (selected.length === 0) {
            toastr.warning('Please select at least one payment');
            return;
        }
        
        // Implement bulk reminder logic
        toastr.info('Bulk reminder feature coming soon');
    }

    // Bulk Export
    function bulkExport() {
        const selected = document.querySelectorAll('.payment-checkbox:checked');
        if (selected.length === 0) {
            toastr.warning('Please select at least one payment');
            return;
        }
        
        // Implement bulk export logic
        toastr.info('Bulk export feature coming soon');
    }

    // Bulk Delete
    function bulkDelete() {
        const selected = document.querySelectorAll('.payment-checkbox:checked');
        if (selected.length === 0) {
            toastr.warning('Please select at least one payment');
            return;
        }
        
        if (!confirm(`Are you sure you want to delete ${selected.length} payment records?`)) {
            return;
        }
        
        // Implement bulk delete logic
        toastr.info('Bulk delete feature coming soon');
    }

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