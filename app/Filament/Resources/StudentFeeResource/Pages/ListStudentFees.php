<?php

namespace App\Filament\Resources\StudentFeeResource\Pages;

use App\Filament\Resources\StudentFeeResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;

class ListStudentFees extends ListRecords
{
    protected static string $resource = StudentFeeResource::class;

    /**
     * Modify the table query to include necessary relationships
     */
    protected function getTableQuery(): Builder
    {
        return static::getResource()::getEloquentQuery()
            ->with([
                'student.grade',
                'student.parentGuardian',
                'feeStructure.grade',
                'feeStructure.term',
                'feeStructure.academicYear'
            ]);
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label('Create Student Fee'),

            Actions\Action::make('exportUnpaid')
                ->label('Export Unpaid Fees')
                ->icon('heroicon-o-arrow-down-tray')
                ->color('info')
                ->action(function () {
                    // This could be implemented to export unpaid fees to Excel/CSV
                    \Filament\Notifications\Notification::make()
                        ->title('Export Started')
                        ->body('Unpaid fees export has been initiated.')
                        ->info()
                        ->send();
                }),

            Actions\Action::make('sendBulkReminders')
                ->label('Send Payment Reminders')
                ->icon('heroicon-o-chat-bubble-left-ellipsis')
                ->color('warning')
                ->requiresConfirmation()
                ->modalHeading('Send Payment Reminders')
                ->modalDescription('This will send SMS payment reminders to all parents/guardians with outstanding balances. Continue?')
                ->action(function () {
                    $unpaidFees = static::getResource()::getEloquentQuery()
                        ->where('payment_status', '!=', 'paid')
                        ->where('balance', '>', 0)
                        ->with(['student.parentGuardian', 'feeStructure'])
                        ->get();

                    $successCount = 0;
                    $failedCount = 0;

                    foreach ($unpaidFees as $fee) {
                        try {
                            StudentFeeResource::sendPaymentSMS($fee);
                            $successCount++;
                        } catch (\Exception $e) {
                            $failedCount++;
                            \Illuminate\Support\Facades\Log::error('Failed to send bulk reminder SMS', [
                                'student_fee_id' => $fee->id,
                                'error' => $e->getMessage()
                            ]);
                        }
                    }

                    \Filament\Notifications\Notification::make()
                        ->title('Bulk Reminders Sent')
                        ->body("Successfully sent: {$successCount}, Failed: {$failedCount}")
                        ->success($successCount > 0)
                        ->warning($failedCount > 0)
                        ->send();
                }),
        ];
    }

    /**
     * Get stats for the page header
     */
    public function getHeaderWidgets(): array
    {
        return [
            StudentFeeResource\Widgets\StudentFeeStatsWidget::class,
        ];
    }
}
