<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('assets', function (Blueprint $table) {
            $table->id();
            
            // Basic Information
            $table->string('name');
            $table->string('asset_code')->unique();
            $table->text('description')->nullable();
            
            // Foreign Keys
            $table->foreignId('category_id')->nullable()->constrained('asset_categories')->onDelete('set null');
            $table->foreignId('created_by')->constrained('users')->onDelete('cascade');
            $table->foreignId('updated_by')->nullable()->constrained('users')->onDelete('set null');
            
            // Asset Details
            $table->string('serial_number')->nullable();
            $table->string('model')->nullable();
            $table->string('brand')->nullable();
            $table->integer('quantity')->default(1);
            
            // Financial Information
            $table->decimal('purchase_price', 15, 2)->nullable();
            $table->decimal('current_value', 15, 2)->nullable();
            $table->date('purchase_date')->nullable();
            $table->date('warranty_expiry')->nullable();
            
            // Location & Status
            $table->string('location')->nullable();
            $table->enum('status', ['available', 'assigned', 'maintenance', 'damaged', 'disposed'])->default('available');
            $table->enum('condition', ['new', 'good', 'fair', 'poor', 'damaged'])->default('good');
            
            // Files
            $table->string('image_path')->nullable();
            $table->string('document_path')->nullable();
            $table->string('document_name')->nullable();
            
            // Additional Info
            $table->json('metadata')->nullable();
            $table->text('notes')->nullable();
            
            // Soft Deletes
            $table->softDeletes();
            $table->timestamps();
            
            // Indexes
            $table->index(['category_id', 'status']);
            $table->index('asset_code');
            $table->index('serial_number');
            $table->index('status');
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('assets');
    }
};