@extends('layouts.master')

@section('title', 'Edit Payroll Period')

@section('content')

<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <div class="card shadow-sm" style="background: #ffffff; border-radius: 12px; border: 1px solid #e9ecef;">
                <div class="card-header" style="background: #ffffff; border-bottom: 2px solid #e9ecef; border-radius: 12px 12px 0 0; padding: 20px 25px;">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-0" style="color: #212529; font-weight: 600;">
                            <i class="fas fa-edit mr-2" style="color: #6c757d;"></i> Edit Payroll Period
                        </h5>
                        <div>
                            <a href="{{ route('payroll-periods.show', $payrollPeriod->id) }}" class="btn btn-sm" style="background: #f8f9fa; color: #212529; border: 1px solid #dee2e6;">
                                <i class="fas fa-eye mr-1"></i> View
                            </a>
                            <a href="{{ route('payroll-periods.index') }}" class="btn btn-sm" style="background: #f8f9fa; color: #212529; border: 1px solid #dee2e6;">
                                <i class="fas fa-arrow-left mr-1"></i> Back
                            </a>
                        </div>
                    </div>
                </div>

                <div class="card-body" style="background: #ffffff; padding: 25px;">
                    @if(session('error'))
                        <div class="alert alert-danger alert-dismissible fade show" style="border-left: 4px solid #dc3545; background: #ffffff; color: #212529; border: 1px solid #f5c6cb;">
                            <i class="fas fa-exclamation-circle mr-1"></i> {{ session('error') }}
                            <button type="button" class="close" data-dismiss="alert" style="color: #212529;">&times;</button>
                        </div>
                    @endif

                    @if($errors->any())
                        <div class="alert alert-danger alert-dismissible fade show" style="border-left: 4px solid #dc3545; background: #ffffff; color: #212529; border: 1px solid #f5c6cb;">
                            <button type="button" class="close" data-dismiss="alert" style="color: #212529;">&times;</button>
                            <ul class="mb-0">
                                @foreach($errors->all() as $error)
                                    <li><i class="fas fa-times-circle mr-1"></i> {{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <div class="alert" style="border-left: 4px solid #6c757d; background: #f8f9fa; color: #212529; border: 1px solid #dee2e6;">
                        <i class="fas fa-info-circle mr-1" style="color: #6c757d;"></i>
                        Editing payroll period: <strong>{{ $payrollPeriod->period_code }}</strong>
                        <span class="badge ml-2" style="background: #e9ecef; color: #212529; padding: 4px 12px;">
                            {{ ucfirst($payrollPeriod->status) }}
                        </span>
                    </div>

                    <form action="{{ route('payroll-periods.update', $payrollPeriod->id) }}" method="POST" id="payrollForm">
                        @csrf
                        @method('PUT')

                        <div class="row">
                            {{-- Period Code (Read-only) --}}
                            <div class="col-md-6 mb-3">
                                <label class="form-label" style="color: #212529; font-weight: 600;">
                                    <i class="fas fa-hashtag mr-1" style="color: #6c757d;"></i> Period Code
                                </label>
                                <div class="form-control" style="background: #f8f9fa; border: 1px solid #dee2e6; color: #212529; font-weight: 500; cursor: not-allowed;">
                                    <i class="fas fa-tag mr-2" style="color: #6c757d;"></i>
                                    {{ $payrollPeriod->period_code }}
                                </div>
                                <small class="text-muted" style="color: #6c757d !important;">Period code is auto-generated and cannot be changed.</small>
                            </div>

                            {{-- Name --}}
                            <div class="col-md-6 mb-3">
                                <label class="form-label" style="color: #212529; font-weight: 600;">
                                    <i class="fas fa-tag mr-1" style="color: #6c757d;"></i> Name <span class="text-danger">*</span>
                                </label>
                                <div class="input-group">
                                    <span class="input-group-text" style="background: #f8f9fa; border: 1px solid #dee2e6; color: #6c757d;">
                                        <i class="fas fa-pencil-alt"></i>
                                    </span>
                                    <input type="text"
                                           name="name"
                                           class="form-control @error('name') is-invalid @enderror"
                                           style="border: 1px solid #dee2e6; color: #212529;"
                                           value="{{ old('name', $payrollPeriod->name) }}"
                                           placeholder="Enter period name"
                                           required>
                                </div>
                                @error('name')
                                    <span class="text-danger" style="color: #dc3545 !important;">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>

                        <div class="row">
                            {{-- Month --}}
                            <div class="col-md-4 mb-3">
                                <label class="form-label" style="color: #212529; font-weight: 600;">
                                    <i class="fas fa-calendar mr-1" style="color: #6c757d;"></i> Month <span class="text-danger">*</span>
                                </label>
                                <div class="input-group">
                                    <span class="input-group-text" style="background: #f8f9fa; border: 1px solid #dee2e6; color: #6c757d;">
                                        <i class="fas fa-calendar-alt"></i>
                                    </span>
                                    <select name="month" class="form-control @error('month') is-invalid @enderror" style="border: 1px solid #dee2e6; color: #212529;" required>
                                        @for($m = 1; $m <= 12; $m++)
                                            <option value="{{ $m }}" {{ old('month', $payrollPeriod->month) == $m ? 'selected' : '' }}>
                                                {{ \Carbon\Carbon::create()->month($m)->format('F') }}
                                            </option>
                                        @endfor
                                    </select>
                                </div>
                                @error('month')
                                    <span class="text-danger" style="color: #dc3545 !important;">{{ $message }}</span>
                                @enderror
                            </div>

                            {{-- Year --}}
                            <div class="col-md-4 mb-3">
                                <label class="form-label" style="color: #212529; font-weight: 600;">
                                    <i class="fas fa-calendar-year mr-1" style="color: #6c757d;"></i> Year <span class="text-danger">*</span>
                                </label>
                                <div class="input-group">
                                    <span class="input-group-text" style="background: #f8f9fa; border: 1px solid #dee2e6; color: #6c757d;">
                                        <i class="fas fa-calendar"></i>
                                    </span>
                                    <select name="year" class="form-control @error('year') is-invalid @enderror" style="border: 1px solid #dee2e6; color: #212529;" required>
                                        @for($y = 2020; $y <= 2030; $y++)
                                            <option value="{{ $y }}" {{ old('year', $payrollPeriod->year) == $y ? 'selected' : '' }}>
                                                {{ $y }}
                                            </option>
                                        @endfor
                                    </select>
                                </div>
                                @error('year')
                                    <span class="text-danger" style="color: #dc3545 !important;">{{ $message }}</span>
                                @enderror
                            </div>

                            {{-- Status --}}
                            <div class="col-md-4 mb-3">
                                <label class="form-label" style="color: #212529; font-weight: 600;">
                                    <i class="fas fa-toggle-on mr-1" style="color: #6c757d;"></i> Status
                                </label>
                                <div class="input-group">
                                    <span class="input-group-text" style="background: #f8f9fa; border: 1px solid #dee2e6; color: #6c757d;">
                                        <i class="fas fa-power-off"></i>
                                    </span>
                                    <select name="status" class="form-control @error('status') is-invalid @enderror" style="border: 1px solid #dee2e6; color: #212529;">
                                        <option value="draft" {{ old('status', $payrollPeriod->status) == 'draft' ? 'selected' : '' }}>
                                            Draft
                                        </option>
                                        <option value="processing" {{ old('status', $payrollPeriod->status) == 'processing' ? 'selected' : '' }}>
                                            Processing
                                        </option>
                                        <option value="paid" {{ old('status', $payrollPeriod->status) == 'paid' ? 'selected' : '' }}>
                                            Paid
                                        </option>
                                        <option value="cancelled" {{ old('status', $payrollPeriod->status) == 'cancelled' ? 'selected' : '' }}>
                                            Cancelled
                                        </option>
                                    </select>
                                </div>
                                @error('status')
                                    <span class="text-danger" style="color: #dc3545 !important;">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>

                        {{-- Dates --}}
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label" style="color: #212529; font-weight: 600;">
                                    <i class="fas fa-play mr-1" style="color: #6c757d;"></i> Start Date <span class="text-danger">*</span>
                                </label>
                                <div class="input-group">
                                    <span class="input-group-text" style="background: #f8f9fa; border: 1px solid #dee2e6; color: #6c757d;">
                                        <i class="fas fa-calendar-plus"></i>
                                    </span>
                                    <input type="date"
                                           name="start_date"
                                           class="form-control @error('start_date') is-invalid @enderror"
                                           style="border: 1px solid #dee2e6; color: #212529;"
                                           value="{{ old('start_date', \Carbon\Carbon::parse($payrollPeriod->start_date)->format('Y-m-d')) }}"
                                           required>
                                </div>
                                @error('start_date')
                                    <span class="text-danger" style="color: #dc3545 !important;">{{ $message }}</span>
                                @enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="form-label" style="color: #212529; font-weight: 600;">
                                    <i class="fas fa-stop mr-1" style="color: #6c757d;"></i> End Date <span class="text-danger">*</span>
                                </label>
                                <div class="input-group">
                                    <span class="input-group-text" style="background: #f8f9fa; border: 1px solid #dee2e6; color: #6c757d;">
                                        <i class="fas fa-calendar-minus"></i>
                                    </span>
                                    <input type="date"
                                           name="end_date"
                                           class="form-control @error('end_date') is-invalid @enderror"
                                           style="border: 1px solid #dee2e6; color: #212529;"
                                           value="{{ old('end_date', \Carbon\Carbon::parse($payrollPeriod->end_date)->format('Y-m-d')) }}"
                                           required>
                                </div>
                                @error('end_date')
                                    <span class="text-danger" style="color: #dc3545 !important;">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>

                        {{-- Description --}}
                        <div class="row">
                            <div class="col-md-12 mb-3">
                                <label class="form-label" style="color: #212529; font-weight: 600;">
                                    <i class="fas fa-sticky-note mr-1" style="color: #6c757d;"></i> Description
                                </label>
                                <div class="input-group">
                                    <span class="input-group-text" style="background: #f8f9fa; border: 1px solid #dee2e6; color: #6c757d;">
                                        <i class="fas fa-align-left"></i>
                                    </span>
                                    <textarea name="description"
                                              class="form-control @error('description') is-invalid @enderror"
                                              style="border: 1px solid #dee2e6; color: #212529;"
                                              rows="3"
                                              placeholder="Enter a description for this payroll period...">{{ old('description', $payrollPeriod->description) }}</textarea>
                                </div>
                                @error('description')
                                    <span class="text-danger" style="color: #dc3545 !important;">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>

                        {{-- Payment Date (shown when status is paid) --}}
                        @if($payrollPeriod->status == 'paid' && $payrollPeriod->payment_date)
                            <div class="row">
                                <div class="col-md-12 mb-3">
                                    <div class="alert" style="border-left: 4px solid #28a745; background: #f8f9fa; color: #212529; border: 1px solid #dee2e6;">
                                        <i class="fas fa-check-circle mr-1" style="color: #28a745;"></i>
                                        This payroll period was paid on <strong>{{ \Carbon\Carbon::parse($payrollPeriod->payment_date)->format('d M, Y h:i A') }}</strong>
                                        @if($payrollPeriod->approved_by)
                                            by <strong>{{ $payrollPeriod->approvedBy->name ?? 'N/A' }}</strong>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @endif

                        {{-- Buttons --}}
                        <div class="form-group mt-4">
                            <button type="submit" class="btn bg-primary text-light">
                                <i class="fas fa-save mr-1"></i> Update Payroll Period
                            </button>
                            <a href="{{ route('payroll-periods.index') }}" class="btn" style="background: #f8f9fa; color: #212529; border: 1px solid #dee2e6;">
                                <i class="fas fa-times mr-1"></i> Cancel
                            </a>
                        </div>

                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
$(document).ready(function() {
    // Form validation
    $('#payrollForm').on('submit', function(e) {
        const startDate = $('input[name="start_date"]').val();
        const endDate = $('input[name="end_date"]').val();
        
        if (startDate && endDate && endDate < startDate) {
            e.preventDefault();
            alert('End date must be after or equal to start date.');
            $('input[name="end_date"]').focus();
            return false;
        }
        
        const name = $('input[name="name"]').val().trim();
        if (!name) {
            e.preventDefault();
            alert('Please enter a name for the payroll period.');
            $('input[name="name"]').focus();
            return false;
        }
        
        return true;
    });
});
</script>
@endpush

@push('styles')
<style>
.card {
    border: none;
    border-radius: 12px;
    background: #ffffff !important;
}
.card-header {
    border-radius: 12px 12px 0 0 !important;
    background: #ffffff !important;
}
.card-body {
    background: #ffffff !important;
}
.form-control {
    background: #ffffff !important;
    color: #212529 !important;
}
.form-control:focus {
    border-color: #6c757d !important;
    box-shadow: 0 0 0 0.2rem rgba(108, 117, 125, 0.25) !important;
}
.input-group-text {
    background: #f8f9fa !important;
    border: 1px solid #dee2e6 !important;
    color: #6c757d !important;
}
select.form-control {
    background: #ffffff !important;
    color: #212529 !important;
}
.btn {
    font-weight: 500;
}
.btn:hover {
    opacity: 0.85;
    transform: translateY(-1px);
    transition: all 0.2s;
}
.alert {
    background: #ffffff !important;
    color: #212529 !important;
    border: 1px solid #dee2e6 !important;
}
.text-muted {
    color: #6c757d !important;
}
.text-danger {
    color: #dc3545 !important;
}
.form-label {
    color: #212529 !important;
}
.badge {
    background: #e9ecef !important;
    color: #212529 !important;
}
/* Remove all colored backgrounds */
.alert-info {
    background: #f8f9fa !important;
    border-color: #dee2e6 !important;
    color: #212529 !important;
}
.alert-success {
    background: #f8f9fa !important;
    border-color: #dee2e6 !important;
    color: #212529 !important;
}
.alert-danger {
    background: #f8f9fa !important;
    border-color: #dee2e6 !important;
    color: #212529 !important;
}
/* Ensure all text is dark */
* {
    color: #212529 !important;
}
/* Exception for icons that need specific colors */
.fas, .far, .fab {
    color: #6c757d !important;
}
.btn .fas, .btn .far, .btn .fab {
    color: inherit !important;
}
.text-danger .fas, .text-danger .far {
    color: #dc3545 !important;
}
.text-muted .fas, .text-muted .far {
    color: #6c757d !important;
}
/* Form control text */
.form-control, .form-control::placeholder {
    color: #212529 !important;
}
/* Select options */
select option {
    color: #212529 !important;
    background: #ffffff !important;
}
/* Close button */
.close {
    color: #212529 !important;
}
</style>
@endpush

@endsection