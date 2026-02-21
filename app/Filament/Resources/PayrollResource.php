<?php

namespace App\Filament\Resources;

use App\Constants\RoleConstants;
use App\Filament\Resources\PayrollResource\Pages;
use App\Models\Employee;
use App\Models\Payroll;
use App\Services\PayrollCalculationService;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class PayrollResource extends Resource
{
    protected static ?string $model = Payroll::class;

    protected static ?string $navigationIcon = 'heroicon-o-calculator';

    protected static ?string $navigationGroup = 'Staff Management';

    protected static ?string $navigationLabel = 'Payroll';

    protected static ?int $navigationSort = 3;

    public static function shouldRegisterNavigation(): bool
    {
        return auth()->user()?->role_id === RoleConstants::ADMIN ?? false;
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Payroll Period')
                    ->description('Select the employee and pay period')
                    ->schema([
                        Forms\Components\Select::make('employee_id')
                            ->label('Employee')
                            ->relationship('employee', 'name')
                            ->searchable()
                            ->preload()
                            ->required()
                            ->live()
                            ->afterStateUpdated(function (Set $set, Get $get, $state) {
                                if ($state) {
                                    $employee = Employee::find($state);
                                    if ($employee && $employee->basic_salary) {
                                        $set('basic_salary', $employee->basic_salary);
                                        $set('department', $employee->department);

                                        // Auto-calculate statutory deductions
                                        $payrollService = new PayrollCalculationService;
                                        $allowances = $get('allowances') ?? [];
                                        $statutoryDeductions = $payrollService->calculateStatutoryDeductions($employee->basic_salary, $allowances);

                                        // Set statutory deductions
                                        $deductions = [];
                                        foreach ($statutoryDeductions as $deduction) {
                                            $deductions[] = [
                                                'type' => $deduction['type'],
                                                'amount' => $deduction['amount'],
                                            ];
                                        }
                                        $set('deductions', $deductions);

                                        static::calculateSalaries($set, $get);
                                    }
                                }
                            }),

                        Forms\Components\Select::make('month')
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
                            ->default(now()->format('F')),

                        Forms\Components\TextInput::make('year')
                            ->numeric()
                            ->required()
                            ->default(now()->year)
                            ->minValue(2000)
                            ->maxValue(now()->year + 1),

                        Forms\Components\Hidden::make('department'),
                    ])
                    ->columns(3),

                Forms\Components\Section::make('Salary Details')
                    ->description('Basic salary and calculations')
                    ->schema([
                        Forms\Components\TextInput::make('basic_salary')
                            ->label('Basic Salary')
                            ->numeric()
                            ->required()
                            ->prefix('ZMW')
                            ->live(onBlur: true)
                            ->afterStateUpdated(fn (Set $set, Get $get) => static::recalculateStatutory($set, $get))
                            ->helperText('Base salary for the month'),

                        Forms\Components\Repeater::make('allowances')
                            ->label('Allowances')
                            ->schema([
                                Forms\Components\Select::make('type')
                                    ->options([
                                        'Housing Allowance' => 'Housing Allowance',
                                        'Transport Allowance' => 'Transport Allowance',
                                        'Meal Allowance' => 'Meal Allowance',
                                        'Medical Allowance' => 'Medical Allowance',
                                        'Overtime' => 'Overtime',
                                        'Bonus' => 'Bonus',
                                        'Other' => 'Other',
                                    ])
                                    ->required()
                                    ->searchable(),
                                Forms\Components\TextInput::make('amount')
                                    ->numeric()
                                    ->required()
                                    ->prefix('ZMW')
                                    ->live(onBlur: true)
                                    ->afterStateUpdated(fn (Set $set, Get $get) => static::recalculateStatutory($set, $get)),
                            ])
                            ->columns(2)
                            ->defaultItems(0)
                            ->live()
                            ->afterStateUpdated(fn (Set $set, Get $get) => static::recalculateStatutory($set, $get))
                            ->addActionLabel('Add Allowance')
                            ->columnSpanFull()
                            ->collapsible(),

                        Forms\Components\Repeater::make('deductions')
                            ->label('Deductions')
                            ->schema([
                                Forms\Components\Select::make('type')
                                    ->options([
                                        'NAPSA' => 'NAPSA (5%)',
                                        'PAYE' => 'PAYE (Tax)',
                                        'NHIMA' => 'NHIMA (1%)',
                                        'Loan Repayment' => 'Loan Repayment',
                                        'Advance Repayment' => 'Advance Repayment',
                                        'Absence Deduction' => 'Absence Deduction',
                                        'Other' => 'Other',
                                    ])
                                    ->required()
                                    ->searchable(),
                                Forms\Components\TextInput::make('amount')
                                    ->numeric()
                                    ->required()
                                    ->prefix('ZMW')
                                    ->live(onBlur: true)
                                    ->afterStateUpdated(fn (Set $set, Get $get) => static::calculateSalaries($set, $get)),
                            ])
                            ->columns(2)
                            ->defaultItems(0)
                            ->live()
                            ->afterStateUpdated(fn (Set $set, Get $get) => static::calculateSalaries($set, $get))
                            ->addActionLabel('Add Deduction')
                            ->columnSpanFull()
                            ->collapsible(),

                        Forms\Components\TextInput::make('gross_salary')
                            ->label('Gross Salary')
                            ->numeric()
                            ->prefix('ZMW')
                            ->disabled()
                            ->dehydrated()
                            ->helperText('Basic salary + Total allowances'),

                        Forms\Components\TextInput::make('net_salary')
                            ->label('Net Salary')
                            ->numeric()
                            ->prefix('ZMW')
                            ->disabled()
                            ->dehydrated()
                            ->helperText('Gross salary - Total deductions'),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Payment Information')
                    ->description('Payment status and details')
                    ->schema([
                        Forms\Components\Select::make('payment_status')
                            ->options([
                                'pending' => 'Pending',
                                'paid' => 'Paid',
                                'cancelled' => 'Cancelled',
                            ])
                            ->required()
                            ->default('pending')
                            ->native(false),

                        Forms\Components\DatePicker::make('payment_date')
                            ->label('Payment Date')
                            ->native(false)
                            ->displayFormat('d M Y'),

                        Forms\Components\Textarea::make('notes')
                            ->label('Notes')
                            ->rows(3)
                            ->columnSpanFull()
                            ->placeholder('Any additional notes or comments'),
                    ])
                    ->columns(2),
            ]);
    }

    /**
     * Recalculate statutory deductions when basic salary or allowances change
     */
    protected static function recalculateStatutory(Set $set, Get $get): void
    {
        $basicSalary = (float) ($get('basic_salary') ?? 0);
        $allowances = $get('allowances') ?? [];

        if ($basicSalary > 0) {
            $payrollService = new PayrollCalculationService;
            $statutoryDeductions = $payrollService->calculateStatutoryDeductions($basicSalary, $allowances);

            // Get existing non-statutory deductions
            $currentDeductions = $get('deductions') ?? [];
            $nonStatutoryTypes = ['NAPSA', 'PAYE', 'NHIMA'];
            $nonStatutory = collect($currentDeductions)->filter(function ($deduction) use ($nonStatutoryTypes) {
                return ! in_array($deduction['type'] ?? '', $nonStatutoryTypes);
            })->values()->toArray();

            // Merge statutory + non-statutory deductions
            $allDeductions = array_merge(
                array_map(fn ($d) => ['type' => $d['type'], 'amount' => $d['amount']], $statutoryDeductions),
                $nonStatutory
            );

            $set('deductions', $allDeductions);
        }

        static::calculateSalaries($set, $get);
    }

    /**
     * Calculate gross and net salaries based on allowances and deductions
     */
    protected static function calculateSalaries(Set $set, Get $get): void
    {
        $basicSalary = (float) ($get('basic_salary') ?? 0);
        $allowances = collect($get('allowances') ?? [])->sum('amount');
        $gross = $basicSalary + $allowances;

        $deductions = collect($get('deductions') ?? [])->sum('amount');
        $net = $gross - $deductions;

        $set('gross_salary', $gross);
        $set('net_salary', $net);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                Tables\Columns\TextColumn::make('employee.name')
                    ->label('Employee')
                    ->searchable()
                    ->sortable()
                    ->icon('heroicon-m-user')
                    ->description(fn (Payroll $record) => $record->employee?->employee_id),

                Tables\Columns\TextColumn::make('month')
                    ->searchable()
                    ->sortable()
                    ->badge()
                    ->color('info'),

                Tables\Columns\TextColumn::make('year')
                    ->sortable()
                    ->alignCenter(),

                Tables\Columns\TextColumn::make('employee.department')
                    ->label('Department')
                    ->sortable()
                    ->formatStateUsing(fn ($state) => ucfirst(str_replace('_', ' ', $state ?? '')))
                    ->toggleable(),

                Tables\Columns\TextColumn::make('basic_salary')
                    ->label('Basic')
                    ->money('ZMW')
                    ->sortable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('gross_salary')
                    ->label('Gross')
                    ->money('ZMW')
                    ->sortable()
                    ->weight('bold')
                    ->color('success'),

                Tables\Columns\TextColumn::make('net_salary')
                    ->label('Net Salary')
                    ->money('ZMW')
                    ->sortable()
                    ->weight('bold')
                    ->color('primary')
                    ->size('lg'),

                Tables\Columns\BadgeColumn::make('payment_status')
                    ->label('Status')
                    ->colors([
                        'warning' => 'pending',
                        'success' => 'paid',
                        'danger' => 'cancelled',
                    ])
                    ->icons([
                        'heroicon-o-clock' => 'pending',
                        'heroicon-o-check-circle' => 'paid',
                        'heroicon-o-x-circle' => 'cancelled',
                    ]),

                Tables\Columns\TextColumn::make('payment_date')
                    ->label('Paid On')
                    ->date('d M Y')
                    ->sortable()
                    ->placeholder('Not paid')
                    ->toggleable(),
            ])
            ->filters([
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

                Tables\Filters\SelectFilter::make('year')
                    ->options(function () {
                        $years = range(now()->year - 5, now()->year + 1);

                        return array_combine($years, $years);
                    })
                    ->default(now()->year)
                    ->native(false),

                Tables\Filters\SelectFilter::make('department')
                    ->options([
                        'ecl' => 'ECL',
                        'primary' => 'Primary School',
                        'secondary' => 'Secondary School',
                        'administration' => 'Administration',
                        'support' => 'Support Staff',
                    ])
                    ->native(false),

                Tables\Filters\SelectFilter::make('payment_status')
                    ->options([
                        'pending' => 'Pending',
                        'paid' => 'Paid',
                        'cancelled' => 'Cancelled',
                    ])
                    ->native(false),
            ], layout: Tables\Enums\FiltersLayout::AboveContentCollapsible)
            ->actions([
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\Action::make('print_payslip')
                        ->label('Print Payslip')
                        ->icon('heroicon-o-printer')
                        ->color('info')
                        ->url(fn (Payroll $record) => route('payslips.stream', $record))
                        ->openUrlInNewTab(),

                    Tables\Actions\Action::make('download_payslip')
                        ->label('Download PDF')
                        ->icon('heroicon-o-arrow-down-tray')
                        ->color('success')
                        ->url(fn (Payroll $record) => route('payslips.download', $record)),
                ])
                    ->label('Payslip')
                    ->icon('heroicon-o-document-text')
                    ->color('primary')
                    ->button(),

                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),

                Tables\Actions\Action::make('mark_paid')
                    ->label('Mark as Paid')
                    ->icon('heroicon-o-check-badge')
                    ->color('success')
                    ->requiresConfirmation()
                    ->modalHeading('Mark Payroll as Paid')
                    ->modalDescription(fn (Payroll $record) => "Mark {$record->employee?->name}'s payroll for {$record->month} {$record->year} as paid?")
                    ->action(function (Payroll $record) {
                        $record->update([
                            'payment_status' => 'paid',
                            'payment_date' => now(),
                        ]);

                        Notification::make()
                            ->title('Payroll Marked as Paid')
                            ->body("Successfully marked {$record->employee?->name}'s payroll as paid")
                            ->success()
                            ->send();
                    })
                    ->visible(fn (Payroll $record) => $record->payment_status === 'pending'),

                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),

                    Tables\Actions\BulkAction::make('mark_paid')
                        ->label('Mark as Paid')
                        ->icon('heroicon-o-check-badge')
                        ->color('success')
                        ->requiresConfirmation()
                        ->modalHeading('Mark Selected Payrolls as Paid')
                        ->modalDescription('Are you sure you want to mark all selected payrolls as paid?')
                        ->action(function ($records) {
                            $count = $records->count();
                            foreach ($records as $record) {
                                $record->update([
                                    'payment_status' => 'paid',
                                    'payment_date' => now(),
                                ]);
                            }

                            Notification::make()
                                ->title('Payrolls Updated')
                                ->body("Successfully marked {$count} payroll(s) as paid")
                                ->success()
                                ->send();
                        }),
                ]),
            ])
            ->emptyStateHeading('No Payrolls Found')
            ->emptyStateDescription('Create a payroll record for employees or generate bulk payrolls for a month.')
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
            'index' => Pages\ListPayrolls::route('/'),
            'create' => Pages\CreatePayroll::route('/create'),
            'view' => Pages\ViewPayroll::route('/{record}'),
            'edit' => Pages\EditPayroll::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->with(['employee:id,name,employee_id,department']);
    }
}
