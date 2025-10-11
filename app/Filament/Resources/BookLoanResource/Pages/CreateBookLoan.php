<?php

namespace App\Filament\Resources\BookLoanResource\Pages;

use App\Filament\Resources\BookLoanResource;
use App\Models\Book;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;

class CreateBookLoan extends CreateRecord
{
    protected static string $resource = BookLoanResource::class;

    protected function handleRecordCreation(array $data): Model
    {
        // Set the lent_by field to the current user
        $data['lent_by'] = auth()->id();
        $data['status'] = 'active';

        // Create the loan record
        $loan = static::getModel()::create($data);

        // Decrease available copies of the book
        $book = Book::find($data['book_id']);
        if ($book) {
            $book->decrementAvailableCopies();

            Notification::make()
                ->title('Book Loaned Successfully')
                ->body("'{$book->title}' has been loaned to {$loan->student->name}. Due date: {$loan->due_date->format('M d, Y')}")
                ->success()
                ->send();
        }

        return $loan;
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
