{{-- resources/views/student/fees/show.blade.php --}}
@extends('students.layouts.app')

@section('title', 'Fee Details')

@section('content')
<div class="container">
    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h4>{{ $allocation->feeStructure->name }}</h4>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-sm-6">
                            <strong>Academic Year:</strong> {{ $allocation->academicYear->name ?? 'N/A' }}
                        </div>
                        <div class="col-sm-6">
                            <strong>Term:</strong> {{ $allocation->term->name ?? 'N/A' }}
                        </div>
                    </div>
                    <div class="row mt-2">
                        <div class="col-sm-6">
                            <strong>Total Amount:</strong> {{ number_format($allocation->total_amount, 2) }}
                        </div>
                        <div class="col-sm-6">
                            <strong>Paid Amount:</strong> {{ number_format($allocation->paid_amount, 2) }}
                        </div>
                    </div>
                    <div class="row mt-2">
                        <div class="col-sm-6">
                            <strong>Balance:</strong> {{ number_format($allocation->balance, 2) }}
                        </div>
                        <div class="col-sm-6">
                            <strong>Status:</strong>
                            @if($allocation->status == 'paid')
                                <span class="badge badge-success">Paid</span>
                            @elseif($allocation->status == 'partial')
                                <span class="badge badge-warning">Partial</span>
                            @elseif($allocation->status == 'overdue')
                                <span class="badge badge-danger">Overdue</span>
                            @else
                                <span class="badge badge-secondary">Pending</span>
                            @endif
                        </div>
                    </div>
                    
                    @if($allocation->feeStructure->feeItems->isNotEmpty())
                        <div class="mt-4">
                            <h5>Fee Breakdown</h5>
                            <table class="table table-sm">
                                <thead>
                                    <tr>
                                        <th>Item</th>
                                        <th>Amount</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($allocation->feeStructure->feeItems as $item)
                                    <tr>
                                        <td>{{ $item->name }}</td>
                                        <td>{{ number_format($item->amount, 2) }}</td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                    
                    @if($allocation->payments->isNotEmpty())
                        <div class="mt-4">
                            <h5>Payment History</h5>
                            <table class="table table-sm">
                                <thead>
                                    <tr>
                                        <th>Invoice</th>
                                        <th>Amount</th>
                                        <th>Method</th>
                                        <th>Date</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($allocation->payments as $payment)
                                    <tr>
                                        <td>{{ $payment->invoice_number }}</td>
                                        <td>{{ number_format($payment->paid_amount, 2) }}</td>
                                        <td>{{ ucfirst(str_replace('_', ' ', $payment->payment_method)) }}</td>
                                        <td>{{ $payment->payment_date->format('Y-m-d') }}</td>
                                        <td>
                                            <a href="{{ route('student.fees.receipt.download', $payment->id) }}" class="btn btn-sm btn-info">
                                                <i class="fas fa-download"></i> Receipt
                                            </a>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            </div>
        </div>
        
        <div class="col-md-4">
            @if($allocation->balance > 0)
                <div class="card">
                    <div class="card-header">
                        <h4>Make Payment</h4>
                    </div>
                    <div class="card-body">
                        <div class="alert alert-info">
                            <strong>Balance Due:</strong> {{ number_format($allocation->balance, 2) }}
                        </div>
                        
                        <form action="{{ route('student.fees.pay', $allocation->id) }}" method="POST">
                            @csrf
                            
                            <div class="form-group">
                                <label>Payment Amount</label>
                                <div class="input-group">
                                    <input type="number" 
                                           name="amount" 
                                           class="form-control @error('amount') is-invalid @enderror" 
                                           step="0.01" 
                                           min="1" 
                                           max="{{ $allocation->balance }}"
                                           placeholder="Enter amount"
                                           required>
                                    <div class="input-group-append">
                                        <span class="input-group-text">₦</span>
                                    </div>
                                </div>
                                @error('amount')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>
                            
                            <div class="form-group">
                                <div class="custom-control custom-checkbox">
                                    <input type="checkbox" 
                                           class="custom-control-input" 
                                           id="payFull" 
                                           name="pay_full" 
                                           value="1"
                                           onchange="toggleFullPayment()">
                                    <label class="custom-control-label" for="payFull">
                                        Pay Full Balance ({{ number_format($allocation->balance, 2) }})
                                    </label>
                                </div>
                            </div>
                            
                            <div class="form-group">
                                <label>Payment Method</label>
                                <select name="payment_method" class="form-control @error('payment_method') is-invalid @enderror" required>
                                    <option value="">Select Method</option>
                                    <option value="cash">Cash</option>
                                    <option value="bank_transfer">Bank Transfer</option>
                                    <option value="card">Card</option>
                                    <option value="online">Online Payment</option>
                                    <option value="cheque">Cheque</option>
                                    <option value="other">Other</option>
                                </select>
                                @error('payment_method')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>
                            
                            <div class="form-group">
                                <label>Reference Number (Optional)</label>
                                <input type="text" name="reference_number" class="form-control" placeholder="Reference or transaction ID">
                            </div>
                            
                            <div class="form-group">
                                <label>Notes (Optional)</label>
                                <textarea name="notes" class="form-control" rows="2" placeholder="Additional notes..."></textarea>
                            </div>
                            
                            <button type="submit" class="btn btn-success btn-block">
                                <i class="fas fa-check-circle"></i> Make Payment
                            </button>
                        </form>
                    </div>
                </div>
            @else
                <div class="card">
                    <div class="card-body text-center">
                        <i class="fas fa-check-circle text-success" style="font-size: 48px;"></i>
                        <h5 class="mt-2">Fully Paid</h5>
                        <p class="text-muted">This fee has been fully paid.</p>
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>

<script>
function toggleFullPayment() {
    const checkbox = document.getElementById('payFull');
    const amountInput = document.querySelector('input[name="amount"]');
    
    if (checkbox.checked) {
        amountInput.value = {{ $allocation->balance }};
        amountInput.readOnly = true;
    } else {
        amountInput.value = '';
        amountInput.readOnly = false;
    }
}
</script>
@endsection