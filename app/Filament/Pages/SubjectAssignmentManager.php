<?php

namespace App\Filament\Pages;

use App\Models\Employee;
use App\Models\Subject;
use App\Models\SchoolClass;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Pages\Page;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\DB;
use Illuminate\Contracts\View\View;

class SubjectAssignmentManager extends Page
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-academic-cap';
    protected static ?string $navigationGroup = 'Academic Management';
    protected static ?string $navigationLabel = 'Subject Manager';
    protected static ?string $slug = 'subject-assignment-manager';
    protected static ?int $navigationSort = 9;

    public $activeTab = 'create';
    public $subjectData = [];
    public $assignmentData = [];

    public function mount(): void
    {
        // Initialize form states
        $this->subjectData = [];
        $this->assignmentData = [];
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Create New Subject')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->label('Subject Name')
                            ->required()
                            ->maxLength(255),

                        Forms\Components\TextInput::make('code')
                            ->label('Subject Code')
                            ->required()
                            ->maxLength(50),

                        Forms\Components\Select::make('grade_level')
                            ->label('Grade Level')
                            ->options([
                                'ECL' => 'ECL',
                                'Primary' => 'Primary',
                                'Secondary' => 'Secondary',
                                'All' => 'All Levels',
                            ])
                            ->required(),

                        Forms\Components\Textarea::make('description')
                            ->label('Description')
                            ->maxLength(65535)
                            ->columnSpanFull(),

                        Forms\Components\Toggle::make('is_active')
                            ->label('Active')
                            ->default(true)
                            ->required(),
                    ])
                    ->columns(2)
            ])
            ->statePath('subjectData');
    }

    public function assignmentForm(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Assign Subjects to Teachers')
                    ->schema([
                        Forms\Components\Select::make('teacher_id')
                            ->label('Select Teacher')
                            ->options(
                                Employee::where('role', 'teacher')
                                    ->orderBy('name')
                                    ->pluck('name', 'id')
                            )
                            ->searchable()
                            ->required()
                            ->reactive()
                            ->afterStateUpdated(fn (callable $set) => $set('subject_ids', [])),

                        Forms\Components\Select::make('department')
                            ->label('Department Filter')
                            ->options([
                                'All' => 'All Departments',
                                'ECL' => 'ECL',
                                'Primary' => 'Primary',
                                'Secondary' => 'Secondary',
                            ])
                            ->default('All')
                            ->reactive(),

                        Forms\Components\Select::make('subject_ids')
                            ->label('Assign Subjects')
                            ->options(function (callable $get) {
                                $department = $get('department');

                                $query = Subject::query()->where('is_active', true);

                                if ($department && $department !== 'All') {
                                    $query->where(function($q) use ($department) {
                                        $q->where('grade_level', $department)
                                          ->orWhere('grade_level', 'All');
                                    });
                                }

                                return $query->orderBy('name')
                                    ->pluck('name', 'id')
                                    ->toArray();
                            })
                            ->multiple()
                            ->searchable()
                            ->required()
                            ->helperText('Select the subjects you want to assign to this teacher'),

                        Forms\Components\Placeholder::make('current_subjects')
                            ->label('Currently Assigned Subjects')
                            ->content(function (callable $get) {
                                $teacherId = $get('teacher_id');

                                if (!$teacherId) {
                                    return 'Select a teacher to see their current subject assignments';
                                }

                                $teacher = Employee::find($teacherId);

                                if (!$teacher) {
                                    return 'Teacher not found';
                                }

                                $subjects = $teacher->subjects;

                                if ($subjects->isEmpty()) {
                                    return 'No subjects currently assigned';
                                }

                                return implode(', ', $subjects->pluck('name')->toArray());
                            }),

                        Forms\Components\Toggle::make('replace_existing')
                            ->label('Replace existing assignments')
                            ->helperText('If checked, this will remove any existing subject assignments not included in your selection')
                            ->default(false),
                    ])
                    ->columns(2)
            ])
            ->statePath('assignmentData');
    }

    // Define form state paths
    protected function getFormStatePath(): string
    {
        return 'subjectData';
    }

    protected function getAssignmentFormStatePath(): string
    {
        return 'assignmentData';
    }

    public function create(): void
    {
        // Validate form data
        $data = $this->form->getState();

        try {
            $subject = Subject::create($data);

            Notification::make()
                ->title('Subject created successfully')
                ->body("Created subject: {$subject->name}")
                ->success()
                ->send();

            // Reset the form
            $this->form->fill();
        } catch (\Exception $e) {
            Notification::make()
                ->title('Error creating subject')
                ->body("Error: {$e->getMessage()}")
                ->danger()
                ->send();
        }
    }

    public function assign(): void
    {
        // Validate form data
        $data = $this->assignmentForm->getState();

        try {
            $teacher = Employee::findOrFail($data['teacher_id']);
            $subjectIds = $data['subject_ids'];

            // Replace or add subjects based on the toggle
            if (!empty($data['replace_existing'])) {
                $teacher->subjects()->sync($subjectIds);
            } else {
                $teacher->subjects()->syncWithoutDetaching($subjectIds);
            }

            // For Secondary teachers, also handle class-subject-teacher assignments
            if ($teacher->department === 'Secondary') {
                $this->handleSecondaryTeacherAssignments($teacher, $subjectIds, $data);
            }

            Notification::make()
                ->title('Subjects assigned successfully')
                ->body("Assigned " . count($subjectIds) . " subjects to {$teacher->name}")
                ->success()
                ->send();

            // Reset only the subject selection
            $this->assignmentData['subject_ids'] = [];
        } catch (\Exception $e) {
            Notification::make()
                ->title('Error assigning subjects')
                ->body("Error: {$e->getMessage()}")
                ->danger()
                ->send();
        }
    }

    protected function handleSecondaryTeacherAssignments($teacher, $subjectIds, $formData): void
    {
        // Get teacher's class assignments
        $classIds = $teacher->classes()->pluck('id')->toArray();

        if (empty($classIds)) {
            return; // No classes assigned, nothing to do
        }

        // If replacing existing, remove old class-subject-teacher records
        if (!empty($formData['replace_existing'])) {
            DB::table('class_subject_teacher')
                ->where('employee_id', $teacher->id)
                ->whereNotIn('subject_id', $subjectIds)
                ->delete();
        }

        // Create new class-subject-teacher records
        $records = [];
        $now = now();

        foreach ($classIds as $classId) {
            foreach ($subjectIds as $subjectId) {
                // Check if record already exists
                $exists = DB::table('class_subject_teacher')
                    ->where([
                        'employee_id' => $teacher->id,
                        'subject_id' => $subjectId,
                        'class_id' => $classId,
                    ])
                    ->exists();

                if (!$exists) {
                    $records[] = [
                        'employee_id' => $teacher->id,
                        'subject_id' => $subjectId,
                        'class_id' => $classId,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ];
                }
            }
        }

        if (!empty($records)) {
            DB::table('class_subject_teacher')->insert($records);
        }
    }

    protected function getViewData(): array
    {
        return [
            'subjects' => Subject::orderBy('name')->get(),
        ];
    }

    protected function getFormActions(): array
    {
        return [
            Forms\Actions\Action::make('create')
                ->label('Create Subject')
                ->submit('create')
                ->action('create')
                ->color('primary')
                ->visible(fn () => $this->activeTab === 'create'),

            Forms\Actions\Action::make('assign')
                ->label('Assign Subjects')
                ->submit('assign')
                ->action('assign')
                ->color('success')
                ->visible(fn () => $this->activeTab === 'assign'),
        ];
    }

    public function render(): View
    {
        return view('filament.pages.subject-assignment-manager', [
            'subjects' => Subject::orderBy('name')->get(),
        ]);
    }
}
