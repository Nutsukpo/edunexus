<?php
// database/migrations/xxxx_xx_xx_create_payments_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained()->onDelete('cascade');
            $table->foreignId('student_fee_allocation_id')->constrained()->onDelete('cascade');
            $table->string('invoice_number')->unique();
            $table->decimal('amount', 15, 2);
            $table->decimal('paid_amount', 15, 2);
            $table->decimal('balance', 15, 2)->storedAs('amount - paid_amount');
            $table->enum('payment_method', ['cash', 'bank_transfer', 'card', 'online', 'cheque', 'other'])->default('cash');
            $table->string('reference_number')->nullable();
            $table->text('payment_details')->nullable();
            $table->enum('status', ['pending', 'completed', 'failed', 'refunded'])->default('pending');
            $table->date('payment_date');
            $table->foreignId('received_by')->nullable()->constrained('users')->onDelete('set null');
            $table->text('notes')->nullable();
            $table->string('receipt_path')->nullable();
            $table->timestamps();
            
            $table->index(['student_id', 'payment_date']);
            $table->index(['invoice_number']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('payments');
    }
};