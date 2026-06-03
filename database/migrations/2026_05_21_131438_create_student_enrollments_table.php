<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('student_enrollments', function (Blueprint $table) {

            $table->id();
        
            $table->foreignId('student_id')->constrained()->restrictOnDelete();
        
            $table->foreignId('student_class_id')->constrained()->cascadeOnDelete();
        
            $table->foreignId('academic_year_id')->constrained()->cascadeOnDelete();
        
            $table->foreignId('term_id')->nullable()->constrained()->nullOnDelete();
        
            $table->boolean('is_current')->default(true);
        
            $table->date('enrollment_date')->nullable();
        
            $table->timestamps();
        
            // ✅ FIXED SHORT INDEX NAME
            $table->unique(['student_id', 'student_class_id', 'academic_year_id'], 'enroll_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('student_enrollments');
    }
};