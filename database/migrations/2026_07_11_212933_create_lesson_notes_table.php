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
        Schema::create('lesson_notes', function (Blueprint $table) {
            $table->id();
            
            // Core identifiers
            $table->string('note_code')->unique();
            $table->enum('type', ['daily', 'weekly', 'monthly', 'termly'])->default('daily'); // Fixed: used enum with proper syntax
            
            // Foreign keys - using staff instead of teacher
            $table->unsignedBigInteger('staff_id');
            $table->unsignedBigInteger('student_class_id');
            $table->unsignedBigInteger('subject_id');
            $table->unsignedBigInteger('academic_year_id');
            $table->unsignedBigInteger('term_id');
            
            // Lesson details
            $table->string('topic');
            $table->string('sub_topic')->nullable();
            $table->text('description')->nullable();
            $table->longText('content');
            
            // Additional metadata
            $table->date('lesson_date');
            $table->time('start_time')->nullable();
            $table->time('end_time')->nullable();
            $table->string('duration')->nullable(); // e.g., "45 minutes", "1 hour"
            
            // Resources and materials
            $table->string('attachment')->nullable(); // For single file
            $table->json('attachments')->nullable(); // For multiple files
            $table->json('resources')->nullable(); // URLs, references, etc.
            
            // Lesson objectives
            $table->json('learning_objectives')->nullable();
            $table->json('learning_outcomes')->nullable();
            
            // Delivery method
            $table->string('delivery_method')->nullable(); // lecture, practical, group work, etc.
            $table->json('teaching_aids')->nullable();
            
            // Assessment
            $table->json('assessment_methods')->nullable();
            $table->text('homework')->nullable();
            
            // Additional information
            $table->text('remarks')->nullable();
            $table->text('challenges')->nullable();
            $table->text('recommendations')->nullable();
            
            // Student engagement
            $table->integer('expected_students')->nullable();
            $table->integer('actual_students')->nullable();
            $table->json('student_participation')->nullable();
            
            // Comment feature
            $table->text('comment')->nullable();
            $table->json('comments')->nullable(); // For multiple comments
            $table->unsignedBigInteger('commented_by')->nullable(); // staff_id
            
            // Status - nullable as requested
            $table->string('status')->nullable();
            
            // Soft deletes and timestamps
            $table->timestamps();
            $table->softDeletes();
            
            // Foreign key constraints
            $table->foreign('staff_id')->references('id')->on('staff')->onDelete('cascade');
            $table->foreign('student_class_id')->references('id')->on('student_classes')->onDelete('cascade');
            $table->foreign('subject_id')->references('id')->on('subjects')->onDelete('cascade');
            $table->foreign('academic_year_id')->references('id')->on('academic_years')->onDelete('cascade');
            $table->foreign('term_id')->references('id')->on('terms')->onDelete('cascade');
            $table->foreign('commented_by')->references('id')->on('staff')->onDelete('set null');
            
            // Indexes for better performance
            $table->index(['staff_id', 'lesson_date']);
            $table->index(['student_class_id', 'subject_id']);
            $table->index(['academic_year_id', 'term_id']);
            $table->index('note_code');
            $table->index('type');
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lesson_notes');
    }
};