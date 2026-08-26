<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('student_fee_accounts', function (Blueprint $table) {
            // Add student_class_assignment_id if it doesn't exist
            if (!Schema::hasColumn('student_fee_accounts', 'student_class_assignment_id')) {
                $table->foreignId('student_class_assignment_id')
                    ->nullable()
                    ->after('student_class_id')
                    ->constrained('student_class_assignments')
                    ->onDelete('cascade');
            }
        });
    }

    public function down(): void
    {
        Schema::table('student_fee_accounts', function (Blueprint $table) {
            if (Schema::hasColumn('student_fee_accounts', 'student_class_assignment_id')) {
                $table->dropForeign(['student_class_assignment_id']);
                $table->dropColumn('student_class_assignment_id');
            }
        });
    }
};