<?php

namespace App\Filament\Resources\StudentResource\Pages;

use App\Filament\Resources\StudentResource;
use App\Models\User;
use App\Models\Grade;
use App\Models\ParentGuardian;
use App\Models\UserCredential;
use App\Services\StudentFeeService;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Log;

class CreateStudent extends CreateRecord
{
    protected static string $resource = StudentResource::class;

    /**
     * Handle the creation of a new student
     * For students, we'll create a user account only for those who should have portal access
     * (e.g. older students in higher grades)
     */
    protected function handleRecordCreation(array $data): Model
    {
        // Get the grade name from the Grade model
        $gradeName = null;
        if (!empty($data['grade_id'])) {
            $grade = Grade::find($data['grade_id']);
            if ($grade) {
                $gradeName = $grade->name;
                $data['grade'] = $gradeName; // Store grade name for backward compatibility
            }
        }

        // Generate student ID if not provided
        if (empty($data['student_id_number']) && $gradeName) {
            $data['student_id_number'] = StudentResource::generateStudentId($gradeName);
        }

        // Wrap in a transaction to ensure both student and user (if created) are created or neither
        return DB::transaction(function () use ($data, $gradeName) {
            // Create the student record
            $student = static::getModel()::create($data);

            // **NEW: Automatically create student fee for current term**
            try {
                $studentFee = StudentFeeService::createFeeForNewStudent($student);

                if ($studentFee) {
                    Log::info('Student fee automatically created', [
                        'student_id' => $student->id,
                        'fee_id' => $studentFee->id,
                        'amount' => $studentFee->balance
                    ]);

                    // Show notification about fee creation
                    Notification::make()
                        ->title('Student Fee Created')
                        ->body("Fee of ZMW " . number_format($studentFee->balance, 2) . " automatically created for current term.")
                        ->success()
                        ->send();
                } else {
                    // Log warning but don't fail student creation
                    Log::warning('Could not automatically create fee for new student', [
                        'student_id' => $student->id,
                        'reason' => 'No active term or fee structure found'
                    ]);

                    Notification::make()
                        ->title('Fee Creation Notice')
                        ->body('Student created successfully, but no fee was created (no active term or fee structure found). Please create fee manually.')
                        ->warning()
                        ->send();
                }
            } catch (\Exception $e) {
                // Log error but don't fail student creation
                Log::error('Failed to create automatic fee for new student', [
                    'student_id' => $student->id,
                    'error' => $e->getMessage()
                ]);

                Notification::make()
                    ->title('Fee Creation Failed')
                    ->body('Student created successfully, but automatic fee creation failed. Please create fee manually.')
                    ->warning()
                    ->send();
            }

            // If the student is in a grade that should have portal access (e.g., grade 8-12)
            if ($gradeName && $this->shouldHavePortalAccess($gradeName)) {
                // Generate a secure random password
                $password = Str::password(10);

                // Create a new user for this student
                $user = User::create([
                    'name' => $data['name'],
                    // If student has no email, generate a school email
                    'email' => $data['email'] ?? $this->generateStudentEmail($data['name'], $data['student_id_number']),
                    'username' => $this->generateUsername($data['name'], $data['student_id_number']),
                    'password' => Hash::make($password),
                    'status' => 'active',
                ]);

                // Update the student with the user ID
                $student->user_id = $user->id;
                $student->save();

                // Get parent/guardian's phone to send the credentials to
                if (isset($data['parent_guardian_id']) && $data['parent_guardian_id']) {
                    $parentGuardian = ParentGuardian::find($data['parent_guardian_id']);

                    if ($parentGuardian && $parentGuardian->phone) {
                        // Send the student's login credentials to the parent/guardian
                        $message = "Hello {$parentGuardian->name}, your child {$data['name']}'s student portal account has been created.\n\nLogin details:\nUsername: {$user->username}\nPassword: {$password}\n\nPortal: https://staff.stfrancisofassisi.tech/\n\nPlease help them log in and change their password.";
                        $formattedPhone = $this->formatPhoneNumber($parentGuardian->phone);
                        $success = $this->sendMessage($message, $formattedPhone);

                        if ($success) {
                            // Record successful SMS sending
                            UserCredential::create([
                                'user_id' => $user->id,
                                'username' => $user->username,
                                'password' => $password, // Store temporary plain text password
                                'is_sent' => true,
                                'sent_at' => now(),
                                'delivery_method' => 'sms',
                            ]);

                            // Log the successful SMS send (without the password)
                            Log::info('Student credentials sent to parent/guardian via SMS', [
                                'student_id' => $student->id,
                                'parent_guardian_id' => $parentGuardian->id,
                                'username' => $user->username
                            ]);

                            // Show success notification
                            Notification::make()
                                ->title('Student portal account created')
                                ->body("Login credentials sent to parent/guardian at {$parentGuardian->phone}. Please verify with the parent that they received the SMS.")
                                ->success()
                                ->send();
                        } else {
                            // Log that SMS failed but don't fail the student creation
                            Log::warning('SMS sending failed but student account created', [
                                'student_id' => $student->id,
                                'parent_guardian_id' => $parentGuardian->id,
                                'username' => $user->username
                            ]);

                            // Store the credentials for manual retrieval later
                            UserCredential::create([
                                'user_id' => $user->id,
                                'username' => $user->username,
                                'password' => $password, // Store temporary plain text password
                                'is_sent' => false,
                                'delivery_method' => 'manual',
                            ]);

                            // Notify the admin of the SMS failure
                            Notification::make()
                                ->title('Student portal account created')
                                ->body("Login credentials created successfully! However, SMS delivery failed. Username: {$user->username}, Password: {$password}. Please manually share these credentials with the parent/guardian.")
                                ->warning()
                                ->persistent() // Make it stay until dismissed so credentials aren't lost
                                ->send();
                        }
                    }
                }
            } else {
                // Even if the student doesn't need a portal account, send a notification to the parent
                $this->sendParentNotification($student, $data);
            }

            return $student;
        });
    }

