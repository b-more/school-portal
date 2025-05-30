<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ParentGuardian extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'email',
        'nrc',
        'nationality',
        'phone',
        'alternate_phone',
        'relationship',
        'occupation',
        'address',
        'user_id',
        'role_id',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function students(): HasMany
    {
        return $this->hasMany(Student::class);
    }

    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class);
    }
    public function getFullNameAttribute(): string
    {
        return $this->name;
    }
    public function getFullPhoneAttribute(): string
    {
        return $this->phone;
    }
}
