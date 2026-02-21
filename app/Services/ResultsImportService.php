<?php

namespace App\Services;

use App\Models\AcademicYear;
use App\Models\ClassSection;
use App\Models\Result;
use App\Models\SchoolSettings;
use App\Models\Student;
use App\Models\Subject;
use App\Models\Term;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Facades\Excel;

class ResultsImportService
{
    protected SmsService $smsService;
    protected array $importErrors = [];
    protected array $importSuccess = [];
    protected array $subjectMapping = [];

    public function __construct(SmsService $smsService)
    {
        $this->smsService = $smsService;
    }

    /**
     * Import results from an Excel/CSV file
     */
    public function importFromFile(
        string $filePath,
        int $classSectionId,
        int $termId,
        int $year,
        string $examType = 'final',
        ?int $recordedBy = null
    ): array {
        $this->importErrors = [];
        $this->importSuccess = [];

        try {
            // Load the file
            $data = Excel::toArray([], $filePath)[0] ?? [];

            if (empty($data)) {
                return [
                    'success' => false,
                    'message' => 'The file is empty or could not be read.',
                    'imported' => 0,
                    'errors' => ['Empty file'],
                ];
            }

            // Get headers from first row
            $headers = array_map('trim', array_map('strtolower', $data[0]));

            // Build subject mapping from headers
            $this->buildSubjectMapping($headers, $classSectionId);

            // Get class section for validation
            $classSection = ClassSection::with('grade')->find($classSectionId);
            if (!$classSection) {
                return [
                    'success' => false,
                    'message' => 'Class section not found.',
                    'imported' => 0,
                    'errors' => ['Invalid class section'],
                ];
            }

            // Process each row (skip header)
            $importedCount = 0;
            $rows = array_slice($data, 1);

            DB::beginTransaction();

            foreach ($rows as $rowIndex => $row) {
                $rowNumber = $rowIndex + 2; // Account for header and 0-index

                // Skip empty rows
                if (empty(array_filter($row))) {
                    continue;
                }

                $result = $this->processRow($row, $headers, $classSectionId, $termId, $year, $examType, $recordedBy, $rowNumber);

                if ($result['success']) {
                    $importedCount++;
                    $this->importSuccess[] = $result;
                }
            }

            DB::commit();

            return [
                'success' => true,
                'message' => "Successfully imported results for {$importedCount} students.",
                'imported' => $importedCount,
                'errors' => $this->importErrors,
                'results' => $this->importSuccess,
            ];

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Results import failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return [
                'success' => false,
                'message' => 'Import failed: ' . $e->getMessage(),
                'imported' => 0,
                'errors' => [$e->getMessage()],
            ];
        }
    }

    /**
     * Build subject mapping from Excel headers
     */
    protected function buildSubjectMapping(array $headers, int $classSectionId): void
    {
        $classSection = ClassSection::with('grade')->find($classSectionId);
        $gradeId = $classSection?->grade_id;

        // Get all subjects for this grade
        $subjects = Subject::where('is_active', true)
            ->when($gradeId, function ($query) use ($gradeId) {
                $query->whereHas('grades', function ($q) use ($gradeId) {
                    $q->where('grades.id', $gradeId);
                });
            })
            ->get();

        // Common subject name variations
        $subjectAliases = [
            'math' => ['math', 'mathematics', 'maths'],
            'eng' => ['eng', 'english', 'english language'],
            'sci' => ['sci', 'science', 'integrated science', 'int science'],
            'ss' => ['ss', 'social studies', 'social', 'soc studies'],
            're' => ['re', 'religious education', 'rel education', 'religion'],
            'cts' => ['cts', 'creative technology', 'creative tech', 'creative and technology studies'],
            'pe' => ['pe', 'physical education', 'phys ed', 'physical ed'],
            'ict' => ['ict', 'computer', 'computers', 'computer studies', 'information technology'],
            'art' => ['art', 'arts', 'art and design'],
            'music' => ['music'],
            'french' => ['french', 'fr'],
            'bemba' => ['bemba', 'local language'],
            'home ec' => ['home ec', 'home economics', 'he'],
        ];

        foreach ($headers as $index => $header) {
            $headerLower = strtolower(trim($header));

            // Skip non-subject columns
            if (in_array($headerLower, ['name', 'student name', 'student_name', 'student id', 'student_id', 'student_id_number', 'rank', 'position', 'total', 'average', 'avg'])) {
                continue;
            }

            // Try to match subject
            foreach ($subjects as $subject) {
                $subjectNameLower = strtolower($subject->name);
                $subjectCodeLower = strtolower($subject->code ?? '');

                // Direct match
                if ($headerLower === $subjectNameLower || $headerLower === $subjectCodeLower) {
                    $this->subjectMapping[$index] = $subject;
                    break;
                }

                // Check aliases
                foreach ($subjectAliases as $code => $aliases) {
                    if (in_array($headerLower, $aliases)) {
                        if (str_contains($subjectNameLower, $code) || $subjectCodeLower === $code) {
                            $this->subjectMapping[$index] = $subject;
                            break 2;
                        }
                    }
                }

                // Partial match
                if (str_contains($subjectNameLower, $headerLower) || str_contains($headerLower, $subjectNameLower)) {
                    $this->subjectMapping[$index] = $subject;
                    break;
                }
            }
        }
    }

