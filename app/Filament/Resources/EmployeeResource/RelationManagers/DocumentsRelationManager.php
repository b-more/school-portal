<?php

namespace App\Filament\Resources\EmployeeResource\RelationManagers;

use App\Models\EmployeeDocument;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class DocumentsRelationManager extends RelationManager
{
    protected static string $relationship = 'documents';

    protected static ?string $recordTitleAttribute = 'document_name';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('document_type')
                    ->options(EmployeeDocument::DOCUMENT_TYPES)
                    ->required(),
                Forms\Components\TextInput::make('document_name')
                    ->required()
                    ->maxLength(255),
                Forms\Components\FileUpload::make('file_path')
                    ->label('Document File')
                    ->directory('employee-documents')
                    ->acceptedFileTypes(['application/pdf', 'image/*'])
                    ->maxSize(10240)
                    ->required()
                    ->columnSpanFull(),
                Forms\Components\DatePicker::make('issue_date'),
                Forms\Components\DatePicker::make('expiry_date'),
                Forms\Components\Textarea::make('notes')
                    ->rows(2)
                    ->columnSpanFull(),
                Forms\Components\Toggle::make('is_verified')
                    ->label('Verified')
                    ->default(false),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('document_type')
                    ->label('Type')
                    ->formatStateUsing(fn ($state) => EmployeeDocument::DOCUMENT_TYPES[$state] ?? $state)
                    ->searchable(),
                Tables\Columns\TextColumn::make('document_name')
                    ->searchable(),
                Tables\Columns\TextColumn::make('issue_date')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('expiry_date')
                    ->date()
                    ->sortable()
                    ->color(fn ($record) => $record->isExpired() ? 'danger' : ($record->isExpiringSoon() ? 'warning' : null)),
                Tables\Columns\IconColumn::make('is_verified')
                    ->label('Verified')
                    ->boolean(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Uploaded')
                    ->date()
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('document_type')
                    ->options(EmployeeDocument::DOCUMENT_TYPES),
                Tables\Filters\TernaryFilter::make('is_verified')
                    ->label('Verified'),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->mutateFormDataUsing(function (array $data): array {
                        $data['uploaded_by'] = auth()->id();
                        return $data;
                    }),
            ])
            ->actions([
                Tables\Actions\Action::make('download')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->url(fn ($record) => asset('storage/' . $record->file_path))
                    ->openUrlInNewTab(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }
}
