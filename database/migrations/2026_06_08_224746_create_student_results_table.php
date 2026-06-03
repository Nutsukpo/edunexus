<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('student_results', function (Blueprint $table) {

            $table->id();

            $table->foreignId('student_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->foreignId('student_class_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->foreignId('subject_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->foreignId('academic_year_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->foreignId('term_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->decimal('class_score',5,2)->default(0);

            $table->decimal('exam_score',5,2)->default(0);

            $table->decimal('total_score',5,2)->default(0);

            $table->string('grade')->nullable();

            $table->string('remark')->nullable();

            $table->timestamps();

            $table->unique([
                'student_id',
                'subject_id',
                'term_id',
                'academic_year_id'
            ], 'student_subject_term_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('student_results');
    }
};