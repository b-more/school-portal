<?php

namespace App\Filament\Widgets;

use App\Services\StudentFeeService;
use App\Models\AcademicYear;
use App\Models\Term;
use App\Models\Grade;
use App\Models\Student;
use App\Models\StudentFee;
use Filament\Widgets\Widget;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Placeholder;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Log;

class FeeGenerationWidget extends Widget
{
    protected static string $view = 'filament.widgets.fee-generation-widget';

    protected int | string | array $columnSpan = 'full';

    protected static ?int $sort = 1;

    public function getActions(): array
    {
        return [
            Action::make('previewFeeGeneration')
                ->label('Preview Fee Generation')
                ->icon('heroicon-o-eye')
                ->color('info')
                ->form([
                    Select::make('grade_id')
                        ->label('Select Grade (Optional)')
                        ->placeholder('All grades')
                        ->options(Grade::orderBy('level')->pluck('name', 'id'))
                        ->searchable(),
                ])
                ->action(function (array $data) {
                    $grade = isset($data['grade_id']) ? Grade::find($data['grade_id']) : null;
                    $preview = StudentFeeService::previewFeeCreation($grade);

                    if (!$preview['success']) {
                        Notification::make()
                            ->title('Preview Failed')
                            ->body($preview['message'])
                            ->danger()
                            ->send();
                        return;
                    }

                    $gradeText = $grade ? "for {$grade->name}" : "for all grades";
                    $message = "Preview {$gradeText}:\n\n";
                    $message .= "Total Students: {$preview['total_students']}\n";
                    $message .= "Need Fees: {$preview['students_needing_fees']}\n";
                    $message .= "Have Fees: {$preview['students_with_fees']}\n";
                    $message .= "Term: {$preview['current_term']}\n";
                    $message .= "Academic Year: {$preview['current_academic_year']}";

                    Notification::make()
                        ->title('Fee Generation Preview')
                        ->body($message)
                        ->info()
                        ->persistent()
                        ->send();
                }),

            Action::make('generateFees')
                ->label('Generate Fees')
                ->icon('heroicon-o-plus-circle')
                ->color('success')
                ->form([
                    Select::make('grade_id')
                        ->label('Select Grade (Optional)')
                        ->placeholder('All grades')
                        ->options(Grade::orderBy('level')->pluck('name', 'id'))
                        ->searchable(),

                    Placeholder::make('warning')
                        ->content('⚠️ This will create fee records for students who don\'t have fees for the current term. This action cannot be undone.')
                        ->columnSpanFull(),
                ])
                ->action(function (array $data) {
                    $grade = isset($data['grade_id']) ? Grade::find($data['grade_id']) : null;

                    try {
                        $result = StudentFeeService::bulkCreateFeesForCurrentTerm($grade);

                        if ($result['success']) {
                            Notification::make()
                                ->title('Fees Generated Successfully')
                                ->body($result['message'])
                                ->success()
                                ->send();

                            // Log the bulk operation
                            Log::info('Bulk fee generation completed', [
                                'grade_id' => $grade?->id,
                                'created' => $result['created'],
                                'skipped' => $result['skipped'],
                                'errors' => $result['errors'],
                                'admin_id' => auth()->id()
                            ]);
                        } else {
                            Notification::make()
                                ->title('Fee Generation Failed')
                                ->body($result['message'])
                                ->danger()
                                ->send();
                        }
                    } catch (\Exception $e) {
                        Log::error('Fee generation widget error', [
                            'error' => $e->getMessage(),
                            'grade_id' => $grade?->id
                        ]);

                        Notification::make()
                            ->title('Unexpected Error')
                            ->body('An error occurred during fee generation. Please check the logs.')
                            ->danger()
                            ->send();
                    }
                })
                ->requiresConfirmation()
                ->modalHeading('Generate Student Fees')
                ->modalSubheading('Are you sure you want to generate fees for the selected students?'),
        ];
    }

    protected function getViewData(): array
    {
        $currentAcademicYear = AcademicYear::where('is_active', true)->first();
        $currentTerm = Term::where('is_active', true)->first();

        if (!$currentAcademicYear || !$currentTerm) {
            return [
                'hasActiveTerm' => false,
                'message' => 'No active academic year or term found. Please activate a term first.',
                'stats' => []
            ];
        }

        // Get statistics
        $totalActiveStudents = Student::where('enrollment_status', 'active')->count();

        $studentsWithFees = StudentFee::where('academic_year_id', $currentAcademicYear->id)
            ->where('term_id', $currentTerm->id)
            ->distinct('student_id')
            ->count('student_id');

        $studentsNeedingFees = $totalActiveStudents - $studentsWithFees;

        // Get breakdown by grade
        $gradeBreakdown = [];
        $grades = Grade::orderBy('level')->get();

        foreach ($grades as $grade) {
            $gradeStudents = Student::where('grade_id', $grade->id)
                ->where('enrollment_status', 'active')
                ->count();

            $gradeWithFees = StudentFee::where('academic_year_id', $currentAcademicYear->id)
                ->where('term_id', $currentTerm->id)
                ->where('grade_id', $grade->id)
                ->count();

            if ($gradeStudents > 0) {
                $gradeBreakdown[] = [
                    'name' => $grade->name,
                    'total' => $gradeStudents,
                    'with_fees' => $gradeWithFees,
                    'need_fees' => $gradeStudents - $gradeWithFees,
                    'percentage' => $gradeStudents > 0 ? round(($gradeWithFees / $gradeStudents) * 100) : 0
                ];
            }
        }

        return [
            'hasActiveTerm' => true,
            'currentTerm' => $currentTerm->name,
            'currentAcademicYear' => $currentAcademicYear->name,
            'stats' => [
                'total_students' => $totalActiveStudents,
                'students_with_fees' => $studentsWithFees,
                'students_needing_fees' => $studentsNeedingFees,
                'completion_percentage' => $totalActiveStudents > 0 ? round(($studentsWithFees / $totalActiveStudents) * 100) : 0
            ],
            'gradeBreakdown' => $gradeBreakdown
        ];
    }
}
