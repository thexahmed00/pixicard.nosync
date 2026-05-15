<?php

namespace Webkul\Marketplace\Repositories;

use Illuminate\Container\Container as App;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Storage;
use Webkul\Attribute\Repositories\AttributeRepository;
use Webkul\Core\Eloquent\Repository;
use Webkul\Customer\Repositories\CustomerRepository;
use Webkul\Marketing\Repositories\SearchSynonymRepository;
use Webkul\Marketplace\Contracts\Product;
use Webkul\Marketplace\Helpers\Toolbar as ToolbarHelper;
use Webkul\Product\Repositories\ProductInventoryRepository;
use Webkul\Product\Repositories\ProductRepository as BaseProductRepository;

class ProductRepository extends Repository
{
    /**
     * Search engine.
     */
    protected $searchEngine = 'database';

    /**
     * Create a new repository instance.
     *
     * @return void
     */
    public function __construct(
        App $app,
        protected AttributeRepository $attributeRepository,
        protected BaseProductRepository $baseProductRepository,
        protected CustomerRepository $customerRepository,
        protected ElasticSearchRepository $elasticSearchRepository,
        protected ProductDownloadableLinkRepository $productDownloadableLinkRepository,
        protected ProductDownloadableSampleRepository $productDownloadableSampleRepository,
        protected SearchSynonymRepository $searchSynonymRepository,
        protected ProductImageRepository $productImageRepository,
        protected ProductInventoryRepository $productInventoryRepository,
        protected ProductVideoRepository $productVideoRepository,
        protected ReviewRepository $reviewRepository,
        protected SellerRepository $sellerRepository,
        protected ToolbarHelper $toolbarHelper,
    ) {
        parent::__construct($app);
    }

    /**
     * Specify Model class name
     */
    public function model(): string
    {
        return Product::class;
    }

    /**
     * @return mixed
     */
    public function create(array $data, $seller = null)
    {
        Event::dispatch('marketplace.product.create.before');

        if (empty($seller)) {
            $seller = seller()->user();
        }

        $sellerProduct = parent::create(array_merge($data, [
            'marketplace_seller_id' => $seller->id,
            'is_approved'           => core()->getConfigData('marketplace.settings.product.approval_required') ? 0 : 1,
        ]));

        foreach ($sellerProduct->product->variants as $baseVariant) {
            parent::create([
                'parent_id'             => $sellerProduct->id,
                'product_id'            => $baseVariant->id,
                'is_owner'              => 1,
                'marketplace_seller_id' => $seller->id,
                'is_approved'           => core()->getConfigData('marketplace.settings.product.approval_required') ? 0 : 1,
            ]);
        }

        $sellerProduct = $sellerProduct->refresh();

        Event::dispatch('marketplace.product.create.after', $sellerProduct);

        return $sellerProduct;
    }

    /**
     * @param  int  $id
     * @return mixed
     */
    public function update(array $data, $id)
    {
        Event::dispatch('marketplace.product.update.before', $id);

        $sellerProduct = $this->find($id);

        $sellerProduct->update($data);

        foreach ($sellerProduct->product->variants as $baseVariant) {
            if (! $this->getMarketplaceProductByProduct($baseVariant->id, $sellerProduct->seller)) {
                parent::create([
                    'parent_id'             => $sellerProduct->id,
                    'product_id'            => $baseVariant->id,
                    'is_owner'              => 1,
                    'marketplace_seller_id' => $sellerProduct->seller->id,
                    'is_approved'           => $sellerProduct->is_approved,
                ]);
            }
        }

        $sellerProduct = $sellerProduct->refresh();

        Event::dispatch('marketplace.product.update.after', $sellerProduct);

        return $sellerProduct;
    }

