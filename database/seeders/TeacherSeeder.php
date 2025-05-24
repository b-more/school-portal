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
        $teacherRole = Role::firstOrCreate(
            ['name' => 'Teacher'],
            [
                'description' => 'School Teacher',
                'is_active' => true,
            ]
        );

        // Teacher data extracted from the image
        $teachersData = [
            [
                'name' => 'Agnes Mwaba',
                'phone' => '0978186032',
                'email' => 'agnesmwaba@gmail.com',
                'employee_id' => '04',
                'joining_date' => '2024-01-01',
            ],
            [
                'name' => 'Masekapana Mitale',
                'phone' => '0977515873',
                'email' => 'mitale.maseka@gmail.com',
                'employee_id' => '09',
                'joining_date' => '2024-01-02',
            ],
            [
                'name' => 'Lubinda Connie',
                'phone' => '0962907946',
                'email' => 'connie.lubinda@gmail.com',
                'employee_id' => '10',
                'joining_date' => '2024-01-10',
            ],
            [
                'name' => 'Munyika Vansa',
                'phone' => '0955205873',
                'email' => 'vansa.munyika@gmail.com',
                'employee_id' => '11',
                'joining_date' => '2024-01-12',
            ],
            [
                'name' => 'Mayele Orchards',
                'phone' => '0966401260',
                'email' => 'orchards.mayele@gmail.com',
                'employee_id' => '12',
                'joining_date' => '2024-01-15',
            ],
            [
                'name' => 'Mwansa Richard',
                'phone' => '0974004620',
                'email' => 'richardmwansa82@gmail.com',
                'employee_id' => '08',
                'joining_date' => '2024-01-25',
            ],
            [
                'name' => 'Chomba Memory',
                'phone' => '0762605525',
                'email' => 'memorychomba142@gmail.com',
                'employee_id' => '13',
                'joining_date' => '2024-01-08',
            ],
            [
                'name' => 'Gift Kakoma',
                'phone' => '0967445018',
                'email' => 'kakomagift24@gmail.com',
                'employee_id' => '07',
                'joining_date' => '2024-01-01',
            ],
            [
                'name' => 'Ailish Mbewe',
                'phone' => '0977450616',
                'email' => 'mbeweailish@gmail.com',
                'employee_id' => '14',
                'joining_date' => '2024-01-02',
            ],
            [
                'name' => 'Chimba Memory',
                'phone' => '0776608066',
                'email' => 'memorychimba07@gmail.com',
                'employee_id' => '02',
                'joining_date' => '2024-01-02',
            ],
            [
                'name' => 'Chief Kakoshi',
                'phone' => '0967291127',
                'email' => 'chiefkakoshi@gmail.com',
                'employee_id' => '05',
                'joining_date' => '2024-01-01',
            ],
            [
                'name' => 'Kalebah Mumba',
                'phone' => '0968206620',
                'email' => 'kalebah3720@gmail.com',
                'employee_id' => '15',
                'joining_date' => '2024-01-12',
            ],
            [
                'name' => 'Brianne Mwaba',
                'phone' => '0974004620',
                'email' => 'brianne.mwaba@gmail.com',
                'employee_id' => '16',
                'joining_date' => '2024-01-08',
            ],
            [
                'name' => 'Fred Simapata',
                'phone' => '0972133568',
                'email' => 'fredsimapata@gmail.com',
                'employee_id' => '17',
                'joining_date' => '2024-01-15',
            ],
            [
                'name' => 'Vincent Phiri',
                'phone' => '0988233358',
                'email' => 'vincentphiri87@gmail.com',
                'employee_id' => '18',
                'joining_date' => '2024-02-02',
            ],
            [
                'name' => 'Nancy Ponga',
                'phone' => '0975003288',
                'email' => 'nancyponga187@gmail.com',
                'employee_id' => '01',
                'joining_date' => '2024-01-01',
            ],
            [
                'name' => 'Mulemema Eudance',
                'phone' => '0969483533',
                'email' => 'eudance.mulemema@gmail.com',
                'employee_id' => '03',
                'joining_date' => '2024-01-01',
            ],
        ];

        foreach ($teachersData as $teacherData) {
            // Create or find user
            $user = User::updateOrCreate(
                ['email' => $teacherData['email']],
                [
                    'name' => $teacherData['name'],
                    'email' => $teacherData['email'],
                    'phone' => $teacherData['phone'],
                    'password' => Hash::make('password123'),
                    'role_id' => $teacherRole->id,
                    'status' => 'active',
                ]
            );

            $employeeNumber = $teacherData['employee_id'] ?? 'T' . str_pad($user->id, 3, '0', STR_PAD_LEFT);

            // Create or update employee
            $employee = Employee::updateOrCreate(
                ['user_id' => $user->id],
                [
                    'name' => $teacherData['name'],
                    'email' => $teacherData['email'],
                    'phone' => $teacherData['phone'],
                    'role_id' => $teacherRole->id,
                    'employee_number' => $employeeNumber,
                    'department' => 'Teaching',
                    'position' => 'Teacher',
                    'joining_date' => Carbon::parse($teacherData['joining_date']),
                    'status' => 'active',
                    'basic_salary' => 10000,
                    'employee_id' => $employeeNumber,
                ]
            );

            // Create or update teacher
            Teacher::updateOrCreate(
                ['user_id' => $user->id],
                [
                    'name' => $teacherData['name'],
                    'role_id' => $teacherRole->id,
                    'employee_id' => $employee->id,
                    'qualification' => 'Bachelor\'s Degree',
                    'specialization' => 'General Education',
                    'join_date' => Carbon::parse($teacherData['joining_date']),
                    'phone' => $teacherData['phone'],
                    'email' => $teacherData['email'],
                    'address' => 'Lusaka, Zambia',
                    'is_active' => true,
                    'is_grade_teacher' => false,
                ]
            );

            $this->command->info("✅ Seeded: {$teacherData['name']} (Emp#: {$employeeNumber})");
        }

        $this->command->info('🎉 All teachers have been seeded successfully.');
        $this->command->info('📦 Total teachers: ' . count($teachersData));
    }
}
