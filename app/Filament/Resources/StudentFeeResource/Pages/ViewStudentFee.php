<?php

namespace App\Filament\Resources\StudentFeeResource\Pages;

use App\Filament\Resources\StudentFeeResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;
use Filament\Infolists;
use Filament\Infolists\Infolist;

class ViewStudentFee extends ViewRecord
{
    protected static string $resource = StudentFeeResource::class;

    /**
     * Configure the record infolist
     */
    public function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Infolists\Components\Section::make('Student Information')
                    ->schema([
                        Infolists\Components\TextEntry::make('student.name')
                            ->label('Student Name'),
                        Infolists\Components\TextEntry::make('student.student_id_number')
                            ->label('Student ID'),
                        Infolists\Components\TextEntry::make('student.grade.name')
                            ->label('Student Grade'),
                        Infolists\Components\TextEntry::make('student.classSection.name')
                            ->label('Section'),
                    ])
                    ->columns(2),

                Infolists\Components\Section::make('Fee Structure Information')
                    ->schema([
                        Infolists\Components\TextEntry::make('feeStructure.name')
                            ->label('Fee Structure Name')
                            ->default('Standard Fee'),
                        Infolists\Components\TextEntry::make('feeStructure.grade.name')
                            ->label('Grade'),
                        Infolists\Components\TextEntry::make('feeStructure.term.name')
                            ->label('Term'),
                        Infolists\Components\TextEntry::make('feeStructure.academicYear.name')
                            ->label('Academic Year'),
                        Infolists\Components\TextEntry::make('feeStructure.basic_fee')
                            ->label('Basic Fee')
                            ->money('ZMW'),
                        Infolists\Components\TextEntry::make('feeStructure.total_fee')
                            ->label('Total Fee')
                            ->money('ZMW')
                            ->weight('bold'),
                    ])
                    ->columns(2),

                Infolists\Components\Section::make('Payment Information')
                    ->schema([
                        Infolists\Components\TextEntry::make('payment_status')
                            ->label('Payment Status')
                            ->badge()
                            ->color(fn (string $state): string => match ($state) {
                                'paid' => 'success',
                                'partial' => 'warning',
                                'unpaid' => 'danger',
                            }),
                        Infolists\Components\TextEntry::make('amount_paid')
                            ->label('Amount Paid')
                            ->money('ZMW'),
                        Infolists\Components\TextEntry::make('balance')
                            ->label('Outstanding Balance')
                            ->money('ZMW')
                            ->color(fn ($state) => $state > 0 ? 'danger' : 'success'),
                        Infolists\Components\TextEntry::make('payment_date')
                            ->label('Payment Date')
                            ->date('F j, Y')
                            ->placeholder('Not paid yet'),
                        Infolists\Components\TextEntry::make('receipt_number')
                            ->label('Receipt Number')
                            ->placeholder('No receipt'),
                        Infolists\Components\TextEntry::make('payment_method')
                            ->label('Payment Method')
                            ->formatStateUsing(fn ($state) => $state ? ucfirst(str_replace('_', ' ', $state)) : 'Not specified'),
                    ])
                    ->columns(2),

                Infolists\Components\Section::make('Additional Information')
                    ->schema([
                        Infolists\Components\TextEntry::make('notes')
                            ->label('Notes')
                            ->placeholder('No additional notes')
                            ->columnSpanFull(),
                        Infolists\Components\TextEntry::make('created_at')
                            ->label('Created')
                            ->dateTime('F j, Y \a\t g:i A'),
                        Infolists\Components\TextEntry::make('updated_at')
                            ->label('Last Updated')
                            ->dateTime('F j, Y \a\t g:i A'),
                    ])
                    ->columns(2),