    /**
     * Assign product to seller
     *
     * @return \Webkul\Marketplace\Contracts\Product
     */
    public function createAssign(array $data)
    {
        Event::dispatch('marketplace.assign-product.create.before');

        $sellerProduct = parent::create(array_merge($data, [
            'is_owner'              => 0,
            'is_approved'           => core()->getConfigData('marketplace.settings.product.approval_required') ? 0 : 1,
        ]));

        $this->productInventoryRepository->saveInventories(array_merge($data, [
            'vendor_id' => $sellerProduct->marketplace_seller_id,
        ]), $sellerProduct->product);

        $this->productImageRepository->uploadImages($data, $sellerProduct);

        $this->productVideoRepository->uploadVideos($data, $sellerProduct);

        if (! empty($data['downloadable_links'])) {
            $this->productDownloadableLinkRepository->saveLinks($data, $sellerProduct);

            $this->productDownloadableSampleRepository->saveSamples($data, $sellerProduct);
        }

        if (! empty($data['selected_variants'])) {
            foreach ($data['selected_variants'] as $baseVariantId) {
                $variantData = $data['variants'][$baseVariantId];

                $childProduct = parent::create(array_merge($variantData, [
                    'parent_id'             => $sellerProduct->id,
                    'condition'             => $sellerProduct->condition,
                    'product_id'            => $baseVariantId,
                    'is_owner'              => 0,
                    'marketplace_seller_id' => $sellerProduct->marketplace_seller_id,
                    'is_approved'           => $sellerProduct->is_approved,
                ]));

                $this->productInventoryRepository->saveInventories(array_merge($variantData, [
                    'vendor_id' => $childProduct->marketplace_seller_id,
                ]), $childProduct->product);

                $this->productImageRepository->uploadImages($variantData, $childProduct);
            }
        }

        Event::dispatch('marketplace.assign-product.create.after', [$sellerProduct, $data]);

        return $sellerProduct;
    }

    /**
     * Update assigned product
     *
     * @return \Webkul\Marketplace\Contracts\Product
     */
    public function updateAssign(array $data, $id)
    {
        Event::dispatch('marketplace.assign-product.update.before', $id);

        $sellerProduct = $this->find($id);

        parent::update(array_merge($data, [
            'is_owner'    => 0,
            'is_approved' => $sellerProduct->is_approved,
        ]), $id);

        $this->productImageRepository->uploadImages($data, $sellerProduct);

        $this->productVideoRepository->uploadVideos($data, $sellerProduct);

        $this->productInventoryRepository->saveInventories(array_merge($data, [
            'vendor_id' => $sellerProduct->marketplace_seller_id,
        ]), $sellerProduct->product);

        if (! empty($data['downloadable_links'])) {
            $this->productDownloadableLinkRepository->saveLinks($data, $sellerProduct);

            $this->productDownloadableSampleRepository->saveSamples($data, $sellerProduct);
        }

        $previousBaseVariantIds = $sellerProduct->variants->pluck('product_id');

        if (isset($data['selected_variants'])) {
            foreach ($data['selected_variants'] as $baseVariantId) {
                $variantData = $data['variants'][$baseVariantId];

                if (is_numeric($index = $previousBaseVariantIds->search($baseVariantId))) {
                    $previousBaseVariantIds->forget($index);
                }

                $childProduct = $this->findOneWhere([
                    'product_id'            => $baseVariantId,
                    'marketplace_seller_id' => $sellerProduct->marketplace_seller_id,
                    'is_owner'              => 0,
                ]);

                if ($childProduct) {
                    parent::update(array_merge($variantData, [
                        'price'       => $variantData['price'],
                        'condition'   => $sellerProduct->condition,
                        'is_approved' => $childProduct->is_approved,
                    ]), $childProduct->id);
                } else {
                    $childProduct = parent::create(array_merge($variantData, [
                        'parent_id'             => $sellerProduct->id,
                        'product_id'            => $baseVariantId,
                        'condition'             => $sellerProduct->condition,
                        'is_owner'              => 0,
                        'marketplace_seller_id' => $sellerProduct->marketplace_seller_id,
                        'is_approved'           => core()->getConfigData('marketplace.settings.product.approval_required') ? 0 : 1,
                    ]));
                }

                $this->productImageRepository->uploadImages($variantData, $childProduct);

                $this->productInventoryRepository->saveInventories(array_merge($variantData, [
                    'vendor_id' => $childProduct->marketplace_seller_id,
                ]), $childProduct->product);
            }
        }

        if ($previousBaseVariantIds->count()) {
            $sellerProduct->variants()
                ->whereIn('product_id', $previousBaseVariantIds)
                ->delete();
        }

        Event::dispatch('marketplace.assign-product.update.after', [$sellerProduct, $data]);

        return $sellerProduct;
    }

