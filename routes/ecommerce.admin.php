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


Route::middleware(['setting:enable_ecommerce,true,abort404'])
    ->prefix('ecommerce')
    ->name('ecommerce.')
    ->group(function () {

        Route::get('/', [EcommerceDashboardController::class, 'index'])->name('dashboard');

        // Import / export — must be declared BEFORE the resource to avoid {product} binding
        Route::get('products/export',          [ProductImportExportController::class, 'export'])->name('products.export');
        Route::post('products/export/selected', [ProductImportExportController::class, 'exportSelected'])->name('products.export.selected');
        Route::get('products/import',          [ProductImportExportController::class, 'importForm'])->name('products.import');
        Route::post('products/import',         [ProductImportExportController::class, 'importProcess'])->name('products.import.process');
        Route::get('products/import/template', [ProductImportExportController::class, 'template'])->name('products.import.template');

        Route::resource('products', ProductController::class);
        Route::post('products/{product}/variants/generate',[ProductController::class, 'generateVariants'])->name('products.variants.generate');
        Route::resource('variants', ProductVariantController::class)->only(['edit', 'update', 'destroy']);
        Route::post('products/{product}/variants', [ProductVariantController::class, 'store'])->name('products.variants.store');
        Route::post('products/{product}/variants/generate', [ProductController::class, 'generateVariants'])->name('products.variants.generate');

        Route::resource('categories', CategoryController::class);
        Route::resource('tags', TagController::class);

        Route::resource('attributes', AttributeController::class);
        Route::get('attributes/{attribute}/values', [AttributeValueController::class, 'index'])->name('attributes.values.index');
        Route::post('attributes/{attribute}/values', [AttributeValueController::class, 'store'])->name('attributes.values.store');
        Route::delete('attributes/{attribute}/values/{value}', [AttributeValueController::class, 'destroy'])->name('attributes.values.destroy');

        Route::get('orders', [OrderController::class, 'index'])->name('orders.index');
        Route::get('orders/{order}', [OrderController::class, 'show'])->name('orders.show');
        Route::patch('orders/{order}/status', [OrderController::class, 'updateStatus'])->name('orders.updateStatus');

        Route::resource('coupons', CouponController::class);
        Route::patch('coupons/{coupon}/toggle', [CouponController::class, 'toggleEnabled'])->name('coupons.toggle');

        Route::resource('shipping-methods', ShippingMethodController::class);
        Route::patch('shipping-methods/{shippingMethod}/toggle', [ShippingMethodController::class, 'toggleEnabled'])->name('shipping-methods.toggle');

        Route::resource('tax-rules', TaxRuleController::class);

        Route::resource('payment-providers', PaymentProviderController::class)->only(['index', 'edit', 'update']);
        Route::resource('settings', SettingController::class)->only(['index','store','update','destroy']);
    });
