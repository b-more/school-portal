<?php

namespace App\Filament\Resources\LeaveApplicationResource\Pages;

use App\Constants\RoleConstants;
use App\Filament\Resources\LeaveApplicationResource;
use App\Models\Employee;
use App\Models\LeaveApplication;
use Filament\Resources\Pages\CreateRecord;
use Filament\Notifications\Notification;
use Illuminate\Validation\ValidationException;

class CreateLeaveApplication extends CreateRecord
{
    protected static string $resource = LeaveApplicationResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $user = auth()->user();

        // If employee_id is not set (disabled field for non-admin), get it from current user
        if (empty($data['employee_id'])) {
            $employee = Employee::where('user_id', $user->id)->first();
            if ($employee) {
                $data['employee_id'] = $employee->id;
            }
        }

        // Check for currently running leave
        $this->validateNoRunningLeave($data['employee_id']);

        // Check for overlapping leave applications
        $this->validateNoOverlappingLeave($data['employee_id'], $data['start_date'], $data['end_date']);

        // Ensure status is set to pending
        $data['status'] = 'pending';

        return $data;
    }

    /**
     * Check if employee has a currently running approved leave
     */
    protected function validateNoRunningLeave(int $employeeId): void
    {
        $today = now()->toDateString();

        $runningLeave = LeaveApplication::where('employee_id', $employeeId)
            ->where('status', 'approved')
            ->where('start_date', '<=', $today)
            ->where('end_date', '>=', $today)
            ->first();

        if ($runningLeave) {
            Notification::make()
                ->danger()
                ->title('Cannot Apply for Leave')
                ->body("You currently have an approved leave running from {$runningLeave->start_date->format('d M Y')} to {$runningLeave->end_date->format('d M Y')}. Please wait until your current leave ends.")
                ->persistent()
                ->send();

            throw ValidationException::withMessages([
                'start_date' => "You have a leave currently running (Ref: {$runningLeave->reference_number}). Please wait until it ends.",
            ]);
        }
    }

    /**
     * Check if the requested dates overlap with existing leave applications
     */
    protected function validateNoOverlappingLeave(int $employeeId, string $startDate, string $endDate): void
    {
        // Check for overlapping approved or pending leaves
        $overlappingLeave = LeaveApplication::where('employee_id', $employeeId)
            ->whereIn('status', ['pending', 'approved_by_hod', 'approved_by_head', 'approved'])
            ->where(function ($query) use ($startDate, $endDate) {
                // Check if new leave overlaps with existing leave
                $query->where(function ($q) use ($startDate, $endDate) {
                    // New start date falls within existing leave period
                    $q->where('start_date', '<=', $startDate)
                      ->where('end_date', '>=', $startDate);
                })->orWhere(function ($q) use ($startDate, $endDate) {
                    // New end date falls within existing leave period
                    $q->where('start_date', '<=', $endDate)
                      ->where('end_date', '>=', $endDate);
                })->orWhere(function ($q) use ($startDate, $endDate) {
                    // Existing leave falls within new leave period
                    $q->where('start_date', '>=', $startDate)
                      ->where('end_date', '<=', $endDate);
                });
            })
            ->first();

        if ($overlappingLeave) {
            $statusLabel = match ($overlappingLeave->status) {
                'pending' => 'pending approval',
                'approved_by_hod' => 'approved by HOD',
                'approved_by_head' => 'approved by Headteacher',
                'approved' => 'approved',
                default => $overlappingLeave->status,
            };

            Notification::make()
                ->danger()
                ->title('Overlapping Leave Application')
                ->body("Your requested dates overlap with an existing leave application ({$statusLabel}) from {$overlappingLeave->start_date->format('d M Y')} to {$overlappingLeave->end_date->format('d M Y')}.")
                ->persistent()
                ->send();

            throw ValidationException::withMessages([
                'start_date' => "These dates overlap with another leave application (Ref: {$overlappingLeave->reference_number}).",
            ]);
        }
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function getCreatedNotification(): ?Notification
    {
        return Notification::make()
            ->success()
            ->title('Leave Application Submitted')
            ->body('Your leave application has been submitted for approval.');
    }
}
