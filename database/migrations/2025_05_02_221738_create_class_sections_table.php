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
        Schema::create('class_sections', function (Blueprint $table) {
            $table->id();
            $table->UnsignedBigInteger('grade_id');
            $table->UnsignedBigInteger('academic_year_id')->nullable();
            $table->string('name')->nullable();
            $table->string('code')->nullable();
            $table->integer('capacity')->default(40);
            $table->UnsignedBigInteger('class_teacher_id')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('class_sections');
    }
};
