<?php

namespace Pixi\Likecard\Console\Commands;

use Illuminate\Support\Str;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Webkul\Product\Models\Product;
use Illuminate\Support\Facades\Log;
use Pixi\Likecard\Services\LikeCard;
use Webkul\Category\Models\Category;
use Illuminate\Support\Facades\Storage;
use Webkul\Product\Models\ProductImage;
use Webkul\Product\Jobs\UpdateCreatePriceIndex;
use Webkul\Product\Repositories\ProductRepository;
use Intervention\Image\ImageManagerStatic as Image;
use Webkul\Product\Jobs\UpdateCreateInventoryIndex;
use Webkul\CatalogRule\Jobs\UpdateCreateProductIndex;
use Webkul\Product\Jobs\ElasticSearch\UpdateCreateIndex;
use Illuminate\Support\Facades\Event;

class SyncLikeCardToBagistoProducts extends Command
{
    protected $signature = 'likecard:sync-products-to-bagisto';
    protected $description = 'Fetches products from LikeCard API and syncs them directly with Bagisto products.';

    public function __construct(protected ProductRepository $productRepository)
    {
        parent::__construct();
    }

    public function handle(LikeCard $likeCardService)
    {
        $this->info('🚀 Starting direct product synchronization from LikeCard to Bagisto...');

        try {
            // DB::beginTransaction();
            $likeCardService::init();

            // Fetch Bagisto categories that are linked to LikeCard
            $syncedCategories = Category::whereNotNull('card_lc_category_id')->get();
            if ($syncedCategories->isEmpty()) {
                $this->warn('⚠️ No LikeCard-linked categories found in Bagisto. Please sync categories first.');
                return;
            }

            $this->info('Found ' . $syncedCategories->count() . ' categories to process.');

            foreach ($syncedCategories as $category) {
                $this->processCategory($likeCardService, $category);
            }

            // DB::commit();
            $this->info('🎉 Product synchronization completed successfully!');
        } catch (\Exception $e) {
            // DB::rollBack();
            $this->error('❌ Synchronization failed: ' . $e->getMessage());
            Log::error('LikeCard to Bagisto Product Sync failed: ' . $e->getMessage() . "\n" . $e->getTraceAsString());
        }
    }

    /**
     * Process all products for a single category.
     */
    protected function processCategory(LikeCard $likeCardService, Category $category): void
    {
        $this->line("--- Processing Category: {$category->name} (LC ID: {$category->card_lc_category_id}) ---");
        try {
            $apiProducts = $likeCardService->product(['categoryId' => $category->card_lc_category_id])->fetch();
           
            if (!is_array($apiProducts)) {
                $this->warn("   - No products found or invalid API response for this category.");
                return;
            }

            $activeApiProductIds = [];
            foreach ($apiProducts as $apiProductData) {
                    $activeApiProductIds[] = $apiProductData['productId'];
                    $this->createOrUpdateProduct($apiProductData, $category);
                
            }

             $this->deactivateOrphanedProducts($category, $activeApiProductIds);
        } catch (\Exception $e) {
            $this->error("   - Failed to sync products for this category: " . $e->getMessage());
            Log::error("Failed to sync for category LC ID {$category->card_lc_category_id}: " . $e->getMessage());
        }
    }

    /**
     * Creates a new Bagisto product or updates an existing one based on LikeCard data.
     */
    // protected function createOrUpdateProduct(array $apiProductData, Category $category): void
    // {
    //     $existingProduct = Product::where('likecard_product_id', $apiProductData['productId'])->first();
    //     $isAvailable = $apiProductData['available'] ?? false;

    //     $data = [
    //         'channel'             => 'default',
    //         'locale'              => 'en',
    //         'name'                 => $apiProductData['productName'],
    //         'url_key'              => 'lc-pr-' . $apiProductData['productId'],
    //         'price'                => $apiProductData['sellPrice'],
    //         'status'               => $isAvailable ? 1 : 0, // Set status based on availability
    //         'visible_individually' => 1,
    //         'weight'               => 0,
    //         'inventories'          => [1 => $isAvailable ? 1000 : 0], // Default Inventory Source ID 1
    //         'categories'           => $this->gatherCategoryIds($category),
    //     ];

    //     if ($existingProduct) {
    //         // --- UPDATE PATH ---
    //         $this->info("   - Updating: {$apiProductData['productName']}");
    //         $bagistoProduct = $this->productRepository->update($data, $existingProduct->id);
    //     } else {
    //         // --- CREATE PATH (using the reliable two-step process) ---
    //         $this->info("   - Creating: {$apiProductData['productName']}");

    //         // Step 1: Create with bare essentials
    //         $baseData = [
    //             'type'                => 'virtual',
    //             'attribute_family_id' => 1, // Default attribute family
    //             'sku'                 => 'lc-pr-' . $apiProductData['productId'],
                
    //         ];
    //         $bagistoProduct = $this->productRepository->create($baseData);
    //         $bagistoProduct->likecard_product_id = $apiProductData['productId'];
    //         $bagistoProduct->save();
    //         // Step 2: Update with the full data to trigger indexing
    //         $bagistoProduct = $this->productRepository->update($data, $bagistoProduct->id);
    //         if ($bagistoProduct) {
    //             $this->info("   - Attempting to dispatch manual indexing job for Product ID: {$bagistoProduct->id}");
        
    //             // Temporarily add this line for testing:
    //             UpdateCreateProductIndex::dispatch($bagistoProduct);
    //         }
    //     }

