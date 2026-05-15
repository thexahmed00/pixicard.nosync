<?php

namespace Webkul\Marketplace\Helpers;

use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Webkul\Marketplace\Helpers\Reporting\Sale;
use Webkul\Marketplace\Helpers\Reporting\Product;
use Webkul\Marketplace\Helpers\Reporting\Visitor;
use Webkul\Marketplace\Helpers\Reporting\Customer;
use Webkul\Marketplace\DataGrids\Seller\OrderDataGrid;

class Dashboard
{
    /**
     * Create a controller instance.
     *
     * @return void
     */
    public function __construct(
        protected Customer $customerReporting,
        protected Product $productReporting,
        protected Sale $saleReporting,
        protected Visitor $visitorReporting
    ) {}

    /**
     * Returns the overall statistics.
     *
     * @param  object  $seller
     */
    public function getOverAllStats($seller): array
    {
        return [
            'total_customers'  => $this->customerReporting->getTotalCustomersProgress($seller),
            'total_orders'     => $this->saleReporting->getTotalOrdersProgress($seller),
            'total_sales'      => $this->saleReporting->getTotalSalesProgress($seller),
            'avg_sales'        => $this->saleReporting->getAverageSalesProgress($seller),
            'total_payout'     => $this->saleReporting->getTotalPayoutProgress($seller),
            'remaining_payout' => $this->saleReporting->getRemainingPayoutProgress($seller),
        ];
    }

    /**
     * Returns the top customers statistics.
     *
     * @param  object  $seller
     * @param  int  $limit
     */
    public function getTopCustomers($seller, $limit = 5): object
    {
        return $this->customerReporting->getCustomersWithMostSales($seller, $limit);
    }

    /**
     * Returns top products statistics.
     */
    public function getTopProducts($seller): object
    {
        return $this->productReporting->getTopSellingProductsByRevenue($seller);
    }

    /**
     * Returns top categories statistics.
     */
    public function getTopCategories($seller): object
    {
        return $this->productReporting->getTopSellingCategoriesByRevenue($seller);
    }

    /**
     * Returns top stock threshold products statistics.
     */
    public function getStockThreshold($seller): object
    {
        return $this->productReporting->getStockThresholdProducts($seller);
    }

    /**
     * Returns sales statistics.
     *
     * @param  object  $seller
     */
    public function getSalesStats($seller): array
    {
        return [
            'total_orders' => $this->saleReporting->getTotalOrdersProgress($seller),
            'total_sales'  => $this->saleReporting->getTotalSalesProgress($seller),
            'over_time'    => $this->saleReporting->getCurrentTotalSalesOverTime($seller),
        ];
    }

    /**
     * Returns visitors statistics.
     */
    public function getVisitorStats($seller): array
    {
        return [
            'total'     => $this->visitorReporting->getTotalVisitorsProgress($seller),
            'unique'    => $this->visitorReporting->getTotalUniqueVisitorsProgress($seller),
            'over_time' => $this->visitorReporting->getCurrentTotalVisitorsOverTime($seller),
        ];
    }

    /**
     * Get the start date.
     *
     * @return \Carbon\Carbon
     */
    public function getStartDate(): Carbon
    {
        return $this->saleReporting->getStartDate();
    }

    /**
     * Get the end date.
     *
     * @return \Carbon\Carbon
     */
    public function getEndDate(): Carbon
    {
        return $this->saleReporting->getEndDate();
    }

    /**
     * Get the orders.
     *
     * @param  object  $seller
     * @return Collection
     */
    public function getRecentOrders($seller)
    {
        $query = app(OrderDataGrid::class)->prepareQuery();

        return collect(['pending', 'processing', 'completed', 'closed'])
            ->mapWithKeys(fn ($status) => [
                $status => (clone $query)
                    ->where('marketplace_orders.marketplace_seller_id', $seller->id)
                    ->where('marketplace_orders.status', $status)
                    ->orderBy('created_at', 'desc')
                    ->take(5)
                    ->get()
                    ->map(fn ($order) => tap($order, function ($order) {
                        $order->payment_method = core()->getConfigData("sales.payment_methods.$order->payment_method.title");

                        $method = current(explode('_', $order->shipping_method));
                        $order->shipping_method = core()->getConfigData("sales.carriers.{$method}.title");
                    })),
            ]);
    }

    /**
     * Returns date range
     */
    public function getDateRange(): string
    {
        return $this->getStartDate()->format('d M').' - '.$this->getEndDate()->format('d M');
    }

