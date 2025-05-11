<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use App\Models\Student;
use App\Models\Homework;
use App\Models\HomeworkSubmission;
use App\Models\Result;
use App\Models\Event;
use App\Models\FeePayment;
use App\Constants\RoleConstants;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Auth;

class StudentDashboard extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-document-text';
    protected static string $view = 'filament.pages.student-dashboard';
    protected static ?string $navigationLabel = 'Dashboard';
    protected static ?int $navigationSort = 1;

    public function getStudent()
    {
        $user = Auth::user();
        return Student::where('user_id', $user->id)
            ->with(['grade', 'classSection', 'parentGuardian'])
            ->first();
    }

    public function getPendingHomework()
    {
        $student = $this->getStudent();

        if (!$student) {
            return collect();
        }

        // Get homework for student's grade that hasn't been submitted yet
        return Homework::where('grade_id', $student->grade_id)
            ->where('status', 'active')
            ->whereDoesntHave('submissions', function ($query) use ($student) {
                $query->where('student_id', $student->id);
            })
            ->with(['subject', 'assignedBy'])
            ->orderBy('due_date')
            ->get();
    }

    public function getRecentHomeworkSubmissions()
    {
        $student = $this->getStudent();

        if (!$student) {
            return collect();
        }

        return HomeworkSubmission::where('student_id', $student->id)
            ->with(['homework.subject', 'homework.assignedBy'])
            ->latest()
            ->take(5)
            ->get();
    }

    public function getRecentResults()
    {
        $student = $this->getStudent();

        if (!$student) {
            return collect();
        }

        return Result::where('student_id', $student->id)
            ->with(['subject'])
            ->latest()
            ->take(5)
            ->get();
    }

    public function getUpcomingEvents()
    {
        $student = $this->getStudent();

        if (!$student) {
            return collect();
        }

        return Event::where('start_date', '>=', now())
            ->where(function($query) use ($student) {
                $query->where('applicable_to', $student->grade_id)
                    ->orWhere('applicable_to', 'all')
                    ->orWhere('applicable_to', 'students')
                    ->orWhereNull('applicable_to');
            })
            ->orderBy('start_date')
            ->take(5)
            ->get();
    }

    public function getAcademicSummary()
    {
        $student = $this->getStudent();

        if (!$student) {
            return [
                'total_homework' => 0,
                'submitted' => 0,
                'pending' => 0,
                'graded' => 0,
                'average_grade' => 0,
            ];
        }

        $homeworkIds = Homework::where('grade_id', $student->grade_id)->pluck('id');
        $submissions = HomeworkSubmission::where('student_id', $student->id);
        $results = Result::where('student_id', $student->id);

        return [
            'total_homework' => $homeworkIds->count(),
            'submitted' => $submissions->count(),
            'pending' => $this->getPendingHomework()->count(),
            'graded' => $submissions->whereNotNull('marks')->count(),
            'average_grade' => $results->avg('marks'),
        ];
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('view_homework')
                ->label('My Homework')
                ->icon('heroicon-o-document-text')
                ->url(route('filament.admin.resources.teacher-homework-submissions.index')),

            Action::make('view_results')
                ->label('My Results')
                ->icon('heroicon-o-clipboard-document-check')
                ->url(route('filament.admin.resources.teacher-results.index')),

            Action::make('view_schedule')
                ->label('Class Schedule')
                ->icon('heroicon-o-calendar-days')
                ->action(function () {
                    Notification::make()
                        ->title('Schedule feature coming soon')
                        ->body('The class schedule feature will be available soon.')
                        ->info()
                        ->send();
                }),
        ];
    }

    public static function canAccess(): bool
    {
        return auth()->user()?->role_id === RoleConstants::STUDENT ?? false;
    }

    public static function shouldRegisterNavigation(): bool
    {
        return auth()->user()?->role_id === RoleConstants::STUDENT ?? false;
    }
}
