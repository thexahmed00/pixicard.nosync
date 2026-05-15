<?php

namespace Webkul\Product\Repositories;

use Webkul\Core\Eloquent\Repository;

class ProductFlatRepository extends Repository
{
    /**
     * Specify model.
     */
    public function model(): string
    {
        return 'Webkul\Product\Contracts\ProductFlat';
    }

     public function getCategoryProductMinPrice(int $categoryId)
    {
        return $this->model
            ->leftJoin('product_categories', 'product_flat.product_id', '=', 'product_categories.product_id')
            ->where('product_categories.category_id', $categoryId)
            ->where('product_flat.status', 1)
            ->whereNotNull('product_flat.price')
            ->where('product_flat.price', '>', 0)
            ->where('product_flat.channel', core()->getCurrentChannelCode())
            ->where('product_flat.locale', core()->getRequestedLocaleCode())
            ->min('price');
    }
}
