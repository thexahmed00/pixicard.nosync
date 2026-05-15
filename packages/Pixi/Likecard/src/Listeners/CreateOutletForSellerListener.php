<?php

namespace Pixi\Likecard\Listeners;

use Illuminate\Support\Facades\Log;
use Webkul\Marketplace\Models\Seller;
use Webkul\Product\Repositories\ProductRepository;
use Webkul\Pos\Repositories\PosOutletRepository;
use Webkul\Pos\Repositories\PosOutletProductRepository;
use Webkul\Inventory\Repositories\InventorySourceRepository;

class CreateOutletForSellerListener
{
    /**
     * Create a new listener instance.
     *
     * @return void
     */
    public function __construct(
        protected PosOutletRepository $posOutletRepository,
        protected InventorySourceRepository $inventorySourceRepository,
        protected ProductRepository $productRepository,
        protected PosOutletProductRepository $posOutletProductRepository
    ) {
    }

    /**
     * Handle the event.
     *
     * @param  \Webkul\Marketplace\Models\Seller  $seller
     * @return void
     */
    public function handle(Seller $seller)
    {
        // 1. Check if the seller was just approved.
        if (!$seller->is_approved || !$seller->getOriginal('is_approved')) {
            return; // Exit if this wasn't an approval action.
        }
        
        // 2. Check if an outlet with the same name already exists to prevent duplicates.
        if ($this->outletExistsForSeller($seller)) {
            Log::info('POS Outlet with name "' . $seller->shop_title . '" already exists. Skipping creation.');
            return;
        }

        try {
            // 3. Find the default inventory source to link to the outlet.
            $inventorySource = $this->inventorySourceRepository->findOneByField('code', 'default');

            if (!$inventorySource) {
                Log::error('POS Outlet Creation Failed: Default inventory source not found for seller ' . $seller->id);
                return;
            }

            // 4. Prepare data for the new outlet using the seller's direct information.
            $outletData = [
                'name'                => $seller->shop_title,
                'email'               => $seller->email,
                'phone'               => $seller->phone,
                'address'             => $seller->address ?? '',
                'country'             => $seller->country ?? '',
                'state'               => $seller->state ?? '',
                'city'                => $seller->city ?? '',
                'postcode'            => $seller->postcode ?? '',
                'status'              => 1, // Set as active
                'inventory_source_id' => $inventorySource->id,
            ];

            // 5. Create the new POS Outlet.
            $newOutlet = $this->posOutletRepository->create($outletData);
            Log::info('Successfully created POS outlet for approved seller: ' . $seller->shop_title);

            // 6. NEW: Assign all existing products to the new outlet.
            $allProductIds = $this->productRepository->all(['id'])->pluck('id');

            foreach ($allProductIds as $productId) {
                $this->posOutletProductRepository->updateOrCreate([
                    'product_id' => $productId,
                    'outlet_id'  => $newOutlet->id,
                ], [
                    'status' => 1, // Set the assignment status to active
                ]);
            }
            
            Log::info('Automatically assigned ' . count($allProductIds) . ' products to new outlet: ' . $newOutlet->name);

        } catch (\Exception $e) {
            Log::error('Error in CreateOutletForSellerListener for seller ' . $seller->id . ': ' . $e->getMessage());
        }
    }

    /**
     * Check if an outlet already exists for this seller to avoid duplicates.
     *
     * @param  \Webkul\Marketplace\Models\Seller $seller
     * @return bool
     */
    protected function outletExistsForSeller(Seller $seller)
    {
        return $this->posOutletRepository->count(['name' => $seller->shop_title]) > 0;
    }
}

