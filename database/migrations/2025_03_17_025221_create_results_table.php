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
        Schema::create('results', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('student_id')->nullable();
            $table->unsignedBigInteger('subject_id')->nullable();
            $table->unsignedBigInteger('homework_id')->nullable();
            $table->enum('exam_type', ['mid-term', 'final', 'quiz', 'assignment'])->nullable();
            $table->decimal('marks', 5, 2)->nullable();
            $table->string('grade')->nullable();
            $table->string('term')->nullable();
            $table->year('year')->nullable();
            $table->text('comment')->nullable();
            $table->unsignedBigInteger('recorded_by')->nullable();
            $table->boolean('notify_parent')->default(true)->nullable();
            $table->string('sms_message')->nullable();
            $table->timestamps();

            $table->index(['student_id', 'subject_id','homework_id', 'term', 'year']);
            $table->index('exam_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('results');
    }
};
