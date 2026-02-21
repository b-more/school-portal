<?php

namespace App\Models\Accounting;

use App\Models\AcademicYear;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class IncomeRecord extends Model
{
    protected $fillable = [
        'income_number',
        'income_date',
        'account_id',
        'description',
        'amount',
        'payment_method',
        'reference',
        'bank_account_id',
        'payer_name',
        'payer_contact',
        'source_type',
        'source_id',
        'journal_entry_id',
        'academic_year_id',
        'notes',
        'created_by',
    ];

    protected $casts = [
        'income_date' => 'date',
        'amount' => 'decimal:2',
    ];

    public const PAYMENT_METHODS = [
        'cash' => 'Cash',
        'bank_transfer' => 'Bank Transfer',
        'cheque' => 'Cheque',
        'mobile_money' => 'Mobile Money',
        'online' => 'Online Payment',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($income) {
            if (empty($income->income_number)) {
                $income->income_number = static::generateIncomeNumber();
            }
        });
    }

    public static function generateIncomeNumber(): string
    {
        $prefix = 'INC';
        $year = date('Y');
        $month = date('m');

        $lastIncome = static::whereYear('created_at', $year)
            ->whereMonth('created_at', $month)
            ->orderBy('id', 'desc')
            ->first();

        $sequence = $lastIncome ? ((int) substr($lastIncome->income_number, -5)) + 1 : 1;

        return $prefix . $year . $month . str_pad($sequence, 5, '0', STR_PAD_LEFT);
    }

    public function account(): BelongsTo
    {
        return $this->belongsTo(ChartOfAccount::class, 'account_id');
    }

    public function bankAccount(): BelongsTo
    {
        return $this->belongsTo(BankAccount::class);
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

    public function source(): MorphTo
    {
        return $this->morphTo('source', 'source_type', 'source_id');
    }

    public function scopeDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('income_date', [$startDate, $endDate]);
    }

    public function scopeOfAccount($query, int $accountId)
    {
        return $query->where('account_id', $accountId);
    }

    public function scopeFromSource($query, string $sourceType)
    {
        return $query->where('source_type', $sourceType);
    }
}
