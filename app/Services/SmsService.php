<?php

namespace App\Services;

use App\Models\SmsLog;
use App\Models\User;
use App\Models\SmsCredit;
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
     * SMS Credit Service instance
     */
    protected ?SmsCreditService $creditService = null;

    /**
     * Get the credit service instance.
     */
    protected function getCreditService(): SmsCreditService
    {
        if ($this->creditService === null) {
            $this->creditService = new SmsCreditService();
        }
        return $this->creditService;
    }

    /**
     * Check if SMS can be sent (has sufficient credit).
     *
     * @param string $message The message to check
     * @return array Result with 'allowed', 'reason', 'balance', 'cost' keys
     */
    public function canSend(string $message): array
    {
        return $this->getCreditService()->canSend($message);
    }

    /**
     * Get current SMS credit balance.
     */
    public function getBalance(): float
    {
        return $this->getCreditService()->getBalance();
    }

    /**
     * Calculate cost for a message.
     */
    public function calculateCost(string $message): array
    {
        return $this->getCreditService()->calculateCost($message);
    }

    /**
     * Send an SMS message and log it in the database
     *
     * @param string $message The message content
     * @param string $recipient The recipient's phone number
     * @param string $messageType Type of message (general, homework_notification, etc.)
     * @param int|null $referenceId ID of related record if applicable
     * @param float|null $cost Cost of the SMS (if known)
     * @param bool $skipCreditCheck Skip credit check (for system messages)
     * @return bool Whether the message was sent successfully
     */
    public function send(
        string $message,
        string $recipient,
        string $messageType = 'general',
        ?int $referenceId = null,
        ?float $cost = null,
        bool $skipCreditCheck = false
    ): bool {
        // Format the phone number
        $formattedPhone = $this->formatPhoneNumber($recipient);

        // Get the current authenticated user or default to system
        $userId = Auth::id() ?? User::where('email', 'system@stfrancisofassisi.tech')->first()?->id;

        // Check if message contains email addresses and sanitize them
        $sanitizedMessage = $this->sanitizeMessage($message);

        // Calculate message parts for cost calculation
        $messageParts = $this->calculateMessageParts($sanitizedMessage);
        $calculatedCost = $cost ?? (0.50 * $messageParts);

        // Check credit balance before sending (unless skipped)
        if (!$skipCreditCheck) {
            $canSendResult = $this->canSend($sanitizedMessage);

            if (!$canSendResult['allowed']) {
                // Log the failed attempt due to insufficient credit
                Log::warning('SMS not sent - insufficient credit', [
                    'reason' => $canSendResult['reason'],
                    'balance' => $canSendResult['balance'] ?? 0,
                    'cost' => $canSendResult['cost'] ?? $calculatedCost,
                    'phone' => $this->maskPhoneNumber($formattedPhone),
                ]);

                // Create a failed SMS log entry
                SmsLog::create([
                    'recipient' => $formattedPhone,
                    'message' => $sanitizedMessage,
                    'status' => 'failed',
                    'message_type' => $messageType,
                    'reference_id' => $referenceId,
                    'cost' => $calculatedCost,
                    'sent_by' => $userId,
                    'error_message' => $canSendResult['reason'],
                ]);

                return false;
            }
        }

        // Create an SMS log entry with status 'pending'
        $smsLog = SmsLog::create([
            'recipient' => $formattedPhone,
            'message' => $sanitizedMessage,
            'status' => 'pending',
            'message_type' => $messageType,
            'reference_id' => $referenceId,
            'cost' => $calculatedCost,
            'sent_by' => $userId,
        ]);

        try {
            // Log the sending attempt with sanitized data for security
            Log::info('Sending SMS notification', [
                'phone' => $this->maskPhoneNumber($formattedPhone),
                'message_length' => strlen($sanitizedMessage),
                'message_parts' => $messageParts,
                'cost' => $calculatedCost,
                'log_id' => $smsLog->id
            ]);

            // Set a longer timeout for the HTTP request
            try {
                // Build the URL with query parameters (GET request like Postman)
                $apiUrl = config('services.sms.api_url', env('SMS_API_URL'));
                $queryParams = http_build_query([
                    'username' => config('services.sms.username', env('SMS_USERNAME')),
                    'password' => config('services.sms.password', env('SMS_PASSWORD')),
                    'msg' => $sanitizedMessage, // Don't double-encode, http_build_query will encode
                    'shortcode' => config('services.sms.shortcode', env('SMS_SHORTCODE')),
                    'sender_id' => config('services.sms.sender_id', env('SMS_SENDER_ID', 'StFrancis')),
                    'phone' => '+' . $formattedPhone, // Add + prefix
                    'api_key' => config('services.sms.api_key', env('SMS_API_KEY')),
                ]);

                $fullUrl = $apiUrl . '?' . $queryParams;

                // Log the request URL (with masked credentials)
                Log::debug('SMS API Request', [
                    'url' => preg_replace('/password=[^&]+/', 'password=***', $fullUrl),
                ]);

                // Send as GET request (matching working Postman request)
                $response = Http::timeout($this->timeout)
                    ->withoutVerifying()
                    ->get($fullUrl);

                // Check if successful (response body is "Success")
                $isSuccessful = $response->successful() &&
                               (strtolower(trim($response->body())) === 'success');

                // Update the SMS log with the result
                $smsLog->update([
                    'status' => $isSuccessful ? 'sent' : 'failed',
                    'provider_reference' => $response->json('message_id') ?? null,
                    'error_message' => $isSuccessful ? null : $response->body(),
                ]);

                // Deduct credit if successful and not skipped
                if ($isSuccessful && !$skipCreditCheck) {
                    try {
                        $this->getCreditService()->deductCredit(
                            $sanitizedMessage,
                            $smsLog->id,
                            $this->maskPhoneNumber($formattedPhone)
                        );
                    } catch (\Exception $e) {
                        Log::error('Failed to deduct SMS credit', [
                            'error' => $e->getMessage(),
                            'sms_log_id' => $smsLog->id,
                        ]);
                    }
                }

                // Log the response
                Log::info('SMS API Response', [
                    'log_id' => $smsLog->id,
                    'status' => $response->status(),
                    'to' => $this->maskPhoneNumber($formattedPhone),
                    'response' => $isSuccessful ? 'success' : $response->body(),
                    'successful' => $isSuccessful,
                ]);

                return $isSuccessful;
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
    public function calculateMessageParts(string $message): int
    {
        $length = strlen($message);

        // Standard GSM 03.38 character set: 160 chars per SMS for single, 153 for multipart
        // Unicode messages: 70 chars per SMS for single, 67 for multipart
        $hasUnicode = preg_match('/[^\x20-\x7E]/', $message);

        if ($hasUnicode) {
            if ($length <= 70) {
                return 1;
            }
            return (int) ceil($length / 67);
        } else {
            if ($length <= 160) {
                return 1;
            }
            return (int) ceil($length / 153);
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
     * @return array Count of successful, failed, and skipped (insufficient credit) messages
     */
    public function sendBulk(
        string $messageTemplate,
        array $recipients,
        string $messageType = 'general',
        ?int $referenceId = null
    ): array {
        $results = [
            'success' => 0,
            'failed' => 0,
            'skipped_no_credit' => 0,
            'skipped_no_phone' => 0,
            'total_cost' => 0,
        ];

        foreach ($recipients as $recipient) {
            if (empty($recipient['phone'])) {
                $results['skipped_no_phone']++;
                continue;
            }

            // Replace placeholders in the template
            $personalizedMessage = $messageTemplate;
            foreach ($recipient as $key => $value) {
                if ($key !== 'phone') {
                    $personalizedMessage = str_replace('{' . $key . '}', $value, $personalizedMessage);
                }
            }

            // Check if there's credit for this message
            $canSendResult = $this->canSend($personalizedMessage);
            if (!$canSendResult['allowed']) {
                $results['skipped_no_credit']++;
                Log::warning('Bulk SMS skipped - insufficient credit', [
                    'remaining_recipients' => count($recipients) - ($results['success'] + $results['failed'] + $results['skipped_no_credit'] + $results['skipped_no_phone']),
                    'balance' => $canSendResult['balance'] ?? 0,
                ]);
                // Stop sending if we run out of credit
                break;
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
                $results['total_cost'] += $canSendResult['cost'];
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
            'skipped_no_credit' => 0,
            'total_attempted' => 0
        ];

        // Get failed messages that are at least X minutes old
        $failedMessages = SmsLog::where('status', 'failed')
            ->where('created_at', '<', now()->subMinutes($olderThanMinutes))
            ->whereNull('error_message') // Don't retry if it was a credit issue
            ->orWhere(function ($query) use ($olderThanMinutes) {
                $query->where('status', 'failed')
                      ->where('created_at', '<', now()->subMinutes($olderThanMinutes))
                      ->where('error_message', 'not like', '%credit%');
            })
            ->limit($limit)
            ->get();

        $results['total_attempted'] = $failedMessages->count();

        foreach ($failedMessages as $message) {
            // Check credit before retrying
            $canSendResult = $this->canSend($message->message);
            if (!$canSendResult['allowed']) {
                $results['skipped_no_credit']++;
                continue;
            }

            try {
                // Format phone number again in case that was the issue
                $formattedPhone = $this->formatPhoneNumber($message->recipient);

                // Sanitize message in case that was the issue
                $sanitizedMessage = $this->sanitizeMessage($message->message);

                // Build the URL with query parameters (GET request)
                $apiUrl = config('services.sms.api_url', env('SMS_API_URL'));
                $queryParams = http_build_query([
                    'username' => config('services.sms.username', env('SMS_USERNAME')),
                    'password' => config('services.sms.password', env('SMS_PASSWORD')),
                    'msg' => $sanitizedMessage,
                    'shortcode' => config('services.sms.shortcode', env('SMS_SHORTCODE')),
                    'sender_id' => config('services.sms.sender_id', env('SMS_SENDER_ID', 'StFrancis')),
                    'phone' => '+' . $formattedPhone,
                    'api_key' => config('services.sms.api_key', env('SMS_API_KEY')),
                ]);

                $response = Http::timeout($this->timeout)
                    ->withoutVerifying()
                    ->get($apiUrl . '?' . $queryParams);

                $isSuccessful = $response->successful() &&
                               (strtolower(trim($response->body())) === 'success');

                // Update the message status
                if ($isSuccessful) {
                    $message->update([
                        'status' => 'sent',
                        'provider_reference' => $response->json('message_id') ?? null,
                        'error_message' => null,
                    ]);

                    // Deduct credit
                    try {
                        $this->getCreditService()->deductCredit(
                            $sanitizedMessage,
                            $message->id,
                            $this->maskPhoneNumber($formattedPhone)
                        );
                    } catch (\Exception $e) {
                        Log::error('Failed to deduct SMS credit on retry', [
                            'error' => $e->getMessage(),
                            'sms_log_id' => $message->id,
                        ]);
                    }

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

    /**
     * Get SMS statistics including credit info.
     */
    public function getStatistics(): array
    {
        return $this->getCreditService()->getStatistics();
    }
}
