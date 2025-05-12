<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Term extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'start_date',
        'end_date',
        'is_active',
        'is_current',
        'academic_year_id',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'is_active' => 'boolean',
    ];

    public function academicYear(): BelongsTo
    {
        return $this->belongsTo(AcademicYear::class);
    }

    public function feeStructures(): HasMany
    {
        return $this->hasMany(FeeStructure::class);
    }

    // Make sure only one term can be active at a time
    protected static function boot()
    {
        parent::boot();

        static::saving(function ($model) {
            if ($model->is_active) {
                static::where('id', '!=', $model->id)
                    ->where('academic_year_id', $model->academic_year_id)
                    ->where('is_active', true)
                    ->update(['is_active' => false]);
            }
        });
    }

    // Get current term
    public static function current()
    {
        return self::where('is_active', true)->first();
    }
}
