<?php

namespace App\Filament\Resources\TeacherAssignmentResource\Pages;

use App\Filament\Resources\TeacherAssignmentResource;
use App\Models\ParentGuardian;
use App\Models\Student;
use App\Services\SmsService;
use Filament\Actions;
use Filament\Forms;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ListTeacherAssignments extends ListRecords
{
    protected static string $resource = TeacherAssignmentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // Broadcast message action
            Actions\Action::make('broadcastMessage')
                ->label('Send Broadcast')
                ->icon('heroicon-o-paper-airplane')
                ->color('warning')
                ->modalWidth('lg')
                ->modalHeading('Send Broadcast Message to Parents')
                ->form([
                    Forms\Components\Section::make('Target Recipients')
                        ->description('Select which parents should receive this message')
                        ->schema([
                            Forms\Components\Select::make('selection_type')
                                ->label('Selection Method')
                                ->options([
                                    'manual' => 'Select Parents Manually',
                                    'grade' => 'Filter by Grade',
                                    'payment' => 'Filter by Payment Status',
                                    'all' => 'All Parents',
                                ])
                                ->default('manual')
                                ->live()
                                ->required(),

                            // Manual parent selection
                            Forms\Components\Select::make('parents')
                                ->label('Select Parents')
                                ->multiple()
                                ->searchable()
                                ->preload()
                                ->relationship('parentGuardians', 'name')
                                ->getOptionLabelFromRecordUsing(fn($record) => "{$record->name} ({$record->phone})")
                                ->visible(fn ($get) => $get('selection_type') === 'manual')
                                ->required(fn ($get) => $get('selection_type') === 'manual'),

                            // Grade-based selection
                            Forms\Components\Select::make('grades')
                                ->label('Select Grades')
                                ->multiple()
                                ->options(function () {
                                    return Student::select('grade')
                                        ->distinct()
                                        ->pluck('grade', 'grade')
                                        ->toArray();
                                })
                                ->visible(fn ($get) => $get('selection_type') === 'grade')
                                ->required(fn ($get) => $get('selection_type') === 'grade'),

                            // Payment status selection
                            Forms\Components\Select::make('payment_status')
                                ->label('Payment Status')
                                ->options([
                                    'unpaid' => 'Unpaid Fees',
                                    'partial' => 'Partially Paid Fees',
                                    'paid' => 'Fully Paid Fees',
                                    'overdue' => 'Overdue Payments',
                                ])
                                ->multiple()
                                ->visible(fn ($get) => $get('selection_type') === 'payment')
                                ->required(fn ($get) => $get('selection_type') === 'payment'),

                            // Recipient count display
                            Forms\Components\Placeholder::make('recipient_count')
                                ->label('Estimated Recipients')
                                ->content(function ($get) {
                                    $count = 0;
                                    $type = $get('selection_type');

                                    if ($type === 'manual' && !empty($get('parents'))) {
                                        $count = count($get('parents'));
                                    } elseif ($type === 'grade' && !empty($get('grades'))) {
                                        $count = ParentGuardian::whereHas('students', function ($query) use ($get) {
                                            $query->whereIn('grade', $get('grades'));
                                        })->count();
                                    } elseif ($type === 'payment' && !empty($get('payment_status'))) {
                                        // Count parents with students having the selected payment status
                                        $count = ParentGuardian::whereHas('students', function ($query) use ($get) {
                                            $query->whereHas('fees', function ($feeQuery) use ($get) {
                                                $feeQuery->whereIn('payment_status', $get('payment_status'));
                                            });
                                        })->count();
                                    } elseif ($type === 'all') {
                                        $count = ParentGuardian::whereNotNull('phone')->count();
                                    }

                                    return $count > 0
                                        ? "{$count} parents will receive this message"
                                        : "No recipients selected";
                                }),
                        ]),

                    Forms\Components\Section::make('Message Content')
                        ->description('Compose your message')
                        ->schema([
                            Forms\Components\Textarea::make('message')
                                ->label('Message')
                                ->required()
                                ->rows(4)
                                ->maxLength(320)
                                ->helperText('Available placeholders: {parent_name}, {student_name}, {grade}, {balance}')
                                ->placeholder('Enter your message here...'),

                            Forms\Components\Toggle::make('include_student_details')
                                ->label('Include Student Details')
                                ->helperText('Automatically add student name and grade to the message')
                                ->default(true),

                            Forms\Components\Toggle::make('send_test')
                                ->label('Send Test Message First')
                                ->helperText('Send a test message to your number before broadcasting')
                                ->default(true),

                            Forms\Components\TextInput::make('test_number')
                                ->label('Your Phone Number')
                                ->tel()
                                ->visible(fn ($get) => $get('send_test'))
                                ->required(fn ($get) => $get('send_test'))
                                ->placeholder('260xxxxxxxxx')
                                ->helperText('Enter your number to receive a test message'),
                        ]),
                ])
                ->action(function (array $data) {
                    // First, handle test message if requested
                    if (!empty($data['send_test']) && !empty($data['test_number'])) {
                        try {
                            $testMessage = "[TEST MESSAGE] " . $data['message'];
                            $testNumber = $this->formatPhoneNumber($data['test_number']);
                            $this->sendMessage($testMessage, $testNumber);

                            Notification::make()
                                ->title('Test Message Sent')
                                ->body('A test message has been sent to your number.')
                                ->success()
                                ->send();

                            // Give user a chance to cancel after seeing test
                            return;

                        } catch (\Exception $e) {
                            Notification::make()
                                ->title('Test Message Failed')
                                ->body("Error: {$e->getMessage()}")
                                ->danger()
                                ->send();

                            return;
                        }
                    }

                    // Get parent recipients based on selection criteria
                    $parentQuery = ParentGuardian::whereNotNull('phone');

                    // Apply filters based on selection type
                    if ($data['selection_type'] === 'manual' && !empty($data['parents'])) {
                        $parentQuery->whereIn('id', $data['parents']);
                    } elseif ($data['selection_type'] === 'grade' && !empty($data['grades'])) {
                        $parentQuery->whereHas('students', function ($query) use ($data) {
                            $query->whereIn('grade', $data['grades']);
                        });
                    } elseif ($data['selection_type'] === 'payment' && !empty($data['payment_status'])) {
                        $parentQuery->whereHas('students', function ($query) use ($data) {
                            $query->whereHas('fees', function ($feeQuery) use ($data) {
                                $feeQuery->whereIn('payment_status', $data['payment_status']);
                            });
                        });
                    }

                    // Get the final list of parents
                    $parents = $parentQuery->get();

                    if ($parents->isEmpty()) {
                        Notification::make()
                            ->title('No Recipients')
                            ->body('No parents match your criteria or have phone numbers.')
                            ->warning()
                            ->send();
                        return;
                    }

                    // Initialize counters
                    $successCount = 0;
                    $failCount = 0;

                    // Start database transaction for SMS logs
                    DB::beginTransaction();

                    try {
                        foreach ($parents as $parent) {
                            // Skip if no phone number
                            if (empty($parent->phone)) {
                                continue;
                            }

                            // Get the first student for this parent for personalization
                            $student = $parent->students()->first();

                            // Personalize the message
                            $personalized = $data['message'];
                            $personalized = str_replace('{parent_name}', $parent->name, $personalized);

                            if ($student) {
                                $personalized = str_replace('{student_name}', $student->name, $personalized);
                                $personalized = str_replace('{grade}', $student->grade ?? 'N/A', $personalized);

                                // Get balance if needed
                                if (strpos($personalized, '{balance}') !== false) {
                                    $balance = $student->fees()->sum('balance') ?? 0;
                                    $personalized = str_replace('{balance}', number_format($balance, 2), $personalized);
                                }

                                // Append student details if requested
                                if (!empty($data['include_student_details']) &&
                                    strpos($personalized, '{student_name}') === false) {
                                    $personalized .= "\nStudent: {$student->name}";

                                    if (!empty($student->grade)) {
                                        $personalized .= ", Grade: {$student->grade}";
                                    }
                                }
                            }

                            // Send the message
                            try {
                                $formattedPhone = $this->formatPhoneNumber($parent->phone);
                                $this->sendMessage($personalized, $formattedPhone);

                                // Log successful SMS
                                $this->logSms($parent, $personalized, 'sent');
                                $successCount++;

                            } catch (\Exception $e) {
                                // Log failed SMS
                                $this->logSms($parent, $personalized, 'failed', $e->getMessage());
                                $failCount++;

                                // Log the error
                                Log::error('SMS broadcast failed for parent', [
                                    'parent_id' => $parent->id,
                                    'error' => $e->getMessage()
                                ]);
                            }
                        }

                        // Commit the transaction
                        DB::commit();

                        // Show final notification
                        Notification::make()
                            ->title('Broadcast Complete')
                            ->body("Successfully sent: {$successCount}, Failed: {$failCount}")
                            ->success($successCount > 0)
                            ->warning($failCount > 0)
                            ->send();

                    } catch (\Exception $e) {
                        // Rollback on major error
                        DB::rollBack();

                        Notification::make()
                            ->title('Broadcast Failed')
                            ->body("Error: {$e->getMessage()}")
                            ->danger()
                            ->send();

                        // Log the error
                        Log::error('SMS broadcast operation failed', [
                            'error' => $e->getMessage(),
                            'trace' => $e->getTraceAsString()
                        ]);
                    }
                }),
        ];
    }

    /**
     * Format phone number to ensure it has the country code
     */
    protected function formatPhoneNumber(string $phoneNumber): string
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
    protected function sendMessage($message_string, $phone_number)
    {
        try {
            // Log the sending attempt
            Log::info('Sending broadcast SMS', [
                'phone' => $phone_number,
                'message' => substr($message_string, 0, 30) . '...' // Only log beginning of message for privacy
            ]);

            $url_encoded_message = urlencode($message_string);

            $sendSenderSMS = \Illuminate\Support\Facades\Http::withoutVerifying()
                ->post('https://www.cloudservicezm.com/smsservice/httpapi?username=Blessmore&password=Blessmore&msg=' . $url_encoded_message . '&shortcode=2343&sender_id=StFrancis&phone=' . $phone_number . '&api_key=121231313213123123');

            // Log the response
            Log::info('SMS API Response', [
                'status' => $sendSenderSMS->status(),
                'body' => $sendSenderSMS->body(),
                'to' => substr($phone_number, 0, 6) . '****' . substr($phone_number, -3),
            ]);

            return $sendSenderSMS->successful();
        } catch (\Exception $e) {
            Log::error('SMS sending failed', [
                'error' => $e->getMessage(),
                'phone' => $phone_number,
            ]);
            throw $e; // Re-throw to be caught by the calling method
        }
    }

    /**
     * Log the SMS to the database
     */
    protected function logSms($parent, $message, $status, $errorMessage = null)
    {
        // Create a log record in the SMS logs table
        \App\Models\SmsLog::create([
            'recipient' => $parent->phone,
            'message' => $message,
            'status' => $status,
            'message_type' => 'broadcast',
            'reference_id' => $parent->id,
            'cost' => 0.5, // Assuming standard cost per SMS
            'error_message' => $errorMessage,
            'sent_by' => auth()->id(),
        ]);
    }
}
