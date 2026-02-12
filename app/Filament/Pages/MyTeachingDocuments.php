<?php

namespace App\Filament\Pages;

use App\Constants\RoleConstants;
use App\Models\AcademicYear;
use App\Models\SubjectTeaching;
use App\Models\Teacher;
use App\Models\TeachingDocument;
use App\Models\Term;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Tables\Actions\Action;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class MyTeachingDocuments extends Page implements HasTable
{
    use InteractsWithTable;

    protected static ?string $navigationIcon = 'heroicon-o-document-arrow-up';
    protected static string $view = 'filament.pages.my-teaching-documents';
    protected static ?string $navigationLabel = 'My Documents';
    protected static ?string $navigationGroup = 'Teaching';
    protected static ?int $navigationSort = 4;
    protected static ?string $title = 'My Teaching Documents';

    public function getTeacher(): ?Teacher
    {
        return Teacher::where('user_id', Auth::id())->first();
    }

    public function table(Table $table): Table
    {
        $teacher = $this->getTeacher();
        $teacherId = $teacher?->id;

        return $table
            ->query(
                TeachingDocument::query()
                    ->where('teacher_id', $teacherId ?? 0)
                    ->with(['subject', 'classSection.grade', 'term', 'academicYear'])
            )
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('document_type')
                    ->label('Type')
                    ->badge()
                    ->formatStateUsing(fn (string $state) => TeachingDocument::DOCUMENT_TYPES[$state] ?? $state)
                    ->color(fn (string $state) => match ($state) {
                        'scheme_of_work' => 'success',
                        'lesson_plan' => 'info',
                        default => 'gray',
                    }),
                TextColumn::make('title')
                    ->searchable()
                    ->limit(40),
                TextColumn::make('subject.name')
                    ->label('Subject')
                    ->sortable(),
                TextColumn::make('classSection')
                    ->label('Class')
                    ->formatStateUsing(function ($record) {
                        $cs = $record->classSection;
                        return $cs ? ($cs->grade->name . ' - ' . $cs->name) : '-';
                    }),
                TextColumn::make('term.name')
                    ->label('Term'),
                TextColumn::make('created_at')
                    ->label('Uploaded')
                    ->dateTime('d M Y')
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('document_type')
                    ->label('Type')
                    ->options(TeachingDocument::DOCUMENT_TYPES),
                SelectFilter::make('subject_id')
                    ->label('Subject')
                    ->options(function () use ($teacherId) {
                        if (!$teacherId) return [];
                        return SubjectTeaching::where('teacher_id', $teacherId)
                            ->currentYear()
                            ->with('subject')
                            ->get()
                            ->pluck('subject.name', 'subject_id')
                            ->unique()
                            ->toArray();
                    }),
                SelectFilter::make('term_id')
                    ->label('Term')
                    ->options(function () {
                        $year = AcademicYear::current();
                        if (!$year) return [];
                        return Term::where('academic_year_id', $year->id)
                            ->pluck('name', 'id')
                            ->toArray();
                    }),
            ])
            ->headerActions([
                \Filament\Tables\Actions\Action::make('upload')
                    ->label('Upload Document')
                    ->icon('heroicon-o-arrow-up-tray')
                    ->color('primary')
                    ->modalHeading('Upload Teaching Document')
                    ->modalWidth('lg')
                    ->form(function () use ($teacherId) {
                        $currentYear = AcademicYear::current();
                        $currentTerm = Term::current();

                        return [
                            Select::make('document_type')
                                ->label('Document Type')
                                ->options(TeachingDocument::DOCUMENT_TYPES)
                                ->required(),
                            TextInput::make('title')
                                ->label('Title')
                                ->required()
                                ->maxLength(255),
                            Select::make('subject_id')
                                ->label('Subject')
                                ->options(function () use ($teacherId, $currentYear) {
                                    if (!$teacherId || !$currentYear) return [];
                                    return SubjectTeaching::where('teacher_id', $teacherId)
                                        ->where('academic_year_id', $currentYear->id)
                                        ->with('subject')
                                        ->get()
                                        ->pluck('subject.name', 'subject_id')
                                        ->unique()
                                        ->toArray();
                                })
                                ->required()
                                ->live(),
                            Select::make('class_section_id')
                                ->label('Class Section')
                                ->options(function ($get) use ($teacherId, $currentYear) {
                                    $subjectId = $get('subject_id');
                                    if (!$teacherId || !$currentYear || !$subjectId) return [];
                                    return SubjectTeaching::where('teacher_id', $teacherId)
                                        ->where('academic_year_id', $currentYear->id)
                                        ->where('subject_id', $subjectId)
                                        ->with('classSection.grade')
                                        ->get()
                                        ->mapWithKeys(function ($st) {
                                            $cs = $st->classSection;
                                            return [$cs->id => $cs->grade->name . ' - ' . $cs->name];
                                        })
                                        ->toArray();
                                })
                                ->required(),
                            Select::make('academic_year_id')
                                ->label('Academic Year')
                                ->options(AcademicYear::pluck('name', 'id'))
                                ->default($currentYear?->id)
                                ->required()
                                ->live(),
                            Select::make('term_id')
                                ->label('Term')
                                ->options(function ($get) {
                                    $yearId = $get('academic_year_id');
                                    if (!$yearId) return [];
                                    return Term::where('academic_year_id', $yearId)
                                        ->pluck('name', 'id')
                                        ->toArray();
                                })
                                ->default($currentTerm?->id)
                                ->required(),
                            FileUpload::make('file_path')
                                ->label('Document File')
                                ->disk('public')
                                ->directory('teaching-documents/uploads')
                                ->visibility('public')
                                ->preserveFilenames()
                                ->acceptedFileTypes([
                                    'application/pdf',
                                    'application/msword',
                                    'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                                ])
                                ->maxSize(10240)
                                ->required()
                                ->helperText('PDF or Word documents only, max 10MB'),
                            Textarea::make('description')
                                ->label('Description (optional)')
                                ->rows(3)
                                ->maxLength(1000),
                        ];
                    })
                    ->action(function (array $data) {
                        $teacher = $this->getTeacher();
                        if (!$teacher) {
                            Notification::make()
                                ->title('Error')
                                ->body('Teacher profile not found.')
                                ->danger()
                                ->send();
                            return;
                        }

                        TeachingDocument::create([
                            'teacher_id' => $teacher->id,
                            'subject_id' => $data['subject_id'],
                            'class_section_id' => $data['class_section_id'],
                            'academic_year_id' => $data['academic_year_id'],
                            'term_id' => $data['term_id'],
                            'document_type' => $data['document_type'],
                            'title' => $data['title'],
                            'file_path' => $data['file_path'],
                            'original_filename' => $data['file_path'],
                            'description' => $data['description'] ?? null,
                        ]);

                        Notification::make()
                            ->title('Document Uploaded')
                            ->body('Your teaching document has been uploaded successfully.')
                            ->success()
                            ->send();
                    }),
            ])
            ->actions([
                Action::make('download')
                    ->label('Download')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->color('success')
                    ->url(fn (TeachingDocument $record) => Storage::disk('public')->url($record->file_path))
                    ->openUrlInNewTab(),
                DeleteAction::make()
                    ->before(function (TeachingDocument $record) {
                        if ($record->file_path) {
                            Storage::disk('public')->delete($record->file_path);
                        }
                    }),
            ])
            ->emptyStateHeading('No documents uploaded yet')
            ->emptyStateDescription('Click "Upload Document" to upload your Schemes of Work and Lesson Plans.')
            ->emptyStateIcon('heroicon-o-document');
    }

    public static function canAccess(): bool
    {
        return in_array(auth()->user()?->role_id, RoleConstants::teaching());
    }

    public static function shouldRegisterNavigation(): bool
    {
        return in_array(auth()->user()?->role_id, RoleConstants::teaching());
    }
}
