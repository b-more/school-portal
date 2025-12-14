<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SmsCreditTransaction extends Model
{
    protected $fillable = [
        'type',
        'amount',
        'balance_before',
        'balance_after',
        'description',
        'reference',
        'sms_log_id',
        'performed_by',
        'metadata',
    ];

    protected $casts = [
        'amount' => 'integer',
        'balance_before' => 'integer',
        'balance_after' => 'integer',
        'metadata' => 'array',
    ];

    /**
     * Transaction types
     */
    const TYPE_CREDIT = 'credit';
    const TYPE_DEBIT = 'debit';
    const TYPE_ADJUSTMENT = 'adjustment';
    const TYPE_REFUND = 'refund';

    /**
     * Get the user who performed the transaction.
     */
    public function performedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'performed_by');
    }

    /**
     * Get the related SMS log.
     */
    public function smsLog(): BelongsTo
    {
        return $this->belongsTo(SmsLog::class, 'sms_log_id');
    }

    /**
     * Scope for credit transactions.
     */
    public function scopeCredits($query)
    {
        return $query->where('type', self::TYPE_CREDIT);
    }

    /**
     * Scope for debit transactions.
     */
    public function scopeDebits($query)
    {
        return $query->where('type', self::TYPE_DEBIT);
    }

    /**
     * Scope for today's transactions.
     */
    public function scopeToday($query)
    {
        return $query->whereDate('created_at', today());
    }

    /**
     * Scope for this month's transactions.
     */
    public function scopeThisMonth($query)
    {
        return $query->whereMonth('created_at', now()->month)
                     ->whereYear('created_at', now()->year);
    }

    /**
     * Get formatted type label.
     */
    public function getTypeLabelAttribute(): string
    {
        return match($this->type) {
            self::TYPE_CREDIT => 'Top Up',
            self::TYPE_DEBIT => 'SMS Sent',
            self::TYPE_ADJUSTMENT => 'Adjustment',
            self::TYPE_REFUND => 'Refund',
            default => ucfirst($this->type),
        };
    }

    /**
     * Get type color for display.
     */
    public function getTypeColorAttribute(): string
    {
        return match($this->type) {
            self::TYPE_CREDIT => 'success',
            self::TYPE_DEBIT => 'danger',
            self::TYPE_ADJUSTMENT => 'warning',
            self::TYPE_REFUND => 'info',
            default => 'gray',
        };
    }
}
