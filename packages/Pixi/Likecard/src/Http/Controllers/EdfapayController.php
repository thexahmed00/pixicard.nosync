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
use Webkul\Shipping\Facades\Shipping;
use Webkul\Sales\Models\OrderItem;
use Webkul\Marketplace\Models\SellerWallet; // Import the SellerWallet model

class EdfapayController extends Controller
{
    protected $orderRepository;

    public function __construct(OrderRepository $orderRepository)
    {
        $this->orderRepository = $orderRepository;
    }

    public function callback(Request $request)
    {
        // This remains the same, for handling customer redirection after checkout
        $cartId = CheckoutCart::getCart()->id;
        
        return view('shop::checkout.success', [
            'cartId' => $cartId
        ]);
    }

    /**
     * Unified webhook to handle all Edfapay notifications (Checkout and Wallet).
     */
    public function webhook(Request $request)
    {
        $params = $request->all();
        Log::info("Unified Edfapay Webhook received", ['data' => $params]);

        $orderId = $params['order_id'] ?? null;
        if (!$orderId) {
            Log::error('Edfapay Webhook: Missing order_id.');
            return response('Missing order_id', 400);
        }

        // --- ROUTING LOGIC ---
        // CASE 1: Handle regular checkout orders
        if (str_starts_with($orderId, 'PIXI-')) {
            Log::info('Webhook is for a checkout order.', ['order_id' => $orderId]);
            $this->processCheckoutPayment($params);

        // CASE 2: Handle seller wallet recharges
        } elseif (str_starts_with($orderId, 'wallet-recharge-')) {
            Log::info('Webhook is for a seller wallet recharge.', ['order_id' => $orderId]);
            $this->processWalletRecharge($params);

        // CASE 3: Handle unknown formats
        } else {
            Log::error('Edfapay Webhook: Unknown order_id format.', ['order_id' => $orderId]);
        }

        return response('OK', 200);
    }

    /**
     * Processes a successful seller wallet recharge.
     */
    private function processWalletRecharge(array $params)
    {
        $orderId = $params['order_id'];
        
        $sellerId = null;
        if (preg_match('/wallet-recharge-(\d+)-\d+/', $orderId, $matches)) {
            $sellerId = $matches[1];
        }

        if (!$sellerId) {
            Log::error('Wallet Webhook: Could not extract seller_id from order_id.', ['order_id' => $orderId]);
            return;
        }

        $status = $params['status'] ?? '';
        $result = $params['result'] ?? '';

        if ($status === 'SETTLED' && $result === 'SUCCESS') {
            DB::beginTransaction();
            try {
                $existing = SellerWallet::where('transaction_id', $params['trans_id'])->first();
                if ($existing) {
                    Log::warning('Wallet Webhook: Duplicate transaction detected.', ['trans_id' => $params['trans_id']]);
                    DB::rollBack();
                    return;
                }

                SellerWallet::create([
                    'seller_id'      => $sellerId,
                    'amount'         => $params['amount'],
                    'transaction_id' => $params['trans_id'],
                    'status'         => 'success',
                ]);

                DB::commit();
                Log::info('Seller wallet recharged successfully.', ['seller_id' => $sellerId, 'amount' => $params['amount']]);
            } catch (\Exception $e) {
                DB::rollBack();
                Log::error('Wallet Webhook: Failed to save wallet transaction.', ['error' => $e->getMessage()]);
            }
        } else {
            Log::error('Seller wallet recharge failed or was declined.', ['data' => $params]);
        }
    }

