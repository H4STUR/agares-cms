<?php

namespace App\Services\Newsletter;

use App\Models\Setting;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Thin HTTP client over the Agares SaaS newsletter API.
 *
 * Phase 3 placeholder paths (`/api/newsletter/...`) were finalized in Phase 4.3 to
 * the real SaaS contract under `/api/v1/services/newsletter/...`. The user-pasted
 * base URL is normalized so both `https://saas` and `https://saas/api` work
 * (no `/api/api`, no missing `/api`).
 *
 * SaaS contract:
 *   GET  {base}/api/v1/services/newsletter/health
 *   POST {base}/api/v1/services/newsletter/campaigns
 *   GET  {base}/api/v1/services/newsletter/campaigns/{externalCampaignId}
 *   POST {base}/api/v1/services/newsletter/campaigns/{externalCampaignId}/cancel
 */
class AgaresNewsletterApiClient
{
    private const HEALTH_TIMEOUT       = 8;
    private const DELEGATE_TIMEOUT     = 30;
    private const SYNC_TIMEOUT         = 15;
    private const CANCEL_TIMEOUT       = 15;
    private const SUPPRESSION_TIMEOUT  = 10;

    private const API_PREFIX = '/api/v1/services/newsletter';

    public function isConfigured(): bool
    {
        return filled($this->baseUrl()) && filled($this->apiKey());
    }

    /**
     * Normalized SaaS base URL — never ends with `/`, never includes `/api`.
     * Accepts the user pasting either form.
     */
    public function baseUrl(): string
    {
        $raw = trim((string) Setting::str('newsletter_external_api_url', ''));
        if ($raw === '') {
            return '';
        }

        // strip trailing slashes
        $url = rtrim($raw, '/');

        // user pasted `…/api` — drop it so we don't end up with `/api/api/v1/...`
        if (preg_match('#/api$#i', $url)) {
            $url = preg_replace('#/api$#i', '', $url);
        }

        return rtrim($url, '/');
    }

    public function projectId(): string
    {
        return (string) Setting::str('newsletter_external_project_id', '');
    }

    private function endpoint(string $path): string
    {
        return $this->baseUrl() . self::API_PREFIX . $path;
    }

    /**
     * Verify connectivity + key validity. Surfaces tenant + limits info if the SaaS returns it.
     *
     * @return array{ok: bool, message: string, http_status?: int, tenant?: array, newsletter_enabled?: bool, limits?: array}
     */
    public function testConnection(): array
    {
        if (!$this->isConfigured()) {
            return ['ok' => false, 'message' => __('External API URL or API key is not configured.')];
        }

        try {
            $resp = $this->client(self::HEALTH_TIMEOUT)
                ->get($this->endpoint('/health'));

            if ($resp->successful()) {
                $data    = $resp->json();
                $enabled = (bool) data_get($data, 'newsletter_enabled', false);

                $msg = $enabled
                    ? __('Connection OK. Newsletter service is enabled.')
                    : __('Connected, but the newsletter service is NOT enabled for this tenant on the SaaS.');

                return array_filter([
                    'ok'                 => $enabled,
                    'message'            => $msg,
                    'http_status'        => $resp->status(),
                    'tenant'             => data_get($data, 'tenant'),
                    'newsletter_enabled' => $enabled,
                    'limits'             => data_get($data, 'limits'),
                ], fn ($v) => $v !== null);
            }

            return [
                'ok'          => false,
                'message'     => $this->extractError($resp),
                'http_status' => $resp->status(),
            ];
        } catch (Throwable $e) {
            Log::warning('AgaresNewsletterApiClient testConnection failed', [
                'error' => $e->getMessage(),
            ]);
            return [
                'ok'      => false,
                'message' => __('Could not reach external newsletter API: :error', ['error' => $e->getMessage()]),
            ];
        }
    }

