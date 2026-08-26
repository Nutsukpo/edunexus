<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('assessment_forms', function (Blueprint $table) {
            $table->id();
            
            // Basic Information
            $table->string('title')->nullable();
            $table->text('description')->nullable();
            
            // Foreign Keys
            $table->foreignId('staff_id')->constrained('staff')->onDelete('cascade');
            $table->foreignId('student_class_id')->constrained('student_classes')->onDelete('cascade');
            $table->foreignId('academic_year_id')->constrained('academic_years')->onDelete('cascade');
            $table->foreignId('term_id')->constrained('terms')->onDelete('cascade');
            $table->foreignId('subject_id')->nullable()->constrained('subjects')->onDelete('set null');
            
            // File Information
            $table->string('file_path');
            $table->string('file_name');
            $table->string('file_type'); // pdf, jpg, jpeg, png, gif, doc, docx
            $table->bigInteger('file_size')->nullable();
            $table->string('file_mime')->nullable();
            
            // Date Information
            $table->date('assessment_date');
            $table->date('due_date')->nullable();
            
            // Status
            $table->enum('status', ['draft', 'published', 'archived'])->default('draft');
            
            // Assessment Type
            $table->enum('assessment_type', ['quiz', 'test', 'exam', 'assignment', 'project'])->default('test');
            
            // Metadata
            $table->json('metadata')->nullable();
            $table->integer('downloads_count')->default(0);
            $table->integer('views_count')->default(0);
            
            // Tracking
            $table->foreignId('created_by')->constrained('staff')->onDelete('cascade');
            $table->foreignId('updated_by')->nullable()->constrained('staff')->onDelete('set null');
            
            // Soft Deletes
            $table->softDeletes();
            $table->timestamps();
            
            // Indexes
            $table->index(['staff_id', 'student_class_id']);
            $table->index(['academic_year_id', 'term_id']);
            $table->index('assessment_date');
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('assessment_forms');
    }
};