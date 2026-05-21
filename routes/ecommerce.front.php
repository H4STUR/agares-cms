<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Frontend\Ecommerce\ShopController;
use App\Http\Controllers\Frontend\Ecommerce\ProductController;
use App\Http\Controllers\Frontend\Ecommerce\CartController;
use App\Http\Controllers\Frontend\Ecommerce\CheckoutController;
use App\Http\Controllers\Frontend\Ecommerce\PaymentReturnController;
use App\Http\Controllers\Frontend\Ecommerce\PaymentWebhookController;

Route::middleware(['maintenance', 'setting:enable_ecommerce,true,abort404'])
->prefix('shop')
->name('shop.')
->group(function () {

    Route::get('/', [ShopController::class, 'index'])->name('home');

    Route::get('/product/{slug}', [ProductController::class, 'show'])->name('product.show');

    Route::get('/cart', [CartController::class, 'index'])->name('cart');
    Route::post('/cart', [CartController::class, 'add'])->name('cart.add');
    Route::patch('/cart/{productId}', [CartController::class, 'update'])->name('cart.update');
    Route::delete('/cart/{productId}', [CartController::class, 'remove'])->name('cart.remove');
    Route::delete('/cart', [CartController::class, 'clear'])->name('cart.clear');

    Route::get('/checkout', [CheckoutController::class, 'index'])->name('checkout');
    Route::post('/checkout', [CheckoutController::class, 'store'])->name('checkout.store');

    Route::get('/order/{orderNumber}/confirmation', [CheckoutController::class, 'confirmation'])->name('order.confirmation');
});

// Payment return + webhook — outside maintenance so gateways can always reach them.
// CSRF excluded in bootstrap/app.php for the webhook path.
Route::middleware(['setting:enable_ecommerce,true,abort404'])
    ->prefix('shop')
    ->name('shop.')
    ->group(function () {
        Route::get('/payment/return/{driver}', [PaymentReturnController::class, 'handle'])
            ->name('payment.return');

        Route::post('/payment/webhook/{driver}', [PaymentWebhookController::class, 'handle'])
            ->name('payment.webhook');
    });
