<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SmsLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'recipient',
        'message',
        'status',
        'message_type',
        'reference_id',
        'cost',
        'provider_reference',
        'error_message',
        'sent_by',
    ];

    /**
     * Get the user who sent the SMS.
     */
    public function sender(): BelongsTo
    {
        return $this->belongsTo(User::class, 'sent_by');
    }

    /**
     * Get the related homework if this is a homework notification.
     */
    public function homework(): BelongsTo
    {
        return $this->belongsTo(Homework::class, 'reference_id')
            ->when(fn ($query) => $query->where('message_type', 'homework_notification'));
    }

    /**
     * Get the related result if this is a result notification.
     */
    public function result(): BelongsTo
    {
        return $this->belongsTo(Result::class, 'reference_id')
            ->when(fn ($query) => $query->where('message_type', 'result_notification'));
    }

    /**
     * Get the related event if this is an event notification.
     */
    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class, 'reference_id')
            ->when(fn ($query) => $query->where('message_type', 'event_notification'));
    }

    /**
     * Get the total cost of SMS messages for a given period.
     */
    public static function getTotalCost($startDate = null, $endDate = null)
    {
        $query = self::query();

        if ($startDate) {
            $query->whereDate('created_at', '>=', $startDate);
        }

        if ($endDate) {
            $query->whereDate('created_at', '<=', $endDate);
        }

        return $query->sum('cost');
    }

    /**
     * Get count of SMS by status.
     */
    public static function getCountByStatus($status, $startDate = null, $endDate = null)
    {
        $query = self::where('status', $status);

        if ($startDate) {
            $query->whereDate('created_at', '>=', $startDate);
        }

        if ($endDate) {
            $query->whereDate('created_at', '<=', $endDate);
        }

        return $query->count();
    }
}
