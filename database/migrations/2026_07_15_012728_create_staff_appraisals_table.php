<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('staff_appraisals', function (Blueprint $table) {
            $table->id();
            
            // Basic Information
            $table->string('title')->nullable();
            $table->text('description')->nullable();
            
            // Relationships
            $table->foreignId('staff_id')->constrained('staff')->onDelete('cascade');
            $table->foreignId('academic_year_id')->constrained('academic_years')->onDelete('cascade');
            $table->foreignId('term_id')->constrained('terms')->onDelete('cascade');
            
            // File Information
            $table->string('file_path');
            $table->string('file_name');
            $table->string('file_type');
            $table->bigInteger('file_size')->nullable();
            $table->string('file_mime')->nullable();
            
            // Submission Date
            $table->date('submission_date');
            
            // Status
            $table->enum('status', ['draft', 'submitted', 'reviewed', 'approved', 'rejected'])->default('draft');
            
            // Review Information
            $table->text('reviewer_comments')->nullable();
            $table->foreignId('reviewed_by')->nullable()->constrained('staff')->onDelete('set null');
            $table->timestamp('reviewed_at')->nullable();
            
            // Tracking
            $table->foreignId('created_by')->constrained('staff')->onDelete('cascade');
            $table->foreignId('updated_by')->nullable()->constrained('staff')->onDelete('set null');
            
            $table->softDeletes();
            $table->timestamps();
            
            // Indexes
            $table->index(['staff_id', 'academic_year_id', 'term_id']);
            $table->index('status');
            $table->index('submission_date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('staff_appraisals');
    }
};