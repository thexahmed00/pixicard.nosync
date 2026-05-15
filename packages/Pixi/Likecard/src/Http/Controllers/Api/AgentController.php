<?php

namespace Pixi\Likecard\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Webkul\Sales\Repositories\OrderRepository;
use Webkul\Pos\Repositories\PosOrderRepository;
use Webkul\Product\Repositories\ProductRepository;
use Webkul\Tax\Repositories\TaxCategoryRepository;
use Webkul\Category\Repositories\CategoryRepository;
use Webkul\Customer\Repositories\CustomerRepository;
use PHPOpenSourceSaver\JWTAuth\Exceptions\JWTException;


class AgentController extends Controller
{
    /**
     * Authenticate the agent based on the pos_users table.
     */
 public function login(Request $request)
    {

        // Use 'username' instead of 'email' for authentication
        $credentials = $request->only('username', 'password');

        try {
            // Attempt to verify the credentials and create a token for the user
            if (! $token = Auth::guard('pos_user')->attempt($credentials)) {
                return response()->json(['error' => 'Invalid Credentials'], 401);
            }
        } catch (JWTException $e) {
            // Something went wrong whilst attempting to encode the token
            return response()->json(['error' => 'Could not create token'], 500);
        }

        // Get the authenticated user (agent)
        $agent = Auth::guard('pos_user')->user();

        // If authentication is successful, return the token and agent details
        return response()->json([
            'message' => 'Login successful',
            'token'   => $token,
            'agent'   => [
                'id'        => $agent->id,
                'username'  => $agent->username,
                'firstname' => $agent->firstname,
                'lastname'  => $agent->lastname,
                'email'     => $agent->email,
                'outlet_id' => $agent->outlet_id,
            ]
        ]);
    }
    /**
     * Fetch products. This logic assumes products are filtered by channel/inventory source.
     */
    public function getProducts(Request $request, ProductRepository $productRepository)
    {
        $agent = $request->user(); // Gets the authenticated PosUser model
        
        // TODO: Implement logic to filter products based on the agent's outlet.
        // You might need to map the `outlet_id` to a Bagisto Channel or Inventory Source.
        // For now, it returns all products.
        $products = $productRepository->getAll();

        return response()->json($products);
    }

    /**
     * Fetch all categories.
     */
    public function getCategories(CategoryRepository $categoryRepository)
    {
        $categories = $categoryRepository->getVisibleCategoryTree(core()->getCurrentChannel()->root_category_id);
        
        return response()->json($categories);
    }
    
    /**
     * Sync orders created on the POS to Bagisto.
     */
    public function syncOrders(Request $request, OrderRepository $orderRepository)
    {
        $agent = $request->user(); // The authenticated agent creating the orders
        $ordersData = $request->input('orders', []);

        foreach ($ordersData as $orderData) {
            
        }
        
        return response()->json(['message' => 'Orders synced successfully']);
    }

    /**
     * Fetch a list of orders previously created by the authenticated agent.
     */
    /**
     * Fetch a list of orders previously created by the authenticated agent.
     */
    public function getOrders(Request $request, OrderRepository $orderRepository)
    {
        // Get the currently authenticated agent from the 'pos_user' guard
        $agent = Auth::guard('pos_user')->user();

        // Get the desired limit from the request, with a default of 15
        $limit = $request->input('limit', 15);

        // Start the query directly on the repository object
        $orders = $orderRepository
            ->with(['items.product'])
            ->join('pos_order as po', 'orders.id', '=', 'po.order_id')
            ->where('po.user_id', $agent->id)
            ->select('orders.*', 'po.order_ref_id') // Select all order fields and the POS reference ID
            ->orderBy('orders.created_at', 'desc') // Sort by most recent first
            ->paginate($limit); // Use paginate() instead of get()
        
        // Return the successful JSON response with the paginated order data
        return response()->json([
            'message' => 'Order list for agent ' . $agent->username,
            'data'    => $orders,
        ]);
    }

    
    /**
     * Sync (fetch) products from Bagisto to the POS device.
     */
    public function syncProducts(Request $request, ProductRepository $productRepository)
    {
        $agent = $request->user();
        
        $products = $productRepository->getAll();

        return response()->json($products);
    }

