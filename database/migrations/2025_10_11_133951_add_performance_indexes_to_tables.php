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
            $table->index('enrollment_status');
            $table->index(['grade_id', 'class_section_id']);
        });

        // Homework table indexes
        Schema::table('homework', function (Blueprint $table) {
            $table->index('status');
            $table->index(['grade_id', 'subject_id']);
            $table->index('due_date');
        });

        // Homework submissions table indexes
        Schema::table('homework_submissions', function (Blueprint $table) {
            $table->index(['homework_id', 'student_id']);
            $table->index('status');
        });

        // Payment transactions table indexes
        Schema::table('payment_transactions', function (Blueprint $table) {
            $table->index('type');
            $table->index('transaction_date');
            $table->index(['student_fee_id', 'type']);
        });

        // Student fees table indexes
        Schema::table('student_fees', function (Blueprint $table) {
            $table->index('payment_status');
            $table->index(['student_id', 'academic_year_id']);
        });

        // Results table indexes
        Schema::table('results', function (Blueprint $table) {
            $table->index(['student_id', 'term_id']);
            $table->index('academic_year_id');
        });

        // Teachers table indexes
        Schema::table('teachers', function (Blueprint $table) {
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Students table indexes
        Schema::table('students', function (Blueprint $table) {
            $table->dropIndex(['enrollment_status']);
            $table->dropIndex(['grade_id', 'class_section_id']);
        });

        // Homework table indexes
        Schema::table('homework', function (Blueprint $table) {
            $table->dropIndex(['status']);
            $table->dropIndex(['grade_id', 'subject_id']);
            $table->dropIndex(['due_date']);
        });

        // Homework submissions table indexes
        Schema::table('homework_submissions', function (Blueprint $table) {
            $table->dropIndex(['homework_id', 'student_id']);
            $table->dropIndex(['status']);
        });

        // Payment transactions table indexes
        Schema::table('payment_transactions', function (Blueprint $table) {
            $table->dropIndex(['type']);
            $table->dropIndex(['transaction_date']);
            $table->dropIndex(['student_fee_id', 'type']);
        });

        // Student fees table indexes
        Schema::table('student_fees', function (Blueprint $table) {
            $table->dropIndex(['payment_status']);
            $table->dropIndex(['student_id', 'academic_year_id']);
        });

        // Results table indexes
        Schema::table('results', function (Blueprint $table) {
            $table->dropIndex(['student_id', 'term_id']);
            $table->dropIndex(['academic_year_id']);
        });

        // Teachers table indexes
        Schema::table('teachers', function (Blueprint $table) {
            $table->dropIndex(['status']);
        });
    }
};
