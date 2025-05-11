<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

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
        'profile_photo',
        'biography',
    ];

    protected $casts = [
        'join_date' => 'date',
        'is_active' => 'boolean',
        'is_class_teacher' => 'boolean',
        'is_grade_teacher' => 'boolean',
    ];

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
     * Get the class section if this teacher is a class teacher
     */
    public function classSection(): BelongsTo
    {
        return $this->belongsTo(ClassSection::class);
    }

    /**
     * Get the grade if this teacher is a grade teacher
     */
    public function grade(): BelongsTo
    {
        return $this->belongsTo(Grade::class);
    }

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

    /**
     * Get homework assigned by this teacher
     */
    public function homework(): HasMany
    {
        return $this->hasMany(Homework::class, 'assigned_by');
    }

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
     * Get the employee record for this teacher
     */
    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'employee_id');
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
        return $this->teachesSubjectInClass($student->class_section_id);
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

    /**
     * Get teacher's department
     */
    public function getDepartmentAttribute()
    {
        return $this->employee?->department ?? 'N/A';
    }
}
