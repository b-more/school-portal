<?php

namespace App\Filament\Pages;

use App\Constants\RoleConstants;
use App\Models\Teacher;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Auth;

class MySchedule extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-calendar-days';
    protected static string $view = 'filament.pages.my-schedule';
    protected static ?string $navigationLabel = 'My Schedule';
    protected static ?string $navigationGroup = 'Teaching';
    protected static ?int $navigationSort = 2;

    public function getTeacher()
    {
        $user = Auth::user();
        return Teacher::where('user_id', $user->id)
            ->with(['subjectTeachings.subject', 'subjectTeachings.classSection.grade', 'grade', 'classSection'])
            ->first();
    }

    public function getMyClasses()
    {
        $teacher = $this->getTeacher();

        if (!$teacher) {
            return collect();
        }

        return $teacher->subjectTeachings()
            ->with(['subject', 'classSection.grade'])
            ->get()
            ->groupBy('classSection.id');
    }

    public function getMySubjects()
    {
        $teacher = $this->getTeacher();

        if (!$teacher) {
            return collect();
        }

        return $teacher->subjects;
    }

    public static function canAccess(): bool
    {
        return in_array(auth()->user()?->role_id, RoleConstants::teaching()) ?? false;
    }

    public static function shouldRegisterNavigation(): bool
    {
        return in_array(auth()->user()?->role_id, RoleConstants::teaching()) ?? false;
    }
}
