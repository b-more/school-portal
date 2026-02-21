<?php

namespace App\Filament\Resources;

use App\Constants\RoleConstants;
use App\Filament\Resources\LeaveApplicationResource\Pages;
use App\Models\Employee;
use App\Models\LeaveApplication;
use App\Models\LeaveBalance;
use App\Models\LeaveType;
use App\Services\SmsService;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Builder;

class LeaveApplicationResource extends Resource
{
    protected static ?string $model = LeaveApplication::class;

    protected static ?string $navigationIcon = 'heroicon-o-calendar-days';

    protected static ?string $navigationGroup = 'Staff Management';

    protected static ?int $navigationSort = 2;

    protected static ?string $navigationLabel = 'Leave Applications';

    public static function getNavigationBadge(): ?string
    {
        $user = auth()->user();
        if ($user?->role_id === RoleConstants::ADMIN) {
            return static::getModel()::where('status', 'pending')
                ->orWhere('status', 'approved_by_hod')
                ->orWhere('status', 'approved_by_head')
                ->count() ?: null;
        }
        return null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'warning';
    }

    public static function form(Form $form): Form
    {
        $user = auth()->user();
        $isAdmin = $user?->role_id === RoleConstants::ADMIN;

        return $form
            ->schema([
                Forms\Components\Section::make('Leave Request')
                    ->schema([
                        Forms\Components\Select::make('employee_id')
                            ->label('Employee')
                            ->options(Employee::where('status', 'active')->pluck('name', 'id'))
                            ->searchable()
                            ->preload()
                            ->required()
                            ->live()
                            ->disabled(!$isAdmin)
                            ->dehydrated() // Ensure value is submitted even when disabled
                            ->default(function () use ($user) {
                                $employee = Employee::where('user_id', $user?->id)->first();
                                return $employee?->id;
                            }),

                        Forms\Components\Select::make('leave_type_id')
                            ->label('Leave Type')
                            ->options(function (Get $get) {
                                $employeeId = $get('employee_id');
                                $employee = Employee::find($employeeId);

                                return LeaveType::where('is_active', true)
                                    ->get()
                                    ->filter(fn ($type) => !$employee || $type->isAvailableFor($employee))
                                    ->pluck('name', 'id');
                            })
                            ->required()
                            ->live()
                            ->afterStateUpdated(function (Get $get, Set $set) {
                                $employeeId = $get('employee_id');
                                $leaveTypeId = $get('leave_type_id');

                                if ($employeeId && $leaveTypeId) {
                                    $balance = LeaveBalance::getOrCreate($employeeId, $leaveTypeId);
                                    $set('available_balance', $balance->remaining_days);
                                }
                            }),

                        Forms\Components\Placeholder::make('available_balance')
                            ->label('Available Balance')
                            ->content(function (Get $get) {
                                $employeeId = $get('employee_id');
                                $leaveTypeId = $get('leave_type_id');

                                if (!$employeeId || !$leaveTypeId) return 'Select employee and leave type';

                                $balance = LeaveBalance::getOrCreate($employeeId, $leaveTypeId);
                                return $balance->remaining_days . ' days';
                            }),
                    ])->columns(3),

                Forms\Components\Section::make('Leave Period')
                    ->schema([
                        Forms\Components\DatePicker::make('start_date')
                            ->required()
                            ->minDate(now())
                            ->live()
                            ->afterStateUpdated(function (Get $get, Set $set) {
                                $start = $get('start_date');
                                $end = $get('end_date');
                                $isHalfDay = $get('is_half_day');

                                if ($start && $end) {
                                    $days = static::calculateWorkingDays($start, $end);
                                    if ($isHalfDay) $days = 0.5;
                                    $set('days_requested', $days);
                                }
                            })
                            ->rules([
                                fn (Get $get): \Closure => function (string $attribute, $value, \Closure $fail) use ($get) {
                                    if (!$value) return;

                                    $employeeId = $get('employee_id');
                                    if (!$employeeId) return;

                                    // Check for running leave
                                    $today = now()->toDateString();
                                    $runningLeave = LeaveApplication::where('employee_id', $employeeId)
                                        ->where('status', 'approved')
                                        ->where('start_date', '<=', $today)
                                        ->where('end_date', '>=', $today)
                                        ->first();

                                    if ($runningLeave) {
                                        $fail("You have a leave currently running until {$runningLeave->end_date->format('d M Y')}. Please wait until it ends.");
                                    }
                                },
                            ]),

                        Forms\Components\DatePicker::make('end_date')
                            ->required()
                            ->afterOrEqual('start_date')
                            ->live()
                            ->afterStateUpdated(function (Get $get, Set $set) {
                                $start = $get('start_date');
                                $end = $get('end_date');
                                $isHalfDay = $get('is_half_day');

                                if ($start && $end) {
                                    $days = static::calculateWorkingDays($start, $end);
                                    if ($isHalfDay) $days = 0.5;
                                    $set('days_requested', $days);
                                }
                            })
                            ->rules([
                                fn (Get $get): \Closure => function (string $attribute, $value, \Closure $fail) use ($get) {
                                    if (!$value) return;

                                    $employeeId = $get('employee_id');
                                    $startDate = $get('start_date');
                                    if (!$employeeId || !$startDate) return;

                                    // Check for overlapping leaves
                                    $overlappingLeave = LeaveApplication::where('employee_id', $employeeId)
                                        ->whereIn('status', ['pending', 'approved_by_hod', 'approved_by_head', 'approved'])
                                        ->where(function ($query) use ($startDate, $value) {
                                            $query->where(function ($q) use ($startDate, $value) {
                                                $q->where('start_date', '<=', $startDate)
                                                  ->where('end_date', '>=', $startDate);
                                            })->orWhere(function ($q) use ($startDate, $value) {
                                                $q->where('start_date', '<=', $value)
                                                  ->where('end_date', '>=', $value);
                                            })->orWhere(function ($q) use ($startDate, $value) {
                                                $q->where('start_date', '>=', $startDate)
                                                  ->where('end_date', '<=', $value);
                                            });
                                        })
                                        ->first();

                                    if ($overlappingLeave) {
                                        $fail("These dates overlap with existing leave (Ref: {$overlappingLeave->reference_number}, {$overlappingLeave->start_date->format('d M')} - {$overlappingLeave->end_date->format('d M Y')}).");
                                    }
                                },
                            ]),

                        Forms\Components\TextInput::make('days_requested')
                            ->label('Days Requested')
                            ->numeric()
                            ->required()
                            ->disabled()
                            ->dehydrated()
                            ->helperText('Excludes weekends (Sat & Sun)'),

                        Forms\Components\Toggle::make('is_half_day')
                            ->label('Half Day?')
                            ->live()
                            ->afterStateUpdated(function (Get $get, Set $set, $state) {
                                if ($state) {
                                    $set('days_requested', 0.5);
                                    $set('end_date', $get('start_date'));
                                } else {
                                    $start = $get('start_date');
                                    $end = $get('end_date');
                                    if ($start && $end) {
                                        $days = static::calculateWorkingDays($start, $end);
                                        $set('days_requested', $days);
                                    }
                                }
                            }),

                        Forms\Components\Select::make('half_day_period')
                            ->options([
                                'morning' => 'Morning',
                                'afternoon' => 'Afternoon',
                            ])
                            ->visible(fn (Get $get) => $get('is_half_day')),
                    ])->columns(3),

                Forms\Components\Section::make('Details')
                    ->schema([
                        Forms\Components\Textarea::make('reason')
                            ->required()
                            ->rows(3)
                            ->columnSpanFull()
                            ->placeholder('Please provide a reason for your leave request...'),

                        Forms\Components\TextInput::make('contact_during_leave')
                            ->label('Contact Number During Leave')
                            ->tel()
                            ->placeholder('Phone number where you can be reached'),

                        Forms\Components\Select::make('covering_employee_id')
                            ->label('Covering Employee')
                            ->options(Employee::where('status', 'active')->pluck('name', 'id'))
                            ->searchable()
                            ->preload()
                            ->helperText('Who will cover your duties?'),

                        Forms\Components\Textarea::make('handover_notes')
                            ->rows(3)
                            ->columnSpanFull()
                            ->placeholder('Any notes for handover or pending work...'),

                        Forms\Components\FileUpload::make('attachment')
                            ->label('Supporting Document')
                            ->directory('leave-attachments')
                            ->acceptedFileTypes(['application/pdf', 'image/*'])
                            ->maxSize(5120)
                            ->helperText('Medical certificate or other supporting documents'),
                    ])->columns(2),

                // Admin-only approval section
                Forms\Components\Section::make('Approval Status')
                    ->schema([
                        Forms\Components\Select::make('status')
                            ->options([
                                'pending' => 'Pending',
                                'approved_by_hod' => 'HOD Approved',
                                'approved_by_head' => 'Head Approved',
                                'approved' => 'Approved',
                                'rejected' => 'Rejected',
                                'cancelled' => 'Cancelled',
                            ])
                            ->disabled(!$isAdmin),

                        Forms\Components\Textarea::make('approval_remarks')
                            ->label('Approval Remarks')
                            ->rows(2)
                            ->visible($isAdmin),
                    ])
                    ->columns(2)
                    ->visible(fn (?LeaveApplication $record) => $record !== null),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('reference_number')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('employee.name')
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
                    ->label('Days')
                    ->alignCenter(),
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
                    ->label('Applied On')
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
                    ])
                    ->multiple(),
                Tables\Filters\SelectFilter::make('leave_type_id')
                    ->label('Leave Type')
                    ->options(LeaveType::pluck('name', 'id')),
                Tables\Filters\SelectFilter::make('employee_id')
                    ->label('Employee')
                    ->options(Employee::where('status', 'active')->pluck('name', 'id'))
                    ->searchable(),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make()
                    ->visible(fn ($record) => $record->status === 'pending'),

                // Print Leave Approval Letter
                Tables\Actions\Action::make('print_letter')
                    ->label('Print Letter')
                    ->icon('heroicon-o-printer')
                    ->color('gray')
                    ->url(fn ($record) => route('leave-applications.pdf', $record))
                    ->openUrlInNewTab()
                    ->visible(fn ($record) => in_array($record->status, ['approved', 'rejected'])),

                // Download Leave Approval Letter
                Tables\Actions\Action::make('download_letter')
                    ->label('Download')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->color('info')
                    ->url(fn ($record) => route('leave-applications.download', $record))
                    ->visible(fn ($record) => in_array($record->status, ['approved', 'rejected'])),

                // HOD Approval
                Tables\Actions\Action::make('approve_hod')
                    ->label('HOD Approve')
                    ->icon('heroicon-o-check')
                    ->color('info')
                    ->requiresConfirmation()
                    ->form([
                        Forms\Components\Textarea::make('hod_remarks')
                            ->label('Remarks')
                            ->rows(2),
                    ])
                    ->action(function (LeaveApplication $record, array $data) {
                        $record->update([
                            'status' => 'approved_by_hod',
                            'hod_approved_by' => auth()->id(),
                            'hod_approved_at' => now(),
                            'hod_remarks' => $data['hod_remarks'] ?? null,
                        ]);

                        // Send SMS notification
                        static::sendLeaveStatusSms($record, 'hod_approved');

                        Notification::make()
                            ->title('Leave application approved by HOD')
                            ->success()
                            ->send();
                    })
                    ->visible(fn ($record) => $record->status === 'pending' && auth()->user()?->role_id === RoleConstants::ADMIN),

                // Headteacher Approval
                Tables\Actions\Action::make('approve_head')
                    ->label('Head Approve')
                    ->icon('heroicon-o-check')
                    ->color('info')
                    ->requiresConfirmation()
                    ->form([
                        Forms\Components\Textarea::make('head_remarks')
                            ->label('Remarks')
                            ->rows(2),
                    ])
                    ->action(function (LeaveApplication $record, array $data) {
                        $record->update([
                            'status' => 'approved_by_head',
                            'head_approved_by' => auth()->id(),
                            'head_approved_at' => now(),
                            'head_remarks' => $data['head_remarks'] ?? null,
                        ]);

                        // Send SMS notification
                        static::sendLeaveStatusSms($record, 'head_approved');

                        Notification::make()
                            ->title('Leave application approved by Headteacher')
                            ->success()
                            ->send();
                    })
                    ->visible(fn ($record) => $record->status === 'approved_by_hod' && auth()->user()?->role_id === RoleConstants::ADMIN),

                // Final Approval (Director/Admin)
                Tables\Actions\Action::make('final_approve')
                    ->label('Final Approve')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->requiresConfirmation()
                    ->form([
                        Forms\Components\Textarea::make('approval_remarks')
                            ->label('Remarks')
                            ->rows(2),
                    ])
                    ->action(function (LeaveApplication $record, array $data) {
                        // Update leave application
                        $record->update([
                            'status' => 'approved',
                            'approved_by' => auth()->id(),
                            'approved_at' => now(),
                            'approval_remarks' => $data['approval_remarks'] ?? null,
                        ]);

                        // Deduct from leave balance
                        $balance = LeaveBalance::getOrCreate($record->employee_id, $record->leave_type_id);
                        $balance->deductDays($record->days_requested);

                        // Send SMS notification
                        static::sendLeaveStatusSms($record, 'approved');

                        Notification::make()
                            ->title('Leave application approved')
                            ->body("Leave approved for {$record->employee->name}")
                            ->success()
                            ->send();
                    })
                    ->visible(fn ($record) => $record->status === 'approved_by_head' && auth()->user()?->role_id === RoleConstants::ADMIN),

                // Quick Full Approval (Skip Steps)
                Tables\Actions\Action::make('quick_approve')
                    ->label('Quick Approve')
                    ->icon('heroicon-o-bolt')
                    ->color('success')
                    ->requiresConfirmation()
                    ->modalHeading('Quick Approval')
                    ->modalDescription('This will approve the leave application immediately, skipping all intermediate steps.')
                    ->form([
                        Forms\Components\Textarea::make('approval_remarks')
                            ->label('Remarks')
                            ->rows(2),
                    ])
                    ->action(function (LeaveApplication $record, array $data) {
                        $record->update([
                            'status' => 'approved',
                            'hod_approved_by' => auth()->id(),
                            'hod_approved_at' => now(),
                            'head_approved_by' => auth()->id(),
                            'head_approved_at' => now(),
                            'approved_by' => auth()->id(),
                            'approved_at' => now(),
                            'approval_remarks' => $data['approval_remarks'] ?? 'Quick approved by Admin',
                        ]);

                        $balance = LeaveBalance::getOrCreate($record->employee_id, $record->leave_type_id);
                        $balance->deductDays($record->days_requested);

                        // Send SMS notification
                        static::sendLeaveStatusSms($record, 'approved');

                        Notification::make()
                            ->title('Leave application quick approved')
                            ->success()
                            ->send();
                    })
                    ->visible(fn ($record) => in_array($record->status, ['pending', 'approved_by_hod', 'approved_by_head']) && auth()->user()?->role_id === RoleConstants::ADMIN),

                // Reject
                Tables\Actions\Action::make('reject')
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
                    ->action(function (LeaveApplication $record, array $data) {
                        $record->update([
                            'status' => 'rejected',
                            'rejected_by' => auth()->id(),
                            'rejected_at' => now(),
                            'rejection_reason' => $data['rejection_reason'],
                        ]);

                        // Send SMS notification
                        static::sendLeaveStatusSms($record, 'rejected', $data['rejection_reason']);

                        Notification::make()
                            ->title('Leave application rejected')
                            ->warning()
                            ->send();
                    })
                    ->visible(fn ($record) => $record->isPending() && auth()->user()?->role_id === RoleConstants::ADMIN),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListLeaveApplications::route('/'),
            'create' => Pages\CreateLeaveApplication::route('/create'),
            'view' => Pages\ViewLeaveApplication::route('/{record}'),
            'edit' => Pages\EditLeaveApplication::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery()->with(['employee', 'leaveType']);

        // Non-admin users can only see their own leave applications
        $user = auth()->user();
        if ($user && $user->role_id !== RoleConstants::ADMIN) {
            $employee = Employee::where('user_id', $user->id)->first();
            if ($employee) {
                $query->where('employee_id', $employee->id);
            }
        }

        return $query;
    }

