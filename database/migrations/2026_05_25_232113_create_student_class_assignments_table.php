<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run migrations.
     */
    public function up(): void
    {
        Schema::create('student_class_assignments', function (Blueprint $table) {

            $table->id();

            /*
            |--------------------------------------------------------------------------
            | RELATIONSHIPS
            |--------------------------------------------------------------------------
            */

            $table->foreignId('student_id')
                  ->constrained()
                  ->cascadeOnDelete();

            $table->foreignId('student_class_id')
                  ->constrained()
                  ->cascadeOnDelete();

            $table->foreignId('academic_year_id')
                  ->nullable()
                  ->constrained()
                  ->nullOnDelete();

            /*
            |--------------------------------------------------------------------------
            | STATUS
            |--------------------------------------------------------------------------
            */

            $table->enum('status', [
                'active',
                'promoted',
                'repeated',
                'transferred',
                'graduated',
            ])->default('active');

            /*
            |--------------------------------------------------------------------------
            | TRACKING
            |--------------------------------------------------------------------------
            */

            $table->date('assigned_date')->nullable();

            $table->date('promotion_date')->nullable();

            $table->boolean('is_current')
                  ->default(true);

            $table->timestamps();
        });
    }

    /**
     * Reverse migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('student_class_assignments');
    }
};