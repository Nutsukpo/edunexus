@extends('layouts.master')

@section('title', 'Payslips')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <div class="card shadow-sm">
                <div class="card-header bg-white d-flex justify-content-between align-items-center py-3 flex-wrap gap-2">
                    <h5 class="mb-0 fw-bold" style="color: #212529;">
                        <i class="fas fa-file-invoice me-2 text-primary" ></i>Payslips
                        <span class="badge ms-2" style="background: #e9ecef; color: #212529;">{{ $payslips->total() }}</span>
                    </h5>
                    <div class="d-flex gap-2">
                        <a href="{{ route('payslips.create') }}" class="btn btn-sm text-primary" >
                            <i class="fas fa-plus me-1"></i> Generate Payslip
                        </a>
                    </div>
                </div>

                <div class="card-body" style="background: #ffffff;">
                    @if(session('success'))
                        <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm" role="alert" style="background: #f8f9fa; color: #212529; border: 1px solid #dee2e6;">
                            <i class="fas fa-check-circle me-2" style="color: #28a745;"></i> {{ session('success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @endif

                    @if(session('error'))
                        <div class="alert alert-danger alert-dismissible fade show border-0 shadow-sm" role="alert" style="background: #f8f9fa; color: #212529; border: 1px solid #dee2e6;">
                            <i class="fas fa-exclamation-circle me-2" style="color: #dc3545;"></i> {{ session('error') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @endif

                    <!-- Filter Section -->
                    <div class="row mb-3">
                        <div class="col-md-12">
                            <form action="{{ route('payslips.filter') }}" method="GET" class="row g-2">
                                <div class="col-md-3">
                                    <select name="month" class="form-select form-select-sm" style="border-color: #dee2e6; color: #212529;">
                                        <option value="">All Months</option>
                                        @foreach($months ?? [] as $key => $month)
                                            <option value="{{ $key }}" {{ request('month') == $key ? 'selected' : '' }}>
                                                {{ $month }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-2">
                                    <select name="year" class="form-select form-select-sm" style="border-color: #dee2e6; color: #212529;">
                                        <option value="">All Years</option>
                                        @foreach($years ?? [] as $year)
                                            <option value="{{ $year }}" {{ request('year') == $year ? 'selected' : '' }}>
                                                {{ $year }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <select name="staff_id" class="form-select form-select-sm" style="border-color: #dee2e6; color: #212529;">
                                        <option value="">All Staff</option>
                                        @foreach($staffs ?? [] as $staff)
                                            <option value="{{ $staff->id }}" {{ request('staff_id') == $staff->id ? 'selected' : '' }}>
                                                {{ $staff->first_name }} {{ $staff->last_name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-2">
                                    <button type="submit" class="btn btn-sm w-100 bg-primary text-white" >
                                        <i class="fas fa-filter me-1"></i> Filter
                                    </button>
                                </div>
                                <div class="col-md-2">
                                    <a href="{{ route('payslips.index') }}" class="btn btn-sm w-100" style="background: #f8f9fa; color: #212529; border: 1px solid #dee2e6;">
                                        <i class="fas fa-undo me-1"></i> Reset
                                    </a>
                                </div>
                            </form>
                        </div>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-hover" id="payslipTable">
                            <thead style="background: #f8f9fa;">
                                <tr>
                                    <th width="50" style="color: #212529;">#</th>
                                    <th style="color: #212529;">Staff</th>
                                    <th style="color: #212529;">Staff Code</th>
                                    <th style="color: #212529;">Month</th>
                                    <th style="color: #212529;">Year</th>
                                    <th class="text-end" style="color: #212529;">Basic Salary</th>
                                    <th class="text-end" style="color: #212529;">Net Pay</th>
                                    <th class="text-center" style="color: #212529;">Status</th>
                                    <th class="text-center" style="color: #212529; min-width: 140px;">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($payslips as $payslip)
                                <tr data-id="{{ $payslip->id }}">
                                    <td style="color: #212529;">{{ $loop->iteration }}</td>
                                    <td style="color: #212529;">
                                        <div class="fw-bold">{{ $payslip->staff->first_name ?? '' }} {{ $payslip->staff->last_name ?? '' }}</div>
                                    </td>
                                    <td style="color: #212529;">{{ $payslip->staff->staff_id ?? 'N/A' }}</td>
                                    <td style="color: #212529;">{{ $payslip->month_name }}</td>
                                    <td style="color: #212529;">{{ $payslip->year }}</td>
                                    <td class="text-end" style="color: #212529;">${{ number_format($payslip->basic_salary, 2) }}</td>
                                    <td class="text-end" style="color: #212529; font-weight: 600;">${{ number_format($payslip->net_pay, 2) }}</td>
                                    <td class="text-center">
                                        @if($payslip->status == 'generated')
                                            <span class="badge" style="background: #e9ecef; color: #212529;">
                                                <i class="fas fa-check-circle me-1" style="color: #28a745;"></i> Generated
                                            </span>
                                        @elseif($payslip->status == 'cancelled')
                                            <span class="badge" style="background: #f8f9fa; color: #212529; border: 1px solid #dee2e6;">
                                                <i class="fas fa-times-circle me-1" style="color: #dc3545;"></i> Cancelled
                                            </span>
                                        @else
                                            <span class="badge" style="background: #f8f9fa; color: #212529; border: 1px solid #dee2e6;">
                                                {{ ucfirst($payslip->status) }}
                                            </span>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="d-flex justify-content-center gap-1 flex-wrap">
                                            <a href="{{ route('payslips.show', $payslip) }}" 
                                               class="btn btn-sm" 
                                               style="background: #f8f9fa; color: #212529; border: 1px solid #dee2e6;"
                                               title="View Payslip">
                                                <i class="fas fa-eye" style="color: #6c757d;"></i>
                                            </a>
                                            <a href="{{ route('payslips.pdf', $payslip) }}" 
                                               class="btn btn-sm" 
                                               style="background: #f8f9fa; color: #212529; border: 1px solid #dee2e6;"
                                               title="Download PDF">
                                                <i class="fas fa-file-pdf" style="color: #dc3545;"></i>
                                            </a>
                                            <button type="button" 
                                                    class="btn btn-sm delete-payslip-btn" 
                                                    style="background: #f8f9fa; color: #212529; border: 1px solid #dee2e6;"
                                                    data-id="{{ $payslip->id }}"
                                                    data-url="{{ route('payslips.destroy', $payslip) }}"
                                                    data-staff="{{ $payslip->staff->first_name ?? '' }} {{ $payslip->staff->last_name ?? '' }}"
                                                    data-month="{{ $payslip->month_name }}"
                                                    data-year="{{ $payslip->year }}"
                                                    data-status="{{ $payslip->status }}"
                                                    title="Delete Payslip">
                                                <i class="fas fa-trash-alt" style="color: #6c757d;"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="9" class="text-center py-5">
                                        <i class="fas fa-file-invoice fa-3x d-block mb-3" style="color: #6c757d;"></i>
                                        <p class="mb-0" style="color: #6c757d;">No payslips found.</p>
                                        <a href="{{ route('payslips.create') }}" class="btn btn-sm mt-3" style="background: #212529; color: #ffffff;">
                                            <i class="fas fa-plus me-1"></i> Generate First Payslip
                                        </a>
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="d-flex justify-content-center mt-3">
                        {{ $payslips->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Delete Form (Hidden) -->
<form id="deleteForm" method="POST" style="display: none;">
    @csrf
    @method('DELETE')
</form>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    console.log('Payslip index page loaded');

    // Single delete button click handler with browser confirm
    $(document).on('click', '.delete-payslip-btn', function() {
        const $btn = $(this);
        const id = $btn.data('id');
        const url = $btn.data('url');
        const staffName = $btn.data('staff');
        const month = $btn.data('month');
        const year = $btn.data('year');
        const status = $btn.data('status');
        
        console.log('Delete button clicked for:', {
            id: id,
            staff: staffName,
            month: month,
            year: year,
            status: status,
            url: url
        });

        // Build confirmation message
        let message = `Are you sure you want to delete this payslip?\n\n`;
        message += `Staff: ${staffName || 'Unknown Staff'}\n`;
        message += `Period: ${month || ''} ${year || ''}\n`;
        message += `Status: ${status ? status.charAt(0).toUpperCase() + status.slice(1) : 'Unknown'}\n\n`;
        
        if (status === 'cancelled') {
            message += `⚠️ WARNING: This payslip has been CANCELLED.\n`;
        }
        
        message += `\nThis action cannot be undone!`;
        
        // Show browser confirm dialog
        if (confirm(message)) {
            console.log('User confirmed deletion for payslip:', id);
            
            // Set the form action and submit
            const $form = $('#deleteForm');
            $form.attr('action', url);
            $form.submit();
        } else {
            console.log('User cancelled deletion for payslip:', id);
        }
    });

    // Log total number of delete buttons
    console.log('Total delete buttons found:', $('.delete-payslip-btn').length);
});
</script>
@endpush

@push('styles')
<style>
    .table th {
        font-size: 12px;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        border-bottom: 2px solid #dee2e6;
    }
    .table td {
        font-size: 13px;
        vertical-align: middle;
    }
    .table-hover tbody tr:hover {
        background: #f8f9fa;
    }
    .btn-sm {
        padding: 4px 10px;
        font-size: 12px;
    }
    .btn-sm:hover {
        transform: translateY(-1px);
        box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        transition: all 0.2s;
    }
    .badge {
        font-weight: 500;
        padding: 5px 12px;
        font-size: 11px;
    }
    .form-select, .form-control {
        border-color: #dee2e6;
        color: #212529;
        background: #ffffff;
    }
    .form-select:focus, .form-control:focus {
        border-color: #6c757d;
        box-shadow: 0 0 0 0.2rem rgba(108, 117, 125, 0.25);
    }
    .pagination {
        margin-bottom: 0;
    }
    .pagination .page-link {
        color: #212529;
        border-color: #dee2e6;
    }
    .pagination .page-item.active .page-link {
        background: #212529;
        border-color: #212529;
        color: #ffffff;
    }
    .pagination .page-link:hover {
        background: #f8f9fa;
        color: #212529;
    }
    .delete-payslip-btn:hover {
        background: #dc3545 !important;
        border-color: #dc3545 !important;
    }
    .delete-payslip-btn:hover .fa-trash-alt {
        color: #ffffff !important;
    }
    /* Responsive fixes for action buttons */
    @media (max-width: 576px) {
        .btn-sm {
            padding: 2px 6px;
            font-size: 10px;
        }
        .table td {
            font-size: 12px;
            padding: 6px 4px;
        }
        .table th {
            font-size: 10px;
            padding: 6px 4px;
        }
        .actions-cell {
            min-width: 100px;
        }
    }
</style>
@endpush