    /**
     * Process a single row from the Excel file
     */
    protected function processRow(
        array $row,
        array $headers,
        int $classSectionId,
        int $termId,
        int $year,
        string $examType,
        ?int $recordedBy,
        int $rowNumber
    ): array {
        // Find student name/ID column
        $studentName = null;
        $studentId = null;

        foreach ($headers as $index => $header) {
            $headerLower = strtolower(trim($header));
            $value = trim($row[$index] ?? '');

            if (in_array($headerLower, ['name', 'student name', 'student_name'])) {
                $studentName = $value;
            }
            if (in_array($headerLower, ['student id', 'student_id', 'student_id_number'])) {
                $studentId = $value;
            }
        }

        if (empty($studentName) && empty($studentId)) {
            $this->importErrors[] = "Row {$rowNumber}: No student name or ID found.";
            return ['success' => false];
        }

        // Find the student
        $student = $this->findStudent($studentName, $studentId, $classSectionId);

        if (!$student) {
            $this->importErrors[] = "Row {$rowNumber}: Student not found - '{$studentName}' (ID: {$studentId})";
            return ['success' => false];
        }

        // Import results for each subject
        $resultsImported = [];
        foreach ($this->subjectMapping as $columnIndex => $subject) {
            $mark = $row[$columnIndex] ?? null;

            if ($mark === null || $mark === '' || !is_numeric($mark)) {
                continue;
            }

            $mark = floatval($mark);

            // Validate mark range
            if ($mark < 0 || $mark > 100) {
                $this->importErrors[] = "Row {$rowNumber}: Invalid mark {$mark} for {$subject->name}. Must be 0-100.";
                continue;
            }

            // Calculate grade
            $grade = $this->calculateGrade($mark);

            // Create or update result
            $result = Result::updateOrCreate(
                [
                    'student_id' => $student->id,
                    'subject_id' => $subject->id,
                    'term' => $termId,
                    'year' => $year,
                    'exam_type' => $examType,
                ],
                [
                    'marks' => $mark,
                    'grade' => $grade,
                    'recorded_by' => $recordedBy,
                ]
            );

            $resultsImported[] = [
                'subject' => $subject->code ?? substr($subject->name, 0, 4),
                'mark' => $mark,
            ];
        }

        if (empty($resultsImported)) {
            $this->importErrors[] = "Row {$rowNumber}: No valid marks found for student '{$student->name}'.";
            return ['success' => false];
        }

        return [
            'success' => true,
            'student_id' => $student->id,
            'student_name' => $student->name,
            'results_count' => count($resultsImported),
            'results' => $resultsImported,
        ];
    }

    /**
     * Find a student by name or ID
     */
    protected function findStudent(?string $name, ?string $studentId, int $classSectionId): ?Student
    {
        $query = Student::where('class_section_id', $classSectionId)
            ->where('enrollment_status', 'active');

        if ($studentId) {
            $student = (clone $query)->where('student_id_number', $studentId)->first();
            if ($student) {
                return $student;
            }
        }

        if ($name) {
            // Try exact match first
            $student = (clone $query)->where('name', $name)->first();
            if ($student) {
                return $student;
            }

            // Try case-insensitive match
            $student = (clone $query)->whereRaw('LOWER(name) = ?', [strtolower($name)])->first();
            if ($student) {
                return $student;
            }

            // Try partial match (for names with different formatting)
            $nameParts = explode(' ', $name);
            if (count($nameParts) >= 2) {
                $student = (clone $query)
                    ->where(function ($q) use ($nameParts) {
                        foreach ($nameParts as $part) {
                            $q->where('name', 'LIKE', '%' . $part . '%');
                        }
                    })
                    ->first();
            }

            return $student;
        }

        return null;
    }

    /**
     * Calculate grade letter based on mark
     */
    protected function calculateGrade(float $mark): string
    {
        $settings = SchoolSettings::getInstance();
        return $settings->getGradeLetter($mark);
    }

