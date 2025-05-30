<?php

namespace App\Traits;

use App\Models\Teacher;
use App\Models\Student;
use App\Models\AcademicYear;
use App\Constants\RoleConstants;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

trait HasTeacherAccess
{
    /**
     * Get the current teacher from the authenticated user
     */
    protected function getCurrentTeacher(): ?Teacher
    {
        $user = Auth::user();

        if (!$user || $user->role_id !== RoleConstants::TEACHER) {
            return null;
        }

        return Teacher::where('user_id', $user->id)->first();
    }

    /**
     * Filter students query to only show students accessible to the current teacher
     */
    protected function filterStudentsForTeacher(Builder $query, ?Teacher $teacher = null): Builder
    {
        $teacher = $teacher ?? $this->getCurrentTeacher();

        if (!$teacher) {
            return $query;
        }

        // Get current academic year
        $currentYear = AcademicYear::where('is_active', true)->first();
        $academicYearId = $currentYear ? $currentYear->id : null;

        // Get class section IDs this teacher is assigned to
        $assignedClassSectionIds = $teacher->subjectTeachings()
            ->when($academicYearId, function ($q) use ($academicYearId) {
                return $q->where('academic_year_id', $academicYearId);
            })
            ->pluck('class_section_id')
            ->unique()
            ->toArray();

        // Include class teacher assignment
        if ($teacher->is_class_teacher && $teacher->class_section_id) {
            $assignedClassSectionIds[] = $teacher->class_section_id;
            $assignedClassSectionIds = array_unique($assignedClassSectionIds);
        }

        // Filter students to only those in assigned class sections
        return $query->whereIn('class_section_id', $assignedClassSectionIds)
                    ->where('enrollment_status', 'active');
    }

    /**
     * Filter homework query to only show homework for classes the teacher is assigned to
     */
    protected function filterHomeworkForTeacher(Builder $query, ?Teacher $teacher = null): Builder
    {
        $teacher = $teacher ?? $this->getCurrentTeacher();

        if (!$teacher) {
            return $query;
        }

        // Teachers can see homework they assigned or homework for their assigned grades
        return $query->where(function ($q) use ($teacher) {
            // Homework assigned by this teacher
            $q->where('assigned_by', $teacher->id)
              // Or homework for grades where teacher has class sections
              ->orWhereHas('grade', function ($gradeQuery) use ($teacher) {
                  $gradeQuery->whereHas('classSections', function ($classQuery) use ($teacher) {
                      $classQuery->whereHas('subjectTeachings', function ($teachingQuery) use ($teacher) {
                          $teachingQuery->where('teacher_id', $teacher->id);
                      });
                  });
              });
        });
    }

    /**
     * Filter results/grades to only show for students the teacher has access to
     */
    protected function filterResultsForTeacher(Builder $query, ?Teacher $teacher = null): Builder
    {
        $teacher = $teacher ?? $this->getCurrentTeacher();

        if (!$teacher) {
            return $query;
        }

        // Get accessible student IDs
        $accessibleStudentIds = $teacher->getAccessibleStudents()->pluck('id')->toArray();

        return $query->whereIn('student_id', $accessibleStudentIds);
    }

    /**
     * Get class sections accessible to the teacher
     */
    protected function getAccessibleClassSections(?Teacher $teacher = null): array
    {
        $teacher = $teacher ?? $this->getCurrentTeacher();

        if (!$teacher) {
            return [];
        }

        $currentYear = AcademicYear::where('is_active', true)->first();
        $academicYearId = $currentYear ? $currentYear->id : null;

        $classSectionIds = $teacher->subjectTeachings()
            ->when($academicYearId, function ($q) use ($academicYearId) {
                return $q->where('academic_year_id', $academicYearId);
            })
            ->pluck('class_section_id')
            ->unique()
            ->toArray();

        // Include class teacher assignment
        if ($teacher->is_class_teacher && $teacher->class_section_id) {
            $classSectionIds[] = $teacher->class_section_id;
            $classSectionIds = array_unique($classSectionIds);
        }

        return $classSectionIds;
    }

    /**
     * Get subjects accessible to the teacher
     */
    protected function getAccessibleSubjects(?Teacher $teacher = null): array
    {
        $teacher = $teacher ?? $this->getCurrentTeacher();

        if (!$teacher) {
            return [];
        }

        return $teacher->subjects()->pluck('id')->toArray();
    }

    /**
     * Check if teacher can access a specific student
     */
    protected function canAccessStudent(int $studentId, ?Teacher $teacher = null): bool
    {
        $teacher = $teacher ?? $this->getCurrentTeacher();

        if (!$teacher) {
            return false;
        }

        $student = Student::find($studentId);

        if (!$student) {
            return false;
        }

        return $teacher->canAccessStudent($student);
    }

    /**
     * Get teaching summary for the current teacher
     */
    protected function getTeachingSummary(?Teacher $teacher = null): array
    {
        $teacher = $teacher ?? $this->getCurrentTeacher();

        if (!$teacher) {
            return [
                'teacher_type' => 'Unknown',
                'total_students' => 0,
                'total_subjects' => 0,
                'total_class_sections' => 0,
                'assignments' => collect(),
                'is_class_teacher' => false,
                'is_grade_teacher' => false,
            ];
        }

        return $teacher->getTeachingSummary();
    }

    /**
     * Apply teacher access filters to an Eloquent query based on the model type
     */
    protected function applyTeacherAccessFilter(Builder $query, string $modelType, ?Teacher $teacher = null): Builder
    {
        $teacher = $teacher ?? $this->getCurrentTeacher();

        if (!$teacher) {
            return $query;
        }

        return match($modelType) {
            'student', 'students' => $this->filterStudentsForTeacher($query, $teacher),
            'homework' => $this->filterHomeworkForTeacher($query, $teacher),
            'result', 'results' => $this->filterResultsForTeacher($query, $teacher),
            default => $query,
        };
    }

    /**
     * Get filtered options for form components based on teacher access
     */
    protected function getTeacherFilteredOptions(string $type, ?Teacher $teacher = null): array
    {
        $teacher = $teacher ?? $this->getCurrentTeacher();

        if (!$teacher) {
            return [];
        }

        return match($type) {
            'students' => $teacher->getAccessibleStudents()->pluck('name', 'id')->toArray(),
            'subjects' => $teacher->subjects()->pluck('name', 'id')->toArray(),
            'class_sections' => $teacher->classSections()->with('grade')->get()
                ->mapWithKeys(function ($section) {
                    return [$section->id => "{$section->grade->name} - {$section->name}"];
                })->toArray(),
            default => [],
        };
    }
}
