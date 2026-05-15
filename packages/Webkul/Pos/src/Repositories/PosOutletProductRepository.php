<?php

namespace Webkul\Pos\Repositories;

use Illuminate\Container\Container;
use Webkul\Core\Eloquent\Repository;
use Webkul\Pos\Contracts\PosOutletProduct;
use Webkul\Product\Repositories\ProductInventoryRepository;
use Webkul\Product\Repositories\ProductRepository;

class PosOutletProductRepository extends Repository
{
    /**
     * Create a new repository instance.
     *
     * @return void
     */
    public function __construct(
        Container $container,
        protected ProductRepository $productRepository,
        protected ProductInventoryRepository $productInventoryRepository,
    ) {
        parent::__construct($container);
    }

    /**
     * Specify Model class name
     *
     * @return string
     */
    public function model()
    {
        return PosOutletProduct::class;
    }

    /**
     * Get Product qty
     */
    public function getProductTotalQty(int $productId, int $inventorySourceId)
    {
        $product = $this->productRepository->find($productId);

        $productIds = $product->getTypeInstance()->isComposite()
            ? $product->getTypeInstance()->getChildrenIds()
            : [$product->id];

        return $this->productInventoryRepository->findWhereIn('product_id', $productIds)
            ->where('inventory_source_id', $inventorySourceId)
            ->sum('qty');
    }

    /**
     * Get Low Stock Products
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getLowStockProducts(array $params = [])
    {
        $query = $this->productRepository
            ->with([
                'images',
                'variants',
            ])->scopeQuery(function ($query) use ($params) {
                return $query->distinct()
                    ->select('products.*', 'product_inventories.qty as quantity')
                    ->leftJoin('product_inventories', 'products.id', '=', 'product_inventories.product_id')
                    ->leftJoin('pos_outlet_product', 'products.id', '=', 'pos_outlet_product.product_id')
                    ->where('product_inventories.qty', '<=', $params['low_stock_qty'])
                    ->where('product_inventories.inventory_source_id', $params['inventory_source_id'])
                    ->where('pos_outlet_product.outlet_id', $params['outlet_id'])
                    ->where('products.type', 'simple')
                    ->groupBy('products.id');
            });

        return $query->paginate($params['limit'] ?? 10);
    }
}
