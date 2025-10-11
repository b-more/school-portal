<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class BookLoan extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'book_id',
        'student_id',
        'lent_by',
        'returned_to',
        'lent_date',
        'due_date',
        'returned_at',
        'status',
        'fine_amount',
        'fine_paid',
        'notes',
        'condition_on_loan',
        'condition_on_return',
    ];

    protected $casts = [
        'lent_date' => 'date',
        'due_date' => 'date',
        'returned_at' => 'date',
        'fine_amount' => 'decimal:2',
        'fine_paid' => 'boolean',
    ];

    /**
     * Get the book that was loaned
     */
    public function book(): BelongsTo
    {
        return $this->belongsTo(Book::class);
    }

    /**
     * Get the student who borrowed the book
     */
    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    /**
     * Get the user who lent the book
     */
    public function lentBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'lent_by');
    }

    /**
     * Get the user who received the returned book
     */
    public function returnedTo(): BelongsTo
    {
        return $this->belongsTo(User::class, 'returned_to');
    }

    /**
     * Check if the loan is overdue
     */
    public function isOverdue(): bool
    {
        return $this->status === 'active' && $this->due_date < now();
    }

    /**
     * Calculate days overdue
     */
    public function daysOverdue(): int
    {
        if (! $this->isOverdue()) {
            return 0;
        }

        return now()->diffInDays($this->due_date);
    }

    /**
     * Calculate fine for overdue book
     */
    public function calculateFine(float $dailyFineRate = 1.0): float
    {
        if (! $this->isOverdue()) {
            return 0;
        }

        return $this->daysOverdue() * $dailyFineRate;
    }

    /**
     * Mark book as returned
     */
    public function markAsReturned(int $returnedToUserId, ?string $condition = null): void
    {
        $this->update([
            'returned_at' => now(),
            'returned_to' => $returnedToUserId,
            'status' => 'returned',
            'condition_on_return' => $condition,
        ]);

        // Increment available copies
        $this->book->incrementAvailableCopies();
    }

    /**
     * Update overdue status if needed
     */
    public function updateOverdueStatus(): void
    {
        if ($this->status === 'active' && $this->due_date < now()) {
            $this->update(['status' => 'overdue']);
        }
    }
}
