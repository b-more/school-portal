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
        // Students table indexes
        Schema::table('students', function (Blueprint $table) {
            $table->index('enrollment_status', 'idx_students_enrollment_status');
            $table->index(['grade_id', 'class_section_id'], 'idx_students_grade_section');
            $table->index('parent_guardian_id', 'idx_students_parent_guardian');
        });

        // Student fees table indexes
        Schema::table('student_fees', function (Blueprint $table) {
            $table->index(['student_id', 'academic_year_id', 'term_id'], 'idx_student_fees_lookup');
            $table->index('payment_status', 'idx_student_fees_payment_status');
            $table->index(['term_id', 'student_id', 'payment_status'], 'idx_student_fees_term_student');
            $table->index('fee_structure_id', 'idx_student_fees_structure');
        });

        // Payment transactions table indexes
        Schema::table('payment_transactions', function (Blueprint $table) {
            $table->index('student_fee_id', 'idx_payment_transactions_fee');
            $table->index('type', 'idx_payment_transactions_type');
            $table->index('transaction_date', 'idx_payment_transactions_date');
            $table->index(['student_fee_id', 'type'], 'idx_payment_transactions_fee_type');
        });

        // Fee structures table indexes
        Schema::table('fee_structures', function (Blueprint $table) {
            $table->index(['grade_id', 'academic_year_id', 'term_id', 'is_active'], 'idx_fee_structures_lookup');
        });

        // Subject teaching table indexes (for teacher assignments)
        Schema::table('subject_teaching', function (Blueprint $table) {
            $table->index('teacher_id', 'idx_subject_teaching_teacher');
            $table->index(['subject_id', 'class_section_id'], 'idx_subject_teaching_subject_class');
            $table->index('academic_year_id', 'idx_subject_teaching_year');
        });

        // Class sections table indexes
        Schema::table('class_sections', function (Blueprint $table) {
            $table->index(['grade_id', 'is_active'], 'idx_class_sections_grade_active');
            $table->index('academic_year_id', 'idx_class_sections_year');
        });

        // SMS logs table indexes
        Schema::table('sms_logs', function (Blueprint $table) {
            $table->index('status', 'idx_sms_logs_status');
            $table->index('message_type', 'idx_sms_logs_type');
            $table->index('created_at', 'idx_sms_logs_created');
            $table->index(['reference_id', 'message_type'], 'idx_sms_logs_reference');
        });

        // Homework table indexes
        Schema::table('homework', function (Blueprint $table) {
            $table->index(['class_section_id', 'subject_id'], 'idx_homework_class_subject');
            $table->index('teacher_id', 'idx_homework_teacher');
            $table->index('due_date', 'idx_homework_due_date');
        });

        // Homework submissions table indexes
        Schema::table('homework_submissions', function (Blueprint $table) {
            $table->index(['homework_id', 'student_id'], 'idx_homework_submissions_lookup');
            $table->index('status', 'idx_homework_submissions_status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Students table
        Schema::table('students', function (Blueprint $table) {
            $table->dropIndex('idx_students_enrollment_status');
            $table->dropIndex('idx_students_grade_section');
            $table->dropIndex('idx_students_parent_guardian');
        });

        // Student fees table
        Schema::table('student_fees', function (Blueprint $table) {
            $table->dropIndex('idx_student_fees_lookup');
            $table->dropIndex('idx_student_fees_payment_status');
            $table->dropIndex('idx_student_fees_term_student');
            $table->dropIndex('idx_student_fees_structure');
        });

        // Payment transactions table
        Schema::table('payment_transactions', function (Blueprint $table) {
            $table->dropIndex('idx_payment_transactions_fee');
            $table->dropIndex('idx_payment_transactions_type');
            $table->dropIndex('idx_payment_transactions_date');
            $table->dropIndex('idx_payment_transactions_fee_type');
        });

        // Fee structures table
        Schema::table('fee_structures', function (Blueprint $table) {
            $table->dropIndex('idx_fee_structures_lookup');
        });

        // Subject teaching table
        Schema::table('subject_teaching', function (Blueprint $table) {
            $table->dropIndex('idx_subject_teaching_teacher');
            $table->dropIndex('idx_subject_teaching_subject_class');
            $table->dropIndex('idx_subject_teaching_year');
        });

        // Class sections table
        Schema::table('class_sections', function (Blueprint $table) {
            $table->dropIndex('idx_class_sections_grade_active');
            $table->dropIndex('idx_class_sections_year');
        });

        // SMS logs table
        Schema::table('sms_logs', function (Blueprint $table) {
            $table->dropIndex('idx_sms_logs_status');
            $table->dropIndex('idx_sms_logs_type');
            $table->dropIndex('idx_sms_logs_created');
            $table->dropIndex('idx_sms_logs_reference');
        });

        // Homework table
        Schema::table('homework', function (Blueprint $table) {
            $table->dropIndex('idx_homework_class_subject');
            $table->dropIndex('idx_homework_teacher');
            $table->dropIndex('idx_homework_due_date');
        });

        // Homework submissions table
        Schema::table('homework_submissions', function (Blueprint $table) {
            $table->dropIndex('idx_homework_submissions_lookup');
            $table->dropIndex('idx_homework_submissions_status');
        });
    }
};
