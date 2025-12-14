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
        Schema::create('teacher_designations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('teacher_id')->constrained()->onDelete('cascade');
            $table->foreignId('staff_designation_id')->constrained()->onDelete('cascade');
            $table->foreignId('school_section_id')->nullable()->constrained()->onDelete('set null');
            $table->date('assigned_date')->nullable();
            $table->date('end_date')->nullable();
            $table->boolean('is_active')->default(true);
            $table->text('notes')->nullable();
            $table->timestamps();

            // Unique constraint: a teacher can only have one designation per section
            $table->unique(['teacher_id', 'staff_designation_id', 'school_section_id'], 'teacher_designation_section_unique');

            $table->index(['teacher_id', 'is_active']);
            $table->index(['staff_designation_id', 'is_active']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('teacher_designations');
    }
};
