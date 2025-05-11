<?php

namespace App\Filament\Widgets;

use App\Models\Homework;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class RecentHomework extends BaseWidget
{
    public function table(Table $table): Table
    {
        return $table
            ->query(
                Homework::query()
                    ->with(['subject', 'grade'])  // Eager load relationships
                    ->latest()
                    ->limit(5)
            )
            ->columns([
                Tables\Columns\TextColumn::make('title')
                    ->searchable()
                    ->limit(50),
                Tables\Columns\TextColumn::make('subject.name')
                    ->label('Subject')
                    ->searchable(),
                Tables\Columns\TextColumn::make('grade.name')  // Changed from 'grade' to 'grade.name'
                    ->label('Grade')
                    ->searchable(),
                Tables\Columns\TextColumn::make('due_date')
                    ->date()
                    ->sortable(),
                Tables\Columns\BadgeColumn::make('status')
                    ->colors([
                        'warning' => 'active',
                        'success' => 'completed',
                    ])
            ]);
    }
}
