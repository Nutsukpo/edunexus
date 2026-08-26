@extends('layouts.master')

@section('title', 'Edit Salary Structure')

@section('content')

<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <div class="card shadow-sm">
                <div class="card-header" style="background: linear-gradient(135deg, #e3f2fd 0%, #bbdefb 100%); border-bottom: 2px solid #90caf9;">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-0" style="color: #1565c0;">
                            <i class="fas fa-edit mr-2"></i> Edit Salary Structure
                        </h5>
                        <a href="{{ route('salary-structures.index') }}" class="btn btn-sm" style="background: #1565c0; color: white;">
                            <i class="fas fa-arrow-left"></i> Back
                        </a>
                    </div>
                </div>

                <div class="card-body" style="background: #f8fbff;">
                    @if(session('error'))
                        <div class="alert alert-danger alert-dismissible fade show" style="border-left: 4px solid #dc3545;">
                            <i class="fas fa-exclamation-circle mr-1"></i> {{ session('error') }}
                            <button type="button" class="close" data-dismiss="alert">&times;</button>
                        </div>
                    @endif

                    @if($errors->any())
                        <div class="alert alert-danger alert-dismissible fade show" style="border-left: 4px solid #dc3545;">
                            <button type="button" class="close" data-dismiss="alert">&times;</button>
                            <ul class="mb-0">
                                @foreach($errors->all() as $error)
                                    <li><i class="fas fa-times-circle mr-1"></i> {{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form action="{{ route('salary-structures.update', $salaryStructure->id) }}" method="POST">
                        @csrf
                        @method('PUT')

                        {{-- Staff Information --}}
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label" style="color: #1565c0; font-weight: 600;">
                                    <i class="fas fa-user mr-1"></i> Staff
                                </label>
                                <div class="form-control" style="background: #e3f2fd; border: 1px solid #90caf9; color: #0d47a1; font-weight: 500;">
                                    <i class="fas fa-user-circle mr-2"></i>
                                    {{ $salaryStructure->staff->first_name ?? '' }} {{ $salaryStructure->staff->last_name ?? '' }}
                                    @if(isset($salaryStructure->staff->staff_code))
                                        <span class="badge" style="background: #1565c0; color: white; margin-left: 10px;">
                                            {{ $salaryStructure->staff->staff_code }}
                                        </span>
                                    @endif
                                    @if(isset($salaryStructure->staff->position))
                                        <span class="badge" style="background: #42a5f5; color: white; margin-left: 5px;">
                                            {{ $salaryStructure->staff->position }}
                                        </span>
                                    @endif
                                </div>
                                <input type="hidden" name="staff_id" value="{{ $salaryStructure->staff_id }}">
                                <small class="text-muted">Staff member cannot be changed after creation.</small>
                            </div>

                            <div class="col-md-6 mb-3">
                                <div class="row">
                                    <div class="col-6">
                                        <label class="form-label" style="color: #1565c0; font-weight: 600;">
                                            <i class="fas fa-calendar-alt mr-1"></i> Effective Date
                                        </label>
                                        <input type="date"
                                               name="effective_date"
                                               class="form-control"
                                               style="border: 1px solid #90caf9;"
                                               value="{{ old('effective_date', \Carbon\Carbon::parse($salaryStructure->effective_date)->format('Y-m-d')) }}">
                                    </div>
                                    <div class="col-6">
                                        <label class="form-label" style="color: #1565c0; font-weight: 600;">
                                            <i class="fas fa-toggle-on mr-1"></i> Status
                                        </label>
                                        <select name="is_active" class="form-control" style="border: 1px solid #90caf9;">
                                            <option value="1" {{ old('is_active', $salaryStructure->is_active) == 1 ? 'selected' : '' }}>
                                                <i class="fas fa-check-circle text-success"></i> Active
                                            </option>
                                            <option value="0" {{ old('is_active', $salaryStructure->is_active) == 0 ? 'selected' : '' }}>
                                                <i class="fas fa-times-circle text-danger"></i> Inactive
                                            </option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Salary Components --}}
                        <div class="card mt-3" style="border: 1px solid #bbdefb; border-radius: 8px;">
                            <div class="card-header" style="background: #e3f2fd; border-bottom: 1px solid #bbdefb;">
                                <h6 class="mb-0" style="color: #1565c0;">
                                    <i class="fas fa-calculator mr-2"></i> Salary Components
                                </h6>
                            </div>
                            <div class="card-body" style="background: #f8fbff;">
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label" style="color: #0d47a1; font-weight: 500;">
                                            Basic Salary <span class="text-danger">*</span>
                                        </label>
                                        <div class="input-group">
                                            <span class="input-group-text" style="background: #e3f2fd; border: 1px solid #90caf9;">
                                                <i class="fas fa-money-bill-wave" style="color: #1565c0;"></i>
                                            </span>
                                            <input type="number"
                                                   name="basic_salary"
                                                   id="basic_salary"
                                                   class="form-control salary-input"
                                                   style="border: 1px solid #90caf9;"
                                                   value="{{ old('basic_salary', $salaryStructure->basic_salary) }}"
                                                   min="0"
                                                   step="0.01"
                                                   placeholder="0.00"
                                                   required>
                                        </div>
                                    </div>

                                    <div class="col-md-6 mb-3">
                                        <label class="form-label" style="color: #0d47a1; font-weight: 500;">
                                            Housing Allowance
                                        </label>
                                        <div class="input-group">
                                            <span class="input-group-text" style="background: #e3f2fd; border: 1px solid #90caf9;">
                                                <i class="fas fa-home" style="color: #1565c0;"></i>
                                            </span>
                                            <input type="number"
                                                   name="housing_allowance"
                                                   id="housing_allowance"
                                                   class="form-control allowance-input"
                                                   style="border: 1px solid #90caf9;"
                                                   value="{{ old('housing_allowance', $salaryStructure->housing_allowance ?? 0) }}"
                                                   min="0"
                                                   step="0.01"
                                                   placeholder="0.00">
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-4 mb-3">
                                        <label class="form-label" style="color: #0d47a1; font-weight: 500;">
                                            Transport Allowance
                                        </label>
                                        <div class="input-group">
                                            <span class="input-group-text" style="background: #e3f2fd; border: 1px solid #90caf9;">
                                                <i class="fas fa-bus" style="color: #1565c0;"></i>
                                            </span>
                                            <input type="number"
                                                   name="transport_allowance"
                                                   id="transport_allowance"
                                                   class="form-control allowance-input"
                                                   style="border: 1px solid #90caf9;"
                                                   value="{{ old('transport_allowance', $salaryStructure->transport_allowance ?? 0) }}"
                                                   min="0"
                                                   step="0.01"
                                                   placeholder="0.00">
                                        </div>
                                    </div>

                                    <div class="col-md-4 mb-3">
                                        <label class="form-label" style="color: #0d47a1; font-weight: 500;">
                                            Medical Allowance
                                        </label>
                                        <div class="input-group">
                                            <span class="input-group-text" style="background: #e3f2fd; border: 1px solid #90caf9;">
                                                <i class="fas fa-heartbeat" style="color: #1565c0;"></i>
                                            </span>
                                            <input type="number"
                                                   name="medical_allowance"
                                                   id="medical_allowance"
                                                   class="form-control allowance-input"
                                                   style="border: 1px solid #90caf9;"
                                                   value="{{ old('medical_allowance', $salaryStructure->medical_allowance ?? 0) }}"
                                                   min="0"
                                                   step="0.01"
                                                   placeholder="0.00">
                                        </div>
                                    </div>

                                    <div class="col-md-4 mb-3">
                                        <label class="form-label" style="color: #0d47a1; font-weight: 500;">
                                            Responsibility Allowance
                                        </label>
                                        <div class="input-group">
                                            <span class="input-group-text" style="background: #e3f2fd; border: 1px solid #90caf9;">
                                                <i class="fas fa-user-tie" style="color: #1565c0;"></i>
                                            </span>
                                            <input type="number"
                                                   name="responsibility_allowance"
                                                   id="responsibility_allowance"
                                                   class="form-control allowance-input"
                                                   style="border: 1px solid #90caf9;"
                                                   value="{{ old('responsibility_allowance', $salaryStructure->responsibility_allowance ?? 0) }}"
                                                   min="0"
                                                   step="0.01"
                                                   placeholder="0.00">
                                        </div>
                                    </div>

                                    <div class="col-md-4 mb-3">
                                        <label class="form-label" style="color: #0d47a1; font-weight: 500;">
                                            Other Allowance
                                        </label>
                                        <div class="input-group">
                                            <span class="input-group-text" style="background: #e3f2fd; border: 1px solid #90caf9;">
                                                <i class="fas fa-plus-circle" style="color: #1565c0;"></i>
                                            </span>
                                            <input type="number"
                                                   name="other_allowance"
                                                   id="other_allowance"
                                                   class="form-control allowance-input"
                                                   style="border: 1px solid #90caf9;"
                                                   value="{{ old('other_allowance', $salaryStructure->other_allowance ?? 0) }}"
                                                   min="0"
                                                   step="0.01"
                                                   placeholder="0.00">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Deductions --}}
                        <div class="card mt-3" style="border: 1px solid #bbdefb; border-radius: 8px;">
                            <div class="card-header" style="background: #e3f2fd; border-bottom: 1px solid #bbdefb;">
                                <h6 class="mb-0" style="color: #1565c0;">
                                    <i class="fas fa-minus-circle mr-2"></i> Deductions
                                </h6>
                            </div>
                            <div class="card-body" style="background: #f8fbff;">
                                <div class="row">
                                    <div class="col-md-4 mb-3">
                                        <label class="form-label" style="color: #0d47a1; font-weight: 500;">
                                            PAYE Tax
                                        </label>
                                        <div class="input-group">
                                            <span class="input-group-text" style="background: #e3f2fd; border: 1px solid #90caf9;">
                                                <i class="fas fa-percent" style="color: #1565c0;"></i>
                                            </span>
                                            <input type="number"
                                                   name="tax"
                                                   id="tax"
                                                   class="form-control deduction-input"
                                                   style="border: 1px solid #90caf9;"
                                                   value="{{ old('tax', $salaryStructure->tax ?? 0) }}"
                                                   min="0"
                                                   step="0.01"
                                                   placeholder="0.00">
                                        </div>
                                    </div>

                                    <div class="col-md-4 mb-3">
                                        <label class="form-label" style="color: #0d47a1; font-weight: 500;">
                                            SSNIT
                                        </label>
                                        <div class="input-group">
                                            <span class="input-group-text" style="background: #e3f2fd; border: 1px solid #90caf9;">
                                                <i class="fas fa-shield-alt" style="color: #1565c0;"></i>
                                            </span>
                                            <input type="number"
                                                   name="ssnit"
                                                   id="ssnit"
                                                   class="form-control deduction-input"
                                                   style="border: 1px solid #90caf9;"
                                                   value="{{ old('ssnit', $salaryStructure->ssnit ?? 0) }}"
                                                   min="0"
                                                   step="0.01"
                                                   placeholder="0.00">
                                        </div>
                                    </div>

                                    <div class="col-md-4 mb-3">
                                        <label class="form-label" style="color: #0d47a1; font-weight: 500;">
                                            Tier 2 Pension
                                        </label>
                                        <div class="input-group">
                                            <span class="input-group-text" style="background: #e3f2fd; border: 1px solid #90caf9;">
                                                <i class="fas fa-piggy-bank" style="color: #1565c0;"></i>
                                            </span>
                                            <input type="number"
                                                   name="tier2"
                                                   id="tier2"
                                                   class="form-control deduction-input"
                                                   style="border: 1px solid #90caf9;"
                                                   value="{{ old('tier2', $salaryStructure->tier2 ?? 0) }}"
                                                   min="0"
                                                   step="0.01"
                                                   placeholder="0.00">
                                        </div>
                                    </div>

                                    <div class="col-md-4 mb-3">
                                        <label class="form-label" style="color: #0d47a1; font-weight: 500;">
                                            Tier 3 Pension
                                        </label>
                                        <div class="input-group">
                                            <span class="input-group-text" style="background: #e3f2fd; border: 1px solid #90caf9;">
                                                <i class="fas fa-hand-holding-usd" style="color: #1565c0;"></i>
                                            </span>
                                            <input type="number"
                                                   name="tier3"
                                                   id="tier3"
                                                   class="form-control deduction-input"
                                                   style="border: 1px solid #90caf9;"
                                                   value="{{ old('tier3', $salaryStructure->tier3 ?? 0) }}"
                                                   min="0"
                                                   step="0.01"
                                                   placeholder="0.00">
                                        </div>
                                    </div>

                                    <div class="col-md-4 mb-3">
                                        <label class="form-label" style="color: #0d47a1; font-weight: 500;">
                                            Loan Deduction
                                        </label>
                                        <div class="input-group">
                                            <span class="input-group-text" style="background: #e3f2fd; border: 1px solid #90caf9;">
                                                <i class="fas fa-hand-holding-heart" style="color: #1565c0;"></i>
                                            </span>
                                            <input type="number"
                                                   name="loan_deduction"
                                                   id="loan_deduction"
                                                   class="form-control deduction-input"
                                                   style="border: 1px solid #90caf9;"
                                                   value="{{ old('loan_deduction', $salaryStructure->loan_deduction ?? 0) }}"
                                                   min="0"
                                                   step="0.01"
                                                   placeholder="0.00">
                                        </div>
                                    </div>

                                    <div class="col-md-4 mb-3">
                                        <label class="form-label" style="color: #0d47a1; font-weight: 500;">
                                            Other Deduction
                                        </label>
                                        <div class="input-group">
                                            <span class="input-group-text" style="background: #e3f2fd; border: 1px solid #90caf9;">
                                                <i class="fas fa-minus-circle" style="color: #1565c0;"></i>
                                            </span>
                                            <input type="number"
                                                   name="other_deduction"
                                                   id="other_deduction"
                                                   class="form-control deduction-input"
                                                   style="border: 1px solid #90caf9;"
                                                   value="{{ old('other_deduction', $salaryStructure->other_deduction ?? 0) }}"
                                                   min="0"
                                                   step="0.01"
                                                   placeholder="0.00">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Summary --}}
                        <div class="card mt-3" style="border: 1px solid #bbdefb; border-radius: 8px;">
                            <div class="card-header" style="background: #e3f2fd; border-bottom: 1px solid #bbdefb;">
                                <h6 class="mb-0" style="color: #1565c0;">
                                    <i class="fas fa-chart-pie mr-2"></i> Salary Summary
                                </h6>
                            </div>
                            <div class="card-body" style="background: #f8fbff;">
                                <div class="row text-center">
                                    <div class="col-md-4">
                                        <div class="p-3" style="background: #e3f2fd; border-radius: 8px;">
                                            <small style="color: #0d47a1;">Gross Salary</small>
                                            <h4 class="font-weight-bold" style="color: #1565c0;" id="grossSalary">GHS 0.00</h4>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="p-3" style="background: #ffebee; border-radius: 8px;">
                                            <small style="color: #c62828;">Total Deductions</small>
                                            <h4 class="font-weight-bold" style="color: #c62828;" id="totalDeductions">GHS 0.00</h4>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="p-3" style="background: #e8f5e9; border-radius: 8px;">
                                            <small style="color: #2e7d32;">Net Salary</small>
                                            <h4 class="font-weight-bold" style="color: #2e7d32;" id="netSalary">GHS 0.00</h4>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Notes --}}
                        <div class="row mt-3">
                            <div class="col-md-12 mb-3">
                                <label class="form-label" style="color: #0d47a1; font-weight: 500;">
                                    <i class="fas fa-sticky-note mr-1"></i> Notes
                                </label>
                                <textarea name="notes" 
                                          class="form-control" 
                                          style="border: 1px solid #90caf9;"
                                          rows="2"
                                          placeholder="Additional notes about this salary structure...">{{ old('notes', $salaryStructure->notes) }}</textarea>
                            </div>
                        </div>

                        {{-- Buttons --}}
                        <div class="form-group mt-4">
                            <button type="submit" class="btn" style="background: #1565c0; color: white;">
                                <i class="fas fa-save"></i> Update Salary Structure
                            </button>
                            <a href="{{ route('salary-structures.index') }}" class="btn" style="background: #e0e0e0; color: #333;">
                                <i class="fas fa-times"></i> Cancel
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
    // Salary calculation
    function calculateSalary() {
        const basic = parseFloat($('#basic_salary').val()) || 0;
        const housing = parseFloat($('#housing_allowance').val()) || 0;
        const transport = parseFloat($('#transport_allowance').val()) || 0;
        const medical = parseFloat($('#medical_allowance').val()) || 0;
        const responsibility = parseFloat($('#responsibility_allowance').val()) || 0;
        const other = parseFloat($('#other_allowance').val()) || 0;
        const tax = parseFloat($('#tax').val()) || 0;
        const ssnit = parseFloat($('#ssnit').val()) || 0;
        const tier2 = parseFloat($('#tier2').val()) || 0;
        const tier3 = parseFloat($('#tier3').val()) || 0;
        const loan = parseFloat($('#loan_deduction').val()) || 0;
        const otherDeduction = parseFloat($('#other_deduction').val()) || 0;

        const gross = basic + housing + transport + medical + responsibility + other;
        const deductions = tax + ssnit + tier2 + tier3 + loan + otherDeduction;
        const net = gross - deductions;

        $('#grossSalary').text('GHS ' + gross.toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ','));
        $('#totalDeductions').text('GHS ' + deductions.toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ','));
        $('#netSalary').text('GHS ' + net.toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ','));
    }

    // Bind calculation to all input changes
    $('.salary-input, .allowance-input, .deduction-input').on('input change', function() {
        calculateSalary();
    });

    // Initial calculation
    calculateSalary();

    // Form validation
    $('#salaryStructureForm').on('submit', function(e) {
        const basic = parseFloat($('#basic_salary').val()) || 0;
        
        if (basic <= 0) {
            e.preventDefault();
            alert('Please enter a valid basic salary.');
            $('#basic_salary').focus();
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
}
.form-control:focus {
    border-color: #42a5f5 !important;
    box-shadow: 0 0 0 0.2rem rgba(66, 165, 245, 0.25) !important;
}
.input-group-text {
    background: #e3f2fd;
    border: 1px solid #90caf9;
}
.btn:hover {
    opacity: 0.9;
    transform: translateY(-1px);
    transition: all 0.2s;
}
select.form-control {
    background: white;
}
</style>
@endpush

@endsection