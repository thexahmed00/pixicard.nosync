<?php

namespace Webkul\Pos\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class DataGridExport implements FromView, ShouldAutoSize
{
    /**
     * DataGrid instance
     *
     * @var mixed
     */
    protected $gridData = [];

    /**
     * DataGrid instance
     *
     * @var mixed
     */
    protected $exportColumns = [
        'created_at',
        'sales_type',
        'order_increment_id',
        'outlet_name',
        'order_note',
        'payment_type',
        'bank_name',
        'order_status',
    ];

    /**
     * Create a new instance.
     *
     * @param mixed DataGrid
     * @return void
     */
    public function __construct($gridData)
    {
        $this->gridData = $gridData;
    }

    /**
     * function to create a blade view for export.
     */
    public function view(): View
    {
        $columns = [];

        foreach ($this->gridData as $key => $gridData) {
            $columns = array_keys((array) $gridData);

            break;
        }

        foreach ($this->gridData as $key => $gridData) {
            $allow_records = [];
            $record_array = (array) $gridData;

            foreach ($record_array as $index => $record) {
                if (in_array($index, $this->exportColumns)) {
                    if ($index == 'sales_type') {
                        if (! $record) {
                            $record = 'Website';
                        } else {
                            $record = 'Pos';
                        }
                    }

                    if ($index == 'payment_type') {
                        $record = $record_array['method_title'];
                    }

                    if ($index == 'order_increment_id') {
                        $allow_records['id'] = $record;
                    } else {
                        $allow_records[$index] = $record;
                    }
                }
            }
            $this->gridData[$key] = (object) $allow_records;
        }

        $allow_columns = [];

        foreach ($columns as $index => $column) {
            if (in_array($column, $this->exportColumns)) {
                if ($column == 'order_increment_id') {
                    $allow_columns[] = 'id';
                } else {
                    $allow_columns[] = $column;
                }
            }
        }

        return view('admin::export.temp', [
            'columns' => $allow_columns,
            'records' => $this->gridData,
        ]);
    }
}
