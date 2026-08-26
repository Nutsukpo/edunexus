<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('fee_payments', function (Blueprint $table) {
            if (!Schema::hasColumn('fee_payments', 'fee_item_id')) {
                $table->foreignId('fee_item_id')
                    ->nullable()
                    ->after('bill_sheet_item_id')
                    ->constrained('student_fee_items')
                    ->onDelete('set null');
            }
        });
    }

    public function down()
    {
        Schema::table('fee_payments', function (Blueprint $table) {
            $table->dropForeign(['fee_item_id']);
            $table->dropColumn('fee_item_id');
        });
    }
};