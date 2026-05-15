<?php

namespace Webkul\Marketplace\DataGrids\Seller;

use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;
use Webkul\DataGrid\DataGrid;

class PosOrderDataGrid extends DataGrid
{
    /**
     * Primary column.
     *
     * @var string
     */
    protected $primaryColumn = 'id';

    /**
     * Prepare query builder.
     *
     * @return \Illuminate\Database\Query\Builder
     */
    public function prepareQueryBuilder(): Builder
    {
        $seller = auth()->guard('seller')->user();

        $outlet = DB::table('pos_outlets')->where('email', $seller->email)->first();

        if (!$outlet) {
            // If no outlet is found, return a query that finds nothing
            return DB::table('pos_order')->whereRaw('0 = 1');
        }

        // Updated query to join with pos_users and select the agent's name
        $queryBuilder = DB::table('pos_order')
            ->leftJoin('pos_users', 'pos_order.user_id', '=', 'pos_users.id')
            ->leftJoin('orders', 'pos_order.order_id', '=', 'orders.id')
            ->where('pos_order.outlet_id', $outlet->id)
            ->addSelect(
                'pos_order.id as id',
                'pos_order.order_id as order_id',
                DB::raw("'SAR' as order_currency"),
                DB::raw('COALESCE(orders.grand_total, (pos_order.cash_total + COALESCE(pos_order.card_total, 0))) as grand_total'),
                'pos_order.created_at as created_at',
                DB::raw("CONCAT(pos_users.firstname, ' ', pos_users.lastname) as agent_name")
            );

        // Add filters for the columns
        $this->addFilter('id', 'pos_order.id');
        $this->addFilter('order_id', 'pos_order.order_id');
        $this->addFilter('order_currency', DB::raw("'SAR'"));
        $this->addFilter('grand_total', DB::raw('COALESCE(orders.grand_total, (pos_order.cash_total + COALESCE(pos_order.card_total, 0)))'));
        $this->addFilter('created_at', 'pos_order.created_at');
        $this->addFilter('agent_name', DB::raw("CONCAT(pos_users.firstname, ' ', pos_users.lastname)"));

        return $queryBuilder;
    }

    /**
     * Prepare columns.
     *
     * @return void
     */
    public function prepareColumns(): void
    {
        $this->addColumn([
            'index' => 'order_id',
            'label' => trans('marketplace::app.seller.pos-orders.datagrid.order-id'),
            'type' => 'string',
            'sortable' => true,
            'filterable' => true,
            'searchable' => true,
        ]);

        $this->addColumn([
            'index' => 'agent_name',
            'label' => trans('marketplace::app.seller.pos-orders.datagrid.agent'),
            'type' => 'string',
            'sortable' => true,
            'filterable' => true,
            'searchable' => true,
        ]);

        $this->addColumn([
            'index' => 'grand_total',
            'label' => trans('marketplace::app.seller.pos-orders.datagrid.total'),
            'type' => 'integer',
            'sortable' => true,
            'filterable' => true,
        ]);

        $this->addColumn([
            'index' => 'order_currency',
            'label' => trans('marketplace::app.seller.pos-orders.datagrid.currency'),
            'type' => 'string',
            'sortable' => true,
            'filterable' => true,
        ]);

        $this->addColumn([
            'index' => 'created_at',
            'label' => trans('marketplace::app.seller.pos-orders.datagrid.created-at'),
            'type' => 'datetime',
            'sortable' => true,
            'filterable' => true,
        ]);
    }
}

