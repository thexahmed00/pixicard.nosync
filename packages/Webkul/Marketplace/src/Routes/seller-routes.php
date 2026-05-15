<?php

use Webkul\Marketplace\Http\Controllers\Seller\PosUserController;
use Webkul\Marketplace\Http\Controllers\Seller\PosOrderController;
use Webkul\Marketplace\Http\Controllers\Seller\WalletController;


Route::prefix('pos-users')
    ->name('pos_users.')
    ->middleware(['web', 'seller'])
    ->controller(PosUserController::class)
    ->group(function () {
        Route::get('', 'index')->name('index');
        Route::get('create', 'create')->name('create');
        Route::post('', 'store')->name('store');
        Route::get('{id}/edit', 'edit')->name('edit');
        Route::put('{id}', 'update')->name('update');
        Route::post('{id}', 'destroy')->name('destroy');
    });


Route::prefix('pos-orders')
    ->name('pos_orders.')
    ->middleware(['web', 'seller'])
    ->controller(PosOrderController::class)
    ->group(function () {
        Route::get('', 'index')->name('index');
    });

    Route::prefix('wallet')
    ->name('wallet.')
    ->middleware(['web', 'seller'])
    ->controller(WalletController::class)
    ->group(function () {
        Route::get('', 'index')->name('index');        // Wallet transactions list
        Route::get('create', 'create')->name('create'); // Add balance page
        Route::post('store', 'store')->name('store');   // Store balance
        Route::post('{id}', 'destroy')->name('destroy'); // delete route
    });

    Route::prefix('wallet')
    ->name('wallet.')
    ->middleware(['web', 'theme','seller','locale', 'currency'])
    ->controller(WalletController::class)
    ->group(function () {
        
        Route::get('callback', 'callback')->name('callback');
       
    });

    Route::post('wallet/webhook', [WalletController::class, 'webhook'])
    ->name('seller.wallet.webhook'); 

