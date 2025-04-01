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
        Schema::create('user_credentials', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('username');
            $table->string('password'); // Temporarily stores plain text password
            $table->boolean('is_sent')->default(false);
            $table->dateTime('sent_at')->nullable();
            $table->string('delivery_method')->default('sms');
            $table->boolean('is_retrieved')->default(false);
            $table->dateTime('retrieved_at')->nullable();
            $table->dateTime('expires_at');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_credentials');
    }
};
