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
        Schema::create('homework', function (Blueprint $table) {
            $table->id();
            $table->string('title')->nullable();
            $table->text('description')->nullable();
            $table->json('file_attachment')->nullable();
            $table->string('homework_file')->nullable(); // Removed 'after'
            $table->unsignedBigInteger('assigned_by')->nullable();
            $table->unsignedBigInteger('grade_id')->nullable();
            $table->unsignedBigInteger('subject_id')->nullable();
            $table->date('due_date')->nullable();
            $table->dateTime('submission_start')->nullable(); // Removed 'after'
            $table->dateTime('submission_end')->nullable(); // Removed 'after'
            $table->boolean('allow_late_submission')->default(false); // Removed 'after'
            $table->dateTime('late_submission_deadline')->nullable(); // Removed 'after'
            $table->integer('max_score')->default(100); // Removed 'after'
            $table->text('submission_instructions')->nullable(); // Removed 'after'
            $table->enum('status', ['active', 'completed'])->default('active')->nullable();
            $table->boolean('notify_parents')->default(true)->nullable();
            $table->string('sms_message')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('homework');
    }
};
