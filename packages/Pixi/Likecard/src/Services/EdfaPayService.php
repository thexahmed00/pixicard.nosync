<?php

namespace Pixi\Likecard\Services;

use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;

// use Illuminate\Support\Facades\Http;

/**
 * EdfaPay Payment Gateway Integration Plugin
 *
 * This is an unofficial PHP plugin made using the EdfaPay documentation
 * provided at: https://documenter.getpostman.com/view/21813149/2s9Y5SX6Xs#51ada774-6275-41ee-ac0c-dd618963deef.
 *
 * Please note that the APIs used by this plugin are not owned by the author of this plugin
 * and should be used as per the policies set by EdfaPay.
 *
 * @package EdfaPay
 * @author Jayakrishnan Thoppil (iamjk.in, Github jkthoppil)
 * @version 2.1.5
 * @created 2024-01-05
 * @last_modified 2024-01-06
 */
class EdfaPayService
{
    /**
     * The base URL for the EdfaPay API.
     */
    const API_BASE_URL = 'https://api.edfapay.com/payment';

    /**
     * @var string The EdfaPay merchant ID.
     */
    private string $merchantId;

    /**
     * @var string The merchant password.
     */
    private string $merchantPassword;

    /**
     * @var array|null
     */
    private ?array $payeeDetails = null;

    /**
     * @var array|null
     */
    private ?array $transactionDetails = null;

    /**
     * @var array|null
     */
    private ?array $intervalDetails = null;

    /**
     * @var string|null
     */
    private ?string $lastOrderId = null;

    /**
     * @var string|null
     */
    private ?string $lastPaymentId = null;

    /**
     * @var array Holds the last response data
     */
    private static $lastResponse = [];

    /**
     * @var array Holds the last request data
     */
    private static $lastRequest = [];

    /**
     * Constructor for the EdfaPay class.
     *
     * @param string $merchantPassword The merchant password.
     * @param string $merchantId The merchant ID.
     * @param bool $savePayeeDetails (Optional) If set to true, payee details will be saved after being called
     */
    private function __construct(string $merchantId, string $merchantPassword, bool $savePayeeDetails = false)
    {
        $this->merchantId = $merchantId;
        $this->merchantPassword = $merchantPassword;
    }

    /**
     * Initiates the EdfaPay integration.
     *
     * @param string $merchantPassword The merchant password.
     * @param string $merchantId The merchant ID.
     * @param bool $savePayeeDetails (Optional) If set to true, payee details will be saved after being called
     * @return EdfaPay The EdfaPay instance.
     */
    public static function init(string $merchantId, string $merchantPassword, bool $savePayeeDetails = false): EdfaPayService
    {
        return new self($merchantId, $merchantPassword, $savePayeeDetails);
    }

    /**
     *  Generate a random order ID.
     * @param string|null $prefix
     * @param string|null $suffix
     *
     * @return string orderId
     */
    public static function order_id(?string $prefix = null, ?string $suffix = null): string
    {
        $prefix = $prefix ?? '';
        $suffix = $suffix ?? '';
        return $prefix . uniqid() . $suffix;
    }

    /**
     * Sets the payee details for the current instance.
     *
     * @param array $payeeDetails An associative array of payee details.
     * @return EdfaPay The EdfaPay instance.
     */
    public function payee(array $payeeDetails): self
    {
        if ($this->payeeDetails !== null &&  $this->payeeDetails != $payeeDetails) {
            $this->payeeDetails = array_merge($this->payeeDetails, $payeeDetails);
        } else {
            $this->payeeDetails = $payeeDetails;
        }

        return $this;
    }

    /**
     * Sets the transaction details for the current instance.
     *
     * @param array $transactionDetails An associative array of transaction parameters.
     * @return EdfaPay The EdfaPay instance.
     */
    public function pay(array $transactionDetails): self
    {
        $this->transactionDetails = $transactionDetails;

        if (!isset($this->transactionDetails['edfa_merchant_id']) || empty($this->transactionDetails['edfa_merchant_id']))
            $this->transactionDetails['edfa_merchant_id'] = $this->merchantId;
        return $this;
    }

    /**
     * Sets the interval details for the current instance.
     *
     * @param array $intervalDetails An associative array of interval parameters.
     * @return EdfaPay The EdfaPay instance.
     */
    public function interval(array $intervalDetails): self
    {
        $this->intervalDetails = $intervalDetails;
        return $this;
    }

