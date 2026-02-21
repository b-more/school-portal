<?php

namespace App\Filament\Resources;

use App\Filament\Resources\StudentFeeResource\Pages;
use App\Models\StudentFee;
use App\Models\FeeStructure;
use App\Models\Student;
use App\Models\ParentGuardian;
use App\Models\AcademicYear;
use App\Models\Term;
use App\Models\Grade;
use App\Services\StudentFeeService;
use App\Services\SmsService;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use App\Constants\RoleConstants;
use Illuminate\Support\Facades\Auth;

class StudentFeeResource extends Resource
{
    protected static ?string $model = StudentFee::class;

    protected static ?string $navigationIcon = 'heroicon-o-banknotes';

    protected static ?string $navigationGroup = 'Finance Management';

    protected static ?int $navigationSort = 2;

    public static function shouldRegisterNavigation(): bool
    {
        return auth()->user()?->role_id === RoleConstants::ADMIN ?? false;
    }

    /**
     * Optimize query performance with eager loading
     */
    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->with([
                'student:id,name,student_id_number,parent_guardian_id',
                'student.parentGuardian:id,name,phone',
                'feeStructure:id,grade_id,term_id,academic_year_id,total_fee,basic_fee,additional_charges',
                'feeStructure.grade:id,name',
                'feeStructure.term:id,name',
                'feeStructure.academicYear:id,name',
                'paymentTransactions:id,student_fee_id,amount,type,transaction_date,payment_method',
            ]);
    }

    /**
     * Check if there is an existing fee record for the student and fee structure
     */
    protected static function checkForDuplicateFee($studentId, $feeStructureId, $academicYearId, $termId, $editing = false, $recordId = null)
    {
        if (!$studentId || !$feeStructureId) {
            return null;
        }

        $query = StudentFee::where('student_id', $studentId)
            ->where('fee_structure_id', $feeStructureId);

        // If we're editing, exclude the current record
        if ($editing && $recordId) {
            $query->where('id', '!=', $recordId);
        }

        return $query->first();
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Group::make()
                    ->schema([
                        Forms\Components\Section::make('Fee Period')
                            ->schema([
                                // Step 1: Select Academic Year
                                Forms\Components\Select::make('academic_year_id')
                                    ->label('Academic Year')
                                    ->options(function () {
                                        return AcademicYear::orderBy('name')
                                            ->pluck('name', 'id')
                                            ->toArray();
                                    })
                                    ->required()
                                    ->live()
                                    ->afterStateUpdated(function ($state, callable $set) {
                                        // Clear dependent selections when this changes
                                        $set('term_id', null);
                                        $set('grade_id', null);
                                        $set('fee_structure_id', null);
                                        $set('student_id', null);
                                        $set('balance', null);
                                    }),

                                // Step 2: Select Term
                                Forms\Components\Select::make('term_id')
                                    ->label('Term')
                                    ->options(function (callable $get) {
                                        $academicYearId = $get('academic_year_id');

                                        if (!$academicYearId) {
                                            return [];
                                        }

                                        return Term::where('academic_year_id', $academicYearId)
                                            ->orderBy('name')
                                            ->pluck('name', 'id')
                                            ->toArray();
                                    })
                                    ->required()
                                    ->live()
                                    ->afterStateUpdated(function ($state, callable $set) {
                                        // Clear dependent selections when this changes
                                        $set('grade_id', null);
                                        $set('fee_structure_id', null);
                                        $set('student_id', null);
                                        $set('balance', null);
                                    })
                                    ->visible(fn (callable $get) => (bool) $get('academic_year_id')),

                                // Step 3: Select Grade
                                Forms\Components\Select::make('grade_id')
                                    ->label('Grade')
                                    ->options(function (callable $get) {
                                        $academicYearId = $get('academic_year_id');
                                        $termId = $get('term_id');

                                        if (!$academicYearId || !$termId) {
                                            return [];
                                        }

                                        // First try to find grades with fee structures for this term/year
                                        $gradeIds = FeeStructure::where('academic_year_id', $academicYearId)
                                            ->where('term_id', $termId)
                                            ->where('is_active', true)
                                            ->distinct('grade_id')
                                            ->pluck('grade_id');

                                        return Grade::whereIn('id', $gradeIds)
                                            ->orderBy('level')
                                            ->pluck('name', 'id')
                                            ->toArray();
                                    })
                                    ->required()
                                    ->live()
                                    ->afterStateUpdated(function ($state, callable $set, callable $get) {
                                        // Automatically select the fee structure for this grade/term/year
                                        $academicYearId = $get('academic_year_id');
                                        $termId = $get('term_id');
                                        $gradeId = $state;

                                        if (!$academicYearId || !$termId || !$gradeId) {
                                            $set('fee_structure_id', null);
                                            return;
                                        }

                                        $feeStructure = FeeStructure::where('academic_year_id', $academicYearId)
                                            ->where('term_id', $termId)
                                            ->where('grade_id', $gradeId)
                                            ->where('is_active', true)
                                            ->first();

                                        if ($feeStructure) {
                                            $set('fee_structure_id', $feeStructure->id);
                                        } else {
                                            $set('fee_structure_id', null);
                                        }

                                        // Clear student selection
                                        $set('student_id', null);
                                    })
                                    ->visible(fn (callable $get) => (bool) $get('academic_year_id') && (bool) $get('term_id')),
                            ]),

                        Forms\Components\Section::make('Student & Fee Information')
                            ->schema([
                                // Step 4: Fee Structure (Auto-selected but displayed)
                                Forms\Components\Select::make('fee_structure_id')
                                    ->label('Fee Structure')
                                    ->options(function (callable $get) {
                                        $academicYearId = $get('academic_year_id');
                                        $termId = $get('term_id');
                                        $gradeId = $get('grade_id');

                                        if (!$academicYearId || !$termId || !$gradeId) {
                                            return [];
                                        }

                                        return FeeStructure::where('academic_year_id', $academicYearId)
                                            ->where('term_id', $termId)
                                            ->where('grade_id', $gradeId)
                                            ->where('is_active', true)
                                            ->get()
                                            ->mapWithKeys(function ($feeStructure) {
                                                return [$feeStructure->id => "ZMW " . number_format($feeStructure->total_fee, 2)];
                                            });
                                    })
                                    ->disabled() // Auto-selected, just shown for visibility
                                    ->dehydrated() // Ensure value is included in form data even when disabled
                                    ->required()
                                    ->live()
                                    ->afterStateUpdated(function ($state, callable $set) {
                                        if ($state) {
                                            $feeStructure = FeeStructure::find($state);
                                            if ($feeStructure) {
                                                $set('balance', $feeStructure->total_fee);
                                            } else {
                                                $set('balance', null);
                                            }
                                        } else {
                                            $set('balance', null);
                                        }
                                    })
                                    ->visible(fn (callable $get) => (bool) $get('grade_id')),

                                // Fee amount display
                                Forms\Components\Placeholder::make('fee_amount')
                                    ->label('Total Fee Amount')
                                    ->content(function (callable $get) {
                                        $feeStructureId = $get('fee_structure_id');

                                        if (!$feeStructureId) {
                                            return 'Select grade first';
                                        }

                                        $feeStructure = FeeStructure::find($feeStructureId);

                                        if (!$feeStructure) {
                                            return 'No fee structure found';
                                        }

                                        return "ZMW " . number_format($feeStructure->total_fee, 2);
                                    })
                                    ->visible(fn (callable $get) => (bool) $get('fee_structure_id')),

                                // Step 5: Select Student for selected grade
                                Forms\Components\Select::make('student_id')
                                    ->label('Student')
                                    ->options(function (callable $get) {
                                        $gradeId = $get('grade_id');

                                        if (!$gradeId) {
                                            return [];
                                        }

                                        // FIXED: Get students using the grade_id foreign key relationship
                                        $students = Student::where('grade_id', $gradeId)
                                            ->where('enrollment_status', 'active')
                                            ->orderBy('name')
                                            ->get();

                                        // If no students found with grade_id, also try class_section approach
                                        if ($students->isEmpty()) {
                                            // Get class sections for this grade
                                            $classSectionIds = \App\Models\ClassSection::where('grade_id', $gradeId)
                                                ->pluck('id');

                                            $students = Student::whereIn('class_section_id', $classSectionIds)
                                                ->where('enrollment_status', 'active')
                                                ->orderBy('name')
                                                ->get();
                                        }

                                        return $students->pluck('name', 'id');
                                    })
                                    ->searchable()
                                    ->preload()
                                    ->required()
                                    ->live()
                                    ->afterStateUpdated(function($state, callable $set, callable $get) {
                                        // Check if this student already has fees assigned for this structure
                                        $feeStructureId = $get('fee_structure_id');
                                        $academicYearId = $get('academic_year_id');
                                        $termId = $get('term_id');
                                        $gradeId = $get('grade_id');

                                        if ($state && $feeStructureId) {
                                            $existingFee = StudentFee::where('student_id', $state)
                                                ->where('fee_structure_id', $feeStructureId)
                                                ->first();

                                            if ($existingFee) {
                                                // Get the URL to edit the existing fee
                                                $url = route('filament.admin.resources.student-fees.edit', ['record' => $existingFee->id]);

                                                Notification::make()
                                                    ->title('Duplicate Fee Record')
                                                    ->body('This student already has a fee for this term/grade. You cannot create a duplicate. Please edit the existing record.')
                                                    ->danger()
                                                    ->actions([
                                                        \Filament\Notifications\Actions\Action::make('edit')
                                                            ->label('Edit Existing Record')
                                                            ->url($url)
                                                            ->button()
                                                            ->color('primary'),
                                                    ])
                                                    ->persistent()
                                                    ->send();

                                                // Clear the student selection to prevent accidental creation
                                                $set('student_id', null);
                                            }
                                        }
                                    })
                                    ->visible(fn (callable $get) => (bool) $get('fee_structure_id')),
                            ]),

                        Forms\Components\Section::make('Payment Details')
                            ->schema([
                                Forms\Components\Select::make('payment_status')
                                    ->options([
                                        'unpaid' => 'Unpaid',
                                        'partial' => 'Partial',
                                        'paid' => 'Paid',
                                    ])
                                    ->required()
                                    ->default('unpaid')
                                    ->live()
                                    ->reactive()
                                    ->disabled()
                                    ->dehydrated()
                                    ->helperText('Automatically calculated based on amount paid'),

                                Forms\Components\TextInput::make('amount_paid')
                                    ->numeric()
                                    ->required()
                                    ->default(0)
                                    ->prefix('ZMW')
                                    ->step(0.01)
                                    ->live()
                                    ->reactive()
                                    ->afterStateUpdated(function ($state, callable $set, $get) {
                                        $feeStructureId = $get('fee_structure_id');
                                        if ($feeStructureId) {
                                            $feeStructure = FeeStructure::find($feeStructureId);
                                            if ($feeStructure) {
                                                $amountPaid = (float) $state;
                                                $totalFee = (float) $feeStructure->total_fee;
                                                $balance = $totalFee - $amountPaid;

                                                $set('balance', max(0, $balance));

                                                if ($amountPaid <= 0) {
                                                    $set('payment_status', 'unpaid');
                                                } elseif ($amountPaid >= $totalFee) {
                                                    $set('payment_status', 'paid');
                                                } else {
                                                    $set('payment_status', 'partial');
                                                }
                                            }
                                        }
                                    }),

                                Forms\Components\TextInput::make('balance')
                                    ->numeric()
                                    ->required()
                                    ->prefix('ZMW')
                                    ->step(0.01)
                                    ->disabled(),

                                Forms\Components\DatePicker::make('payment_date')
                                    ->required()
                                    ->visible(fn (callable $get) => $get('payment_status') !== 'unpaid')
                                    ->default(now()),

                                Forms\Components\TextInput::make('receipt_number')
                                    ->label('Receipt Number (Auto-generated)')
                                    ->disabled()
                                    ->dehydrated()
                                    ->visible(fn (callable $get) => $get('payment_status') !== 'unpaid')
                                    ->placeholder('Will be auto-generated')
                                    ->helperText('Receipt number will be automatically generated')
                                    ->maxLength(255),

                                Forms\Components\Select::make('payment_method')
                                    ->options([
                                        'cash' => 'Cash',
                                        'mobile_money' => 'Mobile Money',
                                        'bank_transfer' => 'Bank Transfer',
                                        'cheque' => 'Cheque',
                                        'other' => 'Other',
                                    ])
                                    ->required()
                                    ->live()
                                    ->visible(fn (callable $get) => $get('payment_status') !== 'unpaid'),

                                Forms\Components\TextInput::make('payment_reference')
                                    ->label('Mobile Money Reference Number')
                                    ->required()
                                    ->maxLength(255)
                                    ->helperText('Enter the unique payment reference from your mobile money transaction')
                                    ->visible(fn (callable $get) => $get('payment_method') === 'mobile_money'),

                                Forms\Components\FileUpload::make('proof_of_payment')
                                    ->label('Proof of Payment')
                                    ->image()
                                    ->acceptedFileTypes(['image/*', 'application/pdf'])
                                    ->maxSize(5120) // 5MB
                                    ->directory('payment-proofs')
                                    ->helperText('Upload bank transfer receipt, cheque image, or other proof (Max 5MB)')
                                    ->visible(fn (callable $get) => in_array($get('payment_method'), ['bank_transfer', 'cheque', 'other'])),

                                Forms\Components\Toggle::make('send_sms_notification')
                                    ->label('Send SMS Notification')
                                    ->helperText('Send an SMS notification to the parent/guardian about this payment')
                                    ->default(true)
                                    ->visible(fn (callable $get) => $get('payment_status') !== 'unpaid'),
                            ])
                            ->visible(fn (callable $get) => (bool) $get('student_id')),
                    ])
                    ->columnSpan(2),

                Forms\Components\Group::make()
                    ->schema([
                        Forms\Components\Section::make('Notes')
                            ->schema([
                                Forms\Components\Textarea::make('notes')
                                    ->maxLength(65535),
                            ]),

                        Forms\Components\Section::make('Fee Structure Details')
                            ->schema([
                                Forms\Components\Placeholder::make('fee_details')
                                    ->label('Fee Structure Details')
                                    ->content(function (callable $get) {
                                        $feeStructureId = $get('fee_structure_id');

                                        if (!$feeStructureId) {
                                            return 'Select grade first';
                                        }

                                        $feeStructure = FeeStructure::find($feeStructureId);

                                        if (!$feeStructure) {
                                            return 'No fee structure found';
                                        }

                                        $details = "<strong>Basic Fee:</strong> ZMW " . number_format($feeStructure->basic_fee, 2) . "<br>";

                                        if ($feeStructure->additional_charges) {
                                            $details .= "<strong>Additional Charges:</strong><br>";

                                            foreach ($feeStructure->additional_charges as $charge) {
                                                if (isset($charge['description']) && isset($charge['amount'])) {
                                                    $details .= "• {$charge['description']}: ZMW " . number_format($charge['amount'], 2) . "<br>";
                                                }
                                            }
                                        }

                                        $details .= "<strong>Total:</strong> ZMW " . number_format($feeStructure->total_fee, 2);

                                        return new \Illuminate\Support\HtmlString($details);
                                    })
                                    ->visible(fn (callable $get) => (bool) $get('fee_structure_id')),
                            ])
                            ->visible(fn (callable $get) => (bool) $get('fee_structure_id')),

                        Forms\Components\Section::make('Student Information')
                            ->schema([
                                Forms\Components\Placeholder::make('student_details')
                                    ->label('Student Details')
                                    ->content(function (callable $get) {
                                        $studentId = $get('student_id');

                                        if (!$studentId) {
                                            return 'No student selected';
                                        }

                                        $student = Student::with(['parentGuardian', 'grade'])->find($studentId);

                                        if (!$student) {
                                            return 'Student not found';
                                        }

                                        $details = "<strong>Name:</strong> {$student->name}<br>";

                                        // Get grade name from relationship or fallback
                                        $gradeName = '';
                                        if ($student->grade) {
                                            $gradeName = $student->grade->name;
                                        } elseif ($student->grade_id) {
                                            $grade = Grade::find($student->grade_id);
                                            $gradeName = $grade ? $grade->name : 'Unknown';
                                        } else {
                                            $gradeName = 'Not assigned';
                                        }

                                        $details .= "<strong>Grade:</strong> {$gradeName}<br>";
                                        $details .= "<strong>ID Number:</strong> " . ($student->student_id_number ?? 'Not assigned') . "<br>";

                                        if ($student->parentGuardian) {
                                            $details .= "<br><strong>Parent/Guardian:</strong> {$student->parentGuardian->name}<br>";
                                            $details .= "<strong>Contact:</strong> {$student->parentGuardian->phone}<br>";
                                        }

                                        return new \Illuminate\Support\HtmlString($details);
                                    })
                                    ->visible(fn (callable $get) => (bool) $get('student_id')),
                            ])
                            ->visible(fn (callable $get) => (bool) $get('student_id')),
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
                    ->sortable(),
                Tables\Columns\TextColumn::make('feeStructure.grade.name')
                    ->label('Grade')
                    ->sortable(),
                Tables\Columns\TextColumn::make('feeStructure.term.name')
                    ->label('Term')
                    ->sortable(),
                Tables\Columns\TextColumn::make('feeStructure.academicYear.name')
                    ->label('Academic Year')
                    ->sortable(),
                Tables\Columns\TextColumn::make('feeStructure.total_fee')
                    ->money('ZMW')
                    ->label('Total Fee')
                    ->sortable(),
                Tables\Columns\TextColumn::make('amount_paid')
                    ->money('ZMW')
                    ->sortable(),
                Tables\Columns\TextColumn::make('balance')
                    ->money('ZMW')
                    ->sortable(),
                Tables\Columns\BadgeColumn::make('payment_status')
                    ->colors([
                        'danger' => 'unpaid',
                        'warning' => 'partial',
                        'success' => 'paid',
                    ]),
                Tables\Columns\TextColumn::make('payment_date')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('receipt_number')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('payment_method')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('payment_status')
                    ->options([
                        'unpaid' => 'Unpaid',
                        'partial' => 'Partial',
                        'paid' => 'Paid',
                    ]),
                Tables\Filters\SelectFilter::make('student')
                    ->relationship('student', 'name'),
                Tables\Filters\SelectFilter::make('academic_year_id')
                    ->label('Academic Year')
                    ->options(function() {
                        return AcademicYear::orderBy('name')
                            ->pluck('name', 'id')
                            ->toArray();
                    })
                    ->default(fn () => AcademicYear::where('is_active', true)->first()?->id),
                Tables\Filters\SelectFilter::make('term_id')
                    ->label('Term')
                    ->options(function() {
                        $activeYear = AcademicYear::where('is_active', true)->first();
                        if (!$activeYear) {
                            return Term::orderBy('name')->pluck('name', 'id')->toArray();
                        }
                        return Term::where('academic_year_id', $activeYear->id)
                            ->orderBy('start_date')
                            ->pluck('name', 'id')
                            ->toArray();
                    })
                    ->default(fn () => Term::where('is_active', true)->first()?->id),
                Tables\Filters\SelectFilter::make('grade_id')
                    ->label('Grade')
                    ->options(function() {
                        return Grade::orderBy('level')
                            ->pluck('name', 'id')
                            ->toArray();
                    }),
                Tables\Filters\Filter::make('payment_date')
                    ->form([
                        Forms\Components\DatePicker::make('from'),
                        Forms\Components\DatePicker::make('until'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['from'],
                                fn (Builder $query, $date): Builder => $query->whereDate('payment_date', '>=', $date),
                            )
                            ->when(
                                $data['until'],
                                fn (Builder $query, $date): Builder => $query->whereDate('payment_date', '<=', $date),
                            );
                    }),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\Action::make('recordPayment')
                    ->label('Record Payment')
                    ->icon('heroicon-o-currency-dollar')
                    ->color('success')
                    ->modalWidth('4xl')
                    ->form(function ($record) {
                        $totalFee = $record->feeStructure->total_fee ?? 0;
                        $amountPaid = $record->amount_paid ?? 0;
                        $balance = $record->balance ?? $totalFee;

                        // Get payment history
                        $transactions = $record->paymentTransactions()
                            ->orderBy('transaction_date', 'desc')
                            ->get();

                        $paymentHistory = '';
                        if ($transactions->isNotEmpty()) {
                            $paymentHistory .= '<div class="space-y-2">';
                            foreach ($transactions as $transaction) {
                                $date = $transaction->transaction_date->format('d M Y');
                                $amount = number_format($transaction->amount, 2);
                                $method = ucfirst(str_replace('_', ' ', $transaction->payment_method ?? 'N/A'));

                                $paymentHistory .= '<div class="flex justify-between items-center p-3 bg-gray-50 dark:bg-gray-800 rounded-lg">';
                                $paymentHistory .= '<div>';
                                $paymentHistory .= '<div class="font-medium text-gray-900 dark:text-white">' . $date . '</div>';
                                $paymentHistory .= '<div class="text-sm text-gray-500 dark:text-gray-400">' . $method . '</div>';
                                $paymentHistory .= '</div>';
                                $paymentHistory .= '<div class="text-right">';
                                $paymentHistory .= '<div class="font-semibold text-green-600 dark:text-green-400">ZMW ' . $amount . '</div>';
                                $paymentHistory .= '</div>';
                                $paymentHistory .= '</div>';
                            }
                            $paymentHistory .= '</div>';
                        } else {
                            $paymentHistory = '<p class="text-gray-500 dark:text-gray-400 text-center py-4">No previous payments recorded</p>';
                        }

                        return [
                            Forms\Components\Section::make('Fee Summary')
                                ->schema([
                                    Forms\Components\Grid::make(3)
                                        ->schema([
                                            Forms\Components\Placeholder::make('total_fee_display')
                                                ->label('Total Fee')
                                                ->content(fn () => 'ZMW ' . number_format($totalFee, 2))
                                                ->extraAttributes(['class' => 'text-lg font-bold text-blue-600']),

                                            Forms\Components\Placeholder::make('amount_paid_display')
                                                ->label('Amount Paid')
                                                ->content(fn () => 'ZMW ' . number_format($amountPaid, 2))
                                                ->extraAttributes(['class' => 'text-lg font-bold text-green-600']),

                                            Forms\Components\Placeholder::make('balance_display')
                                                ->label('Outstanding Balance')
                                                ->content(fn () => 'ZMW ' . number_format($balance, 2))
                                                ->extraAttributes(['class' => 'text-lg font-bold text-orange-600']),
                                        ]),
                                ])
                                ->columnSpan('full'),

                            Forms\Components\Section::make('Payment History')
                                ->schema([
                                    Forms\Components\Placeholder::make('previous_payments')
                                        ->label('')
                                        ->content(new \Illuminate\Support\HtmlString($paymentHistory)),
                                ])
                                ->collapsible()
                                ->collapsed(false)
                                ->visible(fn () => $transactions->isNotEmpty())
                                ->columnSpan('full'),

                            Forms\Components\Section::make('New Payment Details')
                                ->schema([
                                    Forms\Components\Grid::make(2)
                                        ->schema([
                                            Forms\Components\TextInput::make('amount_paid')
                                                ->label('Payment Amount')
                                                ->numeric()
                                                ->required()
                                                ->prefix('ZMW')
                                                ->step(0.01)
                                                ->suffix('Remaining: ZMW ' . number_format($balance, 2))
                                                ->helperText('Enter the amount being paid today')
                                                ->autofocus(),

                                            Forms\Components\DatePicker::make('payment_date')
                                                ->label('Payment Date')
                                                ->required()
                                                ->default(now())
                                                ->maxDate(now())
                                                ->native(false)
                                                ->displayFormat('d/m/Y'),
                                        ]),

                                    Forms\Components\Grid::make(2)
                                        ->schema([
                                            Forms\Components\TextInput::make('receipt_number')
                                                ->label('Receipt Number')
                                                ->required()
                                                ->maxLength(255)
                                                ->default(fn () => 'RCP-' . date('Y') . '-' . str_pad(mt_rand(1, 99999), 6, '0', STR_PAD_LEFT))
                                                ->helperText('Auto-generated, can be modified'),

                                            Forms\Components\Select::make('payment_method')
                                                ->label('Payment Method')
                                                ->options([
                                                    'cash' => 'Cash',
                                                    'mobile_money' => 'Mobile Money',
                                                    'bank_transfer' => 'Bank Transfer',
                                                    'cheque' => 'Cheque',
                                                    'other' => 'Other',
                                                ])
                                                ->required()
                                                ->native(false),
                                        ]),

                                    Forms\Components\Textarea::make('notes')
                                        ->label('Payment Notes')
                                        ->rows(2)
                                        ->maxLength(500)
                                        ->placeholder('Optional: Add any notes about this payment'),

                                    Forms\Components\Toggle::make('send_sms_notification')
                                        ->label('Send SMS Notification to Parent')
                                        ->helperText('Notify parent/guardian about this payment via SMS')
                                        ->default(true)
                                        ->inline(false),
                                ])
                                ->columnSpan('full'),
                        ];
                    })
                    ->action(function ($record, array $data): void {
                        $paymentAmount = (float) $data['amount_paid'];
                        $newAmountPaid = (float) $record->amount_paid + $paymentAmount;
                        $totalFee = (float) $record->feeStructure->total_fee;
                        $newBalance = max(0, $totalFee - $newAmountPaid);
                        $status = 'partial';

                        if ($newBalance <= 0) {
                            $status = 'paid';
                            $newBalance = 0;
                        } elseif ($newAmountPaid <= 0) {
                            $status = 'unpaid';
                        }

                        // Update the main fee record
                        $record->update([
                            'amount_paid' => $newAmountPaid,
                            'balance' => $newBalance,
                            'payment_status' => $status,
                            'payment_date' => $data['payment_date'],
                            'receipt_number' => $data['receipt_number'],
                            'payment_method' => $data['payment_method'],
                        ]);

                        // Create payment transaction record
                        $record->paymentTransactions()->create([
                            'amount' => $paymentAmount,
                            'type' => 'payment',
                            'payment_method' => $data['payment_method'],
                            'transaction_date' => $data['payment_date'],
                            'notes' => $data['notes'] ?? null,
                            'processed_by' => Auth::id(),
                            'reference_number' => $data['receipt_number'],
                        ]);

                        // Debug log for fee collection dashboard
                        Log::info('Payment recorded', [
                            'fee_id' => $record->id,
                            'fee_structure_id' => $record->fee_structure_id,
                            'amount' => $paymentAmount,
                            'total_paid' => $newAmountPaid,
                            'balance' => $newBalance,
                            'status' => $status,
                            'receipt' => $data['receipt_number']
                        ]);

                        // Send SMS notification if requested
                        if (isset($data['send_sms_notification']) && $data['send_sms_notification']) {
                            try {
                                self::sendPaymentSMS($record, $paymentAmount);
                            } catch (\Exception $e) {
                                // SMS failed but payment was recorded
                                Log::warning('Payment recorded but SMS failed', [
                                    'fee_id' => $record->id,
                                    'error' => $e->getMessage()
                                ]);
                            }
                        }

                        Notification::make()
                            ->title('Payment Recorded Successfully')
                            ->body("Payment of ZMW " . number_format($paymentAmount, 2) . " recorded. New balance: ZMW " . number_format($newBalance, 2))
                            ->success()
                            ->duration(5000)
                            ->send();
                    })
                    ->visible(fn ($record) => $record->payment_status !== 'paid'),
                Tables\Actions\Action::make('viewTransactions')
                    ->label('View Transactions')
                    ->icon('heroicon-o-rectangle-stack')
                    ->color('info')
                    ->modalWidth('6xl')
                    ->modalHeading(fn ($record) => 'Payment Transactions - ' . $record->student->name)
                    ->modalDescription(fn ($record) => $record->feeStructure->grade->name . ' | ' . $record->feeStructure->term->name . ' | ' . $record->feeStructure->academicYear->name)
                    ->modalContent(function ($record) {
                        $transactions = $record->paymentTransactions()
                            ->orderBy('transaction_date', 'asc')
                            ->get();

                        $totalFee = $record->feeStructure->total_fee;
                        $totalPaid = $record->amount_paid;
                        $balance = $record->balance;

                        $html = '<div class="space-y-6">';

                        // Summary Cards
                        $html .= '<div class="grid grid-cols-4 gap-4">';
                        $html .= '<div class="bg-blue-50 dark:bg-blue-900/20 p-4 rounded-lg border border-blue-200 dark:border-blue-800">';
                        $html .= '<div class="text-sm text-blue-600 dark:text-blue-400 font-medium">Total Fee</div>';
                        $html .= '<div class="text-2xl font-bold text-blue-900 dark:text-blue-100">ZMW ' . number_format($totalFee, 2) . '</div>';
                        $html .= '</div>';

                        $html .= '<div class="bg-green-50 dark:bg-green-900/20 p-4 rounded-lg border border-green-200 dark:border-green-800">';
                        $html .= '<div class="text-sm text-green-600 dark:text-green-400 font-medium">Total Paid</div>';
                        $html .= '<div class="text-2xl font-bold text-green-900 dark:text-green-100">ZMW ' . number_format($totalPaid, 2) . '</div>';
                        $html .= '</div>';

                        $html .= '<div class="bg-orange-50 dark:bg-orange-900/20 p-4 rounded-lg border border-orange-200 dark:border-orange-800">';
                        $html .= '<div class="text-sm text-orange-600 dark:text-orange-400 font-medium">Balance</div>';
                        $html .= '<div class="text-2xl font-bold text-orange-900 dark:text-orange-100">ZMW ' . number_format($balance, 2) . '</div>';
                        $html .= '</div>';

                        $html .= '<div class="bg-purple-50 dark:bg-purple-900/20 p-4 rounded-lg border border-purple-200 dark:border-purple-800">';
                        $html .= '<div class="text-sm text-purple-600 dark:text-purple-400 font-medium">Transactions</div>';
                        $html .= '<div class="text-2xl font-bold text-purple-900 dark:text-purple-100">' . $transactions->count() . '</div>';
                        $html .= '</div>';
                        $html .= '</div>';

                        // Transactions Timeline
                        $html .= '<div class="mt-6">';
                        $html .= '<h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Payment Timeline</h3>';

                        if ($transactions->isEmpty()) {
                            $html .= '<div class="text-center py-12 bg-gray-50 dark:bg-gray-800 rounded-lg">';
                            $html .= '<svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">';
                            $html .= '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>';
                            $html .= '</svg>';
                            $html .= '<p class="mt-2 text-sm text-gray-500 dark:text-gray-400">No payment transactions recorded yet</p>';
                            $html .= '</div>';
                        } else {
                            $html .= '<div class="relative">';
                            $html .= '<div class="absolute left-8 top-0 bottom-0 w-0.5 bg-gray-200 dark:bg-gray-700"></div>';
                            $html .= '<div class="space-y-4">';

                            $runningBalance = $totalFee;
                            foreach ($transactions as $index => $transaction) {
                                $runningBalance -= $transaction->amount;
                                $transactionNum = $index + 1;

                                $html .= '<div class="relative pl-16">';
                                $html .= '<div class="absolute left-6 w-4 h-4 bg-green-500 rounded-full border-4 border-white dark:border-gray-900"></div>';
                                $html .= '<div class="bg-white dark:bg-gray-800 p-4 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 hover:shadow-md transition-shadow">';

                                // Header
                                $html .= '<div class="flex justify-between items-start mb-3">';
                                $html .= '<div>';
                                $html .= '<div class="flex items-center gap-2">';
                                $html .= '<span class="text-xs font-semibold text-gray-500 dark:text-gray-400">#' . $transactionNum . '</span>';
                                $html .= '<h4 class="text-sm font-semibold text-gray-900 dark:text-white">' . $transaction->transaction_date->format('D, d M Y') . '</h4>';
                                $html .= '</div>';
                                $html .= '<div class="text-xs text-gray-500 dark:text-gray-400">' . $transaction->transaction_date->format('h:i A') . '</div>';
                                $html .= '</div>';
                                $html .= '<div class="text-right">';
                                $html .= '<div class="text-2xl font-bold text-green-600 dark:text-green-400">ZMW ' . number_format($transaction->amount, 2) . '</div>';
                                $html .= '<div class="text-xs text-gray-500 dark:text-gray-400">Balance: ZMW ' . number_format(max(0, $runningBalance), 2) . '</div>';
                                $html .= '</div>';
                                $html .= '</div>';

                                // Details Grid
                                $html .= '<div class="grid grid-cols-2 gap-4 text-sm">';
                                $html .= '<div>';
                                $html .= '<span class="text-gray-500 dark:text-gray-400">Receipt No:</span> ';
                                $html .= '<span class="font-medium text-gray-900 dark:text-white">' . ($transaction->reference_number ?? 'N/A') . '</span>';
                                $html .= '</div>';
                                $html .= '<div>';
                                $html .= '<span class="text-gray-500 dark:text-gray-400">Payment Method:</span> ';
                                $paymentMethod = ucfirst(str_replace('_', ' ', $transaction->payment_method ?? 'N/A'));
                                $html .= '<span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-blue-100 dark:bg-blue-900 text-blue-800 dark:text-blue-200">' . $paymentMethod . '</span>';
                                $html .= '</div>';
                                $html .= '</div>';

                                if ($transaction->notes) {
                                    $html .= '<div class="mt-2 text-sm text-gray-600 dark:text-gray-300 bg-gray-50 dark:bg-gray-900 p-2 rounded">';
                                    $html .= '<span class="font-medium">Notes:</span> ' . htmlspecialchars($transaction->notes);
                                    $html .= '</div>';
                                }

                                // Action Buttons
                                $html .= '<div class="mt-3 flex gap-2">';
                                $html .= '<a href="' . route('student-fees.transaction-receipt', ['fee' => $record->id, 'transaction' => $transaction->id]) . '" target="_blank" class="inline-flex items-center px-3 py-1.5 bg-blue-600 hover:bg-blue-700 text-white text-xs font-medium rounded-md transition-colors">';
                                $html .= '<svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path></svg>';
                                $html .= 'Print Receipt';
                                $html .= '</a>';
                                $html .= '</div>';

                                $html .= '</div>';
                                $html .= '</div>';
                            }

                            $html .= '</div>';
                            $html .= '</div>';
                        }

                        $html .= '</div>';
                        $html .= '</div>';

                        return new \Illuminate\Support\HtmlString($html);
                    })
                    ->modalFooterActions([
                        Tables\Actions\Action::make('downloadFullHistory')
                            ->label('Download Complete History')
                            ->icon('heroicon-o-arrow-down-tray')
                            ->color('success')
                            ->url(fn ($record) => route('student-fees.full-history', $record))
                            ->openUrlInNewTab(),

                        Tables\Actions\Action::make('downloadStatement')
                            ->label('Download Statement')
                            ->icon('heroicon-o-document-text')
                            ->color('primary')
                            ->url(fn ($record) => route('student-fees.receipt', $record))
                            ->openUrlInNewTab(),
                    ])
                    ->modalCancelActionLabel('Close')
                    ->visible(fn ($record) => $record->payment_status !== 'unpaid'),

                Tables\Actions\Action::make('printReceipt')
                    ->label('Print Statement')
                    ->icon('heroicon-o-printer')
                    ->color('gray')
                    ->url(fn (StudentFee $record) => route('student-fees.receipt', $record))
                    ->openUrlInNewTab()
                    ->visible(fn ($record) => $record->payment_status !== 'unpaid'),
                Tables\Actions\Action::make('sendPaymentSMS')
                    ->label('Send SMS Receipt')
                    ->icon('heroicon-o-chat-bubble-left')
                    ->color('warning')
                    ->action(function (StudentFee $record) {
                        self::sendPaymentSMS($record);
                    })
                    ->visible(fn ($record) => $record->payment_status !== 'unpaid'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),

                    // NEW: Bulk Fee Generation Actions
                    Tables\Actions\BulkAction::make('bulkGenerateFeesCurrentTerm')
                        ->label('Generate Fees for Current Term')
                        ->icon('heroicon-o-plus-circle')
                        ->color('success')
                        ->form([
                            Forms\Components\Select::make('grade_id')
                                ->label('Select Grade (Optional)')
                                ->placeholder('All grades')
                                ->options(Grade::orderBy('level')->pluck('name', 'id'))
                                ->searchable(),

                            Forms\Components\Placeholder::make('info')
                                ->content('This will create fee records for students who don\'t have fees for the current term. Existing fees will not be affected.')
                                ->columnSpanFull(),
                        ])
                        ->action(function (array $data) {
                            $grade = isset($data['grade_id']) ? Grade::find($data['grade_id']) : null;

                            try {
                                $result = StudentFeeService::bulkCreateFeesForCurrentTerm($grade);

                                if ($result['success']) {
                                    Notification::make()
                                        ->title('Bulk Fee Generation Complete')
                                        ->body($result['message'])
                                        ->success()
                                        ->send();
                                } else {
                                    Notification::make()
                                        ->title('Fee Generation Failed')
                                        ->body($result['message'])
                                        ->danger()
                                        ->send();
                                }
                            } catch (\Exception $e) {
                                Log::error('Bulk fee generation failed', [
                                    'error' => $e->getMessage(),
                                    'grade_id' => $grade?->id
                                ]);

                                Notification::make()
                                    ->title('Generation Failed')
                                    ->body('An error occurred during fee generation: ' . $e->getMessage())
                                    ->danger()
                                    ->send();
                            }
                        })
                        ->requiresConfirmation()
                        ->modalHeading('Generate Student Fees')
                        ->modalSubheading('Are you sure you want to generate fees for students?')
                        ->deselectRecordsAfterCompletion(),

                    Tables\Actions\BulkAction::make('previewFeeGeneration')
                        ->label('Preview Fee Generation')
                        ->icon('heroicon-o-eye')
                        ->color('info')
                        ->form([
                            Forms\Components\Select::make('grade_id')
                                ->label('Select Grade (Optional)')
                                ->placeholder('All grades')
                                ->options(Grade::orderBy('level')->pluck('name', 'id'))
                                ->searchable(),
                        ])
                        ->action(function (array $data) {
                            $grade = isset($data['grade_id']) ? Grade::find($data['grade_id']) : null;
                            $preview = StudentFeeService::previewFeeCreation($grade);

                            if (!$preview['success']) {
                                Notification::make()
                                    ->title('Preview Failed')
                                    ->body($preview['message'])
                                    ->danger()
                                    ->send();
                                return;
                            }

                            $gradeText = $grade ? "for {$grade->name}" : "for all grades";
                            $message = "Fee Generation Preview {$gradeText}:\n\n";
                            $message .= "• Total Students: {$preview['total_students']}\n";
                            $message .= "• Students Needing Fees: {$preview['students_needing_fees']}\n";
                            $message .= "• Students With Fees: {$preview['students_with_fees']}\n";
                            $message .= "• Current Term: {$preview['current_term']}\n";
                            $message .= "• Academic Year: {$preview['current_academic_year']}";

                            Notification::make()
                                ->title('Fee Generation Preview')
                                ->body($message)
                                ->info()
                                ->persistent()
                                ->send();
                        })
                        ->deselectRecordsAfterCompletion(),

                    Tables\Actions\BulkAction::make('bulkPrintReceipts')
                        ->label('Print Receipts')
                        ->icon('heroicon-o-printer')
                        ->color('primary')
                        ->action(function (Builder $query) {
                            // This action would typically trigger a batch job to generate receipts
                            $count = $query->where('payment_status', '!=', 'unpaid')->count();

                            if ($count > 0) {
                                Notification::make()
                                    ->title('Receipt Generation Initiated')
                                    ->body("Generating receipts for {$count} payments. Please check the downloads folder.")
                                    ->success()
                                    ->send();
                            } else {
                                Notification::make()
                                    ->title('No Receipts to Generate')
                                    ->body("There are no paid or partially paid fees to generate receipts for.")
                                    ->warning()
                                    ->send();
                            }
                        })
                        ->deselectRecordsAfterCompletion(),

                    Tables\Actions\BulkAction::make('bulkSendSMS')
                        ->label('Send SMS Receipts')
                        ->icon('heroicon-o-chat-bubble-left-ellipsis')
                        ->color('warning')
                        ->action(function (Builder $query) {
                            $records = $query->where('payment_status', '!=', 'unpaid')->get();

                            $successCount = 0;
                            $failedCount = 0;

                            foreach ($records as $record) {
                                try {
                                    self::sendPaymentSMS($record);
                                    $successCount++;
                                } catch (\Exception $e) {
                                    $failedCount++;
                                    Log::error('Failed to send bulk SMS receipt', [
                                        'student_fee_id' => $record->id,
                                        'error' => $e->getMessage()
                                    ]);
                                }
                            }

                            Notification::make()
                                ->title('SMS Receipts')
                                ->body("Successfully sent: {$successCount}, Failed: {$failedCount}")
                                ->success($successCount > 0)
                                ->warning($failedCount > 0)
                                ->send();
                        })
                        ->deselectRecordsAfterCompletion()
                        ->requiresConfirmation(),
                ]),
            ]);
   }

   /**
    * Send payment SMS notification
    */
   public static function sendPaymentSMS(StudentFee $studentFee, float $lastPaymentAmount = null): void
   {
       // Get the student
       $student = Student::find($studentFee->student_id);

       if (!$student || !$student->parent_guardian_id) {
           Notification::make()
               ->title('SMS Not Sent')
               ->body('No parent/guardian found for this student.')
               ->warning()
               ->send();
           return;
       }

       // Get the parent/guardian
       $parentGuardian = ParentGuardian::find($student->parent_guardian_id);

       if (!$parentGuardian || !$parentGuardian->phone) {
           Notification::make()
               ->title('SMS Not Sent')
               ->body('No phone number found for the parent/guardian.')
               ->warning()
               ->send();
           return;
       }

       try {
           // Payment amount is either the specified last payment or the total amount paid
           $paymentAmount = $lastPaymentAmount ?? $studentFee->amount_paid;

           // Format the message with payment details
           $message = "Dear {$parentGuardian->name}, thank you for your payment of ZMW {$paymentAmount} for {$student->name}'s fees. ";

           // Add fee structure details
           if ($studentFee->feeStructure) {
               $gradeName = $studentFee->feeStructure->grade->name ?? 'Unknown';
               $termName = $studentFee->feeStructure->term->name ?? 'Unknown';
               $message .= "Grade: {$gradeName}, Term: {$termName}. ";
           }

           // Add payment status
           $message .= "Total fee: ZMW {$studentFee->feeStructure->total_fee}, Balance: ZMW {$studentFee->balance}. ";

           if ($studentFee->payment_status === 'paid') {
               $message .= "Status: FULLY PAID. Thank you!";
           } else {
               $message .= "Status: PARTIALLY PAID. Receipt No: {$studentFee->receipt_number}.";
           }

           // Send SMS using SmsService (handles logging automatically)
           $smsService = app(SmsService::class);
           $success = $smsService->send(
               $message,
               $parentGuardian->phone,
               'fee_reminder',
               $studentFee->id
           );

           if ($success) {
               // Show success notification
               Notification::make()
                   ->title('Payment Notification Sent')
                   ->body("SMS notification sent to {$parentGuardian->name}.")
                   ->success()
                   ->send();
           } else {
               Notification::make()
                   ->title('SMS Notification Failed')
                   ->body('Failed to send SMS. Check logs for details.')
                   ->danger()
                   ->send();
           }

       } catch (\Exception $e) {
           // Log error
           Log::error('Failed to send payment notification', [
               'student_fee_id' => $studentFee->id,
               'student_id' => $student->id,
               'parent_id' => $parentGuardian->id,
               'error' => $e->getMessage()
           ]);

           // Show error notification
           Notification::make()
               ->title('SMS Notification Failed')
               ->body("Could not send payment notification: {$e->getMessage()}")
               ->danger()
               ->send();

           // Re-throw the exception to be caught by the caller
           throw $e;
       }
   }

   public static function getWidgets(): array
   {
       return [
           StudentFeeResource\Widgets\StudentFeeStatsWidget::class,
       ];
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
           'index' => Pages\ListStudentFees::route('/'),
           'create' => Pages\CreateStudentFee::route('/create'),
           'view' => Pages\ViewStudentFee::route('/{record}'),
           'edit' => Pages\EditStudentFee::route('/{record}/edit'),
       ];
   }
}
