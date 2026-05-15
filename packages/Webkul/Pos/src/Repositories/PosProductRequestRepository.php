<?php

namespace Webkul\Pos\Repositories;

use Webkul\Core\Eloquent\Repository;
use Webkul\Pos\Contracts\PosProductRequest;

class PosProductRequestRepository extends Repository
{
    /**
     * Specify Model class name
     *
     * @return string
     */
    public function model()
    {
        return PosProductRequest::class;
    }

    /**
     * Get All requested Product
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getAll(array $params = [])
    {
        $results = $this->scopeQuery(function ($query) use ($params) {
            return $query->distinct()
                ->leftJoin('product_flat', 'product_flat.product_id', '=', 'pos_product_request.product_id')
                ->select('pos_product_request.*')
                ->addSelect('product_flat.name')
                ->where('product_flat.locale', core()->getRequestedLocaleCode())
                ->where('pos_product_request.user_id', $params['user_id'])
                ->when(! empty($params['query']), function ($qb) use ($params) {
                    $qb->where('product_flat.name', 'like', '%'.$params['query'].'%');
                })
                ->orderBy('pos_product_request.created_at', 'desc')
                ->groupBy('product_flat.product_id');
        })->get();

        return $results;
    }
}
