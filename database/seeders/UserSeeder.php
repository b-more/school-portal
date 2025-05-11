<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Role;
use App\Models\Employee;
use App\Models\Student;
use App\Models\Grade;
use App\Models\ParentGuardian;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('Creating users...');

        // Get roles
        $adminRole = Role::where('name', 'Admin')->first();
        $teacherRole = Role::where('name', 'Teacher')->first();
        $studentRole = Role::where('name', 'Student')->first();
        $parentRole = Role::where('name', 'Parent')->first();

        // Create admin user
        $admin = User::create([
            'role_id' => $adminRole->id,
            'name' => 'System Administrator',
            'email' => 'admin@stfrancisofassisi.tech',
            'username' => 'admin',
            'email_verified_at' => now(),
            'password' => Hash::make('password'), // Change this to a secure password
            'remember_token' => Str::random(10),
            'status' => 'active',
            'phone' => '+260971234567',
            'phone_verified_at' => now(),
        ]);

        // Create Employee record for Admin
        Employee::create([
    'user_id' => $admin->id,
    'name' => $admin->name,
    'email' => $admin->email,
    'phone' => $admin->phone,
    'role_id' => $adminRole->id,  // CORRECT - using 'role_id'
    'employee_number' => 'EMP001',
    'status' => 'active',
    'department' => 'Administration',
]);

        // Create specific teacher users first
        $this->createSpecificTeachers();

        // Create additional staff users (for teachers and admin staff)
        // Uncomment if needed: $this->createStaffUsers(5);

        // Create student users (older students)
        $this->createStudentUsers(20);

        // Create parent users
        $this->createParentUsers(15);

        $this->command->info('Successfully created ' . User::count() . ' users!');
    }

    /**
     * Create specific teachers from the requirements
     */
    private function createSpecificTeachers(): void
    {
        $this->command->info("Creating specific teacher users...");

        $teacherRole = Role::where('name', 'Teacher')->first();

        $teachers = [
            'Chungu',
            'Zunda',
            'Constance',
            'Musa Doris',
            'Musakanya Mutale',
            'Eunice Kansa',
            'Euelle Sinyangwe',
            'Mukupa Agness',
            'Mubisa Martin',
            'Kopakopa Leonard',
            'Kaposhi',
            'Muonda Bwalya',
            'Chibwe Quintino',
            'Mwaba Breven',
            'Sintomba Freddy',
            'Mulenga Vincent',
            'Bwalya Sylvester',
            'Singongo Bruce',
            'Mercy Kapelenga',
            'Sylvester Lupando',
            'Tiza Nkhomo'
        ];

        $departments = ['ECL', 'Primary', 'Secondary'];

        foreach ($teachers as $index => $name) {
            $email = $this->generateUniqueEmail($name, 'staff');
            $phone = '+26097' . rand(1000000, 9999999);
            $empNumber = 'TEA' . str_pad($index + 1, 3, '0', STR_PAD_LEFT);

            $user = User::create([
                'role_id' => $teacherRole->id,
                'name' => $name,
                'email' => $email,
                'username' => $this->generateUsername($name),
                'phone' => $phone,
                'email_verified_at' => now(),
                'password' => Hash::make('password'),
                'remember_token' => Str::random(10),
                'status' => 'active',
                'phone_verified_at' => now(),
            ]);

            // Create Employee record for Teacher
            Employee::create([
    'user_id' => $user->id,
    'name' => $user->name,
    'email' => $user->email,
    'phone' => $user->phone,
    'role_id' => $teacherRole->id,  // CORRECT - using 'role_id'
    'employee_number' => $empNumber,
    'status' => 'active',
    'department' => $departments[array_rand($departments)],
]);
        }
    }

    /**
     * Create staff users
     */
    private function createStaffUsers(int $count): void
    {
        $this->command->info("Creating {$count} additional staff users...");

        $roles = [
            'Teacher' => ['role' => 'teacher', 'department' => ['ECL', 'Primary', 'Secondary']],
            'Accountant' => ['role' => 'accountant', 'department' => ['Finance']],
            'Nurse' => ['role' => 'nurse', 'department' => ['Health']],
            'Librarian' => ['role' => 'librarian', 'department' => ['Library']],
            'Security' => ['role' => 'security', 'department' => ['Security']],
        ];

        for ($i = 1; $i <= $count; $i++) {
            $name = $this->getRandomName();
            $email = $this->generateUniqueEmail($name, 'staff');
            $phone = '+26097' . rand(1000000, 9999999);

            // Randomly assign a role
            $roleName = array_rand($roles);
            $roleData = $roles[$roleName];
            $role = Role::where('name', $roleName)->first();

            $user = User::create([
                'role_id' => $role->id,
                'name' => $name,
                'email' => $email,
                'username' => $this->generateUsername($name),
                'phone' => $phone,
                'email_verified_at' => now(),
                'password' => Hash::make('password'),
                'remember_token' => Str::random(10),
                'status' => 'active',
                'phone_verified_at' => now(),
            ]);

           Employee::create([
            'user_id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'phone' => $user->phone,
            'role_id' => $role->id,  // CORRECT - using 'role_id'
            'employee_number' => strtoupper(substr($roleName, 0, 3)) . str_pad($i, 3, '0', STR_PAD_LEFT),
            'status' => 'active',
            'department' => is_array($roleData['department']) ? $roleData['department'][array_rand($roleData['department'])] : $roleData['department'][0],
        ]);
        }
    }

    /**
     * Create student users
     */
    private function createStudentUsers(int $count): void
    {
        $this->command->info("Creating {$count} student users...");

        $studentRole = Role::where('name', 'Student')->first();
        $grades = Grade::where('is_active', true)->get();

        for ($i = 1; $i <= $count; $i++) {
            $name = $this->getRandomName();
            $email = $this->generateUniqueEmail($name, 'student');

            $user = User::create([
                'role_id' => $studentRole->id,
                'name' => $name,
                'email' => $email,
                'username' => $this->generateUsername($name),
                'email_verified_at' => now(),
                'password' => Hash::make('password'),
                'remember_token' => Str::random(10),
                'status' => 'active',
            ]);

            // Randomly select a grade (Secondary students are more likely to have user accounts)
            $gradeId = $grades->where('level', '>=', 11)->random()->id;

            // Create Student record
            Student::create([
                'user_id' => $user->id,
                'name' => $user->name,
                'student_id_number' => 'STF' . str_pad($i, 4, '0', STR_PAD_LEFT),
                'grade_id' => $gradeId,  // Use grade_id instead of grade
                'admission_date' => now()->subDays(rand(30, 365)),
                'enrollment_status' => 'active',
                'date_of_birth' => now()->subYears(rand(14, 18))->subDays(rand(0, 365)),
                'gender' => ['male', 'female'][array_rand(['male', 'female'])],
            ]);
        }
    }

    /**
     * Create parent users
     */
    private function createParentUsers(int $count): void
    {
        $this->command->info("Creating {$count} parent users...");

        $parentRole = Role::where('name', 'Parent')->first();
        $relationships = ['father', 'mother', 'guardian'];
        $students = Student::all();

        for ($i = 1; $i <= $count; $i++) {
            $name = $this->getRandomName();
            $email = $this->generateUniqueEmail($name, 'parent');
            $phone = '+26097' . rand(1000000, 9999999);

            $user = User::create([
                'role_id' => $parentRole->id,
                'name' => $name,
                'email' => $email,
                'username' => $this->generateUsername($name),
                'phone' => $phone,
                'email_verified_at' => now(),
                'password' => Hash::make('password'),
                'remember_token' => Str::random(10),
                'status' => 'active',
                'phone_verified_at' => now(),
            ]);

            // Create ParentGuardian record
            $parentGuardian = ParentGuardian::create([
                'user_id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'phone' => $user->phone,
                'relationship' => $relationships[array_rand($relationships)],
                'address' => $this->generateRandomAddress(),
            ]);

            // Link parent to some students (1-3 children)
            $childrenCount = rand(1, 3);
            $availableStudents = $students->where('parent_guardian_id', null);

            if ($availableStudents->count() >= $childrenCount) {
                $childrenStudents = $availableStudents->random($childrenCount);

                foreach ($childrenStudents as $student) {
                    $student->update(['parent_guardian_id' => $parentGuardian->id]);
                }
            }
        }
    }

    /**
     * Generate a random name
     */
    private function getRandomName(): string
    {
        $firstNames = [
            'Chipo', 'Mulenga', 'Mutale', 'Bwalya', 'Chomba', 'Mwila', 'Nkonde', 'Musonda', 'Chilufya', 'Kalaba',
            'Mwamba', 'Chanda', 'Zulu', 'Tembo', 'Banda', 'Phiri', 'Mbewe', 'Lungu', 'Daka', 'Mumba',
            'James', 'John', 'Michael', 'David', 'Robert', 'Mary', 'Patricia', 'Jennifer', 'Linda', 'Elizabeth',
            'Grace', 'Ruth', 'Sarah', 'Esther', 'Faith', 'Hope', 'Love', 'Joy', 'Peace', 'Mercy'
        ];

        $lastNames = [
            'Mwila', 'Banda', 'Phiri', 'Mbewe', 'Zulu', 'Tembo', 'Chanda', 'Mutale', 'Bwalya', 'Musonda',
            'Daka', 'Mulenga', 'Mumba', 'Ngoma', 'Ngulube', 'Sinkala', 'Ng\'andu', 'Kalumba', 'Chisenga', 'Mwansa',
            'Mwape', 'Kabwe', 'Muleya', 'Kalaba', 'Chikwanda', 'Chilufya', 'Nkonde', 'Chisanga', 'Siame', 'Mofya',
            'Kaoma', 'Kachingwe', 'Nyirenda', 'Simukoko', 'Sibanda', 'Sikazwe', 'Sichinga', 'Mwanza', 'Chansa', 'Sikanyiti'
        ];

        return $firstNames[array_rand($firstNames)] . ' ' . $lastNames[array_rand($lastNames)];
    }

    /**
     * Generate a username from a name
     */
    private function generateUsername(string $name): string
    {
        $username = strtolower(str_replace(' ', '.', $name));
        $baseUsername = $username;
        $counter = 1;

        // Ensure uniqueness
        while (User::where('username', $username)->exists()) {
            $username = $baseUsername . $counter;
            $counter++;
        }

        return $username;
    }

    /**
     * Generate a unique email address
     */
    private function generateUniqueEmail(string $name, string $type): string
    {
        $baseName = strtolower(str_replace(' ', '.', $name));
        $email = $baseName;

        // Add type suffix (staff, student, parent)
        if ($type === 'student') {
            $email .= '.student';
        } elseif ($type === 'parent') {
            $email .= '.parent';
        }

        // Add domain
        $email .= '@stfrancisofassisi.tech';

        // Ensure uniqueness
        $counter = 1;
        $originalEmail = $email;

        while (User::where('email', $email)->exists()) {
            $email = str_replace('@', $counter . '@', $originalEmail);
            $counter++;
        }

        return $email;
    }

    /**
     * Generate a random address
     */
    private function generateRandomAddress(): string
    {
        $compounds = ['Kabulonga', 'Roma', 'Libala', 'Chilenje', 'Matero', 'Kalingalinga', 'Broadhurst', 'Fairview', 'Chelston', 'Avondale'];
        $streets = ['Main Road', 'Independence Avenue', 'Church Road', 'Government Road', 'Market Road', 'Station Road', 'School Road'];

        return 'Plot ' . rand(1, 999) . ', ' . $streets[array_rand($streets)] . ', ' . $compounds[array_rand($compounds)] . ', Lusaka';
    }
}
