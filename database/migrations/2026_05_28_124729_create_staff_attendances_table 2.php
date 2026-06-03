<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('staff_attendances', function (Blueprint $table) {

            $table->id();

            // CHANGE: user_id → staff_id
            $table->foreignId('staff_id')
                ->constrained('staff')
                ->onDelete('cascade');

            $table->date('date');

            $table->time('clock_in_time')->nullable();
            $table->time('clock_out_time')->nullable();

            $table->decimal('clock_in_latitude', 10, 7)->nullable();
            $table->decimal('clock_in_longitude', 10, 7)->nullable();

            $table->decimal('clock_out_latitude', 10, 7)->nullable();
            $table->decimal('clock_out_longitude', 10, 7)->nullable();

            $table->enum('status', ['present', 'late', 'absent'])
                ->default('present');

            $table->timestamps();

            $table->unique(['staff_id', 'date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('staff_attendances');
    }
};
