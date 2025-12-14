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
        // For SQLite, we need to alter the enum values differently
        if (DB::getDriverName() === 'sqlite') {
            // SQLite doesn't support ALTER COLUMN, so we'll handle it differently
            // The enum is already flexible in SQLite as it stores TEXT
            // No changes needed for SQLite
        } else {
            // For MySQL/PostgreSQL
            DB::statement("ALTER TABLE student_fees MODIFY COLUMN payment_status ENUM('unpaid', 'partial', 'paid', 'overpaid') DEFAULT 'unpaid'");
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (DB::getDriverName() !== 'sqlite') {
            DB::statement("ALTER TABLE student_fees MODIFY COLUMN payment_status ENUM('unpaid', 'partial', 'paid') DEFAULT 'unpaid'");
        }
    }
};
