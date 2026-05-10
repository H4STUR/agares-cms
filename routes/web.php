<?php

use Illuminate\Support\Facades\Route;
use App\Http\Middleware\CheckMaintenanceMode;

// controllers
use App\Http\Controllers\{
    ProfileController,
    ContactController,
    Auth\LoginController,
    Auth\RegisterController,
    Auth\SocialAuthController,
};

// frontend controllers
use App\Http\Controllers\Frontend\{
    PageController,
    CookieConsentController,
};

// Admin controllers
use App\Http\Controllers\Admin\{
    DashboardController,
    SettingsController,
    SiteController,
    CategoryController,
    ArticleController,
    UserController,
    InputInstanceController,
    InputTemplateController,
    MenuController,
    CustomController,
    MediaController,
    PermissionController,
    UserSettingsController,
    GalleryController,
    CookieController,
    FormController,
    FormFieldController,
    SearchController,
    NotificationController,
};

// Forum controllers
use App\Http\Controllers\Admin\Forum\{
    ForumController,
};

// Forum controllers
use App\Http\Controllers\Admin\Tools\{
    QRGeneratorController,
};

// API controllers
use App\Http\Controllers\Admin\API\{
    APIController,
};

Route::middleware(['auth', 'verified', 'can:view admin panel'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/dashboard/realtime-users', [DashboardController::class, 'realtimeUsers'])->name('dashboard.realtime-users');
    Route::get('/documentation', [DashboardController::class, 'documentationCMS'])->name('documentation');
    Route::get('/info', [DashboardController::class, 'infoCMS'])->name('info');
    
    /* =========================
     * GLOBAL SEARCH
     * ========================= */
    Route::get('/search', [SearchController::class, 'search'])->name('search');

    /* =========================
     * SITES
     * ========================= */
    Route::get('/sites', [SiteController::class, 'index'])->name('sites');
    Route::get('/sites/create', [SiteController::class, 'create'])->name('sites.create');
    Route::post('/sites', [SiteController::class, 'store'])->name('sites.store');
    Route::get('/sites/{site}', [SiteController::class, 'show'])->name('sites.show');
    Route::get('/sites/{site}/edit', [SiteController::class, 'edit'])->name('sites.edit');
    Route::patch('/sites/{site}', [SiteController::class, 'update'])->name('sites.update');
    Route::delete('/sites/{site}', [SiteController::class, 'delete'])->name('sites.delete');
    Route::get('/menus/{menu}/sites', [SiteController::class, 'getSitesByMenu'])->name('menus.sites');
    Route::get('/get-input-template/{type}', [SiteController::class, 'getInputTemplate']);
    Route::post('/sites/{site}/restore', [SiteController::class, 'restore'])->name('sites.restore');
    Route::delete('/sites/{site}/force', [SiteController::class, 'forceDelete'])->name('sites.forceDelete');

    /* =========================
    * DUPLICATE
    * ========================= */

    // duplicate site
    Route::post('/sites/{site}/duplicate', [SiteController::class, 'duplicate'])->name('sites.duplicate');

    // duplicate article (scoped to site)
    Route::post('sites/{site}/articles/{article}/duplicate', [ArticleController::class, 'duplicate'])->name('articles.duplicate');

    /* =========================
     * INPUTS (Input Instances)
     * ========================= */

    // Create input instance for any owner type (your preferred route)
    Route::post('/{type}/{id}/inputs', [InputInstanceController::class, 'store'])
        ->where('type', 'site|category|article')
        ->name('inputs.store');

    // Delete input instance (by id)
    Route::delete('/{type}/inputs/{instanceId}', [InputInstanceController::class, 'delete'])
        ->where('type', 'site|category|article')
        ->name('inputs.delete');

    // Move input instance up/down
    Route::post('/{type}/inputs/{instanceId}/move', [InputInstanceController::class, 'move'])
        ->where('type', 'site|category|article')
        ->name('inputs.move');

    // Bulk update values (this is what your edit screen uses)
    Route::patch('/input-instances/bulk', [InputInstanceController::class, 'bulkUpdate'])
        ->name('inputInstances.bulkUpdate');

    // Apply defaults (if you still use it)
    Route::post('/{type}/{id}/inputs/apply-defaults', [InputInstanceController::class, 'applyDefaults'])
        ->where('type', 'site|category|article')
        ->name('inputs.applyDefaults');

    // Apply template to owner (site/article/category)
    Route::post('/input-templates/apply', [InputTemplateController::class, 'applyToOwner'])
        ->name('inputTemplates.applyToOwner');

    // FAQ (inside InputInstanceController)
    Route::post('/input-instances/{instanceId}/faq/settings', [InputInstanceController::class, 'faqSaveSettings'])
        ->name('inputInstances.faq.settings');

    Route::post('/input-instances/{instanceId}/faq/items', [InputInstanceController::class, 'faqItemStore'])
        ->name('inputInstances.faq.items.store');

    Route::patch('/input-instances/{instanceId}/faq/items/bulk', [InputInstanceController::class, 'faqItemsBulkUpdate'])
        ->name('inputInstances.faq.items.bulkUpdate');

    Route::post('/faq-items/{itemId}/move', [InputInstanceController::class, 'faqItemMove'])
        ->name('faqItems.move');

    Route::delete('/faq-items/{itemId}', [InputInstanceController::class, 'faqItemDestroy'])
        ->name('faqItems.destroy');


    /* =========================
     * GALLERY (only GalleryController handles gallery)
     * ========================= */
    Route::post('/input-instances/{inputInstance}/gallery/ensure', [GalleryController::class, 'ensureForInputInstance'])->name('inputInstances.gallery.ensure');
    // Upload images into the input instance gallery
    Route::post('/input-instances/{inputInstance}/gallery/upload', [GalleryController::class, 'uploadToInputInstance'])->name('inputInstances.gallery.upload');
    // Reorder gallery items
    Route::post('/galleries/{gallery}/reorder', [GalleryController::class, 'reorder'])->name('galleries.reorder');
    // Remove an image from a gallery (does NOT delete file from media library)
    Route::delete('/galleries/{gallery}/media/{media}', [GalleryController::class, 'removeFromGallery'])->name('galleries.media.remove');


    /* =========================
     * Input instance files (like gallery but for files)
     * ========================= */
    Route::post('/input-instances/{instanceId}/files/upload', [InputInstanceController::class, 'uploadFiles'])->name('inputInstances.files.upload');
    Route::delete('/input-instances/{instanceId}/files/{mediaId}', [InputInstanceController::class, 'detachFile'])->name('inputInstances.files.detach');
    Route::post('/input-instances/{instanceId}/files/reorder', [InputInstanceController::class, 'reorderFiles'])->name('inputInstances.files.reorder'); 
    Route::patch('/input-instances/{instanceId}/value', [InputInstanceController::class, 'updateValue'])->name('inputInstances.updateValue');
    Route::post('/input-instances/{instance}/image/replace', [InputInstanceController::class, 'replaceImage'])->name('inputInstances.replaceImage');

    // Category -> Article fields (scoped template items)
    Route::post('sites/{site}/categories/{category}/article-template/items', [CategoryController::class, 'storeArticleTemplateItem'])->name('categories.articleTemplate.items.store');
    Route::delete('sites/{site}/categories/{category}/article-template/items/{item}', [CategoryController::class, 'deleteArticleTemplateItem'])->name('categories.articleTemplate.items.delete');
    Route::post('sites/{site}/categories/{category}/article-template/items/reorder', [CategoryController::class, 'reorderArticleTemplateItems'])->name('categories.articleTemplate.items.reorder');

    /* =========================
    * forms
    * ========================= */
    Route::post('/forms/{form}/settings', [FormController::class, 'updateSettings'])->name('forms.settings');
    Route::post('/forms/{form}/fields', [FormFieldController::class, 'store'])->name('forms.fields.store');
    Route::delete('/forms/fields/{field}', [FormFieldController::class, 'destroy'])->name('forms.fields.destroy');
    Route::post('/forms/fields/{field}/move', [FormFieldController::class, 'move'])->name('forms.fields.move');
    Route::patch('/forms/fields/{field}', [FormFieldController::class, 'update'])->name('forms.fields.update');
    Route::patch('/forms/{form}/fields/bulk', [FormFieldController::class, 'bulkUpdate'])->name('forms.fields.bulkUpdate');

    /* =========================
     * CATEGORIES (per Site)
     * ========================= */
    Route::get('sites/{site}/categories/create', [CategoryController::class, 'create'])->name('categories.create');
    Route::post('sites/{site}/categories', [CategoryController::class, 'store'])->name('categories.store');
    Route::get('sites/{site}/categories/{category}/edit', [CategoryController::class, 'edit'])->name('categories.edit');
    Route::patch('sites/{site}/categories/{category}', [CategoryController::class, 'update'])->name('categories.update');
    Route::delete('sites/{site}/categories/{category}', [CategoryController::class, 'delete'])->name('categories.delete');

    /* =========================
     * ARTICLES (per Site)
     * ========================= */
    Route::get('sites/{site}/articles/create', [ArticleController::class, 'create'])->name('articles.create');
    Route::post('sites/{site}/articles', [ArticleController::class, 'store'])->name('articles.store');
    Route::get('sites/{site}/articles/{article}/edit', [ArticleController::class, 'edit'])->name('articles.edit');
    Route::patch('sites/{site}/articles/{article}', [ArticleController::class, 'update'])->name('articles.update');
    Route::delete('sites/{site}/articles/{article}', [ArticleController::class, 'delete'])->name('articles.delete');
    Route::post('sites/{site}/articles/reorder', [ArticleController::class, 'reorder'])->name('articles.reorder');
    Route::post('sites/{site}/articles/{articleId}/restore', [ArticleController::class, 'restore'])->name('articles.restore');
    Route::delete('sites/{site}/articles/{articleId}/force', [ArticleController::class, 'forceDelete'])->name('articles.forceDelete');

    /* =========================
     * SETTINGS
     * ========================= */
    Route::get('/settings', [SettingsController::class, 'index'])->name('settings');
    Route::patch('/settings', [SettingsController::class, 'update'])->name('settings.update');
    Route::delete('/settings/{id}', [SettingsController::class, 'deleteSetting'])->name('settings.deleteSetting');
    Route::post('/settings/addcustom', [SettingsController::class, 'storeCustom'])->name('settings.storeCustom');
    Route::post('/cache/clear', [SettingsController::class, 'clearCache'])->name('cache.clear');

    /* =========================
     * USERS
     * ========================= */
    Route::get('/users', [UserController::class, 'index'])->name('users');
    Route::post('/users/add', [UserController::class, 'store'])->name('users.store');

    /* =========================
     * MENUS
     * ========================= */
    Route::get('/menus', [MenuController::class, 'index'])->name('menus');
    Route::post('/menus', [MenuController::class, 'store'])->name('menus.store');
    Route::delete('/menus/{menu}', [MenuController::class, 'destroy'])->name('menus.destroy');
    Route::post('/menus/{menu}/sites/{site}/move-up', [SiteController::class, 'moveUp'])->name('sites.moveUp');
    Route::post('/menus/{menu}/sites/{site}/move-down', [SiteController::class, 'moveDown'])->name('sites.moveDown');

    /* =========================
     * MEDIA LIBRARY (only MediaController owns CRUD for /media)
     * ========================= */
    Route::get('/media', [MediaController::class, 'index'])->name('media');
    Route::patch('/media/{media}', [MediaController::class, 'update'])->name('media.update');
    Route::post('/media/upload', [MediaController::class, 'upload'])->name('media.upload');
    Route::patch('/media/{id}/rename', [MediaController::class, 'rename'])->name('media.rename');
    Route::delete('/media/{id}', [MediaController::class, 'delete'])->name('media.delete');

    /* =========================
     * CUSTOM CODE PAGE
     * ========================= */
    Route::middleware('can:manage custom')->group(function () {
        Route::get('/custom', [CustomController::class, 'index'])->name('custom');
        Route::patch('/custom', [CustomController::class, 'update'])->name('custom.update');
    });

    /* =========================
    * PERMISSIONS
    * ========================= */
    Route::get('/permissions', [PermissionController::class, 'index'])->name('permissions');
    Route::get('/permissions/roles/{role}/edit', [PermissionController::class, 'editRole'])->name('permissions.roles.edit');
    Route::patch('/permissions/roles/{role}', [PermissionController::class, 'updateRole'])->name('permissions.roles.update');


    // keep existing
    Route::post('/permissions/assign', [PermissionController::class, 'assign'])->name('permissions.assign');
    Route::post('/permissions/create', [PermissionController::class, 'create'])->name('permissions.create');
    Route::delete('/permissions/{permission}', [PermissionController::class, 'delete'])->name('permissions.delete');
    Route::post('/roles/create', [PermissionController::class, 'createRole'])->name('roles.create');
    Route::delete('/roles/{role}', [PermissionController::class, 'deleteRole'])->name('roles.delete');

    /* =========================
     * robots and sitemap
     * ========================= */
    Route::post('/settings/robots', [SettingsController::class, 'saveRobots'])->name('settings.robots.save');
    Route::post('/settings/sitemap/generate', [SettingsController::class, 'generateSitemap'])->name('settings.sitemap.generate');
    Route::post('/settings/addcustom', [SettingsController::class, 'storeCustom'])->name('settings.storeCustom');

    /* =========================
     * cookies
     * ========================= */
    Route::get('/cookies', [CookieController::class, 'index'])->name('cookies');

    Route::get('/cookies/scans', [CookieController::class, 'scans'])->name('cookies.scans');
    Route::get('/cookies/scans/{scan}', [CookieController::class, 'showScan'])->name('cookies.scans.show');

    Route::get('/cookies/settings', [CookieController::class, 'editSettings'])->name('cookies.settings.edit');
    Route::post('/cookies/settings', [CookieController::class, 'updateSettings'])->name('cookies.settings.update');

    Route::post('/cookies/saas-settings', [CookieController::class, 'saveSaasSettings'])->name('cookies.saas.settings');
    Route::post('/cookies/connection-check', [CookieController::class, 'checkConnection'])->name('cookies.connection.check');
    Route::post('/cookies/scan-async', [CookieController::class, 'scanAsync'])->name('cookies.scan.async');
    Route::get('/cookies/scan-progress/{scan}', [CookieController::class, 'scanProgress'])->name('cookies.scan.progress');
    Route::post('/cookies/scan-cancel/{scan}', [CookieController::class, 'cancelScan'])->name('cookies.scan.cancel');

    /* =========================
    * Forum
    * ========================= */
    Route::get('/forum', [ForumController::class, 'index'])->name('forum');


    /* =========================
    * Tools
    * ========================= */
    Route::get('/tools/qr-generator', [QRGeneratorController::class, 'index'])->name('tools.qr-generator');

    /* =========================
     * NOTIFICATIONS
     * ========================= */
    Route::patch('/notifications/read-all', [NotificationController::class, 'markAllRead'])->name('notifications.readAll');
    Route::delete('/notifications/dismiss-all', [NotificationController::class, 'dismissAll'])->name('notifications.dismissAll');
    Route::patch('/notifications/{notification}/read', [NotificationController::class, 'markRead'])->name('notifications.read');
    Route::delete('/notifications/{notification}', [NotificationController::class, 'dismiss'])->name('notifications.dismiss');

    /* =========================
    * API
    * ========================= */
    Route::get('/api', [APIController::class, 'index'])->name('api.index');
    Route::get('/api/documentation', [DashboardController::class, 'documentationAPI'])->name('api.documentation');
    Route::post('/api/keys', [APIController::class, 'store'])->name('api.keys.store');
    Route::post('/api/keys/{apiKey}/revoke', [APIController::class, 'revoke'])->name('api.keys.revoke');

    require base_path('routes/ecommerce.admin.php');
});


