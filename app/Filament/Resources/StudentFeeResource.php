<?php

namespace App\Filament\Resources;

use App\Filament\Resources\StudentFeeResource\Pages;
use App\Models\StudentFee;
use App\Models\FeeStructure;
use App\Models\Student;
use App\Models\AcademicYear;
use App\Models\Term;
use App\Models\Grade;
use App\Services\BalanceForwardService;
use App\Services\TermService;
use App\Http\Controllers\PaymentStatementController;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Builder;

class StudentFeeResource extends Resource
{
    protected static ?string $model = StudentFee::class;
    protected static ?string $navigationIcon = 'heroicon-o-banknotes';
    protected static ?string $navigationGroup = 'Finance Management';

    public static function form(Form $form): Form
    {
        $termService = app(TermService::class);
        $currentTerm = $termService->getCurrentTerm();
        $currentAcademicYear = $termService->getCurrentAcademicYear();

        return $form->schema([
            Forms\Components\Group::make()
                ->schema([
                    Forms\Components\Section::make('Smart Term Selection')
                        ->description('System automatically detects current term based on date')
                        ->schema([
                            Forms\Components\Placeholder::make('current_period_info')
                                ->label('Current Period')
                                ->content(function () use ($currentTerm, $currentAcademicYear) {
                                    if ($currentTerm && $currentAcademicYear) {
                                        return "📅 Current Term: {$currentTerm->name} ({$currentAcademicYear->name})";
                                    }
                                    return "⚠️ No active term found. Please check term dates.";
                                })
                                ->columnSpanFull(),

                            Forms\Components\Select::make('academic_year_id')
                                ->label('Academic Year')
                                ->options(AcademicYear::orderBy('start_date', 'desc')->pluck('name', 'id'))
                                ->default($currentAcademicYear?->id)
                                ->required()
                                ->live()
                                ->afterStateUpdated(function ($state, callable $set) {
                                    $set('term_id', null);
                                    $set('grade_id', null);
                                    $set('fee_structure_id', null);
                                }),

                            Forms\Components\Select::make('term_id')
                                ->label('Term')
                                ->options(function (callable $get) use ($termService) {
                                    $academicYearId = $get('academic_year_id');
                                    if (!$academicYearId) return [];

                                    $terms = Term::where('academic_year_id', $academicYearId)
                                        ->orderBy('start_date')
                                        ->get();

                                    $options = [];
                                    foreach ($terms as $term) {
                                        $validation = $termService->validateTermForFeeAssignment($term);
                                        $status = $validation['is_current'] ? ' (Current)' : '';
                                        $options[$term->id] = $term->name . $status;
                                    }

                                    return $options;
                                })
                                ->default($currentTerm?->id)
                                ->required()
                                ->live()
                                ->afterStateUpdated(function ($state, callable $set) {
                                    $set('grade_id', null);
                                    $set('fee_structure_id', null);
                                }),

                            Forms\Components\Select::make('grade_id')
                                ->label('Grade')
                                ->options(function (callable $get) {
                                    $academicYearId = $get('academic_year_id');
                                    $termId = $get('term_id');

                                    if (!$academicYearId || !$termId) return [];

                                    $gradeIds = FeeStructure::where('academic_year_id', $academicYearId)
                                        ->where('term_id', $termId)
                                        ->where('is_active', true)
                                        ->distinct('grade_id')
                                        ->pluck('grade_id');

                                    return Grade::whereIn('id', $gradeIds)
                                        ->orderBy('level')
                                        ->pluck('name', 'id');
                                })
                                ->required()
                                ->live()
                                ->afterStateUpdated(function ($state, callable $set, callable $get) {
                                    $academicYearId = $get('academic_year_id');
                                    $termId = $get('term_id');

                                    if ($academicYearId && $termId && $state) {
                                        $feeStructure = FeeStructure::where('academic_year_id', $academicYearId)
                                            ->where('term_id', $termId)
                                            ->where('grade_id', $state)
                                            ->where('is_active', true)
                                            ->first();

                                        if ($feeStructure) {
                                            $set('fee_structure_id', $feeStructure->id);
                                            $set('balance', $feeStructure->total_fee);
                                        }
                                    }
                                }),
                        ])
                        ->columns(2),

                    Forms\Components\Section::make('Student & Payment Details')
                        ->schema([
                            Forms\Components\Select::make('student_id')
                                ->label('Student')
                                ->options(function (callable $get) {
                                    $gradeId = $get('grade_id');
                                    if (!$gradeId) return [];

                                    return Student::where('grade_id', $gradeId)
                                        ->where('enrollment_status', 'active')
                                        ->orderBy('name')
                                        ->get()
                                        ->mapWithKeys(fn($student) => [
                                            $student->id => "{$student->name} ({$student->student_id_number})"
                                        ]);
                                })
                                ->searchable()
                                ->required()
                                ->live()
                                ->afterStateUpdated(function ($state, callable $set, callable $get) {
                                    if ($state && $get('fee_structure_id')) {
                                        // Check for existing fee record
                                        $existing = StudentFee::where('student_id', $state)
                                            ->where('fee_structure_id', $get('fee_structure_id'))
                                            ->first();

                                        if ($existing) {
                                            Notification::make()
                                                ->title('Duplicate Fee Record')
                                                ->body('This student already has a fee record for this term.')
                                                ->warning()
                                                ->send();
                                            $set('student_id', null);
                                        }
                                    }
                                }),

                            Forms\Components\Hidden::make('fee_structure_id'),

                            Forms\Components\Placeholder::make('fee_breakdown')
                                ->label('Fee Structure')
                                ->content(function (callable $get) {
                                    $feeStructureId = $get('fee_structure_id');
                                    if (!$feeStructureId) return 'Select grade first';

                                    $feeStructure = FeeStructure::find($feeStructureId);
                                    if (!$feeStructure) return 'Fee structure not found';

                                    $html = "<div class='space-y-2'>";
                                    $html .= "<div><strong>Basic Fee:</strong> ZMW " . number_format($feeStructure->basic_fee, 2) . "</div>";

                                    if ($feeStructure->additional_charges) {
                                        $html .= "<div><strong>Additional Charges:</strong></div>";
                                        foreach ($feeStructure->additional_charges as $charge) {
                                            if (isset($charge['description'], $charge['amount'])) {
                                                $html .= "<div class='ml-4'>• {$charge['description']}: ZMW " . number_format($charge['amount'], 2) . "</div>";
                                            }
                                        }
                                    }

                                    $html .= "<div class='border-t pt-2 mt-2 font-bold'>Total Fee: ZMW " . number_format($feeStructure->total_fee, 2) . "</div>";
                                    $html .= "</div>";

                                    return new \Illuminate\Support\HtmlString($html);
                                })
                                ->visible(fn (callable $get) => (bool) $get('fee_structure_id')),

                            Forms\Components\Select::make('payment_status')
                                ->options([
                                    'unpaid' => 'Unpaid',
                                    'partial' => 'Partial Payment',
                                    'paid' => 'Fully Paid',
                                    'overpaid' => 'Overpaid (Credit Balance)',
                                ])
                                ->default('unpaid')
                                ->required()
                                ->live(),

                            Forms\Components\TextInput::make('amount_paid')
                                ->label('Payment Amount')
                                ->numeric()
                                ->prefix('ZMW')
                                ->step(0.01)
                                ->default(0)
                                ->live()
                                ->afterStateUpdated(function ($state, callable $set, callable $get) {
                                    $feeStructureId = $get('fee_structure_id');
                                    if (!$feeStructureId) return;

                                    $feeStructure = FeeStructure::find($feeStructureId);
                                    if (!$feeStructure) return;

                                    $amountPaid = (float) $state;
                                    $totalFee = (float) $feeStructure->total_fee;
                                    $balance = $totalFee - $amountPaid;

                                    $set('balance', $balance);

                                    // Auto-set payment status
                                    if ($amountPaid <= 0) {
                                        $set('payment_status', 'unpaid');
                                    } elseif ($amountPaid > $totalFee) {
                                        $set('payment_status', 'overpaid');
                                    } elseif ($amountPaid >= $totalFee) {
                                        $set('payment_status', 'paid');
                                    } else {
                                        $set('payment_status', 'partial');
                                    }
                                }),

                            Forms\Components\TextInput::make('balance')
                                ->label('Outstanding Balance')
                                ->numeric()
                                ->prefix('ZMW')
                                ->disabled()
                                ->dehydrated(),

                            Forms\Components\Group::make()
                                ->schema([
                                    Forms\Components\DatePicker::make('payment_date')
                                        ->default(now())
                                        ->visible(fn (callable $get) => $get('payment_status') !== 'unpaid'),

                                    Forms\Components\TextInput::make('receipt_number')
                                        ->default(fn() => 'RCP-' . date('Y') . '-' . str_pad(mt_rand(1, 9999), 4, '0', STR_PAD_LEFT))
                                        ->visible(fn (callable $get) => $get('payment_status') !== 'unpaid'),

                                    Forms\Components\Select::make('payment_method')
                                        ->options([
                                            'cash' => 'Cash',
                                            'bank_transfer' => 'Bank Transfer',
                                            'mobile_money' => 'Mobile Money (Airtel/MTN)',
                                            'cheque' => 'Cheque',
                                            'credit_card' => 'Credit Card',
                                            'online_payment' => 'Online Payment',
                                            'other' => 'Other',
                                        ])
                                        ->visible(fn (callable $get) => $get('payment_status') !== 'unpaid'),
                                ])
                                ->columns(2),

                            Forms\Components\Toggle::make('send_sms_notification')
                                ->label('Send SMS Notification')
                                ->default(true)
                                ->visible(fn (callable $get) => $get('payment_status') !== 'unpaid'),

                            Forms\Components\Textarea::make('notes')
                                ->rows(3)
                                ->placeholder('Additional notes about this payment...'),
                        ])
                        ->columns(2),

                    // Overpayment handling section
                    Forms\Components\Section::make('Overpayment Options')
                        ->schema([
                            Forms\Components\Placeholder::make('overpayment_notice')
                                ->content(function (callable $get) {
                                    $amountPaid = (float) ($get('amount_paid') ?? 0);
                                    $feeStructureId = $get('fee_structure_id');

                                    if (!$feeStructureId) return '';

                                    $feeStructure = FeeStructure::find($feeStructureId);
                                    if (!$feeStructure) return '';

                                    $overpayment = $amountPaid - $feeStructure->total_fee;

                                    if ($overpayment <= 0) return '';

                                    return "⚠️ Overpayment detected: ZMW " . number_format($overpayment, 2) .
                                           ". This amount can be carried forward to the next term.";
                                }),

                            Forms\Components\Radio::make('overpayment_action')
                                ->label('Handle Overpayment')
                                ->options([
                                    'carry_forward' => 'Carry forward to next term',
                                    'credit_balance' => 'Keep as credit balance',
                                    'refund' => 'Process refund (manual)',
                                ])
                                ->default('carry_forward')
                                ->visible(function (callable $get) {
                                    $amountPaid = (float) ($get('amount_paid') ?? 0);
                                    $feeStructureId = $get('fee_structure_id');

                                    if (!$feeStructureId) return false;

                                    $feeStructure = FeeStructure::find($feeStructureId);
                                    return $feeStructure && $amountPaid > $feeStructure->total_fee;
                                }),
                        ])
                        ->visible(function (callable $get) {
                            return $get('payment_status') === 'overpaid';
                        }),
                ])
                ->columnSpan(2),

            // Payment History Preview (for existing records)
            Forms\Components\Group::make()
                ->schema([
                    Forms\Components\Section::make('Student Payment History')
                        ->schema([
                            Forms\Components\Placeholder::make('payment_history')
                                ->content(function (callable $get) {
                                    $studentId = $get('student_id');
                                    if (!$studentId) return 'Select a student to view payment history';

                                    $balanceService = app(BalanceForwardService::class);
                                    $student = Student::find($studentId);
                                    $history = $balanceService->getPaymentHistory($student);

                                    if (empty($history)) {
                                        return 'No previous payment records found for this student.';
                                    }

                                    $html = "<div class='space-y-3'>";
                                    foreach (array_slice($history, 0, 3) as $record) { // Show last 3 records
                                        $statusColor = match($record['payment_status']) {
                                            'paid' => 'text-green-600',
                                            'partial' => 'text-yellow-600',
                                            'unpaid' => 'text-red-600',
                                            default => 'text-gray-600'
                                        };

                                        $html .= "<div class='border-l-4 border-blue-400 pl-3'>";
                                        $html .= "<div class='font-semibold'>{$record['term']} ({$record['academic_year']})</div>";
                                        $html .= "<div class='text-sm text-gray-600'>";
                                        $html .= "Fee: ZMW " . number_format($record['total_fee'], 2) . " | ";
                                        $html .= "Paid: ZMW " . number_format($record['amount_paid'], 2) . " | ";
                                        $html .= "<span class='{$statusColor}'>Balance: ZMW " . number_format($record['balance'], 2) . "</span>";
                                        $html .= "</div></div>";
                                    }
                                    $html .= "</div>";

                                    return new \Illuminate\Support\HtmlString($html);
                                })
                                ->visible(fn (callable $get) => (bool) $get('student_id')),
                        ]),

                    Forms\Components\Section::make('Payment Summary')
                        ->schema([
                            Forms\Components\Placeholder::make('payment_summary')
                                ->content(function (callable $get) {
                                    $studentId = $get('student_id');
                                    if (!$studentId) return '';

                                    $balanceService = app(BalanceForwardService::class);
                                    $student = Student::find($studentId);
                                    $statement = $balanceService->generatePaymentStatement($student);

                                    $html = "<div class='bg-gray-50 p-4 rounded-lg space-y-2'>";
                                    $html .= "<div class='font-semibold text-lg'>Payment Summary</div>";
                                    $html .= "<div>Total Fees: ZMW " . number_format($statement['total_fees_charged'], 2) . "</div>";
                                    $html .= "<div>Total Paid: ZMW " . number_format($statement['total_payments_made'], 2) . "</div>";
                                    $html .= "<div class='font-semibold'>Outstanding: ZMW " . number_format($statement['total_outstanding'], 2) . "</div>";
                                    $html .= "</div>";

                                    return new \Illuminate\Support\HtmlString($html);
                                })
                                ->visible(fn (callable $get) => (bool) $get('student_id')),
                        ]),
                ])
                ->columnSpan(1),
        ])
        ->columns(3);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('student.name')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

                Tables\Columns\TextColumn::make('student.student_id_number')
                    ->label('Student ID')
                    ->searchable(),

                Tables\Columns\BadgeColumn::make('feeStructure.grade.name')
                    ->label('Grade')
                    ->color('primary'),

                Tables\Columns\BadgeColumn::make('feeStructure.term.name')
                    ->label('Term')
                    ->color('secondary'),

                Tables\Columns\TextColumn::make('feeStructure.total_fee')
                    ->label('Fee Amount')
                    ->money('ZMW')
                    ->sortable(),

                Tables\Columns\TextColumn::make('amount_paid')
                    ->money('ZMW')
                    ->sortable()
                    ->color(fn ($record) => match($record->payment_status) {
                        'paid' => 'success',
                        'partial' => 'warning',
                        'unpaid' => 'danger',
                        'overpaid' => 'info',
                        default => 'gray'
                    }),

                Tables\Columns\TextColumn::make('balance')
                    ->money('ZMW')
                    ->sortable()
                    ->color(fn ($record) => $record->balance <= 0 ? 'success' : 'danger'),

                Tables\Columns\BadgeColumn::make('payment_status')
                    ->colors([
                        'danger' => 'unpaid',
                        'warning' => 'partial',
                        'success' => 'paid',
                        'info' => 'overpaid',
                    ])
                    ->formatStateUsing(fn ($state) => match($state) {
                        'unpaid' => 'Unpaid',
                        'partial' => 'Partial',
                        'paid' => 'Paid',
                        'overpaid' => 'Overpaid',
                        default => $state
                    }),

                Tables\Columns\TextColumn::make('payment_date')
                    ->date('M j, Y')
                    ->sortable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('receipt_number')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('payment_status')
                    ->options([
                        'unpaid' => 'Unpaid',
                        'partial' => 'Partial',
                        'paid' => 'Paid',
                        'overpaid' => 'Overpaid',
                    ]),

                Tables\Filters\SelectFilter::make('academic_year_id')
                    ->label('Academic Year')
                    ->relationship('academicYear', 'name')
                    ->default(fn() => app(TermService::class)->getCurrentAcademicYear()?->id),

                Tables\Filters\SelectFilter::make('term_id')
                    ->label('Term')
                    ->relationship('term', 'name'),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),

