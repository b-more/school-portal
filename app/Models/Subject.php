<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Subject extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'code',
        'grade_level',
        'description',
        'is_active',
        'academic_year_id',
        'is_core',
        'credit_hours',
        'weight',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'is_core' => 'boolean',
        'weight' => 'decimal:2',
    ];

    /**
     * Get the academic year this subject belongs to
     */
    public function academicYear(): BelongsTo
    {
        return $this->belongsTo(AcademicYear::class);
    }

    /**
     * Get all grades that have this subject
     */
    public function grades(): BelongsToMany
    {
        return $this->belongsToMany(Grade::class, 'grade_subject')
                    ->withPivot('is_mandatory')
                    ->withTimestamps();
    }

    /**
     * Get all class sections where this subject is taught
     */
    public function classSections(): BelongsToMany
    {
        return $this->belongsToMany(ClassSection::class, 'class_section_subject')
                    ->withPivot('teacher_id')
                    ->withTimestamps();
    }

    /**
     * Get all employees/teachers who teach this subject (legacy relationship)
     */
    public function employees(): BelongsToMany
    {
        return $this->belongsToMany(Employee::class, 'employee_subject')
                    ->withTimestamps()
                    ->select(['employees.*']);
    }

    /**
     * Get all teachers who teach this subject
     */
    public function teachers(): BelongsToMany
    {
        return $this->belongsToMany(Teacher::class, 'subject_teachings')
                    ->withPivot('class_section_id', 'academic_year_id')
                    ->withTimestamps();
    }

    /**
     * Get all homework for this subject
     */
    public function homeworks(): HasMany
    {
        return $this->hasMany(Homework::class);
    }

    /**
     * Get all results for this subject
     */
    public function results(): HasMany
    {
        return $this->hasMany(Result::class);
    }

    /**
     * Get all subject teachings for this subject
     */
    public function subjectTeachings(): HasMany
    {
        return $this->hasMany(SubjectTeaching::class);
    }

    /**
     * Get all exams for this subject
     */
    public function exams(): HasMany
    {
        return $this->hasMany(Exam::class);
    }

    /**
     * Get all assessments for this subject
     */
    public function assessments(): HasMany
    {
        return $this->hasMany(Assessment::class);
    }

    /**
     * Scope to get only active subjects
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope to get only core subjects
     */
    public function scopeCore($query)
    {
        return $query->where('is_core', true);
    }

    /**
     * Scope to get subjects for a specific grade
     */
    public function scopeForGrade($query, $gradeId)
    {
        return $query->whereHas('grades', function ($q) use ($gradeId) {
            $q->where('grades.id', $gradeId);
        });
    }

    /**
     * Scope to get subjects for a specific academic year
     */
    public function scopeForYear($query, $yearId)
    {
        return $query->where('academic_year_id', $yearId);
    }

    /**
     * Get all class sections and their teachers for this subject
     */
    public function getClassSectionsWithTeachers($academicYearId = null)
    {
        $query = $this->classSections()->with(['grade', 'teacher']);

        if ($academicYearId) {
            $query->whereHas('subjectTeachings', function ($q) use ($academicYearId) {
                $q->where('academic_year_id', $academicYearId);
            });
        }

        return $query->get();
    }

    /**
     * Get all teachers for a specific class section and this subject
     */
    public function getTeachersForClassSection($classSectionId)
    {
        return $this->teachers()
            ->wherePivot('class_section_id', $classSectionId)
            ->get();
    }

    /**
     * Get the average score for this subject across all students
     */
    public function getAverageScore($classSectionId = null, $assessmentType = null)
    {
        $query = $this->results();

        if ($classSectionId) {
            $query->whereHas('student', function ($q) use ($classSectionId) {
                $q->where('class_section_id', $classSectionId);
            });
        }

        if ($assessmentType) {
            $query->where('exam_type', $assessmentType);
        }

        return $query->avg('marks');
    }
}
