<?php

namespace App\Services;

use App\Models\Setting;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AgaresSaasService
{
    private const HEALTH_TIMEOUT = 8;
    private const SCAN_TIMEOUT   = 30;
    private const POLL_TIMEOUT   = 15;

    private function baseUrl(): string
    {
        return rtrim(Setting::str('agares_saas_url', ''), '/');
    }

    private function apiKey(): string
    {
        return Setting::str('agares_saas_api_key', '');
    }

    public function isConfigured(): bool
    {
        return filled($this->baseUrl()) && filled($this->apiKey());
    }

    /**
     * Check connectivity and key validity.
     * Returns ['ok' => bool, 'message' => string].
     *
     * Uses POST /scan with no body: a valid key returns 422 (validation error),
     * an invalid key returns 401. Both mean the server is reachable.
     */
    public function health(): array
    {
        if (! $this->isConfigured()) {
            return ['ok' => false, 'message' => 'API key or SaaS URL not configured.'];
        }

        try {
            $resp = Http::timeout(self::HEALTH_TIMEOUT)
                ->withToken($this->apiKey())
                ->post($this->baseUrl() . '/api/v1/services/cookie-scanner/scan', []);

            // 422 = server reachable + key accepted (URL validation failed, expected)
            // 200/201 = also fine
            if ($resp->status() === 422 || $resp->successful()) {
                return ['ok' => true, 'message' => 'Connected'];
            }

            if ($resp->status() === 401) {
                return ['ok' => false, 'message' => 'Invalid API key (401 Unauthorized).'];
            }

            if ($resp->status() === 403) {
                return ['ok' => false, 'message' => 'API key lacks cookie_scanner scope (403 Forbidden).'];
            }

            return ['ok' => false, 'message' => 'SaaS returned HTTP ' . $resp->status()];
        } catch (\Throwable $e) {
            Log::warning('AgaresSaasService health check failed', ['error' => $e->getMessage()]);
            return ['ok' => false, 'message' => 'Could not reach SaaS: ' . $e->getMessage()];
        }
    }

    /**
     * Submit a scan request to Agares SaaS.
     * Returns the SaaS scan ID (string) on success.
     *
     * @throws \RuntimeException with a human-readable message from the SaaS response.
     */
    public function requestScan(string $url): string
    {
        $resp = Http::timeout(self::SCAN_TIMEOUT)
            ->withToken($this->apiKey())
            ->post($this->baseUrl() . '/api/v1/services/cookie-scanner/scan', [
                'url' => $url,
            ]);

        if (! $resp->successful()) {
            throw new \RuntimeException($this->extractError($resp));
        }

        $data = $resp->json();
        $id   = data_get($data, 'data.id') ?? data_get($data, 'id');

        if (blank($id)) {
            throw new \RuntimeException('SaaS response missing scan ID.');
        }

        return (string) $id;
    }

    /**
     * Extract a clean, human-readable error message from a failed HTTP response.
     */
    private function extractError(\Illuminate\Http\Client\Response $resp): string
    {
        $status = $resp->status();
        $json   = $resp->json();

        // Prefer the SaaS message field if present
        $saasMessage = data_get($json, 'message')
            ?? data_get($json, 'error')
            ?? data_get($json, 'errors.url.0'); // 422 validation errors

        return match (true) {
            $status === 401 => 'Invalid API key (401 Unauthorized).',
            $status === 403 => $saasMessage ?? 'Domain not authorised for this API key (403 Forbidden).',
            $status === 422 => $saasMessage ?? 'Invalid scan request (422 Unprocessable).',
            $status === 429 => 'Scanner is at capacity, too many concurrent scans (429). Try again shortly.',
            $status >= 500  => 'Agares SaaS server error (HTTP ' . $status . '). Try again later.',
            default         => $saasMessage ?? 'SaaS returned HTTP ' . $status . '.',
        };
    }

    /**
     * Fetch a scan result from Agares SaaS by its ID.
     * Returns the decoded JSON array, or null if not ready / not found.
     */
    public function fetchScan(string $saasId): ?array
    {
        try {
            $resp = Http::timeout(self::POLL_TIMEOUT)
                ->withToken($this->apiKey())
                ->get($this->baseUrl() . '/api/v1/services/cookie-scanner/scans/' . $saasId);

            if (! $resp->successful()) {
                return null;
            }

            $data = $resp->json();
            // Unwrap { data: {...} } envelope if present
            return data_get($data, 'data') ?? $data;
        } catch (\Throwable $e) {
            Log::warning('AgaresSaasService fetchScan failed', ['saas_id' => $saasId, 'error' => $e->getMessage()]);
            return null;
        }
    }
}
