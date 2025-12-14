<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class StaffDesignation extends Model
{
    use HasFactory;

    // Designation codes
    public const DEAN_OF_TEACHERS = 'dean_teachers';
    public const SENIOR_TEACHER = 'senior_teacher';
    public const SUBJECT_COORDINATOR = 'subject_coordinator';
    public const TEACHER = 'teacher';

    // Hierarchy levels (1 = highest, 5 = lowest)
    public const LEVEL_DEAN = 1;
    public const LEVEL_SENIOR = 2;
    public const LEVEL_COORDINATOR = 2;
    public const LEVEL_TEACHER = 3;

    protected $fillable = [
        'name',
        'code',
        'description',
        'section',
        'hierarchy_level',
        'permissions',
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'permissions' => 'array',
        'is_active' => 'boolean',
        'hierarchy_level' => 'integer',
        'sort_order' => 'integer',
    ];

    /**
     * Get teachers with this designation
     */
    public function teachers(): BelongsToMany
    {
        return $this->belongsToMany(Teacher::class, 'teacher_designations')
            ->withPivot(['school_section_id', 'assigned_date', 'end_date', 'is_active', 'notes'])
            ->withTimestamps();
    }

    /**
     * Get all teacher designation records
     */
    public function teacherDesignations(): HasMany
    {
        return $this->hasMany(TeacherDesignation::class);
    }

    /**
     * Scope for active designations
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope for specific section
     */
    public function scopeForSection($query, string $section)
    {
        return $query->where(function ($q) use ($section) {
            $q->where('section', $section)
              ->orWhere('section', 'both');
        });
    }

    /**
     * Scope ordered by hierarchy (highest level first)
     */
    public function scopeOrderByHierarchy($query)
    {
        return $query->orderBy('hierarchy_level')->orderBy('sort_order');
    }

    /**
     * Check if designation is for primary section
     */
    public function isForPrimary(): bool
    {
        return in_array($this->section, ['primary', 'both']);
    }

    /**
     * Check if designation is for secondary section
     */
    public function isForSecondary(): bool
    {
        return in_array($this->section, ['secondary', 'both']);
    }

    /**
     * Check if designation has specific permission
     */
    public function hasPermission(string $permission): bool
    {
        return in_array($permission, $this->permissions ?? []);
    }

    /**
     * Get all permissions as array
     */
    public function getPermissionsArray(): array
    {
        return $this->permissions ?? [];
    }

    /**
     * Get designation by code
     */
    public static function findByCode(string $code): ?self
    {
        return static::where('code', $code)->first();
    }
}
