<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payment_vouchers', function (Blueprint $table) {
            $table->id();
            $table->string('voucher_number', 30)->unique();
            $table->date('voucher_date');
            $table->enum('payee_type', ['vendor', 'employee', 'other']);
            $table->unsignedBigInteger('payee_id')->nullable();
            $table->string('payee_name');
            $table->text('description');
            $table->decimal('amount', 15, 2);
            $table->string('amount_in_words')->nullable();
            $table->string('payment_method');
            $table->foreignId('bank_account_id')->nullable()->constrained('bank_accounts')->nullOnDelete();
            $table->string('cheque_number')->nullable();
            $table->enum('status', ['pending', 'approved', 'paid', 'cancelled'])->default('pending');
            $table->foreignId('prepared_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('paid_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->foreignId('expense_id')->nullable()->constrained('expenses')->nullOnDelete();
            $table->foreignId('journal_entry_id')->nullable()->constrained('journal_entries')->nullOnDelete();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['voucher_date', 'status']);
            $table->index(['payee_type', 'payee_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payment_vouchers');
    }
};
