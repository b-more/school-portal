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
            ParentGuardianSeeder::class,
            StudentSeeder::class,

            // 2. Create employees (teachers and staff)
            EmployeeSeeder::class,

            // 3. Create parent/guardian records
            //ParentGuardianSeeder::class,

            // 4. Create classes for all departments
            ClassSeeder::class,

            // 5. Create subjects for all grades
            SubjectSeeder::class,

            // 6. Create students
            //StudentSeeder::class,

            // 7. Create teacher assignments (must come after teachers, classes and subjects)
            TeacherAssignmentSeeder::class,

            FeeStructureSeeder::class,

        ]);
    }
}
