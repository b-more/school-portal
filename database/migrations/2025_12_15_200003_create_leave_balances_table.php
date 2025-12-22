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
        Schema::create('leave_balances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained()->onDelete('cascade');
            $table->foreignId('leave_type_id')->constrained()->onDelete('cascade');
            $table->year('year');
            $table->integer('allocated_days')->default(0);
            $table->integer('used_days')->default(0);
            $table->integer('carried_forward')->default(0);
            $table->integer('adjustment_days')->default(0); // Manual adjustments
            $table->text('adjustment_reason')->nullable();
            $table->timestamps();

            // Unique constraint for employee-leave_type-year
            $table->unique(['employee_id', 'leave_type_id', 'year']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('leave_balances');
    }
};
