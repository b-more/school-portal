<?php

namespace Database\Seeders;

use App\Models\Accounting\AccountCategory;
use App\Models\Accounting\ChartOfAccount;
use Illuminate\Database\Seeder;

class ChartOfAccountsSeeder extends Seeder
{
    public function run(): void
    {
        // Create Account Categories
        $categories = [
            ['name' => 'Assets', 'code' => 'AST', 'type' => 'asset', 'description' => 'Resources owned by the school'],
            ['name' => 'Liabilities', 'code' => 'LIA', 'type' => 'liability', 'description' => 'Debts and obligations'],
            ['name' => 'Equity', 'code' => 'EQU', 'type' => 'equity', 'description' => 'Owner\'s equity and retained earnings'],
            ['name' => 'Revenue', 'code' => 'REV', 'type' => 'revenue', 'description' => 'Income from operations'],
            ['name' => 'Expenses', 'code' => 'EXP', 'type' => 'expense', 'description' => 'Operating costs and expenditures'],
        ];

        $categoryIds = [];
        foreach ($categories as $category) {
            $cat = AccountCategory::firstOrCreate(
                ['code' => $category['code']],
                $category
            );
            $categoryIds[$category['type']] = $cat->id;
        }

        // Chart of Accounts structure
        $accounts = [
            // ASSETS (1000-1999)
            [
                'code' => '1000',
                'name' => 'Current Assets',
                'type' => 'asset',
                'level' => 1,
                'children' => [
                    ['code' => '1010', 'name' => 'Cash on Hand', 'type' => 'asset', 'level' => 2],
                    ['code' => '1020', 'name' => 'Petty Cash', 'type' => 'asset', 'level' => 2],
                    ['code' => '1100', 'name' => 'Bank Accounts', 'type' => 'asset', 'level' => 2, 'children' => [
                        ['code' => '1110', 'name' => 'Main Operating Account', 'type' => 'asset', 'level' => 3],
                        ['code' => '1120', 'name' => 'Savings Account', 'type' => 'asset', 'level' => 3],
                        ['code' => '1130', 'name' => 'Fees Collection Account', 'type' => 'asset', 'level' => 3],
                    ]],
                    ['code' => '1200', 'name' => 'Accounts Receivable', 'type' => 'asset', 'level' => 2, 'children' => [
                        ['code' => '1210', 'name' => 'Student Fees Receivable', 'type' => 'asset', 'level' => 3],
                        ['code' => '1220', 'name' => 'Other Receivables', 'type' => 'asset', 'level' => 3],
                    ]],
                    ['code' => '1300', 'name' => 'Prepaid Expenses', 'type' => 'asset', 'level' => 2],
                    ['code' => '1400', 'name' => 'Inventory', 'type' => 'asset', 'level' => 2, 'children' => [
                        ['code' => '1410', 'name' => 'Office Supplies', 'type' => 'asset', 'level' => 3],
                        ['code' => '1420', 'name' => 'Teaching Materials', 'type' => 'asset', 'level' => 3],
                        ['code' => '1430', 'name' => 'Cleaning Supplies', 'type' => 'asset', 'level' => 3],
                    ]],
                ],
            ],
            [
                'code' => '1500',
                'name' => 'Fixed Assets',
                'type' => 'asset',
                'level' => 1,
                'children' => [
                    ['code' => '1510', 'name' => 'Land', 'type' => 'asset', 'level' => 2],
                    ['code' => '1520', 'name' => 'Buildings', 'type' => 'asset', 'level' => 2],
                    ['code' => '1530', 'name' => 'Furniture & Fixtures', 'type' => 'asset', 'level' => 2],
                    ['code' => '1540', 'name' => 'Vehicles', 'type' => 'asset', 'level' => 2],
                    ['code' => '1550', 'name' => 'Computer Equipment', 'type' => 'asset', 'level' => 2],
                    ['code' => '1560', 'name' => 'Laboratory Equipment', 'type' => 'asset', 'level' => 2],
                    ['code' => '1570', 'name' => 'Library Books', 'type' => 'asset', 'level' => 2],
                    ['code' => '1590', 'name' => 'Accumulated Depreciation', 'type' => 'asset', 'level' => 2],
                ],
            ],

            // LIABILITIES (2000-2999)
            [
                'code' => '2000',
                'name' => 'Current Liabilities',
                'type' => 'liability',
                'level' => 1,
                'children' => [
                    ['code' => '2010', 'name' => 'Accounts Payable', 'type' => 'liability', 'level' => 2],
                    ['code' => '2020', 'name' => 'Salaries Payable', 'type' => 'liability', 'level' => 2],
                    ['code' => '2030', 'name' => 'NAPSA Payable', 'type' => 'liability', 'level' => 2],
                    ['code' => '2040', 'name' => 'PAYE Payable', 'type' => 'liability', 'level' => 2],
                    ['code' => '2050', 'name' => 'Advances from Parents', 'type' => 'liability', 'level' => 2],
                    ['code' => '2060', 'name' => 'Deferred Revenue', 'type' => 'liability', 'level' => 2],
                    ['code' => '2070', 'name' => 'Accrued Expenses', 'type' => 'liability', 'level' => 2],
                ],
            ],
            [
                'code' => '2500',
                'name' => 'Long-term Liabilities',
                'type' => 'liability',
                'level' => 1,
                'children' => [
                    ['code' => '2510', 'name' => 'Bank Loans', 'type' => 'liability', 'level' => 2],
                    ['code' => '2520', 'name' => 'Other Long-term Debt', 'type' => 'liability', 'level' => 2],
                ],
            ],

            // EQUITY (3000-3999)
            [
                'code' => '3000',
                'name' => 'Equity',
                'type' => 'equity',
                'level' => 1,
                'children' => [
                    ['code' => '3100', 'name' => 'Owner\'s Capital', 'type' => 'equity', 'level' => 2],
                    ['code' => '3200', 'name' => 'Retained Earnings', 'type' => 'equity', 'level' => 2],
                    ['code' => '3300', 'name' => 'Current Year Earnings', 'type' => 'equity', 'level' => 2],
                ],
            ],

            // REVENUE (4000-4999)
            [
                'code' => '4000',
                'name' => 'Operating Revenue',
                'type' => 'revenue',
                'level' => 1,
                'children' => [
                    ['code' => '4100', 'name' => 'Tuition Fees', 'type' => 'revenue', 'level' => 2, 'children' => [
                        ['code' => '4110', 'name' => 'Tuition - Pre-School', 'type' => 'revenue', 'level' => 3],
                        ['code' => '4120', 'name' => 'Tuition - Primary', 'type' => 'revenue', 'level' => 3],
                        ['code' => '4130', 'name' => 'Tuition - Secondary', 'type' => 'revenue', 'level' => 3],
                    ]],
                    ['code' => '4200', 'name' => 'Other School Fees', 'type' => 'revenue', 'level' => 2, 'children' => [
                        ['code' => '4210', 'name' => 'Registration Fees', 'type' => 'revenue', 'level' => 3],
                        ['code' => '4220', 'name' => 'Examination Fees', 'type' => 'revenue', 'level' => 3],
                        ['code' => '4230', 'name' => 'Computer Lab Fees', 'type' => 'revenue', 'level' => 3],
                        ['code' => '4240', 'name' => 'Library Fees', 'type' => 'revenue', 'level' => 3],
                        ['code' => '4250', 'name' => 'Sports Fees', 'type' => 'revenue', 'level' => 3],
                        ['code' => '4260', 'name' => 'Transport Fees', 'type' => 'revenue', 'level' => 3],
                        ['code' => '4270', 'name' => 'Boarding Fees', 'type' => 'revenue', 'level' => 3],
                        ['code' => '4280', 'name' => 'Uniform Sales', 'type' => 'revenue', 'level' => 3],
                        ['code' => '4290', 'name' => 'Book Sales', 'type' => 'revenue', 'level' => 3],
                    ]],
                ],
            ],
            [
                'code' => '4500',
                'name' => 'Other Income',
                'type' => 'revenue',
                'level' => 1,
                'children' => [
                    ['code' => '4510', 'name' => 'Interest Income', 'type' => 'revenue', 'level' => 2],
                    ['code' => '4520', 'name' => 'Rental Income', 'type' => 'revenue', 'level' => 2],
                    ['code' => '4530', 'name' => 'Donations Received', 'type' => 'revenue', 'level' => 2],
                    ['code' => '4540', 'name' => 'Government Grants', 'type' => 'revenue', 'level' => 2],
                    ['code' => '4550', 'name' => 'Fundraising Income', 'type' => 'revenue', 'level' => 2],
                    ['code' => '4590', 'name' => 'Miscellaneous Income', 'type' => 'revenue', 'level' => 2],
                ],
            ],

            // EXPENSES (5000-5999)
            [
                'code' => '5000',
                'name' => 'Staff Expenses',
                'type' => 'expense',
                'level' => 1,
                'children' => [
                    ['code' => '5100', 'name' => 'Salaries & Wages', 'type' => 'expense', 'level' => 2, 'children' => [
                        ['code' => '5110', 'name' => 'Teaching Staff Salaries', 'type' => 'expense', 'level' => 3],
                        ['code' => '5120', 'name' => 'Administrative Staff Salaries', 'type' => 'expense', 'level' => 3],
                        ['code' => '5130', 'name' => 'Support Staff Wages', 'type' => 'expense', 'level' => 3],
                        ['code' => '5140', 'name' => 'Overtime Pay', 'type' => 'expense', 'level' => 3],
                    ]],
                    ['code' => '5200', 'name' => 'Staff Benefits', 'type' => 'expense', 'level' => 2, 'children' => [
                        ['code' => '5210', 'name' => 'NAPSA Contributions', 'type' => 'expense', 'level' => 3],
                        ['code' => '5220', 'name' => 'Medical Insurance', 'type' => 'expense', 'level' => 3],
                        ['code' => '5230', 'name' => 'Staff Meals', 'type' => 'expense', 'level' => 3],
                        ['code' => '5240', 'name' => 'Staff Training', 'type' => 'expense', 'level' => 3],
                        ['code' => '5250', 'name' => 'Staff Uniforms', 'type' => 'expense', 'level' => 3],
                    ]],
                ],
            ],
            [
                'code' => '5300',
                'name' => 'Academic Expenses',
                'type' => 'expense',
                'level' => 1,
                'children' => [
                    ['code' => '5310', 'name' => 'Textbooks & Learning Materials', 'type' => 'expense', 'level' => 2],
                    ['code' => '5320', 'name' => 'Examination Costs', 'type' => 'expense', 'level' => 2],
                    ['code' => '5330', 'name' => 'Laboratory Supplies', 'type' => 'expense', 'level' => 2],
                    ['code' => '5340', 'name' => 'Computer Software & Licenses', 'type' => 'expense', 'level' => 2],
                    ['code' => '5350', 'name' => 'Library Acquisitions', 'type' => 'expense', 'level' => 2],
                    ['code' => '5360', 'name' => 'Sports Equipment', 'type' => 'expense', 'level' => 2],
                    ['code' => '5370', 'name' => 'Field Trips & Excursions', 'type' => 'expense', 'level' => 2],
                ],
            ],
            [
                'code' => '5400',
                'name' => 'Administrative Expenses',
                'type' => 'expense',
                'level' => 1,
                'children' => [
                    ['code' => '5410', 'name' => 'Office Supplies', 'type' => 'expense', 'level' => 2],
                    ['code' => '5420', 'name' => 'Printing & Stationery', 'type' => 'expense', 'level' => 2],
                    ['code' => '5430', 'name' => 'Communication (Phone/Internet)', 'type' => 'expense', 'level' => 2],
                    ['code' => '5440', 'name' => 'Postage & Courier', 'type' => 'expense', 'level' => 2],
                    ['code' => '5450', 'name' => 'Bank Charges', 'type' => 'expense', 'level' => 2],
                    ['code' => '5460', 'name' => 'Professional Fees', 'type' => 'expense', 'level' => 2],
                    ['code' => '5470', 'name' => 'Insurance', 'type' => 'expense', 'level' => 2],
                    ['code' => '5480', 'name' => 'Licenses & Permits', 'type' => 'expense', 'level' => 2],
                    ['code' => '5490', 'name' => 'Advertising & Marketing', 'type' => 'expense', 'level' => 2],
                ],
            ],
            [
                'code' => '5500',
                'name' => 'Facility Expenses',
                'type' => 'expense',
                'level' => 1,
                'children' => [
                    ['code' => '5510', 'name' => 'Rent', 'type' => 'expense', 'level' => 2],
                    ['code' => '5520', 'name' => 'Electricity', 'type' => 'expense', 'level' => 2],
                    ['code' => '5530', 'name' => 'Water', 'type' => 'expense', 'level' => 2],
                    ['code' => '5540', 'name' => 'Repairs & Maintenance', 'type' => 'expense', 'level' => 2],
                    ['code' => '5550', 'name' => 'Cleaning Services', 'type' => 'expense', 'level' => 2],
                    ['code' => '5560', 'name' => 'Security Services', 'type' => 'expense', 'level' => 2],
                    ['code' => '5570', 'name' => 'Gardening & Grounds', 'type' => 'expense', 'level' => 2],
                ],
            ],
            [
                'code' => '5600',
                'name' => 'Transport Expenses',
                'type' => 'expense',
                'level' => 1,
                'children' => [
                    ['code' => '5610', 'name' => 'Fuel', 'type' => 'expense', 'level' => 2],
                    ['code' => '5620', 'name' => 'Vehicle Maintenance', 'type' => 'expense', 'level' => 2],
                    ['code' => '5630', 'name' => 'Vehicle Insurance', 'type' => 'expense', 'level' => 2],
                    ['code' => '5640', 'name' => 'Road Tax & Licenses', 'type' => 'expense', 'level' => 2],
                    ['code' => '5650', 'name' => 'Driver Allowances', 'type' => 'expense', 'level' => 2],
                ],
            ],
            [
                'code' => '5700',
                'name' => 'Other Operating Expenses',
                'type' => 'expense',
                'level' => 1,
                'children' => [
                    ['code' => '5710', 'name' => 'Depreciation Expense', 'type' => 'expense', 'level' => 2],
                    ['code' => '5720', 'name' => 'Bad Debt Expense', 'type' => 'expense', 'level' => 2],
                    ['code' => '5730', 'name' => 'Donations & Contributions', 'type' => 'expense', 'level' => 2],
                    ['code' => '5790', 'name' => 'Miscellaneous Expenses', 'type' => 'expense', 'level' => 2],
                ],
            ],
        ];

        // Create accounts recursively
        foreach ($accounts as $account) {
            $this->createAccount($account, $categoryIds, null);
        }

        $this->command->info('Chart of Accounts seeded successfully!');
    }

    private function createAccount(array $data, array $categoryIds, ?int $parentId): void
    {
        $type = $data['type'];
        $categoryId = $categoryIds[$type] ?? null;

        // Map account category to debit/credit normal balance
        $accountType = match ($type) {
            'asset', 'expense' => 'debit',
            'liability', 'equity', 'revenue' => 'credit',
            default => 'debit',
        };

        $account = ChartOfAccount::firstOrCreate(
            ['code' => $data['code']],
            [
                'name' => $data['name'],
                'account_type' => $accountType,
                'account_category_id' => $categoryId,
                'parent_id' => $parentId,
                'description' => $data['description'] ?? null,
                'is_active' => true,
                'is_system' => false,
                'allow_direct_posting' => !isset($data['children']),
                'level' => $data['level'] ?? 1,
                'opening_balance' => 0,
                'current_balance' => 0,
            ]
        );

        // Create children if any
        if (isset($data['children'])) {
            foreach ($data['children'] as $child) {
                $this->createAccount($child, $categoryIds, $account->id);
            }
        }
    }
}
