<?php

namespace App\Http\Controllers;

use App\Models\PayrollPeriod;
use App\Models\PayrollItem;
use App\Models\Payslip;
use App\Models\Staff;
use App\Models\Attendance; // Assuming you have an Attendance model
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

class PayrollController extends Controller
{
    /**
     * Display a listing of payroll periods.
     */
    public function index(Request $request)
    {
        $query = PayrollPeriod::with(['createdBy', 'approvedBy']);

        // Filters
        if ($request->has('status') && $request->status) {
            $query->where('status', $request->status);
        }

        if ($request->has('period_type') && $request->period_type) {
            $query->where('period_type', $request->period_type);
        }

        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'LIKE', "%{$search}%")
                  ->orWhere('period_code', 'LIKE', "%{$search}%");
            });
        }

        $payrollPeriods = $query->orderBy('created_at', 'desc')->paginate(15);
        
        return view('payroll.index', compact('payrollPeriods'));
    }

    /**
     * Show the form for creating a new payroll period.
     */
    public function create()
    {
        $staff = Staff::all();
        return view('payroll.create', compact('staff'));
    }

    /**
     * Store a newly created payroll period.
     */
    public function store(Request $request)
    {
        // Get the staff IDs and salaries from the form
        $staffIds = $request->input('staff_ids_hidden', []);
        $basicSalaries = $request->input('basic_salaries_hidden', []);
        $dailyRates = $request->input('daily_rates_hidden', []);
        $attendanceDays = $request->input('attendance_days_hidden', []);
        
        // If no hidden fields, try regular fields (fallback)
        if (empty($staffIds)) {
            $staffIds = $request->input('staff_ids', []);
            $basicSalaries = $request->input('basic_salaries', []);
        }
        
        // Validate that we have at least one staff selected
        if (empty($staffIds) || count($staffIds) === 0) {
            return redirect()->back()
                ->with('error', 'Please select at least one staff member.')
                ->withInput();
        }
        
        // Clean and validate the data
        $cleanedStaffIds = [];
        $cleanedSalaries = [];
        $cleanedDailyRates = [];
        $cleanedAttendanceDays = [];
        
        foreach ($staffIds as $index => $staffId) {
            // Skip if staff ID is empty
            if (empty($staffId)) {
                continue;
            }
            
            // Get values for this staff
            $salary = isset($basicSalaries[$index]) ? $basicSalaries[$index] : 0;
            $dailyRate = isset($dailyRates[$index]) ? $dailyRates[$index] : 0;
            $attendanceDay = isset($attendanceDays[$index]) ? $attendanceDays[$index] : 0;
            
            // Ensure values are numeric
            if (!is_numeric($salary)) $salary = 0;
            if (!is_numeric($dailyRate)) $dailyRate = 0;
            if (!is_numeric($attendanceDay)) $attendanceDay = 0;
            
            $cleanedStaffIds[] = $staffId;
            $cleanedSalaries[] = floatval($salary);
            $cleanedDailyRates[] = floatval($dailyRate);
            $cleanedAttendanceDays[] = intval($attendanceDay);
        }
        
        // Validate we have cleaned data
        if (empty($cleanedStaffIds)) {
            return redirect()->back()
                ->with('error', 'Please select at least one staff member.')
                ->withInput();
        }
        
        // Prepare data for validation
        $data = [
            'name' => $request->input('name'),
            'period_type' => $request->input('period_type'),
            'start_date' => $request->input('start_date'),
            'end_date' => $request->input('end_date'),
            'payment_date' => $request->input('payment_date'),
            'description' => $request->input('description'),
            'daily_rate' => $request->input('daily_rate', 0),
            'staff_ids' => $cleanedStaffIds,
            'basic_salaries' => $cleanedSalaries,
            'daily_rates' => $cleanedDailyRates,
            'attendance_days' => $cleanedAttendanceDays,
        ];

        $validator = Validator::make($data, [
            'name' => 'required|string|max:255',
            'period_type' => 'required|in:monthly,bi-weekly,weekly',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
            'payment_date' => 'nullable|date|after:end_date',
            'description' => 'nullable|string',
            'daily_rate' => 'nullable|numeric|min:0',
            'staff_ids' => 'required|array|min:1',
            'staff_ids.*' => 'exists:staff,id',
            'basic_salaries' => 'required|array|min:1',
            'basic_salaries.*' => 'numeric|min:0',
            'daily_rates.*' => 'numeric|min:0',
            'attendance_days.*' => 'integer|min:0',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        try {
            DB::beginTransaction();

            // Create payroll period
            $payrollPeriod = new PayrollPeriod();
            $payrollPeriod->period_code = $payrollPeriod->generatePeriodCode();
            $payrollPeriod->name = $data['name'];
            $payrollPeriod->period_type = $data['period_type'];
            $payrollPeriod->start_date = $data['start_date'];
            $payrollPeriod->end_date = $data['end_date'];
            $payrollPeriod->payment_date = $data['payment_date'];
            $payrollPeriod->description = $data['description'];
            $payrollPeriod->daily_rate = $data['daily_rate'] ?? 0;
            $payrollPeriod->created_by = auth()->user()->staff_id ?? null;
            $payrollPeriod->save();

            // Create payroll items for each staff
            foreach ($cleanedStaffIds as $index => $staffId) {
                $staff = Staff::find($staffId);
                $basicSalary = $cleanedSalaries[$index] ?? 0;
                $dailyRate = $cleanedDailyRates[$index] ?? $staff->daily_rate ?? 0;
                $attendanceDay = $cleanedAttendanceDays[$index] ?? 0;

                if ($staff) {
                    $payrollItem = new PayrollItem();
                    $payrollItem->payroll_period_id = $payrollPeriod->id;
                    $payrollItem->staff_id = $staffId;
                    $payrollItem->staff_name = $staff->full_name ?? $staff->name;
                    $payrollItem->staff_email = $staff->email ?? null;
                    $payrollItem->staff_phone = $staff->phone ?? null;
                    $payrollItem->staff_position = $staff->position ?? null;
                    $payrollItem->staff_department = $staff->department ?? null;
                    $payrollItem->basic_salary = $basicSalary;
                    $payrollItem->daily_rate = $dailyRate;
                    $payrollItem->attendance_days = $attendanceDay;
                    $payrollItem->save();

                    // Calculate taxes and deductions
                    $payrollItem->calculateTax();
                    $payrollItem->calculatePension();
                    $payrollItem->calculateHealthInsurance();
                    
                    // Calculate totals
                    $payrollItem->calculateTotals();
                }
            }

            DB::commit();

            return redirect()->route('payroll.show', $payrollPeriod->id)
                ->with('success', 'Payroll period created successfully!');

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->with('error', 'Failed to create payroll period: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Get staff attendance data for payroll
     */
    public function getAttendanceData(Request $request)
    {
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');
        
        if (!$startDate || !$endDate) {
            return response()->json([
                'success' => false,
                'message' => 'Start date and end date are required.'
            ]);
        }
        
        try {
            $staff = Staff::all();
            $data = [];
            
            foreach ($staff as $staffMember) {
                // Count attendance days for this staff member in the date range
                $attendanceDays = $this->calculateAttendanceDays(
                    $staffMember->id,
                    $startDate,
                    $endDate
                );
                
                $data[] = [
                    'staff_id' => $staffMember->id,
                    'name' => $staffMember->full_name ?? $staffMember->name,
                    'attendance_days' => $attendanceDays,
                    'daily_rate' => $staffMember->daily_rate ?? 0,
                    'basic_salary' => $staffMember->salary ?? 0,
                ];
            }
            
            return response()->json([
                'success' => true,
                'data' => $data
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch attendance data: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Calculate attendance days for a staff member in a date range
     */
    private function calculateAttendanceDays($staffId, $startDate, $endDate)
    {
        // If you have an Attendance model with proper relationship
        // This assumes you have an attendance table with staff_id, date, and status fields
        try {
            $attendanceDays = Attendance::where('staff_id', $staffId)
                ->whereBetween('date', [$startDate, $endDate])
                ->whereIn('status', ['present', 'present_half_day']) // Count present and half-day as full day
                ->count();
            
            return $attendanceDays;
        } catch (\Exception $e) {
            // If Attendance table doesn't exist or query fails, return 0
            return 0;
        }
    }

    /**
     * Display the specified payroll period.
     */
    public function show($id)
    {
        $payrollPeriod = PayrollPeriod::with([
            'payrollItems.staff',
            'payrollItems.payslip',
            'createdBy',
            'approvedBy'
        ])->findOrFail($id);

        $summary = [
            'total_staff' => $payrollPeriod->payrollItems->count(),
            'total_gross' => $payrollPeriod->payrollItems->sum('gross_pay'),
            'total_deductions' => $payrollPeriod->payrollItems->sum('total_deductions'),
            'total_net' => $payrollPeriod->payrollItems->sum('net_pay'),
            'total_tax' => $payrollPeriod->payrollItems->sum('tax'),
            'total_pension' => $payrollPeriod->payrollItems->sum('pension'),
            'total_health_insurance' => $payrollPeriod->payrollItems->sum('health_insurance'),
            'paid_count' => $payrollPeriod->payrollItems->where('is_paid', true)->count(),
            'unpaid_count' => $payrollPeriod->payrollItems->where('is_paid', false)->count(),
            'total_attendance_days' => $payrollPeriod->payrollItems->sum('attendance_days'),
            'working_days' => $payrollPeriod->getWorkingDays(),
        ];

        return view('payroll.show', compact('payrollPeriod', 'summary'));
    }

    /**
     * Generate payroll from attendance
     */
    public function generateFromAttendance(Request $request, $periodId)
    {
        $payrollPeriod = PayrollPeriod::findOrFail($periodId);
        
        try {
            DB::beginTransaction();
            
            $staff = Staff::all();
            $generated = 0;
            
            foreach ($staff as $staffMember) {
                // Calculate attendance days
                $attendanceDays = $this->calculateAttendanceDays(
                    $staffMember->id,
                    $payrollPeriod->start_date,
                    $payrollPeriod->end_date
                );
                
                if ($attendanceDays > 0) {
                    // Check if payroll item already exists
                    $existingItem = PayrollItem::where('payroll_period_id', $periodId)
                        ->where('staff_id', $staffMember->id)
                        ->first();
                    
                    if ($existingItem) {
                        // Update existing item
                        $existingItem->attendance_days = $attendanceDays;
                        $existingItem->basic_salary = $staffMember->daily_rate * $attendanceDays;
                        $existingItem->daily_rate = $staffMember->daily_rate ?? 0;
                        $existingItem->calculateTax();
                        $existingItem->calculatePension();
                        $existingItem->calculateHealthInsurance();
                        $existingItem->calculateTotals();
                    } else {
                        // Create new payroll item
                        $payrollItem = new PayrollItem();
                        $payrollItem->payroll_period_id = $periodId;
                        $payrollItem->staff_id = $staffMember->id;
                        $payrollItem->staff_name = $staffMember->full_name ?? $staffMember->name;
                        $payrollItem->staff_email = $staffMember->email ?? null;
                        $payrollItem->staff_phone = $staffMember->phone ?? null;
                        $payrollItem->staff_position = $staffMember->position ?? null;
                        $payrollItem->staff_department = $staffMember->department ?? null;
                        $payrollItem->daily_rate = $staffMember->daily_rate ?? 0;
                        $payrollItem->attendance_days = $attendanceDays;
                        $payrollItem->basic_salary = $staffMember->daily_rate * $attendanceDays;
                        $payrollItem->save();
                        
                        $payrollItem->calculateTax();
                        $payrollItem->calculatePension();
                        $payrollItem->calculateHealthInsurance();
                        $payrollItem->calculateTotals();
                    }
                    
                    $generated++;
                }
            }
            
            DB::commit();
            
            return redirect()->route('payroll.show', $periodId)
                ->with('success', "Payroll generated for {$generated} staff members based on attendance!");
            
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->with('error', 'Failed to generate payroll: ' . $e->getMessage());
        }
    }

    /**
     * Show the form for editing the specified payroll period.
     */
    public function edit($id)
    {
        $payrollPeriod = PayrollPeriod::with('payrollItems.staff')->findOrFail($id);
        
        if ($payrollPeriod->status === 'completed' || $payrollPeriod->status === 'cancelled') {
            return redirect()->route('payroll.show', $id)
                ->with('error', 'Cannot edit completed or cancelled payroll periods.');
        }

        $staff = Staff::all();
        return view('payroll.edit', compact('payrollPeriod', 'staff'));
    }

    /**
     * Update the specified payroll period.
     */
    public function update(Request $request, $id)
    {
        $payrollPeriod = PayrollPeriod::findOrFail($id);

        if ($payrollPeriod->status === 'completed' || $payrollPeriod->status === 'cancelled') {
            return redirect()->back()
                ->with('error', 'Cannot update completed or cancelled payroll periods.');
        }

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'period_type' => 'required|in:monthly,bi-weekly,weekly',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
            'payment_date' => 'nullable|date|after:end_date',
            'description' => 'nullable|string',
            'status' => 'in:draft,processing,completed,cancelled',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        try {
            DB::beginTransaction();

            $payrollPeriod->update([
                'name' => $request->name,
                'period_type' => $request->period_type,
                'start_date' => $request->start_date,
                'end_date' => $request->end_date,
                'payment_date' => $request->payment_date,
                'description' => $request->description,
                'status' => $request->status ?? $payrollPeriod->status,
            ]);

            if ($request->status === 'completed') {
                $payrollPeriod->approved_by = auth()->user()->staff_id ?? null;
                $payrollPeriod->approved_at = now();
                $payrollPeriod->save();
            }

            DB::commit();

            return redirect()->route('payroll.show', $payrollPeriod->id)
                ->with('success', 'Payroll period updated successfully!');

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->with('error', 'Failed to update payroll period: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Remove the specified payroll period.
     */
    public function destroy($id)
    {
        $payrollPeriod = PayrollPeriod::findOrFail($id);

        if ($payrollPeriod->status === 'completed') {
            return redirect()->back()
                ->with('error', 'Cannot delete completed payroll periods.');
        }

        try {
            DB::beginTransaction();

            // Delete payslips first
            foreach ($payrollPeriod->payrollItems as $item) {
                if ($item->payslip) {
                    $item->payslip->delete();
                }
            }

            // Delete payroll items and period
            $payrollPeriod->payrollItems()->delete();
            $payrollPeriod->delete();

            DB::commit();

            return redirect()->route('payroll.index')
                ->with('success', 'Payroll period deleted successfully!');

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->with('error', 'Failed to delete payroll period: ' . $e->getMessage());
        }
    }

    /**
     * Edit a specific payroll item.
     */
    public function editPayrollItem($id)
    {
        $payrollItem = PayrollItem::with(['payrollPeriod', 'staff'])->findOrFail($id);
        
        if ($payrollItem->payrollPeriod->status === 'completed') {
            return redirect()->back()
                ->with('error', 'Cannot edit completed payroll period.');
        }

        return view('payroll.edit-item', compact('payrollItem'));
    }

    /**
     * Update a specific payroll item.
     */
    public function updatePayrollItem(Request $request, $id)
    {
        $payrollItem = PayrollItem::findOrFail($id);

        if ($payrollItem->payrollPeriod->status === 'completed') {
            return redirect()->back()
                ->with('error', 'Cannot update completed payroll period.');
        }

        $validator = Validator::make($request->all(), [
            'basic_salary' => 'required|numeric|min:0',
            'allowance' => 'nullable|numeric|min:0',
            'bonus' => 'nullable|numeric|min:0',
            'overtime_pay' => 'nullable|numeric|min:0',
            'commission' => 'nullable|numeric|min:0',
            'tax' => 'nullable|numeric|min:0',
            'pension' => 'nullable|numeric|min:0',
            'health_insurance' => 'nullable|numeric|min:0',
            'loan_deduction' => 'nullable|numeric|min:0',
            'other_deductions' => 'nullable|numeric|min:0',
            'payment_method' => 'nullable|in:bank_transfer,cash,cheque',
            'bank_name' => 'nullable|string|max:255',
            'account_number' => 'nullable|string|max:50',
            'account_name' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        try {
            DB::beginTransaction();

            $payrollItem->update($request->all());
            $payrollItem->calculateTotals();

            DB::commit();

            return redirect()->route('payroll.show', $payrollItem->payroll_period_id)
                ->with('success', 'Payroll item updated successfully!');

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->with('error', 'Failed to update payroll item: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Generate payslip for a staff member.
     */
    public function generatePayslip($id)
    {
        $payrollItem = PayrollItem::with(['staff', 'payrollPeriod'])->findOrFail($id);

        if ($payrollItem->payrollPeriod->status !== 'completed') {
            return redirect()->back()
                ->with('error', 'Payroll must be completed to generate payslip.');
        }

        try {
            DB::beginTransaction();

            // Check if payslip already exists
            if ($payrollItem->payslip) {
                return redirect()->back()
                    ->with('info', 'Payslip already generated for this staff.');
            }

            $payslip = $payrollItem->generatePayslip();

            DB::commit();

            return redirect()->route('payroll.view-payslip', $payslip->id)
                ->with('success', 'Payslip generated successfully!');

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->with('error', 'Failed to generate payslip: ' . $e->getMessage());
        }
    }

    /**
     * View payslip.
     */
    public function viewPayslip($id)
    {
        $payslip = Payslip::with(['payrollItem.staff', 'payrollItem.payrollPeriod'])->findOrFail($id);
        
        // Mark as viewed
        if ($payslip->status === 'generated') {
            $payslip->markAsViewed();
        }

        return view('payroll.payslip', compact('payslip'));
    }

    /**
     * Generate payslips for all staff in a payroll period.
     */
    public function generateAllPayslips($periodId)
    {
        $payrollPeriod = PayrollPeriod::with('payrollItems')->findOrFail($periodId);

        if ($payrollPeriod->status !== 'completed') {
            return redirect()->back()
                ->with('error', 'Payroll must be completed to generate payslips.');
        }

        try {
            DB::beginTransaction();

            $generated = 0;
            foreach ($payrollPeriod->payrollItems as $item) {
                if (!$item->payslip) {
                    $item->generatePayslip();
                    $generated++;
                }
            }

            DB::commit();

            return redirect()->route('payroll.show', $periodId)
                ->with('success', "{$generated} payslips generated successfully!");

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->with('error', 'Failed to generate payslips: ' . $e->getMessage());
        }
    }

    /**
     * Mark payroll item as paid.
     */
    public function markAsPaid(Request $request, $id)
    {
        $payrollItem = PayrollItem::findOrFail($id);

        if ($payrollItem->payrollPeriod->status !== 'completed') {
            return redirect()->back()
                ->with('error', 'Payroll must be completed to mark as paid.');
        }

        try {
            DB::beginTransaction();

            $payrollItem->markAsPaid($request->payment_date);

            DB::commit();

            return redirect()->route('payroll.show', $payrollItem->payroll_period_id)
                ->with('success', 'Payment marked as successful!');

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->with('error', 'Failed to mark payment: ' . $e->getMessage());
        }
    }

    /**
     * Add adjustment to payroll item.
     */
    public function addAdjustment(Request $request, $id)
    {
        $payrollItem = PayrollItem::findOrFail($id);

        if ($payrollItem->payrollPeriod->status === 'completed') {
            return redirect()->back()
                ->with('error', 'Cannot adjust completed payroll period.');
        }

        $validator = Validator::make($request->all(), [
            'type' => 'required|in:allowance,deduction,bonus,overtime,other',
            'description' => 'required|string|max:255',
            'amount' => 'required|numeric|min:0',
            'effect' => 'required|in:add,subtract',
            'reason' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        try {
            DB::beginTransaction();

            $adjustment = $payrollItem->adjustments()->create([
                'type' => $request->type,
                'description' => $request->description,
                'amount' => $request->amount,
                'effect' => $request->effect,
                'reason' => $request->reason,
                'approved_by' => auth()->user()->staff_id ?? null,
                'approved_at' => now(),
            ]);

            // Update payroll item based on adjustment
            if ($request->effect === 'add') {
                if ($request->type === 'allowance') {
                    $payrollItem->allowance += $request->amount;
                } elseif ($request->type === 'bonus') {
                    $payrollItem->bonus += $request->amount;
                } elseif ($request->type === 'overtime') {
                    $payrollItem->overtime_pay += $request->amount;
                } elseif ($request->type === 'other') {
                    $payrollItem->allowance += $request->amount;
                }
            } else {
                if ($request->type === 'deduction' || $request->type === 'other') {
                    $payrollItem->other_deductions += $request->amount;
                }
            }

            $payrollItem->calculateTotals();

            DB::commit();

            return redirect()->route('payroll.edit-item', $payrollItem->id)
                ->with('success', 'Adjustment added successfully!');

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->with('error', 'Failed to add adjustment: ' . $e->getMessage());
        }
    }
}