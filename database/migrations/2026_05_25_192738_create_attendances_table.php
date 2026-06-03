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
        Schema::create('attendances', function (Blueprint $table) {

            $table->id();

            $table->foreignId('attendance_session_id')
                  ->constrained()
                  ->cascadeOnDelete();

            $table->foreignId('student_id')
                  ->constrained()
                  ->cascadeOnDelete();

            $table->enum('status', [
                'present',
                'absent',
                'late',
                'excused'
            ])->default('present');

            $table->time('check_in_time')->nullable();

            $table->text('remarks')->nullable();

            $table->timestamps();

            $table->unique([
                'attendance_session_id',
                'student_id'
            ]);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('attendances');
    }
};