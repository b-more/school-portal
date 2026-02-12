<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TeachingDocument extends Model
{
    use HasFactory;

    public const DOCUMENT_TYPES = [
        'scheme_of_work' => 'Scheme of Work',
        'lesson_plan' => 'Lesson Plan',
    ];

    protected $fillable = [
        'teacher_id',
        'subject_id',
        'class_section_id',
        'academic_year_id',
        'term_id',
        'document_type',
        'title',
        'file_path',
        'original_filename',
        'description',
    ];

    public function teacher(): BelongsTo
    {
        return $this->belongsTo(Teacher::class);
    }

    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class);
    }

    public function classSection(): BelongsTo
    {
        return $this->belongsTo(ClassSection::class);
    }

    public function academicYear(): BelongsTo
    {
        return $this->belongsTo(AcademicYear::class);
    }

    public function term(): BelongsTo
    {
        return $this->belongsTo(Term::class);
    }

    public function scopeForTeacher($query, $teacherId)
    {
        return $query->where('teacher_id', $teacherId);
    }

    public function scopeCurrentYear($query)
    {
        $currentYear = AcademicYear::where('is_active', true)->first();
        if ($currentYear) {
            return $query->where('academic_year_id', $currentYear->id);
        }
        return $query;
    }

    public function scopeCurrentTerm($query)
    {
        $currentTerm = Term::where('is_active', true)->first();
        if ($currentTerm) {
            return $query->where('term_id', $currentTerm->id);
        }
        return $query;
    }
}
