<?php

namespace Webkul\Pos\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Webkul\Pos\Contracts\PosProductBarcode as PosProductBarcodeContract;

class PosProductBarcode extends Model implements PosProductBarcodeContract
{
    /**
     * The table associated with the model.
     *
     * @var array
     */
    protected $table = 'pos_product_barcode';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'id',
        'barcode',
        'product_id',
    ];

    /**
     * Get Product Barcode associated with the PosProductBarcode
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(PosOutletProductProxy::modelClass());
    }
}
