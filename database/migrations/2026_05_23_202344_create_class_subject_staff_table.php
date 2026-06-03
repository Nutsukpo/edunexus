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
        Schema::create('class_subject_staff', function (Blueprint $table) {

            $table->id();

            $table->foreignId('student_class_id')
                  ->constrained()
                  ->cascadeOnDelete();

            $table->foreignId('subject_id')
                  ->constrained()
                  ->cascadeOnDelete();

            $table->foreignId('staff_id')
                  ->constrained()
                  ->cascadeOnDelete();

            $table->string('day')->nullable();

            $table->time('start_time')->nullable();

            $table->time('end_time')->nullable();

            $table->string('room')->nullable();

            $table->timestamps();

            // Prevent duplicate assignment
            $table->unique([
                'student_class_id',
                'subject_id',
                'staff_id'
            ], 'class_subject_staff_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('class_subject_staff');
    }
};