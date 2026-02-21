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
        Schema::create('timetable_periods', function (Blueprint $table) {
            $table->id();
            $table->foreignId('academic_year_id')->constrained()->onDelete('cascade');
            $table->string('name'); // e.g., "Period 1", "Assembly", "Tea Break"
            $table->enum('type', ['lesson', 'assembly', 'tea_break', 'lunch_break', 'other'])->default('lesson');
            $table->time('start_time');
            $table->time('end_time');
            $table->integer('order')->default(0); // For sorting periods in sequence
            $table->string('short_name', 20)->nullable(); // e.g., "P1", "ASM", "TEA"
            $table->boolean('is_active')->default(true);
            $table->text('description')->nullable();
            $table->timestamps();

            // Ensure period order is unique per academic year
            $table->unique(['academic_year_id', 'order'], 'unique_period_order');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('timetable_periods');
    }
};
