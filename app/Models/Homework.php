<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Carbon\Carbon;

class Homework extends Model
{
    use HasFactory;

    protected $table = 'homework';

    protected $fillable = [
        'title',
        'description',
        'file_attachment',
        'homework_file',
        'assigned_by',
        'grade',
        'subject_id',
        'due_date',
        'submission_start',
        'submission_end',
        'allow_late_submission',
        'late_submission_deadline',
        'max_score',
        'submission_instructions',
        'status',
        'notify_parents',
        'sms_message',
    ];

    protected $casts = [
        'due_date' => 'date',
        'submission_start' => 'datetime',
        'submission_end' => 'datetime',
        'late_submission_deadline' => 'datetime',
        'notify_parents' => 'boolean',
        'allow_late_submission' => 'boolean',
        'file_attachment' => 'array',
    ];

    public function assignedBy(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'assigned_by');
    }

    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class);
    }

    public function submissions(): HasMany
    {
        return $this->hasMany(HomeworkSubmission::class);
    }

    /**
     * Get the SMS logs associated with this homework.
     */
    public function smsLogs(): HasMany
    {
        return $this->hasMany(SmsLog::class, 'reference_id')
            ->where('message_type', 'homework_notification');
    }

    /**
     * Get the results associated with this homework.
     */
    public function results(): HasMany
    {
        return $this->hasMany(Result::class)
            ->where('exam_type', 'assignment');
    }

    /**
     * Check if submissions are currently allowed
     */
    public function isSubmissionOpen(): bool
    {
        $now = Carbon::now();

        // If submission period has started and not ended yet
        if ($this->submission_start && $now->gte($this->submission_start)) {
            if (!$this->submission_end || $now->lte($this->submission_end)) {
                return true;
            }

            // If late submissions are allowed and within late deadline
            if ($this->allow_late_submission && $this->late_submission_deadline && $now->lte($this->late_submission_deadline)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Check if the current submission would be late
     */
    public function isLateSubmission(): bool
    {
        $now = Carbon::now();

        // If past regular deadline but before late deadline
        if ($this->submission_end && $now->gt($this->submission_end)) {
            if ($this->allow_late_submission && $this->late_submission_deadline && $now->lte($this->late_submission_deadline)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Get submission status text
     */
    public function getSubmissionStatusText(): string
    {
        $now = Carbon::now();

        if (!$this->submission_start || $now->lt($this->submission_start)) {
            return 'Not Open Yet';
        }

        if (!$this->submission_end || $now->lte($this->submission_end)) {
            return 'Open for Submission';
        }

        if ($this->allow_late_submission && $this->late_submission_deadline && $now->lte($this->late_submission_deadline)) {
            return 'Late Submission Period';
        }

        return 'Submission Closed';
    }

    /**
     * Get student submissions by grade
     */
    public function getSubmissionsByGrade(): array
    {
        return $this->submissions()
            ->join('students', 'homework_submissions.student_id', '=', 'students.id')
            ->select('students.grade', DB::raw('count(*) as total'))
            ->groupBy('students.grade')
            ->pluck('total', 'grade')
            ->toArray();
    }

    /**
     * Get submission statistics
     */
    public function getSubmissionStats(): array
    {
        $total = $this->submissions()->count();
        $graded = $this->submissions()->whereNotNull('marks')->count();
        $late = $this->submissions()->where('is_late', true)->count();

        return [
            'total' => $total,
            'graded' => $graded,
            'late' => $late,
            'average_score' => $this->submissions()->whereNotNull('marks')->avg('marks') ?? 0,
        ];
    }

    /**
     * Get result statistics
     */
    public function getResultStats(): array
    {
        $results = $this->results;

        if ($results->isEmpty()) {
            return [
                'total' => 0,
                'average_marks' => 0,
                'grades' => [],
            ];
        }

        // Count grades
        $grades = [];
        foreach ($results as $result) {
            if (!isset($grades[$result->grade])) {
                $grades[$result->grade] = 0;
            }
            $grades[$result->grade]++;
        }

        return [
            'total' => $results->count(),
            'average_marks' => $results->avg('marks'),
            'grades' => $grades,
        ];
    }

    /**
     * Check if a student has submitted this homework
     */
    public function isSubmittedByStudent(int $studentId): bool
    {
        return $this->submissions()
            ->where('student_id', $studentId)
            ->exists();
    }

    /**
     * Get a student's submission for this homework
     */
    public function getStudentSubmission(int $studentId): ?HomeworkSubmission
    {
        return $this->submissions()
            ->where('student_id', $studentId)
            ->first();
    }

    /**
     * Check if a student's submission has been graded
     */
    public function isSubmissionGraded(int $studentId): bool
    {
        return $this->submissions()
            ->where('student_id', $studentId)
            ->whereNotNull('marks')
            ->exists();
    }

    /**
     * Boot the model.
     */
    protected static function boot()
    {
        parent::boot();

        // When a new homework is created, send SMS notifications if enabled
        static::created(function ($homework) {
            if ($homework->notify_parents) {
                \App\Filament\Resources\HomeworkResource::sendSmsNotifications($homework);
            }
        });
    }
}
