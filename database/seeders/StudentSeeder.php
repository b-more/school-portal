<?php

namespace Database\Seeders;

use App\Models\Student;
use App\Models\User;
use App\Models\Role;
use App\Models\Grade;
use App\Models\ClassSection;
use App\Models\SchoolClass;
use App\Models\ParentGuardian;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

class StudentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get student role
        $studentRole = Role::where('name', 'Student')->first();
        if (!$studentRole) {
            $this->command->error('Student role not found. Please run RoleSeeder first.');
            return;
        }

        // Clear existing students
        Student::truncate();

        // Get student users who don't have student records yet
        $studentUsers = User::where('role_id', $studentRole->id)
                            ->whereDoesntHave('student')
                            ->get();

        // Get all grades with their IDs
        $grades = Grade::where('is_active', true)->get();
        if ($grades->isEmpty()) {
            $this->command->error('No grades found. Please run GradeSeeder first.');
            return;
        }

        // Get all parents
        $parentGuardians = ParentGuardian::all();
        if ($parentGuardians->isEmpty()) {
            $this->command->warn('No parents/guardians found. Please run the ParentGuardianSeeder first.');
            return;
        }

        // Create 200 students distributed across grades
        $studentCount = 200;
        $createdCount = 0;

        // Religious denominations common in Zambia
        $denominations = [
            'Catholic', 'Seventh-day Adventist', 'Anglican', 'Baptist', 'United Church of Zambia',
            'Pentecostal', 'Evangelical', 'Methodist', 'Lutheran', 'Reformed Church'
        ];

        // Birth places in Zambia
        $birthPlaces = [
            'Lusaka', 'Ndola', 'Kitwe', 'Kabwe', 'Chingola',
            'Mufulira', 'Livingstone', 'Chipata', 'Kasama', 'Mongu',
            'Solwezi', 'Mansa', 'Choma', 'Mazabuka', 'Kafue'
        ];

        // For backward compatibility with existing data, get some SchoolClass records
        $schoolClasses = SchoolClass::where('is_active', true)->get();

        // Distribute students across grades
        foreach ($grades as $grade) {
            // Get all active class sections for this grade
            $classSections = ClassSection::where('grade_id', $grade->id)
                                        ->where('is_active', true)
                                        ->get();

            // If no class sections exist for this grade, create a default one
            if ($classSections->isEmpty()) {
                $this->command->warn("No class sections found for {$grade->name}. Creating default section...");

                $defaultSection = ClassSection::create([
                    'grade_id' => $grade->id,
                    'academic_year_id' => \App\Models\AcademicYear::where('is_active', true)->first()?->id,
                    'name' => 'A',
                    'code' => "{$grade->code}-A",
                    'capacity' => 40,
                    'is_active' => true,
                ]);

                $classSections = collect([$defaultSection]);
            }

            // Different class sizes based on grade level
            $classSize = match($grade->level) {
                1,2,3 => rand(15, 25),         // ECL
                4,5,6,7,8,9,10 => rand(25, 35), // Primary
                11,12,13,14,15 => rand(25, 40), // Secondary
                default => rand(20, 30),
            };

            for ($i = 1; $i <= $classSize && $createdCount < $studentCount; $i++) {
                // Determine if we should use a student user (for older students)
                $useStudentUser = $grade->level >= 11 && !$studentUsers->isEmpty() && rand(0, 3) === 0;
                $user = null;

                if ($useStudentUser) {
                    $user = $studentUsers->shift(); // Get and remove first user
                    $name = $user->name;
                } else {
                    $name = $this->getRandomName();
                }

                // Get a random parent/guardian
                $parent = $parentGuardians->random();

                // Select a class section - distribute evenly across available sections
                $classSection = $classSections->sortBy(function($section) {
                    return $section->students()->count();
                })->first();

                // Generate a birth date appropriate for the student's grade level
                $baseAge = match($grade->level) {
                    1 => 3,   // Baby Class
                    2 => 4,   // Middle Class
                    3 => 5,   // Reception
                    4 => 6,   // Grade 1
                    5 => 7,   // Grade 2
                    6 => 8,   // Grade 3
                    7 => 9,   // Grade 4
                    8 => 10,  // Grade 5
                    9 => 11,  // Grade 6
                    10 => 12, // Grade 7
                    11 => 13, // Grade 8
                    12 => 14, // Grade 9
                    13 => 15, // Grade 10
                    14 => 16, // Grade 11
                    15 => 17, // Grade 12
                    default => 10,
                };

                $age = $baseAge + rand(0, 1); // Add 0-1 years variation
                $birthDate = Carbon::now()->subYears($age)->subDays(rand(0, 365));

                // Generate a unique student ID using the new format
                $gradeName = strtoupper(str_replace(' ', '', $grade->name));
                $prefix = substr($gradeName, 0, 3); // First 3 letters of grade name
                $studentId = $prefix . str_pad($createdCount + 1, 4, '0', STR_PAD_LEFT);

                // For backward compatibility, assign school_class_id if needed
                $schoolClassId = null;
                if ($schoolClasses->isNotEmpty()) {
                    // Try to match by grade and section, fallback to random
                    $schoolClass = $schoolClasses->where('grade', $grade->name)
                                                ->where('section', $classSection->name)
                                                ->first() ?? $schoolClasses->random();
                    $schoolClassId = $schoolClass->id;
                }

                // Create the student
                $student = Student::create([
                    'name' => $name,
                    'user_id' => $user?->id,
                    'date_of_birth' => $birthDate,
                    'place_of_birth' => $birthPlaces[array_rand($birthPlaces)],
                    'religious_denomination' => $denominations[array_rand($denominations)],
                    'standard_of_education' => $this->getStandardOfEducation($grade->level),
                    'smallpox_vaccination' => ['Yes', 'No', 'Not Sure'][rand(0, 2)],
                    'date_vaccinated' => rand(0, 1) ? $birthDate->copy()->addMonths(rand(1, 12)) : null,
                    'gender' => ['male', 'female'][rand(0, 1)],
                    'address' => $parent->address ?? $this->getRandomAddress(),
                    'student_id_number' => $studentId,
                    'parent_guardian_id' => $parent->id,
                    'grade_id' => $grade->id,
                    'class_section_id' => $classSection->id, // Assign to class section
                    'school_class_id' => $schoolClassId, // For backward compatibility
                    'admission_date' => Carbon::now()->subMonths(rand(1, 36)),
                    'enrollment_status' => 'active',
                    'previous_school' => rand(0, 5) > 0 ? $this->getRandomSchoolName() : null,
                    'medical_information' => rand(0, 10) === 0 ? $this->getRandomMedicalInfo() : null,
                    'notes' => rand(0, 10) === 0 ? $this->getRandomNotes() : null,
                ]);

                $createdCount++;

                // Show progress
                if ($createdCount % 50 === 0) {
                    $this->command->info("Created {$createdCount} students...");
                }

                // If the current section is getting full, rotate to next section
                if ($classSection->students()->count() >= $classSection->capacity) {
                    $classSections = $classSections->reject(function($section) use ($classSection) {
                        return $section->id === $classSection->id;
                    });

                    // If all sections are full, create a new one
                    if ($classSections->isEmpty()) {
                        $nextSectionName = $this->getNextSectionName($grade);
                        $newSection = ClassSection::create([
                            'grade_id' => $grade->id,
                            'academic_year_id' => \App\Models\AcademicYear::where('is_active', true)->first()?->id,
                            'name' => $nextSectionName,
                            'code' => "{$grade->code}-{$nextSectionName}",
                            'capacity' => 40,
                            'is_active' => true,
                        ]);

                        $classSections = collect([$newSection]);
                        $this->command->info("Created new section {$nextSectionName} for {$grade->name}");
                    }
                }
            }
        }

        $this->command->info('Successfully seeded ' . $createdCount . ' students!');
    }

    /**
     * Determine standard of education based on grade level
     */
    private function getStandardOfEducation(int $level): string
    {
        return match($level) {
            1,2,3 => 'Nursery',
            4,5,6,7,8,9,10 => 'Primary',
            11,12 => 'Junior Secondary',
            13,14,15 => 'Senior Secondary',
            default => 'Primary',
        };
    }

    /**
     * Generate a random name
     */
    private function getRandomName(): string
    {
        $firstNames = [
            'Chipo', 'Mulenga', 'Mutale', 'Bwalya', 'Chomba', 'Mwila', 'Nkonde', 'Musonda', 'Chilufya', 'Kalaba',
            'Mwamba', 'Chanda', 'Zulu', 'Tembo', 'Banda', 'Phiri', 'Mbewe', 'Lungu', 'Daka', 'Mumba',
            'Muleya', 'Ngoma', 'Ngulube', 'Sinkala', 'Ng\'andu', 'Kalumba', 'Mwansa', 'Kabwe', 'Bupe', 'Mwape',
            'Emmanuel', 'Gift', 'Blessing', 'Grace', 'Faith', 'Hope', 'Joseph', 'Mary', 'John', 'David'
        ];

        $lastNames = [
            'Mwila', 'Banda', 'Phiri', 'Mbewe', 'Zulu', 'Tembo', 'Chanda', 'Mutale', 'Bwalya', 'Musonda',
            'Daka', 'Mulenga', 'Mumba', 'Ngoma', 'Ngulube', 'Sinkala', 'Ng\'andu', 'Kalumba', 'Chisenga', 'Mwansa',
            'Mwape', 'Kabwe', 'Muleya', 'Kalaba', 'Chikwanda', 'Chilufya', 'Nkonde', 'Chisanga', 'Siame', 'Mofya'
        ];

        return $firstNames[array_rand($firstNames)] . ' ' . $lastNames[array_rand($lastNames)];
    }

    /**
     * Generate a random school name
     */
    private function getRandomSchoolName(): string
    {
        $prefixes = [
            'St.', 'Holy', 'Sacred', 'Our Lady of', 'Blessed', 'Mount', 'New', 'Central', 'United', 'International',
            'Hillside', 'Riverside', 'Modern', 'Golden', 'Green', 'Community', 'Valley', 'Horizon', 'Trinity', 'Royal'
        ];

        $names = [
            'Francis', 'Mary', 'Joseph', 'Peter', 'Paul', 'Anne', 'Teresa', 'Michael', 'Rose', 'Patrick',
            'Hope', 'Faith', 'Victory', 'Progress', 'Excellence', 'Success', 'Future', 'Vision', 'Glory', 'Light'
        ];

        $suffixes = [
            'Primary School', 'Basic School', 'Secondary School', 'Academy', 'College', 'International School',
            'School', 'Christian School', 'Community School', 'Institute'
        ];

        $usePrefix = rand(0, 1);
        $useSuffix = rand(0, 1) || !$usePrefix;

        $school = '';
        if ($usePrefix) {
            $school .= $prefixes[array_rand($prefixes)] . ' ';
        }

        $school .= $names[array_rand($names)];

        if ($useSuffix) {
            $school .= ' ' . $suffixes[array_rand($suffixes)];
        }

        return $school;
    }

    /**
     * Generate random medical information
     */
    private function getRandomMedicalInfo(): string
    {
        $medicalInfos = [
            'Mild asthma, requires inhaler during physical activities.',
            'Allergic to peanuts. Epipen available in school clinic.',
            'Wears prescription glasses for myopia.',
            'Has Type 1 diabetes. Checks blood sugar levels during lunch break.',
            'Hay fever during spring season.',
            'Mild eczema. Has prescribed cream if needed.',
            'Mild hearing impairment in left ear. Prefers to sit at the front of class.',
            'Takes medication for ADHD before school.',
            'Allergic to bee stings. Emergency medication in school clinic.',
            'Occasional migraines. May need to rest in quiet area.',
        ];

        return $medicalInfos[array_rand($medicalInfos)];
    }

    /**
     * Generate random notes
     */
    private function getRandomNotes(): string
    {
        $notes = [
            'Excellent at mathematics. Consider advanced placement.',
            'Very talented in art. Works displayed in school gallery.',
            'Requires additional support in English language.',
            'Active participant in school choir and drama club.',
            'Excels in sports, particularly football/netball.',
            'Sometimes struggles with punctuality. Parents informed.',
            'Has shown great improvement in behavior and academics this term.',
            'Parent requested extra homework in science subjects.',
            'Preparing for national mathematics competition.',
            'Recently transferred from another school. Still adjusting.',
        ];

        return $notes[array_rand($notes)];
    }

    /**
     * Generate random address
     */
    private function getRandomAddress(): string
    {
        $areas = [
            'Kabwata', 'Garden', 'Lusaka South', 'Chilenje', 'Matero',
            'Kaunda Square', 'Woodlands', 'Roma', 'Kamwala', 'Avondale',
            'Rhodes Park', 'New Kasama', 'Bauleni', 'Mandevu', 'Libala'
        ];

        $streetTypes = ['Road', 'Avenue', 'Drive', 'Street', 'Lane', 'Crescent'];
        $houseNumber = rand(1, 999);
        $streetName = $this->getRandomName();
        $area = $areas[array_rand($areas)];

        return "{$houseNumber} {$streetName} {$streetTypes[array_rand($streetTypes)]}, {$area}, Lusaka";
    }

    /**
     * Get next section name
     */
    private function getNextSectionName(Grade $grade): string
    {
        $existingSections = ClassSection::where('grade_id', $grade->id)
                                      ->pluck('name')
                                      ->toArray();

        $alphabet = ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z'];

        foreach ($alphabet as $letter) {
            if (!in_array($letter, $existingSections)) {
                return $letter;
            }
        }

        // If we've used all letters, start with double letters
        return 'AA';
    }
}
