<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Result extends Model
{
    use HasFactory;

    protected $fillable = [
        'student_id',
        'subject_id',
        'exam_type',
        'homework_id', // Added field
        'marks',
        'grade',
        'term',
        'year',
        'comment',
        'recorded_by',
        'notify_parent',
        'sms_message',
    ];

    /**
     * Check if this result is associated with a homework submission
     */
    public function hasHomeworkSubmission(): bool
    {
        return $this->exam_type === 'assignment' && $this->homework_id !== null;
    }

    protected $casts = [
        'marks' => 'decimal:2',
        'year' => 'integer',
        'notify_parent' => 'boolean',
    ];

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class);
    }

    public function recordedBy(): BelongsTo
{
    return $this->belongsTo(Teacher::class, 'recorded_by');
}

    /**
     * Get the homework associated with this result (if applicable).
     */
    public function homework(): BelongsTo
    {
        return $this->belongsTo(Homework::class);
    }

    /**
     * Get the SMS logs associated with this result.
     */
    public function smsLogs(): HasMany
    {
        return $this->hasMany(SmsLog::class, 'reference_id')
            ->where('message_type', 'result_notification');
    }

    /**
     * Boot the model.
     */
    protected static function boot()
    {
        parent::boot();

        // When a new result is created, send SMS notification if enabled
        static::created(function ($result) {
            if ($result->notify_parent) {
                app(\App\Filament\Resources\ResultResource::class)->sendResultNotification($result);
            }
        });
    }
}
