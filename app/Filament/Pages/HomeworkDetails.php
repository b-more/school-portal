<?php

namespace App\Filament\Pages;

use App\Models\Homework;
use App\Models\HomeworkSubmission;
use App\Models\Student;
use Filament\Pages\Page;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Storage;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Redirect;

class HomeworkDetails extends Page
{
    // Page configuration
    protected static ?string $navigationIcon = 'heroicon-o-document-text';
    protected static ?string $navigationLabel = 'Homework Details';
    protected static bool $shouldRegisterNavigation = false; // Don't show in navigation menu
    protected static string $view = 'filament.pages.homework-details';

    // Route parameters
    public ?int $homeworkId = null;
    public ?int $childId = null;

    // Properties to store data
    public ?Homework $homework = null;
    public ?Student $student = null;
    public ?HomeworkSubmission $submission = null;

    public function mount(int $homeworkId, ?int $childId = null): void
    {
        // Get the authenticated user's parent record
        $parent = auth()->user()->parentGuardian;

        if (!$parent) {
            Notification::make()
                ->title('Access Denied')
                ->body('You must be logged in as a parent to view homework')
                ->danger()
                ->send();

            $this->redirect(route('filament.admin.pages.dashboard'));
            return;
        }

        // Get the homework
        $this->homework = Homework::findOrFail($homeworkId);

        // Get the student if childId is provided, otherwise default to the first child
        if ($childId) {
            // Verify the child belongs to this parent
            $this->student = $parent->students()
                ->where('id', $childId)
                ->where('enrollment_status', 'active')
                ->first();
        } else {
            // Get any child in this grade
            $this->student = $parent->students()
                ->where('grade', $this->homework->grade)
                ->where('enrollment_status', 'active')
                ->first();
        }

        if (!$this->student) {
            Notification::make()
                ->title('Student Not Found')
                ->body('The selected student was not found or is not assigned to your account')
                ->danger()
                ->send();

            $this->redirect(route('filament.admin.pages.view-homework'));
            return;
        }

        // Check if this student has a submission for this homework
        $this->submission = HomeworkSubmission::where('homework_id', $this->homework->id)
            ->where('student_id', $this->student->id)
            ->first();

        // Page title
        $this->title = $this->homework->title;
    }

    protected function getHeaderActions(): array
    {
        $actions = [
            Action::make('back')
                ->label('Back to Homework List')
                ->url(route('filament.admin.pages.view-homework'))
                ->color('secondary'),
        ];

        // Add download action if homework has files
        if ($this->homework && $this->homework->homework_file) {
            $actions[] = Action::make('download')
                ->label('Download Homework')
                ->url(route('homework.download', $this->homework))
                ->color('primary')
                ->icon('heroicon-o-arrow-down-tray')
                ->openUrlInNewTab();
        }

        return $actions;
    }

    protected function getViewData(): array
    {
        return [
            'homework' => $this->homework,
            'student' => $this->student,
            'submission' => $this->submission,
            'canSubmit' => $this->homework->isSubmissionOpen(),
            'isLate' => $this->homework->isLateSubmission(),
            'submissionStatus' => $this->homework->getSubmissionStatusText(),
        ];
    }
}
