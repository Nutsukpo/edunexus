<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('students', function (Blueprint $table) {

            $table->string('password')->nullable()->after('photo');

            $table->boolean('password_changed')
                  ->default(false)
                  ->after('password');

            $table->rememberToken();

            $table->timestamp('last_login_at')
                  ->nullable()
                  ->after('remember_token');
        });
    }

    public function down(): void
    {
        Schema::table('students', function (Blueprint $table) {

            $table->dropColumn([
                'password',
                'password_changed',
                'remember_token',
                'last_login_at'
            ]);

        });
    }
};