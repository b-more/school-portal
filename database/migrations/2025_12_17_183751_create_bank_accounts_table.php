<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bank_accounts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('account_id')->nullable()->constrained('chart_of_accounts')->nullOnDelete();
            $table->string('bank_name');
            $table->string('account_name');
            $table->string('account_number')->unique();
            $table->string('branch')->nullable();
            $table->string('swift_code')->nullable();
            $table->string('currency', 3)->default('ZMW');
            $table->decimal('opening_balance', 15, 2)->default(0);
            $table->decimal('current_balance', 15, 2)->default(0);
            $table->boolean('is_active')->default(true);
            $table->boolean('is_default')->default(false);
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index('is_active');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bank_accounts');
    }
};
