<?php

namespace Webkul\Pos\Repositories;

use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Storage;
use Webkul\Core\Eloquent\Repository;
use Webkul\Pos\Contracts\PosReceipt;

class PosReceiptRepository extends Repository
{
    /**
     * Contains boolean variables
     *
     * @var array
     */
    protected $boolean_index = [
        'status',
        'display_outlet_name',
        'display_date',
        'display_order_id',
        'display_customer_name',
        'display_sub_total',
        'display_tax',
        'display_credit_amount',
        'display_change_amount',
        'display_cashier_name',
        'display_outlet_address',
        'display_discount',
        'display_logo',
    ];

    /**
     * Specify Model class name
     *
     * @return string
     */
    public function model()
    {
        return PosReceipt::class;
    }

    /**
     * Create POS receipt
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function create(array $data)
    {
        Event::dispatch('pos.receipt.create.before');

        if (! empty($data['logo'])) {
            $logo = $data['logo'];
            unset($data['logo']);
        }

        $posReceipt = parent::create($data);

        if (! empty($logo)) {
            $this->uploadImage($logo, $posReceipt);
        }

        Event::dispatch('pos.receipt.create.after', $posReceipt);

        return $posReceipt;
    }

    /**
     * Update POS receipt
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function update(array $data, $id)
    {
        Event::dispatch('pos.receipt.update.before', $id);

        $logo = '';

        if (! empty($data['logo'])) {
            $logo = $data['logo'];
            unset($data['logo']);
        }

        foreach ($this->boolean_index as $index) {
            $data[$index] = $data[$index] ?? 0;
        }

        $posReceipt = parent::find($id);
        $posReceipt->update($data);

        $this->uploadImage($logo, $posReceipt);

        Event::dispatch('pos.receipt.update.after', $posReceipt);

        return $posReceipt;
    }

    /**
     * Upload image
     *
     * @param  array  $data
     * @param  mixed  $posReceipt
     * @return mixed
     */
    public function uploadImage($logo, $posReceipt, $type = 'logo')
    {
        if (! empty($logo)) {
            $request = request();

            foreach ($logo as $imageId => $image) {
                $file = $type.'.'.$imageId;
                $dir = 'pos_receipt/'.$posReceipt->id;

                if ($request->hasFile($file)) {
                    if ($posReceipt->{$type}) {
                        Storage::delete($posReceipt->{$type});
                    }

                    $posReceipt->{$type} = $request->file($file)->store($dir);
                    $posReceipt->save();
                }
            }
        } else {
            if ($posReceipt->{$type}) {
                Storage::delete($posReceipt->{$type});
            }

            $posReceipt->{$type} = null;
            $posReceipt->save();
        }
    }
}
