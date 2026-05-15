<?php

namespace Webkul\Marketplace\Http\Controllers\Shop;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Http\Response;
use Webkul\Checkout\Facades\Cart;
use Webkul\Marketplace\Helpers\Cart as CartHelper;
use Webkul\Marketplace\Repositories\ProductRepository;
use Webkul\Shop\Http\Resources\CartResource;
use Webkul\Marketplace\Repositories\SellerRepository;

class CartController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(
        protected ProductRepository $productRepository,
        protected SellerRepository $sellerRepository,
        protected CartHelper $cartHelper
    ) {}

    /**
     * Function for guests user to add the product in the cart.
     */
    public function store(Request $request)
    {
        $request->validate([
            'id'         => ['required_without:product_id'],
            'product_id' => ['required_without:id'],
        ]);

        
        $id = $request->input('id');

        $productId = $request->input('product_id');
        $seller_id = $request->input('seller_id');

        $product = $id
            ? $this->productRepository->find($id)
            : $this->productRepository->searchProductsById($productId);

        $seller = $this->sellerRepository->find($seller_id);

        try {

            // if ($this->productRepository->isAvailableForSale($product) === false) {
            //     return new JsonResponse([
            //         'message' => trans('marketplace::app.shop.checkout.cart.product-not-available'),
            //     ], 404);
            // }

            $data =  [
                'product_id'  => $product->id,
                'seller_info' => [
                    'product_id' => $product->id,
                    'seller_id'  => $seller_id,
                    'is_owner'   => 0,
                ],
                'attributes' => [
                    'seller_info' => [
                        'attribute_name' => trans('marketplace::app.shop.checkout.cart.sold-by'),
                        'option_label'   => $seller->name ?? '',
                    ],
                ],
            ];

            if ($product?->type === 'configurable') {
                $configurableProductData = [];

                foreach ($product->attribute_values as $attribute) {
                    $option = $attribute->options()
                        ->where('id', $product->{$attribute->code})
                        ->first();

                    if ($option) {
                        $configurableProductData[$attribute->code] = [
                            'attribute_name' => $attribute->name ?? $attribute->admin_name,
                            'option_id'      => $option->id,
                            'option_label'   => $option->label ?? $option->admin_name,
                        ];
                    }
                }

                $data['attributes'] = array_merge($data['attributes'], $configurableProductData);
            }

            $request->merge($data);

            $cart = Cart::addProduct($product, $request->all());

            return new JsonResource([
                'data'    => new CartResource($cart),
                'message' => trans('shop::app.checkout.cart.item-add-to-cart'),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'redirect_uri' => route('shop.product_or_category.index', $product->url_key),
                'message'      => $e->getMessage(),
            ], Response::HTTP_BAD_REQUEST);
        }
    }
}
