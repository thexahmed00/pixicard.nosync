<?php

namespace Webkul\Pos\DataGrids\Admin;

use Illuminate\Support\Facades\DB;
use Webkul\DataGrid\DataGrid;

class BankDataGrid extends DataGrid
{
    /**
     * Primary column.
     *
     * @var string
     */
    protected $primaryColumn = 'bank_id';

    /**
     * Prepare query builder.
     *
     * @return \Illuminate\Database\Query\Builder
     */
    public function prepareQueryBuilder()
    {
        $queryBuilder = DB::table('pos_banks as pb')
            ->leftJoin('pos_users as pu', 'pb.agent_id', '=', 'pu.id')
            ->select(
                'pb.id as bank_id',
                'pb.agent_id',
                'pb.name as bank_name',
                'pb.address as bank_address',
                'pb.email as bank_email',
                'pb.phone as bank_phone',
                'pb.status as bank_status'
            )
            ->addSelect(DB::raw(DB::getTablePrefix().'CONCAT(pu.firstname, " ", pu.lastname) as agent_name'));

        $this->addFilter('bank_id', 'pb.id');
        $this->addFilter('bank_name', 'pb.name');
        $this->addFilter('bank_email', 'pb.email');
        $this->addFilter('bank_phone', 'pb.phone');
        $this->addFilter('bank_address', 'pb.address');
        $this->addFilter('agent_name', DB::raw(DB::getTablePrefix().'CONCAT(pu.firstname, " ", pu.lastname)'));
        $this->addFilter('bank_status', 'pb.status');

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
            'index'      => 'bank_id',
            'label'      => trans('pos::app.admin.banks.index.datagrid.id'),
            'type'       => 'integer',
            'searchable' => true,
            'filterable' => true,
            'sortable'   => true,
        ]);

        $this->addColumn([
            'index'      => 'bank_name',
            'label'      => trans('pos::app.admin.banks.index.datagrid.name'),
            'type'       => 'string',
            'searchable' => true,
            'filterable' => true,
            'sortable'   => true,
        ]);

        $this->addColumn([
            'index'      => 'bank_address',
            'label'      => trans('pos::app.admin.banks.index.datagrid.address'),
            'type'       => 'string',
            'searchable' => true,
            'filterable' => true,
            'sortable'   => true,
        ]);

        $this->addColumn([
            'index'      => 'agent_name',
            'label'      => trans('pos::app.admin.banks.index.datagrid.agent-name'),
            'type'       => 'string',
            'searchable' => true,
            'filterable' => true,
            'sortable'   => true,
            'closure'    => function ($row) {
                if ($row->agent_id) {
                    return '<a href="'.route('admin.pos.users.edit', $row->agent_id).'">'.$row->agent_name.'</a>';
                }

                return '-';
            },
        ]);

        $this->addColumn([
            'index'      => 'bank_status',
            'label'      => trans('pos::app.admin.banks.index.datagrid.status'),
            'type'       => 'boolean',
            'sortable'   => true,
            'searchable' => false,
            'filterable' => false,
            'closure'    => function ($row) {
                if ($row->bank_status) {
                    return '<p class="label-active">'.trans('pos::app.admin.banks.index.datagrid.active').'</p>';
                }

                return '<p class="label-info">'.trans('pos::app.admin.banks.index.datagrid.disable').'</p>';
            },
        ]);
    }

    /**
     * Prepare Actions
     *
     * @return void
     */
    public function prepareActions()
    {
        if (bouncer()->hasPermission('pos.banks.edit')) {
            $this->addAction([
                'icon'   => 'icon-edit',
                'title'  => trans('pos::app.admin.banks.index.datagrid.edit'),
                'method' => 'GET',
                'url'    => function ($row) {
                    return route('admin.pos.banks.edit', $row->bank_id);
                },
            ]);
        }

        if (bouncer()->hasPermission('pos.banks.delete')) {
            $this->addAction([
                'icon'   => 'icon-delete',
                'title'  => trans('pos::app.admin.banks.index.datagrid.delete'),
                'method' => 'DELETE',
                'url'    => function ($row) {
                    return route('admin.pos.banks.delete', $row->bank_id);
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
        if (bouncer()->hasPermission('pos.banks.delete')) {
            $this->addMassAction([
                'title'  => trans('pos::app.admin.banks.index.datagrid.delete'),
                'method' => 'POST',
                'url'    => route('admin.pos.banks.mass_delete'),
            ]);
        }
    }
}
