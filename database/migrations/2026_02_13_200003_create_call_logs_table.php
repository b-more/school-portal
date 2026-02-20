<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('call_logs', function (Blueprint $table) {
            $table->id();
            $table->string('caller_name');
            $table->string('phone', 20);
            $table->enum('call_type', ['incoming', 'outgoing'])->default('incoming');
            $table->string('purpose');
            $table->text('notes')->nullable();
            $table->unsignedInteger('duration_minutes')->nullable();
            $table->boolean('follow_up_required')->default(false);
            $table->date('follow_up_date')->nullable();
            $table->text('follow_up_notes')->nullable();
            $table->enum('status', ['logged', 'follow_up_pending', 'completed'])->default('logged');
            $table->foreignId('logged_by')->constrained('users');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('call_logs');
    }
};
