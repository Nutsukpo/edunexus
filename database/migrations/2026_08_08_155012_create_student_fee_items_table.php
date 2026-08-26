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
        Schema::create('student_fee_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_fee_account_id')->constrained('student_fee_accounts')->onDelete('cascade');
            $table->foreignId('class_fee_structure_id')->nullable()->constrained('class_fee_structures')->onDelete('set null');
            $table->string('fee_type');
            $table->string('fee_name');
            $table->decimal('amount', 15, 2);
            $table->text('description')->nullable();
            $table->boolean('is_required')->default(true);
            $table->date('due_date')->nullable();
            $table->enum('status', ['pending', 'partial', 'paid', 'overdue'])->default('pending');
            $table->decimal('paid_amount', 15, 2)->default(0);
            $table->decimal('remaining_amount', 15, 2)->default(0);
            $table->json('metadata')->nullable();
            $table->timestamps();
            $table->softDeletes();

            // Indexes for better performance
            $table->index('student_fee_account_id');
            $table->index('fee_type');
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('student_fee_items');
    }
};