<?php

namespace App\Services\Newsletter;

use App\Models\Setting;

/**
 * Resolves the newsletter sender driver based on the
 * `newsletter_sending_driver` setting.
 *
 * Recognised drivers:
 *  - "disabled"     → DisabledNewsletterSender (default; refuses everything)
 *  - "local"        → LocalNewsletterSender    (Mail::send for test emails only)
 *  - "external_api" → ExternalApiNewsletterSender (delegates to Agares SaaS;
 *                    falls back to Disabled if the SaaS is not configured)
 */
class NewsletterSenderFactory
{
    public static function make(?string $driver = null): NewsletterSenderInterface
    {
        $driver = $driver ?: Setting::str('newsletter_sending_driver', 'disabled');

        return match ($driver) {
            'local'        => new LocalNewsletterSender(),
            'external_api' => new ExternalApiNewsletterSender(new AgaresNewsletterApiClient()),
            default        => new DisabledNewsletterSender(),
        };
    }
}
