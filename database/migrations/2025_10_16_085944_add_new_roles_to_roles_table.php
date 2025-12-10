<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\Role;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Add new roles to the database
        $newRoles = [
            [
                'name' => 'Clinician',
                'description' => 'Medical staff responsible for student health care',
                'is_active' => true,
            ],
            [
                'name' => 'Director',
                'description' => 'School director with administrative oversight',
                'is_active' => true,
            ],
            [
                'name' => 'Dean of Primary',
                'description' => 'Dean of primary school teachers',
                'is_active' => true,
            ],
            [
                'name' => 'Dean of Secondary',
                'description' => 'Dean of secondary school teachers',
                'is_active' => true,
            ],
            [
                'name' => 'Driver',
                'description' => 'School bus/transport driver',
                'is_active' => true,
            ],
        ];

        foreach ($newRoles as $role) {
            Role::firstOrCreate(
                ['name' => $role['name']],
                $role
            );
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Remove the added roles
        $roleNames = ['Clinician', 'Director', 'Dean of Primary', 'Dean of Secondary', 'Driver'];

        Role::whereIn('name', $roleNames)->delete();
    }
};
