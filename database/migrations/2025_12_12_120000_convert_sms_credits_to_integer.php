<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     * This migration converts SMS credit columns from decimal to integer format.
     */
    public function up(): void
    {
        // Check if sms_credits table exists and has decimal columns
        if (Schema::hasTable('sms_credits')) {
            Schema::table('sms_credits', function (Blueprint $table) {
                // Change decimal columns to integer
                $table->integer('balance')->default(0)->change();
                $table->integer('cost_per_sms')->default(1)->change();
                $table->integer('low_balance_threshold')->default(50)->change();
            });

            // Update existing record to use sensible default for cost_per_sms
            DB::table('sms_credits')
                ->where('cost_per_sms', '<', 1)
                ->update(['cost_per_sms' => 1]);
        }

        // Check if sms_credit_transactions table exists
        if (Schema::hasTable('sms_credit_transactions')) {
            Schema::table('sms_credit_transactions', function (Blueprint $table) {
                // Change decimal columns to integer
                $table->integer('amount')->change();
                $table->integer('balance_before')->change();
                $table->integer('balance_after')->change();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('sms_credits')) {
            Schema::table('sms_credits', function (Blueprint $table) {
                $table->decimal('balance', 12, 2)->default(0.00)->change();
                $table->decimal('cost_per_sms', 8, 4)->default(0.50)->change();
                $table->decimal('low_balance_threshold', 10, 2)->default(50.00)->change();
            });
        }

        if (Schema::hasTable('sms_credit_transactions')) {
            Schema::table('sms_credit_transactions', function (Blueprint $table) {
                $table->decimal('amount', 12, 2)->change();
                $table->decimal('balance_before', 12, 2)->change();
                $table->decimal('balance_after', 12, 2)->change();
            });
        }
    }
};
