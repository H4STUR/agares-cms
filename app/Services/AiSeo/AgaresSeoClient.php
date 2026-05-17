<?php

namespace App\Services\AiSeo;

use App\Models\Setting;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AgaresSeoClient
{
    private const GENERATE_TIMEOUT = 60;

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
     * POST a generation request to the SaaS AI SEO endpoint.
     *
     * @return array{
     *   suggestions: array<string, array<string, mixed>>,
     *   warnings: array<int, string>,
     *   usage: array<string, int>,
     *   model: string
     * }
     *
     * @throws \RuntimeException with a human-readable message and SaaS status carried in the exception code.
     */
    public function generate(array $payload): array
    {
        if (! $this->isConfigured()) {
            throw new \RuntimeException('Agares SaaS is not configured (missing URL or API key).', 500);
        }

        try {
            $resp = Http::timeout(self::GENERATE_TIMEOUT)
                ->withToken($this->apiKey())
                ->acceptJson()
                ->post($this->baseUrl() . '/api/v1/services/ai-seo/generate', $payload);
        } catch (\Throwable $e) {
            Log::warning('AgaresSeoClient: HTTP error', ['error' => $e->getMessage()]);
            throw new \RuntimeException('Could not reach Agares SaaS: ' . $e->getMessage(), 503);
        }

        if (! $resp->successful()) {
            throw new \RuntimeException($this->extractError($resp), $resp->status());
        }

        $data = $resp->json();

        if (! is_array($data) || ! isset($data['suggestions'])) {
            throw new \RuntimeException('SaaS response missing suggestions.', 502);
        }

        return $data;
    }

    private function extractError(Response $resp): string
    {
        $status = $resp->status();
        $json   = $resp->json();

        $saasMessage = data_get($json, 'message')
            ?? data_get($json, 'error')
            ?? data_get($json, 'errors.0')
            ?? data_get($json, 'errors.title.0')
            ?? data_get($json, 'errors.body.0');

        return match (true) {
            $status === 401 => 'Invalid API key (401 Unauthorized).',
            $status === 403 => $saasMessage ?? 'API key lacks ai_seo scope, or service is disabled (403).',
            $status === 422 => $saasMessage ?? 'Invalid AI SEO request (422 Unprocessable).',
            $status === 429 => $saasMessage ?? 'AI SEO quota exceeded for this API key (429).',
            $status === 503 => $saasMessage ?? 'AI SEO model is currently unavailable (503).',
            $status >= 500  => $saasMessage ?? 'Agares SaaS server error (HTTP ' . $status . ').',
            default         => $saasMessage ?? 'SaaS returned HTTP ' . $status . '.',
        };
    }
}
