<?php

namespace App\Services\Newsletter;

use App\Models\Newsletter\NewsletterCampaign;

/**
 * Default driver. Refuses every send action.
 * Used when the admin has not yet picked a sender, or has explicitly turned sending off.
 */
class DisabledNewsletterSender implements NewsletterSenderInterface
{
    public function driver(): string
    {
        return 'disabled';
    }

    public function isEnabled(): bool
    {
        return false;
    }

    public function supportsBulk(): bool
    {
        return false;
    }

    public function sendTest(NewsletterCampaign $campaign, string $recipient): array
    {
        return [
            'success' => false,
            'message' => __('Newsletter sending is disabled. Set the newsletter sending driver in Settings to enable test emails.'),
        ];
    }

    public function delegateBulk(NewsletterCampaign $campaign): array
    {
        return [
            'success' => false,
            'message' => __('Newsletter sending is disabled. Set the newsletter sending driver to "external_api" to delegate campaigns.'),
        ];
    }
}
