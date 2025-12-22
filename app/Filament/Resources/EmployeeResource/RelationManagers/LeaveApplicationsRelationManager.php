<?php

namespace App\Filament\Resources\EmployeeResource\RelationManagers;

use App\Models\LeaveType;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class LeaveApplicationsRelationManager extends RelationManager
{
    protected static string $relationship = 'leaveApplications';

    protected static ?string $recordTitleAttribute = 'reference_number';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('leave_type_id')
                    ->label('Leave Type')
                    ->options(LeaveType::where('is_active', true)->pluck('name', 'id'))
                    ->required(),
                Forms\Components\DatePicker::make('start_date')
                    ->required()
                    ->minDate(now()),
                Forms\Components\DatePicker::make('end_date')
                    ->required()
                    ->afterOrEqual('start_date'),
                Forms\Components\TextInput::make('days_requested')
                    ->numeric()
                    ->required()
                    ->minValue(1),
                Forms\Components\Textarea::make('reason')
                    ->required()
                    ->rows(3)
                    ->columnSpanFull(),
                Forms\Components\TextInput::make('contact_during_leave')
                    ->tel(),
                Forms\Components\Textarea::make('handover_notes')
                    ->rows(2)
                    ->columnSpanFull(),
                Forms\Components\FileUpload::make('attachment')
                    ->directory('leave-attachments')
                    ->acceptedFileTypes(['application/pdf', 'image/*'])
                    ->maxSize(5120),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('reference_number')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('leaveType.name')
                    ->label('Type')
                    ->badge(),
                Tables\Columns\TextColumn::make('start_date')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('end_date')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('days_requested')
                    ->label('Days'),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'pending' => 'warning',
                        'approved_by_hod' => 'info',
                        'approved_by_head' => 'info',
                        'approved' => 'success',
                        'rejected' => 'danger',
                        'cancelled' => 'gray',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Applied')
                    ->date()
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'pending' => 'Pending',
                        'approved_by_hod' => 'HOD Approved',
                        'approved_by_head' => 'Head Approved',
                        'approved' => 'Approved',
                        'rejected' => 'Rejected',
                        'cancelled' => 'Cancelled',
                    ]),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make(),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make()
                    ->visible(fn ($record) => $record->status === 'pending'),
                Tables\Actions\DeleteAction::make()
                    ->visible(fn ($record) => $record->status === 'pending'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }
}
