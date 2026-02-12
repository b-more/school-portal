<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('teaching_documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('teacher_id')->constrained()->cascadeOnDelete();
            $table->foreignId('subject_id')->constrained()->cascadeOnDelete();
            $table->foreignId('class_section_id')->constrained()->cascadeOnDelete();
            $table->foreignId('academic_year_id')->constrained()->cascadeOnDelete();
            $table->foreignId('term_id')->constrained()->cascadeOnDelete();
            $table->enum('document_type', ['scheme_of_work', 'lesson_plan']);
            $table->string('title');
            $table->string('file_path');
            $table->string('original_filename')->nullable();
            $table->text('description')->nullable();
            $table->timestamps();

            $table->index(['teacher_id', 'academic_year_id', 'term_id'], 'tdocs_teacher_year_term_idx');
            $table->index(['subject_id', 'class_section_id', 'document_type'], 'tdocs_subject_class_type_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('teaching_documents');
    }
};
