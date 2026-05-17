<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\Ecommerce\EcommerceDashboardController;
use App\Http\Controllers\Admin\Ecommerce\ProductController;
use App\Http\Controllers\Admin\Ecommerce\CategoryController;
use App\Http\Controllers\Admin\Ecommerce\TagController;
use App\Http\Controllers\Admin\Ecommerce\AttributeController;
use App\Http\Controllers\Admin\Ecommerce\AttributeValueController;
use App\Http\Controllers\Admin\Ecommerce\OrderController;
use App\Http\Controllers\Admin\Ecommerce\CouponController;
use App\Http\Controllers\Admin\Ecommerce\ShippingMethodController;
use App\Http\Controllers\Admin\Ecommerce\TaxRuleController;
use App\Http\Controllers\Admin\Ecommerce\PaymentProviderController;
use App\Http\Controllers\Admin\Ecommerce\SettingController;
use App\Http\Controllers\Admin\Ecommerce\ProductVariantController;
use App\Http\Controllers\Admin\Ecommerce\ProductImportExportController;


/*
 | Ecommerce admin routes.
 | All routes require 'view ecommerce'. Mutating routes additionally require 'manage ecommerce'
 | (orders status changes additionally require 'manage orders').
 */
Route::middleware(['setting:enable_ecommerce,true,abort404', 'can:view ecommerce'])
    ->prefix('ecommerce')
    ->name('ecommerce.')
    ->group(function () {

        /* ---------- READ-ONLY (view ecommerce) ---------- */
        Route::get('/', [EcommerceDashboardController::class, 'index'])->name('dashboard');

        // GET-only resource indices/show pages
        // whereNumber() prevents literal segments like "create"/"export"/"import" from being captured by the wildcard
        Route::get('products', [ProductController::class, 'index'])->name('products.index');
        Route::get('products/{product}', [ProductController::class, 'show'])->whereNumber('product')->name('products.show');

        Route::get('categories', [CategoryController::class, 'index'])->name('categories.index');
        Route::get('categories/{category}', [CategoryController::class, 'show'])->whereNumber('category')->name('categories.show');

        Route::get('tags', [TagController::class, 'index'])->name('tags.index');
        Route::get('tags/{tag}', [TagController::class, 'show'])->whereNumber('tag')->name('tags.show');

        Route::get('attributes', [AttributeController::class, 'index'])->name('attributes.index');
        Route::get('attributes/{attribute}', [AttributeController::class, 'show'])->whereNumber('attribute')->name('attributes.show');
        Route::get('attributes/{attribute}/values', [AttributeValueController::class, 'index'])->whereNumber('attribute')->name('attributes.values.index');

        Route::middleware('can:view orders')->group(function () {
            Route::get('orders', [OrderController::class, 'index'])->name('orders.index');
            Route::get('orders/{order}', [OrderController::class, 'show'])->whereNumber('order')->name('orders.show');
        });

        Route::get('coupons', [CouponController::class, 'index'])->name('coupons.index');
        Route::get('coupons/{coupon}', [CouponController::class, 'show'])->whereNumber('coupon')->name('coupons.show');

        Route::get('shipping-methods', [ShippingMethodController::class, 'index'])->name('shipping-methods.index');
        Route::get('shipping-methods/{shipping_method}', [ShippingMethodController::class, 'show'])->whereNumber('shipping_method')->name('shipping-methods.show');

        Route::get('tax-rules', [TaxRuleController::class, 'index'])->name('tax-rules.index');
        Route::get('tax-rules/{tax_rule}', [TaxRuleController::class, 'show'])->whereNumber('tax_rule')->name('tax-rules.show');

        Route::get('payment-providers', [PaymentProviderController::class, 'index'])->name('payment-providers.index');

        Route::get('settings', [SettingController::class, 'index'])->name('settings.index');

        /* ---------- MUTATING (manage ecommerce) ---------- */
        Route::middleware('can:manage ecommerce')->group(function () {

            // Import / export must be declared BEFORE the resource create/edit to avoid {product} binding
            Route::get('products/export',          [ProductImportExportController::class, 'export'])->name('products.export');
            Route::post('products/export/selected', [ProductImportExportController::class, 'exportSelected'])->name('products.export.selected');
            Route::get('products/import',          [ProductImportExportController::class, 'importForm'])->name('products.import');
            Route::post('products/import',         [ProductImportExportController::class, 'importProcess'])->name('products.import.process');
            Route::get('products/import/template', [ProductImportExportController::class, 'template'])->name('products.import.template');

            // Products — create/edit/store/update/destroy
            Route::get('products/create', [ProductController::class, 'create'])->name('products.create');
            Route::post('products', [ProductController::class, 'store'])->name('products.store');
            Route::get('products/{product}/edit', [ProductController::class, 'edit'])->name('products.edit');
            Route::put('products/{product}', [ProductController::class, 'update'])->name('products.update');
            Route::patch('products/{product}', [ProductController::class, 'update']);
            Route::delete('products/{product}', [ProductController::class, 'destroy'])->name('products.destroy');

            Route::post('products/{product}/variants/generate', [ProductController::class, 'generateVariants'])->name('products.variants.generate');
            Route::post('products/{product}/variants', [ProductVariantController::class, 'store'])->name('products.variants.store');
            Route::get('variants/{variant}/edit', [ProductVariantController::class, 'edit'])->name('variants.edit');
            Route::put('variants/{variant}', [ProductVariantController::class, 'update'])->name('variants.update');
            Route::patch('variants/{variant}', [ProductVariantController::class, 'update']);
            Route::delete('variants/{variant}', [ProductVariantController::class, 'destroy'])->name('variants.destroy');

            // Categories
            Route::get('categories/create', [CategoryController::class, 'create'])->name('categories.create');
            Route::post('categories', [CategoryController::class, 'store'])->name('categories.store');
            Route::get('categories/{category}/edit', [CategoryController::class, 'edit'])->name('categories.edit');
            Route::put('categories/{category}', [CategoryController::class, 'update'])->name('categories.update');
            Route::patch('categories/{category}', [CategoryController::class, 'update']);
            Route::delete('categories/{category}', [CategoryController::class, 'destroy'])->name('categories.destroy');

            // Tags
            Route::get('tags/create', [TagController::class, 'create'])->name('tags.create');
            Route::post('tags', [TagController::class, 'store'])->name('tags.store');
            Route::get('tags/{tag}/edit', [TagController::class, 'edit'])->name('tags.edit');
            Route::put('tags/{tag}', [TagController::class, 'update'])->name('tags.update');
            Route::patch('tags/{tag}', [TagController::class, 'update']);
            Route::delete('tags/{tag}', [TagController::class, 'destroy'])->name('tags.destroy');

            // Attributes
            Route::get('attributes/create', [AttributeController::class, 'create'])->name('attributes.create');
            Route::post('attributes', [AttributeController::class, 'store'])->name('attributes.store');
            Route::get('attributes/{attribute}/edit', [AttributeController::class, 'edit'])->name('attributes.edit');
            Route::put('attributes/{attribute}', [AttributeController::class, 'update'])->name('attributes.update');
            Route::patch('attributes/{attribute}', [AttributeController::class, 'update']);
            Route::delete('attributes/{attribute}', [AttributeController::class, 'destroy'])->name('attributes.destroy');

            Route::post('attributes/{attribute}/values', [AttributeValueController::class, 'store'])->name('attributes.values.store');
            Route::delete('attributes/{attribute}/values/{value}', [AttributeValueController::class, 'destroy'])->name('attributes.values.destroy');

            // Coupons
            Route::get('coupons/create', [CouponController::class, 'create'])->name('coupons.create');
            Route::post('coupons', [CouponController::class, 'store'])->name('coupons.store');
            Route::get('coupons/{coupon}/edit', [CouponController::class, 'edit'])->name('coupons.edit');
            Route::put('coupons/{coupon}', [CouponController::class, 'update'])->name('coupons.update');
            Route::patch('coupons/{coupon}', [CouponController::class, 'update']);
            Route::delete('coupons/{coupon}', [CouponController::class, 'destroy'])->name('coupons.destroy');
            Route::patch('coupons/{coupon}/toggle', [CouponController::class, 'toggleEnabled'])->name('coupons.toggle');

            // Shipping methods
            Route::get('shipping-methods/create', [ShippingMethodController::class, 'create'])->name('shipping-methods.create');
            Route::post('shipping-methods', [ShippingMethodController::class, 'store'])->name('shipping-methods.store');
            Route::get('shipping-methods/{shipping_method}/edit', [ShippingMethodController::class, 'edit'])->name('shipping-methods.edit');
            Route::put('shipping-methods/{shipping_method}', [ShippingMethodController::class, 'update'])->name('shipping-methods.update');
            Route::patch('shipping-methods/{shipping_method}', [ShippingMethodController::class, 'update']);
            Route::delete('shipping-methods/{shipping_method}', [ShippingMethodController::class, 'destroy'])->name('shipping-methods.destroy');
            Route::patch('shipping-methods/{shippingMethod}/toggle', [ShippingMethodController::class, 'toggleEnabled'])->name('shipping-methods.toggle');

            // Tax rules
            Route::get('tax-rules/create', [TaxRuleController::class, 'create'])->name('tax-rules.create');
            Route::post('tax-rules', [TaxRuleController::class, 'store'])->name('tax-rules.store');
            Route::get('tax-rules/{tax_rule}/edit', [TaxRuleController::class, 'edit'])->name('tax-rules.edit');
            Route::put('tax-rules/{tax_rule}', [TaxRuleController::class, 'update'])->name('tax-rules.update');
            Route::patch('tax-rules/{tax_rule}', [TaxRuleController::class, 'update']);
            Route::delete('tax-rules/{tax_rule}', [TaxRuleController::class, 'destroy'])->name('tax-rules.destroy');

            // Payment providers
            Route::get('payment-providers/{payment_provider}/edit', [PaymentProviderController::class, 'edit'])->name('payment-providers.edit');
            Route::put('payment-providers/{payment_provider}', [PaymentProviderController::class, 'update'])->name('payment-providers.update');
            Route::patch('payment-providers/{payment_provider}', [PaymentProviderController::class, 'update']);

            // Ecommerce settings
            Route::post('settings', [SettingController::class, 'store'])->name('settings.store');
            Route::put('settings/{setting}', [SettingController::class, 'update'])->name('settings.update');
            Route::patch('settings/{setting}', [SettingController::class, 'update']);
            Route::delete('settings/{setting}', [SettingController::class, 'destroy'])->name('settings.destroy');
        });

        /* ---------- ORDERS — status updates require 'manage orders' ---------- */
        Route::patch('orders/{order}/status', [OrderController::class, 'updateStatus'])
            ->middleware('can:manage orders')
            ->name('orders.updateStatus');
    });
