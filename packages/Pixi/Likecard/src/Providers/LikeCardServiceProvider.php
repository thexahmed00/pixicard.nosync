<?php
namespace Pixi\Likecard\Providers;

use Illuminate\Support\Facades\Log;
// use Pixi\Likecard\Console\Commands\CreateBagistoProductsFromLc;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\ServiceProvider;
use Pixi\Likecard\Listeners\OrderEventSubscriber;
// use Pixi\Likecard\Console\Commands\ApplyVatToProducts;
// use Webkul\Notification\Listeners\CreditRequestListener;
use Pixi\Likecard\Listeners\CreateOutletForSellerListener;
use Pixi\Likecard\View\Composers\CategoryCarouselComposer;
use Pixi\Likecard\Console\Commands\SyncLikeCardToBagistoProducts;
use Pixi\Likecard\Console\Commands\SyncLikeCardToBagistoCategories;

class LikeCardServiceProvider extends ServiceProvider
{
    public function register()
    {

         $this->app->bind(
            \Webkul\Category\Contracts\Category::class,
            \Pixi\Likecard\Models\Category::class
        );

         $this->mergeConfigFrom(
            __DIR__  . '/../Config/edfapay-payment.php',
            'payment_methods'
        );
        
        $this->mergeConfigFrom(
            __DIR__ . '/../Config/edfapay-system.php',
            'core'
        );

        // $this->mergeConfigFrom(
        //     __DIR__ . '/../Config/themes.php', 'themes'
        // );
    }

    public function boot()
    {
        // Fix for not taking success.blade from theme
        $finder = app('view')->getFinder();
        $hints = $finder->getHints();
        $themePath = resource_path('themes/default/views');

        if (isset($hints['shop'])) {
            // Remove if already present to avoid duplicates.
            $hints['shop'] = array_values(array_filter($hints['shop'], fn($p) => $p !== $themePath));
            array_unshift($hints['shop'], $themePath);
            app('view')->addNamespace('shop', $hints['shop']);
        } else {
            app('view')->addNamespace('shop', [$themePath]);
        }
        $this->loadViewsFrom(__DIR__ . '/../Resources/views', 'likecard');
        
        Event::listen('marketplace.seller.update.after', CreateOutletForSellerListener::class);
        
        //----------------------END----------------------------------------

        // Publish assets from your package's Resources directory
        $this->publishes([
            __DIR__ . '/../Resources/assets' => public_path('vendor/pixi/likecard/assets')
        ], 'public');

        $this->publishes([
            __DIR__ . '/../Resources/views/sales/invoices/view.blade.php' => resource_path('views/vendor/admin/sales/invoices/view.blade.php'),
            __DIR__ . '/../Resources/views/sales/invoices/pdf.blade.php' => resource_path('views/vendor/admin/sales/invoices/pdf.blade.php'),
            __DIR__ . '/../Resources/views/checkout/success.blade.php' => resource_path('themes/default/views/checkout/success.blade.php'), 
            // __DIR__ . '/../Resources/views/checkout/processing.blade.php' => resource_path('themes/default/views/checkout/processing.blade.php'),
            __DIR__ . '/../Resources/views/customers/account/orders/view.blade.php' => resource_path('themes/default/views/customers/account/orders/view.blade.php'),

            //  __DIR__ . '/../Resources/views/components/layouts/header/desktop/bottom.blade.php' => resource_path('views/vendor/shop/components/layouts/header/desktop/bottom.blade.php'),
        ], 'likecard-views');
        

     
         $this->loadRoutesFrom(__DIR__ . '/../Http/routes.php');
        $this->loadRoutesFrom(__DIR__ . '/../routes/api.php');
     

        $migrationPath = __DIR__ . '/../Database/Migrations';
        if (!is_dir($migrationPath)) {
            logger('LikeCard Migration path NOT found: ' . $migrationPath);
        } else {
            
        }

        $this->loadMigrationsFrom($migrationPath);
        $this->commands([
            SyncLikeCardToBagistoCategories::class,
            SyncLikeCardToBagistoProducts::class,
            // ApplyVatToProducts::class,
            // CreateBagistoProductsFromLc::class
            
        ]);
        Event::subscribe(OrderEventSubscriber::class);
        // Event::listen('marketplace.seller.credit_request.after.create', CreditRequestListener::class . '@createCreditRequestNotification');
    }
}


