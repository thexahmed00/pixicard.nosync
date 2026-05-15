<?php

namespace Webkul\Pos\Type;

use Webkul\Product\Type\Simple as BaseSimple;

class Simple extends BaseSimple
{
    /**
     * Returns product inventory index of current channel or outlet.
     *
     * @return mixed
     */
    public function getInventoryIndex()
    {
        if (
            request()->segment(1) == 'graphql'
            && auth()->guard('pos_user')->check()
        ) {
            return $this->product
                ->inventories
                ->where('inventory_source_id', auth()->guard('pos_user')->user()->outlet->inventory_source_id)
                ->first();
        }

        return $this->product
            ->inventory_indices
            ->where('channel_id', core()->getCurrentChannel()->id)
            ->first();
    }
}
