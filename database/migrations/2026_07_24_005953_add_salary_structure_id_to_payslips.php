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
            if (!Schema::hasColumn('payslips', 'salary_structure_id')) {
                $table->unsignedBigInteger('salary_structure_id')->nullable()->after('payroll_period_id');
                $table->foreign('salary_structure_id')
                      ->references('id')
                      ->on('salary_structures')
                      ->onDelete('set null');
            }
            
            // Check if tier2 column doesn't exist (optional - if you need to add tier fields)
            if (!Schema::hasColumn('payslips', 'tier2')) {
                $table->decimal('tier2', 15, 2)->default(0)->nullable()->after('pension');
            }
            
            if (!Schema::hasColumn('payslips', 'tier3')) {
                $table->decimal('tier3', 15, 2)->default(0)->nullable()->after('tier2');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('payslips', function (Blueprint $table) {
            // Drop foreign key constraint first
            if (Schema::hasColumn('payslips', 'salary_structure_id')) {
                $table->dropForeign(['salary_structure_id']);
            }
            
            // Drop columns if they exist
            if (Schema::hasColumn('payslips', 'salary_structure_id')) {
                $table->dropColumn('salary_structure_id');
            }
            
            if (Schema::hasColumn('payslips', 'tier2')) {
                $table->dropColumn('tier2');
            }
            
            if (Schema::hasColumn('payslips', 'tier3')) {
                $table->dropColumn('tier3');
            }
        });
    }
};