    /**
     * Executes the transaction/interval request
     *
     * @return array An associative array containing the API response data
     * @throws Exception If the API request fails or if data is missing.
     */
    public function exec(): array
    {
        $payeeDetails =  $this->payeeDetails;
        $transactionDetails = $this->transactionDetails;

        if (empty($payeeDetails)) {
            throw new \Exception("Missing Payee details");
        }

        $params = array_merge($transactionDetails, $payeeDetails);

        self::validateRequiredParams($params, [
            'edfa_merchant_id',
            'order_id',
            'order_amount',
            'order_currency',
            'order_description',
            'payer_ip'
            // Optional params can be added to the validation if needed.
        ]);

        if ($this->intervalDetails !== null) {
            $params['recurring_init'] = 'Y';
            $params['recurring_interval_value'] = $this->intervalDetails['value'];
            $params['recurring_interval_type'] = $this->intervalDetails['type'];
        }

        $params['action'] = 'SALE';
        $params['auth'] = 'N';
        $params['hash'] = $this->generateHash(
            [
                $params['order_id'],
                $params['order_amount'],
                $params['order_currency'],
                $params['order_description']
            ]
        );
        
        Log::info("generated hash");
        Log::debug($params['hash']);

        $response =  $this->makeRequest('/initiate', $params);

        $this->lastOrderId = $params['order_id'];

        if (isset($response['payment_id'])) {
            $this->lastPaymentId = $response['payment_id'];
        }
        if ($this->intervalDetails !== null && isset($response['recurring_token']) && isset($response['trans_id'])) {
            return $this->makeRecurring($response['recurring_token'], $response['trans_id']);
        }

        return $response;
    }

    /**
     * Fetches the status of a payment.
     *
     * @param string|null $orderId The order ID in the merchant's system.
     * @param string|null $paymentId The Payment ID in the Payment Platform.
     *
     * @return array An associative array containing the API response data
     *
     * @throws Exception If the API request fails.
     */
    public function status(?string $orderId = null, ?string $paymentId = null): array
    {
        $orderId = $orderId ?? $this->lastOrderId;
        $paymentId = $paymentId ?? $this->lastPaymentId;

        if (empty($orderId) || empty($paymentId)) {
            throw new \Exception("Missing order or payment ID. Please provide the order ID and payment ID or call after a successful transaction");
        }

        $hash = $this->generateHash([$paymentId]);

        return $this->makeRequest('/status', [
            'order_id' => $orderId,
            'merchant_id' => $this->merchantId,
            'gway_Payment_id' => $paymentId,
            'hash' => $hash,
            'auth' => 'N'
        ], 'json');
    }

    /**
     * Initiates a refund request.
     *
     * @param string $gwayId The public transaction ID of the Payment Gateway.
     * @param string $orderId The order ID in the merchant's system.
     * @param string $payerIp The IP address of the payer.
     * @param string|null $amount The amount to refund (optional).
     *
     * @return array An associative array containing the API response data.
     *
     * @throws Exception If the API request fails or if data is missing.
     */
    public function refund(string $gwayId, string $orderId,  string $payerIp, ?string $amount = null): array
    {
        $amount = $amount ?? '';

        $hash = $this->generateHash([$gwayId, $amount]);

        return $this->makeRequest('/refund', [
            'gwayId' => $gwayId,
            'order_id' => $orderId,
            'edfa_merchant_id' => $this->merchantId,
            'hash' => $hash,
            'payer_ip' => $payerIp,
            'amount' => $amount
        ], 'json');
    }

    /**
     * Get the last response.
     *
     * @return array|null the last response data or null if no request has been made
     */
    public static function getLastResponse(): ?array
    {
        return self::$lastResponse;
    }

    /**
     * Get the last request and it's response.
     *
     * @return array|null the last request data or null if no request has been made
     */
    public static function last(): ?array
    {
        return self::$lastRequest ? [
            'request' => self::$lastRequest,
            'response' => self::$lastResponse
        ] : null;
    }

    /**
     * Get the user's IP address.
     *
     * @return string The user's IP address.
     */
    public static function getIp(): string
    {
        foreach (['HTTP_CLIENT_IP', 'HTTP_X_FORWARDED_FOR', 'HTTP_X_FORWARDED', 'HTTP_FORWARDED_FOR', 'HTTP_FORWARDED', 'REMOTE_ADDR'] as $key) {
            if (isset($_SERVER[$key])) {
                $ip = $_SERVER[$key];
                if (strpos($ip, ',') !== false) {
                    $ip = explode(',', $ip)[0];
                }
                return $ip;
            }
        }
        return '';
    }


    // ====================================

