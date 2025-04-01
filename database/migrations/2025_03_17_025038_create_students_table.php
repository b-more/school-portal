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
        Schema::create('students', function (Blueprint $table) {
            $table->id();
            $table->string('name')->nullable();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->date('date_of_birth')->nullable();
            $table->enum('gender', ['male', 'female', 'other'])->nullable();
            $table->string('address')->nullable();
            $table->string('student_id_number')->unique()->nullable();
            $table->foreignId('parent_guardian_id')->nullable();
            $table->string('grade')->nullable();
            $table->date('admission_date')->nullable();
            $table->enum('enrollment_status', ['active', 'inactive', 'graduated', 'transferred'])->default('active')->nullable();
            $table->string('previous_school')->nullable();
            $table->string('profile_photo')->nullable();
            $table->text('medical_information')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('students');
    }
};
