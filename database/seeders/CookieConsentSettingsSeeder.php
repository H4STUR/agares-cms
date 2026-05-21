<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\CookieConsentSetting;

class CookieConsentSettingsSeeder extends Seeder
{
    public function run(): void
    {
        // Use app.url domain as default
        $domain = parse_url(config('app.url'), PHP_URL_HOST) ?? 'localhost';

        CookieConsentSetting::firstOrCreate(
            ['domain' => $domain],
            [
                'enabled' => true,
                'block_until_choice' => true,
                'remember_consent' => true,

                // Banner text
                'title' => 'We use cookies',
                'message' => 'We use cookies to ensure the proper functioning of the website, analyze traffic, and personalize content. You can manage your preferences at any time. <a href="/privacy-policy" class="cookie-link">Learn more</a>.  ',

                // Buttons
                'btn_accept_all' => 'Accept all',
                'btn_reject_all' => 'Reject all',
                'btn_manage' => 'Manage cookies',
                'btn_save' => 'Save preferences',

                // Defaults (UI state)
                'allow_essential' => true,
                'allow_functional' => true,
                'allow_analytics' => false,
                'allow_marketing' => false,

                // Category descriptions
                'desc_essential' =>
                    'Essential cookies are necessary for the website to function properly and cannot be disabled.',

                'desc_functional' =>
                    'Functional cookies enable additional features such as remembering your preferences or settings.',

                'desc_analytics' =>
                    'Analytics cookies help us understand how visitors interact with the website by collecting anonymous statistics.',

                'desc_marketing' =>
                    'Marketing cookies are used to track visitors across websites to display relevant advertisements.',
            ]
        );
    }
}