    /**
     * Delete the product
     */
    public function delete($id): void
    {
        Event::dispatch('marketplace.product.delete.before', $id);

        parent::delete($id);

        Storage::deleteDirectory("assign-product/$id");

        Event::dispatch('marketplace.product.delete.after', $id);
    }

    /**
     * Returns top selling products
     *
     * @param  int  $sellerId
     * @return mixed
     */
    public function getTopSellingProducts($sellerId)
    {
        return app(OrderItemRepository::class)->getModel()
            ->leftJoin('marketplace_products', 'marketplace_order_items.marketplace_product_id', 'marketplace_products.id')
            ->leftJoin('order_items', 'marketplace_order_items.order_item_id', 'order_items.id')
            ->leftJoin('marketplace_orders', 'marketplace_order_items.marketplace_order_id', 'marketplace_orders.id')
            ->select(DB::raw('SUM(qty_ordered) as total_qty_ordered'), 'marketplace_products.product_id')
            ->where('marketplace_orders.marketplace_seller_id', $sellerId)
            ->where('marketplace_products.is_approved', 1)
            ->whereNull('order_items.parent_id')
            ->groupBy('marketplace_products.product_id')
            ->orderBy('total_qty_ordered', 'DESC')
            ->limit(5)
            ->pluck('product_id')
            ->toArray();
    }

    /**
     * Returns the total products of the seller
     *
     * @param  Seller  $seller
     * @return int
     */
    public function getTotalProducts($seller, $assignProducts = false)
    {
        return $this->getModel()
            ->where([
                ['marketplace_seller_id', $seller->id],
                ['is_approved', 1],
            ])
            ->when(! $assignProducts, function ($query) {
                $query->where('is_owner', 1);
            })
            ->whereHas('product_flats', function ($query) {
                $query->where('status', 1)
                    ->where('visible_individually', 1);
            })
            ->count();
    }

    /**
     * Set search engine.
     */
    public function setSearchEngine(string $searchEngine): self
    {
        $this->searchEngine = $searchEngine;

        return $this;
    }

    /**
     * Get all products.
     *
     * @return \Illuminate\Support\Collection
     */
    public function getAll(array $params = [])
    {
        if ($this->searchEngine == 'elastic') {
            return $this->searchFromElastic($params);
        }

        return $this->searchFromDatabase($params);
    }

