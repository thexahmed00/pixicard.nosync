<?php

namespace Webkul\Pos\Models;

use Illuminate\Database\Eloquent\Model;
use Webkul\Pos\Contracts\PosProductCategories as PosProductCategoriesContract;

class PosProductCategories extends Model implements PosProductCategoriesContract
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'product_categories';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'product_id',
        'category_id',
    ];
}
