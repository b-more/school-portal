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
        Schema::table('student_fees', function (Blueprint $table) {
            $table->decimal('discount_amount', 10, 2)->default(0.00)->after('late_fee_applied');
            $table->decimal('discount_percentage', 5, 2)->nullable()->after('discount_amount');
            $table->string('discount_type')->nullable()->after('discount_percentage'); // 'scholarship', 'sibling', 'early_payment', 'custom'
            $table->text('discount_reason')->nullable()->after('discount_type');
            $table->unsignedBigInteger('approved_by')->nullable()->after('discount_reason');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('student_fees', function (Blueprint $table) {
            $table->dropColumn(['discount_amount', 'discount_percentage', 'discount_type', 'discount_reason', 'approved_by']);
        });
    }
};
