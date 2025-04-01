<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class UserCredential extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'username',
        'password', // This will store a temporary plain-text password
        'is_sent',
        'sent_at',
        'delivery_method',
        'is_retrieved',
        'retrieved_at',
        'expires_at',
    ];

    protected $casts = [
        'is_sent' => 'boolean',
        'is_retrieved' => 'boolean',
        'sent_at' => 'datetime',
        'retrieved_at' => 'datetime',
        'expires_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Automatically expire credentials after a certain period
    public static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            // Set expiration date to 7 days from creation if not set
            if (!$model->expires_at) {
                $model->expires_at = now()->addDays(7);
            }
        });
    }
}
