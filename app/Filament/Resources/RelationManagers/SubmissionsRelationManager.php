<?php

namespace App\Filament\Resources\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use App\Models\HomeworkSubmission;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Resources\RelationManagers\SubmissionsRelationManager;

class SubmissionsRelationManager extends RelationManager
{
    protected static string $relationship = 'submissions';

    protected static ?string $recordTitleAttribute = 'student.name';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('comment')
                    ->maxLength(255),
                Forms\Components\FileUpload::make('submission_file')
                    ->label('Submission File')
                    ->disk('public')
                    ->directory('homework-submissions'),
                Forms\Components\Toggle::make('is_submitted')
                    ->label('Mark as Submitted')
                    ->default(true),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('student.name')
                    ->label('Student Name')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Submission Date')
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\IconColumn::make('is_submitted')
                    ->label('Submitted')
                    ->boolean(),
                Tables\Columns\TextColumn::make('grade')
                    ->label('Grade')
                    ->numeric(min: 0, max: 100),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make(),
            ])
            ->actions([
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
