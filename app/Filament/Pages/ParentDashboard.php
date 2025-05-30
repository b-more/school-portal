<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use App\Models\ParentGuardian;
use App\Models\Student;
use App\Models\FeePayment;
use App\Models\Event;
use App\Models\Homework;
use App\Models\HomeworkSubmission;
use App\Models\Result;
use App\Filament\Widgets\ParentHomeworkWidget;
use App\Constants\RoleConstants;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Auth;

class ParentDashboard extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-home';
    protected static string $view = 'filament.pages.parent-dashboard';
    protected static ?string $navigationLabel = 'Dashboard';
    protected static ?int $navigationSort = 1;

    /**
     * Get widgets for the parent dashboard
     */
    protected function getHeaderWidgets(): array
    {
        return [
            ParentHomeworkWidget::class,
        ];
    }

    public function getParentGuardian()
    {
        $user = Auth::user();
        return ParentGuardian::where('user_id', $user->id)->first();
    }

    public function getStudents()
    {
        $parentGuardian = $this->getParentGuardian();

        if (!$parentGuardian) {
            return collect();
        }

        return $parentGuardian->students()
            ->with(['grade', 'classSection'])
            ->where('enrollment_status', 'active')
            ->get();
    }

    public function getFeePayments()
    {
        $parentGuardian = $this->getParentGuardian();

        if (!$parentGuardian) {
            return collect();
        }

        $studentIds = $parentGuardian->students()
            ->where('enrollment_status', 'active')
            ->pluck('id')
            ->toArray();

        if (empty($studentIds)) {
            return collect();
        }

        return FeePayment::whereIn('student_id', $studentIds)
            ->with(['student', 'student.grade'])
            ->latest('payment_date')
            ->take(5)
            ->get();
    }

    /**
     * Get recent homework for children (updated to use Homework model directly)
     */
    public function getRecentHomework()
    {
        $parentGuardian = $this->getParentGuardian();

        if (!$parentGuardian) {
            return collect();
        }

        // Get children's grade IDs
        $childrenGradeIds = $parentGuardian->students()
            ->where('enrollment_status', 'active')
            ->pluck('grade_id')
            ->unique();

        if ($childrenGradeIds->isEmpty()) {
            return collect();
        }

        return Homework::whereIn('grade_id', $childrenGradeIds)
            ->where('status', 'active')
            ->with(['subject', 'grade', 'assignedBy'])
            ->orderBy('due_date', 'asc')
            ->take(5)
            ->get();
    }

    /**
     * Get homework submissions by children
     */
    public function getHomeworkSubmissions()
    {
        $parentGuardian = $this->getParentGuardian();

        if (!$parentGuardian) {
            return collect();
        }

        $studentIds = $parentGuardian->students()
            ->where('enrollment_status', 'active')
            ->pluck('id')
            ->toArray();

        if (empty($studentIds)) {
            return collect();
        }

        return HomeworkSubmission::whereIn('student_id', $studentIds)
            ->with(['homework.subject', 'homework.assignedBy', 'student'])
            ->latest()
            ->take(5)
            ->get();
    }

    public function getRecentResults()
    {
        $parentGuardian = $this->getParentGuardian();

        if (!$parentGuardian) {
            return collect();
        }

        $studentIds = $parentGuardian->students()
            ->where('enrollment_status', 'active')
            ->pluck('id')
            ->toArray();

        if (empty($studentIds)) {
            return collect();
        }

        return Result::whereIn('student_id', $studentIds)
            ->with(['student', 'subject'])
            ->latest()
            ->take(5)
            ->get();
    }

    public function getUpcomingEvents()
    {
        $parentGuardian = $this->getParentGuardian();

        if (!$parentGuardian) {
            return collect();
        }

        $gradeIds = $parentGuardian->students()
            ->where('enrollment_status', 'active')
            ->pluck('grade_id')
            ->unique()
            ->toArray();

        if (empty($gradeIds)) {
            return collect();
        }

        return Event::where('start_date', '>=', now())
            ->where(function ($query) use ($gradeIds) {
                $query->whereIn('applicable_to', $gradeIds)
                    ->orWhere('applicable_to', 'all')
                    ->orWhereNull('applicable_to');
            })
            ->orderBy('start_date')
            ->take(5)
            ->get();
    }

    /**
     * Get dashboard statistics
     */
    public function getDashboardStats()
    {
        $parentGuardian = $this->getParentGuardian();

        if (!$parentGuardian) {
            return [
                'children_count' => 0,
                'pending_homework' => 0,
                'overdue_homework' => 0,
                'recent_results' => 0,
                'upcoming_events' => 0,
            ];
        }

        $studentIds = $parentGuardian->students()
            ->where('enrollment_status', 'active')
            ->pluck('id')
            ->toArray();

        $gradeIds = $parentGuardian->students()
            ->where('enrollment_status', 'active')
            ->pluck('grade_id')
            ->unique()
            ->toArray();

        return [
            'children_count' => count($studentIds),
            'pending_homework' => Homework::whereIn('grade_id', $gradeIds)
                ->where('status', 'active')
                ->where('due_date', '>=', now())
                ->count(),
            'overdue_homework' => Homework::whereIn('grade_id', $gradeIds)
                ->where('status', 'active')
                ->where('due_date', '<', now())
                ->count(),
            'recent_results' => Result::whereIn('student_id', $studentIds)
                ->where('created_at', '>=', now()->subDays(30))
                ->count(),
            'upcoming_events' => Event::where('start_date', '>=', now())
                ->where('start_date', '<=', now()->addDays(7))
                ->where(function ($query) use ($gradeIds) {
                    $query->whereIn('applicable_to', $gradeIds)
                        ->orWhere('applicable_to', 'all')
                        ->orWhereNull('applicable_to');
                })
                ->count(),
        ];
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('view_children')
                ->label('My Children')
                ->icon('heroicon-o-academic-cap')
                ->color('info')
                ->url(route('filament.admin.resources.students.index'))
                ->tooltip('View and manage your children\'s information'),

            Action::make('view_homework')
                ->label('All Homework')
                ->icon('heroicon-o-document-text')
                ->color('warning')
                ->url(route('filament.admin.resources.homework.index'))
                ->tooltip('View all homework assignments for your children'),

            Action::make('view_results')
                ->label('Academic Results')
                ->icon('heroicon-o-clipboard-document-check')
                ->color('success')
                ->url(route('filament.admin.resources.results.index'))
                ->tooltip('View your children\'s academic results'),

            Action::make('view_payments')
                ->label('Fee Payments')
                ->icon('heroicon-o-banknotes')
                ->color('gray')
                ->url(route('filament.admin.resources.fee-payments.index'))
                ->tooltip('View fee payment history and statements'),

            Action::make('refresh_dashboard')
                ->label('Refresh')
                ->icon('heroicon-o-arrow-path')
                ->color('gray')
                ->action(function () {
                    Notification::make()
                        ->title('Dashboard Refreshed')
                        ->success()
                        ->send();

                    return redirect()->to(request()->getUri());
                })
                ->tooltip('Refresh dashboard data'),
        ];
    }

    /**
     * Get view data for the dashboard template
     */
    protected function getViewData(): array
    {
        return [
            'parentGuardian' => $this->getParentGuardian(),
            'students' => $this->getStudents(),
            'recentHomework' => $this->getRecentHomework(),
            'homeworkSubmissions' => $this->getHomeworkSubmissions(),
            'feePayments' => $this->getFeePayments(),
            'recentResults' => $this->getRecentResults(),
            'upcomingEvents' => $this->getUpcomingEvents(),
            'dashboardStats' => $this->getDashboardStats(),
        ];
    }

    public static function canAccess(): bool
    {
        return auth()->user()?->role_id === RoleConstants::PARENT ?? false;
    }

    public static function shouldRegisterNavigation(): bool
    {
        return auth()->user()?->role_id === RoleConstants::PARENT ?? false;
    }

    /**
     * Get the page title with parent's name
     */
    public function getTitle(): string
    {
        $parentGuardian = $this->getParentGuardian();

        if ($parentGuardian) {
            return "Welcome, {$parentGuardian->name}";
        }

        return "Parent Dashboard";
    }

    /**
     * Get the page heading
     */
    public function getHeading(): string
    {
        $parentGuardian = $this->getParentGuardian();
        $stats = $this->getDashboardStats();

        if ($parentGuardian && $stats['children_count'] > 0) {
            $childrenText = $stats['children_count'] === 1 ? 'child' : 'children';
            return "Dashboard - {$stats['children_count']} {$childrenText} enrolled";
        }

        return "Parent Dashboard";
    }

    /**
     * Get subheading with quick stats
     */
    public function getSubheading(): ?string
    {
        $stats = $this->getDashboardStats();

        if ($stats['children_count'] === 0) {
            return "No children enrolled at this time";
        }

        $messages = [];

        if ($stats['overdue_homework'] > 0) {
            $messages[] = "{$stats['overdue_homework']} overdue homework";
        }

        if ($stats['pending_homework'] > 0) {
            $messages[] = "{$stats['pending_homework']} pending homework";
        }

        if ($stats['upcoming_events'] > 0) {
            $messages[] = "{$stats['upcoming_events']} upcoming events";
        }

        return empty($messages) ? "All caught up! 🎉" : implode(' • ', $messages);
    }
}
