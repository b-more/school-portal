<?php

namespace App\Services;

use App\Models\Teacher;
use App\Models\ClassSection;
use App\Models\Grade;
use App\Models\Subject;
use App\Models\AcademicYear;
use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;

class TeacherAssignmentValidator
{
    /**
     * Validate primary teacher assignment
     */
    public function validatePrimaryTeacherAssignment(array $data): array
    {
        $errors = [];

        if (!isset($data['primary_class_section_id'])) {
            $errors[] = 'Primary teachers must be assigned to a class section';
            return $errors;
        }

        $classSection = ClassSection::find($data['primary_class_section_id']);

        if (!$classSection) {
            $errors[] = 'Selected class section does not exist';
            return $errors;
        }

        // Check if class section is for primary grades
        $grade = $classSection->grade;
        if (!$grade) {
            $errors[] = 'Class section must belong to a valid grade';
            return $errors;
        }

        $primaryGrades = ['Baby Class', 'Middle Class', 'Reception', 'Grade 1', 'Grade 2', 'Grade 3', 'Grade 4', 'Grade 5', 'Grade 6', 'Grade 7'];

        if (!in_array($grade->name, $primaryGrades)) {
            $errors[] = 'Primary teachers can only be assigned to primary grades (Baby Class to Grade 7)';
        }

        // Check if class section already has a class teacher
        if ($classSection->class_teacher_id &&
            (!isset($data['teacher_id']) || $classSection->class_teacher_id != $data['teacher_id'])) {
            $existingTeacher = Teacher::find($classSection->class_teacher_id);
            $errors[] = 'Class section already has a class teacher: ' . ($existingTeacher->name ?? 'Unknown');
        }

        // Check if grade has subjects assigned
        $gradeSubjects = $grade->subjects()->where('is_active', true)->count();
        if ($gradeSubjects === 0) {
            $errors[] = "Grade {$grade->name} has no subjects assigned. Please assign subjects to this grade first.";
        }

        return $errors;
    }

    /**
     * Validate secondary teacher assignment
     */
    public function validateSecondaryTeacherAssignment(array $data): array
    {
        $errors = [];

        if (!isset($data['subject_classes']) || empty($data['subject_classes'])) {
            $errors[] = 'Secondary teachers must be assigned to at least one subject and class section';
            return $errors;
        }

        $assignedCombinations = [];

        foreach ($data['subject_classes'] as $index => $assignment) {
            $lineErrors = [];

            if (!isset($assignment['subject_id']) || !isset($assignment['class_section_id'])) {
                $lineErrors[] = "Assignment #" . ($index + 1) . ": Both subject and class section must be selected";
                continue;
            }

            $subject = Subject::find($assignment['subject_id']);
            $classSection = ClassSection::find($assignment['class_section_id']);

            if (!$subject) {
                $lineErrors[] = "Assignment #" . ($index + 1) . ": Selected subject does not exist";
            }

            if (!$classSection) {
                $lineErrors[] = "Assignment #" . ($index + 1) . ": Selected class section does not exist";
            }

            if (!empty($lineErrors)) {
                $errors = array_merge($errors, $lineErrors);
                continue;
            }

            // Check if it's a secondary grade
            $grade = $classSection->grade;
            if ($grade) {
                $secondaryGrades = ['Grade 8', 'Grade 9', 'Grade 10', 'Grade 11', 'Grade 12'];
                if (!in_array($grade->name, $secondaryGrades)) {
                    $errors[] = "Assignment #" . ($index + 1) . ": Secondary teachers can only be assigned to secondary grades (Grade 8-12)";
                }
            }

            // Check for duplicate assignments
            $combination = $assignment['subject_id'] . '-' . $assignment['class_section_id'];
            if (in_array($combination, $assignedCombinations)) {
                $errors[] = "Assignment #" . ($index + 1) . ": Duplicate assignment - same subject and class section selected multiple times";
            } else {
                $assignedCombinations[] = $combination;
            }

            // Check if subject is assigned to the grade
            if ($subject && $grade && !$grade->subjects()->where('subject_id', $subject->id)->exists()) {
                $errors[] = "Assignment #" . ($index + 1) . ": Subject '{$subject->name}' is not assigned to grade '{$grade->name}'";
            }
        }

        return $errors;
    }

    /**
     * Validate teacher data before saving
     */
    public function validateTeacherAssignment(array $data, ?int $teacherId = null): void
    {
        $errors = [];

        if (!isset($data['teacher_type'])) {
            throw ValidationException::withMessages(['teacher_type' => 'Teacher type must be specified']);
        }

        if ($data['teacher_type'] === 'primary') {
            $errors = $this->validatePrimaryTeacherAssignment($data);
        } elseif ($data['teacher_type'] === 'secondary') {
            $errors = $this->validateSecondaryTeacherAssignment($data);
        } else {
            $errors[] = 'Invalid teacher type specified';
        }

        if (!empty($errors)) {
            throw ValidationException::withMessages(['assignment' => $errors]);
        }
    }