    /**
     * Get the user's IP address.
     *
     * @return string The user's IP address.
     */
    public static function getClientIp(): string
    {
        $ip = '';

        if (isset($_SERVER['HTTP_CLIENT_IP'])) {
            $ip = $_SERVER['HTTP_CLIENT_IP'];
        } elseif (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
        } elseif (isset($_SERVER['HTTP_X_FORWARDED'])) {
            $ip = $_SERVER['HTTP_X_FORWARDED'];
        } elseif (isset($_SERVER['HTTP_FORWARDED_FOR'])) {
            $ip = $_SERVER['HTTP_FORWARDED_FOR'];
        } elseif (isset($_SERVER['HTTP_FORWARDED'])) {
            $ip = $_SERVER['HTTP_FORWARDED'];
        } elseif (isset($_SERVER['REMOTE_ADDR'])) {
            $ip = $_SERVER['REMOTE_ADDR'];
        }

        // Check for comma-separated multiple IPs and take the first one
        if (strpos($ip, ',') !== false) {
            $ip = explode(',', $ip)[0];
        }

        return $ip;
    }

    /**
     * Verify the hash sent by EdfaPay (for Card, 3DS payments).
     *
     * @param array $params
     * @return bool True if the hash is valid, false otherwise.
     */
    public function verifyHash(array $params): bool
    {
        $receivedHash = $params['hash'] ?? null;

        if (empty($receivedHash)) {
            return true;
        }

        $email = $this->payeeDetails['payer_email'];

        $paymentMethod = $this->determinePaymentMethod($params);

        if ($paymentMethod == 'card' || $paymentMethod == '3ds') {
            // Hash verification for Card and 3DS
            $email = $this->payeeDetails['payer_email']; // Get email from your payee details

            $transId = $params['trans_id'];
            $cardNumber = $params['card']; // Masked card number
            $password = $this->merchantPassword;

            // Special signature, used to validate callback 
            // md5( strtoupper(strrev(email) . PASSWORD . trans_id . strrev(substr(card_number,0,6) . substr(card_number,-4))) )

            $stringToHash = strtoupper( strrev($email) . $password . $transId . strrev( substr($cardNumber, 0, 6) . substr($cardNumber, -4) ) );
            $calculatedHash = md5($stringToHash);

            $time = Carbon::now();
            Log::debug("\n\n ===== \n\n Edfa Verification Completed at $time: ", compact('email', 'transId', 'cardNumber', 'password', 'stringToHash', 'receivedHash', 'calculatedHash'));

            return $receivedHash === $calculatedHash;
        } else if ($paymentMethod == 'applepay' || $paymentMethod == 'manafith') {
            // Hash verification for Apple Pay and Manafith
            $payment_public_id = $params['id'];
            $order_number = $params['order_number'];
            $order_amount = $params['order_amount'];
            $order_currency = $params['order_currency'];
            $order_description = $params['order_description'];
            $password = $this->merchantPassword;

            $stringToHash = strtoupper($payment_public_id . $order_number . $order_amount . $order_currency . $order_description . $password);

            $calculatedHash = hash('sha1', md5($stringToHash));

            return $receivedHash === $calculatedHash;
        }

        return true; // For other methods, you might not need hash verification or handle it differently
    }

    /**
     * Determine the payment method based on the request parameters.
     *
     * @param array $params
     * @return string The payment method (e.g., 'card', 'applepay', 'tamara').
     */
    public function determinePaymentMethod(array $params): string
    {
        if (isset($params['card'])) {
            return 'card';
        } elseif (isset($params['redirect_url'])) {
            return '3ds';
        } elseif (isset($params['source']) && $params['source'] === 'tamara') {
            return 'tamara';
        } elseif (isset($params['source']) && $params['source'] === 'manafith') {
            return 'manafith';
        } elseif (isset($params['id']) && isset($params['order_number'])) {
            // Assuming 'id' and 'order_number' are present for Apple Pay
            return 'applepay';
        } else {
            return 'unknown';
        }
    }

    /**
     * Determine the payment status based on the request parameters.
     *
     * @param array $params
     * @param string $paymentMethod
     * @return string The payment status (e.g., 'success', 'declined', 'redirect').
     */
    public function determineStatus(array $params, string $paymentMethod): string
    {
        if ($paymentMethod === 'card' || $paymentMethod === '3ds') {
            $result = strtolower($params['result']);

            if ($result === 'success') {
                return 'success';
            } elseif ($result === 'declined') {
                return 'declined';
            } elseif ($result === 'redirect') {
                return 'redirect';
            } else {
                return 'unknown';
            }
        } elseif ($paymentMethod === 'applepay' || $paymentMethod === 'tamara' || $paymentMethod === 'manafith') {
            $status = strtolower($params['status']);

            if ($status === 'success') {
                return 'success';
            } elseif ($status === 'fail') {
                return 'fail';
            } else {
                return 'unknown';
            }
        } else {
            return 'unknown';
        }
    }


