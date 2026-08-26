<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('discussion_participants', function (Blueprint $table) {
            $table->id();
            $table->foreignId('group_id')->constrained('discussion_groups')->onDelete('cascade');
            $table->foreignId('staff_id')->constrained('staff')->onDelete('cascade');
            $table->enum('role', ['admin', 'moderator', 'member'])->default('member');
            $table->timestamp('joined_at')->useCurrent();
            $table->timestamp('last_read_at')->nullable();
            $table->boolean('is_active')->default(true);
            $table->boolean('is_muted')->default(false);
            $table->timestamps();
            
            $table->unique(['group_id', 'staff_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('discussion_participants');
    }
};