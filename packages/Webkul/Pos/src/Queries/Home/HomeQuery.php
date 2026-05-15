<?php

namespace Webkul\Pos\Queries\Home;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Webkul\Category\Repositories\CategoryRepository;
use Webkul\Customer\Repositories\CustomerGroupRepository;
use Webkul\Product\Helpers\BundleOption as BundleOptionHelper;
use Webkul\Product\Helpers\ConfigurableOption as ConfigurableOptionHelper;
use Webkul\Product\Repositories\ProductRepository;
use Illuminate\Support\Facades\Log;

class HomeQuery extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(
        protected ProductRepository $productRepository,
        protected CategoryRepository $categoryRepository,
        protected CustomerGroupRepository $customerGroupRepository,
        protected ConfigurableOptionHelper $configurableOptionHelper,
        protected BundleOptionHelper $bundleOptionHelper
    ) {
        Auth::setDefaultDriver('pos_user');
    }

    /**
     * Get the home page categories
     */
    public function getCategories($query, $input)
    {
        $qb = $this->categoryRepository->query();

        $qb->select(
            'categories.id',
            'categories.parent_id',
            'category_translations.name',
            'category_translations.slug',
            'category_translations.category_id'
        )
            ->join('category_translations', function ($join) {
                $join->on('categories.id', '=', 'category_translations.category_id')
                    ->where('category_translations.locale', app()->getLocale());
            })
            ->join('product_categories', 'categories.id', '=', 'product_categories.category_id')
            ->join('pos_outlet_product', function ($join) {
                $join->on('product_categories.product_id', '=', 'pos_outlet_product.product_id')
                    ->where('pos_outlet_product.outlet_id', Auth::user()->outlet_id);
            })
            ->where('categories.status', 1)
            ->where('categories.parent_id', core()->getCurrentChannel()->root_category_id);

        return $qb->groupBy('categories.id');
    }

    /**
     * Get the home page products
     */
    public function getProducts($query, $input)
    {
        $agent = Auth::user();

        $params = array_merge($input, [
            'outlet_id'           => $agent->outlet_id,
            'inventory_source_id' => $agent->outlet->inventory_source_id,
        ]);

        $qb = $this->productRepository->query()->distinct();

        $customerGroupId = $this->customerGroupRepository->findOneWhere([
            'code' => 'general',
        ])->id;

        $qb->select(
            'products.*',
            'product_inventories.qty as quantity',
            'pos_product_barcode.barcode',
        )
            ->leftJoin('product_flat', 'products.id', '=', 'product_flat.product_id')
            ->leftJoin('pos_product_barcode', 'products.id', '=', 'pos_product_barcode.product_id')
            ->leftJoin('pos_outlet_product', 'products.id', '=', 'pos_outlet_product.product_id')
            ->leftJoin('product_inventories', function ($join) use ($params) {
                $join->on('products.id', '=', 'product_inventories.product_id')
                    ->where('product_inventories.inventory_source_id', $params['inventory_source_id']);
            })
            ->leftJoin('product_price_indices', function ($join) use ($customerGroupId) {
                $join->on('products.id', '=', 'product_price_indices.product_id')
                    ->where('product_price_indices.customer_group_id', $customerGroupId);
            })
            ->where('products.parent_id', null)
            ->where('pos_outlet_product.outlet_id', $params['outlet_id']);
            
        return $qb->groupBy('products.id');
    }

    /**
     * Get Price html of Product
     */
    public function getConvertedPrice($product)
    {
        return core()->convertPrice($product->price);
    }

    /**
     * Get Price html of Product
     */
    public function getPriceHtml($product)
    {
        return $product->getTypeInstance()->getPriceHtml();
    }

    /**
     * Get the variants config for the product
     */
    public function getVariantsConfig($product)
    {
        $data = [];

        if ($product->type == 'configurable') {
            $data = $this->configurableOptionHelper->getConfigurationConfig($product);
        }

        return json_encode($data);
    }

    /**
     * Get the bundle options for the product
     */
    public function getBundleOptions($product)
    {
        $data = [];

        if ($product->type == 'bundle') {
            $data = $this->bundleOptionHelper->getBundleConfig($product);
        }

        return $data['options'] ?? [];
    }
}
