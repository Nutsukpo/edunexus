<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('student_invoices', function (Blueprint $table) {

            $table->id();

            // Core relations
            $table->foreignId('student_id')
                  ->constrained()
                  ->cascadeOnDelete();

            $table->foreignId('academic_year_id')
                  ->constrained()
                  ->cascadeOnDelete();

            $table->foreignId('term_id')
                  ->constrained()
                  ->cascadeOnDelete();

            $table->foreignId('student_class_id')
                  ->constrained()
                  ->cascadeOnDelete();

            // Invoice identity
            $table->string('invoice_number')->unique();

            // Financial summary
            $table->decimal('total_amount', 12, 2)->default(0);
            $table->decimal('paid_amount', 12, 2)->default(0);
            $table->decimal('balance', 12, 2)->default(0);

            // Payment status
            $table->enum('status', ['pending', 'partial', 'paid'])
                  ->default('pending');

            // Audit fields (recommended for ERP systems)
            $table->foreignId('generated_by')
                  ->nullable()
                  ->constrained('users')
                  ->nullOnDelete();

            $table->timestamp('generated_at')->nullable();

            $table->timestamps();

            // Indexes for performance (important for large schools)
            $table->index(['student_id', 'academic_year_id', 'term_id']);
            $table->index('invoice_number');
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('student_invoices');
    }
};