<?php

namespace Webkul\Pos\Queries\Products;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Nuwave\Lighthouse\Support\Contracts\GraphQLContext;
use Webkul\Pos\Repositories\PosOutletProductRepository;
use Webkul\Pos\Repositories\PosProductRequestRepository;

class ProductQuery extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(
        protected PosOutletProductRepository $posOutletProductRepository,
        protected PosProductRequestRepository $posProductRequestRepository,
    ) {
        Auth::setDefaultDriver('pos_user');
    }

    /**
     * Get the products for the home page
     */
    public function getLowStockProducts($rootValue, array $args, GraphQLContext $context)
    {
        $agent = Auth::user();

        $params = [
            'low_stock_qty'       => $agent->outlet->low_stock_qty ?? 10,
            'outlet_id'           => $agent->outlet_id,
            'inventory_source_id' => $agent->outlet->inventory_source_id,
        ];

        $products = $this->posOutletProductRepository->getLowStockProducts($params);

        $products->map(function ($product) {
            $product->price_html = $product->getTypeInstance()->getPriceHtml();
        });

        return $products;
    }

    /**
     * Get the requested products
     */
    public function getRequestedProducts($rootValue, array $args, GraphQLContext $context)
    {
        $params = array_merge($args, [
            'user_id' => Auth::user()->id,
        ]);

        return $this->posProductRequestRepository->getAll($params);
    }
}
