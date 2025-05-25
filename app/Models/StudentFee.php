<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class StudentFee extends Model
{
    use HasFactory;

    protected $fillable = [
        'student_id',
        'fee_structure_id',
        'academic_year_id',
        'term_id',
        'grade_id',
        'amount_paid',
        'balance',
        'payment_status',
        'payment_date',
        'receipt_number',
        'payment_method',
        'notes',
        'send_sms_notification',
    ];

    protected $casts = [
        'amount_paid' => 'decimal:2',
        'balance' => 'decimal:2',
        'payment_date' => 'date',
        'send_sms_notification' => 'boolean',
    ];

    /**
     * Boot method to handle model events
     */
    protected static function boot()
    {
        parent::boot();

        // Automatically calculate balance when saving
        static::saving(function ($studentFee) {
            if ($studentFee->feeStructure && $studentFee->isDirty('amount_paid')) {
                $totalFee = (float) $studentFee->feeStructure->total_fee;
                $amountPaid = (float) $studentFee->amount_paid;
                $studentFee->balance = max(0, $totalFee - $amountPaid);

                // Auto-set payment status
                if ($amountPaid <= 0) {
                    $studentFee->payment_status = 'unpaid';
                } elseif ($amountPaid >= $totalFee) {
                    $studentFee->payment_status = 'paid';
                    $studentFee->balance = 0;
                } else {
                    $studentFee->payment_status = 'partial';
                }
            }
        });

        // Load fee structure when creating
        static::created(function ($studentFee) {
            $studentFee->load('feeStructure');
        });
    }

    /**
     * Get the student that owns the fee.
     */
    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    /**
     * Get the fee structure associated with the fee.
     */
    public function feeStructure(): BelongsTo
    {
        return $this->belongsTo(FeeStructure::class);
    }

    /**
     * Get the academic year associated with the fee.
     */
    public function academicYear(): BelongsTo
    {
        return $this->belongsTo(AcademicYear::class);
    }

    /**
     * Get the term associated with the fee.
     */
    public function term(): BelongsTo
    {
        return $this->belongsTo(Term::class);
    }

    /**
     * Get the grade associated with the fee.
     */
    public function grade(): BelongsTo
    {
        return $this->belongsTo(Grade::class);
    }

    /**
     * Calculate the total fee amount from fee structure
     */
    public function getTotalFeeAttribute()
    {
        return $this->feeStructure?->total_fee ?? 0;
    }

    /**
     * Get the grade name from various sources
     */
    public function getGradeNameAttribute()
    {
        // Try to get grade name from fee structure first
        if ($this->feeStructure && $this->feeStructure->grade) {
            return $this->feeStructure->grade->name;
        }

        // Then try from the direct grade relationship
        if ($this->grade) {
            return $this->grade->name;
        }

        // Finally try from student's grade
        if ($this->student && $this->student->grade) {
            return $this->student->grade->name;
        }

        return 'Unknown Grade';
    }

    /**
     * Get the term name from various sources
     */
    public function getTermNameAttribute()
    {
        // Try to get term name from fee structure first
        if ($this->feeStructure && $this->feeStructure->term) {
            return $this->feeStructure->term->name;
        }

        // Then try from the direct term relationship
        if ($this->term) {
            return $this->term->name;
        }

        return 'Unknown Term';
    }

    /**
     * Get the academic year name from various sources
     */
    public function getAcademicYearNameAttribute()
    {
        // Try to get academic year name from fee structure first
        if ($this->feeStructure && $this->feeStructure->academicYear) {
            return $this->feeStructure->academicYear->name;
        }

        // Then try from the direct academic year relationship
        if ($this->academicYear) {
            return $this->academicYear->name;
        }

        return 'Unknown Academic Year';
    }

    /**
     * Check if the fee is fully paid
     */
    public function isFullyPaid(): bool
    {
        return $this->payment_status === 'paid' || $this->balance <= 0;
    }

    /**
     * Check if the fee has any payment
     */
    public function hasPayment(): bool
    {
        return $this->amount_paid > 0;
    }

    /**
     * Get formatted payment status
     */
    public function getFormattedPaymentStatusAttribute(): string
    {
        return match($this->payment_status) {
            'paid' => 'Fully Paid',
            'partial' => 'Partially Paid',
            'unpaid' => 'Unpaid',
            default => 'Unknown'
        };
    }

    /**
     * Scope to filter by payment status
     */
    public function scopeByPaymentStatus($query, $status)
    {
        return $query->where('payment_status', $status);
    }

    /**
     * Scope to filter by academic year
     */
    public function scopeByAcademicYear($query, $academicYearId)
    {
        return $query->where('academic_year_id', $academicYearId);
    }

    /**
     * Scope to filter by term
     */
    public function scopeByTerm($query, $termId)
    {
        return $query->where('term_id', $termId);
    }

    /**
     * Scope to filter by grade
     */
    public function scopeByGrade($query, $gradeId)
    {
        return $query->where('grade_id', $gradeId);
    }

    /**
     * Scope to get only unpaid fees
     */
    public function scopeUnpaid($query)
    {
        return $query->where('payment_status', 'unpaid');
    }

    /**
     * Scope to get only paid fees
     */
    public function scopePaid($query)
    {
        return $query->where('payment_status', 'paid');
    }

    /**
     * Scope to get fees with outstanding balance
     */
    public function scopeWithBalance($query)
    {
        return $query->where('balance', '>', 0);
    }

    /**
 * Get payment transactions for this fee record
 */
public function paymentTransactions(): HasMany
{
    return $this->hasMany(PaymentTransaction::class);
}

/**
 * Process payment and handle overpayments automatically
 */
public function processPayment(float $amount, array $paymentData = []): array
{
    $balanceService = app(BalanceForwardService::class);

    // Update payment amounts
    $this->amount_paid += $amount;
    $totalFee = $this->feeStructure->total_fee;
    $this->balance = max(0, $totalFee - $this->amount_paid);

    // Update payment status
    if ($this->amount_paid <= 0) {
        $this->payment_status = 'unpaid';
    } elseif ($this->amount_paid > $totalFee) {
        $this->payment_status = 'overpaid';
    } elseif ($this->amount_paid >= $totalFee) {
        $this->payment_status = 'paid';
        $this->balance = 0;
    } else {
        $this->payment_status = 'partial';
    }

    // Set payment details
    if ($amount > 0) {
        $this->payment_date = $paymentData['payment_date'] ?? now();
        $this->receipt_number = $paymentData['receipt_number'] ?? $this->generateReceiptNumber();
        $this->payment_method = $paymentData['payment_method'] ?? null;
    }

    $this->save();

    // Create transaction record
    $transaction = $this->paymentTransactions()->create([
        'amount' => $amount,
        'type' => 'payment',
        'payment_method' => $paymentData['payment_method'] ?? null,
        'notes' => $paymentData['notes'] ?? null,
        'metadata' => $paymentData['metadata'] ?? null,
        'processed_by' => auth()->id(),
        'transaction_date' => $paymentData['payment_date'] ?? now(),
    ]);

    $result = ['transaction' => $transaction];

    // Handle overpayment
    if ($this->payment_status === 'overpaid') {
        $overpayment = $this->amount_paid - $totalFee;
        $forwardResult = $balanceService->processOverpayment($this, $overpayment);
        $result['balance_forward'] = $forwardResult;
    }

    return $result;
}

/**
 * Generate receipt number
 */
private function generateReceiptNumber(): string
{
    do {
        $number = 'RCP-' . date('Y') . '-' . str_pad(mt_rand(1, 9999), 4, '0', STR_PAD_LEFT);
    } while (static::where('receipt_number', $number)->exists());

    return $number;
}

/**
 * Get payment timeline with all transactions
 */
public function getPaymentTimeline(): array
{
    $timeline = [];

    // Add fee creation
    $timeline[] = [
        'date' => $this->created_at,
        'type' => 'fee_created',
        'description' => 'Fee record created',
        'amount' => $this->feeStructure->total_fee,
        'running_balance' => $this->feeStructure->total_fee,
    ];

    // Add all payment transactions
    $runningBalance = $this->feeStructure->total_fee;
    foreach ($this->paymentTransactions()->orderBy('transaction_date')->get() as $transaction) {
        $runningBalance -= $transaction->impact;

        $timeline[] = [
            'date' => $transaction->transaction_date,
            'type' => $transaction->type,
            'description' => $transaction->formatted_type . ($transaction->notes ? ': ' . $transaction->notes : ''),
            'amount' => $transaction->amount,
            'payment_method' => $transaction->formatted_payment_method,
            'reference' => $transaction->reference_number,
            'running_balance' => max(0, $runningBalance),
        ];
    }

    return $timeline;
}
}
