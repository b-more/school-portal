<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TeacherDesignation extends Model
{
    use HasFactory;

    protected $fillable = [
        'teacher_id',
        'staff_designation_id',
        'school_section_id',
        'assigned_date',
        'end_date',
        'is_active',
        'notes',
    ];

    protected $casts = [
        'assigned_date' => 'date',
        'end_date' => 'date',
        'is_active' => 'boolean',
    ];

    /**
     * Get the teacher
     */
    public function teacher(): BelongsTo
    {
        return $this->belongsTo(Teacher::class);
    }

    /**
     * Get the designation
     */
    public function designation(): BelongsTo
    {
        return $this->belongsTo(StaffDesignation::class, 'staff_designation_id');
    }

    /**
     * Alias for designation
     */
    public function staffDesignation(): BelongsTo
    {
        return $this->belongsTo(StaffDesignation::class, 'staff_designation_id');
    }

    /**
     * Get the school section
     */
    public function schoolSection(): BelongsTo
    {
        return $this->belongsTo(SchoolSection::class);
    }

    /**
     * Scope for active designations
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true)
            ->where(function ($q) {
                $q->whereNull('end_date')
                  ->orWhere('end_date', '>=', now());
            });
    }

    /**
     * Scope for specific teacher
     */
    public function scopeForTeacher($query, int $teacherId)
    {
        return $query->where('teacher_id', $teacherId);
    }

    /**
     * Scope for specific section
     */
    public function scopeForSection($query, int $sectionId)
    {
        return $query->where('school_section_id', $sectionId);
    }

    /**
     * Check if designation is currently active
     */
    public function isCurrentlyActive(): bool
    {
        if (!$this->is_active) {
            return false;
        }

        if ($this->end_date && $this->end_date->isPast()) {
            return false;
        }

        return true;
    }

    /**
     * Get designation name with section
     */
    public function getFullNameAttribute(): string
    {
        $name = $this->designation?->name ?? 'Unknown';

        if ($this->schoolSection) {
            $name .= ' (' . $this->schoolSection->name . ')';
        }

        return $name;
    }
}
