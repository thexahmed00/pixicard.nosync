<?php

namespace Pixi\Likecard\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Pixi\Likecard\Services\LikeCard;
use Webkul\Category\Models\Category;
use Webkul\Category\Repositories\CategoryRepository;
use Webkul\Attribute\Models\Attribute;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\ImageManagerStatic as Image;
use Illuminate\Support\Str;

class SyncLikeCardToBagistoCategories extends Command
{
    protected $signature = 'likecard:sync-to-bagisto';
    protected $description = 'Fetches categories from LikeCard API and syncs them (create/update) directly with Bagisto categories.';

    protected CategoryRepository $categoryRepository;
    private array $processedLikeCardIds = []; // To track which categories are still active

    public function __construct(CategoryRepository $categoryRepository)
    {
        parent::__construct();
        $this->categoryRepository = $categoryRepository;
    }

    public function handle(LikeCard $likeCardService)
    {
        $this->info('🚀 Starting direct synchronization from LikeCard to Bagisto...');

        try {
            // DB::beginTransaction();

            // 1. Fetch data from LikeCard API
            $likeCardService::init();
            $apiCategories = $likeCardService->category()->fetch();

            if (!is_array($apiCategories)) {
                throw new \Exception('Failed to fetch categories from API or invalid response format.');
            }
            $this->info('✅ Successfully fetched data from LikeCard API.');

            // 2. Get or create the main "Cards" parent category
            $cardsParentCategory = $this->getOrCreateParentCategory();
            if (!$cardsParentCategory) {
                return; // Stop if parent category can't be established
            }

            // 3. Start the recursive sync process
            $this->info('🔄 Processing and syncing categories...');
            $this->syncCategoryLevel($apiCategories, $cardsParentCategory->id);
            $this->info('👍 Category tree processing complete.');

            // 4. Deactivate orphaned categories
            $this->deactivateOrphanedCategories();

            // DB::commit();
            $this->info('🎉 Synchronization completed successfully!');
        } catch (\Exception $e) {
            // DB::rollBack();
            $this->error('❌ Synchronization failed: ' . $e->getMessage());
            Log::error('LikeCard to Bagisto Sync failed: ' . $e->getMessage() . "\n" . $e->getTraceAsString());
        }
    }

    /**
     * Recursively syncs a level of categories.
     *
     * @param array    $apiCategoryLevel
     * @param int|null $bagistoParentId
     */
    protected function syncCategoryLevel(array $apiCategoryLevel, ?int $bagistoParentId): void
    {
        $priceAttribute = Attribute::where('code', 'price')->first();
        $localeCode     = core()->getCurrentLocale()->code;

        foreach ($apiCategoryLevel as $apiCategory) {
            $this->processedLikeCardIds[] = $apiCategory['id']; // Mark this ID as active

            $existingCategory = Category::where('card_lc_category_id', $apiCategory['id'])->first();

            if ($existingCategory) {
                // --- UPDATE PATH (Direct Model Manipulation) ---
                // This bypasses the unreliable repository update method.
                $this->info("   Updating: {$apiCategory['categoryName']} (ID: {$apiCategory['id']})");

                // Update non-translatable attributes directly on the main model.
                $existingCategory->parent_id    = $bagistoParentId;
                $existingCategory->status       = 1;
                $existingCategory->display_mode = 'products_and_description';

                // Update translatable fields for the current locale.
                // Bagisto's translatable models automatically handle saving to the translations table.
                $existingCategory->translateOrNew($localeCode)->name        = $apiCategory['categoryName'];
                $existingCategory->translateOrNew($localeCode)->slug        = 'lc-cat-' . $apiCategory['id'];
                $existingCategory->translateOrNew($localeCode)->description = $apiCategory['metaData']['description'] ?? '';

                // Save the changes to both the categories and category_translations tables.
                $existingCategory->save();

                $bagistoCategory = $existingCategory;

            } else {
                // --- CREATE PATH (This logic is correct and remains the same) ---
                $this->info("   Creating: {$apiCategory['categoryName']} (ID: {$apiCategory['id']})");
                
                $createData = [
                    'parent_id'           => $bagistoParentId,
                    'status'              => 1,
                    'display_mode'        => 'products_and_description',
                    'card_lc_category_id' => $apiCategory['id'],
                    'translations' => [
                        $localeCode => [
                            'name'        => $apiCategory['categoryName'],
                            'slug'        => 'lc-cat-' . $apiCategory['id'],
                            'description' => $apiCategory['metaData']['description'] ?? '',
                        ],
                    ],
                ];

                $bagistoCategory = $this->categoryRepository->create($createData);
                // Re-assign the custom attribute just in case create() doesn't handle it
                $bagistoCategory->card_lc_category_id = $apiCategory['id'];
                $bagistoCategory->save();
            }

            if ($bagistoCategory && !empty($apiCategory['amazonImage'])) {
                $this->attachImageToCategory($bagistoCategory, $apiCategory['amazonImage']);
            }

            // Attach filterable attribute if needed
            if ($priceAttribute && isset($bagistoCategory)) {
                $this->attachFilterableAttribute($bagistoCategory, $priceAttribute);
            }

            // --- RECURSIVE CALL FOR CHILDREN ---
            if (!empty($apiCategory['childs']) && is_array($apiCategory['childs'])) {
                $this->syncCategoryLevel($apiCategory['childs'], $bagistoCategory->id);
            }
        }
    }

