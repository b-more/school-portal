<?php

namespace Database\Seeders;

use App\Models\Subject;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class SubjectSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Clear existing subjects to avoid duplicates
        Subject::truncate();

        // --- ECL Department Subjects ---
        $eclGrades = [
            'Baby Class',
            'Middle Class',
            'Reception'
        ];

        $eclSubjects = [
            [
                'name' => 'Literacy Activities',
                'description' => 'Early reading and writing skills development through play-based activities.'
            ],
            [
                'name' => 'Numeracy Activities',
                'description' => 'Introduction to numbers, counting, shapes, and basic math concepts.'
            ],
            [
                'name' => 'Creative Arts',
                'description' => 'Drawing, coloring, craft making, and other artistic activities.'
            ],
            [
                'name' => 'Environmental Activities',
                'description' => 'Exploration of the natural world, plants, animals, and environment.'
            ],
            [
                'name' => 'Social Skills',
                'description' => 'Activities focused on sharing, taking turns, and cooperating with others.'
            ],
            [
                'name' => 'Physical Development',
                'description' => 'Movement, coordination, and fine/gross motor skills development.'
            ],
        ];

        foreach ($eclGrades as $grade) {
            foreach ($eclSubjects as $subject) {
                $this->createSubject($subject['name'], $subject['description'], $grade, 'ECL');
            }
        }

        // --- Primary Department Subjects (Grades 1-7) ---
        $primarySubjects = [
            [
                'name' => 'English',
                'description' => 'Reading, writing, grammar, vocabulary, and communication skills.'
            ],
            [
                'name' => 'Mathematics',
                'description' => 'Numbers, operations, measurements, geometry, and problem-solving.'
            ],
            [
                'name' => 'Science',
                'description' => 'Basic scientific concepts, experiments, and understanding of the natural world.'
            ],
            [
                'name' => 'Social Studies',
                'description' => 'History, geography, civics, and cultural understanding.'
            ],
            [
                'name' => 'Religious Education',
                'description' => 'Moral values, religious teachings, and ethical principles.'
            ],
            [
                'name' => 'Art & Craft',
                'description' => 'Creative expression through various artistic mediums and craft projects.'
            ],
            [
                'name' => 'Music',
                'description' => 'Singing, rhythm, musical instruments, and appreciation of different music styles.'
            ],
            [
                'name' => 'Physical Education',
                'description' => 'Sports, games, physical fitness, and health education.'
            ],
            [
                'name' => 'Computer Studies',
                'description' => 'Basic computer skills, digital literacy, and responsible technology use.'
            ],
            [
                'name' => 'Zambian Languages',
                'description' => 'Local language instruction focusing on reading, writing, and cultural context.'
            ],
        ];

        for ($grade = 1; $grade <= 7; $grade++) {
            $gradeLevel = "Grade {$grade}";
            foreach ($primarySubjects as $subject) {
                $this->createSubject($subject['name'], $subject['description'], $gradeLevel, 'Primary');
            }
        }

        // --- Secondary Department Subjects (Grades 8-12) ---
        $secondaryCore = [
            [
                'name' => 'English Language',
                'description' => 'Advanced reading, writing, comprehension, literature, and communication skills.'
            ],
            [
                'name' => 'Mathematics',
                'description' => 'Algebra, geometry, statistics, calculus, and advanced problem-solving.'
            ],
            [
                'name' => 'Integrated Science',
                'description' => 'Combined biology, chemistry, and physics concepts for junior secondary.'
            ],
            [
                'name' => 'Social Studies',
                'description' => 'History, geography, civics, and social issues for junior secondary.'
            ],
            [
                'name' => 'Religious Education',
                'description' => 'Study of religious principles, ethics, philosophy, and world religions.'
            ],
            [
                'name' => 'Computer Studies',
                'description' => 'Computer applications, basic programming, and information technology.'
            ],
            [
                'name' => 'Business Studies',
                'description' => 'Introduction to business concepts, entrepreneurship, and commerce.'
            ],
            [
                'name' => 'Physical Education',
                'description' => 'Sports, fitness, health education, and physical well-being.'
            ],
            [
                'name' => 'Art & Design',
                'description' => 'Visual arts, design principles, and creative expression.'
            ],
            [
                'name' => 'Home Economics',
                'description' => 'Cooking, nutrition, textile work, and home management skills.'
            ],
        ];

        // Junior Secondary (8-9)
        for ($grade = 8; $grade <= 9; $grade++) {
            $gradeLevel = "Grade {$grade}";
            foreach ($secondaryCore as $subject) {
                $this->createSubject($subject['name'], $subject['description'], $gradeLevel, 'Secondary');
            }
        }

        // Senior Secondary (10-12) - More specialized subjects
        $seniorScience = [
            [
                'name' => 'Biology',
                'description' => 'Study of living organisms, their structure, function, growth, and evolution.'
            ],
            [
                'name' => 'Chemistry',
                'description' => 'Study of matter, its properties, composition, structure, and reactions.'
            ],
            [
                'name' => 'Physics',
                'description' => 'Study of matter, energy, and the interactions between them.'
            ],
            [
                'name' => 'Additional Mathematics',
                'description' => 'Advanced mathematical concepts for science and engineering paths.'
            ],
            [
                'name' => 'Agricultural Science',
                'description' => 'Study of agriculture, farming practices, and agricultural technology.'
            ],
        ];

        $seniorArts = [
            [
                'name' => 'History',
                'description' => 'Study of past events, societies, civilizations, and their impact.'
            ],
            [
                'name' => 'Geography',
                'description' => 'Study of physical features of the earth and human societies.'
            ],
            [
                'name' => 'Literature in English',
                'description' => 'Study of novels, poetry, drama, and other literary forms.'
            ],
            [
                'name' => 'Civics',
                'description' => 'Study of citizenship, government, rights, and responsibilities.'
            ],
        ];

        $seniorCommerce = [
            [
                'name' => 'Principles of Accounts',
                'description' => 'Fundamentals of accounting, financial statements, and business records.'
            ],
            [
                'name' => 'Commerce',
                'description' => 'Study of business operations, trade, and commercial activities.'
            ],
            [
                'name' => 'Economics',
                'description' => 'Study of production, distribution, and consumption of goods and services.'
            ],
            [
                'name' => 'Computer Science',
                'description' => 'Advanced programming, algorithms, data structures, and software development.'
            ],
        ];

        // Core subjects for Senior Secondary (10-12)
        $seniorCore = [
            [
                'name' => 'English Language',
                'description' => 'Advanced reading, writing, comprehension, literature, and communication skills.'
            ],
            [
                'name' => 'Mathematics',
                'description' => 'Algebra, geometry, statistics, calculus, and advanced problem-solving.'
            ],
            [
                'name' => 'Religious Education',
                'description' => 'Study of religious principles, ethics, philosophy, and world religions.'
            ],
        ];

        for ($grade = 10; $grade <= 12; $grade++) {
            $gradeLevel = "Grade {$grade}";

            // Core subjects
            foreach ($seniorCore as $subject) {
                $this->createSubject($subject['name'], $subject['description'], $gradeLevel, 'Secondary');
            }

            // Science stream subjects
            foreach ($seniorScience as $subject) {
                $this->createSubject($subject['name'], $subject['description'], $gradeLevel, 'Secondary');
            }

            // Arts stream subjects
            foreach ($seniorArts as $subject) {
                $this->createSubject($subject['name'], $subject['description'], $gradeLevel, 'Secondary');
            }

            // Commerce stream subjects
            foreach ($seniorCommerce as $subject) {
                $this->createSubject($subject['name'], $subject['description'], $gradeLevel, 'Secondary');
            }
        }

        $this->command->info('Successfully seeded ' . Subject::count() . ' subjects across all grade levels!');
    }

    /**
     * Create a new subject with the given parameters
     */
    private function createSubject(string $name, string $description, string $gradeLevel, string $department): void
    {
        // Generate code based on name, department and grade level
        $namePrefix = Str::upper(Str::substr($name, 0, 3));
        $deptPrefix = Str::substr($department, 0, 1);
        $gradeNum = preg_replace('/\D/', '', $gradeLevel) ?: substr($gradeLevel, 0, 1);
        $code = "{$namePrefix}-{$deptPrefix}{$gradeNum}";

        // Ensure code is unique by adding a suffix if needed
        $counter = 1;
        $originalCode = $code;
        while (Subject::where('code', $code)->exists()) {
            $code = $originalCode . '-' . $counter;
            $counter++;
        }

        Subject::create([
            'name' => $name,
            'code' => $code,
            'description' => $description,
            'grade_level' => $gradeLevel,
            'department' => $department,
            'is_active' => true,
        ]);
    }
}
