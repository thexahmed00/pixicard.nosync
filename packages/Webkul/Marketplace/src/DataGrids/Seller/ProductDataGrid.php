<?php

namespace Webkul\Marketplace\DataGrids\Seller;

use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;
use Webkul\DataGrid\DataGrid;
use Webkul\Marketplace\Enums\Product;

class ProductDataGrid extends DataGrid
{
    /**
     * The primary column for the datagrid. This will be used for actions and mass actions.
     * We will alias our main product ID to 'id' to resolve all ambiguities.
     *
     * @var string
     */
    protected $primaryColumn = 'id';

    /**
     * Prepare query builder.
     */
    public function prepareQueryBuilder(): Builder
    {
        $sellerId = seller()->id(); // Get the current seller's ID

        // Subquery to get the total inventory for each product for the current seller.
        $inventorySubquery = DB::table('product_inventories')
            ->select('product_id', DB::raw('SUM(qty) as total_qty'))
            // ->where('vendor_id', $sellerId)
            ->groupBy('product_id');

        $queryBuilder = DB::table('product_flat')
            ->leftJoin('products', 'product_flat.product_id', '=', 'products.id')
            
            // Join the pre-calculated inventory sum
            ->leftJoinSub($inventorySubquery, 'inventory', function ($join) {
                $join->on('product_flat.product_id', '=', 'inventory.product_id');
            })
            
            ->select(
                'product_flat.product_id as id',
                'product_flat.sku',
                'product_flat.name',
                'product_flat.status',
                'product_flat.price',
                'product_flat.type'
            )
            
            // This CASE statement correctly applies your logic.
            ->selectRaw('
                CASE 
                    WHEN products.type = "virtual" THEN 1000
                    ELSE COALESCE(inventory.total_qty, 0) 
                END as quantity
            ')
            
            // **FIX FOR DUPLICATES**: Group by the unique product ID to get one row per product.
            ->groupBy('product_flat.product_id');

        // These filters remain the same
        $this->addFilter('id', 'product_flat.product_id');
        $this->addFilter('name', 'product_flat.name');
        $this->addFilter('sku', 'product_flat.sku');
        $this->addFilter('status', 'product_flat.status');
        $this->addFilter('type', 'product_flat.type');

        return $queryBuilder;
    }




    /**
     * Prepare columns.
     */
    public function prepareColumns(): void
    {
        $this->addColumn([
            'index'           => 'id', // Use the aliased 'id'
            'label'           => trans('marketplace::app.seller.products.index.datagrid.id'),
            'type'            => 'integer',
            'filterable'      => true,
            'filterable_type' => 'number',
            'searchable'      => false,
            'sortable'        => true,
        ]);
        
        $this->addColumn([
            'index'      => 'name',
            'label'      => trans('marketplace::app.seller.products.index.datagrid.name'),
            'type'       => 'string',
            'filterable' => true,
            'searchable' => true,
            'sortable'   => true,
        ]);

        $this->addColumn([
            'index'      => 'sku',
            'label'      => trans('marketplace::app.seller.products.index.datagrid.sku'),
            'type'       => 'string',
            'filterable' => true,
            'searchable' => true,
            'sortable'   => true,
        ]);

        $this->addColumn([
            'index'      => 'price',
            'label'      => trans('marketplace::app.seller.products.index.datagrid.price'),
            'type'       => 'string',
            'filterable' => true,
            'sortable'   => true,
            'searchable' => false,
            'closure'    => fn ($row) => core()->formatBasePrice($row->price),
        ]);

        $this->addColumn([
            'index'           => 'quantity',
            'label'           => trans('marketplace::app.seller.products.index.datagrid.stock'),
            'type'            => 'integer',
            'filterable'      => false,
            'sortable'        => true,
            'searchable'      => false,
        ]);

        $this->addColumn([
            'index'              => 'status',
            'label'              => trans('marketplace::app.seller.products.index.datagrid.status'),
            'type'               => 'integer',
            'filterable'         => true,
            'filterable_type'    => 'dropdown',
            'filterable_options' => [
                ['label' => trans('marketplace::app.seller.products.index.datagrid.active'), 'value' => 1],
                ['label' => trans('marketplace::app.seller.products.index.datagrid.disable'), 'value' => 0],
            ],
            'searchable' => true,
            'sortable'   => true,
        ]);
    }

    /**
     * Prepare actions.
     */
    public function prepareActions(): void
    {
        if (seller()->hasPermission('products.edit')) {
            $this->addAction([
                'method' => 'GET',
                'icon'   => 'icon-arrow-right',
                'title'  => trans('marketplace::app.seller.products.index.datagrid.edit'),
                'url'    => fn ($row) => route('seller.products.edit', $row->id),
            ]);
        }
    }

    /**
     * Prepare mass actions.
     */
    public function prepareMassActions(): void
    {
        // Mass actions are removed to prevent sellers from deleting admin products.
    }
}

