<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PaymentTransactionResource\Pages;
use App\Filament\Resources\PaymentTransactionResource\RelationManagers;
use App\Models\PaymentTransaction;
use App\Models\AcademicYear;
use App\Models\Term;
use App\Models\Grade;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\Filter;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class PaymentTransactionResource extends Resource
{
    protected static ?string $model = PaymentTransaction::class;

    protected static ?string $navigationIcon = 'heroicon-o-banknotes';

    protected static ?string $navigationLabel = 'Payments Received';

    protected static ?string $modelLabel = 'Payment';

    protected static ?string $pluralModelLabel = 'Payments Received';

    protected static ?string $navigationGroup = 'Financial Management';

    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Transaction Details')
                    ->schema([
                        Forms\Components\TextInput::make('reference_number')
                            ->label('Reference Number')
                            ->disabled(),
                        Forms\Components\TextInput::make('amount')
                            ->label('Amount (ZMW)')
                            ->numeric()
                            ->disabled(),
                        Forms\Components\TextInput::make('type')
                            ->disabled(),
                        Forms\Components\TextInput::make('payment_method')
                            ->disabled(),
                        Forms\Components\DateTimePicker::make('transaction_date')
                            ->disabled(),
                        Forms\Components\Textarea::make('notes')
                            ->disabled()
                            ->columnSpanFull(),
                    ])
                    ->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('transaction_date', 'desc')
            ->columns([
                Tables\Columns\TextColumn::make('transaction_date')
                    ->label('Date')
                    ->dateTime('d M Y H:i')
                    ->sortable()
                    ->searchable(),

                Tables\Columns\TextColumn::make('reference_number')
                    ->label('Receipt #')
                    ->searchable()
                    ->copyable()
                    ->copyMessage('Reference copied!')
                    ->sortable(),

                Tables\Columns\TextColumn::make('studentFee.student.name')
                    ->label('Student')
                    ->searchable()
                    ->sortable()
                    ->wrap(),

                Tables\Columns\TextColumn::make('studentFee.student.student_id_number')
                    ->label('Student ID')
                    ->searchable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('studentFee.student.grade.name')
                    ->label('Grade')
                    ->sortable()
                    ->badge()
                    ->color('info'),

                Tables\Columns\TextColumn::make('studentFee.feeStructure.term.name')
                    ->label('Term')
                    ->sortable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('amount')
                    ->label('Amount')
                    ->money('ZMW')
                    ->sortable()
                    ->summarize([
                        Tables\Columns\Summarizers\Sum::make()
                            ->money('ZMW')
                            ->label('Total'),
                    ]),

                Tables\Columns\TextColumn::make('payment_method')
                    ->label('Method')
                    ->formatStateUsing(fn ($state) => ucfirst(str_replace('_', ' ', $state ?? 'N/A')))
                    ->badge()
                    ->color(fn ($state) => match($state) {
                        'cash' => 'success',
                        'mobile_money' => 'info',
                        'bank_transfer' => 'warning',
                        'cheque' => 'primary',
                        default => 'gray',
                    }),

                Tables\Columns\TextColumn::make('type')
                    ->label('Type')
                    ->formatStateUsing(fn ($state) => ucfirst(str_replace('_', ' ', $state)))
                    ->badge()
                    ->color(fn ($state) => match($state) {
                        'payment' => 'success',
                        'refund' => 'danger',
                        'adjustment' => 'warning',
                        'balance_forward' => 'info',
                        default => 'gray',
                    })
                    ->toggleable(),

                Tables\Columns\TextColumn::make('processedBy.name')
                    ->label('Processed By')
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Recorded At')
                    ->dateTime('d M Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Filter::make('transaction_date')
                    ->form([
                        Forms\Components\DatePicker::make('from')
                            ->label('From Date'),
                        Forms\Components\DatePicker::make('until')
                            ->label('Until Date'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['from'],
                                fn (Builder $query, $date): Builder => $query->whereDate('transaction_date', '>=', $date),
                            )
                            ->when(
                                $data['until'],
                                fn (Builder $query, $date): Builder => $query->whereDate('transaction_date', '<=', $date),
                            );
                    })
                    ->indicateUsing(function (array $data): array {
                        $indicators = [];
                        if ($data['from'] ?? null) {
                            $indicators[] = 'From ' . Carbon::parse($data['from'])->format('d M Y');
                        }
                        if ($data['until'] ?? null) {
                            $indicators[] = 'Until ' . Carbon::parse($data['until'])->format('d M Y');
                        }
                        return $indicators;
                    }),

                SelectFilter::make('academic_year')
                    ->label('Academic Year')
                    ->relationship('studentFee.feeStructure.academicYear', 'name')
                    ->preload()
                    ->default(function () {
                        $currentYear = AcademicYear::where('is_current', true)->first();
                        return $currentYear?->id;
                    }),

                SelectFilter::make('term')
                    ->label('Term')
                    ->relationship('studentFee.feeStructure.term', 'name')
                    ->preload()
                    ->default(function () {
                        $currentTerm = Term::where('is_current', true)->first();
                        return $currentTerm?->id;
                    }),

                SelectFilter::make('grade')
                    ->label('Grade')
                    ->options(Grade::pluck('name', 'id'))
                    ->query(function (Builder $query, $state) {
                        if ($state['value']) {
                            $query->whereHas('studentFee.student', function (Builder $query) use ($state) {
                                $query->where('grade_id', $state['value']);
                            });
                        }
                    }),

                SelectFilter::make('payment_method')
                    ->label('Payment Method')
                    ->options([
                        'cash' => 'Cash',
                        'mobile_money' => 'Mobile Money',
                        'bank_transfer' => 'Bank Transfer',
                        'cheque' => 'Cheque',
                        'other' => 'Other',
                    ]),

                SelectFilter::make('type')
                    ->label('Transaction Type')
                    ->options([
                        'payment' => 'Payment',
                        'refund' => 'Refund',
                        'adjustment' => 'Adjustment',
                        'balance_forward' => 'Balance Forward',
                        'overpayment' => 'Overpayment',
                        'credit_applied' => 'Credit Applied',
                    ])
                    ->default('payment'),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\Action::make('printReceipt')
                    ->label('Print Receipt')
                    ->icon('heroicon-o-printer')
                    ->color('success')
                    ->url(fn (PaymentTransaction $record) => route('student-fees.transaction-receipt', [
                        'fee' => $record->student_fee_id,
                        'transaction' => $record->id,
                    ]))
                    ->openUrlInNewTab(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\BulkAction::make('exportSelected')
                        ->label('Export Selected')
                        ->icon('heroicon-o-arrow-down-tray')
                        ->action(function ($records) {
                            // Export functionality can be added here
                        }),
                ]),
            ])
            ->emptyStateHeading('No payment transactions found')
            ->emptyStateDescription('Payment transactions will appear here once fees are recorded.')
            ->emptyStateIcon('heroicon-o-banknotes');
    }

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery()
            ->with([
                'studentFee.student.grade',
                'studentFee.feeStructure.term',
                'studentFee.feeStructure.academicYear',
                'processedBy'
            ]);

        // Default to current academic year if no filters applied
        $currentYear = AcademicYear::where('is_current', true)->first();
        if ($currentYear) {
            $query->whereHas('studentFee.feeStructure.academicYear', function (Builder $query) use ($currentYear) {
                $query->where('id', $currentYear->id);
            });
        }

        return $query;
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
            'index' => Pages\ListPaymentTransactions::route('/'),
            'view' => Pages\ViewPaymentTransaction::route('/{record}'),
        ];
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function canEdit($record): bool
    {
        return false;
    }

    public static function canDelete($record): bool
    {
        return false;
    }
}
