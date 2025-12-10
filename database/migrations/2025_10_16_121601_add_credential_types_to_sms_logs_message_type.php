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
        // Add new enum values to message_type column
        DB::statement("ALTER TABLE sms_logs MODIFY COLUMN message_type ENUM(
            'homework_notification',
            'result_notification',
            'fee_reminder',
            'event_notification',
            'general',
            'other',
            'student_credentials',
            'staff_credentials'
        ) DEFAULT 'general'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Remove the new enum values
        DB::statement("ALTER TABLE sms_logs MODIFY COLUMN message_type ENUM(
            'homework_notification',
            'result_notification',
            'fee_reminder',
            'event_notification',
            'general',
            'other'
        ) DEFAULT 'general'");
    }
};
