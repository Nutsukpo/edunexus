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
        Schema::table('payslips', function (Blueprint $table) {
            // Check if column doesn't exist before adding
            if (!Schema::hasColumn('payslips', 'payroll_period_id')) {
                $table->foreignId('payroll_period_id')
                    ->nullable()
                    ->after('staff_id')
                    ->constrained('payroll_periods')
                    ->onDelete('set null');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('payslips', function (Blueprint $table) {
            if (Schema::hasColumn('payslips', 'payroll_period_id')) {
                $table->dropForeign(['payroll_period_id']);
                $table->dropColumn('payroll_period_id');
            }
        });
    }
};