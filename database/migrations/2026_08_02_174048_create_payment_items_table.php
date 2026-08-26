<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payment_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('fee_payment_id')->constrained()->onDelete('cascade');
            $table->foreignId('bill_sheet_item_id')->nullable()->constrained()->onDelete('set null');
            $table->string('item_name');
            $table->decimal('original_amount', 15, 2);
            $table->decimal('paid_amount', 15, 2);
            $table->decimal('balance', 15, 2);
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index('fee_payment_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payment_items');
    }
};