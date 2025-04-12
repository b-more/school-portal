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
        if (!Schema::hasTable('class_subject_teacher')) {
            Schema::create('class_subject_teacher', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('class_id');
                $table->unsignedBigInteger('subject_id');
                $table->unsignedBigInteger('employee_id'); // Teacher
                $table->timestamps();

                // $table->foreign('class_id')->references('id')->on('classes')->onDelete('cascade');
                // $table->foreign('subject_id')->references('id')->on('subjects')->onDelete('cascade');
                // $table->foreign('employee_id')->references('id')->on('employees')->onDelete('cascade');

                // Ensure a teacher only teaches a subject to a class once
                $table->unique(['class_id', 'subject_id', 'employee_id']);
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('class_subject_teacher');
    }
};
