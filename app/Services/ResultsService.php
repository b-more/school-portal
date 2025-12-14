<?php

namespace App\Services;

use App\Models\ClassSection;
use App\Models\Grade;
use App\Models\GradingScale;
use App\Models\GradingScaleItem;
use App\Models\Result;
use App\Models\Student;
use App\Models\Subject;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class ResultsService
{
    /**
     * Calculate grade from marks using the appropriate grading scale.
     *
     * @param float $marks The marks scored
     * @param Grade|int|null $grade The grade model or ID to determine grading scale
     * @param GradingScale|int|null $gradingScale Override with specific grading scale
     * @return array ['grade' => 'A', 'remark' => 'Excellent', 'grade_points' => 4.0]
     */
    public function calculateGradeFromMarks(float $marks, $grade = null, $gradingScale = null): array
    {
        // Get the grading scale
        $scale = $this->resolveGradingScale($grade, $gradingScale);

        if (!$scale) {
            return [
                'grade' => 'N/A',
                'remark' => '',
                'grade_points' => 0,
            ];
        }

        $item = $scale->calculateGrade($marks);

        if (!$item) {
            return [
                'grade' => 'N/A',
                'remark' => '',
                'grade_points' => 0,
            ];
        }

        return [
            'grade' => $item->grade,
            'remark' => $item->remark ?? '',
            'grade_points' => $item->grade_points,
        ];
    }

    /**
     * Resolve grading scale from various inputs.
     */
    protected function resolveGradingScale($grade = null, $gradingScale = null): ?GradingScale
    {
        // If specific grading scale provided, use it
        if ($gradingScale instanceof GradingScale) {
            return $gradingScale;
        }

        if (is_numeric($gradingScale)) {
            return GradingScale::find($gradingScale);
        }

        // Determine grade level from grade
        if ($grade instanceof Grade) {
            $gradeLevel = GradingScale::determineGradeLevelFromGrade($grade);
        } elseif (is_numeric($grade)) {
            $gradeModel = Grade::find($grade);
            $gradeLevel = $gradeModel ? GradingScale::determineGradeLevelFromGrade($gradeModel) : 'primary';
        } else {
            $gradeLevel = 'primary';
        }

        return GradingScale::getDefaultForGradeLevel($gradeLevel);
    }

    /**
     * Calculate student's average marks for a specific term and year.
     *
     * @param int $studentId
     * @param int $termId
     * @param int $year
     * @param string|null $examType Filter by specific exam type (mid_term, final, etc.)
     * @return array ['average' => 75.5, 'total_marks' => 755, 'subjects_count' => 10, 'max_marks' => 1000]
     */
    public function calculateStudentAverage(int $studentId, int $termId, int $year, ?string $examType = null): array
    {
        $query = Result::where('student_id', $studentId)
            ->where('term', $termId)
            ->where('year', $year);

        if ($examType) {
            $query->where('exam_type', $examType);
        }

        $results = $query->get();

        if ($results->isEmpty()) {
            return [
                'average' => 0,
                'total_marks' => 0,
                'subjects_count' => 0,
                'max_marks' => 0,
            ];
        }

        $totalMarks = $results->sum('marks');
        $subjectsCount = $results->count();
        $maxMarks = $subjectsCount * 100;

        return [
            'average' => $subjectsCount > 0 ? round($totalMarks / $subjectsCount, 2) : 0,
            'total_marks' => $totalMarks,
            'subjects_count' => $subjectsCount,
            'max_marks' => $maxMarks,
        ];
    }

    /**
     * Calculate combined average for mid-term and final exams.
     *
     * @param int $studentId
     * @param int $termId
     * @param int $year
     * @return array ['mid_term' => [], 'final' => [], 'combined' => [], 'subjects' => []]
     */
    public function calculateCombinedAverage(int $studentId, int $termId, int $year): array
    {
        $midTermResults = Result::where('student_id', $studentId)
            ->where('term', $termId)
            ->where('year', $year)
            ->where('exam_type', 'mid-term')
            ->get()
            ->keyBy('subject_id');

        $finalResults = Result::where('student_id', $studentId)
            ->where('term', $termId)
            ->where('year', $year)
            ->where('exam_type', 'final')
            ->get()
            ->keyBy('subject_id');

        // Get all unique subjects
        $subjectIds = $midTermResults->keys()->merge($finalResults->keys())->unique();
        $subjects = Subject::whereIn('id', $subjectIds)->get()->keyBy('id');

        $subjectDetails = [];
        $totalMidTerm = 0;
        $totalFinal = 0;
        $totalCombined = 0;
        $subjectsWithBoth = 0;

        foreach ($subjectIds as $subjectId) {
            $midTerm = $midTermResults->get($subjectId);
            $final = $finalResults->get($subjectId);
            $subject = $subjects->get($subjectId);

            $midTermMarks = $midTerm ? $midTerm->marks : null;
            $finalMarks = $final ? $final->marks : null;

            // Calculate combined average for this subject
            $combinedMarks = null;
            if ($midTermMarks !== null && $finalMarks !== null) {
                $combinedMarks = ($midTermMarks + $finalMarks) / 2;
                $subjectsWithBoth++;
            } elseif ($midTermMarks !== null) {
                $combinedMarks = $midTermMarks;
            } elseif ($finalMarks !== null) {
                $combinedMarks = $finalMarks;
            }

            if ($midTermMarks !== null) {
                $totalMidTerm += $midTermMarks;
            }
            if ($finalMarks !== null) {
                $totalFinal += $finalMarks;
            }
            if ($combinedMarks !== null) {
                $totalCombined += $combinedMarks;
            }

            $subjectDetails[] = [
                'subject_id' => $subjectId,
                'subject_name' => $subject ? $subject->name : 'Unknown',
                'mid_term' => $midTermMarks,
                'final' => $finalMarks,
                'combined' => $combinedMarks ? round($combinedMarks, 2) : null,
            ];
        }

        $midTermCount = $midTermResults->count();
        $finalCount = $finalResults->count();
        $totalSubjects = $subjectIds->count();

        return [
            'mid_term' => [
                'average' => $midTermCount > 0 ? round($totalMidTerm / $midTermCount, 2) : 0,
                'total' => $totalMidTerm,
                'subjects_count' => $midTermCount,
            ],
            'final' => [
                'average' => $finalCount > 0 ? round($totalFinal / $finalCount, 2) : 0,
                'total' => $totalFinal,
                'subjects_count' => $finalCount,
            ],
            'combined' => [
                'average' => $totalSubjects > 0 ? round($totalCombined / $totalSubjects, 2) : 0,
                'total' => round($totalCombined, 2),
                'subjects_count' => $totalSubjects,
            ],
            'subjects' => $subjectDetails,
        ];
    }

    /**
     * Get class rankings for all students in a class section.
     *
     * @param int $classSectionId
     * @param int $termId
     * @param int $year
     * @param string|null $examType Filter by exam type
     * @return Collection
     */
    public function getClassRankings(int $classSectionId, int $termId, int $year, ?string $examType = null): Collection
    {
        $students = Student::where('class_section_id', $classSectionId)
            ->where('enrollment_status', 'active')
            ->get();

        $rankings = collect();

        foreach ($students as $student) {
            if ($examType) {
                $avgData = $this->calculateStudentAverage($student->id, $termId, $year, $examType);
            } else {
                // Use combined average for overall rankings
                $combinedData = $this->calculateCombinedAverage($student->id, $termId, $year);
                $avgData = [
                    'average' => $combinedData['combined']['average'],
                    'total_marks' => $combinedData['combined']['total'],
                    'subjects_count' => $combinedData['combined']['subjects_count'],
                ];
            }

            if ($avgData['subjects_count'] > 0) {
                $rankings->push([
                    'student_id' => $student->id,
                    'student_name' => $student->name,
                    'student_id_number' => $student->student_id_number,
                    'average' => $avgData['average'],
                    'total_marks' => $avgData['total_marks'],
                    'subjects_count' => $avgData['subjects_count'],
                ]);
            }
        }

        // Sort by average descending and assign positions
        $rankings = $rankings->sortByDesc('average')->values();

        $position = 0;
        $lastAverage = null;
        $samePositionCount = 0;

        return $rankings->map(function ($item, $index) use (&$position, &$lastAverage, &$samePositionCount) {
            if ($item['average'] !== $lastAverage) {
                $position = $index + 1;
                $samePositionCount = 1;
            } else {
                $samePositionCount++;
            }

            $lastAverage = $item['average'];
            $item['position'] = $position;
            $item['total_students'] = $this->getLastTotalCount();

            return $item;
        });
    }

    protected $lastTotalCount = 0;

    protected function getLastTotalCount(): int
    {
        return $this->lastTotalCount;
    }

    /**
     * Get class position for a specific student.
     *
     * @param int $studentId
     * @param int $classSectionId
     * @param int $termId
     * @param int $year
     * @return array ['position' => 5, 'total' => 32]
     */
    public function getStudentClassPosition(int $studentId, int $classSectionId, int $termId, int $year): array
    {
        $rankings = $this->getClassRankings($classSectionId, $termId, $year);

        $this->lastTotalCount = $rankings->count();

        $studentRanking = $rankings->firstWhere('student_id', $studentId);

        if (!$studentRanking) {
            return [
                'position' => null,
                'total' => $rankings->count(),
            ];
        }

        return [
            'position' => $studentRanking['position'],
            'total' => $rankings->count(),
        ];
    }

    /**
     * Get top performers for a specific subject.
     *
     * @param int $subjectId
     * @param int $classSectionId
     * @param int $termId
     * @param int $year
     * @param int $limit
     * @return Collection
     */
    public function getSubjectTopPerformers(int $subjectId, int $classSectionId, int $termId, int $year, int $limit = 5): Collection
    {
        $students = Student::where('class_section_id', $classSectionId)
            ->where('enrollment_status', 'active')
            ->pluck('id');

        return Result::whereIn('student_id', $students)
            ->where('subject_id', $subjectId)
            ->where('term', $termId)
            ->where('year', $year)
            ->whereIn('exam_type', ['mid-term', 'final'])
            ->select('student_id', DB::raw('AVG(marks) as average_marks'))
            ->groupBy('student_id')
            ->orderByDesc('average_marks')
            ->limit($limit)
            ->get()
            ->map(function ($result) {
                $student = Student::find($result->student_id);
                return [
                    'student_id' => $result->student_id,
                    'student_name' => $student ? $student->name : 'Unknown',
                    'average_marks' => round($result->average_marks, 2),
                ];
            });
    }

    /**
     * Get subject-wise averages for a class section.
     *
     * @param int $classSectionId
     * @param int $termId
     * @param int $year
     * @return Collection
     */
    public function getSubjectAverages(int $classSectionId, int $termId, int $year): Collection
    {
        $students = Student::where('class_section_id', $classSectionId)
            ->where('enrollment_status', 'active')
            ->pluck('id');

        return Result::whereIn('student_id', $students)
            ->where('term', $termId)
            ->where('year', $year)
            ->whereIn('exam_type', ['mid-term', 'final'])
            ->select('subject_id', DB::raw('AVG(marks) as average_marks'), DB::raw('COUNT(DISTINCT student_id) as student_count'))
            ->groupBy('subject_id')
            ->orderByDesc('average_marks')
            ->get()
            ->map(function ($result) {
                $subject = Subject::find($result->subject_id);
                return [
                    'subject_id' => $result->subject_id,
                    'subject_name' => $subject ? $subject->name : 'Unknown',
                    'average_marks' => round($result->average_marks, 2),
                    'student_count' => $result->student_count,
                ];
            });
    }

    /**
     * Get grade distribution for a class section.
     *
     * @param int $classSectionId
     * @param int $termId
     * @param int $year
     * @return array ['A' => 10, 'B' => 15, 'C' => 8, ...]
     */
    public function getGradeDistribution(int $classSectionId, int $termId, int $year): array
    {
        $students = Student::where('class_section_id', $classSectionId)
            ->where('enrollment_status', 'active')
            ->pluck('id');

        return Result::whereIn('student_id', $students)
            ->where('term', $termId)
            ->where('year', $year)
            ->whereIn('exam_type', ['mid-term', 'final'])
            ->select('grade', DB::raw('COUNT(*) as count'))
            ->groupBy('grade')
            ->orderBy('grade')
            ->pluck('count', 'grade')
            ->toArray();
    }

    /**
     * Save bulk results for multiple students.
     *
     * @param array $resultsData Array of result data
     * @param int|null $recordedById Teacher ID who recorded (nullable for admin users)
     * @return array ['saved' => 10, 'errors' => []]
     */
    public function saveBulkResults(array $resultsData, ?int $recordedById): array
    {
        $saved = 0;
        $errors = [];

        DB::beginTransaction();

        try {
            foreach ($resultsData as $index => $data) {
                // Validate required fields
                if (empty($data['student_id']) || empty($data['subject_id']) || !isset($data['marks'])) {
                    $errors[] = "Row {$index}: Missing required fields";
                    continue;
                }

                // Calculate grade if not provided
                if (empty($data['grade'])) {
                    $student = Student::with('grade')->find($data['student_id']);
                    $gradeData = $this->calculateGradeFromMarks(
                        (float) $data['marks'],
                        $student ? $student->grade : null
                    );
                    $data['grade'] = $gradeData['grade'];
                }

                // Check for existing result
                $existingResult = Result::where('student_id', $data['student_id'])
                    ->where('subject_id', $data['subject_id'])
                    ->where('exam_type', $data['exam_type'] ?? 'final')
                    ->where('term', $data['term'])
                    ->where('year', $data['year'])
                    ->first();

                if ($existingResult) {
                    // Update existing
                    $existingResult->update([
                        'marks' => $data['marks'],
                        'grade' => $data['grade'],
                        'comment' => $data['comment'] ?? null,
                        'recorded_by' => $recordedById,
                    ]);
                } else {
                    // Create new
                    Result::create([
                        'student_id' => $data['student_id'],
                        'subject_id' => $data['subject_id'],
                        'exam_type' => $data['exam_type'] ?? 'final',
                        'marks' => $data['marks'],
                        'grade' => $data['grade'],
                        'term' => $data['term'],
                        'year' => $data['year'],
                        'comment' => $data['comment'] ?? null,
                        'recorded_by' => $recordedById,
                        'notify_parent' => false, // Bulk entry doesn't auto-notify
                    ]);
                }

                $saved++;
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            $errors[] = "Database error: " . $e->getMessage();
        }

        return [
            'saved' => $saved,
            'errors' => $errors,
        ];
    }

    /**
     * Get all results for a student in a term (for report card).
     *
     * @param int $studentId
     * @param int $termId
     * @param int $year
     * @return array
     */
    public function getStudentTermResults(int $studentId, int $termId, int $year): array
    {
        $student = Student::with(['classSection.grade', 'classSection.classTeacher', 'parentGuardian'])
            ->find($studentId);

        if (!$student) {
            return [];
        }

        $combinedData = $this->calculateCombinedAverage($studentId, $termId, $year);

        // Get grading scale for this student's grade level
        $gradingScale = null;
        if ($student->classSection && $student->classSection->grade) {
            $gradeLevel = GradingScale::determineGradeLevelFromGrade($student->classSection->grade);
            $gradingScale = GradingScale::getDefaultForGradeLevel($gradeLevel);
        }

        // Calculate grade for combined average
        $overallGrade = null;
        if ($gradingScale && $combinedData['combined']['average'] > 0) {
            $overallGrade = $this->calculateGradeFromMarks(
                $combinedData['combined']['average'],
                $student->classSection->grade ?? null
            );
        }

        // Get class position
        $position = null;
        if ($student->class_section_id) {
            $position = $this->getStudentClassPosition(
                $studentId,
                $student->class_section_id,
                $termId,
                $year
            );
        }

        // Add grades to each subject
        $subjectsWithGrades = collect($combinedData['subjects'])->map(function ($subject) use ($gradingScale, $student) {
            if ($subject['combined'] !== null && $gradingScale) {
                $gradeData = $this->calculateGradeFromMarks($subject['combined'], $student->classSection->grade ?? null);
                $subject['grade'] = $gradeData['grade'];
                $subject['remark'] = $gradeData['remark'];
            } else {
                $subject['grade'] = 'N/A';
                $subject['remark'] = '';
            }
            return $subject;
        })->toArray();

        return [
            'student' => $student,
            'subjects' => $subjectsWithGrades,
            'mid_term' => $combinedData['mid_term'],
            'final' => $combinedData['final'],
            'combined' => $combinedData['combined'],
            'overall_grade' => $overallGrade,
            'position' => $position,
            'grading_scale' => $gradingScale,
        ];
    }
}
