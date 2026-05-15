<?php

namespace Webkul\Pos\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;
use Webkul\Pos\Contracts\PosReceipt as PosReceiptContract;

class PosReceipt extends Model implements PosReceiptContract
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'title',
        'status',
        'display_outlet_name',
        'show_order_barcode',
        'show_print_confirmation',
        'display_date',
        'display_order_id',
        'order_id_label',
        'display_customer_name',
        'display_sub_total',
        'sub_total_label',
        'display_discount',
        'discount_label',
        'display_tax',
        'tax_label',
        'display_credit_amount',
        'credit_amount_label',
        'display_change_amount',
        'credit_change_label',
        'display_cashier_name',
        'cashier_label',
        'display_outlet_address',
        'grand_total_label',
        'display_logo',
        'logo',
        'logo_width',
        'logo_height',
        'logo_alt',
        'header_content',
        'footer_content',
    ];

    /**
     * Get image url for the receipt logo.
     */
    public function getLogoUrlAttribute()
    {
        if (! $this->logo) {
            return;
        }

        return Storage::url($this->logo);
    }

    /**
     * Get the outlet that owns the receipt.
     */
    public function outlet(): BelongsTo
    {
        return $this->belongsTo(PosOutletProxy::modelClass(), 'id', 'receipt_id');
    }
}
