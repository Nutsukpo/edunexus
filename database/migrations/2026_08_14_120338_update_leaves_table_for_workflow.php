<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('leaves', function (Blueprint $table) {

            /*
            |--------------------------------------------------------------------------
            | Workflow status
            |--------------------------------------------------------------------------
            */

            $table->enum('status', [
                'draft',
                'pending',
                'approved',
                'rejected',
                'cancelled',
            ])
            ->default('draft')
            ->change();


            /*
            |--------------------------------------------------------------------------
            | Submission
            |--------------------------------------------------------------------------
            */

            $table->timestamp('submitted_at')
                ->nullable()
                ->after('zonal_coordinator_date');


            /*
            |--------------------------------------------------------------------------
            | Approval
            |--------------------------------------------------------------------------
            */

            $table->timestamp('approved_at')
                ->nullable()
                ->after('submitted_at');

            $table->unsignedBigInteger('approved_by')
                ->nullable()
                ->after('approved_at');


            /*
            |--------------------------------------------------------------------------
            | Rejection
            |--------------------------------------------------------------------------
            */

            $table->timestamp('rejected_at')
                ->nullable()
                ->after('approved_by');

            $table->unsignedBigInteger('rejected_by')
                ->nullable()
                ->after('rejected_at');


            /*
            |--------------------------------------------------------------------------
            | Cancellation
            |--------------------------------------------------------------------------
            */

            $table->timestamp('cancelled_at')
                ->nullable()
                ->after('rejected_by');

            $table->unsignedBigInteger('cancelled_by')
                ->nullable()
                ->after('cancelled_at');


            /*
            |--------------------------------------------------------------------------
            | Audit
            |--------------------------------------------------------------------------
            */

            $table->unsignedBigInteger('created_by')
                ->nullable()
                ->after('cancelled_by');

            $table->unsignedBigInteger('updated_by')
                ->nullable()
                ->after('created_by');
        });
    }

    public function down(): void
    {
        Schema::table('leaves', function (Blueprint $table) {

            $table->enum('status', [
                'pending',
                'approved',
                'rejected',
            ])
            ->default('pending')
            ->change();

            $table->dropColumn([
                'submitted_at',
                'approved_at',
                'approved_by',
                'rejected_at',
                'rejected_by',
                'cancelled_at',
                'cancelled_by',
                'created_by',
                'updated_by',
            ]);
        });
    }
};