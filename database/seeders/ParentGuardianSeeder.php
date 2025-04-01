<?php

namespace Database\Seeders;

use App\Models\ParentGuardian;
use App\Models\Student;
use App\Models\User;
use Illuminate\Database\Seeder;

class ParentGuardianSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Link the parent user
        $parentUser = User::where('email', 'parent@example.com')->first();

        // Create main test parent with user account
        $testParent = ParentGuardian::create([
            'name' => 'Jane Doe',
            'email' => 'parent@example.com',
            'phone' => '260977123458',
            'alternate_phone' => '260967123458',
            'relationship' => 'mother',
            'occupation' => 'Accountant',
            'address' => '123 Main St, Lusaka',
            'user_id' => $parentUser->id,
        ]);

        // Create students for this parent - one in each grade for testing
        $grades = ['Grade 1', 'Grade 2', 'Grade 3', 'Grade 8', 'Grade 9'];

        foreach ($grades as $index => $grade) {
            Student::create([
                'name' => 'Child ' . ($index + 1) . ' Doe',
                'date_of_birth' => now()->subYears(5 + $index),
                'gender' => $index % 2 == 0 ? 'male' : 'female',
                'address' => '123 Main St, Lusaka',
                'student_id_number' => 'ST' . str_pad($index + 1, 4, '0', STR_PAD_LEFT),
                'parent_guardian_id' => $testParent->id,
                'grade' => $grade,
                'admission_date' => now()->subMonths(rand(1, 24)),
                'enrollment_status' => 'active',
                'previous_school' => $index > 0 ? 'Previous School ' . $index : null,
            ]);
        }

        // Create 20 more parents with 1-3 children each
        for ($i = 1; $i <= 20; $i++) {
            $parent = ParentGuardian::create([
                'name' => fake()->name(),
                'email' => fake()->unique()->email(),
                'phone' => '26097' . fake()->numberBetween(1000000, 9999999),
                'alternate_phone' => rand(0, 1) ? '26096' . fake()->numberBetween(1000000, 9999999) : null,
                'relationship' => fake()->randomElement(['father', 'mother', 'guardian']),
                'occupation' => fake()->jobTitle(),
                'address' => fake()->address(),
                'user_id' => null,
            ]);

            // Create 1-3 students for this parent
            $childrenCount = rand(1, 3);

            for ($j = 1; $j <= $childrenCount; $j++) {
                $grade = $grades[array_rand($grades)];
                $age = $grade == 'Grade 1' ? rand(6, 7) :
                       ($grade == 'Grade 2' ? rand(7, 8) :
                       ($grade == 'Grade 3' ? rand(8, 9) :
                       ($grade == 'Grade 8' ? rand(13, 14) : rand(14, 15))));

                Student::create([
                    'name' => fake()->name(),
                    'date_of_birth' => now()->subYears($age)->subMonths(rand(0, 11)),
                    'gender' => fake()->randomElement(['male', 'female']),
                    'address' => $parent->address,
                    'student_id_number' => 'ST' . fake()->unique()->numberBetween(1000, 9999),
                    'parent_guardian_id' => $parent->id,
                    'grade' => $grade,
                    'admission_date' => now()->subMonths(rand(1, 36)),
                    'enrollment_status' => fake()->randomElement(['active', 'active', 'active', 'inactive']),
                    'previous_school' => fake()->randomElement(['', 'Previous School A', 'Previous School B', 'Home Schooled']),
                ]);
            }
        }
    }
}
