<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SalaryGrade extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'name',
        'staff_designation_id',
        'designation', // Keep for backward compatibility
        'basic_salary',
        'description',
        'is_active',
    ];

    protected $casts = [
        'basic_salary' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    /**
     * Get the staff designation for this salary grade
     */
    public function staffDesignation(): BelongsTo
    {
        return $this->belongsTo(StaffDesignation::class);
    }

    /**
     * Get employees with this salary grade
     */
    public function employees(): HasMany
    {
        return $this->hasMany(Employee::class);
    }

    /**
     * Scope for active salary grades
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Get formatted salary
     */
    public function getFormattedSalaryAttribute(): string
    {
        return 'ZMW ' . number_format($this->basic_salary, 2);
    }

    /**
     * Get designation name (from relationship or legacy field)
     */
    public function getDesignationNameAttribute(): string
    {
        return $this->staffDesignation?->name ?? $this->designation ?? 'N/A';
    }
}
