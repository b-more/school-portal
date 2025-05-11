<?php

namespace Database\Seeders;

use App\Models\FeeStructure;
use App\Models\Student;
use Illuminate\Database\Seeder;
use App\Models\Grade;
use App\Models\AcademicYear;
use App\Models\Term;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class FeeStructureSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // First, check if the StudentFee model exists
        // If not, we'll use direct database operations
        $studentFeeModelExists = class_exists('App\Models\StudentFee');

        // First truncate existing fee structures and student fees
        if ($studentFeeModelExists) {
            \App\Models\StudentFee::truncate();
        } else if (Schema::hasTable('student_fees')) {
            DB::table('student_fees')->truncate();
        }

        FeeStructure::truncate();

        // Create or get the academic year - now using single year format
        $academicYear = AcademicYear::firstOrCreate(
            ['name' => '2025'],
            [
                'start_date' => '2025-01-01',
                'end_date' => '2025-12-31',
                'is_active' => true,
                'description' => 'Academic Year 2025',
                'number_of_terms' => 3,
            ]
        );

        // Create terms for this academic year if they don't exist
        $termNames = ['Term 1', 'Term 2', 'Term 3'];
        $termDates = [
            ['2025-01-05', '2025-04-30'],  // Term 1: Jan-Apr
            ['2025-05-05', '2025-08-31'],  // Term 2: May-Aug
            ['2025-09-01', '2025-12-15'],  // Term 3: Sep-Dec
        ];

        $terms = [];
        foreach ($termNames as $index => $termName) {
            $terms[$termName] = Term::firstOrCreate(
                [
                    'name' => $termName,
                    'academic_year_id' => $academicYear->id,
                ],
                [
                    'start_date' => $termDates[$index][0],
                    'end_date' => $termDates[$index][1],
                    'is_active' => $index === 0, // First term is active
                ]
            );
        }

        // Get grades
        $gradeNames = ['Grade 1', 'Grade 2', 'Grade 3', 'Grade 8', 'Grade 9'];
        $grades = Grade::whereIn('name', $gradeNames)->get();

        if ($grades->count() < count($gradeNames)) {
            $this->command->info('Not all required grades found in the database. Please run the GradeSeeder first.');
            return;
        }

        // Create a fee structure for each grade and term
        foreach ($grades as $grade) {
            // Set fees based on grade level
            $gradeLevel = $grade->level;

            // Calculate basic fee (higher for higher grades)
            $basicFee = 1000 + ($gradeLevel * 100);
            if ($gradeLevel > 7) { // Secondary school grades
                $basicFee = 2000 + (($gradeLevel - 7) * 200);
            }

            // Define additional charges (higher for secondary grades)
            $isSecondary = $grade->schoolSection?->name === 'Secondary';
            $additionalCharges = [
                ['description' => 'Books and Stationery', 'amount' => $isSecondary ? 500 : 300],
                ['description' => 'Sports Fee', 'amount' => 150],
                ['description' => 'Technology Fee', 'amount' => $isSecondary ? 300 : 150],
                ['description' => 'Development Fund', 'amount' => 200],
            ];

            $totalFee = $basicFee + array_sum(array_column($additionalCharges, 'amount'));

            foreach ($terms as $termName => $term) {
                $feeStructure = FeeStructure::create([
                    'grade_id' => $grade->id,
                    'term_id' => $term->id,
                    'academic_year_id' => $academicYear->id,
                    'basic_fee' => $basicFee,
                    'additional_charges' => $additionalCharges,
                    'total_fee' => $totalFee,
                    'description' => "{$grade->name} {$termName} {$academicYear->name} Fee Structure",
                    'is_active' => true,
                ]);

                // Create student fees for this fee structure
                // Get students in this grade using grade_id only
                $students = Student::where('grade_id', $grade->id)
                            ->where('enrollment_status', 'active')
                            ->get();

                // Only create student fees if we have the model
                if ($studentFeeModelExists && count($students) > 0) {
                    foreach ($students as $student) {
                        // Randomly set some as paid, some as partial, and some as unpaid
                        $status = fake()->randomElement(['paid', 'partial', 'unpaid', 'partial']);
                        $amountPaid = 0;
                        $balance = $totalFee;

                        if ($status === 'paid') {
                            $amountPaid = $totalFee;
                            $balance = 0;
                        } elseif ($status === 'partial') {
                            // Pay between 30% and 70% of the total
                            $amountPaid = round($totalFee * (fake()->numberBetween(30, 70) / 100), 2);
                            $balance = $totalFee - $amountPaid;
                        }

                        \App\Models\StudentFee::create([
                            'student_id' => $student->id,
                            'fee_structure_id' => $feeStructure->id,
                            'payment_status' => $status,
                            'amount_paid' => $amountPaid,
                            'balance' => $balance,
                            'payment_date' => $status !== 'unpaid' ? fake()->dateTimeBetween('-3 months', 'now') : null,
                            'receipt_number' => $status !== 'unpaid' ? 'REC-' . fake()->unique()->numberBetween(10000, 99999) : null,
                            'payment_method' => $status !== 'unpaid' ? fake()->randomElement(['cash', 'bank_transfer', 'mobile_money']) : null,
                            'notes' => $status === 'partial' ? 'Balance to be paid by end of term' : null,
                        ]);
                    }
                } elseif (Schema::hasTable('student_fees') && count($students) > 0) {
                    // Use direct DB insertion if the model doesn't exist but the table does
                    foreach ($students as $student) {
                        // Randomly set some as paid, some as partial, and some as unpaid
                        $status = fake()->randomElement(['paid', 'partial', 'unpaid', 'partial']);
                        $amountPaid = 0;
                        $balance = $totalFee;

                        if ($status === 'paid') {
                            $amountPaid = $totalFee;
                            $balance = 0;
                        } elseif ($status === 'partial') {
                            // Pay between 30% and 70% of the total
                            $amountPaid = round($totalFee * (fake()->numberBetween(30, 70) / 100), 2);
                            $balance = $totalFee - $amountPaid;
                        }

                        DB::table('student_fees')->insert([
                            'student_id' => $student->id,
                            'fee_structure_id' => $feeStructure->id,
                            'payment_status' => $status,
                            'amount_paid' => $amountPaid,
                            'balance' => $balance,
                            'payment_date' => $status !== 'unpaid' ? fake()->dateTimeBetween('-3 months', 'now') : null,
                            'receipt_number' => $status !== 'unpaid' ? 'REC-' . fake()->unique()->numberBetween(10000, 99999) : null,
                            'payment_method' => $status !== 'unpaid' ? fake()->randomElement(['cash', 'bank_transfer', 'mobile_money']) : null,
                            'notes' => $status === 'partial' ? 'Balance to be paid by end of term' : null,
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]);
                    }
                }

                $this->command->info("Created fee structure for {$grade->name} - {$termName} (Found {$students->count()} students)");
            }
        }

        $this->command->info('Fee structures created successfully!');
    }
}
