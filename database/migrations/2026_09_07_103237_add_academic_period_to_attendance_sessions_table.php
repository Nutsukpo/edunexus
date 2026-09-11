<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Add academic year and term to attendance sessions.
     *
     * Existing attendance records are preserved.
     */
    public function up(): void
    {
        /*
        |--------------------------------------------------------------------------
        | Add academic year
        |--------------------------------------------------------------------------
        */

        if (!Schema::hasColumn('attendance_sessions', 'academic_year_id')) {
            Schema::table('attendance_sessions', function (Blueprint $table) {
                $table->foreignId('academic_year_id')
                    ->nullable()
                    ->after('student_class_id')
                    ->constrained('academic_years')
                    ->nullOnDelete();
            });
        }

        /*
        |--------------------------------------------------------------------------
        | Add term
        |--------------------------------------------------------------------------
        */

        if (!Schema::hasColumn('attendance_sessions', 'term_id')) {
            Schema::table('attendance_sessions', function (Blueprint $table) {
                $table->foreignId('term_id')
                    ->nullable()
                    ->after('academic_year_id')
                    ->constrained('terms')
                    ->nullOnDelete();
            });
        }

        /*
        |--------------------------------------------------------------------------
        | Remove original uniqueness constraint
        |--------------------------------------------------------------------------
        */

        $indexes = collect(
            Schema::getIndexes('attendance_sessions')
        );

        if ($indexes->contains(function ($index) {
            return $index['name'] ===
                'attendance_sessions_student_class_id_attendance_date_unique';
        })) {
            Schema::table('attendance_sessions', function (Blueprint $table) {
                $table->dropUnique(
                    'attendance_sessions_student_class_id_attendance_date_unique'
                );
            });
        }

        /*
        |--------------------------------------------------------------------------
        | Add period-aware uniqueness
        |--------------------------------------------------------------------------
        */

        $indexes = collect(
            Schema::getIndexes('attendance_sessions')
        );

        if (!$indexes->contains(function ($index) {
            return $index['name'] ===
                'attendance_sessions_period_date_unique';
        })) {
            Schema::table('attendance_sessions', function (Blueprint $table) {
                $table->unique(
                    [
                        'student_class_id',
                        'academic_year_id',
                        'term_id',
                        'attendance_date',
                    ],
                    'attendance_sessions_period_date_unique'
                );
            });
        }
    }

    /**
     * Reverse the migration.
     */
    public function down(): void
    {
        /*
        |--------------------------------------------------------------------------
        | Remove period-aware unique index
        |--------------------------------------------------------------------------
        */

        $indexes = collect(
            Schema::getIndexes('attendance_sessions')
        );

        if ($indexes->contains(function ($index) {
            return $index['name'] ===
                'attendance_sessions_period_date_unique';
        })) {
            Schema::table('attendance_sessions', function (Blueprint $table) {
                $table->dropUnique(
                    'attendance_sessions_period_date_unique'
                );
            });
        }

        /*
        |--------------------------------------------------------------------------
        | Restore original unique index
        |--------------------------------------------------------------------------
        */

        $indexes = collect(
            Schema::getIndexes('attendance_sessions')
        );

        if (!$indexes->contains(function ($index) {
            return $index['name'] ===
                'attendance_sessions_student_class_id_attendance_date_unique';
        })) {
            Schema::table('attendance_sessions', function (Blueprint $table) {
                $table->unique(
                    [
                        'student_class_id',
                        'attendance_date',
                    ],
                    'attendance_sessions_student_class_id_attendance_date_unique'
                );
            });
        }

        /*
        |--------------------------------------------------------------------------
        | Remove academic-period columns
        |--------------------------------------------------------------------------
        */

        if (
            Schema::hasColumn('attendance_sessions', 'term_id') ||
            Schema::hasColumn('attendance_sessions', 'academic_year_id')
        ) {
            Schema::table('attendance_sessions', function (Blueprint $table) {

                if (Schema::hasColumn('attendance_sessions', 'term_id')) {
                    $table->dropForeign(['term_id']);
                }

                if (Schema::hasColumn('attendance_sessions', 'academic_year_id')) {
                    $table->dropForeign(['academic_year_id']);
                }

                if (Schema::hasColumn('attendance_sessions', 'term_id')) {
                    $table->dropColumn('term_id');
                }

                if (Schema::hasColumn('attendance_sessions', 'academic_year_id')) {
                    $table->dropColumn('academic_year_id');
                }
            });
        }
    }
};