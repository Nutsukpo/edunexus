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

            /*
            |--------------------------------------------------------------------------
            | RELATIONSHIPS
            |--------------------------------------------------------------------------
            */

            $table->foreignId('student_invoice_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->foreignId('student_id')
                ->constrained()
                ->cascadeOnDelete();

            /*
            |--------------------------------------------------------------------------
            | PAYMENT DETAILS
            |--------------------------------------------------------------------------
            */

            $table->string('receipt_number')->unique();

            $table->decimal('amount', 12, 2);

            $table->date('payment_date');

            $table->enum('payment_method', [
                'Cash',
                'Bank Transfer',
                'Mobile Money',
                'Cheque',
                'POS',
            ]);

            $table->string('reference_number')->nullable();

            $table->text('remarks')->nullable();

            /*
            |--------------------------------------------------------------------------
            | AUDIT TRAIL
            |--------------------------------------------------------------------------
            */

            $table->foreignId('received_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            /*
            |--------------------------------------------------------------------------
            | SYSTEM FIELDS
            |--------------------------------------------------------------------------
            */

            $table->softDeletes();

            $table->timestamps();

            /*
            |--------------------------------------------------------------------------
            | INDEXES
            |--------------------------------------------------------------------------
            */

            $table->index('student_invoice_id');
            $table->index('student_id');
            $table->index('receipt_number');
            $table->index('payment_date');
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