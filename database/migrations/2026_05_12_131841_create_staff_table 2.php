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
        Schema::create('staff', function (Blueprint $table) {
            $table->id();

            $table->string('staff_id')->unique();

            $table->string('first_name');
            $table->string('last_name');
            $table->string('other_name')->nullable();

            $table->enum('gender', ['Male', 'Female']);

            $table->date('date_of_birth')->nullable();

            $table->string('phone')->nullable();
            $table->string('email')->nullable()->unique();

            $table->string('department')->nullable();
            $table->string('position')->nullable();

            $table->date('date_employed')->nullable();

            $table->decimal('salary', 10, 2)->nullable();

            $table->text('address')->nullable();

            $table->string('photo')->nullable();

            $table->string('staff_type')->nullable();

            $table->enum('status', ['Active', 'Inactive'])
                  ->default('Active');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('staff');
    }
};
