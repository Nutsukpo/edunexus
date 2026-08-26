<?php

namespace App\Http\Controllers;

use App\Models\PayrollPeriod;
use App\Models\AcademicYear;
use App\Models\Staff;
use App\Models\PayrollItem;
use App\Models\SalaryStructure;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Database\QueryException;

class PayrollPeriodController extends Controller
{
    /**
     * ============================================================
     * INDEX
     * ============================================================
     */
    public function index(Request $request)
    {
        try {

            $query = PayrollPeriod::with([
                'createdBy',
                'approvedBy',
                'academicYear'
            ])->withCount('staff');

            if ($request->filled('academic_year_id')) {
                $query->where(
                    'academic_year_id',
                    $request->academic_year_id
                );
            }

            if ($request->filled('month')) {
                $query->where(
                    'month',
                    $request->month
                );
            }

            if ($request->filled('year')) {
                $query->where(
                    'year',
                    $request->year
                );
            }

            if ($request->filled('status')) {
                $query->where(
                    'status',
                    $request->status
                );
            }

            $payrollPeriods = $query
                ->orderBy('year', 'desc')
                ->orderBy('month', 'desc')
                ->paginate(15);

            $academicYears = AcademicYear::orderBy(
                'name',
                'desc'
            )->pluck(
                'name',
                'id'
            )->toArray();

            $months = PayrollPeriod::getMonths();

            $years = PayrollPeriod::select('year')
                ->distinct()
                ->orderBy('year', 'desc')
                ->pluck('year')
                ->toArray();

            $statuses = PayrollPeriod::getStatuses();

            return view(
                'payroll.index',
                compact(
                    'payrollPeriods',
                    'academicYears',
                    'months',
                    'years',
                    'statuses'
                )
            );

        } catch (\Exception $e) {

            $academicYears = AcademicYear::orderBy(
                'name',
                'desc'
            )->pluck(
                'name',
                'id'
            )->toArray();

            $months = PayrollPeriod::getMonths();

            $years = range(
                date('Y') - 2,
                date('Y') + 1
            );

            $statuses = PayrollPeriod::getStatuses();

            $payrollPeriods = collect();

            return view(
                'payroll.index',
                compact(
                    'payrollPeriods',
                    'academicYears',
                    'months',
                    'years',
                    'statuses'
                )
            )->with(
                'warning',
                'Error loading data: ' . $e->getMessage()
            );
        }
    }


    /**
     * ============================================================
     * CREATE
     * ============================================================
     */
    public function create()
    {
        $statuses = PayrollPeriod::getStatuses();

        $periodCode =
            PayrollPeriod::generatePeriodCode();

        $academicYears =
            AcademicYear::orderBy(
                'name',
                'desc'
            )->pluck(
                'name',
                'id'
            )->toArray();

        $months =
            PayrollPeriod::getMonths();

        $currentYear =
            date('Y');

        $years = [];

        for ($i = -5; $i <= 2; $i++) {

            $year =
                $currentYear + $i;

            $years[$year] =
                $year;
        }

        return view(
            'payroll.create',
            compact(
                'statuses',
                'periodCode',
                'academicYears',
                'months',
                'years'
            )
        );
    }


    /**
     * ============================================================
     * STORE
     * ============================================================
     */
    public function store(Request $request)
    {
        try {

            $validated = $request->validate([

                'period_code' => [
                    'required',
                    'string',
                    'unique:payroll_periods,period_code'
                ],

                'name' => [
                    'required',
                    'string',
                    'max:255'
                ],

                'academic_year_id' => [
                    'required',
                    'exists:academic_years,id'
                ],

                'month' => [
                    'required',
                    'integer',
                    'between:1,12'
                ],

                'year' => [
                    'required',
                    'integer',
                    'min:2000',
                    'max:2100'
                ],

                'start_date' => [
                    'required',
                    'date',
                    'before_or_equal:end_date'
                ],

                'end_date' => [
                    'required',
                    'date',
                    'after_or_equal:start_date'
                ],

                'payment_date' => [
                    'nullable',
                    'date',
                    'after_or_equal:end_date'
                ],

                'status' => [
                    'required',
                    Rule::in(
                        array_keys(
                            PayrollPeriod::getStatuses()
                        )
                    )
                ],

                'description' => [
                    'nullable',
                    'string'
                ],
            ]);

            $validated['created_by'] =
                Auth::id();

            $payrollPeriod =
                PayrollPeriod::create(
                    $validated
                );

            return redirect()
                ->route(
                    'payroll-periods.index'
                )
                ->with(
                    'success',
                    'Payroll period created successfully.'
                );

        } catch (QueryException $e) {

            return redirect()
                ->back()
                ->withInput()
                ->with(
                    'error',
                    'Database error: ' .
                    $e->getMessage()
                );
        }
    }


