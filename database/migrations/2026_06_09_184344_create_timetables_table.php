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
        Schema::create('timetables', function (Blueprint $table) {

            $table->id();

            // Academic Session
            $table->foreignId('academic_year_id')
                ->nullable()
                ->constrained()
                ->nullOnDelete();

            // Class
            $table->foreignId('student_class_id')
                ->constrained()
                ->cascadeOnDelete();

            // Uploaded By
            $table->foreignId('uploaded_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            // Timetable Details
            $table->string('title');

            $table->text('description')
                ->nullable();

            // File Information
            $table->string('file_name');

            $table->string('file_path');

            $table->string('file_type')
                ->nullable(); // pdf, docx, xlsx, image

            $table->bigInteger('file_size')
                ->nullable();

            // Status
            $table->enum('status', [
                'active',
                'archived'
            ])->default('active');

            $table->timestamps();

            $table->softDeletes();

            // Indexes
            $table->index('academic_year_id');
            $table->index('student_class_id');
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('timetables');
    }
};
