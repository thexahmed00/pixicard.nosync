<?php

namespace Webkul\Marketplace\Http\Controllers\Seller;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Webkul\Marketplace\Helpers\Dashboard;

class DashboardController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(protected Dashboard $dashboardHelper) {}

    /**
     * Request param functions
     *
     * @var array
     */
    // protected $typeFunctions = [
    //     'over-all'        => 'getOverAllStats',
    //     'total-sales'     => 'getSalesStats',
    //     'total-visitors'  => 'getVisitorStats',
    //     'recent-orders'   => 'getRecentOrders',
    //     'stock-threshold' => 'getStockThreshold',
    //     'top-products'    => 'getTopProducts',
    //     'top-customers'   => 'getTopCustomers',
    //     'top-categories'  => 'getTopCategories',
    // ];

    protected $typeFunctions = [
        // Update these keys to point to your new functions
        'over-all'        => 'getPosOverAllStats',
        'total-sales'     => 'getPosSalesStats',   // Create this function if needed
        'recent-orders'   => 'getPosRecentOrders',
        'total-visitors'  => 'getVisitorStats',
        'stock-threshold' => 'getStockThreshold',
        'top-products'    => 'getTopProducts',
        'top-customers'   => 'getTopCustomers',
        'top-categories'  => 'getTopCategories',
    ];

    /**
     * Display a listing of the resource.
     */
    public function index(): View
    {
        $seller = auth()->guard('seller')->user();

        return view('marketplace::seller.dashboard.index')
            ->with([
                'seller'    => $seller,
                'startDate' => $this->dashboardHelper->getStartDate(),
                'endDate'   => $this->dashboardHelper->getEndDate(),
            ]);
    }

    /**
     * Display a listing of the resource.
     */
    public function stats(Request $request): JsonResponse
    {
        $stats = $this->dashboardHelper->
            {$this->typeFunctions[$request->query('type')]}(seller()->user());

        return new JsonResponse([
            'statistics' => $stats,
            'date_range' => $this->dashboardHelper->getDateRange(),
        ]);
    }
}

