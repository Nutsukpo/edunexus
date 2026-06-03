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
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
        
            $table->foreignId('student_invoice_id')->constrained()->cascadeOnDelete();
        
            $table->string('receipt_no')->unique();
            $table->decimal('amount', 12, 2);
        
            $table->string('payment_method')->nullable();
            $table->date('payment_date');
        
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
