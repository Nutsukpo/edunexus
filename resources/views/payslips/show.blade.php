@extends('layouts.master')

@section('title', 'Payslip Details')

@section('content')
<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-md-10">
            <div class="card shadow-sm">
                <div class="card-header bg-white d-flex justify-content-between align-items-center flex-wrap gap-2">
                    <h4 class="mb-0 fw-bold text-dark">
                        <i class="fas fa-file-invoice text-primary me-2"></i> Payslip Details
                    </h4>
                    <div class="d-flex flex-wrap gap-2">
                        <a href="{{ route('payslips.pdf', $payslip->id) }}" class="btn btn-sm" style="background: #dc3545; color: #ffffff;">
                            <i class="fas fa-file-pdf me-1"></i> Download PDF
                        </a>
                        <a href="{{ route('payslips.index') }}" class="btn btn-sm" style="background: #6c757d; color: #ffffff;">
                            <i class="fas fa-arrow-left me-1"></i> Back
                        </a>
                    </div>
                </div>

                <div class="card-body" style="background: #ffffff;">
                    @if(session('success'))
                        <div class="alert alert-success alert-dismissible fade show" style="border-left: 4px solid #28a745; background: #f8fff8;">
                            <i class="fas fa-check-circle me-1"></i> {{ session('success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    @if(session('error'))
                        <div class="alert alert-danger alert-dismissible fade show" style="border-left: 4px solid #dc3545; background: #fff8f8;">
                            <i class="fas fa-exclamation-circle me-1"></i> {{ session('error') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    <div class="row">
                        <!-- Staff Information -->
                        <div class="col-md-6">
                            <div class="card mb-3" style="border: 1px solid #e9ecef;">
                                <div class="card-header bg-light">
                                    <h6 class="mb-0 fw-bold text-dark">
                                        <i class="fas fa-user text-primary me-1"></i> Staff Information
                                    </h6>
                                </div>
                                <div class="card-body">
                                    <table class="table table-sm table-borderless">
                                        <tr>
                                            <th style="width: 30%; color: #6c757d;">Name</th>
                                            <td class="text-dark fw-semibold">{{ $payslip->staff->first_name ?? '' }} {{ $payslip->staff->last_name ?? '' }}</td>
                                        </tr>
                                        <tr>
                                            <th style="color: #6c757d;">Position</th>
                                            <td class="text-dark">{{ $payslip->staff->position ?? 'N/A' }}</td>
                                        </tr>
                                        <tr>
                                            <th style="color: #6c757d;">Department</th>
                                            <td class="text-dark">{{ $payslip->staff->department ?? 'N/A' }}</td>
                                        </tr>
                                        <tr>
                                            <th style="color: #6c757d;">Staff Code</th>
                                            <td class="text-dark">{{ $payslip->staff->staff_id ?? 'N/A' }}</td>
                                        </tr>
                                        <tr>
                                            <th style="color: #6c757d;">Email</th>
                                            <td class="text-dark">{{ $payslip->staff->email ?? 'N/A' }}</td>
                                        </tr>
                                    </table>
                                </div>
                            </div>
                        </div>

                        <!-- Payslip Information -->
                        <div class="col-md-6">
                            <div class="card mb-3" style="border: 1px solid #e9ecef;">
                                <div class="card-header bg-light">
                                    <h6 class="mb-0 fw-bold text-dark">
                                        <i class="fas fa-info-circle text-primary me-1"></i> Payslip Information
                                    </h6>
                                </div>
                                <div class="card-body">
                                    <table class="table table-sm table-borderless">
                                        <tr>
                                            <th style="width: 30%; color: #6c757d;">Period</th>
                                            <td class="text-dark fw-semibold">{{ $payslip->month_name }} {{ $payslip->year }}</td>
                                        </tr>
                                        <tr>
                                            <th style="color: #6c757d;">Generated Date</th>
                                            <td class="text-dark">{{ $payslip->created_at ? $payslip->created_at->format('d M, Y h:i A') : 'N/A' }}</td>
                                        </tr>
                                        <tr>
                                            <th style="color: #6c757d;">Status</th>
                                            <td>
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
                                        </tr>
                                        <tr>
                                            <th style="color: #6c757d;">Generated By</th>
                                            <td class="text-dark">{{ $payslip->creator->name ?? 'N/A' }}</td>
                                        </tr>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Salary Breakdown -->
                    <div class="row mt-3">
                        <div class="col-md-12">
                            <div class="card" style="border: 1px solid #e9ecef;">
                                <div class="card-header bg-light">
                                    <h6 class="mb-0 fw-bold text-dark">
                                        <i class="fas fa-money-bill-wave text-primary me-1"></i> Salary Breakdown
                                    </h6>
                                </div>
                                <div class="card-body p-0">
                                    <div class="table-responsive">
                                        <table class="table table-hover mb-0">
                                            <thead style="background: #f8f9fa;">
                                                <tr>
                                                    <th style="color: #212529; font-weight: 600;">Description</th>
                                                    <th class="text-end" style="color: #212529; font-weight: 600;">Amount</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <!-- Basic Salary -->
                                                <tr>
                                                    <td class="text-dark">Basic Salary</td>
                                                    <td class="text-end text-dark">${{ number_format($payslip->basic_salary ?? 0, 2) }}</td>
                                                </tr>

                                                <!-- Allowances -->
                                                <tr>
                                                    <td class="text-dark">Allowances</td>
                                                    <td class="text-end text-dark">${{ number_format($payslip->allowances ?? 0, 2) }}</td>
                                                </tr>

                                                <!-- Bonus -->
                                                @if(($payslip->bonus ?? 0) > 0)
                                                <tr>
                                                    <td class="text-dark">Bonus</td>
                                                    <td class="text-end text-dark">${{ number_format($payslip->bonus ?? 0, 2) }}</td>
                                                </tr>
                                                @endif

                                                <!-- Overtime -->
                                                @if(($payslip->overtime ?? 0) > 0)
                                                <tr>
                                                    <td class="text-dark">Overtime</td>
                                                    <td class="text-end text-dark">${{ number_format($payslip->overtime ?? 0, 2) }}</td>
                                                </tr>
                                                @endif

                                                <!-- Total Earnings -->
                                                <tr style="background: #f8f9fa;">
                                                    <td class="fw-bold text-dark">Total Earnings</td>
                                                    <td class="text-end fw-bold text-success">${{ number_format($payslip->total_earnings ?? 0, 2) }}</td>
                                                </tr>

                                                <!-- Deductions Header -->
                                                <tr>
                                                    <td colspan="2" style="padding: 5px 10px;"></td>
                                                </tr>
                                                <tr style="background: #fff3f3;">
                                                    <td class="fw-bold text-danger">Deductions</td>
                                                    <td class="text-end"></td>
                                                </tr>

                                                <!-- Tax -->
                                                @if(($payslip->tax ?? 0) > 0)
                                                <tr>
                                                    <td class="text-dark" style="padding-left: 20px;">Tax (PAYE)</td>
                                                    <td class="text-end text-dark">${{ number_format($payslip->tax ?? 0, 2) }}</td>
                                                </tr>
                                                @endif

                                                <!-- Pension -->
                                                @if(($payslip->pension ?? 0) > 0)
                                                <tr>
                                                    <td class="text-dark" style="padding-left: 20px;">Pension (SSNIT)</td>
                                                    <td class="text-end text-dark">${{ number_format($payslip->pension ?? 0, 2) }}</td>
                                                </tr>
                                                @endif

                                                <!-- Tier 2 -->
                                                @if(($payslip->tier2 ?? 0) > 0)
                                                <tr>
                                                    <td class="text-dark" style="padding-left: 20px;">Tier 2</td>
                                                    <td class="text-end text-dark">${{ number_format($payslip->tier2 ?? 0, 2) }}</td>
                                                </tr>
                                                @endif

                                                <!-- Tier 3 -->
                                                @if(($payslip->tier3 ?? 0) > 0)
                                                <tr>
                                                    <td class="text-dark" style="padding-left: 20px;">Tier 3</td>
                                                    <td class="text-end text-dark">${{ number_format($payslip->tier3 ?? 0, 2) }}</td>
                                                </tr>
                                                @endif

                                                <!-- Insurance -->
                                                @if(($payslip->insurance ?? 0) > 0)
                                                <tr>
                                                    <td class="text-dark" style="padding-left: 20px;">Insurance</td>
                                                    <td class="text-end text-dark">${{ number_format($payslip->insurance ?? 0, 2) }}</td>
                                                </tr>
                                                @endif

                                                <!-- Loans -->
                                                @if(($payslip->loans ?? 0) > 0)
                                                <tr>
                                                    <td class="text-dark" style="padding-left: 20px;">Loans</td>
                                                    <td class="text-end text-dark">${{ number_format($payslip->loans ?? 0, 2) }}</td>
                                                </tr>
                                                @endif

                                                <!-- Other Deductions -->
                                                @if(($payslip->other_deductions ?? 0) > 0)
                                                <tr>
                                                    <td class="text-dark" style="padding-left: 20px;">Other Deductions</td>
                                                    <td class="text-end text-dark">${{ number_format($payslip->other_deductions ?? 0, 2) }}</td>
                                                </tr>
                                                @endif

                                                <!-- Total Deductions -->
                                                <tr style="background: #f8f9fa;">
                                                    <td class="fw-bold text-dark">Total Deductions</td>
                                                    <td class="text-end fw-bold text-danger">${{ number_format($payslip->total_deductions ?? 0, 2) }}</td>
                                                </tr>

                                                <!-- Net Pay -->
                                                <tr style="background: #e8f5e9;">
                                                    <td class="fw-bold text-dark" style="font-size: 1.1rem;">Net Pay</td>
                                                    <td class="text-end fw-bold text-success" style="font-size: 1.1rem;">
                                                        ${{ number_format($payslip->net_pay ?? 0, 2) }}
                                                    </td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Breakdown Details (if available) -->
                    @if($payslip->breakdown && is_array($payslip->breakdown) && count($payslip->breakdown) > 0)
                    <div class="row mt-3">
                        <div class="col-md-12">
                            <div class="card" style="border: 1px solid #e9ecef;">
                                <div class="card-header bg-light">
                                    <h6 class="mb-0 fw-bold text-dark">
                                        <i class="fas fa-list-ul text-primary me-1"></i> Breakdown Details
                                    </h6>
                                </div>
                                <div class="card-body p-0">
                                    <div class="table-responsive">
                                        <table class="table table-sm table-hover mb-0">
                                            <thead style="background: #f8f9fa;">
                                                <tr>
                                                    <th style="color: #212529; font-weight: 600;">Item</th>
                                                    <th class="text-end" style="color: #212529; font-weight: 600;">Value</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($payslip->breakdown as $key => $value)
                                                    @if(!is_array($value) && !is_object($value))
                                                    <tr>
                                                        <td class="text-dark">{{ ucfirst(str_replace('_', ' ', $key)) }}</td>
                                                        <td class="text-end text-dark">
                                                            @if(is_numeric($value))
                                                                ${{ number_format((float) $value, 2) }}
                                                            @else
                                                                {{ $value }}
                                                            @endif
                                                        </td>
                                                    </tr>
                                                    @endif
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endif

                    <!-- Notes -->
                    @if($payslip->notes)
                    <div class="row mt-3">
                        <div class="col-md-12">
                            <div class="card" style="border: 1px solid #e9ecef;">
                                <div class="card-header bg-light">
                                    <h6 class="mb-0 fw-bold text-dark">
                                        <i class="fas fa-sticky-note text-primary me-1"></i> Notes
                                    </h6>
                                </div>
                                <div class="card-body">
                                    <p class="text-dark mb-0">{{ $payslip->notes }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    .table th {
        font-size: 13px;
        font-weight: 600;
    }
    .table td {
        font-size: 13px;
        vertical-align: middle;
    }
    .table-sm td, .table-sm th {
        padding: 0.5rem;
    }
    .table-borderless td, .table-borderless th {
        border: none;
    }
    .table-hover tbody tr:hover {
        background: #f8f9fa;
    }
    .btn-sm {
        padding: 6px 14px;
        font-size: 13px;
    }
    .badge {
        font-weight: 500;
        padding: 5px 12px;
        font-size: 12px;
    }
    .card {
        border-radius: 8px;
    }
    .card-header {
        border-radius: 8px 8px 0 0 !important;
    }
</style>
@endpush