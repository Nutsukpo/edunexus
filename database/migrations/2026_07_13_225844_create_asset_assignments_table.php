<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('asset_assignments', function (Blueprint $table) {
            $table->id();
            
            $table->foreignId('asset_id')->constrained()->onDelete('cascade');
            $table->foreignId('assigned_to')->constrained('users')->onDelete('cascade');
            $table->foreignId('assigned_by')->constrained('users')->onDelete('cascade');
            $table->foreignId('returned_to')->nullable()->constrained('users')->onDelete('set null');
            
            $table->date('assigned_date');
            $table->date('expected_return_date')->nullable();
            $table->date('actual_return_date')->nullable();
            
            $table->text('assignment_notes')->nullable();
            $table->text('return_notes')->nullable();
            
            $table->enum('status', ['active', 'returned', 'overdue'])->default('active');
            
            $table->timestamps();
            $table->softDeletes();
            
            $table->index(['asset_id', 'status']);
            $table->index('assigned_to');
            $table->index('assigned_date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('asset_assignments');
    }
};