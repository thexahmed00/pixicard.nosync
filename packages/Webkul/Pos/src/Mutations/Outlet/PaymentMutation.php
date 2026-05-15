<?php

namespace Webkul\Pos\Mutations\Outlet;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use Nuwave\Lighthouse\Support\Contracts\GraphQLContext;
use Webkul\Checkout\Facades\Cart;
use Webkul\Checkout\Repositories\CartRepository;
use Webkul\Customer\Repositories\CustomerRepository;
use Webkul\GraphQLAPI\Validators\CustomException;
use Webkul\Pos\Repositories\PosCustomerCreditRepository;
use Webkul\Pos\Repositories\PosOrderRepository;
use Webkul\Pos\Transformers\OrderResource;
use Webkul\Sales\Repositories\InvoiceRepository;
use Webkul\Sales\Repositories\OrderRepository;
use Webkul\Sales\Repositories\ShipmentRepository;

class PaymentMutation extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(
        protected CartRepository $cartRepository,
        protected CustomerRepository $customerRepository,
        protected OrderRepository $orderRepository,
        protected ShipmentRepository $shipmentRepository,
        protected InvoiceRepository $invoiceRepository,
        protected PosOrderRepository $posOrderRepository,
        protected PosCustomerCreditRepository $posCustomerCreditRepository
    ) {}

    /**
     * Confirm Payment
     */
    public function confirmPayment($root, array $args, GraphQLContext $context): array
    {
        $validator = Validator::make($args, [
            'cart_id'      => 'required|integer',
            'payment_mode' => 'required|string|in:pos_cash,pos_card,pos_split',
            'cash_total'   => 'required_if:payment_mode,pos_cash,pos_split|numeric|min:0',
            'card_details' => 'required_if:payment_mode,pos_card,pos_split|string',
            'bank_name'    => 'required_if:payment_mode,pos_card,pos_split|string',
        ]);

        if ($validator->fails()) {
            throw new CustomException($validator->errors()->first());
        }

        $cart = $this->cartRepository->find($args['cart_id']);

        if (! $cart) {
            throw new CustomException(trans('pos::app.outlet.payment.no-items'));
        }

        Cart::setCart($cart);

        Cart::collectTotals();

        $cart = Cart::getCart();

        if (! $cart?->items()->count()) {
            return [
                'success' => false,
                'message' => trans('pos::app.outlet.payment.no-items'),
                'order'   => null,
            ];
        }

        $data = (new OrderResource($cart))->jsonSerialize();

        $data['payment'] = $this->getPaymentData($args['payment_mode']);

        $data['billing_address'] = $this->getCustomerAddress($cart->customer, 'cart_billing');

        if ($cart->haveStockableItems()) {
            $data['shipping_address'] = $this->getCustomerAddress($cart->customer, 'cart_shipping');
        }

        $order = $this->orderRepository->create($data);

        $customerCreditData = $this->prepareCustomerCreditData($order, $args);

        $customerCredit = $this->posCustomerCreditRepository->create($customerCreditData);

        $posOrderData = $this->preparePosOrderData($order, $args);

        $posOrder = $this->posOrderRepository->create($posOrderData);

        /**
         * Create Shipment and Generate Invoice
         */
        $shipmentProduct = [];

        $invoiceProduct = [];

        $posOutlet = auth()->guard('pos_user')->user()->outlet;

        foreach ($order->items as $key => $item) {
            $shipmentProduct[$item->id][$posOutlet->inventory_source_id] = $item['qty_ordered'];

            $invoiceProduct[$item->id] = $item['qty_ordered'];
        }

        if (! empty($data['shipping_address'])) {
            $shipmentData = [
                'shipment'  => [
                    'carrier_title' => 'POS Store Pick Up',
                    'track_number'  => rand(pow(10, 4), pow(10, 5) - 1),
                    'source'        => $posOutlet->inventory_source_id,
                    'items'         => $shipmentProduct,
                    'email_sent'    => 1,
                ],
                'order_id' => $posOrder->order_id,
            ];

            $this->shipmentRepository->create($shipmentData);
        }

        $invoiceData = [
            'invoice' => [
                'items'      => $invoiceProduct,
                'email_sent' => 1,
            ],
            'order_id' => $posOrder->order_id,
        ];

        $this->invoiceRepository->create($invoiceData);

        Cart::removeCart($cart);

        return [
            'success'         => true,
            'message'         => trans('pos::app.outlet.payment.success'),
            'outlet_order'    => $posOrder,
            'customer_credit' => $customerCredit,
        ];
    }

    /**
     * Get payment data
     */
    protected function getPaymentData(string $paymentMode): array
    {
        $data = [];

        if ($paymentMode == 'pos_cash') {
            $data = [
                'method'       => 'pos_cash',
                'method_title' => trans('pos::app.outlet.payment.options.cash.title'),
            ];
        } elseif ($paymentMode == 'pos_card') {
            $data = [
                'method'       => 'pos_card',
                'method_title' => trans('pos::app.outlet.payment.options.card.title'),
            ];
        } elseif ($paymentMode == 'pos_split') {
            $data = [
                'method'       => 'pos_split',
                'method_title' => trans('pos::app.outlet.payment.options.split.title'),
            ];
        }

        return $data;
    }

    /**
     * Get customer address
     */
    protected function getCustomerAddress(object $customer, string $type): array
    {
        $posOutlet = auth()->guard('pos_user')->user()->outlet;

        $address = [
            'address_type' => $type,
            'first_name'   => $customer->first_name,
            'last_name'    => $customer->last_name,
            'gender'       => $customer->gender,
            'company_name' => $customer->company_name,
            'email'        => $customer->email,
            'phone'        => $customer->phone ?? $posOutlet->phone,
            'vat_id'       => $customer->vat_id,
            'address'      => $posOutlet->address,
            'city'         => $posOutlet->city,
            'state'        => $posOutlet->state,
            'country'      => $posOutlet->country,
            'postcode'     => $posOutlet->postcode,
        ];

        return $address;
    }

    /**
     * Prepare customer credit data
     */
    protected function prepareCustomerCreditData(object $order, array $data): array
    {
        $customerCreditData = [
            'order_id'     => $order->id,
            'customer_id'  => $order->customer_id,
            'payment_mode' => $data['payment_mode'],
            'used_status'  => false,
        ];

        if ($data['payment_mode'] == 'pos_cash') {
            $changeAmount = $data['cash_total'] - $order->grand_total;

            $customerCreditData = array_merge($customerCreditData, [
                'tendered_amount'      => $data['cash_total'],
                'base_tendered_amount' => core()->convertToBasePrice($data['cash_total']),
                'change_amount'        => $changeAmount,
                'base_change_amount'   => core()->convertToBasePrice($changeAmount),
            ]);
        }

        if (
            $data['payment_mode'] == 'pos_card'
            || $data['payment_mode'] == 'pos_split'
        ) {
            $customerCreditData = array_merge($customerCreditData, [
                'tendered_amount'      => $order->grand_total,
                'base_tendered_amount' => core()->convertToBasePrice($order->grand_total),
                'change_amount'        => 0,
                'base_change_amount'   => 0,
            ]);
        }

        return $customerCreditData;
    }

    /**
     * Prepare pos order data
     */
    protected function preparePosOrderData(object $order, array $data): array
    {
        $posUser = auth()->guard('pos_user')->user();

        $posOrderData = [
            'order_id'             => $order->id,
            'order_ref_id'         => $posUser->username,
            'outlet_id'            => $posUser->outlet->id,
            'user_id'              => $posUser->id,
            'order_note'           => $data['order_note'] ?? '',
            'discount_amount'      => $order->discount_amount,
            'base_discount_amount' => $order->base_discount_amount,
            'order_currency'       => $order->order_currency_code,
        ];

        if (core()->getConfigData('pos.settings.barcode.show_on_receipt')) {
            $posOrderData['order_barcode_path'] = $this->posOrderRepository->generateOrderBarcode($order->id);
        }

        if ($data['payment_mode'] == 'pos_cash') {
            $posOrderData['cash_total'] = $order->grand_total;
        }

        if ($data['payment_mode'] == 'pos_card') {
            $posOrderData['card_total'] = $order->grand_total;
        }

        if ($data['payment_mode'] == 'pos_split') {
            $posOrderData = array_merge($posOrderData, [
                'cash_total' => $data['cash_total'],
                'card_total' => $order->grand_total - $data['cash_total'],
            ]);
        }

        if (
            $data['payment_mode'] == 'pos_card'
            || $data['payment_mode'] == 'pos_split'
        ) {
            $posOrderData = array_merge($posOrderData, [
                'bank_name'    => $data['bank_name'],
                'card_details' => $data['card_details'],
            ]);
        }

        return $posOrderData;
    }
}