/* =========================
 * AUTH ROUTES (should take precedence)
 * ========================= */
Route::group([], function () {

    Route::get('/oauth/{provider}/redirect', [SocialAuthController::class, 'redirect'])
        ->name('oauth.redirect');

    Route::get('/oauth/{provider}/callback', [SocialAuthController::class, 'callback'])
        ->name('oauth.callback');

});


/* =========================
 * USER PROFILE (public when public_profiles setting allows it)
 * ========================= */
Route::prefix('user')->name('admin.')->group(function () {
    Route::get('/{user}', [UserSettingsController::class, 'profile'])->name('user.profile');
});

/* =========================
 * USER ACCOUNT (auth required)
 * ========================= */
Route::prefix('user')->middleware(['auth'])->name('admin.')->group(function () {
    Route::get('{user}/settings', [UserSettingsController::class, 'edit'])->name('user.settings');
    Route::patch('{user}/settings', [UserSettingsController::class, 'update'])->name('user.settings.update');
    Route::patch('{user}/settings/password', [UserSettingsController::class, 'updatePassword'])->name('user.settings.password');
    Route::delete('{user}', [UserSettingsController::class, 'destroy'])->name('user.delete');

    // Ecommerce user sections
    Route::get('{user}/orders', [UserSettingsController::class, 'orders'])->name('user.orders');
    Route::get('{user}/favorites', [UserSettingsController::class, 'favorites'])->name('user.favorites');
    Route::get('{user}/tracking', [UserSettingsController::class, 'tracking'])->name('user.tracking');
    Route::get('{user}/invoices', [UserSettingsController::class, 'invoices'])->name('user.invoices');
    Route::get('{user}/returns', [UserSettingsController::class, 'returns'])->name('user.returns');
});

require __DIR__.'/auth.php';

/* =========================
 * FRONTEND ROUTES (maintenance-aware)
 * ========================= */
Route::middleware(['maintenance'])->group(function () {
    Route::get('/', [PageController::class, 'showHomepage'])->name('home');

    require base_path('routes/ecommerce.front.php');

    Route::get('/{site:slug}', [PageController::class, 'showSite'])->name('site.show');
    Route::get('/{site:slug}/{category}', [PageController::class, 'showCategory'])->name('category.show');
    Route::get('/{site:slug}/{category}/{articleId}/{articleName}', [PageController::class, 'showArticle'])->name('article.show');

    //form
    Route::post('/forms/{form}/submit', [App\Http\Controllers\Frontend\FormController::class, 'submit'])->name('forms.submit');
    // cookie consent — paths avoid "cookie" keyword so Brave/EasyList filters don't block them
    Route::get('/api/cc/cfg', [CookieConsentController::class, 'show']);
    Route::get('/api/cc/cat', [CookieConsentController::class, 'catalog']);

});

