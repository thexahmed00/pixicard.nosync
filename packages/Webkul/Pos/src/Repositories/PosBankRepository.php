<?php

namespace Webkul\Pos\Repositories;

use Webkul\Core\Eloquent\Repository;
use Webkul\Pos\Contracts\PosBank;

class PosBankRepository extends Repository
{
    /**
     * Specify Model class name
     *
     * @return string
     */
    public function model()
    {
        return PosBank::class;
    }
}