    /**
     * Processes a regular checkout payment (original logic).
     */
    private function processCheckoutPayment(array $params)
    {
        $orderIdFromWebhook = $params['order_id'];

        if (preg_match('/PIXI-(\d+)-\d+/', $orderIdFromWebhook, $matches)) {
            $cartId = $matches[1];
        } else {
            $cartId = $orderIdFromWebhook;
        }
        
        $existingOrder = Order::where('increment_id', $orderIdFromWebhook)->first();
        if ($existingOrder) {
            Log::info('Order already exists', ['order_increment_id' => $orderIdFromWebhook]);
            $this->updateOrderStatus($existingOrder, $params);
            return;
        }
        
        $cart = Cart::with(['items.product', 'billing_address', 'shipping_address', 'customer'])->find($cartId);
        if (!$cart) {
            Log::error('Cart not found for checkout payment.', ['order_id' => $orderIdFromWebhook]);
            return;
        }
        
        $status = $params['status'] ?? '';
        $result = $params['result'] ?? '';
        
        switch ($status) {
            case 'SETTLED':
                if ($result === 'SUCCESS') {
                    try {
                        $order = $this->createOrderFromCart($cart, $params);
                        Log::info('Payment successful, order created', ['order_id' => $order->id]);
                    } catch (\Exception $e) {
                        Log::error('Failed to create order', ['cart_id' => $cartId, 'error' => $e->getMessage()]);
                    }
                } else {
                    Log::error('Payment failed', ['cart_id' => $cartId, 'status' => $status, 'result' => $result]);
                    $this->updateCartStatus($cart, 'failed', $params);
                }
                break;
            case 'PENDING':
                $this->updateCartStatus($cart, 'pending', $params);
                break;
            case 'DECLINED':
            case 'FAILED':
                $this->updateCartStatus($cart, 'failed', $params);
                break;
        }
    }

    // --- ALL HELPER METHODS BELOW REMAIN UNCHANGED ---

