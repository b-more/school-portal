<?php

namespace App\Services;

use Exception;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * CGrate Mobile Money Payment Service
 * Integrates with CGrate SOAP API for mobile money transactions
 */
class CGrateService
{
    protected string $soapUrl;

    protected string $username;

    protected string $password;

    protected int $timeout;

    protected int $retryAttempts;

    protected bool $mockMode;

    public function __construct()
    {
        $this->soapUrl = env('CGRATE_SOAP_URL', 'https://543.cgrate.co.zm/Konik/KonikWs');
        $this->username = env('CGRATE_USERNAME', '1751463093895');
        $this->password = env('CGRATE_PASSWORD', 'D6cQ21d0');
        $this->timeout = (int) env('CGRATE_TIMEOUT', 30);
        $this->retryAttempts = (int) env('CGRATE_RETRY_ATTEMPTS', 3);
        $this->mockMode = env('CGRATE_MOCK_MODE', false);

        Log::info('CGrate service initialized', [
            'mode' => $this->mockMode ? 'mock' : 'live',
            'url' => $this->soapUrl,
        ]);
    }

    /**
     * Get service status
     */
    public function getServiceStatus(): array
    {
        return [
            'available' => true,
            'mode' => $this->mockMode ? 'mock' : 'live',
            'url' => $this->soapUrl,
        ];
    }

    /**
     * Process customer payment request
     *
     * @param  float  $amount  Payment amount
     * @param  string  $customerMobile  Customer mobile number
     * @param  string  $paymentReference  Unique payment reference
     * @return array Payment result
     */
    public function processCustomerPayment(float $amount, string $customerMobile, string $paymentReference): array
    {
        Log::info('Processing CGrate payment', [
            'amount' => $amount,
            'mobile' => $this->maskPhone($customerMobile),
            'reference' => $paymentReference,
        ]);

        if ($this->mockMode) {
            return $this->mockProcessPayment($amount, $customerMobile, $paymentReference);
        }

        try {
            $soapRequest = $this->buildPaymentRequest($amount, $customerMobile, $paymentReference);
            $response = $this->sendSoapRequest($soapRequest);
            $result = $this->parsePaymentResponse($response);

            Log::info('CGrate payment result', $result);

            return $result;

        } catch (Exception $e) {
            Log::error('CGrate payment error', [
                'error' => $e->getMessage(),
                'reference' => $paymentReference,
            ]);

            // Format user-friendly error messages
            $errorMessage = $this->formatErrorMessage($e->getMessage());

            return [
                'success' => false,
                'message' => $errorMessage,
                'error_code' => $this->getErrorCode($e->getMessage()),
            ];
        }
    }

    /**
     * Query customer payment status
     *
     * @param  string  $paymentReference  Payment reference to query
     * @return array Payment status
     */
    public function queryCustomerPayment(string $paymentReference): array
    {
        Log::info('Querying CGrate payment status', ['reference' => $paymentReference]);

        if ($this->mockMode) {
            return $this->mockQueryStatus($paymentReference);
        }

        try {
            $soapRequest = $this->buildStatusQuery($paymentReference);
            $response = $this->sendSoapRequest($soapRequest);
            $result = $this->parseStatusResponse($response);

            Log::info('CGrate status result', $result);

            return $result;

        } catch (Exception $e) {
            Log::error('CGrate status query error', [
                'error' => $e->getMessage(),
                'reference' => $paymentReference,
            ]);

            // Format user-friendly error messages
            $errorMessage = $this->formatErrorMessage($e->getMessage());

            return [
                'payment_complete' => false,
                'payment_status' => 'error',
                'message' => $errorMessage,
                'error_code' => $this->getErrorCode($e->getMessage()),
            ];
        }
    }

    /**
     * Get account balance
     *
     * @return array Balance information
     */
    public function getAccountBalance(): array
    {
        Log::info('Checking CGrate account balance');

        if ($this->mockMode) {
            return [
                'success' => true,
                'balance' => 10000.0,
                'currency' => 'ZMW',
            ];
        }

        try {
            $soapRequest = $this->buildBalanceRequest();
            $response = $this->sendSoapRequest($soapRequest);
            $result = $this->parseBalanceResponse($response);

            Log::info('CGrate balance result', $result);

            return $result;

        } catch (Exception $e) {
            Log::error('CGrate balance check error', ['error' => $e->getMessage()]);

            return [
                'success' => false,
                'message' => $e->getMessage(),
            ];
        }
    }