    /**
     * Send SMS notifications to parents for imported results
     */
    public function sendResultsNotifications(
        int $classSectionId,
        int $termId,
        int $year,
        string $examType = 'final'
    ): array {
        $classSection = ClassSection::with('grade')->find($classSectionId);
        $term = Term::find($termId);
        $settings = SchoolSettings::getInstance();

        if (!$classSection || !$term) {
            return [
                'success' => false,
                'message' => 'Invalid class section or term.',
                'sent' => 0,
                'failed' => 0,
            ];
        }

        // Get all students with their results and parent info
        $students = Student::with(['parentGuardian', 'results' => function ($query) use ($termId, $year, $examType) {
            $query->where('term', $termId)
                ->where('year', $year)
                ->where('exam_type', $examType)
                ->with('subject');
        }])
            ->where('class_section_id', $classSectionId)
            ->where('enrollment_status', 'active')
            ->get();

        // Calculate rankings
        $rankings = $this->calculateRankings($students);

        $recipients = [];
        $totalStudents = $students->count();

        foreach ($students as $student) {
            if (!$student->parentGuardian || empty($student->parentGuardian->phone)) {
                continue;
            }

            if ($student->results->isEmpty()) {
                continue;
            }

            // Build results string (subject:mark format)
            $resultsStr = $student->results->map(function ($result) {
                $subjectCode = $result->subject->code ?? substr($result->subject->name, 0, 3);
                return $subjectCode . ':' . round($result->marks);
            })->join(', ');

            // Get rank
            $rank = $rankings[$student->id] ?? 0;

            // Format exam type for SMS
            $examTypeLabel = match ($examType) {
                'final' => 'EOT',
                'mid-term' => 'MID',
                default => strtoupper(substr($examType, 0, 3)),
            };

            // Format term
            $termLabel = 'T' . $term->name;

            // Build grade + class name
            $gradeName = $classSection->grade->name ?? '';
            $className = $classSection->name ?? '';
            $fullClassName = trim($gradeName . $className);

            // Build SMS message
            // Format: Michael Johnson (Grade 7A): EOT T1 2025. Rank:#3/45. Math:78, Eng:82, Sci:88-St Francis of Assisi School
            $message = "{$student->name} ({$fullClassName}): {$examTypeLabel} {$termLabel} {$year}. Rank:#{$rank}/{$totalStudents}. {$resultsStr}-{$settings->school_name}";

            $recipients[] = [
                'phone' => $student->parentGuardian->phone,
                'student_id' => $student->id,
                'name' => $student->name,
            ];

            // Send individual SMS
            $this->smsService->send(
                $message,
                $student->parentGuardian->phone,
                'result_notification',
                $student->id
            );
        }

        return [
            'success' => true,
            'message' => 'SMS notifications sent.',
            'sent' => count($recipients),
            'total_students' => $totalStudents,
        ];
    }

    /**
     * Calculate rankings for students based on average marks
     */
    protected function calculateRankings(Collection $students): array
    {
        $averages = [];

        foreach ($students as $student) {
            if ($student->results->isEmpty()) {
                continue;
            }

            $total = $student->results->sum('marks');
            $count = $student->results->count();
            $average = $count > 0 ? $total / $count : 0;

            $averages[$student->id] = $average;
        }

        // Sort by average descending
        arsort($averages);

        // Assign ranks
        $rankings = [];
        $rank = 1;
        $prevAverage = null;
        $sameRankCount = 0;

        foreach ($averages as $studentId => $average) {
            if ($prevAverage !== null && $average < $prevAverage) {
                $rank += $sameRankCount;
                $sameRankCount = 1;
            } else {
                $sameRankCount++;
            }

            $rankings[$studentId] = $rank;
            $prevAverage = $average;
        }

        return $rankings;
    }

    /**
     * Generate sample CSV content for download
     */
    public static function generateSampleTemplate(int $classSectionId): string
    {
        $classSection = ClassSection::with('grade')->find($classSectionId);
        $gradeId = $classSection?->grade_id;

        // Get subjects for this grade
        $subjects = Subject::where('is_active', true)
            ->when($gradeId, function ($query) use ($gradeId) {
                $query->whereHas('grades', function ($q) use ($gradeId) {
                    $q->where('grades.id', $gradeId);
                });
            })
            ->orderBy('name')
            ->get();

        // Build header row
        $headers = ['Student Name', 'Student ID'];
        foreach ($subjects as $subject) {
            $headers[] = $subject->code ?? $subject->name;
        }

        // Get students for sample data
        $students = Student::where('class_section_id', $classSectionId)
            ->where('enrollment_status', 'active')
            ->orderBy('name')
            ->limit(5)
            ->get();

        // Build CSV content
        $csv = implode(',', $headers) . "\n";

        foreach ($students as $student) {
            $row = [$student->name, $student->student_id_number ?? ''];
            foreach ($subjects as $subject) {
                $row[] = ''; // Empty marks for template
            }
            $csv .= implode(',', $row) . "\n";
        }

        return $csv;
    }

    /**
     * Get import errors
     */
    public function getErrors(): array
    {
        return $this->importErrors;
    }

    /**
     * Get import successes
     */
    public function getSuccesses(): array
    {
        return $this->importSuccess;
    }
}