    /**
     * Hand a campaign payload to the SaaS.
     *
     * @return array{
     *   ok: bool, message: string, external_campaign_id?: string, external_status?: ?string,
     *   requested_recipient_count?: int, accepted_recipient_count?: int, skipped_count?: int
     * }
     */
    public function sendCampaign(array $payload): array
    {
        if (!$this->isConfigured()) {
            return ['ok' => false, 'message' => __('External API is not configured.')];
        }

        try {
            $resp = $this->client(self::DELEGATE_TIMEOUT)
                ->post($this->endpoint('/campaigns'), $payload);

            if (!$resp->successful()) {
                return ['ok' => false, 'message' => $this->extractError($resp)];
            }

            $data = $resp->json() ?? [];

            $id = data_get($data, 'external_campaign_id')
                ?? data_get($data, 'data.external_campaign_id')
                ?? data_get($data, 'id')
                ?? data_get($data, 'data.id');

            if (blank($id)) {
                return ['ok' => false, 'message' => __('External API response missing campaign ID.')];
            }

            return [
                'ok'                        => true,
                'message'                   => __('Campaign delegated to the external newsletter API.'),
                'external_campaign_id'      => (string) $id,
                'external_status'           => data_get($data, 'status'),
                'requested_recipient_count' => (int) (data_get($data, 'requested_recipient_count') ?? 0),
                'accepted_recipient_count'  => (int) (data_get($data, 'accepted_recipient_count') ?? 0),
                'skipped_count'             => (int) (data_get($data, 'skipped_count') ?? 0),
            ];
        } catch (Throwable $e) {
            Log::warning('AgaresNewsletterApiClient sendCampaign failed', [
                'error' => $e->getMessage(),
            ]);
            return [
                'ok'      => false,
                'message' => __('Could not delegate campaign: :error', ['error' => $e->getMessage()]),
            ];
        }
    }

    /**
     * Pull current campaign status from the SaaS.
     *
     * @return array{
     *   ok: bool, message: string,
     *   status?: ?string,
     *   sent?: ?int, failed?: ?int, skipped?: ?int, accepted?: ?int,
     *   opens?: ?int, clicks?: ?int,
     *   queued_at?: ?string, sending_started_at?: ?string, sent_at?: ?string, cancelled_at?: ?string, failed_at?: ?string
     * }
     */
    public function getCampaignStatus(string $externalCampaignId): array
    {
        if (!$this->isConfigured()) {
            return ['ok' => false, 'message' => __('External API is not configured.')];
        }

        if ($externalCampaignId === '') {
            return ['ok' => false, 'message' => __('Campaign has not been delegated yet.')];
        }

        try {
            $resp = $this->client(self::SYNC_TIMEOUT)
                ->get($this->endpoint('/campaigns/' . urlencode($externalCampaignId)));

            if (!$resp->successful()) {
                return ['ok' => false, 'message' => $this->extractError($resp), 'http_status' => $resp->status()];
            }

            $data = $resp->json() ?? [];
            $node = data_get($data, 'data', $data);

            return [
                'ok'                  => true,
                'message'             => __('Status synced.'),
                'status'              => isset($node['status']) ? (string) $node['status'] : null,
                'sent'                => isset($node['sent_count'])     ? (int) $node['sent_count']     : null,
                'failed'              => isset($node['failed_count'])   ? (int) $node['failed_count']   : null,
                'skipped'             => isset($node['skipped_count'])  ? (int) $node['skipped_count']  : null,
                'accepted'            => isset($node['accepted_recipient_count']) ? (int) $node['accepted_recipient_count'] : null,
                'opens'               => $this->firstInt($node, ['opened_count', 'open_count']),
                'clicks'              => $this->firstInt($node, ['clicked_count', 'click_count']),
                'queued_at'           => $node['queued_at']           ?? null,
                'sending_started_at'  => $node['sending_started_at']  ?? null,
                'sent_at'             => $node['sent_at']             ?? null,
                'cancelled_at'        => $node['cancelled_at']        ?? null,
                'failed_at'           => $node['failed_at']           ?? null,
            ];
        } catch (Throwable $e) {
            Log::warning('AgaresNewsletterApiClient getCampaignStatus failed', [
                'external_campaign_id' => $externalCampaignId,
                'error'                => $e->getMessage(),
            ]);
            return [
                'ok'      => false,
                'message' => __('Could not sync status: :error', ['error' => $e->getMessage()]),
            ];
        }
    }

