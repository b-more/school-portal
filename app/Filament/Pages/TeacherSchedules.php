<?php

namespace App\Filament\Pages;

use App\Constants\RoleConstants;
use App\Models\AcademicYear;
use App\Models\Teacher;
use App\Models\TimetableEntry;
use App\Models\TimetablePeriod;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Pages\Page;
use Illuminate\Support\Collection;

class TeacherSchedules extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-user-group';
    protected static ?string $navigationLabel = 'Teacher Schedules';
    protected static ?string $navigationGroup = 'Timetable Management';
    protected static ?int $navigationSort = 3;
    protected static ?string $slug = 'teacher-schedules';
    protected static ?string $title = 'Teacher Schedules';
    protected static string $view = 'filament.pages.teacher-schedules';

    public ?int $selectedTeacher = null;
    public ?int $selectedAcademicYear = null;

    public static function shouldRegisterNavigation(): bool
    {
        return auth()->user()?->role_id === RoleConstants::ADMIN;
    }

    public static function canAccess(): bool
    {
        return auth()->user()?->role_id === RoleConstants::ADMIN;
    }

    public function mount(): void
    {
        $this->selectedAcademicYear = AcademicYear::current()?->id;
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Select Teacher')
                    ->description('Choose an academic year and teacher to view their schedule')
                    ->schema([
                        Select::make('selectedAcademicYear')
                            ->label('Academic Year')
                            ->options(AcademicYear::where('name', '>=', '2025')
                                ->orderBy('name', 'desc')
                                ->pluck('name', 'id'))
                            ->default(AcademicYear::current()?->id)
                            ->live()
                            ->afterStateUpdated(fn() => $this->selectedTeacher = null)
                            ->required(),

                        Select::make('selectedTeacher')
                            ->label('Teacher')
                            ->options(function () {
                                return Teacher::where('is_active', true)
                                    ->orderBy('name')
                                    ->get()
                                    ->mapWithKeys(fn($t) => [
                                        $t->id => $t->name . ($t->employee_id ? " ({$t->employee_id})" : '')
                                    ]);
                            })
                            ->searchable()
                            ->live()
                            ->placeholder('Select a teacher'),
                    ])
                    ->columns(2),
            ]);
    }

    public function getPeriods(): Collection
    {
        if (!$this->selectedAcademicYear) {
            return collect();
        }

        return TimetablePeriod::where('academic_year_id', $this->selectedAcademicYear)
            ->where('is_active', true)
            ->orderBy('order')
            ->get();
    }

    public function getDays(): array
    {
        return TimetableEntry::DAYS;
    }

    public function getTimetableData(): array
    {
        if (!$this->selectedTeacher || !$this->selectedAcademicYear) {
            return [];
        }

        $entries = TimetableEntry::with(['subject', 'classSection.grade', 'period'])
            ->where('teacher_id', $this->selectedTeacher)
            ->where('academic_year_id', $this->selectedAcademicYear)
            ->where('is_active', true)
            ->get();

        $timetable = [];
        foreach ($entries as $entry) {
            $timetable[$entry->timetable_period_id][$entry->day_of_week] = $entry;
        }

        return $timetable;
    }

    public function getTeachingLoad(): array
    {
        if (!$this->selectedTeacher || !$this->selectedAcademicYear) {
            return [
                'total_periods' => 0,
                'periods_per_day' => [],
                'subjects_taught' => 0,
                'classes_taught' => 0,
            ];
        }

        $entries = TimetableEntry::where('teacher_id', $this->selectedTeacher)
            ->where('academic_year_id', $this->selectedAcademicYear)
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

    public function getSelectedTeacherName(): string
    {
        if (!$this->selectedTeacher) {
            return '';
        }

        $teacher = Teacher::find($this->selectedTeacher);
        return $teacher ? $teacher->name : '';
    }

    public function getPrintUrl(): ?string
    {
        if (!$this->selectedTeacher || !$this->selectedAcademicYear) {
            return null;
        }

        return route('timetable.print.teacher', [
            'teacher' => $this->selectedTeacher,
            'academicYear' => $this->selectedAcademicYear,
        ]);
    }
}
