<?php

namespace App\Filament\Pages;

use App\Constants\RoleConstants;
use App\Models\AcademicYear;
use App\Models\Teacher;
use App\Models\TimetableEntry;
use App\Models\TimetablePeriod;
use Filament\Pages\Page;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;

class MySchedule extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-calendar-days';
    protected static string $view = 'filament.pages.my-schedule';
    protected static ?string $navigationLabel = 'My Schedule';
    protected static ?string $navigationGroup = 'Teaching';
    protected static ?int $navigationSort = 2;

    public function getTeacher(): ?Teacher
    {
        $user = Auth::user();
        return Teacher::where('user_id', $user->id)
            ->with(['subjectTeachings.subject', 'subjectTeachings.classSection.grade', 'grade', 'classSection'])
            ->first();
    }

    public function getMyClasses(): Collection
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

    public function getMySubjects(): Collection
    {
        $teacher = $this->getTeacher();

        if (!$teacher) {
            return collect();
        }

        return $teacher->subjects ?? collect();
    }

    /**
     * Get the current academic year
     */
    public function getAcademicYear(): ?AcademicYear
    {
        return AcademicYear::current();
    }

    /**
     * Get all timetable periods for the current academic year
     */
    public function getPeriods(): Collection
    {
        $academicYear = $this->getAcademicYear();
        if (!$academicYear) {
            return collect();
        }

        return TimetablePeriod::where('academic_year_id', $academicYear->id)
            ->where('is_active', true)
            ->orderBy('order')
            ->get();
    }

    /**
     * Get the days of the week
     */
    public function getDays(): array
    {
        return TimetableEntry::DAYS;
    }

    /**
     * Get the teacher's timetable data organized by period and day
     */
    public function getTimetableData(): array
    {
        $teacher = $this->getTeacher();
        $academicYear = $this->getAcademicYear();

        if (!$teacher || !$academicYear) {
            return [];
        }

        $entries = TimetableEntry::with(['subject', 'classSection.grade', 'period'])
            ->where('teacher_id', $teacher->id)
            ->where('academic_year_id', $academicYear->id)
            ->where('is_active', true)
            ->get();

        // Organize by period_id => day => entry
        $timetable = [];
        foreach ($entries as $entry) {
            $timetable[$entry->timetable_period_id][$entry->day_of_week] = $entry;
        }

        return $timetable;
    }

    /**
     * Get teaching load statistics
     */
    public function getTeachingLoad(): array
    {
        $teacher = $this->getTeacher();
        $academicYear = $this->getAcademicYear();

        if (!$teacher || !$academicYear) {
            return [
                'total_periods' => 0,
                'periods_per_day' => [],
                'subjects_taught' => 0,
                'classes_taught' => 0,
            ];
        }

        $entries = TimetableEntry::where('teacher_id', $teacher->id)
            ->where('academic_year_id', $academicYear->id)
            ->where('is_active', true)
            ->get();

        $periodsPerDay = [];
        foreach ($this->getDays() as $day) {
            $periodsPerDay[$day] = $entries->where('day_of_week', $day)->count();
        }

        return [
            'total_periods' => $entries->count(),
            'periods_per_day' => $periodsPerDay,
            'subjects_taught' => $entries->pluck('subject_id')->unique()->count(),
            'classes_taught' => $entries->pluck('class_section_id')->unique()->count(),
        ];
    }

    /**
     * Get the print URL for teacher schedule
     */
    public function getPrintUrl(): ?string
    {
        $teacher = $this->getTeacher();
        $academicYear = $this->getAcademicYear();

        if (!$teacher || !$academicYear) {
            return null;
        }

        return route('timetable.print.teacher', [
            'teacher' => $teacher->id,
            'academicYear' => $academicYear->id,
        ]);
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
