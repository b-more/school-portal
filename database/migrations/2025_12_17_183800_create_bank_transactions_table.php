<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bank_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('bank_account_id')->constrained('bank_accounts')->cascadeOnDelete();
            $table->date('transaction_date');
            $table->enum('type', ['deposit', 'withdrawal', 'transfer', 'fee', 'charge', 'interest']);
            $table->decimal('amount', 15, 2);
            $table->string('reference')->nullable();
            $table->text('description')->nullable();
            $table->string('payee')->nullable();
            $table->boolean('reconciled')->default(false);
            $table->timestamp('reconciled_at')->nullable();
            $table->foreignId('reconciled_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('journal_entry_id')->nullable()->constrained('journal_entries')->nullOnDelete();
            $table->timestamps();

            $table->index(['bank_account_id', 'transaction_date']);
            $table->index('reconciled');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bank_transactions');
    }
};
