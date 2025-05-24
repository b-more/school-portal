<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Grade extends Model
{
    use HasFactory;

    protected $fillable = [
        'school_section_id',
        'name',
        'code',
        'level',
        'description',
        'capacity',
        'breakeven_number',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * Get the school section that this grade belongs to
     */
    public function schoolSection(): BelongsTo
    {
        return $this->belongsTo(SchoolSection::class);
    }

    /**
     * Get students count through class sections
     */
    public function studentsCount()
    {
        return $this->hasManyThrough(Student::class, ClassSection::class);
    }

    /**
     * Get homework for this grade
     */
    public function homework(): HasMany
    {
        return $this->hasMany(Homework::class);
    }

    /**
     * Get class sections for this grade
     */
    public function classSections(): HasMany
    {
        return $this->hasMany(ClassSection::class);
    }

    /**
     * Get subjects associated with this grade
     */
    public function subjects(): BelongsToMany
    {
        return $this->belongsToMany(Subject::class, 'grade_subject')
                    ->withTimestamps();
    }

    /**
     * Get students through class sections
     */
    public function students()
    {
        return $this->hasManyThrough(Student::class, ClassSection::class);
    }

    /**
     * Get fee structures for this grade.
     */
    public function feeStructures(): HasMany
    {
        return $this->hasMany(FeeStructure::class);
    }

    /**
     * Calculate total students in this grade across all sections - more efficiently
     */
    public function getTotalStudentsAttribute()
    {
        return $this->students()->count();
    }

    /**
     * Check if grade is at capacity
     */
    public function isAtCapacity()
    {
        return $this->getTotalStudentsAttribute() >= $this->capacity;
    }

    /**
     * Check if grade needs a new section
     */
    public function needsNewSection()
    {
        if ($this->classSections()->count() === 0) {
            return true;
        }

        $allFull = true;
        foreach ($this->classSections as $section) {
            if (!$section->isAtCapacity()) {
                $allFull = false;
                break;
            }
        }

        return $allFull || $this->getTotalStudentsAttribute() >= $this->breakeven_number;
    }
}
