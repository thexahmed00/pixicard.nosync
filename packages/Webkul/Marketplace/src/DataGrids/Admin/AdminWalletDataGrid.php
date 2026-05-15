<?php

namespace Webkul\Marketplace\DataGrids\Admin;

use Webkul\DataGrid\DataGrid;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AdminWalletDataGrid extends DataGrid
{
    protected $primaryColumn = 'id';

    public function prepareQueryBuilder()
    {
        $sellerId = request()->route('id');

        $queryBuilder = DB::table('seller_wallets')
            ->select('id', 'seller_id', 'vendor_id', 'amount', 'type', 'transaction_id', 'status', 'created_at')
            ->where('seller_id', $sellerId);

        return $queryBuilder;
    }

    public function prepareColumns(): void
    {
        $this->addColumn([
            'index'   => 'vendor_id',
            'label'   => 'Vendor',
            'type'    => 'string',
            'sortable'=> true,
            'closure' => function ($row) {
                return $row->vendor_id == 1 ? 'LikeCard' : $row->vendor_id;
            },
        ]);

        $this->addColumn([
            'index'   => 'amount',
            'label'   => 'Amount',
            'type'    => 'string',
            'sortable'=> true,
            'closure' => function ($row) {
                return core()->currency($row->amount);
            },
        ]);

       $this->addColumn([
            'index'              => 'type',
            'label'              => 'Type',
            'type'               => 'string',
            'filterable'         => true,
            'filterable_type'    => 'dropdown',
            'filterable_options' => [
                ['label' => 'Credit', 'value' => 'credit'],
                ['label' => 'EdfaPay', 'value' => 'edfapay'],
            ],
            'sortable'           => true,
        ]);

        $this->addColumn([
            'index'   => 'transaction_id',
            'label'   => 'Transaction ID',
            'type'    => 'integer',
            'sortable'=> true,
        ]);

        // --- CORRECTED STATUS COLUMN ---
        // In your WalletDataGrid.php -> prepareColumns() method

        $this->addColumn([
            'index'              => 'status',
            'label'              => 'Status',
            'type'               => 'string',
            'filterable'         => true, // This must be true
            'filterable_type'    => 'dropdown', // This defines the filter UI
            'filterable_options' => [ // This provides the dropdown options
                ['label' => 'Pending', 'value' => 'pending'],
                ['label' => 'Pending Approval', 'value' => 'pending_approval'],
                ['label' => 'Rejected', 'value' => 'rejected'],
                ['label' => 'Success', 'value' => 'success'],
                ['label' => 'Failed', 'value' => 'failed'],
            ],
            'sortable'           => true,
            'closure'            => function ($row) {
                $status = ucfirst($row->status);
                if ($row->status == 'pending') {
                    return "<span class='label-pending'>$status</span>";
                }
                if ($row->status == 'pending_approval') {
                    return "<span class='label-pending'>Pending Approval</span>";
                }
                if ($row->status == 'rejected') {
                    return "<span class='label-canceled'>Rejected</span>";
                }
                if ($row->status == 'success') {
                    return "<span class='label-active'>$status</span>";
                }
                if ($row->status == 'failed') {
                    return "<span class='label-canceled'>$status</span>";
                }
                return $status;
            },
        ]);

        // --- END OF CORRECTION ---

        $this->addColumn([
            'index'   => 'created_at',
            'label'   => 'Date',
            'type'    => 'string',
            'sortable'=> true,
            'closure' => function ($row) {
                return date('Y-m-d H:i:s', strtotime($row->created_at));
            },
        ]);
    }

    public function prepareActions(): void
    {
        // Approve Action
        $this->addAction([
            'icon'   => 'icon-tick text-green-600', // Correct icon class
            'title'  => 'Approve',
            'method' => 'POST',
            'url'    => function ($row) {
                return route('admin.marketplace.sellers.wallet.update_status', ['id' => $row->id, 'status' => 'success']);
            },
            'condition' => function ($row) {
                return $row->status == 'pending_approval';
            }
        ]);

        // Reject Action
        $this->addAction([
            'icon'   => 'icon-cancel text-red-600', // Correct icon class
            'title'  => 'Reject',
            'method' => 'POST',
            'url'    => function ($row) {
                return route('admin.marketplace.sellers.wallet.update_status', ['id' => $row->id, 'status' => 'rejected']);
            },
            'condition' => function ($row) {
                return $row->status == 'pending_approval';
            }
        ]);
    }

    protected function formatRecords($records): mixed
    {
        foreach ($records as $record) {
            // --- THE FIX ---
            // Store the original status before it gets replaced by the closure.
            $original_status = $record->status;
            // ---------------

            $record = $this->sanitizeRow($record);

            foreach ($this->columns as $column) {
                if ($closure = $column->getClosure()) {
                    $record->{$column->getIndex()} = $closure($record);
                }
            }

            $record->actions = [];

            // Use the original status for the comparison.
            if ($original_status == 'pending_approval') {
                foreach ($this->actions as $index => $action) {
                    $getUrl = $action->url;

                    $record->actions[] = [
                        'index'  => ! empty($action->index) ? $action->index : 'action_'.$index + 1,
                        'icon'   => $action->icon,
                        'title'  => $action->title,
                        'method' => $action->method,
                        'url'    => $getUrl($record),
                        'class'  => $action->class ?? '',
                    ];
                }
            }
        }

        return $records;
    }
}

