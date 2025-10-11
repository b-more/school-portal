<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Book extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'isbn',
        'title',
        'author',
        'publisher',
        'publication_year',
        'category',
        'description',
        'total_copies',
        'available_copies',
        'shelf_location',
        'price',
        'language',
        'cover_image',
        'is_active',
    ];

    protected $casts = [
        'publication_year' => 'integer',
        'total_copies' => 'integer',
        'available_copies' => 'integer',
        'price' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    /**
     * Get all loans for this book
     */
    public function loans(): HasMany
    {
        return $this->hasMany(BookLoan::class);
    }

    /**
     * Get active loans for this book
     */
    public function activeLoans(): HasMany
    {
        return $this->hasMany(BookLoan::class)->whereNull('returned_at');
    }

    /**
     * Check if book is available for lending
     */
    public function isAvailable(): bool
    {
        return $this->is_active && $this->available_copies > 0;
    }

    /**
     * Decrease available copies when lending
     */
    public function decrementAvailableCopies(): void
    {
        $this->decrement('available_copies');
    }

    /**
     * Increase available copies when returned
     */
    public function incrementAvailableCopies(): void
    {
        if ($this->available_copies < $this->total_copies) {
            $this->increment('available_copies');
        }
    }
}
