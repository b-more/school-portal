<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            UserSeeder::class,
            //EmployeeSeeder::class,
            SubjectSeeder::class,
            //ParentGuardianSeeder::class, // This will also create students
            FeeStructureSeeder::class,
            //HomeworkSeeder::class,
            //ResultSeeder::class,
            //EventSeeder::class,
            //SmsLogSeeder::class, // SMS logs seeder added

        ]);
    }
}
