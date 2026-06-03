<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('student_class_staff', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_class_id')->constrained()->onDelete('cascade');
            $table->foreignId('staff_id')->constrained()->onDelete('cascade');
            $table->timestamps();
            
            // Optional: prevent duplicates
            $table->unique(['student_class_id', 'staff_id']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('student_class_staff');
    }
};