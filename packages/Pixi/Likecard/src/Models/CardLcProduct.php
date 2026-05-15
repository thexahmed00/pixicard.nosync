<?php

namespace Pixi\Likecard\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Http\Request;

use Illuminate\Support\Facades\DB;

class CardLcProduct extends Model
{
    use HasFactory;

    protected $table = 'card_lc_products';

    protected $primaryKey = 'productId';

    protected $fillable = [
        'categoryId',
        'productName',
        'productPrice',
        'productPriceWithoutVat',
        'productImage',
        'productCurrency',
        'optionalFieldsExist',
        'productOptionalFields',
        'sellPrice',
        'sellPriceWithoutVat',
        'available',
        'status',
        'vatAmount',
        'vatPercentage',
        'productImageWhiteLabel',
        'name_ar',
        'name_ur',
        'name_fr',
        'translation'
    ];

    protected $casts = [
        'categoryId'             => 'integer',
        'productPrice'           => 'decimal:2', // Cast to decimal with 2 places
        'productPriceWithoutVat' => 'decimal:2',
        'sellPrice'              => 'decimal:2',
        'sellPriceWithoutVat'    => 'decimal:2',
        'vatAmount'              => 'decimal:2',
        'vatPercentage'          => 'decimal:2',
        'optionalFieldsExist'    => 'boolean', // Cast tinyint(1) to boolean
        'available'              => 'boolean',
        'status'              => 'boolean',
        'productOptionalFields'  => 'string', //or 'array' if it stores json.
        'productImage'          => 'string',
        'productImageWhiteLabel' => 'string',
        'productCurrency'        => 'string',
        'productName'         => 'string'

    ];

    /**
     * Get the category that the product belongs to.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(CardLcCategory::class, 'categoryId', 'id');
    }

    /**
     * Fetches products from the 'card_lc_products' table with a consistent set of columns.
     *
     * @param int $perPage
     * @return \Illuminate\Pagination\LengthAwarePaginator
     */
    public static function get_filtered_products(
        $id=null,
        $search=null,
        $name=null,
        $cat=null,
        $cat_id=null,
        $main_cat_id=null,
        $SKU=null,
        $status=true,
        $active_cat=true,
        $count=20
    )
    {
        return DB::table('card_lc_products')
            ->select(
                'card_lc_products.productId AS id',
                'card_lc_products.productName AS name',
                DB::raw('COALESCE(clc.categoryName, "") AS cat'),  // Use COALESCE
                'card_lc_products.categoryId AS cat_id',
                'mc.id AS main_cat_id',
                'card_lc_products.productPrice AS price',
                DB::raw('NULL AS quantity'),
                DB::raw('NULL AS SKU'),
                DB::raw('vatAmount AS tax'),
                DB::raw('productImage AS image'),
                DB::raw('NULL AS description'),
                DB::raw('NULL AS detail'),
                DB::raw('NULL AS specification'),
            )
            ->leftJoin('card_lc_categories AS clc', 'card_lc_products.categoryId', '=', 'clc.id')
            ->leftJoin('main_categories AS mc', 'clc.main_cat', '=', 'mc.id')
            ->where('card_lc_products.available', $status)    
            ->when($id, function ($query) use ($id) {
                return $query->where('card_lc_products.productId', $id);
            })
            ->when($search, function ($query) use ($search) {
                $searchTerms = preg_split('/[^a-zA-Z0-9]+/', $search); // Split by non-alphanumeric characters
            
                return $query->where(function ($q) use ($searchTerms) {
                    foreach ($searchTerms as $term) {
                        if (!empty($term)) { // Skip empty terms
                            $q->orWhere('card_lc_products.productName', 'like', "%$term%")
                              ->orWhere('clc.categoryName', 'like', "%$term%")
                              ->orWhere('card_lc_products.productId', 'like', "%$term%");
                        }
                    }
                });
            })            
            ->when($name, function ($query) use ($name) {
                return $query->where('card_lc_products.productName', 'like', "%$name%");
            })
            ->when($cat, function ($query) use ($cat) {
                return $query->where('clc.categoryName', 'like', "%$cat%");
            })
            ->when($cat_id, function ($query) use ($cat_id) {
                return $query->where('card_lc_products.categoryId', '=', $cat_id);
            })
            ->when($main_cat_id, function ($query) use ($main_cat_id) {
                return $query->where('mc.id', $main_cat_id);
            })
            ->when($SKU, function ($query) use ($SKU) {
                return $query->where('products.SKU', '=', $SKU);
            })
            ->when($status, function ($query) use ($status) {
                return $query->where('card_lc_products.status', $status);
            })
            ->when($active_cat, function ($query) use ($active_cat) {
                return $query->where('clc.status', $active_cat);
            })
            ->orderBy('card_lc_products.productName')

            ->paginate($count);
    }
}
