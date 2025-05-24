<?php

namespace App\Filament\Resources\CommunicationCenterResource\Pages;

use App\Filament\Resources\CommunicationCenterResource;
use App\Models\MessageBroadcast;
use App\Models\ParentGuardian;
use App\Models\SmsLog;
use Filament\Resources\Pages\Page;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Livewire\Attributes\On;

class SendBroadcast extends Page
{
    protected static string $resource = CommunicationCenterResource::class;

    protected static string $view = 'filament.resources.communication-center-resource.pages.send-broadcast';

    public MessageBroadcast $record;

    public $recipients = [];
    public $currentBatch = 0;
    public $batchSize = 25; // Optimized for better performance
    public $totalBatches = 0;
    public $successCount = 0;
    public $failureCount = 0;
    public $isProcessing = false;
    public $processingComplete = false;
    public $progress = 0;

    public function mount($record): void
    {
        $this->record = $record;

        // Load recipients if not already processed
        if ($this->record->status === 'draft' || $this->record->status === 'sending') {
            $this->loadRecipients();
        } else {
            $this->processingComplete = true;
            $this->successCount = $this->record->sent_count;
            $this->failureCount = $this->record->failed_count;
            $this->progress = 100;
        }
    }

    protected function loadRecipients(): void
    {
        Log::info('Loading recipients for broadcast', ['broadcast_id' => $this->record->id]);

        // Get filters from the broadcast record
        $filters = $this->record->filters ?? [];
        $recipientType = $filters['recipient_type'] ?? 'parents';

        if ($recipientType === 'parents') {
            // Start with all parents that have a phone number
            $query = ParentGuardian::whereNotNull('phone')->whereHas('students');

            // Apply grade filter if selected
            if (!empty($filters['grade_id'])) {
                Log::info('Applying grade filter', ['grade_id' => $filters['grade_id']]);
                $query->whereHas('students', function ($q) use ($filters) {
                    $q->whereHas('classSection', function ($csq) use ($filters) {
                        $csq->where('grade_id', $filters['grade_id']);
                    });
                });
            }

            // Apply fee status filter if not "all"
            if (!empty($filters['fee_status']) && $filters['fee_status'] !== 'all') {
                Log::info('Applying fee status filter', ['fee_status' => $filters['fee_status']]);
                $query->whereHas('students', function ($studentQuery) use ($filters) {
                    $studentQuery->whereHas('fees', function ($feeQuery) use ($filters) {
                        if ($filters['fee_status'] === 'paid') {
                            $feeQuery->where('payment_status', 'paid');
                        } elseif ($filters['fee_status'] === 'partial') {
                            $feeQuery->where('payment_status', 'partial');
                        } elseif ($filters['fee_status'] === 'unpaid') {
                            $feeQuery->where('payment_status', 'unpaid');
                        }
                    });
                });
            }

            // Get parents with their students
            $parents = $query->with(['students.classSection.grade'])->get();
            Log::info('Parents loaded', ['count' => $parents->count()]);

            // Format the recipients for display - ONE MESSAGE PER CHILD
            $formattedRecipients = [];

            foreach ($parents as $parent) {
                // Get all students for this parent
                $allStudents = $parent->students()->with('classSection.grade')->get();

                // Filter students based on criteria
                $qualifyingStudents = $allStudents;

                // Apply grade filter to students if specified
                if (!empty($filters['grade_id'])) {
                    $qualifyingStudents = $qualifyingStudents->filter(function ($student) use ($filters) {
                        return $student->classSection && $student->classSection->grade_id == $filters['grade_id'];
                    });
                }

                // Create one message entry per qualifying student
                foreach ($qualifyingStudents as $student) {
                    $gradeName = $student->classSection?->grade?->name ?? 'Unknown Grade';
                    $classSectionName = $student->classSection?->name ?? 'Unknown Class';

                    $formattedRecipients[] = [
                        'id' => $parent->id,
                        'type' => 'parent',
                        'name' => $parent->name,
                        'phone' => $parent->phone,
                        'student_name' => $student->name,
                        'grade' => $gradeName,
                        'class_section' => $classSectionName,
                        'student_id' => $student->id,
                    ];
                }
            }

            $this->recipients = $formattedRecipients;
            Log::info('Recipients formatted', ['total_messages' => count($formattedRecipients)]);
        } else {
            // Handle staff and teachers (simplified placeholder)
            $this->recipients = [];

            Notification::make()
                ->title('Feature Not Available')
                ->body('Staff and teacher filtering is not implemented in this basic version.')
                ->warning()
                ->send();
        }

        // Calculate total batches
        $this->totalBatches = ceil(count($this->recipients) / $this->batchSize);

        // Update total recipients in database if count changed
        if ($this->record->total_recipients !== count($this->recipients)) {
            $this->record->update([
                'total_recipients' => count($this->recipients)
            ]);
            Log::info('Updated total recipients in database', ['new_count' => count($this->recipients)]);
        }
    }

