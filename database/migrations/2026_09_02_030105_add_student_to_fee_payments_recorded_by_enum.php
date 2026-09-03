<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("
            ALTER TABLE fee_payments
            MODIFY recorded_by
            ENUM('accountant', 'admin', 'cashier', 'student')
            NOT NULL
        ");
    }

    public function down(): void
    {
        DB::statement("
            ALTER TABLE fee_payments
            MODIFY recorded_by
            ENUM('accountant', 'admin', 'cashier')
            NOT NULL
        ");
    }
};