<?php

namespace Pixi\Likecard\Services;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class LikeCard
{
    private static $apiBaseUrl = 'https://taxes.like4app.com/online/';
    protected static $deviceId;
    protected static $email;
    // protected static $password;
    protected static $securityCode;
    protected static $secretIv;
    protected static $hash_key;
    protected static $secret_key;
    protected static $langId;
    protected static $phone;
    // protected static $merchRefId;
    protected $action;
    protected $params = [];

    public static $initialized = false;

    /**
     * Initialize the LikeCard API client.
     *
     * @param string|null $deviceId
     * @param string|null $email
     * @param string|null $password
     * @param string|null $securityCode
     * @param string|null $langId
    //  * @param string|null $merchRefId
     */
    public static function init(
        ?string $email = null,
        // ?string $password = null,
        ?string $securityCode = null,
        ?string $secretIv = null,
        ?string $hash_key = null,
        ?string $secret_key = null,
        ?string $deviceId = null,
        // ?string $merchRefId = null,
        ?string $langId = null
    ) {
        self::$email = $email ?? config('likecard.email');
        self::$securityCode = $securityCode ??  config('likecard.security_code');
        self::$secretIv = $secretIv ?? config('likecard.secret_iv');
        self::$hash_key = $hash_key ?? config('likecard.hash_key');
        self::$secret_key = $secret_key ?? config('likecard.secret_key');
        self::$deviceId = $deviceId ?? config('likecard.device_id');
        // self::$merchRefId = $merchRefId ?? config('likecard.merch_ref_id');
        self::$phone = $phone ?? config('likecard.phone');
        self::$langId = $langId ?? config('likecard.lang_id'); // Default to 1 (English)

        self::$initialized = true;
    }

    /**
     * Get the balance.
     *
     * @return array|null
     */
    public static function balance()
    {
        $instance = new self();
        $instance->action = 'check_balance';
        return $instance->execute();
    }

    /**
     * Set the action to get categories.
     *
     * @return self
     */
    public static function category(): self
    {
        $instance = new self();
        $instance->action = 'categories';
        return $instance;
    }

    /**
     * Sets the action to get products based on provided filters.
     *
     * @param array $filter An associative array containing filter criteria.
     *   The array must contain *either* an 'ids' key *or* a 'categoryId' key, but not both.
     *   - 'ids': (int|array<int>) An integer representing a single product ID, or an array of integers
     *            representing multiple product IDs.
     *   - 'categoryId': (int) An integer representing a category ID.
     *
     * @return self Returns the current instance for method chaining.
     *
     * @throws ValidationException If the filter array is invalid or missing required keys.
     * @throws \InvalidArgumentException If neither 'ids' nor 'categoryId' is provided, or if both are provided.
     */
    public static function product(array $filter): self
    {
        $instance = new self();
        $instance->action = 'products';

        $validator = Validator::make($filter, [
            'ids' => 'sometimes|required_without:categoryId|array', // ids is required if categoryId is not present
            'ids.*' => 'integer',  // Each element in the ids array must be an integer
            'categoryId' => 'sometimes|required_without:ids|integer',  // categoryId is required if ids is not present
        ]);


        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        // Check if *both* 'ids' and 'categoryId' are present (XOR condition)
        if (isset($filter['ids']) && isset($filter['categoryId'])) {
            throw new \InvalidArgumentException("Only one of 'ids' or 'categoryId' can be provided, not both.");
        }

        if (isset($filter['ids'])) {
            // Ensure $filter['ids'] is always an array, even if a single ID is provided
            $instance->params['ids'] = is_array($filter['ids']) ? $filter['ids'] : [$filter['ids']];
        } elseif (isset($filter['categoryId'])) {
            $instance->params['categoryId'] = $filter['categoryId'];
        } else {
            // This should never happen due to the validator, but it's good practice to have a final check.
            throw new \InvalidArgumentException("Either 'ids' or 'categoryId' must be provided in the filter.");
        }


        return $instance;
    }

    /**
     * Set the action to get orders or order details.
     *
     * @param int|array|null $orderId (Optional) The order ID to fetch details for.
     *                                      Or an array for filtering when fetching all orders.
     *                                      Or null to fetch all orders.
     * @return self
     */
    public static function order(mixed $orderId = null): self
    {
        $instance = new self();
        $instance->action = 'orders';

        if (is_numeric($orderId)) {
            // Fetch details for a specific order
            $instance->action = 'orders/details'; // Corrected endpoint
            $instance->params['orderId'] = $orderId;
        } elseif (is_array($orderId)) {
            // Filters for fetching all orders
            $instance->params = $orderId;
        }

        return $instance;
    }

    /**
     * Execute the API request for fetching data (POST).
     *
     * @return array|null The API response data or null on error.
     * @throws \Exception If the action is not set.
     */
    public function fetch()
    {
        return $this->execute('POST');
    }

