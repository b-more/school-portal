<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Event extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'description',
        'slug',
        'start_date',
        'end_date',
        'location',
        'image',
        'category',
        'status',
        'organizer_id',
        'notify_parents',
        'target_grades',
        'sms_message',
    ];

    protected $casts = [
        'start_date' => 'datetime',
        'end_date' => 'datetime',
        'notify_parents' => 'boolean',
        'target_grades' => 'array',
    ];

    public function organizer(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'organizer_id');
    }

    /**
     * Get the SMS logs associated with this event.
     */
    public function smsLogs(): HasMany
    {
        return $this->hasMany(SmsLog::class, 'reference_id')
            ->where('message_type', 'event_notification');
    }

    /**
     * Boot the model.
     */
    protected static function boot()
    {
        parent::boot();

        // When a new event is created, send SMS notifications if enabled
        static::created(function ($event) {
            if ($event->notify_parents) {
                app(\App\Filament\Resources\EventResource::class)->sendEventNotifications($event);
            }
        });
    }
}
