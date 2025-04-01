<?php

namespace App\Filament\Resources\EmployeeResource\Pages;

use App\Filament\Resources\EmployeeResource;
use App\Models\User;
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

class CreateEmployee extends CreateRecord
{
    protected static string $resource = EmployeeResource::class;

    /**
     * Handle the creation of a new employee and a corresponding user account
     */
    protected function handleRecordCreation(array $data): Model
    {
        // Wrap in a transaction to ensure both employee and user are created or neither
        return DB::transaction(function () use ($data) {
            // Generate a secure random password
            $password = Str::password(10);

            // Create a new user for this employee
            $user = User::create([
                'name' => $data['name'],
                'email' => $data['email'],
                'phone' => $data['phone'],
                'username' => $this->generateUsername($data['name']),
                'password' => Hash::make($password),
                'status' => 'active',
            ]);

            // Create the employee and link it to the user
            $employee = static::getModel()::create(array_merge(
                $data,
                ['user_id' => $user->id]
            ));

            // Send the login credentials via SMS
            try {
                $message = "Hello {$data['name']}, your account has been created. Username: {$user->username}, Password: {$password}. Please log in and change your password.";
                $formattedPhone = $this->formatPhoneNumber($data['phone']);
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
                Log::info('Employee credentials sent via SMS', [
                    'employee_id' => $employee->id,
                    'phone' => $data['phone'],
                    'username' => $user->username
                ]);

                // Show success notification with warning about potential SMS unreliability
                Notification::make()
                    ->title('User account created')
                    ->body("Login credentials sent to {$data['phone']}. Please verify with the employee that they received the SMS.")
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
                Log::error('Failed to send employee credentials via SMS', [
                    'employee_id' => $employee->id,
                    'error' => $e->getMessage()
                ]);

                // Notify the admin of the SMS failure
                Notification::make()
                    ->title('User account created')
                    ->body("Warning: Failed to send credentials to {$data['phone']}. The credentials have been stored for manual retrieval.")
                    ->warning()
                    ->send();
            }

            return $employee;
        });
    }

    /**
     * Generate a username from the employee's name
     */
    protected function generateUsername(string $name): string
    {
        // Convert the name to lowercase and remove spaces
        $baseUsername = strtolower(str_replace(' ', '.', $name));

        // Add the domain to the username
        $domainUsername = $baseUsername . '@stfrancisofassisi.tech';

        // Check if the username exists, if it does, append numbers
        $username = $domainUsername;
        $counter = 1;

        while (User::where('username', $username)->exists()) {
            $username = $baseUsername . $counter . '@stfrancisofassisi.tech';
            $counter++;
        }

        return $username;
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
