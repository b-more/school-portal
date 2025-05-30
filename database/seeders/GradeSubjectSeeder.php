<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Grade;
use App\Models\Subject;
use App\Models\AcademicYear;

class GradeSubjectSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get the current academic year
        $currentAcademicYear = AcademicYear::where('is_active', true)->first();

        if (!$currentAcademicYear) {
            $this->command->error('No active academic year found. Please create an academic year first.');
            return;
        }

        $this->command->info('Assigning subjects to grades for academic year: ' . $currentAcademicYear->name);

        // Primary School Subjects (Baby Class to Grade 7)
        $primarySubjects = [
            'English Language',
            'Mathematics',
            'Integrated Science',
            'Social Studies',
            'Creative and Technology Studies (CTS)',
            'Zambian Languages',
            'Physical Education',
            'Religious Education',
            'Art',
            'Music',
        ];

        // Secondary School Subjects (Grades 8-12)
        $secondarySubjects = [
            // Core subjects
            'English',
            'Mathematics',
            'Science',
            'Social Studies',

            // Specialized subjects
            'Physics',
            'Chemistry',
            'Biology',
            'Geography',
            'History',
            'Civic Education',
            'Religious Education',
            'Physical Education',
            'Computer Studies',
            'Business Studies',
            'Accounting',
            'Home Economics',
            'Art',
            'Music',
            'French',
            'Technical Drawing',
            'Agriculture',
        ];

        // Create or get primary subjects
        $this->createSubjects($primarySubjects, 'Primary', $currentAcademicYear->id);

        // Create or get secondary subjects
        $this->createSubjects($secondarySubjects, 'Secondary', $currentAcademicYear->id);

        // Assign subjects to primary grades (Baby Class to Grade 7)
        $primaryGrades = Grade::whereIn('name', [
            'Baby Class', 'Middle Class', 'Reception',
            'Grade 1', 'Grade 2', 'Grade 3', 'Grade 4',
            'Grade 5', 'Grade 6', 'Grade 7'
        ])->get();

        foreach ($primaryGrades as $grade) {
            $subjects = Subject::where('grade_level', 'Primary')
                ->where('academic_year_id', $currentAcademicYear->id)
                ->get();

            foreach ($subjects as $subject) {
                // Check if already assigned
                if (!$grade->subjects()->where('subject_id', $subject->id)->exists()) {
                    $grade->subjects()->attach($subject->id, [
                        'is_mandatory' => in_array($subject->name, [
                            'English Language', 'Mathematics', 'Integrated Science', 'Social Studies'
                        ]),
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }

            $this->command->info("Assigned " . $subjects->count() . " subjects to " . $grade->name);
        }

        // Assign subjects to secondary grades (Grades 8-12)
        $secondaryGrades = Grade::whereIn('name', [
            'Grade 8', 'Grade 9', 'Grade 10', 'Grade 11', 'Grade 12'
        ])->get();

        foreach ($secondaryGrades as $grade) {
            // Core subjects for all secondary grades
            $coreSubjects = Subject::where('grade_level', 'Secondary')
                ->where('academic_year_id', $currentAcademicYear->id)
                ->whereIn('name', ['English', 'Mathematics', 'Science', 'Social Studies'])
                ->get();

            // Additional subjects based on grade level
            $additionalSubjects = collect();

            if (in_array($grade->name, ['Grade 8', 'Grade 9'])) {
                // Lower secondary - broader curriculum
                $additionalSubjects = Subject::where('grade_level', 'Secondary')
                    ->where('academic_year_id', $currentAcademicYear->id)
                    ->whereIn('name', [
                        'Geography', 'History', 'Civic Education', 'Religious Education',
                        'Physical Education', 'Computer Studies', 'Art', 'Music'
                    ])
                    ->get();
            } else {
                // Upper secondary - more specialized
                $additionalSubjects = Subject::where('grade_level', 'Secondary')
                    ->where('academic_year_id', $currentAcademicYear->id)
                    ->whereNotIn('name', ['English', 'Mathematics', 'Science', 'Social Studies'])
                    ->get();
            }

            $allSubjects = $coreSubjects->merge($additionalSubjects);

            foreach ($allSubjects as $subject) {
                // Check if already assigned
                if (!$grade->subjects()->where('subject_id', $subject->id)->exists()) {
                    $grade->subjects()->attach($subject->id, [
                        'is_mandatory' => $coreSubjects->contains($subject),
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }

            $this->command->info("Assigned " . $allSubjects->count() . " subjects to " . $grade->name);
        }

        $this->command->info('Grade-Subject assignments completed successfully!');
    }

    /**
     * Create subjects if they don't exist
     */
    private function createSubjects(array $subjectNames, string $gradeLevel, int $academicYearId): void
    {
        foreach ($subjectNames as $name) {
            Subject::firstOrCreate([
                'name' => $name,
                'academic_year_id' => $academicYearId,
            ], [
                'code' => $this->generateSubjectCode($name),
                'grade_level' => $gradeLevel,
                'description' => $name . ' for ' . $gradeLevel . ' level',
                'is_active' => true,
                'is_core' => in_array($name, [
                    'English Language', 'English', 'Mathematics', 'Integrated Science',
                    'Science', 'Social Studies'
                ]),
                'credit_hours' => 1,
                'weight' => 1.0,
            ]);
        }
    }

    /**
     * Generate a subject code based on the subject name
     */
    private function generateSubjectCode(string $name): string
    {
        // Remove common words and get initials
        $words = explode(' ', $name);
        $code = '';

        foreach ($words as $word) {
            if (!in_array(strtolower($word), ['and', 'the', 'of', 'for', 'studies', 'education'])) {
                $code .= strtoupper(substr($word, 0, 1));
            }
        }

        // Ensure minimum 2 characters
        if (strlen($code) < 2) {
            $code = strtoupper(substr($name, 0, 3));
        }

        // Add numbers if needed to make unique
        $baseCode = $code;
        $counter = 1;

        while (Subject::where('code', $code)->exists()) {
            $code = $baseCode . $counter;
            $counter++;
        }

        return $code;
    }
}
