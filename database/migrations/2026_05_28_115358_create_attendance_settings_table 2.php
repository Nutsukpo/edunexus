<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('attendance_settings', function (Blueprint $table) {

            $table->id();

            // SCHOOL GPS CENTER
            $table->decimal('latitude', 10, 7);
            $table->decimal('longitude', 10, 7);

            // ALLOWED DISTANCE IN METERS
            $table->integer('radius')->default(100);

            // CLOCK-IN TIME
            $table->time('clock_in_start')->nullable();
            $table->time('clock_in_end')->nullable();

            // CLOCK-OUT TIME
            $table->time('clock_out_start')->nullable();
            $table->time('clock_out_end')->nullable();

            $table->boolean('gps_enabled')->default(true);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('attendance_settings');
    }
};