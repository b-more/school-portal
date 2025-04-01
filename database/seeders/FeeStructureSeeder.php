<?php

namespace Database\Seeders;

use App\Models\FeeStructure;
use App\Models\Student;
use App\Models\StudentFee;
use Illuminate\Database\Seeder;

class FeeStructureSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $grades = ['Grade 1', 'Grade 2', 'Grade 3', 'Grade 8', 'Grade 9'];
        $terms = ['First Term', 'Second Term', 'Third Term'];
        $academicYear = '2023-2024';

        // Create a fee structure for each grade and term
        foreach ($grades as $grade) {
            $basicFee = $grade == 'Grade 1' ? 1500 :
                       ($grade == 'Grade 2' ? 1600 :
                       ($grade == 'Grade 3' ? 1700 :
                       ($grade == 'Grade 8' ? 2500 : 3000)));

            // Higher fees for higher grades
            $additionalCharges = [
                ['description' => 'Books and Stationery', 'amount' => $grade == 'Grade 8' || $grade == 'Grade 9' ? 500 : 300],
                ['description' => 'Sports Fee', 'amount' => 150],
                ['description' => 'Technology Fee', 'amount' => $grade == 'Grade 8' || $grade == 'Grade 9' ? 300 : 150],
                ['description' => 'Development Fund', 'amount' => 200],
            ];

            $totalFee = $basicFee + array_sum(array_column($additionalCharges, 'amount'));

            foreach ($terms as $term) {
                $feeStructure = FeeStructure::create([
                    'grade' => $grade,
                    'term' => $term,
                    'academic_year' => $academicYear,
                    'basic_fee' => $basicFee,
                    'additional_charges' => $additionalCharges,
                    'total_fee' => $totalFee,
                    'description' => "{$grade} {$term} {$academicYear} Fee Structure",
                    'is_active' => true,
                ]);

                // Create student fees for this fee structure
                $students = Student::where('grade', $grade)
                                  ->where('enrollment_status', 'active')
                                  ->get();

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

                    StudentFee::create([
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
            }
        }
    }
}
