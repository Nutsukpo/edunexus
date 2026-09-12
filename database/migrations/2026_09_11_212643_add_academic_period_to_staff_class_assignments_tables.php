<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        /*
        |--------------------------------------------------------------------------
        | student_class_staff
        |--------------------------------------------------------------------------
        | Form Master assignments
        |--------------------------------------------------------------------------
        */

        if (Schema::hasTable('student_class_staff')) {

            if (!Schema::hasColumn('student_class_staff', 'academic_year_id')) {
                Schema::table('student_class_staff', function (Blueprint $table) {
                    $table->foreignId('academic_year_id')
                        ->nullable()
                        ->after('staff_id')
                        ->constrained('academic_years')
                        ->cascadeOnDelete();
                });
            }

            if (!Schema::hasColumn('student_class_staff', 'term_id')) {
                Schema::table('student_class_staff', function (Blueprint $table) {
                    $table->foreignId('term_id')
                        ->nullable()
                        ->after('academic_year_id')
                        ->constrained('terms')
                        ->cascadeOnDelete();
                });
            }

            Schema::table('student_class_staff', function (Blueprint $table) {
                $table->index(
                    [
                        'student_class_id',
                        'academic_year_id',
                        'term_id',
                    ],
                    'student_class_staff_period_index'
                );

                $table->index(
                    [
                        'staff_id',
                        'academic_year_id',
                        'term_id',
                    ],
                    'student_class_staff_staff_period_index'
                );
            });
        }

        /*
        |--------------------------------------------------------------------------
        | class_subject_staff
        |--------------------------------------------------------------------------
        | Subject Teacher assignments
        |--------------------------------------------------------------------------
        */

        if (Schema::hasTable('class_subject_staff')) {

            if (!Schema::hasColumn('class_subject_staff', 'academic_year_id')) {
                Schema::table('class_subject_staff', function (Blueprint $table) {
                    $table->foreignId('academic_year_id')
                        ->nullable()
                        ->after('staff_id')
                        ->constrained('academic_years')
                        ->cascadeOnDelete();
                });
            }

            if (!Schema::hasColumn('class_subject_staff', 'term_id')) {
                Schema::table('class_subject_staff', function (Blueprint $table) {
                    $table->foreignId('term_id')
                        ->nullable()
                        ->after('academic_year_id')
                        ->constrained('terms')
                        ->cascadeOnDelete();
                });
            }

            Schema::table('class_subject_staff', function (Blueprint $table) {
                $table->index(
                    [
                        'student_class_id',
                        'subject_id',
                        'academic_year_id',
                        'term_id',
                    ],
                    'class_subject_staff_period_index'
                );

                $table->index(
                    [
                        'staff_id',
                        'academic_year_id',
                        'term_id',
                    ],
                    'class_subject_staff_staff_period_index'
                );
            });
        }
    }

    public function down(): void
    {
        /*
        |--------------------------------------------------------------------------
        | class_subject_staff
        |--------------------------------------------------------------------------
        */

        if (Schema::hasTable('class_subject_staff')) {

            if (Schema::hasColumn('class_subject_staff', 'term_id')) {
                Schema::table('class_subject_staff', function (Blueprint $table) {
                    $table->dropForeign(['term_id']);
                    $table->dropIndex(
                        'class_subject_staff_period_index'
                    );
                    $table->dropIndex(
                        'class_subject_staff_staff_period_index'
                    );
                    $table->dropColumn('term_id');
                });
            }

            if (Schema::hasColumn('class_subject_staff', 'academic_year_id')) {
                Schema::table('class_subject_staff', function (Blueprint $table) {
                    $table->dropForeign(['academic_year_id']);
                    $table->dropColumn('academic_year_id');
                });
            }
        }

        /*
        |--------------------------------------------------------------------------
        | student_class_staff
        |--------------------------------------------------------------------------
        */

        if (Schema::hasTable('student_class_staff')) {

            if (Schema::hasColumn('student_class_staff', 'term_id')) {
                Schema::table('student_class_staff', function (Blueprint $table) {
                    $table->dropForeign(['term_id']);
                    $table->dropIndex(
                        'student_class_staff_period_index'
                    );
                    $table->dropIndex(
                        'student_class_staff_staff_period_index'
                    );
                    $table->dropColumn('term_id');
                });
            }

            if (Schema::hasColumn('student_class_staff', 'academic_year_id')) {
                Schema::table('student_class_staff', function (Blueprint $table) {
                    $table->dropForeign(['academic_year_id']);
                    $table->dropColumn('academic_year_id');
                });
            }
        }
    }
};