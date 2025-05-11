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
         Schema::create('teachers', function (Blueprint $table) {
            $table->id();
            $table->UnsignedBigInteger('user_id')->nullable();
            $table->string('name')->nullable();
            $table->boolean('is_grade_teacher')->default(false);
            $table->UnsignedBigInteger('role_id')->default(2); // Teacher role
            $table->string('employee_id')->unique()->nullable();
            $table->string('qualification')->nullable();
            $table->string('specialization')->nullable();
            $table->date('join_date')->nullable();
            $table->string('phone')->nullable();
            $table->string('email')->unique()->nullable();
            $table->text('address')->nullable();
            $table->boolean('is_active')->default(true);
            $table->boolean('is_class_teacher')->default(false);
            $table->UnsignedBigInteger('class_section_id')->nullable();
            $table->string('profile_photo')->nullable();
            $table->text('biography')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('teachers');
    }
};
