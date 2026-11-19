<?php

use App\Http\Controllers\Store\CartController;
use App\Http\Controllers\Store\CatalogController;
use App\Http\Controllers\Store\CheckoutController;
use App\Http\Controllers\Store\MembershipController;
use App\Http\Controllers\Store\OrderController;
use Illuminate\Support\Facades\Route;

Route::name('store.')->group(function (): void {
    Route::get('/', [CatalogController::class, 'home'])->name('home');
    Route::get('/catalog', [CatalogController::class, 'index'])->name('catalog');
    Route::get('/products/{product:slug}', [CatalogController::class, 'show'])->name('product');
    Route::get('/faq', [CatalogController::class, 'faq'])->name('faq');
    Route::get('/contact', [CatalogController::class, 'contact'])->name('contact');

    Route::get('/cart', [CartController::class, 'show'])->block(15, 15)->name('cart');
    Route::post('/cart', [CartController::class, 'add'])->block(15, 15)->name('cart.add');
    Route::patch('/cart/{variant}', [CartController::class, 'update'])->whereNumber('variant')->block(15, 15)->name('cart.update');
    Route::delete('/cart/{variant}', [CartController::class, 'remove'])->whereNumber('variant')->block(15, 15)->name('cart.remove');
    Route::get('/checkout', [CheckoutController::class, 'show'])->block(15, 15)->name('checkout');
    Route::post('/checkout', [CheckoutController::class, 'place'])->block(15, 15)->name('checkout.place');
    Route::get('/membership', [MembershipController::class, 'show'])->name('membership');

    Route::middleware('auth')->group(function (): void {
        Route::get('/orders', [OrderController::class, 'index'])->name('orders.index');
        Route::post('/membership', [MembershipController::class, 'apply'])->name('membership.apply');
    });

    Route::get('/orders/lookup', [OrderController::class, 'lookup'])->name('orders.lookup');
    Route::post('/orders/lookup', [OrderController::class, 'sendLookup'])->middleware('throttle:6,1')->name('orders.lookup.send');

    Route::prefix('/orders/{order:public_id}')->where(['order' => '[0-7][0-9a-hjkmnp-tv-zA-HJKMNP-TV-Z]{25}'])->name('orders.')->group(function (): void {
        Route::get('/', [OrderController::class, 'show'])->name('show');
        Route::get('/access', [OrderController::class, 'access'])->middleware('signed:relative')->block(15, 15)->name('access');
        Route::get('/invoice', [OrderController::class, 'invoice'])->name('invoice');
        Route::post('/payment', [OrderController::class, 'payment'])->middleware('throttle:10,1')->name('payment');
        Route::post('/tickets', [OrderController::class, 'ticket'])->middleware('throttle:10,1')->name('tickets');
        Route::post('/resend', [OrderController::class, 'resend'])->middleware('throttle:6,1')->name('resend');
    });
});
