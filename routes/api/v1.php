<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\V1\ContentController;
use App\Http\Controllers\Api\V1\SettingsController;
use App\Http\Controllers\Api\V1\MediaController;
use App\Http\Controllers\Api\V1\AdminController;

Route::prefix('v1')->group(function () {

    Route::get('/health', fn () => ['ok' => true, 'version' => 1]);

    // Public content (still requires api key, but only "read" keys)
    Route::middleware(['api.key:content:read', 'throttle:api-keys'])->group(function () {
        Route::get('/menus', [ContentController::class, 'menus']);
        Route::get('/sites', [ContentController::class, 'sites']);
        Route::get('/sites/{slug}', [ContentController::class, 'siteBySlug']);

        Route::get('/sites/{siteSlug}/categories', [ContentController::class, 'categoriesBySite']);
        Route::get('/sites/{siteSlug}/articles', [ContentController::class, 'articlesBySite']);
        Route::get('/articles/{article}', [ContentController::class, 'article']); // model bind by id
        Route::get('/categories/{category}/articles', [ContentController::class, 'articlesByCategory']);
    });

    // Preview (drafts/unpublished) – require preview scope
    Route::middleware(['api.key:preview:read', 'throttle:api-keys'])->group(function () {
        Route::get('/preview/sites/{slug}', [ContentController::class, 'previewSiteBySlug']);
        Route::get('/preview/articles/{article}', [ContentController::class, 'previewArticle']);
    });

    Route::middleware(['api.key:settings:read_public', 'throttle:api-keys'])->group(function () {
        Route::get('/settings', [SettingsController::class, 'public']);
    });

    Route::middleware(['api.key:media:read', 'throttle:api-keys'])->group(function () {
        Route::get('/media/{media}', [MediaController::class, 'show']);
    });

    Route::middleware(['api.key:admin:read', 'throttle:api-keys'])->prefix('admin')->group(function () {
        Route::get('/users', [AdminController::class, 'users']);
        Route::get('/roles', [AdminController::class, 'roles']);
        Route::get('/permissions', [AdminController::class, 'permissions']);
    });

});
