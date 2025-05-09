<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AcademicYear extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'start_date',
        'end_date',
        'description',
        'is_active',
        'number_of_terms',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'is_active' => 'boolean',
    ];

    public function terms(): HasMany
    {
        return $this->hasMany(Term::class);
    }

    public function feeStructures(): HasMany
    {
        return $this->hasMany(FeeStructure::class);
    }

    // Make sure only one academic year can be active at a time
    protected static function boot()
    {
        parent::boot();

        static::saving(function ($model) {
            if ($model->is_active) {
                static::where('id', '!=', $model->id)
                    ->where('is_active', true)
                    ->update(['is_active' => false]);
            }
        });
    }

    // Automatically create terms when creating academic year
    public static function createWithTerms(array $data): self
    {
        $academicYear = self::create($data);

        // Create terms based on number_of_terms
        $termCount = $data['number_of_terms'] ?? 3;
        $startDate = $academicYear->start_date;
        $endDate = $academicYear->end_date;

        // Calculate date ranges for terms
        $interval = $startDate->diffInDays($endDate) / $termCount;

        for ($i = 1; $i <= $termCount; $i++) {
            $termStartDate = $startDate->copy()->addDays(($i-1) * $interval);
            $termEndDate = $startDate->copy()->addDays($i * $interval)->subDay();

            if ($i === $termCount) {
                $termEndDate = $endDate; // Make sure the last term ends on the academic year end date
            }

            $academicYear->terms()->create([
                'name' => 'Term ' . $i,
                'start_date' => $termStartDate,
                'end_date' => $termEndDate,
                'is_active' => ($i === 1 && $academicYear->is_active), // Make first term active if academic year is active
            ]);
        }

        return $academicYear;
    }

    // Get current academic year
    public static function current()
    {
        return self::where('is_active', true)->first();
    }
}
