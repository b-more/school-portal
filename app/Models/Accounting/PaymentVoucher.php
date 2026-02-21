<?php

namespace App\Models\Accounting;

use App\Models\User;
use App\Models\Employee;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PaymentVoucher extends Model
{
    protected $fillable = [
        'voucher_number',
        'voucher_date',
        'payee_type',
        'payee_id',
        'payee_name',
        'description',
        'amount',
        'amount_in_words',
        'payment_method',
        'bank_account_id',
        'cheque_number',
        'status',
        'prepared_by',
        'approved_by',
        'paid_by',
        'approved_at',
        'paid_at',
        'expense_id',
        'journal_entry_id',
        'notes',
    ];

    protected $casts = [
        'voucher_date' => 'date',
        'amount' => 'decimal:2',
        'approved_at' => 'datetime',
        'paid_at' => 'datetime',
    ];

    public const STATUS_PENDING = 'pending';
    public const STATUS_APPROVED = 'approved';
    public const STATUS_PAID = 'paid';
    public const STATUS_CANCELLED = 'cancelled';

    public const PAYEE_TYPE_VENDOR = 'vendor';
    public const PAYEE_TYPE_EMPLOYEE = 'employee';
    public const PAYEE_TYPE_OTHER = 'other';

    public const PAYMENT_METHODS = [
        'cash' => 'Cash',
        'bank_transfer' => 'Bank Transfer',
        'cheque' => 'Cheque',
        'mobile_money' => 'Mobile Money',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($voucher) {
            if (empty($voucher->voucher_number)) {
                $voucher->voucher_number = static::generateVoucherNumber();
            }
        });
    }

    public static function generateVoucherNumber(): string
    {
        $prefix = 'PV';
        $year = date('Y');
        $month = date('m');

        $lastVoucher = static::whereYear('created_at', $year)
            ->whereMonth('created_at', $month)
            ->orderBy('id', 'desc')
            ->first();

        $sequence = $lastVoucher ? ((int) substr($lastVoucher->voucher_number, -5)) + 1 : 1;

        return $prefix . $year . $month . str_pad($sequence, 5, '0', STR_PAD_LEFT);
    }

    public function payee()
    {
        if ($this->payee_type === self::PAYEE_TYPE_VENDOR) {
            return $this->belongsTo(Vendor::class, 'payee_id');
        } elseif ($this->payee_type === self::PAYEE_TYPE_EMPLOYEE) {
            return $this->belongsTo(Employee::class, 'payee_id');
        }
        return null;
    }

    public function bankAccount(): BelongsTo
    {
        return $this->belongsTo(BankAccount::class);
    }

    public function expense(): BelongsTo
    {
        return $this->belongsTo(Expense::class);
    }

    public function journalEntry(): BelongsTo
    {
        return $this->belongsTo(JournalEntry::class);
    }

    public function preparedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'prepared_by');
    }

    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function paidBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'paid_by');
    }

    public function scopePending($query)
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    public function scopeApproved($query)
    {
        return $query->where('status', self::STATUS_APPROVED);
    }

    public function scopePaid($query)
    {
        return $query->where('status', self::STATUS_PAID);
    }

    public function scopeCancelled($query)
    {
        return $query->where('status', self::STATUS_CANCELLED);
    }

    public function isPending(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }

    public function isApproved(): bool
    {
        return $this->status === self::STATUS_APPROVED;
    }

    public function isPaid(): bool
    {
        return $this->status === self::STATUS_PAID;
    }

    public function isCancelled(): bool
    {
        return $this->status === self::STATUS_CANCELLED;
    }

    public function approve(int $userId): bool
    {
        if (!$this->isPending()) {
            return false;
        }

        $this->status = self::STATUS_APPROVED;
        $this->approved_by = $userId;
        $this->approved_at = now();
        $this->save();

        return true;
    }

    public function markAsPaid(int $userId): bool
    {
        if (!$this->isApproved()) {
            return false;
        }

        $this->status = self::STATUS_PAID;
        $this->paid_by = $userId;
        $this->paid_at = now();
        $this->save();

        // Update linked expense if exists
        if ($this->expense) {
            $this->expense->recordPayment(
                $this->amount,
                $this->payment_method,
                $this->voucher_number
            );
        }

        return true;
    }

    public function cancel(): bool
    {
        if ($this->isPaid()) {
            return false;
        }

        $this->status = self::STATUS_CANCELLED;
        $this->save();

        return true;
    }

    public static function numberToWords(float $number): string
    {
        $ones = ['', 'One', 'Two', 'Three', 'Four', 'Five', 'Six', 'Seven', 'Eight', 'Nine', 'Ten', 'Eleven', 'Twelve', 'Thirteen', 'Fourteen', 'Fifteen', 'Sixteen', 'Seventeen', 'Eighteen', 'Nineteen'];
        $tens = ['', '', 'Twenty', 'Thirty', 'Forty', 'Fifty', 'Sixty', 'Seventy', 'Eighty', 'Ninety'];

        $number = number_format($number, 2, '.', '');
        $parts = explode('.', $number);
        $whole = (int) $parts[0];
        $decimal = (int) $parts[1];

        $words = '';

        if ($whole >= 1000000) {
            $words .= self::numberToWords(floor($whole / 1000000)) . ' Million ';
            $whole %= 1000000;
        }

        if ($whole >= 1000) {
            $words .= self::numberToWords(floor($whole / 1000)) . ' Thousand ';
            $whole %= 1000;
        }

        if ($whole >= 100) {
            $words .= $ones[floor($whole / 100)] . ' Hundred ';
            $whole %= 100;
        }

        if ($whole >= 20) {
            $words .= $tens[floor($whole / 10)] . ' ';
            $whole %= 10;
        }

        if ($whole > 0) {
            $words .= $ones[$whole] . ' ';
        }

        $words = trim($words);

        if ($decimal > 0) {
            $words .= ' and ' . $decimal . '/100';
        }

        return $words . ' Kwacha Only';
    }
}
