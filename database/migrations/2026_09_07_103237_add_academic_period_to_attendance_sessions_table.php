<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        /*
        |--------------------------------------------------------------------------
        | Attendance Sessions Academic Period
        |--------------------------------------------------------------------------
        |
        | This migration is intentionally idempotent because the database
        | already contains the required academic_year_id, term_id,
        | foreign keys and period-aware unique index.
        |
        */

        if (!Schema::hasColumn('attendance_sessions', 'academic_year_id')) {
            throw new RuntimeException(
                'attendance_sessions.academic_year_id is missing.'
            );
        }

        if (!Schema::hasColumn('attendance_sessions', 'term_id')) {
            throw new RuntimeException(
                'attendance_sessions.term_id is missing.'
            );
        }

        $indexes = collect(
            Schema::getIndexes('attendance_sessions')
        );

        if (!$indexes->contains(function ($index) {
            return $index['name'] === 'attendance_sessions_period_date_unique';
        })) {
            throw new RuntimeException(
                'attendance_sessions_period_date_unique is missing.'
            );
        }

        $foreignKeys = collect(
            Schema::getForeignKeys('attendance_sessions')
        );

        if (!$foreignKeys->contains(function ($foreignKey) {
            return $foreignKey['name'] === 'attendance_sessions_academic_year_id_foreign';
        })) {
            throw new RuntimeException(
                'Academic year foreign key is missing.'
            );
        }

        if (!$foreignKeys->contains(function ($foreignKey) {
            return $foreignKey['name'] === 'attendance_sessions_term_id_foreign';
        })) {
            throw new RuntimeException(
                'Term foreign key is missing.'
            );
        }

        if (!$foreignKeys->contains(function ($foreignKey) {
            return $foreignKey['name'] === 'attendance_sessions_student_class_id_foreign';
        })) {
            throw new RuntimeException(
                'Student class foreign key is missing.'
            );
        }
    }

    public function down(): void
    {
        /*
        |--------------------------------------------------------------------------
        | Intentionally left empty
        |--------------------------------------------------------------------------
        |
        | The academic-period structure was already established before this
        | migration was recorded. We must not remove existing production/local
        | attendance data or structural constraints during rollback.
        |
        */
    }
};