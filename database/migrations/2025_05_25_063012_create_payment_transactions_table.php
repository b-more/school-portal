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
        Schema::create('payment_transactions', function (Blueprint $table) {
            $table->id();

            // Core transaction details
            $table->UnsignedBigInteger('student_fee_id')->nullable();
            $table->decimal('amount', 10, 2)->nullable();
            $table->enum('type', [
                'payment',           // Regular payment
                'refund',           // Payment refund
                'adjustment',       // Manual adjustment
                'balance_forward',  // Balance carried forward
                'overpayment',      // Overpayment record
                'credit_applied'    // Credit balance applied
            ])->nullable();

            // Reference and tracking
            $table->string('reference_number')->unique()->nullable();
            $table->string('external_reference')->nullable(); // Bank ref, mobile money ref, etc.
            $table->enum('payment_method', [
                'cash',
                'bank_transfer',
                'mobile_money',
                'cheque',
                'credit_card',
                'online_payment',
                'other'
            ])->nullable();

            // Additional details
            $table->json('metadata')->nullable(); // Store payment gateway responses, etc.
            $table->text('notes')->nullable();
            $table->enum('status', ['pending', 'completed', 'failed', 'cancelled'])->default('completed');

            // Audit fields
            $table->UnsignedBigInteger('processed_by')->nullable();
            $table->timestamp('transaction_date')->default(now());
            $table->timestamps();

            // Indexes for performance
            $table->index(['student_fee_id', 'type']);
            $table->index(['reference_number']);
            $table->index(['transaction_date']);
            $table->index(['status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payment_transactions');
    }
};
