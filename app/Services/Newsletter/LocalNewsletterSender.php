<?php

namespace App\Services\Newsletter;

use App\Mail\Newsletter\NewsletterCampaignTestMail;
use App\Models\Newsletter\NewsletterCampaign;
use App\Models\Setting;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Throwable;

/**
 * Local sender — uses Laravel's Mail facade synchronously.
 *
 * Phase 2: only `sendTest()` is implemented. The driver intentionally does NOT
 * iterate the subscriber table or accept bulk delivery, even though the
 * interface declares `delegateBulk()`. AgaresCMS does not assume queue workers
 * or cron exist on the host server.
 *
 * `newsletter_local_send_limit` is reserved for a Phase-3 fallback ("send to
 * <=N internal recipients in a tight loop"); it is not used in Phase 2.
 */
class LocalNewsletterSender implements NewsletterSenderInterface
{
    public function driver(): string
    {
        return 'local';
    }

    public function isEnabled(): bool
    {
        return true;
    }

    public function supportsBulk(): bool
    {
        return false;
    }

    public function sendTest(NewsletterCampaign $campaign, string $recipient): array
    {
        if (!filter_var($recipient, FILTER_VALIDATE_EMAIL)) {
            return [
                'success' => false,
                'message' => __('The test recipient email is not valid.'),
            ];
        }

        if (trim((string) $campaign->subject) === '' || trim((string) $campaign->body) === '') {
            return [
                'success' => false,
                'message' => __('The campaign needs a subject and a body before a test email can be sent.'),
            ];
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

            return [
                'success' => true,
                'message' => __('Test newsletter sent to :email.', ['email' => $recipient]),
            ];
        } catch (Throwable $e) {
            Log::error('Newsletter test send failed: ' . $e->getMessage(), [
                'campaign_id' => $campaign->id,
                'recipient'   => $recipient,
                'exception'   => $e,
            ]);

            return [
                'success' => false,
                'message' => __('Failed to send test newsletter: :error', ['error' => $e->getMessage()]),
            ];
        }
    }

    public function delegateBulk(NewsletterCampaign $campaign): array
    {
        // Bulk delivery is intentionally unsupported by the local driver.
        // Switch the `newsletter_sending_driver` setting to `external_api` to delegate to Agares SaaS.
        return [
            'success' => false,
            'message' => __('Local sender does not support bulk delivery. Switch the newsletter driver to "external_api" to delegate campaigns to Agares SaaS.'),
        ];
    }
}
