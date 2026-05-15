<?php

namespace Webkul\Pos\Mutations\Outlet;

use Illuminate\Support\Facades\Validator;
use Nuwave\Lighthouse\Support\Contracts\GraphQLContext;
use Webkul\Customer\Repositories\CustomerRepository;
use Webkul\GraphQLAPI\Validators\CustomException;
use Webkul\Pos\Helpers\Pos as PosHelper;
use Webkul\Pos\Repositories\PosCustomerCreditRepository;
use Webkul\Pos\Repositories\PosOrderRepository;
use Webkul\Product\Repositories\ProductRepository;
use Webkul\Sales\Repositories\InvoiceRepository;
use Webkul\Sales\Repositories\OrderRepository;
use Webkul\Sales\Repositories\ShipmentRepository;
use Webkul\Tax\Repositories\TaxCategoryRepository;

class OrderMutation extends PaymentMutation
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(
        protected PosHelper $posHelper,
        protected ProductRepository $productRepository,
        protected CustomerRepository $customerRepository,
        protected OrderRepository $orderRepository,
        protected ShipmentRepository $shipmentRepository,
        protected InvoiceRepository $invoiceRepository,
        protected PosOrderRepository $posOrderRepository,
        protected TaxCategoryRepository $taxCategoryRepository,
        protected PosCustomerCreditRepository $posCustomerCreditRepository
    ) {}

    /**
     * Confirm Payment
     */
    public function syncOrder($root, array $args, GraphQLContext $context)
    {
        $validator = Validator::make($args, [
            'customer_id'  => 'required|integer',
            'payment_mode' => 'required|string|in:pos_cash,pos_card,pos_split',
            'cash_total'   => 'required_if:payment_mode,pos_cash,pos_split|numeric|min:0',
            'card_details' => 'required_if:payment_mode,pos_card,pos_split|string',
            'bank_name'    => 'required_if:payment_mode,pos_card,pos_split|string',
        ]);

        if ($validator->fails()) {
            throw new CustomException($validator->errors()->first());
        }

        $customer = $this->customerRepository->find($args['customer_id']);

        $customerAddress = $customer->addresses->first();

        $outlet = $context->user()->outlet;

        $billingAddress = $customerAddress ?? $outlet;

        $shippableItemsCount = 0;

        $channel = core()->getDefaultChannel();

        $orderData = [
            'cart_id'                  => null,
            'is_guest'                 => 0,
            'customer_id'              => $customer->id,
            'customer_type'            => get_class($customer),
            'customer_email'           => $customer->email,
            'customer_first_name'      => $customer->first_name,
            'customer_last_name'       => $customer->last_name,
            'channel_id'               => $channel->id,
            'channel_name'             => $channel->name,
            'channel_type'             => get_class($channel),
            'total_item_count'         => 0,
            'total_qty_ordered'        => 0,
            'base_currency_code'       => core()->getBaseCurrencyCode(),
            'channel_currency_code'    => $channel->base_currency->code,
            'order_currency_code'      => core()->getCurrentCurrencyCode(),
            'grand_total'              => 0,
            'base_grand_total'         => 0,
            'sub_total'                => 0,
            'sub_total_incl_tax'       => 0,
            'base_sub_total'           => 0,
            'base_sub_total_incl_tax'  => 0,
            'tax_amount'               => 0,
            'base_tax_amount'          => 0,
            'shipping_tax_amount'      => 0,
            'base_shipping_tax_amount' => 0,
            'coupon_code'              => null,
            'applied_cart_rule_ids'    => null,
            'discount_amount'          => 0,
            'base_discount_amount'     => 0,
        ];

        foreach ($args['order_items'] as $key => $orderItem) {
            $children = [];

            $weight = 0;

            $orderData['total_item_count'] += 1;

            $orderData['total_qty_ordered'] += $orderItem['qty_ordered'];

            $product = $this->productRepository->findOrFail($orderItem['product_id']);

            $additional = $orderItem['additional'] ?? [];

            if ($product->isStockable()) {
                $shippableItemsCount += 1;
            }

            if (! in_array($product->type, ['simple', 'virtual'])) {
                $product = $this->productRepository->find($additional['product_id']);

                if ($orderItem['type'] == 'configurable') {
                    if (isset($additional['super_attribute'])) {
                        $additional['super_attribute'] = array_filter($additional['super_attribute']);
                    }

                    $child = $this->productRepository->find($orderItem['additional']['selected_configurable_option']);

                    $price = $child->getTypeInstance()->getFinalPrice($orderItem['qty_ordered']);

                    $weight = floatval($child->weight);

                    $children[] = $child;
                }

                if ($orderItem['type'] == 'bundle') {
                    $children = $product->getTypeInstance()->getCartChildProducts($additional);

                    foreach ($children as $key => $child) {
                        $childProduct = $this->productRepository->find($child['product_id']);
                        $weight += floatval($childProduct->weight);
                        $children[] = $childProduct;
                    }

                    $price = $this->posHelper->getBundleProductPrice($orderItem['additional']);
                }

                if ($orderItem['type'] == 'downloadable') {
                    $price = $product->getTypeInstance()->getFinalPrice($orderItem['qty_ordered']);

                    $weight = floatval($product->weight);
                }
            } else {
                $price = $product->getTypeInstance()->getFinalPrice($orderItem['qty_ordered']);

                $weight = floatval($product->weight);
            }

            if ($product->type == 'downloadable') {
                $price = $price + $this->posHelper->getDownloadLinkPrice($product, $orderItem);
            }

            $taxPercent = $product->tax_category_id
                ? $this->getTaxPercentage($product->tax_category_id, $outlet)
                : 0;

            $productTaxAmount = $taxPercent
                ? (core()->convertPrice($price * $orderItem['qty_ordered']) * $taxPercent) / 100
                : 0;

            $productBaseTaxAmount = $taxPercent ? ($price * $orderItem['qty_ordered'] * $taxPercent) / 100 : 0;

            $orderData['items'][$key] = [
                'product_id'           => $product->id,
                'product_type'         => get_class($product),
                'sku'                  => $product->sku,
                'type'                 => $product->type,
                'name'                 => $product->name,
                'weight'               => $weight,
                'total_weight'         => $weight * $orderItem['qty_ordered'],
                'qty_ordered'          => $orderItem['qty_ordered'],
                'price'                => core()->convertPrice($price),
                'price_incl_tax'       => core()->convertPrice($price + $productTaxAmount),
                'base_price'           => $price,
                'base_price_incl_tax'  => $price + $productBaseTaxAmount,
                'total'                => core()->convertPrice($price * $orderItem['qty_ordered']),
                'total_incl_tax'       => core()->convertPrice(($price * $orderItem['qty_ordered']) + $productTaxAmount),
                'base_total'           => $price * $orderItem['qty_ordered'],
                'tax_percent'          => $taxPercent,
                'tax_amount'           => $productTaxAmount,
                'base_tax_amount'      => $productBaseTaxAmount,
                'tax_category_id'      => null,
                'discount_percent'     => 0,
                'discount_amount'      => 0,
                'base_discount_amount' => 0,
                'additional'           => array_merge($additional, [
                    'locale' => core()->getCurrentLocale()->code,
                ]),
            ];

            if (! empty($children)) {
                foreach ($children as $child) {
                    $order_data['items'][$key]['children'][] = [
                        'product_id'           => $child->id,
                        'product_type'         => get_class($child),
                        'sku'                  => $child->sku,
                        'type'                 => $child->type,
                        'name'                 => $child->name,
                        'weight'               => $child->weight,
                        'total_weight'         => 0,
                        'qty_ordered'          => 0,
                        'price'                => 1.0,
                        'price_incl_tax'       => 1.0,
                        'base_price'           => 0,
                        'base_price_incl_tax'  => 0,
                        'total'                => 0,
                        'total_incl_tax'       => 0,
                        'base_total'           => 0,
                        'tax_percent'          => 0,
                        'tax_amount'           => 0,
                        'base_tax_amount'      => 0,
                        'tax_category_id'      => null,
                        'discount_percent'     => 0,
                        'discount_amount'      => 0,
                        'base_discount_amount' => 0,
                        'additional'           => array_merge($child->additional ?? [], [
                            'locale' => core()->getCurrentLocale()->code,
                        ]),
                    ];
                }
            }

            $orderData['sub_total'] += core()->convertPrice($price * $orderItem['qty_ordered']);

            $orderData['sub_total_incl_tax'] += core()->convertPrice(($price * $orderItem['qty_ordered']) + $productTaxAmount);

            $orderData['base_sub_total'] += $price * $orderItem['qty_ordered'];

            $orderData['base_sub_total_incl_tax'] += ($price * $orderItem['qty_ordered']) + $productBaseTaxAmount;

            $orderData['tax_amount'] += $productTaxAmount;

            $orderData['base_tax_amount'] += $productBaseTaxAmount;

            $orderData['grand_total'] += core()->convertPrice(($price * $orderItem['qty_ordered']) + $productTaxAmount);

            $orderData['base_grand_total'] += ($price * $orderItem['qty_ordered']) + $productBaseTaxAmount;
        }

        $orderData = array_merge($orderData, [
            'payment'          => $this->getPaymentData($args['payment_mode']),
            'billing_address'  => $this->getCustomerAddress($billingAddress, 'cart_billing'),
        ]);

        if ($shippableItemsCount > 0) {
            $orderData = array_merge($orderData, [
                'shipping_method'               => 'pos_free_shipping',
                'shipping_title'                => trans('pos::app.outlet.shipping.title'),
                'shipping_description'          => trans('pos::app.outlet.shipping.description'),
                'shipping_amount'               => 0,
                'base_shipping_amount'          => 0,
                'shipping_amount_incl_tax'      => 0,
                'base_shipping_amount_incl_tax' => 0,
                'shipping_discount_amount'      => 0,
                'base_shipping_discount_amount' => 0,
                'shipping_address'              => $this->getCustomerAddress($customer, 'cart_shipping'),
            ]);
        }

        $order = $this->orderRepository->create($orderData);

        $customerCreditData = $this->prepareCustomerCreditData($order, $args);

        $customerCredit = $this->posCustomerCreditRepository->create($customerCreditData);

        $posOrderData = $this->preparePosOrderData($order, $args);

        $posOrder = $this->posOrderRepository->create($posOrderData);

        /**
         * Create Shipment and Generate Invoice
         */
        $shipmentProduct = [];

        $invoiceProduct = [];

        foreach ($order->items as $key => $item) {
            $shipmentProduct[$item->id][$outlet->inventory_source_id] = $item['qty_ordered'];

            $invoiceProduct[$item->id] = $item['qty_ordered'];
        }

        if (! empty($orderData['shipping_address'])) {
            $shipmentData = [
                'shipment'  => [
                    'carrier_title' => 'POS Store Pick Up',
                    'track_number'  => rand(pow(10, 4), pow(10, 5) - 1),
                    'source'        => $outlet->inventory_source_id,
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

        return [
            'success'      => true,
            'message'      => trans('pos::app.outlet.order.sync-success'),
            'outlet_order' => $posOrder,
        ];
    }

    /**
     * Get Tax Percentage
     *
     * @return float
     */
    public function getTaxPercentage($tax_category_id, $posOutlet)
    {
        $tax_percent = 0;
        $taxCategory = $this->taxCategoryRepository->find($tax_category_id);

        if (! $taxCategory) {
            return $tax_percent;
        }

        $taxRates = $taxCategory->tax_rates()->where([
            ['country', '=', $posOutlet->country],
            ['state', '=', $posOutlet->state],
        ])->orWhere([
            ['country', '=', $posOutlet->country],
            ['state', '=', ''],
        ])->orderBy('tax_rate', 'desc')->get();

        foreach ($taxRates as $rate) {
            $haveTaxRate = false;

            if ($rate->is_zip == 0) {
                if (
                    $rate->zip_code == ''
                    || $rate->zip_code == '*'
                    || $rate->zip_code == $posOutlet->postcode
                ) {
                    $haveTaxRate = true;
                }
            } else {
                if (
                    $posOutlet->postcode >= $rate->zip_from
                    && $posOutlet->postcode <= $rate->zip_to
                ) {
                    $haveTaxRate = true;
                }
            }

            if ($haveTaxRate) {
                $tax_percent = $rate->tax_rate;
                break;
            }
        }

        return $tax_percent;
    }
}