    //     // Handle image attachment
    //     if ($bagistoProduct && !empty($apiProductData['productImage'])) {
    //         $this->attachImageToProduct($bagistoProduct, $apiProductData['productImage']);
    //     }

    //     // It's good practice to dispatch events after create/update
        
    //     /*UpdateCreateInventoryIndex::dispatch($bagistoProduct);
    //     UpdateCreatePriceIndex::dispatch($bagistoProduct);
    //     UpdateCreateProductIndex::dispatch($bagistoProduct);
    //     UpdateCreateIndex::dispatch($bagistoProduct); // for Elasticsearch

    //     Event::dispatch('catalog.product.create.after', $bagistoProduct);
    //     Event::dispatch('catalog.product.update.after', $bagistoProduct);*/
    // }

    protected function createOrUpdateProduct(array $apiProductData, Category $category): void
{
    $existingProduct = Product::where('likecard_product_id', $apiProductData['productId'])->first();
    $isAvailable = $apiProductData['available'] ?? false;

    $data = [
        'channel'              => 'default',
        'locale'               => 'en',
        'name'                 => $apiProductData['productName'],
        'url_key'              => 'lc-pr-' . $apiProductData['productId'],
        'price'                => $apiProductData['sellPrice'],
        'status'               => $isAvailable ? 1 : 0,
        'visible_individually' => 1,
        'weight'               => 0,
        'inventories'          => [1 => $isAvailable ? 1000 : 0],
        'categories'           => $this->gatherCategoryIds($category),
    ];

    if ($existingProduct) {
        $this->info("   - Updating: {$apiProductData['productName']}");
        $bagistoProduct = $this->productRepository->update($data, $existingProduct->id);
    } else {
        $this->info("   - Creating: {$apiProductData['productName']}");
        $baseData = [
            'type'                => 'virtual',
            'attribute_family_id' => 1,
            'sku'                 => 'lc-pr-' . $apiProductData['productId'],
        ];
        $bagistoProduct = $this->productRepository->create($baseData);
        $bagistoProduct->likecard_product_id = $apiProductData['productId'];
        $bagistoProduct->save();
        $bagistoProduct = $this->productRepository->update($data, $bagistoProduct->id);
    }

    if ($bagistoProduct && !empty($apiProductData['productImage'])) {
        $this->attachImageToProduct($bagistoProduct, $apiProductData['productImage']);
    }

    // --- THIS IS THE ONLY PART YOU NEED ---
    // Dispatch the single event and let Bagisto handle the rest.
    if ($existingProduct) {
        Event::dispatch('catalog.product.update.after', $bagistoProduct);
    } else {
        Event::dispatch('catalog.product.create.after', $bagistoProduct);
    }
}


    /**
     * Attaches an image to a product, downloading it from a URL.
     */
    protected function attachImageToProduct(Product $product, string $imageUrl): void
    {
        // Check if an image from this URL hash already exists to avoid re-downloads
        $urlHash = md5($imageUrl);
        if ($product->images()->where('url_hash', $urlHash)->exists()) {
            return; // Image already exists
        }

        try {
            $this->info("     - Downloading image...");
            $imageContent = file_get_contents($imageUrl);
            if ($imageContent === false) throw new \Exception('Failed to download image content.');

            $path = 'product/' . $product->id . '/' . Str::random(20) . '.webp';
            Storage::put($path, Image::make($imageContent)->encode('webp', 80));

            // Create the image record in the database
            $product->images()->create([
                'path'     => $path,
                'url_hash' => $urlHash, // Store hash to prevent duplicates
            ]);
        } catch (\Exception $e) {
            $this->error("     - Failed to process image: " . $e->getMessage());
            Log::error("Image sync error for product {$product->id}: " . $e->getMessage());
        }
    }

    /**
     * Deactivates products that exist in Bagisto for a category but were not in the latest API feed.
     */
/**
 * Deactivates products that exist in Bagisto for a category but were not in the latest API feed.
 */
protected function deactivateOrphanedProducts(Category $category, array $activeApiProductIds): void
{
    // Find the IDs of products that are potential orphans
    $orphanedProductIds = Product::query()
        ->whereNotNull('likecard_product_id')
        // Ensure the product belongs to the category we are currently processing
        ->whereHas('categories', function ($query) use ($category) {
            $query->where('product_categories.category_id', $category->id);
        })
        // THIS IS THE FIX: Only consider products where the related product_flat record has status = 1
        ->whereHas('product_flats', function ($query) {
            $query->where('status', 1);
        })
        // Filter out products that were present in the latest API sync
        ->whereNotIn('likecard_product_id', $activeApiProductIds)
        // Get only the IDs of the products that need to be deactivated
        ->pluck('id');

    if ($orphanedProductIds->isNotEmpty()) {
        $this->warn("   - Found {$orphanedProductIds->count()} orphaned products to deactivate in this category...");

        // Loop through the IDs and use the repository to deactivate them
        foreach ($orphanedProductIds as $productId) {
            // Using the repository ensures that all indexes (price, inventory, etc.) are updated correctly.
            $this->productRepository->update(['status' => 0], $productId);
            $this->warn("     - Deactivated product ID: {$productId}");
        }
    }
}

    /**
     * Gathers all parent category IDs up to the root.
     */
    protected function gatherCategoryIds(Category $directCategory): array
    {
        $ids = [];
        $current = $directCategory;
        while ($current) {
            $ids[] = $current->id;
            $current = $current->parent;
        }
        return array_unique($ids);
    }
}