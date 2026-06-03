<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('students', function (Blueprint $table) {

            $table->id();

            /*
            |--------------------------------------------------------------------------
            | STUDENT ID
            |--------------------------------------------------------------------------
            */
            $table->string('student_id')->unique();

            /*
            |--------------------------------------------------------------------------
            | PERSONAL DETAILS
            |--------------------------------------------------------------------------
            */
            $table->string('first_name');
            $table->string('middle_name')->nullable();
            $table->string('last_name');

            $table->enum('gender', ['Male', 'Female']);
            $table->date('date_of_birth')->nullable();

            $table->string('nationality')->nullable();
            $table->string('religion')->nullable();

            /*
            |--------------------------------------------------------------------------
            | ADDRESS (PRIMARY RESIDENCE)
            |--------------------------------------------------------------------------
            */
            $table->text('address')->nullable();

            /*
            |--------------------------------------------------------------------------
            | DISABILITY
            |--------------------------------------------------------------------------
            */
            $table->boolean('has_disability')->default(false);
            $table->string('disability_type')->nullable();

            /*
            |--------------------------------------------------------------------------
            | FATHER DETAILS
            |--------------------------------------------------------------------------
            */
            $table->string('father_name')->nullable();
            $table->string('father_phone')->nullable();
            $table->string('father_email')->nullable();
            $table->string('father_occupation')->nullable();

            /*
            |--------------------------------------------------------------------------
            | MOTHER DETAILS
            |--------------------------------------------------------------------------
            */
            $table->string('mother_name')->nullable();
            $table->string('mother_phone')->nullable();
            $table->string('mother_email')->nullable();
            $table->string('mother_occupation')->nullable();

            /*
            |--------------------------------------------------------------------------
            | GUARDIAN DETAILS
            |--------------------------------------------------------------------------
            */
            $table->string('guardian_name')->nullable();
            $table->string('guardian_phone')->nullable();
            $table->string('guardian_email')->nullable();

            /*
            |--------------------------------------------------------------------------
            | SCHOOL DETAILS
            |--------------------------------------------------------------------------
            */
            $table->date('admission_date')->nullable();
            $table->string('photo')->nullable();
            $table->boolean('is_active')->default(true);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('students');
    }
};