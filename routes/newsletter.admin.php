<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\Newsletter\NewsletterDashboardController;
use App\Http\Controllers\Admin\Newsletter\SubscriberController;
use App\Http\Controllers\Admin\Newsletter\ListController;
use App\Http\Controllers\Admin\Newsletter\TemplateController;
use App\Http\Controllers\Admin\Newsletter\CampaignController;
use App\Http\Controllers\Admin\Newsletter\NewsletterSettingsController;

/*
 | Newsletter admin routes.
 | Gated by the `enable_newsletter` setting + the `view newsletter` permission.
 | Mutating routes additionally require the relevant `manage ...` permission.
 */
Route::middleware(['setting:enable_newsletter,true,abort404', 'can:view newsletter'])
    ->prefix('newsletter')
    ->name('newsletter.')
    ->group(function () {

        Route::get('/', [NewsletterDashboardController::class, 'index'])->name('dashboard');

        /* ---------- SUBSCRIBERS ---------- */
        Route::middleware('can:view newsletter subscribers')->group(function () {
            Route::get('subscribers', [SubscriberController::class, 'index'])->name('subscribers.index');
            Route::get('subscribers/{subscriber}/edit', [SubscriberController::class, 'edit'])->name('subscribers.edit');
        });

        Route::middleware('can:manage newsletter subscribers')->group(function () {
            Route::get('subscribers/create', [SubscriberController::class, 'create'])->name('subscribers.create');
            Route::post('subscribers', [SubscriberController::class, 'store'])->name('subscribers.store');
            Route::put('subscribers/{subscriber}', [SubscriberController::class, 'update'])->name('subscribers.update');
            Route::patch('subscribers/{subscriber}', [SubscriberController::class, 'update']);
            Route::delete('subscribers/{subscriber}', [SubscriberController::class, 'destroy'])->name('subscribers.destroy');
            Route::post('subscribers/{subscriber}/sync-suppression', [SubscriberController::class, 'syncSuppression'])->name('subscribers.sync-suppression');
        });

        /* ---------- LISTS ---------- */
        Route::middleware('can:view newsletter lists')->group(function () {
            Route::get('lists', [ListController::class, 'index'])->name('lists.index');
            Route::get('lists/{list}/edit', [ListController::class, 'edit'])->name('lists.edit');
        });

        Route::middleware('can:manage newsletter lists')->group(function () {
            Route::get('lists/create', [ListController::class, 'create'])->name('lists.create');
            Route::post('lists', [ListController::class, 'store'])->name('lists.store');
            Route::put('lists/{list}', [ListController::class, 'update'])->name('lists.update');
            Route::patch('lists/{list}', [ListController::class, 'update']);
            Route::delete('lists/{list}', [ListController::class, 'destroy'])->name('lists.destroy');
        });

        /* ---------- TEMPLATES ---------- */
        Route::middleware('can:view newsletter templates')->group(function () {
            Route::get('templates', [TemplateController::class, 'index'])->name('templates.index');
            Route::get('templates/{template}/edit', [TemplateController::class, 'edit'])->name('templates.edit');
            Route::get('templates/{template}/preview', [TemplateController::class, 'preview'])->name('templates.preview');
        });

        Route::middleware('can:manage newsletter templates')->group(function () {
            Route::get('templates/create', [TemplateController::class, 'create'])->name('templates.create');
            Route::post('templates', [TemplateController::class, 'store'])->name('templates.store');
            Route::put('templates/{template}', [TemplateController::class, 'update'])->name('templates.update');
            Route::patch('templates/{template}', [TemplateController::class, 'update']);
            Route::delete('templates/{template}', [TemplateController::class, 'destroy'])->name('templates.destroy');
        });

        /* ---------- CAMPAIGNS (drafts only — no bulk send in CMS) ---------- */
        Route::middleware('can:view newsletter campaigns')->group(function () {
            Route::get('campaigns', [CampaignController::class, 'index'])->name('campaigns.index');
            Route::get('campaigns/{campaign}/edit', [CampaignController::class, 'edit'])->name('campaigns.edit');
        });

        Route::middleware('can:preview newsletter campaigns')->group(function () {
            Route::get('campaigns/{campaign}/preview', [CampaignController::class, 'preview'])->name('campaigns.preview');
        });

        Route::middleware('can:send test newsletter campaigns')->group(function () {
            Route::get('campaigns/{campaign}/test', [CampaignController::class, 'testSendForm'])->name('campaigns.test.form');
            Route::post('campaigns/{campaign}/test', [CampaignController::class, 'testSend'])->name('campaigns.test.send');
        });

        Route::middleware('can:manage newsletter campaigns')->group(function () {
            Route::get('campaigns/create', [CampaignController::class, 'create'])->name('campaigns.create');
            Route::post('campaigns', [CampaignController::class, 'store'])->name('campaigns.store');
            Route::put('campaigns/{campaign}', [CampaignController::class, 'update'])->name('campaigns.update');
            Route::patch('campaigns/{campaign}', [CampaignController::class, 'update']);
            Route::delete('campaigns/{campaign}', [CampaignController::class, 'destroy'])->name('campaigns.destroy');
            Route::patch('campaigns/{campaign}/cancel', [CampaignController::class, 'cancel'])->name('campaigns.cancel');
        });

        /* ---------- EXTERNAL DELEGATION + SYNC + CANCEL ---------- */
        Route::middleware('can:delegate newsletter campaigns')->group(function () {
            Route::post('campaigns/{campaign}/delegate', [CampaignController::class, 'delegate'])->name('campaigns.delegate');
            Route::post('campaigns/{campaign}/cancel-external', [CampaignController::class, 'cancelExternal'])->name('campaigns.cancel-external');
        });

        Route::middleware('can:sync newsletter campaigns')->group(function () {
            Route::post('campaigns/{campaign}/sync', [CampaignController::class, 'syncStatus'])->name('campaigns.sync');
        });

        /* ---------- INTEGRATION SETTINGS ---------- */
        Route::middleware('can:view newsletter settings')->group(function () {
            Route::get('settings', [NewsletterSettingsController::class, 'index'])->name('settings.index');
        });

        Route::middleware('can:edit newsletter settings')->group(function () {
            Route::patch('settings', [NewsletterSettingsController::class, 'update'])->name('settings.update');
        });

        Route::middleware('can:test newsletter integration')->group(function () {
            Route::post('settings/test-connection', [NewsletterSettingsController::class, 'testConnection'])->name('settings.test-connection');
        });
    });
