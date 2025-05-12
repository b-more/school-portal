<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Role;
use App\Models\User;
use App\Models\Employee;
use App\Models\Grade;
use App\Models\Subject;
use App\Models\AcademicYear;
use App\Models\Term;
use App\Models\FeeStructure;
use App\Models\FeeComponent;
use App\Models\ClassSection;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Schema;

class EssentialDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('Seeding essential data for the system...');

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

        // 7. Create Subjects
        $this->seedSubjects();

        // 8. Create Fee Structures
        $this->seedFeeStructures();

        $this->command->info('All essential data has been seeded successfully!');
    }

    /**
     * Seed roles
     */
    private function seedRoles(): void
    {
        $this->command->info('Creating roles...');

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
    }

    /**
     * Seed admin user
     */
    private function seedAdminUser(): void
    {
        $this->command->info('Creating admin user...');

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

        $this->command->info('Admin user created: admin@stfrancisofassisi.tech / password');
    }

    /**
     * Seed academic years
     */
    private function seedAcademicYears(): void
    {
        $this->command->info('Creating academic years...');

        $years = [
            [
                'name' => '2024-2025',
                'start_date' => '2024-01-08',
                'end_date' => '2024-12-20',
                'is_current' => true,
            ],
            [
                'name' => '2025-2026',
                'start_date' => '2025-01-06',
                'end_date' => '2025-12-19',
                'is_current' => false,
            ],
        ];

        foreach ($years as $year) {
            AcademicYear::updateOrCreate(
                ['name' => $year['name']],
                $year
            );
        }
    }

    /**
     * Seed terms
     */
    private function seedTerms(): void
    {
        $this->command->info('Creating terms...');

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
    }

    /**
     * Seed grades
     */
    private function seedGrades(): void
    {
        $this->command->info('Creating grades...');

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
    }

    /**
     * Seed class sections
     */
    private function seedClassSections(): void
    {
        $this->command->info('Creating class sections...');

        $academicYear = AcademicYear::where('name', '2024-2025')->first();

        if (!$academicYear) {
            $this->command->error('Academic year not found.');
            return;
        }

        $grades = Grade::all();
        $hasIsActiveField = Schema::hasColumn('class_sections', 'is_active');

        foreach ($grades as $grade) {
            // Create sections (A, B) for each grade
            foreach (['A', 'B'] as $section) {
                $data = [
                    'name' => $section,
                    'grade_id' => $grade->id,
                    'academic_year_id' => $academicYear->id,
                    'capacity' => 40,
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
            }
        }
    }

    /**
     * Seed subjects
     */
    private function seedSubjects(): void
{
    $this->command->info('Creating subjects...');

    $hasIsActiveField = Schema::hasColumn('subjects', 'is_active');

    $primarySubjects = [
        'English' => 'ENGP',
        'Mathematics' => 'MATP',
        'Science' => 'SCIP',
        'Social Studies' => 'SOCP',
        'Religious Education' => 'RELP',
        'Creative & Technology Studies' => 'CTSP',
        'Expressive Arts' => 'EXAP',
        'Zambian Languages' => 'ZAMP'
    ];

    $secondarySubjects = [
        'English' => 'ENGS',
        'Mathematics' => 'MATS',
        'Biology' => 'BIOS',
        'Chemistry' => 'CHMS',
        'Physics' => 'PHYS',
        'History' => 'HISS',
        'Geography' => 'GEOS',
        'Religious Education' => 'RELS',
        'Civic Education' => 'CIVS',
        'Computer Studies' => 'COMS',
        'Commerce' => 'CMMS',
        'Principles of Accounts' => 'ACCS',
        'Business Studies' => 'BUSS',
        'Agricultural Science' => 'AGRS',
        'French' => 'FRNS',
        'Art' => 'ARTS',
        'Home Economics' => 'HOMS',
        'Physical Education' => 'PHES'
    ];

    // Create primary subjects (Grade 1-7)
    $primaryGrades = Grade::whereIn('level', [4, 5, 6, 7, 8, 9, 10])->get();

    foreach ($primarySubjects as $subjectName => $subjectCode) {
        $data = [
            'name' => $subjectName . ' (Primary)',
            'code' => $subjectCode,
            'description' => "Primary school {$subjectName}",
        ];

        // Add optional fields if they exist
        if ($hasIsActiveField) {
            $data['is_active'] = true;
        }

        $subject = Subject::updateOrCreate(
            ['code' => $subjectCode],
            $data
        );

        // Check if the relationship method exists before trying to sync
        if (method_exists($subject, 'grades')) {
            $subject->grades()->sync($primaryGrades->pluck('id')->toArray());
        }
    }

    // Create secondary subjects (Grade 8-12)
    $secondaryGrades = Grade::whereIn('level', [11, 12, 13, 14, 15])->get();

    foreach ($secondarySubjects as $subjectName => $subjectCode) {
        $data = [
            'name' => $subjectName . ' (Secondary)',
            'code' => $subjectCode,
            'description' => "Secondary school {$subjectName}",
        ];

        // Add optional fields if they exist
        if ($hasIsActiveField) {
            $data['is_active'] = true;
        }

        $subject = Subject::updateOrCreate(
            ['code' => $subjectCode],
            $data
        );

        // Check if the relationship method exists before trying to sync
        if (method_exists($subject, 'grades')) {
            $subject->grades()->sync($secondaryGrades->pluck('id')->toArray());
        }
    }
}

    /**
     * Seed fee structures
     */
    private function seedFeeStructures(): void
{
    $this->command->info('Creating fee structures...');

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
        }
    }

    $this->command->info("Fee structures created successfully!");
}
}