    /**
     * Initiates a recurring payment request.
     *
     *
     * @return array An associative array containing the API response data
     *
     * @throws Exception If the API request fails or if data is missing.
     */
    private function makeRecurring($recurring_token, $recurring_init_trans_id): array
    {

        $params = [
            'order_id' => $this->lastOrderId,
            'order_amount' => $this->transactionDetails['order_amount'],
            'order_description' => $this->transactionDetails['order_description'],
            'payer_ip' => $this->payeeDetails['payer_ip'],
            'recurring_token' => $recurring_token,
            'recurring_init_trans_id' => $recurring_init_trans_id
        ];

        self::validateRequiredParams($params, [
            'order_id',
            'order_amount',
            'order_description',
            'payer_ip',
            'recurring_token',
            'recurring_init_trans_id'
        ]);

        $hash = $this->generateHash([$params['order_id'], $params['order_amount'], $params['order_description'], $params['recurring_init_trans_id'], $params['recurring_token']]);

        return $this->makeRequest('/recurring', [
            'order_id' => $params['order_id'],
            'merchant_id' => $this->merchantId,
            'hash' => $hash,
            'action' => 'RECURRING_SALE',
            'order_amount' => $params['order_amount'],
            'primary_order_id' => $params['order_id'],
            'order_description' => $params['order_description'],
            'payer_ip' => $params['payer_ip'],
            'recurring_token' => $params['recurring_token']
        ], 'json');
    }

    // HASH GENERATION ========================================

    /**
     * Generates the hash for different API calls based on the request type.
     *
     * @param string $reqType The type of request (initiate, status, refund, recurring).
     * @param string ...$val The parameters required for hash generation based on the request type.
     *
     * @return string The generated hash.
     * @throws Exception If an invalid request type is provided.
     */
    private function generateHash(array $params): string
    {
    Log::info("Params inside generate hash");
    Log::debug($params);
        Log::info("password--".$this->merchantPassword);
        $toMd5 = strtoupper(implode('', $params) . $this->merchantPassword);

        return hash('sha1', md5($toMd5));
    }

    // HELPER FUNCTIONS ========================================

    /**
     * Makes a request to the EdfaPay API.
     *
     * @param string $endpoint The API endpoint.
     * @param array $params The request parameters.
     * @param string $contentType The content type for the body (formdata or json)
     *
     * @return array An associative array containing the API response data.
     *
     * @throws Exception If the API request fails.
     */
    private function makeRequest(string $endpoint, array $params, string $contentType = 'formdata'): array
    {
        $url = self::API_BASE_URL . $endpoint;

        self::$lastRequest = [
            'url' => $url,
            'params' => $params,
            'contentType' => $contentType
        ];
Log::debug('EdfaPay Request:', [
        'url' => $url,
        'params' => $params
    ]);
        $curl = curl_init($url);

        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_POST, true);

        if ($contentType == 'json') {
            curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($params));
            curl_setopt($curl, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
        } else {
            curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query($params));
        }

        $response = curl_exec($curl);
        Log::info("response from edfapay");
Log::debug($response);
        $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        $error = curl_error($curl);

        curl_close($curl);

        if ($error) {
            throw new \Exception('cURL Error: ' . $error);
        }

        self::$lastResponse = json_decode($response, true);
        self::$lastResponse['status'] = $httpCode;

        if ($httpCode >= 400) {
            throw new \Exception('API Request Failed: ' . json_encode(self::$lastResponse));
        }

        if (json_last_error() !== JSON_ERROR_NONE) {
            return json_decode($response, true);
        }

        return self::$lastResponse;
    }

    /**
     * Validates that all required parameters are present in the given array.
     *
     * @param array $params The array of parameters to validate.
     * @param array $requiredParams An array of required parameter keys.
     *
     * @throws Exception If any required parameter is missing.
     */
    private static function validateRequiredParams(array $params, array $requiredParams): void
    {
        foreach ($requiredParams as $param) {
            if (!isset($params[$param]) || empty($params[$param])) {
                throw new \Exception("Missing required parameter: {$param}");
            }
        }
    }
}

// $edfa = EdfaPay::init(env('EDFAPAY_ID'), env('EDFAPAY_PASS'));

// $transactionDetails = [
//     'edfa_merchant_id' => env('EDFAPAY_ID'),
//     'order_id' => EdfaPay::order_id(),
//     'order_amount' => '1.00',
//     'order_currency' => 'SAR',
//     'order_description' => 'Test Recurring Order',
//     'term_url_3ds' => 'https://successorfailurl.com',
// ];

// $intervalDetails = [
//     'value' => 2,  // Every 2 months
//     'type' => 'month', // Month, year, etc.
// ];

// // 1. Initiate transaction with recurring interval
// $payment = $edfa->payee($payeeDetails)
//                 ->pay($transactionDetails)
//                 ->interval($intervalDetails)
//                 ->exec();

// The exec() method will automatically:
//   - Set 'recurring_init' => 'Y'
//   - Send the initial transaction request
//   - Get the 'recurring_token' and 'trans_id' from the response
//   - Send the recurring payment request using makeRecurring()