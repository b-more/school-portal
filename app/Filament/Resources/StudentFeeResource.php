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
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
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
                                                    ->title('Fee Already Assigned')
                                                    ->body('This student already has fees assigned for this term/grade. Please edit the existing record instead.')
                                                    ->warning()
                                                    ->actions([
                                                        \Filament\Notifications\Actions\Action::make('edit')
                                                            ->label('Edit Existing Record')
                                                            ->url($url)
                                                            ->openUrlInNewTab(),
                                                    ])
                                                    ->persistent()
                                                    ->send();
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
                                    ->reactive(),

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
                                    ->required()
                                    ->visible(fn (callable $get) => $get('payment_status') !== 'unpaid')
                                    ->maxLength(255),

                                Forms\Components\Select::make('payment_method')
                                    ->options([
                                        'cash' => 'Cash',
                                        'bank_transfer' => 'Bank Transfer',
                                        'mobile_money' => 'Mobile Money',
                                        'cheque' => 'Cheque',
                                        'other' => 'Other',
                                    ])
                                    ->required()
                                    ->visible(fn (callable $get) => $get('payment_status') !== 'unpaid'),

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
                    }),
                Tables\Filters\SelectFilter::make('term_id')
                    ->label('Term')
                    ->options(function() {
                        return Term::orderBy('name')
                            ->pluck('name', 'id')
                            ->toArray();
                    }),
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
                Tables\Actions\DeleteAction::make(),
                Tables\Actions\Action::make('recordPayment')
                    ->label('Record Payment')
                    ->icon('heroicon-o-currency-dollar')
                    ->color('success')
                    ->form([
                        Forms\Components\TextInput::make('amount_paid')
                            ->numeric()
                            ->required()
                            ->prefix('ZMW')
                            ->step(0.01),
                        Forms\Components\DatePicker::make('payment_date')
                            ->required()
                            ->default(now()),
                        Forms\Components\TextInput::make('receipt_number')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\Select::make('payment_method')
                            ->options([
                                'cash' => 'Cash',
                                'bank_transfer' => 'Bank Transfer',
                                'mobile_money' => 'Mobile Money',
                                'cheque' => 'Cheque',
                                'other' => 'Other',
                            ])
                            ->required(),
                        Forms\Components\Textarea::make('notes')
                            ->maxLength(65535),
                        Forms\Components\Toggle::make('send_sms_notification')
                            ->label('Send SMS Notification')
                            ->helperText('Send an SMS notification to the parent/guardian about this payment')
                            ->default(true),
                    ])
                    ->action(function ($record, array $data): void {
                        $paymentAmount = (float) $data['amount_paid'];
                        $newAmountPaid = (float) $record->amount_paid + $paymentAmount;
                        $totalFee = (float) $record->feeStructure->total_fee;
                        $newBalance = $totalFee - $newAmountPaid;
                        $status = 'partial';

                        if ($newBalance <= 0) {
                            $status = 'paid';
                            $newBalance = 0;
                        }

                        $record->update([
                            'amount_paid' => $newAmountPaid,
                            'balance' => $newBalance,
                            'payment_status' => $status,
                            'payment_date' => $data['payment_date'],
                            'receipt_number' => $data['receipt_number'],
                            'payment_method' => $data['payment_method'],
                            'notes' => $data['notes'] ?? $record->notes,
                        ]);

                        // Debug log for fee collection dashboard
                        Log::info('Payment recorded', [
                            'fee_id' => $record->id,
                            'fee_structure_id' => $record->fee_structure_id,
                            'amount' => $paymentAmount,
                            'total_paid' => $newAmountPaid,
                            'balance' => $newBalance,
                            'status' => $status
                        ]);

                        // Send SMS notification if requested
                        if (isset($data['send_sms_notification']) && $data['send_sms_notification']) {
                            self::sendPaymentSMS($record, $paymentAmount);
                        }

                        Notification::make()
                            ->title('Payment Recorded')
                            ->body("Payment of ZMW {$paymentAmount} has been recorded successfully.")
                            ->success()
                            ->send();
                    })
                    ->visible(fn ($record) => $record->payment_status !== 'paid'),
                Tables\Actions\Action::make('printReceipt')
                    ->label('Print Receipt')
                    ->icon('heroicon-o-printer')
                    ->color('primary')
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

           // Format phone number
           $phoneNumber = self::formatPhoneNumber($parentGuardian->phone);

           // Send the SMS
           self::sendMessage($message, $phoneNumber);

           // Log successful SMS
           Log::info('Fee payment notification sent', [
               'student_fee_id' => $studentFee->id,
               'student_id' => $student->id,
               'parent_id' => $parentGuardian->id,
               'amount' => $paymentAmount,
               'total_amount' => $studentFee->feeStructure->total_fee,
               'balance' => $studentFee->balance,
               'receipt' => $studentFee->receipt_number
           ]);

           // Show success notification
           Notification::make()
               ->title('Payment Notification Sent')
               ->body("SMS notification sent to {$parentGuardian->name}.")
               ->success()
               ->send();

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

   /**
    * Format phone number to ensure it has the country code
    */
   protected static function formatPhoneNumber(string $phoneNumber): string
   {
       // Remove any non-numeric characters
       $phoneNumber = preg_replace('/[^0-9]/', '', $phoneNumber);

       // Check if number already has country code (260 for Zambia)
       if (substr($phoneNumber, 0, 3) === '260') {
           // Number already has country code
           return $phoneNumber;
       }

       // If starting with 0, replace with country code
       if (substr($phoneNumber, 0, 1) === '0') {
           return '260' . substr($phoneNumber, 1);
       }

       // If number doesn't have country code, add it
       if (strlen($phoneNumber) === 9) {
           return '260' . $phoneNumber;
       }

       return $phoneNumber;
   }

   /**
    * Send a message via SMS
    */
   protected static function sendMessage($message_string, $phone_number)
   {
       try {
           // Log the sending attempt
           Log::info('Sending fee payment SMS notification', [
               'phone' => $phone_number,
               'message' => substr($message_string, 0, 30) . '...' // Only log beginning of message for privacy
           ]);

           $url_encoded_message = urlencode($message_string);

           $sendSenderSMS = Http::withoutVerifying()
               ->post('https://www.cloudservicezm.com/smsservice/httpapi?username=Blessmore&password=Blessmore&msg=' . $url_encoded_message . '&shortcode=2343&sender_id=StFrancis&phone=' . $phone_number . '&api_key=121231313213123123');

           // Log the response
           Log::info('SMS API Response for fee payment', [
               'status' => $sendSenderSMS->status(),
               'body' => $sendSenderSMS->body(),
               'to' => substr($phone_number, 0, 6) . '****' . substr($phone_number, -3),
           ]);

           return $sendSenderSMS->successful();
       } catch (\Exception $e) {
           Log::error('Fee payment SMS sending failed', [
               'error' => $e->getMessage(),
               'phone' => $phone_number,
           ]);
           throw $e; // Re-throw to be caught by the calling method
       }
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
