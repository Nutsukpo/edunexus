<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('departments', function (Blueprint $table) {

            // Primary Key
            $table->id();

            // Department Details
            $table->string('name');
            $table->string('code')->unique()->nullable();
            $table->text('description')->nullable();

            // Head Of Department
            $table->foreignId('head_of_department')
                  ->nullable()
                  ->constrained('users')
                  ->nullOnDelete();

            // Status
            $table->enum('status', ['active', 'inactive'])
                  ->default('active');

            // Soft Deletes
            $table->softDeletes();

            // Timestamps
            $table->timestamps();

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('departments');
    }
};