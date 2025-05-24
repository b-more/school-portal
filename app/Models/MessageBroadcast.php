<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MessageBroadcast extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'message',
        'filters',
        'total_recipients',
        'sent_count',
        'failed_count',
        'total_cost',
        'status',
        'started_at',
        'completed_at',
        'created_by',
    ];

    protected $casts = [
        'filters' => 'array',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function getCompletionPercentageAttribute(): int
    {
        if ($this->total_recipients === 0) {
            return 0;
        }

        return round(($this->sent_count + $this->failed_count) / $this->total_recipients * 100);
    }
}
