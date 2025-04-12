<?php

namespace App\Filament\Resources\SchoolClassResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class TeachersRelationManager extends RelationManager
{
    protected static string $relationship = 'teachers';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('employee_id')
                    ->label('Teacher')
                    ->relationship('teachers', 'name')
                    ->searchable()
                    ->preload()
                    ->required(),

                Forms\Components\Select::make('pivot.role')
                    ->label('Role')
                    ->options([
                        'Class Teacher' => 'Class Teacher',
                        'Assistant Teacher' => 'Assistant Teacher',
                        'Subject Teacher' => 'Subject Teacher',
                    ])
                    ->required(),

                Forms\Components\Toggle::make('pivot.is_primary')
                    ->label('Is Primary Teacher')
                    ->default(false),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Teacher Name')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('position')
                    ->label('Position')
                    ->searchable(),

                Tables\Columns\TextColumn::make('department')
                    ->label('Department')
                    ->searchable(),

                Tables\Columns\TextColumn::make('pivot.role')
                    ->label('Role in Class')
                    ->badge(),

                Tables\Columns\IconColumn::make('pivot.is_primary')
                    ->label('Primary Teacher')
                    ->boolean(),

                Tables\Columns\TextColumn::make('phone')
                    ->label('Contact')
                    ->searchable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('role')
                    ->label('Role in Class')
                    ->options([
                        'Class Teacher' => 'Class Teacher',
                        'Assistant Teacher' => 'Assistant Teacher',
                        'Subject Teacher' => 'Subject Teacher',
                    ]),

                Tables\Filters\TernaryFilter::make('pivot.is_primary')
                    ->label('Primary Teachers Only'),
            ])
            ->headerActions([
                Tables\Actions\AttachAction::make()
                    ->label('Assign Teacher')
                    ->preloadRecordSelect()
                    ->form(fn (Tables\Actions\AttachAction $action): array => [
                        $action->getRecordSelect(),
                        Forms\Components\Select::make('role')
                            ->label('Role')
                            ->options([
                                'Class Teacher' => 'Class Teacher',
                                'Assistant Teacher' => 'Assistant Teacher',
                                'Subject Teacher' => 'Subject Teacher',
                            ])
                            ->required(),
                        Forms\Components\Toggle::make('is_primary')
                            ->label('Is Primary Teacher')
                            ->default(false),
                    ]),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DetachAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DetachBulkAction::make(),
                ]),
            ]);
    }
}