    /**
     * Cancel a delegated campaign on the SaaS.
     *
     * @return array{ok: bool, message: string, status?: ?string, http_status?: int}
     */
    public function cancelCampaign(string $externalCampaignId): array
    {
        if (!$this->isConfigured()) {
            return ['ok' => false, 'message' => __('External API is not configured.')];
        }

        if ($externalCampaignId === '') {
            return ['ok' => false, 'message' => __('Campaign has not been delegated yet.')];
        }

        try {
            $resp = $this->client(self::CANCEL_TIMEOUT)
                ->post($this->endpoint('/campaigns/' . urlencode($externalCampaignId) . '/cancel'));

            if (!$resp->successful()) {
                return [
                    'ok'          => false,
                    'message'     => $this->extractError($resp),
                    'http_status' => $resp->status(),
                ];
            }

            $data = $resp->json() ?? [];

            return [
                'ok'      => true,
                'message' => __('Campaign cancelled on the external API.'),
                'status'  => data_get($data, 'status'),
            ];
        } catch (Throwable $e) {
            Log::warning('AgaresNewsletterApiClient cancelCampaign failed', [
                'external_campaign_id' => $externalCampaignId,
                'error'                => $e->getMessage(),
            ]);
            return [
                'ok'      => false,
                'message' => __('Could not cancel external campaign: :error', ['error' => $e->getMessage()]),
            ];
        }
    }

    /**
     * Push a suppression entry to the SaaS so future campaigns skip this email.
     * Failures are returned to the caller — they must NEVER block local CMS state.
     *
     * @return array{ok: bool, message: string, created?: bool, http_status?: int}
     */
    public function syncSuppression(
        string $email,
        string $reason = 'unsubscribed',
        ?string $sourceSubscriberId = null,
        ?array $metadata = null,
    ): array {
        if (!$this->isConfigured()) {
            return ['ok' => false, 'message' => __('External API is not configured.')];
        }

        try {
            $resp = $this->client(self::SUPPRESSION_TIMEOUT)
                ->post($this->endpoint('/suppressions'), array_filter([
                    'email'                => $email,
                    'reason'               => $reason,
                    'source'               => 'agares_cms',
                    'source_subscriber_id' => $sourceSubscriberId,
                    'metadata'             => $metadata,
                ], fn ($v) => $v !== null));

            if (!$resp->successful()) {
                return [
                    'ok'          => false,
                    'message'     => $this->extractError($resp),
                    'http_status' => $resp->status(),
                ];
            }

            $data = $resp->json() ?? [];

            return [
                'ok'          => true,
                'message'     => __('Suppression synced.'),
                'created'     => (bool) data_get($data, 'created', false),
                'http_status' => $resp->status(),
            ];
        } catch (Throwable $e) {
            Log::warning('AgaresNewsletterApiClient syncSuppression failed', [
                'error' => $e->getMessage(),
            ]);
            return [
                'ok'      => false,
                'message' => __('Could not sync suppression: :error', ['error' => $e->getMessage()]),
            ];
        }
    }

    /* ---------- internals ---------- */

    private function apiKey(): string
    {
        return (string) Setting::str('newsletter_external_api_key', '');
    }

    private function client(int $timeout): \Illuminate\Http\Client\PendingRequest
    {
        return Http::timeout($timeout)
            ->acceptJson()
            ->asJson()
            ->withToken($this->apiKey())
            ->withHeaders(array_filter([
                'X-Agares-Project' => $this->projectId() ?: null,
                'X-Agares-Source'  => 'agares_cms',
            ]));
    }

    private function firstInt(array $node, array $keys): ?int
    {
        foreach ($keys as $k) {
            if (isset($node[$k]) && is_numeric($node[$k])) {
                return (int) $node[$k];
            }
        }
        return null;
    }

    /**
     * Translate failed responses into a friendly admin-facing message.
     * Never echoes the raw exception or stack trace.
     */
    private function extractError(Response $resp): string
    {
        $status = $resp->status();
        $json   = $resp->json();

        $saasMessage = data_get($json, 'message')
            ?? data_get($json, 'error')
            ?? data_get($json, 'errors.0');

        return match (true) {
            $status === 401 => __('Invalid external API key (401 Unauthorized).'),
            $status === 403 => $saasMessage
                ?? __('External API rejected the request — newsletter may be disabled for this tenant (403 Forbidden).'),
            $status === 404 => $saasMessage
                ?? __('External API endpoint or campaign not found (404).'),
            $status === 422 => $saasMessage
                ?? __('External API rejected the payload (422 Unprocessable).'),
            $status === 429 => __('External API rate limit hit (429). Try again shortly.'),
            $status >= 500  => __('External API server error (HTTP :code). Try again later.', ['code' => $status]),
            default         => $saasMessage ?? __('External API returned HTTP :code.', ['code' => $status]),
        };
    }
}
