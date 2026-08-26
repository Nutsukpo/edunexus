<?php

namespace App\Http\Controllers;

use App\Models\Payslip;
use App\Models\Staff;
use App\Models\SalaryStructure;
use App\Models\PayrollPeriod;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;

class PayslipController extends Controller
{
    /**
     * Display a listing of all payslips.
     */
    public function index()
    {
        $payslips = Payslip::with(['staff', 'payrollPeriod'])
            ->latest()
            ->paginate(10);
        
        return view('payslips.index', compact('payslips'));
    }

    /**
     * Show the form for creating a new payslip.
     */
    public function create()
    {
        // Get all staff with their salary structures
        $staffs = Staff::with(['salaryStructures' => function($query) {
            $query->where('is_active', true)
                  ->latest('effective_date');
        }])->get();

        // Add helper attributes
        $staffs->each(function($staff) {
            $staff->has_active_structure = $staff->salaryStructures->isNotEmpty();
            $staff->active_structure = $staff->salaryStructures->first();
        });

        // Get all payroll periods
        $availablePayrollPeriods = PayrollPeriod::all();
        
        return view('payslips.create', compact('staffs', 'availablePayrollPeriods'));
    }

    /**
     * Store a newly created payslip in storage.
     */
    public function store(Request $request)
    {
        try {
            // Validate request
            $validated = $request->validate([
                'staff_id' => 'required|exists:staff,id',
                'payroll_period_id' => 'required|exists:payroll_periods,id',
                'basic_salary' => 'required|numeric|min:0',
                'allowances' => 'required|numeric|min:0',
                'bonus' => 'nullable|numeric|min:0',
                'overtime' => 'nullable|numeric|min:0',
                'total_earnings' => 'required|numeric|min:0',
                'tax' => 'nullable|numeric|min:0',
                'pension' => 'nullable|numeric|min:0',
                'tier2' => 'nullable|numeric|min:0',
                'tier3' => 'nullable|numeric|min:0',
                'insurance' => 'nullable|numeric|min:0',
                'loans' => 'nullable|numeric|min:0',
                'other_deductions' => 'nullable|numeric|min:0',
                'total_deductions' => 'required|numeric|min:0',
                'net_pay' => 'required|numeric|min:0',
                'notes' => 'nullable|string',
                'breakdown' => 'nullable|json'
            ]);

            // Check for duplicate payslip
            $existing = Payslip::where('staff_id', $request->staff_id)
                ->where('payroll_period_id', $request->payroll_period_id)
                ->first();

            if ($existing) {
                return redirect()->back()
                    ->with('error', 'A payslip already exists for this staff member in the selected period.')
                    ->withInput();
            }

            $payrollPeriod = PayrollPeriod::findOrFail($request->payroll_period_id);

            DB::beginTransaction();

            try {
                // Round all monetary values to 2 decimal places
                $data = [
                    'staff_id' => $request->staff_id,
                    'payroll_period_id' => $request->payroll_period_id,
                    'month' => $payrollPeriod->month,
                    'year' => $payrollPeriod->year,
                    'basic_salary' => round((float) $request->basic_salary, 2),
                    'allowances' => round((float) $request->allowances, 2),
                    'bonus' => round((float) ($request->bonus ?? 0), 2),
                    'overtime' => round((float) ($request->overtime ?? 0), 2),
                    'total_earnings' => round((float) $request->total_earnings, 2),
                    'tax' => round((float) ($request->tax ?? 0), 2),
                    'pension' => round((float) ($request->pension ?? 0), 2),
                    'tier2' => round((float) ($request->tier2 ?? 0), 2),
                    'tier3' => round((float) ($request->tier3 ?? 0), 2),
                    'insurance' => round((float) ($request->insurance ?? 0), 2),
                    'loans' => round((float) ($request->loans ?? 0), 2),
                    'other_deductions' => round((float) ($request->other_deductions ?? 0), 2),
                    'total_deductions' => round((float) $request->total_deductions, 2),
                    'net_pay' => round((float) $request->net_pay, 2),
                    'notes' => $request->notes,
                    'breakdown' => json_decode($request->breakdown, true),
                    'created_by' => auth()->id(),
                    'status' => 'generated'
                ];

                $payslip = Payslip::create($data);

                DB::commit();

                return redirect()
                    ->route('payslips.show', $payslip->id)
                    ->with('success', 'Payslip generated successfully!');

            } catch (\Exception $e) {
                DB::rollBack();
                Log::error('Database error while creating payslip: ' . $e->getMessage());
                Log::error('Stack trace: ' . $e->getTraceAsString());
                
                return redirect()->back()
                    ->with('error', 'Database error: ' . $e->getMessage())
                    ->withInput();
            }

        } catch (\Illuminate\Validation\ValidationException $e) {
            return redirect()->back()
                ->withErrors($e->errors())
                ->withInput();
                
        } catch (\Exception $e) {
            Log::error('Unexpected error while creating payslip: ' . $e->getMessage());
            Log::error('Stack trace: ' . $e->getTraceAsString());
            
            return redirect()->back()
                ->with('error', 'Failed to generate payslip: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Display the specified payslip.
     */
    public function show($id)
    {
        $payslip = Payslip::with(['staff', 'payrollPeriod', 'creator'])
            ->findOrFail($id);
        
        return view('payslips.show', compact('payslip'));
    }

        //**
 //* Export the specified payslip as PDF.
 
    public function exportPdf($id)
    {
        try {
            $payslip = Payslip::with(['staff', 'payrollPeriod', 'creator'])
                ->findOrFail($id);
            
            // Load the view
            $pdf = Pdf::loadView('payslips.pdf', compact('payslip'));
            
            // Set paper size and orientation
            $pdf->setPaper('a4', 'portrait');
            
            // Get safe filename
            $firstName = $payslip->staff->first_name ?? 'staff';
            $lastName = $payslip->staff->last_name ?? '';
            $staffName = trim($firstName . ' ' . $lastName);
            $staffName = empty($staffName) ? 'staff' : str_replace(' ', '_', $staffName);
            
            $monthName = $payslip->month_name ?? 'unknown';
            $year = $payslip->year ?? date('Y');
            
            $filename = "payslip_{$staffName}_{$monthName}_{$year}.pdf";
            
            // Try streaming first to see if there's an error
            return $pdf->stream($filename);
            
        } catch (\Exception $e) {
            \Log::error('Error generating PDF: ' . $e->getMessage());
            \Log::error('Stack trace: ' . $e->getTraceAsString());
            
            return redirect()->back()
                ->with('error', 'Failed to generate PDF: ' . $e->getMessage());
        }
    }

    /**
     * Display a listing of payslips for a specific month/year.
     */
    public function filter(Request $request)
    {
        $query = Payslip::with(['staff', 'payrollPeriod']);
        
        if ($request->filled('month')) {
            $query->where('month', $request->month);
        }
        
        if ($request->filled('year')) {
            $query->where('year', $request->year);
        }
        
        if ($request->filled('staff_id')) {
            $query->where('staff_id', $request->staff_id);
        }
        
        if ($request->filled('payroll_period_id')) {
            $query->where('payroll_period_id', $request->payroll_period_id);
        }
        
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        
        $payslips = $query->latest()->paginate(10);
        
        $staffs = Staff::all();
        $payrollPeriods = PayrollPeriod::all();
        $months = [
            1 => 'January', 2 => 'February', 3 => 'March', 4 => 'April',
            5 => 'May', 6 => 'June', 7 => 'July', 8 => 'August',
            9 => 'September', 10 => 'October', 11 => 'November', 12 => 'December'
        ];
        $years = range(date('Y'), date('Y') - 5);
        
        return view('payslips.index', compact(
            'payslips', 
            'staffs', 
            'payrollPeriods',
            'months', 
            'years'
        ));
    }

    /**
     * Delete a payslip
     */
    public function destroy($id)
    {
        try {
            $payslip = Payslip::findOrFail($id);
            
            $staffName = $payslip->staff->first_name . ' ' . $payslip->staff->last_name;
            $period = $payslip->month_name . ' ' . $payslip->year;
            
            $payslip->delete();
            
            return redirect()
                ->route('payslips.index')
                ->with('success', "Payslip for {$staffName} ({$period}) has been deleted successfully.");
            
        } catch (\Exception $e) {
            Log::error('Error deleting payslip: ' . $e->getMessage());
            return redirect()->back()
                ->with('error', 'Failed to delete payslip: ' . $e->getMessage());
        }
    }

    /**
     * Get staff salary structure and calculate pay (AJAX)
     */
    public function getStaffSalaryData(Request $request)
    {
        try {
            $request->validate([
                'staff_id' => 'required|exists:staff,id',
                'payroll_period_id' => 'required|exists:payroll_periods,id'
            ]);

            $staff = Staff::findOrFail($request->staff_id);
            $payrollPeriod = PayrollPeriod::findOrFail($request->payroll_period_id);
            
            // Get the salary structure
            $salaryStructure = SalaryStructure::where('staff_id', $staff->id)
                ->where('is_active', true)
                ->latest('effective_date')
                ->first();
                
            if (!$salaryStructure) {
                return response()->json([
                    'success' => false,
                    'message' => 'No active salary structure found for this staff member. Please create a salary structure first.'
                ]);
            }
            
            // Calculate values with proper rounding
            $basicSalary = round((float) ($salaryStructure->basic_salary ?? 0), 2);
            $housingAllowance = round((float) ($salaryStructure->housing_allowance ?? 0), 2);
            $transportAllowance = round((float) ($salaryStructure->transport_allowance ?? 0), 2);
            $medicalAllowance = round((float) ($salaryStructure->medical_allowance ?? 0), 2);
            $responsibilityAllowance = round((float) ($salaryStructure->responsibility_allowance ?? 0), 2);
            $otherAllowances = round((float) ($salaryStructure->other_allowance ?? 0), 2);
            
            $totalAllowances = round(
                $housingAllowance + $transportAllowance + $medicalAllowance + 
                $responsibilityAllowance + $otherAllowances, 
                2
            );
            
            // Days calculation
            $daysInMonth = cal_days_in_month(CAL_GREGORIAN, $payrollPeriod->month, $payrollPeriod->year);
            $workingDays = $payrollPeriod->working_days ?? $daysInMonth;
            $dailyRate = round($basicSalary / $daysInMonth, 4);
            $proratedBasicSalary = round($dailyRate * $workingDays, 2);
            
            // Overtime
            $overtimeRate = round((float) ($salaryStructure->overtime_rate ?? 0), 2);
            $overtimeHours = round((float) ($payrollPeriod->overtime_hours ?? 0), 2);
            $overtimePay = round($overtimeRate * $overtimeHours, 2);
            
            // Bonuses
            $performanceBonus = round((float) ($salaryStructure->performance_bonus ?? 0), 2);
            $holidayBonus = round((float) ($salaryStructure->holiday_bonus ?? 0), 2);
            $otherBonuses = round((float) ($salaryStructure->other_bonuses ?? 0), 2);
            $totalBonuses = round($performanceBonus + $holidayBonus + $otherBonuses, 2);
            
            // Deductions
            $taxDeduction = round(
                (float) ($salaryStructure->tax ?? $this->calculateTax($proratedBasicSalary + $totalAllowances)), 
                2
            );
            $pensionDeduction = round(
                (float) ($salaryStructure->ssnit ?? $this->calculatePension($proratedBasicSalary)), 
                2
            );
            $tier2Deduction = round((float) ($salaryStructure->tier2 ?? 0), 2);
            $tier3Deduction = round((float) ($salaryStructure->tier3 ?? 0), 2);
            $healthInsurance = round((float) ($salaryStructure->health_insurance ?? 0), 2);
            $loanDeductions = round((float) ($salaryStructure->loan_deduction ?? 0), 2);
            $otherDeductions = round((float) ($salaryStructure->other_deduction ?? 0), 2);
            
            // Totals
            $totalEarnings = round($proratedBasicSalary + $totalAllowances + $overtimePay + $totalBonuses, 2);
            $totalDeductions = round(
                $taxDeduction + $pensionDeduction + $tier2Deduction + $tier3Deduction + 
                $healthInsurance + $loanDeductions + $otherDeductions, 
                2
            );
            $netPay = round($totalEarnings - $totalDeductions, 2);
            
            return response()->json([
                'success' => true,
                'staff' => [
                    'id' => $staff->id,
                    'first_name' => $staff->first_name ?? '',
                    'last_name' => $staff->last_name ?? '',
                    'position' => $staff->position ?? 'N/A',
                    'department' => $staff->department ?? 'N/A',
                    'email' => $staff->email ?? 'N/A'
                ],
                'basic_salary' => $proratedBasicSalary,
                'allowances' => $totalAllowances,
                'allowances_breakdown' => [
                    'housing' => $housingAllowance,
                    'transport' => $transportAllowance,
                    'medical' => $medicalAllowance,
                    'responsibility' => $responsibilityAllowance,
                    'other' => $otherAllowances
                ],
                'bonuses' => $totalBonuses,
                'bonus_breakdown' => [
                    'performance' => $performanceBonus,
                    'holiday' => $holidayBonus,
                    'other' => $otherBonuses
                ],
                'overtime_pay' => $overtimePay,
                'overtime_hours' => $overtimeHours,
                'total_earnings' => $totalEarnings,
                'tax' => $taxDeduction,
                'pension' => $pensionDeduction,
                'tier2' => $tier2Deduction,
                'tier3' => $tier3Deduction,
                'health_insurance' => $healthInsurance,
                'loan_deductions' => $loanDeductions,
                'other_deductions' => $otherDeductions,
                'total_deductions' => $totalDeductions,
                'net_pay' => $netPay,
                'working_days' => $workingDays,
                'days_in_month' => $daysInMonth,
                'daily_rate' => $dailyRate
            ]);
            
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error: ' . $e->getMessage()
            ]);
            
        } catch (\Exception $e) {
            Log::error('Error fetching salary data: ' . $e->getMessage());
            Log::error('Stack trace: ' . $e->getTraceAsString());
            
            return response()->json([
                'success' => false,
                'message' => 'Error loading salary structure: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Calculate tax deduction based on salary (simplified progressive tax)
     */
    private function calculateTax($salary)
    {
        if ($salary <= 0) return 0;
        
        $tax = 0;
        $remaining = $salary;
        
        // Progressive tax brackets (simplified example)
        if ($remaining > 5000) {
            $tax += ($remaining - 5000) * 0.30;
            $remaining = 5000;
        }
        if ($remaining > 3000) {
            $tax += ($remaining - 3000) * 0.20;
            $remaining = 3000;
        }
        if ($remaining > 1000) {
            $tax += ($remaining - 1000) * 0.10;
        }
        
        return round($tax, 2);
    }

    /**
     * Calculate pension deduction (5% of basic salary)
     */
    private function calculatePension($salary)
    {
        return round($salary * 0.05, 2);
    }

    /**
     * Re-generate a payslip
     */
    public function regenerate($id)
    {
        try {
            $payslip = Payslip::findOrFail($id);
            
            if (!in_array($payslip->status, ['generated', 'failed'])) {
                return redirect()->back()
                    ->with('error', 'This payslip cannot be regenerated.');
            }

            // Regeneration logic here
            $payslip->update([
                'status' => 'generated',
                'updated_at' => now()
            ]);

            return redirect()
                ->route('payslips.show', $payslip->id)
                ->with('success', 'Payslip regenerated successfully.');
            
        } catch (\Exception $e) {
            Log::error('Error regenerating payslip: ' . $e->getMessage());
            return redirect()->back()
                ->with('error', 'Failed to regenerate payslip: ' . $e->getMessage());
        }
    }

    /**
     * Bulk generate payslips for all staff for a payroll period
     */
    public function bulkGenerate(Request $request)
    {
        $request->validate([
            'payroll_period_id' => 'required|exists:payroll_periods,id',
        ]);

        $payrollPeriod = PayrollPeriod::findOrFail($request->payroll_period_id);

        // Get all staff with active salary structures
        $staffWithStructures = Staff::whereHas('salaryStructures', function($query) {
            $query->where('is_active', true);
        })->get();

        if ($staffWithStructures->isEmpty()) {
            return redirect()->back()
                ->with('error', 'No staff members with active salary structures found.');
        }

        $generated = 0;
        $failed = 0;
        $errors = [];

        DB::beginTransaction();
        try {
            foreach ($staffWithStructures as $staff) {
                try {
                    // Skip if payslip already exists
                    $existing = Payslip::where('staff_id', $staff->id)
                        ->where('payroll_period_id', $payrollPeriod->id)
                        ->first();
                    
                    if ($existing) {
                        continue;
                    }

                    // Get the latest active salary structure
                    $salaryStructure = SalaryStructure::where('staff_id', $staff->id)
                        ->where('is_active', true)
                        ->latest('effective_date')
                        ->first();
                    
                    if (!$salaryStructure) {
                        $failed++;
                        $errors[] = "No active salary structure for {$staff->name}";
                        continue;
                    }

                    // Calculate values
                    $basicSalary = round((float) ($salaryStructure->basic_salary ?? 0), 2);
                    $housingAllowance = round((float) ($salaryStructure->housing_allowance ?? 0), 2);
                    $transportAllowance = round((float) ($salaryStructure->transport_allowance ?? 0), 2);
                    $medicalAllowance = round((float) ($salaryStructure->medical_allowance ?? 0), 2);
                    $responsibilityAllowance = round((float) ($salaryStructure->responsibility_allowance ?? 0), 2);
                    $otherAllowances = round((float) ($salaryStructure->other_allowance ?? 0), 2);
                    $totalAllowances = round($housingAllowance + $transportAllowance + $medicalAllowance + $responsibilityAllowance + $otherAllowances, 2);
                    
                    $daysInMonth = cal_days_in_month(CAL_GREGORIAN, $payrollPeriod->month, $payrollPeriod->year);
                    $workingDays = $payrollPeriod->working_days ?? $daysInMonth;
                    $dailyRate = round($basicSalary / $daysInMonth, 4);
                    $proratedBasicSalary = round($dailyRate * $workingDays, 2);
                    
                    $overtimeRate = round((float) ($salaryStructure->overtime_rate ?? 0), 2);
                    $overtimeHours = round((float) ($payrollPeriod->overtime_hours ?? 0), 2);
                    $overtimePay = round($overtimeRate * $overtimeHours, 2);
                    
                    $performanceBonus = round((float) ($salaryStructure->performance_bonus ?? 0), 2);
                    $holidayBonus = round((float) ($salaryStructure->holiday_bonus ?? 0), 2);
                    $otherBonuses = round((float) ($salaryStructure->other_bonuses ?? 0), 2);
                    $totalBonuses = round($performanceBonus + $holidayBonus + $otherBonuses, 2);
                    
                    $taxDeduction = round((float) ($salaryStructure->tax ?? $this->calculateTax($proratedBasicSalary + $totalAllowances)), 2);
                    $pensionDeduction = round((float) ($salaryStructure->ssnit ?? $this->calculatePension($proratedBasicSalary)), 2);
                    $tier2Deduction = round((float) ($salaryStructure->tier2 ?? 0), 2);
                    $tier3Deduction = round((float) ($salaryStructure->tier3 ?? 0), 2);
                    $healthInsurance = round((float) ($salaryStructure->health_insurance ?? 0), 2);
                    $loanDeductions = round((float) ($salaryStructure->loan_deduction ?? 0), 2);
                    $otherDeductions = round((float) ($salaryStructure->other_deduction ?? 0), 2);
                    
                    $totalEarnings = round($proratedBasicSalary + $totalAllowances + $overtimePay + $totalBonuses, 2);
                    $totalDeductions = round($taxDeduction + $pensionDeduction + $tier2Deduction + $tier3Deduction + $healthInsurance + $loanDeductions + $otherDeductions, 2);
                    $netPay = round($totalEarnings - $totalDeductions, 2);
                    
                    Payslip::create([
                        'staff_id' => $staff->id,
                        'payroll_period_id' => $payrollPeriod->id,
                        'month' => $payrollPeriod->month,
                        'year' => $payrollPeriod->year,
                        'basic_salary' => $proratedBasicSalary,
                        'allowances' => $totalAllowances,
                        'bonus' => $totalBonuses,
                        'overtime' => $overtimePay,
                        'total_earnings' => $totalEarnings,
                        'tax' => $taxDeduction,
                        'pension' => $pensionDeduction,
                        'tier2' => $tier2Deduction,
                        'tier3' => $tier3Deduction,
                        'insurance' => $healthInsurance,
                        'loans' => $loanDeductions,
                        'other_deductions' => $otherDeductions,
                        'total_deductions' => $totalDeductions,
                        'net_pay' => $netPay,
                        'created_by' => auth()->id(),
                        'status' => 'generated',
                        'breakdown' => [
                            'salary_structure_id' => $salaryStructure->id,
                            'effective_date' => $salaryStructure->effective_date,
                            'housing_allowance' => $housingAllowance,
                            'transport_allowance' => $transportAllowance,
                            'medical_allowance' => $medicalAllowance,
                            'responsibility_allowance' => $responsibilityAllowance,
                            'other_allowances' => $otherAllowances,
                            'performance_bonus' => $performanceBonus,
                            'holiday_bonus' => $holidayBonus,
                            'other_bonuses' => $otherBonuses,
                            'overtime_hours' => $overtimeHours,
                            'overtime_rate' => $overtimeRate,
                            'working_days' => $workingDays,
                            'daily_rate' => $dailyRate,
                            'tax' => $taxDeduction,
                            'ssnit' => $pensionDeduction,
                            'tier2' => $tier2Deduction,
                            'tier3' => $tier3Deduction,
                            'loan_deduction' => $loanDeductions,
                            'other_deduction' => $otherDeductions
                        ]
                    ]);
                    
                    $generated++;
                } catch (\Exception $e) {
                    $failed++;
                    $errors[] = "Failed for {$staff->name}: " . $e->getMessage();
                }
            }

            DB::commit();
            
            $message = "Bulk generation completed. Generated: {$generated}, Failed: {$failed}";
            if (!empty($errors)) {
                $message .= ". Errors: " . implode('; ', $errors);
            }
            
            return redirect()
                ->route('payslips.index')
                ->with('success', $message);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Bulk generation error: ' . $e->getMessage());
            
            return redirect()->back()
                ->with('error', 'Failed to generate bulk payslips: ' . $e->getMessage());
        }
    }
}