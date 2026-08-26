@extends('layouts.master')

@section('title', 'Generate Payslip')

@section('content')
<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-md-11">
            <div class="card shadow-lg">
                <div class="card-header bg-gradient-primary text-white d-flex justify-content-between align-items-center flex-wrap gap-2">
                    <h4 class="mb-0">
                        <i class="fas fa-file-invoice"></i> Generate Payslip
                    </h4>
                    <a href="{{ route('payslips.index') }}" class="btn btn-light btn-sm">
                        <i class="fas fa-arrow-left"></i> Back to List
                    </a>
                </div>

                <div class="card-body">
                    @if(session('error'))
                        <div class="alert alert-danger">
                            <i class="fas fa-exclamation-circle"></i> {{ session('error') }}
                        </div>
                    @endif

                    <form action="{{ route('payslips.store') }}" method="POST" id="payslipForm">
                        @csrf

                        <!-- Staff Selection -->
                        <div class="card mb-4">
                            <div class="card-header bg-light">
                                <h6 class="mb-0"><i class="fas fa-user"></i> Staff & Period Information</h6>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group mb-3">
                                            <label class="fw-bold text-dark">
                                                Payroll Period <span class="text-danger">*</span>
                                            </label>
                                            <select name="payroll_period_id" id="payroll_period_id" 
                                                    class="form-select form-select-lg @error('payroll_period_id') is-invalid @enderror" 
                                                    required>
                                                <option value="">-- Select Payroll Period --</option>
                                                @foreach($availablePayrollPeriods as $period)
                                                    <option value="{{ $period->id }}" 
                                                            data-working-days="{{ $period->working_days ?? cal_days_in_month(CAL_GREGORIAN, $period->month, $period->year) }}"
                                                            data-overtime-hours="{{ $period->overtime_hours ?? 0 }}"
                                                            {{ old('payroll_period_id') == $period->id ? 'selected' : '' }}>
                                                        {{ $period->month_name }} {{ $period->year }}
                                                        @if($period->working_days)
                                                            ({{ $period->working_days }} days)
                                                        @endif
                                                    </option>
                                                @endforeach
                                            </select>
                                            @error('payroll_period_id')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                            <small class="text-muted">
                                                <i class="fas fa-calendar-alt"></i> 
                                                Select the payroll period for this payslip
                                            </small>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group mb-3">
                                            <label class="fw-bold text-dark">
                                                Select Staff Member <span class="text-danger">*</span>
                                            </label>
                                            <select name="staff_id" id="staff_id" 
                                                    class="form-select form-select-lg @error('staff_id') is-invalid @enderror" 
                                                    required>
                                                <option value="">-- Select Staff Member --</option>
                                                @foreach($staffs as $staff)
                                                    <option value="{{ $staff->id }}" 
                                                            data-has-structure="{{ $staff->has_active_structure ? 'true' : 'false' }}"
                                                            {{ old('staff_id') == $staff->id ? 'selected' : '' }}>
                                                        {{ $staff->first_name }} {{ $staff->last_name }} 
                                                        @if($staff->position)
                                                            - {{ $staff->position }}
                                                        @endif
                                                        @if($staff->has_active_structure)
                                                            <span class="badge bg-success">Active Structure</span>
                                                        @else
                                                            <span class="badge bg-warning">No Structure</span>
                                                        @endif
                                                    </option>
                                                @endforeach
                                            </select>
                                            @error('staff_id')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                            <small class="text-muted">
                                                <i class="fas fa-info-circle"></i> 
                                                Select a staff member with an active salary structure
                                            </small>
                                        </div>
                                    </div>
                                </div>

                                <!-- Staff Details -->
                                <div id="staffInfoCard" class="mt-3" style="display: none;">
                                    <div class="alert alert-info">
                                        <div class="row">
                                            <div class="col-md-3 col-sm-6">
                                                <strong>Name:</strong> <span id="staffName">-</span>
                                            </div>
                                            <div class="col-md-3 col-sm-6">
                                                <strong>Position:</strong> <span id="staffPosition">-</span>
                                            </div>
                                            <div class="col-md-3 col-sm-6">
                                                <strong>Department:</strong> <span id="staffDepartment">-</span>
                                            </div>
                                            <div class="col-md-3 col-sm-6">
                                                <strong>Email:</strong> <span id="staffEmail">-</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Period Details -->
                                <div id="periodInfoCard" class="mt-3" style="display: none;">
                                    <div class="alert alert-secondary">
                                        <div class="row">
                                            <div class="col-md-4 col-sm-6">
                                                <strong>Working Days:</strong> <span id="workingDays">-</span>
                                            </div>
                                            <div class="col-md-4 col-sm-6">
                                                <strong>Overtime Hours:</strong> <span id="overtimeHours">-</span>
                                            </div>
                                            <div class="col-md-4 col-sm-12">
                                                <strong>Total Days:</strong> <span id="totalDays">-</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Hidden fields -->
                        <input type="hidden" name="total_earnings" id="total_earnings_input" value="0">
                        <input type="hidden" name="total_deductions" id="total_deductions_input" value="0">
                        <input type="hidden" name="net_pay" id="net_pay_input" value="0">
                        <input type="hidden" name="breakdown" id="breakdown_input" value="">

                        <!-- Salary Structure -->
                        <div class="card mb-4">
                            <div class="card-header bg-light">
                                <h6 class="mb-0"><i class="fas fa-money-bill-wave"></i> Salary Structure</h6>
                            </div>
                            <div class="card-body">
                                <div id="structureStatus" class="alert alert-warning">
                                    <i class="fas fa-info-circle"></i> 
                                    Please select a staff member and payroll period
                                </div>

                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="form-group mb-3">
                                            <label class="fw-bold">Basic Salary <span class="text-danger">*</span></label>
                                            <div class="input-group">
                                                <span class="input-group-text bg-light">$</span>
                                                <input type="number" step="0.01" name="basic_salary" id="basic_salary" 
                                                       class="form-control" value="0" readonly>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group mb-3">
                                            <label class="fw-bold">Allowances</label>
                                            <div class="input-group">
                                                <span class="input-group-text bg-light">$</span>
                                                <input type="number" step="0.01" name="allowances" id="allowances" 
                                                       class="form-control" value="0" readonly>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group mb-3">
                                            <label class="fw-bold">Total Allowances</label>
                                            <div class="input-group">
                                                <span class="input-group-text bg-light">$</span>
                                                <input type="number" step="0.01" name="total_allowances" id="total_allowances" 
                                                       class="form-control" value="0" readonly>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group mb-3">
                                            <label class="fw-bold">Bonus</label>
                                            <div class="input-group">
                                                <span class="input-group-text bg-light">$</span>
                                                <input type="number" step="0.01" name="bonus" id="bonus" 
                                                       class="form-control" value="0" readonly>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group mb-3">
                                            <label class="fw-bold">Overtime</label>
                                            <div class="input-group">
                                                <span class="input-group-text bg-light">$</span>
                                                <input type="number" step="0.01" name="overtime" id="overtime" 
                                                       class="form-control" value="0" readonly>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group mb-3">
                                            <label class="fw-bold">Total Earnings</label>
                                            <div class="input-group">
                                                <span class="input-group-text bg-light">$</span>
                                                <input type="number" step="0.01" id="total_earnings_display" 
                                                       class="form-control text-success fw-bold" value="0.00" readonly>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <hr>

                                <div class="row">
                                    <div class="col-md-3">
                                        <div class="form-group mb-3">
                                            <label class="fw-bold">Tax</label>
                                            <div class="input-group">
                                                <span class="input-group-text bg-light">$</span>
                                                <input type="number" step="0.01" name="tax" id="tax" 
                                                       class="form-control" value="0" readonly>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-group mb-3">
                                            <label class="fw-bold">Pension (SSNIT)</label>
                                            <div class="input-group">
                                                <span class="input-group-text bg-light">$</span>
                                                <input type="number" step="0.01" name="pension" id="pension" 
                                                       class="form-control" value="0" readonly>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-group mb-3">
                                            <label class="fw-bold">Tier 2</label>
                                            <div class="input-group">
                                                <span class="input-group-text bg-light">$</span>
                                                <input type="number" step="0.01" name="tier2" id="tier2" 
                                                       class="form-control" value="0" readonly>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-group mb-3">
                                            <label class="fw-bold">Tier 3</label>
                                            <div class="input-group">
                                                <span class="input-group-text bg-light">$</span>
                                                <input type="number" step="0.01" name="tier3" id="tier3" 
                                                       class="form-control" value="0" readonly>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-group mb-3">
                                            <label class="fw-bold">Insurance</label>
                                            <div class="input-group">
                                                <span class="input-group-text bg-light">$</span>
                                                <input type="number" step="0.01" name="insurance" id="insurance" 
                                                       class="form-control" value="0" readonly>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-group mb-3">
                                            <label class="fw-bold">Loans</label>
                                            <div class="input-group">
                                                <span class="input-group-text bg-light">$</span>
                                                <input type="number" step="0.01" name="loans" id="loans" 
                                                       class="form-control" value="0" readonly>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-group mb-3">
                                            <label class="fw-bold">Other Deductions</label>
                                            <div class="input-group">
                                                <span class="input-group-text bg-light">$</span>
                                                <input type="number" step="0.01" name="other_deductions" id="other_deductions" 
                                                       class="form-control" value="0" readonly>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-group mb-3">
                                            <label class="fw-bold">Total Deductions</label>
                                            <div class="input-group">
                                                <span class="input-group-text bg-light">$</span>
                                                <input type="number" step="0.01" id="total_deductions_display" 
                                                       class="form-control text-danger fw-bold" value="0.00" readonly>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Summary -->
                        <div id="summaryCard" class="card mb-4" style="display: none;">
                            <div class="card-header bg-gradient-success text-white">
                                <h6 class="mb-0"><i class="fas fa-calculator"></i> Payslip Summary</h6>
                            </div>
                            <div class="card-body">
                                <div class="row text-center">
                                    <div class="col-md-4">
                                        <div class="p-3 border rounded bg-light">
                                            <h6 class="text-muted">Total Earnings</h6>
                                            <h3 class="text-success" id="summaryTotalEarnings">$0.00</h3>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="p-3 border rounded bg-light">
                                            <h6 class="text-muted">Total Deductions</h6>
                                            <h3 class="text-danger" id="summaryTotalDeductions">$0.00</h3>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="p-3 border rounded bg-primary text-white">
                                            <h6 class="text-white-50">Net Pay</h6>
                                            <h2 class="text-white" id="summaryNetPay">$0.00</h2>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Notes -->
                        <div class="card mb-4">
                            <div class="card-header bg-light">
                                <h6 class="mb-0"><i class="fas fa-pen"></i> Additional Information</h6>
                            </div>
                            <div class="card-body">
                                <textarea name="notes" class="form-control" rows="3" 
                                          placeholder="Add any additional notes or remarks...">{{ old('notes') }}</textarea>
                            </div>
                        </div>

                        <!-- Submit Buttons -->
                        <div class="d-flex flex-wrap justify-content-between align-items-center gap-2">
                            <div class="d-flex flex-wrap gap-2">
                                <button type="submit" class="btn btn-primary btn-lg" id="submitBtn" >
                                    <i class="fas fa-save"></i> Generate Payslip
                                </button>
                                <button type="reset" class="btn btn-warning btn-lg" id="resetBtn">
                                    <i class="fas fa-undo"></i> Reset
                                </button>
                            </div>
                            <a href="{{ route('payslips.index') }}" class="btn btn-secondary btn-lg">
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
    console.log('Payslip generation page loaded');

    // Staff selection change
    $('#staff_id').on('change', function() {
        var staffId = $(this).val();
        var hasStructure = $(this).find('option:selected').data('has-structure');
        var payrollPeriodId = $('#payroll_period_id').val();
        
        console.log('Staff selected:', staffId, 'Has structure:', hasStructure);
        
        if (!staffId) {
            resetForm();
            return;
        }
        
        if (hasStructure === 'false') {
            showNoStructureAlert();
            resetForm();
            return;
        }
        
        if (!payrollPeriodId) {
            toastr.warning('Please select a payroll period first.');
            return;
        }
        
        fetchSalaryStructure(staffId, payrollPeriodId);
    });

    // Payroll period change
    $('#payroll_period_id').on('change', function() {
        var periodId = $(this).val();
        var staffId = $('#staff_id').val();
        
        console.log('Payroll period selected:', periodId);
        
        if (!periodId) {
            $('#periodInfoCard').fadeOut();
            return;
        }
        
        var selected = $(this).find('option:selected');
        var workingDays = selected.data('working-days');
        var overtimeHours = selected.data('overtime-hours');
        
        $('#workingDays').text(workingDays + ' days');
        $('#overtimeHours').text(overtimeHours + ' hours');
        $('#periodInfoCard').fadeIn();
        
        if (staffId) {
            var hasStructure = $('#staff_id').find('option:selected').data('has-structure');
            if (hasStructure === 'true') {
                fetchSalaryStructure(staffId, periodId);
            }
        }
    });

    // Fetch salary structure
    function fetchSalaryStructure(staffId, payrollPeriodId) {
        console.log('Fetching salary structure...');
        
        $('#structureStatus').removeClass('alert-warning alert-success alert-danger')
            .addClass('alert-info')
            .html('<i class="fas fa-spinner fa-spin"></i> Loading salary structure...');
        
        $.ajax({
            url: "{{ route('payslips.staff-salary-data') }}",
            method: 'GET',
            data: {
                staff_id: staffId,
                payroll_period_id: payrollPeriodId
            },
            dataType: 'json',
            timeout: 30000,
            success: function(response) {
                console.log('AJAX Success:', response);
                
                if (response.success === true) {
                    populateData(response);
                    toastr.success('Salary structure loaded!');
                    $('#submitBtn').prop('disabled', false);
                } else {
                    showError(response.message || 'Failed to load salary structure.');
                    resetForm();
                }
            },
            error: function(xhr) {
                console.error('AJAX Error:', xhr.status, xhr.responseText);
                var msg = 'Failed to load salary structure. ';
                if (xhr.status === 404) msg += 'No structure found.';
                else if (xhr.status === 0) msg += 'Network error.';
                else msg += 'Please try again.';
                showError(msg);
                resetForm();
            }
        });
    }

    // Populate data
    function populateData(data) {
        console.log('Populating data...');
        
        // Staff info
        if (data.staff) {
            $('#staffName').text((data.staff.first_name || '') + ' ' + (data.staff.last_name || ''));
            $('#staffPosition').text(data.staff.position || 'N/A');
            $('#staffDepartment').text(data.staff.department || 'N/A');
            $('#staffEmail').text(data.staff.email || 'N/A');
            $('#staffInfoCard').fadeIn();
        }

        // Salary data
        var basicSalary = parseFloat(data.basic_salary) || 0;
        var allowances = parseFloat(data.allowances) || 0;
        var bonuses = parseFloat(data.bonuses) || 0;
        var overtimePay = parseFloat(data.overtime_pay) || 0;
        var totalEarnings = parseFloat(data.total_earnings) || 0;
        
        $('#basic_salary').val(basicSalary.toFixed(2));
        $('#allowances').val(allowances.toFixed(2));
        $('#total_allowances').val(allowances.toFixed(2));
        $('#bonus').val(bonuses.toFixed(2));
        $('#overtime').val(overtimePay.toFixed(2));
        $('#total_earnings_display').val(totalEarnings.toFixed(2));
        $('#total_earnings_input').val(totalEarnings);

        // Deductions
        var tax = parseFloat(data.tax) || 0;
        var pension = parseFloat(data.pension) || 0;
        var tier2 = parseFloat(data.tier2) || 0;
        var tier3 = parseFloat(data.tier3) || 0;
        var healthInsurance = parseFloat(data.health_insurance) || 0;
        var loanDeductions = parseFloat(data.loan_deductions) || 0;
        var otherDeductions = parseFloat(data.other_deductions) || 0;
        var totalDeductions = parseFloat(data.total_deductions) || 0;
        
        $('#tax').val(tax.toFixed(2));
        $('#pension').val(pension.toFixed(2));
        $('#tier2').val(tier2.toFixed(2));
        $('#tier3').val(tier3.toFixed(2));
        $('#insurance').val(healthInsurance.toFixed(2));
        $('#loans').val(loanDeductions.toFixed(2));
        $('#other_deductions').val(otherDeductions.toFixed(2));
        $('#total_deductions_display').val(totalDeductions.toFixed(2));
        $('#total_deductions_input').val(totalDeductions);

        // Net pay
        var netPay = parseFloat(data.net_pay) || 0;
        $('#net_pay_input').val(netPay);

        // Store breakdown
        var breakdown = {
            basic_salary: basicSalary,
            allowances: allowances,
            allowances_breakdown: data.allowances_breakdown || {},
            bonuses: bonuses,
            bonus_breakdown: data.bonus_breakdown || {},
            overtime_pay: overtimePay,
            overtime_hours: data.overtime_hours || 0,
            tax: tax,
            pension: pension,
            tier2: tier2,
            tier3: tier3,
            health_insurance: healthInsurance,
            loan_deductions: loanDeductions,
            other_deductions: otherDeductions,
            working_days: data.working_days || 0,
            days_in_month: data.days_in_month || 0,
            daily_rate: data.daily_rate || 0
        };
        $('#breakdown_input').val(JSON.stringify(breakdown));

        // Update summary
        updateSummary(totalEarnings, totalDeductions, netPay);

        var staffName = data.staff ? (data.staff.first_name || '') + ' ' + (data.staff.last_name || '') : 'Staff';
        $('#structureStatus').removeClass('alert-warning alert-info alert-danger')
            .addClass('alert-success')
            .html('<i class="fas fa-check-circle"></i> Salary structure loaded for ' + staffName);
    }

    // Update summary
    function updateSummary(totalEarnings, totalDeductions, netPay) {
        $('#summaryTotalEarnings').text('$' + totalEarnings.toFixed(2));
        $('#summaryTotalDeductions').text('$' + totalDeductions.toFixed(2));
        $('#summaryNetPay').text('$' + netPay.toFixed(2));
        $('#summaryCard').fadeIn();
    }

    // Show error
    function showError(message) {
        $('#structureStatus').removeClass('alert-warning alert-info alert-success')
            .addClass('alert-danger')
            .html('<i class="fas fa-exclamation-circle"></i> ' + message);
        $('#submitBtn').prop('disabled', true);
    }

    // Show no structure alert
    function showNoStructureAlert() {
        $('#structureStatus').removeClass('alert-warning alert-info alert-success')
            .addClass('alert-danger')
            .html('<i class="fas fa-exclamation-circle"></i> No active salary structure found.');
        $('#submitBtn').prop('disabled', true);
    }

    // Reset form
    function resetForm() {
        $('#basic_salary, #allowances, #total_allowances, #bonus, #overtime').val('0');
        $('#tax, #pension, #tier2, #tier3, #insurance, #loans, #other_deductions').val('0');
        $('#total_earnings_display, #total_deductions_display').val('0.00');
        $('#total_earnings_input, #total_deductions_input, #net_pay_input').val('0');
        $('#summaryTotalEarnings, #summaryTotalDeductions, #summaryNetPay').text('$0.00');
        $('#summaryCard').fadeOut();
        $('#staffInfoCard').fadeOut();
        $('#submitBtn').prop('disabled', true);
        $('#structureStatus').removeClass('alert-success alert-danger alert-info')
            .addClass('alert-warning')
            .html('<i class="fas fa-info-circle"></i> Please select a staff member and payroll period');
    }

    // Reset button
    $('#resetBtn').on('click', function(e) {
        e.preventDefault();
        resetForm();
        $('#staff_id').val('').trigger('change');
        $('#payroll_period_id').val('').trigger('change');
        toastr.info('Form has been reset');
    });

    // Form validation
    $('#payslipForm').on('submit', function(e) {
        if (!$('#staff_id').val()) {
            e.preventDefault();
            toastr.error('Please select a staff member.');
            return false;
        }
        if (!$('#payroll_period_id').val()) {
            e.preventDefault();
            toastr.error('Please select a payroll period.');
            return false;
        }
        if (!$('#basic_salary').val() || parseFloat($('#basic_salary').val()) <= 0) {
            e.preventDefault();
            toastr.error('Invalid basic salary.');
            return false;
        }
        return true;
    });

    // Auto-fetch if pre-selected
    if ($('#staff_id').val() && $('#payroll_period_id').val()) {
        var hasStructure = $('#staff_id').find('option:selected').data('has-structure');
        if (hasStructure === 'true') {
            setTimeout(function() {
                fetchSalaryStructure($('#staff_id').val(), $('#payroll_period_id').val());
            }, 500);
        }
    }
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

