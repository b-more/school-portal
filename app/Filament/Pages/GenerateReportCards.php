<?php

namespace App\Filament\Pages;

use App\Constants\RoleConstants;
use App\Models\AcademicYear;
use App\Models\ClassSection;
use App\Models\ReportCardComment;
use App\Models\Result;
use App\Models\Student;
use App\Models\SubjectTeaching;
use App\Models\Teacher;
use App\Models\Term;
use App\Services\ResultsService;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Auth;

class GenerateReportCards extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static string $view = 'filament.pages.generate-report-cards';

    protected static ?string $navigationLabel = 'Report Cards';

    protected static ?string $title = 'Generate Report Cards';

    protected static ?string $navigationGroup = 'Academic';

    protected static ?int $navigationSort = 9;

    public ?array $data = [];

    public $classSectionId = null;

    public $termId = null;

    public $year = null;

    public $students = [];

    public $studentComments = [];

    public $selectedStudentId = null;

    public $classTeacherComment = '';

    public $headTeacherComment = '';

    protected $resultsService;

    public function boot(ResultsService $resultsService): void
    {
        $this->resultsService = $resultsService;
    }

    public function mount(): void
    {
        $this->year = now()->year;

        // Get current term
        $currentTerm = Term::whereHas('academicYear', function ($q) {
            $q->where('is_active', true);
        })->where('is_current', true)->first();

        if ($currentTerm) {
            $this->termId = $currentTerm->id;
        }

        $this->form->fill([
            'year' => $this->year,
            'termId' => $this->termId,
        ]);
    }

    public function form(Form $form): Form
    {
        $user = Auth::user();
        $teacher = null;
        $isAdmin = $user->role_id === RoleConstants::ADMIN;

        if ($user->role_id === RoleConstants::TEACHER) {
            $teacher = Teacher::where('user_id', $user->id)->first();
        }

        // Get class section options based on role
        $classSectionOptions = $this->getClassSectionOptions($teacher, $isAdmin);

        // Get terms
        $termOptions = Term::whereHas('academicYear', function ($q) {
            $q->where('is_active', true);
        })->orderBy('name')->pluck('name', 'id')->toArray();

        return $form
            ->schema([
                Select::make('classSectionId')
                    ->label('Select Class')
                    ->options($classSectionOptions)
                    ->required()
                    ->reactive()
                    ->afterStateUpdated(function ($state) {
                        $this->classSectionId = $state;
                        $this->loadStudents();
                    }),

                Select::make('termId')
                    ->label('Term')
                    ->options($termOptions)
                    ->required()
                    ->default($this->termId)
                    ->reactive()
                    ->afterStateUpdated(function ($state) {
                        $this->termId = $state;
                        $this->loadStudents();
                    }),

                Select::make('year')
                    ->label('Year')
                    ->options(function () {
                        $currentYear = now()->year;
                        return [
                            $currentYear - 1 => $currentYear - 1,
                            $currentYear => $currentYear,
                            $currentYear + 1 => $currentYear + 1,
                        ];
                    })
                    ->default(now()->year)
                    ->required()
                    ->reactive()
                    ->afterStateUpdated(function ($state) {
                        $this->year = $state;
                        $this->loadStudents();
                    }),
            ])
            ->statePath('data')
            ->columns(3);
    }

    protected function getClassSectionOptions($teacher, $isAdmin): array
    {
        if ($isAdmin) {
            return ClassSection::with('grade')
                ->where('is_active', true)
                ->get()
                ->mapWithKeys(function ($section) {
                    $gradeName = $section->grade ? $section->grade->name : 'Unknown';
                    return [$section->id => "{$gradeName} - {$section->name}"];
                })
                ->toArray();
        }

        if ($teacher) {
            // Get class sections where teacher is class teacher or has subject assignments
            $classSectionIds = collect();

            // Class sections where teacher is class teacher
            $classTeacherSections = ClassSection::where('class_teacher_id', $teacher->id)
                ->where('is_active', true)
                ->pluck('id');

            // Class sections where teacher has subject assignments
            $subjectSections = SubjectTeaching::where('teacher_id', $teacher->id)
                ->currentYear()
                ->pluck('class_section_id');

            $classSectionIds = $classTeacherSections->merge($subjectSections)->unique();

            return ClassSection::with('grade')
                ->whereIn('id', $classSectionIds)
                ->where('is_active', true)
                ->get()
                ->mapWithKeys(function ($section) {
                    $gradeName = $section->grade ? $section->grade->name : 'Unknown';
                    return [$section->id => "{$gradeName} - {$section->name}"];
                })
                ->toArray();
        }

        return [];
    }

    public function loadStudents(): void
    {
        if (!$this->classSectionId || !$this->termId || !$this->year) {
            $this->students = [];
            return;
        }

        $resultsService = app(ResultsService::class);
        $academicYear = AcademicYear::where('is_active', true)->first();

        $students = Student::where('class_section_id', $this->classSectionId)
            ->where('enrollment_status', 'active')
            ->orderBy('name')
            ->get();

        $this->students = [];

        foreach ($students as $student) {
            // Get results count
            $resultsCount = Result::where('student_id', $student->id)
                ->where('term', $this->termId)
                ->where('year', $this->year)
                ->whereIn('exam_type', ['mid-term', 'final'])
                ->count();

            // Calculate average
            $combinedData = $resultsService->calculateCombinedAverage($student->id, $this->termId, $this->year);

            // Get existing comments
            $comment = null;
            if ($academicYear) {
                $comment = ReportCardComment::where('student_id', $student->id)
                    ->where('term_id', $this->termId)
                    ->where('academic_year_id', $academicYear->id)
                    ->first();
            }

            $this->students[] = [
                'id' => $student->id,
                'name' => $student->name,
                'student_id_number' => $student->student_id_number,
                'results_count' => $resultsCount,
                'subjects_count' => $combinedData['combined']['subjects_count'],
                'average' => $combinedData['combined']['average'],
                'has_class_teacher_comment' => $comment ? !empty($comment->class_teacher_comment) : false,
                'has_head_teacher_comment' => $comment ? !empty($comment->head_teacher_comment) : false,
                'last_generated' => $comment ? $comment->last_generated_at?->format('d/m/Y H:i') : null,
            ];
        }
    }

    public function openCommentModal($studentId): void
    {
        $this->selectedStudentId = $studentId;

        $academicYear = AcademicYear::where('is_active', true)->first();

        if ($academicYear) {
            $comment = ReportCardComment::where('student_id', $studentId)
                ->where('term_id', $this->termId)
                ->where('academic_year_id', $academicYear->id)
                ->first();

            $this->classTeacherComment = $comment ? ($comment->class_teacher_comment ?? '') : '';
            $this->headTeacherComment = $comment ? ($comment->head_teacher_comment ?? '') : '';
        }

        $this->dispatch('open-modal', id: 'comment-modal');
    }

    public function saveComments(): void
    {
        if (!$this->selectedStudentId) {
            return;
        }

        $academicYear = AcademicYear::where('is_active', true)->first();
        if (!$academicYear) {
            Notification::make()
                ->title('No active academic year found')
                ->danger()
                ->send();
            return;
        }

        $user = Auth::user();
        $teacher = Teacher::where('user_id', $user->id)->first();
        $teacherId = $teacher ? $teacher->id : null;

        $comment = ReportCardComment::findOrCreateFor(
            $this->selectedStudentId,
            $this->termId,
            $academicYear->id
        );

        $updateData = [];

        // Only update class teacher comment if provided
        if ($this->classTeacherComment) {
            $updateData['class_teacher_comment'] = $this->classTeacherComment;
            $updateData['class_teacher_id'] = $teacherId;
            $updateData['class_teacher_commented_at'] = now();
        }

        // Only update head teacher comment if user is admin
        if ($user->role_id === RoleConstants::ADMIN && $this->headTeacherComment) {
            $updateData['head_teacher_comment'] = $this->headTeacherComment;
            $updateData['head_teacher_id'] = $teacherId;
            $updateData['head_teacher_commented_at'] = now();
        }

        if (!empty($updateData)) {
            $comment->update($updateData);
        }

        Notification::make()
            ->title('Comments saved successfully')
            ->success()
            ->send();

        $this->dispatch('close-modal', id: 'comment-modal');

        // Reload students to update the UI
        $this->loadStudents();
    }

    public function generatePdf($studentId): void
    {
        // Redirect to PDF generation URL
        $url = route('report-cards.generate', [
            'student' => $studentId,
            'term' => $this->termId,
            'year' => $this->year,
        ]);

        $this->redirect($url);
    }

    public function previewReport($studentId): void
    {
        // Redirect to preview URL (opens in new tab via JS)
        $url = route('report-cards.preview', [
            'student' => $studentId,
            'term' => $this->termId,
            'year' => $this->year,
        ]);

        $this->dispatch('open-preview', url: $url);
    }

    public function generateBulkPdf(): void
    {
        if (!$this->classSectionId || !$this->termId || !$this->year) {
            Notification::make()
                ->title('Please select class, term and year')
                ->danger()
                ->send();
            return;
        }

        // Redirect to bulk generation URL
        $url = route('report-cards.bulk-generate', [
            'classSection' => $this->classSectionId,
            'term' => $this->termId,
            'year' => $this->year,
        ]);

        $this->redirect($url);
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
