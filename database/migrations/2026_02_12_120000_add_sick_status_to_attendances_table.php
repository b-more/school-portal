<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE attendances MODIFY COLUMN status ENUM('present','absent','late','excused','sick') NOT NULL DEFAULT 'present'");
    }

    public function down(): void
    {
        // Convert any 'sick' records to 'excused' before shrinking enum
        DB::table('attendances')->where('status', 'sick')->update(['status' => 'excused']);

        DB::statement("ALTER TABLE attendances MODIFY COLUMN status ENUM('present','absent','late','excused') NOT NULL DEFAULT 'present'");
    }
};
