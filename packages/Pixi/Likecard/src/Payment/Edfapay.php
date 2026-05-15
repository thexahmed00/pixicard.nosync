<?php

namespace Pixi\Likecard\Payment;

// Make sure to use your EdfaPay service
use Webkul\Checkout\Facades\Cart;

use Illuminate\Support\Facades\Log;
use Webkul\Payment\Payment\Payment;
use Illuminate\Support\Facades\Storage;
use Pixi\Likecard\Services\EdfaPayService;

class Edfapay extends Payment
{
    protected $code = 'edfapay';

    public function getRedirectUrl()
    {
        // 1. Get the current cart instance
        $cart = Cart::getCart();

        // 2. Get EdfaPay credentials from admin config
        $merchantId = $this->getConfigData('merchant_id');
        $merchantPassword = $this->getConfigData('merchant_password');

        if (! $merchantId || ! $merchantPassword) {
            throw new \Exception('EdfaPay credentials are not configured.');
        }

        // 3. Initialize the EdfaPay service
        $edfaPay = EdfaPayService::init($merchantId, $merchantPassword);

        // 4. Prepare Payee (Customer) Details
        $billingAddress = $cart->billing_address;
        $payeeDetails = [
            'payer_first_name' => $billingAddress->first_name,
            'payer_last_name'  => $billingAddress->last_name,
            'payer_address'    => $billingAddress->address,
            'payer_city'       => $billingAddress->city,
            'payer_country'    => $billingAddress->country,
            'payer_zip'        => $billingAddress->postcode,
            'payer_email'      => $billingAddress->email,
            'payer_phone'      => $billingAddress->phone,
            'payer_ip'         => request()->ip(),
        ];

        // 5. Prepare Transaction Details
        $transactionDetails = [
            'order_id'          => 'PIXI-' . $cart->id . '-' . time(), // Must be unique
            'order_amount'      => number_format($cart->grand_total, 2, '.', ''),
            'order_currency'    => $cart->cart_currency_code,
            'order_description' => 'Order #' . $cart->id . ' from ' . config('app.name'),
            'term_url_3ds'      => route('edfapay.callback'), // The URL EdfaPay will redirect back to
        ];

        try {
            // 6. Execute the payment initiation request
            $response = $edfaPay
                ->payee($payeeDetails)
                ->pay($transactionDetails)
                ->exec();

            // 7. Check for the redirect URL and return it
            if (isset($response['redirect_url'])) {
                return $response['redirect_url'];
            }
            
            // Handle cases where the redirect URL is not returned
            Log::error('EdfaPay API Error: Redirect URL not found.', ['response' => $response]);
            throw new \Exception('Failed to initiate payment with EdfaPay.');

        } catch (\Exception $e) {
            Log::error('EdfaPay Service Exception: ' . $e->getMessage());
            // You can redirect to a failure page or re-throw the exception
            throw $e;
        }
    }

    public function getImage()
    {
        $url = $this->getConfigData('image');
        return $url ? Storage::url($url) : asset('vendor/pixi/likecard/assets/images/edfapay.png');
    }
}

