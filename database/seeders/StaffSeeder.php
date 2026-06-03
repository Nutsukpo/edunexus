<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Staff;

class StaffSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $staffMembers = [

            [
                'staff_id' => '100',
                'first_name' => 'John',
                'last_name' => 'Doe',
                'other_name' => null,
                'gender' => 'Male',
                'date_of_birth' => '1988-04-12',
                'phone' => '0241111111',
                'email' => 'john.doe@school.com',
                'department' => 'Administration',
                'position' => 'Administrator',
                'date_employed' => '2020-01-10',
                'salary' => 5000,
                'address' => 'Accra',
                'photo' => null,
                'staff_type' => 'Non-Teaching',
                'status' => 'Active',
            ],

            [
                'staff_id' => '200',
                'first_name' => 'Ama',
                'last_name' => 'Mensah',
                'other_name' => null,
                'gender' => 'Female',
                'date_of_birth' => '1990-06-20',
                'phone' => '0242222222',
                'email' => 'ama.mensah@school.com',
                'department' => 'Science',
                'position' => 'Science Teacher',
                'date_employed' => '2021-02-15',
                'salary' => 4200,
                'address' => 'Accra',
                'photo' => null,
                'staff_type' => 'Teaching',
                'status' => 'Active',
            ],

            [
                'staff_id' => '300',
                'first_name' => 'Kwame',
                'last_name' => 'Asare',
                'other_name' => 'K',
                'gender' => 'Male',
                'date_of_birth' => '1985-03-11',
                'phone' => '0243333333',
                'email' => 'kwame.asare@school.com',
                'department' => 'Mathematics',
                'position' => 'Math Teacher',
                'date_employed' => '2019-09-01',
                'salary' => 4500,
                'address' => 'Kumasi',
                'photo' => null,
                'staff_type' => 'Teaching',
                'status' => 'Active',
            ],

            [
                'staff_id' => '400',
                'first_name' => 'Linda',
                'last_name' => 'Owusu',
                'other_name' => null,
                'gender' => 'Female',
                'date_of_birth' => '1992-07-25',
                'phone' => '0244444444',
                'email' => 'linda.owusu@school.com',
                'department' => 'Accounts',
                'position' => 'Accountant',
                'date_employed' => '2022-01-05',
                'salary' => 4000,
                'address' => 'Takoradi',
                'photo' => null,
                'staff_type' => 'Non-Teaching',
                'status' => 'Active',
            ],

        ];

        foreach ($staffMembers as $staff) {
            Staff::create($staff);
        }
    }
}