    /**
     * ============================================================
     * SHOW
     * ============================================================
     */
    public function show($id)
    {
        $payrollPeriod =
            PayrollPeriod::with([
                'staff',
                'academicYear',
                'createdBy',
                'approvedBy'
            ])->findOrFail($id);


        /*
        |--------------------------------------------------------------------------
        | Salary Calculations
        |--------------------------------------------------------------------------
        */

        $staffWithSalaries = [];

        $totalBasicSalary = 0;
        $totalAllowances = 0;
        $totalGrossPay = 0;
        $totalTax = 0;
        $totalPension = 0;
        $totalDeductions = 0;
        $totalNetPay = 0;
        $totalWorkedDays = 0;


        foreach ($payrollPeriod->staff as $staff) {

            /*
            |--------------------------------------------------------------------------
            | Find Latest Salary Structure
            |--------------------------------------------------------------------------
            */

            $salaryStructure =
                SalaryStructure::where(
                    'staff_id',
                    $staff->id
                )
                ->where(
                    'effective_date',
                    '<=',
                    now()
                )
                ->orderBy(
                    'effective_date',
                    'desc'
                )
                ->first();


            /*
            |--------------------------------------------------------------------------
            | Salary Structure Exists
            |--------------------------------------------------------------------------
            */

            if ($salaryStructure) {

                $basicSalary =
                    (float) (
                        $salaryStructure->basic_salary
                        ?? 0
                    );


                $allowances =
                    (float) (
                        ($salaryStructure->housing_allowance ?? 0)
                        +
                        ($salaryStructure->transport_allowance ?? 0)
                        +
                        ($salaryStructure->medical_allowance ?? 0)
                        +
                        ($salaryStructure->responsibility_allowance ?? 0)
                        +
                        ($salaryStructure->other_allowance ?? 0)
                    );


                $grossPay =
                    $basicSalary +
                    $allowances;


                $tax =
                    $salaryStructure->tax
                    ?? $this->calculateTax(
                        $grossPay
                    );


                $pension =
                    $salaryStructure->ssnit
                    ?? $this->calculatePension(
                        $grossPay
                    );


                $loanDeduction =
                    (float) (
                        $salaryStructure->loan_deduction
                        ?? 0
                    );


                $otherDeduction =
                    (float) (
                        $salaryStructure->other_deduction
                        ?? 0
                    );


                $deductions =
                    $tax
                    +
                    $pension
                    +
                    $loanDeduction
                    +
                    $otherDeduction;


                $netPay =
                    $grossPay -
                    $deductions;


                $workedDays =
                    $staff->pivot->worked_days
                    ?? 22;


                $staffWithSalaries[] = [

                    'staff' =>
                        $staff,

                    'salary_structure' =>
                        $salaryStructure,

                    'basic_salary' =>
                        $basicSalary,

                    'allowances' =>
                        $allowances,

                    'overtime' =>
                        $staff->pivot->overtime
                        ?? 0,

                    'gross_pay' =>
                        $grossPay,

                    'tax' =>
                        $tax,

                    'pension' =>
                        $pension,

                    'deductions' =>
                        $deductions,

                    'net_pay' =>
                        $netPay,

                    'worked_days' =>
                        $workedDays,
                ];


                $totalBasicSalary +=
                    $basicSalary;

                $totalAllowances +=
                    $allowances;

                $totalGrossPay +=
                    $grossPay;

                $totalTax +=
                    $tax;

                $totalPension +=
                    $pension;

                $totalDeductions +=
                    $deductions;

                $totalNetPay +=
                    $netPay;

                $totalWorkedDays +=
                    $workedDays;

            } else {

                /*
                |--------------------------------------------------------------------------
                | Fallback To Payroll Pivot
                |--------------------------------------------------------------------------
                */

                $basicSalary =
                    (float) (
                        $staff->pivot->basic_salary
                        ?? 0
                    );

                $allowances =
                    (float) (
                        $staff->pivot->allowances
                        ?? 0
                    );

                $grossPay =
                    (float) (
                        $staff->pivot->gross_pay
                        ?? (
                            $basicSalary +
                            $allowances
                        )
                    );

                $tax =
                    (float) (
                        $staff->pivot->tax
                        ?? 0
                    );

                $pension =
                    (float) (
                        $staff->pivot->pension
                        ?? 0
                    );

                $deductions =
                    (float) (
                        $staff->pivot->deductions
                        ?? (
                            $tax +
                            $pension
                        )
                    );

                $netPay =
                    (float) (
                        $staff->pivot->net_pay
                        ?? (
                            $grossPay -
                            $deductions
                        )
                    );

                $workedDays =
                    $staff->pivot->worked_days
                    ?? 22;


                $staffWithSalaries[] = [

                    'staff' =>
                        $staff,

                    'salary_structure' =>
                        null,

                    'basic_salary' =>
                        $basicSalary,

                    'allowances' =>
                        $allowances,

                    'overtime' =>
                        $staff->pivot->overtime
                        ?? 0,

                    'gross_pay' =>
                        $grossPay,

                    'tax' =>
                        $tax,

                    'pension' =>
                        $pension,

                    'deductions' =>
                        $deductions,

                    'net_pay' =>
                        $netPay,

                    'worked_days' =>
                        $workedDays,
                ];


                $totalBasicSalary +=
                    $basicSalary;

                $totalAllowances +=
                    $allowances;

                $totalGrossPay +=
                    $grossPay;

                $totalTax +=
                    $tax;

                $totalPension +=
                    $pension;

                $totalDeductions +=
                    $deductions;

                $totalNetPay +=
                    $netPay;

                $totalWorkedDays +=
                    $workedDays;
            }
        }


        /*
        |--------------------------------------------------------------------------
        | Summary
        |--------------------------------------------------------------------------
        */

        $totalStaff =
            $payrollPeriod->staff->count();


        $summary = [

            'total_staff' =>
                $totalStaff,

            'total_basic_salary' =>
                $totalBasicSalary,

            'total_gross' =>
                $totalGrossPay,

            'total_net' =>
                $totalNetPay,

            'total_tax' =>
                $totalTax,

            'total_pension' =>
                $totalPension,

            'total_deductions' =>
                $totalDeductions,

            'total_allowances' =>
                $totalAllowances,

            'total_overtime' =>
                collect(
                    $staffWithSalaries
                )->sum(
                    'overtime'
                ),

            'total_worked_days' =>
                $totalWorkedDays,

            'avg_salary' =>
                $totalStaff > 0
                    ? $totalNetPay / $totalStaff
                    : 0,
        ];


        /*
        |--------------------------------------------------------------------------
        | Top Earners
        |--------------------------------------------------------------------------
        */

        $topEarnersArray =
            collect($staffWithSalaries)
                ->sortByDesc('net_pay')
                ->take(3)
                ->values()
                ->map(
                    function ($item) {

                        return [

                            'name' =>
                                $item['staff']->name
                                ?? trim(
                                    ($item['staff']->first_name ?? '')
                                    . ' ' .
                                    ($item['staff']->last_name ?? '')
                                ),

                            'position' =>
                                $item['staff']->position
                                ?? 'N/A',

                            'net_pay' =>
                                $item['net_pay'],
                        ];
                    }
                )
                ->toArray();


        return view(
            'payroll.show',
            compact(
                'payrollPeriod',
                'summary',
                'staffWithSalaries',
                'topEarnersArray'
            )
        );
    }


