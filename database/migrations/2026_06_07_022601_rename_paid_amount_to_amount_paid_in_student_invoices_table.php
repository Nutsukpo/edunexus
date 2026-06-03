<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('student_invoices', function (Blueprint $table) {
            $table->renameColumn('paid_amount', 'amount_paid');
        });
    }

    public function down(): void
    {
        Schema::table('student_invoices', function (Blueprint $table) {
            $table->renameColumn('amount_paid', 'paid_amount');
        });
    }
};