    /**
     * Send a notification to the parent about the new student registration
     */
    protected function sendParentNotification(Model $student, array $data): void
    {
        if (isset($data['parent_guardian_id']) && $data['parent_guardian_id']) {
            $parentGuardian = ParentGuardian::find($data['parent_guardian_id']);

            if ($parentGuardian && $parentGuardian->phone) {
                // Get parent's user account if it exists
                $parentUser = User::find($parentGuardian->user_id);
                $message = "Hello {$parentGuardian->name}, your child {$data['name']} has been registered at St Francis of Assisi School.\n\n";

                // Add login information if parent has an account
                if ($parentUser) {
                    $message .= "You can view their information on your parent portal: https://staff.stfrancisofassisi.tech/\nYour username: {$parentUser->username}";
                } else {
                    $message .= "Please contact the school office for more information.";
                }

                $formattedPhone = $this->formatPhoneNumber($parentGuardian->phone);

                // Log the formatted phone number for debugging
                Log::debug('Sending SMS to formatted phone', [
                    'original' => $parentGuardian->phone,
                    'formatted' => $formattedPhone,
                    'message_length' => strlen($message)
                ]);

                // Insert SMS log entry directly before sending
                $smsLogId = null;
                try {
                    $smsLogId = DB::table('sms_logs')->insertGetId([
                        'recipient' => $formattedPhone,
                        'message' => str_replace('@', '(at)', $message),
                        'status' => 'pending',
                        'message_type' => 'general', // Using general as it's safe for the existing enum
                        'reference_id' => $student->id, // Include student ID as reference
                        'cost' => ceil(strlen($message) / 160) * 0.50,
                        'sent_by' => auth()->id() ?? 1,
                        'created_at' => now(),
                        'updated_at' => now()
                    ]);

                    Log::info('Parent notification SMS log created', [
                        'log_id' => $smsLogId,
                        'student_id' => $student->id
                    ]);
                } catch (\Exception $logError) {
                    Log::error('Failed to create registration SMS log: ' . $logError->getMessage());
                }

                $success = $this->sendMessage($message, $formattedPhone);

                // If we created our own log, ensure it has the right status
                if ($smsLogId) {
                    try {
                        DB::table('sms_logs')
                            ->where('id', $smsLogId)
                            ->update([
                                'status' => $success ? 'sent' : 'failed'
                            ]);
                    } catch (\Exception $updateError) {
                        Log::error('Failed to update parent notification log: ' . $updateError->getMessage());
                    }
                }

                if ($success) {
                    // Log the successful SMS send
                    Log::info('Student registration notification sent to parent/guardian via SMS', [
                        'student_id' => $student->id,
                        'parent_guardian_id' => $parentGuardian->id
                    ]);

                    // Show success notification
                    Notification::make()
                        ->title('Registration notification sent')
                        ->body("Registration notification sent to parent/guardian at {$parentGuardian->phone}.")
                        ->success()
                        ->send();
                } else {
                    // SMS failed to send
                    Log::warning('Failed to send student registration notification - SMS sending failed', [
                        'student_id' => $student->id,
                        'parent_guardian_id' => $parentGuardian->id,
                        'phone' => $parentGuardian->phone
                    ]);

                    // Notify the admin of the SMS failure
                    Notification::make()
                        ->title('SMS notification failed')
                        ->body("Failed to send registration notification to {$parentGuardian->name} at {$parentGuardian->phone}. Please contact them manually.")
                        ->warning()
                        ->send();
                }
            } else {
                Log::warning('Parent/guardian has no phone number', [
                    'student_id' => $student->id,
                    'parent_guardian_id' => $data['parent_guardian_id'] ?? null
                ]);
            }
        }
    }

