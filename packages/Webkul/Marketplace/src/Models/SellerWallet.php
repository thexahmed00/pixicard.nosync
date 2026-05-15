<?php

namespace Webkul\Marketplace\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SellerWallet extends Model
{
    protected $fillable = [
        'seller_id',
        'vendor_id',
        'amount',
        'order_id',
        'transaction_id',
        'type',
        'status'
    ];

    public function seller()
    {
        return $this->belongsTo(\Webkul\Marketplace\Models\Seller::class, 'seller_id');
    }

    public function order()
    {
        return $this->belongsTo(\Webkul\Sales\Models\Order::class, 'order_id');
    }
}
