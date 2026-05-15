<?php

namespace Webkul\Pos\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Webkul\Pos\Contracts\PosProductRequest as PosProductRequestContract;
use Webkul\Product\Models\ProductProxy;

class PosProductRequest extends Model implements PosProductRequestContract
{
    /**
     * The table associated with the model.
     *
     * @var array
     */
    protected $table = 'pos_product_request';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'user_id',
        'product_id',
        'options',
        'requested_quantity',
        'supplier_id',
        'comment',
        'send_status',
        'request_status',
    ];

    /**
     * Get users associated with the PosProductRequest
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(PosUserProxy::modelClass());
    }

    /**
     * Get product associated with the PosProductRequest
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(ProductProxy::modelClass());
    }
}
