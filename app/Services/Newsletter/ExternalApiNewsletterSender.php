<?php

namespace App\Services\Newsletter;

use App\Mail\Newsletter\NewsletterCampaignTestMail;
use App\Models\Newsletter\NewsletterCampaign;
use App\Models\Setting;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Throwable;

/**
 * The Phase 3 driver — delegates real bulk sending to the external Agares SaaS
 * newsletter API. Agares CMS itself never iterates the subscriber table to send.
 *
 * `sendTest()` still runs locally (one synchronous email) so admins can preview
 * a campaign without reaching the SaaS.
 */
class ExternalApiNewsletterSender implements NewsletterSenderInterface
{
    public function __construct(private readonly AgaresNewsletterApiClient $client) {}

    public function driver(): string
    {
        return 'external_api';
    }

    public function isEnabled(): bool
    {
        return $this->client->isConfigured();
    }

    public function supportsBulk(): bool
    {
        return true;
    }

    public function sendTest(NewsletterCampaign $campaign, string $recipient): array
    {
        if (!filter_var($recipient, FILTER_VALIDATE_EMAIL)) {
            return ['success' => false, 'message' => __('The test recipient email is not valid.')];
        }
        if (trim((string) $campaign->subject) === '' || trim((string) $campaign->body) === '') {
            return ['success' => false, 'message' => __('The campaign needs a subject and a body before a test email can be sent.')];
        }

        try {
            $mailable = new NewsletterCampaignTestMail($campaign);

            $fromEmail = trim((string) ($campaign->from_email ?: Setting::str('newsletter_from_email', '')));
            $fromName  = trim((string) ($campaign->from_name  ?: Setting::str('newsletter_from_name', '')));
            $replyTo   = trim((string) ($campaign->reply_to   ?: Setting::str('newsletter_reply_to', '')));

            if ($fromEmail !== '' && filter_var($fromEmail, FILTER_VALIDATE_EMAIL)) {
                $mailable->from($fromEmail, $fromName !== '' ? $fromName : null);
            }
            if ($replyTo !== '' && filter_var($replyTo, FILTER_VALIDATE_EMAIL)) {
                $mailable->replyTo($replyTo);
            }

            Mail::to($recipient)->send($mailable);

            $campaign->forceFill([
                'status'       => NewsletterCampaign::STATUS_TEST_SENT,
                'test_sent_at' => now(),
            ])->save();

            return ['success' => true, 'message' => __('Test newsletter sent to :email.', ['email' => $recipient])];
        } catch (Throwable $e) {
            Log::error('Newsletter (external driver) test send failed: ' . $e->getMessage(), [
                'campaign_id' => $campaign->id,
                'recipient'   => $recipient,
                'exception'   => $e,
            ]);
            return ['success' => false, 'message' => __('Failed to send test newsletter: :error', ['error' => $e->getMessage()])];
        }
    }

    public function delegateBulk(NewsletterCampaign $campaign): array
    {
        if (!$this->isEnabled()) {
            return [
                'success' => false,
                'message' => __('External newsletter API is not configured. Configure URL and API key in Newsletter settings.'),
            ];
        }

        if (!$campaign->isDelegatable()) {
            return [
                'success' => false,
                'message' => __('Campaign cannot be delegated in its current status (:status).', ['status' => $campaign->status]),
            ];
        }

        if (trim((string) $campaign->subject) === '' || trim((string) $campaign->body) === '') {
            return ['success' => false, 'message' => __('Campaign needs a subject and a body before delegation.')];
        }

        if ($campaign->lists()->count() === 0) {
            return [
                'success' => false,
                'message' => __('Pick at least one newsletter list before delegating. AgaresCMS will not send to "everyone" by default.'),
            ];
        }

        $built           = CampaignPayloadBuilder::build($campaign);
        $recipientCount  = $built['recipient_count'];

        if ($recipientCount === 0) {
            return [
                'success' => false,
                'message' => __('The selected lists contain no active subscribers. Delegation aborted.'),
            ];
        }

        $result = $this->client->sendCampaign($built['payload']);

        if (!($result['ok'] ?? false)) {
            $campaign->forceFill([
                'status'              => NewsletterCampaign::STATUS_EXTERNAL_FAILED,
                'external_last_error' => $result['message'] ?? 'Unknown error',
            ])->save();

            return ['success' => false, 'message' => $result['message'] ?? __('External API rejected the campaign.')];
        }

        $campaign->forceFill([
            'status'                 => NewsletterCampaign::STATUS_EXTERNAL_PENDING,
            'external_campaign_id'   => $result['external_campaign_id'] ?? null,
            'external_status'        => $result['external_status'] ?? null,
            'external_last_synced_at' => now(),
            'external_last_error'    => null,
            'delegated_at'           => now(),
        ])->save();

        Log::info('Newsletter campaign delegated to external API', [
            'campaign_id'          => $campaign->id,
            'external_campaign_id' => $campaign->external_campaign_id,
            'recipient_count'      => $recipientCount,
        ]);

        return [
            'success'              => true,
            'message'              => __('Campaign delegated to the external API. :count active recipient(s) were sent for delivery.', ['count' => $recipientCount]),
            'external_campaign_id' => $campaign->external_campaign_id,
        ];
    }
}
