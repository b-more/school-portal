<?php

namespace App\Filament\Resources;

use App\Constants\RoleConstants;
use App\Filament\Resources\BookLoanResource\Pages;
use App\Models\Book;
use App\Models\BookLoan;
use App\Models\Student;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class BookLoanResource extends Resource
{
    protected static ?string $model = BookLoan::class;

    protected static ?string $navigationIcon = 'heroicon-o-arrows-right-left';

    protected static ?string $navigationLabel = 'Book Loans';

    protected static ?string $navigationGroup = 'Library Management';

    protected static ?int $navigationSort = 2;

    public static function shouldRegisterNavigation(): bool
    {
        return in_array(auth()->user()?->role_id, [RoleConstants::ADMIN, RoleConstants::LIBRARIAN]) ?? false;
    }

    public static function canViewAny(): bool
    {
        return in_array(auth()->user()?->role_id, [RoleConstants::ADMIN, RoleConstants::LIBRARIAN]) ?? false;
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Loan Information')
                    ->schema([
                        Forms\Components\Select::make('student_id')
                            ->label('Student')
                            ->relationship('student', 'name')
                            ->searchable()
                            ->preload()
                            ->required()
                            ->getOptionLabelFromRecordUsing(fn (Student $record) => "{$record->name} ({$record->student_id}) - {$record->grade->name} {$record->classSection->name}"),

                        Forms\Components\Select::make('book_id')
                            ->label('Book')
                            ->relationship('book', 'title', fn (Builder $query) => $query->where('is_active', true)->where('available_copies', '>', 0))
                            ->searchable()
                            ->preload()
                            ->required()
                            ->getOptionLabelFromRecordUsing(fn (Book $record) => "{$record->title} by {$record->author} (Available: {$record->available_copies})")
                            ->helperText('Only shows active books with available copies'),

                        Forms\Components\DatePicker::make('lent_date')
                            ->label('Loan Date')
                            ->required()
                            ->default(now())
                            ->maxDate(now()),

                        Forms\Components\DatePicker::make('due_date')
                            ->label('Due Date')
                            ->required()
                            ->default(now()->addDays(14))
                            ->after('lent_date')
                            ->helperText('Standard loan period is 14 days'),

                        Forms\Components\Select::make('condition_on_loan')
                            ->label('Book Condition at Lending')
                            ->options([
                                'Excellent' => 'Excellent',
                                'Good' => 'Good',
                                'Fair' => 'Fair',
                                'Poor' => 'Poor',
                            ])
                            ->default('Good')
                            ->required(),

                        Forms\Components\Textarea::make('notes')
                            ->rows(2)
                            ->columnSpanFull()
                            ->helperText('Any special notes about this loan'),
                    ])
                    ->columns(2)
                    ->hiddenOn('edit'),

                Forms\Components\Section::make('Return Information')
                    ->schema([
                        Forms\Components\DatePicker::make('returned_at')
                            ->label('Return Date')
                            ->maxDate(now()),

                        Forms\Components\Select::make('status')
                            ->options([
                                'active' => 'Active (On Loan)',
                                'returned' => 'Returned',
                                'overdue' => 'Overdue',
                                'lost' => 'Lost',
                            ])
                            ->required()
                            ->default('active'),

                        Forms\Components\Select::make('condition_on_return')
                            ->label('Book Condition at Return')
                            ->options([
                                'Excellent' => 'Excellent',
                                'Good' => 'Good',
                                'Fair' => 'Fair',
                                'Poor' => 'Poor',
                                'Damaged' => 'Damaged',
                            ]),

                        Forms\Components\TextInput::make('fine_amount')
                            ->label('Fine Amount (ZMW)')
                            ->numeric()
                            ->default(0)
                            ->prefix('ZMW')
                            ->helperText('Fine for overdue or damaged books'),

                        Forms\Components\Toggle::make('fine_paid')
                            ->label('Fine Paid')
                            ->default(false),
                    ])
                    ->columns(2)
                    ->visibleOn('edit'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('student.name')
                    ->label('Student')
                    ->searchable()
                    ->sortable()
                    ->description(fn (BookLoan $record): string => $record->student->student_id.' - '.$record->student->grade->name),

                Tables\Columns\TextColumn::make('book.title')
                    ->label('Book')
                    ->searchable()
                    ->sortable()
                    ->description(fn (BookLoan $record): string => $record->book->author)
                    ->limit(30),

                Tables\Columns\TextColumn::make('lent_date')
                    ->label('Lent')
                    ->date()
                    ->sortable(),

                Tables\Columns\TextColumn::make('due_date')
                    ->label('Due')
                    ->date()
                    ->sortable()
                    ->color(fn (BookLoan $record): string => match (true) {
                        $record->status === 'returned' => 'success',
                        $record->due_date < now() => 'danger',
                        $record->due_date < now()->addDays(3) => 'warning',
                        default => 'gray',
                    }),

                Tables\Columns\TextColumn::make('returned_at')
                    ->label('Returned')
                    ->date()
                    ->sortable()
                    ->placeholder('Not returned'),

                Tables\Columns\BadgeColumn::make('status')
                    ->colors([
                        'success' => 'returned',
                        'primary' => 'active',
                        'danger' => 'overdue',
                        'warning' => 'lost',
                    ]),

                Tables\Columns\TextColumn::make('fine_amount')
                    ->label('Fine')
                    ->money('ZMW')
                    ->sortable()
                    ->toggleable(),

                Tables\Columns\IconColumn::make('fine_paid')
                    ->label('Paid')
                    ->boolean()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('lentBy.name')
                    ->label('Lent By')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'active' => 'Active',
                        'returned' => 'Returned',
                        'overdue' => 'Overdue',
                        'lost' => 'Lost',
                    ]),

                Tables\Filters\Filter::make('overdue')
                    ->label('Overdue Books')
                    ->query(fn (Builder $query): Builder => $query->where('status', 'active')->where('due_date', '<', now())),

                Tables\Filters\Filter::make('due_soon')
                    ->label('Due in 3 Days')
                    ->query(fn (Builder $query): Builder => $query->where('status', 'active')->whereBetween('due_date', [now(), now()->addDays(3)])),

                Tables\Filters\Filter::make('unreturned_fines')
                    ->label('Unpaid Fines')
                    ->query(fn (Builder $query): Builder => $query->where('fine_amount', '>', 0)->where('fine_paid', false)),

                Tables\Filters\TrashedFilter::make(),
            ])
            ->actions([
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\ViewAction::make(),

                    Tables\Actions\Action::make('return_book')
                        ->label('Return Book')
                        ->icon('heroicon-o-check-circle')
                        ->color('success')
                        ->visible(fn (BookLoan $record): bool => $record->status !== 'returned')
                        ->form([
                            Forms\Components\DatePicker::make('return_date')
                                ->label('Return Date')
                                ->default(now())
                                ->required(),

                            Forms\Components\Select::make('condition')
                                ->label('Book Condition')
                                ->options([
                                    'Excellent' => 'Excellent',
                                    'Good' => 'Good',
                                    'Fair' => 'Fair',
                                    'Poor' => 'Poor',
                                    'Damaged' => 'Damaged',
                                ])
                                ->required(),

                            Forms\Components\TextInput::make('fine')
                                ->label('Fine Amount (ZMW)')
                                ->numeric()
                                ->default(0)
                                ->prefix('ZMW'),

                            Forms\Components\Textarea::make('notes')
                                ->label('Return Notes')
                                ->rows(2),
                        ])
                        ->action(function (BookLoan $record, array $data): void {
                            $record->update([
                                'returned_at' => $data['return_date'],
                                'returned_to' => auth()->id(),
                                'status' => 'returned',
                                'condition_on_return' => $data['condition'],
                                'fine_amount' => $data['fine'] ?? 0,
                                'notes' => ($record->notes ? $record->notes."\n\n" : '').'Return: '.$data['notes'],
                            ]);

                            // Increment available copies
                            $record->book->incrementAvailableCopies();

                            Notification::make()
                                ->title('Book Returned Successfully')
                                ->body("'{$record->book->title}' has been returned by {$record->student->name}")
                                ->success()
                                ->send();
                        }),

                    Tables\Actions\EditAction::make(),

                    Tables\Actions\DeleteAction::make(),
                ]),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\ForceDeleteBulkAction::make(),
                    Tables\Actions\RestoreBulkAction::make(),
                ]),
            ])
            ->defaultSort('lent_date', 'desc');
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListBookLoans::route('/'),
            'create' => Pages\CreateBookLoan::route('/create'),
            'view' => Pages\ViewBookLoan::route('/{record}'),
            'edit' => Pages\EditBookLoan::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->with([
                'student:id,name,student_id,grade_id,class_section_id',
                'student.grade:id,name',
                'student.classSection:id,name',
                'book:id,title,author',
                'lentBy:id,name',
                'returnedTo:id,name',
            ])
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }
}
