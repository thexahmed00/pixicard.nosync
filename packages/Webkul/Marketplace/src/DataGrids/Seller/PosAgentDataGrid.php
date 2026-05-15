<?php

namespace Webkul\Marketplace\DataGrids\Seller;

use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;
use Webkul\DataGrid\DataGrid;
use Webkul\Marketplace\Enums\Status;

class PosAgentDataGrid extends DataGrid
{
    /**
     * Primary column.
     *
     * @var string
     */
    protected $primaryColumn = 'user_id';

    /**
     * Prepare query builder.
     */
     public function prepareQueryBuilder(): Builder
    {
         $seller = auth()->guard('seller')->user();
         $queryBuilder = DB::table('pos_users as pu')
            ->leftJoin('pos_outlets as po', 'pu.outlet_id', '=', 'po.id')
            ->select(
                'pu.id as user_id',
                'pu.username as user_name',
                'pu.email as user_email',
                'pu.image as user_image',
                'po.name as outlet_name',
                'pu.status as user_status'
            )
            ->addSelect(DB::raw('CONCAT(pu.firstname, " ", pu.lastname) as full_name')
            )
            ->where('pu.created_by', $seller->id);  

        $this->addFilter('user_id', 'pu.id');
        $this->addFilter('user_name', 'pu.username');
        $this->addFilter('user_email', 'pu.email');
        $this->addFilter('outlet_name', 'po.name');
        $this->addFilter('user_status', 'pu.status');
        $this->addFilter('full_name', DB::raw('CONCAT(pu.firstname, " ", pu.lastname)'));

        return $queryBuilder;
    }

    /**
     * Prepare columns.
     */
    public function prepareColumns(): void
    {
       $this->addColumn([
            'index'      => 'user_id',
            'label'      => trans('pos::app.admin.users.users.index.datagrid.id'),
            'type'       => 'integer',
            'sortable'   => true,
            'filterable' => true,
            'searchable' => true,
        ]);

        $this->addColumn([
            'index'      => 'user_image',
            'label'      => trans('pos::app.admin.users.users.index.datagrid.profile-image'),
            'type'       => 'string',
            'searchable' => false,
            'sortable'   => false,
            'filterable' => false,
        ]);

        $this->addColumn([
            'index'      => 'user_name',
            'label'      => trans('pos::app.admin.users.users.index.datagrid.username'),
            'type'       => 'string',
            'sortable'   => true,
            'filterable' => true,
            'searchable' => true,
        ]);

        $this->addColumn([
            'index'      => 'full_name',
            'label'      => trans('pos::app.admin.users.users.index.datagrid.full-name'),
            'type'       => 'string',
            'sortable'   => true,
            'filterable' => true,
            'searchable' => true,
        ]);

        $this->addColumn([
            'index'      => 'user_email',
            'label'      => trans('pos::app.admin.users.users.index.datagrid.email'),
            'type'       => 'string',
            'sortable'   => true,
            'filterable' => true,
            'searchable' => true,
        ]);

        $this->addColumn([
            'index'      => 'outlet_name',
            'label'      => trans('pos::app.admin.users.users.index.datagrid.outlet-name'),
            'type'       => 'string',
            'sortable'   => true,
            'filterable' => true,
            'searchable' => true,
        ]);

        $this->addColumn([
            'index'      => 'user_status',
            'label'      => trans('pos::app.admin.users.users.index.datagrid.status.title'),
            'type'       => 'boolean',
            'sortable'   => true,
            'filterable' => false,
            'searchable' => false,
        ]);
    }

    /**
     * Prepare actions (Edit / Delete).
     */
    public function prepareActions(): void
    {
    
            $this->addAction([
                'icon'   => 'icon-edit',
                'title'  => trans('pos::app.admin.users.users.index.datagrid.edit'),
                'method' => 'GET',
                'url'    => function ($row) {
                    return route('seller.pos_users.edit', $row->user_id);
                },
            ]);
        
            $this->addAction([
                'icon'   => 'icon delete',
                'title'  => trans('pos::app.admin.users.users.index.datagrid.delete'),
                'method' => 'POST',
                'url'    => function ($row) {
                    return route('seller.pos_users.destroy', $row->user_id);
                },
            ]);
       
    }
}
