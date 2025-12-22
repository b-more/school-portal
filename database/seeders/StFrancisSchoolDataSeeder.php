<?php

namespace Database\Seeders;

use App\Constants\RoleConstants;
use App\Models\Employee;
use App\Models\Grade;
use App\Models\SchoolSection;
use App\Models\StaffDesignation;
use App\Models\Subject;
use App\Models\Teacher;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class StFrancisSchoolDataSeeder extends Seeder
{
    /**
     * Run the database seeds with actual St. Francis of Assisi School data.
     */
    public function run(): void
    {
        $this->seedSchoolSections();
        $this->seedSubjects();
        $this->seedGrades();
        $this->seedStaffDesignations();
        $this->seedAdministrators();
        $this->seedTeachers();
        $this->seedSupportStaff();
    }

    /**
     * Seed school sections
     */
    private function seedSchoolSections(): void
    {
        $sections = [
            ['name' => 'Pre-School', 'code' => 'PRE', 'description' => 'Early Childhood Education (Baby Class, Middle Class, Reception)', 'order' => 1],
            ['name' => 'Primary', 'code' => 'PRI', 'description' => 'Primary School (Grade 1-7)', 'order' => 2],
            ['name' => 'Secondary', 'code' => 'SEC', 'description' => 'Secondary School (Grade 8-12 / Form 1-5)', 'order' => 3],
        ];

        foreach ($sections as $section) {
            SchoolSection::updateOrCreate(
                ['code' => $section['code']],
                $section
            );
        }
    }

    /**
     * Seed subjects with proper codes
     */
    private function seedSubjects(): void
    {
        $subjects = [
            // Languages - Core subjects
            ['name' => 'English', 'code' => 'ENG', 'description' => 'Language - English Language and Literature', 'grade_level' => 'All', 'is_core' => true, 'is_active' => true],
            ['name' => 'French', 'code' => 'FRN', 'description' => 'Language - French Language', 'grade_level' => 'Secondary', 'is_core' => false, 'is_active' => true],
            ['name' => 'Zambian Languages', 'code' => 'ZLG', 'description' => 'Language - Local Zambian Languages', 'grade_level' => 'Primary', 'is_core' => true, 'is_active' => true],

            // STEM
            ['name' => 'Mathematics', 'code' => 'MATHS', 'description' => 'STEM - Mathematics', 'grade_level' => 'All', 'is_core' => true, 'is_active' => true],
            ['name' => 'Biology', 'code' => 'BIO', 'description' => 'STEM - Biological Sciences', 'grade_level' => 'Secondary', 'is_core' => false, 'is_active' => true],
            ['name' => 'Chemistry', 'code' => 'CHEM', 'description' => 'STEM - Chemistry', 'grade_level' => 'Secondary', 'is_core' => false, 'is_active' => true],
            ['name' => 'Physics', 'code' => 'PHYS', 'description' => 'STEM - Physics', 'grade_level' => 'Secondary', 'is_core' => false, 'is_active' => true],
            ['name' => 'Integrated Science', 'code' => 'SCI', 'description' => 'STEM - Integrated Science for Primary', 'grade_level' => 'Primary', 'is_core' => true, 'is_active' => true],
            ['name' => 'Computer Studies', 'code' => 'ICT', 'description' => 'STEM - Information and Communication Technology', 'grade_level' => 'Secondary', 'is_core' => false, 'is_active' => true],

            // Humanities
            ['name' => 'Religious Education', 'code' => 'RE', 'description' => 'Humanities - Religious Education', 'grade_level' => 'All', 'is_core' => true, 'is_active' => true],
            ['name' => 'Civic Education', 'code' => 'CIED', 'description' => 'Humanities - Civic Education', 'grade_level' => 'Secondary', 'is_core' => true, 'is_active' => true],
            ['name' => 'Geography', 'code' => 'GEOG', 'description' => 'Humanities - Geography', 'grade_level' => 'Secondary', 'is_core' => false, 'is_active' => true],
            ['name' => 'History', 'code' => 'HIST', 'description' => 'Humanities - History', 'grade_level' => 'Secondary', 'is_core' => false, 'is_active' => true],
            ['name' => 'Social Studies', 'code' => 'SOC', 'description' => 'Humanities - Social Studies for Primary', 'grade_level' => 'Primary', 'is_core' => true, 'is_active' => true],

            // Commerce
            ['name' => 'Business Studies', 'code' => 'BUS', 'description' => 'Commerce - Business Studies', 'grade_level' => 'Secondary', 'is_core' => false, 'is_active' => true],
            ['name' => 'Accounts', 'code' => 'ACCT', 'description' => 'Commerce - Principles of Accounts', 'grade_level' => 'Secondary', 'is_core' => false, 'is_active' => true],
            ['name' => 'Commerce', 'code' => 'COM', 'description' => 'Commerce - Commerce', 'grade_level' => 'Secondary', 'is_core' => false, 'is_active' => true],

            // Practical
            ['name' => 'Design and Technology', 'code' => 'DT', 'description' => 'Practical - Design and Technology', 'grade_level' => 'Secondary', 'is_core' => false, 'is_active' => true],
            ['name' => 'Food and Nutrition', 'code' => 'FN', 'description' => 'Practical - Food and Nutrition', 'grade_level' => 'Secondary', 'is_core' => false, 'is_active' => true],
            ['name' => 'Home Management', 'code' => 'HM', 'description' => 'Practical - Home Management', 'grade_level' => 'Secondary', 'is_core' => false, 'is_active' => true],
            ['name' => 'Creative and Technology Studies', 'code' => 'CTS', 'description' => 'Practical - Creative and Technology Studies for Primary', 'grade_level' => 'Primary', 'is_core' => true, 'is_active' => true],

            // Creative/Physical
            ['name' => 'Art and Design', 'code' => 'ART', 'description' => 'Creative - Art and Design', 'grade_level' => 'All', 'is_core' => false, 'is_active' => true],
            ['name' => 'Music', 'code' => 'MUS', 'description' => 'Creative - Music', 'grade_level' => 'All', 'is_core' => false, 'is_active' => true],
            ['name' => 'Physical Education', 'code' => 'PE', 'description' => 'Physical - Physical Education', 'grade_level' => 'All', 'is_core' => true, 'is_active' => true],
        ];

        foreach ($subjects as $subject) {
            Subject::updateOrCreate(
                ['code' => $subject['code']],
                $subject
            );
        }
    }

    /**
     * Seed grades with proper school sections
     */
    private function seedGrades(): void
    {
        $preSchool = SchoolSection::where('code', 'PRE')->first();
        $primary = SchoolSection::where('code', 'PRI')->first();
        $secondary = SchoolSection::where('code', 'SEC')->first();

        $grades = [
            // Pre-School
            ['name' => 'Baby Class', 'code' => 'BC', 'level' => 0, 'school_section_id' => $preSchool?->id, 'description' => 'Early Childhood - Baby Class'],
            ['name' => 'Middle Class', 'code' => 'MC', 'level' => 1, 'school_section_id' => $preSchool?->id, 'description' => 'Early Childhood - Middle Class'],
            ['name' => 'Reception', 'code' => 'REC', 'level' => 2, 'school_section_id' => $preSchool?->id, 'description' => 'Early Childhood - Reception'],

            // Primary
            ['name' => 'Grade 1', 'code' => 'G1', 'level' => 3, 'school_section_id' => $primary?->id, 'description' => 'Primary - Grade 1'],
            ['name' => 'Grade 2', 'code' => 'G2', 'level' => 4, 'school_section_id' => $primary?->id, 'description' => 'Primary - Grade 2'],
            ['name' => 'Grade 3', 'code' => 'G3', 'level' => 5, 'school_section_id' => $primary?->id, 'description' => 'Primary - Grade 3'],
            ['name' => 'Grade 4', 'code' => 'G4', 'level' => 6, 'school_section_id' => $primary?->id, 'description' => 'Primary - Grade 4'],
            ['name' => 'Grade 5', 'code' => 'G5', 'level' => 7, 'school_section_id' => $primary?->id, 'description' => 'Primary - Grade 5'],
            ['name' => 'Grade 6', 'code' => 'G6', 'level' => 8, 'school_section_id' => $primary?->id, 'description' => 'Primary - Grade 6'],
            ['name' => 'Grade 7', 'code' => 'G7', 'level' => 9, 'school_section_id' => $primary?->id, 'description' => 'Primary - Grade 7'],

            // Secondary (Form 1-5 / Grade 8-12)
            ['name' => 'Grade 8', 'code' => 'G8', 'level' => 10, 'school_section_id' => $secondary?->id, 'description' => 'Secondary - Form 1 (Grade 8)'],
            ['name' => 'Grade 9', 'code' => 'G9', 'level' => 11, 'school_section_id' => $secondary?->id, 'description' => 'Secondary - Form 2 (Grade 9)'],
            ['name' => 'Grade 10', 'code' => 'G10', 'level' => 12, 'school_section_id' => $secondary?->id, 'description' => 'Secondary - Form 3 (Grade 10)'],
            ['name' => 'Grade 11', 'code' => 'G11', 'level' => 13, 'school_section_id' => $secondary?->id, 'description' => 'Secondary - Form 4 (Grade 11)'],
            ['name' => 'Grade 12', 'code' => 'G12', 'level' => 14, 'school_section_id' => $secondary?->id, 'description' => 'Secondary - Form 5 (Grade 12)'],
        ];

        foreach ($grades as $grade) {
            Grade::updateOrCreate(
                ['code' => $grade['code']],
                $grade
            );
        }
    }

    /**
     * Seed staff designations
     */
    private function seedStaffDesignations(): void
    {
        $designations = [
            // Administration
            ['name' => 'School Director', 'code' => 'DIR', 'section' => 'both', 'description' => 'Overall school leadership', 'hierarchy_level' => 1, 'sort_order' => 1, 'is_active' => true],
            ['name' => 'Headmaster', 'code' => 'HM', 'section' => 'both', 'description' => 'Head of school operations', 'hierarchy_level' => 1, 'sort_order' => 2, 'is_active' => true],
            ['name' => 'Deputy Headmaster', 'code' => 'DHM', 'section' => 'both', 'description' => 'Deputy head of school', 'hierarchy_level' => 2, 'sort_order' => 3, 'is_active' => true],
            ['name' => 'Dean of Senior Teachers', 'code' => 'DST', 'section' => 'both', 'description' => 'Oversees senior teaching staff', 'hierarchy_level' => 2, 'sort_order' => 4, 'is_active' => true],
            ['name' => 'Administrative Assistant', 'code' => 'AA', 'section' => 'both', 'description' => 'Administrative support', 'hierarchy_level' => 4, 'sort_order' => 5, 'is_active' => true],

            // Finance
            ['name' => 'Accountant', 'code' => 'ACC', 'section' => 'both', 'description' => 'Financial management', 'hierarchy_level' => 3, 'sort_order' => 6, 'is_active' => true],

            // Teaching
            ['name' => 'Class Teacher', 'code' => 'CT', 'section' => 'primary', 'description' => 'Primary class teacher', 'hierarchy_level' => 4, 'sort_order' => 7, 'is_active' => true],
            ['name' => 'Subject Teacher', 'code' => 'ST', 'section' => 'secondary', 'description' => 'Secondary subject specialist', 'hierarchy_level' => 4, 'sort_order' => 8, 'is_active' => true],
            ['name' => 'Senior Teacher', 'code' => 'SNT', 'section' => 'both', 'description' => 'Experienced teacher with leadership role', 'hierarchy_level' => 3, 'sort_order' => 9, 'is_active' => true],

            // Support
            ['name' => 'Head Driver', 'code' => 'HD', 'section' => 'both', 'description' => 'Head of transport department', 'hierarchy_level' => 4, 'sort_order' => 10, 'is_active' => true],
            ['name' => 'Bus Driver', 'code' => 'BD', 'section' => 'both', 'description' => 'School bus driver', 'hierarchy_level' => 5, 'sort_order' => 11, 'is_active' => true],
        ];

        foreach ($designations as $designation) {
            StaffDesignation::updateOrCreate(
                ['code' => $designation['code']],
                $designation
            );
        }
    }

    /**
     * Seed administrators
     */
    private function seedAdministrators(): void
    {
        $admins = [
            [
                'name' => 'Mr. Francis Mulenga',
                'email' => 'info@stfrancisschool.tech',
                'designation' => 'DIR',
                'employee_id' => 'SFA-ADM-001',
                'department' => 'Administration',
                'position' => 'School Director',
                'phone' => null,
            ],
            [
                'name' => 'Mr. Silvester Lupando',
                'email' => 'chipangulupando@gmail.com',
                'designation' => 'HM',
                'employee_id' => 'SFA-ADM-002',
                'department' => 'Administration',
                'position' => 'Headmaster',
                'phone' => null,
            ],
            [
                'name' => 'Mr. Chiuto Morris',
                'email' => 'chiutomorris4@gmail.com',
                'designation' => 'DHM',
                'employee_id' => 'SFA-ADM-003',
                'department' => 'Administration',
                'position' => 'Deputy Headmaster',
                'phone' => null,
            ],
            [
                'name' => "Ms. Kapelang'a Luwi Mercy",
                'email' => 'kapelanga.mercy@stfrancisschool.tech',
                'designation' => 'DST',
                'employee_id' => 'SFA-ADM-004',
                'department' => 'Administration',
                'position' => 'Dean of Senior Teachers',
                'phone' => null,
            ],
            [
                'name' => 'Mr. Mpongwe Stephen',
                'email' => 'mpongwe.stephen@stfrancisschool.tech',
                'designation' => 'ACC',
                'employee_id' => 'SFA-FIN-001',
                'department' => 'Finance',
                'position' => 'Accountant',
                'phone' => null,
            ],
            [
                'name' => 'Mr. Enock Lunda',
                'email' => 'enock.lunda@stfrancisschool.tech',
                'designation' => 'AA',
                'employee_id' => 'SFA-ADM-005',
                'department' => 'Administration',
                'position' => 'Administrative Assistant',
                'phone' => null,
            ],
        ];

        foreach ($admins as $admin) {
            // Create user
            $user = User::updateOrCreate(
                ['email' => $admin['email']],
                [
                    'name' => $admin['name'],
                    'password' => Hash::make('password123'),
                    'role_id' => RoleConstants::ADMIN,
                    'status' => 'active',
                ]
            );

            // Create employee
            Employee::updateOrCreate(
                ['employee_id' => $admin['employee_id']],
                [
                    'user_id' => $user->id,
                    'name' => $admin['name'],
                    'email' => $admin['email'],
                    'phone' => $admin['phone'],
                    'department' => $admin['department'],
                    'position' => $admin['position'],
                    'status' => 'active',
                    'employment_type' => 'permanent',
                    'joining_date' => now()->subYears(rand(1, 10)),
                ]
            );
        }
    }

    /**
     * Seed teachers with actual data
     */
    private function seedTeachers(): void
    {
        $preSchool = SchoolSection::where('code', 'PRE')->first();
        $primary = SchoolSection::where('code', 'PRI')->first();
        $secondary = SchoolSection::where('code', 'SEC')->first();

        $teachers = [
            // Pre-School Teachers
            [
                'name' => 'Ms. Monica Mpoya',
                'email' => 'monicampoya772@gmail.com',
                'employee_id' => 'SFA-TCH-001',
                'grade' => 'Baby Class',
                'school_section_id' => $preSchool?->id,
                'subjects' => [],
                'is_class_teacher' => true,
            ],
            [
                'name' => 'Ms. Gift Zunda',
                'email' => 'giftnzunda@gmail.com',
                'employee_id' => 'SFA-TCH-002',
                'grade' => 'Middle Class',
                'school_section_id' => $preSchool?->id,
                'subjects' => [],
                'is_class_teacher' => true,
            ],
            [
                'name' => 'Ms. Chomba Memory',
                'email' => 'memorychomba483@gmail.com',
                'employee_id' => 'SFA-TCH-003',
                'grade' => 'Reception',
                'school_section_id' => $preSchool?->id,
                'subjects' => [],
                'is_class_teacher' => true,
            ],

            // Primary Teachers
            [
                'name' => 'Ms. Doris Musa',
                'email' => 'mulengamusa429@gmail.com',
                'employee_id' => 'SFA-TCH-004',
                'grade' => 'Grade 1',
                'school_section_id' => $primary?->id,
                'subjects' => [],
                'is_class_teacher' => true,
            ],
            [
                'name' => 'Ms. Mutale Musakanya',
                'email' => 'mutalemusakanya944@gmail.com',
                'employee_id' => 'SFA-TCH-005',
                'grade' => 'Grade 2',
                'school_section_id' => $primary?->id,
                'subjects' => [],
                'is_class_teacher' => true,
            ],
            [
                'name' => 'Ms. Eunice Kansa',
                'email' => 'eunicekansa@gmail.com',
                'employee_id' => 'SFA-TCH-006',
                'grade' => 'Grade 3',
                'school_section_id' => $primary?->id,
                'subjects' => [],
                'is_class_teacher' => true,
            ],
            [
                'name' => 'Mr. Sinyangwe Eull',
                'email' => 'sinyangwe.eull@stfrancisschool.tech',
                'employee_id' => 'SFA-TCH-007',
                'grade' => 'Grade 4',
                'school_section_id' => $primary?->id,
                'subjects' => [],
                'is_class_teacher' => true,
            ],
            [
                'name' => 'Ms. Agness Mukupa',
                'email' => 'agnessmukupa2@gmail.com',
                'employee_id' => 'SFA-TCH-008',
                'grade' => 'Grade 5',
                'school_section_id' => $primary?->id,
                'subjects' => [],
                'is_class_teacher' => true,
            ],
            [
                'name' => 'Mr. Micheal Mubisa',
                'email' => 'micheal.mubisa@stfrancisschool.tech',
                'employee_id' => 'SFA-TCH-009',
                'grade' => 'Grade 6',
                'school_section_id' => $primary?->id,
                'subjects' => [],
                'is_class_teacher' => true,
            ],
            [
                'name' => 'Mr. Leonard Kopakopa',
                'email' => 'leonardkopakopa@gmail.com',
                'employee_id' => 'SFA-TCH-010',
                'grade' => 'Grade 7',
                'school_section_id' => $primary?->id,
                'subjects' => [],
                'is_class_teacher' => true,
            ],

            // Secondary Teachers
            [
                'name' => 'Ms. Evidence Mulenga',
                'email' => 'evidencem9@gmail.com',
                'employee_id' => 'SFA-TCH-011',
                'grade' => null,
                'school_section_id' => $secondary?->id,
                'subjects' => ['BUS'],
                'is_class_teacher' => false,
            ],
            [
                'name' => 'Mr. Vincent Mulenga',
                'email' => 'vincentmulenga1987@gmail.com',
                'employee_id' => 'SFA-TCH-012',
                'grade' => null,
                'school_section_id' => $secondary?->id,
                'subjects' => ['SCI', 'PHYS'],
                'is_class_teacher' => false,
                'weekly_periods' => 32,
            ],
            [
                'name' => 'Ms. Mulenga Bwalya',
                'email' => 'bwalyamuele1501@gmail.com',
                'employee_id' => 'SFA-TCH-013',
                'grade' => null,
                'school_section_id' => $secondary?->id,
                'subjects' => ['ENG', 'RE'],
                'is_class_teacher' => false,
            ],
            [
                'name' => 'Mr. Godwin Lubinda',
                'email' => 'godwin.lubinda@stfrancisschool.tech',
                'employee_id' => 'SFA-TCH-014',
                'grade' => null,
                'school_section_id' => $secondary?->id,
                'subjects' => ['ICT', 'ACCT'],
                'is_class_teacher' => false,
                'weekly_periods' => 24,
            ],
            [
                'name' => 'Mr. Richard Nkandu',
                'email' => 'richienkandu@gmail.com',
                'employee_id' => 'SFA-TCH-015',
                'grade' => null,
                'school_section_id' => $secondary?->id,
                'subjects' => ['BIO', 'CHEM'],
                'is_class_teacher' => false,
                'weekly_periods' => 32,
            ],
            [
                'name' => 'Ms. Cynthia Besa',
                'email' => 'cynthiabesa2023@gmail.com',
                'employee_id' => 'SFA-TCH-016',
                'grade' => null,
                'school_section_id' => $secondary?->id,
                'subjects' => ['SOC', 'CIED', 'GEOG'],
                'is_class_teacher' => false,
                'weekly_periods' => 36,
            ],
            [
                'name' => 'Mr. Morgan Simukonda',
                'email' => 'morgansimukond449@gmail.com',
                'employee_id' => 'SFA-TCH-017',
                'grade' => null,
                'school_section_id' => $secondary?->id,
                'subjects' => ['BIO', 'CHEM'],
                'is_class_teacher' => false,
                'weekly_periods' => 40,
            ],
            [
                'name' => 'Mr. Felistus Chibwe',
                'email' => 'quintinohchibwe89@gmail.com',
                'employee_id' => 'SFA-TCH-018',
                'grade' => null,
                'school_section_id' => $secondary?->id,
                'subjects' => ['ENG', 'MUS'],
                'is_class_teacher' => false,
                'weekly_periods' => 24,
            ],
            [
                'name' => 'Ms. Tisatenji Mwaba',
                'email' => 'mwabafranisna@gmail.com',
                'employee_id' => 'SFA-TCH-019',
                'grade' => null,
                'school_section_id' => $secondary?->id,
                'subjects' => ['CIED', 'GEOG'],
                'is_class_teacher' => false,
            ],
            [
                'name' => 'Mr. Fred Simpemba',
                'email' => 'fredsimpemba@gmail.com',
                'employee_id' => 'SFA-TCH-020',
                'grade' => null,
                'school_section_id' => $secondary?->id,
                'subjects' => ['DT'],
                'is_class_teacher' => false,
                'weekly_periods' => 18,
            ],
            [
                'name' => 'Ms. Nancy Ponga',
                'email' => 'chikondanancy@gmail.com',
                'employee_id' => 'SFA-TCH-021',
                'grade' => null,
                'school_section_id' => $secondary?->id,
                'subjects' => ['FN', 'HM', 'FRN'],
                'is_class_teacher' => false,
                'weekly_periods' => 24,
            ],
            [
                'name' => 'Mr. Silvester Bwalya',
                'email' => 'chileshejunior92@gmail.com',
                'employee_id' => 'SFA-TCH-022',
                'grade' => null,
                'school_section_id' => $secondary?->id,
                'subjects' => ['ART', 'PE'],
                'is_class_teacher' => false,
            ],
            [
                'name' => 'Ms. Gift Kaposhi',
                'email' => 'giftkaposhi2018@gmail.com',
                'employee_id' => 'SFA-TCH-023',
                'grade' => null,
                'school_section_id' => $secondary?->id,
                'subjects' => ['FN', 'HM', 'FRN'],
                'is_class_teacher' => false,
                'weekly_periods' => 28,
            ],
            [
                'name' => 'Mr. Handson Kabamba',
                'email' => 'kabambahandson7@gmail.com',
                'employee_id' => 'SFA-TCH-024',
                'grade' => null,
                'school_section_id' => $secondary?->id,
                'subjects' => ['MATHS'],
                'is_class_teacher' => false,
                'weekly_periods' => 24,
            ],
            [
                'name' => 'Ms. Chimba Memory',
                'email' => 'chimbamemory14@gmail.com',
                'employee_id' => 'SFA-TCH-025',
                'grade' => null,
                'school_section_id' => $secondary?->id,
                'subjects' => ['ENG', 'RE'],
                'is_class_teacher' => false,
            ],
            [
                'name' => 'Mr. Bruno Silwamba',
                'email' => 'silwambabruno88@gmail.com',
                'employee_id' => 'SFA-TCH-026',
                'grade' => null,
                'school_section_id' => $secondary?->id,
                'subjects' => ['ENG', 'RE'],
                'is_class_teacher' => false,
                'weekly_periods' => 24,
            ],
            [
                'name' => 'Mr. Bravine Mwaba',
                'email' => 'bravine.mwaba312019@gmail.com',
                'employee_id' => 'SFA-TCH-027',
                'grade' => null,
                'school_section_id' => $secondary?->id,
                'subjects' => ['MATHS', 'PHYS'],
                'is_class_teacher' => false,
                'weekly_periods' => 30,
            ],
        ];

        foreach ($teachers as $teacherData) {
            // Create user
            $user = User::updateOrCreate(
                ['email' => $teacherData['email']],
                [
                    'name' => $teacherData['name'],
                    'password' => Hash::make('password123'),
                    'role_id' => RoleConstants::TEACHER,
                    'status' => 'active',
                ]
            );

            // Create employee
            $employee = Employee::updateOrCreate(
                ['employee_id' => $teacherData['employee_id']],
                [
                    'user_id' => $user->id,
                    'name' => $teacherData['name'],
                    'email' => $teacherData['email'],
                    'department' => 'Teaching',
                    'position' => $teacherData['is_class_teacher'] ? 'Class Teacher' : 'Subject Teacher',
                    'status' => 'active',
                    'employment_type' => 'permanent',
                    'joining_date' => now()->subYears(rand(1, 5)),
                ]
            );

            // Find grade if class teacher
            $grade = null;
            if ($teacherData['grade']) {
                $grade = Grade::where('name', $teacherData['grade'])->first();
            }

            // Get specialization for secondary teachers
            $specialization = null;
            if (!$teacherData['is_class_teacher'] && !empty($teacherData['subjects'])) {
                $subjects = Subject::whereIn('code', $teacherData['subjects'])->pluck('name')->toArray();
                $specialization = implode(', ', $subjects);
            }

            // Create teacher
            $teacher = Teacher::updateOrCreate(
                ['employee_id' => $teacherData['employee_id']],
                [
                    'user_id' => $user->id,
                    'name' => $teacherData['name'],
                    'email' => $teacherData['email'],
                    'school_section_id' => $teacherData['school_section_id'],
                    'grade_id' => $grade?->id,
                    'is_grade_teacher' => $teacherData['is_class_teacher'],
                    'is_class_teacher' => $teacherData['is_class_teacher'],
                    'qualification' => $teacherData['is_class_teacher'] ? 'Diploma in Education' : 'Bachelor of Education',
                    'specialization' => $specialization,
                    'is_active' => true,
                ]
            );

            // Note: Subject teaching assignments should be done when setting up timetables
            // since they require class_section_id in the pivot table
        }
    }

    /**
     * Seed support staff (non-teaching)
     */
    private function seedSupportStaff(): void
    {
        $staff = [
            [
                'name' => 'Mr. Halaba Anthony',
                'employee_id' => 'SFA-DRV-001',
                'position' => 'Head Driver',
                'department' => 'Transportation',
            ],
            [
                'name' => "Mr. Ng'andwe Stanely",
                'employee_id' => 'SFA-DRV-002',
                'position' => 'Bus Driver',
                'department' => 'Transportation',
            ],
            [
                'name' => 'Mr. Chola Kelvin',
                'employee_id' => 'SFA-DRV-003',
                'position' => 'Bus Driver',
                'department' => 'Transportation',
            ],
            [
                'name' => 'Mr. Charles Kalonde',
                'employee_id' => 'SFA-DRV-004',
                'position' => 'Bus Driver',
                'department' => 'Transportation',
            ],
            [
                'name' => 'Mr. Mwiinga Patrick',
                'employee_id' => 'SFA-DRV-005',
                'position' => 'Bus Driver',
                'department' => 'Transportation',
            ],
        ];

        foreach ($staff as $staffData) {
            Employee::updateOrCreate(
                ['employee_id' => $staffData['employee_id']],
                [
                    'name' => $staffData['name'],
                    'department' => $staffData['department'],
                    'position' => $staffData['position'],
                    'status' => 'active',
                    'employment_type' => 'permanent',
                    'joining_date' => now()->subYears(rand(1, 5)),
                ]
            );
        }
    }

    /**
     * Get first name from full name
     */
    private function getFirstName(string $fullName): string
    {
        // Remove titles like Mr., Ms., Mrs., Dr., Prof.
        $name = preg_replace('/^(Mr\.|Ms\.|Mrs\.|Dr\.|Prof\.)\s*/i', '', $fullName);
        $parts = explode(' ', trim($name));
        return $parts[0] ?? $fullName;
    }

    /**
     * Get last name from full name
     */
    private function getLastName(string $fullName): string
    {
        // Remove titles like Mr., Ms., Mrs., Dr., Prof.
        $name = preg_replace('/^(Mr\.|Ms\.|Mrs\.|Dr\.|Prof\.)\s*/i', '', $fullName);
        $parts = explode(' ', trim($name));
        return count($parts) > 1 ? end($parts) : '';
    }
}