    /**
     * Build SOAP payment request
     */
    protected function buildPaymentRequest(float $amount, string $customerMobile, string $paymentReference): string
    {
        // Format mobile number (remove +260 prefix if present)
        $mobile = $this->formatPhoneNumber($customerMobile);

        // Format amount as integer (CGrate expects whole numbers)
        $formattedAmount = (string) (int) round($amount);

        Log::debug('Formatted payment request', [
            'amount' => $formattedAmount,
            'mobile' => $mobile,
            'reference' => $paymentReference,
        ]);

        return <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" xmlns:xsd="http://www.w3.org/2001/XMLSchema" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:kon="http://konik.cgrate.com">
    <soapenv:Header>
        <wsse:Security xmlns:wsse="http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-secext-1.0.xsd" soapenv:mustUnderstand="1">
            <wsse:UsernameToken xmlns:wsu="http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-utility-1.0.xsd" wsu:Id="{$this->username}">
                <wsse:Username>{$this->username}</wsse:Username>
                <wsse:Password Type="http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-username-token-profile-1.0#PasswordText">{$this->password}</wsse:Password>
            </wsse:UsernameToken>
        </wsse:Security>
    </soapenv:Header>
    <soapenv:Body>
        <kon:processCustomerPayment>
            <transactionAmount>{$formattedAmount}</transactionAmount>
            <customerMobile>{$mobile}</customerMobile>
            <paymentReference>{$paymentReference}</paymentReference>
        </kon:processCustomerPayment>
    </soapenv:Body>
</soapenv:Envelope>
XML;
    }

    /**
     * Build SOAP status query request
     */
    protected function buildStatusQuery(string $paymentReference): string
    {
        return <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" xmlns:xsd="http://www.w3.org/2001/XMLSchema" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:kon="http://konik.cgrate.com">
    <soapenv:Header>
        <wsse:Security xmlns:wsse="http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-secext-1.0.xsd" soapenv:mustUnderstand="1">
            <wsse:UsernameToken xmlns:wsu="http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-utility-1.0.xsd" wsu:Id="{$this->username}">
                <wsse:Username>{$this->username}</wsse:Username>
                <wsse:Password Type="http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-username-token-profile-1.0#PasswordText">{$this->password}</wsse:Password>
            </wsse:UsernameToken>
        </wsse:Security>
    </soapenv:Header>
    <soapenv:Body>
        <kon:queryCustomerPayment>
            <paymentReference>{$paymentReference}</paymentReference>
        </kon:queryCustomerPayment>
    </soapenv:Body>
</soapenv:Envelope>
XML;
    }

    /**
     * Build SOAP balance request
     */
    protected function buildBalanceRequest(): string
    {
        return <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" xmlns:xsd="http://www.w3.org/2001/XMLSchema" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:kon="http://konik.cgrate.com">
    <soapenv:Header>
        <wsse:Security xmlns:wsse="http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-secext-1.0.xsd" soapenv:mustUnderstand="1">
            <wsse:UsernameToken xmlns:wsu="http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-utility-1.0.xsd" wsu:Id="{$this->username}">
                <wsse:Username>{$this->username}</wsse:Username>
                <wsse:Password Type="http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-username-token-profile-1.0#PasswordText">{$this->password}</wsse:Password>
            </wsse:UsernameToken>
        </wsse:Security>
    </soapenv:Header>
    <soapenv:Body>
        <kon:getAccountBalance/>
    </soapenv:Body>
</soapenv:Envelope>
XML;
    }

    /**
     * Send SOAP request to CGrate
     */
    protected function sendSoapRequest(string $soapBody): string
    {
        $headers = [
            'Content-Type' => 'application/soap+xml',
            'SOAPAction' => '""',
        ];

        Log::debug('Sending SOAP request to CGrate', ['url' => $this->soapUrl]);

        $response = Http::withHeaders($headers)
            ->timeout($this->timeout)
            ->withoutVerifying()
            ->send('POST', $this->soapUrl, [
                'body' => $soapBody,
            ]);

        Log::debug('CGrate response received', ['status' => $response->status()]);

        if (! $response->successful()) {
            throw new Exception("CGrate API returned status {$response->status()}");
        }

        return $response->body();
    }

