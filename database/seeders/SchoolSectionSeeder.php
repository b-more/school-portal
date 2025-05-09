<?php

namespace Database\Seeders;

use App\Models\SchoolSection;
use Illuminate\Database\Seeder;

class SchoolSectionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Define the school sections
        $sections = [
            [
                'name' => 'Pre-School',
                'description' => 'Early childhood education for children aged 3-6 years',
                'is_active' => true,
            ],
            [
                'name' => 'Primary School',
                'description' => 'Primary education from Grade 1 to Grade 7',
                'is_active' => true,
            ],
            [
                'name' => 'Secondary School',
                'description' => 'Secondary education from Grade 8 to Grade 12',
                'is_active' => true,
            ],
        ];

        // Create or update each section
        foreach ($sections as $sectionData) {
            SchoolSection::updateOrCreate(
                ['name' => $sectionData['name']],
                $sectionData
            );
        }

        $this->command->info('School sections created successfully');
    }
}
