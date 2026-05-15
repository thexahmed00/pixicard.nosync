<?php

namespace Webkul\Pos\Queries\Order;

use App\Http\Controllers\Controller;
use Webkul\Pos\Repositories\PosOrderRepository;

class OrderQuery extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(protected PosOrderRepository $posOrderRepository) {}

    /**
     * Get orders.
     */
    public function getOrders($query, $input)
    {
        $query = $this->posOrderRepository->query()
            ->where('pos_order.created_at', '>=', now()->subDays(30))
            ->where('pos_order.outlet_id', auth()->guard('pos_user')->user()->outlet_id)
            ->orderBy('pos_order.created_at', 'desc');

        return $query;
    }
}
