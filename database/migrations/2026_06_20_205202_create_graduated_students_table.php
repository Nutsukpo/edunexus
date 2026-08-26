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
        Schema::create('graduated_students', function (Blueprint $table) {

            $table->id();
        
            $table->foreignId('student_id')
                  ->constrained()
                  ->cascadeOnDelete();
        
            $table->foreignId('student_class_id')
                  ->constrained()
                  ->cascadeOnDelete();
        
            $table->foreignId('academic_year_id')
                  ->constrained()
                  ->cascadeOnDelete();
        
            $table->foreignId('term_id')
                  ->constrained()
                  ->cascadeOnDelete();
        
            $table->date('graduation_date');
        
            $table->string('graduation_type')->default('Completed');
        
            $table->text('remarks')->nullable();
        
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('graduated_students');
    }
};
