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
        Schema::create('payroll_periods', function (Blueprint $table) {

            $table->id();
        
            $table->string('period_code')->unique();
        
            $table->string('name');
        
            $table->date('start_date');
        
            $table->date('end_date');
        
            $table->date('payment_date')->nullable();
        
            $table->enum('status',[
                'draft',
                'processing',
                'approved',
                'paid',
                'cancelled'
            ])->default('draft');
        
            $table->text('description')->nullable();
        
            $table->foreignId('created_by')->nullable()->constrained('staff')->nullOnDelete();
        
            $table->foreignId('approved_by')->nullable()->constrained('staff')->nullOnDelete();
        
            $table->timestamp('approved_at')->nullable();
        
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payroll_periods');
    }
};
