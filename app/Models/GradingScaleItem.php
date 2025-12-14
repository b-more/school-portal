<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GradingScaleItem extends Model
{
    protected $fillable = [
        'grading_scale_id',
        'grade',
        'min_marks',
        'max_marks',
        'grade_points',
        'remark',
        'sort_order',
    ];

    protected $casts = [
        'min_marks' => 'decimal:2',
        'max_marks' => 'decimal:2',
        'grade_points' => 'decimal:1',
        'sort_order' => 'integer',
    ];

    /**
     * Get the grading scale.
     */
    public function gradingScale(): BelongsTo
    {
        return $this->belongsTo(GradingScale::class);
    }

    /**
     * Check if marks fall within this grade range.
     */
    public function containsMarks(float $marks): bool
    {
        return $marks >= $this->min_marks && $marks <= $this->max_marks;
    }

    /**
     * Get formatted range display.
     */
    public function getRangeDisplayAttribute(): string
    {
        return "{$this->min_marks} - {$this->max_marks}";
    }

    /**
     * Get full display text.
     */
    public function getFullDisplayAttribute(): string
    {
        $display = "{$this->grade}: {$this->min_marks}-{$this->max_marks}%";
        if ($this->remark) {
            $display .= " ({$this->remark})";
        }
        return $display;
    }
}
