<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class HomeworkSubmission extends Model
{
    use HasFactory;

    protected $fillable = [
        'homework_id',
        'student_id',
        'content',
        'file_attachment',
        'submitted_at',
        'status',
        'is_late',
        'marks',
        'feedback',
        'teacher_notes',
        'graded_by',
        'graded_at',
    ];

    protected $casts = [
        'submitted_at' => 'datetime',
        'marks' => 'decimal:2',
        'is_late' => 'boolean',
        'graded_at' => 'datetime',
        'file_attachment' => 'array',
    ];

    public function homework(): BelongsTo
    {
        return $this->belongsTo(Homework::class);
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function gradedBy(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'graded_by');
    }

    /**
     * Get the result record associated with this submission.
     */
    public function result(): HasOne
    {
        return $this->hasOneThrough(
            Result::class,
            Homework::class,
            'id', // Foreign key on homework table
            'homework_id', // Foreign key on results table
            'homework_id', // Local key on homework_submissions table
            'id' // Local key on homework table
        )->where('results.student_id', $this->student_id)
         ->where('results.exam_type', 'assignment');
    }

    /**
     * Check if this submission has an associated result record
     */
    public function hasResult(): bool
    {
        return $this->result()->exists();
    }

    /**
     * Calculate the percentage score
     */
    public function getScorePercentage(): float
    {
        if (!$this->marks || !$this->homework || !$this->homework->max_score) {
            return 0;
        }

        return min(100, ($this->marks / $this->homework->max_score) * 100);
    }

    /**
     * Get submission status badge color
     */
    public function getStatusColor(): string
    {
        return match($this->status) {
            'submitted' => 'warning',
            'graded' => 'success',
            'returned' => 'info',
            default => 'gray',
        };
    }

    /**
     * Get a human-readable submission status
     */
    public function getSubmissionStatus(): string
    {
        $status = $this->status;

        if ($this->is_late) {
            $status .= ' (Late)';
        }

        return ucfirst($status);
    }
}
