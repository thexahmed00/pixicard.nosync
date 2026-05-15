<?php

namespace Webkul\Pos\Helpers;

use Webkul\Product\Repositories\ProductBundleOptionProductRepository;
use Webkul\Product\Repositories\ProductBundleOptionRepository;
use Webkul\Product\Repositories\ProductRepository;

class Pos
{
    /**
     * Create a new instance.
     *
     * @return void
     */
    public function __construct(
        protected ProductRepository $productRepository,
        protected ProductBundleOptionRepository $productBundleOptionRepository,
        protected ProductBundleOptionProductRepository $productBundleOptionProductRepository,
    ) {}

    /**
     * Get Download Product's Links Price
     *
     * @param  object  $product
     * @param  array  $orderItem
     * @return float
     */
    public function getDownloadLinkPrice($product, $orderItem)
    {
        $linkPrice = 0;

        foreach ($product->downloadable_links as $link) {
            if (! in_array($link->id, $orderItem['additional']['links'])) {
                continue;
            }

            $linkPrice += $link->price;
        }

        return $linkPrice;
    }

    /**
     * Get Bundle Product's Price
     *
     * @param  array  $additional
     * @return float
     */
    public function getBundleProductPrice($additional)
    {
        $basePrice = 0;

        foreach ($additional['bundle_options'] as $optionId => $optionProductIds) {
            foreach ($optionProductIds as $optionProductId) {
                $optionProduct = $this->productBundleOptionProductRepository->find($optionProductId);

                if ($optionProduct) {
                    $product = $this->productRepository->find($optionProduct->product_id);

                    $bundleOption = $this->productBundleOptionRepository->find($optionProduct->product_bundle_option_id);

                    if ($bundleOption) {
                        $qty = $additional['bundle_option_qty'][$optionId];

                        if (
                            $bundleOption->type == 'checkbox'
                            || $bundleOption->type == 'multiselect'
                        ) {
                            $qty = $optionProduct->qty;
                        }

                        $basePrice += $product->getTypeInstance()->getMinimalPrice() * $qty;
                    }
                }
            }
        }

        return $basePrice;
    }
}