    /**
     * ============================================================
     * EDIT
     * ============================================================
     */
    public function edit(PayrollPeriod $payrollPeriod)
    {
        if (!$payrollPeriod->isEditable()) {

            return redirect()
                ->route(
                    'payroll-periods.index'
                )
                ->with(
                    'error',
                    'This payroll period cannot be edited.'
                );
        }


        $statuses =
            PayrollPeriod::getStatuses();

        $academicYears =
            AcademicYear::orderBy(
                'name',
                'desc'
            )->pluck(
                'name',
                'id'
            )->toArray();

        $months =
            PayrollPeriod::getMonths();

        $currentYear =
            date('Y');

        $years = [];

        for ($i = -5; $i <= 2; $i++) {

            $year =
                $currentYear + $i;

            $years[$year] =
                $year;
        }


        return view(
            'payroll.edit',
            compact(
                'payrollPeriod',
                'statuses',
                'academicYears',
                'months',
                'years'
            )
        );
    }


    /**
     * ============================================================
     * UPDATE
     * ============================================================
     */
    public function update(
        Request $request,
        PayrollPeriod $payrollPeriod
    ) {

        if (!$payrollPeriod->isEditable()) {

            return redirect()
                ->route(
                    'payroll-periods.index'
                )
                ->with(
                    'error',
                    'This payroll period cannot be updated.'
                );
        }


        $validated =
            $request->validate([

                'period_code' => [
                    'required',
                    'string',
                    Rule::unique(
                        'payroll_periods',
                        'period_code'
                    )->ignore(
                        $payrollPeriod->id
                    )
                ],

                'name' => [
                    'required',
                    'string',
                    'max:255'
                ],

                'academic_year_id' => [
                    'required',
                    'exists:academic_years,id'
                ],

                'month' => [
                    'required',
                    'integer',
                    'between:1,12'
                ],

                'year' => [
                    'required',
                    'integer',
                    'min:2000',
                    'max:2100'
                ],

                'start_date' => [
                    'required',
                    'date',
                    'before_or_equal:end_date'
                ],

                'end_date' => [
                    'required',
                    'date',
                    'after_or_equal:start_date'
                ],

                'payment_date' => [
                    'nullable',
                    'date',
                    'after_or_equal:end_date'
                ],

                'status' => [
                    'required',
                    Rule::in(
                        array_keys(
                            PayrollPeriod::getStatuses()
                        )
                    )
                ],

                'description' => [
                    'nullable',
                    'string'
                ],
            ]);


        $payrollPeriod->update(
            $validated
        );


        return redirect()
            ->route(
                'payroll-periods.show',
                $payrollPeriod->id
            )
            ->with(
                'success',
                'Payroll period updated successfully.'
            );
    }