    public function createOrder(
        Request $request,
        OrderRepository $orderRepository,
        CustomerRepository $customerRepository,
        ProductRepository $productRepository,
        PosOrderRepository $posOrderRepository
    ) {
        $agent = Auth::guard('pos_user')->user();

        $validator = Validator::make($request->all(), [
            'items'                   => 'required|array|min:1',
            'items.*.product_id'      => 'required|integer|exists:products,id',
            'items.*.quantity'        => 'required|integer|min:1',
            'billing_address'         => 'required|array',
            'billing_address.address' => 'required|array',
            'customer_id'             => 'required_if:is_guest,false|nullable|integer|exists:customers,id',
            'is_guest'                => 'required|boolean',
            'payment_method'          => 'required|string',
            'shipping_method'         => 'required|string',
            'tax_amount'              => 'nullable|numeric|min:0'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $data = $request->all();
        $channel = core()->getCurrentChannel();
        $customer = null;

        if (! $data['is_guest']) {
            $customer = $customerRepository->find($data['customer_id']);
        }

        if (is_array($data['billing_address']['address'])) {
            $data['billing_address']['address'] = implode("\n", $data['billing_address']['address']);
        }
        if (isset($data['shipping_address']) && is_array($data['shipping_address']['address'])) {
            $data['shipping_address']['address'] = implode("\n", $data['shipping_address']['address']);
        }

        // --- THIS IS THE FINAL FIX ---
        // Manually build the order data array, including all required total fields

        // 1. Initialize all totals to zero
        $subTotal = 0;
        $baseSubTotal = 0;
        $grandTotal = 0;
        $baseGrandTotal = 0;
        // Add other totals like tax, shipping, discount if you need to calculate them
        // MODIFICATION 2: Get tax_amount from the request, default to 0 if not provided.
        $taxAmount = $request->input('tax_amount', 0);
        $baseTaxAmount = $request->input('tax_amount', 0); 
        $orderItems = [];

        // 2. Loop through items to calculate totals and prepare item data
        foreach ($data['items'] as $itemData) {
            $product = $productRepository->findOrFail($itemData['product_id']);
            $price = $product->getTypeInstance()->getFinalPrice();

            $itemTotal = $price * $itemData['quantity'];
            
            $orderItems[] = [
                'product_id'       => $product->id,
                'sku'              => $product->sku,
                'type'             => $product->type,
                'name'             => $product->name,
                'weight'           => $product->weight ?? 0,
                'total_weight'     => ($product->weight ?? 0) * $itemData['quantity'],
                'qty_ordered'      => $itemData['quantity'],
                'qty_to_ship'      => $itemData['quantity'],
                'qty_to_invoice'   => $itemData['quantity'],
                'price'            => $price,
                'base_price'       => $price,
                'total'            => $itemTotal,
                'base_total'       => $itemTotal,
                'product_type'     => get_class($product)
            ];
            
            $subTotal += $itemTotal;
            $baseSubTotal += $itemTotal;
        }

        $subTotalInclTax = $subTotal + $taxAmount;
        $baseSubTotalInclTax = $baseSubTotal + $baseTaxAmount;
        // 3. Calculate final grand total (can be expanded with tax/shipping)
        $grandTotal = $subTotal + $taxAmount;
        $baseGrandTotal = $baseSubTotal + $baseTaxAmount;
        

        // 4. Build the final order data array with all totals
        $orderData = [
            'is_guest'              => $data['is_guest'],
            'customer_id'           => $customer?->id,
            'customer_email'        => $customer?->email ?? $data['billing_address']['email'],
            'customer_first_name'   => $customer?->first_name ?? $data['billing_address']['first_name'],
            'customer_last_name'    => $customer?->last_name ?? $data['billing_address']['last_name'],
            'customer_type'         => \Webkul\Customer\Models\Customer::class,
            'channel_id'            => $channel->id,
            'channel_name'          => $channel->name,
            'channel_type'          => \Webkul\Core\Models\Channel::class,
            'status'                => 'pending',
            'shipping_method'       => $data['shipping_method'],
            'shipping_title'        => '',
            'shipping_description'  => '',
            'billing_address'       => $data['billing_address'],
            'shipping_address'      => $data['shipping_address'] ?? $data['billing_address'],
            'payment'               => ['method' => $data['payment_method']],
            'items'                 => $orderItems,
            'sub_total'                  => $subTotal,
            'base_sub_total'             => $baseSubTotal,
            'grand_total'                => $grandTotal,
            'base_grand_total'           => $baseGrandTotal,
            'tax_amount'                 => $taxAmount,
            'base_tax_amount'            => $baseTaxAmount,
            'sub_total_incl_tax'         => $subTotalInclTax,
            'base_sub_total_incl_tax'    => $baseSubTotalInclTax,
        ];

        // Create the order
        $order = $orderRepository->create($orderData);
        
        // Link to POS agent
        $posOrderRepository->create([
            'order_id'  => $order->id,
            'user_id'  => $agent->id,
            'outlet_id' => $agent->outlet_id,
        ]);
        
        return response()->json([
            'message' => 'Order created successfully with pending status.',
            'order'   => $order,
        ], 201);
    }

    public function updateOrderStatus(Request $request, $orderId, OrderRepository $orderRepository)
    {
        // 1. Validate the incoming request
        $validator = Validator::make($request->all(), [
            'status' => 'required|string|in:pending,processing,completed,canceled,closed',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        // Get the currently authenticated agent
        $agent = Auth::guard('pos_user')->user();

        // *** THIS IS THE FIX ***
        // 2. Find the order AND verify it belongs to the authenticated agent
        $order = $orderRepository
            ->join('pos_order as po', 'orders.id', '=', 'po.order_id')
            ->where('po.user_id', $agent->id)
            ->where('orders.id', $orderId)
            ->select('orders.*') // Ensure you get the Order model instance
            ->first();

        // If no order is found, it either doesn't exist or doesn't belong to this agent
        if (!$order) {
            return response()->json(['message' => 'Order not found or you are not authorized to update it.'], 404);
        }
        
        // 3. Update the order status
        try {
            // Since we got a valid order model, we can update and save it.
            $order->status = $request->input('status');
            $order->save();
            $order->load('items');
            return response()->json([
                'message' => 'Order status updated successfully.',
                'order' => $order,
            ]);
        } catch (\Exception $e) {
            // Log the error for debugging
            Log::error('Order status update failed: ' . $e->getMessage());
            
            return response()->json([
                'message' => 'Failed to update order status.',
            ], 500);
        }
    }

    public function searchOrders(Request $request, OrderRepository $orderRepository)
    {
        // 1. Validate the incoming request
        $validator = Validator::make($request->all(), [
            'from_date' => 'nullable|date_format:Y-m-d',
            'to_date'   => 'nullable|date_format:Y-m-d|after_or_equal:from_date',
            'limit'     => 'nullable|integer|min:1',
        ]);


        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }


        // Get the currently authenticated agent
        $agent = Auth::guard('pos_user')->user();


        // 2. Start building the query
        $query = $orderRepository
            ->join('pos_order as po', 'orders.id', '=', 'po.order_id')
            ->where('po.user_id', $agent->id)
            ->select('orders.*', 'po.order_ref_id')
            ->with(['items.product']);


        // 3. Apply date range filters if provided
        if ($request->has('from_date') && $request->from_date) {
            $query->whereDate('orders.created_at', '>=', $request->from_date);
        }


        if ($request->has('to_date') && $request->to_date) {
            $query->whereDate('orders.created_at', '<=', $request->to_date);
        }

        // 4. Order the results and paginate
        $limit = $request->input('limit', 15); // Default to 15 items per page
        $orders = $query->orderBy('orders.created_at', 'desc')->paginate($limit);


        // 5. Return the successful JSON response
        return response()->json([
            'message' => 'Search results for agent ' . $agent->username,
            'data'    => $orders,
        ]);
    }

    public function getSalesSummary(Request $request, OrderRepository $orderRepository)
    {
        // 1. Validate the optional date inputs
        $validator = Validator::make($request->all(), [
            'from_date' => 'nullable|date_format:Y-m-d',
            'to_date'   => 'nullable|date_format:Y-m-d|after_or_equal:from_date',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        // 2. Get the currently authenticated agent
        $agent = Auth::guard('pos_user')->user();

        // 3. Build the base query for the agent's orders with date filters
        $query = $orderRepository
            ->join('pos_order as po', 'orders.id', '=', 'po.order_id')
            ->where('po.user_id', $agent->id);

        if ($request->filled('from_date')) {
            $query->whereDate('orders.created_at', '>=', $request->from_date);
        }

        if ($request->filled('to_date')) {
            $query->whereDate('orders.created_at', '<=', $request->to_date);
        }

        // 4. Calculate total sale amount and order count for the filtered period
        // We clone the query to avoid modifying it for subsequent calculations
        $totalSaleAmount = (clone $query)->sum('orders.grand_total');
        $orderCount = (clone $query)->count();

        // 5. Calculate today's sale amount, ignoring the date filters
        $todaySaleAmount = $orderRepository
            ->join('pos_order as po', 'orders.id', '=', 'po.order_id')
            ->where('po.user_id', $agent->id)
            ->whereDate('orders.created_at', today()->toDateString())
            ->sum('orders.grand_total');

        // 6. Return the consolidated data
        return response()->json([
            'message' => 'Sales summary retrieved successfully.',
            'data'    => [
                'total_sale_amount' => (float) $totalSaleAmount,
                'order_count'       => (int) $orderCount,
                'today_sale_amount' => (float) $todaySaleAmount,
            ]
        ]);
    }

    public function initiateWalletBalance(Request $request)
    {
        // 1. Validate the incoming request
        $validator = Validator::make($request->all(), [
            'amount' => 'required|numeric|min:0',
            'recharge_option' => 'required|in:payment,credit,tap_pay', 
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        // 2. Get the currently authenticated agent
        $agent = Auth::guard('pos_user')->user();

        // 3. Find the agent's outlet email
        $outletEmail = DB::table('pos_outlets')->where('id', $agent->outlet_id)->value('email');

        if (!$outletEmail) {
            return response()->json(['message' => 'POS outlet not found for the authenticated agent.'], 404);
        }

        // 4. Fetch specific seller details, excluding sensitive or unnecessary fields
        $seller = DB::table('marketplace_sellers')
                    ->where('email', $outletEmail)
                    ->select('id', 'shop_title', 'url', 'phone', 'address', 'city', 'state', 'country', 'postcode') // Add or remove fields as needed
                    ->first();

        if (!$seller) {
            return response()->json(['message' => 'Marketplace seller not found for the associated outlet.'], 404);
        }

        if($request->input('recharge_option') == 'credit') {

            $walletId = DB::table('seller_wallets')->insertGetId([
                'seller_id'  => $seller->id,
                'amount'     => $request->input('amount'),
                'vendor_id'  => 1, // Assuming a default vendor_id
                'status'     => 'pending_approval',
                'type'       => 'credit',
                'created_at' => now(),
                'updated_at' => now(),
            ]);

        }else {

            $walletId = DB::table('seller_wallets')->insertGetId([
                'seller_id'  => $seller->id,
                'amount'     => $request->input('amount'),
                'vendor_id'  => 1, // Assuming a default vendor_id
                'status'     => 'pending',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            
        }
        // 6. Fetch the newly created wallet record
        $wallet = DB::table('seller_wallets')->find($walletId);

        // 7. Return the successful response with the new wallet transaction and the filtered seller details
        return response()->json([
            'message' => 'New wallet transaction initiated successfully.',
            'wallet'  => $wallet,
            'seller'  => $seller,
        ], 201); // 201 Created
    }

    public function updateWalletStatus(Request $request)
    {
        // 1. Validate the incoming request
        $validator = Validator::make($request->all(), [
            'wallet_id' => 'required|integer|exists:seller_wallets,id',
            'status'    => 'required|string|in:pending,success,failed',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $walletId = $request->input('wallet_id');
        $newStatus = $request->input('status');

        // 2. Find the wallet record to ensure it exists before updating
        $wallet = DB::table('seller_wallets')->find($walletId);

        if (!$wallet) {
            // This is a fallback, though the 'exists' rule should prevent this
            return response()->json(['message' => 'Wallet record not found.'], 404);
        }
        
        

        // 3. Update the wallet status in the database
        DB::table('seller_wallets')
            ->where('id', $walletId)
            ->update([
                'status'     => $newStatus,
                'updated_at' => now(),
            ]);

        // 4. Fetch the updated record to return in the response
        $updatedWallet = DB::table('seller_wallets')->find($walletId);

        return response()->json([
            'message' => 'Wallet status updated successfully.',
            'wallet'  => $updatedWallet,
        ], 200);
    }

    public function getWalletBalance(Request $request)
    {
        // 1. Get the currently authenticated agent from the 'pos_user' guard
        $agent = Auth::guard('pos_user')->user();

        // 2. Find the agent's outlet email to link to the marketplace seller
        $outletEmail = DB::table('pos_outlets')->where('id', $agent->outlet_id)->value('email');

        if (!$outletEmail) {
            return response()->json(['message' => 'POS outlet not found for the authenticated agent.'], 404);
        }

        // 3. Find the corresponding seller ID from the marketplace_sellers table
        $sellerId = DB::table('marketplace_sellers')->where('email', $outletEmail)->value('id');

        if (!$sellerId) {
            return response()->json(['message' => 'Marketplace seller not found for the associated outlet.'], 404);
        }

        // 4. Calculate the total balance by summing the 'amount' of all 'success' transactions.
        // This assumes that 'success' status indicates a credit to the wallet.
        $totalBalance = DB::table('seller_wallets')
                            ->where('seller_id', $sellerId)
                            ->where('status', 'success')
                            ->sum('amount');

        // 5. Return the calculated balance in a JSON response
        return response()->json([
            'message' => 'Wallet balance retrieved successfully.',
            'data' => [
                'balance'   => (float) $totalBalance,
                'seller_id' => $sellerId,
            ]
        ], 200);
    }

    public function getWalletHistory(Request $request)
    {
        // 1. Validate the optional 'limit', 'from_date', and 'to_date' parameters
        $validator = Validator::make($request->all(), [
            'limit'     => 'nullable|integer|min:1',
            'from_date' => 'nullable|date_format:Y-m-d',
            'to_date'   => 'nullable|date_format:Y-m-d|after_or_equal:from_date',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        // 2. Get the currently authenticated agent
        $agent = Auth::guard('pos_user')->user();

        // 3. Find the agent's outlet email to link to the marketplace seller
        $outletEmail = DB::table('pos_outlets')->where('id', $agent->outlet_id)->value('email');

        if (!$outletEmail) {
            return response()->json(['message' => 'POS outlet not found for the authenticated agent.'], 404);
        }

        // 4. Find the corresponding seller ID from the marketplace_sellers table
        $sellerId = DB::table('marketplace_sellers')->where('email', $outletEmail)->value('id');

        if (!$sellerId) {
            return response()->json(['message' => 'Marketplace seller not found for the associated outlet.'], 404);
        }

        // 5. Start building the query
        $query = DB::table('seller_wallets')->where('seller_id', $sellerId);

        // 6. Apply date range filters if provided
        if ($request->filled('from_date')) {
            $query->whereDate('created_at', '>=', $request->from_date);
        }

        if ($request->filled('to_date')) {
            $query->whereDate('created_at', '<=', $request->to_date);
        }

        // 7. Get the desired number of items per page from the request, with a default of 15
        $limit = $request->input('limit', 15);

        // 8. Fetch the wallet history, order by most recent, and paginate the results
        $walletHistory = $query->orderBy('created_at', 'desc') // Show newest transactions first
                               ->paginate($limit);

        // 9. Return the successful JSON response with the paginated transaction data
        return response()->json([
            'message' => 'Wallet history retrieved successfully.',
            'data'    => $walletHistory,
        ], 200);
    }
}
