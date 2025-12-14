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
        Schema::table('school_settings', function (Blueprint $table) {
            // =============================================
            // SCHOOL IDENTITY
            // =============================================
            $table->string('registration_number')->nullable()->after('school_code');
            $table->string('tax_pin')->nullable()->after('registration_number');
            $table->text('school_vision')->nullable()->after('school_motto');
            $table->text('school_mission')->nullable()->after('school_vision');
            $table->string('favicon')->nullable()->after('school_logo');
            $table->string('header_logo')->nullable()->after('favicon');
            $table->string('footer_logo')->nullable()->after('header_logo');
            $table->string('report_card_logo')->nullable()->after('footer_logo');
            $table->string('primary_color', 7)->default('#1e40af')->after('report_card_logo');
            $table->string('secondary_color', 7)->default('#64748b')->after('primary_color');
            $table->string('accent_color', 7)->default('#f59e0b')->after('secondary_color');

            // =============================================
            // ACADEMIC SETTINGS
            // =============================================
            $table->string('academic_year_format')->default('YYYY')->after('accent_color'); // YYYY or YYYY-YYYY
            $table->integer('terms_per_year')->default(3)->after('academic_year_format');
            $table->string('grading_system')->default('percentage')->after('terms_per_year'); // percentage, letter, gpa
            $table->integer('passing_mark')->default(40)->after('grading_system');
            $table->integer('max_mark')->default(100)->after('passing_mark');
            $table->boolean('show_position_in_class')->default(true)->after('max_mark');
            $table->boolean('show_position_in_grade')->default(true)->after('show_position_in_class');
            $table->boolean('show_grade_average')->default(true)->after('show_position_in_grade');
            $table->boolean('enable_continuous_assessment')->default(true)->after('show_grade_average');
            $table->integer('ca_weight_percentage')->default(40)->after('enable_continuous_assessment');
            $table->integer('exam_weight_percentage')->default(60)->after('ca_weight_percentage');

            // =============================================
            // ATTENDANCE SETTINGS
            // =============================================
            $table->time('school_start_time')->default('07:30:00')->after('exam_weight_percentage');
            $table->time('school_end_time')->default('13:00:00')->after('school_start_time');
            $table->integer('late_arrival_minutes')->default(15)->after('school_end_time');
            $table->boolean('notify_parent_on_absence')->default(true)->after('late_arrival_minutes');
            $table->boolean('notify_parent_on_late')->default(false)->after('notify_parent_on_absence');
            $table->integer('absence_notification_threshold')->default(3)->after('notify_parent_on_late');
            $table->json('school_days')->nullable()->after('absence_notification_threshold'); // [1,2,3,4,5] Mon-Fri

            // =============================================
            // FEE SETTINGS
            // =============================================
            $table->boolean('enable_online_payments')->default(false)->after('school_days');
            $table->boolean('enable_partial_payments')->default(true)->after('enable_online_payments');
            $table->decimal('minimum_partial_payment', 10, 2)->default(100)->after('enable_partial_payments');
            $table->boolean('enable_late_fees')->default(true)->after('minimum_partial_payment');
            $table->decimal('late_fee_percentage', 5, 2)->default(5)->after('enable_late_fees');
            $table->integer('grace_period_days')->default(7)->after('late_fee_percentage');
            $table->string('invoice_prefix')->default('INV')->after('grace_period_days');
            $table->string('receipt_prefix')->default('RCP')->after('invoice_prefix');
            $table->text('payment_instructions')->nullable()->after('receipt_prefix');
            $table->json('payment_methods')->nullable()->after('payment_instructions'); // ['cash', 'bank', 'mobile_money']
            $table->json('bank_details')->nullable()->after('payment_methods');
            $table->json('mobile_money_details')->nullable()->after('bank_details');

            // =============================================
            // SMS & COMMUNICATION SETTINGS
            // =============================================
            $table->string('sms_sender_id', 11)->nullable()->after('mobile_money_details');
            $table->boolean('enable_sms_notifications')->default(true)->after('sms_sender_id');
            $table->boolean('enable_email_notifications')->default(true)->after('enable_sms_notifications');
            $table->boolean('enable_whatsapp_notifications')->default(false)->after('enable_email_notifications');
            $table->boolean('sms_on_fee_payment')->default(true)->after('enable_whatsapp_notifications');
            $table->boolean('sms_on_result_release')->default(true)->after('sms_on_fee_payment');
            $table->boolean('sms_on_attendance')->default(false)->after('sms_on_result_release');
            $table->boolean('sms_on_homework')->default(false)->after('sms_on_attendance');
            $table->integer('sms_balance_alert_threshold')->default(100)->after('sms_on_homework');

            // =============================================
            // REPORT CARD SETTINGS
            // =============================================
            $table->string('report_card_format')->default('standard')->after('sms_balance_alert_threshold'); // standard, detailed, minimal
            $table->boolean('show_teacher_comments')->default(true)->after('report_card_format');
            $table->boolean('show_headteacher_comments')->default(true)->after('show_teacher_comments');
            $table->boolean('show_principal_signature')->default(true)->after('show_headteacher_comments');
            $table->boolean('show_class_teacher_signature')->default(true)->after('show_principal_signature');
            $table->boolean('show_parent_signature_line')->default(true)->after('show_class_teacher_signature');
            $table->boolean('show_attendance_summary')->default(true)->after('show_parent_signature_line');
            $table->boolean('show_conduct_grade')->default(true)->after('show_attendance_summary');
            $table->string('principal_name')->nullable()->after('show_conduct_grade');
            $table->string('principal_title')->default('Executive Director')->after('principal_name');
            $table->string('principal_signature')->nullable()->after('principal_title');
            $table->text('report_card_footer_text')->nullable()->after('principal_signature');
            $table->date('next_term_starts')->nullable()->after('report_card_footer_text');
            $table->date('next_term_ends')->nullable()->after('next_term_starts');

            // =============================================
            // SYSTEM SETTINGS
            // =============================================
            $table->string('date_format')->default('d/m/Y')->after('next_term_ends');
            $table->string('time_format')->default('H:i')->after('date_format');
            $table->string('datetime_format')->default('d/m/Y H:i')->after('time_format');
            $table->integer('session_timeout_minutes')->default(120)->after('datetime_format');
            $table->boolean('enable_maintenance_mode')->default(false)->after('session_timeout_minutes');
            $table->text('maintenance_message')->nullable()->after('enable_maintenance_mode');
            $table->boolean('enable_student_portal')->default(true)->after('maintenance_message');
            $table->boolean('enable_parent_portal')->default(true)->after('enable_student_portal');
            $table->boolean('enable_teacher_portal')->default(true)->after('enable_parent_portal');
            $table->boolean('require_password_change_on_first_login')->default(true)->after('enable_teacher_portal');
            $table->integer('password_expiry_days')->default(90)->after('require_password_change_on_first_login');
            $table->integer('max_login_attempts')->default(5)->after('password_expiry_days');
            $table->integer('lockout_duration_minutes')->default(30)->after('max_login_attempts');

            // =============================================
            // BACKUP & DATA SETTINGS
            // =============================================
            $table->boolean('enable_auto_backup')->default(false)->after('lockout_duration_minutes');
            $table->string('backup_frequency')->default('daily')->after('enable_auto_backup'); // daily, weekly, monthly
            $table->time('backup_time')->default('02:00:00')->after('backup_frequency');
            $table->integer('backup_retention_days')->default(30)->after('backup_time');

            // =============================================
            // SECTION-SPECIFIC SETTINGS
            // =============================================
            $table->string('primary_head_name')->nullable()->after('backup_retention_days');
            $table->string('primary_head_title')->default('Head Teacher Primary')->after('primary_head_name');
            $table->string('primary_head_signature')->nullable()->after('primary_head_title');
            $table->string('secondary_head_name')->nullable()->after('primary_head_signature');
            $table->string('secondary_head_title')->default('Head Teacher Secondary')->after('secondary_head_name');
            $table->string('secondary_head_signature')->nullable()->after('secondary_head_title');

            // =============================================
            // ADDITIONAL METADATA
            // =============================================
            $table->json('custom_settings')->nullable()->after('secondary_head_signature');
            $table->timestamp('settings_last_updated_at')->nullable()->after('custom_settings');
            $table->foreignId('settings_updated_by')->nullable()->after('settings_last_updated_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('school_settings', function (Blueprint $table) {
            $table->dropColumn([
                // School Identity
                'registration_number', 'tax_pin', 'school_vision', 'school_mission',
                'favicon', 'header_logo', 'footer_logo', 'report_card_logo',
                'primary_color', 'secondary_color', 'accent_color',

                // Academic Settings
                'academic_year_format', 'terms_per_year', 'grading_system',
                'passing_mark', 'max_mark', 'show_position_in_class', 'show_position_in_grade',
                'show_grade_average', 'enable_continuous_assessment',
                'ca_weight_percentage', 'exam_weight_percentage',

                // Attendance Settings
                'school_start_time', 'school_end_time', 'late_arrival_minutes',
                'notify_parent_on_absence', 'notify_parent_on_late',
                'absence_notification_threshold', 'school_days',

                // Fee Settings
                'enable_online_payments', 'enable_partial_payments', 'minimum_partial_payment',
                'enable_late_fees', 'late_fee_percentage', 'grace_period_days',
                'invoice_prefix', 'receipt_prefix', 'payment_instructions',
                'payment_methods', 'bank_details', 'mobile_money_details',

                // SMS & Communication
                'sms_sender_id', 'enable_sms_notifications', 'enable_email_notifications',
                'enable_whatsapp_notifications', 'sms_on_fee_payment', 'sms_on_result_release',
                'sms_on_attendance', 'sms_on_homework', 'sms_balance_alert_threshold',

                // Report Card Settings
                'report_card_format', 'show_teacher_comments', 'show_headteacher_comments',
                'show_principal_signature', 'show_class_teacher_signature', 'show_parent_signature_line',
                'show_attendance_summary', 'show_conduct_grade', 'principal_name', 'principal_title',
                'principal_signature', 'report_card_footer_text', 'next_term_starts', 'next_term_ends',

                // System Settings
                'date_format', 'time_format', 'datetime_format', 'session_timeout_minutes',
                'enable_maintenance_mode', 'maintenance_message', 'enable_student_portal',
                'enable_parent_portal', 'enable_teacher_portal',
                'require_password_change_on_first_login', 'password_expiry_days',
                'max_login_attempts', 'lockout_duration_minutes',

                // Backup Settings
                'enable_auto_backup', 'backup_frequency', 'backup_time', 'backup_retention_days',

                // Section Settings
                'primary_head_name', 'primary_head_title', 'primary_head_signature',
                'secondary_head_name', 'secondary_head_title', 'secondary_head_signature',

                // Metadata
                'custom_settings', 'settings_last_updated_at', 'settings_updated_by',
            ]);
        });
    }
};
