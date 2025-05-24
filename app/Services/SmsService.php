<?php

namespace App\Services;

use App\Models\SmsLog;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SmsService
{
    /**
     * Default timeout for HTTP requests in seconds
     */
    protected $timeout = 15;

    /**
     * Send an SMS message and log it in the database
     *
     * @param string $message The message content
     * @param string $recipient The recipient's phone number
     * @param string $messageType Type of message (general, homework_notification, etc.)
     * @param int|null $referenceId ID of related record if applicable
     * @param float|null $cost Cost of the SMS (if known)
     * @return bool Whether the message was sent successfully
     */
    public function send(
        string $message,
        string $recipient,
        string $messageType = 'general',
        ?int $referenceId = null,
        ?float $cost = null
    ): bool {
        // Format the phone number
        $formattedPhone = $this->formatPhoneNumber($recipient);

        // Get the current authenticated user or default to system
        $userId = Auth::id() ?? User::where('email', 'system@stfrancisofassisi.tech')->first()?->id;

        // Create an SMS log entry with status 'pending'
        $smsLog = SmsLog::create([
            'recipient' => $formattedPhone,
            'message' => $message,
            'status' => 'pending',
            'message_type' => $messageType,
            'reference_id' => $referenceId,
            'cost' => $cost ?? 0.50, // Default cost if not provided
            'sent_by' => $userId,
        ]);

        try {
            // Check if message contains email addresses and sanitize them
            $sanitizedMessage = $this->sanitizeMessage($message);

            // Log the sending attempt with sanitized data for security
            Log::info('Sending SMS notification', [
                'phone' => $this->maskPhoneNumber($formattedPhone),
                'message_length' => strlen($sanitizedMessage),
                'log_id' => $smsLog->id
            ]);

            // Encode the message for URL
            $urlEncodedMessage = urlencode($sanitizedMessage);

            // Calculate message parts for cost calculation
            $messageParts = $this->calculateMessageParts($sanitizedMessage);

            // Update SMS log with parts info for cost calculation
            $smsLog->update([
                'cost' => $cost ?? (0.50 * $messageParts), // 0.50 per message part
            ]);

            // Set a longer timeout for the HTTP request
            try {
                // Send the SMS with increased timeout
                $response = Http::timeout($this->timeout)->withoutVerifying()
                    ->post('https://www.cloudservicezm.com/smsservice/httpapi', [
                        'username' => 'Blessmore',
                        'password' => 'Blessmore',
                        'msg' => $urlEncodedMessage,
                        'shortcode' => '2343',
                        'sender_id' => 'StFrancis',
                        'phone' => $formattedPhone,
                        'api_key' => '121231313213123123'
                    ]);

                // Update the SMS log with the result
                $smsLog->update([
                    'status' => $response->successful() ? 'sent' : 'failed',
                    'provider_reference' => $response->json('message_id') ?? null,
                    'error_message' => $response->successful() ? null : $response->body(),
                ]);

                // Log the response
                Log::info('SMS API Response', [
                    'log_id' => $smsLog->id,
                    'status' => $response->status(),
                    'to' => $this->maskPhoneNumber($formattedPhone),
                    'response' => $response->successful() ? 'success' : $response->body(),
                ]);

                return $response->successful();
            } catch (\Illuminate\Http\Client\ConnectionException $e) {
                // Handle connection timeout specifically
                Log::error('SMS connection error', [
                    'error' => 'Unable to connect to SMS service',
                    'message' => $e->getMessage(),
                    'phone' => $this->maskPhoneNumber($formattedPhone)
                ]);

                // Update the SMS log with the specific timeout error
                $smsLog->update([
                    'status' => 'failed',
                    'error_message' => "Connection timeout: " . $e->getMessage(),
                ]);

                return false;
            }
        } catch (\Exception $e) {
            // Update the SMS log with the error
            $smsLog->update([
                'status' => 'failed',
                'error_message' => $e->getMessage(),
            ]);

            // Log the error
            Log::error('SMS sending failed', [
                'log_id' => $smsLog->id,
                'error' => $e->getMessage(),
                'phone' => $this->maskPhoneNumber($formattedPhone),
            ]);

            return false;
        }
    }

    /**
     * Sanitize message content to avoid SMS gateway issues
     *
     * @param string $message The message to sanitize
     * @return string Sanitized message
     */
    protected function sanitizeMessage(string $message): string
    {
        // Check for email addresses and convert @ to (at) to prevent URL encoding issues
        $pattern = '/[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}/';
        if (preg_match($pattern, $message)) {
            // Log that message contains an email
            Log::debug('Message contains email address, sanitizing', [
                'original_length' => strlen($message)
            ]);

            // Replace @ with the word "at" instead of (at)
            $sanitized = preg_replace('/([a-zA-Z0-9._%+-]+)@([a-zA-Z0-9.-]+\.[a-zA-Z]{2,})/', '$1 at $2', $message);

            return $sanitized;
        }

        return $message;
    }

    /**
     * Calculate how many SMS parts a message will be split into
     *
     * @param string $message The message to calculate parts for
     * @return int Number of message parts
     */
    protected function calculateMessageParts(string $message): int
    {
        $length = strlen($message);

        // Standard GSM 03.38 character set: 160 chars per SMS
        // Unicode messages: 70 chars per SMS
        $hasUnicode = preg_match('/[^\x20-\x7E]/', $message);

        if ($hasUnicode) {
            return ceil($length / 70);
        } else {
            return ceil($length / 160);
        }
    }

    /**
     * Format phone number to ensure it has the country code
     */
    public function formatPhoneNumber(string $phoneNumber): string
    {
        // Log original and formatted numbers for debugging
        Log::debug('Formatting phone number', [
            'original' => $phoneNumber
        ]);

        // Remove any non-numeric characters
        $phoneNumber = preg_replace('/[^0-9]/', '', $phoneNumber);

        // Check if number already has country code (260 for Zambia)
        if (substr($phoneNumber, 0, 3) === '260') {
            // Number already has country code
            Log::debug('Phone already has country code', [
                'formatted' => $phoneNumber
            ]);
            return $phoneNumber;
        }

        // If starting with 0, replace with country code
        if (substr($phoneNumber, 0, 1) === '0') {
            $formatted = '260' . substr($phoneNumber, 1);
            Log::debug('Replaced leading 0 with country code', [
                'original' => $phoneNumber,
                'formatted' => $formatted
            ]);
            return $formatted;
        }

        // If number doesn't have country code, add it
        if (strlen($phoneNumber) === 9) {
            $formatted = '260' . $phoneNumber;
            Log::debug('Added country code to 9-digit number', [
                'original' => $phoneNumber,
                'formatted' => $formatted
            ]);
            return $formatted;
        }

        Log::debug('No formatting rules applied', [
            'formatted' => $phoneNumber
        ]);
        return $phoneNumber;
    }

    /**
     * Mask a phone number for privacy in logs
     */
    private function maskPhoneNumber(string $phoneNumber): string
    {
        if (strlen($phoneNumber) <= 6) {
            return '****' . substr($phoneNumber, -3);
        }

        return substr($phoneNumber, 0, 6) . '****' . substr($phoneNumber, -3);
    }

    /**
     * Send multiple SMS messages to different recipients
     *
     * @param string $messageTemplate The message template with placeholders
     * @param array $recipients Array of recipient data with phone numbers and replacement values
     * @param string $messageType Type of message
     * @param int|null $referenceId ID of related record
     * @return array Count of successful and failed messages
     */
    public function sendBulk(
        string $messageTemplate,
        array $recipients,
        string $messageType = 'general',
        ?int $referenceId = null
    ): array {
        $results = [
            'success' => 0,
            'failed' => 0
        ];

        foreach ($recipients as $recipient) {
            if (empty($recipient['phone'])) {
                $results['failed']++;
                continue;
            }

            // Replace placeholders in the template
            $personalizedMessage = $messageTemplate;
            foreach ($recipient as $key => $value) {
                if ($key !== 'phone') {
                    $personalizedMessage = str_replace('{' . $key . '}', $value, $personalizedMessage);
                }
            }

            // Use student_id as reference_id if available
            $refId = $recipient['student_id'] ?? $referenceId;

            // Send the message
            $sent = $this->send(
                $personalizedMessage,
                $recipient['phone'],
                $messageType,
                $refId
            );

            if ($sent) {
                $results['success']++;
            } else {
                $results['failed']++;
            }

            // Add a small delay between messages to avoid overwhelming the SMS gateway
            if (count($recipients) > 5) {
                usleep(200000); // 200ms delay
            }
        }

        return $results;
    }

    /**
     * Retry sending failed SMS messages
     *
     * @param int $limit Maximum number of messages to retry
     * @param int $olderThanMinutes Only retry messages older than this many minutes
     * @return array Count of successful and failed retries
     */
    public function retryFailedMessages(int $limit = 50, int $olderThanMinutes = 5): array
    {
        $results = [
            'success' => 0,
            'failed' => 0,
            'total_attempted' => 0
        ];

        // Get failed messages that are at least X minutes old
        $failedMessages = SmsLog::where('status', 'failed')
            ->where('created_at', '<', now()->subMinutes($olderThanMinutes))
            ->limit($limit)
            ->get();

        $results['total_attempted'] = $failedMessages->count();

        foreach ($failedMessages as $message) {
            try {
                // Format phone number again in case that was the issue
                $formattedPhone = $this->formatPhoneNumber($message->recipient);

                // Sanitize message in case that was the issue
                $sanitizedMessage = $this->sanitizeMessage($message->message);

                // Encode the message
                $urlEncodedMessage = urlencode($sanitizedMessage);

                // Send the SMS with increased timeout
                $response = Http::timeout($this->timeout)->withoutVerifying()
                    ->post('https://www.cloudservicezm.com/smsservice/httpapi', [
                        'username' => 'Blessmore',
                        'password' => 'Blessmore',
                        'msg' => $urlEncodedMessage,
                        'shortcode' => '2343',
                        'sender_id' => 'StFrancis',
                        'phone' => $formattedPhone,
                        'api_key' => '121231313213123123'
                    ]);

                // Update the message status
                if ($response->successful()) {
                    $message->update([
                        'status' => 'sent',
                        'provider_reference' => $response->json('message_id') ?? null,
                        'error_message' => null,
                    ]);

                    $results['success']++;
                } else {
                    $message->update([
                        'error_message' => $response->body() . ' (RETRY)',
                    ]);

                    $results['failed']++;
                }

                // Add a delay between messages
                usleep(200000); // 200ms

            } catch (\Exception $e) {
                $message->update([
                    'error_message' => $e->getMessage() . ' (RETRY)',
                ]);

                $results['failed']++;
            }
        }

        return $results;
    }
}
