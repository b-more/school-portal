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
        Schema::create('sms_logs', function (Blueprint $table) {
            $table->id();
            $table->string('recipient')->nullable();
            $table->text('message')->nullable();
            $table->enum('status', ['sent', 'delivered', 'failed', 'pending'])->default('sent')->nullable();
            $table->enum('message_type', [
                'homework_notification',
                'result_notification',
                'fee_reminder',
                'event_notification',
                'general',
                'other'
            ])->default('general')->nullable();
            $table->unsignedBigInteger('reference_id')->nullable()->comment('ID of related record (homework, result, etc.)');
            $table->decimal('cost', 10, 2)->nullable();
            $table->string('provider_reference')->nullable()->comment('Message ID from SMS provider');
            $table->text('error_message')->nullable();
            $table->unsignedBigInteger('sent_by')->nullable();
            $table->timestamps();

            $table->index(['status', 'message_type', 'reference_id']);
            $table->index('recipient');
            $table->index('sent_by');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sms_logs');
    }
};
