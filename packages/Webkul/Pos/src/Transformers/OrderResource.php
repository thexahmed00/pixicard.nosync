<?php

namespace Webkul\Pos\Transformers;

use Illuminate\Http\Resources\Json\JsonResource;
use Webkul\Sales\Transformers\OrderItemResource;

class OrderResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request
     * @return array
     */
    public function toArray($request)
    {
        $shippingInformation = [];

        if ($this->haveStockableItems()) {
            $shippingInformation = [
                'shipping_method'               => 'pos_free_shipping',
                'shipping_title'                => trans('pos::app.outlet.shipping.title'),
                'shipping_description'          => trans('pos::app.outlet.shipping.description'),
                'shipping_amount'               => 0,
                'base_shipping_amount'          => 0,
                'shipping_amount_incl_tax'      => 0,
                'base_shipping_amount_incl_tax' => 0,
                'shipping_discount_amount'      => 0,
                'base_shipping_discount_amount' => 0,
            ];
        }

        return [
            'cart_id'                  => $this->id,
            'is_guest'                 => $this->is_guest,
            'customer_id'              => $this->customer_id,
            'customer_type'            => $this->customer ? get_class($this->customer) : null,
            'customer_email'           => $this->customer_email,
            'customer_first_name'      => $this->customer_first_name,
            'customer_last_name'       => $this->customer_last_name,
            'channel_id'               => $this->channel_id,
            'channel_name'             => $this->channel->name,
            'channel_type'             => get_class($this->channel),
            'total_item_count'         => $this->items_count,
            'total_qty_ordered'        => $this->items_qty,
            'base_currency_code'       => $this->base_currency_code,
            'channel_currency_code'    => $this->channel_currency_code,
            'order_currency_code'      => $this->cart_currency_code,
            'grand_total'              => $this->grand_total,
            'base_grand_total'         => $this->base_grand_total,
            'sub_total'                => $this->sub_total,
            'sub_total_incl_tax'       => $this->sub_total_incl_tax,
            'base_sub_total'           => $this->base_sub_total,
            'base_sub_total_incl_tax'  => $this->base_sub_total_incl_tax,
            'tax_amount'               => $this->tax_total,
            'base_tax_amount'          => $this->base_tax_total,
            'shipping_tax_amount'      => $this->selected_shipping_rate?->tax_amount ?? 0,
            'base_shipping_tax_amount' => $this->selected_shipping_rate?->base_tax_amount ?? 0,
            'coupon_code'              => $this->coupon_code,
            'applied_cart_rule_ids'    => $this->applied_cart_rule_ids,
            'discount_amount'          => $this->discount_amount,
            'base_discount_amount'     => $this->base_discount_amount,
            $this->mergeWhen($this->haveStockableItems(), $shippingInformation),
            'items'                    => OrderItemResource::collection($this->items)->jsonSerialize(),
        ];
    }
}
