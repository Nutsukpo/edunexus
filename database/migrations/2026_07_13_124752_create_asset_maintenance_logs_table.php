<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('asset_maintenance', function (Blueprint $table) {
            $table->id();
            
            $table->foreignId('asset_id')->constrained()->onDelete('cascade');
            $table->foreignId('performed_by')->constrained('users')->onDelete('cascade');
            
            $table->enum('maintenance_type', ['preventive', 'corrective', 'emergency', 'scheduled']);
            $table->date('maintenance_date');
            $table->date('next_maintenance_date')->nullable();
            
            $table->text('description')->nullable();
            $table->text('notes')->nullable();
            $table->decimal('cost', 15, 2)->nullable();
            
            $table->string('document_path')->nullable();
            $table->string('document_name')->nullable();
            
            $table->enum('status', ['scheduled', 'in_progress', 'completed', 'cancelled'])->default('scheduled');
            
            $table->timestamps();
            $table->softDeletes();
            
            $table->index(['asset_id', 'maintenance_date']);
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('asset_maintenance');
    }
};