    /**
     * Search product from database.
     *
     * @return \Illuminate\Support\Collection
     */
    public function searchFromDatabase(array $params = [])
    {
        $params['url_key'] ??= null;

        if (! empty($params['query'])) {
            $params['name'] = $params['query'];
        }

        $query = $this->baseProductRepository->with([
            'attribute_family',
            'images',
            'videos',
            'attribute_values',
            'price_indices',
            'inventory_indices',
            'reviews',
            'variants',
            'variants.attribute_family',
            'variants.attribute_values',
            'variants.price_indices',
            'variants.inventory_indices',
        ])->scopeQuery(function ($query) use ($params) {
            // We removed distinct() because groupBy('products.id') makes it redundant and cleaner.
            $qb = $query->select('products.*')
                ->leftJoin('products as variants', function ($join) {
                    $join->on('variants.parent_id', '=', 'products.id')
                        ->orOn('variants.id', 'products.id');
                })
                ->leftJoin('product_price_indices', function ($join) {
                    $customerGroup = $this->customerRepository->getCurrentGroup();

                    $join->on('products.id', '=', 'product_price_indices.product_id')
                        ->where('product_price_indices.customer_group_id', $customerGroup->id);
                })
                ->whereNull('products.parent_id');

            /**
             * IMPORTANT: We add the raw select here to satisfy SQL group by rules,
             * but we will re-add the attribute after pagination to ensure Eloquent keeps it.
             */
            if (! empty($params['seller_id'])) {
                $sellerId = (int) $params['seller_id'];
                $qb->addSelect(DB::raw("MAX($sellerId) as seller_id"));
            }

            // ... rest of your query building logic ...

            if (! empty($params['channel_id'])) {
                $qb->leftJoin('product_channels', 'products.id', '=', 'product_channels.product_id')
                    ->where('product_channels.channel_id', explode(',', $params['channel_id']));
            }

            if (! empty($params['type'])) {
                $qb->where('products.type', $params['type']);
            }

            if (! empty($params['id'])) {
                $qb->where('products.id', $params['id']);
            }

            if (! empty($params['price'])) {
                $priceRange = explode(',', $params['price']);

                $qb->whereBetween('product_price_indices.min_price', [
                    core()->convertToBasePrice(current($priceRange)),
                    core()->convertToBasePrice(end($priceRange)),
                ]);
            }

            $filterableAttributes = $this->attributeRepository->getProductDefaultAttributes(array_keys($params));

            $attributes = $filterableAttributes->whereIn('code', [
                'name',
                'status',
                'visible_individually',
                'url_key',
            ]);

            foreach ($attributes as $attribute) {
                $alias = $attribute->code . '_product_attribute_values';

                $qb->leftJoin('product_attribute_values as ' . $alias, 'products.id', '=', $alias . '.product_id')
                    ->where($alias . '.attribute_id', $attribute->id);

                if ($attribute->code == 'name') {
                    $synonyms = $this->searchSynonymRepository->getSynonymsByQuery(urldecode($params['name']));

                    $qb->where(function ($subQuery) use ($alias, $synonyms) {
                        foreach ($synonyms as $synonym) {
                            $subQuery->orWhere($alias . '.text_value', 'like', '%' . $synonym . '%');
                        }
                    });
                } elseif ($attribute->code == 'url_key') {
                    if (empty($params['url_key'])) {
                        $qb->whereNotNull($alias . '.text_value');
                    } else {
                        $qb->where($alias . '.text_value', 'like', '%' . urldecode($params['url_key']) . '%');
                    }
                } else {
                    if (is_null($params[$attribute->code])) {
                        continue;
                    }

                    $qb->where($alias . '.' . $attribute->column_name, 1);
                }
            }

            $attributes = $filterableAttributes->whereNotIn('code', [
                'price',
                'name',
                'status',
                'visible_individually',
                'url_key',
            ]);

            if ($attributes->isNotEmpty()) {
                $qb->where(function ($filterQuery) use ($qb, $params, $attributes) {
                    $aliases = [
                        'products' => 'product_attribute_values',
                        'variants' => 'variant_attribute_values',
                    ];

                    foreach ($aliases as $table => $tableAlias) {
                        $filterQuery->orWhere(function ($subFilterQuery) use ($qb, $params, $attributes, $table, $tableAlias) {
                            foreach ($attributes as $attribute) {
                                $alias = $attribute->code . '_' . $tableAlias;

                                $qb->leftJoin('product_attribute_values as ' . $alias, function ($join) use ($table, $alias, $attribute) {
                                    $join->on($table . '.id', '=', $alias . '.product_id');

                                    $join->where($alias . '.attribute_id', $attribute->id);
                                });

                                $subFilterQuery->whereIn($alias . '.' . $attribute->column_name, explode(',', $params[$attribute->code]));
                            }
                        });
                    }
                });
                // Note: The groupBy is repeated at the end, which is fine.
                $qb->groupBy('products.id');
            }

            $sortOptions = $this->getSortOptions($params);

            if ($sortOptions['order'] != 'rand') {
                $attribute = $this->attributeRepository->findOneByField('code', $sortOptions['sort']);

                if ($attribute) {
                    if ($attribute->code === 'price') {
                        $qb->orderBy('product_price_indices.min_price', $sortOptions['order']);
                    } else {
                        // ... your sorting logic ...
                        $alias = 'sort_product_attribute_values';
                        $qb->leftJoin('product_attribute_values as ' . $alias, function ($join) use ($alias, $attribute) {
                            $join->on('products.id', '=', $alias . '.product_id')
                                ->where($alias . '.attribute_id', $attribute->id);
                            // ...
                        })->orderBy($alias . '.' . $attribute->column_name, $sortOptions['order']);
                    }
                } else {
                    $qb->orderBy('products.created_at', $sortOptions['order']);
                }
            } else {
                return $qb->inRandomOrder();
            }

            return $qb->groupBy('products.id');
        });

        $limit = $this->getPerPageLimit($params);

        // Step 1: Execute the query and get the paginator instance
        $paginator = $query->paginate($limit);

        // Step 2: If seller_id was used, manually add it to each model in the result set.
        if (! empty($params['seller_id'])) {
            $paginator->getCollection()->transform(function ($product) use ($params) {
                $product->seller_id = (int) $params['seller_id'];
                return $product;
            });
        }

        // Step 3: Return the modified paginator
        return $paginator;
    }

