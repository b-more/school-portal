<?php

namespace App\Filament\Resources\LeaveApplicationResource\Pages;

use App\Constants\RoleConstants;
use App\Filament\Resources\LeaveApplicationResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;
use Filament\Notifications\Notification;
use Filament\Forms;

class ViewLeaveApplication extends ViewRecord
{
    protected static string $resource = LeaveApplicationResource::class;

    protected function getHeaderActions(): array
    {
        $user = auth()->user();
        $isAdmin = $user?->role_id === RoleConstants::ADMIN;

        return [
            Actions\EditAction::make()
                ->visible(fn () => $this->record->status === 'pending'),

            // Print Leave Approval Letter
            Actions\Action::make('print_letter')
                ->label('Print Letter')
                ->icon('heroicon-o-printer')
                ->color('gray')
                ->url(fn () => route('leave-applications.pdf', $this->record))
                ->openUrlInNewTab()
                ->visible(fn () => in_array($this->record->status, ['approved', 'rejected'])),

            // Download Leave Approval Letter
            Actions\Action::make('download_letter')
                ->label('Download PDF')
                ->icon('heroicon-o-arrow-down-tray')
                ->color('info')
                ->url(fn () => route('leave-applications.download', $this->record))
                ->visible(fn () => in_array($this->record->status, ['approved', 'rejected'])),

            // HOD Approval
            Actions\Action::make('approve_hod')
                ->label('HOD Approve')
                ->icon('heroicon-o-check')
                ->color('info')
                ->requiresConfirmation()
                ->form([
                    Forms\Components\Textarea::make('hod_remarks')
                        ->label('Remarks')
                        ->rows(2),
                ])
                ->action(function (array $data) {
                    $this->record->update([
                        'status' => 'approved_by_hod',
                        'hod_approved_by' => auth()->id(),
                        'hod_approved_at' => now(),
                        'hod_remarks' => $data['hod_remarks'] ?? null,
                    ]);

                    Notification::make()
                        ->title('Leave application approved by HOD')
                        ->success()
                        ->send();
                })
                ->visible(fn () => $this->record->status === 'pending' && $isAdmin),

            // Headteacher Approval
            Actions\Action::make('approve_head')
                ->label('Head Approve')
                ->icon('heroicon-o-check')
                ->color('info')
                ->requiresConfirmation()
                ->form([
                    Forms\Components\Textarea::make('head_remarks')
                        ->label('Remarks')
                        ->rows(2),
                ])
                ->action(function (array $data) {
                    $this->record->update([
                        'status' => 'approved_by_head',
                        'head_approved_by' => auth()->id(),
                        'head_approved_at' => now(),
                        'head_remarks' => $data['head_remarks'] ?? null,
                    ]);

                    Notification::make()
                        ->title('Leave application approved by Headteacher')
                        ->success()
                        ->send();
                })
                ->visible(fn () => $this->record->status === 'approved_by_hod' && $isAdmin),

            // Final Approval
            Actions\Action::make('final_approve')
                ->label('Final Approve')
                ->icon('heroicon-o-check-circle')
                ->color('success')
                ->requiresConfirmation()
                ->form([
                    Forms\Components\Textarea::make('approval_remarks')
                        ->label('Remarks')
                        ->rows(2),
                ])
                ->action(function (array $data) {
                    $this->record->update([
                        'status' => 'approved',
                        'approved_by' => auth()->id(),
                        'approved_at' => now(),
                        'approval_remarks' => $data['approval_remarks'] ?? null,
                    ]);

                    // Deduct from leave balance
                    $balance = \App\Models\LeaveBalance::getOrCreate(
                        $this->record->employee_id,
                        $this->record->leave_type_id
                    );
                    $balance->deductDays($this->record->days_requested);

                    Notification::make()
                        ->title('Leave application approved')
                        ->success()
                        ->send();
                })
                ->visible(fn () => $this->record->status === 'approved_by_head' && $isAdmin),

            // Reject
            Actions\Action::make('reject')
                ->label('Reject')
                ->icon('heroicon-o-x-circle')
                ->color('danger')
                ->requiresConfirmation()
                ->form([
                    Forms\Components\Textarea::make('rejection_reason')
                        ->label('Reason for Rejection')
                        ->required()
                        ->rows(3),
                ])
                ->action(function (array $data) {
                    $this->record->update([
                        'status' => 'rejected',
                        'rejected_by' => auth()->id(),
                        'rejected_at' => now(),
                        'rejection_reason' => $data['rejection_reason'],
                    ]);

                    Notification::make()
                        ->title('Leave application rejected')
                        ->warning()
                        ->send();
                })
                ->visible(fn () => $this->record->isPending() && $isAdmin),
        ];
    }
}