    private function createOrderFromCart($cart, $paymentData)
    {
        DB::beginTransaction();

        try {
            $shippingRates = Shipping::getGroupedAllShippingRates();
            $shippingMethodCode = $cart->shipping_method;
            $shippingTitle = '';
            $shippingDescription = '';

            if ($shippingMethodCode && !empty($shippingRates)) {
                foreach ($shippingRates as $carrierRates) {
                    if (isset($carrierRates['rates'][$shippingMethodCode])) {
                        $rate = $carrierRates['rates'][$shippingMethodCode];
                        $shippingTitle = $rate['carrier_title'] . ' - ' . $rate['method_title'];
                        $shippingDescription = $rate['method_description'];
                        break;
                    }
                }
            }

            $orderCurrency = $paymentData['order_currency'] ?? 'SAR';
            $order = Order::create([
                'increment_id' => $paymentData['order_id'] ?? 'PIXI-' . $cart->id . '-' . time(),
                'status' => 'processing',
                'channel_name' => $cart->channel->name,
                'is_guest' => $cart->is_guest,
                'customer_email' => $cart->customer_email,
                'customer_first_name' => $cart->customer_first_name,
                'customer_last_name' => $cart->customer_last_name,
                'shipping_method' => $shippingMethodCode,
                'shipping_title' => $shippingTitle,
                'shipping_description' => $shippingDescription,
                'coupon_code' => $cart->coupon_code,
                'is_gift' => $cart->is_gift,
                'total_item_count' => $cart->items->count(),
                'total_qty_ordered' => $cart->items->sum('quantity'),
                'base_currency_code' => $orderCurrency,
                'channel_currency_code' => $orderCurrency,
                'order_currency_code' => $orderCurrency,
                'grand_total' => $cart->grand_total,
                'base_grand_total' => $cart->base_grand_total,
                'sub_total' => $cart->sub_total,
                'base_sub_total' => $cart->base_sub_total,
                'tax_amount' => $cart->tax_total,
                'base_tax_amount' => $cart->base_tax_total,
                'discount_amount' => $cart->discount_amount,
                'base_discount_amount' => $cart->base_discount_amount,
                'shipping_amount' => $cart->shipping_amount,
                'base_shipping_amount' => $cart->base_shipping_amount,
                'sub_total_incl_tax' => $cart->sub_total_incl_tax,
                'base_sub_total_incl_tax' => $cart->base_sub_total_incl_tax,
                'shipping_amount_incl_tax' => $cart->shipping_amount_incl_tax,
                'base_shipping_amount_incl_tax' => $cart->base_shipping_amount_incl_tax,
                'customer_id' => $cart->customer_id,
                'customer_type' => \Webkul\Customer\Models\Customer::class,
                'channel_id' => $cart->channel_id,
                'channel_type' => \Webkul\Core\Models\Channel::class,
                'cart_id' => $cart->id,
                'applied_cart_rule_ids' => $cart->applied_cart_rule_ids,
            ]);

            $order->payment()->create([
                'method' => 'edfapay',
                'method_title' => 'EdfaPay',
                'additional' => $paymentData,
            ]);

            if ($cart->billing_address) {
                $order->addresses()->create($cart->billing_address->toArray());
            }
            if ($cart->shipping_address) {
                $order->addresses()->create($cart->shipping_address->toArray());
            }

            foreach ($cart->items as $item) {
                $orderItemData = $item->toArray();
                $orderItemData['qty_ordered'] = $item->quantity;
                $orderItemData['qty_to_ship'] = $item->quantity;
                $orderItemData['qty_to_invoice'] = $item->quantity;
                $orderItemData['qty_canceled'] = 0;
                $orderItemData['qty_refunded'] = 0;
                $orderItemData['product_type'] = \Webkul\Product\Models\Product::class;
                unset($orderItemData['product']);
                $order->items()->create($orderItemData);
            }

            $cart->update(['is_active' => false]);
            DB::commit();
            event('sales.order.save.after', $order);
            event('sales.order.update-status.after', $order);
            
            return $order;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Final manual order creation failed', [
                'cart_id' => $cart->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }

    public function checkStatus(Request $request)
    {
        $cartId = $request->get('cart_id');
        if (!$cartId) {
            return response()->json(['status' => 'error', 'message' => 'Cart ID required']);
        }

        $order = Order::where('cart_id', $cartId)->first();
        if ($order) {
            $sessionCart = CheckoutCart::getCart();
            if ($sessionCart && $sessionCart->id == $cartId) {
                CheckoutCart::deActivateCart();
            }
            return response()->json([
                'status' => 'success',
                'redirect_url' => route('edfapay.success', ['orderId' => $order->id])
            ]);
        }
        
        $cart = Cart::find($cartId);
        if ($cart && $cart->payment_status === 'failed') {
            return response()->json([
                'status' => 'failed',
                'redirect_url' => route('shop.checkout.cart.index')
            ]);
        }
        
        return response()->json(['status' => 'processing']);
    }

    private function updateCartStatus($cart, $status, $paymentData)
    {
        $updateData = ['payment_status' => $status];
        if (isset($paymentData['trans_id'])) {
            $updateData['payment_transaction_id'] = $paymentData['trans_id'];
        }
        $cart->update($updateData);
    }

    private function updateOrderStatus($order, $paymentData)
    {
        if (isset($paymentData['status']) && $paymentData['status'] === 'SETTLED') {
            $order->update(['status' => 'processing']);
            Log::info('Existing order status updated', ['order_id' => $order->id]);
        }
    }

    public function success($orderId)
    {
        $order = $this->orderRepository->findOrFail($orderId);
        return view('likecard::checkout.success-final', compact('order'));
    }

    public function getOrderDetails($incrementId)
    {
        $order = Order::where('increment_id', $incrementId)->first();
        if (!$order) {
            return response()->json(['success' => false, 'message' => 'Order not found.'], 404);
        }
        $orderData = $order->toArray();
        $orderItems = OrderItem::where('order_id', $order->id)->get();
        $orderData['items'] = $orderItems->toArray();
        return response()->json(['success' => true, 'data' => $orderData]);
    }
}
