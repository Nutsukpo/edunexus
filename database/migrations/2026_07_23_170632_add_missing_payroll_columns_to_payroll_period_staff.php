<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('payroll_period_staff', function (Blueprint $table) {
            // Check if column exists before adding
            if (!Schema::hasColumn('payroll_period_staff', 'basic_salary')) {
                $table->decimal('basic_salary', 10, 2)->default(0)->after('staff_id');
            }
            if (!Schema::hasColumn('payroll_period_staff', 'allowances')) {
                $table->decimal('allowances', 10, 2)->default(0)->after('basic_salary');
            }
            if (!Schema::hasColumn('payroll_period_staff', 'overtime')) {
                $table->decimal('overtime', 10, 2)->default(0)->after('allowances');
            }
            if (!Schema::hasColumn('payroll_period_staff', 'gross_pay')) {
                $table->decimal('gross_pay', 10, 2)->default(0)->after('overtime');
            }
            if (!Schema::hasColumn('payroll_period_staff', 'tax')) {
                $table->decimal('tax', 10, 2)->default(0)->after('gross_pay');
            }
            if (!Schema::hasColumn('payroll_period_staff', 'pension')) {
                $table->decimal('pension', 10, 2)->default(0)->after('tax');
            }
            if (!Schema::hasColumn('payroll_period_staff', 'deductions')) {
                $table->decimal('deductions', 10, 2)->default(0)->after('pension');
            }
            if (!Schema::hasColumn('payroll_period_staff', 'net_pay')) {
                $table->decimal('net_pay', 10, 2)->default(0)->after('deductions');
            }
            if (!Schema::hasColumn('payroll_period_staff', 'worked_days')) {
                $table->integer('worked_days')->default(0)->after('net_pay');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('payroll_period_staff', function (Blueprint $table) {
            $columns = ['basic_salary', 'allowances', 'overtime', 'gross_pay', 'tax', 'pension', 'deductions', 'net_pay', 'worked_days'];
            foreach ($columns as $column) {
                if (Schema::hasColumn('payroll_period_staff', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};