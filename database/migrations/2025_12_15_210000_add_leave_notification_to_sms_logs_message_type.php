<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Add leave_notification to the message_type enum
        DB::statement("ALTER TABLE sms_logs MODIFY COLUMN message_type ENUM('homework_notification', 'result_notification', 'fee_reminder', 'event_notification', 'general', 'other', 'student_credentials', 'staff_credentials', 'broadcast', 'leave_notification') DEFAULT 'general'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Remove leave_notification from the enum (revert to original)
        DB::statement("ALTER TABLE sms_logs MODIFY COLUMN message_type ENUM('homework_notification', 'result_notification', 'fee_reminder', 'event_notification', 'general', 'other', 'student_credentials', 'staff_credentials', 'broadcast') DEFAULT 'general'");
    }
};
