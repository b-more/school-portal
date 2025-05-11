<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SubjectTeaching extends Model
{
    use HasFactory;

    protected $fillable = [
        'teacher_id',
        'subject_id',
        'class_section_id',
        'academic_year_id',
    ];

    /**
     * Get the teacher that owns the subject teaching
     */
    public function teacher(): BelongsTo
    {
        return $this->belongsTo(Teacher::class);
    }

    /**
     * Get the subject that is being taught
     */
    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class);
    }

    /**
     * Get the class section where this subject is taught
     */
    public function classSection(): BelongsTo
    {
        return $this->belongsTo(ClassSection::class);
    }

    /**
     * Get the academic year for this teaching assignment
     */
    public function academicYear(): BelongsTo
    {
        return $this->belongsTo(AcademicYear::class);
    }

    /**
     * Get the grade through class section
     */
    public function grade()
    {
        return $this->classSection->grade ?? null;
    }

    /**
     * Get all students in this class section
     */
    public function students()
    {
        return $this->classSection->students ?? collect([]);
    }

    /**
     * Scope for current academic year
     */
    public function scopeCurrentYear($query)
    {
        $currentYear = AcademicYear::where('is_active', true)->first();
        if ($currentYear) {
            return $query->where('academic_year_id', $currentYear->id);
        }
        return $query;
    }

    /**
     * Scope for specific teacher
     */
    public function scopeForTeacher($query, $teacherId)
    {
        return $query->where('teacher_id', $teacherId);
    }

    /**
     * Scope for specific subject
     */
    public function scopeForSubject($query, $subjectId)
    {
        return $query->where('subject_id', $subjectId);
    }

    /**
     * Scope for specific class section
     */
    public function scopeForClassSection($query, $classSectionId)
    {
        return $query->where('class_section_id', $classSectionId);
    }

    /**
     * Get the display name for this teaching assignment
     */
    public function getDisplayNameAttribute()
    {
        return "{$this->subject->name} - {$this->classSection->name}";
    }

    public function subjectTeachings(): HasMany
{
    return $this->hasMany(SubjectTeaching::class);
}
}