    /**
     * Parse payment response
     */
    protected function parsePaymentResponse(string $response): array
    {
        try {
            $xml = simplexml_load_string($response);
            $xml->registerXPathNamespace('ns2', 'http://konik.cgrate.com');

            $responseElem = $xml->xpath('//ns2:processCustomerPaymentResponse/return');

            if (! empty($responseElem)) {
                $return = $responseElem[0];
                $responseCode = (string) $return->responseCode;
                $responseMessage = (string) $return->responseMessage;
                $paymentID = (string) $return->paymentID;

                return [
                    'success' => $responseCode === '0',
                    'message' => $responseMessage,
                    'paymentID' => $paymentID,
                    'responseCode' => $responseCode,
                ];
            }

            return [
                'success' => false,
                'message' => 'Invalid response format from CGrate',
                'error_code' => 'PARSE_ERROR',
            ];

        } catch (Exception $e) {
            Log::error('Response parsing error', ['error' => $e->getMessage()]);

            return [
                'success' => false,
                'message' => 'Response parsing error: '.$e->getMessage(),
                'error_code' => 'PARSE_ERROR',
            ];
        }
    }

    /**
     * Parse status response
     */
    protected function parseStatusResponse(string $response): array
    {
        try {
            $xml = simplexml_load_string($response);
            $xml->registerXPathNamespace('ns2', 'http://konik.cgrate.com');

            $responseElem = $xml->xpath('//ns2:queryCustomerPaymentResponse/return');

            if (! empty($responseElem)) {
                $return = $responseElem[0];
                $responseCode = (string) $return->responseCode;
                $responseMessage = (string) $return->responseMessage;
                $paymentStatus = (string) $return->paymentStatus;

                // Check if payment is complete - be flexible with status text
                $statusLower = strtolower(trim($paymentStatus));
                $isComplete = $responseCode === '0' && (
                    in_array($statusLower, ['completed', 'successful', 'success', 'paid']) ||
                    str_contains($statusLower, 'success') ||
                    str_contains($statusLower, 'complete')
                );

                Log::info('Payment status check', [
                    'responseCode' => $responseCode,
                    'paymentStatus' => $paymentStatus,
                    'isComplete' => $isComplete,
                ]);

                return [
                    'payment_complete' => $isComplete,
                    'payment_status' => $paymentStatus,
                    'message' => $responseMessage,
                    'responseCode' => $responseCode,
                ];
            }

            return [
                'payment_complete' => false,
                'payment_status' => 'unknown',
                'message' => 'Invalid response format',
            ];

        } catch (Exception $e) {
            return [
                'payment_complete' => false,
                'payment_status' => 'error',
                'message' => 'Response parsing error: '.$e->getMessage(),
            ];
        }
    }

