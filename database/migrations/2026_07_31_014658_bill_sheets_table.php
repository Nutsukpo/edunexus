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
        // Create bill_sheets table
        Schema::create('bill_sheets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_class_id')->constrained()->onDelete('cascade');
            $table->foreignId('academic_year_id')->constrained()->onDelete('cascade');
            $table->foreignId('term_id')->nullable()->constrained()->onDelete('set null');
            $table->string('name');
            $table->text('description')->nullable();
            $table->date('generated_date');
            $table->date('due_date')->nullable();
            $table->decimal('total_amount', 15, 2)->default(0);
            $table->decimal('discount_amount', 15, 2)->default(0);
            $table->decimal('tax_amount', 15, 2)->default(0);
            $table->decimal('net_amount', 15, 2)->default(0);
            $table->enum('status', ['draft', 'pending', 'approved', 'rejected', 'published', 'archived'])
                ->default('draft');
            $table->foreignId('generated_by')->constrained('users');
            $table->foreignId('approved_by')->nullable()->constrained('users');
            $table->timestamp('approved_at')->nullable();
            $table->boolean('is_active')->default(true);
            $table->json('metadata')->nullable();
            $table->timestamps();
            $table->softDeletes();

            // Indexes for better performance
            $table->index(['student_class_id', 'academic_year_id', 'term_id']);
            $table->index('status');
        });

        // Create bill_sheet_items table
        Schema::create('bill_sheet_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('bill_sheet_id')->constrained()->onDelete('cascade');
            $table->foreignId('fee_structure_id')->nullable()->constrained('school_fee_structures')->onDelete('set null');
            $table->foreignId('fee_category_id')->nullable()->constrained()->onDelete('set null');
            $table->string('name');
            $table->text('description')->nullable();
            $table->decimal('amount', 15, 2);
            $table->integer('quantity')->default(1);
            $table->decimal('total_amount', 15, 2);
            $table->boolean('is_optional')->default(false);
            $table->boolean('is_mandatory')->default(true);
            $table->integer('sort_order')->default(0);
            $table->json('metadata')->nullable();
            $table->timestamps();
            $table->softDeletes();

            // Indexes
            $table->index('bill_sheet_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Drop tables in reverse order to avoid foreign key constraints
        Schema::dropIfExists('bill_sheet_items');
        Schema::dropIfExists('bill_sheets');
    }
};