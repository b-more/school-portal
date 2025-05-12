<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class ClassSection extends Model
{
    use HasFactory;

    protected $fillable = [
        'grade_id',
        'academic_year_id',
        'description',
        'name',
        'code',
        'capacity',
        'class_teacher_id',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function grade(): BelongsTo
    {
        return $this->belongsTo(Grade::class);
    }

    public function academicYear(): BelongsTo
    {
        return $this->belongsTo(AcademicYear::class);
    }

    public function classTeacher(): BelongsTo
    {
        return $this->belongsTo(Teacher::class, 'class_teacher_id');
    }

    // This relationship is already correctly defined - students have class_section_id
    public function students(): HasMany
    {
        return $this->hasMany(Student::class);
    }

    public function subjects(): BelongsToMany
    {
        return $this->belongsToMany(Subject::class, 'class_section_subject')
                    ->withPivot('teacher_id')
                    ->withTimestamps();
    }

    // Check if class section is at capacity
    public function isAtCapacity()
    {
        return $this->students()->count() >= $this->capacity;
    }

    // Get available spaces
    public function getAvailableSpacesAttribute()
    {
        return max(0, $this->capacity - $this->students()->count());
    }

    // Generate section code
    public static function generateCode($gradeCode, $sectionName)
    {
        return "{$gradeCode}-{$sectionName}";
    }

    // Get display name including grade
    public function getFullNameAttribute()
    {
        return "{$this->grade->name} - {$this->name}";
    }
}