                Tables\Actions\Action::make('viewPaymentHistory')
                    ->label('Payment History')
                    ->icon('heroicon-o-clock')
                    ->color('info')
                    ->modal()
                    ->modalContent(fn (StudentFee $record) => view('filament.student-payment-history', [
                        'student' => $record->student,
                        'history' => app(BalanceForwardService::class)->getPaymentHistory($record->student)
                    ]))
                    ->modalHeading(fn (StudentFee $record) => "Payment History - {$record->student->name}"),

                Tables\Actions\Action::make('processBalanceForward')
                    ->label('Process Overpayment')
                    ->icon('heroicon-o-arrow-right-circle')
                    ->color('warning')
                    ->visible(fn (StudentFee $record) => $record->payment_status === 'overpaid')
                    ->action(function (StudentFee $record) {
                        $balanceService = app(BalanceForwardService::class);
                        $overpayment = $record->amount_paid - $record->feeStructure->total_fee;

                        if ($overpayment > 0) {
                            $result = $balanceService->processOverpayment($record, $overpayment);

                            Notification::make()
                                ->title('Overpayment Processed')
                                ->body($result['message'])
                                ->success()
                                ->send();
                        }
                    }),

                Tables\Actions\Action::make('generatePaymentStatement')
                    ->label('Payment Statement')
                    ->icon('heroicon-o-document-chart-bar')
                    ->color('info')
                    ->form([
                        Forms\Components\Radio::make('statement_type')
                            ->label('Statement Type')
                            ->options([
                                'term' => 'Current Term Only',
                                'academic_year' => 'Full Academic Year',
                                'complete' => 'Complete Payment History',
                            ])
                            ->default('term')
                            ->required(),

                        Forms\Components\Radio::make('format')
                            ->label('Format')
                            ->options([
                                'pdf' => 'PDF Download',
                                'html' => 'View in Browser',
                            ])
                            ->default('pdf')
                            ->required(),
                    ])
                    ->action(function (StudentFee $record, array $data) {
                        $params = ['student' => $record->student->id, 'format' => $data['format']];

                        switch ($data['statement_type']) {
                            case 'term':
                                $params['term_id'] = $record->term_id;
                                break;
                            case 'academic_year':
                                $params['academic_year_id'] = $record->academic_year_id;
                                break;
                            // 'complete' doesn't need additional parameters
                        }

                        $url = route('payment-statement.generate', $params);
                        return redirect($url);
                    }),