    /**
     * Search product from elastic search.
     *
     * @return \Illuminate\Support\Collection
     */
    public function searchFromElastic(array $params = [])
    {
        $currentPage = Paginator::resolveCurrentPage('page');

        $limit = $this->getPerPageLimit($params);

        $sortOptions = $this->getSortOptions($params);

        $indices = $this->elasticSearchRepository->search($params, [
            'from'  => ($currentPage * $limit) - $limit,
            'limit' => $limit,
            'sort'  => $sortOptions['sort'],
            'order' => $sortOptions['order'],
        ]);

        $query = $this->baseProductRepository->with([
            'attribute_family',
            'images',
            'videos',
            'attribute_values',
            'price_indices',
            'inventory_indices',
            'reviews',
            'variants',
            'variants.attribute_family',
            'variants.attribute_values',
            'variants.price_indices',
            'variants.inventory_indices',
        ])->scopeQuery(function ($query) use ($indices) {
            $qb = $query->distinct()
                ->whereIn('products.id', $indices['ids']);

            // Sort collection
            $qb->orderBy(DB::raw('FIELD(id, ' . implode(',', $indices['ids']) . ')'));

            return $qb;
        });

        $items = $indices['total'] ? $query->get() : [];

        $results = new LengthAwarePaginator($items, $indices['total'], $limit, $currentPage, [
            'path'  => request()->url(),
            'query' => $params,
        ]);

        return $results;
    }

    /**
     * Fetch per page limit from toolbar helper. Adapter for this repository.
     */
    public function getPerPageLimit(array $params): int
    {
        return $this->toolbarHelper->getLimit($params);
    }

    /**
     * Fetch sort option from toolbar helper. Adapter for this repository.
     */
    public function getSortOptions(array $params): array
    {
        return $this->toolbarHelper->getOrder($params);
    }

    /**
     * Search Product by Attribute
     */
    public function searchProductsById(int $id)
    {
        return $this->baseProductRepository->scopeQuery(function ($query) use ($id) {
            return $query->distinct()
                ->addSelect('products.*')
                ->leftJoin('product_flat', 'product_flat.product_id', '=', 'products.id')
                // ->leftJoin('marketplace_products', 'products.id', '=', 'marketplace_products.product_id')
                // ->whereIn('products.type', ['simple', 'configurable', 'virtual', 'downloadable'])
                ->where('product_flat.locale', request()->get('locale') ?: app()->getLocale())
                ->whereNotNull('product_flat.url_key')
                ->where('products.id', $id)
                ->groupBy('products.id');
        })->first();
    }

    /**
     * Search Product by Attribute
     */
    public function searchProducts(string $term, $seller): Collection
    {
        return $this->baseProductRepository->scopeQuery(function ($query) use ($term, $seller) {
            return $query->distinct()
                ->addSelect('products.*')
                ->leftJoin('product_flat', 'product_flat.product_id', '=', 'products.id')
                // ->leftJoin('marketplace_products', 'products.id', '=', 'marketplace_products.product_id')
                ->whereIn('products.type', ['simple', 'configurable', 'virtual', 'downloadable'])
                ->where('product_flat.locale', request()->get('locale') ?: app()->getLocale())
                ->whereNotNull('product_flat.url_key')
                ->where('product_flat.name', 'like', '%' . $term . '%')
                // ->where(function ($query) use ($seller) {
                //     $query->whereNull('marketplace_products.marketplace_seller_id')
                //         ->orWhere(function ($subQuery) use ($seller) {
                //             $subQuery->whereNotNull('marketplace_products.marketplace_seller_id')
                //                 ->where('marketplace_products.is_owner', 0)
                //                 ->where('marketplace_products.marketplace_seller_id', '!=', $seller->id);
                //         });
                // })
                ->groupBy('products.id');
        })->limit(10);
    }

