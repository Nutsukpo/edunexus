<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('student_progressions', function (Blueprint $table) {
            $table->foreignId('from_class_id')->nullable()->change();
        });
    }

    public function down()
    {
        Schema::table('student_progressions', function (Blueprint $table) {
            $table->foreignId('from_class_id')->nullable(false)->change();
        });
    }
};