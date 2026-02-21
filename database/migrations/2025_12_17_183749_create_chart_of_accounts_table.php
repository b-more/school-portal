<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('chart_of_accounts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('account_category_id')->constrained('account_categories')->cascadeOnDelete();
            $table->foreignId('parent_id')->nullable()->constrained('chart_of_accounts')->nullOnDelete();
            $table->string('code', 20)->unique();
            $table->string('name');
            $table->text('description')->nullable();
            $table->enum('account_type', ['debit', 'credit']);
            $table->decimal('opening_balance', 15, 2)->default(0);
            $table->decimal('current_balance', 15, 2)->default(0);
            $table->boolean('is_active')->default(true);
            $table->boolean('is_system')->default(false);
            $table->boolean('allow_direct_posting')->default(true);
            $table->integer('level')->default(1);
            $table->timestamps();

            $table->index(['account_category_id', 'is_active']);
            $table->index('code');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('chart_of_accounts');
    }
};
