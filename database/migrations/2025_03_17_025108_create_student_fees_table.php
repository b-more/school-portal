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
        Schema::create('student_fees', function (Blueprint $table) {
            $table->id();

            // Foreign key relationships
            $table->unsignedBigInteger('student_id');
            $table->unsignedBigInteger('fee_structure_id');
            $table->unsignedBigInteger('academic_year_id')->nullable();
            $table->unsignedBigInteger('term_id')->nullable();
            $table->unsignedBigInteger('grade_id')->nullable();

            // Payment information
            $table->enum('payment_status', ['unpaid', 'partial', 'paid'])->default('unpaid');
            $table->decimal('amount_paid', 10, 2)->default(0.00);
            $table->decimal('balance', 10, 2)->default(0.00);
            $table->date('payment_date')->nullable();
            $table->string('receipt_number')->nullable();
            $table->enum('payment_method', ['cash', 'bank_transfer', 'mobile_money', 'cheque', 'other'])->nullable();

            // Additional fields
            $table->boolean('send_sms_notification')->default(false);
            $table->text('notes')->nullable();

            // Timestamps
            $table->timestamps();

            // Foreign key constraints
            $table->foreign('student_id')->references('id')->on('students')->onDelete('cascade');
            $table->foreign('fee_structure_id')->references('id')->on('fee_structures')->onDelete('cascade');
            $table->foreign('academic_year_id')->references('id')->on('academic_years')->onDelete('set null');
            $table->foreign('term_id')->references('id')->on('terms')->onDelete('set null');
            $table->foreign('grade_id')->references('id')->on('grades')->onDelete('set null');

            // Indexes for better performance
            $table->index(['student_id', 'academic_year_id']);
            $table->index(['payment_status']);
            $table->index(['payment_date']);
            $table->index(['fee_structure_id']);

            // Unique constraint to prevent duplicate fee assignments
            $table->unique(['student_id', 'fee_structure_id'], 'unique_student_fee_structure');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('student_fees');
    }
};