    /**
     * Returns seller by product
     *
     * @param  int  $productId
     * @return mixed
     */
    public function getSellerByProductId($productId)
    {
        $product = parent::findOneWhere([
            'product_id' => $productId,
            'is_owner'   => 1,
        ]);

        if (! $product) {
            return;
        }

        return $product->seller;
    }

    /**
     * Returns count of seller that selling the same product
     *
     * @param  \Webkul\Product\Contracts\Product  $product
     * @return int
     */
    public function getSellerCount($product)
    {
        return $this->scopeQuery(function ($query) use ($product) {
            return $query
                ->where('marketplace_products.product_id', $product->id)
                ->join('marketplace_sellers', 'marketplace_sellers.id', 'marketplace_products.marketplace_seller_id')
                ->where('marketplace_sellers.is_suspended', 0)
                ->where('marketplace_sellers.is_approved', 1)
                ->where('marketplace_products.is_owner', 0)
                ->where('marketplace_products.is_approved', 1);
        })->count();
    }

    /**
     * Returns the seller products of the product
     *
     * @param  \Webkul\Product\Contracts\Product  $product
     * @return Illuminate\Database\Eloquent\Collection
     */
    public function getSellerProducts($product)
    {
        $products = $this->with([
            'seller',
            'images',
            'videos',
            'downloadable_links',
            'downloadable_samples',
        ])
            ->scopeQuery(function ($query) use ($product) {
                return $query
                    ->join('marketplace_sellers', 'marketplace_sellers.id', 'marketplace_products.marketplace_seller_id')
                    ->leftJoin('marketplace_seller_reviews', 'marketplace_seller_reviews.marketplace_seller_id', 'marketplace_sellers.id')
                    ->where('marketplace_sellers.is_suspended', 0)
                    ->where('marketplace_sellers.is_approved', 1)
                    ->where('marketplace_products.product_id', $product->id)
                    ->where('marketplace_products.is_owner', 0)
                    ->where('marketplace_products.is_approved', 1)
                    ->groupBy('marketplace_products.id');
            })
            ->select(
                'marketplace_products.*',
                DB::raw('COALESCE(AVG(' . DB::getTablePrefix() . 'marketplace_seller_reviews.rating), 0) as avg_rating')
            )
            ->get();

        $products = $products->map(function ($sellerProduct) {
            $sellerProduct->formatted_price = core()->currency($sellerProduct->price);
            $sellerProduct->is_saleable = $sellerProduct->isSaleable();

            if ($sellerProduct->downloadable_links) {
                $sellerProduct->downloadable_links = $sellerProduct->downloadable_links->map(function ($link) {
                    $link->formatted_price = core()->currency($link->price);

                    return $link;
                });
            }

            return $sellerProduct;
        });

        return $products;
    }

    /**
     * Returns the seller products of the product id
     *
     * @param  \Webkul\Marketplace\Contracts\Product  $product
     */
    public function isAvailableForSale($product)
    {
        if (
            ! $product
            || ! $product->is_approved
            || ! $product->seller->is_approved
            || $product->seller->is_suspended == 1
            || ! $product->product?->status
            || ! core()->getConfigData('marketplace.settings.general.status')
        ) {
            return false;
        }

        return true;
    }

    /**
     * Returns the seller products of the product id
     */
    public function getMarketplaceProductByProduct(int $productId, $seller)
    {
        return $this->findOneWhere([
            'product_id'            => $productId,
            'marketplace_seller_id' => $seller->id,
        ]);
    }
}
