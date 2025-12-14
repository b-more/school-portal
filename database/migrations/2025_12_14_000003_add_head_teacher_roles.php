<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // First, add any missing roles from constants (10-14)
        $missingRoles = [
            ['id' => 10, 'name' => 'Clinician', 'created_at' => now(), 'updated_at' => now()],
            ['id' => 11, 'name' => 'Director', 'created_at' => now(), 'updated_at' => now()],
            ['id' => 12, 'name' => 'Dean of Primary', 'created_at' => now(), 'updated_at' => now()],
            ['id' => 13, 'name' => 'Dean of Secondary', 'created_at' => now(), 'updated_at' => now()],
            ['id' => 14, 'name' => 'Driver', 'created_at' => now(), 'updated_at' => now()],
        ];

        foreach ($missingRoles as $role) {
            DB::table('roles')->insertOrIgnore($role);
        }

        // Add new head teacher roles (15-18)
        $newRoles = [
            ['id' => 15, 'name' => 'Head Teacher Primary', 'created_at' => now(), 'updated_at' => now()],
            ['id' => 16, 'name' => 'Head Teacher Secondary', 'created_at' => now(), 'updated_at' => now()],
            ['id' => 17, 'name' => 'Deputy Head Primary', 'created_at' => now(), 'updated_at' => now()],
            ['id' => 18, 'name' => 'Deputy Head Secondary', 'created_at' => now(), 'updated_at' => now()],
        ];

        foreach ($newRoles as $role) {
            DB::table('roles')->insertOrIgnore($role);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Remove the new head teacher roles
        DB::table('roles')->whereIn('id', [15, 16, 17, 18])->delete();
    }
};
