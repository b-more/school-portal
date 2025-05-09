<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FeeStructure extends Model
{
    use HasFactory;

    protected $fillable = [
        'grade_id', // Changed from 'grade' to 'grade_id'
        'term_id',
        'academic_year_id',
        'basic_fee',
        'additional_charges',
        'total_fee',
        'description',
        'is_active',
    ];

    protected $casts = [
        'additional_charges' => 'json',
        'basic_fee' => 'decimal:2',
        'total_fee' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    public function studentFees(): HasMany
    {
        return $this->hasMany(StudentFee::class);
    }

    public function academicYear(): BelongsTo
    {
        return $this->belongsTo(AcademicYear::class);
    }

    public function term(): BelongsTo
    {
        return $this->belongsTo(Term::class);
    }

    public function grade(): BelongsTo
    {
        return $this->belongsTo(Grade::class);
    }

    // Helper method to get grade name (for backward compatibility)
    public function getGradeNameAttribute()
    {
        return $this->grade?->name ?? 'Unknown Grade';
    }
}
