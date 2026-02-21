<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('inquiries', function (Blueprint $table) {
            $table->id();
            $table->string('inquirer_name');
            $table->string('phone', 20);
            $table->string('email')->nullable();
            $table->enum('inquiry_type', ['admission', 'fees', 'academic', 'general', 'other'])->default('general');
            $table->string('subject');
            $table->text('message');
            $table->text('response')->nullable();
            $table->enum('status', ['new', 'responded', 'closed'])->default('new');
            $table->foreignId('responded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('responded_at')->nullable();
            $table->foreignId('logged_by')->constrained('users');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inquiries');
    }
};
