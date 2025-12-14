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
        // Add late fee configuration to fee_structures table
        Schema::table('fee_structures', function (Blueprint $table) {
            $table->decimal('late_fee_amount', 10, 2)->nullable()->after('total_fee');
            $table->decimal('late_fee_percentage', 5, 2)->nullable()->after('late_fee_amount');
            $table->date('payment_deadline')->nullable()->after('late_fee_percentage');
        });

        // Add late fee tracking to student_fees table
        Schema::table('student_fees', function (Blueprint $table) {
            $table->date('payment_deadline')->nullable()->after('balance');
            $table->decimal('late_fee_applied', 10, 2)->default(0.00)->after('payment_deadline');
            $table->boolean('is_overdue')->default(false)->after('late_fee_applied');
            $table->date('overdue_since')->nullable()->after('is_overdue');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('fee_structures', function (Blueprint $table) {
            $table->dropColumn(['late_fee_amount', 'late_fee_percentage', 'payment_deadline']);
        });

        Schema::table('student_fees', function (Blueprint $table) {
            $table->dropColumn(['payment_deadline', 'late_fee_applied', 'is_overdue', 'overdue_since']);
        });
    }
};
