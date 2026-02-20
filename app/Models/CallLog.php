<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CallLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'caller_name',
        'phone',
        'call_type',
        'purpose',
        'notes',
        'duration_minutes',
        'follow_up_required',
        'follow_up_date',
        'follow_up_notes',
        'status',
        'logged_by',
    ];

    protected $casts = [
        'follow_up_required' => 'boolean',
        'follow_up_date' => 'date',
    ];

    public function logger(): BelongsTo
    {
        return $this->belongsTo(User::class, 'logged_by');
    }
}
