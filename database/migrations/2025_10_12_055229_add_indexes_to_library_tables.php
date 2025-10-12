<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Add indexes to books table for better query performance
        Schema::table('books', function (Blueprint $table) {
            if (! $this->indexExists('books', 'books_is_active_index')) {
                $table->index('is_active');
            }
            if (! $this->indexExists('books', 'books_available_copies_index')) {
                $table->index('available_copies');
            }
            if (! $this->indexExists('books', 'books_category_index')) {
                $table->index('category');
            }
        });

        // Add indexes to book_loans table for better query performance
        // Note: student_id, book_id, status, due_date already indexed in create table
        Schema::table('book_loans', function (Blueprint $table) {
            if (! $this->indexExists('book_loans', 'book_loans_lent_date_index')) {
                $table->index('lent_date');
            }
            if (! $this->indexExists('book_loans', 'book_loans_returned_at_index')) {
                $table->index('returned_at');
            }
            if (! $this->indexExists('book_loans', 'book_loans_fine_paid_fine_amount_index')) {
                $table->index(['fine_paid', 'fine_amount']); // Composite index for fine queries
            }
            if (! $this->indexExists('book_loans', 'book_loans_status_due_date_index')) {
                $table->index(['status', 'due_date']); // Composite index for overdue queries
            }
            if (! $this->indexExists('book_loans', 'book_loans_student_id_status_index')) {
                $table->index(['student_id', 'status']); // Composite index for student clearance
            }
        });
    }

    /**
     * Check if an index exists on a table
     */
    private function indexExists(string $table, string $index): bool
    {
        $connection = Schema::getConnection();
        $databaseName = $connection->getDatabaseName();

        $result = $connection->select(
            'SELECT COUNT(*) as count FROM information_schema.statistics
             WHERE table_schema = ? AND table_name = ? AND index_name = ?',
            [$databaseName, $table, $index]
        );

        return $result[0]->count > 0;
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('books', function (Blueprint $table) {
            if ($this->indexExists('books', 'books_is_active_index')) {
                $table->dropIndex(['is_active']);
            }
            if ($this->indexExists('books', 'books_available_copies_index')) {
                $table->dropIndex(['available_copies']);
            }
            if ($this->indexExists('books', 'books_category_index')) {
                $table->dropIndex(['category']);
            }
        });

        Schema::table('book_loans', function (Blueprint $table) {
            if ($this->indexExists('book_loans', 'book_loans_lent_date_index')) {
                $table->dropIndex(['lent_date']);
            }
            if ($this->indexExists('book_loans', 'book_loans_returned_at_index')) {
                $table->dropIndex(['returned_at']);
            }
            if ($this->indexExists('book_loans', 'book_loans_fine_paid_fine_amount_index')) {
                $table->dropIndex(['fine_paid', 'fine_amount']);
            }
            if ($this->indexExists('book_loans', 'book_loans_status_due_date_index')) {
                $table->dropIndex(['status', 'due_date']);
            }
            if ($this->indexExists('book_loans', 'book_loans_student_id_status_index')) {
                $table->dropIndex(['student_id', 'status']);
            }
        });
    }
};
