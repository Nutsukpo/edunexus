<?php

namespace App\Http\Controllers;

use App\Models\PayrollPeriod;
use App\Models\SalaryStructure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PayrollPeriodApprovalController extends Controller
{
    /*
 * Display all payroll periods for approval/review.
 */
    public function index(Request $request)
    {
        $query = PayrollPeriod::query()
            ->with([
                'academicYear',
                'createdBy',
                'approvedBy',
            ])
            ->withCount('staff');

        /*
        |--------------------------------------------------------------------------
        | ALL PAYROLL STATUSES
        |--------------------------------------------------------------------------
        */

        $query->whereIn('status', [
            PayrollPeriod::STATUS_DRAFT,
            PayrollPeriod::STATUS_PROCESSING,
            PayrollPeriod::STATUS_PENDING_APPROVAL,
            PayrollPeriod::STATUS_APPROVED,
            PayrollPeriod::STATUS_REJECTED,
            PayrollPeriod::STATUS_PAID,
        ]);

        /*
        |--------------------------------------------------------------------------
        | FILTERS
        |--------------------------------------------------------------------------
        */

        if ($request->filled('academic_year_id')) {
            $query->where(
                'academic_year_id',
                $request->academic_year_id
            );
        }

        if ($request->filled('year')) {
            $query->where(
                'year',
                $request->year
            );
        }

        if ($request->filled('month')) {
            $query->where(
                'month',
                $request->month
            );
        }

        if ($request->filled('status')) {
            $query->where(
                'status',
                $request->status
            );
        }

        /*
        |--------------------------------------------------------------------------
        | PAGINATION
        |--------------------------------------------------------------------------
        */

        $payrollPeriods = $query
            ->orderByDesc('year')
            ->orderByDesc('month')
            ->orderByDesc('id')
            ->paginate(15)
            ->withQueryString();

        /*
        |--------------------------------------------------------------------------
        | LOAD ONLY REAL PAYROLL PIVOT COLUMNS
        |--------------------------------------------------------------------------
        |
        | IMPORTANT:
        | There is NO "amount" column in payroll_period_staff.
        |
        */

        $payrollPeriods->getCollection()->load([
            'staff' => function ($query) {
                $query->withPivot([
                    'basic_salary',
                    'allowances',
                    'overtime',
                    'gross_pay',
                    'tax',
                    'pension',
                    'deductions',
                    'net_pay',
                    'worked_days',
                ]);
            },
        ]);

        /*
        |--------------------------------------------------------------------------
        | CALCULATE DISPLAY PAYROLL AMOUNT
        |--------------------------------------------------------------------------
        |
        | Payroll Amount = TOTAL NET PAY
        |
        */

        $payrollPeriods->getCollection()->each(
            function ($payrollPeriod) {

                $payrollPeriod->display_payroll_amount =
                    $payrollPeriod->staff->sum(
                        function ($staff) {

                            return (float) (
                                $staff->pivot->net_pay ?? 0
                            );
                        }
                    );
            }
        );

        /*
        |--------------------------------------------------------------------------
        | RETURN APPROVAL INDEX
        |--------------------------------------------------------------------------
        */

        return view(
            'payroll-period-approvals.index',
            compact('payrollPeriods')
        );
    }


    /**
     * ============================================================
     * SHOW PAYROLL FOR APPROVAL
     * ============================================================
     *
     * IMPORTANT:
     *
     * SalaryStructure is the PRIMARY source for salary figures.
     *
     * payroll_period_staff supplies:
     *
     * - worked_days
     * - overtime
     * - previously saved payroll values as fallback
     *
     */
    public function show($id)
    {
        /*
        |--------------------------------------------------------------------------
        | LOAD PAYROLL PERIOD
        |--------------------------------------------------------------------------
        */

        $payrollPeriod = PayrollPeriod::with([
            'academicYear',
            'createdBy',
            'approvedBy',
        ])->findOrFail($id);


        /*
        |--------------------------------------------------------------------------
        | LOAD STAFF
        |--------------------------------------------------------------------------
        */

        $payrollPeriod->load([
            'staff' => function ($query) {

                $query->withPivot([
                    'basic_salary',
                    'allowances',
                    'overtime',
                    'gross_pay',
                    'tax',
                    'pension',
                    'deductions',
                    'net_pay',
                    'worked_days',
                ]);

            },
        ]);


        /*
        |--------------------------------------------------------------------------
        | INITIALIZE
        |--------------------------------------------------------------------------
        */

        $staffWithSalaries = [];

        $totalBasicSalary = 0;
        $totalAllowances = 0;
        $totalOvertime = 0;
        $totalGrossPay = 0;
        $totalTax = 0;
        $totalPension = 0;
        $totalDeductions = 0;
        $totalNetPay = 0;
        $totalWorkedDays = 0;


        /*
        |--------------------------------------------------------------------------
        | LOOP THROUGH STAFF
        |--------------------------------------------------------------------------
        */

        foreach ($payrollPeriod->staff as $staff) {

            /*
            |--------------------------------------------------------------------------
            | FIND SALARY STRUCTURE FOR THE PAYROLL PERIOD
            |--------------------------------------------------------------------------
            |
            | We use the payroll period's end date rather than now().
            |
            | This is important.
            |
            | If the payroll is August 2026, the salary applicable to
            | August should be used, not necessarily the salary that
            | became effective after August.
            |
            */

            $effectiveDate =
                $payrollPeriod->end_date
                ?? now();


            $salaryStructure = SalaryStructure::where(
                'staff_id',
                $staff->id
            )
            ->where(
                'effective_date',
                '<=',
                $effectiveDate
            )
            ->orderBy(
                'effective_date',
                'desc'
            )
            ->first();


            /*
            |--------------------------------------------------------------------------
            | WORKED DAYS
            |--------------------------------------------------------------------------
            */

            $workedDays = (float) (
                $staff->pivot->worked_days
                ?? 22
            );


            /*
            |--------------------------------------------------------------------------
            | OVERTIME
            |--------------------------------------------------------------------------
            */

            $overtime = (float) (
                $staff->pivot->overtime
                ?? 0
            );


            /*
            |--------------------------------------------------------------------------
            | SALARY STRUCTURE FOUND
            |--------------------------------------------------------------------------
            */

            if ($salaryStructure) {

                /*
                |--------------------------------------------------------------------------
                | BASIC SALARY
                |--------------------------------------------------------------------------
                */

                $basicSalary = (float) (
                    $salaryStructure->basic_salary
                    ?? 0
                );


                /*
                |--------------------------------------------------------------------------
                | ALLOWANCES
                |--------------------------------------------------------------------------
                */

                $allowances = (float) (

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


                /*
                |--------------------------------------------------------------------------
                | GROSS PAY
                |--------------------------------------------------------------------------
                */

                $grossPay =
                    $basicSalary
                    +
                    $allowances
                    +
                    $overtime;


                /*
                |--------------------------------------------------------------------------
                | TAX
                |--------------------------------------------------------------------------
                */

                $tax = 0;

                if (
                    isset($salaryStructure->tax)
                    &&
                    $salaryStructure->tax !== null
                ) {

                    $tax = (float) (
                        $salaryStructure->tax
                    );

                } elseif (
                    method_exists(
                        $this,
                        'calculateTax'
                    )
                ) {

                    $tax = (float) (
                        $this->calculateTax(
                            $grossPay
                        )
                    );

                }


                /*
                |--------------------------------------------------------------------------
                | PENSION / SSNIT
                |--------------------------------------------------------------------------
                */

                $pension = 0;

                if (
                    isset($salaryStructure->ssnit)
                    &&
                    $salaryStructure->ssnit !== null
                ) {

                    $pension = (float) (
                        $salaryStructure->ssnit
                    );

                } elseif (
                    isset($salaryStructure->pension)
                    &&
                    $salaryStructure->pension !== null
                ) {

                    $pension = (float) (
                        $salaryStructure->pension
                    );

                } elseif (
                    method_exists(
                        $this,
                        'calculatePension'
                    )
                ) {

                    $pension = (float) (
                        $this->calculatePension(
                            $grossPay
                        )
                    );

                }


                /*
                |--------------------------------------------------------------------------
                | LOAN
                |--------------------------------------------------------------------------
                */

                $loanDeduction = (float) (
                    $salaryStructure->loan_deduction
                    ?? 0
                );


                /*
                |--------------------------------------------------------------------------
                | OTHER DEDUCTION
                |--------------------------------------------------------------------------
                */

                $otherDeduction = (float) (
                    $salaryStructure->other_deduction
                    ?? 0
                );


                /*
                |--------------------------------------------------------------------------
                | TOTAL DEDUCTIONS
                |--------------------------------------------------------------------------
                */

                $deductions =
                    $tax
                    +
                    $pension
                    +
                    $loanDeduction
                    +
                    $otherDeduction;


                /*
                |--------------------------------------------------------------------------
                | NET PAY
                |--------------------------------------------------------------------------
                */

                $netPay =
                    $grossPay
                    -
                    $deductions;

            } else {

                /*
                |--------------------------------------------------------------------------
                | FALLBACK TO PAYROLL PIVOT
                |--------------------------------------------------------------------------
                */

                $basicSalary = (float) (
                    $staff->pivot->basic_salary
                    ?? 0
                );

                $allowances = (float) (
                    $staff->pivot->allowances
                    ?? 0
                );

                $grossPay = (float) (
                    $staff->pivot->gross_pay
                    ?? (
                        $basicSalary
                        +
                        $allowances
                        +
                        $overtime
                    )
                );

                $tax = (float) (
                    $staff->pivot->tax
                    ?? 0
                );

                $pension = (float) (
                    $staff->pivot->pension
                    ?? 0
                );

                $deductions = (float) (
                    $staff->pivot->deductions
                    ?? (
                        $tax
                        +
                        $pension
                    )
                );

                $netPay = (float) (
                    $staff->pivot->net_pay
                    ?? (
                        $grossPay
                        -
                        $deductions
                    )
                );
            }


            /*
            |--------------------------------------------------------------------------
            | STAFF NAME
            |--------------------------------------------------------------------------
            */

            $staffName = trim(
                ($staff->first_name ?? '')
                . ' '
                .
                ($staff->middle_name ?? '')
                . ' '
                .
                ($staff->last_name ?? '')
            );


            if ($staffName === '') {

                $staffName =
                    $staff->name
                    ?? 'Unknown Staff';

            }


            /*
            |--------------------------------------------------------------------------
            | STAFF ID / CODE
            |--------------------------------------------------------------------------
            */

            $staffCode =
                $staff->staff_id
                ??
                $staff->staff_code
                ??
                $staff->employee_id
                ??
                $staff->id;


            /*
            |--------------------------------------------------------------------------
            | POSITION
            |--------------------------------------------------------------------------
            */

            $position =
                $staff->position
                ??
                $staff->designation
                ??
                $staff->job_title
                ??
                'N/A';


            /*
            |--------------------------------------------------------------------------
            | INITIALS
            |--------------------------------------------------------------------------
            */

            $firstInitial = strtoupper(
                substr(
                    $staff->first_name
                    ??
                    $staffName,
                    0,
                    1
                )
            );

            $lastInitial = strtoupper(
                substr(
                    $staff->last_name
                    ?? '',
                    0,
                    1
                )
            );

            $initials =
                $firstInitial
                .
                $lastInitial;


            /*
            |--------------------------------------------------------------------------
            | ADD STAFF RECORD
            |--------------------------------------------------------------------------
            */

            $staffWithSalaries[] = [

                'staff' =>
                    $staff,

                'salary_structure' =>
                    $salaryStructure,

                'staff_name' =>
                    $staffName,

                'staff_code' =>
                    $staffCode,

                'position' =>
                    $position,

                'initials' =>
                    $initials,

                'basic_salary' =>
                    $basicSalary,

                'allowances' =>
                    $allowances,

                'overtime' =>
                    $overtime,

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


            /*
            |--------------------------------------------------------------------------
            | TOTALS
            |--------------------------------------------------------------------------
            */

            $totalBasicSalary +=
                $basicSalary;

            $totalAllowances +=
                $allowances;

            $totalOvertime +=
                $overtime;

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


        /*
        |--------------------------------------------------------------------------
        | SUMMARY
        |--------------------------------------------------------------------------
        */

        $totalStaff =
            count(
                $staffWithSalaries
            );


        $averageNetSalary =
            $totalStaff > 0
                ? $totalNetPay / $totalStaff
                : 0;


        $summary = [

            'total_staff' =>
                $totalStaff,

            'total_basic_salary' =>
                $totalBasicSalary,

            'total_allowances' =>
                $totalAllowances,

            'total_overtime' =>
                $totalOvertime,

            'total_gross' =>
                $totalGrossPay,

            'total_tax' =>
                $totalTax,

            'total_pension' =>
                $totalPension,

            'total_deductions' =>
                $totalDeductions,

            'total_net' =>
                $totalNetPay,

            'total_worked_days' =>
                $totalWorkedDays,

            'avg_salary' =>
                $averageNetSalary,

        ];


        /*
        |--------------------------------------------------------------------------
        | TOP EARNERS
        |--------------------------------------------------------------------------
        */

        $topEarnersArray =
            collect(
                $staffWithSalaries
            )
            ->sortByDesc(
                'net_pay'
            )
            ->take(5)
            ->values()
            ->toArray();


        /*
        |--------------------------------------------------------------------------
        | RETURN VIEW
        |--------------------------------------------------------------------------
        */

        return view(
            'payroll-period-approvals.show',
            compact(
                'payrollPeriod',
                'staffWithSalaries',
                'summary',
                'topEarnersArray'
            )
        );
    }


    /**
     * ============================================================
     * APPROVE
     * ============================================================
     */
    public function approve($id)
    {
        $payrollPeriod =
            PayrollPeriod::findOrFail($id);


        if (
            $payrollPeriod->status
            !== PayrollPeriod::STATUS_PENDING_APPROVAL
        ) {

            return back()->with(
                'error',
                'Only payroll periods pending approval can be approved.'
            );
        }


        DB::transaction(function () use (
            $payrollPeriod
        ) {

            $payrollPeriod->update([

                'status' =>
                    PayrollPeriod::STATUS_APPROVED,

                'approved_by' =>
                    Auth::id(),

                'approved_at' =>
                    now(),

            ]);

        });


        return redirect()
            ->route(
                'payroll-period-approvals.show',
                $payrollPeriod->id
            )
            ->with(
                'success',
                'Payroll period approved successfully.'
            );
    }


    /**
     * ============================================================
     * REJECT
     * ============================================================
     */
    public function reject(
        Request $request,
        $id
    ) {

        $payrollPeriod =
            PayrollPeriod::findOrFail($id);


        if (
            $payrollPeriod->status
            !== PayrollPeriod::STATUS_PENDING_APPROVAL
        ) {

            return back()->with(
                'error',
                'Only payroll periods pending approval can be rejected.'
            );
        }


        $validated =
            $request->validate([
                'rejection_reason' => [
                    'required',
                    'string',
                    'max:2000',
                ],
            ]);


        DB::transaction(function () use (
            $payrollPeriod,
            $validated
        ) {

            $existingDescription =
                trim(
                    $payrollPeriod->description
                    ?? ''
                );


            $newDescription =
                $existingDescription !== ''
                    ? $existingDescription
                        . "\n\n"
                    : '';


            $newDescription .=
                'Rejection Reason: '
                .
                trim(
                    $validated[
                        'rejection_reason'
                    ]
                );


            $payrollPeriod->update([

                'status' =>
                    PayrollPeriod::STATUS_REJECTED,

                'description' =>
                    $newDescription,

            ]);

        });


        return redirect()
            ->route(
                'payroll-period-approvals.show',
                $payrollPeriod->id
            )
            ->with(
                'success',
                'Payroll period rejected successfully.'
            );
    }


    /**
     * ============================================================
     * RESUBMIT
     * ============================================================
     */
    public function resubmit($id)
    {
        $payrollPeriod =
            PayrollPeriod::findOrFail($id);


        if (
            $payrollPeriod->status
            !== PayrollPeriod::STATUS_REJECTED
        ) {

            return back()->with(
                'error',
                'Only rejected payroll periods can be resubmitted.'
            );
        }


        $payrollPeriod->update([

            'status' =>
                PayrollPeriod::STATUS_PENDING_APPROVAL,

        ]);


        return redirect()
            ->route(
                'payroll-period-approvals.show',
                $payrollPeriod->id
            )
            ->with(
                'success',
                'Payroll period resubmitted for approval.'
            );
    }
}