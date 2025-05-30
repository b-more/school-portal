<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Collection;

class Teacher extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'name',
        'role_id',
        'is_grade_teacher',
        'employee_id',
        'qualification',
        'specialization',
        'join_date',
        'phone',
        'email',
        'address',
        'is_active',
        'is_class_teacher',
        'class_section_id',
        'grade_id', // Added this field
        'profile_photo',
        'biography',
    ];

    protected $casts = [
        'join_date' => 'date',
        'is_active' => 'boolean',
        'is_class_teacher' => 'boolean',
        'is_grade_teacher' => 'boolean',
    ];

    protected $with = ['grade', 'classSection']; // Eager load relationships

    // ============================================
    // CORE RELATIONSHIPS
    // ============================================

    /**
     * Get the user that owns the teacher
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the role of the teacher
     */
    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class);
    }

    /**
     * Get the assigned grade for this teacher
     */
    public function grade(): BelongsTo
    {
        return $this->belongsTo(Grade::class, 'grade_id');
    }

    /**
     * Get the class section if this teacher is a class teacher
     */
    public function classSection(): BelongsTo
    {
        return $this->belongsTo(ClassSection::class, 'class_section_id');
    }

    /**
     * Get the employee record for this teacher
     */
    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'employee_id');
    }

    // ============================================
    // SUBJECT & TEACHING RELATIONSHIPS
    // ============================================

    /**
     * Get all subject teachings for this teacher
     */
    public function subjectTeachings(): HasMany
    {
        return $this->hasMany(SubjectTeaching::class);
    }

    /**
     * Get all subjects taught by this teacher
     */
    public function subjects(): BelongsToMany
    {
        return $this->belongsToMany(Subject::class, 'subject_teachings')
            ->withPivot('class_section_id', 'academic_year_id')
            ->withTimestamps();
    }

    /**
     * Get all class sections where this teacher teaches
     */
    public function classSections(): BelongsToMany
    {
        return $this->belongsToMany(ClassSection::class, 'subject_teachings')
            ->withPivot('subject_id', 'academic_year_id')
            ->withTimestamps();
    }

    // ============================================
    // SCHOOL CLASS RELATIONSHIPS
    // ============================================

    /**
     * Get all school classes this teacher is assigned to
     */
    public function schoolClasses(): BelongsToMany
    {
        return $this->belongsToMany(SchoolClass::class, 'class_teacher', 'teacher_id', 'class_id')
            ->withPivot('role', 'is_primary')
            ->withTimestamps();
    }

    /**
     * Get all school classes where this teacher teaches specific subjects
     */
    public function subjectClasses(): BelongsToMany
    {
        return $this->belongsToMany(SchoolClass::class, 'class_subject_teacher', 'teacher_id', 'class_id')
            ->withPivot('subject_id')
            ->withTimestamps();
    }

    /**
     * Get subjects taught by this teacher in specific classes
     */
    public function classSubjects(): BelongsToMany
    {
        return $this->belongsToMany(Subject::class, 'class_subject_teacher', 'teacher_id', 'subject_id')
            ->withPivot('class_id')
            ->withTimestamps();
    }

    // ============================================
    // OTHER RELATIONSHIPS
    // ============================================

    /**
     * Get homework assigned by this teacher
     */
    public function homework(): HasMany
    {
        return $this->hasMany(Homework::class, 'assigned_by');
    }

    /**
     * Get all exams this teacher is responsible for
     */
    public function exams(): HasMany
    {
        return $this->hasMany(Exam::class, 'teacher_id');
    }

    /**
     * Get all assessments this teacher is responsible for
     */
    public function assessments(): HasMany
    {
        return $this->hasMany(Assessment::class, 'teacher_id');
    }

    /**
     * Get all results this teacher has graded
     */
    public function gradedResults(): HasMany
    {
        return $this->hasMany(Result::class, 'graded_by');
    }

    // ============================================
    // SCOPES
    // ============================================

    /**
     * Scope for active teachers
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope for class teachers
     */
    public function scopeClassTeachers($query)
    {
        return $query->where('is_class_teacher', true);
    }

    /**
     * Scope for grade teachers
     */
    public function scopeGradeTeachers($query)
    {
        return $query->where('is_grade_teacher', true);
    }

    /**
     * Scope for primary teachers
     */
    public function scopePrimary($query)
    {
        return $query->whereNull('specialization');
    }

    /**
     * Scope for secondary teachers
     */
    public function scopeSecondary($query)
    {
        return $query->whereNotNull('specialization');
    }

    // ============================================
    // HELPER METHODS
    // ============================================

    /**
     * Check if teacher is a primary teacher (no specialization)
     */
    public function isPrimaryTeacher(): bool
    {
        return empty($this->specialization);
    }

    /**
     * Check if teacher is a secondary teacher (has specialization)
     */
    public function isSecondaryTeacher(): bool
    {
        return !empty($this->specialization);
    }

    /**
     * Get full name with employee ID
     */
    public function getFullNameAttribute()
    {
        $name = $this->name;
        if ($this->employee_id) {
            $name .= " ({$this->employee_id})";
        }
        return $name;
    }

    /**
     * Get teacher's department based on assigned grade
     */
    public function getDepartmentAttribute()
    {
        if ($this->grade) {
            if (in_array($this->grade->name, ['Baby Class', 'Middle Class', 'Reception'])) {
                return 'ECL';
            } elseif (in_array($this->grade->name, ['Grade 1', 'Grade 2', 'Grade 3', 'Grade 4', 'Grade 5', 'Grade 6', 'Grade 7'])) {
                return 'Primary';
            } else {
                return 'Secondary';
            }
        }

        // Fallback to employee department or specialization
        if ($this->employee) {
            return $this->employee->department ?? 'N/A';
        }

        return $this->isSecondaryTeacher() ? 'Secondary' : 'Primary';
    }

    // ============================================
    // SCHOOL CLASS METHODS
    // ============================================

    /**
     * Check if teacher is assigned to a specific school class
     */
    public function isAssignedToClass(SchoolClass $class): bool
    {
        return $this->schoolClasses()->where('classes.id', $class->id)->exists();
    }

    /**
     * Check if teacher teaches a specific subject in a specific class
     */
    public function teachesSubjectInSchoolClass($subjectId, $classId): bool
    {
        return DB::table('class_subject_teacher')
            ->where('teacher_id', $this->id)
            ->where('subject_id', $subjectId)
            ->where('class_id', $classId)
            ->exists();
    }

    /**
     * Get teacher's role in a specific class
     */
    public function getRoleInClass(SchoolClass $class): ?string
    {
        $assignment = $this->schoolClasses()
            ->where('classes.id', $class->id)
            ->first();

        return $assignment?->pivot->role;
    }

    /**
     * Check if teacher is primary teacher for a specific class
     */
    public function isPrimaryTeacherForClass(SchoolClass $class): bool
    {
        $assignment = $this->schoolClasses()
            ->where('classes.id', $class->id)
            ->first();

        return $assignment?->pivot->is_primary ?? false;
    }

    /**
     * Get all subjects this teacher teaches in a specific class
     */
    public function getSubjectsInClass(SchoolClass $class): Collection
    {
        $subjectIds = DB::table('class_subject_teacher')
            ->where('teacher_id', $this->id)
            ->where('class_id', $class->id)
            ->pluck('subject_id');

        return Subject::whereIn('id', $subjectIds)->get();
    }

    /**
     * Get teaching workload summary for school classes
     */
    public function getSchoolClassWorkload(): array
    {
        $classAssignments = $this->schoolClasses()->get();
        $subjectAssignments = DB::table('class_subject_teacher')
            ->where('teacher_id', $this->id)
            ->count();

        return [
            'total_classes' => $classAssignments->count(),
            'class_teacher_assignments' => $classAssignments->where('pivot.role', 'class_teacher')->count(),
            'subject_teacher_assignments' => $classAssignments->where('pivot.role', 'subject_teacher')->count(),
            'primary_teacher_assignments' => $classAssignments->where('pivot.is_primary', true)->count(),
            'total_subject_assignments' => $subjectAssignments,
        ];
    }

    // ============================================
    // SUBJECT TEACHING METHODS
    // ============================================

    /**
     * Get subjects for a specific academic year
     */
    public function getSubjectsForYear($academicYearId)
    {
        return $this->subjects()
            ->wherePivot('academic_year_id', $academicYearId)
            ->get();
    }

    /**
     * Get class sections for a specific subject and academic year
     */
    public function getClassSectionsForSubject($subjectId, $academicYearId)
    {
        return $this->classSections()
            ->wherePivot('subject_id', $subjectId)
            ->wherePivot('academic_year_id', $academicYearId)
            ->get();
    }

    /**
     * Check if teacher teaches a specific subject in a specific class section
     */
    public function teachesSubjectInClass($subjectId, $classSectionId, $academicYearId = null)
    {
        $query = $this->subjectTeachings()
            ->where('subject_id', $subjectId)
            ->where('class_section_id', $classSectionId);

        if ($academicYearId) {
            $query->where('academic_year_id', $academicYearId);
        }

        return $query->exists();
    }

    /**
     * Check if teacher teaches in a specific class section
     */
    public function teachesInClassSection($classSectionId)
    {
        return $this->subjectTeachings()
            ->where('class_section_id', $classSectionId)
            ->exists();
    }

    // ============================================
    // STUDENT ACCESS METHODS
    // ============================================

    /**
     * Get all students in classes taught by this teacher
     */
    public function students()
    {
        return Student::whereIn('class_section_id',
            $this->classSections()->pluck('class_sections.id')
        );
    }

    /**
     * Get all students currently taught by this teacher
     */
    public function getCurrentStudents($academicYearId = null)
    {
        $query = Student::query();

        if ($this->is_class_teacher && $this->class_section_id) {
            // If teacher is a class teacher, get students from their assigned class
            $query->where('class_section_id', $this->class_section_id);
        } else {
            // Get students from all classes where this teacher teaches
            $query->whereIn('class_section_id',
                $this->classSections()->pluck('class_sections.id')
            );
        }

        if ($academicYearId) {
            $query->whereHas('classSection.subjectTeachings', function ($q) use ($academicYearId) {
                $q->where('teacher_id', $this->id)
                  ->where('academic_year_id', $academicYearId);
            });
        }

        return $query->get();
    }

    /**
     * Get all students this teacher has access to based on their assignments.
     * Teachers can only see students from class sections they are assigned to teach.
     */
    public function getAccessibleStudents($academicYearId = null)
    {
        // Get current academic year if not provided
        if (!$academicYearId) {
            $currentYear = AcademicYear::where('is_active', true)->first();
            $academicYearId = $currentYear ? $currentYear->id : null;
        }

        // Get all class section IDs this teacher is assigned to
        $assignedClassSectionIds = $this->subjectTeachings()
            ->when($academicYearId, function ($query) use ($academicYearId) {
                return $query->where('academic_year_id', $academicYearId);
            })
            ->pluck('class_section_id')
            ->unique()
            ->toArray();

        // If teacher is a class teacher, also include their assigned class section
        if ($this->is_class_teacher && $this->class_section_id) {
            $assignedClassSectionIds[] = $this->class_section_id;
            $assignedClassSectionIds = array_unique($assignedClassSectionIds);
        }

        // Return students from assigned class sections only
        return Student::whereIn('class_section_id', $assignedClassSectionIds)
            ->where('enrollment_status', 'active')
            ->with(['classSection.grade', 'parentGuardian']);
    }

    /**
     * Get students for a specific subject taught by this teacher
     */
    public function getStudentsForSubject($subjectId, $academicYearId = null)
    {
        // Get current academic year if not provided
        if (!$academicYearId) {
            $currentYear = AcademicYear::where('is_active', true)->first();
            $academicYearId = $currentYear ? $currentYear->id : null;
        }

        // Get class sections where this teacher teaches the specific subject
        $classSectionIds = $this->subjectTeachings()
            ->where('subject_id', $subjectId)
            ->when($academicYearId, function ($query) use ($academicYearId) {
                return $query->where('academic_year_id', $academicYearId);
            })
            ->pluck('class_section_id')
            ->toArray();

        return Student::whereIn('class_section_id', $classSectionIds)
            ->where('enrollment_status', 'active')
            ->with(['classSection.grade', 'parentGuardian']);
    }

    /**
     * Check if teacher has access to a specific student
     */
    public function canAccessStudent(Student $student, $academicYearId = null): bool
    {
        // Get current academic year if not provided
        if (!$academicYearId) {
            $currentYear = AcademicYear::where('is_active', true)->first();
            $academicYearId = $currentYear ? $currentYear->id : null;
        }

        // Check if teacher is assigned to the student's class section
        $hasAccess = $this->subjectTeachings()
            ->where('class_section_id', $student->class_section_id)
            ->when($academicYearId, function ($query) use ($academicYearId) {
                return $query->where('academic_year_id', $academicYearId);
            })
            ->exists();

        // Also check if teacher is the class teacher for this student's section
        if (!$hasAccess && $this->is_class_teacher && $this->class_section_id === $student->class_section_id) {
            $hasAccess = true;
        }

        return $hasAccess;
    }

    /**
     * Get subjects this teacher can teach to a specific class section
     */
    public function getSubjectsForClassSection($classSectionId, $academicYearId = null)
    {
        // Get current academic year if not provided
        if (!$academicYearId) {
            $currentYear = AcademicYear::where('is_active', true)->first();
            $academicYearId = $currentYear ? $currentYear->id : null;
        }

        return $this->subjects()
            ->whereHas('subjectTeachings', function ($query) use ($classSectionId, $academicYearId) {
                $query->where('teacher_id', $this->id)
                      ->where('class_section_id', $classSectionId)
                      ->when($academicYearId, function ($q) use ($academicYearId) {
                          return $q->where('academic_year_id', $academicYearId);
                      });
            })
            ->get();
    }

    /**
     * Get teaching assignments grouped by class section
     */
    public function getTeachingAssignmentsByClass($academicYearId = null)
    {
        // Get current academic year if not provided
        if (!$academicYearId) {
            $currentYear = AcademicYear::where('is_active', true)->first();
            $academicYearId = $currentYear ? $currentYear->id : null;
        }

        return $this->subjectTeachings()
            ->with(['subject', 'classSection.grade'])
            ->when($academicYearId, function ($query) use ($academicYearId) {
                return $query->where('academic_year_id', $academicYearId);
            })
            ->get()
            ->groupBy('class_section_id')
            ->map(function ($teachings) {
                $classSection = $teachings->first()->classSection;
                return [
                    'class_section' => $classSection,
                    'subjects' => $teachings->pluck('subject'),
                    'student_count' => $classSection->students()->where('enrollment_status', 'active')->count(),
                ];
            });
    }

    // ============================================
    // ASSIGNMENT & RESPONSIBILITY METHODS
    // ============================================

    /**
     * Get teaching schedule for this teacher
     */
    public function getTeachingSchedule($academicYearId = null)
    {
        $query = $this->subjectTeachings()
            ->with(['subject', 'classSection.grade', 'academicYear']);

        if ($academicYearId) {
            $query->where('academic_year_id', $academicYearId);
        }

        return $query->get()->groupBy('academic_year_id');
    }

    /**
     * Get class for which this teacher is the class teacher
     */
    public function assignedClass()
    {
        if ($this->is_class_teacher && $this->class_section_id) {
            return $this->classSection;
        }
        return null;
    }

    /**
     * Get grade for which this teacher is the grade teacher
     */
    public function assignedGrade()
    {
        if ($this->is_grade_teacher && $this->grade_id) {
            return $this->grade;
        }
        return null;
    }

    /**
     * Check if teacher has responsibility for a specific student
     */
    public function hasResponsibilityFor(Student $student)
    {
        // Check if teacher is the class teacher for this student
        if ($this->is_class_teacher && $this->class_section_id === $student->class_section_id) {
            return true;
        }

        // Check if teacher is the grade teacher for this student's grade
        if ($this->is_grade_teacher && $this->grade_id === $student->grade_id) {
            return true;
        }

        // Check if teacher teaches any subject in this student's class
        return $this->teachesInClassSection($student->class_section_id);
    }

    // ============================================
    // STATISTICS & SUMMARIES
    // ============================================

    /**
     * Get all homework statistics for this teacher
     */
    public function getHomeworkStats()
    {
        $homework = $this->homework;

        return [
            'total' => $homework->count(),
            'active' => $homework->where('status', 'active')->count(),
            'completed' => $homework->where('status', 'completed')->count(),
            'with_submissions' => $homework->filter(function ($hw) {
                return $hw->submissions()->exists();
            })->count(),
        ];
    }

    /**
     * Get teaching summary for dashboard/reports
     */
    public function getTeachingSummary($academicYearId = null)
    {
        $assignments = $this->getTeachingAssignmentsByClass($academicYearId);
        $totalStudents = $this->getAccessibleStudents($academicYearId)->count();
        $subjects = $this->subjects()->count();
        $classSections = $assignments->count();

        return [
            'teacher_type' => $this->isPrimaryTeacher() ? 'Primary' : 'Secondary',
            'total_students' => $totalStudents,
            'total_subjects' => $subjects,
            'total_class_sections' => $classSections,
            'assignments' => $assignments,
            'is_class_teacher' => $this->is_class_teacher,
            'is_grade_teacher' => $this->is_grade_teacher,
        ];
    }

    // ============================================
    // MUTATORS & ACCESSORS
    // ============================================

    /**
     * Get the teacher's display name with type
     */
    public function getDisplayNameAttribute(): string
    {
        $type = $this->isPrimaryTeacher() ? 'Primary' : 'Secondary';
        return "{$this->name} ({$this->employee_id}) - {$type}";
    }

    /**
     * Get the teacher's assigned subjects as a string
     */
    public function getSubjectsListAttribute(): string
    {
        return $this->subjects->pluck('name')->join(', ') ?: 'No subjects assigned';
    }

    /**
     * Get the teacher's assigned class sections as a string
     */
    public function getClassSectionsListAttribute(): string
    {
        $sections = $this->classSections->map(function ($section) {
            return $section->grade->name . ' - ' . $section->name;
        });

        return $sections->join(', ') ?: 'No class sections assigned';
    }

    // ============================================
    // VALIDATION & BUSINESS LOGIC
    // ============================================

    /**
     * Validate teacher assignment consistency
     */
    public function validateAssignments(): array
    {
        $errors = [];

        // Primary teachers must have a grade and class section
        if ($this->isPrimaryTeacher()) {
            if (!$this->grade_id) {
                $errors[] = 'Primary teachers must be assigned to a grade';
            }

            if (!$this->class_section_id) {
                $errors[] = 'Primary teachers must be assigned to a class section';
            }

            if ($this->specialization) {
                $errors[] = 'Primary teachers should not have a specialization';
            }
        }

        // Secondary teachers must have specialization
        if ($this->isSecondaryTeacher()) {
            if (!$this->specialization) {
                $errors[] = 'Secondary teachers must have a specialization';
            }

            // If they are grade teachers, they must have a grade assigned
            if ($this->is_grade_teacher && !$this->grade_id) {
                $errors[] = 'Grade teachers must be assigned to a grade';
            }
        }

        // Class teachers must have a class section
        if ($this->is_class_teacher && !$this->class_section_id) {
            $errors[] = 'Class teachers must be assigned to a class section';
        }

        return $errors;
    }

    /**
     * Check if teacher can be assigned to a specific grade
     */
    public function canBeAssignedToGrade(Grade $grade): bool
    {
        $gradeName = $grade->name;

        // Primary teachers can only be assigned to primary grades
        if ($this->isPrimaryTeacher()) {
            return in_array($gradeName, [
                'Baby Class', 'Middle Class', 'Reception',
                'Grade 1', 'Grade 2', 'Grade 3', 'Grade 4',
                'Grade 5', 'Grade 6', 'Grade 7'
            ]);
        }

        // Secondary teachers can only be assigned to secondary grades
        if ($this->isSecondaryTeacher()) {
            return in_array($gradeName, [
                'Grade 8', 'Grade 9', 'Grade 10', 'Grade 11', 'Grade 12'
            ]);
        }

        return false;
    }

    /**
     * Get available grades for this teacher
     */
    public function getAvailableGrades(): Collection
    {
        if ($this->isPrimaryTeacher()) {
            return Grade::whereIn('name', [
                'Baby Class', 'Middle Class', 'Reception',
                'Grade 1', 'Grade 2', 'Grade 3', 'Grade 4',
                'Grade 5', 'Grade 6', 'Grade 7'
            ])->orderBy('level')->get();
        }

        if ($this->isSecondaryTeacher()) {
            return Grade::whereIn('name', [
                'Grade 8', 'Grade 9', 'Grade 10', 'Grade 11', 'Grade 12'
            ])->orderBy('level')->get();
        }

        return collect();
    }

    // ============================================
    // QUERY HELPERS
    // ============================================

    /**
     * Get teachers by type
     */
    public static function byType(string $type): Builder
    {
        return match($type) {
            'primary' => static::primary(),
            'secondary' => static::secondary(),
            default => static::query()
        };
    }

    /**
     * Get teachers assigned to a specific grade
     */
    public static function assignedToGrade(int $gradeId): Builder
    {
        return static::where('grade_id', $gradeId);
    }

    /**
     * Get teachers teaching a specific subject
     */
    public static function teachingSubject(int $subjectId): Builder
    {
        return static::whereHas('subjects', function ($query) use ($subjectId) {
            $query->where('subjects.id', $subjectId);
        });
    }

    /**
     * Get available teachers for assignment
     */
    public static function availableForAssignment(): Builder
    {
        return static::where('is_active', true)
            ->where(function ($query) {
                $query->where('is_class_teacher', false)
                    ->orWhereNull('class_section_id');
            });
    }

    // ============================================
    // DEBUGGING METHODS
    // ============================================

    /**
     * Get debug information about this teacher
     */
    public function getDebugInfo(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'employee_id' => $this->employee_id,
            'type' => $this->isPrimaryTeacher() ? 'Primary' : 'Secondary',
            'specialization' => $this->specialization,
            'grade_id' => $this->grade_id,
            'grade_name' => $this->grade?->name,
            'class_section_id' => $this->class_section_id,
            'class_section_name' => $this->classSection?->name,
            'is_grade_teacher' => $this->is_grade_teacher,
            'is_class_teacher' => $this->is_class_teacher,
            'is_active' => $this->is_active,
            'subjects_count' => $this->subjects()->count(),
            'class_sections_count' => $this->classSections()->count(),
            'validation_errors' => $this->validateAssignments(),
        ];
    }
}
