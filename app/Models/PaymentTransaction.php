<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;

class PaymentTransaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'student_fee_id',
        'amount',
        'type',
        'reference_number',
        'external_reference',
        'payment_method',
        'metadata',
        'notes',
        'status',
        'processed_by',
        'transaction_date',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'metadata' => 'array',
        'transaction_date' => 'datetime',
    ];

    /**
     * Boot method for model events
     */
    protected static function boot()
    {
        parent::boot();

        // Generate reference number if not provided
        static::creating(function ($transaction) {
            if (!$transaction->reference_number) {
                $transaction->reference_number = $transaction->generateReferenceNumber();
            }
        });
    }

    /**
     * Generate unique reference number
     */
    private function generateReferenceNumber(): string
    {
        $prefix = match($this->type) {
            'payment' => 'PAY',
            'refund' => 'REF',
            'adjustment' => 'ADJ',
            'balance_forward' => 'BF',
            'overpayment' => 'OVP',
            'credit_applied' => 'CRD',
            default => 'TXN'
        };

        do {
            $reference = $prefix . '-' . date('Y') . '-' . str_pad(mt_rand(1, 99999), 5, '0', STR_PAD_LEFT);
        } while (static::where('reference_number', $reference)->exists());

        return $reference;
    }

    /**
     * Get the student fee that owns this transaction
     */
    public function studentFee(): BelongsTo
    {
        return $this->belongsTo(StudentFee::class);
    }

    /**
     * Get the user who processed this transaction
     */
    public function processedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'processed_by');
    }

    /**
     * Get the student through the student fee
     */
    public function student()
    {
        return $this->hasOneThrough(Student::class, StudentFee::class, 'id', 'id', 'student_fee_id', 'student_id');
    }

    /**
     * Scope for filtering by transaction type
     */
    public function scopeOfType($query, string $type)
    {
        return $query->where('type', $type);
    }

    /**
     * Scope for filtering by date range
     */
    public function scopeBetweenDates($query, Carbon $startDate, Carbon $endDate)
    {
        return $query->whereBetween('transaction_date', [$startDate, $endDate]);
    }

    /**
     * Scope for filtering by payment method
     */
    public function scopeByPaymentMethod($query, string $method)
    {
        return $query->where('payment_method', $method);
    }

    /**
     * Scope for successful transactions only
     */
    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    /**
     * Get formatted transaction type
     */
    public function getFormattedTypeAttribute(): string
    {
        return match($this->type) {
            'payment' => 'Payment',
            'refund' => 'Refund',
            'adjustment' => 'Adjustment',
            'balance_forward' => 'Balance Forward',
            'overpayment' => 'Overpayment',
            'credit_applied' => 'Credit Applied',
            default => 'Transaction'
        };
    }

    /**
     * Get formatted payment method
     */
    public function getFormattedPaymentMethodAttribute(): string
    {
        if (!$this->payment_method) return 'Not specified';

        return match($this->payment_method) {
            'mobile_money' => 'Mobile Money',
            'bank_transfer' => 'Bank Transfer',
            'credit_card' => 'Credit Card',
            'online_payment' => 'Online Payment',
            default => ucfirst(str_replace('_', ' ', $this->payment_method))
        };
    }

    /**
     * Check if transaction is a payment type
     */
    public function isPayment(): bool
    {
        return in_array($this->type, ['payment', 'balance_forward', 'credit_applied']);
    }

    /**
     * Check if transaction is a deduction type
     */
    public function isDeduction(): bool
    {
        return in_array($this->type, ['refund', 'adjustment']);
    }

    /**
     * Get transaction impact (positive for payments, negative for refunds)
     */
    public function getImpactAttribute(): float
    {
        return $this->isPayment() ? $this->amount : -$this->amount;
    }
}
