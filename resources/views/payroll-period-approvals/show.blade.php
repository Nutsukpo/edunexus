@extends('layouts.master')

@section('title', 'Payroll Approval')

@section('content')

@php
    $status = strtolower((string) ($payrollPeriod->status ?? 'draft'));
    $staffWithSalaries = $staffWithSalaries ?? [];
    $summary = $summary ?? [];

    $totalStaff = (int) ($summary['total_staff'] ?? count($staffWithSalaries));
    $totalBasic = (float) ($summary['total_basic_salary'] ?? 0);
    $totalAllowances = (float) ($summary['total_allowances'] ?? 0);
    $totalOvertime = (float) ($summary['total_overtime'] ?? 0);
    $totalGross = (float) ($summary['total_gross'] ?? 0);
    $totalTax = (float) ($summary['total_tax'] ?? 0);
    $totalPension = (float) ($summary['total_pension'] ?? 0);
    $totalDeductions = (float) ($summary['total_deductions'] ?? 0);
    $totalNet = (float) ($summary['total_net'] ?? 0);
    $totalWorkedDays = (float) ($summary['total_worked_days'] ?? 0);

    $isPendingApproval = $status === 'pending_approval';
    $isApproved = $status === 'approved';
    $isRejected = $status === 'rejected';
    $isPaid = $status === 'paid';
    $isCancelled = $status === 'cancelled';

    $canSubmit = in_array($status, ['draft', 'processing'], true);

    $submitRoute = Route::has('payroll-periods.submit')
        ? 'payroll-periods.submit'
        : (Route::has('payroll-period-approvals.submit') ? 'payroll-period-approvals.submit' : null);

    /*
     * Approval actions MUST use the approval workflow routes first.
     * The POST handlers are responsible for redirecting to the approval
     * index after a successful decision.
     */
    $approveRoute = Route::has('payroll-period-approvals.approve')
        ? 'payroll-period-approvals.approve'
        : (Route::has('payroll-periods.approve') ? 'payroll-periods.approve' : null);

    $rejectRoute = Route::has('payroll-period-approvals.reject')
        ? 'payroll-period-approvals.reject'
        : (Route::has('payroll-periods.reject') ? 'payroll-periods.reject' : null);

    $resubmitRoute = Route::has('payroll-periods.resubmit')
        ? 'payroll-periods.resubmit'
        : (Route::has('payroll-period-approvals.resubmit') ? 'payroll-period-approvals.resubmit' : null);
@endphp

