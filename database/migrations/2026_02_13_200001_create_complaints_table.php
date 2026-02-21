<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('complaints', function (Blueprint $table) {
            $table->id();
            $table->string('complainant_name');
            $table->string('phone', 20);
            $table->string('email')->nullable();
            $table->enum('complaint_type', ['academic', 'behavioral', 'facility', 'staff', 'other'])->default('other');
            $table->string('subject');
            $table->text('description');
            $table->enum('priority', ['low', 'medium', 'high', 'urgent'])->default('medium');
            $table->enum('status', ['open', 'in_progress', 'resolved', 'closed'])->default('open');
            $table->text('resolution')->nullable();
            $table->foreignId('related_student_id')->nullable()->constrained('students')->nullOnDelete();
            $table->foreignId('logged_by')->constrained('users');
            $table->foreignId('resolved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('resolved_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('complaints');
    }
};