                Tables\Actions\Action::make('emailPaymentStatement')
                    ->label('Email Statement')
                    ->icon('heroicon-o-envelope')
                    ->color('warning')
                    ->form([
                        Forms\Components\TextInput::make('email')
                            ->label('Email Address')
                            ->email()
                            ->required()
                            ->default(fn (StudentFee $record) => $record->student->parentGuardian?->email),

                        Forms\Components\Select::make('statement_type')
                            ->label('Statement Type')
                            ->options([
                                'term' => 'Current Term Only',
                                'academic_year' => 'Full Academic Year',
                                'complete' => 'Complete Payment History',
                            ])
                            ->default('academic_year')
                            ->required(),

                        Forms\Components\Textarea::make('message')
                            ->label('Personal Message (Optional)')
                            ->rows(3)
                            ->placeholder('Add a personal message to include with the statement...'),
                    ])
                    ->action(function (StudentFee $record, array $data) {
                        $params = [
                            'email' => $data['email'],
                            'message' => $data['message'] ?? null,
                        ];

                        switch ($data['statement_type']) {
                            case 'term':
                                $params['term_id'] = $record->term_id;
                                break;
                            case 'academic_year':
                                $params['academic_year_id'] = $record->academic_year_id;
                                break;
                        }

                        try {
                            app(PaymentStatementController::class)->emailStatement($record->student, request()->merge($params));

                            Notification::make()
                                ->title('Statement Sent')
                                ->body("Payment statement has been sent to {$data['email']}")
                                ->success()
                                ->send();
                        } catch (\Exception $e) {
                            Notification::make()
                                ->title('Email Failed')
                                ->body('Failed to send payment statement: ' . $e->getMessage())
                                ->danger()
                                ->send();
                        }
                    })
                    ->visible(fn (StudentFee $record) => $record->student->parentGuardian?->email !== null),
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
            'index' => Pages\ListStudentFees::route('/'),
            'create' => Pages\CreateStudentFee::route('/create'),
            'view' => Pages\ViewStudentFee::route('/{record}'),
            'edit' => Pages\EditStudentFee::route('/{record}/edit'),
        ];
    }
}
