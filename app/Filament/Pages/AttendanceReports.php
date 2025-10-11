<?php

namespace App\Filament\Pages;

use App\Constants\RoleConstants;
use App\Models\AcademicYear;
use App\Models\Attendance;
use App\Models\ClassSection;
use App\Models\Grade;
use App\Models\Student;
use App\Models\Term;
use Barryvdh\DomPDF\Facade\Pdf;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Tables\Actions\Action;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class AttendanceReports extends Page implements HasForms, HasTable
{
    use InteractsWithForms, InteractsWithTable;

    protected static ?string $navigationIcon = 'heroicon-o-calendar-days';

    protected static ?string $navigationLabel = 'Attendance Reports';

    protected static ?string $navigationGroup = 'Reports';

    protected static ?int $navigationSort = 3;

    protected static string $view = 'filament.pages.attendance-reports';

    public ?array $data = [];

    public static function shouldRegisterNavigation(): bool
    {
        return auth()->user()?->role_id === RoleConstants::ADMIN ?? false;
    }

    public function mount(): void
    {
        $this->form->fill([
            'academic_year_id' => AcademicYear::where('is_active', true)->first()?->id,
            'term_id' => Term::where('is_active', true)->first()?->id,
            'date_from' => now()->startOfMonth(),
            'date_to' => now()->endOfMonth(),
        ]);
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Select::make('report_type')
                    ->label('Report Type')
                    ->options([
                        'all' => 'All Attendance',
                        'by_class' => 'By Class Section',
                        'by_grade' => 'By Grade',
                        'by_student' => 'By Student',
                        'by_status' => 'By Status',
                        'summary' => 'Attendance Summary',
                    ])
                    ->default('all')
                    ->live()
                    ->columnSpan(2),

                DatePicker::make('date_from')
                    ->label('From Date')
                    ->default(now()->startOfMonth())
                    ->maxDate(now())
                    ->required(),

                DatePicker::make('date_to')
                    ->label('To Date')
                    ->default(now()->endOfMonth())
                    ->maxDate(now())
                    ->required()
                    ->after('date_from'),

                Select::make('academic_year_id')
                    ->label('Academic Year')
                    ->options(AcademicYear::orderBy('name', 'desc')->pluck('name', 'id'))
                    ->default(AcademicYear::where('is_active', true)->first()?->id)
                    ->live(),

                Select::make('term_id')
                    ->label('Term')
                    ->options(function ($get) {
                        $academicYearId = $get('academic_year_id');
                        if (! $academicYearId) {
                            return Term::pluck('name', 'id');
                        }

                        return Term::where('academic_year_id', $academicYearId)->pluck('name', 'id');
                    })
                    ->live(),

                Select::make('grade_id')
                    ->label('Grade')
                    ->options(Grade::orderBy('level')->pluck('name', 'id'))
                    ->searchable()
                    ->visible(fn ($get) => in_array($get('report_type'), ['by_grade', 'by_class']))
                    ->live(),

                Select::make('class_section_id')
                    ->label('Class Section')
                    ->options(function ($get) {
                        $gradeId = $get('grade_id');
                        if (! $gradeId) {
                            return ClassSection::with('grade')->get()->mapWithKeys(function ($section) {
                                return [$section->id => $section->grade->name.' - '.$section->name];
                            });
                        }

                        return ClassSection::where('grade_id', $gradeId)->pluck('name', 'id');
                    })
                    ->searchable()
                    ->visible(fn ($get) => $get('report_type') === 'by_class'),

                Select::make('student_id')
                    ->label('Student')
                    ->options(Student::where('enrollment_status', 'active')
                        ->orderBy('name')
                        ->get()
                        ->mapWithKeys(function ($student) {
                            return [$student->id => $student->name.' ('.$student->student_id_number.')'];
                        }))
                    ->searchable()
                    ->visible(fn ($get) => $get('report_type') === 'by_student'),

                Select::make('status')
                    ->label('Attendance Status')
                    ->options([
                        'present' => 'Present',
                        'absent' => 'Absent',
                        'late' => 'Late',
                        'excused' => 'Excused',
                    ])
                    ->visible(fn ($get) => $get('report_type') === 'by_status'),
            ])
            ->columns(3)
            ->statePath('data');
    }

    public function table(Table $table): Table
    {
        return $table
            ->query($this->getTableQuery())
            ->columns([
                TextColumn::make('attendance_date')
                    ->label('Date')
                    ->date()
                    ->sortable(),

                TextColumn::make('student.student_id_number')
                    ->label('Student ID')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('student.name')
                    ->label('Student Name')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('grade.name')
                    ->label('Grade')
                    ->sortable()
                    ->default(fn ($record) => $record->student?->grade?->name ?? 'N/A'),

                TextColumn::make('classSection.name')
                    ->label('Class')
                    ->sortable()
                    ->default(fn ($record) => $record->student?->classSection?->name ?? 'N/A'),

                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'present' => 'success',
                        'late' => 'warning',
                        'absent' => 'danger',
                        'excused' => 'info',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => ucfirst($state)),

                TextColumn::make('check_in_time')
                    ->label('Check In')
                    ->time('H:i')
                    ->sortable(),

                TextColumn::make('check_out_time')
                    ->label('Check Out')
                    ->time('H:i')
                    ->sortable(),

                TextColumn::make('notes')
                    ->label('Notes')
                    ->limit(30)
                    ->tooltip(function (TextColumn $column): ?string {
                        $state = $column->getState();

                        if (strlen($state) <= 30) {
                            return null;
                        }

                        return $state;
                    }),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label('Status')
                    ->options([
                        'present' => 'Present',
                        'absent' => 'Absent',
                        'late' => 'Late',
                        'excused' => 'Excused',
                    ]),

                SelectFilter::make('grade_id')
                    ->label('Grade')
                    ->options(Grade::pluck('name', 'id')),

                SelectFilter::make('class_section_id')
                    ->label('Class Section')
                    ->options(function () {
                        return ClassSection::with('grade')->get()->mapWithKeys(function ($section) {
                            return [$section->id => $section->grade->name.' - '.$section->name];
                        });
                    }),

                Filter::make('date_range')
                    ->form([
                        DatePicker::make('from_date'),
                        DatePicker::make('to_date'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['from_date'],
                                fn (Builder $query, $date): Builder => $query->whereDate('attendance_date', '>=', $date),
                            )
                            ->when(
                                $data['to_date'],
                                fn (Builder $query, $date): Builder => $query->whereDate('attendance_date', '<=', $date),
                            );
                    }),
            ])
            ->actions([
                Action::make('edit_attendance')
                    ->label('Edit')
                    ->icon('heroicon-o-pencil')
                    ->modalHeading('Edit Attendance')
                    ->modalWidth('md')
                    ->form([
                        Select::make('status')
                            ->options([
                                'present' => 'Present',
                                'absent' => 'Absent',
                                'late' => 'Late',
                                'excused' => 'Excused',
                            ])
                            ->required(),
                        DatePicker::make('attendance_date')
                            ->required(),
                        \Filament\Forms\Components\TextInput::make('notes')
                            ->maxLength(500),
                    ])
                    ->fillForm(fn (Attendance $record): array => [
                        'status' => $record->status,
                        'attendance_date' => $record->attendance_date,
                        'notes' => $record->notes,
                    ])
                    ->action(function (Attendance $record, array $data): void {
                        $record->update($data);

                        Notification::make()
                            ->title('Attendance Updated')
                            ->success()
                            ->send();
                    }),
            ])
            ->bulkActions([
                \Filament\Tables\Actions\BulkAction::make('export_bulk_pdf')
                    ->label('Export Selected as PDF')
                    ->icon('heroicon-o-document-duplicate')
                    ->action(function ($records) {
                        return $this->exportBulkAttendancePdf($records);
                    })
                    ->deselectRecordsAfterCompletion(),
            ])
            ->headerActions([
                Action::make('export_summary')
                    ->label('Export Summary PDF')
                    ->icon('heroicon-o-document-text')
                    ->color('success')
                    ->action(function () {
                        return $this->exportAttendanceSummaryPdf();
                    }),
            ])
            ->defaultSort('attendance_date', 'desc')
            ->paginated([10, 25, 50, 100]);
    }

    protected function getTableQuery(): Builder
    {
        $query = Attendance::query()
            ->with(['student.grade', 'student.classSection', 'grade', 'classSection', 'term']);

        // Apply date range filters
        if (! empty($this->data['date_from'])) {
            $query->whereDate('attendance_date', '>=', $this->data['date_from']);
        }

        if (! empty($this->data['date_to'])) {
            $query->whereDate('attendance_date', '<=', $this->data['date_to']);
        }

        // Apply academic year and term filters
        if (! empty($this->data['academic_year_id'])) {
            $query->where('academic_year_id', $this->data['academic_year_id']);
        }

        if (! empty($this->data['term_id'])) {
            $query->where('term_id', $this->data['term_id']);
        }

        // Apply report type filters
        $reportType = $this->data['report_type'] ?? 'all';

        switch ($reportType) {
            case 'by_class':
                if (! empty($this->data['class_section_id'])) {
                    $query->where('class_section_id', $this->data['class_section_id']);
                }
                break;

            case 'by_grade':
                if (! empty($this->data['grade_id'])) {
                    $query->where('grade_id', $this->data['grade_id']);
                }
                break;

            case 'by_student':
                if (! empty($this->data['student_id'])) {
                    $query->where('student_id', $this->data['student_id']);
                }
                break;

            case 'by_status':
                if (! empty($this->data['status'])) {
                    $query->where('status', $this->data['status']);
                }
                break;

            case 'all':
            case 'summary':
            default:
                // No additional filtering
                break;
        }

        return $query;
    }

    protected function exportBulkAttendancePdf($attendances)
    {
        try {
            $attendancesData = $attendances->load(['student', 'grade', 'classSection']);

            $pdf = Pdf::loadView('pdf.attendance-list', [
                'attendances' => $attendancesData,
                'schoolName' => 'St. Francis Of Assisi Private School',
                'reportDate' => now()->format('F d, Y'),
                'dateFrom' => $this->data['date_from'] ?? null,
                'dateTo' => $this->data['date_to'] ?? null,
            ]);

            $filename = 'attendance-list-'.now()->format('Y-m-d').'.pdf';

            return response()->streamDownload(
                fn () => print ($pdf->output()),
                $filename,
                ['Content-Type' => 'application/pdf']
            );
        } catch (\Exception $e) {
            Notification::make()
                ->title('Export Failed')
                ->body('Error: '.$e->getMessage())
                ->danger()
                ->send();
        }
    }

    protected function exportAttendanceSummaryPdf()
    {
        try {
            $attendances = $this->getTableQuery()->get()->load(['student', 'grade', 'classSection']);

            $summary = [
                'total_records' => $attendances->count(),
                'present_count' => $attendances->where('status', 'present')->count(),
                'absent_count' => $attendances->where('status', 'absent')->count(),
                'late_count' => $attendances->where('status', 'late')->count(),
                'excused_count' => $attendances->where('status', 'excused')->count(),
                'unique_students' => $attendances->unique('student_id')->count(),
            ];

            $pdf = Pdf::loadView('pdf.attendance-summary', [
                'attendances' => $attendances,
                'summary' => $summary,
                'schoolName' => 'St. Francis Of Assisi Private School',
                'reportDate' => now()->format('F d, Y'),
                'dateFrom' => $this->data['date_from'] ?? null,
                'dateTo' => $this->data['date_to'] ?? null,
                'filters' => $this->data,
            ]);

            $filename = 'attendance-summary-'.now()->format('Y-m-d').'.pdf';

            return response()->streamDownload(
                fn () => print ($pdf->output()),
                $filename,
                ['Content-Type' => 'application/pdf']
            );
        } catch (\Exception $e) {
            Notification::make()
                ->title('Export Failed')
                ->body('Error: '.$e->getMessage())
                ->danger()
                ->send();
        }
    }
}
