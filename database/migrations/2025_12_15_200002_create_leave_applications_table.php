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
        Schema::create('leave_applications', function (Blueprint $table) {
            $table->id();
            $table->string('reference_number')->unique();
            $table->foreignId('employee_id')->constrained()->onDelete('cascade');
            $table->foreignId('leave_type_id')->constrained()->onDelete('cascade');

            // Leave Period
            $table->date('start_date');
            $table->date('end_date');
            $table->integer('days_requested');
            $table->boolean('is_half_day')->default(false);
            $table->enum('half_day_period', ['morning', 'afternoon'])->nullable();

            // Application Details
            $table->text('reason');
            $table->string('contact_during_leave')->nullable();
            $table->text('handover_notes')->nullable();
            $table->string('covering_employee_id')->nullable(); // Who will cover duties

            // Supporting Documents
            $table->string('attachment')->nullable();

            // Approval Workflow
            $table->enum('status', [
                'pending',
                'approved_by_hod',      // Head of Department/Section
                'approved_by_head',     // Headteacher
                'approved',             // Final approval (Director/Admin)
                'rejected',
                'cancelled'
            ])->default('pending');

            // HOD Approval (First Level)
            $table->foreignId('hod_approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('hod_approved_at')->nullable();
            $table->text('hod_remarks')->nullable();

            // Headteacher Approval (Second Level)
            $table->foreignId('head_approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('head_approved_at')->nullable();
            $table->text('head_remarks')->nullable();

            // Final Approval (Director/Admin)
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();
            $table->text('approval_remarks')->nullable();

            // Rejection Details
            $table->foreignId('rejected_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('rejected_at')->nullable();
            $table->text('rejection_reason')->nullable();

            // Return from Leave
            $table->date('actual_return_date')->nullable();
            $table->text('return_remarks')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('leave_applications');
    }
};
