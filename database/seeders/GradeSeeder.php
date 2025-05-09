<?php

namespace Database\Seeders;

use App\Models\Grade;
use App\Models\SchoolSection;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Log;

class GradeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get the school sections (or create them if they don't exist)
        $preschool = SchoolSection::firstOrCreate(
            ['name' => 'Pre-School'],
            [
                'description' => 'Early childhood education for children aged 3-6 years',
                'is_active' => true,
            ]
        );

        $primary = SchoolSection::firstOrCreate(
            ['name' => 'Primary School'],
            [
                'description' => 'Primary education from Grade 1 to Grade 7',
                'is_active' => true,
            ]
        );

        $secondary = SchoolSection::firstOrCreate(
            ['name' => 'Secondary School'],
            [
                'description' => 'Secondary education from Grade 8 to Grade 12',
                'is_active' => true,
            ]
        );

        // Log the school section IDs for debugging
        Log::info('School Sections', [
            'Preschool ID' => $preschool->id,
            'Primary ID' => $primary->id,
            'Secondary ID' => $secondary->id
        ]);

        // Define preschool grades
        $preschoolGrades = [
            [
                'name' => 'Baby Class',
                'code' => 'BC',
                'level' => 1,
                'description' => 'First level of pre-school for children aged 3 years',
                'capacity' => 25,
                'breakeven_number' => 15,
                'is_active' => true,
            ],
            [
                'name' => 'Middle Class',
                'code' => 'MC',
                'level' => 2,
                'description' => 'Second level of pre-school for children aged 4 years',
                'capacity' => 25,
                'breakeven_number' => 15,
                'is_active' => true,
            ],
            [
                'name' => 'Reception',
                'code' => 'REC',
                'level' => 3,
                'description' => 'Final level of pre-school for children aged 5-6 years',
                'capacity' => 30,
                'breakeven_number' => 18,
                'is_active' => true,
            ],
        ];

        // Define primary school grades
        $primaryGrades = [];
        for ($i = 1; $i <= 7; $i++) {
            $primaryGrades[] = [
                'name' => "Grade $i",
                'code' => "G$i",
                'level' => $i + 3, // Continuing from preschool levels
                'description' => "Primary school Grade $i",
                'capacity' => 35,
                'breakeven_number' => 20,
                'is_active' => true,
            ];
        }

        // Define secondary school grades
        $secondaryGrades = [];
        for ($i = 8; $i <= 12; $i++) {
            $secondaryGrades[] = [
                'name' => "Grade $i",
                'code' => "G$i",
                'level' => $i + 3, // Continuing from primary levels
                'description' => "Secondary school Grade $i",
                'capacity' => 40,
                'breakeven_number' => 25,
                'is_active' => true,
            ];
        }

        // Create preschool grades
        foreach ($preschoolGrades as $gradeData) {
            Grade::updateOrCreate(
                [
                    'school_section_id' => $preschool->id,
                    'name' => $gradeData['name'],
                ],
                array_merge($gradeData, ['school_section_id' => $preschool->id])
            );
        }

        // Create primary school grades
        foreach ($primaryGrades as $gradeData) {
            Grade::updateOrCreate(
                [
                    'school_section_id' => $primary->id,
                    'name' => $gradeData['name'],
                ],
                array_merge($gradeData, ['school_section_id' => $primary->id])
            );
        }

        // Create secondary school grades
        foreach ($secondaryGrades as $gradeData) {
            Grade::updateOrCreate(
                [
                    'school_section_id' => $secondary->id,
                    'name' => $gradeData['name'],
                ],
                array_merge($gradeData, ['school_section_id' => $secondary->id])
            );
        }

        // Count grades created
        $totalGrades = count($preschoolGrades) + count($primaryGrades) + count($secondaryGrades);
        $this->command->info("Created $totalGrades grades across all school sections");
    }
}
