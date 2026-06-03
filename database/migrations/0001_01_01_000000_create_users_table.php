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
        Schema::create('users', function (Blueprint $table) {

            // Primary Key
            $table->id();

            // Basic Information
            $table->string('name');
            $table->string('email')->unique();
            $table->string('phone')->nullable();

            // User Role
            $table->string('role')->default('staff');

            // Profile Image
            $table->string('profile_photo')->nullable();

            // User Status
            $table->enum('status', ['active', 'inactive'])
                  ->default('active');

            // Email Verification
            $table->timestamp('email_verified_at')->nullable();

            // Password
            $table->string('password');

            // Remember Token
            $table->rememberToken();

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
        Schema::dropIfExists('users');
    }
};