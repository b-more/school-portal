<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class QrPayment extends Model
{
    protected $fillable = [
        'qr_code',
        'payment_reference',
        'amount',
        'customer_mobile',
        'student_id',
        'student_fee_id',
        'status',
        'cgrate_payment_id',
        'response_message',
        'response_code',
        'initiated_at',
        'completed_at',
        'expires_at',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'initiated_at' => 'datetime',
        'completed_at' => 'datetime',
        'expires_at' => 'datetime',
    ];

    /**
     * Get the student that owns the QR payment
     */
    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    /**
     * Get the student fee associated with this payment
     */
    public function studentFee(): BelongsTo
    {
        return $this->belongsTo(StudentFee::class);
    }

    /**
     * Check if payment is expired
     */
    public function isExpired(): bool
    {
        return $this->expires_at && $this->expires_at->isPast();
    }

    /**
     * Check if payment is pending
     */
    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    /**
     * Check if payment is completed
     */
    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }

    /**
     * Generate QR code data
     */
    public static function generateQrCode(string $paymentReference, float $amount, string $mobile): string
    {
        // Format: PAYMENT_REF|AMOUNT|MOBILE
        return base64_encode("{$paymentReference}|{$amount}|{$mobile}");
    }
}
