<?php

namespace App\Filament\Resources\AttendanceResource\Pages;

use App\Constants\RoleConstants;
use App\Filament\Resources\AttendanceResource;
use App\Filament\Resources\AttendanceResource\Widgets\AttendanceDailyRegister;
use App\Filament\Resources\AttendanceResource\Widgets\FlaggedStudentsWidget;
use App\Models\ClassSection;
use App\Models\Student;
use App\Models\Teacher;
use Filament\Actions;
use Filament\Forms;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Support\Facades\Auth;

class ListAttendances extends ListRecords
{
    protected static string $resource = AttendanceResource::class;

    protected function getHeaderWidgets(): array
    {
        return [
            AttendanceDailyRegister::class,
            FlaggedStudentsWidget::class,
        ];
    }

    protected function getHeaderActions(): array
    {
        $user = Auth::user();
        $canCreate = in_array($user->role_id, [RoleConstants::ADMIN, RoleConstants::TEACHER]);

        return [
            Actions\Action::make('mark_attendance')
                ->label('Mark Attendance')
                ->icon('heroicon-o-clipboard-document-check')
                ->color('primary')
                ->url(route('filament.admin.pages.mark-attendance'))
                ->visible($canCreate)
                ->tooltip('Use the visual interface to mark attendance'),

            Actions\Action::make('bulk_mark')
                ->label('Quick Bulk Mark')
                ->icon('heroicon-o-clipboard-document-list')
                ->color('gray')
                ->outlined()
                ->form($this->getBulkMarkForm())
                ->action(function (array $data) {
                    $this->bulkMarkAttendance($data);
                })
                ->visible($canCreate)
                ->modalWidth('3xl')
                ->slideOver()
                ->tooltip('Mark all students with same status'),

            Actions\Action::make('export')
                ->label('Export Report')
                ->icon('heroicon-o-arrow-down-tray')
                ->color('success')
                ->outlined()
                ->url(fn () => route('attendance.export'))
                ->openUrlInNewTab(),

            Actions\CreateAction::make()
                ->outlined()
                ->visible($canCreate),
        ];
    }

    protected function getBulkMarkForm(): array
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

        return [
            Forms\Components\Select::make('class_section_id')
                ->label('Select Class')
                ->options($classSectionOptions)
                ->required()
                ->reactive()
                ->searchable(),

            Forms\Components\DatePicker::make('attendance_date')
                ->label('Date')
                ->required()
                ->default(now())
                ->maxDate(now()),

            Forms\Components\Select::make('default_status')
                ->label('Mark All As')
                ->options([
                    'present' => 'Present',
                    'absent' => 'Absent',
                    'sick' => 'Sick',
                    'late' => 'Late',
                    'excused' => 'Excused',
                ])
                ->required()
                ->default('present')
                ->helperText('This will mark all students in the selected class with this status'),

            Forms\Components\TimePicker::make('check_in_time')
                ->label('Check In Time (Optional)')
                ->default(now()->format('H:i'))
                ->visible(fn (callable $get) => in_array($get('default_status'), ['present', 'late'])),

            Forms\Components\Textarea::make('notes')
                ->label('Notes (Optional)')
                ->maxLength(500)
                ->columnSpanFull(),
        ];
    }

    protected function bulkMarkAttendance(array $data): void
    {
        $classSectionId = $data['class_section_id'];
        $date = $data['attendance_date'];
        $status = $data['default_status'];
        $checkInTime = $data['check_in_time'] ?? null;
        $notes = $data['notes'] ?? null;

        // Get class section
        $classSection = ClassSection::find($classSectionId);
        if (! $classSection) {
            Notification::make()
                ->title('Error')
                ->body('Class section not found')
                ->danger()
                ->send();

            return;
        }

        // Get all students in this class
        $students = Student::where('class_section_id', $classSectionId)->get();

        if ($students->isEmpty()) {
            Notification::make()
                ->title('No Students Found')
                ->body('No students found in this class section')
                ->warning()
                ->send();

            return;
        }

        $created = 0;
        $updated = 0;

        foreach ($students as $student) {
            $attendanceData = [
                'student_id' => $student->id,
                'class_section_id' => $classSectionId,
                'grade_id' => $classSection->grade_id,
                'attendance_date' => $date,
                'status' => $status,
                'check_in_time' => $checkInTime,
                'check_out_time' => null,
                'notes' => $notes,
                'marked_by' => Auth::id(),
            ];

            // Check if attendance already exists for this date and student
            $existing = \App\Models\Attendance::where('student_id', $student->id)
                ->where('attendance_date', $date)
                ->first();

            if ($existing) {
                $existing->update($attendanceData);
                $updated++;
            } else {
                \App\Models\Attendance::create($attendanceData);
                $created++;
            }
        }

        Notification::make()
            ->title('Attendance Marked Successfully')
            ->body("Created: {$created} | Updated: {$updated} | Total Students: {$students->count()}")
            ->success()
            ->send();
    }
}
