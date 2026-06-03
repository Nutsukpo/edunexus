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
        Schema::table('attendances', function (Blueprint $table) {

            $table->foreignId('student_class_assignment_id')
                  ->nullable()
                  ->after('attendance_session_id')
                  ->constrained()
                  ->cascadeOnDelete();

        });
    }

    /**
     * Reverse migrations.
     */
    public function down(): void
    {
        Schema::table('attendances', function (Blueprint $table) {

            $table->dropForeign([
                'student_class_assignment_id'
            ]);

            $table->dropColumn(
                'student_class_assignment_id'
            );
        });
    }
};