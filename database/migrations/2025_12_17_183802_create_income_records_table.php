<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('income_records', function (Blueprint $table) {
            $table->id();
            $table->string('income_number', 30)->unique();
            $table->date('income_date');
            $table->foreignId('account_id')->constrained('chart_of_accounts')->cascadeOnDelete();
            $table->text('description');
            $table->decimal('amount', 15, 2);
            $table->string('payment_method')->nullable();
            $table->string('reference')->nullable();
            $table->foreignId('bank_account_id')->nullable()->constrained('bank_accounts')->nullOnDelete();
            $table->string('payer_name')->nullable();
            $table->string('payer_contact')->nullable();
            $table->string('source_type')->nullable();
            $table->unsignedBigInteger('source_id')->nullable();
            $table->foreignId('journal_entry_id')->nullable()->constrained('journal_entries')->nullOnDelete();
            $table->foreignId('academic_year_id')->nullable()->constrained('academic_years')->nullOnDelete();
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['income_date', 'account_id']);
            $table->index(['source_type', 'source_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('income_records');
    }
};
