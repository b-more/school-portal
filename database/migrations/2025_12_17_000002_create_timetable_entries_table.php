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
        Schema::create('timetable_entries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('timetable_period_id')->constrained()->onDelete('cascade');
            $table->foreignId('class_section_id')->constrained()->onDelete('cascade');
            $table->foreignId('subject_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('teacher_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('academic_year_id')->constrained()->onDelete('cascade');
            $table->enum('day_of_week', ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday'])->default('Monday');
            $table->string('room')->nullable(); // Optional room/venue
            $table->text('notes')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            // Prevent duplicate entries for same class/period/day
            $table->unique(
                ['timetable_period_id', 'class_section_id', 'day_of_week', 'academic_year_id'],
                'unique_class_period_day'
            );

            // Index for conflict detection queries (teacher at same period/day)
            $table->index(
                ['teacher_id', 'timetable_period_id', 'day_of_week', 'academic_year_id'],
                'idx_teacher_conflict'
            );
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('timetable_entries');
    }
};
