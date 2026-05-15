<?php

namespace Webkul\Pos\Repositories;

use Webkul\Core\Eloquent\Repository;
use Webkul\Pos\Contracts\PosDrawer;

class PosDrawerRepository extends Repository
{
    /**
     * Specify Model class name
     *
     * @return string
     */
    public function model()
    {
        return PosDrawer::class;
    }
}
