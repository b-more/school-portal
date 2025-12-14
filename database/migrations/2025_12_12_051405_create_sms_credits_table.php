<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('sms_credits', function (Blueprint $table) {
            $table->id();
            $table->integer('balance')->default(0)->comment('Current SMS credit balance (in credits)');
            $table->integer('cost_per_sms')->default(1)->comment('Credits per SMS part (160 chars)');
            $table->integer('low_balance_threshold')->default(50)->comment('Alert when credits fall below this');
            $table->boolean('allow_negative_balance')->default(false)->comment('Allow sending when credits are insufficient');
            $table->boolean('is_active')->default(true)->comment('Enable/disable SMS sending');
            $table->timestamp('last_topped_up_at')->nullable();
            $table->unsignedBigInteger('last_topped_up_by')->nullable();
            $table->timestamps();

            $table->foreign('last_topped_up_by')->references('id')->on('users')->onDelete('set null');
        });

        Schema::create('sms_credit_transactions', function (Blueprint $table) {
            $table->id();
            $table->enum('type', ['credit', 'debit', 'adjustment', 'refund'])->comment('Transaction type');
            $table->integer('amount')->comment('Number of credits');
            $table->integer('balance_before')->comment('Credit balance before transaction');
            $table->integer('balance_after')->comment('Credit balance after transaction');
            $table->string('description')->comment('Transaction description');
            $table->string('reference')->nullable()->comment('External reference (receipt, invoice, etc.)');
            $table->unsignedBigInteger('sms_log_id')->nullable()->comment('Related SMS log for debits');
            $table->unsignedBigInteger('performed_by')->nullable()->comment('User who performed the transaction');
            $table->json('metadata')->nullable()->comment('Additional transaction data');
            $table->timestamps();

            $table->foreign('sms_log_id')->references('id')->on('sms_logs')->onDelete('set null');
            $table->foreign('performed_by')->references('id')->on('users')->onDelete('set null');

            $table->index(['type', 'created_at']);
            $table->index('reference');
        });

        // Insert initial credit record
        DB::table('sms_credits')->insert([
            'balance' => 0,
            'cost_per_sms' => 1,
            'low_balance_threshold' => 50,
            'allow_negative_balance' => false,
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sms_credit_transactions');
        Schema::dropIfExists('sms_credits');
    }
};
