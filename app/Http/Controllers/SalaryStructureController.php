<?php

namespace App\Http\Controllers;

use App\Models\SalaryStructure;
use App\Models\Staff;
use Illuminate\Http\Request;

class SalaryStructureController extends Controller
{

    public function index()
    {

        $salaryStructures = SalaryStructure::with('staff')
            ->latest()
            ->paginate(15);


        return view(
            'salary_structures.index',
            compact('salaryStructures')
        );
    }



    public function create()
{

    $staff = Staff::orderBy('first_name')
        ->get();


    return view(
        'salary_structures.create',
        compact('staff')
    );

}


    public function store(Request $request)
    {

        $validated = $request->validate([

            'staff_id'=>'required|exists:staff,id',

            'basic_salary'=>'required|numeric|min:0',

            'housing_allowance'=>'nullable|numeric|min:0',

            'transport_allowance'=>'nullable|numeric|min:0',

            'medical_allowance'=>'nullable|numeric|min:0',

            'responsibility_allowance'=>'nullable|numeric|min:0',

            'other_allowance'=>'nullable|numeric|min:0',

            'tax'=>'nullable|numeric|min:0',

            'ssnit'=>'nullable|numeric|min:0',

            'tier2'=>'nullable|numeric|min:0',

            'tier3'=>'nullable|numeric|min:0',

            'loan_deduction'=>'nullable|numeric|min:0',

            'other_deduction'=>'nullable|numeric|min:0',

            'effective_date'=>'required|date',

        ]);



        SalaryStructure::create($validated);



        return redirect()
            ->route('salary-structures.index')
            ->with(
                'success',
                'Salary structure created successfully'
            );

    }





    public function edit($id)
    {

        $salaryStructure = SalaryStructure::findOrFail($id);


        return view(
            'salary_structures.edit',
            compact('salaryStructure')
        );

    }




    public function update(Request $request,$id)
    {

        $salaryStructure = SalaryStructure::findOrFail($id);



        $validated = $request->validate([


            'basic_salary'=>'required|numeric|min:0',

            'housing_allowance'=>'nullable|numeric|min:0',

            'transport_allowance'=>'nullable|numeric|min:0',

            'medical_allowance'=>'nullable|numeric|min:0',

            'responsibility_allowance'=>'nullable|numeric|min:0',

            'other_allowance'=>'nullable|numeric|min:0',

            'tax'=>'nullable|numeric|min:0',

            'ssnit'=>'nullable|numeric|min:0',

            'tier2'=>'nullable|numeric|min:0',

            'tier3'=>'nullable|numeric|min:0',

            'loan_deduction'=>'nullable|numeric|min:0',

            'other_deduction'=>'nullable|numeric|min:0',

            'effective_date'=>'required|date',

            'is_active'=>'boolean'

        ]);



        $salaryStructure->update($validated);



        return redirect()
            ->route('salary-structures.index')
            ->with(
                'success',
                'Salary structure updated'
            );

    }




    public function destroy($id)
    {

        $salaryStructure =
            SalaryStructure::findOrFail($id);


        $salaryStructure->delete();



        return back()->with(
            'success',
            'Salary structure deleted'
        );

    }


}