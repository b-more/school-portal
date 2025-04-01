<?php

namespace Database\Seeders;

use App\Models\Employee;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class EmployeeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('Creating employees with different roles...');

        // Determine allowed roles by inspecting the database schema
        $allowedRoles = $this->getAllowedRoles();
        $this->command->info('Allowed roles in the database: ' . implode(', ', $allowedRoles));

        // Map our desired roles to the allowed roles in the database
        $roleMapping = [
            'teacher' => in_array('teacher', $allowedRoles) ? 'teacher' : 'other',
            'admin' => in_array('admin', $allowedRoles) ? 'admin' : 'other',
            'support' => in_array('support', $allowedRoles) ? 'support' : 'other',
            'management' => in_array('principal', $allowedRoles) ? 'principal' :
                           (in_array('admin', $allowedRoles) ? 'admin' : 'other')
        ];

        // Define employee roles and how many to create
        $roles = [
            'teacher' => 15,    // Create 15 teachers
            'admin' => 3,       // Create 3 admin staff
            'support' => 2,     // Create 2 support staff
            'management' => 2   // Create 2 management staff
        ];

        $departments = [
            'teacher' => ['Mathematics', 'English', 'Social Studies', 'Science', 'Physical Education'],
            'admin' => ['Administration', 'Finance', 'Human Resources'],
            'support' => ['IT Support', 'Maintenance', 'Library'],
            'management' => ['School Management']
        ];

        $positions = [
            'teacher' => ['Junior Teacher', 'Senior Teacher', 'Department Head'],
            'admin' => ['Administrative Assistant', 'Secretary', 'Accountant'],
            'support' => ['IT Technician', 'Lab Assistant', 'Librarian'],
            'management' => ['Principal', 'Vice Principal', 'Academic Coordinator']
        ];

        $count = 1;

        // Create employees for each role
        foreach ($roles as $roleKey => $numToCreate) {
            $this->command->info("Creating $numToCreate $roleKey employees...");
            $mappedRole = $roleMapping[$roleKey];

            for ($i = 1; $i <= $numToCreate; $i++) {
                // Create a user account for teachers and management
                $userId = null;
                if ($roleKey === 'teacher' || $roleKey === 'management') {
                    // Create user without role field
                    $user = User::create([
                        'name' => fake()->name(),
                        'email' => strtolower($roleKey) . $i . '@school.test',
                        'email_verified_at' => now(),
                        'password' => Hash::make('password'), // default password
                        'remember_token' => Str::random(10)
                    ]);
                    $userId = $user->id;
                }

                // Select department and position based on role
                $department = $departments[$roleKey][array_rand($departments[$roleKey])];
                $position = $positions[$roleKey][array_rand($positions[$roleKey])];

                // Adjust salary based on role and position
                $baseSalary = match($roleKey) {
                    'teacher' => fake()->numberBetween(3000, 5500),
                    'admin' => fake()->numberBetween(2500, 4000),
                    'support' => fake()->numberBetween(2000, 3500),
                    'management' => fake()->numberBetween(6000, 8000),
                    default => fake()->numberBetween(2000, 3000)
                };

                // Create employee record with the mapped role
                Employee::create([
                    'name' => $userId ? User::find($userId)->name : fake()->name(),
                    'email' => $userId ? User::find($userId)->email : strtolower($roleKey) . $i . '@school.test',
                    'phone' => '260' . fake()->numberBetween(900000000, 999999999),
                    'role' => $mappedRole, // Use mapped role that's allowed in the database
                    'department' => $department,
                    'position' => $position,
                    'joining_date' => now()->subMonths(fake()->numberBetween(1, 60)),
                    'status' => 'active',
                    'basic_salary' => $baseSalary,
                    'employee_id' => 'EMP' . str_pad($count, 3, '0', STR_PAD_LEFT),
                    'user_id' => $userId,
                ]);

                $count++;
            }
        }

        // Create one specific teacher for testing purposes if teacher role is allowed
        if (in_array('teacher', $allowedRoles)) {
            $testTeacherUser = User::where('email', 'teacher@example.com')->first();

            if (!$testTeacherUser) {
                $testTeacherUser = User::create([
                    'name' => 'John Smith',
                    'email' => 'teacher@example.com',
                    'email_verified_at' => now(),
                    'password' => Hash::make('password'),
                    'remember_token' => Str::random(10)
                ]);
            }

            if (!Employee::where('email', 'teacher@example.com')->exists()) {
                Employee::create([
                    'name' => 'John Smith',
                    'email' => 'teacher@example.com',
                    'phone' => '260977123457',
                    'role' => 'teacher',
                    'department' => 'Science',
                    'position' => 'Senior Teacher',
                    'joining_date' => now()->subYears(2),
                    'status' => 'active',
                    'basic_salary' => 5000.00,
                    'employee_id' => 'EMP' . str_pad($count, 3, '0', STR_PAD_LEFT),
                    'user_id' => $testTeacherUser->id,
                ]);
            }
        }

        $this->command->info('Employee seeding completed successfully!');
    }

    /**
     * Get the allowed values for the role column from the database.
     * This works with ENUM columns in MySQL.
     */
    private function getAllowedRoles(): array
    {
        try {
            // Try to get the ENUM values directly from the database
            $columnType = DB::select("SHOW COLUMNS FROM employees WHERE Field = 'role'")[0]->Type;
            preg_match('/^enum\((.*)\)$/', $columnType, $matches);

            if (isset($matches[1])) {
                $values = array_map(function ($value) {
                    return trim($value, "'");
                }, explode(',', $matches[1]));

                return $values;
            }
        } catch (\Exception $e) {
            // If there's an error, return some default values
            $this->command->warn("Couldn't determine enum values: " . $e->getMessage());
        }

        // Default fallback values if we can't determine from the database
        return ['teacher', 'admin', 'support', 'other'];
    }
}
