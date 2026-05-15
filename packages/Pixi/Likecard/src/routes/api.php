<?php

use Illuminate\Support\Facades\Route;
use Pixi\Likecard\Http\Controllers\EdfapayController;
use Pixi\Likecard\Http\Controllers\Api\CartController;
use Pixi\Likecard\Http\Controllers\Api\AgentController;
use Pixi\Likecard\Http\Controllers\Api\CategoryController;

Route::get('api/order/{orderId}', [EdfapayController::class, 'getOrderDetails'])
    ->name('pixi.api.order.details');

Route::prefix('api')
    ->middleware('api')
    ->group(function () {
        Route::get('categories/{id}/min-price', [CategoryController::class, 'getMinPrice'])->name('shop.api.categories.min_price');
        Route::prefix('v1/employee')->group(function () {
            
            Route::post('login', [AgentController::class, 'login'])->name('pixi.api.employee.login');
            Route::middleware('auth:pos_user')->group(function () {
                
                Route::get('orders', [AgentController::class, 'getOrders'])->name('pixi.api.employee.orders');
                Route::post('orders/sync', [AgentController::class, 'syncOrders'])->name('pixi.api.employee.orders.sync');
                Route::get('products/sync', [AgentController::class, 'syncProducts'])->name('pixi.api.employee.products.sync');
                Route::post('orders/create', [AgentController::class, 'createOrder'])->name('pixi.api.employee.orders.create');
                Route::post('orders/{orderId}/status', [AgentController::class, 'updateOrderStatus'])->name('pixi.api.employee.orders.status.update');
                Route::get('orders/search', [AgentController::class, 'searchOrders'])->name('pixi.api.employee.orders.search');
                Route::get('sales-summary', [AgentController::class, 'getSalesSummary'])->name('pixi.api.employee.sales.summary');
                Route::post('wallet/initiate', [AgentController::class, 'initiateWalletBalance'])->name('pixi.api.employee.wallet.initiate');
                Route::post('wallet/update-status', [AgentController::class, 'updateWalletStatus'])->name('pixi.api.employee.wallet.update_status');
                Route::get('wallet/balance', [AgentController::class, 'getWalletBalance'])->name('pixi.api.employee.wallet.balance');
                Route::get('wallet/history', [AgentController::class, 'getWalletHistory'])->name('pixi.api.employee.wallet.history');


                Route::post('cart', [CartController::class, 'create'])->name('pixi.api.employee.cart.create');
                Route::get('cart/{cartId}', [CartController::class, 'get'])->name('pixi.api.employee.cart.get');
                Route::post('cart/{cartId}/items', [CartController::class, 'addItem'])->name('pixi.api.employee.cart.items.add');
                Route::put('cart/{cartId}/items/{itemId}', [CartController::class, 'updateItem'])->name('pixi.api.employee.cart.items.update');
                Route::delete('cart/{cartId}/items/{itemId}', [CartController::class, 'deleteItem'])->name('pixi.api.employee.cart.items.delete');
                Route::get('cart/{cartId}/count', [CartController::class, 'count'])->name('pixi.api.employee.cart.count');
            });

        });
});

