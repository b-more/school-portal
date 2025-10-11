<?php

namespace App\Filament\Pages;

use App\Constants\RoleConstants;
use App\Models\Attendance;
use App\Models\ClassSection;
use App\Models\Student;
use App\Models\Teacher;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TimePicker;
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

    protected static ?string $navigationLabel = 'Mark Attendance';

    protected static ?string $title = 'Mark Class Attendance';

    protected static ?string $navigationGroup = 'Academic';

    protected static ?int $navigationSort = 6;

    public ?array $data = [];

    public $classSectionId = null;

    public $attendanceDate = null;

    public $checkInTime = null;

    public $notes = null;

    public $students = [];

    public $attendanceData = [];

    public function mount(): void
    {
        $this->attendanceDate = now()->format('Y-m-d');
        $this->checkInTime = now()->format('H:i');
        $this->form->fill();
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
                    return [$section->id => $section->grade->name.' - '.$section->name];
                })
                ->toArray();
        } elseif ($user->role_id === RoleConstants::ADMIN) {
            $classSectionOptions = ClassSection::with('grade')
                ->get()
                ->mapWithKeys(function ($section) {
                    return [$section->id => $section->grade->name.' - '.$section->name];
                })
                ->toArray();
        }

        return $form
            ->schema([
                Select::make('classSectionId')
                    ->label('Select Class')
                    ->options($classSectionOptions)
                    ->required()
                    ->reactive()
                    ->afterStateUpdated(function ($state) {
                        $this->loadStudents($state);
                    }),

                DatePicker::make('attendanceDate')
                    ->label('Date')
                    ->required()
                    ->default(now())
                    ->maxDate(now())
                    ->displayFormat('d/m/Y')
                    ->reactive(),

                TimePicker::make('checkInTime')
                    ->label('Default Check-In Time')
                    ->default(now()->format('H:i'))
                    ->seconds(false),

                Textarea::make('notes')
                    ->label('Notes (Optional)')
                    ->maxLength(500)
                    ->rows(2),
            ])
            ->statePath('data')
            ->columns(2);
    }

    public function loadStudents($classSectionId): void
    {
        if (! $classSectionId) {
            $this->students = [];
            $this->attendanceData = [];

            return;
        }

        $this->classSectionId = $classSectionId;

        // Get all students in this class, ordered by name
        $this->students = Student::where('class_section_id', $classSectionId)
            ->orderBy('name')
            ->get()
            ->toArray();

        // Check if attendance already exists for this date
        if ($this->attendanceDate) {
            $existingAttendance = Attendance::where('class_section_id', $classSectionId)
                ->where('attendance_date', $this->attendanceDate)
                ->get()
                ->keyBy('student_id');

            // Pre-fill attendance data
            foreach ($this->students as $student) {
                $studentId = $student['id'];
                if (isset($existingAttendance[$studentId])) {
                    $this->attendanceData[$studentId] = $existingAttendance[$studentId]->status;
                } else {
                    $this->attendanceData[$studentId] = 'present'; // Default to present
                }
            }
        } else {
            // Default all to present
            foreach ($this->students as $student) {
                $this->attendanceData[$student['id']] = 'present';
            }
        }
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
            ->title('All students marked as present')
            ->success()
            ->send();
    }

    public function markAllAbsent(): void
    {
        foreach ($this->students as $student) {
            $this->attendanceData[$student['id']] = 'absent';
        }

        Notification::make()
            ->title('All students marked as absent')
            ->warning()
            ->send();
    }

    public function submitAttendance(): void
    {
        // Validate
        if (! $this->classSectionId) {
            Notification::make()
                ->title('Please select a class')
                ->danger()
                ->send();

            return;
        }

        if (! $this->attendanceDate) {
            Notification::make()
                ->title('Please select a date')
                ->danger()
                ->send();

            return;
        }

        $classSection = ClassSection::find($this->classSectionId);
        if (! $classSection) {
            Notification::make()
                ->title('Class section not found')
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
                'check_in_time' => in_array($status, ['present', 'late']) ? $this->checkInTime : null,
                'check_out_time' => null,
                'notes' => $this->notes,
                'marked_by' => Auth::id(),
            ];

            // Check if attendance already exists
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

        Notification::make()
            ->title('Attendance Saved Successfully!')
            ->body("Created: {$created} | Updated: {$updated} | Total: ".count($this->attendanceData))
            ->success()
            ->send();

        // Reload students to show updated attendance
        $this->loadStudents($this->classSectionId);
    }

    public static function shouldRegisterNavigation(): bool
    {
        // Hidden from navigation - accessed via AttendanceResource button
        return false;
    }

    public static function canAccess(): bool
    {
        $user = auth()->user();
        if (! $user) {
            return false;
        }

        return in_array($user->role_id, [
            RoleConstants::ADMIN,
            RoleConstants::TEACHER,
        ]);
    }
}
