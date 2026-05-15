<?php

namespace Webkul\Pos\Providers;

use Konekt\Concord\BaseModuleServiceProvider;

class ModuleServiceProvider extends BaseModuleServiceProvider
{
    protected $models = [
        \Webkul\Pos\Models\PosUser::class,
        \Webkul\Pos\Models\PosOutlet::class,
        \Webkul\Pos\Models\PosOutletProduct::class,
        \Webkul\Pos\Models\PosOrder::class,
        \Webkul\Pos\Models\PosDrawer::class,
        \Webkul\Pos\Models\PosCustomerCredit::class,
        \Webkul\Pos\Models\PosProductBarcode::class,
        \Webkul\Pos\Models\PosProductRequest::class,
        \Webkul\Pos\Models\PosProductCategories::class,
        \Webkul\Pos\Models\PosBank::class,
        \Webkul\Pos\Models\PosReceipt::class,
    ];
}
