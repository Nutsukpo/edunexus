<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('system_settings', function (Blueprint $table) {
            $table->id();

            /*
            |--------------------------------------------------------------------------
            | CURRENT ACADEMIC PERIOD
            |--------------------------------------------------------------------------
            */

            $table->foreignId('current_academic_year_id')
                ->nullable()
                ->constrained('academic_years')
                ->nullOnDelete();

            $table->foreignId('current_term_id')
                ->nullable()
                ->constrained('terms')
                ->nullOnDelete();

            /*
            |--------------------------------------------------------------------------
            | ACADEMIC DEFAULTS
            |--------------------------------------------------------------------------
            */

            $table->boolean('auto_assign_current_period')
                ->default(true);

            $table->boolean('use_current_period_defaults')
                ->default(true);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('system_settings');
    }
};