    /**
     * Calculate working days between two dates (excluding Saturdays and Sundays)
     */
    public static function calculateWorkingDays($startDate, $endDate): int
    {
        $start = \Carbon\Carbon::parse($startDate);
        $end = \Carbon\Carbon::parse($endDate);

        $workingDays = 0;

        while ($start->lte($end)) {
            // Check if it's not Saturday (6) or Sunday (0)
            if (!$start->isWeekend()) {
                $workingDays++;
            }
            $start->addDay();
        }

        return $workingDays;
    }

    /**
     * Send SMS notification for leave status changes
     */
    public static function sendLeaveStatusSms(LeaveApplication $record, string $status, ?string $reason = null): void
    {
        try {
            // Get employee phone number
            $employee = $record->employee;
            $phone = $employee->phone ?? null;

            if (!$phone) {
                \Illuminate\Support\Facades\Log::warning('Leave SMS not sent - no phone number', [
                    'employee_id' => $employee->id,
                    'leave_id' => $record->id,
                ]);
                return;
            }

            // Build message based on status
            $employeeName = explode(' ', $employee->name)[0]; // First name
            $leaveType = $record->leaveType->name;
            $startDate = \Carbon\Carbon::parse($record->start_date)->format('d M Y');
            $endDate = \Carbon\Carbon::parse($record->end_date)->format('d M Y');
            $days = $record->days_requested;

            switch ($status) {
                case 'hod_approved':
                    $message = "Dear {$employeeName}, your {$leaveType} request ({$startDate} - {$endDate}, {$days} days) has been approved by HOD. Awaiting Headteacher approval. Ref: {$record->reference_number}. St Francis School";
                    break;

                case 'head_approved':
                    $message = "Dear {$employeeName}, your {$leaveType} request ({$startDate} - {$endDate}, {$days} days) has been approved by Headteacher. Awaiting final approval. Ref: {$record->reference_number}. St Francis School";
                    break;

                case 'approved':
                    $message = "Dear {$employeeName}, CONGRATULATIONS! Your {$leaveType} request ({$startDate} - {$endDate}, {$days} days) has been FULLY APPROVED. Please collect your leave letter. Ref: {$record->reference_number}. St Francis School";
                    break;

                case 'rejected':
                    $reasonText = $reason ? " Reason: " . substr($reason, 0, 50) : "";
                    $message = "Dear {$employeeName}, we regret to inform you that your {$leaveType} request ({$startDate} - {$endDate}) has been REJECTED.{$reasonText} Ref: {$record->reference_number}. St Francis School";
                    break;

                default:
                    return;
            }

            // Send SMS using the SmsService
            $smsService = new SmsService();
            $sent = $smsService->send(
                $message,
                $phone,
                'leave_notification',
                $record->id
            );

            if ($sent) {
                \Illuminate\Support\Facades\Log::info('Leave status SMS sent', [
                    'leave_id' => $record->id,
                    'employee' => $employee->name,
                    'status' => $status,
                ]);
            }

        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Failed to send leave status SMS', [
                'error' => $e->getMessage(),
                'leave_id' => $record->id,
            ]);
        }
    }
}
