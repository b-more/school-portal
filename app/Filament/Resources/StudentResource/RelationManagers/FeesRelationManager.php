<?php

namespace App\Filament\Resources\StudentResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class FeesRelationManager extends RelationManager
{
    protected static string $relationship = 'fees';

    protected static ?string $recordTitleAttribute = 'receipt_number';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('fee_structure_id')
                    ->relationship('feeStructure', 'description')
                    ->searchable()
                    ->preload()
                    ->required(),
                Forms\Components\Select::make('payment_status')
                    ->options([
                        'unpaid' => 'Unpaid',
                        'partial' => 'Partial',
                        'paid' => 'Paid',
                    ])
                    ->required()
                    ->default('unpaid')
                    ->reactive(),
                Forms\Components\TextInput::make('amount_paid')
                    ->numeric()
                    ->required()
                    ->default(0)
                    ->prefix('ZMW')
                    ->step(0.01),
                Forms\Components\TextInput::make('balance')
                    ->numeric()
                    ->required()
                    ->prefix('ZMW')
                    ->step(0.01),
                Forms\Components\DatePicker::make('payment_date')
                    ->required()
                    ->visible(fn (callable $get) => $get('payment_status') !== 'unpaid')
                    ->default(now()),
                Forms\Components\TextInput::make('receipt_number')
                    ->required()
                    ->visible(fn (callable $get) => $get('payment_status') !== 'unpaid')
                    ->maxLength(255),
                Forms\Components\Select::make('payment_method')
                    ->options([
                        'cash' => 'Cash',
                        'bank_transfer' => 'Bank Transfer',
                        'mobile_money' => 'Mobile Money',
                        'cheque' => 'Cheque',
                        'other' => 'Other',
                    ])
                    ->required()
                    ->visible(fn (callable $get) => $get('payment_status') !== 'unpaid'),
                Forms\Components\Textarea::make('notes')
                    ->maxLength(65535)
                    ->columnSpanFull(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('feeStructure.description')
                    ->label('Fee Description')
                    ->searchable()
                    ->limit(30),
                Tables\Columns\TextColumn::make('feeStructure.term')
                    ->label('Term'),
                Tables\Columns\TextColumn::make('feeStructure.academic_year')
                    ->label('Academic Year'),
                Tables\Columns\TextColumn::make('feeStructure.total_fee')
                    ->money('ZMW')
                    ->label('Total Fee'),
                Tables\Columns\TextColumn::make('amount_paid')
                    ->money('ZMW')
                    ->sortable(),
                Tables\Columns\TextColumn::make('balance')
                    ->money('ZMW')
                    ->sortable(),
                Tables\Columns\BadgeColumn::make('payment_status')
                    ->colors([
                        'danger' => 'unpaid',
                        'warning' => 'partial',
                        'success' => 'paid',
                    ]),
                Tables\Columns\TextColumn::make('payment_date')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('receipt_number')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('payment_status')
                    ->options([
                        'unpaid' => 'Unpaid',
                        'partial' => 'Partial',
                        'paid' => 'Paid',
                    ]),
                Tables\Filters\Filter::make('payment_date')
                    ->form([
                        Forms\Components\DatePicker::make('from'),
                        Forms\Components\DatePicker::make('until'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['from'],
                                fn (Builder $query, $date): Builder => $query->whereDate('payment_date', '>=', $date),
                            )
                            ->when(
                                $data['until'],
                                fn (Builder $query, $date): Builder => $query->whereDate('payment_date', '<=', $date),
                            );
                    }),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make(),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
                Tables\Actions\Action::make('recordPayment')
                    ->label('Record Payment')
                    ->icon('heroicon-o-currency-dollar')
                    ->color('success')
                    ->form([
                        Forms\Components\TextInput::make('amount_paid')
                            ->numeric()
                            ->required()
                            ->prefix('ZMW')
                            ->step(0.01),
                        Forms\Components\DatePicker::make('payment_date')
                            ->required()
                            ->default(now()),
                        Forms\Components\TextInput::make('receipt_number')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\Select::make('payment_method')
                            ->options([
                                'cash' => 'Cash',
                                'bank_transfer' => 'Bank Transfer',
                                'mobile_money' => 'Mobile Money',
                                'cheque' => 'Cheque',
                                'other' => 'Other',
                            ])
                            ->required(),
                        Forms\Components\Textarea::make('notes')
                            ->maxLength(65535),
                    ])
                    ->action(function ($record, array $data): void {
                        $newAmountPaid = $record->amount_paid + $data['amount_paid'];
                        $newBalance = $record->feeStructure->total_fee - $newAmountPaid;
                        $status = 'partial';

                        if ($newBalance <= 0) {
                            $status = 'paid';
                            $newBalance = 0;
                        }

                        $record->update([
                            'amount_paid' => $newAmountPaid,
                            'balance' => $newBalance,
                            'payment_status' => $status,
                            'payment_date' => $data['payment_date'],
                            'receipt_number' => $data['receipt_number'],
                            'payment_method' => $data['payment_method'],
                            'notes' => $data['notes'],
                        ]);
                    })
                    ->visible(fn ($record) => $record->payment_status !== 'paid'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }
}
