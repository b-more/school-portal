<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Subject;
use Illuminate\Support\Facades\DB;

class SubjectSeeder extends Seeder
{
    /**
     * Run the database seeds to create default subjects.
     */
    public function run(): void
    {
        $this->command->info('Starting subject seeding...');

        // Clear existing subjects if needed
        // Uncomment this line to remove all existing subjects before seeding
        // DB::table('subjects')->truncate();

        $subjects = [
            // ECL Subjects
            [
                'name' => 'Numeracy',
                'code' => 'NUM-ECL',
                'grade_level' => 'ECL',
                'description' => 'Basic number skills and concepts for early childhood learners',
                'is_active' => true,
            ],
            [
                'name' => 'Literacy',
                'code' => 'LIT-ECL',
                'grade_level' => 'ECL',
                'description' => 'Reading and writing skills for early childhood learners',
                'is_active' => true,
            ],
            [
                'name' => 'Life Skills',
                'code' => 'LSK-ECL',
                'grade_level' => 'ECL',
                'description' => 'Practical skills for everyday life for early childhood learners',
                'is_active' => true,
            ],
            [
                'name' => 'Creative Arts',
                'code' => 'ART-ECL',
                'grade_level' => 'ECL',
                'description' => 'Artistic expression for early childhood learners',
                'is_active' => true,
            ],

            // Primary Subjects
            [
                'name' => 'Mathematics',
                'code' => 'MATH-PRI',
                'grade_level' => 'Primary',
                'description' => 'Mathematics for primary school students',
                'is_active' => true,
            ],
            [
                'name' => 'English',
                'code' => 'ENG-PRI',
                'grade_level' => 'Primary',
                'description' => 'English language and literature for primary school students',
                'is_active' => true,
            ],
            [
                'name' => 'Science',
                'code' => 'SCI-PRI',
                'grade_level' => 'Primary',
                'description' => 'Basic scientific concepts for primary school students',
                'is_active' => true,
            ],
            [
                'name' => 'Social Studies',
                'code' => 'SOC-PRI',
                'grade_level' => 'Primary',
                'description' => 'Social studies and history for primary school students',
                'is_active' => true,
            ],
            [
                'name' => 'Art',
                'code' => 'ART-PRI',
                'grade_level' => 'Primary',
                'description' => 'Art and creative expression for primary school students',
                'is_active' => true,
            ],
            [
                'name' => 'Physical Education',
                'code' => 'PE-PRI',
                'grade_level' => 'Primary',
                'description' => 'Physical education and sports for primary school students',
                'is_active' => true,
            ],

            // Secondary Subjects
            [
                'name' => 'Mathematics',
                'code' => 'MATH-SEC',
                'grade_level' => 'Secondary',
                'description' => 'Advanced mathematics for secondary school students',
                'is_active' => true,
            ],
            [
                'name' => 'English Language',
                'code' => 'ENG-SEC',
                'grade_level' => 'Secondary',
                'description' => 'English language and literature for secondary school students',
                'is_active' => true,
            ],
            [
                'name' => 'Physics',
                'code' => 'PHY-SEC',
                'grade_level' => 'Secondary',
                'description' => 'Physics concepts and principles for secondary school students',
                'is_active' => true,
            ],
            [
                'name' => 'Chemistry',
                'code' => 'CHEM-SEC',
                'grade_level' => 'Secondary',
                'description' => 'Chemistry concepts and principles for secondary school students',
                'is_active' => true,
            ],
            [
                'name' => 'Biology',
                'code' => 'BIO-SEC',
                'grade_level' => 'Secondary',
                'description' => 'Biology concepts and principles for secondary school students',
                'is_active' => true,
            ],
            [
                'name' => 'History',
                'code' => 'HIST-SEC',
                'grade_level' => 'Secondary',
                'description' => 'History for secondary school students',
                'is_active' => true,
            ],
            [
                'name' => 'Geography',
                'code' => 'GEO-SEC',
                'grade_level' => 'Secondary',
                'description' => 'Geography for secondary school students',
                'is_active' => true,
            ],
            [
                'name' => 'Computer Studies',
                'code' => 'COMP-SEC',
                'grade_level' => 'Secondary',
                'description' => 'Computer science and ICT for secondary school students',
                'is_active' => true,
            ],

            // All Levels Subjects
            [
                'name' => 'Religious Education',
                'code' => 'RE-ALL',
                'grade_level' => 'All',
                'description' => 'Religious education for all grade levels',
                'is_active' => true,
            ],
            [
                'name' => 'Music',
                'code' => 'MUS-ALL',
                'grade_level' => 'All',
                'description' => 'Music education for all grade levels',
                'is_active' => true,
            ],
            [
                'name' => 'Physical Education',
                'code' => 'PE-ALL',
                'grade_level' => 'All',
                'description' => 'Physical education for all grade levels',
                'is_active' => true,
            ],
        ];

        $count = 0;

        foreach ($subjects as $subjectData) {
            // Check if subject already exists
            $exists = Subject::where('name', $subjectData['name'])
                ->where('grade_level', $subjectData['grade_level'])
                ->exists();

            if (!$exists) {
                Subject::create($subjectData);
                $count++;
            }
        }

        $this->command->info("Subject seeding completed. Added $count new subjects.");
    }
}