    /**
     * Parse balance response
     */
    protected function parseBalanceResponse(string $response): array
    {
        try {
            $xml = simplexml_load_string($response);
            $xml->registerXPathNamespace('ns2', 'http://konik.cgrate.com');

            $responseElem = $xml->xpath('//ns2:getAccountBalanceResponse/return');

            if (! empty($responseElem)) {
                $return = $responseElem[0];
                $responseCode = (string) $return->responseCode;
                $balance = (float) $return->balance;
                $currency = (string) ($return->currency ?? 'ZMW');

                return [
                    'success' => $responseCode === '0',
                    'balance' => $balance,
                    'currency' => $currency,
                    'responseCode' => $responseCode,
                ];
            }

            return [
                'success' => false,
                'message' => 'Invalid response format',
            ];

        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Response parsing error: '.$e->getMessage(),
            ];
        }
    }

    /**
     * Format phone number
     */
    protected function formatPhoneNumber(string $phoneNumber): string
    {
        // Remove any non-numeric characters
        $phoneNumber = preg_replace('/[^0-9]/', '', $phoneNumber);

        // If starts with +260 or 260, convert to 0...
        if (substr($phoneNumber, 0, 4) === '+260') {
            return '0'.substr($phoneNumber, 4);
        }

        if (substr($phoneNumber, 0, 3) === '260') {
            return '0'.substr($phoneNumber, 3);
        }

        // If 9 digits, add 0 prefix
        if (strlen($phoneNumber) === 9) {
            return '0'.$phoneNumber;
        }

        return $phoneNumber;
    }

    /**
     * Mask phone number for logging
     */
    protected function maskPhone(string $phone): string
    {
        if (strlen($phone) <= 6) {
            return '****'.substr($phone, -3);
        }

        return substr($phone, 0, 6).'****'.substr($phone, -3);
    }

    /**
     * Format error message for user display
     */
    protected function formatErrorMessage(string $errorMessage): string
    {
        // Check for timeout errors
        if (str_contains($errorMessage, 'timed out') || str_contains($errorMessage, 'timeout')) {
            return 'The payment service is currently experiencing delays. Please check your phone for the payment prompt and complete the transaction. If the prompt does not appear within 2 minutes, please try again.';
        }

        // Check for connection errors
        if (str_contains($errorMessage, 'Connection refused') || str_contains($errorMessage, 'Could not connect')) {
            return 'Unable to connect to the payment service. Please check your internet connection and try again.';
        }

        // Check for network errors
        if (str_contains($errorMessage, 'cURL error')) {
            return 'Network error while processing payment. Please ensure you have a stable internet connection and try again.';
        }

        // Check for SSL/certificate errors
        if (str_contains($errorMessage, 'SSL') || str_contains($errorMessage, 'certificate')) {
            return 'Security verification failed. Please try again in a moment.';
        }

        // Check for server errors
        if (str_contains($errorMessage, '500') || str_contains($errorMessage, 'Internal Server Error')) {
            return 'The payment service is temporarily unavailable. Please try again in a few minutes.';
        }

        // Check for invalid response
        if (str_contains($errorMessage, 'Invalid response') || str_contains($errorMessage, 'Parse')) {
            return 'Received an unexpected response from the payment service. Your payment may still be processing. Please wait a moment and check the status.';
        }

        // Default message for unknown errors
        return 'An error occurred while processing your payment. Please try again or contact the school office for assistance.';
    }

    /**
     * Get error code from error message
     */
    protected function getErrorCode(string $errorMessage): string
    {
        if (str_contains($errorMessage, 'timed out') || str_contains($errorMessage, 'timeout')) {
            return 'TIMEOUT';
        }

        if (str_contains($errorMessage, 'Connection refused') || str_contains($errorMessage, 'Could not connect')) {
            return 'CONNECTION_REFUSED';
        }

        if (str_contains($errorMessage, 'cURL error')) {
            return 'NETWORK_ERROR';
        }

        if (str_contains($errorMessage, 'SSL') || str_contains($errorMessage, 'certificate')) {
            return 'SSL_ERROR';
        }

        if (str_contains($errorMessage, '500') || str_contains($errorMessage, 'Internal Server Error')) {
            return 'SERVER_ERROR';
        }

        if (str_contains($errorMessage, 'Invalid response') || str_contains($errorMessage, 'Parse')) {
            return 'PARSE_ERROR';
        }

        return 'UNKNOWN_ERROR';
    }

    /**
     * Mock payment processing for testing
     */
    protected function mockProcessPayment(float $amount, string $customerMobile, string $paymentReference): array
    {
        Log::info('MOCK: Processing payment', [
            'amount' => $amount,
            'mobile' => $this->maskPhone($customerMobile),
            'reference' => $paymentReference,
        ]);

        if (str_contains(strtolower($paymentReference), 'fail')) {
            return [
                'success' => false,
                'message' => 'Mock payment failed',
                'error_code' => 'MOCK_FAIL',
                'responseCode' => '999',
            ];
        }

        return [
            'success' => true,
            'message' => 'Successful',
            'paymentID' => 'MOCK_'.strtoupper(substr(md5($paymentReference), 0, 8)),
            'responseCode' => '0',
        ];
    }

    /**
     * Mock status query for testing
     */
    protected function mockQueryStatus(string $paymentReference): array
    {
        Log::info('MOCK: Querying payment status', ['reference' => $paymentReference]);

        return [
            'payment_complete' => true,
            'payment_status' => 'completed',
            'message' => 'Mock payment completed',
            'responseCode' => '0',
        ];
    }
}
