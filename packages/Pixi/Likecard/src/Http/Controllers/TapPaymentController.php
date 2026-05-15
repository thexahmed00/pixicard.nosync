<?php

namespace Pixi\Likecard\Http\Controllers;

use Illuminate\Http\Request;
use Webkul\Sales\Models\Order;
use Webkul\Checkout\Models\Cart;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Webkul\Sales\Repositories\OrderRepository;
use Webkul\Checkout\Facades\Cart as CheckoutCart;
use Webkul\Marketplace\Models\SellerWallet;

class TapPaymentController extends Controller
{
    protected $orderRepository;

    public function __construct(OrderRepository $orderRepository)
    {
        $this->orderRepository = $orderRepository;
    }

    /**
     * Webhook to handle Tap notifications.
     */
    public function webhook(Request $request)
    {
        $params = $request->all();

        // Log the full payload for debugging
        Log::info("Tap Payment Webhook received", ['data' => $params]);

        // 1. Extract Order ID which corresponds to SellerWallet ID
        $walletId = $params['order_id'] ?? null;
        if (!$walletId) {
            Log::error('Tap Webhook: Missing order_id (Wallet ID).');
            return response()->json(['message' => 'Missing order_id'], 400);
        }

        // 2. Find the Wallet Transaction
        $walletTransaction = SellerWallet::find($walletId);
        if (!$walletTransaction) {
            Log::error("Tap Webhook: Wallet transaction not found for ID: {$walletId}");
            return response()->json(['message' => 'Wallet transaction not found'], 404);
        }

        // 3. Extract Status and Transaction/Ref Number
        $status = $params['status'] ?? '';
        $transactionNumber = $params['transaction_number'] ?? null;
        $amount = $params['amount'] ?? 0;

        // 4. Update Wallet Status
        try {
            DB::beginTransaction();

            if ($status === 'APPROVED') {
                // Determine previous status to avoid duplicate processing if needed
                if ($walletTransaction->status !== 'success') {
                    $walletTransaction->status = 'success';
                    $walletTransaction->transaction_id = $transactionNumber;

                    // Verify amount matches if necessary, or just log mismatch
                    if (floatval($walletTransaction->amount) != floatval($amount)) {
                        Log::warning("Tap Webhook: Amount mismatch for Wallet ID {$walletId}. Expected: {$walletTransaction->amount}, Received: {$amount}");
                    }

                    $walletTransaction->save();
                    Log::info("Tap Webhook: Wallet {$walletId} marked as SUCCESS.");
                } else {
                    Log::info("Tap Webhook: Wallet {$walletId} is already successful. duplicate callback?");
                }

            } elseif ($status === 'DECLINED') {
                if ($walletTransaction->status !== 'failed') {
                    $walletTransaction->status = 'failed';
                    $walletTransaction->transaction_id = $transactionNumber; // Save ref anyway
                    $walletTransaction->save();
                    Log::info("Tap Webhook: Wallet {$walletId} marked as FAILED.");
                }
            } else {
                Log::warning("Tap Webhook: Unknown status '{$status}' for Wallet {$walletId}.");
            }

            DB::commit();
            return response()->json(['message' => 'OK']);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Tap Webhook Error: " . $e->getMessage());
            return response()->json(['message' => 'Internal Server Error'], 500);
        }
    }
}