    public function startProcessing(): void
    {
        if ($this->isProcessing) {
            return;
        }

        Log::info('Starting broadcast processing', [
            'broadcast_id' => $this->record->id,
            'total_recipients' => count($this->recipients),
            'total_batches' => $this->totalBatches
        ]);

        $this->isProcessing = true;

        // Update record status
        if ($this->record->status === 'draft') {
            $this->record->update([
                'status' => 'sending',
                'started_at' => now(),
            ]);
        }

        // Process the first batch
        $this->processBatch();
    }

    #[On('process-next-batch')]
    public function processNextBatch(): void
    {
        $this->currentBatch++;

        if ($this->currentBatch < $this->totalBatches) {
            $this->processBatch();
        } else {
            $this->completeProcessing();
        }
    }

    protected function processBatch(): void
    {
        $startIndex = $this->currentBatch * $this->batchSize;
        $endIndex = min($startIndex + $this->batchSize, count($this->recipients));

        $batchRecipients = array_slice($this->recipients, $startIndex, $this->batchSize);

        Log::info('Processing batch', [
            'batch_number' => $this->currentBatch + 1,
            'batch_size' => count($batchRecipients),
            'start_index' => $startIndex,
            'end_index' => $endIndex
        ]);

        $batchSuccess = 0;
        $batchFailure = 0;
        $totalCost = 0;

        foreach ($batchRecipients as $recipient) {
            // Personalize the message for each specific child
            $message = $this->record->message;
            $message = str_replace('{parent_name}', $recipient['name'], $message);
            $message = str_replace('{student_name}', $recipient['student_name'], $message);
            $message = str_replace('{grade}', $recipient['grade'], $message);

            // Calculate cost for this message
            $messageParts = ceil(strlen($message) / 160);
            $messageCost = 0.50 * $messageParts;

            // Format phone number
            $formattedPhone = $this->formatPhoneNumber($recipient['phone']);

            // Create SMS log entry with student reference
            try {
                $smsLog = DB::table('sms_logs')->insertGetId([
                    'recipient' => $formattedPhone,
                    'message' => $message,
                    'status' => 'pending',
                    'message_type' => 'general',
                    'reference_id' => $recipient['student_id'], // Reference the specific student
                    'cost' => $messageCost,
                    'sent_by' => Auth::id(),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                Log::info('SMS log created', [
                    'sms_log_id' => $smsLog,
                    'recipient' => $formattedPhone,
                    'student_name' => $recipient['student_name']
                ]);

            } catch (\Exception $e) {
                Log::error('Failed to create SMS log', [
                    'error' => $e->getMessage(),
                    'recipient' => $recipient
                ]);
                $batchFailure++;
                continue;
            }

            // Send SMS using your existing mechanism
            try {
                // Replace @ with (at) for SMS compatibility
                $sms_message = str_replace('@', '(at)', $message);
                $url_encoded_message = urlencode($sms_message);

                // Send the SMS with optimized settings
                $sendSenderSMS = Http::withoutVerifying()
                    ->timeout(15)
                    ->connectTimeout(10)
                    ->retry(2, 1000) // Retry failed requests twice
                    ->post('https://www.cloudservicezm.com/smsservice/httpapi?username=Blessmore&password=Blessmore&msg=' . $url_encoded_message . '&shortcode=2343&sender_id=StFrancis&phone=' . $formattedPhone . '&api_key=121231313213123123');

                $isSuccessful = $sendSenderSMS->successful() &&
                              (strtolower($sendSenderSMS->body()) === 'success' ||
                               strpos(strtolower($sendSenderSMS->body()), 'success') !== false);

                // Update SMS log with result
                DB::table('sms_logs')->where('id', $smsLog)->update([
                    'status' => $isSuccessful ? 'sent' : 'failed',
                    'provider_reference' => $sendSenderSMS->json('message_id') ?? null,
                    'error_message' => $isSuccessful ? null : $sendSenderSMS->body(),
                    'updated_at' => now(),
                ]);

                if ($isSuccessful) {
                    $batchSuccess++;
                    $totalCost += $messageCost;
                    Log::info('SMS sent successfully', [
                        'recipient' => $formattedPhone,
                        'student' => $recipient['student_name']
                    ]);
                } else {
                    $batchFailure++;
                    Log::warning('SMS failed to send', [
                        'recipient' => $formattedPhone,
                        'response' => $sendSenderSMS->body()
                    ]);
                }

                // Optimized delay for faster processing
                usleep(100000); // 100ms delay

            } catch (\Exception $e) {
                // Update SMS log with error
                DB::table('sms_logs')->where('id', $smsLog)->update([
                    'status' => 'failed',
                    'error_message' => $e->getMessage(),
                    'updated_at' => now(),
                ]);

                $batchFailure++;
                Log::error('SMS sending exception', [
                    'error' => $e->getMessage(),
                    'recipient' => $formattedPhone,
                    'student' => $recipient['student_name']
                ]);
            }
        }

        // Update success and failure counts
        $this->successCount += $batchSuccess;
        $this->failureCount += $batchFailure;

        Log::info('Batch completed', [
            'batch_number' => $this->currentBatch + 1,
            'batch_success' => $batchSuccess,
            'batch_failure' => $batchFailure,
            'total_success' => $this->successCount,
            'total_failure' => $this->failureCount
        ]);

        // Update record in database
        $this->record->update([
            'sent_count' => $this->successCount,
            'failed_count' => $this->failureCount,
            'total_cost' => DB::raw("total_cost + {$totalCost}"),
        ]);

        // Calculate progress
        $this->progress = min(99, round(($this->currentBatch + 1) / $this->totalBatches * 100));

        // Trigger next batch processing
        $this->dispatch('process-next-batch');
    }

    protected function completeProcessing(): void
    {
        $this->isProcessing = false;
        $this->processingComplete = true;
        $this->progress = 100;

        // Update record status
        $this->record->update([
            'status' => 'completed',
            'completed_at' => now(),
        ]);

        Log::info('Broadcast completed', [
            'broadcast_id' => $this->record->id,
            'total_success' => $this->successCount,
            'total_failure' => $this->failureCount,
            'total_cost' => $this->record->total_cost
        ]);

        Notification::make()
            ->title('Broadcast Complete')
            ->body("Successfully sent: {$this->successCount}, Failed: {$this->failureCount}")
            ->success()
            ->send();
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
}

// namespace App\Filament\Resources\CommunicationCenterResource\Pages;

// use App\Filament\Resources\CommunicationCenterResource;
// use App\Models\MessageBroadcast;
// use App\Models\ParentGuardian;
// use App\Models\SmsLog;
// use Filament\Resources\Pages\Page;
// use Filament\Notifications\Notification;
// use Illuminate\Support\Facades\Auth;
// use Illuminate\Support\Facades\DB;
// use Illuminate\Support\Facades\Http;
// use Livewire\Attributes\On;

// class SendBroadcast extends Page
// {
//     protected static string $resource = CommunicationCenterResource::class;

//     protected static string $view = 'filament.resources.communication-center-resource.pages.send-broadcast';

//     public MessageBroadcast $record;

//     public $recipients = [];
//     public $currentBatch = 0;
//     public $batchSize = 10;
//     public $totalBatches = 0;
//     public $successCount = 0;
//     public $failureCount = 0;
//     public $isProcessing = false;
//     public $processingComplete = false;
//     public $progress = 0;

//     public function mount($record): void
//     {
//         $this->record = $record;

//         // Load recipients if not already processed
//         if ($this->record->status === 'draft' || $this->record->status === 'sending') {
//             $this->loadRecipients();
//         } else {
//             $this->processingComplete = true;
//             $this->successCount = $this->record->sent_count;
//             $this->failureCount = $this->record->failed_count;
//             $this->progress = 100;
//         }
//     }

//     protected function loadRecipients(): void
//     {
//         // Get filters from the broadcast record
//         $filters = $this->record->filters ?? [];
//         $recipientType = $filters['recipient_type'] ?? 'parents';

//         if ($recipientType === 'parents') {
//             // Start with all parents that have a phone number
//             $query = ParentGuardian::whereNotNull('phone');

//             // Apply grade filter if selected - Updated for your database structure
//             if (!empty($filters['grade_id'])) {
//                 $query->whereHas('students', function ($q) use ($filters) {
//                     $q->whereHas('classSection', function ($csq) use ($filters) {
//                         $csq->where('grade_id', $filters['grade_id']);
//                     });
//                 });
//             }

//             // Apply fee status filter if not "all" - Updated for your structure
//             if (!empty($filters['fee_status']) && $filters['fee_status'] !== 'all') {
//                 $query->whereHas('students', function ($studentQuery) use ($filters) {
//                     $studentQuery->whereHas('fees', function ($feeQuery) use ($filters) {
//                         if ($filters['fee_status'] === 'paid') {
//                             $feeQuery->where('payment_status', 'paid');
//                         } elseif ($filters['fee_status'] === 'partial') {
//                             $feeQuery->where('payment_status', 'partial');
//                         } elseif ($filters['fee_status'] === 'unpaid') {
//                             $feeQuery->where('payment_status', 'unpaid');
//                         }
//                     });
//                 });
//             }

//             // Get the filtered parents with proper relationships
//             $recipients = $query->with(['students.classSection.grade'])->get();

//             // Format the recipients for display
//             $formattedRecipients = [];

//             foreach ($recipients as $parent) {
//                 // Get the first student or the student in the selected grade
//                 $student = null;
//                 if (!empty($filters['grade_id'])) {
//                     // Find student in the specific grade
//                     $student = $parent->students()
//                         ->whereHas('classSection', function ($q) use ($filters) {
//                             $q->where('grade_id', $filters['grade_id']);
//                         })
//                         ->with('classSection.grade')
//                         ->first();
//                 } else {
//                     // Get first student with grade info
//                     $student = $parent->students()->with('classSection.grade')->first();
//                 }

//                 // Get grade name through class section
//                 $gradeName = '';
//                 if ($student && $student->classSection && $student->classSection->grade) {
//                     $gradeName = $student->classSection->grade->name;
//                 }

//                 $formattedRecipients[] = [
//                     'id' => $parent->id,
//                     'type' => 'parent',
//                     'name' => $parent->name,
//                     'phone' => $parent->phone,
//                     'student_name' => $student ? $student->name : '',
//                     'grade' => $gradeName,
//                 ];
//             }

//             $this->recipients = $formattedRecipients;
//         } else {
//             // Handle staff and teachers (simplified placeholder)
//             $this->recipients = [];

//             Notification::make()
//                 ->title('Feature Not Available')
//                 ->body('Staff and teacher filtering is not implemented in this basic version.')
//                 ->warning()
//                 ->send();
//         }

//         // Calculate total batches
//         $this->totalBatches = ceil(count($this->recipients) / $this->batchSize);

//         // Update total recipients in database if count changed
//         if ($this->record->total_recipients !== count($this->recipients)) {
//             $this->record->update([
//                 'total_recipients' => count($this->recipients)
//             ]);
//         }
//     }

//     public function startProcessing(): void
//     {
//         if ($this->isProcessing) {
//             return;
//         }

//         $this->isProcessing = true;

//         // Update record status
//         if ($this->record->status === 'draft') {
//             $this->record->update([
//                 'status' => 'sending',
//                 'started_at' => now(),
//             ]);
//         }

//         // Process the first batch
//         $this->processBatch();
//     }

//     #[On('process-next-batch')]
//     public function processNextBatch(): void
//     {
//         $this->currentBatch++;

//         if ($this->currentBatch < $this->totalBatches) {
//             $this->processBatch();
//         } else {
//             $this->completeProcessing();
//         }
//     }

//     protected function processBatch(): void
//     {
//         $startIndex = $this->currentBatch * $this->batchSize;
//         $endIndex = min($startIndex + $this->batchSize, count($this->recipients));

//         $batchRecipients = array_slice($this->recipients, $startIndex, $this->batchSize);

//         $batchSuccess = 0;
//         $batchFailure = 0;
//         $totalCost = 0;

//         foreach ($batchRecipients as $recipient) {
//             // Personalize the message
//             $message = $this->record->message;
//             $message = str_replace('{parent_name}', $recipient['name'], $message);
//             $message = str_replace('{student_name}', $recipient['student_name'], $message);
//             $message = str_replace('{grade}', $recipient['grade'], $message);

//             // Calculate cost for this message
//             $messageParts = ceil(strlen($message) / 160);
//             $messageCost = 0.50 * $messageParts;

//             // Format phone number
//             $formattedPhone = $this->formatPhoneNumber($recipient['phone']);

//             // Create SMS log entry
//             $smsLog = SmsLog::create([
//                 'recipient' => $formattedPhone,
//                 'message' => $message,
//                 'status' => 'pending',
//                 'message_type' => 'general',
//                 'reference_id' => $recipient['id'],
//                 'cost' => $messageCost,
//                 'sent_by' => Auth::id(),
//             ]);

//             // Send SMS using your existing mechanism
//             try {
//                 // Replace @ with (at) for SMS compatibility
//                 $sms_message = str_replace('@', '(at)', $message);
//                 $url_encoded_message = urlencode($sms_message);

//                 // Send the SMS
//                 $sendSenderSMS = Http::withoutVerifying()
//                     ->timeout(10)
//                     ->connectTimeout(5)
//                     ->post('https://www.cloudservicezm.com/smsservice/httpapi?username=Blessmore&password=Blessmore&msg=' . $url_encoded_message . '&shortcode=2343&sender_id=StFrancis&phone=' . $formattedPhone . '&api_key=121231313213123123');

//                 $isSuccessful = $sendSenderSMS->successful() &&
//                               (strtolower($sendSenderSMS->body()) === 'success' ||
//                                strpos(strtolower($sendSenderSMS->body()), 'success') !== false);

//                 // Update SMS log with result
//                 $smsLog->update([
//                     'status' => $isSuccessful ? 'sent' : 'failed',
//                     'provider_reference' => $sendSenderSMS->json('message_id') ?? null,
//                     'error_message' => $isSuccessful ? null : $sendSenderSMS->body(),
//                 ]);

//                 if ($isSuccessful) {
//                     $batchSuccess++;
//                     $totalCost += $messageCost;
//                 } else {
//                     $batchFailure++;
//                 }

//                 // Add a small delay to avoid overwhelming the SMS gateway
//                 usleep(200000); // 200ms delay

//             } catch (\Exception $e) {
//                 // Update SMS log with error
//                 $smsLog->update([
//                     'status' => 'failed',
//                     'error_message' => $e->getMessage(),
//                 ]);

//                 $batchFailure++;
//             }
//         }

//         // Update success and failure counts
//         $this->successCount += $batchSuccess;
//         $this->failureCount += $batchFailure;

//         // Update record in database
//         $this->record->update([
//             'sent_count' => $this->successCount,
//             'failed_count' => $this->failureCount,
//             'total_cost' => DB::raw("total_cost + {$totalCost}"),
//         ]);

//         // Calculate progress
//         $this->progress = min(99, round(($this->currentBatch + 1) / $this->totalBatches * 100));

//         // Trigger next batch processing
//         $this->dispatch('process-next-batch');
//     }

//     protected function completeProcessing(): void
//     {
//         $this->isProcessing = false;
//         $this->processingComplete = true;
//         $this->progress = 100;

//         // Update record status
//         $this->record->update([
//             'status' => 'completed',
//             'completed_at' => now(),
//         ]);

//         Notification::make()
//             ->title('Broadcast Complete')
//             ->body("Successfully sent: {$this->successCount}, Failed: {$this->failureCount}")
//             ->success()
//             ->send();
//     }

//     /**
//      * Format phone number to ensure it has the country code
//      */
//     protected function formatPhoneNumber(string $phoneNumber): string
//     {
//         // Remove any non-numeric characters
//         $phoneNumber = preg_replace('/[^0-9]/', '', $phoneNumber);

//         // Check if number already has country code (260 for Zambia)
//         if (substr($phoneNumber, 0, 3) === '260') {
//             // Number already has country code
//             return $phoneNumber;
//         }

//         // If starting with 0, replace with country code
//         if (substr($phoneNumber, 0, 1) === '0') {
//             return '260' . substr($phoneNumber, 1);
//         }

//         // If number doesn't have country code, add it
//         if (strlen($phoneNumber) === 9) {
//             return '260' . $phoneNumber;
//         }

//         return $phoneNumber;
//     }
// }