                Infolists\Components\Section::make('Parent/Guardian Information')
                    ->schema([
                        Infolists\Components\TextEntry::make('student.parentGuardian.name')
                            ->label('Parent/Guardian Name')
                            ->placeholder('Not assigned'),
                        Infolists\Components\TextEntry::make('student.parentGuardian.phone')
                            ->label('Phone Number')
                            ->placeholder('Not provided'),
                        Infolists\Components\TextEntry::make('student.parentGuardian.relationship')
                            ->label('Relationship')
                            ->formatStateUsing(fn ($state) => $state ? ucfirst($state) : 'Not specified'),
                        Infolists\Components\TextEntry::make('student.parentGuardian.email')
                            ->label('Email')
                            ->placeholder('Not provided'),
                    ])
                    ->columns(2)
                    ->visible(fn () => $this->record->student?->parentGuardian !== null),
            ]);
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
            Actions\DeleteAction::make(),

            Actions\Action::make('generateReceipt')
                ->label('Generate Receipt')
                ->icon('heroicon-o-document-text')
                ->color('success')
                ->url(fn () => route('student-fees.receipt', $this->record))
                ->openUrlInNewTab()
                ->visible(fn () => $this->record->payment_status !== 'unpaid'),

            Actions\Action::make('viewReceiptHtml')
                ->label('View Receipt')
                ->icon('heroicon-o-eye')
                ->color('info')
                ->url(fn () => route('student-fees.receipt.view', $this->record))
                ->openUrlInNewTab()
                ->visible(fn () => $this->record->payment_status !== 'unpaid'),

            Actions\Action::make('recordPayment')
                ->label('Record Payment')
                ->icon('heroicon-o-currency-dollar')
                ->color('warning')
                ->form([
                    \Filament\Forms\Components\Placeholder::make('current_balance')
                        ->label('Current Balance')
                        ->content(fn () => "ZMW " . number_format($this->record->balance, 2)),

                    \Filament\Forms\Components\TextInput::make('payment_amount')
                        ->label('Payment Amount')
                        ->numeric()
                        ->required()
                        ->prefix('ZMW')
                        ->step(0.01)
                        ->maxValue(fn () => $this->record->balance),

                    \Filament\Forms\Components\DatePicker::make('payment_date')
                        ->required()
                        ->default(now()),

                    \Filament\Forms\Components\TextInput::make('receipt_number')
                        ->required()
                        ->maxLength(255)
                        ->default(function () {
                            return 'RCP-' . date('Y') . '-' . str_pad(mt_rand(1, 9999), 4, '0', STR_PAD_LEFT);
                        }),

                    \Filament\Forms\Components\Select::make('payment_method')
                        ->options([
                            'cash' => 'Cash',
                            'bank_transfer' => 'Bank Transfer',
                            'mobile_money' => 'Mobile Money',
                            'cheque' => 'Cheque',
                            'other' => 'Other',
                        ])
                        ->required(),

                    \Filament\Forms\Components\Textarea::make('payment_notes')
                        ->label('Payment Notes')
                        ->maxLength(65535)
                        ->rows(3),

                    \Filament\Forms\Components\Toggle::make('send_sms_notification')
                        ->label('Send SMS Notification')
                        ->helperText('Send an SMS notification to the parent/guardian about this payment')
                        ->default(true),
                ])
                ->action(function (array $data): void {
                    $paymentAmount = (float) $data['payment_amount'];
                    $newAmountPaid = (float) $this->record->amount_paid + $paymentAmount;

                    // Make sure we have the fee structure loaded
                    if (!$this->record->feeStructure) {
                        $this->record->load('feeStructure');
                    }

                    $totalFee = (float) $this->record->feeStructure->total_fee;
                    $newBalance = $totalFee - $newAmountPaid;
                    $status = 'partial';

                    if ($newBalance <= 0) {
                        $status = 'paid';
                        $newBalance = 0;
                    }

                    $this->record->update([
                        'amount_paid' => $newAmountPaid,
                        'balance' => $newBalance,
                        'payment_status' => $status,
                        'payment_date' => $data['payment_date'],
                        'receipt_number' => $data['receipt_number'],
                        'payment_method' => $data['payment_method'],
                        'notes' => $data['payment_notes'] ?? $this->record->notes,
                    ]);

                    // Send SMS notification if requested
                    if (isset($data['send_sms_notification']) && $data['send_sms_notification']) {
                        try {
                            StudentFeeResource::sendPaymentSMS($this->record, $paymentAmount);
                        } catch (\Exception $e) {
                            // SMS failed but don't fail the payment
                            \Illuminate\Support\Facades\Log::error('SMS failed after payment', [
                                'fee_id' => $this->record->id,
                                'error' => $e->getMessage()
                            ]);
                        }
                    }

                    \Filament\Notifications\Notification::make()
                        ->title('Payment Recorded')
                        ->body("Payment of ZMW {$paymentAmount} has been recorded successfully.")
                        ->success()
                        ->send();

                    // Refresh the page to show updated data
                    $this->refreshFormData([
                        'amount_paid',
                        'balance',
                        'payment_status',
                        'payment_date',
                        'receipt_number',
                        'payment_method'
                    ]);
                })
                ->visible(fn () => $this->record->payment_status !== 'paid'),

            Actions\Action::make('sendSMS')
                ->label('Send SMS Receipt')
                ->icon('heroicon-o-chat-bubble-left')
                ->color('gray')
                ->action(function () {
                    try {
                        StudentFeeResource::sendPaymentSMS($this->record);

                        \Filament\Notifications\Notification::make()
                            ->title('SMS Sent')
                            ->body('Payment receipt SMS has been sent successfully.')
                            ->success()
                            ->send();
                    } catch (\Exception $e) {
                        \Filament\Notifications\Notification::make()
                            ->title('SMS Failed')
                            ->body($e->getMessage())
                            ->danger()
                            ->send();
                    }
                })
                ->visible(fn () => $this->record->payment_status !== 'unpaid'),
        ];
    }

    /**
     * Mount the record with necessary relationships
     */
    protected function resolveRecord(int | string $key): \Illuminate\Database\Eloquent\Model
    {
        $record = parent::resolveRecord($key);

        // Load all necessary relationships
        $record->load([
            'student.parentGuardian',
            'student.grade',
            'student.classSection',
            'feeStructure.grade',
            'feeStructure.term',
            'feeStructure.academicYear'
        ]);

        return $record;
    }
}
