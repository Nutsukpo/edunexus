<?php

namespace App\Http\Controllers;

use App\Models\PayrollAdjustment;
use App\Models\PayrollItem;
use App\Models\PayrollPeriod;
use App\Models\Payslip;
use App\Models\Staff;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

class PayrollController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | INDEX
    |--------------------------------------------------------------------------
    */

    public function index(Request $request)
    {
        $query = PayrollPeriod::with([
            'academicYear',
            'createdBy',
            'approvedBy',
        ]);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('month')) {
            $query->where('month', $request->month);
        }

        if ($request->filled('year')) {
            $query->where('year', $request->year);
        }

        if ($request->filled('search')) {
            $search = trim($request->search);

            $query->where(function ($q) use ($search) {
                $q->where('name', 'LIKE', "%{$search}%")
                    ->orWhere('period_code', 'LIKE', "%{$search}%");
            });
        }

        $payrollPeriods = $query
            ->latest('created_at')
            ->paginate(15)
            ->withQueryString();

        return view('payroll.index', compact('payrollPeriods'));
    }


    /*
    |--------------------------------------------------------------------------
    | CREATE
    |--------------------------------------------------------------------------
    */

    public function create()
    {
        $staff = Staff::query()
            ->where('status', 'active')
            ->orderBy('first_name')
            ->orderBy('last_name')
            ->get();

        return view('payroll.create', compact('staff'));
    }


    /*
    |--------------------------------------------------------------------------
    | STORE
    |--------------------------------------------------------------------------
    */

    public function store(Request $request)
    {
        /*
        |--------------------------------------------------------------------------
        | Collect selected staff
        |--------------------------------------------------------------------------
        */

        $staffIds = $request->input(
            'staff_ids_hidden',
            $request->input('staff_ids', [])
        );

        $basicSalaries = $request->input(
            'basic_salaries_hidden',
            $request->input('basic_salaries', [])
        );

        $workedDays = $request->input(
            'attendance_days_hidden',
            $request->input(
                'worked_days',
                $request->input('attendance_days', [])
            )
        );

        if (!is_array($staffIds)) {
            $staffIds = [];
        }

        if (!is_array($basicSalaries)) {
            $basicSalaries = [];
        }

        if (!is_array($workedDays)) {
            $workedDays = [];
        }

        $cleanedStaffIds = [];
        $cleanedSalaries = [];
        $cleanedWorkedDays = [];

        foreach ($staffIds as $index => $staffId) {

            if (empty($staffId)) {
                continue;
            }

            $salary = $basicSalaries[$index] ?? 0;
            $days = $workedDays[$index] ?? 0;

            $cleanedStaffIds[] = (int) $staffId;
            $cleanedSalaries[] = is_numeric($salary)
                ? (float) $salary
                : 0;

            $cleanedWorkedDays[] = is_numeric($days)
                ? (int) $days
                : 0;
        }

        if (empty($cleanedStaffIds)) {
            return redirect()
                ->back()
                ->with('error', 'Please select at least one staff member.')
                ->withInput();
        }


        /*
        |--------------------------------------------------------------------------
        | Validate Payroll Period
        |--------------------------------------------------------------------------
        */

        $validator = Validator::make(
            $request->all(),
            [
                'name' => [
                    'required',
                    'string',
                    'max:255',
                ],

                'academic_year_id' => [
                    'nullable',
                    'integer',
                    'exists:academic_years,id',
                ],

                'month' => [
                    'nullable',
                    'integer',
                    'between:1,12',
                ],

                'year' => [
                    'nullable',
                    'integer',
                    'between:2000,2100',
                ],

                'start_date' => [
                    'required',
                    'date',
                ],

                'end_date' => [
                    'required',
                    'date',
                    'after_or_equal:start_date',
                ],

                'payment_date' => [
                    'nullable',
                    'date',
                    'after_or_equal:end_date',
                ],

                'description' => [
                    'nullable',
                    'string',
                ],

                'status' => [
                    'nullable',
                    'in:draft,processing,pending_approval,approved,rejected,paid,cancelled',
                ],
            ]
        );

        if ($validator->fails()) {
            return redirect()
                ->back()
                ->withErrors($validator)
                ->withInput();
        }


        /*
        |--------------------------------------------------------------------------
        | Validate Staff
        |--------------------------------------------------------------------------
        */

        $staffValidator = Validator::make(
            [
                'staff_ids' => $cleanedStaffIds,
                'basic_salaries' => $cleanedSalaries,
                'worked_days' => $cleanedWorkedDays,
            ],
            [
                'staff_ids' => [
                    'required',
                    'array',
                    'min:1',
                ],

                'staff_ids.*' => [
                    'required',
                    'integer',
                    'exists:staff,id',
                ],

                'basic_salaries' => [
                    'required',
                    'array',
                ],

                'basic_salaries.*' => [
                    'numeric',
                    'min:0',
                ],

                'worked_days' => [
                    'required',
                    'array',
                ],

                'worked_days.*' => [
                    'integer',
                    'min:0',
                ],
            ]
        );

        if ($staffValidator->fails()) {
            return redirect()
                ->back()
                ->withErrors($staffValidator)
                ->withInput();
        }


        /*
        |--------------------------------------------------------------------------
        | Resolve Authenticated User -> Staff
        |--------------------------------------------------------------------------
        |
        | IMPORTANT:
        | users table does not contain staff_id.
        |
        | User and Staff are linked through email.
        |
        */

        $creatorStaff = $this->authenticatedStaff();

        if (!$creatorStaff) {
            return redirect()
                ->back()
                ->with(
                    'error',
                    'Your user account is not linked to a staff record. Please contact the system administrator.'
                )
                ->withInput();
        }


        /*
        |--------------------------------------------------------------------------
        | Create Payroll
        |--------------------------------------------------------------------------
        */

        try {

            DB::beginTransaction();

            $startDate = Carbon::parse($request->start_date);
            $endDate = Carbon::parse($request->end_date);

            $payrollPeriod = new PayrollPeriod();

            $payrollPeriod->period_code =
                PayrollPeriod::generatePeriodCode();

            $payrollPeriod->name =
                $request->name;

            $payrollPeriod->academic_year_id =
                $request->academic_year_id;

            $payrollPeriod->month =
                $request->month ?: $startDate->month;

            $payrollPeriod->year =
                $request->year ?: $startDate->year;

            $payrollPeriod->start_date =
                $request->start_date;

            $payrollPeriod->end_date =
                $request->end_date;

            $payrollPeriod->payment_date =
                $request->payment_date;

            $payrollPeriod->status =
                $request->status ?: PayrollPeriod::STATUS_DRAFT;

            $payrollPeriod->description =
                $request->description;

            /*
             * created_by expects staff.id
             */
            $payrollPeriod->created_by =
                $creatorStaff->id;

            $payrollPeriod->save();


            /*
            |--------------------------------------------------------------------------
            | Create Payroll Items
            |--------------------------------------------------------------------------
            */

            foreach ($cleanedStaffIds as $index => $staffId) {

                $staffMember = Staff::find($staffId);

                if (!$staffMember) {
                    continue;
                }

                $basicSalary =
                    (float) ($cleanedSalaries[$index] ?? 0);

                $workedDays =
                    (int) ($cleanedWorkedDays[$index] ?? 0);

                $payrollItem = new PayrollItem();

                $payrollItem->payroll_period_id =
                    $payrollPeriod->id;

                $payrollItem->staff_id =
                    $staffMember->id;

                $payrollItem->basic_salary =
                    $basicSalary;

                $payrollItem->allowances =
                    0;

                $payrollItem->overtime =
                    0;

                $payrollItem->gross_pay =
                    $basicSalary;

                $payrollItem->tax =
                    0;

                $payrollItem->pension =
                    0;

                $payrollItem->deductions =
                    0;

                $payrollItem->net_pay =
                    $basicSalary;

                $payrollItem->worked_days =
                    $workedDays;

                $payrollItem->status =
                    'pending';

                $payrollItem->notes =
                    null;

                $payrollItem->save();
            }

            DB::commit();

            return redirect()
                ->route('payroll.show', $payrollPeriod->id)
                ->with(
                    'success',
                    'Payroll period created successfully.'
                );

        } catch (\Throwable $e) {

            DB::rollBack();

            report($e);

            return redirect()
                ->back()
                ->with(
                    'error',
                    'Failed to create payroll period: ' .
                    $e->getMessage()
                )
                ->withInput();
        }
    }


    /*
    |--------------------------------------------------------------------------
    | ATTENDANCE DATA
    |--------------------------------------------------------------------------
    */

    public function getAttendanceData(Request $request)
    {
        $validator = Validator::make(
            $request->all(),
            [
                'start_date' => 'required|date',
                'end_date' => 'required|date|after_or_equal:start_date',
            ]
        );

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid date range.',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {

            $staff = Staff::query()
                ->where('status', 'active')
                ->orderBy('first_name')
                ->orderBy('last_name')
                ->get();

            $data = [];

            foreach ($staff as $staffMember) {

                $workedDays = $this->calculateAttendanceDays(
                    $staffMember->id,
                    $request->start_date,
                    $request->end_date
                );

                $data[] = [
                    'staff_id' => $staffMember->id,
                    'staff_code' => $staffMember->staff_id,
                    'name' => $staffMember->full_name,
                    'attendance_days' => $workedDays,
                    'worked_days' => $workedDays,
                    'basic_salary' => (float) ($staffMember->salary ?? 0),
                ];
            }

            return response()->json([
                'success' => true,
                'data' => $data,
            ]);

        } catch (\Throwable $e) {

            report($e);

            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch attendance data.',
            ], 500);
        }
    }


    /*
    |--------------------------------------------------------------------------
    | CALCULATE ATTENDANCE
    |--------------------------------------------------------------------------
    */

    private function calculateAttendanceDays(
        int $staffId,
        $startDate,
        $endDate
    ): int {

        /*
         * Do not assume a particular Attendance schema.
         *
         * The current controller previously assumed:
         *   staff_id
         *   date
         *   status
         *
         * Only query those columns when they actually exist.
         */

        try {

            if (!Schema::hasTable('attendance')) {
                return 0;
            }

            $columns = Schema::getColumnListing('attendance');

            if (
                !in_array('staff_id', $columns) ||
                !in_array('status', $columns)
            ) {
                return 0;
            }

            $dateColumn = null;

            foreach ([
                'date',
                'attendance_date',
                'check_in_date',
            ] as $candidate) {

                if (in_array($candidate, $columns)) {
                    $dateColumn = $candidate;
                    break;
                }
            }

            if (!$dateColumn) {
                return 0;
            }

            return (int) DB::table('attendance')
                ->where('staff_id', $staffId)
                ->whereBetween(
                    $dateColumn,
                    [$startDate, $endDate]
                )
                ->whereIn(
                    'status',
                    [
                        'present',
                        'Present',
                        'present_half_day',
                        'half_day',
                    ]
                )
                ->count();

        } catch (\Throwable $e) {

            report($e);

            return 0;
        }
    }


    /*
    |--------------------------------------------------------------------------
    | SHOW
    |--------------------------------------------------------------------------
    */

    public function show($id)
    {
        $payrollPeriod = PayrollPeriod::with([
            'academicYear',
            'payrollItems.staff',
            'createdBy',
            'approvedBy',
        ])->findOrFail($id);

        $items = $payrollPeriod->payrollItems;

        $summary = [
            'total_staff' =>
                $items->count(),

            'total_gross' =>
                (float) $items->sum('gross_pay'),

            'total_deductions' =>
                (float) $items->sum('deductions'),

            'total_net' =>
                (float) $items->sum('net_pay'),

            'total_tax' =>
                (float) $items->sum('tax'),

            'total_pension' =>
                (float) $items->sum('pension'),

            'total_allowances' =>
                (float) $items->sum('allowances'),

            'total_overtime' =>
                (float) $items->sum('overtime'),

            'total_worked_days' =>
                (int) $items->sum('worked_days'),

            'paid_count' =>
                $items->where('status', 'paid')->count(),

            'unpaid_count' =>
                $items->where('status', '!=', 'paid')->count(),

            'working_days' =>
                $this->workingDays(
                    $payrollPeriod->start_date,
                    $payrollPeriod->end_date
                ),
        ];

        return view(
            'payroll.show',
            compact('payrollPeriod', 'summary')
        );
    }


    /*
    |--------------------------------------------------------------------------
    | WORKING DAYS
    |--------------------------------------------------------------------------
    */

    private function workingDays($startDate, $endDate): int
    {
        if (!$startDate || !$endDate) {
            return 0;
        }

        $start = Carbon::parse($startDate);
        $end = Carbon::parse($endDate);

        $days = 0;

        while ($start->lte($end)) {

            if (!$start->isWeekend()) {
                $days++;
            }

            $start->addDay();
        }

        return $days;
    }


    /*
    |--------------------------------------------------------------------------
    | GENERATE FROM ATTENDANCE
    |--------------------------------------------------------------------------
    */

    public function generateFromAttendance(
        Request $request,
        $periodId
    ) {

        $payrollPeriod =
            PayrollPeriod::findOrFail($periodId);

        if (
            in_array(
                $payrollPeriod->status,
                [
                    PayrollPeriod::STATUS_APPROVED,
                    PayrollPeriod::STATUS_PAID,
                    PayrollPeriod::STATUS_CANCELLED,
                ],
                true
            )
        ) {
            return redirect()
                ->back()
                ->with(
                    'error',
                    'This payroll period cannot be regenerated.'
                );
        }

        try {

            DB::beginTransaction();

            $staff = Staff::query()
                ->where('status', 'active')
                ->get();

            $generated = 0;

            foreach ($staff as $staffMember) {

                $workedDays =
                    $this->calculateAttendanceDays(
                        $staffMember->id,
                        $payrollPeriod->start_date,
                        $payrollPeriod->end_date
                    );

                $existingItem =
                    PayrollItem::where(
                        'payroll_period_id',
                        $periodId
                    )
                    ->where(
                        'staff_id',
                        $staffMember->id
                    )
                    ->first();

                if ($existingItem) {

                    $existingItem->worked_days =
                        $workedDays;

                    $existingItem->save();

                } else {

                    /*
                     * Salary comes from staff.salary.
                     *
                     * We do NOT use the obsolete
                     * staff.daily_rate field.
                     */

                    $basicSalary =
                        (float) ($staffMember->salary ?? 0);

                    $payrollItem = new PayrollItem();

                    $payrollItem->payroll_period_id =
                        $periodId;

                    $payrollItem->staff_id =
                        $staffMember->id;

                    $payrollItem->basic_salary =
                        $basicSalary;

                    $payrollItem->allowances =
                        0;

                    $payrollItem->overtime =
                        0;

                    $payrollItem->gross_pay =
                        $basicSalary;

                    $payrollItem->tax =
                        0;

                    $payrollItem->pension =
                        0;

                    $payrollItem->deductions =
                        0;

                    $payrollItem->net_pay =
                        $basicSalary;

                    $payrollItem->worked_days =
                        $workedDays;

                    $payrollItem->status =
                        'pending';

                    $payrollItem->save();
                }

                $generated++;
            }

            DB::commit();

            return redirect()
                ->route('payroll.show', $periodId)
                ->with(
                    'success',
                    "Payroll attendance updated for {$generated} staff members."
                );

        } catch (\Throwable $e) {

            DB::rollBack();

            report($e);

            return redirect()
                ->back()
                ->with(
                    'error',
                    'Failed to generate payroll from attendance: ' .
                    $e->getMessage()
                );
        }
    }


    /*
    |--------------------------------------------------------------------------
    | EDIT PAYROLL PERIOD
    |--------------------------------------------------------------------------
    */

    public function edit($id)
    {
        $payrollPeriod =
            PayrollPeriod::with([
                'payrollItems.staff',
            ])->findOrFail($id);

        if (
            in_array(
                $payrollPeriod->status,
                [
                    PayrollPeriod::STATUS_APPROVED,
                    PayrollPeriod::STATUS_PAID,
                    PayrollPeriod::STATUS_CANCELLED,
                ],
                true
            )
        ) {
            return redirect()
                ->route('payroll.show', $id)
                ->with(
                    'error',
                    'This payroll period cannot be edited.'
                );
        }

        $staff = Staff::query()
            ->where('status', 'active')
            ->orderBy('first_name')
            ->orderBy('last_name')
            ->get();

        return view(
            'payroll.edit',
            compact(
                'payrollPeriod',
                'staff'
            )
        );
    }


    /*
    |--------------------------------------------------------------------------
    | UPDATE PAYROLL PERIOD
    |--------------------------------------------------------------------------
    */

    public function update(
        Request $request,
        $id
    ) {

        $payrollPeriod =
            PayrollPeriod::findOrFail($id);

        if (
            in_array(
                $payrollPeriod->status,
                [
                    PayrollPeriod::STATUS_APPROVED,
                    PayrollPeriod::STATUS_PAID,
                    PayrollPeriod::STATUS_CANCELLED,
                ],
                true
            )
        ) {
            return redirect()
                ->back()
                ->with(
                    'error',
                    'This payroll period cannot be updated.'
                );
        }

        $validator = Validator::make(
            $request->all(),
            [
                'name' =>
                    'required|string|max:255',

                'academic_year_id' =>
                    'nullable|integer|exists:academic_years,id',

                'month' =>
                    'nullable|integer|between:1,12',

                'year' =>
                    'nullable|integer|between:2000,2100',

                'start_date' =>
                    'required|date',

                'end_date' =>
                    'required|date|after_or_equal:start_date',

                'payment_date' =>
                    'nullable|date|after_or_equal:end_date',

                'description' =>
                    'nullable|string',

                'status' =>
                    'nullable|in:draft,processing,pending_approval,approved,rejected,paid,cancelled',
            ]
        );

        if ($validator->fails()) {
            return redirect()
                ->back()
                ->withErrors($validator)
                ->withInput();
        }


        try {

            DB::beginTransaction();

            $oldStatus =
                $payrollPeriod->status;

            $newStatus =
                $request->status ?: $oldStatus;

            $payrollPeriod->name =
                $request->name;

            $payrollPeriod->academic_year_id =
                $request->academic_year_id;

            $payrollPeriod->month =
                $request->month
                ?: Carbon::parse($request->start_date)->month;

            $payrollPeriod->year =
                $request->year
                ?: Carbon::parse($request->start_date)->year;

            $payrollPeriod->start_date =
                $request->start_date;

            $payrollPeriod->end_date =
                $request->end_date;

            $payrollPeriod->payment_date =
                $request->payment_date;

            $payrollPeriod->description =
                $request->description;

            $payrollPeriod->status =
                $newStatus;


            /*
            |--------------------------------------------------------------------------
            | Approval
            |--------------------------------------------------------------------------
            */

            if (
                $newStatus === PayrollPeriod::STATUS_APPROVED
                &&
                $oldStatus !== PayrollPeriod::STATUS_APPROVED
            ) {

                $approver =
                    $this->authenticatedStaff();

                if (!$approver) {

                    DB::rollBack();

                    return redirect()
                        ->back()
                        ->with(
                            'error',
                            'Your user account is not linked to a staff record.'
                        )
                        ->withInput();
                }

                $payrollPeriod->approved_by =
                    $approver->id;

                $payrollPeriod->approved_at =
                    now();
            }

            $payrollPeriod->save();

            DB::commit();

            return redirect()
                ->route(
                    'payroll.show',
                    $payrollPeriod->id
                )
                ->with(
                    'success',
                    'Payroll period updated successfully.'
                );

        } catch (\Throwable $e) {

            DB::rollBack();

            report($e);

            return redirect()
                ->back()
                ->with(
                    'error',
                    'Failed to update payroll period: ' .
                    $e->getMessage()
                )
                ->withInput();
        }
    }


    /*
    |--------------------------------------------------------------------------
    | DELETE PAYROLL PERIOD
    |--------------------------------------------------------------------------
    */

    public function destroy($id)
    {
        $payrollPeriod =
            PayrollPeriod::with('payrollItems')
                ->findOrFail($id);

        if (
            in_array(
                $payrollPeriod->status,
                [
                    PayrollPeriod::STATUS_APPROVED,
                    PayrollPeriod::STATUS_PAID,
                ],
                true
            )
        ) {
            return redirect()
                ->back()
                ->with(
                    'error',
                    'Approved or paid payroll periods cannot be deleted.'
                );
        }

        try {

            DB::beginTransaction();

            foreach (
                $payrollPeriod->payrollItems
                as $item
            ) {

                if (
                    method_exists(
                        $item,
                        'payslip'
                    )
                ) {
                    $payslip =
                        $item->payslip;

                    if ($payslip) {
                        $payslip->delete();
                    }
                }
            }

            /*
             * Delete adjustments first.
             */

            if (
                Schema::hasTable(
                    'payroll_adjustments'
                )
            ) {

                DB::table(
                    'payroll_adjustments'
                )
                ->whereIn(
                    'payroll_item_id',
                    $payrollPeriod
                        ->payrollItems
                        ->pluck('id')
                )
                ->delete();
            }

            $payrollPeriod
                ->payrollItems()
                ->delete();

            $payrollPeriod->delete();

            DB::commit();

            return redirect()
                ->route('payroll.index')
                ->with(
                    'success',
                    'Payroll period deleted successfully.'
                );

        } catch (\Throwable $e) {

            DB::rollBack();

            report($e);

            return redirect()
                ->back()
                ->with(
                    'error',
                    'Failed to delete payroll period: ' .
                    $e->getMessage()
                );
        }
    }


    /*
    |--------------------------------------------------------------------------
    | EDIT PAYROLL ITEM
    |--------------------------------------------------------------------------
    */

    public function editPayrollItem($id)
    {
        $payrollItem =
            PayrollItem::with([
                'payrollPeriod',
                'staff',
            ])->findOrFail($id);

        if (
            in_array(
                $payrollItem->payrollPeriod->status,
                [
                    PayrollPeriod::STATUS_APPROVED,
                    PayrollPeriod::STATUS_PAID,
                    PayrollPeriod::STATUS_CANCELLED,
                ],
                true
            )
        ) {
            return redirect()
                ->back()
                ->with(
                    'error',
                    'This payroll item cannot be edited.'
                );
        }

        return view(
            'payroll.edit-item',
            compact('payrollItem')
        );
    }


    /*
    |--------------------------------------------------------------------------
    | UPDATE PAYROLL ITEM
    |--------------------------------------------------------------------------
    */

    public function updatePayrollItem(
        Request $request,
        $id
    ) {

        $payrollItem =
            PayrollItem::with('payrollPeriod')
                ->findOrFail($id);

        if (
            in_array(
                $payrollItem->payrollPeriod->status,
                [
                    PayrollPeriod::STATUS_APPROVED,
                    PayrollPeriod::STATUS_PAID,
                    PayrollPeriod::STATUS_CANCELLED,
                ],
                true
            )
        ) {
            return redirect()
                ->back()
                ->with(
                    'error',
                    'This payroll item cannot be updated.'
                );
        }


        $validator = Validator::make(
            $request->all(),
            [
                'basic_salary' =>
                    'required|numeric|min:0',

                'allowances' =>
                    'nullable|numeric|min:0',

                'overtime' =>
                    'nullable|numeric|min:0',

                'tax' =>
                    'nullable|numeric|min:0',

                'pension' =>
                    'nullable|numeric|min:0',

                'deductions' =>
                    'nullable|numeric|min:0',

                'worked_days' =>
                    'nullable|integer|min:0',

                'status' =>
                    'nullable|string|max:50',

                'notes' =>
                    'nullable|string',
            ]
        );

        if ($validator->fails()) {
            return redirect()
                ->back()
                ->withErrors($validator)
                ->withInput();
        }


        try {

            DB::beginTransaction();

            $basicSalary =
                (float) $request->basic_salary;

            $allowances =
                (float) ($request->allowances ?? 0);

            $overtime =
                (float) ($request->overtime ?? 0);

            $tax =
                (float) ($request->tax ?? 0);

            $pension =
                (float) ($request->pension ?? 0);

            $deductions =
                (float) ($request->deductions ?? 0);


            /*
            |--------------------------------------------------------------------------
            | Gross
            |--------------------------------------------------------------------------
            */

            $grossPay =
                $basicSalary
                + $allowances
                + $overtime;


            /*
            |--------------------------------------------------------------------------
            | Total deductions
            |--------------------------------------------------------------------------
            */

            $totalDeductions =
                $tax
                + $pension
                + $deductions;


            /*
            |--------------------------------------------------------------------------
            | Net
            |--------------------------------------------------------------------------
            */

            $netPay =
                max(
                    0,
                    $grossPay - $totalDeductions
                );


            $payrollItem->basic_salary =
                $basicSalary;

            $payrollItem->allowances =
                $allowances;

            $payrollItem->overtime =
                $overtime;

            $payrollItem->gross_pay =
                $grossPay;

            $payrollItem->tax =
                $tax;

            $payrollItem->pension =
                $pension;

            $payrollItem->deductions =
                $deductions;

            $payrollItem->net_pay =
                $netPay;

            $payrollItem->worked_days =
                (int) ($request->worked_days ?? 0);

            $payrollItem->status =
                $request->status
                ?: ($payrollItem->status ?: 'pending');

            $payrollItem->notes =
                $request->notes;

            $payrollItem->save();

            DB::commit();

            return redirect()
                ->route(
                    'payroll.show',
                    $payrollItem->payroll_period_id
                )
                ->with(
                    'success',
                    'Payroll item updated successfully.'
                );

        } catch (\Throwable $e) {

            DB::rollBack();

            report($e);

            return redirect()
                ->back()
                ->with(
                    'error',
                    'Failed to update payroll item: ' .
                    $e->getMessage()
                )
                ->withInput();
        }
    }


    /*
    |--------------------------------------------------------------------------
    | GENERATE PAYSLIP
    |--------------------------------------------------------------------------
    */

    public function generatePayslip($id)
    {
        $payrollItem =
            PayrollItem::with([
                'staff',
                'payrollPeriod',
            ])->findOrFail($id);

        if (
            !in_array(
                $payrollItem->payrollPeriod->status,
                [
                    PayrollPeriod::STATUS_APPROVED,
                    PayrollPeriod::STATUS_PAID,
                ],
                true
            )
        ) {
            return redirect()
                ->back()
                ->with(
                    'error',
                    'Payroll must be approved before generating a payslip.'
                );
        }


        try {

            DB::beginTransaction();

            /*
             * Check whether the Payslip model provides
             * the existing generator.
             */

            if (
                method_exists(
                    $payrollItem,
                    'generatePayslip'
                )
            ) {

                $payslip =
                    $payrollItem->generatePayslip();

            } else {

                /*
                 * Fallback creation using the actual
                 * payroll structure.
                 */

                $payslip =
                    Payslip::where(
                        'payroll_item_id',
                        $payrollItem->id
                    )->first();

                if (!$payslip) {

                    $payslip = new Payslip();

                    $payslip->payroll_item_id =
                        $payrollItem->id;

                    /*
                     * Only assign fields that exist.
                     */

                    $payslipColumns =
                        Schema::getColumnListing(
                            'payslips'
                        );

                    if (
                        in_array(
                            'staff_id',
                            $payslipColumns
                        )
                    ) {
                        $payslip->staff_id =
                            $payrollItem->staff_id;
                    }

                    if (
                        in_array(
                            'payroll_period_id',
                            $payslipColumns
                        )
                    ) {
                        $payslip->payroll_period_id =
                            $payrollItem->payroll_period_id;
                    }

                    if (
                        in_array(
                            'basic_salary',
                            $payslipColumns
                        )
                    ) {
                        $payslip->basic_salary =
                            $payrollItem->basic_salary;
                    }

                    if (
                        in_array(
                            'gross_pay',
                            $payslipColumns
                        )
                    ) {
                        $payslip->gross_pay =
                            $payrollItem->gross_pay;
                    }

                    if (
                        in_array(
                            'deductions',
                            $payslipColumns
                        )
                    ) {
                        $payslip->deductions =
                            $payrollItem->deductions;
                    }

                    if (
                        in_array(
                            'net_pay',
                            $payslipColumns
                        )
                    ) {
                        $payslip->net_pay =
                            $payrollItem->net_pay;
                    }

                    if (
                        in_array(
                            'status',
                            $payslipColumns
                        )
                    ) {
                        $payslip->status =
                            'generated';
                    }

                    $payslip->save();
                }
            }

            DB::commit();

            return redirect()
                ->route(
                    'payroll.view-payslip',
                    $payslip->id
                )
                ->with(
                    'success',
                    'Payslip generated successfully.'
                );

        } catch (\Throwable $e) {

            DB::rollBack();

            report($e);

            return redirect()
                ->back()
                ->with(
                    'error',
                    'Failed to generate payslip: ' .
                    $e->getMessage()
                );
        }
    }


    /*
    |--------------------------------------------------------------------------
    | VIEW PAYSLIP
    |--------------------------------------------------------------------------
    */

    public function viewPayslip($id)
    {
        $payslip =
            Payslip::with([
                'payrollItem.staff',
                'payrollItem.payrollPeriod',
            ])->findOrFail($id);

        if (
            method_exists(
                $payslip,
                'markAsViewed'
            )
            &&
            isset($payslip->status)
            &&
            $payslip->status === 'generated'
        ) {
            $payslip->markAsViewed();
        }

        return view(
            'payroll.payslip',
            compact('payslip')
        );
    }


    /*
    |--------------------------------------------------------------------------
    | GENERATE ALL PAYSLIPS
    |--------------------------------------------------------------------------
    */

    public function generateAllPayslips($periodId)
    {
        $payrollPeriod =
            PayrollPeriod::with('payrollItems')
                ->findOrFail($periodId);

        if (
            !in_array(
                $payrollPeriod->status,
                [
                    PayrollPeriod::STATUS_APPROVED,
                    PayrollPeriod::STATUS_PAID,
                ],
                true
            )
        ) {
            return redirect()
                ->back()
                ->with(
                    'error',
                    'Payroll must be approved before generating payslips.'
                );
        }


        try {

            DB::beginTransaction();

            $generated = 0;

            foreach (
                $payrollPeriod->payrollItems
                as $item
            ) {

                $exists =
                    Schema::hasTable('payslips')
                    &&
                    DB::table('payslips')
                        ->where(
                            'payroll_item_id',
                            $item->id
                        )
                        ->exists();

                if (!$exists) {

                    if (
                        method_exists(
                            $item,
                            'generatePayslip'
                        )
                    ) {
                        $item->generatePayslip();
                    } else {
                        $this->createFallbackPayslip(
                            $item
                        );
                    }

                    $generated++;
                }
            }

            DB::commit();

            return redirect()
                ->route(
                    'payroll.show',
                    $periodId
                )
                ->with(
                    'success',
                    "{$generated} payslips generated successfully."
                );

        } catch (\Throwable $e) {

            DB::rollBack();

            report($e);

            return redirect()
                ->back()
                ->with(
                    'error',
                    'Failed to generate payslips: ' .
                    $e->getMessage()
                );
        }
    }


    /*
    |--------------------------------------------------------------------------
    | FALLBACK PAYSLIP CREATOR
    |--------------------------------------------------------------------------
    */

    private function createFallbackPayslip(
        PayrollItem $payrollItem
    ) {

        if (!Schema::hasTable('payslips')) {
            throw new \RuntimeException(
                'The payslips table does not exist.'
            );
        }

        $columns =
            Schema::getColumnListing(
                'payslips'
            );

        $payslip =
            new Payslip();

        if (
            in_array(
                'payroll_item_id',
                $columns
            )
        ) {
            $payslip->payroll_item_id =
                $payrollItem->id;
        }

        if (
            in_array(
                'payroll_period_id',
                $columns
            )
        ) {
            $payslip->payroll_period_id =
                $payrollItem->payroll_period_id;
        }

        if (
            in_array(
                'staff_id',
                $columns
            )
        ) {
            $payslip->staff_id =
                $payrollItem->staff_id;
        }

        if (
            in_array(
                'basic_salary',
                $columns
            )
        ) {
            $payslip->basic_salary =
                $payrollItem->basic_salary;
        }

        if (
            in_array(
                'gross_pay',
                $columns
            )
        ) {
            $payslip->gross_pay =
                $payrollItem->gross_pay;
        }

        if (
            in_array(
                'deductions',
                $columns
            )
        ) {
            $payslip->deductions =
                $payrollItem->deductions;
        }

        if (
            in_array(
                'net_pay',
                $columns
            )
        ) {
            $payslip->net_pay =
                $payrollItem->net_pay;
        }

        if (
            in_array(
                'status',
                $columns
            )
        ) {
            $payslip->status =
                'generated';
        }

        $payslip->save();

        return $payslip;
    }


    /*
    |--------------------------------------------------------------------------
    | MARK AS PAID
    |--------------------------------------------------------------------------
    */

    public function markAsPaid(
        Request $request,
        $id
    ) {

        $payrollItem =
            PayrollItem::with('payrollPeriod')
                ->findOrFail($id);

        if (
            $payrollItem->payrollPeriod->status
            !== PayrollPeriod::STATUS_APPROVED
            &&
            $payrollItem->payrollPeriod->status
            !== PayrollPeriod::STATUS_PAID
        ) {
            return redirect()
                ->back()
                ->with(
                    'error',
                    'Payroll must be approved before payment.'
                );
        }


        $validator = Validator::make(
            $request->all(),
            [
                'payment_date' =>
                    'nullable|date',
            ]
        );

        if ($validator->fails()) {
            return redirect()
                ->back()
                ->withErrors($validator)
                ->withInput();
        }


        try {

            DB::beginTransaction();

            if (
                method_exists(
                    $payrollItem,
                    'markAsPaid'
                )
            ) {

                $payrollItem->markAsPaid(
                    $request->payment_date
                );

            } else {

                $payrollItem->status =
                    'paid';

                $payrollItem->save();
            }

            DB::commit();

            return redirect()
                ->route(
                    'payroll.show',
                    $payrollItem->payroll_period_id
                )
                ->with(
                    'success',
                    'Payment marked as successful.'
                );

        } catch (\Throwable $e) {

            DB::rollBack();

            report($e);

            return redirect()
                ->back()
                ->with(
                    'error',
                    'Failed to mark payment: ' .
                    $e->getMessage()
                );
        }
    }


    /*
    |--------------------------------------------------------------------------
    | ADD PAYROLL ADJUSTMENT
    |--------------------------------------------------------------------------
    */

    public function addAdjustment(
        Request $request,
        $id
    ) {

        $payrollItem =
            PayrollItem::with('payrollPeriod')
                ->findOrFail($id);

        if (
            in_array(
                $payrollItem->payrollPeriod->status,
                [
                    PayrollPeriod::STATUS_APPROVED,
                    PayrollPeriod::STATUS_PAID,
                    PayrollPeriod::STATUS_CANCELLED,
                ],
                true
            )
        ) {
            return redirect()
                ->back()
                ->with(
                    'error',
                    'Cannot adjust this payroll item.'
                );
        }


        /*
        |--------------------------------------------------------------------------
        | IMPORTANT
        |--------------------------------------------------------------------------
        |
        | Current payroll_adjustments table contains only:
        |
        | payroll_item_id
        | type
        | amount
        | reason
        |
        | Therefore we DO NOT attempt to save:
        | description
        | effect
        | approved_by
        | approved_at
        |
        */

        $validator = Validator::make(
            $request->all(),
            [
                'type' => [
                    'required',
                    'in:allowance,deduction,bonus,overtime,other',
                ],

                'amount' => [
                    'required',
                    'numeric',
                    'min:0',
                ],

                'reason' => [
                    'nullable',
                    'string',
                    'max:1000',
                ],
            ]
        );

        if ($validator->fails()) {
            return redirect()
                ->back()
                ->withErrors($validator)
                ->withInput();
        }


        try {

            DB::beginTransaction();

            PayrollAdjustment::create([
                'payroll_item_id' =>
                    $payrollItem->id,

                'type' =>
                    $request->type,

                'amount' =>
                    $request->amount,

                'reason' =>
                    $request->reason,
            ]);


            /*
            |--------------------------------------------------------------------------
            | Apply Adjustment
            |--------------------------------------------------------------------------
            */

            $amount =
                (float) $request->amount;

            switch ($request->type) {

                case 'allowance':

                case 'bonus':

                    $payrollItem->allowances =
                        (float) $payrollItem->allowances
                        + $amount;

                    break;


                case 'overtime':

                    $payrollItem->overtime =
                        (float) $payrollItem->overtime
                        + $amount;

                    break;


                case 'deduction':

                case 'other':

                    $payrollItem->deductions =
                        (float) $payrollItem->deductions
                        + $amount;

                    break;
            }


            /*
            |--------------------------------------------------------------------------
            | Recalculate Payroll Item
            |--------------------------------------------------------------------------
            */

            $gross =
                (float) $payrollItem->basic_salary
                +
                (float) $payrollItem->allowances
                +
                (float) $payrollItem->overtime;

            $totalDeductions =
                (float) $payrollItem->tax
                +
                (float) $payrollItem->pension
                +
                (float) $payrollItem->deductions;

            $net =
                max(
                    0,
                    $gross - $totalDeductions
                );

            $payrollItem->gross_pay =
                $gross;

            $payrollItem->net_pay =
                $net;

            $payrollItem->save();

            DB::commit();

            return redirect()
                ->route(
                    'payroll.edit-item',
                    $payrollItem->id
                )
                ->with(
                    'success',
                    'Payroll adjustment added successfully.'
                );

        } catch (\Throwable $e) {

            DB::rollBack();

            report($e);

            return redirect()
                ->back()
                ->with(
                    'error',
                    'Failed to add payroll adjustment: ' .
                    $e->getMessage()
                )
                ->withInput();
        }
    }


    /*
    |--------------------------------------------------------------------------
    | AUTHENTICATED USER -> STAFF
    |--------------------------------------------------------------------------
    */

    private function authenticatedStaff(): ?Staff
    {
        $user = auth()->user();

        if (!$user) {
            return null;
        }

        /*
         * User::staff() is already correctly configured
         * to link through email.
         */

        return $user->staff;
    }
}