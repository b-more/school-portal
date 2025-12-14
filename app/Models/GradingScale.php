<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class GradingScale extends Model
{
    protected $fillable = [
        'name',
        'grade_level',
        'description',
        'is_default',
        'is_active',
    ];

    protected $casts = [
        'is_default' => 'boolean',
        'is_active' => 'boolean',
    ];

    /**
     * Get the grading scale items.
     */
    public function items(): HasMany
    {
        return $this->hasMany(GradingScaleItem::class)->orderBy('sort_order')->orderByDesc('min_marks');
    }

    /**
     * Scope for active grading scales.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope for grading scales by grade level.
     */
    public function scopeForGradeLevel($query, string $gradeLevel)
    {
        return $query->where(function ($q) use ($gradeLevel) {
            $q->where('grade_level', $gradeLevel)
              ->orWhere('grade_level', 'all');
        });
    }

    /**
     * Get the default grading scale for a grade level.
     */
    public static function getDefaultForGradeLevel(string $gradeLevel): ?self
    {
        return static::active()
            ->forGradeLevel($gradeLevel)
            ->where('is_default', true)
            ->first()
            ?? static::active()
                ->forGradeLevel($gradeLevel)
                ->first();
    }

    /**
     * Determine grade level from a Grade model.
     */
    public static function determineGradeLevelFromGrade(Grade $grade): string
    {
        $gradeName = $grade->name ?? '';

        // Check for ECL/Primary grades
        if (in_array($gradeName, ['Baby Class', 'Middle Class', 'Reception'])) {
            return 'primary';
        }

        // Check for Grade 1-7 (Primary)
        if (preg_match('/Grade\s*(\d+)/i', $gradeName, $matches)) {
            $gradeNumber = (int) $matches[1];
            return $gradeNumber <= 7 ? 'primary' : 'secondary';
        }

        // Default to primary for unknown grades
        return 'primary';
    }

    /**
     * Calculate grade from marks using this scale.
     */
    public function calculateGrade(float $marks): ?GradingScaleItem
    {
        return $this->items()
            ->where('min_marks', '<=', $marks)
            ->where('max_marks', '>=', $marks)
            ->first();
    }

    /**
     * Get grade letter from marks.
     */
    public function getGradeLetter(float $marks): string
    {
        $item = $this->calculateGrade($marks);
        return $item ? $item->grade : 'N/A';
    }

    /**
     * Get grade remark from marks.
     */
    public function getGradeRemark(float $marks): string
    {
        $item = $this->calculateGrade($marks);
        return $item ? ($item->remark ?? '') : '';
    }

    /**
     * Get grade points from marks.
     */
    public function getGradePoints(float $marks): float
    {
        $item = $this->calculateGrade($marks);
        return $item ? $item->grade_points : 0;
    }
}
