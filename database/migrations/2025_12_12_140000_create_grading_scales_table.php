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
        Schema::create('grading_scales', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->enum('grade_level', ['primary', 'secondary', 'all'])->default('all');
            $table->text('description')->nullable();
            $table->boolean('is_default')->default(false);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['grade_level', 'is_active']);
        });

        Schema::create('grading_scale_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('grading_scale_id')->constrained()->onDelete('cascade');
            $table->string('grade', 10);
            $table->decimal('min_marks', 5, 2);
            $table->decimal('max_marks', 5, 2);
            $table->decimal('grade_points', 3, 1)->default(0);
            $table->string('remark')->nullable();
            $table->integer('sort_order')->default(0);
            $table->timestamps();

            $table->index(['grading_scale_id', 'min_marks', 'max_marks']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('grading_scale_items');
        Schema::dropIfExists('grading_scales');
    }
};
