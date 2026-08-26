<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RolesAndPermissionsSeeder extends Seeder
{
    /**
     * EDUNEXUS Roles & Permissions Seeder
     *
     * IMPORTANT:
     * - This creates the fresh EDUNEXUS RBAC structure.
     * - Super Admin receives all permissions.
     * - All other roles intentionally start with NO permissions.
     * - Permissions for the other roles are assigned manually
     *   through the Role & Permission management interface.
     *
     * Permission naming convention:
     *
     * module.action
     *
     * Example:
     * students.view
     * students.create
     * students.edit
     * students.delete
     */
    public function run(): void
    {
        /*
        |--------------------------------------------------------------------------
        | Clear Spatie Permission Cache
        |--------------------------------------------------------------------------
        */
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        /*
        |--------------------------------------------------------------------------
        | EDUNEXUS Permissions
        |--------------------------------------------------------------------------
        |
        | Keep this list as the master catalogue of permissions available
        | throughout the EDUNEXUS system.
        |
        */

        $permissions = [

            /*
            |--------------------------------------------------------------------------
            | Dashboard
            |--------------------------------------------------------------------------
            */
            'dashboard.view',


            /*
            |--------------------------------------------------------------------------
            | Users
            |--------------------------------------------------------------------------
            */
            'users.view',
            'users.create',
            'users.edit',
            'users.delete',
            'users.toggle-status',
            'users.restore',
            'users.view-profile',


            /*
            |--------------------------------------------------------------------------
            | Roles & Permissions
            |--------------------------------------------------------------------------
            */
            'roles.view',
            'roles.manage-permissions',


            /*
            |--------------------------------------------------------------------------
            | Departments
            |--------------------------------------------------------------------------
            */
            'departments.view',
            'departments.create',
            'departments.edit',
            'departments.delete',


            /*
            |--------------------------------------------------------------------------
            | Academic Years
            |--------------------------------------------------------------------------
            */
            'academic-years.view',
            'academic-years.create',
            'academic-years.edit',
            'academic-years.delete',


            /*
            |--------------------------------------------------------------------------
            | Terms
            |--------------------------------------------------------------------------
            */
            'terms.view',
            'terms.create',
            'terms.edit',
            'terms.delete',


            /*
            |--------------------------------------------------------------------------
            | Student Classes
            |--------------------------------------------------------------------------
            */
            'classes.view',
            'classes.create',
            'classes.edit',
            'classes.delete',
            'classes.assign-subject',
            'classes.assign-staff',
            'classes.assign-prefect',


            /*
            |--------------------------------------------------------------------------
            | Subjects
            |--------------------------------------------------------------------------
            */
            'subjects.view',
            'subjects.create',
            'subjects.edit',
            'subjects.delete',
            'subjects.assign-staff',


            /*
            |--------------------------------------------------------------------------
            | Staff
            |--------------------------------------------------------------------------
            */
            'staff.view',
            'staff.create',
            'staff.edit',
            'staff.delete',
            'staff.restore',
            'staff.view-profile',


            /*
            |--------------------------------------------------------------------------
            | Students
            |--------------------------------------------------------------------------
            */
            'students.view',
            'students.create',
            'students.edit',
            'students.delete',
            'students.restore',
            'students.view-profile',
            'students.manage-assignments',


            /*
            |--------------------------------------------------------------------------
            | Student Enrollments
            |--------------------------------------------------------------------------
            */
            'enrollments.view',
            'enrollments.create',
            'enrollments.edit',
            'enrollments.delete',


            /*
            |--------------------------------------------------------------------------
            | Student Class Assignments
            |--------------------------------------------------------------------------
            */
            'student-class-assignments.view',
            'student-class-assignments.create',
            'student-class-assignments.edit',
            'student-class-assignments.delete',


            /*
            |--------------------------------------------------------------------------
            | Student Attendance
            |--------------------------------------------------------------------------
            */
            'attendance.view',
            'attendance.create',
            'attendance.edit',
            'attendance.delete',
            'attendance.reports',
            'attendance.export',


            /*
            |--------------------------------------------------------------------------
            | Staff Attendance
            |--------------------------------------------------------------------------
            */
            'staff-attendance.view',
            'staff-attendance.create',
            'staff-attendance.edit',
            'staff-attendance.delete',
            'staff-attendance.reports',
            'staff-attendance.export',


            /*
            |--------------------------------------------------------------------------
            | Student Results
            |--------------------------------------------------------------------------
            */
            'results.view',
            'results.create',
            'results.edit',
            'results.delete',
            'results.reports',


            /*
            |--------------------------------------------------------------------------
            | Broadsheet
            |--------------------------------------------------------------------------
            */
            'broadsheet.view',
            'broadsheet.generate',
            'broadsheet.export',


            /*
            |--------------------------------------------------------------------------
            | Report Cards
            |--------------------------------------------------------------------------
            */
            'report-cards.view',
            'report-cards.generate',
            'report-cards.export',


            /*
            |--------------------------------------------------------------------------
            | Lesson Notes
            |--------------------------------------------------------------------------
            */
            'lesson-notes.view',
            'lesson-notes.create',
            'lesson-notes.edit',
            'lesson-notes.delete',
            'lesson-notes.clone',
            'lesson-notes.comment',
            'lesson-notes.download',
            'lesson-notes.approve',
            'lesson-notes.reject',


            /*
            |--------------------------------------------------------------------------
            | Assessment Forms
            |--------------------------------------------------------------------------
            */
            'assessment-forms.view',
            'assessment-forms.create',
            'assessment-forms.edit',
            'assessment-forms.delete',
            'assessment-forms.download',
            'assessment-forms.change-status',


            /*
            |--------------------------------------------------------------------------
            | Timetables
            |--------------------------------------------------------------------------
            */
            'timetables.view',
            'timetables.create',
            'timetables.edit',
            'timetables.delete',
            'timetables.download',


            /*
            |--------------------------------------------------------------------------
            | School Fees
            |--------------------------------------------------------------------------
            */
            'fees.view',
            'fees.create',
            'fees.edit',
            'fees.delete',
            'fees.approve',
            'fees.reports',
            'fees.export',
            'fees.allocate',


            /*
            |--------------------------------------------------------------------------
            | Payments
            |--------------------------------------------------------------------------
            */
            'payments.view',
            'payments.create',
            'payments.edit',
            'payments.delete',
            'payments.receipts',
            'payments.reports',


            /*
            |--------------------------------------------------------------------------
            | Fee Structures
            |--------------------------------------------------------------------------
            */
            'fee-structures.view',
            'fee-structures.create',
            'fee-structures.edit',
            'fee-structures.delete',


            /*
            |--------------------------------------------------------------------------
            | Bill Sheets
            |--------------------------------------------------------------------------
            */
            'bill-sheets.view',
            'bill-sheets.create',
            'bill-sheets.edit',
            'bill-sheets.delete',
            'bill-sheets.approve',
            'bill-sheets.reject',


            /*
            |--------------------------------------------------------------------------
            | Invoices
            |--------------------------------------------------------------------------
            */
            'invoices.view',
            'invoices.create',
            'invoices.edit',
            'invoices.delete',


            /*
            |--------------------------------------------------------------------------
            | Payroll
            |--------------------------------------------------------------------------
            */
            'payroll.view',
            'payroll.create',
            'payroll.edit',
            'payroll.delete',
            'payroll.process',
            'payroll.approve',
            'payroll.reject',
            'payroll.reports',


            /*
            |--------------------------------------------------------------------------
            | Salary Structures
            |--------------------------------------------------------------------------
            */
            'salary-structures.view',
            'salary-structures.create',
            'salary-structures.edit',
            'salary-structures.delete',


            /*
            |--------------------------------------------------------------------------
            | Payslips
            |--------------------------------------------------------------------------
            */
            'payslips.view',
            'payslips.create',
            'payslips.download',


            /*
            |--------------------------------------------------------------------------
            | Leave Management
            |--------------------------------------------------------------------------
            */
            'leaves.view',
            'leaves.create',
            'leaves.edit',
            'leaves.delete',
            'leaves.approve',
            'leaves.reject',


            /*
            |--------------------------------------------------------------------------
            | Staff Appraisals
            |--------------------------------------------------------------------------
            */
            'appraisals.view',
            'appraisals.create',
            'appraisals.edit',
            'appraisals.delete',
            'appraisals.review',


            /*
            |--------------------------------------------------------------------------
            | Assets
            |--------------------------------------------------------------------------
            */
            'assets.view',
            'assets.create',
            'assets.edit',
            'assets.delete',
            'assets.assign',
            'assets.return',


            /*
            |--------------------------------------------------------------------------
            | Discussion Groups
            |--------------------------------------------------------------------------
            */
            'discussions.view',
            'discussions.create',
            'discussions.edit',
            'discussions.delete',
            'discussions.participate',


            /*
            |--------------------------------------------------------------------------
            | Announcements
            |--------------------------------------------------------------------------
            */
            'announcements.view',
            'announcements.create',
            'announcements.edit',
            'announcements.delete',
            'announcements.publish',


            /*
            |--------------------------------------------------------------------------
            | Student Progression
            |--------------------------------------------------------------------------
            */
            'progression.view',
            'progression.process',
            'progression.bulk-promote',


            /*
            |--------------------------------------------------------------------------
            | Graduation
            |--------------------------------------------------------------------------
            */
            'graduation.view',
            'graduation.process',
            'graduation.export',
            'graduation.restore',


            /*
            |--------------------------------------------------------------------------
            | Grievances
            |--------------------------------------------------------------------------
            */
            'grievances.view',
            'grievances.create',
            'grievances.edit',
            'grievances.delete',
            'grievances.assign',
            'grievances.status',
            'grievances.escalate',
            'grievances.appeal',


            /*
            |--------------------------------------------------------------------------
            | General Reports
            |--------------------------------------------------------------------------
            */
            'reports.view',
            'reports.generate',
            'reports.export',
        ];

        /*
        |--------------------------------------------------------------------------
        | Create Permissions
        |--------------------------------------------------------------------------
        |
        | firstOrCreate ensures this seeder can safely be executed more than
        | once without creating duplicate permissions.
        |
        */

        foreach ($permissions as $permissionName) {
            Permission::firstOrCreate([
                'name' => $permissionName,
                'guard_name' => 'web',
            ]);
        }


        /*
        |--------------------------------------------------------------------------
        | EDUNEXUS Roles
        |--------------------------------------------------------------------------
        */

        $roles = [
            'Super Admin',
            'Administrator',
            'Teaching Staff',
            'Accountant',
            'MIS',
            'Power User',
            'Non-Teaching Staff',
        ];


        /*
        |--------------------------------------------------------------------------
        | Create Roles
        |--------------------------------------------------------------------------
        */

        foreach ($roles as $roleName) {
            Role::firstOrCreate([
                'name' => $roleName,
                'guard_name' => 'web',
            ]);
        }


        /*
        |--------------------------------------------------------------------------
        | Super Admin
        |--------------------------------------------------------------------------
        |
        | Super Admin receives every available permission.
        |
        | This guarantees that the system always has one unrestricted
        | emergency/system-owner role.
        |
        */

        $superAdmin = Role::findByName('Super Admin', 'web');

        $allPermissions = Permission::where(
            'guard_name',
            'web'
        )->get();

        $superAdmin->syncPermissions($allPermissions);


        /*
        |--------------------------------------------------------------------------
        | Other Roles
        |--------------------------------------------------------------------------
        |
        | IMPORTANT:
        |
        | We intentionally DO NOT assign permissions to:
        |
        | Administrator
        | Teaching Staff
        | Accountant
        | MIS
        | Power User
        | Non-Teaching Staff
        |
        | Their permissions will be assigned manually from the
        | EDUNEXUS Role Permissions management interface.
        |
        */


        /*
        |--------------------------------------------------------------------------
        | Refresh Permission Cache
        |--------------------------------------------------------------------------
        */

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
}