<?php

namespace Webkul\Marketplace\DataGrids\Seller;

use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;
use Webkul\DataGrid\DataGrid;

class WalletDataGrid extends DataGrid
{
    /**
     * Primary column.
     *
     * @var string
     */
    protected $primaryColumn = 'id';

    /**
     * Prepare query builder.
     */
    public function prepareQueryBuilder(): Builder
    {
        $sellerId = auth()->guard('seller')->user()->id;

        $queryBuilder = DB::table('seller_wallets')
            ->addSelect('id', 'vendor_id', 'amount', 'transaction_id', 'created_at', 'status')
            ->where('seller_id', $sellerId);

        // No filters are defined here. They are defined in prepareColumns().

        return $queryBuilder;
    }

    /**
     * Prepare columns.
     */
    public function prepareColumns(): void
    {
        $this->addColumn([
            'index' => 'vendor_id',
            'label' => trans('marketplace::app.seller.wallet.datagrid.vendor'),
            'type' => 'string',
            'searchable' => false,
            'sortable' => true,
            'closure' => function ($row) {
                return $row->vendor_id == 1 ? 'LikeCard' : $row->vendor_id;
            },
        ]);

        $this->addColumn([
            'index' => 'amount',
            'label' => trans('marketplace::app.seller.wallet.datagrid.amount'),
            'type' => 'string',
            'searchable' => false,
            'sortable' => true,
            'closure' => function ($row) {
                return core()->currency($row->amount);
            },
        ]);

        $this->addColumn([
            'index' => 'transaction_id',
            'label' => trans('marketplace::app.seller.wallet.datagrid.transaction-id'),
            'type' => 'integer',
            'searchable' => false,
            'sortable' => true,
        ]);

        // --- THIS IS THE CORRECTED STATUS COLUMN ---
        $this->addColumn([
            'index' => 'status',
            'label' => trans('marketplace::app.seller.wallet.datagrid.status'),
            'type' => 'string',
            'filterable' => true, // Must be true to enable filtering
            'filterable_type' => 'dropdown', // Defines the filter type
            'filterable_options' => [ // Provides the options for the dropdown
                ['label' => trans('marketplace::app.seller.wallet.datagrid.pending'), 'value' => 'pending'],
                ['label' => trans('marketplace::app.seller.wallet.datagrid.pending-approval'), 'value' => 'pending_approval'],
                ['label' => trans('marketplace::app.seller.wallet.datagrid.success'), 'value' => 'success'],
                ['label' => trans('marketplace::app.seller.wallet.datagrid.failed'), 'value' => 'failed'],
                ['label' => trans('marketplace::app.seller.wallet.datagrid.rejected'), 'value' => 'rejected'],
            ],
            'sortable' => true,
            'closure' => function ($row) {
                $status = ucfirst($row->status);

                $transKey = 'marketplace::app.seller.wallet.datagrid.' . $row->status;
                $translatedStatus = strip_tags(trans($transKey)); // Fallback or direct check
                // If translation key exists, use it, else usage original. 
                // However, since we defined keys for all statuses, we can use them.
                if ($row->status == 'pending_approval') {
                    $translatedStatus = trans('marketplace::app.seller.wallet.datagrid.pending-approval');
                } else {
                    $translatedStatus = trans('marketplace::app.seller.wallet.datagrid.' . strtolower($row->status));
                }

                if ($row->status == 'pending') {
                    return "<span class='label-pending'>$translatedStatus</span>";
                } elseif ($row->status == 'pending_approval') {
                    return "<span class='label-pending'>$translatedStatus</span>";
                } elseif ($row->status == 'rejected') {
                    return "<span class='label-canceled'>$translatedStatus</span>";
                } elseif ($row->status == 'success') {
                    return "<span class='label-active'>$translatedStatus</span>";
                } elseif ($row->status == 'failed') {
                    return "<span class='label-canceled'>$translatedStatus</span>";
                }

                return $translatedStatus;
            },
        ]);
        // --- END OF CORRECTION ---

        $this->addColumn([
            'index' => 'created_at',
            'label' => 'Date',
            'type' => 'string',
            'sortable' => true,
            'closure' => function ($row) {
                return date('d-m-Y H:i:s', strtotime($row->created_at));
            },
        ]);
    }

    /**
     * Actions are removed by commenting out or deleting this method.
     */
    /*
    public function prepareActions(): void
    {
        // Actions are intentionally left empty
    }
    */
}
