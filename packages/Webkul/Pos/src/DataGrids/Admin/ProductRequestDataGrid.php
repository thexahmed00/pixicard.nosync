<?php

namespace Webkul\Pos\DataGrids\Admin;

use Illuminate\Support\Facades\DB;
use Webkul\DataGrid\DataGrid;

class ProductRequestDataGrid extends DataGrid
{
    /**
     * Primary column.
     *
     * @var string
     */
    protected $primaryColumn = 'request_id';

    /**
     * Prepare query builder.
     *
     * @return \Illuminate\Database\Query\Builder
     */
    public function prepareQueryBuilder()
    {
        $queryBuilder = DB::table('pos_product_request as pr')
            ->leftJoin('products as p', 'pr.product_id', '=', 'p.id')
            ->leftJoin('product_images as pi', 'p.id', '=', 'pi.product_id')
            ->leftJoin('product_flat as pf', 'pf.product_id', '=', 'p.id')
            ->leftJoin('pos_users as u', 'u.id', '=', 'pr.user_id')
            ->leftJoin('pos_outlets as po', 'po.id', '=', 'u.outlet_id')
            ->select(
                'pr.id as request_id',
                'p.id as product_id',
                'pi.path as product_image',
                'pf.name as product_name',
                'po.name as outlet_name',
                'pr.created_at as request_date',
                'pr.requested_quantity as quantity',
                'pr.request_status as request_status',
                'pr.comment as comment'
            )
            ->addSelect(DB::raw('CONCAT('.DB::getTablePrefix()."u.firstname, ' ', ".DB::getTablePrefix().'u.lastname) as user_name'))
            ->where('pr.send_status', 1)
            ->where('pf.locale', app()->getLocale())
            ->groupBy('pr.id');

        $this->addFilter('request_id', 'pr.id');
        $this->addFilter('product_id', 'pr.product_id');
        $this->addFilter('product_name', 'pf.name');
        $this->addFilter('user_name', DB::raw('CONCAT('.DB::getTablePrefix()."u.firstname, ' ', ".DB::getTablePrefix().'u.lastname)'));
        $this->addFilter('outlet_name', 'po.name');
        $this->addFilter('request_date', 'pr.created_at');
        $this->addFilter('quantity', 'pr.requested_quantity');

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
            'index'      => 'request_id',
            'label'      => trans('pos::app.admin.requests.index.datagrid.id'),
            'type'       => 'integer',
            'sortable'   => true,
            'filterable' => true,
            'searchable' => true,
        ]);

        $this->addColumn([
            'index'      => 'product_image',
            'label'      => trans('pos::app.admin.requests.index.datagrid.product-image'),
            'type'       => 'string',
            'sortable'   => false,
            'filterable' => false,
            'searchable' => false,
        ]);

        $this->addColumn([
            'index'      => 'product_name',
            'label'      => trans('pos::app.admin.requests.index.datagrid.product-name'),
            'type'       => 'string',
            'sortable'   => true,
            'filterable' => true,
            'searchable' => true,
        ]);

        $this->addColumn([
            'index'      => 'user_name',
            'label'      => trans('pos::app.admin.requests.index.datagrid.user-name'),
            'type'       => 'string',
            'sortable'   => true,
            'filterable' => true,
            'searchable' => true,
        ]);

        $this->addColumn([
            'index'      => 'outlet_name',
            'label'      => trans('pos::app.admin.requests.index.datagrid.outlet-name'),
            'type'       => 'string',
            'sortable'   => true,
            'filterable' => true,
            'searchable' => true,
        ]);

        $this->addColumn([
            'index'      => 'quantity',
            'label'      => trans('pos::app.admin.requests.index.datagrid.requested-qty'),
            'type'       => 'integer',
            'sortable'   => true,
            'filterable' => true,
            'searchable' => false,
        ]);

        $this->addColumn([
            'index'      => 'request_date',
            'label'      => trans('pos::app.admin.requests.index.datagrid.request-date'),
            'type'       => 'datetime',
            'sortable'   => true,
            'filterable' => true,
            'searchable' => true,
        ]);

        $this->addColumn([
            'index'      => 'request_status',
            'label'      => trans('pos::app.admin.requests.index.datagrid.status.title'),
            'type'       => 'boolean',
            'sortable'   => true,
            'filterable' => true,
            'searchable' => false,
        ]);
    }

    /**
     * Prepare Actions
     *
     * @return void
     */
    public function prepareActions()
    {
        if (bouncer()->hasPermission('pos.requests.view')) {
            $this->addAction([
                'icon'   => 'icon-view',
                'title'  => trans('pos::app.admin.requests.index.datagrid.view'),
                'method' => 'GET',
                'url'    => function ($row) {
                    return route('admin.pos.requests.view', $row->request_id);
                },
            ]);
        }
    }

    /**
     * Prepare Mass Actions
     *
     * @return void
     */
    public function prepareMassActions()
    {
        if (bouncer()->hasPermission('pos.requests.view')) {
            $this->addMassAction([
                'title'   => trans('pos::app.admin.requests.index.datagrid.update-status'),
                'url'     => route('admin.pos.requests.mass_update'),
                'method'  => 'POST',
                'options' => [
                    [
                        'label' => trans('pos::app.admin.requests.index.datagrid.status.options.complete'),
                        'value' => 1,
                    ],
                    [
                        'label' => trans('pos::app.admin.requests.index.datagrid.status.options.decline'),
                        'value' => 2,
                    ],
                    [
                        'label' => trans('pos::app.admin.requests.index.datagrid.status.options.pending'),
                        'value' => 0,
                    ],
                ],
            ]);
        }
    }
}
