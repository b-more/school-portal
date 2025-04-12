<?php

namespace Database\Seeders;

use App\Models\Student;
use App\Models\User;
use App\Models\SchoolClass;
use App\Models\ParentGuardian;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class StudentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Clear existing students
        Student::truncate();

        // Get student users who don't have student records yet
        $studentUsers = User::where('email', 'like', '%.student@stfrancisofassisi.tech')
                            ->whereDoesntHave('student')
                            ->get();

        // Get all classes
        $classes = SchoolClass::all()->groupBy('department')->toArray();

        // Get all parents
        $parentGuardians = ParentGuardian::all();
        if ($parentGuardians->isEmpty()) {
            $this->command->warn('No parents/guardians found. Please run the ParentGuardianSeeder first.');
            return;
        }

        // Create 200 students distributed across classes
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

        // For each class, create a reasonable number of students
        foreach ($classes as $department => $depClasses) {
            foreach ($depClasses as $class) {
                // Different class sizes based on department
                $classSize = match($department) {
                    'ECL' => rand(15, 25),
                    'Primary' => rand(25, 35),
                    'Secondary' => rand(20, 30),
                    default => rand(20, 30),
                };

                for ($i = 1; $i <= $classSize && $createdCount < $studentCount; $i++) {
                    // Determine if we should use a student user (for older students)
                    $useStudentUser = $department === 'Secondary' && !$studentUsers->isEmpty() && rand(0, 3) === 0;
                    $user = null;

                    if ($useStudentUser) {
                        $user = $studentUsers->shift(); // Get and remove first user
                        $name = $user->name;
                    } else {
                        $name = $this->getRandomName();
                    }

                    // Get a random parent/guardian
                    $parent = $parentGuardians->random();

                    // Generate a birth date appropriate for the student's grade
                    $age = match($department) {
                        'ECL' => rand(3, 6),
                        'Primary' => rand(6, 13),
                        'Secondary' => rand(13, 19),
                        default => rand(6, 18),
                    };
                    $birthDate = Carbon::now()->subYears($age)->subDays(rand(0, 365));

                    // Generate a unique student ID
                    $studentId = strtoupper(substr($department, 0, 1)) . date('y') . str_pad($createdCount + 1, 4, '0', STR_PAD_LEFT);

                    // Create the student
                    $student = Student::create([
                        'name' => $name,
                        'user_id' => $user?->id,
                        'date_of_birth' => $birthDate,
                        'place_of_birth' => $birthPlaces[array_rand($birthPlaces)],
                        'religious_denomination' => $denominations[array_rand($denominations)],
                        'standard_of_education' => $class['grade'],
                        'smallpox_vaccination' => ['Yes', 'No', 'Not Sure'][rand(0, 2)],
                        'date_vaccinated' => rand(0, 1) ? $birthDate->copy()->addMonths(rand(1, 12)) : null,
                        'gender' => ['male', 'female'][rand(0, 1)],
                        'address' => $parent->address ?? 'Unknown',
                        'student_id_number' => $studentId,
                        'parent_guardian_id' => $parent->id,
                        'grade' => $class['grade'],
                        'admission_date' => Carbon::now()->subMonths(rand(1, 36)),
                        'enrollment_status' => 'active',
                        'previous_school' => rand(0, 5) > 0 ? $this->getRandomSchoolName() : null, // 5/6 chance to have previous school
                        'medical_information' => rand(0, 10) === 0 ? $this->getRandomMedicalInfo() : null, // 1/10 chance to have medical info
                        'notes' => rand(0, 10) === 0 ? $this->getRandomNotes() : null, // 1/10 chance to have notes
                    ]);

                    // Here you could add code to assign student to the class if you have a relationship table

                    $createdCount++;
                }
            }
        }

        $this->command->info('Successfully seeded ' . $createdCount . ' students!');
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
}
