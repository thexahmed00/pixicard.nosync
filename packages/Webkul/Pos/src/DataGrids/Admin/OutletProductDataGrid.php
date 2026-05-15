<?php

namespace Webkul\Pos\DataGrids\Admin;

use Illuminate\Support\Facades\DB;
use Webkul\DataGrid\DataGrid;
use Webkul\Pos\Repositories\PosOutletProductRepository;
use Webkul\Pos\Repositories\PosOutletRepository;

class OutletProductDataGrid extends DataGrid
{
    /**
     * Current Outlet
     *
     * @var object
     */
    protected $posOutlet = null;

    /**
     * Primary column.
     *
     * @var string
     */
    protected $primaryColumn = 'product_id';

    /**
     * Create a new repository instance.
     *
     * @return void
     */
    public function __construct(
        protected PosOutletRepository $posOutletRepository,
        protected PosOutletProductRepository $posOutletProductRepository
    ) {}

    /**
     * Prepare query builder.
     *
     * @return \Illuminate\Database\Query\Builder
     */
    public function prepareQueryBuilder()
    {
        $this->posOutlet = $this->posOutletRepository->find(request()->route()->id);

        $queryBuilder = DB::table('product_flat')
            ->leftJoin('products', 'product_flat.product_id', '=', 'products.id')
            ->leftJoin('product_images', 'products.id', '=', 'product_images.product_id')
            ->leftJoin('product_inventories', 'product_flat.product_id', '=', 'product_inventories.product_id')
            ->leftJoin('pos_outlet_product', function ($qb) {
                $qb->on('pos_outlet_product.product_id', '=', 'products.id')
                    ->leftJoin('pos_outlets', 'pos_outlet_product.outlet_id', '=', 'pos_outlets.id')
                    ->where('pos_outlet_product.outlet_id', $this->posOutlet->id);
            })
            ->addSelect(
                'products.type',
                'product_flat.name as product_name',
                'product_flat.sku as product_sku',
                'product_flat.price as product_price',
                'product_flat.status as product_status',
                'pos_outlet_product.status as pos_status',
                DB::raw(DB::getTablePrefix().'products.id as product_id'),
                DB::raw(DB::getTablePrefix().'product_images.path as product_image'),
                DB::raw('SUM('.DB::getTablePrefix().'product_inventories.qty) as product_quantity'),
            )
            ->whereNull('products.parent_id')
            ->where('product_flat.locale', app()->getLocale())
            ->groupBy('product_id');

        $this->addFilter('product_id', 'products.id');
        $this->addFilter('type', 'products.type');
        $this->addFilter('product_name', 'product_flat.name');
        $this->addFilter('product_sku', 'product_flat.sku');
        $this->addFilter('product_price', 'product_flat.price');
        $this->addFilter('product_quantity', 'product_inventories.qty');
        $this->addFilter('product_status', 'product_flat.status');
        $this->addFilter('pos_status', 'pos_outlet_product.status');

        return $queryBuilder;
    }

    /**
     * Prepare columns.
     *
     * @return void
     */
    public function prepareColumns()
    {
        $this->addColumn([
            'index'      => 'product_id',
            'label'      => trans('pos::app.admin.users.outlets.assign.datagrid.id'),
            'type'       => 'integer',
            'sortable'   => true,
            'filterable' => true,
            'searchable' => true,
        ]);

        $this->addColumn([
            'index'      => 'product_image',
            'label'      => trans('pos::app.admin.users.outlets.assign.datagrid.image'),
            'type'       => 'string',
            'sortable'   => false,
            'filterable' => false,
            'searchable' => false,
        ]);

        $this->addColumn([
            'index'      => 'product_name',
            'label'      => trans('pos::app.admin.users.outlets.assign.datagrid.name'),
            'type'       => 'string',
            'sortable'   => true,
            'filterable' => true,
            'searchable' => true,
        ]);

        $this->addColumn([
            'index'      => 'product_sku',
            'label'      => trans('pos::app.admin.users.outlets.assign.datagrid.sku'),
            'type'       => 'string',
            'sortable'   => true,
            'filterable' => true,
            'searchable' => true,
        ]);

        $this->addColumn([
            'index'      => 'type',
            'label'      => trans('pos::app.admin.users.outlets.assign.datagrid.type'),
            'type'       => 'string',
            'sortable'   => false,
            'filterable' => false,
            'searchable' => true,
        ]);

        $this->addColumn([
            'index'      => 'product_price',
            'label'      => trans('pos::app.admin.users.outlets.assign.datagrid.price'),
            'type'       => 'integer',
            'sortable'   => true,
            'filterable' => true,
            'searchable' => true,
        ]);

        $this->addColumn([
            'index'      => 'product_quantity',
            'label'      => trans('pos::app.admin.users.outlets.assign.datagrid.qty'),
            'type'       => 'integer',
            'sortable'   => true,
            'filterable' => true,
            'searchable' => false,
            'closure'    => function ($row) {
                return $this->posOutletProductRepository->getProductTotalQty($row->product_id, $this->posOutlet->inventory_source_id);
            },
        ]);

        $this->addColumn([
            'index'      => 'product_status',
            'label'      => trans('pos::app.admin.users.outlets.assign.datagrid.status'),
            'type'       => 'boolean',
            'sortable'   => true,
            'filterable' => true,
            'searchable' => false,
        ]);

        $this->addColumn([
            'index'      => 'pos_status',
            'label'      => trans('pos::app.admin.users.outlets.assign.datagrid.pos-status'),
            'type'       => 'boolean',
            'sortable'   => true,
            'filterable' => false,
            'searchable' => false,
        ]);
    }

    /**
     * Prepare Mass Actions
     *
     * @return void
     */
    public function prepareActions()
    {
        if (bouncer()->hasPermission('pos.users.outlets.assign_products')) {
            $this->addMassAction([
                'type'    => 'update',
                'title'   => trans('pos::app.admin.users.outlets.assign.datagrid.update-assign'),
                'url'     => route('admin.pos.outlets.assign_products.mass_assign', request('id')),
                'method'  => 'POST',
                'options' => [
                    [
                        'label' => trans('pos::app.admin.users.outlets.assign.datagrid.assign'),
                        'value' => 1,
                    ], [
                        'label' => trans('pos::app.admin.users.outlets.assign.datagrid.unassign'),
                        'value' => 0,
                    ],
                ],
            ]);
        }
    }
}