    protected function attachImageToCategory(Category $category, string $imageUrl): void
    {
        try {
            $this->info("      - Downloading image for category '{$category->name}'...");

            $imageContent = file_get_contents($imageUrl);
            if ($imageContent === false) {
                throw new \Exception('Failed to download image content.');
            }

            // Define the path. Bagisto stores category images in 'category/{id}/'.
            $dir = 'category/' . $category->id;
            $path = $dir . '/' . Str::random(20) . '.webp';

            // Use Intervention Image to create and encode the image to webp format
            Storage::put($path, Image::make($imageContent)->encode('webp', 80));

            // Delete the old image if it exists to avoid storing unused files
            if ($category->logo_path && $category->logo_path !== $path && Storage::exists($category->logo_path)) {
                Storage::delete($category->logo_path);
            }

            // Update the category's image attribute and save it to the database
            $category->logo_path = $path;
            $category->save();

            $this->info("      - Image attached successfully.");

        } catch (\Exception $e) {
            $this->error("      - Failed to process image for category {$category->id}: " . $e->getMessage());
            Log::error("Image sync error for category {$category->id}: " . $e->getMessage());
        }
    }

    /**
     * Finds categories in DB that were not in the API feed and deactivates them.
     */
    protected function deactivateOrphanedCategories(): void
    {
        $this->info('🔍 Searching for orphaned categories to deactivate...');

        $orphanedCount = Category::whereNotNull('card_lc_category_id')
            ->whereNotIn('card_lc_category_id', $this->processedLikeCardIds)
            ->where('status', 1) // Only deactivate active ones
            ->update(['status' => 0]);

        if ($orphanedCount > 0) {
            $this->warn("   - Deactivated {$orphanedCount} orphaned categories.");
        } else {
            $this->info('   - No orphaned categories found.');
        }
    }

    /**
     * Gets or creates the main "Cards" category to house all synced items.
     */
    protected function getOrCreateParentCategory(): ?Category
    {
        $cardsCategory = Category::whereHas('translations', function ($query) {
            $query->where('slug', 'cards');
        })->first();

        if ($cardsCategory) {
            $this->info('ℹ️ Parent category "Cards" found.');
            return $cardsCategory;
        }

        $this->info('⚠️ Parent category "Cards" not found. Creating it now...');
        try {
             $root = $this->categoryRepository->where('parent_id', null)->first();
            if (!$root) throw new \Exception('Root category not found.');

            $cardsCategory = $this->categoryRepository->create([
                'name'         => 'Cards',
                'slug'         => 'cards',
                'description'  => 'Parent category for all LikeCard items',
                'status'       => 1,
                'display_mode' => 'products_and_description',
                'parent_id'    => $root->id,
            ]);
            $this->info('✅ "Cards" parent category created successfully.');
            return $cardsCategory;
        } catch (\Exception $e) {
            $this->error('❌ Failed to create "Cards" parent category: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Attaches the price attribute as filterable if it's not already.
     */
    protected function attachFilterableAttribute(Category $category, Attribute $attribute): void
    {
        $exists = DB::table('category_filterable_attributes')
                    ->where('category_id', $category->id)
                    ->where('attribute_id', $attribute->id)
                    ->exists();

        if (!$exists) {
            DB::table('category_filterable_attributes')->insert([
                'category_id'  => $category->id,
                'attribute_id' => $attribute->id,
            ]);
        }
    }
}