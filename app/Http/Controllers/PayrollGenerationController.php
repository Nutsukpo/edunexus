<?php

namespace App\Http\Controllers;


use App\Models\PayrollPeriod;
use App\Models\Staff;
use App\Models\PayrollPeriodStaff;



class PayrollGenerationController extends Controller
{


public function addStaff(
PayrollPeriod $payrollPeriod
)
{


$staff = Staff::where('status','Active')
->get();



foreach($staff as $employee)
{


$salary = $employee
->salaryStructures()
->latest()
->first();



if(!$salary)
{
continue;
}



PayrollPeriodStaff::firstOrCreate([

'payroll_period_id'=>$payrollPeriod->id,

'staff_id'=>$employee->id,


], [


'salary_structure_id'=>$salary->id,


'basic_salary'=>$salary->basic_salary,


'total_allowance'=>$salary->total_allowance ?? 0,


'total_deduction'=>$salary->total_deduction ?? 0,


'gross_salary'=>$salary->basic_salary 
+
($salary->total_allowance ?? 0),



'net_salary'=>

$salary->basic_salary
+
($salary->total_allowance ?? 0)
-
($salary->total_deduction ?? 0)



]);


}



return back()
->with(
'success',
'Staff added to payroll successfully'
);


}



}