<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Exception;

class SmsService
{
    /**
     * Send an SMS message
     *
     * @param string $message The message content
     * @param string $phoneNumber The recipient phone number
     * @return bool Whether the SMS was sent successfully
     * @throws Exception If the SMS service returns an error
     */
    public static function send(string $message, string $phoneNumber): bool
    {
        try {
            // Format the phone number (remove leading 0, ensure country code, etc.)
            $formattedPhone = self::formatPhoneNumber($phoneNumber);

            // Get SMS configuration from .env
            $username = config('services.sms.username', env('SMS_USERNAME'));
            $password = config('services.sms.password', env('SMS_PASSWORD'));
            $senderId = config('services.sms.sender_id', env('SMS_SENDER_ID'));
            $shortcode = config('services.sms.shortcode', env('SMS_SHORTCODE'));
            $apiKey = config('services.sms.api_key', env('SMS_API_KEY'));
            $apiUrl = config('services.sms.api_url', env('SMS_API_URL'));

            // Log the request attempt (masking sensitive data)
            Log::info('Sending SMS notification', [
                'phone' => $phoneNumber,
                'message' => substr($message, 0, 30) . '...' // Only log beginning of message for privacy
            ]);

            // Prepare the request data
            $params = [
                'username' => $username,
                'password' => $password,
                'sender' => $senderId,
                'shortcode' => $shortcode,
                'apikey' => $apiKey,
                'message' => $message,
                'recipient' => $formattedPhone,
            ];

            // Try POST request first with increased timeout
            $response = Http::timeout(30)->post($apiUrl, $params);

            // If POST fails, try GET as fallback with params in URL
            if (!$response->successful()) {
                Log::info('POST request failed, trying GET request as fallback');
                $response = Http::timeout(30)->get($apiUrl, $params);
            }

            // Log the response for debugging (excluding sensitive information)
            Log::info('SMS API Response', [
                'status' => $response->status(),
                'body' => $response->body(),
                'to' => substr($formattedPhone, 0, 6) . '****' . substr($formattedPhone, -3),
            ]);

            // Check if the SMS was sent successfully
            if ($response->successful()) {
                return true;
            } else {
                throw new Exception('SMS service responded with error: ' . $response->body());
            }
        } catch (Exception $e) {
            Log::error('SMS sending failed', [
                'error' => $e->getMessage(),
                'phone' => $formattedPhone ?? substr($phoneNumber, 0, 6) . '****' . substr($phoneNumber, -3),
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }

    /**
     * Format the phone number for the SMS service
     */
    private static function formatPhoneNumber(string $phoneNumber): string
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
