<?php

namespace App\Filament\Pages;

use App\Constants\RoleConstants;
use App\Models\ClassSection;
use App\Models\Grade;
use App\Models\Student;
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

class StudentReports extends Page implements HasForms, HasTable
{
    use InteractsWithForms, InteractsWithTable;

    protected static ?string $navigationIcon = 'heroicon-o-document-chart-bar';

    protected static ?string $navigationLabel = 'Student Reports';

    protected static ?string $navigationGroup = 'Reports';

    protected static ?int $navigationSort = 1;

    protected static string $view = 'filament.pages.student-reports';

    public ?array $data = [];

    public static function shouldRegisterNavigation(): bool
    {
        return auth()->user()?->role_id === RoleConstants::ADMIN ?? false;
    }

    public function mount(): void
    {
        $this->form->fill();
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Select::make('report_type')
                    ->label('Report Type')
                    ->options([
                        'all' => 'All Students',
                        'by_class' => 'By Class Section',
                        'by_grade' => 'By Grade',
                        'by_status' => 'By Enrollment Status',
                    ])
                    ->default('all')
                    ->live()
                    ->columnSpan(2),

                Select::make('grade_id')
                    ->label('Grade')
                    ->options(Grade::pluck('name', 'id'))
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

                        return ClassSection::where('grade_id', $gradeId)
                            ->get()
                            ->pluck('name', 'id');
                    })
                    ->searchable()
                    ->visible(fn ($get) => $get('report_type') === 'by_class'),

                Select::make('enrollment_status')
                    ->label('Enrollment Status')
                    ->options([
                        'active' => 'Active',
                        'inactive' => 'Inactive',
                        'graduated' => 'Graduated',
                        'transferred' => 'Transferred',
                        'withdrawn' => 'Withdrawn',
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
                TextColumn::make('student_id_number')
                    ->label('Student ID')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('name')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('grade.name')
                    ->label('Grade')
                    ->sortable(),

                TextColumn::make('classSection.name')
                    ->label('Class')
                    ->sortable()
                    ->default('N/A'),

                TextColumn::make('gender')
                    ->sortable(),

                TextColumn::make('date_of_birth')
                    ->label('DOB')
                    ->date()
                    ->sortable(),

                TextColumn::make('parentGuardian.name')
                    ->label('Parent/Guardian')
                    ->searchable(),

                TextColumn::make('parentGuardian.phone')
                    ->label('Contact')
                    ->searchable(),

                TextColumn::make('enrollment_status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'active' => 'success',
                        'inactive' => 'warning',
                        'graduated' => 'info',
                        'transferred', 'withdrawn' => 'danger',
                        default => 'gray',
                    }),

                TextColumn::make('admission_date')
                    ->label('Admitted')
                    ->date()
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('grade_id')
                    ->label('Grade')
                    ->options(Grade::pluck('name', 'id'))
                    ->searchable(),

                SelectFilter::make('class_section_id')
                    ->label('Class Section')
                    ->options(function () {
                        return ClassSection::with('grade')->get()->mapWithKeys(function ($section) {
                            return [$section->id => $section->grade->name.' - '.$section->name];
                        });
                    })
                    ->searchable(),

                SelectFilter::make('enrollment_status')
                    ->label('Status')
                    ->options([
                        'active' => 'Active',
                        'inactive' => 'Inactive',
                        'graduated' => 'Graduated',
                        'transferred' => 'Transferred',
                        'withdrawn' => 'Withdrawn',
                    ]),

                SelectFilter::make('gender')
                    ->options([
                        'Male' => 'Male',
                        'Female' => 'Female',
                    ]),

                Filter::make('admission_date')
                    ->form([
                        DatePicker::make('admitted_from'),
                        DatePicker::make('admitted_until'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['admitted_from'],
                                fn (Builder $query, $date): Builder => $query->whereDate('admission_date', '>=', $date),
                            )
                            ->when(
                                $data['admitted_until'],
                                fn (Builder $query, $date): Builder => $query->whereDate('admission_date', '<=', $date),
                            );
                    }),
            ])
            ->actions([
                Action::make('view_details')
                    ->label('Details')
                    ->icon('heroicon-o-eye')
                    ->url(fn (Student $record): string => route('filament.admin.resources.students.view', ['record' => $record])),

                Action::make('export_pdf')
                    ->label('PDF')
                    ->icon('heroicon-o-document-arrow-down')
                    ->action(function (Student $record) {
                        return $this->exportStudentPdf($record);
                    }),
            ])
            ->bulkActions([
                \Filament\Tables\Actions\BulkAction::make('export_bulk_pdf')
                    ->label('Export Selected as PDF')
                    ->icon('heroicon-o-document-duplicate')
                    ->action(function ($records) {
                        return $this->exportBulkStudentsPdf($records);
                    })
                    ->deselectRecordsAfterCompletion(),
            ])
            ->headerActions([
                Action::make('export_all_pdf')
                    ->label('Export All as PDF')
                    ->icon('heroicon-o-document-text')
                    ->color('success')
                    ->action(function () {
                        return $this->exportAllStudentsPdf();
                    }),
            ])
            ->defaultSort('name', 'asc')
            ->paginated([10, 25, 50, 100]);
    }

    protected function getTableQuery(): Builder
    {
        $query = Student::query()
            ->with(['grade', 'classSection', 'parentGuardian'])
            ->withCount(['fees', 'results', 'attendances']);

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

            case 'by_status':
                if (! empty($this->data['enrollment_status'])) {
                    $query->where('enrollment_status', $this->data['enrollment_status']);
                }
                break;

            case 'all':
            default:
                // No additional filtering
                break;
        }

        return $query;
    }

    protected function exportStudentPdf(Student $student)
    {
        try {
            $pdf = Pdf::loadView('pdf.student-report', [
                'student' => $student->load(['grade', 'classSection', 'parentGuardian', 'fees', 'attendances']),
                'schoolName' => 'St. Francis Of Assisi Private School',
                'reportDate' => now()->format('F d, Y'),
            ]);

            $filename = 'student-'.$student->student_id_number.'-'.now()->format('Y-m-d').'.pdf';

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

    protected function exportBulkStudentsPdf($students)
    {
        try {
            $studentsData = $students->load(['grade', 'classSection', 'parentGuardian']);

            $pdf = Pdf::loadView('pdf.students-list', [
                'students' => $studentsData,
                'schoolName' => 'St. Francis Of Assisi Private School',
                'reportDate' => now()->format('F d, Y'),
                'reportType' => $this->data['report_type'] ?? 'Custom Selection',
            ]);

            $filename = 'students-list-'.now()->format('Y-m-d').'.pdf';

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

    protected function exportAllStudentsPdf()
    {
        try {
            $students = $this->getTableQuery()
                ->get()
                ->load(['grade', 'classSection', 'parentGuardian']);

            $pdf = Pdf::loadView('pdf.students-list', [
                'students' => $students,
                'schoolName' => 'St. Francis Of Assisi Private School',
                'reportDate' => now()->format('F d, Y'),
                'reportType' => $this->data['report_type'] ?? 'All Students',
                'totalCount' => $students->count(),
            ]);

            $filename = 'all-students-'.now()->format('Y-m-d').'.pdf';

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
