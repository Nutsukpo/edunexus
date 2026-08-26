<?php
// database/migrations/xxxx_xx_xx_add_missing_columns_to_payroll_period_staff_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('payroll_period_staff', function (Blueprint $table) {
            // Check if columns exist before adding
            if (!Schema::hasColumn('payroll_period_staff', 'allowances')) {
                $table->decimal('allowances', 15, 2)->default(0)->after('basic_salary');
            }
            
            if (!Schema::hasColumn('payroll_period_staff', 'deductions')) {
                $table->decimal('deductions', 15, 2)->default(0)->after('allowances');
            }
            
            if (!Schema::hasColumn('payroll_period_staff', 'gross_salary')) {
                $table->decimal('gross_salary', 15, 2)->default(0)->after('deductions');
            }
            
            if (!Schema::hasColumn('payroll_period_staff', 'total_deduction')) {
                $table->decimal('total_deduction', 15, 2)->default(0)->after('gross_salary');
            }
            
            if (!Schema::hasColumn('payroll_period_staff', 'net_salary')) {
                $table->decimal('net_salary', 15, 2)->default(0)->after('total_deduction');
            }
        });
    }

    public function down()
    {
        Schema::table('payroll_period_staff', function (Blueprint $table) {
            $columns = ['allowances', 'deductions', 'gross_salary', 'total_deduction', 'net_salary'];
            foreach ($columns as $column) {
                if (Schema::hasColumn('payroll_period_staff', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};