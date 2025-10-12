<?php

namespace App\Filament\Resources;

use App\Constants\RoleConstants;
use App\Filament\Resources\PaymentTransactionResource\Pages;
use App\Models\AcademicYear;
use App\Models\Grade;
use App\Models\ParentGuardian;
use App\Models\PaymentTransaction;
use App\Models\Student;
use App\Models\Term;
use Carbon\Carbon;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

class PaymentTransactionResource extends Resource
{
    protected static ?string $model = PaymentTransaction::class;

    protected static ?string $navigationIcon = 'heroicon-o-banknotes';

    protected static ?string $navigationGroup = 'Financial Management';

    protected static ?int $navigationSort = 1;

    public static function getNavigationLabel(): string
    {
        $user = Auth::user();
        $isStudent = $user && $user->role_id === RoleConstants::STUDENT;

        return $isStudent ? 'My Payments' : 'Payments Received';
    }

    public static function getModelLabel(): string
    {
        return 'Payment Transaction';
    }

    public static function getPluralModelLabel(): string
    {
        $user = Auth::user();
        $isStudent = $user && $user->role_id === RoleConstants::STUDENT;

        return $isStudent ? 'My Payments' : 'Payment Transactions';
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Transaction Details')
                    ->schema([
                        Forms\Components\TextInput::make('reference_number')
                            ->label('Reference Number')
                            ->disabled(),
                        Forms\Components\TextInput::make('amount')
                            ->label('Amount (ZMW)')
                            ->numeric()
                            ->disabled(),
                        Forms\Components\TextInput::make('type')
                            ->disabled(),
                        Forms\Components\TextInput::make('payment_method')
                            ->disabled(),
                        Forms\Components\DateTimePicker::make('transaction_date')
                            ->disabled(),
                        Forms\Components\Textarea::make('notes')
                            ->disabled()
                            ->columnSpanFull(),
                    ])
                    ->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        $user = Auth::user();
        $isStudent = $user && $user->role_id === RoleConstants::STUDENT;
        $isParent = $user && $user->role_id === RoleConstants::PARENT;

        return $table
            ->defaultSort('transaction_date', 'desc')
            ->striped()
            ->columns([
                Tables\Columns\TextColumn::make('transaction_date')
                    ->label('Payment Date')
                    ->date('d M Y')
                    ->sortable()
                    ->searchable()
                    ->icon('heroicon-m-calendar')
                    ->iconColor('primary')
                    ->weight('medium')
                    ->description(fn (PaymentTransaction $record): string => $record->transaction_date->format('h:i A')
                    ),

                Tables\Columns\TextColumn::make('reference_number')
                    ->label('Receipt Number')
                    ->searchable()
                    ->copyable()
                    ->copyMessage('Receipt number copied!')
                    ->sortable()
                    ->icon('heroicon-m-document-text')
                    ->iconColor('success')
                    ->weight('semibold')
                    ->color('primary'),

                Tables\Columns\TextColumn::make('studentFee.student.name')
                    ->label('Student Name')
                    ->searchable()
                    ->sortable()
                    ->wrap()
                    ->weight('medium')
                    ->icon('heroicon-m-user')
                    ->description(fn (PaymentTransaction $record) => $record->studentFee?->student?->student_id_number ?? ''
                    )
                    ->visible(! $isStudent),

                Tables\Columns\TextColumn::make('studentFee.student.grade.name')
                    ->label('Grade')
                    ->sortable()
                    ->badge()
                    ->color('info')
                    ->icon('heroicon-m-academic-cap'),

                Tables\Columns\TextColumn::make('studentFee.feeStructure.term.name')
                    ->label('Term')
                    ->sortable()
                    ->badge()
                    ->color('primary'),

                Tables\Columns\TextColumn::make('amount')
                    ->label('Amount Paid')
                    ->money('ZMW')
                    ->sortable()
                    ->weight('bold')
                    ->color('success')
                    ->size('lg')
                    ->summarize([
                        Tables\Columns\Summarizers\Sum::make()
                            ->money('ZMW')
                            ->label('Total Payments')
                            ->formatStateUsing(fn ($state) => 'Total: '.$state),
                    ]),

                Tables\Columns\TextColumn::make('payment_method')
                    ->label('Payment Method')
                    ->formatStateUsing(fn ($state) => ucfirst(str_replace('_', ' ', $state ?? 'N/A')))
                    ->badge()
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

                Tables\Columns\TextColumn::make('processedBy.name')
                    ->label('Processed By')
                    ->icon('heroicon-m-user-circle')
                    ->visible(! $isStudent && ! $isParent)
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Recorded At')
                    ->dateTime('d M Y, h:i A')
                    ->sortable()
                    ->icon('heroicon-m-clock')
                    ->color('gray')
                    ->visible(! $isStudent && ! $isParent)
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Filter::make('transaction_date')
                    ->form([
                        Forms\Components\DatePicker::make('from')
                            ->label('From Date')
                            ->native(false)
                            ->displayFormat('d M Y')
                            ->closeOnDateSelection(),
                        Forms\Components\DatePicker::make('until')
                            ->label('Until Date')
                            ->native(false)
                            ->displayFormat('d M Y')
                            ->closeOnDateSelection(),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['from'],
                                fn (Builder $query, $date): Builder => $query->whereDate('transaction_date', '>=', $date),
                            )
                            ->when(
                                $data['until'],
                                fn (Builder $query, $date): Builder => $query->whereDate('transaction_date', '<=', $date),
                            );
                    })
                    ->indicateUsing(function (array $data): array {
                        $indicators = [];
                        if ($data['from'] ?? null) {
                            $indicators[] = 'From: '.Carbon::parse($data['from'])->format('d M Y');
                        }
                        if ($data['until'] ?? null) {
                            $indicators[] = 'Until: '.Carbon::parse($data['until'])->format('d M Y');
                        }

                        return $indicators;
                    })
                    ->columnSpan(2),

                SelectFilter::make('academic_year')
                    ->label('Academic Year')
                    ->relationship('studentFee.feeStructure.academicYear', 'name')
                    ->preload()
                    ->native(false)
                    ->indicator('Year')
                    ->default(function () {
                        return Cache::remember('current_academic_year_id', 3600, function () {
                            return AcademicYear::where('is_current', true)->value('id');
                        });
                    }),

                SelectFilter::make('term')
                    ->label('Term')
                    ->relationship('studentFee.feeStructure.term', 'name')
                    ->preload()
                    ->native(false)
                    ->indicator('Term')
                    ->default(function () {
                        return Cache::remember('current_term_id', 3600, function () {
                            return Term::where('is_current', true)->value('id');
                        });
                    }),

                SelectFilter::make('grade')
                    ->label('Grade')
                    ->options(Grade::pluck('name', 'id'))
                    ->native(false)
                    ->indicator('Grade')
                    ->query(function (Builder $query, $state) {
                        if ($state['value']) {
                            $query->whereHas('studentFee.student', function (Builder $query) use ($state) {
                                $query->where('grade_id', $state['value']);
                            });
                        }
                    })
                    ->visible(! $isStudent && ! $isParent),

                SelectFilter::make('payment_method')
                    ->label('Payment Method')
                    ->options([
                        'cash' => 'Cash',
                        'mobile_money' => 'Mobile Money',
                        'bank_transfer' => 'Bank Transfer',
                        'cheque' => 'Cheque',
                        'other' => 'Other',
                    ])
                    ->native(false)
                    ->indicator('Method'),
            ], layout: Tables\Enums\FiltersLayout::AboveContentCollapsible)
            ->actions([
                Tables\Actions\ViewAction::make()
                    ->label('View Details')
                    ->icon('heroicon-m-eye')
                    ->color('info')
                    ->modalHeading('Payment Transaction Details')
                    ->modalWidth('3xl'),
                Tables\Actions\Action::make('printReceipt')
                    ->label('Receipt')
                    ->icon('heroicon-m-printer')
                    ->color('success')
                    ->tooltip('Download/Print Receipt')
                    ->url(fn (PaymentTransaction $record) => route('student-fees.transaction-receipt', [
                        'fee' => $record->student_fee_id,
                        'transaction' => $record->id,
                    ]))
                    ->openUrlInNewTab(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\BulkAction::make('exportSelected')
                        ->label('Export to PDF')
                        ->icon('heroicon-m-arrow-down-tray')
                        ->color('primary')
                        ->requiresConfirmation()
                        ->modalHeading('Export Payment Records')
                        ->modalDescription('Export selected payment transactions to PDF.')
                        ->action(function ($records) {
                            // Export functionality can be added here
                        })
                        ->visible(! $isStudent && ! $isParent),
                ]),
            ])
            ->emptyStateHeading('No Payment Transactions Found')
            ->emptyStateDescription($isStudent
                ? 'Your payment transactions will appear here once payments are recorded for your account.'
                : 'Payment transactions will appear here once fees are recorded in the system.'
            )
            ->emptyStateIcon('heroicon-o-banknotes')
            ->emptyStateActions([
                Tables\Actions\Action::make('viewFees')
                    ->label('View My Fees')
                    ->icon('heroicon-m-currency-dollar')
                    ->url(fn () => route('filament.admin.resources.student-fees.index'))
                    ->visible($isStudent),
            ])
            ->paginated([10, 25, 50, 100])
            ->defaultPaginationPageOption(25)
            ->persistSortInSession()
            ->persistSearchInSession()
            ->persistFiltersInSession()
            ->deferLoading();
    }

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery()
            ->with([
                'studentFee.student.grade',
                'studentFee.feeStructure.term',
                'studentFee.feeStructure.academicYear',
                'processedBy',
            ]);

        $user = Auth::user();

        // Role-based filtering
        if ($user && $user->role_id === RoleConstants::STUDENT) {
            // Students see only their own payment transactions
            $student = Student::where('user_id', $user->id)->first();
            if ($student) {
                $query->whereHas('studentFee', function (Builder $query) use ($student) {
                    $query->where('student_id', $student->id);
                });
            } else {
                // If no student record found, return empty result
                return $query->whereRaw('1 = 0');
            }
        } elseif ($user && $user->role_id === RoleConstants::PARENT) {
            // Parents see only their children's payment transactions
            $parent = ParentGuardian::where('user_id', $user->id)->first();
            if ($parent) {
                $childrenIds = $parent->students()->pluck('id');
                if ($childrenIds->isNotEmpty()) {
                    $query->whereHas('studentFee', function (Builder $query) use ($childrenIds) {
                        $query->whereIn('student_id', $childrenIds);
                    });
                } else {
                    return $query->whereRaw('1 = 0');
                }
            } else {
                return $query->whereRaw('1 = 0');
            }
        }

        // Default to current academic year if no filters applied (for all users)
        $currentYearId = Cache::remember('current_academic_year_id', 3600, function () {
            return AcademicYear::where('is_current', true)->value('id');
        });

        if ($currentYearId) {
            $query->whereHas('studentFee.feeStructure.academicYear', function (Builder $query) use ($currentYearId) {
                $query->where('id', $currentYearId);
            });
        }

        return $query;
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
            'index' => Pages\ListPaymentTransactions::route('/'),
            'view' => Pages\ViewPaymentTransaction::route('/{record}'),
        ];
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function canEdit($record): bool
    {
        return false;
    }

    public static function canDelete($record): bool
    {
        return false;
    }

    public static function shouldRegisterNavigation(): bool
    {
        $user = Auth::user();
        if (! $user) {
            return false;
        }

        // Show to Admin, Accountant, Student, and Parent
        return in_array($user->role_id, [
            RoleConstants::ADMIN,
            RoleConstants::ACCOUNTANT,
            RoleConstants::STUDENT,
            RoleConstants::PARENT,
        ]);
    }
}
