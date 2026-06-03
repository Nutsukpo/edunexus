<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Drop the existing foreign key constraint
        Schema::table('departments', function (Blueprint $table) {
            $table->dropForeign(['head_of_department']);
        });

        // Re-add the foreign key referencing staff table
        Schema::table('departments', function (Blueprint $table) {
            $table->foreign('head_of_department')
                  ->references('id')
                  ->on('staff')
                  ->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::table('departments', function (Blueprint $table) {
            $table->dropForeign(['head_of_department']);
            
            // Restore original foreign key to users if needed
            $table->foreign('head_of_department')
                  ->references('id')
                  ->on('users')
                  ->onDelete('set null');
        });
    }
};