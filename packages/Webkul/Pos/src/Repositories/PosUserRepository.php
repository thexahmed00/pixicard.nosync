<?php

namespace Webkul\Pos\Repositories;

use Illuminate\Support\Facades\Storage;
use Webkul\Core\Eloquent\Repository;
use Webkul\Pos\Contracts\PosUser;

class PosUserRepository extends Repository
{
    /**
     * Specify Model class name
     *
     * @return string
     */
    public function model()
    {
        return PosUser::class;
    }

    /**
     * Delete user
     */
    public function delete($id)
    {
        $posUser = $this->find($id);

        if ($posUser->image) {
            Storage::delete($posUser->image);
        }

        $posUser->delete();
    }
}
