<?php

use Illuminate\Support\Facades\Route;
use Pixi\Likecard\Http\Controllers\EdfapayController;
use Pixi\Likecard\Http\Controllers\TapPaymentController;



Route::group(['middleware' => ['web', 'theme', 'locale', 'currency']], function () {
    Route::get('edfapay/callback', [EdfapayController::class, 'callback'])->name('edfapay.callback');
});
Route::group(['middleware' => ['web', 'theme', 'locale', 'currency']], function () {

    // Route::get('edfapay/callback', [EdfapayController::class, 'callback'])->name('edfapay.callback');

    // The "processing" page uses this route to check the payment status
    Route::get('edfapay/check-status', [EdfapayController::class, 'checkStatus'])->name('edfapay.check_status');
    Route::get('checkout/success/{order?}', function ($order = null) {
        return view('shop::checkout.success', ['order' => $order]);
    })->name('shop.checkout.success');
    Route::get('edfapay/success/{orderId}', [EdfapayController::class, 'success'])->name('edfapay.success');
});
Route::post('edfapay/webhook', [EdfapayController::class, 'webhook'])->name('edfapay.webhook');
Route::post('taptopay/webhook', [TapPaymentController::class, 'webhook'])->name('taptopay.webhook');
Route::get('/test-likecard-balance', function () {
    // 1. Define the API payload by reading directly from your .env file
    $payload = [
        'email' => env('LC_EMAIL'),
        'securityCode' => env('LC_SECURITY_CODE'),
        'deviceId' => env('LC_DEVICE_ID'),
        'phone' => env('LC_PHONE'), // Adding the phone number, as it's likely needed
        'langId' => env('LC_LANG_ID'),
    ];

    // Define the API endpoint
    $apiUrl = 'https://taxes.like4app.com/online/check_balance';

    try {
        // 2. Make the POST request to the API
        $response = Http::asForm()->post($apiUrl, $payload);

        // Dump the payload we sent and the response we got for easy debugging
        return response()->json([
            'request_payload' => $payload,
            'api_response' => $response->json(),
        ], $response->status());

    } catch (\Exception $e) {
        // Handle cases where the request fails
        return response()->json([
            'error' => 'Request failed',
            'message' => $e->getMessage(),
        ], 500);
    }
});
//Route::get('api/order/{orderId}', [EdfapayController::class, 'getOrderDetails'])->name('pixi.api.order.details');


Route::prefix('api')->middleware(['auth:sanctum'])->group(function () {
    Route::get('/order/{incrementId}', [EdfapayController::class, 'getOrderDetails']);
});


