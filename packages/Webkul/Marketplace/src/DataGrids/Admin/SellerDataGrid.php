<?php

namespace Webkul\Marketplace\DataGrids\Admin;

use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;
use Webkul\DataGrid\DataGrid;
use Webkul\Marketplace\Enums\Seller;

class SellerDataGrid extends DataGrid
{
    /**
     * Prepare query builder.
     */
    public function prepareQueryBuilder(): Builder
    {
        // --- START: CORRECTED SUBQUERY ---
        // This subquery now selects BOTH the seller_id and the max id.
        $latestCreditRequest = DB::table('seller_wallets')
            ->select('seller_id', DB::raw('MAX(id) as id'))
            ->where('status', 'pending_approval')
            ->groupBy('seller_id');
        // --- END: CORRECTED SUBQUERY ---

        $queryBuilder = DB::table('marketplace_sellers')
            ->leftJoin('mp_seller_flags', 'marketplace_sellers.id', '=', 'mp_seller_flags.marketplace_seller_id')
            
            ->leftJoin(DB::raw('(SELECT seller_id, SUM(amount) as wallet_balance FROM ' . DB::getTablePrefix() . 'seller_wallets WHERE status = "success" GROUP BY seller_id) as wallet'), function($join) {
                $join->on('marketplace_sellers.id', '=', 'wallet.seller_id');
            })

            ->leftJoinSub($latestCreditRequest, 'latest_request_ids', function ($join) {
                $join->on('marketplace_sellers.id', '=', 'latest_request_ids.seller_id');
            })
            ->leftJoin('seller_wallets as credit_request', 'latest_request_ids.id', '=', 'credit_request.id')

            ->select(
                'marketplace_sellers.id',
                'marketplace_sellers.created_at',
                'marketplace_sellers.email',
                'marketplace_sellers.is_approved',
                'marketplace_sellers.name',
                'marketplace_sellers.url',
                'marketplace_sellers.is_suspended',
                DB::raw('COUNT(DISTINCT '.DB::getTablePrefix().'mp_seller_flags.id) as flags'),
                'wallet.wallet_balance',
                'credit_request.amount as credit_request_amount',
                'credit_request.created_at as credit_request_date'
            )
            ->whereNull('marketplace_sellers.parent_id')
            ->groupBy('marketplace_sellers.id');

        $this->addFilter('id', 'marketplace_sellers.id');
        $this->addFilter('name', 'marketplace_sellers.name');
        $this->addFilter('email', 'marketplace_sellers.email');
        $this->addFilter('created_at', 'marketplace_sellers.created_at');

        return $queryBuilder;
    }

