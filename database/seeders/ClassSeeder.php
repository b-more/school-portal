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
            ['name' => 'Baby Class A', 'grade' => 'Baby Class', 'section' => 'A'],
            ['name' => 'Baby Class B', 'grade' => 'Baby Class', 'section' => 'B'],
            ['name' => 'Middle Class A', 'grade' => 'Middle Class', 'section' => 'A'],
            ['name' => 'Middle Class B', 'grade' => 'Middle Class', 'section' => 'B'],
            ['name' => 'Reception A', 'grade' => 'Reception', 'section' => 'A'],
            ['name' => 'Reception B', 'grade' => 'Reception', 'section' => 'B'],
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
            // Create 2 sections (A & B) for each grade
            foreach (['A', 'B'] as $section) {
                SchoolClass::create([
                    'name' => "Grade {$grade}{$section}",
                    'department' => 'Primary',
                    'grade' => "Grade {$grade}",
                    'section' => $section,
                    'is_active' => true,
                ]);
            }
        }

        // Secondary Department Classes (Grades 8-12)
        for ($grade = 8; $grade <= 12; $grade++) {
            // Create multiple sections for each grade based on streams for higher grades
            if ($grade < 10) {
                // Junior secondary (grades 8-9): just A and B sections
                $sections = ['A', 'B'];
            } else {
                // Senior secondary (grades 10-12): divided by academic streams
                $sections = ['Science', 'Arts', 'Commerce'];
            }

            foreach ($sections as $section) {
                SchoolClass::create([
                    'name' => "Grade {$grade} {$section}",
                    'department' => 'Secondary',
                    'grade' => "Grade {$grade}",
                    'section' => $section,
                    'is_active' => true,
                ]);
            }
        }

        $this->command->info('Successfully seeded ' . SchoolClass::count() . ' classes!');
    }
}
