@extends('layouts.master')

@section('title', 'Create Salary Structure')

@section('content')

<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <div class="card shadow-sm">
                <div class="card-header" style="background: linear-gradient(135deg, #e3f2fd 0%, #bbdefb 100%); border-bottom: 2px solid #90caf9;">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-0" style="color: #1565c0;">
                            <i class="fas fa-money-check-alt mr-2"></i> Create Salary Structure
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

                    <form action="{{ route('salary-structures.store') }}" method="POST" id="salaryStructureForm">
                        @csrf

                        <div class="row">
                            {{-- Staff Selection --}}
                            <div class="col-md-6 mb-3">
                                <label class="form-label" style="color: #0d47a1; font-weight: 600;">
                                    <i class="fas fa-user mr-1"></i> Staff <span class="text-danger">*</span>
                                </label>
                                <div class="input-group">
                                    <span class="input-group-text" style="background: #e3f2fd; border: 1px solid #90caf9;">
                                        <i class="fas fa-users" style="color: #1565c0;"></i>
                                    </span>
                                    <select name="staff_id" id="staff_id" class="form-control @error('staff_id') is-invalid @enderror" style="border: 1px solid #90caf9;" required>
                                        <option value="">-- Select Staff --</option>
                                        @foreach($staff as $member)
                                            <option value="{{ $member->id }}"
                                                data-staff="{{ json_encode($member) }}"
                                                {{ old('staff_id') == $member->id ? 'selected' : '' }}>
                                                {{ $member->first_name ?? '' }} {{ $member->last_name ?? '' }}
                                                @if(isset($member->staff_code) && $member->staff_code)
                                                    ({{ $member->staff_code }})
                                                @endif
                                                @if(isset($member->position) && $member->position)
                                                    - {{ $member->position }}
                                                @endif
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                @error('staff_id')
                                    <span class="text-danger">{{ $message }}</span>
                                @enderror
                                <small class="text-muted">Select the staff member for this salary structure.</small>
                            </div>

                            {{-- Staff Info Display --}}
                            <div class="col-md-6 mb-3">
                                <div id="staffInfo" class="card" style="border: 1px solid #bbdefb; background: #f8fbff; display: none;">
                                    <div class="card-body py-2">
                                        <div class="row">
                                            <div class="col-md-6">
                                                <small style="color: #6c757d;">Staff Code:</small>
                                                <p class="font-weight-bold mb-1" style="color: #0d47a1;" id="staffCode">-</p>
                                            </div>
                                            <div class="col-md-6">
                                                <small style="color: #6c757d;">Position:</small>
                                                <p class="font-weight-bold mb-1" style="color: #0d47a1;" id="staffPosition">-</p>
                                            </div>
                                            <div class="col-md-6">
                                                <small style="color: #6c757d;">Department:</small>
                                                <p class="font-weight-bold mb-1" style="color: #0d47a1;" id="staffDepartment">-</p>
                                            </div>
                                            <div class="col-md-6">
                                                <small style="color: #6c757d;">Email:</small>
                                                <p class="font-weight-bold mb-1" style="color: #0d47a1;" id="staffEmail">-</p>
                                            </div>
                                        </div>
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
                                                   class="form-control salary-input @error('basic_salary') is-invalid @enderror"
                                                   style="border: 1px solid #90caf9;"
                                                   value="{{ old('basic_salary') }}"
                                                   min="0"
                                                   step="0.01"
                                                   placeholder="0.00"
                                                   required>
                                        </div>
                                        @error('basic_salary')
                                            <span class="text-danger">{{ $message }}</span>
                                        @enderror
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
                                                   value="{{ old('housing_allowance', 0) }}"
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
                                                   value="{{ old('transport_allowance', 0) }}"
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
                                                   value="{{ old('medical_allowance', 0) }}"
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
                                                   value="{{ old('other_allowance', 0) }}"
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
                                                   value="{{ old('tax', 0) }}"
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
                                                   value="{{ old('ssnit', 0) }}"
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
                                                   value="{{ old('tier2', 0) }}"
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
                                                   value="{{ old('tier3', 0) }}"
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
                                                   value="{{ old('loan_deduction', 0) }}"
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
                                                   value="{{ old('other_deduction', 0) }}"
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

                        {{-- Additional Info --}}
                        <div class="row mt-3">
                            <div class="col-md-6 mb-3">
                                <label class="form-label" style="color: #0d47a1; font-weight: 600;">
                                    <i class="fas fa-calendar-alt mr-1"></i> Effective Date <span class="text-danger">*</span>
                                </label>
                                <div class="input-group">
                                    <span class="input-group-text" style="background: #e3f2fd; border: 1px solid #90caf9;">
                                        <i class="fas fa-calendar" style="color: #1565c0;"></i>
                                    </span>
                                    <input type="date"
                                           name="effective_date"
                                           class="form-control @error('effective_date') is-invalid @enderror"
                                           style="border: 1px solid #90caf9;"
                                           value="{{ old('effective_date', date('Y-m-d')) }}"
                                           required>
                                </div>
                                @error('effective_date')
                                    <span class="text-danger">{{ $message }}</span>
                                @enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="form-label" style="color: #0d47a1; font-weight: 600;">
                                    <i class="fas fa-toggle-on mr-1"></i> Status
                                </label>
                                <div class="input-group">
                                    <span class="input-group-text" style="background: #e3f2fd; border: 1px solid #90caf9;">
                                        <i class="fas fa-power-off" style="color: #1565c0;"></i>
                                    </span>
                                    <select name="status" class="form-control" style="border: 1px solid #90caf9;">
                                        <option value="active" {{ old('status') == 'active' ? 'selected' : '' }}>
                                            <i class="fas fa-check-circle text-success"></i> Active
                                        </option>
                                        <option value="inactive" {{ old('status') == 'inactive' ? 'selected' : '' }}>
                                            <i class="fas fa-times-circle text-danger"></i> Inactive
                                        </option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        {{-- Notes --}}
                        <div class="row">
                            <div class="col-md-12 mb-3">
                                <label class="form-label" style="color: #0d47a1; font-weight: 500;">
                                    <i class="fas fa-sticky-note mr-1"></i> Notes
                                </label>
                                <textarea name="notes" 
                                          class="form-control" 
                                          style="border: 1px solid #90caf9;"
                                          rows="2"
                                          placeholder="Additional notes about this salary structure...">{{ old('notes') }}</textarea>
                            </div>
                        </div>

                        {{-- Buttons --}}
                        <div class="form-group mt-4">
                            <button type="submit" class="btn" style="background: #1565c0; color: white;">
                                <i class="fas fa-save"></i> Save Salary Structure
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
    // Staff selection change handler
    $('#staff_id').on('change', function() {
        const selected = $(this).find('option:selected');
        const staffData = selected.data('staff');
        
        if (staffData) {
            $('#staffCode').text(staffData.staff_code || '-');
            $('#staffPosition').text(staffData.position || '-');
            $('#staffDepartment').text(staffData.department || '-');
            $('#staffEmail').text(staffData.email || '-');
            $('#staffInfo').show();
        } else {
            $('#staffInfo').hide();
        }
    });

    // Trigger change on load if staff is preselected
    @if(old('staff_id'))
        $('#staff_id').trigger('change');
    @endif

    // Salary calculation
    function calculateSalary() {
        const basic = parseFloat($('#basic_salary').val()) || 0;
        const housing = parseFloat($('#housing_allowance').val()) || 0;
        const transport = parseFloat($('#transport_allowance').val()) || 0;
        const medical = parseFloat($('#medical_allowance').val()) || 0;
        const other = parseFloat($('#other_allowance').val()) || 0;
        const tax = parseFloat($('#tax').val()) || 0;
        const ssnit = parseFloat($('#ssnit').val()) || 0;
        const tier2 = parseFloat($('#tier2').val()) || 0;
        const tier3 = parseFloat($('#tier3').val()) || 0;
        const loan = parseFloat($('#loan_deduction').val()) || 0;
        const otherDeduction = parseFloat($('#other_deduction').val()) || 0;

        const gross = basic + housing + transport + medical + other;
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

    // Auto-calculate allowances as percentage of basic salary (optional)
    $('#basic_salary').on('change', function() {
        const basic = parseFloat($(this).val()) || 0;
        
        // Optional: Auto-populate allowances based on percentage
        // Uncomment to enable
        /*
        if (!$('#housing_allowance').val() || $('#housing_allowance').val() == 0) {
            $('#housing_allowance').val((basic * 0.3).toFixed(2));
        }
        if (!$('#transport_allowance').val() || $('#transport_allowance').val() == 0) {
            $('#transport_allowance').val((basic * 0.1).toFixed(2));
        }
        if (!$('#medical_allowance').val() || $('#medical_allowance').val() == 0) {
            $('#medical_allowance').val((basic * 0.05).toFixed(2));
        }
        // Auto-calculate tier 2 and tier 3
        if (!$('#tier2').val() || $('#tier2').val() == 0) {
            $('#tier2').val((basic * 0.05).toFixed(2));
        }
        if (!$('#tier3').val() || $('#tier3').val() == 0) {
            $('#tier3').val((basic * 0.025).toFixed(2));
        }
        calculateSalary();
        */
    });

    // Form validation before submit
    $('#salaryStructureForm').on('submit', function(e) {
        const basic = parseFloat($('#basic_salary').val()) || 0;
        
        if (basic <= 0) {
            e.preventDefault();
            alert('Please enter a valid basic salary.');
            $('#basic_salary').focus();
            return false;
        }

        if (!$('#staff_id').val()) {
            e.preventDefault();
            alert('Please select a staff member.');
            $('#staff_id').focus();
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
.card-header {
    border-radius: 12px 12px 0 0 !important;
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
#staffInfo {
    transition: all 0.3s ease;
}
</style>
@endpush

@endsection