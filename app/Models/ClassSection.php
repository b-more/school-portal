<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ClassSection extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'grade_id',
        'academic_year_id',
        'class_teacher_id',
        'capacity',
        'description',
        'is_active',
    ];

    /**
     * Get the grade that this class section belongs to
     */
    public function grade(): BelongsTo
    {
        return $this->belongsTo(Grade::class);
    }

    /**
     * Get the academic year that this class section belongs to
     */
    public function academicYear(): BelongsTo
    {
        return $this->belongsTo(AcademicYear::class);
    }

    /**
     * Get the teacher assigned as the class teacher
     */
    public function classTeacher(): BelongsTo
    {
        return $this->belongsTo(Teacher::class, 'class_teacher_id');
    }

    /**
     * Get the teachers assigned to this class section (for all roles)
     */
    public function teachers(): BelongsToMany
    {
        return $this->belongsToMany(Teacher::class, 'teacher_class_section', 'class_section_id', 'teacher_id')
                    ->withTimestamps();
    }

    /**
     * Get students in this class section
     */
    public function students(): HasMany
    {
        return $this->hasMany(Student::class);
    }

    /**
     * Get the employees associated with this class section through teachers
     */
    public function employees(): BelongsToMany
    {
        return $this->belongsToMany(Employee::class, 'teacher_class_section', 'class_section_id', 'teacher_id')
                   ->using(TeacherClassSection::class)
                   ->withTimestamps();
    }
}
