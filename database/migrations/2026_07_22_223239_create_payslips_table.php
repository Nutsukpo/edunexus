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
        Schema::create('payslips', function (Blueprint $table) {
            $table->id();
            
            // Staff relationship
            $table->foreignId('staff_id')
                  ->constrained()
                  ->onDelete('cascade');
            
            // Period
            $table->string('month', 20);
            $table->year('year');
            
            // Earnings
            $table->decimal('basic_salary', 15, 2)->default(0);
            $table->decimal('allowances', 15, 2)->default(0);
            $table->decimal('bonus', 15, 2)->default(0);
            $table->decimal('overtime', 15, 2)->default(0);
            $table->decimal('total_earnings', 15, 2)->default(0);
            
            // Deductions
            $table->decimal('tax', 15, 2)->default(0);
            $table->decimal('pension', 15, 2)->default(0);
            $table->decimal('insurance', 15, 2)->default(0);
            $table->decimal('loans', 15, 2)->default(0);
            $table->decimal('other_deductions', 15, 2)->default(0);
            $table->decimal('total_deductions', 15, 2)->default(0);
            
            // Net pay
            $table->decimal('net_pay', 15, 2);
            
            // Status
            $table->enum('status', ['draft', 'approved', 'paid', 'cancelled'])
                  ->default('draft');
            
            // Additional info
            $table->json('breakdown')->nullable();
            $table->text('notes')->nullable();
            
            // Audit
            $table->foreignId('created_by')
                  ->nullable()
                  ->constrained('users')
                  ->nullOnDelete();
            
            $table->timestamps();
            
            // Indexes
            $table->index(['staff_id', 'month', 'year']);
            $table->index('status');
            
            // Prevent duplicates
            $table->unique(['staff_id', 'month', 'year']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payslips');
    }
};