    /**
     * Execute the API request for making an order (POST).
     *
     * @return array|null The API response data or null on error.
     * @throws \Exception If the action is not set or if trying to POST without data.
     */
    public function make()
    {
        if ($this->action !== 'create_order' || empty($this->params)) {
            throw new \Exception('make() is only for creating orders and requires order data.');
        }
        return $this->execute('POST');
    }

    /**
     * Set the action to create an order.
     *
     * @param array $orderData The data for the order to be created.
     * @return self
     * @throws \Exception
     */
    public static function createOrder(array $orderData): self
    {
        $instance = new self();
        $instance->action = 'create_order';

        // Essential parameters
        if (!isset($orderData['productId'])) {
            throw new \Exception('Missing required parameter: productId');
        }

        // Essential parameters
        if (!self::$phone || empty(self::$phone)) {
            throw new \Exception('Missing required parameter: phone');
        }

        $order_time = time();
        $hash_string = $order_time . "|" . self::$email . "|" . self::$phone . "|" . self::$hash_key;
        $hash = hash('sha256', $order_time . self::$email . self::$phone . self::$hash_key);
        $optional_fields = $orderData['optionalFields'] ?? [];

        $instance->params = [
            'productId'   => $orderData['productId'],
            'referenceId' => $orderData['referenceId'] ?? $orderData['productId'] . "_$order_time" . rand(100, 999),
            'time'        => $order_time,
            'hash'        => $hash,
            'hash_string' => $hash_string,
            'quantity'    => $orderData['quantity'] ?? 1,
            'optionalFields' => $optional_fields,
        ];

        return $instance;
    }

    /**
     * Decrypt the serial code.
     *
     * @param string $encryptedTxt The encrypted serial code.
     * @return string|null The decrypted serial code or null on failure.
     */
    public static function decryptSerial(string $encryptedTxt): ?string
    {
        $secretKey = self::$secret_key; // Use the same password for decryption
        $secretIv = self::$secretIv; // Use the security code
        $encryptMethod = 'AES-256-CBC';
        $key = hash('sha256', $secretKey);
        $iv = substr(hash('sha256', $secretIv), 0, 16);

        return openssl_decrypt(base64_decode($encryptedTxt), $encryptMethod, $key, 0, $iv);
    }

    /**
     * Execute the API request using cURL.
     *
     * @param string $method The HTTP method.
     * @return array The API response data or null on error.
     * @throws \Exception If the action is not set.
     */
    protected function execute(string $method = 'POST')
    {
        self::checkInit();

        if (!$this->action) {
            throw new \Exception('LikeCard API action not set.');
        }

        $url = self::$apiBaseUrl . $this->action;

        $curl = curl_init();

        $params = [
            'deviceId' => self::$deviceId,
            'email' => self::$email,
            'securityCode' => self::$securityCode,
            'langId' => self::$langId,
            ...$this->params
        ];
        
        Log::debug("Exec Params", $params);
        Log::debug("Url", ['url' => $url]);

        

        curl_setopt_array($curl, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => $method,
            CURLOPT_POSTFIELDS => $params,
            CURLOPT_HTTPHEADER => [
                // 'Accept: application/json',
                'Content-Type: multipart/form-data;'
            ],
            CURLOPT_FAILONERROR => true,
        ]);

        $curl_exec = curl_exec($curl);
if ($curl_exec === false) {
    $curl_error = curl_error($curl);
    Log::error("LikeCard cURL error: " . $curl_error);
    throw new \Exception("cURL error: " . $curl_error);
}
        // print_r($curl_exec); exit;

        $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        $curl_exec = json_decode($curl_exec, true) ?? $curl_exec;

        // print_r($curl_exec); exit;

        if ($curl_exec["response"] == 0) {
            throw new \Exception($curl_exec["message"]); // If the Message is "Security Error", check the credentials from the panel
        }

        $response = match($this->action) {
            'check_balance' => [
                'user_id' => $curl_exec['userId'],
                'balance' => $curl_exec['balance'],
                'currency' => $curl_exec['currency'],
            ],
            'create_order' => $curl_exec,
            default => $curl_exec["data"]
        };

        $err_msg = match($httpCode) {
            1020 => "Blocked IP address. Please contact your account manager",
            408 => "Request Timeout",
            default => ($httpCode < 200 || $httpCode >= 300) ? ($response["message"] ?? $response) : null,
        };

        if ($err_msg) {
            throw new \Exception($err_msg, $httpCode);
        }
        

        $curl_error = curl_errno($curl) ? curl_error($curl) : null;

        curl_close($curl);

        return $curl_error ? $curl_error : $response;
    }


    /**
     * Check if the API client has been initialized.
     *
     * @throws \Exception If the client is not initialized.
     */
    protected static function checkInit(): void
    {
        if (!self::$deviceId || !self::$email || !self::$langId || !self::$securityCode) {
            throw new \Exception('LikeCard API client not initialized. Call LikeCard::init() first.');
        }
    }
}

