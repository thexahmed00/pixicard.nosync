<?php

namespace Pixi\Likecard\Http\Controllers\Api;

use Webkul\Shop\Http\Controllers\API\CategoryController as BaseCategoryController;
use Webkul\Product\Repositories\ProductFlatRepository;

class CategoryController extends BaseCategoryController
{
    /**
     * Get category's min price.
     */
    public function getMinPrice(ProductFlatRepository $productFlatRepository, $categoryId)
    {
        $minPrice = $productFlatRepository->getCategoryProductMinPrice($categoryId);

        return response()->json([
            'data' => [
                'min_price' => core()->convertPrice($minPrice),
            ],
        ]);
    }
}
