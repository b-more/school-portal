<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use App\Models\ParentGuardian;
use App\Models\Student;
use App\Models\FeePayment;
use App\Models\Event;
use App\Models\HomeworkSubmission;
use App\Models\Result;
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
            ->get();
    }

    public function getFeePayments()
    {
        $parentGuardian = $this->getParentGuardian();

        if (!$parentGuardian) {
            return collect();
        }

        $studentIds = $parentGuardian->students()->pluck('id')->toArray();

        if (empty($studentIds)) {
            return collect();
        }

        return FeePayment::whereIn('student_id', $studentIds)
            ->with(['student', 'student.grade'])
            ->latest('payment_date')
            ->take(5)
            ->get();
    }

    public function getRecentHomework()
    {
        $parentGuardian = $this->getParentGuardian();

        if (!$parentGuardian) {
            return collect();
        }

        $studentIds = $parentGuardian->students()->pluck('id')->toArray();

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

        $studentIds = $parentGuardian->students()->pluck('id')->toArray();

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

        $gradeIds = $parentGuardian->students()->pluck('grade_id')->unique()->toArray();

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

    protected function getHeaderActions(): array
    {
        return [
            Action::make('view_children')
                ->label('My Children')
                ->icon('heroicon-o-academic-cap')
                ->url(route('filament.admin.resources.students.index')),

            Action::make('view_homework')
                ->label('Homework')
                ->icon('heroicon-o-document-text')
                ->url(route('filament.admin.resources.teacher-homework-submissions.index')),

            Action::make('view_results')
                ->label('Results')
                ->icon('heroicon-o-clipboard-document-check')
                ->url(route('filament.admin.resources.teacher-results.index')),

            Action::make('view_payments')
                ->label('Fee Payments')
                ->icon('heroicon-o-receipt-percent')
                ->url(route('filament.admin.resources.fee-payments.index')),
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

}
