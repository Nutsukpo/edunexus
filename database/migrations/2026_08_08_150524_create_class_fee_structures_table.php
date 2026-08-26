<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('class_fee_structures', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_class_id')->constrained()->onDelete('cascade');
            $table->foreignId('academic_year_id')->constrained()->onDelete('cascade');
            $table->string('fee_type');
            $table->string('fee_name');
            $table->decimal('amount', 15, 2);
            $table->text('description')->nullable();
            $table->boolean('is_required')->default(true);
            $table->boolean('is_active')->default(true);
            $table->date('due_date')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');
            $table->json('metadata')->nullable();
            $table->timestamps();
            $table->softDeletes();

            // Unique constraint for class, academic year, and fee type
            $table->unique(['student_class_id', 'academic_year_id', 'fee_type'], 'unique_class_year_fee');
        });
    }

    public function down()
    {
        Schema::dropIfExists('class_fee_structures');
    }
};