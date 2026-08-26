<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class FixPayslipsStatusColumn extends Migration
{
    public function up()
    {
        Schema::table('payslips', function (Blueprint $table) {
            // If status is ENUM, drop and recreate as VARCHAR
            $table->string('status', 20)->default('generated')->change();
        });
    }

    public function down()
    {
        Schema::table('payslips', function (Blueprint $table) {
            $table->string('status', 10)->default('generated')->change();
        });
    }
}