    /**
     * ============================================================
     * DELETE
     * ============================================================
     */
    public function destroy(
        PayrollPeriod $payrollPeriod
    ) {

        if (!$payrollPeriod->isDraft()) {

            return redirect()
                ->route(
                    'payroll-periods.index'
                )
                ->with(
                    'error',
                    'Only draft payroll periods can be deleted.'
                );
        }


        $payrollPeriod->delete();


        return redirect()
            ->route(
                'payroll-periods.index'
            )
            ->with(
                'success',
                'Payroll period deleted successfully.'
            );
    }


    /**
     * ============================================================
     * SUBMIT FOR APPROVAL
     * ============================================================
     *
     * This is the important new method.
     *
     * Draft / Processing / Rejected
     *             ↓
     *       Pending Approval
     *
     */
    public function submitForApproval($id)
    {
        try {

            DB::beginTransaction();


            $payrollPeriod =
                PayrollPeriod::with('staff')
                    ->lockForUpdate()
                    ->findOrFail($id);


            /*
            |--------------------------------------------------------------------------
            | Check Current Status
            |--------------------------------------------------------------------------
            */

            if (
                !in_array(
                    $payrollPeriod->status,
                    [
                        PayrollPeriod::STATUS_DRAFT,
                        PayrollPeriod::STATUS_PROCESSING,
                        PayrollPeriod::STATUS_REJECTED,
                    ],
                    true
                )
            ) {

                DB::rollBack();

                return redirect()
                    ->route(
                        'payroll-periods.show',
                        $payrollPeriod->id
                    )
                    ->with(
                        'error',
                        'This payroll period cannot be submitted for approval because it is currently "' .
                        $payrollPeriod->status .
                        '".'
                    );
            }


            /*
            |--------------------------------------------------------------------------
            | Make Sure Staff Are Assigned
            |--------------------------------------------------------------------------
            */

            if (
                !$payrollPeriod->staff ||
                $payrollPeriod->staff->count() === 0
            ) {

                DB::rollBack();

                return redirect()
                    ->route(
                        'payroll-periods.show',
                        $payrollPeriod->id
                    )
                    ->with(
                        'error',
                        'You cannot submit this payroll period because no staff have been assigned.'
                    );
            }


            /*
            |--------------------------------------------------------------------------
            | Change Status
            |--------------------------------------------------------------------------
            */

            $payrollPeriod->status =
                PayrollPeriod::STATUS_PENDING_APPROVAL;


            /*
            |--------------------------------------------------------------------------
            | Clear Previous Approval Information
            |--------------------------------------------------------------------------
            */

            $payrollPeriod->approved_by =
                null;

            $payrollPeriod->approved_at =
                null;


            $payrollPeriod->save();


            DB::commit();


            return redirect()
                ->route(
                    'payroll-periods.show',
                    $payrollPeriod->id
                )
                ->with(
                    'success',
                    'Payroll period submitted for approval successfully.'
                );

        } catch (\Exception $e) {

            DB::rollBack();

            return redirect()
                ->back()
                ->with(
                    'error',
                    'Unable to submit payroll period: ' .
                    $e->getMessage()
                );
        }
    }


