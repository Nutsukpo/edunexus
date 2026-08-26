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
        Schema::create('leaves', function (Blueprint $table) {
            $table->id();

            // Applicant Details
            $table->string('full_name');
            $table->string('designation')->nullable();
            $table->string('contact_number')->nullable();

            // Leave Information
            $table->string('leave_type')->nullable(); // Annual, Sick, etc.
            $table->text('reason')->nullable();

            // Leave Details
            $table->date('date_last_leave')->nullable();
            $table->integer('days_entitled')->nullable();
            $table->integer('days_applied_for')->nullable();
            $table->integer('days_granted')->nullable();
            $table->integer('days_already_utilized')->nullable();
            $table->date('date_commencement');
            $table->date('date_resumption');
            $table->date('date_of_application')->nullable();

            // Signature (Applicant)
            $table->longText('signature')->nullable();

            // Official Use Only
            $table->text('recommendation')->nullable();
            $table->string('administrator_name')->nullable();
            $table->longText('administrator_signature')->nullable();
            $table->date('administrator_date')->nullable();
            $table->string('zonal_coordinator_name')->nullable();
            $table->longText('zonal_coordinator_signature')->nullable();
            $table->date('zonal_coordinator_date')->nullable();

            // Status
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('leaves');
    }
};

