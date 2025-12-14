<?php

namespace App\Http\Controllers;

use App\Models\AcademicYear;
use App\Models\ClassSection;
use App\Models\GradingScale;
use App\Models\ReportCardComment;
use App\Models\SchoolSettings;
use App\Models\Student;
use App\Models\Term;
use App\Services\ResultsService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use ZipArchive;

class ReportCardController extends Controller
{
    protected ResultsService $resultsService;

    public function __construct(ResultsService $resultsService)
    {
        $this->resultsService = $resultsService;
    }

    /**
     * Generate a single student's report card PDF.
     */
    public function generate(Request $request, Student $student, Term $term)
    {
        $year = $request->get('year', now()->year);

        $reportData = $this->prepareReportCardData($student, $term, $year);

        $pdf = Pdf::loadView('pdf.report-card', $reportData);
        $pdf->setPaper('A4', 'portrait');

        // Mark as generated
        $comment = ReportCardComment::findOrCreateFor($student->id, $term->id, $reportData['academicYear']->id);
        $comment->markAsGenerated();

        $filename = "report-card-{$student->student_id_number}-term{$term->id}-{$year}.pdf";

        return $pdf->download($filename);
    }

    /**
     * Preview a student's report card (HTML).
     */
    public function preview(Request $request, Student $student, Term $term)
    {
        $year = $request->get('year', now()->year);

        $reportData = $this->prepareReportCardData($student, $term, $year);

        return view('pdf.report-card', $reportData);
    }

    /**
     * Generate report cards for all students in a class section (ZIP download).
     */
    public function bulkGenerate(Request $request, ClassSection $classSection, Term $term)
    {
        $year = $request->get('year', now()->year);

        $students = $classSection->students()
            ->where('enrollment_status', 'active')
            ->orderBy('name')
            ->get();

        if ($students->isEmpty()) {
            return back()->with('error', 'No active students found in this class.');
        }

        $academicYear = AcademicYear::where('is_active', true)->first();

        // Create a temporary directory
        $tempDir = storage_path('app/temp/report-cards-' . time());
        if (!file_exists($tempDir)) {
            mkdir($tempDir, 0755, true);
        }

        $generatedFiles = [];

        foreach ($students as $student) {
            $reportData = $this->prepareReportCardData($student, $term, $year);

            $pdf = Pdf::loadView('pdf.report-card', $reportData);
            $pdf->setPaper('A4', 'portrait');

            $filename = $this->sanitizeFilename($student->name) . "-{$student->student_id_number}.pdf";
            $filepath = $tempDir . '/' . $filename;

            $pdf->save($filepath);
            $generatedFiles[] = $filepath;

            // Mark as generated
            if ($academicYear) {
                $comment = ReportCardComment::findOrCreateFor($student->id, $term->id, $academicYear->id);
                $comment->markAsGenerated();
            }
        }

        // Create ZIP file
        $gradeName = $classSection->grade ? $classSection->grade->name : 'Unknown';
        $zipFilename = "report-cards-{$gradeName}-{$classSection->name}-term{$term->id}-{$year}.zip";
        $zipPath = storage_path('app/temp/' . $zipFilename);

        $zip = new ZipArchive();
        if ($zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) === true) {
            foreach ($generatedFiles as $file) {
                $zip->addFile($file, basename($file));
            }
            $zip->close();
        }

        // Clean up individual PDF files
        foreach ($generatedFiles as $file) {
            if (file_exists($file)) {
                unlink($file);
            }
        }
        rmdir($tempDir);

        // Download and delete ZIP
        return response()->download($zipPath, $zipFilename)->deleteFileAfterSend(true);
    }

    /**
     * Prepare all data needed for a report card.
     */
    protected function prepareReportCardData(Student $student, Term $term, int $year): array
    {
        // Load relationships
        $student->load(['classSection.grade', 'classSection.classTeacher', 'parentGuardian']);

        // Get school settings
        $schoolSettings = SchoolSettings::getInstance();

        // Get academic year
        $academicYear = AcademicYear::where('is_active', true)->first();

        // Get results data
        $resultsData = $this->resultsService->getStudentTermResults($student->id, $term->id, $year);

        // Get grading scale
        $gradingScale = null;
        if ($student->classSection && $student->classSection->grade) {
            $gradeLevel = GradingScale::determineGradeLevelFromGrade($student->classSection->grade);
            $gradingScale = GradingScale::getDefaultForGradeLevel($gradeLevel);
        }

        // Get report card comments
        $comments = null;
        if ($academicYear) {
            $comments = ReportCardComment::where('student_id', $student->id)
                ->where('term_id', $term->id)
                ->where('academic_year_id', $academicYear->id)
                ->first();
        }

        // Get class teacher name
        $classTeacherName = null;
        if ($student->classSection && $student->classSection->classTeacher) {
            $classTeacherName = $student->classSection->classTeacher->name;
        }

        return [
            'student' => $student,
            'term' => $term,
            'year' => $year,
            'academicYear' => $academicYear,
            'schoolSettings' => $schoolSettings,
            'resultsData' => $resultsData,
            'gradingScale' => $gradingScale,
            'comments' => $comments,
            'classTeacherName' => $classTeacherName,
            'generatedAt' => now(),
        ];
    }

    /**
     * Sanitize filename for safe file creation.
     */
    protected function sanitizeFilename(string $filename): string
    {
        // Remove special characters and replace spaces
        $filename = preg_replace('/[^a-zA-Z0-9\s-]/', '', $filename);
        $filename = preg_replace('/\s+/', '-', trim($filename));
        return strtolower($filename);
    }

    /**
     * API endpoint to get report card data (for AJAX preview).
     */
    public function getReportCardData(Request $request, Student $student, Term $term)
    {
        $year = $request->get('year', now()->year);

        $reportData = $this->prepareReportCardData($student, $term, $year);

        // Convert to JSON-friendly format
        return response()->json([
            'student' => [
                'id' => $student->id,
                'name' => $student->name,
                'student_id_number' => $student->student_id_number,
                'gender' => $student->gender,
                'date_of_birth' => $student->date_of_birth?->format('d/m/Y'),
                'class_section' => $student->classSection ? "{$student->classSection->grade->name} - {$student->classSection->name}" : null,
            ],
            'term' => $term->name,
            'year' => $year,
            'results' => $reportData['resultsData'],
            'comments' => $reportData['comments'] ? [
                'class_teacher_comment' => $reportData['comments']->class_teacher_comment,
                'head_teacher_comment' => $reportData['comments']->head_teacher_comment,
            ] : null,
        ]);
    }
}
