<?php

namespace App\Filament\Resources;

use App\Constants\RoleConstants;
use App\Filament\Resources\BusPaymentResource\Pages;
use App\Models\BusPayment;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class BusPaymentResource extends Resource
{
    protected static ?string $model = BusPayment::class;

    protected static ?string $navigationIcon = 'heroicon-o-credit-card';

    protected static ?string $navigationGroup = 'Finance Management';

    protected static ?string $navigationLabel = 'Bus Payments';

    protected static ?int $navigationSort = 6;

    public static function shouldRegisterNavigation(): bool
    {
        return auth()->user()?->role_id === RoleConstants::ADMIN ?? false;
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Student & Route')
                    ->description('Select student and bus route')
                    ->schema([
                        Forms\Components\Select::make('student_id')
                            ->label('Student')
                            ->relationship('student', 'name')
                            ->searchable()
                            ->preload()
                            ->required()
                            ->columnSpanFull(),

                        Forms\Components\Select::make('bus_fare_structure_id')
                            ->label('Bus Route')
                            ->relationship('busFareStructure', 'route_name', fn ($query) => $query->where('is_active', true))
                            ->searchable()
                            ->preload()
                            ->required()
                            ->live()
                            ->afterStateUpdated(function (Set $set, Get $get, $state) {
                                if ($state) {
                                    $fareStructure = \App\Models\BusFareStructure::find($state);
                                    if ($fareStructure) {
                                        $amount = $fareStructure->getAmount();
                                        $set('amount', $amount);
                                        $set('balance', $amount - ($get('amount_paid') ?? 0));
                                    }
                                }
                            }),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Payment Period')
                    ->description('Define the payment period')
                    ->schema([
                        Forms\Components\Select::make('month')
                            ->label('Month')
                            ->options([
                                'January' => 'January',
                                'February' => 'February',
                                'March' => 'March',
                                'April' => 'April',
                                'May' => 'May',
                                'June' => 'June',
                                'July' => 'July',
                                'August' => 'August',
                                'September' => 'September',
                                'October' => 'October',
                                'November' => 'November',
                                'December' => 'December',
                            ])
                            ->visible(function (Get $get) {
                                $fareStructureId = $get('bus_fare_structure_id');
                                if ($fareStructureId) {
                                    $fareStructure = \App\Models\BusFareStructure::find($fareStructureId);

                                    return $fareStructure && $fareStructure->payment_plan === 'monthly';
                                }

                                return false;
                            })
                            ->required(function (Get $get) {
                                $fareStructureId = $get('bus_fare_structure_id');
                                if ($fareStructureId) {
                                    $fareStructure = \App\Models\BusFareStructure::find($fareStructureId);

                                    return $fareStructure && $fareStructure->payment_plan === 'monthly';
                                }

                                return false;
                            })
                            ->default(now()->format('F'))
                            ->native(false),

                        Forms\Components\TextInput::make('year')
                            ->label('Year')
                            ->numeric()
                            ->required()
                            ->default(now()->year)
                            ->minValue(2000)
                            ->maxValue(now()->year + 1),

                        Forms\Components\DatePicker::make('due_date')
                            ->label('Due Date')
                            ->native(false)
                            ->displayFormat('d M Y'),
                    ])
                    ->columns(3),

                Forms\Components\Section::make('Payment Details')
                    ->description('Amount and payment information')
                    ->schema([
                        Forms\Components\TextInput::make('amount')
                            ->label('Total Amount')
                            ->numeric()
                            ->prefix('ZMW')
                            ->required()
                            ->disabled()
                            ->dehydrated(),

                        Forms\Components\TextInput::make('amount_paid')
                            ->label('Amount Paid')
                            ->numeric()
                            ->prefix('ZMW')
                            ->default(0)
                            ->live(onBlur: true)
                            ->afterStateUpdated(function (Set $set, Get $get, $state) {
                                $amount = (float) ($get('amount') ?? 0);
                                $amountPaid = (float) ($state ?? 0);
                                $balance = $amount - $amountPaid;
                                $set('balance', $balance);

                                // Update status
                                if ($balance <= 0) {
                                    $set('payment_status', 'paid');
                                } elseif ($amountPaid > 0) {
                                    $set('payment_status', 'partial');
                                } else {
                                    $set('payment_status', 'unpaid');
                                }
                            }),

                        Forms\Components\TextInput::make('balance')
                            ->label('Balance')
                            ->numeric()
                            ->prefix('ZMW')
                            ->disabled()
                            ->dehydrated(),

                        Forms\Components\Select::make('payment_status')
                            ->label('Payment Status')
                            ->options([
                                'unpaid' => 'Unpaid',
                                'partial' => 'Partial',
                                'paid' => 'Paid',
                            ])
                            ->required()
                            ->default('unpaid')
                            ->native(false),

                        Forms\Components\Textarea::make('notes')
                            ->label('Notes')
                            ->rows(3)
                            ->columnSpanFull()
                            ->placeholder('Additional notes or comments...'),
                    ])
                    ->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                Tables\Columns\TextColumn::make('student.name')
                    ->label('Student')
                    ->searchable()
                    ->sortable()
                    ->icon('heroicon-m-user'),

                Tables\Columns\TextColumn::make('student.grade.name')
                    ->label('Grade')
                    ->sortable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('busFareStructure.route_name')
                    ->label('Route')
                    ->searchable()
                    ->sortable()
                    ->icon('heroicon-m-map-pin')
                    ->badge()
                    ->color('info'),

                Tables\Columns\TextColumn::make('month')
                    ->label('Month')
                    ->searchable()
                    ->sortable()
                    ->placeholder('Term Payment')
                    ->toggleable(),

                Tables\Columns\TextColumn::make('year')
                    ->label('Year')
                    ->sortable()
                    ->alignCenter(),

                Tables\Columns\TextColumn::make('amount')
                    ->label('Amount')
                    ->money('ZMW')
                    ->sortable(),

                Tables\Columns\TextColumn::make('amount_paid')
                    ->label('Paid')
                    ->money('ZMW')
                    ->sortable()
                    ->color('success'),

                Tables\Columns\TextColumn::make('balance')
                    ->label('Balance')
                    ->money('ZMW')
                    ->sortable()
                    ->weight('bold')
                    ->color(fn ($state) => $state > 0 ? 'danger' : 'success'),

                Tables\Columns\BadgeColumn::make('payment_status')
                    ->label('Status')
                    ->colors([
                        'danger' => 'unpaid',
                        'warning' => 'partial',
                        'success' => 'paid',
                    ])
                    ->icons([
                        'heroicon-o-x-circle' => 'unpaid',
                        'heroicon-o-clock' => 'partial',
                        'heroicon-o-check-circle' => 'paid',
                    ]),

                Tables\Columns\TextColumn::make('due_date')
                    ->label('Due Date')
                    ->date('d M Y')
                    ->sortable()
                    ->toggleable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('bus_fare_structure_id')
                    ->label('Route')
                    ->relationship('busFareStructure', 'route_name')
                    ->preload()
                    ->native(false),

                Tables\Filters\SelectFilter::make('payment_status')
                    ->options([
                        'unpaid' => 'Unpaid',
                        'partial' => 'Partial',
                        'paid' => 'Paid',
                    ])
                    ->native(false),

                Tables\Filters\SelectFilter::make('year')
                    ->options(function () {
                        $years = range(now()->year - 2, now()->year + 1);

                        return array_combine($years, $years);
                    })
                    ->default(now()->year)
                    ->native(false),

                Tables\Filters\SelectFilter::make('month')
                    ->options([
                        'January' => 'January',
                        'February' => 'February',
                        'March' => 'March',
                        'April' => 'April',
                        'May' => 'May',
                        'June' => 'June',
                        'July' => 'July',
                        'August' => 'August',
                        'September' => 'September',
                        'October' => 'October',
                        'November' => 'November',
                        'December' => 'December',
                    ])
                    ->native(false),
            ], layout: Tables\Enums\FiltersLayout::AboveContentCollapsible)
            ->actions([
                Tables\Actions\Action::make('view_receipt')
                    ->label('Receipt')
                    ->icon('heroicon-o-document-text')
                    ->color('success')
                    ->url(fn (BusPayment $record) => route('bus-receipts.view', $record))
                    ->openUrlInNewTab()
                    ->visible(fn (BusPayment $record) => $record->amount_paid > 0),

                Tables\Actions\Action::make('view_bus_pass')
                    ->label('Bus Pass')
                    ->icon('heroicon-o-ticket')
                    ->color('info')
                    ->url(fn (BusPayment $record) => route('bus-passes.view', $record))
                    ->openUrlInNewTab()
                    ->visible(fn (BusPayment $record) => $record->payment_status !== 'unpaid'),

                Tables\Actions\Action::make('record_payment')
                    ->label('Record Payment')
                    ->icon('heroicon-o-banknotes')
                    ->color('success')
                    ->form([
                        Forms\Components\TextInput::make('payment_amount')
                            ->label('Payment Amount')
                            ->numeric()
                            ->required()
                            ->prefix('ZMW')
                            ->minValue(0.01),

                        Forms\Components\Textarea::make('payment_notes')
                            ->label('Payment Notes')
                            ->rows(2),
                    ])
                    ->action(function (BusPayment $record, array $data) {
                        $paymentAmount = (float) $data['payment_amount'];
                        $newAmountPaid = $record->amount_paid + $paymentAmount;
                        $newBalance = $record->amount - $newAmountPaid;

                        $record->update([
                            'amount_paid' => $newAmountPaid,
                            'balance' => $newBalance,
                            'notes' => ($record->notes ? $record->notes."\n" : '')."Payment of ZMW {$paymentAmount} recorded. ".$data['payment_notes'],
                        ]);

                        $record->updatePaymentStatus();

                        Notification::make()
                            ->title('Payment Recorded')
                            ->body("Payment of ZMW {$paymentAmount} recorded successfully")
                            ->success()
                            ->send();
                    })
                    ->visible(fn (BusPayment $record) => $record->payment_status !== 'paid'),

                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->emptyStateHeading('No Bus Payments Found')
            ->emptyStateDescription('Create bus payments for students or use bulk generation.')
            ->emptyStateIcon('heroicon-o-banknotes');
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
            'index' => Pages\ListBusPayments::route('/'),
            'create' => Pages\CreateBusPayment::route('/create'),
            'view' => Pages\ViewBusPayment::route('/{record}'),
            'edit' => Pages\EditBusPayment::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->with(['student:id,name,grade_id', 'student.grade:id,name', 'busFareStructure:id,route_name']);
    }
}
