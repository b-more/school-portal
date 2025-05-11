<?php

namespace Database\Seeders;

use App\Models\Employee;
use App\Models\User;
use App\Models\SchoolClass;
use App\Models\Role;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
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

        // Clear existing class-teacher assignments
        DB::table('class_teacher')->truncate();

        // Create school classes if they don't exist
        $this->createSchoolClasses();

        // Create specific teachers with their class assignments
        $this->createSpecificTeachers();

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
     * Create school classes if they don't exist
     */
    private function createSchoolClasses(): void
    {
        // Define classes grouped by department
        $classes = [
            'ECL' => [
                'Baby Class',
                'Middle Class',
                'Reception',
            ],
            'Primary' => [
                'Grade 1',
                'Grade 2',
                'Grade 3',
                'Grade 4',
                'Grade 5',
                'Grade 6',
                'Grade 7',
            ],
            'Secondary' => [
                'Form 1 Archivers',
                'Form 1 Success',
                'Form 2 Lilly',
                'Form 2 Lotus',
                'Form 3 A',
                'Form 3 B',
                'Form 4',
                'Form 5',
            ],
        ];

        foreach ($classes as $department => $departmentClasses) {
            foreach ($departmentClasses as $className) {
                // Check if class already exists
                $existingClass = SchoolClass::where('name', $className)->first();

                if (!$existingClass) {
                    SchoolClass::create([
                        'name' => $className,
                        'department' => $department,
                        'grade' => $className, // Add grade as well
                        'section' => null, // Add section if needed
                        'is_active' => true,
                        'status' => 'active',
                    ]);
                }
            }
        }
    }

    /**
     * Create specific teachers with their class assignments
     */
    private function createSpecificTeachers(): void
    {
        // Define mapping of teachers to classes and their positions
        $teacherAssignments = [
            // ECL Department
            ['name' => 'Chungu', 'class' => 'Baby Class', 'department' => 'ECL', 'position' => 'Teacher'],
            ['name' => 'Zunda', 'class' => 'Middle Class', 'department' => 'ECL', 'position' => 'Teacher'],
            ['name' => 'Constance', 'class' => 'Reception', 'department' => 'ECL', 'position' => 'Teacher'],

            // Primary Department
            ['name' => 'Musa Doris', 'class' => 'Grade 1', 'department' => 'Primary', 'position' => 'Teacher'],
            ['name' => 'Musakanya Mutale', 'class' => 'Grade 2', 'department' => 'Primary', 'position' => 'Teacher'],
            ['name' => 'Eunice Kansa', 'class' => 'Grade 3', 'department' => 'Primary', 'position' => 'Teacher'],
            ['name' => 'Euelle Sinyangwe', 'class' => 'Grade 4', 'department' => 'Primary', 'position' => 'Teacher'],
            ['name' => 'Mukupa Agness', 'class' => 'Grade 5', 'department' => 'Primary', 'position' => 'Teacher'],
            ['name' => 'Mubisa Martin', 'class' => 'Grade 6', 'department' => 'Primary', 'position' => 'Teacher'],
            ['name' => 'Kopakopa Leonard', 'class' => 'Grade 7', 'department' => 'Primary', 'position' => 'Teacher'],
            ['name' => 'Mercy Kapelenga', 'class' => null, 'department' => 'Primary', 'position' => 'Dean of Senior Teachers'],

            // Secondary Department
            ['name' => 'Kaposhi', 'class' => 'Form 1 Archivers', 'department' => 'Secondary', 'position' => 'Teacher'],
            ['name' => 'Muonda Bwalya', 'class' => 'Form 1 Success', 'department' => 'Secondary', 'position' => 'Teacher'],
            ['name' => 'Chibwe Quintino', 'class' => 'Form 2 Lilly', 'department' => 'Secondary', 'position' => 'Teacher'],
            ['name' => 'Mwaba Breven', 'class' => 'Form 2 Lotus', 'department' => 'Secondary', 'position' => 'Teacher'],
            ['name' => 'Sintomba Freddy', 'class' => 'Form 3 A', 'department' => 'Secondary', 'position' => 'Teacher'],
            ['name' => 'Mulenga Vincent', 'class' => 'Form 3 B', 'department' => 'Secondary', 'position' => 'Teacher'],
            ['name' => 'Bwalya Sylvester', 'class' => 'Form 4', 'department' => 'Secondary', 'position' => 'Teacher'],
            ['name' => 'Singongo Bruce', 'class' => 'Form 5', 'department' => 'Secondary', 'position' => 'Head of Department'],

            // Administration
            ['name' => 'Sylvester Lupando', 'class' => null, 'department' => 'Administration', 'position' => 'Headteacher'],
            ['name' => 'Tiza Nkhomo', 'class' => null, 'department' => 'Administration', 'position' => 'Deputy Headteacher'],
            ['name' => 'Racent Lunda', 'class' => null, 'department' => 'Administration', 'position' => 'School Secretary'],
            ['name' => 'Constance Mulenga', 'class' => null, 'department' => 'Administration', 'position' => 'Deputy Director'],
            ['name' => 'Mpongwe', 'class' => null, 'department' => 'Administration', 'position' => 'Accountant'],
            ['name' => 'Mubanga Chanda', 'class' => null, 'department' => 'Administration', 'position' => 'Librarian'],
            ['name' => 'Lubinda', 'class' => null, 'department' => 'Administration', 'position' => 'ICT Administrator'],
            ['name' => 'Francis Mulenga', 'class' => null, 'department' => 'Administration', 'position' => 'Executive Director'],

            // Support Staff
            ['name' => 'Chanda', 'class' => null, 'department' => 'Support staff', 'position' => 'Nurse'],
            ['name' => 'Mumba', 'class' => null, 'department' => 'Support staff', 'position' => 'Counselor'],
            ['name' => 'Chanda Chibwe', 'class' => null, 'department' => 'Support staff', 'position' => 'Maintenance Officer'],
            ['name' => 'Mumba Chanda', 'class' => null, 'department' => 'Support staff', 'position' => 'Security Officer'],
            ['name' => 'Alaba', 'class' => null, 'department' => 'Support staff', 'position' => 'Driver'],
            ['name' => 'Charles', 'class' => null, 'department' => 'Support staff', 'position' => 'Driver'],
            ['name' => 'Abraham', 'class' => null, 'department' => 'Support staff', 'position' => 'Driver'],
            ['name' => 'Chanda Mwansa', 'class' => null, 'department' => 'Support staff', 'position' => 'Cleaner'],
            ['name' => 'Chanda Mwila', 'class' => null, 'department' => 'Support staff', 'position' => 'Groundskeeper'],
        ];

        foreach ($teacherAssignments as $assignment) {
            // Find the user
            $user = User::where('name', $assignment['name'])->first();

            if (!$user) {
                $this->command->error("User {$assignment['name']} not found. Skipping.");
                continue;
            }

            // Create employee record
            $employee = $this->createEmployee(
                $user,
                'teacher',
                $assignment['department'],
                $assignment['position']
            );

            // Assign to class if applicable
            if ($assignment['class']) {
                $class = SchoolClass::where('name', $assignment['class'])->first();

                if ($class) {
                    DB::table('class_teacher')->insert([
                        'class_id' => $class->id,
                        'employee_id' => $employee->id,
                        'role' => 'Class Teacher',
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                } else {
                    $this->command->error("Class {$assignment['class']} not found for {$assignment['name']}.");
                }
            }
        }
    }

    /**
     * Create an employee record
     */
    private function createEmployee(User $user, string $role, string $department, string $position): Employee
    {
        $joiningDate = now()->subMonths(rand(1, 60)); // Random joining date in the past 5 years
        $employeeId = strtoupper(substr($department, 0, 1) . substr($position, 0, 1) . '-' . str_pad(rand(1, 999), 3, '0', STR_PAD_LEFT));

        // Get the role_id from the role name
        $roleMap = [
            'admin' => 1,      // Admin role_id
            'teacher' => 2,    // Teacher role_id
            'support' => 9,    // Support role_id
        ];

        $roleId = $roleMap[$role] ?? 2; // Default to teacher role_id if not found

        // Salary based on role and position (realistic ranges in ZMW)
        $baseSalary = match($role) {
            'admin' => rand(15000, 35000),
            'teacher' => match($position) {
                'Headteacher' => rand(30000, 40000),
                'Deputy Headteacher' => rand(25000, 30000),
                'Dean of Senior Teachers' => rand(20000, 25000),
                'Head of Department' => rand(15000, 20000),
                default => rand(8000, 15000),
            },
            'support' => rand(5000, 10000),
            default => 5000,
        };

        return Employee::create([
            'name' => $user->name,
            'email' => $user->email,
            'phone' => $user->phone ?? ('+26097' . rand(1000000, 9999999)),
            'role_id' => $roleId,  // FIXED: using 'role_id' instead of 'role'
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
