<?php

namespace Database\Seeders;

use App\Models\Accounting\ExpenseCategory;
use Illuminate\Database\Seeder;

class ExpenseCategoriesSeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            // Operations
            [
                'name' => 'Operations',
                'code' => 'OPS',
                'description' => 'Day-to-day operational expenses',
                'children' => [
                    ['name' => 'Office Supplies', 'code' => 'OPS-001', 'description' => 'Pens, paper, folders, etc.'],
                    ['name' => 'Printing & Stationery', 'code' => 'OPS-002', 'description' => 'Printing, photocopying, forms'],
                    ['name' => 'Communication', 'code' => 'OPS-003', 'description' => 'Phone, internet, SMS services'],
                    ['name' => 'Postage & Courier', 'code' => 'OPS-004', 'description' => 'Mail and delivery services'],
                    ['name' => 'Bank Charges', 'code' => 'OPS-005', 'description' => 'Banking fees and charges'],
                    ['name' => 'Miscellaneous', 'code' => 'OPS-099', 'description' => 'Other operational expenses'],
                ],
            ],

            // Staff & HR
            [
                'name' => 'Staff & Human Resources',
                'code' => 'HR',
                'description' => 'Staff-related expenses',
                'children' => [
                    ['name' => 'Salaries - Teaching Staff', 'code' => 'HR-001', 'description' => 'Teacher salaries and wages'],
                    ['name' => 'Salaries - Admin Staff', 'code' => 'HR-002', 'description' => 'Administrative staff salaries'],
                    ['name' => 'Salaries - Support Staff', 'code' => 'HR-003', 'description' => 'Support staff wages'],
                    ['name' => 'NAPSA Contributions', 'code' => 'HR-004', 'description' => 'Employer NAPSA contributions'],
                    ['name' => 'Medical Insurance', 'code' => 'HR-005', 'description' => 'Staff medical coverage'],
                    ['name' => 'Staff Meals', 'code' => 'HR-006', 'description' => 'Staff lunch and refreshments'],
                    ['name' => 'Staff Training', 'code' => 'HR-007', 'description' => 'Professional development'],
                    ['name' => 'Staff Uniforms', 'code' => 'HR-008', 'description' => 'Staff uniform expenses'],
                    ['name' => 'Recruitment', 'code' => 'HR-009', 'description' => 'Hiring and recruitment costs'],
                    ['name' => 'Staff Welfare', 'code' => 'HR-010', 'description' => 'Staff welfare and events'],
                ],
            ],

            // Academic
            [
                'name' => 'Academic',
                'code' => 'ACD',
                'description' => 'Teaching and learning expenses',
                'children' => [
                    ['name' => 'Textbooks', 'code' => 'ACD-001', 'description' => 'Student and teacher textbooks'],
                    ['name' => 'Learning Materials', 'code' => 'ACD-002', 'description' => 'Educational supplies and materials'],
                    ['name' => 'Examination Costs', 'code' => 'ACD-003', 'description' => 'Internal and external exam fees'],
                    ['name' => 'Laboratory Supplies', 'code' => 'ACD-004', 'description' => 'Science lab materials and equipment'],
                    ['name' => 'Computer Equipment', 'code' => 'ACD-005', 'description' => 'IT equipment and software'],
                    ['name' => 'Library Resources', 'code' => 'ACD-006', 'description' => 'Books, journals, subscriptions'],
                    ['name' => 'Sports Equipment', 'code' => 'ACD-007', 'description' => 'Sports gear and equipment'],
                    ['name' => 'Field Trips', 'code' => 'ACD-008', 'description' => 'Educational trips and excursions'],
                    ['name' => 'Co-curricular Activities', 'code' => 'ACD-009', 'description' => 'Clubs, competitions, events'],
                    ['name' => 'Awards & Prizes', 'code' => 'ACD-010', 'description' => 'Student awards and prizes'],
                ],
            ],

            // Administrative
            [
                'name' => 'Administrative',
                'code' => 'ADM',
                'description' => 'General administrative expenses',
                'children' => [
                    ['name' => 'Professional Fees', 'code' => 'ADM-001', 'description' => 'Legal, audit, consulting fees'],
                    ['name' => 'Insurance', 'code' => 'ADM-002', 'description' => 'School insurance premiums'],
                    ['name' => 'Licenses & Permits', 'code' => 'ADM-003', 'description' => 'Regulatory fees and permits'],
                    ['name' => 'Advertising', 'code' => 'ADM-004', 'description' => 'Marketing and advertising'],
                    ['name' => 'Subscriptions', 'code' => 'ADM-005', 'description' => 'Software and service subscriptions'],
                    ['name' => 'Meetings & Conferences', 'code' => 'ADM-006', 'description' => 'Meeting expenses'],
                    ['name' => 'Board Expenses', 'code' => 'ADM-007', 'description' => 'Board meeting costs'],
                    ['name' => 'Entertainment', 'code' => 'ADM-008', 'description' => 'Official entertainment'],
                ],
            ],

            // Facilities & Maintenance
            [
                'name' => 'Facilities & Maintenance',
                'code' => 'FAC',
                'description' => 'Building and facility expenses',
                'children' => [
                    ['name' => 'Rent', 'code' => 'FAC-001', 'description' => 'Building rent payments'],
                    ['name' => 'Electricity', 'code' => 'FAC-002', 'description' => 'Electricity bills'],
                    ['name' => 'Water', 'code' => 'FAC-003', 'description' => 'Water bills'],
                    ['name' => 'Repairs & Maintenance', 'code' => 'FAC-004', 'description' => 'Building repairs and upkeep'],
                    ['name' => 'Cleaning Services', 'code' => 'FAC-005', 'description' => 'Cleaning and sanitation'],
                    ['name' => 'Security', 'code' => 'FAC-006', 'description' => 'Security services and equipment'],
                    ['name' => 'Gardening', 'code' => 'FAC-007', 'description' => 'Grounds maintenance'],
                    ['name' => 'Waste Management', 'code' => 'FAC-008', 'description' => 'Garbage collection and disposal'],
                    ['name' => 'Furniture & Fixtures', 'code' => 'FAC-009', 'description' => 'Furniture purchases and repairs'],
                ],
            ],

            // Transport
            [
                'name' => 'Transport',
                'code' => 'TRN',
                'description' => 'Vehicle and transport expenses',
                'children' => [
                    ['name' => 'Fuel', 'code' => 'TRN-001', 'description' => 'Vehicle fuel'],
                    ['name' => 'Vehicle Maintenance', 'code' => 'TRN-002', 'description' => 'Vehicle repairs and servicing'],
                    ['name' => 'Vehicle Insurance', 'code' => 'TRN-003', 'description' => 'Vehicle insurance premiums'],
                    ['name' => 'Road Tax & Licenses', 'code' => 'TRN-004', 'description' => 'Vehicle licensing'],
                    ['name' => 'Driver Expenses', 'code' => 'TRN-005', 'description' => 'Driver allowances and costs'],
                    ['name' => 'Hired Transport', 'code' => 'TRN-006', 'description' => 'Contracted transport services'],
                ],
            ],

            // Capital Expenditure
            [
                'name' => 'Capital Expenditure',
                'code' => 'CAP',
                'description' => 'Major asset purchases',
                'children' => [
                    ['name' => 'Building Construction', 'code' => 'CAP-001', 'description' => 'New construction projects'],
                    ['name' => 'Building Renovation', 'code' => 'CAP-002', 'description' => 'Major renovations'],
                    ['name' => 'Equipment Purchase', 'code' => 'CAP-003', 'description' => 'Major equipment acquisitions'],
                    ['name' => 'Vehicle Purchase', 'code' => 'CAP-004', 'description' => 'New vehicle purchases'],
                    ['name' => 'IT Infrastructure', 'code' => 'CAP-005', 'description' => 'IT system investments'],
                ],
            ],
        ];

        foreach ($categories as $category) {
            $this->createCategory($category, null);
        }

        $this->command->info('Expense Categories seeded successfully!');
    }

    private function createCategory(array $data, ?int $parentId): void
    {
        $category = ExpenseCategory::firstOrCreate(
            ['code' => $data['code']],
            [
                'name' => $data['name'],
                'description' => $data['description'] ?? null,
                'parent_id' => $parentId,
                'is_active' => true,
            ]
        );

        // Create children if any
        if (isset($data['children'])) {
            foreach ($data['children'] as $child) {
                $this->createCategory($child, $category->id);
            }
        }
    }
}
