<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Collection;

class TimetableEntry extends Model
{
    use HasFactory;

    // Day constants
    public const DAYS = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday'];

    protected $fillable = [
        'timetable_period_id',
        'class_section_id',
        'subject_id',
        'teacher_id',
        'academic_year_id',
        'day_of_week',
        'room',
        'notes',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * Get the timetable period
     */
    public function timetablePeriod(): BelongsTo
    {
        return $this->belongsTo(TimetablePeriod::class);
    }

    /**
     * Alias for timetablePeriod relationship
     */
    public function period(): BelongsTo
    {
        return $this->belongsTo(TimetablePeriod::class, 'timetable_period_id');
    }

    /**
     * Get the class section
     */
    public function classSection(): BelongsTo
    {
        return $this->belongsTo(ClassSection::class);
    }

    /**
     * Get the subject
     */
    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class);
    }

    /**
     * Get the teacher
     */
    public function teacher(): BelongsTo
    {
        return $this->belongsTo(Teacher::class);
    }

    /**
     * Get the academic year
     */
    public function academicYear(): BelongsTo
    {
        return $this->belongsTo(AcademicYear::class);
    }

    /**
     * Scope for active entries
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope for specific day
     */
    public function scopeForDay($query, string $day)
    {
        return $query->where('day_of_week', $day);
    }

    /**
     * Scope for specific class section
     */
    public function scopeForClassSection($query, int $classSectionId)
    {
        return $query->where('class_section_id', $classSectionId);
    }

    /**
     * Scope for specific teacher
     */
    public function scopeForTeacher($query, int $teacherId)
    {
        return $query->where('teacher_id', $teacherId);
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
     * Check if a teacher has a conflict for this period/day
     * Returns conflicting entry if exists, null otherwise
     */
    public static function checkTeacherConflict(
        int $teacherId,
        int $periodId,
        string $dayOfWeek,
        int $academicYearId,
        ?int $excludeEntryId = null
    ): ?self {
        return self::where('teacher_id', $teacherId)
            ->where('timetable_period_id', $periodId)
            ->where('day_of_week', $dayOfWeek)
            ->where('academic_year_id', $academicYearId)
            ->where('is_active', true)
            ->when($excludeEntryId, fn($q) => $q->where('id', '!=', $excludeEntryId))
            ->with(['classSection.grade', 'subject'])
            ->first();
    }

    /**
     * Get all conflicts for a teacher in an academic year
     */
    public static function getTeacherConflicts(int $teacherId, int $academicYearId): array
    {
        $conflicts = [];

        $entries = self::where('teacher_id', $teacherId)
            ->where('academic_year_id', $academicYearId)
            ->where('is_active', true)
            ->get()
            ->groupBy(fn($e) => "{$e->timetable_period_id}-{$e->day_of_week}");

        foreach ($entries as $key => $group) {
            if ($group->count() > 1) {
                $conflicts[$key] = $group;
            }
        }

        return $conflicts;
    }

    /**
     * Get timetable grid for a class section
     * Returns array with periods as keys, each containing 'period' and 'days' array
     */
    public static function getClassTimetable(int $classSectionId, int $academicYearId): array
    {
        $entries = self::where('class_section_id', $classSectionId)
            ->where('academic_year_id', $academicYearId)
            ->where('is_active', true)
            ->with(['timetablePeriod', 'subject', 'teacher'])
            ->get();

        $periods = TimetablePeriod::where('academic_year_id', $academicYearId)
            ->where('is_active', true)
            ->orderBy('order')
            ->get();

        $timetable = [];
        foreach ($periods as $period) {
            $timetable[$period->id] = [
                'period' => $period,
                'days' => [],
            ];
            foreach (self::DAYS as $day) {
                $entry = $entries->first(fn($e) =>
                    $e->timetable_period_id === $period->id &&
                    $e->day_of_week === $day
                );
                $timetable[$period->id]['days'][$day] = $entry;
            }
        }

        return $timetable;
    }

    /**
     * Get timetable grid for a teacher
     * Returns array with periods as keys, each containing 'period' and 'days' array
     */
    public static function getTeacherTimetable(int $teacherId, int $academicYearId): array
    {
        $entries = self::where('teacher_id', $teacherId)
            ->where('academic_year_id', $academicYearId)
            ->where('is_active', true)
            ->with(['timetablePeriod', 'classSection.grade', 'subject'])
            ->get();

        $periods = TimetablePeriod::where('academic_year_id', $academicYearId)
            ->where('is_active', true)
            ->orderBy('order')
            ->get();

        $timetable = [];
        foreach ($periods as $period) {
            $timetable[$period->id] = [
                'period' => $period,
                'days' => [],
            ];
            foreach (self::DAYS as $day) {
                $entry = $entries->first(fn($e) =>
                    $e->timetable_period_id === $period->id &&
                    $e->day_of_week === $day
                );
                $timetable[$period->id]['days'][$day] = $entry;
            }
        }

        return $timetable;
    }

    /**
     * Get teaching load summary for a teacher
     */
    public static function getTeacherLoadSummary(int $teacherId, int $academicYearId): array
    {
        $entries = self::where('teacher_id', $teacherId)
            ->where('academic_year_id', $academicYearId)
            ->where('is_active', true)
            ->with(['classSection.grade', 'subject'])
            ->get();

        $totalPeriods = $entries->count();
        $classesTaught = [];
        $subjectsTaught = [];

        foreach ($entries as $entry) {
            if ($entry->classSection) {
                $className = ($entry->classSection->grade->name ?? '') . ' ' . ($entry->classSection->name ?? '');
                $classesTaught[trim($className)] = true;
            }
            if ($entry->subject) {
                $subjectsTaught[$entry->subject->name] = true;
            }
        }

        return [
            'totalPeriods' => $totalPeriods,
            'classesTaught' => array_keys($classesTaught),
            'subjectsTaught' => array_keys($subjectsTaught),
        ];
    }

    /**
     * Get display name for the entry
     */
    public function getDisplayNameAttribute(): string
    {
        $subject = $this->subject?->name ?? 'No Subject';
        $teacher = $this->teacher?->name ?? 'No Teacher';
        return "{$subject} ({$teacher})";
    }

    /**
     * Get class display name for the entry
     */
    public function getClassDisplayAttribute(): string
    {
        if (!$this->classSection) {
            return 'No Class';
        }
        return ($this->classSection->grade->name ?? '') . ' ' . ($this->classSection->name ?? '');
    }
}
