<?php

namespace Database\Seeders;

use App\Models\SchoolClass;
use Illuminate\Database\Seeder;

class ClassSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Clear existing classes
        SchoolClass::truncate();

        // ECL Department Classes
        $eclClasses = [
            ['name' => 'Baby Class', 'grade' => 'Baby Class', 'section' => ''],
            ['name' => 'Middle Class', 'grade' => 'Middle Class', 'section' => ''],
            ['name' => 'Reception', 'grade' => 'Reception', 'section' => ''],
        ];

        foreach ($eclClasses as $class) {
            SchoolClass::create([
                'name' => $class['name'],
                'department' => 'ECL',
                'grade' => $class['grade'],
                'section' => $class['section'],
                'is_active' => true,
            ]);
        }

        // Primary Department Classes (Grades 1-7)
        for ($grade = 1; $grade <= 7; $grade++) {
            SchoolClass::create([
                'name' => "Grade {$grade}",
                'department' => 'Primary',
                'grade' => "Grade {$grade}",
                'section' => '',
                'is_active' => true,
            ]);
        }

        // Secondary Department Classes
        $secondaryClasses = [
            // Form 1 (Grade 8)
            ['name' => 'Form 1 Archivers', 'grade' => 'Form 1', 'section' => 'Archivers'],
            ['name' => 'Form 1 Success', 'grade' => 'Form 1', 'section' => 'Success'],

            // Form 2 (Grade 9)
            ['name' => 'Form 2 Lilly', 'grade' => 'Form 2', 'section' => 'Lilly'],
            ['name' => 'Form 2 Lotus', 'grade' => 'Form 2', 'section' => 'Lotus'],

            // Form 3 (Grade 10)
            ['name' => 'Form 3 A', 'grade' => 'Form 3', 'section' => 'A'],
            ['name' => 'Form 3 B', 'grade' => 'Form 3', 'section' => 'B'],

            // Form 4 & 5 (Grade 11 & 12)
            ['name' => 'Form 4', 'grade' => 'Form 4', 'section' => ''],
            ['name' => 'Form 5', 'grade' => 'Form 5', 'section' => ''],
        ];

        foreach ($secondaryClasses as $class) {
            SchoolClass::create([
                'name' => $class['name'],
                'department' => 'Secondary',
                'grade' => $class['grade'],
                'section' => $class['section'],
                'is_active' => true,
            ]);
        }

        $this->command->info('Successfully seeded ' . SchoolClass::count() . ' classes!');
    }
}
