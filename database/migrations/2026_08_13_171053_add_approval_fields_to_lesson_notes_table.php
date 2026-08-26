<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddApprovalFieldsToLessonNotesTable extends Migration
{
    public function up()
    {
        Schema::table('lesson_notes', function (Blueprint $table) {
            $table->timestamp('approved_at')->nullable();
            $table->unsignedBigInteger('approved_by')->nullable();
            $table->timestamp('rejected_at')->nullable();
            $table->unsignedBigInteger('rejected_by')->nullable();
            $table->timestamp('feedback_requested_at')->nullable();
            $table->unsignedBigInteger('feedback_requested_by')->nullable();
            
            // Add foreign keys if needed
            $table->foreign('approved_by')->references('id')->on('staff')->onDelete('set null');
            $table->foreign('rejected_by')->references('id')->on('staff')->onDelete('set null');
            $table->foreign('feedback_requested_by')->references('id')->on('staff')->onDelete('set null');
        });
    }

    public function down()
    {
        Schema::table('lesson_notes', function (Blueprint $table) {
            $table->dropForeign(['approved_by']);
            $table->dropForeign(['rejected_by']);
            $table->dropForeign(['feedback_requested_by']);
            
            $table->dropColumn([
                'approved_at',
                'approved_by',
                'rejected_at',
                'rejected_by',
                'feedback_requested_at',
                'feedback_requested_by'
            ]);
        });
    }
}