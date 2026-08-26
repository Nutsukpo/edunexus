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
            ALTER TABLE payroll_periods
            MODIFY status ENUM(
                'draft',
                'processing',
                'pending_approval',
                'approved',
                'rejected',
                'paid',
                'cancelled'
            ) NOT NULL DEFAULT 'draft'
        ");
    }

    public function down(): void
    {
        DB::statement("
            ALTER TABLE payroll_periods
            MODIFY status ENUM(
                'draft',
                'processing',
                'approved',
                'paid',
                'cancelled'
            ) NOT NULL DEFAULT 'draft'
        ");
    }
};