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
        // Add indexes on frequently queried foreign keys
        Schema::table('students', function (Blueprint $table) {
            $table->index('grade_id', 'idx_students_grade_id');
            $table->index('class_section_id', 'idx_students_class_section_id');
            $table->index('parent_guardian_id', 'idx_students_parent_guardian_id');
            $table->index('enrollment_status', 'idx_students_enrollment_status');
        });

        Schema::table('student_fees', function (Blueprint $table) {
            $table->index('student_id', 'idx_student_fees_student_id');
            $table->index('fee_structure_id', 'idx_student_fees_fee_structure_id');
            $table->index('payment_status', 'idx_student_fees_payment_status');
        });

        Schema::table('homework', function (Blueprint $table) {
            $table->index('grade_id', 'idx_homework_grade_id');
            $table->index('subject_id', 'idx_homework_subject_id');
            $table->index('status', 'idx_homework_status');
            $table->index('due_date', 'idx_homework_due_date');
        });

        Schema::table('homework_submissions', function (Blueprint $table) {
            $table->index('homework_id', 'idx_homework_submissions_homework_id');
            $table->index('student_id', 'idx_homework_submissions_student_id');
            $table->index('status', 'idx_homework_submissions_status');
        });

        Schema::table('sms_logs', function (Blueprint $table) {
            $table->index('status', 'idx_sms_logs_status');
            $table->index('message_type', 'idx_sms_logs_message_type');
            $table->index('created_at', 'idx_sms_logs_created_at');
        });

        Schema::table('employees', function (Blueprint $table) {
            $table->index('role_id', 'idx_employees_role_id');
        });

        Schema::table('results', function (Blueprint $table) {
            $table->index('student_id', 'idx_results_student_id');
            $table->index('subject_id', 'idx_results_subject_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('students', function (Blueprint $table) {
            $table->dropIndex('idx_students_grade_id');
            $table->dropIndex('idx_students_class_section_id');
            $table->dropIndex('idx_students_parent_guardian_id');
            $table->dropIndex('idx_students_enrollment_status');
        });

        Schema::table('student_fees', function (Blueprint $table) {
            $table->dropIndex('idx_student_fees_student_id');
            $table->dropIndex('idx_student_fees_fee_structure_id');
            $table->dropIndex('idx_student_fees_payment_status');
        });

        Schema::table('homework', function (Blueprint $table) {
            $table->dropIndex('idx_homework_grade_id');
            $table->dropIndex('idx_homework_subject_id');
            $table->dropIndex('idx_homework_status');
            $table->dropIndex('idx_homework_due_date');
        });

        Schema::table('homework_submissions', function (Blueprint $table) {
            $table->dropIndex('idx_homework_submissions_homework_id');
            $table->dropIndex('idx_homework_submissions_student_id');
            $table->dropIndex('idx_homework_submissions_status');
        });

        Schema::table('sms_logs', function (Blueprint $table) {
            $table->dropIndex('idx_sms_logs_status');
            $table->dropIndex('idx_sms_logs_message_type');
            $table->dropIndex('idx_sms_logs_created_at');
        });

        Schema::table('employees', function (Blueprint $table) {
            $table->dropIndex('idx_employees_role_id');
        });

        Schema::table('results', function (Blueprint $table) {
            $table->dropIndex('idx_results_student_id');
            $table->dropIndex('idx_results_subject_id');
        });
    }
};
