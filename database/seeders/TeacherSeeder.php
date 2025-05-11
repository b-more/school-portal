<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Employee;
use App\Models\Teacher;
use App\Models\Role;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Carbon;

class TeacherSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Ensure Teacher role exists
        $teacherRole = Role::where('name', 'Teacher')->firstOrCreate([
            'name' => 'Teacher',
        ], [
            'description' => 'School Teacher',
            'is_active' => true,
        ]);

        // Define teacher data
        $teachersData = [
            [
                'name' => 'AGNES MWABA',
                'phone' => '0978186032',
                'email' => 'Agnesmwaba@gmail.com',
                'employee_id' => '04',
                'joining_date' => '2024-01-01',
            ],
            [
                'name' => 'MASEKAPANA MITALE',
                'phone' => '0977515873',
                'email' => 'mitale.maseka@gmail.com',
                'employee_id' => null,
                'joining_date' => '2024-01-02',
            ],
            [
                'name' => 'LUBINDA CONNIE',
                'phone' => '0962907946',
                'email' => 'connie.lubinda@gmail.com',
                'employee_id' => null,
                'joining_date' => '2024-01-10',
            ],
            [
                'name' => 'MUNYIKA VANSA',
                'phone' => '0955205873',
                'email' => 'vansa.munyika@gmail.com',
                'employee_id' => null,
                'joining_date' => '2024-01-12',
            ],
            [
                'name' => 'MAYELE ORCHARDS',
                'phone' => '0966401260',
                'email' => 'orchards.mayele@gmail.com',
                'employee_id' => null,
                'joining_date' => '2024-01-15',
            ],
            [
                'name' => 'MWANSA RICHARD',
                'phone' => '0974004620',
                'email' => 'richardmwansa82@gmail.com',
                'employee_id' => '08',
                'joining_date' => '2024-01-25',
            ],
            [
                'name' => 'CHOMBA MEMORY',
                'phone' => '0762605525',
                'email' => 'memorychomba142@gmail.com',
                'employee_id' => null,
                'joining_date' => '2024-01-08',
            ],
            [
                'name' => 'GIFT KAKOMA',
                'phone' => '0967445018',
                'email' => 'kakomagift24@gmail.com',
                'employee_id' => '07',
                'joining_date' => '2024-01-01',
            ],
            [
                'name' => 'AILISH MBEWE',
                'phone' => '0977450616',
                'email' => 'mbeweailish@gmail.com',
                'employee_id' => null,
                'joining_date' => '2024-01-02',
            ],
            [
                'name' => 'CHIMBA MEMORY',
                'phone' => '0776608066',
                'email' => 'memorychimba07@gmail.com',
                'employee_id' => '02',
                'joining_date' => '2024-01-02',
            ],
            [
                'name' => 'CHIEF KAKOSHI',
                'phone' => '0967291127',
                'email' => 'chiefkakoshi@gmail.com',
                'employee_id' => '05',
                'joining_date' => '2024-01-01',
            ],
            [
                'name' => 'KALEBAH MUMBA',
                'phone' => '0968206620',
                'email' => 'kalebah3720@gmail.com',
                'employee_id' => null,
                'joining_date' => '2024-01-12',
            ],
            [
                'name' => 'BRIANNE MWABA',
                'phone' => '0974004620',
                'email' => 'brianne.mwaba@gmail.com',
                'employee_id' => null,
                'joining_date' => '2024-01-08',
            ],
            [
                'name' => 'FRED SIMAPATA',
                'phone' => '0972133568',
                'email' => 'fredsimapata@gmail.com',
                'employee_id' => null,
                'joining_date' => '2024-01-15',
            ],
            [
                'name' => 'VINCENT PHIRI',
                'phone' => '0988233358',
                'email' => 'vincentphiri87@gmail.com',
                'employee_id' => null,
                'joining_date' => '2024-02-02',
            ],
            [
                'name' => 'NANCY PONGA',
                'phone' => '0975003288',
                'email' => 'nancyponga187@gmail.com',
                'employee_id' => '01',
                'joining_date' => '2024-01-01',
            ],
            [
                'name' => 'MULEMENA EUDANCE',
                'phone' => '0969483533',
                'email' => 'eudance.mulemema@gmail.com',
                'employee_id' => null,
                'joining_date' => '2024-01-01',
            ],
        ];

        // Create each teacher
        foreach ($teachersData as $teacherData) {
            // Create or find user
            $user = User::updateOrCreate(
                ['email' => $teacherData['email']],
                [
                    'name' => $teacherData['name'],
                    'email' => $teacherData['email'],
                    'phone' => $teacherData['phone'],
                    'password' => Hash::make('password123'), // Default password
                    'role_id' => $teacherRole->id,
                    'status' => 'active',
                ]
            );

            // Generate employee number if not provided
            $employeeNumber = $teacherData['employee_id'] ?? 'T' . str_pad($user->id, 3, '0', STR_PAD_LEFT);

            // Create or update employee record
            $employee = Employee::updateOrCreate(
                ['user_id' => $user->id],
                [
                    'name' => $teacherData['name'],
                    'email' => $teacherData['email'],
                    'phone' => $teacherData['phone'],
                    'role_id' => $teacherRole->id,
                    'employee_number' => $employeeNumber,
                    'department' => 'Teaching', // Default department
                    'position' => 'Teacher',
                    'joining_date' => Carbon::parse($teacherData['joining_date']),
                    'status' => 'active',
                    'basic_salary' => 10000, // Default salary
                    'employee_id' => $employeeNumber,
                ]
            );

            // Create or update teacher record
            $teacher = Teacher::updateOrCreate(
                ['user_id' => $user->id],
                [
                    'name' => $teacherData['name'],
                    'role_id' => $teacherRole->id,
                    'employee_id' => $employee->id,
                    'qualification' => 'Bachelor\'s Degree', // Default qualification
                    'specialization' => 'General Education', // Default specialization
                    'join_date' => Carbon::parse($teacherData['joining_date']),
                    'phone' => $teacherData['phone'],
                    'email' => $teacherData['email'],
                    'address' => 'Lusaka, Zambia', // Default address
                    'is_active' => true,
                    'is_grade_teacher' => false,
                ]
            );

            $this->command->info("Created teacher: {$teacherData['name']} (Employee ID: {$employeeNumber})");
        }

        $this->command->info('✅ All teachers have been seeded successfully!');
        $this->command->info('Total teachers created: ' . count($teachersData));
    }
}
