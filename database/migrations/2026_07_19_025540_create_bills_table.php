<?php
// database/migrations/xxxx_xx_xx_create_bills_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('bills', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained()->onDelete('cascade');
            $table->string('invoice_number')->unique();
            $table->date('bill_date');
            $table->date('due_date')->nullable();
            $table->date('billing_period_start')->nullable();
            $table->date('billing_period_end')->nullable();
            
            $table->decimal('subtotal', 15, 2)->default(0);
            $table->decimal('discount_amount', 15, 2)->default(0);
            $table->decimal('discount_percentage', 5, 2)->default(0);
            $table->decimal('tax_amount', 15, 2)->default(0);
            $table->decimal('tax_rate', 5, 2)->default(0);
            $table->decimal('total_amount', 15, 2);
            $table->decimal('paid_amount', 15, 2)->default(0);
            $table->decimal('balance', 15, 2)->storedAs('total_amount - paid_amount');
            
            $table->enum('status', [
                'draft', 'sent', 'viewed', 'approved', 
                'partial', 'paid', 'overdue', 'cancelled', 'refunded'
            ])->default('draft');
            
            $table->string('billing_type')->default('other');
            $table->text('notes')->nullable();
            $table->text('terms')->nullable();
            $table->string('pdf_path')->nullable();
            
            $table->foreignId('generated_by')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('approved_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('approved_at')->nullable();
            $table->timestamp('sent_at')->nullable();
            $table->timestamp('paid_at')->nullable();
            
            // Recurring billing
            $table->boolean('is_recurring')->default(false);
            $table->string('recurring_frequency')->nullable();
            $table->date('recurring_until')->nullable();
            $table->foreignId('parent_invoice_id')->nullable()->constrained('bills')->onDelete('set null');
            
            $table->softDeletes();
            $table->timestamps();
            
            $table->index(['student_id', 'status']);
            $table->index(['bill_date', 'due_date']);
            $table->index('invoice_number');
        });

        Schema::create('bill_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('bill_id')->constrained()->onDelete('cascade');
            $table->foreignId('fee_item_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('fee_category_id')->nullable()->constrained()->onDelete('set null');
            
            $table->string('description');
            $table->integer('quantity')->default(1);
            $table->decimal('unit_price', 15, 2);
            $table->decimal('total_amount', 15, 2);
            $table->decimal('discount', 15, 2)->default(0);
            $table->decimal('tax', 15, 2)->default(0);
            $table->decimal('net_amount', 15, 2);
            $table->text('notes')->nullable();
            $table->integer('sort_order')->default(0);
            
            $table->timestamps();
            
            $table->index('sort_order');
        });
    }

    public function down()
    {
        Schema::dropIfExists('bill_items');
        Schema::dropIfExists('bills');
    }
};