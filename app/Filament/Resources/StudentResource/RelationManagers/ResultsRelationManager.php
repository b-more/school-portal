<?php

namespace App\Filament\Resources\StudentResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class ResultsRelationManager extends RelationManager
{
    protected static string $relationship = 'results';

    protected static ?string $recordTitleAttribute = 'subject_id';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('subject_id')
                    ->relationship('subject', 'name')
                    ->searchable()
                    ->preload()
                    ->required(),
                Forms\Components\Select::make('exam_type')
                    ->options([
                        'mid-term' => 'Mid-Term',
                        'final' => 'Final',
                        'quiz' => 'Quiz',
                        'assignment' => 'Assignment',
                    ])
                    ->required(),
                Forms\Components\TextInput::make('marks')
                    ->numeric()
                    ->required()
                    ->step(0.01)
                    ->minValue(0)
                    ->maxValue(100),
                Forms\Components\TextInput::make('grade')
                    ->required()
                    ->maxLength(255),
                Forms\Components\Select::make('term')
                    ->options([
                        'first' => 'First Term',
                        'second' => 'Second Term',
                        'third' => 'Third Term',
                    ])
                    ->required(),
                Forms\Components\TextInput::make('year')
                    ->numeric()
                    ->required()
                    ->default(date('Y')),
                Forms\Components\Textarea::make('comment')
                    ->maxLength(65535)
                    ->columnSpanFull(),
                Forms\Components\Select::make('recorded_by')
                    ->relationship('recordedBy', 'name')
                    ->searchable()
                    ->preload()
                    ->required(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('subject.name')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('exam_type')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('marks')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('grade')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('term')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('year')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('recordedBy.name')
                    ->sortable()
                    ->label('Recorded By')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('subject')
                    ->relationship('subject', 'name'),
                Tables\Filters\SelectFilter::make('exam_type')
                    ->options([
                        'mid-term' => 'Mid-Term',
                        'final' => 'Final',
                        'quiz' => 'Quiz',
                        'assignment' => 'Assignment',
                    ]),
                Tables\Filters\SelectFilter::make('term')
                    ->options([
                        'first' => 'First Term',
                        'second' => 'Second Term',
                        'third' => 'Third Term',
                    ]),
                Tables\Filters\Filter::make('year')
                    ->form([
                        Forms\Components\TextInput::make('year'),
                    ])
                    ->query(fn (Builder $query, array $data): Builder =>
                        $query->when($data['year'], fn($q) => $q->where('year', $data['year']))
                    ),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make(),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }
}
