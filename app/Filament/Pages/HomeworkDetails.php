<?php

namespace App\Filament\Pages;

use App\Constants\RoleConstants;
use App\Models\Homework;
use App\Models\HomeworkSubmission;
use App\Models\Student;
use Filament\Pages\Page;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Auth;

class HomeworkDetails extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-document-text';
    protected static ?string $navigationLabel = 'Homework Details';
    protected static bool $shouldRegisterNavigation = false;
    protected static string $view = 'filament.pages.homework-details';

    public static function getRouteName(?string $panel = null): string
    {
        return 'filament.admin.pages.homework-details';
    }

    public static function getRoutes(): \Closure
    {
        return function () {
            \Illuminate\Support\Facades\Route::get('/homework-details/{homeworkId}', static::class)
                ->name('filament.admin.pages.homework-details');
        };
    }

    public ?int $homeworkId = null;
    public ?Homework $homework = null;
    public ?Student $student = null;

    public function mount(int $homeworkId): void
    {
        $user = Auth::user();

        // Get the homework with relationships
        $this->homework = Homework::with(['subject', 'grade', 'assignedBy'])->findOrFail($homeworkId);
        $this->homeworkId = $homeworkId;

        // Handle different user roles
        if ($user->role_id === RoleConstants::STUDENT) {
            $this->student = Student::where('user_id', $user->id)->first();

            if (!$this->student) {
                Notification::make()
                    ->title('Access Denied')
                    ->body('Student profile not found.')
                    ->danger()
                    ->send();

                $this->redirect(route('filament.admin.pages.student-dashboard'));
                return;
            }

            // Check if homework is for student's grade
            if ($this->homework->grade_id !== $this->student->grade_id) {
                Notification::make()
                    ->title('Access Denied')
                    ->body('This homework is not for your grade.')
                    ->danger()
                    ->send();

                $this->redirect(route('filament.admin.pages.student-dashboard'));
                return;
            }
        } elseif ($user->role_id === RoleConstants::PARENT) {
            $parent = $user->parentGuardian;
            if ($parent) {
                // Get student in this grade
                $this->student = $parent->students()
                    ->where('grade_id', $this->homework->grade_id)
                    ->where('enrollment_status', 'active')
                    ->first();
            }
        }
    }

    public function getTitle(): string
    {
        return $this->homework?->title ?? 'Homework Details';
    }

    public function getSubmission(): ?HomeworkSubmission
    {
        if (!$this->student || !$this->homework) {
            return null;
        }

        return HomeworkSubmission::where('homework_id', $this->homework->id)
            ->where('student_id', $this->student->id)
            ->first();
    }

    protected function getHeaderActions(): array
    {
        $actions = [];

        // Add submit action if not yet submitted and not past due
        if ($this->student && !$this->getSubmission() && !$this->homework->due_date->isPast()) {
            $actions[] = Action::make('submit')
                ->label('Submit Homework')
                ->icon('heroicon-o-arrow-up-tray')
                ->color('primary')
                ->url(route('filament.admin.resources.homework-submissions.create', ['homework_id' => $this->homework->id]));
        }

        // Add download action if homework has files
        if ($this->homework && $this->homework->homework_file) {
            $actions[] = Action::make('download')
                ->label('Download')
                ->icon('heroicon-o-arrow-down-tray')
                ->color('gray')
                ->url(route('homework.download', $this->homework))
                ->openUrlInNewTab();
        }

        return $actions;
    }

    public static function canAccess(): bool
    {
        $user = auth()->user();
        return in_array($user?->role_id, [
            RoleConstants::ADMIN,
            RoleConstants::TEACHER,
            RoleConstants::STUDENT,
            RoleConstants::PARENT,
        ]);
    }

    public static function shouldRegisterNavigation(): bool
    {
        return false;
    }
}
