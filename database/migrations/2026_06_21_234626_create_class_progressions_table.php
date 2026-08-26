<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('class_progressions', function (Blueprint $table) {

            $table->id();

            $table->foreignId('from_class_id')
                  ->constrained('student_classes')
                  ->cascadeOnDelete();

            $table->foreignId('to_class_id')
                  ->nullable()
                  ->constrained('student_classes')
                  ->nullOnDelete();

            $table->boolean('is_graduation')
                  ->default(false);

            $table->timestamps();

            $table->unique('from_class_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('class_progressions');
    }
};