    /**
     * Determine if a student should have portal access based on their grade
     */
    protected function shouldHavePortalAccess(string $grade): bool
    {
        // Parse grade to handle various formats (e.g., "Grade 8", "8", "8th")
        $normalizedGrade = preg_replace('/[^0-9]/', '', $grade);

        // Convert to integer for proper comparison
        $gradeNum = (int) $normalizedGrade;

        // Secondary school students (typically grades 8-12) get portal access
        // Adjust these numbers based on your school's grade structure
        return $gradeNum >= 8 && $gradeNum <= 12;
    }

    /**
     * Generate a username from the student's name and ID
     */
    protected function generateUsername(string $name, string $studentId): string
    {
        // Use first letter of first name + last name + last 4 digits of ID
        $nameParts = explode(' ', $name);
        $firstName = $nameParts[0] ?? '';
        $lastName = end($nameParts) ?? '';

        $idSuffix = substr($studentId, -4);

        $baseUsername = strtolower(substr($firstName, 0, 1) . $lastName . $idSuffix);
        $baseUsername = preg_replace('/[^a-z0-9]/', '', $baseUsername); // Remove special characters

        // Check if the username exists, if it does, append numbers
        $username = $baseUsername;
        $counter = 1;

        while (User::where('username', $username)->exists()) {
            $username = $baseUsername . $counter;
            $counter++;
        }

        return $username;
    }

