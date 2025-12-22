<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Carbon\Carbon;

class TimetablePeriod extends Model
{
    use HasFactory;

    // Period type constants
    public const TYPE_LESSON = 'lesson';
    public const TYPE_ASSEMBLY = 'assembly';
    public const TYPE_TEA_BREAK = 'tea_break';
    public const TYPE_LUNCH_BREAK = 'lunch_break';
    public const TYPE_OTHER = 'other';

    protected $fillable = [
        'academic_year_id',
        'name',
        'type',
        'start_time',
        'end_time',
        'order',
        'short_name',
        'is_active',
        'description',
    ];

    protected $casts = [
        'start_time' => 'datetime:H:i',
        'end_time' => 'datetime:H:i',
        'is_active' => 'boolean',
        'order' => 'integer',
    ];

    /**
     * Get the academic year this period belongs to
     */
    public function academicYear(): BelongsTo
    {
        return $this->belongsTo(AcademicYear::class);
    }

    /**
     * Get all timetable entries for this period
     */
    public function timetableEntries(): HasMany
    {
        return $this->hasMany(TimetableEntry::class);
    }

    /**
     * Scope for active periods only
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope for lesson periods only (excludes breaks)
     */
    public function scopeLessons($query)
    {
        return $query->where('type', self::TYPE_LESSON);
    }

    /**
     * Scope for break periods (assembly, tea, lunch)
     */
    public function scopeBreaks($query)
    {
        return $query->whereIn('type', [
            self::TYPE_ASSEMBLY,
            self::TYPE_TEA_BREAK,
            self::TYPE_LUNCH_BREAK,
            self::TYPE_OTHER,
        ]);
    }

    /**
     * Scope ordered by sequence
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('order');
    }

    /**
     * Scope for current academic year
     */
    public function scopeCurrentYear($query)
    {
        $currentYear = AcademicYear::current();
        return $query->when($currentYear, fn($q) => $q->where('academic_year_id', $currentYear->id));
    }

    /**
     * Scope for specific academic year
     */
    public function scopeForYear($query, int $academicYearId)
    {
        return $query->where('academic_year_id', $academicYearId);
    }

    /**
     * Check if this is a teaching period
     */
    public function isLesson(): bool
    {
        return $this->type === self::TYPE_LESSON;
    }

    /**
     * Check if this is a break period
     */
    public function isBreak(): bool
    {
        return in_array($this->type, [
            self::TYPE_ASSEMBLY,
            self::TYPE_TEA_BREAK,
            self::TYPE_LUNCH_BREAK,
            self::TYPE_OTHER,
        ]);
    }

    /**
     * Get formatted time range
     */
    public function getTimeRangeAttribute(): string
    {
        $start = $this->start_time ? Carbon::parse($this->start_time)->format('H:i') : '';
        $end = $this->end_time ? Carbon::parse($this->end_time)->format('H:i') : '';
        return "{$start} - {$end}";
    }

    /**
     * Get duration in minutes
     */
    public function getDurationMinutesAttribute(): int
    {
        if (!$this->start_time || !$this->end_time) {
            return 0;
        }
        $start = Carbon::parse($this->start_time);
        $end = Carbon::parse($this->end_time);
        return $start->diffInMinutes($end);
    }

    /**
     * Get type label for display
     */
    public static function getTypeOptions(): array
    {
        return [
            self::TYPE_LESSON => 'Lesson',
            self::TYPE_ASSEMBLY => 'Assembly',
            self::TYPE_TEA_BREAK => 'Tea Break',
            self::TYPE_LUNCH_BREAK => 'Lunch Break',
            self::TYPE_OTHER => 'Other',
        ];
    }

    /**
     * Get the type label
     */
    public function getTypeLabelAttribute(): string
    {
        return self::getTypeOptions()[$this->type] ?? $this->type;
    }

    /**
     * Get next available order number for an academic year
     */
    public static function getNextOrder(int $academicYearId): int
    {
        return (self::where('academic_year_id', $academicYearId)->max('order') ?? 0) + 1;
    }
}
