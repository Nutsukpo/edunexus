<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Staff;
use App\Models\SalaryStructure;

class SalaryStructureSeeder extends Seeder
{
    public function run(): void
    {
        $staffMembers = Staff::all();

        foreach ($staffMembers as $staff) {

            SalaryStructure::firstOrCreate(

                [
                    'staff_id' => $staff->id
                ],

                [
                    'basic_salary' => 0,

                    'housing_allowance' => 0,

                    'transport_allowance' => 0,

                    'medical_allowance' => 0,

                    'responsibility_allowance' => 0,

                    'other_allowance' => 0,

                    'tax' => 0,

                    'ssnit' => 0,

                    'tier2' => 0,

                    'tier3' => 0,

                    'loan_deduction' => 0,

                    'other_deduction' => 0,

                    'effective_date' => now(),

                    'is_active' => true,
                ]
            );

        }
    }
}