    /**
     * Prepare columns.
     */
    public function prepareColumns(): void
    {
        // All other columns remain the same...

        $this->addColumn([
            'index'      => 'name',
            'label'      => trans('marketplace::app.admin.sellers.index.datagrid.seller-name'),
            'type'       => 'string',
            'searchable' => true,
            'sortable'   => true,
            'filterable' => true,
        ]);

        $this->addColumn([
            'index'      => 'email',
            'label'      => trans('marketplace::app.admin.sellers.index.datagrid.email'),
            'type'       => 'string',
            'searchable' => true,
            'sortable'   => true,
            'filterable' => true,
        ]);

        $this->addColumn([
            'index'      => 'url',
            'label'      => trans('marketplace::app.admin.sellers.index.datagrid.shop-url'),
            'type'       => 'integer',
            'searchable' => false,
            'sortable'   => true,
            'filterable' => false,
        ]);

        $this->addColumn([
            'index'              => 'is_approved',
            'label'              => trans('marketplace::app.admin.sellers.index.datagrid.status'),
            'type'               => 'string',
            'filterable'         => true,
            'filterable_type'    => 'dropdown',
            'filterable_options' => [
                [
                    'label' => trans('marketplace::app.admin.sellers.index.datagrid.approved'),
                    'value' => Seller::APPROVED->value(),
                ],
                [
                    'label' => trans('marketplace::app.admin.sellers.index.datagrid.disapproved'),
                    'value' => Seller::DISAPPROVED->value(),
                ],
            ],
            'sortable'   => true,
            'searchable' => false,
            'closure'    => function ($row) {
                if ($row->is_approved) {
                    return '<label class="label-active py-1">'.trans('marketplace::app.admin.sellers.index.datagrid.approved').'</label>';
                }
                return '<label class="label-info py-1">'.trans('marketplace::app.admin.sellers.index.datagrid.disapproved').'</label>';
            },
        ]);

        $this->addColumn([
            'index'      => 'is_suspended',
            'label'      => trans('marketplace::app.admin.sellers.index.datagrid.suspended'),
            'type'       => 'boolean',
            'filterable' => true,
        ]);

        $this->addColumn([
            'index'           => 'id',
            'label'           => trans('marketplace::app.admin.sellers.index.datagrid.id'),
            'type'            => 'integer',
            'filterable'      => true,
            'filterable_type' => 'number',
            'searchable'      => false,
            'sortable'        => true,
        ]);

        $this->addColumn([
            'index'           => 'created_at',
            'label'           => trans('marketplace::app.admin.sellers.index.datagrid.created-at'),
            'type'            => 'date',
            'filterable'      => true,
            'filterable_type' => 'date_range',
            'sortable'        => true,
            'searchable'      => false,
        ]);

        if (bouncer()->hasPermission('marketplace.sellers.assign_product')) {
            $this->addColumn([
                'index'      => 'assign_product',
                'label'      => trans('marketplace::app.admin.sellers.index.datagrid.assign-product'),
                'type'       => 'string',
                'filterable' => false,
                'sortable'   => false,
                'searchable' => false,
            ]);
        }

        $this->addColumn([
            'index'      => 'flags',
            'label'      => trans('marketplace::app.admin.sellers.index.datagrid.flags'),
            'type'       => 'integer',
            'searchable' => false,
            'sortable'   => true,
            'filterable' => false,
        ]);

        $this->addColumn([
            'index'      => 'wallet_balance',
            'label'      => 'Wallet',
            'type'       => 'string',
            'sortable'   => true,
            'searchable' => false,
            'filterable' => false,
            'closure'    => function ($row) {
                $balance = $row->wallet_balance ?? 0;
                return core()->currency($balance);
            },
        ]);

        $this->addColumn([
            'index'      => 'credit_request_date',
            'label'      => 'Credit Request',
            'type'       => 'string',
            'sortable'   => true,
            'searchable' => false,
            'filterable' => false,
            'closure'    => function ($row) {
                if ($row->credit_request_amount) {
                    return core()->currency($row->credit_request_amount) . ' on ' . core()->formatDate($row->credit_request_date, 'Y-m-d');
                }
                return '—';
            },
        ]);
    }

    /**
     * Prepare actions.
     */
    public function prepareActions(): void
    {
        // Unchanged
        if (bouncer()->hasPermission('marketplace.sellers.edit')) {
            $this->addAction([
                'icon'   => 'icon-exit',
                'title'  => trans('marketplace::app.admin.sellers.index.datagrid.login-as-seller'),
                'method' => 'GET',
                'url'    => function ($row) {
                    return route('admin.marketplace.sellers.login_as_seller', $row->id);
                },
            ]);

            $this->addAction([
                'icon'   => 'icon-edit',
                'title'  => trans('marketplace::app.admin.sellers.index.datagrid.edit'),
                'method' => 'GET',
                'url'    => function ($row) {
                    return route('admin.marketplace.sellers.edit', $row->id);
                },
            ]);
        }

        if (bouncer()->hasPermission('marketplace.sellers.delete')) {
            $this->addAction([
                'icon'   => 'icon-delete',
                'title'  => trans('marketplace::app.admin.sellers.index.datagrid.delete'),
                'method' => 'DELETE',
                'url'    => function ($row) {
                    return route('admin.marketplace.sellers.delete', $row->id);
                },
            ]);
        }
    }

    /**
     * Prepare mass actions.
     */
    public function prepareMassActions(): void
    {
        // Unchanged
        if (bouncer()->hasPermission('marketplace.sellers.edit')) {
            $this->addMassAction([
                'title'   => trans('marketplace::app.admin.sellers.index.datagrid.update-status'),
                'url'     => route('admin.marketplace.sellers.mass_update'),
                'method'  => 'POST',
                'options' => [
                    [
                        'label' => trans('marketplace::app.admin.sellers.index.datagrid.approved'),
                        'value' => Seller::APPROVED->value(),
                    ],
                    [
                        'label' => trans('marketplace::app.admin.sellers.index.datagrid.disapproved'),
                        'value' => Seller::DISAPPROVED->value(),
                    ],
                ],
            ]);
        }

        if (bouncer()->hasPermission('marketplace.sellers.delete')) {
            $this->addMassAction([
                'title'  => trans('marketplace::app.admin.sellers.index.datagrid.delete'),
                'url'    => route('admin.marketplace.sellers.mass_delete'),
                'method' => 'POST',
            ]);
        }
    }
}

