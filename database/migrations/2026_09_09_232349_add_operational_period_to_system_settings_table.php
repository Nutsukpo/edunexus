<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Add a separate operational period.
     *
     * current_academic_year_id / current_term_id
     * remain the administrator's selected viewing period.
     *
     * operational_academic_year_id / operational_term_id
     * represent the school's actual operating period.
     */
    public function up(): void
    {
        Schema::table('system_settings', function (Blueprint $table) {
            $table->foreignId('operational_academic_year_id')
                ->nullable()
                ->after('current_term_id')
                ->constrained('academic_years')
                ->nullOnDelete();

            $table->foreignId('operational_term_id')
                ->nullable()
                ->after('operational_academic_year_id')
                ->constrained('terms')
                ->nullOnDelete();
        });
    }

    /**
     * Reverse the migration.
     */
    public function down(): void
    {
        Schema::table('system_settings', function (Blueprint $table) {
            $table->dropForeign([
                'operational_academic_year_id',
            ]);

            $table->dropForeign([
                'operational_term_id',
            ]);

            $table->dropColumn([
                'operational_academic_year_id',
                'operational_term_id',
            ]);
        });
    }
};