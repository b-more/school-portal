<?php

namespace Database\Seeders;

use App\Models\User;
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

        // Create admin user
        User::create([
            'name' => 'System Administrator',
            'email' => 'admin@stfrancisofassisi.tech',
            'username' => 'admin@stfrancisofassisi.tech',
            'email_verified_at' => now(),
            'password' => Hash::make('password'), // Change this to a secure password
            'remember_token' => Str::random(10),
            'status' => 'active',
        ]);

        // Create specific teacher users first
        $this->createSpecificTeachers();

        // Create additional staff users (for teachers and admin staff)
        //$this->createStaffUsers(10);

        // Create student users (older students)
        $this->createStudentUsers(20);

        // Create parent users
        $this->createParentUsers(10);

        $this->command->info('Successfully created ' . User::count() . ' users!');
    }

    /**
     * Create specific teachers from the requirements
     */
    private function createSpecificTeachers(): void
    {
        $this->command->info("Creating specific teacher users...");

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

        // Generate phone number for specific teachers
        foreach ($teachers as $name) {
            $email = $this->generateUniqueEmail($name, 'staff');
            $phone = '+26097' . rand(1000000, 9999999);

            User::create([
                'name' => $name,
                'email' => $email,
                'username' => $email,
                'phone' => $phone,
                'email_verified_at' => now(),
                'password' => Hash::make('password'),
                'remember_token' => Str::random(10),
                'status' => 'active',
            ]);
        }
    }

    /**
     * Create staff users
     */
    private function createStaffUsers(int $count): void
    {
        $this->command->info("Creating {$count} additional staff users...");

        for ($i = 1; $i <= $count; $i++) {
            $name = $this->getRandomName();
            $email = $this->generateUniqueEmail($name, 'staff');
            $phone = '+26097' . rand(1000000, 9999999);

            User::create([
                'name' => $name,
                'email' => $email,
                'username' => $email,
                'phone' => $phone,
                'email_verified_at' => now(),
                'password' => Hash::make('password'),
                'remember_token' => Str::random(10),
                'status' => 'active',
            ]);
        }
    }

    /**
     * Create student users
     */
    private function createStudentUsers(int $count): void
    {
        $this->command->info("Creating {$count} student users...");

        for ($i = 1; $i <= $count; $i++) {
            $name = $this->getRandomName();
            $email = $this->generateUniqueEmail($name, 'student');

            User::create([
                'name' => $name,
                'email' => $email,
                'username' => $email,
                'email_verified_at' => now(),
                'password' => Hash::make('password'),
                'remember_token' => Str::random(10),
                'status' => 'active',
            ]);
        }
    }

    /**
     * Create parent users
     */
    private function createParentUsers(int $count): void
    {
        $this->command->info("Creating {$count} parent users...");

        for ($i = 1; $i <= $count; $i++) {
            $name = $this->getRandomName();
            $email = $this->generateUniqueEmail($name, 'parent');
            $phone = '+26097' . rand(1000000, 9999999);

            User::create([
                'name' => $name,
                'email' => $email,
                'username' => $email,
                'phone' => $phone,
                'email_verified_at' => now(),
                'password' => Hash::make('password'),
                'remember_token' => Str::random(10),
                'status' => 'active',
            ]);
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
            'James', 'John', 'Michael', 'David', 'Robert', 'Mary', 'Patricia', 'Jennifer', 'Linda', 'Elizabeth'
        ];

        $lastNames = [
            'Mwila', 'Banda', 'Phiri', 'Mbewe', 'Zulu', 'Tembo', 'Chanda', 'Mutale', 'Bwalya', 'Musonda',
            'Daka', 'Mulenga', 'Mumba', 'Ngoma', 'Ngulube', 'Sinkala', 'Ng\'andu', 'Kalumba', 'Chisenga', 'Mwansa',
            'Mwape', 'Kabwe', 'Muleya', 'Kalaba', 'Chikwanda', 'Chilufya', 'Nkonde', 'Chisanga', 'Siame', 'Mofya'
        ];

        return $firstNames[array_rand($firstNames)] . ' ' . $lastNames[array_rand($lastNames)];
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
}
