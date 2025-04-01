<?php

namespace App\Filament\Resources\StudentResource\Pages;

use App\Filament\Resources\StudentResource;
use App\Models\User;
use App\Models\ParentGuardian;
use App\Models\UserCredential;
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
        // Wrap in a transaction to ensure both student and user (if created) are created or neither
        return DB::transaction(function () use ($data) {
            // Create the student record
            $student = static::getModel()::create($data);

            // If the student is in a grade that should have portal access (e.g., grade 8-12)
            if ($this->shouldHavePortalAccess($data['grade'])) {
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

                // Get parent/guardian's phone to send the credentials to
                if (isset($data['parent_guardian_id']) && $data['parent_guardian_id']) {
                    $parentGuardian = ParentGuardian::find($data['parent_guardian_id']);

                    if ($parentGuardian && $parentGuardian->phone) {
                        // Send the student's login credentials to the parent/guardian
                        try {
                            $message = "Hello {$parentGuardian->name}, your child {$data['name']}'s student portal account has been created. Username: {$user->username}, Password: {$password}. Please help them log in and change their password.";
                            $formattedPhone = $this->formatPhoneNumber($parentGuardian->phone);
                            $this->sendMessage($message, $formattedPhone);

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

                        } catch (\Exception $e) {
                            // Store the credentials for manual retrieval later
                            UserCredential::create([
                                'user_id' => $user->id,
                                'username' => $user->username,
                                'password' => $password, // Store temporary plain text password
                                'is_sent' => false,
                                'delivery_method' => 'manual',
                            ]);

                            // Log the error but don't fail the transaction
                            Log::error('Failed to send student credentials via SMS', [
                                'student_id' => $student->id,
                                'parent_guardian_id' => $parentGuardian->id,
                                'error' => $e->getMessage()
                            ]);

                            // Notify the admin of the SMS failure
                            Notification::make()
                                ->title('Student portal account created')
                                ->body("Warning: Failed to send credentials to parent/guardian at {$parentGuardian->phone}. The credentials have been stored for manual retrieval.")
                                ->warning()
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
                try {
                    // Get parent's user account if it exists
                    $parentUser = User::find($parentGuardian->user_id);
                    $message = "Hello {$parentGuardian->name}, your child {$data['name']} has been registered at St Francis of Assisi School. ";

                    // Add login information if parent has an account
                    if ($parentUser) {
                        $message .= "You can view their information using your parent portal account (Username: {$parentUser->username}).";
                    } else {
                        $message .= "Please contact the school office for more information.";
                    }

                    $formattedPhone = $this->formatPhoneNumber($parentGuardian->phone);
                    $this->sendMessage($message, $formattedPhone);

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

                } catch (\Exception $e) {
                    // Log the error
                    Log::error('Failed to send student registration notification via SMS', [
                        'student_id' => $student->id,
                        'parent_guardian_id' => $parentGuardian->id,
                        'error' => $e->getMessage()
                    ]);

                    // Notify the admin of the SMS failure
                    Notification::make()
                        ->title('SMS notification failed')
                        ->body("Warning: Failed to send registration notification to parent/guardian at {$parentGuardian->phone}.")
                        ->warning()
                        ->send();
                }
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
        try {
            // Log the sending attempt
            Log::info('Sending SMS notification', [
                'phone' => $phone_number,
                'message' => substr($message_string, 0, 30) . '...' // Only log beginning of message for privacy
            ]);

            $url_encoded_message = urlencode($message_string);

            $sendSenderSMS = Http::withoutVerifying()
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
}