    /**
     * Generate a school email for the student
     */
    protected function generateStudentEmail(string $name, string $studentId): string
    {
        // Create an email like firstname.lastname@stfrancisofassisi.tech
        $nameParts = explode(' ', $name);
        $firstName = $nameParts[0] ?? '';
        $lastName = end($nameParts) ?? '';

        $baseEmail = strtolower($firstName . '.' . $lastName);
        $baseEmail = preg_replace('/[^a-z0-9\.]/', '', $baseEmail); // Remove special characters

        // Check if the email exists, if it does, append numbers
        $email = $baseEmail . '@stfrancisofassisi.tech';
        $counter = 1;

        while (User::where('email', $email)->exists()) {
            $email = $baseEmail . $counter . '@stfrancisofassisi.tech';
            $counter++;
        }

        return $email;
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
        // Generate a unique reference for this message to track through logs
        $messageRef = md5($phone_number . substr($message_string, 0, 30) . time());
        $smsLogId = null;

        try {
            // Log the sending attempt
            Log::info('Sending SMS notification', [
                'ref' => $messageRef,
                'phone' => $phone_number,
                'message' => substr($message_string, 0, 100) . '...', // Log more of the message for debugging
                'message_length' => strlen($message_string),
                'contains_at' => str_contains($message_string, '@') ? 'Yes' : 'No',
            ]);

            // Replace @ with (at) for SMS compatibility
            $sms_message = str_replace('@', '(at)', $message_string);

            // Calculate message cost
            $messageParts = ceil(strlen($sms_message) / 160);
            $cost = 0.50 * $messageParts;

            // Determine appropriate message type based on content
            $messageType = 'general';
            if (strpos($sms_message, 'portal account has been created') !== false) {
                $messageType = 'general'; // Would be 'student_credentials' if enum supported it
            } elseif (strpos($sms_message, 'has been registered') !== false) {
                $messageType = 'general'; // Would be 'student_registration' if enum supported it
            }

            // Look for an existing pending log entry for this message
            $existingLog = DB::table('sms_logs')
                ->where('recipient', $phone_number)
                ->where('message', $sms_message)
                ->where('status', 'pending')
                ->latest('id')
                ->first();

            if ($existingLog) {
                // Use the existing log entry
                $smsLogId = $existingLog->id;
                Log::info('Found existing SMS log', [
                    'ref' => $messageRef,
                    'log_id' => $smsLogId
                ]);
            } else {
                // Create a new log entry
                try {
                    $smsLogId = DB::table('sms_logs')->insertGetId([
                        'recipient' => $phone_number,
                        'message' => $sms_message,
                        'status' => 'pending',
                        'message_type' => $messageType,
                        'reference_id' => null,
                        'cost' => $cost,
                        'sent_by' => auth()->id() ?? 1,
                        'created_at' => now(),
                        'updated_at' => now()
                    ]);

                    Log::info('Created new SMS log', [
                        'ref' => $messageRef,
                        'log_id' => $smsLogId
                    ]);
                } catch (\Exception $logError) {
                    Log::error('Failed to create SMS log', [
                        'ref' => $messageRef,
                        'error' => $logError->getMessage()
                    ]);
                }
            }

            // Send the message
            $url_encoded_message = urlencode($sms_message);
            $url = 'https://www.cloudservicezm.com/smsservice/httpapi?username=Blessmore&password=Blessmore&msg=' . $url_encoded_message . '&shortcode=2343&sender_id=StFrancis&phone=' . $phone_number . '&api_key=121231313213123123';

            Log::debug('SMS API URL', [
                'ref' => $messageRef,
                'url' => preg_replace('/password=[^&]*/', 'password=***', $url)
            ]);

            $sendSenderSMS = Http::withoutVerifying()
                ->timeout(20)
                ->connectTimeout(10)
                ->retry(3, 2000)
                ->post($url);

            // Determine if the message was sent successfully
            $isSuccessful = $sendSenderSMS->successful() &&
                           (strtolower($sendSenderSMS->body()) === 'success' ||
                            strpos(strtolower($sendSenderSMS->body()), 'success') !== false);

            // Log the response
            Log::info('SMS API Response', [
                'ref' => $messageRef,
                'status' => $sendSenderSMS->status(),
                'body' => $sendSenderSMS->body(),
                'to' => substr($phone_number, 0, 6) . '****' . substr($phone_number, -3),
                'successful' => $isSuccessful,
                'log_id' => $smsLogId
            ]);

            // Update the SMS log status
            if ($smsLogId) {
                try {
                    $updated = DB::table('sms_logs')
                        ->where('id', $smsLogId)
                        ->update([
                            'status' => $isSuccessful ? 'sent' : 'failed',
                            'provider_reference' => $sendSenderSMS->json('message_id') ?? null,
                            'error_message' => $isSuccessful ? null : $sendSenderSMS->body(),
                            'updated_at' => now()
                        ]);

                    Log::info('Updated SMS log status', [
                        'ref' => $messageRef,
                        'log_id' => $smsLogId,
                        'status' => $isSuccessful ? 'sent' : 'failed',
                        'updated' => $updated ? 'Yes' : 'No'
                    ]);

                    // Verify the update worked
                    if (!$updated) {
                        // Try direct SQL query as a fallback
                        DB::statement("UPDATE sms_logs SET status = ?, updated_at = NOW() WHERE id = ?", [
                            $isSuccessful ? 'sent' : 'failed',
                            $smsLogId
                        ]);

                        Log::info('Updated SMS log with direct SQL', [
                            'ref' => $messageRef,
                            'log_id' => $smsLogId
                        ]);
                    }
                } catch (\Exception $updateError) {
                    Log::error('Failed to update SMS log', [
                        'ref' => $messageRef,
                        'log_id' => $smsLogId,
                        'error' => $updateError->getMessage()
                    ]);
                }
            }

            return $isSuccessful;
        } catch (\Illuminate\Http\Client\ConnectionException $e) {
            // Connection timeout or other HTTP client error
            Log::error('SMS connection error', [
                'ref' => $messageRef,
                'error' => 'Unable to connect to SMS service',
                'message' => $e->getMessage(),
                'phone' => $phone_number,
            ]);

            // Update log status
            if ($smsLogId) {
                try {
                    DB::table('sms_logs')
                        ->where('id', $smsLogId)
                        ->update([
                            'status' => 'failed',
                            'error_message' => 'Connection error: ' . $e->getMessage(),
                            'updated_at' => now()
                        ]);
                } catch (\Exception $updateError) {
                    Log::error('Failed to update SMS log after connection error', [
                        'ref' => $messageRef,
                        'log_id' => $smsLogId,
                        'error' => $updateError->getMessage()
                    ]);
                }
            }

            return false;
        } catch (\Exception $e) {
            // Other unexpected error
            Log::error('SMS sending failed with exception', [
                'ref' => $messageRef,
                'error' => $e->getMessage(),
                'phone' => $phone_number,
                'trace' => $e->getTraceAsString(),
            ]);

            // Update log status
            if ($smsLogId) {
                try {
                    DB::table('sms_logs')
                        ->where('id', $smsLogId)
                        ->update([
                            'status' => 'failed',
                            'error_message' => 'Exception: ' . $e->getMessage(),
                            'updated_at' => now()
                        ]);
                } catch (\Exception $updateError) {
                    Log::error('Failed to update SMS log after exception', [
                        'ref' => $messageRef,
                        'log_id' => $smsLogId,
                        'error' => $updateError->getMessage()
                    ]);
                }
            }

            return false;
        }
    }
}
