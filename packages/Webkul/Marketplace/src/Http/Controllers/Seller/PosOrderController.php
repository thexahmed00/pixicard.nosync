<?php

namespace Webkul\Marketplace\Http\Controllers\Seller;

use Illuminate\Http\JsonResponse;
use Illuminate\View\View;
use Webkul\Marketplace\DataGrids\Seller\PosOrderDataGrid;

class PosOrderController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): View|JsonResponse
    {
        if (request()->ajax()) {
            return datagrid(PosOrderDataGrid::class)->process();
        }

        return view('marketplace::pos_orders.index');
    }
}
