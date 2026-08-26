<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Grievance Categories Table
        Schema::create('grievance_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->enum('priority', ['low', 'medium', 'high', 'urgent'])->default('medium');
            $table->boolean('is_active')->default(true);
            $table->unsignedBigInteger('created_by')->nullable(); // staff_id
            $table->timestamps();
            
            $table->foreign('created_by')->references('id')->on('staff')->onDelete('set null');
        });

        // Grievances Table
        Schema::create('grievances', function (Blueprint $table) {
            $table->id();
            $table->string('grievance_code')->unique();
            $table->string('title');
            $table->text('description');
            
            // Relationships
            $table->unsignedBigInteger('staff_id'); // The staff submitting the grievance
            $table->unsignedBigInteger('category_id')->nullable();
            $table->unsignedBigInteger('assigned_to')->nullable(); // Staff handling the grievance
            $table->unsignedBigInteger('department_id')->nullable(); // Department handling
            
            // Grievance Details
            $table->enum('priority', ['low', 'medium', 'high', 'urgent'])->default('medium');
            $table->enum('status', [
                'draft',
                'submitted',
                'under_review',
                'investigation',
                'resolution_proposed',
                'resolved',
                'closed',
                'rejected',
                'appealed'
            ])->default('draft');
            
            // Attachments
            $table->string('attachment')->nullable();
            $table->json('attachments')->nullable();
            
            // Dates
            $table->date('submission_date')->nullable();
            $table->date('review_date')->nullable();
            $table->date('resolution_date')->nullable();
            $table->date('closure_date')->nullable();
            $table->date('appeal_deadline')->nullable();
            
            // Additional Info
            $table->json('additional_details')->nullable();
            $table->boolean('is_confidential')->default(true);
            $table->boolean('is_anonymous')->default(false);
            $table->text('remarks')->nullable();
            
            // Timestamps and Soft Delete
            $table->timestamps();
            $table->softDeletes();
            
            // Foreign Keys
            $table->foreign('staff_id')->references('id')->on('staff')->onDelete('cascade');
            $table->foreign('category_id')->references('id')->on('grievance_categories')->onDelete('set null');
            $table->foreign('assigned_to')->references('id')->on('staff')->onDelete('set null');
            $table->foreign('department_id')->references('id')->on('departments')->onDelete('set null');
            
            // Indexes
            $table->index('grievance_code');
            $table->index('status');
            $table->index('priority');
            $table->index('staff_id');
            $table->index(['status', 'priority']);
            $table->index('created_at');
        });

        // Grievance Comments/Conversations Table
        Schema::create('grievance_comments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('grievance_id');
            $table->unsignedBigInteger('staff_id'); // Comment author
            $table->text('comment');
            $table->boolean('is_internal')->default(false); // Internal notes vs external comments
            $table->string('attachment')->nullable();
            $table->json('attachments')->nullable();
            $table->timestamps();
            $table->softDeletes();
            
            $table->foreign('grievance_id')->references('id')->on('grievances')->onDelete('cascade');
            $table->foreign('staff_id')->references('id')->on('staff')->onDelete('cascade');
        });

        // Grievance History/Audit Trail Table
        Schema::create('grievance_histories', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('grievance_id');
            $table->enum('action', [
                'created',
                'submitted',
                'reviewed',
                'assigned',
                'investigated',
                'resolved',
                'closed',
                'rejected',
                'appealed',
                'commented',
                'updated'
            ]);
            $table->text('description');
            $table->json('old_values')->nullable();
            $table->json('new_values')->nullable();
            $table->unsignedBigInteger('performed_by')->nullable(); // staff_id
            $table->timestamps();
            
            $table->foreign('grievance_id')->references('id')->on('grievances')->onDelete('cascade');
            $table->foreign('performed_by')->references('id')->on('staff')->onDelete('set null');
        });

        // Grievance Escalations Table
        Schema::create('grievance_escalations', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('grievance_id');
            $table->unsignedBigInteger('from_staff_id')->nullable();
            $table->unsignedBigInteger('to_staff_id');
            $table->text('reason');
            $table->enum('level', ['level_1', 'level_2', 'level_3', 'level_4']);
            $table->date('escalation_date');
            $table->date('response_deadline')->nullable();
            $table->enum('status', ['pending', 'acknowledged', 'responded', 'resolved'])->default('pending');
            $table->timestamps();
            
            $table->foreign('grievance_id')->references('id')->on('grievances')->onDelete('cascade');
            $table->foreign('from_staff_id')->references('id')->on('staff')->onDelete('set null');
            $table->foreign('to_staff_id')->references('id')->on('staff')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('grievance_escalations');
        Schema::dropIfExists('grievance_histories');
        Schema::dropIfExists('grievance_comments');
        Schema::dropIfExists('grievances');
        Schema::dropIfExists('grievance_categories');
    }
};