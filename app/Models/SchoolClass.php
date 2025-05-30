<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class SchoolClass extends Model
{
    use HasFactory;

    protected $table = 'classes';

    protected $fillable = [
        'name',
        'department',
        'grade',
        'section',
        'is_active',
        'status',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * Get all teachers assigned to this class
     */
    public function teachers(): BelongsToMany
    {
        return $this->belongsToMany(Teacher::class, 'class_teacher', 'class_id', 'teacher_id')
                    ->withPivot('role', 'is_primary')
                    ->withTimestamps();
    }

    /**
     * Get all subjects for this class
     */
    public function subjects(): BelongsToMany
    {
        return $this->belongsToMany(Subject::class, 'class_subject_teacher', 'class_id', 'subject_id')
                    ->withPivot('teacher_id')
                    ->withTimestamps();
    }

    /**
     * Get all subject teachers for this class (teachers assigned to specific subjects)
     */
    public function subjectTeachers(): BelongsToMany
    {
        return $this->belongsToMany(Teacher::class, 'class_subject_teacher', 'class_id', 'teacher_id')
                    ->withPivot('subject_id')
                    ->withTimestamps();
    }

    /**
     * Get primary teachers for this class
     */
    public function primaryTeachers(): BelongsToMany
    {
        return $this->teachers()->wherePivot('is_primary', true);
    }

    /**
     * Get class teachers (main teachers) for this class
     */
    public function classTeachers(): BelongsToMany
    {
        return $this->teachers()->wherePivot('role', 'class_teacher');
    }

    /**
     * Get subject teachers only (not class teachers)
     */
    public function onlySubjectTeachers(): BelongsToMany
    {
        return $this->teachers()->wherePivot('role', 'subject_teacher');
    }

    /**
     * Get assistant teachers for this class
     */
    public function assistantTeachers(): BelongsToMany
    {
        return $this->teachers()->wherePivot('role', 'assistant_teacher');
    }

    /**
     * Check if this class has any teachers assigned
     */
    public function hasTeachers(): bool
    {
        return $this->teachers()->exists();
    }

    /**
     * Get total number of teachers assigned to this class
     */
    public function getTeachersCountAttribute(): int
    {
        return $this->teachers()->count();
    }

    /**
     * Get total number of subject teachers
     */
    public function getSubjectTeachersCountAttribute(): int
    {
        return $this->subjectTeachers()->distinct()->count();
    }

    /**
     * Scope for active classes
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope for classes by department
     */
    public function scopeByDepartment($query, $department)
    {
        return $query->where('department', $department);
    }

    /**
     * Scope for classes by grade
     */
    public function scopeByGrade($query, $grade)
    {
        return $query->where('grade', $grade);
    }
}
