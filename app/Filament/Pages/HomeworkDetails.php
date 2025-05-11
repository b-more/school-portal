<?php

namespace App\Filament\Pages;

use App\Models\Homework;
use App\Models\Student;
use Filament\Pages\Page;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Auth;

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

    public function mount(int $homeworkId, ?int $childId = null): void
    {
        $user = Auth::user();

        // Get the homework
        $this->homework = Homework::findOrFail($homeworkId);

        // Check user permissions and get student context
        if ($user->hasRole('parent')) {
            $parent = $user->parentGuardian;
            if (!$parent) {
                Notification::make()
                    ->title('Access Denied')
                    ->body('You must be logged in as a parent to view homework')
                    ->danger()
                    ->send();

                $this->redirect(route('filament.admin.pages.view-homework'));
                return;
            }

            // Get the student if childId is provided, otherwise default to a child in this grade
            if ($childId) {
                $this->student = $parent->students()
                    ->where('id', $childId)
                    ->where('enrollment_status', 'active')
                    ->first();
            } else {
                $this->student = $parent->students()
                    ->where('grade', $this->homework->grade)
                    ->where('enrollment_status', 'active')
                    ->first();
            }

            if (!$this->student) {
                Notification::make()
                    ->title('Student Not Found')
                    ->body('The selected student was not found or is not in the grade for this homework')
                    ->danger()
                    ->send();

                $this->redirect(route('filament.admin.pages.view-homework'));
                return;
            }
        } elseif ($user->hasRole('student')) {
            $this->student = $user->student;

            if (!$this->student || $this->student->grade !== $this->homework->grade) {
                Notification::make()
                    ->title('Access Denied')
                    ->body('This homework is not for your grade')
                    ->danger()
                    ->send();

                $this->redirect(route('filament.admin.pages.view-homework'));
                return;
            }
        } elseif (!$user->hasRole(['admin', 'teacher'])) {
            abort(403, 'Access denied');
        }

        // Set page title
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
        ];
    }
}
