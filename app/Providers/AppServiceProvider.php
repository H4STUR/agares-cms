<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Http\Request;
use App\Models\Setting;
use App\Models\CustomCode;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        RateLimiter::for('api-keys', function (Request $request) {
            $apiKey = $request->attributes->get('apiKey');
            $id = $apiKey?->id ?? $request->ip();

            return Limit::perMinute(120)->by('api-key:'.$id);
        });

        // 1) Keep your auth.* specific variable
        View::composer(['auth.*'], function ($view) {
            $view->with(
                'registrationEnabled',
                Setting::bool('enable_registration', false)
            );
        });

        // 3) GLOBAL settings - but EXCLUDE admin.settings.*
        View::composer('*', function ($view) {
            $viewName = $view->getName();

            // Do NOT inject $settings into admin settings pages
            if (str_starts_with($viewName, 'pages.admin.settings')) {
                return;
            }

            $settings = cache()->remember(
                'settings.all.kv',
                now()->addMinutes(10),
                fn () => Setting::query()->pluck('value', 'key')->toArray()
            );

            $view->with('settings', $settings);
        });

        // GLOBAL custom codes (frontend)
        View::composer('*', function ($view) {
            $viewName = $view->getName();

            // Don’t inject into admin pages (optional, but keeps things clean)
            if (str_starts_with($viewName, 'pages.admin.')) {
                return;
            }

            $customCodes = cache()->remember(
                'custom_codes.by_type',
                now()->addMinutes(10),
                fn () => CustomCode::query()
                    ->whereIn('type', ['script', 'style'])
                    ->get()
                    ->keyBy('type')
            );

            $view->with('customScript', optional($customCodes->get('script'))->content);
            $view->with('customStyle',  optional($customCodes->get('style'))->content);
        });

    }

}
