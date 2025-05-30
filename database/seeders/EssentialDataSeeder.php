<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Role;
use App\Models\User;
use App\Models\Employee;
use App\Models\Teacher;
use App\Models\Grade;
use App\Models\Subject;
use App\Models\AcademicYear;
use App\Models\Term;
use App\Models\FeeStructure;
use App\Models\FeeComponent;
use App\Models\ClassSection;
use App\Models\SchoolClass;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class EssentialDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('🚀 Seeding essential data for the system...');

        // 1. Create Roles (Admin, Teacher, Student, Parent, etc.)
        $this->seedRoles();

        // 2. Create Admin Account
        $this->seedAdminUser();

        // 3. Create Academic Years
        $this->seedAcademicYears();

        // 4. Create Terms
        $this->seedTerms();

        // 5. Create Grades
        $this->seedGrades();

        // 6. Create Class Sections
        $this->seedClassSections();

        // 7. Create School Classes (NEW)
        $this->seedSchoolClasses();

        // 8. Create Subjects
        $this->seedSubjects();

        // 9. Assign subjects to grades
        $this->assignSubjectsToGrades();

        // 10. Create Sample Teachers (NEW)
        $this->seedSampleTeachers();

        // 11. Assign Teachers to Classes (NEW)
        $this->assignTeachersToClasses();

        // 12. Create Fee Structures
        $this->seedFeeStructures();

        $this->command->info('🎉 All essential data has been seeded successfully!');
        $this->displaySummary();
    }

    /**
     * Seed roles
     */
    private function seedRoles(): void
    {
        $this->command->info('📋 Creating roles...');

        $roles = [
            ['id' => 1, 'name' => 'Admin', 'description' => 'Full access to all system features'],
            ['id' => 2, 'name' => 'Teacher', 'description' => 'Access to teaching and student management'],
            ['id' => 3, 'name' => 'Student', 'description' => 'Access to student portal'],
            ['id' => 4, 'name' => 'Parent', 'description' => 'Access to parent portal'],
            ['id' => 5, 'name' => 'Accountant', 'description' => 'Access to financial features'],
            ['id' => 6, 'name' => 'Nurse', 'description' => 'Access to health records'],
            ['id' => 7, 'name' => 'Librarian', 'description' => 'Access to library management'],
            ['id' => 8, 'name' => 'Security', 'description' => 'Access to security features'],
            ['id' => 9, 'name' => 'Support', 'description' => 'Access to support features'],
        ];

        foreach ($roles as $role) {
            Role::updateOrCreate(
                ['id' => $role['id']],
                $role
            );
        }

        $this->command->info('✅ Roles created: ' . count($roles));
    }

    /**
     * Seed admin user
     */
    private function seedAdminUser(): void
    {
        $this->command->info('👤 Creating admin user...');

        $adminRole = Role::where('name', 'Admin')->first();

        if (!$adminRole) {
            $this->command->error('Admin role not found. Roles must be seeded first.');
            return;
        }

        // Create admin user
        $admin = User::updateOrCreate(
            ['email' => 'admin@stfrancisofassisi.tech'],
            [
                'role_id' => $adminRole->id,
                'name' => 'System Administrator',
                'username' => 'admin',
                'email_verified_at' => now(),
                'password' => Hash::make('password'), // Change this to a secure password
                'remember_token' => Str::random(10),
                'status' => 'active',
                'phone' => '+260971234567',
                'phone_verified_at' => now(),
            ]
        );

        // Create Employee record for Admin
        Employee::updateOrCreate(
            ['user_id' => $admin->id],
            [
                'name' => $admin->name,
                'email' => $admin->email,
                'phone' => $admin->phone,
                'role_id' => $adminRole->id,
                'employee_id' => 'EMP001',
                'status' => 'active',
                'department' => 'Administration',
                'position' => 'System Administrator',
                'joining_date' => now(),
            ]
        );

        $this->command->info('✅ Admin user created: admin@stfrancisofassisi.tech / password');
    }

    /**
     * Seed academic years
     */
    private function seedAcademicYears(): void
    {
        $this->command->info('📅 Creating academic years...');

        $years = [
            [
                'name' => '2024-2025',
                'start_date' => '2024-01-08',
                'end_date' => '2024-12-20',
                'is_active' => true,
            ],
            [
                'name' => '2025-2026',
                'start_date' => '2025-01-06',
                'end_date' => '2025-12-19',
                'is_active' => false,
            ],
            [
                'name' => '2023-2024',
                'start_date' => '2023-01-09',
                'end_date' => '2023-12-15',
                'is_active' => false,
            ],
        ];

        foreach ($years as $year) {
            AcademicYear::updateOrCreate(
                ['name' => $year['name']],
                $year
            );
        }

        $this->command->info('✅ Academic years created: ' . count($years));
    }

    /**
     * Seed terms
     */
    private function seedTerms(): void
    {
        $this->command->info('📚 Creating terms...');

        $academicYear = AcademicYear::where('name', '2024-2025')->first();

        if (!$academicYear) {
            $this->command->error('Academic year not found.');
            return;
        }

        $terms = [
            [
                'name' => 'Term 1',
                'academic_year_id' => $academicYear->id,
                'start_date' => '2024-01-08',
                'end_date' => '2024-04-05',
                'is_current' => true,
            ],
            [
                'name' => 'Term 2',
                'academic_year_id' => $academicYear->id,
                'start_date' => '2024-05-06',
                'end_date' => '2024-08-09',
                'is_current' => false,
            ],
            [
                'name' => 'Term 3',
                'academic_year_id' => $academicYear->id,
                'start_date' => '2024-09-09',
                'end_date' => '2024-12-20',
                'is_current' => false,
            ],
        ];

        foreach ($terms as $term) {
            Term::updateOrCreate(
                ['name' => $term['name'], 'academic_year_id' => $term['academic_year_id']],
                $term
            );
        }

        $this->command->info('✅ Terms created: ' . count($terms));
    }

    /**
     * Seed grades
     */
    private function seedGrades(): void
    {
        $this->command->info('🎓 Creating grades...');

        $hasIsActiveField = Schema::hasColumn('grades', 'is_active');

        $grades = [
            ['name' => 'Baby Class', 'level' => 1, 'description' => 'ECL - Baby Class'],
            ['name' => 'Middle Class', 'level' => 2, 'description' => 'ECL - Middle Class'],
            ['name' => 'Reception', 'level' => 3, 'description' => 'ECL - Reception'],
            ['name' => 'Grade 1', 'level' => 4, 'description' => 'Primary - Grade 1'],
            ['name' => 'Grade 2', 'level' => 5, 'description' => 'Primary - Grade 2'],
            ['name' => 'Grade 3', 'level' => 6, 'description' => 'Primary - Grade 3'],
            ['name' => 'Grade 4', 'level' => 7, 'description' => 'Primary - Grade 4'],
            ['name' => 'Grade 5', 'level' => 8, 'description' => 'Primary - Grade 5'],
            ['name' => 'Grade 6', 'level' => 9, 'description' => 'Primary - Grade 6'],
            ['name' => 'Grade 7', 'level' => 10, 'description' => 'Primary - Grade 7'],
            ['name' => 'Grade 8', 'level' => 11, 'description' => 'Secondary - Grade 8'],
            ['name' => 'Grade 9', 'level' => 12, 'description' => 'Secondary - Grade 9'],
            ['name' => 'Grade 10', 'level' => 13, 'description' => 'Secondary - Grade 10'],
            ['name' => 'Grade 11', 'level' => 14, 'description' => 'Secondary - Grade 11'],
            ['name' => 'Grade 12', 'level' => 15, 'description' => 'Secondary - Grade 12'],
        ];

        // Add is_active field only if it exists
        if ($hasIsActiveField) {
            foreach ($grades as &$grade) {
                $grade['is_active'] = true;
            }
        }

        foreach ($grades as $grade) {
            Grade::updateOrCreate(
                ['name' => $grade['name']],
                $grade
            );
        }

        $this->command->info('✅ Grades created: ' . count($grades));
    }

    /**
     * Seed class sections
     */
    private function seedClassSections(): void
    {
        $this->command->info('🏫 Creating class sections...');

        $academicYear = AcademicYear::where('name', '2024-2025')->first();

        if (!$academicYear) {
            $this->command->error('Academic year not found.');
            return;
        }

        $grades = Grade::all();
        $hasIsActiveField = Schema::hasColumn('class_sections', 'is_active');
        $createdSections = 0;

        foreach ($grades as $grade) {
            // Create sections based on grade level
            $sections = $this->getSectionsForGrade($grade->name);

            foreach ($sections as $section) {
                $data = [
                    'name' => $section,
                    'grade_id' => $grade->id,
                    'academic_year_id' => $academicYear->id,
                    'capacity' => $this->getCapacityForGrade($grade->name),
                    'description' => "{$grade->name} Section {$section}",
                ];

                // Add is_active field only if it exists
                if ($hasIsActiveField) {
                    $data['is_active'] = true;
                }

                ClassSection::updateOrCreate(
                    ['name' => $section, 'grade_id' => $grade->id, 'academic_year_id' => $academicYear->id],
                    $data
                );
                $createdSections++;
            }
        }

        $this->command->info('✅ Class sections created: ' . $createdSections);
    }

    /**
     * Get sections for each grade
     */
    private function getSectionsForGrade(string $gradeName): array
    {
        // ECL classes have fewer sections
        if (in_array($gradeName, ['Baby Class', 'Middle Class', 'Reception'])) {
            return ['A'];
        }

        // Primary classes have more sections
        if (in_array($gradeName, ['Grade 1', 'Grade 2', 'Grade 3', 'Grade 4', 'Grade 5', 'Grade 6', 'Grade 7'])) {
            return ['A', 'B'];
        }

        // Secondary classes
        return ['A', 'B'];
    }

    /**
     * Get capacity for each grade
     */
    private function getCapacityForGrade(string $gradeName): int
    {
        if (in_array($gradeName, ['Baby Class', 'Middle Class', 'Reception'])) {
            return 25; // Smaller ECL classes
        }

        if (in_array($gradeName, ['Grade 1', 'Grade 2', 'Grade 3', 'Grade 4'])) {
            return 35; // Lower primary
        }

        if (in_array($gradeName, ['Grade 5', 'Grade 6', 'Grade 7'])) {
            return 40; // Upper primary
        }

        return 45; // Secondary classes
    }

    /**
     * Seed school classes (NEW)
     */
    private function seedSchoolClasses(): void
    {
        $this->command->info('🏫 Creating school classes...');

        $classSections = ClassSection::with('grade')->get();
        $createdClasses = 0;

        foreach ($classSections as $classSection) {
            $department = $this->getDepartmentByGrade($classSection->grade->name);

            SchoolClass::updateOrCreate(
                [
                    'name' => $classSection->grade->name . ' ' . $classSection->name,
                    'grade' => $classSection->grade->name,
                    'section' => $classSection->name,
                ],
                [
                    'department' => $department,
                    'is_active' => true,
                    'status' => 'active',
                ]
            );
            $createdClasses++;
        }

        $this->command->info('✅ School classes created: ' . $createdClasses);
    }

    /**
     * Get department based on grade name
     */
    private function getDepartmentByGrade(string $gradeName): string
    {
        if (in_array($gradeName, ['Baby Class', 'Middle Class', 'Reception'])) {
            return 'ECL';
        }

        if (in_array($gradeName, ['Grade 1', 'Grade 2', 'Grade 3', 'Grade 4', 'Grade 5', 'Grade 6', 'Grade 7'])) {
            return 'Primary';
        }

        return 'Secondary';
    }

    /**
     * Seed subjects
     */
    private function seedSubjects(): void
    {
        $this->command->info('📖 Creating subjects...');

        $currentAcademicYear = AcademicYear::where('is_active', true)->first();

        if (!$currentAcademicYear) {
            $this->command->error('No active academic year found.');
            return;
        }

        $hasIsActiveField = Schema::hasColumn('subjects', 'is_active');
        $hasGradeLevelField = Schema::hasColumn('subjects', 'grade_level');
        $hasAcademicYearField = Schema::hasColumn('subjects', 'academic_year_id');
        $hasIsCoreField = Schema::hasColumn('subjects', 'is_core');
        $hasCreditHoursField = Schema::hasColumn('subjects', 'credit_hours');
        $hasWeightField = Schema::hasColumn('subjects', 'weight');

        // Primary School Subjects (Baby Class to Grade 7)
        $primarySubjects = [
            'English Language' => ['code' => 'ENGP', 'is_core' => true],
            'Mathematics' => ['code' => 'MATP', 'is_core' => true],
            'Integrated Science' => ['code' => 'SCIP', 'is_core' => true],
            'Social Studies' => ['code' => 'SOCP', 'is_core' => true],
            'Creative and Technology Studies (CTS)' => ['code' => 'CTSP', 'is_core' => false],
            'Zambian Languages' => ['code' => 'ZAMP', 'is_core' => false],
            'Physical Education' => ['code' => 'PHEP', 'is_core' => false],
            'Religious Education' => ['code' => 'RELP', 'is_core' => false],
            'Art' => ['code' => 'ARTP', 'is_core' => false],
            'Music' => ['code' => 'MUSP', 'is_core' => false],
        ];

        // Secondary School Subjects (Grades 8-12)
        $secondarySubjects = [
            // Core subjects
            'English' => ['code' => 'ENGS', 'is_core' => true],
            'Mathematics' => ['code' => 'MATS', 'is_core' => true],
            'Science' => ['code' => 'SCIS', 'is_core' => true],
            'Social Studies' => ['code' => 'SOCS', 'is_core' => true],

            // Specialized subjects
            'Physics' => ['code' => 'PHYS', 'is_core' => false],
            'Chemistry' => ['code' => 'CHMS', 'is_core' => false],
            'Biology' => ['code' => 'BIOS', 'is_core' => false],
            'Geography' => ['code' => 'GEOS', 'is_core' => false],
            'History' => ['code' => 'HISS', 'is_core' => false],
            'Civic Education' => ['code' => 'CIVS', 'is_core' => false],
            'Religious Education' => ['code' => 'RELS', 'is_core' => false],
            'Physical Education' => ['code' => 'PHES', 'is_core' => false],
            'Computer Studies' => ['code' => 'COMS', 'is_core' => false],
            'Business Studies' => ['code' => 'BUSS', 'is_core' => false],
            'Accounting' => ['code' => 'ACCS', 'is_core' => false],
            'Home Economics' => ['code' => 'HOMS', 'is_core' => false],
            'Art' => ['code' => 'ARTS', 'is_core' => false],
            'Music' => ['code' => 'MUSS', 'is_core' => false],
            'French' => ['code' => 'FRNS', 'is_core' => false],
            'Technical Drawing' => ['code' => 'TEDS', 'is_core' => false],
            'Agriculture' => ['code' => 'AGRS', 'is_core' => false],
        ];

        $totalSubjects = 0;

        // Create primary subjects
        foreach ($primarySubjects as $name => $details) {
            $data = [
                'name' => $name,
                'code' => $details['code'],
                'description' => $name . ' for Primary level',
            ];

            // Add optional fields if they exist
            if ($hasGradeLevelField) {
                $data['grade_level'] = 'Primary';
            }

            if ($hasAcademicYearField) {
                $data['academic_year_id'] = $currentAcademicYear->id;
            }

            if ($hasIsActiveField) {
                $data['is_active'] = true;
            }

            if ($hasIsCoreField) {
                $data['is_core'] = $details['is_core'];
            }

            if ($hasCreditHoursField) {
                $data['credit_hours'] = 1;
            }

            if ($hasWeightField) {
                $data['weight'] = 1.0;
            }

            Subject::updateOrCreate(
                ['code' => $details['code']],
                $data
            );
            $totalSubjects++;
        }

        // Create secondary subjects
        foreach ($secondarySubjects as $name => $details) {
            $data = [
                'name' => $name,
                'code' => $details['code'],
                'description' => $name . ' for Secondary level',
            ];

            // Add optional fields if they exist
            if ($hasGradeLevelField) {
                $data['grade_level'] = 'Secondary';
            }

            if ($hasAcademicYearField) {
                $data['academic_year_id'] = $currentAcademicYear->id;
            }

            if ($hasIsActiveField) {
                $data['is_active'] = true;
            }

            if ($hasIsCoreField) {
                $data['is_core'] = $details['is_core'];
            }

            if ($hasCreditHoursField) {
                $data['credit_hours'] = 1;
            }

            if ($hasWeightField) {
                $data['weight'] = 1.0;
            }

            Subject::updateOrCreate(
                ['code' => $details['code']],
                $data
            );
            $totalSubjects++;
        }

        $this->command->info('✅ Subjects created: ' . $totalSubjects);
    }

    /**
     * Assign subjects to grades
     */
    private function assignSubjectsToGrades(): void
    {
        $this->command->info('🔗 Assigning subjects to grades...');

        // Check if grades table has subjects relationship
        if (!Schema::hasTable('grade_subject')) {
            $this->command->warn('grade_subject pivot table not found. Skipping subject-grade assignments.');
            return;
        }

        // Get primary grades (Baby Class to Grade 7)
        $primaryGrades = Grade::whereIn('name', [
            'Baby Class', 'Middle Class', 'Reception',
            'Grade 1', 'Grade 2', 'Grade 3', 'Grade 4',
            'Grade 5', 'Grade 6', 'Grade 7'
        ])->get();

        // Get secondary grades (Grades 8-12)
        $secondaryGrades = Grade::whereIn('name', [
            'Grade 8', 'Grade 9', 'Grade 10', 'Grade 11', 'Grade 12'
        ])->get();

        // Get subjects
        $primarySubjects = Subject::where(function($query) {
            $query->where('grade_level', 'Primary')
                  ->orWhere('name', 'like', '%Primary%')
                  ->orWhereIn('code', ['ENGP', 'MATP', 'SCIP', 'SOCP', 'CTSP', 'ZAMP', 'PHEP', 'RELP', 'ARTP', 'MUSP']);
        })->get();

        $secondarySubjects = Subject::where(function($query) {
            $query->where('grade_level', 'Secondary')
                  ->orWhere('name', 'like', '%Secondary%')
                  ->orWhereIn('code', ['ENGS', 'MATS', 'SCIS', 'SOCS', 'PHYS', 'CHMS', 'BIOS', 'GEOS', 'HISS', 'CIVS', 'RELS', 'PHES', 'COMS', 'BUSS', 'ACCS', 'HOMS', 'ARTS', 'MUSS', 'FRNS', 'TEDS', 'AGRS']);
        })->get();

        $totalAssignments = 0;

        // Assign primary subjects to primary grades
        foreach ($primaryGrades as $grade) {
            foreach ($primarySubjects as $subject) {
                // Check if assignment already exists
                $exists = DB::table('grade_subject')
                    ->where('grade_id', $grade->id)
                    ->where('subject_id', $subject->id)
                    ->exists();

                if (!$exists) {
                    DB::table('grade_subject')->insert([
                        'grade_id' => $grade->id,
                        'subject_id' => $subject->id,
                        'is_mandatory' => in_array($subject->code, ['ENGP', 'MATP', 'SCIP', 'SOCP']),
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                    $totalAssignments++;
                }
            }
        }

        // Assign secondary subjects to secondary grades
        foreach ($secondaryGrades as $grade) {
            foreach ($secondarySubjects as $subject) {
                // Check if assignment already exists
                $exists = DB::table('grade_subject')
                    ->where('grade_id', $grade->id)
                    ->where('subject_id', $subject->id)
                    ->exists();

                if (!$exists) {
                    // Core subjects are mandatory for all secondary grades
                    $isMandatory = in_array($subject->code, ['ENGS', 'MATS', 'SCIS', 'SOCS']);

                    DB::table('grade_subject')->insert([
                        'grade_id' => $grade->id,
                        'subject_id' => $subject->id,
                        'is_mandatory' => $isMandatory,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                    $totalAssignments++;
                }
            }
        }

        $this->command->info('✅ Subject-grade assignments: ' . $totalAssignments);
    }

    /**
     * Seed sample teachers (NEW)
     */
    private function seedSampleTeachers(): void
    {
        $this->command->info('👩‍🏫 Creating sample teachers...');

        $teacherRole = Role::where('name', 'Teacher')->first();
        if (!$teacherRole) {
            $this->command->warn('Teacher role not found. Skipping teacher creation.');
            return;
        }

        $sampleTeachers = [
            // Primary Teachers (no specialization)
            [
                'name' => 'Mary Banda',
                'email' => 'mary.banda@stfrancis.tech',
                'phone' => '0975111001',
                'employee_id' => 'T001',
                'qualification' => 'Diploma in Primary Education',
                'specialization' => null,
            ],
            [
                'name' => 'John Mwale',
                'email' => 'john.mwale@stfrancis.tech',
                'phone' => '0975111002',
                'employee_id' => 'T002',
                'qualification' => 'Certificate in Early Childhood Education',
                'specialization' => null,
            ],
            [
                'name' => 'Grace Phiri',
                'email' => 'grace.phiri@stfrancis.tech',
                'phone' => '0975111003',
                'employee_id' => 'T003',
                'qualification' => 'Bachelor of Education (Primary)',
                'specialization' => null,
            ],
            [
                'name' => 'Peter Zulu',
                'email' => 'peter.zulu@stfrancis.tech',
                'phone' => '0975111004',
                'employee_id' => 'T004',
                'qualification' => 'Diploma in Primary Education',
                'specialization' => null,
            ],

            // Secondary Teachers (with specialization)
            [
                'name' => 'Dr. Sarah Tembo',
                'email' => 'sarah.tembo@stfrancis.tech',
                'phone' => '0975111005',
                'employee_id' => 'T005',
                'qualification' => 'PhD in Mathematics',
                'specialization' => 'Mathematics',
            ],
            [
                'name' => 'Prof. Michael Chanda',
                'email' => 'michael.chanda@stfrancis.tech',
                'phone' => '0975111006',
                'employee_id' => 'T006',
                'qualification' => 'Master of Science in Physics',
                'specialization' => 'Physics',
            ],
            [
                'name' => 'Ms. Janet Kasonde',
                'email' => 'janet.kasonde@stfrancis.tech',
                'phone' => '0975111007',
                'employee_id' => 'T007',
                'qualification' => 'Bachelor of Arts in English',
                'specialization' => 'English Language',
            ],
            [
                'name' => 'Mr. Robert Simwanza',
                'email' => 'robert.simwanza@stfrancis.tech',
                'phone' => '0975111008',
                'employee_id' => 'T008',
                'qualification' => 'Bachelor of Science in Chemistry',
                'specialization' => 'Chemistry',
            ],
        ];

        $createdTeachers = 0;

        foreach ($sampleTeachers as $teacherData) {
            // Create user
            $user = User::updateOrCreate(
                ['email' => $teacherData['email']],
                [
                    'name' => $teacherData['name'],
                    'email' => $teacherData['email'],
                    'phone' => $teacherData['phone'],
                    'password' => Hash::make('password123'),
                    'role_id' => $teacherRole->id,
                    'status' => 'active',
                    'email_verified_at' => now(),
                ]
            );

            // Create employee
            $employee = Employee::updateOrCreate(
                ['user_id' => $user->id],
                [
                    'name' => $teacherData['name'],
                    'email' => $teacherData['email'],
                    'phone' => $teacherData['phone'],
                    'role_id' => $teacherRole->id,
                    'employee_id' => $teacherData['employee_id'],
                    'department' => 'Teaching',
                    'position' => 'Teacher',
                    'joining_date' => now()->subMonths(rand(3, 12)),
                    'status' => 'active',
                    'basic_salary' => rand(8000, 15000),
                ]
            );

            // Create teacher
            Teacher::updateOrCreate(
                ['user_id' => $user->id],
                [
                    'name' => $teacherData['name'],
                    'role_id' => $teacherRole->id,
                    'employee_id' => $teacherData['employee_id'],
                    'qualification' => $teacherData['qualification'],
                    'specialization' => $teacherData['specialization'],
                    'join_date' => now()->subMonths(rand(3, 12)),
                    'phone' => $teacherData['phone'],
                    'email' => $teacherData['email'],
                    'address' => 'Lusaka, Zambia',
                    'is_active' => true,
                    'is_grade_teacher' => false,
                    'is_class_teacher' => false,
                ]
            );

            $createdTeachers++;
        }

        $this->command->info('✅ Sample teachers created: ' . $createdTeachers);
    }

    /**
     * Assign teachers to classes (NEW)
     */
    private function assignTeachersToClasses(): void
    {
        $this->command->info('🔗 Assigning teachers to classes...');

        // Clear existing assignments
        DB::table('class_teacher')->truncate();

        $schoolClasses = SchoolClass::where('is_active', true)->get();
        $teachers = Teacher::where('is_active', true)->get();

        if ($schoolClasses->isEmpty() || $teachers->isEmpty()) {
            $this->command->warn('No active classes or teachers found. Skipping teacher assignments.');
            return;
        }

        $primaryTeachers = $teachers->filter(function ($teacher) {
            return empty($teacher->specialization);
        });

        $secondaryTeachers = $teachers->filter(function ($teacher) {
            return !empty($teacher->specialization);
        });

        $assignments = 0;

        // Assign primary teachers to ECL and Primary classes
        $primaryClasses = $schoolClasses->filter(function ($class) {
            return in_array($class->department, ['ECL', 'Primary']);
        });

        foreach ($primaryClasses as $index => $class) {
            $teacher = $primaryTeachers->get($index % $primaryTeachers->count());

            if ($teacher) {
                DB::table('class_teacher')->insert([
                    'class_id' => $class->id,
                    'teacher_id' => $teacher->id,
                    'role' => 'class_teacher',
                    'is_primary' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
                $assignments++;

                $this->command->info("✅ Assigned {$teacher->name} to {$class->name} as class teacher");
            }
        }

        // Assign secondary teachers to Secondary classes
        $secondaryClasses = $schoolClasses->filter(function ($class) {
            return $class->department === 'Secondary';
        });

        foreach ($secondaryClasses as $index => $class) {
            $teacher = $secondaryTeachers->get($index % $secondaryTeachers->count());

            if ($teacher) {
                DB::table('class_teacher')->insert([
                    'class_id' => $class->id,
                    'teacher_id' => $teacher->id,
                    'role' => 'class_teacher',
                    'is_primary' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
                $assignments++;

                $this->command->info("✅ Assigned {$teacher->name} to {$class->name} as class teacher");
            }
        }

        // Add some assistant teachers for variety
        $this->assignAssistantTeachers($schoolClasses, $teachers);

        $this->command->info('✅ Total teacher assignments: ' . $assignments);
    }

    /**
     * Assign assistant teachers to some classes
     */
    private function assignAssistantTeachers($schoolClasses, $teachers): void
    {
        // Add assistant teachers to some primary classes
        $primaryClasses = $schoolClasses->filter(function ($class) {
            return in_array($class->department, ['ECL', 'Primary']);
        })->take(3); // Only first 3 classes get assistants

        $availableTeachers = $teachers->filter(function ($teacher) {
            // Don't assign teachers who are already primary class teachers
            return !DB::table('class_teacher')
                ->where('teacher_id', $teacher->id)
                ->where('is_primary', true)
                ->exists();
        });

        foreach ($primaryClasses as $class) {
            $assistantTeacher = $availableTeachers->random();

            if ($assistantTeacher) {
                DB::table('class_teacher')->insert([
                    'class_id' => $class->id,
                    'teacher_id' => $assistantTeacher->id,
                    'role' => 'assistant_teacher',
                    'is_primary' => false,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                $this->command->info("✅ Assigned {$assistantTeacher->name} to {$class->name} as assistant teacher");
            }
        }
    }

    /**
     * Seed fee structures
     */
    private function seedFeeStructures(): void
    {
        $this->command->info('💰 Creating fee structures...');

        $academicYear = AcademicYear::where('name', '2024-2025')->first();
        $terms = Term::where('academic_year_id', $academicYear->id)->get();
        $grades = Grade::all();

        if (!$academicYear || $terms->isEmpty()) {
            $this->command->error('Academic years or terms not found.');
            return;
        }

        $hasIsActiveField = Schema::hasColumn('fee_structures', 'is_active');
        $hasNameField = Schema::hasColumn('fee_structures', 'name');
        $hasAdditionalChargesField = Schema::hasColumn('fee_structures', 'additional_charges');
        $hasDescriptionField = Schema::hasColumn('fee_structures', 'description');

        // Fee structure by grade level
        $feeStructureAmounts = [
            // ECL
            1 => 2500, // Baby Class
            2 => 2500, // Middle Class
            3 => 2500, // Reception

            // Primary
            4 => 3000, // Grade 1
            5 => 3000, // Grade 2
            6 => 3000, // Grade 3
            7 => 3000, // Grade 4
            8 => 3000, // Grade 5
            9 => 3000, // Grade 6
            10 => 3500, // Grade 7

            // Secondary
            11 => 4000, // Grade 8
            12 => 4000, // Grade 9
            13 => 4500, // Grade 10
            14 => 4500, // Grade 11
            15 => 5000, // Grade 12
        ];

        $totalFeeStructures = 0;

        foreach ($terms as $term) {
            foreach ($grades as $grade) {
                $totalFee = $feeStructureAmounts[$grade->level] ?? 3000;
                $basicFee = $totalFee * 0.7; // Basic fee is 70% of total fee (tuition)

                $data = [
                    'total_fee' => $totalFee,
                    'basic_fee' => $basicFee,
                ];

                // Add name field if it exists
                if ($hasNameField) {
                    $data['name'] = "{$grade->name} - {$term->name} ({$academicYear->name})";
                }

                // Add description field if it exists
                if ($hasDescriptionField) {
                    $data['description'] = "Fee structure for {$grade->name} during {$term->name} ({$academicYear->name})";
                }

                // Add additional_charges field if it exists
                if ($hasAdditionalChargesField) {
                    $additionalCharges = [
                        'Computer Fee' => 200,
                        'Sports Fee' => 150,
                        'Library Fee' => 100,
                        'Laboratory Fee' => $grade->level >= 11 ? 300 : 0, // Secondary only
                    ];

                    $data['additional_charges'] = json_encode($additionalCharges);
                }

                // Add is_active field if it exists
                if ($hasIsActiveField) {
                    $data['is_active'] = $term->is_current;
                }

                // Create fee structure
                FeeStructure::updateOrCreate(
                    [
                        'grade_id' => $grade->id,
                        'term_id' => $term->id,
                        'academic_year_id' => $academicYear->id,
                    ],
                    $data
                );
                $totalFeeStructures++;
            }
        }

        $this->command->info('✅ Fee structures created: ' . $totalFeeStructures);
    }

    /**
     * Display seeding summary
     */
    private function displaySummary(): void
    {
        $this->command->info('');
        $this->command->info('📊 SEEDING SUMMARY');
        $this->command->info('==================');

        $counts = [
            'Roles' => Role::count(),
            'Users' => User::count(),
            'Employees' => Employee::count(),
            'Teachers' => Teacher::count(),
            'Academic Years' => AcademicYear::count(),
            'Terms' => Term::count(),
            'Grades' => Grade::count(),
            'Class Sections' => ClassSection::count(),
            'School Classes' => SchoolClass::count(),
            'Subjects' => Subject::count(),
            'Fee Structures' => FeeStructure::count(),
        ];

        foreach ($counts as $item => $count) {
            $this->command->info("📌 {$item}: {$count}");
        }

        // Teacher assignments summary
        $teacherAssignments = DB::table('class_teacher')->count();
        $this->command->info("📌 Teacher-Class Assignments: {$teacherAssignments}");

        // Grade-Subject assignments summary
        if (Schema::hasTable('grade_subject')) {
            $gradeSubjectAssignments = DB::table('grade_subject')->count();
            $this->command->info("📌 Grade-Subject Assignments: {$gradeSubjectAssignments}");
        }

        $this->command->info('');
        $this->command->info('🔐 LOGIN CREDENTIALS:');
        $this->command->info('Email: admin@stfrancisofassisi.tech');
        $this->command->info('Password: password');
        $this->command->info('');
        $this->command->info('👨‍🏫 SAMPLE TEACHER LOGINS:');
        $this->command->info('Email: mary.banda@stfrancis.tech');
        $this->command->info('Email: john.mwale@stfrancis.tech');
        $this->command->info('Password: password123 (for all teachers)');
        $this->command->info('');
        $this->command->info('🎉 System is ready for use!');
    }
}
