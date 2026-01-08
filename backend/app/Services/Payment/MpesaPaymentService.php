<?php

namespace App\Services\Payment;

use App\Models\Payment;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class MpesaPaymentService
{
    private string $baseUrl;
    private string $consumerKey;
    private string $consumerSecret;
    private string $shortcode;
    private string $passkey;
    private ?string $accessToken = null;

    public function __construct()
    {
        $environment = config('services.mpesa.environment', 'sandbox');
        
        $this->baseUrl = $environment === 'production'
            ? 'https://api.safaricom.co.ke'
            : 'https://sandbox.safaricom.co.ke';
            
        $this->consumerKey = config('services.mpesa.consumer_key');
        $this->consumerSecret = config('services.mpesa.consumer_secret');
        $this->shortcode = config('services.mpesa.shortcode');
        $this->passkey = config('services.mpesa.passkey');
    }

    /**
     * Initiate STK Push (Lipa Na M-Pesa Online)
     *
     * @param Payment $payment
     * @param string $phoneNumber
     * @return array
     */
    public function initiateStkPush(Payment $payment, string $phoneNumber): array
    {
        try {
            // Get access token
            if (!$this->getAccessToken()) {
                return [
                    'success' => false,
                    'error' => 'Failed to get M-Pesa access token',
                ];
            }

            // Format phone number (remove + and spaces)
            $phone = $this->formatPhoneNumber($phoneNumber);

            // Generate timestamp and password
            $timestamp = Carbon::now()->format('YmdHis');
            $password = base64_encode($this->shortcode . $this->passkey . $timestamp);

            // Prepare request payload
            $payload = [
                'BusinessShortCode' => $this->shortcode,
                'Password' => $password,
                'Timestamp' => $timestamp,
                'TransactionType' => 'CustomerPayBillOnline',
                'Amount' => (int) $payment->amount,
                'PartyA' => $phone,
                'PartyB' => $this->shortcode,
                'PhoneNumber' => $phone,
                'CallBackURL' => config('services.mpesa.callback_url'),
                'AccountReference' => $payment->lease->lease_number ?? 'RMS-' . $payment->id,
                'TransactionDesc' => $this->getPaymentDescription($payment),
            ];

            // Make API request
            $response = Http::withToken($this->accessToken)
                ->post($this->baseUrl . '/mpesa/stkpush/v1/processrequest', $payload);

            $result = $response->json();

            if ($response->successful() && isset($result['ResponseCode']) && $result['ResponseCode'] == '0') {
                // Update payment record
                $payment->update([
                    'mpesa_checkout_request_id' => $result['CheckoutRequestID'] ?? null,
                    'mpesa_merchant_request_id' => $result['MerchantRequestID'] ?? null,
                    'status' => 'pending',
                    'gateway_response' => json_encode($result),
                ]);

                Log::info('M-Pesa STK Push initiated', [
                    'payment_id' => $payment->id,
                    'checkout_request_id' => $result['CheckoutRequestID'] ?? null,
                    'phone' => $phone,
                ]);

                return [
                    'success' => true,
                    'checkout_request_id' => $result['CheckoutRequestID'] ?? null,
                    'merchant_request_id' => $result['MerchantRequestID'] ?? null,
                    'customer_message' => $result['CustomerMessage'] ?? 'STK Push sent to your phone',
                ];
            }

            // Handle error response
            Log::error('M-Pesa STK Push failed', [
                'payment_id' => $payment->id,
                'response' => $result,
            ]);

            $payment->update([
                'status' => 'failed',
                'gateway_response' => json_encode($result),
            ]);

            return [
                'success' => false,
                'error' => $result['errorMessage'] ?? $result['ResponseDescription'] ?? 'STK Push failed',
            ];

        } catch (\Exception $e) {
            Log::error('M-Pesa STK Push exception', [
                'payment_id' => $payment->id,
                'error' => $e->getMessage(),
            ]);

            $payment->update([
                'status' => 'failed',
                'gateway_response' => json_encode(['error' => $e->getMessage()]),
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Query transaction status
     *
     * @param string $checkoutRequestId
     * @return array
     */
    public function queryTransactionStatus(string $checkoutRequestId): array
    {
        try {
            if (!$this->getAccessToken()) {
                return [
                    'success' => false,
                    'error' => 'Failed to get M-Pesa access token',
                ];
            }

            $timestamp = Carbon::now()->format('YmdHis');
            $password = base64_encode($this->shortcode . $this->passkey . $timestamp);

            $payload = [
                'BusinessShortCode' => $this->shortcode,
                'Password' => $password,
                'Timestamp' => $timestamp,
                'CheckoutRequestID' => $checkoutRequestId,
            ];

            $response = Http::withToken($this->accessToken)
                ->post($this->baseUrl . '/mpesa/stkpushquery/v1/query', $payload);

            $result = $response->json();

            if ($response->successful()) {
                return [
                    'success' => true,
                    'result_code' => $result['ResultCode'] ?? null,
                    'result_desc' => $result['ResultDesc'] ?? null,
                    'data' => $result,
                ];
            }

            return [
                'success' => false,
                'error' => $result['errorMessage'] ?? 'Query failed',
            ];

        } catch (\Exception $e) {
            Log::error('M-Pesa query exception', [
                'checkout_request_id' => $checkoutRequestId,
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Get OAuth access token
     *
     * @return bool
     */
    private function getAccessToken(): bool
    {
        try {
            $response = Http::withBasicAuth($this->consumerKey, $this->consumerSecret)
                ->get($this->baseUrl . '/oauth/v1/generate?grant_type=client_credentials');

            if ($response->successful()) {
                $result = $response->json();
                $this->accessToken = $result['access_token'] ?? null;

                Log::info('M-Pesa access token obtained');
                return true;
            }

            Log::error('Failed to get M-Pesa access token', [
                'response' => $response->json(),
            ]);

            return false;

        } catch (\Exception $e) {
            Log::error('M-Pesa OAuth exception', [
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }

    /**
     * Format phone number to M-Pesa format (254XXXXXXXXX)
     *
     * @param string $phone
     * @return string
     */
    private function formatPhoneNumber(string $phone): string
    {
        // Remove spaces, dashes, and plus sign
        $phone = preg_replace('/[\s\-\+]/', '', $phone);

        // If starts with 0, replace with 254
        if (substr($phone, 0, 1) === '0') {
            $phone = '254' . substr($phone, 1);
        }

        // If doesn't start with 254, add it
        if (substr($phone, 0, 3) !== '254') {
            $phone = '254' . $phone;
        }

        return $phone;
    }

    /**
     * Generate payment description
     *
     * @param Payment $payment
     * @return string
     */
    private function getPaymentDescription(Payment $payment): string
    {
        $type = ucfirst($payment->payment_type);
        return "{$type} payment - RMS";
    }

    /**
     * Validate M-Pesa callback
     *
     * @param array $callbackData
     * @return bool
     */
    public function validateCallback(array $callbackData): bool
    {
        // Basic validation - check required fields
        return isset($callbackData['Body']['stkCallback']);
    }

    /**
     * Process M-Pesa callback
     *
     * @param array $callbackData
     * @return array
     */
    public function processCallback(array $callbackData): array
    {
        try {
            $callback = $callbackData['Body']['stkCallback'];
            
            $checkoutRequestId = $callback['CheckoutRequestID'] ?? null;
            $resultCode = $callback['ResultCode'] ?? null;
            $resultDesc = $callback['ResultDesc'] ?? null;

            // Extract callback metadata
            $metadata = [];
            if (isset($callback['CallbackMetadata']['Item'])) {
                foreach ($callback['CallbackMetadata']['Item'] as $item) {
                    $metadata[$item['Name']] = $item['Value'] ?? null;
                }
            }

            return [
                'checkout_request_id' => $checkoutRequestId,
                'result_code' => $resultCode,
                'result_desc' => $resultDesc,
                'amount' => $metadata['Amount'] ?? null,
                'mpesa_receipt' => $metadata['MpesaReceiptNumber'] ?? null,
                'transaction_date' => $metadata['TransactionDate'] ?? null,
                'phone_number' => $metadata['PhoneNumber'] ?? null,
            ];

        } catch (\Exception $e) {
            Log::error('M-Pesa callback processing failed', [
                'error' => $e->getMessage(),
                'callback' => $callbackData,
            ]);

            return [];
        }
    }
}
