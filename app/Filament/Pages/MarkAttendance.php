<?php

namespace App\Filament\Pages;

use App\Constants\RoleConstants;
use App\Models\Attendance;
use App\Models\ClassSection;
use App\Models\Student;
use App\Models\Teacher;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Auth;

class MarkAttendance extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-check';

    protected static string $view = 'filament.pages.mark-attendance';

    protected static ?string $navigationLabel = 'Daily Attendance';

    protected static ?string $title = 'Daily Attendance';

    protected static ?string $navigationGroup = 'Academic';

    protected static ?int $navigationSort = 5;

    public ?array $data = [];

    public $classSectionId = null;

    public $attendanceDate = null;

    public $students = [];

    public $attendanceData = [];

    public $attendanceAlreadyMarked = false;

    public function mount(): void
    {
        $this->attendanceDate = now()->format('Y-m-d');

        $user = Auth::user();

        // Auto-select class for teachers with only one class
        if ($user->role_id === RoleConstants::TEACHER) {
            $teacher = Teacher::where('user_id', $user->id)->first();
            if ($teacher) {
                $classSections = $teacher->classSections()->get();
                if ($classSections->count() === 1) {
                    $this->classSectionId = $classSections->first()->id;
                    $this->loadStudents($this->classSectionId);
                }
            }
        }

        $this->form->fill([
            'classSectionId' => $this->classSectionId,
            'attendanceDate' => $this->attendanceDate,
        ]);
    }

    public function form(Form $form): Form
    {
        $user = Auth::user();
        $teacher = $user->role_id === RoleConstants::TEACHER
            ? Teacher::where('user_id', $user->id)->first()
            : null;

        // Get class section options
        $classSectionOptions = [];
        if ($teacher) {
            $classSectionOptions = $teacher->classSections()
                ->with('grade')
                ->get()
                ->mapWithKeys(function ($section) {
                    return [$section->id => $section->grade->name . ' - ' . $section->name];
                })
                ->toArray();
        } elseif ($user->role_id === RoleConstants::ADMIN) {
            $classSectionOptions = ClassSection::with('grade')
                ->where('is_active', true)
                ->get()
                ->mapWithKeys(function ($section) {
                    return [$section->id => $section->grade->name . ' - ' . $section->name];
                })
                ->toArray();
        }

        return $form
            ->schema([
                Select::make('classSectionId')
                    ->label('Class')
                    ->options($classSectionOptions)
                    ->required()
                    ->reactive()
                    ->default($this->classSectionId)
                    ->afterStateUpdated(function ($state) {
                        $this->classSectionId = $state;
                        $this->loadStudents($state);
                    }),

                DatePicker::make('attendanceDate')
                    ->label('Date')
                    ->required()
                    ->default(now())
                    ->maxDate(now())
                    ->displayFormat('D, d M Y')
                    ->reactive()
                    ->afterStateUpdated(function ($state) {
                        $this->attendanceDate = $state;
                        if ($this->classSectionId) {
                            $this->loadStudents($this->classSectionId);
                        }
                    }),
            ])
            ->statePath('data')
            ->columns(2);
    }

    public function loadStudents($classSectionId): void
    {
        if (!$classSectionId) {
            $this->students = [];
            $this->attendanceData = [];
            $this->attendanceAlreadyMarked = false;
            return;
        }

        $this->classSectionId = $classSectionId;

        // Get all active students in this class, ordered by name
        $this->students = Student::where('class_section_id', $classSectionId)
            ->where('enrollment_status', 'active')
            ->orderBy('name')
            ->get()
            ->toArray();

        // Check if attendance already exists for this date
        $existingAttendance = Attendance::where('class_section_id', $classSectionId)
            ->where('attendance_date', $this->attendanceDate)
            ->get()
            ->keyBy('student_id');

        $this->attendanceAlreadyMarked = $existingAttendance->isNotEmpty();

        // Pre-fill attendance data - default to present
        $this->attendanceData = [];
        foreach ($this->students as $student) {
            $studentId = $student['id'];
            if (isset($existingAttendance[$studentId])) {
                $this->attendanceData[$studentId] = $existingAttendance[$studentId]->status;
            } else {
                $this->attendanceData[$studentId] = 'present'; // Default to present
            }
        }
    }

    public function toggleStatus($studentId): void
    {
        $current = $this->attendanceData[$studentId] ?? 'present';

        // Cycle: present → absent → sick → late → excused → present
        $cycle = ['present', 'absent', 'sick', 'late', 'excused'];
        $index = array_search($current, $cycle);
        $next = ($index !== false) ? ($index + 1) % count($cycle) : 0;
        $this->attendanceData[$studentId] = $cycle[$next];
    }

    public function setStatus($studentId, $status): void
    {
        $this->attendanceData[$studentId] = $status;
    }

    public function markAllPresent(): void
    {
        foreach ($this->students as $student) {
            $this->attendanceData[$student['id']] = 'present';
        }

        Notification::make()
            ->title('All marked present')
            ->success()
            ->send();
    }

    public function submitAttendance(): void
    {
        if (!$this->classSectionId) {
            Notification::make()
                ->title('Please select a class')
                ->danger()
                ->send();
            return;
        }

        if (!$this->attendanceDate) {
            Notification::make()
                ->title('Please select a date')
                ->danger()
                ->send();
            return;
        }

        $classSection = ClassSection::find($this->classSectionId);
        if (!$classSection) {
            Notification::make()
                ->title('Class not found')
                ->danger()
                ->send();
            return;
        }

        $created = 0;
        $updated = 0;

        foreach ($this->attendanceData as $studentId => $status) {
            $attendanceData = [
                'student_id' => $studentId,
                'class_section_id' => $this->classSectionId,
                'grade_id' => $classSection->grade_id,
                'attendance_date' => $this->attendanceDate,
                'status' => $status,
                'check_in_time' => in_array($status, ['present', 'late']) ? now()->format('H:i:s') : null,
                'marked_by' => Auth::id(),
            ];

            $existing = Attendance::where('student_id', $studentId)
                ->where('attendance_date', $this->attendanceDate)
                ->first();

            if ($existing) {
                $existing->update($attendanceData);
                $updated++;
            } else {
                Attendance::create($attendanceData);
                $created++;
            }
        }

        $this->attendanceAlreadyMarked = true;

        Notification::make()
            ->title('Attendance Saved!')
            ->body($this->getAttendanceSummary())
            ->success()
            ->send();
    }

    protected function getAttendanceSummary(): string
    {
        $data = collect($this->attendanceData);
        $present = $data->filter(fn($s) => $s === 'present')->count();
        $absent = $data->filter(fn($s) => $s === 'absent')->count();
        $sick = $data->filter(fn($s) => $s === 'sick')->count();
        $late = $data->filter(fn($s) => $s === 'late')->count();
        $excused = $data->filter(fn($s) => $s === 'excused')->count();

        return "Present: {$present} | Absent: {$absent} | Sick: {$sick} | Late: {$late} | Excused: {$excused}";
    }

    public static function shouldRegisterNavigation(): bool
    {
        return static::canAccess();
    }

    public static function canAccess(): bool
    {
        $user = auth()->user();
        if (!$user) {
            return false;
        }

        return in_array($user->role_id, [
            RoleConstants::ADMIN,
            RoleConstants::TEACHER,
        ]);
    }
}
