<?php

namespace App\Filament\Pages;

use App\Constants\RoleConstants;
use App\Models\AcademicYear;
use App\Models\ClassSection;
use App\Models\Subject;
use App\Models\Teacher;
use App\Models\TimetableEntry;
use App\Models\TimetablePeriod;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Collection;

class ManageTimetable extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-calendar-days';
    protected static ?string $navigationLabel = 'Manage Timetable';
    protected static ?string $navigationGroup = 'Timetable Management';
    protected static ?int $navigationSort = 2;
    protected static ?string $slug = 'manage-timetable';
    protected static ?string $title = 'Manage Class Timetable';
    protected static string $view = 'filament.pages.manage-timetable';

    // Form state
    public ?int $selectedClassSection = null;
    public ?int $selectedAcademicYear = null;
    public array $timetableData = [];

    // Single entry modal state
    public bool $showEntryModal = false;
    public ?int $editingPeriodId = null;
    public ?string $editingDay = null;
    public ?int $editingEntryId = null;
    public ?int $entrySubjectId = null;
    public ?int $entryTeacherId = null;
    public ?string $entryRoom = null;
    public ?string $entryNotes = null;

    // Day assignment modal state
    public bool $showDayModal = false;
    public ?string $assigningDay = null;
    public array $dayAssignments = [];

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
                Section::make('Select Class')
                    ->description('Choose the academic year and class section to manage its timetable')
                    ->schema([
                        Select::make('selectedAcademicYear')
                            ->label('Academic Year')
                            ->options(AcademicYear::where('name', '>=', '2025')
                                ->orderBy('name', 'desc')
                                ->pluck('name', 'id'))
                            ->default(AcademicYear::current()?->id)
                            ->live()
                            ->afterStateUpdated(function () {
                                $this->selectedClassSection = null;
                                $this->timetableData = [];
                            })
                            ->required(),

                        Select::make('selectedClassSection')
                            ->label('Class Section')
                            ->options(function () {
                                if (!$this->selectedAcademicYear) {
                                    return [];
                                }

                                return ClassSection::with('grade')
                                    ->where('is_active', true)
                                    ->where('academic_year_id', $this->selectedAcademicYear)
                                    ->get()
                                    ->sortBy(fn($cs) => $cs->grade->level ?? 0)
                                    ->mapWithKeys(fn($cs) => [
                                        $cs->id => ($cs->grade?->name ?? 'Unknown') . ($cs->name ? ' ' . $cs->name : '')
                                    ]);
                            })
                            ->searchable()
                            ->live()
                            ->afterStateUpdated(fn() => $this->loadTimetable())
                            ->placeholder('Select a class section'),
                    ])
                    ->columns(2),
            ]);
    }

    public function loadTimetable(): void
    {
        if (!$this->selectedClassSection || !$this->selectedAcademicYear) {
            $this->timetableData = [];
            return;
        }

        $this->timetableData = TimetableEntry::getClassTimetable(
            $this->selectedClassSection,
            $this->selectedAcademicYear
        );
    }

    // ==========================================
    // Single Entry Modal Methods
    // ==========================================

    public function openEntryModal(int $periodId, string $day): void
    {
        $this->editingPeriodId = $periodId;
        $this->editingDay = $day;

        $entry = TimetableEntry::where('timetable_period_id', $periodId)
            ->where('class_section_id', $this->selectedClassSection)
            ->where('day_of_week', $day)
            ->where('academic_year_id', $this->selectedAcademicYear)
            ->first();

        if ($entry) {
            $this->editingEntryId = $entry->id;
            $this->entrySubjectId = $entry->subject_id;
            $this->entryTeacherId = $entry->teacher_id;
            $this->entryRoom = $entry->room;
            $this->entryNotes = $entry->notes;
        } else {
            $this->editingEntryId = null;
            $this->entrySubjectId = null;
            // For primary classes, auto-fill with class teacher
            $this->entryTeacherId = $this->isPrimaryClass() ? $this->getClassTeacherId() : null;
            $this->entryRoom = null;
            $this->entryNotes = null;
        }

        $this->showEntryModal = true;
    }

    public function saveEntry(): void
    {
        if ($this->entryTeacherId) {
            $conflict = TimetableEntry::checkTeacherConflict(
                $this->entryTeacherId,
                $this->editingPeriodId,
                $this->editingDay,
                $this->selectedAcademicYear,
                $this->editingEntryId
            );

            if ($conflict) {
                $className = ($conflict->classSection?->grade?->name ?? '') . ' - ' . ($conflict->classSection?->name ?? '');
                $subjectName = $conflict->subject?->name ?? 'Unknown Subject';

                Notification::make()
                    ->title('Teacher Conflict Detected!')
                    ->body("This teacher is already assigned to {$className} for {$subjectName} during this period on {$this->editingDay}.")
                    ->danger()
                    ->persistent()
                    ->send();

                return;
            }
        }

        $data = [
            'timetable_period_id' => $this->editingPeriodId,
            'class_section_id' => $this->selectedClassSection,
            'day_of_week' => $this->editingDay,
            'academic_year_id' => $this->selectedAcademicYear,
            'subject_id' => $this->entrySubjectId,
            'teacher_id' => $this->entryTeacherId,
            'room' => $this->entryRoom,
            'notes' => $this->entryNotes,
            'is_active' => true,
        ];

        if ($this->editingEntryId) {
            TimetableEntry::find($this->editingEntryId)->update($data);
            Notification::make()->title('Entry Updated')->success()->send();
        } else {
            TimetableEntry::create($data);
            Notification::make()->title('Entry Created')->success()->send();
        }

        $this->closeModal();
        $this->loadTimetable();
    }

    public function deleteEntry(): void
    {
        if ($this->editingEntryId) {
            TimetableEntry::destroy($this->editingEntryId);
            Notification::make()->title('Entry Deleted')->success()->send();
        }

        $this->closeModal();
        $this->loadTimetable();
    }

    public function closeModal(): void
    {
        $this->showEntryModal = false;
        $this->editingPeriodId = null;
        $this->editingDay = null;
        $this->editingEntryId = null;
        $this->entrySubjectId = null;
        $this->entryTeacherId = null;
        $this->entryRoom = null;
        $this->entryNotes = null;
    }

    // ==========================================
    // Day Assignment Modal Methods (Set All Periods for a Day)
    // ==========================================

    public function openDayModal(string $day): void
    {
        $this->assigningDay = $day;
        $this->dayAssignments = [];

        // Get lesson periods only
        $periods = $this->getLessonPeriods();

        // For primary classes, get the class teacher ID to auto-fill
        $isPrimary = $this->isPrimaryClass();
        $classTeacherId = $isPrimary ? $this->getClassTeacherId() : null;

        // Load existing entries for this day
        foreach ($periods as $period) {
            $entry = TimetableEntry::where('timetable_period_id', $period->id)
                ->where('class_section_id', $this->selectedClassSection)
                ->where('day_of_week', $day)
                ->where('academic_year_id', $this->selectedAcademicYear)
                ->first();

            $this->dayAssignments[$period->id] = [
                'subject_id' => $entry?->subject_id,
                // For primary classes, auto-fill with class teacher if no existing entry
                'teacher_id' => $entry?->teacher_id ?? ($isPrimary ? $classTeacherId : null),
            ];
        }

        $this->showDayModal = true;
    }

    public function saveDayAssignments(): void
    {
        $conflicts = [];

        // Check for conflicts first
        foreach ($this->dayAssignments as $periodId => $assignment) {
            if (!empty($assignment['teacher_id'])) {
                $conflict = TimetableEntry::checkTeacherConflict(
                    $assignment['teacher_id'],
                    $periodId,
                    $this->assigningDay,
                    $this->selectedAcademicYear,
                    null // Check all entries
                );

                // Exclude conflicts with the same class section (we're updating those)
                if ($conflict && $conflict->class_section_id != $this->selectedClassSection) {
                    $period = TimetablePeriod::find($periodId);
                    $teacher = Teacher::find($assignment['teacher_id']);
                    $conflicts[] = "{$period->name}: {$teacher->name} is in " .
                        ($conflict->classSection?->grade?->name ?? '') . ' ' . ($conflict->classSection?->name ?? '');
                }
            }
        }

        if (!empty($conflicts)) {
            Notification::make()
                ->title('Teacher Conflicts Detected!')
                ->body(implode("\n", $conflicts))
                ->danger()
                ->persistent()
                ->send();
            return;
        }

        // Save all entries
        foreach ($this->dayAssignments as $periodId => $assignment) {
            $existingEntry = TimetableEntry::where('timetable_period_id', $periodId)
                ->where('class_section_id', $this->selectedClassSection)
                ->where('day_of_week', $this->assigningDay)
                ->where('academic_year_id', $this->selectedAcademicYear)
                ->first();

            $data = [
                'timetable_period_id' => $periodId,
                'class_section_id' => $this->selectedClassSection,
                'day_of_week' => $this->assigningDay,
                'academic_year_id' => $this->selectedAcademicYear,
                'subject_id' => $assignment['subject_id'] ?: null,
                'teacher_id' => $assignment['teacher_id'] ?: null,
                'is_active' => true,
            ];

            if ($existingEntry) {
                $existingEntry->update($data);
            } elseif ($assignment['subject_id'] || $assignment['teacher_id']) {
                TimetableEntry::create($data);
            }
        }

        Notification::make()
            ->title('Day Schedule Saved')
            ->body("All periods for {$this->assigningDay} have been updated.")
            ->success()
            ->send();

        $this->closeDayModal();
        $this->loadTimetable();
    }

    public function closeDayModal(): void
    {
        $this->showDayModal = false;
        $this->assigningDay = null;
        $this->dayAssignments = [];
    }

    // ==========================================
    // Helper Methods
    // ==========================================

    public function getAvailableSubjects(): array
    {
        if (!$this->selectedClassSection) {
            return [];
        }

        $classSection = ClassSection::with('grade.subjects')->find($this->selectedClassSection);

        if (!$classSection || !$classSection->grade) {
            return Subject::where('is_active', true)
                ->orderBy('name')
                ->pluck('name', 'id')
                ->toArray();
        }

        $subjects = $classSection->grade->subjects()
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        if ($subjects->isEmpty()) {
            $gradeLevel = $classSection->grade->school_section_id == 1 ? 'Primary' : 'Secondary';
            $subjects = Subject::where('is_active', true)
                ->where(function ($q) use ($gradeLevel) {
                    $q->where('grade_level', $gradeLevel)
                        ->orWhere('grade_level', 'All');
                })
                ->orderBy('name')
                ->get();
        }

        return $subjects->pluck('name', 'id')->toArray();
    }

    public function getAvailableTeachers(): array
    {
        return Teacher::where('is_active', true)
            ->orderBy('name')
            ->get()
            ->mapWithKeys(fn($t) => [$t->id => $t->name . ($t->employee_id ? " ({$t->employee_id})" : '')])
            ->toArray();
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

    public function getLessonPeriods(): Collection
    {
        return $this->getPeriods()->filter(fn($p) => $p->isLesson());
    }

    public function getDays(): array
    {
        return TimetableEntry::DAYS;
    }

    public function getSelectedClassName(): string
    {
        if (!$this->selectedClassSection) {
            return '';
        }

        $classSection = ClassSection::with('grade')->find($this->selectedClassSection);
        return $classSection ? ($classSection->grade?->name ?? '') . ($classSection->name ? ' ' . $classSection->name : '') : '';
    }

    public function getEditingPeriodName(): string
    {
        if (!$this->editingPeriodId) {
            return '';
        }

        $period = TimetablePeriod::find($this->editingPeriodId);
        return $period ? $period->name . ' (' . $period->time_range . ')' : '';
    }

    /**
     * Check if the selected class section is a primary class
     * Primary classes have school_section_id = 1
     */
    public function isPrimaryClass(): bool
    {
        if (!$this->selectedClassSection) {
            return false;
        }

        $classSection = ClassSection::with('grade')->find($this->selectedClassSection);
        return $classSection?->grade?->school_section_id === 1;
    }

    /**
     * Get the class teacher ID for the selected class section
     */
    public function getClassTeacherId(): ?int
    {
        if (!$this->selectedClassSection) {
            return null;
        }

        $classSection = ClassSection::find($this->selectedClassSection);
        return $classSection?->class_teacher_id;
    }

    /**
     * Get the class teacher name for display
     */
    public function getClassTeacherName(): string
    {
        $teacherId = $this->getClassTeacherId();
        if (!$teacherId) {
            return 'No class teacher assigned';
        }

        $teacher = Teacher::find($teacherId);
        return $teacher ? $teacher->name : 'Unknown';
    }
}