    /**
     * ============================================================
     * APPROVE
     * ============================================================
     *
     * ONLY pending_approval can be approved.
     *
     */
    public function approve($id)
    {
        try {

            DB::beginTransaction();


            $payrollPeriod =
                PayrollPeriod::lockForUpdate()
                    ->findOrFail($id);


            /*
            |--------------------------------------------------------------------------
            | Must Be Pending Approval
            |--------------------------------------------------------------------------
            */

            if (
                $payrollPeriod->status !==
                PayrollPeriod::STATUS_PENDING_APPROVAL
            ) {

                DB::rollBack();

                return redirect()
                    ->route(
                        'payroll-periods.show',
                        $payrollPeriod->id
                    )
                    ->with(
                        'error',
                        'Only payroll periods pending approval can be approved.'
                    );
            }


            /*
            |--------------------------------------------------------------------------
            | Approve
            |--------------------------------------------------------------------------
            */

            $payrollPeriod->status =
                PayrollPeriod::STATUS_APPROVED;


            /*
            |--------------------------------------------------------------------------
            | Store Approver
            |--------------------------------------------------------------------------
            */

            $payrollPeriod->approved_by =
                Auth::id();

            $payrollPeriod->approved_at =
                now();


            $payrollPeriod->save();


            DB::commit();


            return redirect()
                ->route(
                    'payroll-periods.show',
                    $payrollPeriod->id
                )
                ->with(
                    'success',
                    'Payroll period approved successfully.'
                );

        } catch (\Exception $e) {

            DB::rollBack();

            return redirect()
                ->back()
                ->with(
                    'error',
                    'Unable to approve payroll period: ' .
                    $e->getMessage()
                );
        }
    }


    /**
     * ============================================================
     * REJECT
     * ============================================================
     */
    public function reject($id)
    {
        try {

            DB::beginTransaction();


            $payrollPeriod =
                PayrollPeriod::lockForUpdate()
                    ->findOrFail($id);


            /*
            |--------------------------------------------------------------------------
            | Must Be Pending Approval
            |--------------------------------------------------------------------------
            */

            if (
                $payrollPeriod->status !==
                PayrollPeriod::STATUS_PENDING_APPROVAL
            ) {

                DB::rollBack();

                return redirect()
                    ->route(
                        'payroll-periods.show',
                        $payrollPeriod->id
                    )
                    ->with(
                        'error',
                        'Only payroll periods pending approval can be rejected.'
                    );
            }


            /*
            |--------------------------------------------------------------------------
            | Reject
            |--------------------------------------------------------------------------
            */

            $payrollPeriod->status =
                PayrollPeriod::STATUS_REJECTED;


            /*
            |--------------------------------------------------------------------------
            | Clear Approval Information
            |--------------------------------------------------------------------------
            */

            $payrollPeriod->approved_by =
                null;

            $payrollPeriod->approved_at =
                null;


            $payrollPeriod->save();


            DB::commit();


            return redirect()
                ->route(
                    'payroll-periods.show',
                    $payrollPeriod->id
                )
                ->with(
                    'success',
                    'Payroll period rejected successfully. It can now be edited and resubmitted.'
                );

        } catch (\Exception $e) {

            DB::rollBack();

            return redirect()
                ->back()
                ->with(
                    'error',
                    'Unable to reject payroll period: ' .
                    $e->getMessage()
                );
        }
    }


