<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Admin\Newsletter\CampaignController;
use App\Http\Controllers\Controller;
use App\Models\Newsletter\NewsletterCampaign;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

/**
 * Optional Phase-3 webhook endpoint. The Agares SaaS POSTs status updates
 * here so admins don't have to click "Sync" to see counters move.
 *
 * Verification: HMAC-SHA256 over the raw request body using the shared
 * `newsletter_external_webhook_secret`. The signature is sent in the
 * `X-Agares-Signature` header.
 *
 * If the secret isn't configured, the endpoint hard-fails 503 — never
 * silently accept unsigned data.
 */
class NewsletterWebhookController extends Controller
{
    public function handle(Request $request)
    {
        $secret = (string) Setting::str('newsletter_external_webhook_secret', '');
        if ($secret === '') {
            Log::warning('Newsletter webhook hit but no shared secret is configured.');
            return response()->json(['ok' => false, 'message' => 'Webhook not configured.'], 503);
        }

        $payload   = $request->getContent();
        $signature = (string) $request->header('X-Agares-Signature', '');
        $expected  = hash_hmac('sha256', $payload, $secret);

        if ($signature === '' || !hash_equals($expected, $signature)) {
            Log::warning('Newsletter webhook signature mismatch.', [
                'ip' => $request->ip(),
            ]);
            return response()->json(['ok' => false, 'message' => 'Invalid signature.'], 401);
        }

        $data = $request->json()->all();

        $extId = (string) ($data['external_campaign_id'] ?? data_get($data, 'campaign.id') ?? '');
        if ($extId === '') {
            return response()->json(['ok' => false, 'message' => 'Missing external_campaign_id.'], 422);
        }

        $campaign = NewsletterCampaign::where('external_campaign_id', $extId)->first();
        if (!$campaign) {
            // Quietly accept — the SaaS may legitimately notify about old or
            // foreign-project campaigns; logging is enough.
            Log::info('Newsletter webhook received for unknown external_campaign_id.', [
                'external_campaign_id' => $extId,
            ]);
            return response()->json(['ok' => true, 'message' => 'No matching campaign.'], 200);
        }

        $patch = [
            'external_last_synced_at' => now(),
            'external_last_error'     => null,
        ];

        if (isset($data['status'])) {
            $patch['external_status'] = (string) $data['status'];
            $patch['status']          = CampaignController::mapExternalStatus((string) $data['status'], $campaign->status);
        }

        // SaaS Phase 4.2 sends `opened_count` / `clicked_count` + new counts;
        // accept the legacy `open_count` / `click_count` keys too.
        $countMap = [
            'sent_count'               => 'external_sent_count',
            'failed_count'             => 'external_failed_count',
            'skipped_count'            => 'external_skipped_count',
            'accepted_recipient_count' => 'external_accepted_count',
            'requested_recipient_count' => 'external_requested_count',
            'opened_count'             => 'external_open_count',
            'clicked_count'            => 'external_click_count',
            'open_count'               => 'external_open_count',  // legacy alias
            'click_count'              => 'external_click_count', // legacy alias
        ];
        foreach ($countMap as $src => $dst) {
            if (isset($data[$src]) && is_numeric($data[$src])) {
                $patch[$dst] = (int) $data[$src];
            }
        }

        $campaign->forceFill($patch)->save();

        Log::info('Newsletter webhook applied.', [
            'campaign_id'          => $campaign->id,
            'external_campaign_id' => $extId,
            'new_status'           => $campaign->status,
        ]);

        return response()->json(['ok' => true]);
    }
}
