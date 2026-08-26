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
        Schema::create('payroll_period_staff', function (Blueprint $table) {


            $table->id();
        
        
            $table->foreignId('payroll_period_id')
                  ->constrained()
                  ->cascadeOnDelete();
        
        
            $table->foreignId('staff_id')
                  ->constrained()
                  ->cascadeOnDelete();
        
        
        
            $table->foreignId('salary_structure_id')
                  ->nullable()
                  ->constrained()
                  ->nullOnDelete();
        
        
        
            // Salary snapshot
            // Important because salary structures can change later
        
            $table->decimal(
                'basic_salary',
                12,
                2
            )->default(0);
        
        
        
            $table->decimal(
                'total_allowance',
                12,
                2
            )->default(0);
        
        
        
            $table->decimal(
                'total_deduction',
                12,
                2
            )->default(0);
        
        
        
            $table->decimal(
                'gross_salary',
                12,
                2
            )->default(0);
        
        
        
            $table->decimal(
                'net_salary',
                12,
                2
            )->default(0);
        
        
        
            $table->enum('status',[
        
                'Pending',
                'Processed',
                'Approved',
                'Paid'
        
            ])->default('Pending');
        
        
            $table->timestamps();
        
        
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payroll_period_staff');
    }
};
