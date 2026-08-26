<?php

namespace Database\Seeders;

use App\Models\Student;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class StudentPortalSeeder extends Seeder
{
    public function run(): void
    {
        Student::query()->update([

            'password' => Hash::make('123456'),

            'password_changed' => false,

        ]);
    }
}