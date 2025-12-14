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
        Schema::create('report_card_comments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained()->onDelete('cascade');
            $table->foreignId('term_id')->constrained()->onDelete('cascade');
            $table->foreignId('academic_year_id')->constrained()->onDelete('cascade');

            // Class Teacher Comments
            $table->text('class_teacher_comment')->nullable();
            $table->foreignId('class_teacher_id')->nullable()->constrained('teachers')->onDelete('set null');
            $table->timestamp('class_teacher_commented_at')->nullable();

            // Head Teacher Comments
            $table->text('head_teacher_comment')->nullable();
            $table->foreignId('head_teacher_id')->nullable()->constrained('teachers')->onDelete('set null');
            $table->timestamp('head_teacher_commented_at')->nullable();

            // Report card generation tracking
            $table->timestamp('last_generated_at')->nullable();
            $table->integer('generation_count')->default(0);

            $table->timestamps();

            // Unique constraint to prevent duplicate comments
            $table->unique(['student_id', 'term_id', 'academic_year_id'], 'unique_student_term_year_comment');

            // Indexes for performance
            $table->index(['term_id', 'academic_year_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('report_card_comments');
    }
};
