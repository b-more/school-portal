<?php

namespace App\Filament\Resources;

use App\Filament\Resources\GalleryImageResource\Pages;
use App\Models\GalleryImage;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class GalleryImageResource extends Resource
{
    protected static ?string $model = GalleryImage::class;

    protected static ?string $navigationIcon = 'heroicon-o-photo';

    protected static ?string $navigationGroup = 'Website Management';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('title')
                    ->required()
                    ->maxLength(255),
                Forms\Components\Textarea::make('description')
                    ->maxLength(65535)
                    ->columnSpanFull(),
                Forms\Components\FileUpload::make('filename')
                    ->label('Image')
                    ->image()
                    ->directory('gallery-images')
                    ->maxSize(5120) // 5MB
                    ->required()
                    ->columnSpanFull(),
                Forms\Components\Select::make('category')
                    ->options([
                        'facility' => 'Facility',
                        'event' => 'Event',
                        'student' => 'Student',
                        'classroom' => 'Classroom',
                        'sports' => 'Sports',
                        'other' => 'Other',
                    ])
                    ->required(),
                Forms\Components\Select::make('uploaded_by')
                    ->relationship('uploader', 'name')
                    ->searchable()
                    ->preload()
                    ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('filename')
                    ->label('Image')
                    ->square(),
                Tables\Columns\TextColumn::make('title')
                    ->searchable()
                    ->sortable()
                    ->limit(30),
                Tables\Columns\TextColumn::make('category')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('uploader.name')
                    ->label('Uploaded By')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('category')
                    ->options([
                        'facility' => 'Facility',
                        'event' => 'Event',
                        'student' => 'Student',
                        'classroom' => 'Classroom',
                        'sports' => 'Sports',
                        'other' => 'Other',
                    ]),
                Tables\Filters\SelectFilter::make('uploader')
                    ->relationship('uploader', 'name'),
                Tables\Filters\Filter::make('created_at')
                    ->form([
                        Forms\Components\DatePicker::make('from'),
                        Forms\Components\DatePicker::make('until'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['from'],
                                fn (Builder $query, $date): Builder => $query->whereDate('created_at', '>=', $date),
                            )
                            ->when(
                                $data['until'],
                                fn (Builder $query, $date): Builder => $query->whereDate('created_at', '<=', $date),
                            );
                    }),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\BulkAction::make('changeCategory')
                        ->label('Change Category')
                        ->icon('heroicon-o-tag')
                        ->form([
                            Forms\Components\Select::make('category')
                                ->options([
                                    'facility' => 'Facility',
                                    'event' => 'Event',
                                    'student' => 'Student',
                                    'classroom' => 'Classroom',
                                    'sports' => 'Sports',
                                    'other' => 'Other',
                                ])
                                ->required(),
                        ])
                        ->action(function (Builder $query, array $data): void {
                            $query->update(['category' => $data['category']]);
                        })
                        ->deselectRecordsAfterCompletion(),
                ]),
            ]);
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
            'index' => Pages\ListGalleryImages::route('/'),
            'create' => Pages\CreateGalleryImage::route('/create'),
            'view' => Pages\ViewGalleryImage::route('/{record}'),
            'edit' => Pages\EditGalleryImage::route('/{record}/edit'),
        ];
    }
}
