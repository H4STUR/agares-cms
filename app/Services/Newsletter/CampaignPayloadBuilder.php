<?php

namespace App\Services\Newsletter;

use App\Models\Newsletter\NewsletterCampaign;
use App\Models\Newsletter\NewsletterSubscriber;
use App\Models\Setting;

/**
 * Builds the JSON payload sent to the Agares SaaS newsletter API.
 *
 * Shape — flat at the top level, exactly matching SaaS Phase 4.1/4.2 contract
 * (`POST /api/v1/services/newsletter/campaigns`):
 *
 *   {
 *     "source": "agares_cms",
 *     "project_id": "...",
 *     "source_campaign_id": "123",
 *     "title": "...",
 *     "subject": "...",
 *     "body": "<html>…</html>",
 *     "from_name": "...",
 *     "from_email": "...",
 *     "reply_to": "...",
 *     "callback_url": "https://cms/newsletter/external/webhook",
 *     "recipients": [{ email, name, source_subscriber_id, unsubscribe_token, unsubscribe_url, metadata }],
 *     "metadata": { cms_url, cms_campaign_url, cms_lists }
 *   }
 *
 * Recipient rules — strictly enforced here:
 *   - only `active` subscribers are included
 *   - pending / unsubscribed / bounced / complained are EXCLUDED
 *   - only subscribers attached to one of the campaign's selected lists
 *   - personal data limited to email + name + unsubscribe URL (GDPR minimisation)
 */
class CampaignPayloadBuilder
{
    /**
     * @return array{payload: array, recipient_count: int}
     */
    public static function build(NewsletterCampaign $campaign): array
    {
        $listIds = $campaign->lists()->pluck('newsletter_lists.id')->all();

        $recipients = [];
        if (!empty($listIds)) {
            NewsletterSubscriber::query()
                ->where('status', NewsletterSubscriber::STATUS_ACTIVE)
                ->whereHas('lists', fn ($q) => $q->whereIn('newsletter_lists.id', $listIds))
                ->select(['id', 'email', 'name', 'unsubscribe_token'])
                ->orderBy('id')
                ->chunk(500, function ($chunk) use (&$recipients) {
                    foreach ($chunk as $sub) {
                        $recipients[] = array_filter([
                            'email'                => $sub->email,
                            'name'                 => $sub->name,
                            'source_subscriber_id' => (string) $sub->id,
                            'unsubscribe_token'    => $sub->unsubscribe_token,
                            'unsubscribe_url'      => $sub->unsubscribe_token
                                ? route('newsletter.unsubscribe', ['token' => $sub->unsubscribe_token])
                                : null,
                        ], fn ($v) => $v !== null && $v !== '');
                    }
                });
        }

        $callbackUrl = route('newsletter.external.webhook');

        $payload = array_filter([
            'source'             => 'agares_cms',
            'project_id'         => Setting::str('newsletter_external_project_id', '') ?: null,
            'source_campaign_id' => (string) $campaign->id,
            'title'              => $campaign->title ?: null,
            'subject'            => (string) $campaign->subject,
            'body'               => (string) $campaign->body,
            'from_name'          => $campaign->from_name  ?: (Setting::str('newsletter_from_name', '')  ?: null),
            'from_email'         => $campaign->from_email ?: (Setting::str('newsletter_from_email', '') ?: null),
            'reply_to'           => $campaign->reply_to   ?: (Setting::str('newsletter_reply_to', '')   ?: null),
            'callback_url'       => $callbackUrl,
            'recipients'         => $recipients,
            'metadata'           => [
                'cms_url'          => config('app.url'),
                'cms_app_name'     => config('app.name'),
                'cms_campaign_id'  => $campaign->id,
                'cms_campaign_url' => route('admin.newsletter.campaigns.edit', $campaign),
                'cms_lists'        => $campaign->lists->map(fn ($l) => [
                    'id'   => $l->id,
                    'name' => $l->name,
                    'slug' => $l->slug,
                ])->values()->all(),
                'cms_created_at'   => optional($campaign->created_at)->toIso8601String(),
            ],
        ], fn ($v) => $v !== null);

        return [
            'payload'         => $payload,
            'recipient_count' => count($recipients),
        ];
    }
}
