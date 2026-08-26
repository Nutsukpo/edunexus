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
        // Payroll Periods Table
        Schema::create('payroll_periods', function (Blueprint $table) {
            $table->id();
            $table->string('period_code')->unique();
            $table->string('name'); // e.g., "July 2026 Payroll"
            $table->enum('period_type', ['monthly', 'bi-weekly', 'weekly'])->default('monthly');
            $table->date('start_date');
            $table->date('end_date');
            $table->date('payment_date')->nullable();
            $table->enum('status', ['draft', 'processing', 'completed', 'cancelled'])->default('draft');
            $table->text('description')->nullable();
            
            // Tracking
            $table->unsignedBigInteger('created_by')->nullable(); // staff_id
            $table->unsignedBigInteger('approved_by')->nullable(); // staff_id
            $table->timestamp('approved_at')->nullable();
            
            $table->timestamps();
            $table->softDeletes();
            
            // Foreign keys
            $table->foreign('created_by')->references('id')->on('staff')->onDelete('set null');
            $table->foreign('approved_by')->references('id')->on('staff')->onDelete('set null');
            
            // Indexes
            $table->index('period_code');
            $table->index('status');
            $table->index(['start_date', 'end_date']);
        });

        // Payroll Items Table (Individual Staff Payroll Records)
        Schema::create('payroll_items', function (Blueprint $table) {
            $table->id();
            
            // Relationships
            $table->unsignedBigInteger('payroll_period_id');
            $table->unsignedBigInteger('staff_id');
            
            // Employee Information (snapshot at time of payroll)
            $table->string('staff_name');
            $table->string('staff_email')->nullable();
            $table->string('staff_phone')->nullable();
            $table->string('staff_position')->nullable();
            $table->string('staff_department')->nullable();
            
            // Payroll Details
            $table->decimal('basic_salary', 15, 2)->default(0);
            $table->decimal('allowance', 15, 2)->default(0);
            $table->decimal('bonus', 15, 2)->default(0);
            $table->decimal('overtime_pay', 15, 2)->default(0);
            $table->decimal('commission', 15, 2)->default(0);
            
            // Deductions
            $table->decimal('tax', 15, 2)->default(0);
            $table->decimal('pension', 15, 2)->default(0);
            $table->decimal('health_insurance', 15, 2)->default(0);
            $table->decimal('loan_deduction', 15, 2)->default(0);
            $table->decimal('other_deductions', 15, 2)->default(0);
            
            // Net Pay
            $table->decimal('gross_pay', 15, 2)->default(0);
            $table->decimal('total_deductions', 15, 2)->default(0);
            $table->decimal('net_pay', 15, 2)->default(0);
            
            // Payment Details
            $table->enum('payment_method', ['bank_transfer', 'cash', 'cheque'])->default('bank_transfer');
            $table->string('bank_name')->nullable();
            $table->string('account_number')->nullable();
            $table->string('account_name')->nullable();
            $table->date('payment_date')->nullable();
            $table->boolean('is_paid')->default(false);
            $table->timestamp('paid_at')->nullable();
            
            // Additional Info
            $table->text('notes')->nullable();
            $table->json('additional_details')->nullable(); // For extra fields
            
            $table->timestamps();
            $table->softDeletes();
            
            // Foreign keys
            $table->foreign('payroll_period_id')->references('id')->on('payroll_periods')->onDelete('cascade');
            $table->foreign('staff_id')->references('id')->on('staff')->onDelete('cascade');
            
            // Indexes
            $table->unique(['payroll_period_id', 'staff_id']);
            $table->index('staff_id');
            $table->index('is_paid');
            $table->index('payment_date');
        });

        // Payroll Adjustments Table (for manual adjustments)
        Schema::create('payroll_adjustments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('payroll_item_id');
            $table->enum('type', ['allowance', 'deduction', 'bonus', 'overtime', 'other']);
            $table->string('description');
            $table->decimal('amount', 15, 2);
            $table->enum('effect', ['add', 'subtract'])->default('add');
            $table->text('reason')->nullable();
            $table->unsignedBigInteger('approved_by')->nullable(); // staff_id
            $table->timestamp('approved_at')->nullable();
            
            $table->timestamps();
            
            $table->foreign('payroll_item_id')->references('id')->on('payroll_items')->onDelete('cascade');
            $table->foreign('approved_by')->references('id')->on('staff')->onDelete('set null');
        });

        // Payslips Table
        Schema::create('payslips', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('payroll_item_id');
            $table->string('payslip_code')->unique();
            $table->date('issue_date');
            $table->enum('status', ['generated', 'viewed', 'downloaded', 'emailed'])->default('generated');
            $table->string('file_path')->nullable();
            $table->timestamp('viewed_at')->nullable();
            $table->timestamp('downloaded_at')->nullable();
            $table->timestamp('emailed_at')->nullable();
            $table->text('notes')->nullable();
            
            $table->timestamps();
            
            $table->foreign('payroll_item_id')->references('id')->on('payroll_items')->onDelete('cascade');
            $table->index('payslip_code');
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payslips');
        Schema::dropIfExists('payroll_adjustments');
        Schema::dropIfExists('payroll_items');
        Schema::dropIfExists('payroll_periods');
    }
};