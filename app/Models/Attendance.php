<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Attendance extends Model
{
    use HasFactory;

    protected $fillable = [
        'student_id',
        'class_section_id',
        'grade_id',
        'academic_year_id',
        'term_id',
        'attendance_date',
        'status',
        'check_in_time',
        'check_out_time',
        'notes',
        'marked_by',
    ];

    protected $casts = [
        'attendance_date' => 'date',
        'check_in_time' => 'datetime:H:i',
        'check_out_time' => 'datetime:H:i',
    ];

    /**
     * Get the student that this attendance belongs to
     */
    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    /**
     * Get the class section
     */
    public function classSection(): BelongsTo
    {
        return $this->belongsTo(ClassSection::class);
    }

    /**
     * Get the grade
     */
    public function grade(): BelongsTo
    {
        return $this->belongsTo(Grade::class);
    }

    /**
     * Get the academic year
     */
    public function academicYear(): BelongsTo
    {
        return $this->belongsTo(AcademicYear::class);
    }

    /**
     * Get the term
     */
    public function term(): BelongsTo
    {
        return $this->belongsTo(Term::class);
    }

    /**
     * Get the user who marked the attendance
     */
    public function markedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'marked_by');
    }

    /**
     * Scope to filter by student
     */
    public function scopeByStudent($query, $studentId)
    {
        return $query->where('student_id', $studentId);
    }

    /**
     * Scope to filter by class section
     */
    public function scopeByClassSection($query, $classSectionId)
    {
        return $query->where('class_section_id', $classSectionId);
    }

    /**
     * Scope to filter by grade
     */
    public function scopeByGrade($query, $gradeId)
    {
        return $query->where('grade_id', $gradeId);
    }

    /**
     * Scope to filter by date range
     */
    public function scopeByDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('attendance_date', [$startDate, $endDate]);
    }

    /**
     * Scope to filter by status
     */
    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope to filter by academic year
     */
    public function scopeByAcademicYear($query, $academicYearId)
    {
        return $query->where('academic_year_id', $academicYearId);
    }

    /**
     * Scope to filter by term
     */
    public function scopeByTerm($query, $termId)
    {
        return $query->where('term_id', $termId);
    }

    /**
     * Get the status symbol (single source of truth for P/X/S/Y/L mapping)
     */
    public static function getStatusSymbol(string $status): string
    {
        return match ($status) {
            'present' => 'P',
            'absent' => 'X',
            'sick' => 'S',
            'late' => 'Y',
            'excused' => 'L',
            default => '-',
        };
    }

    /**
     * Get formatted status
     */
    public function getFormattedStatusAttribute(): string
    {
        return match ($this->status) {
            'present' => 'Present',
            'absent' => 'Absent',
            'sick' => 'Sick',
            'late' => 'Late',
            'excused' => 'Excused',
            default => 'Unknown'
        };
    }

    /**
     * Check if student was present
     */
    public function wasPresent(): bool
    {
        return $this->status === 'present';
    }

    /**
     * Check if student was absent
     */
    public function wasAbsent(): bool
    {
        return $this->status === 'absent';
    }

    /**
     * Check if student was late
     */
    public function wasLate(): bool
    {
        return $this->status === 'late';
    }

    /**
     * Check if student was sick
     */
    public function wasSick(): bool
    {
        return $this->status === 'sick';
    }
}
