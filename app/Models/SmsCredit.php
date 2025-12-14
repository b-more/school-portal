<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SmsCredit extends Model
{
    protected $fillable = [
        'balance',
        'cost_per_sms',
        'low_balance_threshold',
        'allow_negative_balance',
        'is_active',
        'last_topped_up_at',
        'last_topped_up_by',
    ];

    protected $casts = [
        'balance' => 'integer',
        'cost_per_sms' => 'integer',
        'low_balance_threshold' => 'integer',
        'allow_negative_balance' => 'boolean',
        'is_active' => 'boolean',
        'last_topped_up_at' => 'datetime',
    ];

    /**
     * Get the user who last topped up the credit.
     */
    public function lastToppedUpBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'last_topped_up_by');
    }

    /**
     * Get the credit transactions.
     */
    public function transactions(): HasMany
    {
        return $this->hasMany(SmsCreditTransaction::class, 'sms_credit_id');
    }

    /**
     * Get the singleton instance of SMS credit.
     */
    public static function getInstance(): self
    {
        return static::firstOrCreate(
            ['id' => 1],
            [
                'balance' => 0,
                'cost_per_sms' => 1, // 1 credit per SMS part
                'low_balance_threshold' => 50,
                'allow_negative_balance' => false,
                'is_active' => true,
            ]
        );
    }

    /**
     * Calculate the credits needed for a message based on character count.
     */
    public function calculateMessageCost(string $message): array
    {
        $length = strlen($message);
        $parts = $this->calculateMessageParts($message);
        $credits = $parts * $this->cost_per_sms; // 1 credit per part by default

        return [
            'length' => $length,
            'parts' => $parts,
            'credits_per_part' => $this->cost_per_sms,
            'total_credits' => $credits,
            'total_cost' => $credits, // Alias for compatibility
        ];
    }

    /**
     * Calculate the number of SMS parts for a message.
     * Standard SMS: 160 chars for single, 153 chars per part for multipart.
     */
    public function calculateMessageParts(string $message): int
    {
        $length = strlen($message);

        if ($length <= 160) {
            return 1;
        }

        // For multipart messages, each part can hold 153 characters
        // due to UDH (User Data Header) overhead
        return (int) ceil($length / 153);
    }

    /**
     * Check if there's sufficient balance for a message.
     */
    public function hasSufficientBalance(string $message): bool
    {
        if (!$this->is_active) {
            return false;
        }

        if ($this->allow_negative_balance) {
            return true;
        }

        $cost = $this->calculateMessageCost($message)['total_cost'];
        return $this->balance >= $cost;
    }

    /**
     * Check if balance is below threshold.
     */
    public function isBalanceLow(): bool
    {
        return $this->balance <= $this->low_balance_threshold;
    }

    /**
     * Get estimated SMS count that can be sent with current balance.
     */
    public function getEstimatedSmsCount(): int
    {
        if ($this->cost_per_sms <= 0) {
            return 0;
        }

        return (int) floor($this->balance / $this->cost_per_sms);
    }
}