    /**
     * ============================================================
     * REMOVE STAFF
     * ============================================================
     */
    public function removeStaff(
        Request $request,
        $id
    ) {

        try {

            $payrollPeriod =
                PayrollPeriod::findOrFail($id);


            if (!$payrollPeriod->isEditable()) {

                return response()->json([
                    'success' => false,
                    'message' =>
                        'This payroll period cannot be modified.'
                ], 403);
            }


            $staffIds =
                $request->input(
                    'staff_ids'
                );


            /*
            |--------------------------------------------------------------------------
            | Normalize Staff IDs
            |--------------------------------------------------------------------------
            */

            if (is_string($staffIds)) {

                if (
                    strpos(
                        $staffIds,
                        ','
                    ) !== false
                ) {

                    $staffIds =
                        array_map(
                            'trim',
                            explode(
                                ',',
                                $staffIds
                            )
                        );

                } elseif (
                    strpos(
                        $staffIds,
                        '['
                    ) === 0
                ) {

                    $staffIds =
                        json_decode(
                            $staffIds,
                            true
                        );

                    if (!is_array($staffIds)) {

                        $staffIds =
                            [$staffIds];
                    }

                } else {

                    $staffIds =
                        [$staffIds];
                }

            } elseif (
                !is_array($staffIds)
            ) {

                return response()->json([
                    'success' => false,
                    'message' =>
                        'Invalid staff IDs provided.'
                ], 400);
            }


            /*
            |--------------------------------------------------------------------------
            | Validate IDs
            |--------------------------------------------------------------------------
            */

            $validStaffIds =
                array_filter(
                    $staffIds,
                    function ($id) {

                        return is_numeric($id)
                            && $id > 0;
                    }
                );


            if (
                empty($validStaffIds)
            ) {

                return response()->json([
                    'success' => false,
                    'message' =>
                        'No valid staff IDs provided.'
                ], 400);
            }


            /*
            |--------------------------------------------------------------------------
            | Detach Staff
            |--------------------------------------------------------------------------
            */

            $detachedCount =
                $payrollPeriod
                    ->staff()
                    ->detach(
                        $validStaffIds
                    );


            /*
            |--------------------------------------------------------------------------
            | Get Removed Names
            |--------------------------------------------------------------------------
            */

            $removedStaff =
                Staff::whereIn(
                    'id',
                    $validStaffIds
                )->get();


            $removedNames =
                $removedStaff
                    ->pluck('name')
                    ->implode(', ');


            $payrollPeriod->refresh();


            return response()->json([

                'success' =>
                    true,

                'message' =>
                    $detachedCount .
                    ' staff member(s) removed successfully.',

                'removed_count' =>
                    $detachedCount,

                'removed_names' =>
                    $removedNames,

                'remaining_count' =>
                    $payrollPeriod
                        ->staff
                        ->count(),
            ]);

        } catch (\Exception $e) {

            return response()->json([

                'success' =>
                    false,

                'message' =>
                    'Error removing staff: ' .
                    $e->getMessage(),

            ], 500);
        }
    }


    /**
     * ============================================================
     * PROCESS PAYROLL
     * ============================================================
     */
    public function process(
        PayrollPeriod $payrollPeriod
    ) {

        if (
            $payrollPeriod->status !==
            PayrollPeriod::STATUS_DRAFT
        ) {

            return redirect()
                ->route(
                    'payroll-periods.show',
                    $payrollPeriod->id
                )
                ->with(
                    'error',
                    'Only draft periods can be processed.'
                );
        }


        $payrollPeriod->update([

            'status' =>
                PayrollPeriod::STATUS_PROCESSING,

        ]);


        return redirect()
            ->route(
                'payroll-periods.show',
                $payrollPeriod->id
            )
            ->with(
                'success',
                'Payroll period is now being processed.'
            );
    }


    /**
     * ============================================================
     * MARK AS PAID
     * ============================================================
     */
    public function markAsPaid(
        PayrollPeriod $payrollPeriod
    ) {

        if (
            $payrollPeriod->status !==
            PayrollPeriod::STATUS_APPROVED
        ) {

            return redirect()
                ->route(
                    'payroll-periods.show',
                    $payrollPeriod->id
                )
                ->with(
                    'error',
                    'Only approved periods can be marked as paid.'
                );
        }


        $payrollPeriod->update([

            'status' =>
                PayrollPeriod::STATUS_PAID,

        ]);


        return redirect()
            ->route(
                'payroll-periods.show',
                $payrollPeriod->id
            )
            ->with(
                'success',
                'Payroll period marked as paid.'
            );
    }


    /**
     * ============================================================
     * CANCEL
     * ============================================================
     */
    public function cancel(
        PayrollPeriod $payrollPeriod
    ) {

        if (
            $payrollPeriod->status ===
            PayrollPeriod::STATUS_PAID
        ) {

            return redirect()
                ->route(
                    'payroll-periods.show',
                    $payrollPeriod->id
                )
                ->with(
                    'error',
                    'Paid periods cannot be cancelled.'
                );
        }


        $payrollPeriod->update([

            'status' =>
                PayrollPeriod::STATUS_CANCELLED,

        ]);


        return redirect()
            ->route(
                'payroll-periods.show',
                $payrollPeriod->id
            )
            ->with(
                'success',
                'Payroll period cancelled successfully.'
            );
    }


