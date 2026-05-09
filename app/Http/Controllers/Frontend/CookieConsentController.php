<?php
// app/Http/Controllers/Frontend/CookieConsentController.php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\CookieConsentSetting;
use App\Models\CookieScan;
use Illuminate\Support\Str;

class CookieConsentController extends Controller
{
    public function show()
    {
        $domain = parse_url(config('app.url'), PHP_URL_HOST) ?: request()->getHost();

        $settings = CookieConsentSetting::where('domain', $domain)->first();

        if (!$settings || !$settings->enabled) {
            return response()->json(['enabled' => false]);
        }

        return response()->json([
            'enabled' => true,

            'block_until_choice' => $settings->block_until_choice,
            'remember_consent'   => $settings->remember_consent,

            'title'   => $settings->title,
            'message' => $settings->message,

            'buttons' => [
                'accept_all' => $settings->btn_accept_all,
                'reject_all' => $settings->btn_reject_all,
                'manage'     => $settings->btn_manage,
                'save'       => $settings->btn_save,
            ],

            'categories' => [
                'essential' => [
                    'locked' => true,
                    'description' => $settings->desc_essential,
                ],
                'functional' => [
                    'enabled' => $settings->allow_functional,
                    'description' => $settings->desc_functional,
                ],
                'analytics' => [
                    'enabled' => $settings->allow_analytics,
                    'description' => $settings->desc_analytics,
                ],
                'marketing' => [
                    'enabled' => $settings->allow_marketing,
                    'description' => $settings->desc_marketing,
                ],
            ],
        ]);
    }

    public function catalog()
    {
        $domain = parse_url(config('app.url'), PHP_URL_HOST) ?: request()->getHost();

        $scan = CookieScan::where('domain', $domain)
            ->where('status', 'completed')
            ->latest('scanned_at')
            ->with('cookies')
            ->first();

        if (!$scan) {
            return response()->json([
                'scanned_at' => null,
                'categories' => [
                    'essential' => [],
                    'functional' => [],
                    'analytics' => [],
                    'marketing' => [],
                ],
            ]);
        }

        $grouped = $scan->cookies
            ->groupBy('type')
            ->map(function ($items) {
                return $items->map(function ($c) {
                    return [
                        'name' => $c->name,
                        'domain' => $c->domain,
                        'description' => $c->description,
                    ];
                })->values();
            });

        return response()->json([
            'scanned_at' => $scan->scanned_at?->toISOString(),
            'categories' => [
                'essential' => $grouped->get('essential', collect())->all(),
                'functional' => $grouped->get('functional', collect())->all(),
                'analytics' => $grouped->get('analytics', collect())->all(),
                'marketing' => $grouped->get('marketing', collect())->all(),
            ],
        ]);
    }
}
