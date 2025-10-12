<?php

namespace App\Filament\Resources\PayrollResource\Pages;

use App\Filament\Resources\PayrollResource;
use App\Models\Employee;
use App\Models\Payroll;
use App\Services\PayrollCalculationService;
use Filament\Actions;
use Filament\Forms;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;

class ListPayrolls extends ListRecords
{
    protected static string $resource = PayrollResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->icon('heroicon-o-plus'),

            Actions\Action::make('generate_bulk')
                ->label('Generate Bulk Payroll')
                ->icon('heroicon-o-clipboard-document-list')
                ->color('success')
                ->form([
                    Forms\Components\Section::make()
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
                                ->required()
                                ->default(now()->format('F'))
                                ->native(false),

                            Forms\Components\TextInput::make('year')
                                ->label('Year')
                                ->numeric()
                                ->required()
                                ->default(now()->year)
                                ->minValue(2000)
                                ->maxValue(now()->year + 1),

                            Forms\Components\Select::make('departments')
                                ->label('Departments')
                                ->multiple()
                                ->options([
                                    'ecl' => 'ECL',
                                    'primary' => 'Primary School',
                                    'secondary' => 'Secondary School',
                                    'administration' => 'Administration',
                                    'support' => 'Support Staff',
                                ])
                                ->helperText('Leave empty to generate for all departments')
                                ->native(false),

                            Forms\Components\Toggle::make('skip_existing')
                                ->label('Skip Existing Payrolls')
                                ->helperText('Skip employees who already have payroll for this period')
                                ->default(true),

                            Forms\Components\Placeholder::make('warning')
                                ->content('This will generate payroll records for all active employees in the selected departments based on their basic salary.')
                                ->columnSpanFull(),
                        ])
                        ->columns(2),
                ])
                ->modalHeading('Generate Bulk Payroll')
                ->modalDescription('Generate payroll records for multiple employees at once')
                ->modalSubmitActionLabel('Generate Payrolls')
                ->modalWidth('2xl')
                ->action(function (array $data) {
                    $month = $data['month'];
                    $year = $data['year'];
                    $departments = $data['departments'] ?? [];
                    $skipExisting = $data['skip_existing'] ?? true;

                    // Build employee query
                    $query = Employee::where('status', 'active')
                        ->whereNotNull('basic_salary');

                    if (! empty($departments)) {
                        $query->whereIn('department', $departments);
                    }

                    $employees = $query->get();

                    if ($employees->isEmpty()) {
                        Notification::make()
                            ->title('No Employees Found')
                            ->body('No active employees found with the specified criteria.')
                            ->warning()
                            ->send();

                        return;
                    }

                    $created = 0;
                    $skipped = 0;
                    $errors = 0;

                    foreach ($employees as $employee) {
                        // Check if payroll already exists
                        if ($skipExisting) {
                            $exists = Payroll::where('employee_id', $employee->id)
                                ->where('month', $month)
                                ->where('year', $year)
                                ->exists();

                            if ($exists) {
                                $skipped++;

                                continue;
                            }
                        }

                        try {
                            // Calculate statutory deductions
                            $payrollService = new PayrollCalculationService;
                            $calculation = $payrollService->calculatePayroll($employee->basic_salary);

                            // Create payroll with statutory deductions
                            Payroll::create([
                                'employee_id' => $employee->id,
                                'month' => $month,
                                'year' => $year,
                                'department' => $employee->department,
                                'basic_salary' => $employee->basic_salary,
                                'allowances' => [],
                                'deductions' => $calculation['deductions'],
                                'gross_salary' => $calculation['gross_salary'],
                                'net_salary' => $calculation['net_salary'],
                                'payment_status' => 'pending',
                                'notes' => 'Generated via bulk payroll creation with statutory deductions',
                            ]);

                            $created++;
                        } catch (\Exception $e) {
                            $errors++;
                            \Log::error('Failed to create payroll for employee', [
                                'employee_id' => $employee->id,
                                'error' => $e->getMessage(),
                            ]);
                        }
                    }

                    // Show result notification
                    $message = "Created: {$created}";
                    if ($skipped > 0) {
                        $message .= ", Skipped: {$skipped}";
                    }
                    if ($errors > 0) {
                        $message .= ", Errors: {$errors}";
                    }

                    Notification::make()
                        ->title('Bulk Payroll Generation Complete')
                        ->body($message)
                        ->success($created > 0)
                        ->warning($errors > 0 && $created === 0)
                        ->duration(5000)
                        ->send();
                }),
        ];
    }
}
