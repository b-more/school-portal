<?php

namespace Database\Seeders;

use App\Models\Employee;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class EmployeeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Clear existing employees
        Employee::truncate();

        // Get staff users that aren't assigned to employees yet
        $staffUsers = User::whereDoesntHave('employee')
                        ->where('email', 'not like', '%.student@stfrancisofassisi.tech')
                        ->where('email', 'not like', '%.parent@stfrancisofassisi.tech')
                        ->where('email', '!=', 'admin@stfrancisofassisi.tech')
                        ->get();

        // Admin staff
        $adminRoles = [
            'Principal' => 'admin',
            'Vice Principal (Academic)' => 'admin',
            'Vice Principal (Administration)' => 'admin',
            'Bursar' => 'admin',
            'Accountant' => 'admin',
            'Secretary' => 'admin',
            'Librarian' => 'admin',
            'ICT Administrator' => 'admin',
        ];

        // Support staff
        $supportRoles = [
            'Nurse' => 'support',
            'Counselor' => 'support',
            'Maintenance Officer' => 'support',
            'Security Officer' => 'support',
            'Driver' => 'support',
            'Cleaner' => 'support',
            'Groundskeeper' => 'support',
        ];

        // Create admin and support staff (non-teaching)
        $adminUserCount = min(count($adminRoles), 8); // Take up to 8 users for admin
        $adminUsers = $staffUsers->splice(0, $adminUserCount);

        $i = 0;
        foreach ($adminRoles as $position => $role) {
            if ($i >= count($adminUsers)) break;

            $user = $adminUsers[$i];
            $this->createEmployee($user, $role, 'Administration', $position);
            $i++;
        }

        $supportUserCount = min(count($supportRoles), 7); // Take up to 7 users for support
        $supportUsers = $staffUsers->splice(0, $supportUserCount);

        $i = 0;
        foreach ($supportRoles as $position => $role) {
            if ($i >= count($supportUsers)) break;

            $user = $supportUsers[$i];
            $this->createEmployee($user, $role, 'Support Services', $position);
            $i++;
        }

        // Remaining users will be teachers in different departments
        $departments = ['ECL', 'Primary', 'Secondary'];
        $remainingUsers = $staffUsers;

        foreach ($remainingUsers as $user) {
            $department = $departments[array_rand($departments)];
            $position = 'Teacher';

            if (rand(1, 10) === 1) { // 10% chance to be a head of department
                $position = 'Head of Department';
            }

            $this->createEmployee($user, 'teacher', $department, $position);
        }

        $this->command->info('Successfully seeded ' . Employee::count() . ' employees!');
    }

    /**
     * Create an employee record
     */
    private function createEmployee(User $user, string $role, string $department, string $position): Employee
    {
        $joiningDate = now()->subMonths(rand(1, 60)); // Random joining date in the past 5 years
        $employeeId = strtoupper(substr($department, 0, 1) . substr($position, 0, 1) . '-' . str_pad(rand(1, 999), 3, '0', STR_PAD_LEFT));

        // Generate a random Zambian phone number
        $phoneNumber = '+26097' . rand(1000000, 9999999);

        // Salary based on role and position (realistic ranges in ZMW)
        $baseSalary = match($role) {
            'admin' => rand(15000, 35000),
            'teacher' => match($position) {
                'Head of Department' => rand(12000, 18000),
                default => rand(8000, 12000),
            },
            'support' => rand(5000, 10000),
            default => 5000,
        };

        return Employee::create([
            'name' => $user->name,
            'email' => $user->email,
            'phone' => $phoneNumber,
            'role' => $role,
            'department' => $department,
            'position' => $position,
            'joining_date' => $joiningDate,
            'status' => 'active',
            'basic_salary' => $baseSalary,
            'employee_id' => $employeeId,
            'user_id' => $user->id,
        ]);
    }
}
