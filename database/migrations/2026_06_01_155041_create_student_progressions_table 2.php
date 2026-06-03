<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('student_progressions', function (Blueprint $table) {

            $table->id();

            $table->foreignId('student_id')
                  ->constrained()
                  ->cascadeOnDelete();

            $table->foreignId('from_class_id')
                  ->constrained('student_classes')
                  ->cascadeOnDelete();

            $table->foreignId('to_class_id')
                  ->nullable()
                  ->constrained('student_classes')
                  ->nullOnDelete();

            $table->foreignId('academic_year_id')
                  ->constrained()
                  ->cascadeOnDelete();

            $table->enum('action', ['promoted', 'repeated', 'graduated']);

            $table->text('remarks')->nullable();

            $table->foreignId('processed_by')
                  ->nullable()
                  ->constrained('users')
                  ->nullOnDelete();

            $table->timestamps();

        });
    }

    public function down(): void
    {
        Schema::dropIfExists('student_progressions');
    }
};