    /**
     * ============================================================
     * GET MONTHS FOR YEAR
     * ============================================================
     */
    public function getMonthsForYear(
        Request $request
    ) {

        $yearId =
            $request->input(
                'academic_year_id'
            );


        $monthsFromDb =
            PayrollPeriod::where(
                'academic_year_id',
                $yearId
            )
            ->distinct()
            ->orderBy('month')
            ->pluck('month')
            ->toArray();


        $allMonths =
            PayrollPeriod::getMonths();


        if (
            !empty($monthsFromDb)
        ) {

            $months = [];

            foreach (
                $monthsFromDb
                as $monthNumber
            ) {

                if (
                    isset(
                        $allMonths[
                            $monthNumber
                        ]
                    )
                ) {

                    $months[
                        $monthNumber
                    ] =
                        $allMonths[
                            $monthNumber
                        ];
                }
            }

        } else {

            $months =
                $allMonths;
        }


        return response()->json(
            $months
        );
    }


    /**
     * ============================================================
     * EXPORT
     * ============================================================
     */
    public function export(
        PayrollPeriod $payrollPeriod
    ) {

        try {

            $payrollPeriod->load(
                'staff'
            );


            $headers = [

                'Content-Type' =>
                    'text/csv',

                'Content-Disposition' =>
                    'attachment; filename="payroll_' .
                    $payrollPeriod->period_code .
                    '.csv"',
            ];


            $callback =
                function () use (
                    $payrollPeriod
                ) {

                    $file =
                        fopen(
                            'php://output',
                            'w'
                        );


                    fputcsv(
                        $file,
                        [
                            'Staff Name',
                            'Position',
                            'Basic Salary',
                            'Allowances',
                            'Overtime',
                            'Gross Pay',
                            'Tax',
                            'Pension',
                            'Deductions',
                            'Net Pay',
                            'Worked Days'
                        ]
                    );


                    foreach (
                        $payrollPeriod->staff
                        as $staff
                    ) {

                        fputcsv(
                            $file,
                            [

                                $staff->name
                                    ?? 'N/A',

                                $staff->position
                                    ?? 'N/A',

                                number_format(
                                    $staff->pivot->basic_salary
                                    ?? $staff->pivot->amount
                                    ?? 0,
                                    2
                                ),

                                number_format(
                                    $staff->pivot->allowances
                                    ?? 0,
                                    2
                                ),

                                number_format(
                                    $staff->pivot->overtime
                                    ?? 0,
                                    2
                                ),

                                number_format(
                                    $staff->pivot->gross_pay
                                    ?? 0,
                                    2
                                ),

                                number_format(
                                    $staff->pivot->tax
                                    ?? 0,
                                    2
                                ),

                                number_format(
                                    $staff->pivot->pension
                                    ?? 0,
                                    2
                                ),

                                number_format(
                                    $staff->pivot->deductions
                                    ?? 0,
                                    2
                                ),

                                number_format(
                                    $staff->pivot->net_pay
                                    ?? $staff->pivot->amount
                                    ?? 0,
                                    2
                                ),

                                $staff->pivot->worked_days
                                    ?? 0,
                            ]
                        );
                    }


                    fclose($file);
                };


            return response()->stream(
                $callback,
                200,
                $headers
            );

        } catch (\Exception $e) {

            return redirect()
                ->route(
                    'payroll-periods.index'
                )
                ->with(
                    'error',
                    'Error exporting payroll data: ' .
                    $e->getMessage()
                );
        }
    }


    /**
     * ============================================================
     * GENERATE PAYROLL
     * ============================================================
     */
    public function generatePayroll(
        PayrollPeriod $payrollPeriod
    ) {

        try {

            $staff =
                Staff::where(
                    'status',
                    'active'
                )->get();


            $payrollPeriod
                ->staff()
                ->detach();


            foreach (
                $staff as $staffMember
            ) {

                $basicSalary =
                    $staffMember->basic_salary
                    ?? 0;

                $allowances =
                    $staffMember->allowances
                    ?? 0;

                $grossPay =
                    $basicSalary +
                    $allowances;

                $tax =
                    $this->calculateTax(
                        $grossPay
                    );

                $pension =
                    $this->calculatePension(
                        $grossPay
                    );

                $deductions =
                    $tax +
                    $pension;

                $netPay =
                    $grossPay -
                    $deductions;


                $payrollPeriod
                    ->staff()
                    ->attach(
                        $staffMember->id,
                        [

                            'basic_salary' =>
                                $basicSalary,

                            'allowances' =>
                                $allowances,

                            'overtime' =>
                                0,

                            'gross_pay' =>
                                $grossPay,

                            'tax' =>
                                $tax,

                            'pension' =>
                                $pension,

                            'deductions' =>
                                $deductions,

                            'net_pay' =>
                                $netPay,

                            'worked_days' =>
                                22,
                        ]
                    );
            }


            return redirect()
                ->route(
                    'payroll-periods.show',
                    $payrollPeriod->id
                )
                ->with(
                    'success',
                    'Payroll generated successfully for ' .
                    $staff->count() .
                    ' staff members.'
                );

        } catch (\Exception $e) {

            return redirect()
                ->route(
                    'payroll-periods.show',
                    $payrollPeriod->id
                )
                ->with(
                    'error',
                    'Error generating payroll: ' .
                    $e->getMessage()
                );
        }
    }


