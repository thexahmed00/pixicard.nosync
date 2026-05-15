<?php

namespace Webkul\Pos\Mutations\Outlet;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use Nuwave\Lighthouse\Support\Contracts\GraphQLContext;
use Webkul\CartRule\Repositories\CartRuleCouponRepository;
use Webkul\Checkout\Facades\Cart;
use Webkul\Checkout\Repositories\CartItemRepository;
use Webkul\Checkout\Repositories\CartRepository;
use Webkul\Customer\Repositories\CustomerRepository;
use Webkul\GraphQLAPI\Validators\CustomException;
use Webkul\Product\Repositories\ProductRepository;

class CartMutation extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(
        protected CartRuleCouponRepository $cartRuleCouponRepository,
        protected CartItemRepository $cartItemRepository,
        protected CartRepository $cartRepository,
        protected CustomerRepository $customerRepository,
        protected ProductRepository $productRepository,
    ) {}

    /**
     * Create new cart
     */
    public function store($root, array $args, GraphQLContext $context): array
    {
        $customer = $this->customerRepository->find($args['customer_id']);

        try {
            $cart = Cart::createCart([
                'customer'  => $customer,
                'is_active' => false,
            ]);

            return [
                'success' => true,
                'message' => trans('pos::app.outlet.cart.create-success'),
                'cart'    => $cart,
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage(),
            ];
        }
    }

    /**
     * Add item to cart
     */
    public function storeItem($root, array $args, GraphQLContext $context): array
    {
        $validator = Validator::make($args, [
            'cart_id'    => 'required|integer',
            'product_id' => 'required|integer',
            'quantity'   => 'required|integer',
        ]);

        if ($validator->fails()) {
            throw new CustomException($validator->errors()->first());
        }

        $this->setCart($args['cart_id']);

        try {
            $product = $this->productRepository->find($args['product_id']);

            $data = bagisto_graphql()->manageInputForCart($product, $args);

            $cart = Cart::addProduct($product, $data);
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage(),
                'cart'    => Cart::getCart(),
            ];
        }

        return [
            'success' => true,
            'message' => trans('pos::app.outlet.cart.item-add-success'),
            'cart'    => $cart,
        ];
    }

    /**
     * Update item in cart
     */
    public function updateItem($root, array $args, GraphQLContext $context): array
    {
        $validator = Validator::make($args, [
            'qty'                => 'required|array',
            'qty.*.cart_item_id' => 'required|integer|exists:cart_items,id',
            'qty.*.quantity'     => 'required|integer|min:1',
            'cart_id'            => 'required|integer',
        ]);

        if ($validator->fails()) {
            throw new CustomException($validator->errors()->first());
        }

        $this->setCart($args['cart_id']);

        foreach ($args['qty'] as $item) {
            $qty[$item['cart_item_id']] = $item['quantity'] ?: 1;

            $this->cartItemRepository->update([
                'custom_price' => $item['custom_price'] ?? null,
            ], $item['cart_item_id']);
        }

        $data['qty'] = $qty;

        try {
            Cart::updateItems($data);
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage(),
                'cart'    => Cart::getCart(),
            ];
        }

        return [
            'success' => true,
            'message' => trans('pos::app.outlet.cart.item-update-success'),
            'cart'    => Cart::getCart(),
        ];
    }

    /**
     * Remove product from cart
     */
    public function deleteItem($root, array $args, GraphQLContext $context): array
    {
        $validator = Validator::make($args, [
            'cart_item_id' => 'required|integer|exists:cart_items,id',
            'cart_id'      => 'required|integer',
        ]);

        if ($validator->fails()) {
            throw new CustomException($validator->errors()->first());
        }

        $this->setCart($args['cart_id']);

        Cart::removeItem($args['cart_item_id']);

        Cart::collectTotals();

        return [
            'success' => true,
            'message' => trans('pos::app.outlet.cart.item-remove-success'),
            'cart'    => Cart::getCart(),
        ];
    }

    /**
     * Apply coupon to cart
     */
    public function applyCoupon($root, array $args, GraphQLContext $context): array
    {
        $validator = Validator::make($args, [
            'code'    => 'required|string',
            'cart_id' => 'required|integer',
        ]);

        if ($validator->fails()) {
            throw new CustomException($validator->errors()->first());
        }

        $this->setCart($args['cart_id']);

        $coupon = $this->cartRuleCouponRepository->findOneByField('code', $args['code']);

        if (
            ! $coupon
            || ! $coupon->cart_rule->status
        ) {
            return [
                'success' => false,
                'message' => trans('pos::app.outlet.cart.invalid-coupon'),
                'cart'    => Cart::getCart(),
            ];
        }

        if (Cart::getCart()->coupon_code == $args['code']) {
            return [
                'success' => false,
                'message' => trans('pos::app.outlet.cart.already-applied'),
                'cart'    => Cart::getCart(),
            ];
        }

        Cart::setCouponCode($args['code'])->collectTotals();

        return [
            'success' => true,
            'message' => trans('pos::app.outlet.cart.coupon-applied'),
            'cart'    => Cart::getCart(),
        ];
    }

    /**
     * Remove coupon from cart
     */
    public function removeCoupon($root, array $args, GraphQLContext $context): array
    {
        $this->setCart($args['cart_id']);

        Cart::removeCouponCode()->collectTotals();

        return [
            'success' => true,
            'message' => trans('pos::app.outlet.cart.coupon-removed'),
            'cart'    => Cart::getCart(),
        ];
    }

    /**
     * Set cart
     */
    private function setCart(int $cartId): void
    {
        $cart = $this->cartRepository->find($cartId);

        if (! $cart) {
            throw new CustomException(trans('pos::app.outlet.cart.not-found'));
        }

        Cart::setCart($cart);
    }
}
