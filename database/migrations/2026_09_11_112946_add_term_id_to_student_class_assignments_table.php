<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        /*
         * -------------------------------------------------------------
         * 1. Add term_id to student_class_assignments
         * -------------------------------------------------------------
         *
         * term_id is nullable initially because older historical
         * assignments may not have enough information to determine
         * their original term.
         */
        if (!Schema::hasColumn('student_class_assignments', 'term_id')) {
            Schema::table('student_class_assignments', function (Blueprint $table) {
                $table->foreignId('term_id')
                    ->nullable()
                    ->after('academic_year_id')
                    ->constrained('terms')
                    ->nullOnDelete();

                $table->index(
                    ['academic_year_id', 'term_id'],
                    'student_class_assignments_academic_year_term_index'
                );
            });
        }

        /*
         * -------------------------------------------------------------
         * 2. Backfill term_id from existing Bill Sheets
         * -------------------------------------------------------------
         *
         * Bill Sheets already contain:
         * - student_class_assignment_id
         * - academic_year_id
         * - term_id
         *
         * Therefore they provide a reliable source for assignments
         * that already have Bill Sheets.
         *
         * We only update assignments whose term_id is currently NULL.
         */
        DB::statement("
            UPDATE student_class_assignments AS sca
            INNER JOIN (
                SELECT
                    student_class_assignment_id,
                    MAX(term_id) AS term_id
                FROM bill_sheets
                WHERE student_class_assignment_id IS NOT NULL
                  AND term_id IS NOT NULL
                GROUP BY student_class_assignment_id
            ) AS bs
                ON bs.student_class_assignment_id = sca.id
            SET sca.term_id = bs.term_id
            WHERE sca.term_id IS NULL
        ");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('student_class_assignments', 'term_id')) {
            Schema::table('student_class_assignments', function (Blueprint $table) {
                $table->dropForeign(['term_id']);
                $table->dropIndex(
                    'student_class_assignments_academic_year_term_index'
                );
                $table->dropColumn('term_id');
            });
        }
    }
};