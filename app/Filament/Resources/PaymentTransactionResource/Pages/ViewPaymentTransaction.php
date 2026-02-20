<?php

namespace App\Filament\Resources\PaymentTransactionResource\Pages;

use App\Filament\Resources\PaymentTransactionResource;
use Filament\Actions;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Filament\Resources\Pages\ViewRecord;

class ViewPaymentTransaction extends ViewRecord
{
    protected static string $resource = PaymentTransactionResource::class;

    public function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Infolists\Components\Section::make('Transaction Summary')
                    ->icon('heroicon-o-banknotes')
                    ->schema([
                        Infolists\Components\TextEntry::make('amount')
                            ->label('Amount')
                            ->money('ZMW')
                            ->size(Infolists\Components\TextEntry\TextEntrySize::Large)
                            ->weight('bold')
                            ->color('success'),
                        Infolists\Components\TextEntry::make('reference_number')
                            ->label('Receipt Number')
                            ->copyable()
                            ->copyMessage('Receipt number copied!')
                            ->icon('heroicon-m-document-text')
                            ->weight('semibold'),
                        Infolists\Components\TextEntry::make('transaction_date')
                            ->label('Transaction Date')
                            ->dateTime('F j, Y \a\t g:i A')
                            ->icon('heroicon-m-calendar'),
                        Infolists\Components\TextEntry::make('payment_method')
                            ->label('Payment Method')
                            ->badge()
                            ->formatStateUsing(fn ($state) => ucfirst(str_replace('_', ' ', $state ?? 'N/A')))
                            ->icon(fn ($state) => match ($state) {
                                'cash' => 'heroicon-m-banknotes',
                                'mobile_money' => 'heroicon-m-device-phone-mobile',
                                'bank_transfer' => 'heroicon-m-building-library',
                                'cheque' => 'heroicon-m-document-check',
                                default => 'heroicon-m-question-mark-circle',
                            })
                            ->color(fn ($state) => match ($state) {
                                'cash' => 'success',
                                'mobile_money' => 'info',
                                'bank_transfer' => 'warning',
                                'cheque' => 'primary',
                                default => 'gray',
                            }),
                        Infolists\Components\TextEntry::make('type')
                            ->label('Transaction Type')
                            ->badge()
                            ->formatStateUsing(fn ($state) => match ($state) {
                                'payment' => 'Payment',
                                'refund' => 'Refund',
                                'adjustment' => 'Adjustment',
                                'balance_forward' => 'Balance Forward',
                                'overpayment' => 'Overpayment',
                                'credit_applied' => 'Credit Applied',
                                default => ucfirst($state ?? 'N/A'),
                            })
                            ->color(fn ($state) => match ($state) {
                                'payment' => 'success',
                                'refund' => 'danger',
                                'adjustment' => 'warning',
                                'overpayment' => 'info',
                                default => 'gray',
                            }),
                        Infolists\Components\TextEntry::make('status')
                            ->label('Status')
                            ->badge()
                            ->formatStateUsing(fn ($state) => ucfirst($state ?? 'completed'))
                            ->color(fn ($state) => match ($state) {
                                'completed' => 'success',
                                'pending' => 'warning',
                                'failed' => 'danger',
                                default => 'success',
                            }),
                    ])
                    ->columns(3),

                Infolists\Components\Section::make('Student & Fee Details')
                    ->icon('heroicon-o-academic-cap')
                    ->schema([
                        Infolists\Components\TextEntry::make('studentFee.student.name')
                            ->label('Student Name')
                            ->icon('heroicon-m-user')
                            ->weight('medium'),
                        Infolists\Components\TextEntry::make('studentFee.student.student_id_number')
                            ->label('Student ID'),
                        Infolists\Components\TextEntry::make('studentFee.student.grade.name')
                            ->label('Grade')
                            ->badge()
                            ->color('info'),
                        Infolists\Components\TextEntry::make('studentFee.student.classSection.name')
                            ->label('Class Section')
                            ->placeholder('Not assigned'),
                        Infolists\Components\TextEntry::make('studentFee.feeStructure.term.name')
                            ->label('Term')
                            ->badge()
                            ->color('primary'),
                        Infolists\Components\TextEntry::make('studentFee.feeStructure.academicYear.name')
                            ->label('Academic Year'),
                        Infolists\Components\TextEntry::make('studentFee.feeStructure.total_fee')
                            ->label('Total Fee')
                            ->money('ZMW')
                            ->weight('bold'),
                        Infolists\Components\TextEntry::make('studentFee.amount_paid')
                            ->label('Amount Paid So Far')
                            ->money('ZMW'),
                        Infolists\Components\TextEntry::make('studentFee.balance')
                            ->label('Outstanding Balance')
                            ->money('ZMW')
                            ->weight('bold')
                            ->color(fn ($state) => $state > 0 ? 'danger' : 'success'),
                    ])
                    ->columns(3),

                Infolists\Components\Section::make('Parent/Guardian')
                    ->icon('heroicon-o-users')
                    ->schema([
                        Infolists\Components\TextEntry::make('studentFee.student.parentGuardian.name')
                            ->label('Name')
                            ->placeholder('Not assigned'),
                        Infolists\Components\TextEntry::make('studentFee.student.parentGuardian.phone')
                            ->label('Phone Number')
                            ->placeholder('Not provided'),
                        Infolists\Components\TextEntry::make('studentFee.student.parentGuardian.relationship')
                            ->label('Relationship')
                            ->formatStateUsing(fn ($state) => $state ? ucfirst($state) : 'Not specified'),
                        Infolists\Components\TextEntry::make('studentFee.student.parentGuardian.email')
                            ->label('Email')
                            ->placeholder('Not provided'),
                    ])
                    ->columns(2)
                    ->visible(fn () => $this->record->studentFee?->student?->parentGuardian !== null),

                Infolists\Components\Section::make('Processing Details')
                    ->icon('heroicon-o-cog-6-tooth')
                    ->schema([
                        Infolists\Components\TextEntry::make('processedBy.name')
                            ->label('Processed By')
                            ->icon('heroicon-m-user-circle')
                            ->placeholder('System'),
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
                    ->columns(2)
                    ->collapsed(),
            ]);
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('printReceipt')
                ->label('Print Receipt')
                ->icon('heroicon-o-printer')
                ->color('success')
                ->url(fn ($record) => route('student-fees.transaction-receipt', [
                    'fee' => $record->student_fee_id,
                    'transaction' => $record->id,
                ]))
                ->openUrlInNewTab(),
        ];
    }

    protected function resolveRecord(int|string $key): \Illuminate\Database\Eloquent\Model
    {
        $record = parent::resolveRecord($key);

        $record->load([
            'studentFee.student.parentGuardian',
            'studentFee.student.grade',
            'studentFee.student.classSection',
            'studentFee.feeStructure.grade',
            'studentFee.feeStructure.term',
            'studentFee.feeStructure.academicYear',
            'processedBy',
        ]);

        return $record;
    }
}