@push('styles')
<style>
    .bg-gradient-primary {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    }
    .bg-gradient-success {
        background: linear-gradient(135deg, #84fab0 0%, #8fd3f4 100%);
    }
    .form-select-lg {
        font-size: 1.1rem;
    }
    .input-group-text {
        min-width: 38px;
        justify-content: center;
    }
    .card {
        border-radius: 10px;
        overflow: hidden;
    }
    .btn-primary {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        border: none;
    }
    .btn-primary:hover {
        transform: translateY(-2px);
        box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
    }
    input[readonly] {
        background-color: #f8f9fa;
        cursor: default;
    }
    input[readonly]:focus {
        box-shadow: none;
        border-color: #ced4da;
    }
    
    @media (max-width: 576px) {
        .btn-lg {
            padding: 8px 16px;
            font-size: 0.9rem;
        }
        .d-flex.flex-wrap.justify-content-between {
            flex-direction: column !important;
            align-items: stretch !important;
        }
        .d-flex.flex-wrap.justify-content-between > div,
        .d-flex.flex-wrap.justify-content-between > a {
            width: 100%;
        }
        .d-flex.flex-wrap.justify-content-between .btn {
            width: 100%;
        }
        .d-flex.flex-wrap.gap-2 {
            flex-direction: column;
        }
        .d-flex.flex-wrap.gap-2 .btn {
            width: 100%;
        }
        .form-select-lg {
            font-size: 0.9rem;
        }
        .card-body {
            padding: 15px;
        }
    }
</style>
@endpush
@endsection