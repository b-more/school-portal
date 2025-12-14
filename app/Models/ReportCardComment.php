<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ReportCardComment extends Model
{
    protected $fillable = [
        'student_id',
        'term_id',
        'academic_year_id',
        'class_teacher_comment',
        'class_teacher_id',
        'class_teacher_commented_at',
        'head_teacher_comment',
        'head_teacher_id',
        'head_teacher_commented_at',
        'last_generated_at',
        'generation_count',
    ];

    protected $casts = [
        'class_teacher_commented_at' => 'datetime',
        'head_teacher_commented_at' => 'datetime',
        'last_generated_at' => 'datetime',
        'generation_count' => 'integer',
    ];

    /**
     * Get the student that owns this report card comment.
     */
    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    /**
     * Get the term for this report card.
     */
    public function term(): BelongsTo
    {
        return $this->belongsTo(Term::class);
    }

    /**
     * Get the academic year.
     */
    public function academicYear(): BelongsTo
    {
        return $this->belongsTo(AcademicYear::class);
    }

    /**
     * Get the class teacher who wrote the comment.
     */
    public function classTeacher(): BelongsTo
    {
        return $this->belongsTo(Teacher::class, 'class_teacher_id');
    }

    /**
     * Get the head teacher who wrote the comment.
     */
    public function headTeacher(): BelongsTo
    {
        return $this->belongsTo(Teacher::class, 'head_teacher_id');
    }

    /**
     * Find or create a comment record for a student/term/year.
     */
    public static function findOrCreateFor(int $studentId, int $termId, int $academicYearId): self
    {
        return static::firstOrCreate([
            'student_id' => $studentId,
            'term_id' => $termId,
            'academic_year_id' => $academicYearId,
        ]);
    }

    /**
     * Set the class teacher comment.
     */
    public function setClassTeacherComment(string $comment, int $teacherId): self
    {
        $this->update([
            'class_teacher_comment' => $comment,
            'class_teacher_id' => $teacherId,
            'class_teacher_commented_at' => now(),
        ]);

        return $this;
    }

    /**
     * Set the head teacher comment.
     */
    public function setHeadTeacherComment(string $comment, int $teacherId): self
    {
        $this->update([
            'head_teacher_comment' => $comment,
            'head_teacher_id' => $teacherId,
            'head_teacher_commented_at' => now(),
        ]);

        return $this;
    }

    /**
     * Mark as generated.
     */
    public function markAsGenerated(): self
    {
        $this->update([
            'last_generated_at' => now(),
            'generation_count' => $this->generation_count + 1,
        ]);

        return $this;
    }

    /**
     * Scope for a specific term and academic year.
     */
    public function scopeForTermAndYear($query, int $termId, int $academicYearId)
    {
        return $query->where('term_id', $termId)
            ->where('academic_year_id', $academicYearId);
    }

    /**
     * Scope for students with class teacher comments.
     */
    public function scopeWithClassTeacherComment($query)
    {
        return $query->whereNotNull('class_teacher_comment');
    }

    /**
     * Scope for students with head teacher comments.
     */
    public function scopeWithHeadTeacherComment($query)
    {
        return $query->whereNotNull('head_teacher_comment');
    }
}
