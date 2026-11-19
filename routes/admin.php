<?php

use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\MembershipController;
use App\Http\Controllers\Admin\OrderController;
use App\Http\Controllers\Admin\PaymentController;
use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\Admin\SettingsController;
use App\Http\Controllers\Admin\ShippingRateController;
use App\Http\Controllers\Admin\TicketController;
use App\Http\Controllers\Admin\VariantController;
use Illuminate\Support\Facades\Route;

Route::prefix('admin')->name('admin.')->middleware(['auth', 'verified', 'admin.mfa'])->group(function (): void {
    Route::get('/', DashboardController::class)->name('dashboard');

    Route::resource('categories', CategoryController::class)->except('show');
    Route::resource('products', ProductController::class)->except('show');
    Route::delete('products/{product}/images/{image}', [ProductController::class, 'removeImage'])->whereNumber('image')->name('products.images.destroy');

    Route::post('products/{product}/variants', [VariantController::class, 'store'])->name('products.variants.store');
    Route::put('products/{product}/variants/{variant}', [VariantController::class, 'update'])->whereNumber('variant')->name('products.variants.update');
    Route::delete('products/{product}/variants/{variant}', [VariantController::class, 'destroy'])->whereNumber('variant')->name('products.variants.destroy');

    Route::get('orders', [OrderController::class, 'index'])->name('orders.index');
    Route::get('orders/{order}', [OrderController::class, 'show'])->name('orders.show');
    Route::patch('orders/{order}/status', [OrderController::class, 'update'])->name('orders.update');
    Route::get('payments/{payment}/proof', [PaymentController::class, 'proof'])->name('payments.proof');
    Route::patch('payments/{payment}', [PaymentController::class, 'update'])->name('payments.update');

    Route::get('memberships', [MembershipController::class, 'index'])->name('memberships.index');
    Route::patch('memberships/{membership}', [MembershipController::class, 'update'])->name('memberships.update');
    Route::get('tickets', [TicketController::class, 'index'])->name('tickets.index');
    Route::get('tickets/{ticket}', [TicketController::class, 'show'])->name('tickets.show');
    Route::patch('tickets/{ticket}', [TicketController::class, 'update'])->name('tickets.update');

    Route::get('settings', [SettingsController::class, 'edit'])->name('settings.edit');
    Route::put('settings', [SettingsController::class, 'update'])->name('settings.update');
    Route::resource('shipping-rates', ShippingRateController::class)->except('show');
});
