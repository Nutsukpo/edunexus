<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        /*
        |--------------------------------------------------------------------------
        | Create Roles (Spatie)
        |--------------------------------------------------------------------------
        */

        $adminRole = Role::firstOrCreate(['name' => 'admin']);
        $staffRole = Role::firstOrCreate(['name' => 'staff']);
        $userRole  = Role::firstOrCreate(['name' => 'user']);

        /*
        |--------------------------------------------------------------------------
        | ADMIN USER
        |--------------------------------------------------------------------------
        */

        $admin = User::firstOrCreate(
            ['email' => 'admin@adam.com'],
            [
                'name' => 'System Admin',
                'phone' => '0200000000',
                'role' => 'admin',
                'status' => 'active',
                'profile_photo' => null,
                'password' => Hash::make('password'),
            ]
        );

        $admin->assignRole($adminRole);

        /*
        |--------------------------------------------------------------------------
        | STAFF USER
        |--------------------------------------------------------------------------
        */

        $staff = User::firstOrCreate(
            ['email' => 'staff@adam.com'],
            [
                'name' => 'Staff Member',
                'phone' => '0201111111',
                'role' => 'staff',
                'status' => 'active',
                'profile_photo' => null,
                'password' => Hash::make('password'),
            ]
        );

        $staff->assignRole($staffRole);

        /*
        |--------------------------------------------------------------------------
        | NORMAL USER (OPTIONAL)
        |--------------------------------------------------------------------------
        */

        $user = User::firstOrCreate(
            ['email' => 'webartisan@usms.com'],
            [
                'name' => 'Php Webartisan',
                'phone' => '0542013350',
                'role' => 'user',
                'status' => 'active',
                'profile_photo' => null,
                'password' => Hash::make('Mon@10019887'),
            ]
        );

        $user->assignRole($userRole);
    }
}