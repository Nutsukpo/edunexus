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
        Schema::create('attendance_sessions', function (Blueprint $table) {

            $table->id();

            $table->foreignId('student_class_id')
                  ->constrained()
                  ->cascadeOnDelete();

            $table->date('attendance_date');

            $table->foreignId('taken_by')
                  ->nullable()
                  ->constrained('users')
                  ->nullOnDelete();

            $table->text('remarks')->nullable();

            $table->enum('status', [
                'open',
                'completed'
            ])->default('open');

            $table->timestamps();

            /*
            |--------------------------------------------------------------------------
            | PREVENT DUPLICATE ATTENDANCE
            |--------------------------------------------------------------------------
            */
            $table->unique([
                'student_class_id',
                'attendance_date'
            ]);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('attendance_sessions');
    }
};