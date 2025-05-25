<?php

namespace Database\Seeders;

use App\Models\AcademicYear;
use App\Models\Term;
use Illuminate\Database\Seeder;
use Carbon\Carbon;

class AcademicYearAndTermSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Define academic years (past, current, and future years)
        $academicYears = [
            [
                'name' => '2023',
                'start_date' => '2023-01-09',
                'end_date' => '2023-12-15',
                'is_active' => false,
                'description' => 'Academic Year 2023',
                'number_of_terms' => 3,
                'terms' => [
                    [
                        'name' => 'Term 1',
                        'start_date' => '2023-01-09',
                        'end_date' => '2023-04-14',
                        'is_active' => false,
                    ],
                    [
                        'name' => 'Term 2',
                        'start_date' => '2023-05-08',
                        'end_date' => '2023-08-18',
                        'is_active' => false,
                    ],
                    [
                        'name' => 'Term 3',
                        'start_date' => '2023-09-11',
                        'end_date' => '2023-12-15',
                        'is_active' => false,
                    ],
                ]
            ],
            [
                'name' => '2024',
                'start_date' => '2024-01-08',
                'end_date' => '2024-12-13',
                'is_active' => false,
                'description' => 'Academic Year 2024',
                'number_of_terms' => 3,
                'terms' => [
                    [
                        'name' => 'Term 1',
                        'start_date' => '2024-01-08',
                        'end_date' => '2024-04-12',
                        'is_active' => false,
                    ],
                    [
                        'name' => 'Term 2',
                        'start_date' => '2024-05-06',
                        'end_date' => '2024-08-16',
                        'is_active' => false,
                    ],
                    [
                        'name' => 'Term 3',
                        'start_date' => '2024-09-09',
                        'end_date' => '2024-12-13',
                        'is_active' => false,
                    ],
                ]
            ],
            [
                'name' => '2025',
                'start_date' => '2025-01-06',
                'end_date' => '2025-12-12',
                'is_active' => true, // Current active academic year
                'description' => 'Academic Year 2025 - Current',
                'number_of_terms' => 3,
                'terms' => [
                    [
                        'name' => 'Term 1',
                        'start_date' => '2025-01-06',
                        'end_date' => '2025-04-11',
                        'is_active' => false, // Past term
                    ],
                    [
                        'name' => 'Term 2',
                        'start_date' => '2025-05-05',
                        'end_date' => '2025-08-15',
                        'is_active' => true, // Current active term (May 2025)
                    ],
                    [
                        'name' => 'Term 3',
                        'start_date' => '2025-09-08',
                        'end_date' => '2025-12-12',
                        'is_active' => false, // Future term
                    ],
                ]
            ],
            [
                'name' => '2026',
                'start_date' => '2026-01-05',
                'end_date' => '2026-12-11',
                'is_active' => false,
                'description' => 'Academic Year 2026',
                'number_of_terms' => 3,
                'terms' => [
                    [
                        'name' => 'Term 1',
                        'start_date' => '2026-01-05',
                        'end_date' => '2026-04-10',
                        'is_active' => false,
                    ],
                    [
                        'name' => 'Term 2',
                        'start_date' => '2026-05-04',
                        'end_date' => '2026-08-14',
                        'is_active' => false,
                    ],
                    [
                        'name' => 'Term 3',
                        'start_date' => '2026-09-07',
                        'end_date' => '2026-12-11',
                        'is_active' => false,
                    ],
                ]
            ],
        ];

        // First, disable all active academic years and terms to avoid conflicts
        AcademicYear::where('is_active', true)->update(['is_active' => false]);
        Term::where('is_active', true)->update(['is_active' => false]);

        // Create or update each academic year and its terms
        foreach ($academicYears as $yearData) {
            $terms = $yearData['terms'];
            unset($yearData['terms']);

            // Convert string dates to Carbon instances
            $yearData['start_date'] = Carbon::parse($yearData['start_date']);
            $yearData['end_date'] = Carbon::parse($yearData['end_date']);

            // Create or update the academic year
            $academicYear = AcademicYear::updateOrCreate(
                ['name' => $yearData['name']],
                $yearData
            );

            // Create or update each term for this academic year
            foreach ($terms as $termData) {
                // Convert string dates to Carbon instances
                $termData['start_date'] = Carbon::parse($termData['start_date']);
                $termData['end_date'] = Carbon::parse($termData['end_date']);
                $termData['academic_year_id'] = $academicYear->id;

                Term::updateOrCreate(
                    [
                        'name' => $termData['name'],
                        'academic_year_id' => $academicYear->id
                    ],
                    $termData
                );
            }

            $this->command->info("Created/Updated Academic Year: {$academicYear->name} with " . count($terms) . " terms");
        }

        // Validate current active settings
        $activeAcademicYear = AcademicYear::where('is_active', true)->first();
        $activeTerm = Term::where('is_active', true)->first();

        if ($activeAcademicYear) {
            $this->command->info("✅ Active Academic Year: {$activeAcademicYear->name}");
        }

        if ($activeTerm) {
            $this->command->info("✅ Active Term: {$activeTerm->name} (Academic Year: {$activeTerm->academicYear->name})");
        }

        // Auto-detect and set current term based on today's date
        $this->autoDetectCurrentTerm();

        $this->command->info('✅ Academic years and terms seeded successfully for 2025!');
    }

    /**
     * Automatically detect and set the current term based on today's date
     */
    private function autoDetectCurrentTerm(): void
    {
        $today = Carbon::now();

        // Find the term that contains today's date
        $currentTerm = Term::where('start_date', '<=', $today)
            ->where('end_date', '>=', $today)
            ->first();

        if ($currentTerm) {
            // Set all terms to inactive first
            Term::where('is_active', true)->update(['is_active' => false]);

            // Set the detected term as active
            $currentTerm->update(['is_active' => true]);

            // Also ensure its academic year is active
            $currentTerm->academicYear->update(['is_active' => true]);

            $this->command->info("🎯 Auto-detected current term: {$currentTerm->name} ({$currentTerm->academicYear->name})");
            $this->command->info("📅 Term period: {$currentTerm->start_date->format('M j, Y')} - {$currentTerm->end_date->format('M j, Y')}");
        } else {
            $this->command->warn("⚠️  No current term found for today's date ({$today->format('M j, Y')})");

            // Find the next upcoming term
            $nextTerm = Term::where('start_date', '>', $today)
                ->orderBy('start_date')
                ->first();

            if ($nextTerm) {
                $daysUntil = $today->diffInDays($nextTerm->start_date);
                $this->command->info("📅 Next term: {$nextTerm->name} starts in {$daysUntil} days ({$nextTerm->start_date->format('M j, Y')})");
            }
        }
    }
}
