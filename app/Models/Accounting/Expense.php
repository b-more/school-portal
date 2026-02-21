<?php

namespace App\Models\Accounting;

use App\Models\AcademicYear;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Expense extends Model
{
    protected $fillable = [
        'expense_number',
        'expense_date',
        'vendor_id',
        'expense_category_id',
        'description',
        'amount',
        'tax_amount',
        'total_amount',
        'payment_status',
        'amount_paid',
        'payment_method',
        'payment_reference',
        'bank_account_id',
        'approved_by',
        'approved_at',
        'journal_entry_id',
        'academic_year_id',
        'attachments',
        'notes',
        'created_by',
    ];

    protected $casts = [
        'expense_date' => 'date',
        'amount' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'amount_paid' => 'decimal:2',
        'approved_at' => 'datetime',
        'attachments' => 'array',
    ];

    public const STATUS_UNPAID = 'unpaid';
    public const STATUS_PARTIAL = 'partial';
    public const STATUS_PAID = 'paid';

    public const PAYMENT_METHODS = [
        'cash' => 'Cash',
        'bank_transfer' => 'Bank Transfer',
        'cheque' => 'Cheque',
        'mobile_money' => 'Mobile Money',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($expense) {
            if (empty($expense->expense_number)) {
                $expense->expense_number = static::generateExpenseNumber();
            }
            $expense->total_amount = $expense->amount + ($expense->tax_amount ?? 0);
        });

        static::updating(function ($expense) {
            $expense->total_amount = $expense->amount + ($expense->tax_amount ?? 0);
            $expense->updatePaymentStatus();
        });
    }

    public static function generateExpenseNumber(): string
    {
        $prefix = 'EXP';
        $year = date('Y');
        $month = date('m');

        $lastExpense = static::whereYear('created_at', $year)
            ->whereMonth('created_at', $month)
            ->orderBy('id', 'desc')
            ->first();

        $sequence = $lastExpense ? ((int) substr($lastExpense->expense_number, -5)) + 1 : 1;

        return $prefix . $year . $month . str_pad($sequence, 5, '0', STR_PAD_LEFT);
    }

    public function vendor(): BelongsTo
    {
        return $this->belongsTo(Vendor::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(ExpenseCategory::class, 'expense_category_id');
    }

    public function bankAccount(): BelongsTo
    {
        return $this->belongsTo(BankAccount::class);
    }

    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function journalEntry(): BelongsTo
    {
        return $this->belongsTo(JournalEntry::class);
    }

    public function academicYear(): BelongsTo
    {
        return $this->belongsTo(AcademicYear::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function paymentVouchers(): HasMany
    {
        return $this->hasMany(PaymentVoucher::class);
    }

    public function scopeUnpaid($query)
    {
        return $query->where('payment_status', self::STATUS_UNPAID);
    }

    public function scopePartial($query)
    {
        return $query->where('payment_status', self::STATUS_PARTIAL);
    }

    public function scopePaid($query)
    {
        return $query->where('payment_status', self::STATUS_PAID);
    }

    public function scopeDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('expense_date', [$startDate, $endDate]);
    }

    public function scopeOfCategory($query, int $categoryId)
    {
        return $query->where('expense_category_id', $categoryId);
    }

    public function getBalanceAttribute(): float
    {
        return $this->total_amount - $this->amount_paid;
    }

    public function isUnpaid(): bool
    {
        return $this->payment_status === self::STATUS_UNPAID;
    }

    public function isPartiallyPaid(): bool
    {
        return $this->payment_status === self::STATUS_PARTIAL;
    }

    public function isPaid(): bool
    {
        return $this->payment_status === self::STATUS_PAID;
    }

    public function updatePaymentStatus(): void
    {
        if ($this->amount_paid <= 0) {
            $this->payment_status = self::STATUS_UNPAID;
        } elseif ($this->amount_paid >= $this->total_amount) {
            $this->payment_status = self::STATUS_PAID;
        } else {
            $this->payment_status = self::STATUS_PARTIAL;
        }
    }

    public function recordPayment(float $amount, string $method, string $reference = null): void
    {
        $this->amount_paid += $amount;
        $this->payment_method = $method;
        $this->payment_reference = $reference;
        $this->updatePaymentStatus();
        $this->save();
    }
}