    /**
     * Get assignment preview for a teacher
     */
    public function getAssignmentPreview(array $data): array
    {
        $preview = [
            'teacher_type' => $data['teacher_type'] ?? 'Unknown',
            'assignments' => [],
            'total_students' => 0,
            'warnings' => [],
        ];

        if ($data['teacher_type'] === 'primary' && isset($data['primary_class_section_id'])) {
            $classSection = ClassSection::with(['grade.subjects', 'students'])->find($data['primary_class_section_id']);

            if ($classSection) {
                $subjects = $classSection->grade->subjects()->where('is_active', true)->get();
                $studentCount = $classSection->students()->where('enrollment_status', 'active')->count();

                $preview['assignments'] = [
                    [
                        'class_section' => $classSection->grade->name . ' - ' . $classSection->name,
                        'subjects' => $subjects->pluck('name')->toArray(),
                        'student_count' => $studentCount,
                        'role' => 'Class Teacher (All Subjects)',
                    ]
                ];
                $preview['total_students'] = $studentCount;

                if ($subjects->count() === 0) {
                    $preview['warnings'][] = 'No subjects are assigned to this grade yet.';
                }
            }
        } elseif ($data['teacher_type'] === 'secondary' && isset($data['subject_classes'])) {
            $totalStudents = 0;
            $classSections = [];

            foreach ($data['subject_classes'] as $assignment) {
                $subject = Subject::find($assignment['subject_id']);
                $classSection = ClassSection::with(['grade', 'students'])->find($assignment['class_section_id']);

                if ($subject && $classSection) {
                    $studentCount = $classSection->students()->where('enrollment_status', 'active')->count();
                    $key = $classSection->id;

                    if (!isset($classSections[$key])) {
                        $classSections[$key] = [
                            'class_section' => $classSection->grade->name . ' - ' . $classSection->name,
                            'subjects' => [],
                            'student_count' => $studentCount,
                            'role' => 'Subject Teacher',
                        ];
                        $totalStudents += $studentCount;
                    }

                    $classSections[$key]['subjects'][] = $subject->name;
                }
            }

            $preview['assignments'] = array_values($classSections);
            $preview['total_students'] = $totalStudents;
        }

        return $preview;
    }

    /**
     * Check for assignment conflicts
     */
    public function checkAssignmentConflicts(array $data, ?int $teacherId = null): array
    {
        $conflicts = [];

        if ($data['teacher_type'] === 'primary' && isset($data['primary_class_section_id'])) {
            $classSection = ClassSection::find($data['primary_class_section_id']);

            if ($classSection && $classSection->class_teacher_id &&
                $classSection->class_teacher_id !== $teacherId) {
                $existingTeacher = Teacher::find($classSection->class_teacher_id);
                $conflicts[] = [
                    'type' => 'class_teacher_conflict',
                    'message' => "Class section already assigned to {$existingTeacher->name}",
                    'existing_teacher' => $existingTeacher->name,
                    'class_section' => $classSection->grade->name . ' - ' . $classSection->name,
                ];
            }
        }

        if ($data['teacher_type'] === 'secondary' && isset($data['subject_classes'])) {
            foreach ($data['subject_classes'] as $assignment) {
                // Check for conflicting subject assignments
                $existingAssignments = \App\Models\SubjectTeaching::where('subject_id', $assignment['subject_id'])
                    ->where('class_section_id', $assignment['class_section_id'])
                    ->where('teacher_id', '!=', $teacherId)
                    ->with('teacher')
                    ->get();

                foreach ($existingAssignments as $existing) {
                    $subject = Subject::find($assignment['subject_id']);
                    $classSection = ClassSection::with('grade')->find($assignment['class_section_id']);

                    $conflicts[] = [
                        'type' => 'subject_conflict',
                        'message' => "Subject {$subject->name} in {$classSection->grade->name} - {$classSection->name} already assigned to {$existing->teacher->name}",
                        'existing_teacher' => $existing->teacher->name,
                        'subject' => $subject->name,
                        'class_section' => $classSection->grade->name . ' - ' . $classSection->name,
                    ];
                }
            }
        }

        return $conflicts;
    }

    /**
     * Get validation summary for a teacher assignment
     */
    public function getValidationSummary(array $data, ?int $teacherId = null): array
    {
        $summary = [
            'is_valid' => true,
            'errors' => [],
            'warnings' => [],
            'conflicts' => [],
            'preview' => [],
        ];

        try {
            // Validate assignment
            $this->validateTeacherAssignment($data, $teacherId);

            // Get preview
            $summary['preview'] = $this->getAssignmentPreview($data);

            // Check conflicts
            $summary['conflicts'] = $this->checkAssignmentConflicts($data, $teacherId);

            if (!empty($summary['conflicts'])) {
                $summary['warnings'][] = 'Assignment conflicts detected. Please review before saving.';
            }

        } catch (ValidationException $e) {
            $summary['is_valid'] = false;
            $summary['errors'] = $e->errors()['assignment'] ?? ['Validation failed'];
        }

        return $summary;
    }
}
