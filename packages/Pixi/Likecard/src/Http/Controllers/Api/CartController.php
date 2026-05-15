<?php

namespace Pixi\Likecard\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Webkul\Checkout\Facades\Cart;
use Webkul\Checkout\Repositories\CartRepository;
use Webkul\Checkout\Repositories\CartItemRepository;
use Webkul\Customer\Repositories\CustomerRepository;
use Webkul\Product\Repositories\ProductRepository;
use Webkul\GraphQLAPI\Validators\CustomException;

class CartController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(
        protected CartRepository $cartRepository,
        protected CartItemRepository $cartItemRepository,
        protected CustomerRepository $customerRepository,
        protected ProductRepository $productRepository
    ) {
    }

    /**
     * Create a new cart for a customer.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function create(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'customer_id' => 'required|integer|exists:customers,id',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $customer = $this->customerRepository->find($request->input('customer_id'));

        try {
            $cart = Cart::createCart([
                'customer'  => $customer,
                'is_active' => false,
            ]);

            return response()->json([
                'message' => 'Cart created successfully.',
                'cart'    => $cart,
            ], 201);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }

    /**
     * Get the details of a specific cart.
     *
     * @param  int  $cartId
     * @return \Illuminate\Http\JsonResponse
     */
    public function get($cartId)
    {
        // Eager load the 'items' relationship, and for each item, load its 'product'
        $cart = $this->cartRepository->with(['items.product'])->find($cartId);

        if (!$cart) {
            return response()->json(['message' => 'Cart not found.'], 404);
        }

        return response()->json($cart);
    }

    /**
     * Add an item to the specified cart.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $cartId
     * @return \Illuminate\Http\JsonResponse
     */
    public function addItem(Request $request, $cartId)
    {
        $validator = Validator::make($request->all(), [
            'product_id' => 'required|integer|exists:products,id',
            'quantity'   => 'required|integer|min:1',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $this->setCart($cartId);

        try {
            $product = $this->productRepository->find($request->input('product_id'));
            
            // This function prepares the data array for different product types (configurable, grouped, etc.)
            $data = bagisto_graphql()->manageInputForCart($product, $request->all());

            $cart = Cart::addProduct($product, $data);

            return response()->json([
                'message' => 'Item added to cart successfully.',
                'cart'    => $cart,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => $e->getMessage(),
                'cart'    => Cart::getCart(),
            ], 500);
        }
    }

    /**
     * Update an item in the cart.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $cartId
     * @param  int  $itemId
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateItem(Request $request, $cartId, $itemId)
    {
        $validator = Validator::make($request->all(), [
            'quantity' => 'required|integer|min:1',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $this->setCart($cartId);

        try {
            $data = [
                'qty' => [
                    $itemId => $request->input('quantity'),
                ],
            ];
            
            Cart::updateItems($data);

            return response()->json([
                'message' => 'Cart item updated successfully.',
                'cart'    => Cart::getCart(),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => $e->getMessage(),
                'cart'    => Cart::getCart(),
            ], 500);
        }
    }

    /**
     * Remove an item from the cart.
     *
     * @param  int  $cartId
     * @param  int  $itemId
     * @return \Illuminate\Http\JsonResponse
     */
    public function deleteItem($cartId, $itemId)
        {
            try {
                $this->setCart($cartId);

                // ** FIX: Use findOneWhere instead of findOneBy **
                $item = $this->cartItemRepository->findOneWhere([
                    'id'      => $itemId,
                    'cart_id' => $cartId,
                ]);

                if (!$item) {
                    return response()->json(['message' => 'Cart item not found.'], 404);
                }

                Cart::removeItem($itemId);
                Cart::collectTotals();

                return response()->json([
                    'message' => 'Item removed from cart successfully.',
                    'cart'    => Cart::getCart(),
                ]);
            } catch (\Exception $e) {
                return response()->json(['message' => $e->getMessage()], 500);
            }
        }

    /**
     * Set cart for operations.
     *
     * @param  int  $cartId
     * @return void
     */
    private function setCart(int $cartId): void
    {
        $cart = $this->cartRepository->find($cartId);

        if (!$cart) {
            // Using CustomException to throw a proper exception that can be caught
            throw new CustomException('Cart not found.', 404);
        }

        Cart::setCart($cart);
    }

    public function count($cartId)
    {
        // Find the cart. We don't need to eager load 'product' here, just 'items' is enough.
        $cart = $this->cartRepository->with(['items'])->find($cartId);

        if (!$cart) {
            return response()->json(['message' => 'Cart not found.'], 404);
        }

        // 'items_count' = Number of unique product lines (e.g., 2 items: 1x Shirt, 5x Pants)
        // 'total_quantity' = Total sum of units (e.g., 1 + 5 = 6)
        
        return response()->json([
            'data' => [
                'cart_id'        => $cart->id,
                'items_count'    => $cart->items->count(), 
                'total_quantity' => (int) $cart->items->sum('quantity'),
            ]
        ]);
    }
}