<div class="container-fluid py-4">

    {{-- HEADER --}}
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4">
        <div>
            <div class="d-flex flex-wrap align-items-center gap-2">
                <h3 class="mb-1 fw-bold text-primary">
                    <i class="fas fa-file-invoice-dollar me-2"></i>
                    Payroll Approval
                </h3>

                <span class="badge rounded-pill status-{{ $status }}">
                    {{ ucwords(str_replace('_', ' ', $status)) }}
                </span>
            </div>

            <p class="text-muted mb-0">
                {{ $payrollPeriod->name ?? 'Payroll Period' }}
                @if($payrollPeriod->period_code)
                    <span class="mx-1">•</span>{{ $payrollPeriod->period_code }}
                @endif
            </p>
        </div>

        <div class="d-flex flex-wrap gap-2 mt-3 mt-md-0">
            <a href="{{ route('payroll-period-approvals.index') }}" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left me-1"></i> Back to Approvals
            </a>

            @if(Route::has('payroll-periods.export-pdf'))
                <a href="{{ route('payroll-periods.export-pdf', $payrollPeriod->id) }}" target="_blank" class="btn btn-outline-danger">
                    <i class="fas fa-file-pdf me-1"></i> PDF
                </a>
            @endif

            @if(Route::has('payroll-periods.export-excel'))
                <a href="{{ route('payroll-periods.export-excel', $payrollPeriod->id) }}" class="btn btn-outline-success">
                    <i class="fas fa-file-excel me-1"></i> Excel
                </a>
            @endif
        </div>
    </div>

    {{-- FLASH MESSAGES --}}
    @foreach(['success', 'error', 'warning'] as $type)
        @if(session($type))
            <div class="alert {{ $type === 'success' ? 'alert-success' : ($type === 'error' ? 'alert-danger' : 'alert-warning') }} alert-dismissible fade show">
                <i class="fas {{ $type === 'success' ? 'fa-check-circle' : ($type === 'error' ? 'fa-exclamation-circle' : 'fa-exclamation-triangle') }} me-2"></i>
                {{ session($type) }}
                <button type="button" class="btn-close" aria-label="Close" onclick="this.closest('.alert').remove()"></button>
            </div>
        @endif
    @endforeach

    {{-- VALIDATION ERRORS --}}
    @if($errors->any())
        <div class="alert alert-danger">
            <strong><i class="fas fa-exclamation-circle me-1"></i> Please correct the following:</strong>
            <ul class="mb-0 mt-2">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- PERIOD INFORMATION --}}
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white py-3">
            <h5 class="mb-0 fw-bold"><i class="fas fa-calendar-alt text-primary me-2"></i> Payroll Period Information</h5>
        </div>
        <div class="card-body">
            <div class="row g-4">
                <div class="col-xl-3 col-md-6"><div class="info-item"><small>Payroll Period</small><strong>{{ $payrollPeriod->name ?? 'N/A' }}</strong></div></div>
                <div class="col-xl-3 col-md-6"><div class="info-item"><small>Period Code</small><strong>{{ $payrollPeriod->period_code ?? 'N/A' }}</strong></div></div>
                <div class="col-xl-3 col-md-6"><div class="info-item"><small>Academic Year</small><strong>{{ $payrollPeriod->academicYear?->name ?? 'N/A' }}</strong></div></div>
                <div class="col-xl-3 col-md-6"><div class="info-item"><small>Month / Year</small><strong>{{ $payrollPeriod->month ?? 'N/A' }} / {{ $payrollPeriod->year ?? 'N/A' }}</strong></div></div>
                <div class="col-xl-3 col-md-6"><div class="info-item"><small>Start Date</small><strong>{{ $payrollPeriod->start_date ? $payrollPeriod->start_date->format('d M Y') : 'N/A' }}</strong></div></div>
                <div class="col-xl-3 col-md-6"><div class="info-item"><small>End Date</small><strong>{{ $payrollPeriod->end_date ? $payrollPeriod->end_date->format('d M Y') : 'N/A' }}</strong></div></div>
                <div class="col-xl-3 col-md-6"><div class="info-item"><small>Payment Date</small><strong>{{ $payrollPeriod->payment_date ? $payrollPeriod->payment_date->format('d M Y') : 'Not Set' }}</strong></div></div>
                <div class="col-xl-3 col-md-6"><div class="info-item"><small>Created By</small><strong>{{ $payrollPeriod->createdBy?->name ?? 'N/A' }}</strong></div></div>
            </div>
        </div>
    </div>

    {{-- SUMMARY --}}
    <div class="row g-3 mb-4">
        <div class="col-xl-3 col-md-6"><div class="summary-card"><div><small>Total Staff</small><h3>{{ number_format($totalStaff) }}</h3></div><div class="summary-icon icon-blue"><i class="fas fa-users"></i></div></div></div>
        <div class="col-xl-3 col-md-6"><div class="summary-card"><div><small>Total Gross Pay</small><h3>GHS {{ number_format($totalGross, 2) }}</h3></div><div class="summary-icon icon-primary"><i class="fas fa-money-bill-wave"></i></div></div></div>
        <div class="col-xl-3 col-md-6"><div class="summary-card"><div><small>Total Deductions</small><h3>GHS {{ number_format($totalDeductions, 2) }}</h3></div><div class="summary-icon icon-danger"><i class="fas fa-minus-circle"></i></div></div></div>
        <div class="col-xl-3 col-md-6"><div class="summary-card summary-net"><div><small>Total Net Pay</small><h3>GHS {{ number_format($totalNet, 2) }}</h3></div><div class="summary-icon icon-success"><i class="fas fa-wallet"></i></div></div></div>
    </div>

    {{-- STAFF SALARY DETAILS --}}
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white py-3">
            <div class="d-flex flex-wrap justify-content-between align-items-center gap-2">
                <div>
                    <h5 class="mb-1 fw-bold"><i class="fas fa-users text-primary me-2"></i> Staff Salary Details</h5>
                    <small class="text-muted"><span id="visibleStaffCount">{{ $totalStaff }}</span> staff member(s)</small>
                </div>
                <div style="width:280px; max-width:100%;">
                    <div class="input-group input-group-sm">
                        <span class="input-group-text bg-light"><i class="fas fa-search"></i></span>
                        <input type="text" id="staffSearch" class="form-control" placeholder="Search staff..." autocomplete="off">
                    </div>
                </div>
            </div>
        </div>

        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0" id="staffTable">
                    <thead class="table-light">
                        <tr>
                            <th>#</th>
                            <th>Staff</th>
                            <th>Staff ID</th>
                            <th class="text-end">Basic Salary</th>
                            <th class="text-end">Allowances</th>
                            <th class="text-end">Overtime</th>
                            <th class="text-end">Gross Pay</th>
                            <th class="text-end">Tax</th>
                            <th class="text-end">Pension</th>
                            <th class="text-end">Deductions</th>
                            <th class="text-end">Net Pay</th>
                            <th class="text-center">Worked Days</th>
                        </tr>
                    </thead>
                    <tbody id="staffTableBody">
                        @forelse($staffWithSalaries as $index => $item)
                            @php
                                $staff = $item['staff'] ?? null;
                                $staffName = trim(($staff->first_name ?? '') . ' ' . ($staff->middle_name ?? '') . ' ' . ($staff->last_name ?? ''));
                                $staffName = $staffName ?: ($staff->name ?? 'N/A');
                                $staffCode = $staff->staff_code ?? $staff->staff_id ?? 'N/A';
                                $searchText = strtolower($staffName . ' ' . $staffCode . ' ' . ($staff->designation ?? $staff->position ?? ''));
                                $basic = (float) ($item['basic_salary'] ?? 0);
                                $allowances = (float) ($item['allowances'] ?? 0);
                                $overtime = (float) ($item['overtime'] ?? 0);
                                $gross = (float) ($item['gross_pay'] ?? 0);
                                $tax = (float) ($item['tax'] ?? 0);
                                $pension = (float) ($item['pension'] ?? 0);
                                $deductions = (float) ($item['deductions'] ?? 0);
                                $net = (float) ($item['net_pay'] ?? 0);
                                $workedDays = (float) ($item['worked_days'] ?? 0);
                                $parts = preg_split('/\s+/', trim($staffName));
                                $initials = strtoupper(substr($parts[0] ?? 'S', 0, 1) . substr($parts[count($parts) - 1] ?? '', 0, 1));
                            @endphp
                            <tr class="staff-row" data-search="{{ $searchText }}">
                                <td>{{ $index + 1 }}</td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="staff-avatar me-2">{{ $initials ?: 'S' }}</div>
                                        <div>
                                            <div class="fw-semibold">{{ $staffName }}</div>
                                            <small class="text-muted">{{ $staff->designation ?? $staff->position ?? 'Staff' }}</small>
                                        </div>
                                    </div>
                                </td>
                                <td><span class="staff-code">{{ $staffCode }}</span></td>
                                <td class="text-end">GHS {{ number_format($basic, 2) }}</td>
                                <td class="text-end">GHS {{ number_format($allowances, 2) }}</td>
                                <td class="text-end">GHS {{ number_format($overtime, 2) }}</td>
                                <td class="text-end fw-semibold">GHS {{ number_format($gross, 2) }}</td>
                                <td class="text-end">GHS {{ number_format($tax, 2) }}</td>
                                <td class="text-end">GHS {{ number_format($pension, 2) }}</td>
                                <td class="text-end text-danger">GHS {{ number_format($deductions, 2) }}</td>
                                <td class="text-end fw-bold text-success">GHS {{ number_format($net, 2) }}</td>
                                <td class="text-center">{{ number_format($workedDays, 2) }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="12" class="text-center py-5 text-muted">
                                    <i class="fas fa-users-slash fa-3x mb-3"></i>
                                    <h6 class="fw-bold">No Staff Assigned</h6>
                                    <p class="mb-0">No staff members are assigned to this payroll period.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                    @if($totalStaff > 0)
                        <tfoot class="table-light">
                            <tr>
                                <th colspan="3" class="text-end">TOTAL</th>
                                <th class="text-end">GHS {{ number_format($totalBasic, 2) }}</th>
                                <th class="text-end">GHS {{ number_format($totalAllowances, 2) }}</th>
                                <th class="text-end">GHS {{ number_format($totalOvertime, 2) }}</th>
                                <th class="text-end">GHS {{ number_format($totalGross, 2) }}</th>
                                <th class="text-end">GHS {{ number_format($totalTax, 2) }}</th>
                                <th class="text-end">GHS {{ number_format($totalPension, 2) }}</th>
                                <th class="text-end text-danger">GHS {{ number_format($totalDeductions, 2) }}</th>
                                <th class="text-end text-success">GHS {{ number_format($totalNet, 2) }}</th>
                                <th class="text-center">{{ number_format($totalWorkedDays, 0) }}</th>
                            </tr>
                        </tfoot>
                    @endif
                </table>
            </div>
        </div>
    </div>

    {{-- WORKFLOW --}}
    @if($canSubmit && $submitRoute)
        <div class="card border-0 shadow-sm mb-4 workflow-card">
            <div class="card-body">
                <div class="d-flex flex-wrap justify-content-between align-items-center gap-3">
                    <div>
                        <h5 class="fw-bold mb-1"><i class="fas fa-paper-plane text-primary me-2"></i> Submit Payroll for Approval</h5>
                        <p class="text-muted mb-0">Review all staff salary details before submitting this payroll.</p>
                    </div>
                    <form action="{{ route($submitRoute, $payrollPeriod->id) }}" method="POST" id="submitPayrollForm">
                        @csrf
                        <button type="submit" class="btn btn-primary btn-lg" {{ $totalStaff < 1 ? 'disabled' : '' }}>
                            <i class="fas fa-paper-plane me-2"></i> Submit for Approval
                        </button>
                    </form>
                </div>
            </div>
        </div>
    @elseif($isPendingApproval && $approveRoute && $rejectRoute)
        <div class="card border-0 shadow-sm mb-4 workflow-card pending-card">
            <div class="card-body">
                <div class="row align-items-center">
                    <div class="col-lg-7">
                        <div class="d-flex align-items-start">
                            <div class="workflow-icon me-3"><i class="fas fa-hourglass-half"></i></div>
                            <div>
                                <h5 class="fw-bold mb-1">Payroll Awaiting Approval</h5>
                                <p class="text-muted mb-0">Review the figures and make an approval decision.</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-5 mt-3 mt-lg-0">
                        <div class="d-flex justify-content-lg-end flex-wrap gap-2">
                            <form action="{{ route($approveRoute, $payrollPeriod->id) }}" method="POST" id="approvePayrollForm">
                                @csrf
                                <button type="submit" class="btn btn-success btn-lg">
                                    <i class="fas fa-check-circle me-2"></i> Approve Payroll
                                </button>
                            </form>

                            {{-- Native dialog: no Bootstrap modal/backdrop dependency --}}
                            <button type="button" class="btn btn-danger btn-lg" id="openRejectDialog">
                                <i class="fas fa-times-circle me-2"></i> Reject Payroll
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        @if($rejectRoute)
            <dialog id="rejectDialog" class="reject-dialog">
                <div class="reject-dialog-inner">
                    <div class="reject-dialog-header">
                        <div>
                            <h5 class="mb-1 fw-bold">Reject Payroll</h5>
                            <small>This action will return the payroll for correction.</small>
                        </div>
                        <button type="button" class="dialog-close" id="closeRejectDialog" aria-label="Close">&times;</button>
                    </div>

                    <form action="{{ route($rejectRoute, $payrollPeriod->id) }}" method="POST" id="rejectPayrollForm">
                        @csrf
                        <div class="reject-dialog-body">
                            <div class="alert alert-warning mb-3">
                                <i class="fas fa-exclamation-triangle me-2"></i>
                                Please provide a clear reason for rejecting this payroll.
                            </div>

                            <label for="rejection_reason" class="form-label fw-semibold">
                                Rejection Reason <span class="text-danger">*</span>
                            </label>
                            <textarea name="rejection_reason" id="rejection_reason" class="form-control" rows="6" maxlength="2000" required placeholder="Enter the reason for rejecting this payroll..."></textarea>
                            <div class="d-flex justify-content-between mt-1">
                                <small class="text-muted">Maximum 2,000 characters.</small>
                                <small class="text-muted"><span id="reasonCount">0</span> / 2000</small>
                            </div>
                        </div>

                        <div class="reject-dialog-footer">
                            <button type="button" class="btn btn-secondary" id="cancelRejectDialog">Cancel</button>
                            <button type="submit" class="btn btn-danger" id="confirmRejectButton">
                                <i class="fas fa-times-circle me-1"></i> Confirm Rejection
                            </button>
                        </div>
                    </form>
                </div>
            </dialog>
        @endif

    @elseif($isApproved)
        <div class="alert alert-success shadow-sm">
            <div class="d-flex align-items-center">
                <i class="fas fa-check-circle fa-2x me-3"></i>
                <div>
                    <h5 class="fw-bold mb-1">Payroll Approved</h5>
                    <div>
                        This payroll period has been approved.
                        @if($payrollPeriod->approvedBy)
                            <br><small>Approved by: <strong>{{ $payrollPeriod->approvedBy->name ?? trim(($payrollPeriod->approvedBy->first_name ?? '') . ' ' . ($payrollPeriod->approvedBy->last_name ?? '')) }}</strong></small>
                        @endif
                        @if($payrollPeriod->approved_at)
                            <small class="ms-2">on {{ $payrollPeriod->approved_at->format('d M Y H:i') }}</small>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    @elseif($isRejected)
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-body">
                <div class="d-flex align-items-start">
                    <div class="workflow-icon rejection-icon me-3"><i class="fas fa-times-circle"></i></div>
                    <div class="flex-grow-1">
                        <h5 class="fw-bold text-danger mb-1">Payroll Rejected</h5>
                        <p class="text-muted mb-3">This payroll period was rejected and requires correction before resubmission.</p>
                        @if($payrollPeriod->description)
                            <div class="rejection-box">
                                <strong><i class="fas fa-comment-alt me-1"></i> Rejection Details</strong>
                                <div class="mt-2">{!! nl2br(e($payrollPeriod->description)) !!}</div>
                            </div>
                        @endif
                        @if($resubmitRoute)
                            <form action="{{ route($resubmitRoute, $payrollPeriod->id) }}" method="POST" class="mt-3">
                                @csrf
                                <button type="submit" class="btn btn-warning"><i class="fas fa-redo me-1"></i> Resubmit for Approval</button>
                            </form>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    @elseif($isPaid)
        <div class="alert alert-primary shadow-sm"><i class="fas fa-money-check-alt me-2"></i><strong>Payroll Paid</strong><div class="small mt-1">This payroll period has already been paid.</div></div>
    @elseif($isCancelled)
        <div class="alert alert-secondary shadow-sm"><i class="fas fa-ban me-2"></i><strong>Payroll Cancelled</strong><div class="small mt-1">This payroll period has been cancelled.</div></div>
    @endif

</div>
@endsection

@push('styles')
<style>
    .info-item small{display:block;color:#6c757d;font-size:12px;margin-bottom:5px}.info-item strong{display:block;color:#212529;font-size:14px}
    .summary-card{min-height:120px;background:#fff;border-radius:12px;padding:20px;box-shadow:0 3px 12px rgba(0,0,0,.06);border-left:4px solid #1565c0;display:flex;align-items:center;justify-content:space-between}.summary-card small{display:block;color:#6c757d;font-size:12px;font-weight:600;margin-bottom:5px;text-transform:uppercase}.summary-card h3{margin:0;font-size:20px;font-weight:700}.summary-net{border-left-color:#198754}.summary-icon{width:48px;height:48px;border-radius:12px;display:flex;align-items:center;justify-content:center;font-size:20px;flex-shrink:0}.icon-blue{background:#e3f2fd;color:#1565c0}.icon-primary{background:#e8eaf6;color:#3949ab}.icon-danger{background:#ffebee;color:#c62828}.icon-success{background:#e8f5e9;color:#2e7d32}
    .status-draft{background:#e9ecef;color:#495057}.status-processing{background:#cff4fc;color:#055160}.status-pending_approval{background:#fff3cd;color:#664d03}.status-approved{background:#d1e7dd;color:#0f5132}.status-rejected{background:#f8d7da;color:#842029}.status-paid{background:#cfe2ff;color:#084298}.status-cancelled{background:#e2e3e5;color:#41464b}
    .workflow-card{border-left:4px solid #1565c0!important}.pending-card{border-left-color:#ffc107!important}.workflow-icon{width:50px;height:50px;border-radius:12px;background:#e7f1ff;color:#0d6efd;display:flex;align-items:center;justify-content:center;flex-shrink:0;font-size:21px}.rejection-icon{background:#f8d7da;color:#dc3545}.staff-avatar{width:38px;height:38px;border-radius:50%;background:#e7f1ff;color:#0d6efd;display:flex;align-items:center;justify-content:center;font-size:11px;font-weight:700;flex-shrink:0}.staff-code{font-family:monospace;background:#f8f9fa;padding:4px 7px;border-radius:5px;font-size:12px}.table th{white-space:nowrap;font-size:11px;text-transform:uppercase;letter-spacing:.3px}.table td{font-size:13px;white-space:nowrap}.rejection-box{background:#fff5f5;border:1px solid #f5c2c7;border-radius:8px;padding:15px;color:#842029;overflow-wrap:anywhere}

    /* Native dialog deliberately used here so the reject window cannot be trapped by Bootstrap modal/backdrop state. */
    .reject-dialog{width:min(620px,calc(100vw - 30px));padding:0;border:0;border-radius:14px;box-shadow:0 20px 70px rgba(0,0,0,.30);overflow:hidden}.reject-dialog::backdrop{background:rgba(0,0,0,.58)}.reject-dialog-inner{background:#fff}.reject-dialog-header{background:#dc3545;color:#fff;padding:18px 20px;display:flex;justify-content:space-between;align-items:center}.reject-dialog-header small{opacity:.9}.dialog-close{border:0;background:transparent;color:#fff;font-size:30px;line-height:1;cursor:pointer;padding:0 4px}.reject-dialog-body{padding:22px}.reject-dialog-footer{padding:15px 20px;background:#f8f9fa;border-top:1px solid #dee2e6;display:flex;justify-content:flex-end;gap:10px}.reject-dialog textarea{resize:vertical;min-height:140px}.reject-dialog[open]{animation:rejectDialogIn .15s ease-out}@keyframes rejectDialogIn{from{opacity:0;transform:scale(.97)}to{opacity:1;transform:scale(1)}}
    @media(max-width:768px){.table{min-width:1200px}.reject-dialog-footer{flex-wrap:wrap}.reject-dialog-footer .btn{flex:1}.summary-card h3{font-size:17px}}
</style>
@endpush

@push('scripts')
<script>
(function () {
    'use strict';

    function ready(fn) {
        if (document.readyState !== 'loading') fn();
        else document.addEventListener('DOMContentLoaded', fn);
    }

    ready(function () {
        /* STAFF SEARCH */
        const search = document.getElementById('staffSearch');
        const rows = Array.from(document.querySelectorAll('#staffTableBody .staff-row'));
        const count = document.getElementById('visibleStaffCount');

        if (search) {
            search.addEventListener('input', function () {
                const term = this.value.toLowerCase().trim();
                let visible = 0;
                rows.forEach(function (row) {
                    const match = !term || (row.dataset.search || '').includes(term);
                    row.style.display = match ? '' : 'none';
                    if (match) visible++;
                });
                if (count) count.textContent = visible;
            });
        }

        /* SUBMIT CONFIRMATION */
        const submitForm = document.getElementById('submitPayrollForm');
        if (submitForm) {
            submitForm.addEventListener('submit', function (event) {
                if (!window.confirm('Are you sure you want to submit this payroll for approval?')) {
                    event.preventDefault();
                    return;
                }
                const button = submitForm.querySelector('button[type="submit"]');
                if (button) {
                    button.disabled = true;
                    button.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Submitting...';
                }
            });
        }

        /* APPROVAL CONFIRMATION
         * Do not preventDefault(). The browser must perform the normal POST,
         * then Laravel redirects to the approval index.
         */
        const approveForm = document.getElementById('approvePayrollForm');
        if (approveForm) {
            approveForm.addEventListener('submit', function (event) {
                if (!window.confirm('Are you sure you want to approve this payroll period?')) {
                    event.preventDefault();
                    return;
                }

                const button = approveForm.querySelector('button[type="submit"]');
                if (button) {
                    button.disabled = true;
                    button.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Approving...';
                }
                // IMPORTANT: no preventDefault here.
            });
        }

        /* REJECT DIALOG -- NO BOOTSTRAP MODAL CODE */
        const dialog = document.getElementById('rejectDialog');
        const openButton = document.getElementById('openRejectDialog');
        const closeButton = document.getElementById('closeRejectDialog');
        const cancelButton = document.getElementById('cancelRejectDialog');
        const form = document.getElementById('rejectPayrollForm');
        const reason = document.getElementById('rejection_reason');
        const counter = document.getElementById('reasonCount');
        const confirmButton = document.getElementById('confirmRejectButton');

        function openRejectDialog() {
            if (!dialog) return;
            if (typeof dialog.showModal === 'function') {
                dialog.showModal();
            } else {
                dialog.setAttribute('open', 'open');
            }
            setTimeout(function () {
                if (reason) reason.focus();
            }, 50);
        }

        function closeRejectDialog() {
            if (!dialog) return;
            if (typeof dialog.close === 'function') dialog.close();
            else dialog.removeAttribute('open');
        }

        if (openButton) openButton.addEventListener('click', openRejectDialog);
        if (closeButton) closeButton.addEventListener('click', closeRejectDialog);
        if (cancelButton) cancelButton.addEventListener('click', closeRejectDialog);

        if (reason && counter) {
            reason.addEventListener('input', function () {
                counter.textContent = this.value.length;
            });
        }

        if (dialog) {
            dialog.addEventListener('cancel', function () {
                dialog.close();
            });

            /* Click outside the white dialog panel to close. */
            dialog.addEventListener('click', function (event) {
                if (event.target === dialog) closeRejectDialog();
            });
        }

        if (form) {
            form.addEventListener('submit', function (event) {
                const value = reason ? reason.value.trim() : '';

                if (!value) {
                    event.preventDefault();
                    alert('Please enter a rejection reason before continuing.');
                    if (reason) reason.focus();
                    return;
                }

                if (value.length > 2000) {
                    event.preventDefault();
                    alert('The rejection reason cannot exceed 2,000 characters.');
                    if (reason) reason.focus();
                    return;
                }

                if (!window.confirm('Are you sure you want to reject this payroll?')) {
                    event.preventDefault();
                    return;
                }

                if (confirmButton) {
                    confirmButton.disabled = true;
                    confirmButton.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i> Rejecting...';
                }

                // IMPORTANT: do not preventDefault().
                // Laravel handles the POST and redirects to approval index.

            });
        }

        /* Reopen the native dialog automatically after Laravel validation errors. */
        @if($errors->has('rejection_reason'))
            openRejectDialog();
            if (reason) reason.focus();
        @endif
    });
})();
</script>
@endpush