    /**
     * ============================================================
     * ASSIGN STAFF FORM
     * ============================================================
     */
    public function assignStaffForm($id)
    {
        $payrollPeriod =
            PayrollPeriod::with(
                'staff'
            )->findOrFail($id);


        $assignedStaffIds =
            $payrollPeriod
                ->staff
                ->pluck('id')
                ->toArray();


        $availableStaff =
            Staff::whereNotIn(
                'id',
                $assignedStaffIds
            )->get();


        $selectedStaff =
            $payrollPeriod
                ->staff
                ->pluck('id')
                ->toArray();


        return view(
            'payroll.assign-staff',
            compact(
                'payrollPeriod',
                'availableStaff',
                'selectedStaff'
            )
        );
    }


    /**
     * ============================================================
     * ASSIGN STAFF
     * ============================================================
     */
    public function assignStaff(
        Request $request,
        $id
    ) {

        $payrollPeriod =
            PayrollPeriod::findOrFail($id);


        if (!$payrollPeriod->isEditable()) {

            return redirect()
                ->route(
                    'payroll-periods.show',
                    $payrollPeriod->id
                )
                ->with(
                    'error',
                    'Staff cannot be assigned to this payroll period because it is no longer editable.'
                );
        }


        $request->validate([

            'staff_ids' => [
                'required',
                'array'
            ],

            'staff_ids.*' => [
                'exists:staff,id'
            ],

        ]);


        $basicSalary =
            $request->input(
                'basic_salary',
                0
            );

        $allowances =
            $request->input(
                'allowances',
                0
            );

        $workedDays =
            $request->input(
                'worked_days',
                22
            );


        $assignedCount = 0;


        foreach (
            $request->staff_ids
            as $staffId
        ) {

            $exists =
                DB::table(
                    'payroll_period_staff'
                )
                ->where(
                    'payroll_period_id',
                    $payrollPeriod->id
                )
                ->where(
                    'staff_id',
                    $staffId
                )
                ->exists();


            if (!$exists) {

                $grossPay =
                    $basicSalary +
                    $allowances;


                $tax =
                    $this->calculateTax(
                        $grossPay
                    );


                $pension =
                    $this->calculatePension(
                        $grossPay
                    );


                $deductions =
                    $tax +
                    $pension;


                $netPay =
                    $grossPay -
                    $deductions;


                $payrollPeriod
                    ->staff()
                    ->attach(
                        $staffId,
                        [

                            'basic_salary' =>
                                $basicSalary,

                            'allowances' =>
                                $allowances,

                            'overtime' =>
                                0,

                            'worked_days' =>
                                $workedDays,

                            'gross_pay' =>
                                $grossPay,

                            'tax' =>
                                $tax,

                            'pension' =>
                                $pension,

                            'deductions' =>
                                $deductions,

                            'net_pay' =>
                                $netPay,
                        ]
                    );


                $assignedCount++;
            }
        }


        $message =
            $assignedCount > 0

                ? "{$assignedCount} staff member(s) assigned successfully!"

                : "No new staff were assigned (they may already be assigned).";


        return redirect()
            ->route(
                'payroll-periods.show',
                $payrollPeriod->id
            )
            ->with(
                'success',
                $message
            );
    }


    /**
     * ============================================================
     * TAX
     * ============================================================
     */
    private function calculateTax(
        $grossPay
    ) {

        if (
            $grossPay <= 0
        ) {

            return 0;
        }


        $tax = 0;


        if (
            $grossPay > 5000
        ) {

            $tax +=
                ($grossPay - 5000)
                * 0.30;

            $grossPay =
                5000;
        }


        if (
            $grossPay > 3000
        ) {

            $tax +=
                ($grossPay - 3000)
                * 0.20;

            $grossPay =
                3000;
        }


        if (
            $grossPay > 1000
        ) {

            $tax +=
                ($grossPay - 1000)
                * 0.10;
        }


        return round(
            $tax,
            2
        );
    }


    /**
     * ============================================================
     * PENSION
     * ============================================================
     */
    private function calculatePension(
        $grossPay
    ) {

        return round(
            $grossPay * 0.05,
            2
        );
    }
}