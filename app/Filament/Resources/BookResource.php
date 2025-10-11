<?php

namespace App\Filament\Resources;

use App\Constants\RoleConstants;
use App\Filament\Resources\BookResource\Pages;
use App\Models\Book;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class BookResource extends Resource
{
    protected static ?string $model = Book::class;

    protected static ?string $navigationIcon = 'heroicon-o-book-open';

    protected static ?string $navigationLabel = 'Books';

    protected static ?string $navigationGroup = 'Library Management';

    protected static ?int $navigationSort = 1;

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
                Forms\Components\Group::make()
                    ->schema([
                        Forms\Components\Section::make('Book Information')
                            ->schema([
                                Forms\Components\TextInput::make('title')
                                    ->required()
                                    ->maxLength(255)
                                    ->columnSpanFull(),

                                Forms\Components\TextInput::make('author')
                                    ->required()
                                    ->maxLength(255),

                                Forms\Components\TextInput::make('isbn')
                                    ->label('ISBN')
                                    ->maxLength(255)
                                    ->unique(ignoreRecord: true)
                                    ->helperText('International Standard Book Number'),

                                Forms\Components\TextInput::make('publisher')
                                    ->maxLength(255),

                                Forms\Components\TextInput::make('publication_year')
                                    ->numeric()
                                    ->minValue(1800)
                                    ->maxValue(date('Y'))
                                    ->helperText('Year the book was published'),

                                Forms\Components\Select::make('category')
                                    ->options([
                                        'Fiction' => 'Fiction',
                                        'Non-Fiction' => 'Non-Fiction',
                                        'Science' => 'Science',
                                        'Mathematics' => 'Mathematics',
                                        'History' => 'History',
                                        'Geography' => 'Geography',
                                        'Literature' => 'Literature',
                                        'Reference' => 'Reference',
                                        'Religious Studies' => 'Religious Studies',
                                        'Art & Music' => 'Art & Music',
                                        'Sports & Recreation' => 'Sports & Recreation',
                                        'Technology' => 'Technology',
                                        'Other' => 'Other',
                                    ])
                                    ->searchable()
                                    ->required(),

                                Forms\Components\Select::make('language')
                                    ->options([
                                        'English' => 'English',
                                        'French' => 'French',
                                        'Spanish' => 'Spanish',
                                        'Portuguese' => 'Portuguese',
                                        'Other' => 'Other',
                                    ])
                                    ->default('English')
                                    ->required(),

                                Forms\Components\Textarea::make('description')
                                    ->rows(3)
                                    ->columnSpanFull(),
                            ])
                            ->columns(2),

                        Forms\Components\Section::make('Library Details')
                            ->schema([
                                Forms\Components\TextInput::make('total_copies')
                                    ->required()
                                    ->numeric()
                                    ->default(1)
                                    ->minValue(1)
                                    ->live()
                                    ->afterStateUpdated(function (Forms\Get $get, Forms\Set $set, $state) {
                                        // When total copies changes, update available copies if it's higher
                                        $availableCopies = $get('available_copies') ?? 0;
                                        if ($state < $availableCopies) {
                                            $set('available_copies', $state);
                                        }
                                    }),

                                Forms\Components\TextInput::make('available_copies')
                                    ->required()
                                    ->numeric()
                                    ->default(1)
                                    ->minValue(0)
                                    ->helperText('Number of copies currently available for lending'),

                                Forms\Components\TextInput::make('shelf_location')
                                    ->maxLength(255)
                                    ->helperText('e.g., A-12, B-05'),

                                Forms\Components\TextInput::make('price')
                                    ->numeric()
                                    ->prefix('ZMW')
                                    ->helperText('Purchase price of the book'),

                                Forms\Components\Toggle::make('is_active')
                                    ->label('Active')
                                    ->default(true)
                                    ->helperText('Inactive books cannot be lent out'),
                            ])
                            ->columns(2),
                    ])
                    ->columnSpan(['lg' => 2]),

                Forms\Components\Group::make()
                    ->schema([
                        Forms\Components\Section::make('Book Cover')
                            ->schema([
                                Forms\Components\FileUpload::make('cover_image')
                                    ->image()
                                    ->directory('book-covers')
                                    ->imageResizeMode('cover')
                                    ->imageCropAspectRatio('2:3')
                                    ->imageResizeTargetWidth('300')
                                    ->imageResizeTargetHeight('450')
                                    ->label('Cover Image'),
                            ]),

                        Forms\Components\Section::make('Availability')
                            ->schema([
                                Forms\Components\Placeholder::make('availability_info')
                                    ->label('Current Status')
                                    ->content(function ($record) {
                                        if (! $record) {
                                            return 'New book';
                                        }

                                        $loaned = $record->total_copies - $record->available_copies;
                                        $status = $record->isAvailable() ? '✅ Available' : '❌ Not Available';

                                        return "{$status}\n\nTotal: {$record->total_copies}\nAvailable: {$record->available_copies}\nLoaned: {$loaned}";
                                    }),
                            ]),
                    ])
                    ->columnSpan(['lg' => 1]),
            ])
            ->columns(3);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('cover_image')
                    ->label('Cover')
                    ->circular()
                    ->defaultImageUrl(url('/images/default-book.png')),

                Tables\Columns\TextColumn::make('title')
                    ->searchable()
                    ->sortable()
                    ->weight('bold')
                    ->description(fn (Book $record): string => $record->author),

                Tables\Columns\TextColumn::make('isbn')
                    ->label('ISBN')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('category')
                    ->badge()
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('publisher')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('publication_year')
                    ->label('Year')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('total_copies')
                    ->label('Total')
                    ->numeric()
                    ->sortable()
                    ->alignCenter(),

                Tables\Columns\TextColumn::make('available_copies')
                    ->label('Available')
                    ->numeric()
                    ->sortable()
                    ->alignCenter()
                    ->color(fn (Book $record): string => $record->available_copies > 0 ? 'success' : 'danger'),

                Tables\Columns\TextColumn::make('shelf_location')
                    ->label('Location')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('language')
                    ->badge()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\IconColumn::make('is_active')
                    ->label('Active')
                    ->boolean()
                    ->sortable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('category')
                    ->options([
                        'Fiction' => 'Fiction',
                        'Non-Fiction' => 'Non-Fiction',
                        'Science' => 'Science',
                        'Mathematics' => 'Mathematics',
                        'History' => 'History',
                        'Geography' => 'Geography',
                        'Literature' => 'Literature',
                        'Reference' => 'Reference',
                        'Religious Studies' => 'Religious Studies',
                        'Art & Music' => 'Art & Music',
                        'Sports & Recreation' => 'Sports & Recreation',
                        'Technology' => 'Technology',
                        'Other' => 'Other',
                    ]),

                Tables\Filters\SelectFilter::make('language'),

                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Active Status'),

                Tables\Filters\Filter::make('available')
                    ->label('Available Books')
                    ->query(fn (Builder $query): Builder => $query->where('available_copies', '>', 0)),

                Tables\Filters\Filter::make('out_of_stock')
                    ->label('Out of Stock')
                    ->query(fn (Builder $query): Builder => $query->where('available_copies', '=', 0)),

                Tables\Filters\TrashedFilter::make(),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\ForceDeleteBulkAction::make(),
                    Tables\Actions\RestoreBulkAction::make(),
                ]),
            ])
            ->defaultSort('title', 'asc');
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
            'index' => Pages\ListBooks::route('/'),
            'create' => Pages\CreateBook::route('/create'),
            'view' => Pages\ViewBook::route('/{record}'),
            'edit' => Pages\EditBook::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->withCount(['loans', 'activeLoans'])
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }
}
