<?php

namespace Webkul\Pos\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Facades\Storage;
use Webkul\Pos\Contracts\PosOrder as PosOrderContract;
use Webkul\Sales\Models\OrderProxy;

class PosOrder extends Model implements PosOrderContract
{
    /**
     * The table associated with the model.
     *
     * @var array
     */
    protected $table = 'pos_order';

    /**
     * The attributes that aren't mass assignable.
     *
     * @var array
     */
    protected $guarded = ['id'];

    /**
     * Get image url for the category image.
     *
     * @return string
     */
    public function getBarcodeUrlAttribute()
    {
        if (! $this->order_barcode_path) {
            return;
        }

        return Storage::url($this->order_barcode_path);
    }

    /**
     * Get the order associated with the PosOrder
     */
    public function order(): HasOne
    {
        return $this->hasOne(OrderProxy::modelClass(), 'id', 'order_id');
    }

    /**
     * Get the outlet associated with the PosOrder
     */
    public function outlet(): BelongsTo
    {
        return $this->belongsTo(PosOutletProxy::modelClass(), 'outlet_id');
    }

    /**
     * Get the customer credit associated with the PosOrder
     */
    public function customerCredit(): HasOne
    {
        return $this->hasOne(PosCustomerCreditProxy::modelClass(), 'order_id', 'order_id');
    }
}
