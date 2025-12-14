<?php

namespace App\Filament\Resources;

use App\Constants\RoleConstants;
use App\Filament\Resources\QrPaymentResource\Pages;
use App\Models\QrPayment;
use App\Models\Student;
use App\Services\CGrateService;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Str;

class QrPaymentResource extends Resource
{
    protected static ?string $model = QrPayment::class;

    protected static ?string $navigationIcon = 'heroicon-o-qr-code';

    protected static ?string $navigationGroup = 'Finance Management';

    protected static ?string $navigationLabel = 'QR Code Payments';

    protected static ?int $navigationSort = 3;

    public static function shouldRegisterNavigation(): bool
    {
        return ! in_array(auth()->user()?->role_id, [RoleConstants::LIBRARIAN, RoleConstants::TEACHER, RoleConstants::PARENT, RoleConstants::STUDENT]) ?? false;
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Generate QR Code Payment')
                    ->description('Generate a standard QR code for mobile money payment')
                    ->schema([
                        Forms\Components\Select::make('student_id')
                            ->label('Student')
                            ->relationship('student', 'name')
                            ->searchable()
                            ->preload()
                            ->required()
                            ->live()
                            ->afterStateUpdated(function ($state, callable $set) {
                                if ($state) {
                                    $student = Student::with('parentGuardian')->find($state);
                                    if ($student && $student->parentGuardian) {
                                        $set('customer_mobile', $student->parentGuardian->phone);
                                    }

                                    // Get student's outstanding balance
                                    $balance = \App\Models\StudentFee::where('student_id', $state)
                                        ->sum('balance');

                                    if ($balance > 0) {
                                        $set('amount', $balance);
                                    }
                                }
                            }),

                        Forms\Components\TextInput::make('customer_mobile')
                            ->label('Parent/Guardian Mobile Number')
                            ->required()
                            ->tel()
                            ->placeholder('0977123456'),

                        Forms\Components\TextInput::make('amount')
                            ->label('Amount (ZMW)')
                            ->required()
                            ->numeric()
                            ->prefix('ZMW')
                            ->step(0.01)
                            ->helperText('Outstanding balance will be auto-filled'),

                        Forms\Components\TextInput::make('payment_reference')
                            ->label('Payment Reference')
                            ->default(fn () => 'QR-'.strtoupper(Str::random(10)))
                            ->required()
                            ->disabled()
                            ->dehydrated()
                            ->helperText('Auto-generated unique reference'),

                        Forms\Components\DateTimePicker::make('expires_at')
                            ->label('Expires At')
                            ->default(now()->addHours(24))
                            ->required()
                            ->helperText('QR code will expire after this time'),
                    ])
                    ->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('payment_reference')
                    ->label('Reference')
                    ->searchable()
                    ->copyable(),

                Tables\Columns\TextColumn::make('student.name')
                    ->label('Student')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('amount')
                    ->label('Amount')
                    ->money('ZMW')
                    ->sortable(),

                Tables\Columns\TextColumn::make('customer_mobile')
                    ->label('Mobile')
                    ->searchable(),

                Tables\Columns\BadgeColumn::make('status')
                    ->colors([
                        'secondary' => 'pending',
                        'warning' => 'processing',
                        'success' => 'completed',
                        'danger' => 'failed',
                        'gray' => 'expired',
                    ]),

                Tables\Columns\TextColumn::make('initiated_at')
                    ->label('Initiated')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('completed_at')
                    ->label('Completed')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('expires_at')
                    ->label('Expires')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'pending' => 'Pending',
                        'processing' => 'Processing',
                        'completed' => 'Completed',
                        'failed' => 'Failed',
                        'expired' => 'Expired',
                    ]),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),

                Tables\Actions\Action::make('check_status')
                    ->label('Check Status')
                    ->icon('heroicon-o-arrow-path')
                    ->color('info')
                    ->visible(fn (QrPayment $record) => $record->status === 'pending' || $record->status === 'processing')
                    ->action(function (QrPayment $record) {
                        $cgrateService = new CGrateService;
                        $result = $cgrateService->queryCustomerPayment($record->payment_reference);

                        \Log::info('QR Payment status check', [
                            'reference' => $record->payment_reference,
                            'result' => $result,
                            'payment_complete' => $result['payment_complete'] ?? false,
                        ]);

                        if ($result['payment_complete']) {
                            $record->update([
                                'status' => 'completed',
                                'completed_at' => now(),
                                'response_message' => $result['message'] ?? 'Payment completed',
                                'response_code' => $result['responseCode'] ?? null,
                            ]);

                            // Auto-deduct from student balance
                            static::processPayment($record);

                            Notification::make()
                                ->title('Payment Confirmed')
                                ->body("Payment has been completed successfully. Status: {$result['payment_status']}")
                                ->success()
                                ->duration(5000)
                                ->send();
                        } else {
                            // Update response but keep processing status
                            $record->update([
                                'response_message' => $result['message'] ?? 'Payment is still pending',
                                'response_code' => $result['responseCode'] ?? null,
                            ]);

                            Notification::make()
                                ->title('Payment Not Complete')
                                ->body("Status: {$result['payment_status']} - {$result['message']}")
                                ->warning()
                                ->duration(7000)
                                ->send();
                        }
                    }),

                Tables\Actions\Action::make('view_qr')
                    ->label('View QR Code')
                    ->icon('heroicon-o-qr-code')
                    ->color('primary')
                    ->modalHeading('QR Code Payment')
                    ->modalContent(fn (QrPayment $record) => view('filament.modals.qr-code', ['record' => $record]))
                    ->modalSubmitAction(false)
                    ->modalCancelActionLabel('Close'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    //
                ]),
            ]);
    }

    /**
     * Process payment and auto-deduct from student balance
     */
    protected static function processPayment(QrPayment $payment): void
    {
        if (! $payment->student_id || $payment->status !== 'completed') {
            return;
        }

        // Get student's unpaid fees ordered by oldest first
        $fees = \App\Models\StudentFee::where('student_id', $payment->student_id)
            ->where('balance', '>', 0)
            ->orderBy('created_at', 'asc')
            ->get();

        $remainingAmount = $payment->amount;

        foreach ($fees as $fee) {
            if ($remainingAmount <= 0) {
                break;
            }

            $balanceDue = $fee->balance;
            $amountToApply = min($remainingAmount, $balanceDue);

            $newAmountPaid = $fee->amount_paid + $amountToApply;
            $newBalance = $balanceDue - $amountToApply;

            $fee->update([
                'amount_paid' => $newAmountPaid,
                'balance' => $newBalance,
                'payment_status' => $newBalance <= 0 ? 'paid' : 'partial',
            ]);

            $remainingAmount -= $amountToApply;
        }
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
            'index' => Pages\ListQrPayments::route('/'),
            'create' => Pages\CreateQrPayment::route('/create'),
            'view' => Pages\ViewQrPayment::route('/{record}'),
        ];
    }

    public static function getEloquentQuery(): \Illuminate\Database\Eloquent\Builder
    {
        $query = parent::getEloquentQuery()
            ->with(['student:id,name,user_id']);

        $user = auth()->user();

        // Students see only their own QR payments
        if ($user && $user->role_id === RoleConstants::STUDENT) {
            $student = Student::where('user_id', $user->id)->first();
            if ($student) {
                $query->where('student_id', $student->id);
            } else {
                // If no student record found, return empty result
                return $query->whereRaw('1 = 0');
            }
        }

        return $query;
    }
}
