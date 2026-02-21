<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Inquiry extends Model
{
    use HasFactory;

    protected $fillable = [
        'inquirer_name',
        'phone',
        'email',
        'inquiry_type',
        'subject',
        'message',
        'response',
        'status',
        'responded_by',
        'responded_at',
        'logged_by',
    ];

    protected $casts = [
        'responded_at' => 'datetime',
    ];

    public function responder(): BelongsTo
    {
        return $this->belongsTo(User::class, 'responded_by');
    }

    public function logger(): BelongsTo
    {
        return $this->belongsTo(User::class, 'logged_by');
    }
}
