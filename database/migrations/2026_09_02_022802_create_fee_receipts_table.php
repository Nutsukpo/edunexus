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
        Schema::create('fee_receipts', function (Blueprint $table) {
            $table->id();

            // Payment this receipt belongs to
            $table->foreignId('fee_payment_id')
                ->constrained('fee_payments')
                ->cascadeOnDelete();

            // Receipt identification
            $table->string('receipt_number')->unique();

            // Receipt generation information
            $table->string('receipt_template')->nullable();

            // Snapshot of receipt information at the time of generation
            $table->json('receipt_data')->nullable();

            // Generated PDF location
            $table->string('pdf_path')->nullable();

            // Date/time receipt was generated
            $table->timestamp('generated_at')->nullable();

            $table->timestamps();

            // Useful lookup index
            $table->index('fee_payment_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('fee_receipts');
    }
};