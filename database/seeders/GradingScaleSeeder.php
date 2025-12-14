<?php

namespace Database\Seeders;

use App\Models\GradingScale;
use Illuminate\Database\Seeder;

class GradingScaleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Primary Grading Scale (Baby Class - Grade 7)
        $primaryScale = GradingScale::firstOrCreate(
            ['name' => 'Primary Grading Scale'],
            [
                'grade_level' => 'primary',
                'description' => 'Grading scale for Baby Class through Grade 7',
                'is_default' => true,
                'is_active' => true,
            ]
        );

        $primaryGrades = [
            ['grade' => 'A', 'min_marks' => 80, 'max_marks' => 100, 'grade_points' => 4.0, 'remark' => 'Excellent', 'sort_order' => 1],
            ['grade' => 'B', 'min_marks' => 70, 'max_marks' => 79, 'grade_points' => 3.0, 'remark' => 'Very Good', 'sort_order' => 2],
            ['grade' => 'C', 'min_marks' => 60, 'max_marks' => 69, 'grade_points' => 2.0, 'remark' => 'Good', 'sort_order' => 3],
            ['grade' => 'D', 'min_marks' => 50, 'max_marks' => 59, 'grade_points' => 1.0, 'remark' => 'Satisfactory', 'sort_order' => 4],
            ['grade' => 'E', 'min_marks' => 40, 'max_marks' => 49, 'grade_points' => 0.5, 'remark' => 'Fair', 'sort_order' => 5],
            ['grade' => 'F', 'min_marks' => 0, 'max_marks' => 39, 'grade_points' => 0.0, 'remark' => 'Needs Improvement', 'sort_order' => 6],
        ];

        foreach ($primaryGrades as $gradeData) {
            $primaryScale->items()->firstOrCreate(
                ['grade' => $gradeData['grade']],
                $gradeData
            );
        }

        // Secondary Grading Scale (Grade 8 - Grade 12)
        $secondaryScale = GradingScale::firstOrCreate(
            ['name' => 'Secondary Grading Scale'],
            [
                'grade_level' => 'secondary',
                'description' => 'Grading scale for Grade 8 through Grade 12',
                'is_default' => true,
                'is_active' => true,
            ]
        );

        $secondaryGrades = [
            ['grade' => 'A', 'min_marks' => 75, 'max_marks' => 100, 'grade_points' => 4.0, 'remark' => 'Distinction', 'sort_order' => 1],
            ['grade' => 'B', 'min_marks' => 65, 'max_marks' => 74, 'grade_points' => 3.0, 'remark' => 'Merit', 'sort_order' => 2],
            ['grade' => 'C', 'min_marks' => 55, 'max_marks' => 64, 'grade_points' => 2.0, 'remark' => 'Credit', 'sort_order' => 3],
            ['grade' => 'D', 'min_marks' => 45, 'max_marks' => 54, 'grade_points' => 1.0, 'remark' => 'Pass', 'sort_order' => 4],
            ['grade' => 'E', 'min_marks' => 35, 'max_marks' => 44, 'grade_points' => 0.5, 'remark' => 'Fair', 'sort_order' => 5],
            ['grade' => 'F', 'min_marks' => 0, 'max_marks' => 34, 'grade_points' => 0.0, 'remark' => 'Fail', 'sort_order' => 6],
        ];

        foreach ($secondaryGrades as $gradeData) {
            $secondaryScale->items()->firstOrCreate(
                ['grade' => $gradeData['grade']],
                $gradeData
            );
        }

        $this->command->info('Grading scales seeded successfully!');
    }
}
