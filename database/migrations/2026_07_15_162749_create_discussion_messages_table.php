<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('discussion_messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('group_id')->constrained('discussion_groups')->onDelete('cascade');
            $table->foreignId('sender_id')->constrained('staff')->onDelete('cascade');
            $table->foreignId('parent_id')->nullable()->constrained('discussion_messages')->onDelete('cascade');
            $table->text('message');
            $table->enum('type', ['text', 'image', 'file', 'audio', 'video', 'location'])->default('text');
            $table->json('metadata')->nullable();
            $table->boolean('is_edited')->default(false);
            $table->timestamp('edited_at')->nullable();
            $table->boolean('is_deleted')->default(false);
            $table->timestamp('deleted_at')->nullable();
            $table->json('read_by')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('discussion_messages');
    }
};