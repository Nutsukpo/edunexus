<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('staff_attendances', function (Blueprint $table) {
            $table->foreignId('academic_year_id')
                ->nullable()
                ->after('staff_id')
                ->constrained('academic_years')
                ->nullOnDelete();

            $table->foreignId('term_id')
                ->nullable()
                ->after('academic_year_id')
                ->constrained('terms')
                ->nullOnDelete();

            $table->index(
                ['academic_year_id', 'term_id'],
                'staff_attendance_academic_period_index'
            );
        });

        /*
         * Backfill existing records only when their date falls inside
         * a configured Term range. Unmatched historical records remain
         * nullable rather than being assigned to a potentially wrong period.
         */
        $terms = DB::table('terms')
            ->whereNotNull('academic_year_id')
            ->whereNotNull('start_date')
            ->whereNotNull('end_date')
            ->select('id', 'academic_year_id', 'start_date', 'end_date')
            ->orderBy('start_date')
            ->get();

        foreach ($terms as $term) {
            DB::table('staff_attendances')
                ->whereNull('academic_year_id')
                ->whereNull('term_id')
                ->whereDate('date', '>=', $term->start_date)
                ->whereDate('date', '<=', $term->end_date)
                ->update([
                    'academic_year_id' => $term->academic_year_id,
                    'term_id' => $term->id,
                ]);
        }
    }

    public function down(): void
    {
        Schema::table('staff_attendances', function (Blueprint $table) {
            $table->dropIndex('staff_attendance_academic_period_index');
            $table->dropConstrainedForeignId('term_id');
            $table->dropConstrainedForeignId('academic_year_id');
        });
    }
};