    public function getPosOverAllStats($seller): array
    {
        // Fetch the core sales stats
        $totalSales = $this->saleReporting->getPosTotalSalesProgress($seller);
        $totalOrders = $this->saleReporting->getPosTotalOrdersProgress($seller);
        $avgSales = $this->saleReporting->getPosAverageSalesProgress($seller);
        $totalCustomers = $this->customerReporting->getPosTotalCustomersProgress($seller);

        // For POS, Total Payout is the same as Total Sales.
        $totalPayout = [
            'total'           => $totalSales['current'],
            'formatted_total' => core()->formatPrice($totalSales['current']), // Changed to formatPrice
            'percent'         => 100,
        ];

        // Remaining Payout is always 0 for the POS model.
        $remainingPayout = [
            'total'           => 0,
            'formatted_total' => core()->formatPrice(0), // Changed to formatPrice
            'percent'         => 0,
        ];

        return [
            'total_customers'  => $totalCustomers,
            'total_orders'     => $totalOrders,
            'total_sales'      => $totalSales,
            'avg_sales'        => $avgSales,
            'total_payout'     => $totalPayout,
            'remaining_payout' => $remainingPayout,
        ];
    }

    /**
     * Get the recent POS orders.
     */
    public function getPosRecentOrders($seller)
    {
        // Step 1: Get the agent IDs for the current seller
        $agentIds = DB::table('pos_users')
            ->join('pos_outlets', 'pos_users.outlet_id', '=', 'pos_outlets.id')
            ->join('marketplace_sellers', 'pos_outlets.email', '=', 'marketplace_sellers.email')
            ->where('marketplace_sellers.id', $seller->id)
            ->pluck('pos_users.id');

        // Step 2: Fetch the latest 5 POS orders, joining with the main 'orders' table
        $posOrders = DB::table('pos_order')
            ->join('orders', 'pos_order.order_id', '=', 'orders.id')
            ->whereIn('pos_order.user_id', $agentIds)
            ->whereBetween('pos_order.created_at', [$this->getStartDate(), $this->getEndDate()])
            ->select(
                'orders.id as order_id',
                'orders.increment_id',
                'orders.created_at',
                'orders.status',
                'orders.customer_first_name',
                'orders.customer_last_name',
                'orders.customer_email',
                'orders.total_item_count',
                'orders.sub_total as base_sub_total',
                'orders.discount_amount as base_discount_amount',
                'orders.grand_total as base_grand_total'
                // Select any other necessary fields from the 'orders' table
            )
            ->orderBy('pos_order.created_at', 'desc')
            ->take(5)
            ->get();

        // Step 3: Map the fetched data to the exact format the Vue component requires
        $formattedOrders = $posOrders->map(function ($order) {
            return [
                // IDs and Status
                'order_id'               => $order->order_id,
                'increment_id'           => $order->increment_id,
                'status'                 => $order->status,
                'created_at'             => $order->created_at,
                'marketplace_order_id'   => $order->order_id, // Used for some internal links

                // Customer Details
                'customer_name'          => $order->customer_first_name . ' ' . $order->customer_last_name,
                'customer_email'         => $order->customer_email,
                'location'               => 'In-Store Sale', // Static value for POS

                // Order Details
                'total_item_count'       => $order->total_item_count,
                'payment_method'         => 'POS', // Static value
                'shipping_method'        => 'N/A', // Not applicable for POS

                // Financials
                'base_sub_total'         => $order->base_sub_total,
                'base_discount_amount'   => $order->base_discount_amount,
                'base_commission'        => 0.00, // You can add your commission logic here if needed
                'base_seller_total'      => $order->base_grand_total, // The final amount for the seller

                // Payout Status
                'seller_payout_status'   => 'paid', // Or logic to determine if paid, e.g., 'pending'
                'marketplace_transaction_id' => null, // Placeholder, can be linked to a transaction ID if you have one
            ];
        });

        // Step 4: Group the formatted orders by status for the tabs
        return collect(['pending', 'processing', 'completed', 'closed', 'canceled'])
            ->mapWithKeys(fn ($status) => [
                $status => $formattedOrders->where('status', $status)->values()
            ]);
    }

    public function getPosSalesStats($seller): array
    {
        return [
            'total_orders' => $this->saleReporting->getPosTotalOrdersProgress($seller),
            'total_sales'  => $this->saleReporting->getPosTotalSalesProgress($seller),
            'over_time'    => $this->saleReporting->getPosCurrentTotalSalesOverTime($seller),
        ];
    }
}

