<?php

namespace App\Models\Accounting;

use App\Models\AcademicYear;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class JournalEntry extends Model
{
    protected $fillable = [
        'entry_number',
        'entry_date',
        'reference_type',
        'reference_id',
        'description',
        'notes',
        'total_debit',
        'total_credit',
        'status',
        'posted_by',
        'posted_at',
        'voided_by',
        'voided_at',
        'void_reason',
        'academic_year_id',
        'created_by',
    ];

    protected $casts = [
        'entry_date' => 'date',
        'posted_at' => 'datetime',
        'voided_at' => 'datetime',
        'total_debit' => 'decimal:2',
        'total_credit' => 'decimal:2',
    ];

    public const STATUS_DRAFT = 'draft';
    public const STATUS_POSTED = 'posted';
    public const STATUS_VOID = 'void';

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($entry) {
            if (empty($entry->entry_number)) {
                $entry->entry_number = static::generateEntryNumber();
            }
        });
    }

    public static function generateEntryNumber(): string
    {
        $prefix = 'JE';
        $year = date('Y');
        $month = date('m');

        $lastEntry = static::whereYear('created_at', $year)
            ->whereMonth('created_at', $month)
            ->orderBy('id', 'desc')
            ->first();

        $sequence = $lastEntry ? ((int) substr($lastEntry->entry_number, -5)) + 1 : 1;

        return $prefix . $year . $month . str_pad($sequence, 5, '0', STR_PAD_LEFT);
    }

    public function lines(): HasMany
    {
        return $this->hasMany(JournalEntryLine::class)->orderBy('sort_order');
    }

    public function reference(): MorphTo
    {
        return $this->morphTo('reference', 'reference_type', 'reference_id');
    }

    public function academicYear(): BelongsTo
    {
        return $this->belongsTo(AcademicYear::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function postedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'posted_by');
    }

    public function voidedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'voided_by');
    }

    public function scopeDraft($query)
    {
        return $query->where('status', self::STATUS_DRAFT);
    }

    public function scopePosted($query)
    {
        return $query->where('status', self::STATUS_POSTED);
    }

    public function scopeVoid($query)
    {
        return $query->where('status', self::STATUS_VOID);
    }

    public function scopeDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('entry_date', [$startDate, $endDate]);
    }

    public function isBalanced(): bool
    {
        return bccomp($this->total_debit, $this->total_credit, 2) === 0;
    }

    public function isDraft(): bool
    {
        return $this->status === self::STATUS_DRAFT;
    }

    public function isPosted(): bool
    {
        return $this->status === self::STATUS_POSTED;
    }

    public function isVoid(): bool
    {
        return $this->status === self::STATUS_VOID;
    }

    public function calculateTotals(): void
    {
        $this->total_debit = $this->lines()->sum('debit_amount');
        $this->total_credit = $this->lines()->sum('credit_amount');
        $this->save();
    }

    public function post(int $userId): bool
    {
        if (!$this->isDraft() || !$this->isBalanced()) {
            return false;
        }

        $this->status = self::STATUS_POSTED;
        $this->posted_by = $userId;
        $this->posted_at = now();
        $this->save();

        // Update account balances
        foreach ($this->lines as $line) {
            $line->account->updateBalance($line->debit_amount, $line->credit_amount);
        }

        return true;
    }

    public function void(int $userId, string $reason): bool
    {
        if (!$this->isPosted()) {
            return false;
        }

        // Reverse account balances
        foreach ($this->lines as $line) {
            $line->account->updateBalance(-$line->debit_amount, -$line->credit_amount);
        }

        $this->status = self::STATUS_VOID;
        $this->voided_by = $userId;
        $this->voided_at = now();
        $this->void_reason = $reason;
        $this->save();

        return true;
    }
}
