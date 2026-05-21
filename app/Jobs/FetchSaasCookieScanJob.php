<?php

namespace App\Jobs;

use App\Models\CookieScan;
use App\Models\CookieScanCookie;
use App\Services\AgaresSaasService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class FetchSaasCookieScanJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries   = 1;
    public int $timeout = 180;

    private const MAX_POLLS    = 18;   // 18 × 5s = 90s window
    private const POLL_SLEEP   = 5;

    public function __construct(public CookieScan $scan, public string $url) {}

    public function handle(AgaresSaasService $saas): void
    {
        $this->scan->update(['status' => 'scanning']);

        try {
            $saasId = $saas->requestScan($this->url);
            $this->scan->update(['saas_scan_id' => $saasId]);
        } catch (\Throwable $e) {
            $this->markFailed('Failed to submit scan: ' . $e->getMessage());
            return;
        }

        $data = null;
        for ($i = 0; $i < self::MAX_POLLS; $i++) {
            sleep(self::POLL_SLEEP);

            // Respect cancellation requested by the user mid-poll
            $this->scan->refresh();
            if ($this->scan->status === 'cancelled') {
                return;
            }

            $data = $saas->fetchScan($saasId);

            if (! $data) {
                continue;
            }

            $status = data_get($data, 'status', 'pending');

            if ($status === 'completed') {
                break;
            }

            if ($status === 'failed') {
                $this->markFailed('SaaS scan failed: ' . data_get($data, 'error_message', 'unknown error'));
                return;
            }
        }

        if (! $data || data_get($data, 'status') !== 'completed') {
            $this->markFailed('Scan timed out waiting for SaaS result after ' . (self::MAX_POLLS * self::POLL_SLEEP) . 's.');
            return;
        }

        $this->persist($data);
    }

    private function persist(array $data): void
    {
        DB::beginTransaction();
        try {
            // SaaS may return camelCase (microservice passthrough) or snake_case
            $this->scan->update([
                'status'     => 'completed',
                'url'        => data_get($data, 'url', $this->url),
                'scanned_at' => data_get($data, 'scanned_at') ?? data_get($data, 'scannedAt') ?? now(),

                'total'       => data_get($data, 'stats.total') ?? data_get($data, 'total', 0),
                'first_party' => data_get($data, 'stats.firstParty') ?? data_get($data, 'stats.first_party') ?? data_get($data, 'first_party', 0),
                'third_party' => data_get($data, 'stats.thirdParty') ?? data_get($data, 'stats.third_party') ?? data_get($data, 'third_party', 0),
                'secure'      => data_get($data, 'stats.secure') ?? data_get($data, 'secure', 0),
                'http_only'   => data_get($data, 'stats.httpOnly') ?? data_get($data, 'stats.http_only') ?? data_get($data, 'http_only', 0),

                'essential'  => data_get($data, 'stats.byType.essential') ?? data_get($data, 'essential', 0),
                'functional' => data_get($data, 'stats.byType.functional') ?? data_get($data, 'functional', 0),
                'analytics'  => data_get($data, 'stats.byType.analytics') ?? data_get($data, 'analytics', 0),
                'marketing'  => data_get($data, 'stats.byType.marketing') ?? data_get($data, 'marketing', 0),

                'privacy_score' => data_get($data, 'privacy_analysis.score') ?? data_get($data, 'privacyAnalysis.score') ?? data_get($data, 'privacy_score'),
                'privacy_grade' => data_get($data, 'privacy_analysis.grade') ?? data_get($data, 'privacyAnalysis.grade') ?? data_get($data, 'privacy_grade'),

                'requested_domains'  => data_get($data, 'requested_domains') ?? data_get($data, 'requestedDomains'),
                'third_party_domains'=> data_get($data, 'third_party_domains') ?? data_get($data, 'thirdPartyDomains'),
                'ga_detected'        => data_get($data, 'ga_detected') ?? data_get($data, 'gaDetected'),

                'raw_payload' => $data,
            ]);

            $cookies = data_get($data, 'cookies', []);

            foreach ($cookies as $c) {
                CookieScanCookie::create([
                    'cookie_scan_id'  => $this->scan->id,
                    'name'            => data_get($c, 'name', ''),
                    'value'           => data_get($c, 'value'),
                    'domain'          => data_get($c, 'domain', ''),
                    'path'            => data_get($c, 'path', '/'),
                    'expires'         => data_get($c, 'expires'),
                    'expires_timestamp' => data_get($c, 'expiresTimestamp') ?? data_get($c, 'expires_timestamp'),
                    'size'            => data_get($c, 'size', 0),
                    'http_only'       => (bool) (data_get($c, 'httpOnly') ?? data_get($c, 'http_only', false)),
                    'secure'          => (bool) data_get($c, 'secure', false),
                    'same_site'       => data_get($c, 'sameSite') ?? data_get($c, 'same_site'),
                    'session'         => (bool) data_get($c, 'session', false),
                    'type'            => data_get($c, 'type', 'functional'),
                    'is_first_party'  => (bool) (data_get($c, 'isFirstParty') ?? data_get($c, 'is_first_party', true)),
                    'description'     => data_get($c, 'description'),
                ]);
            }

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            $this->markFailed('Failed to save scan data: ' . $e->getMessage());
            Log::error('FetchSaasCookieScanJob persist failed', ['scan_id' => $this->scan->id, 'error' => $e->getMessage()]);
        }
    }

    private function markFailed(string $message): void
    {
        Log::error('FetchSaasCookieScanJob failed', ['scan_id' => $this->scan->id, 'reason' => $message]);

        $this->scan->update([
            'status'        => 'failed',
            'error_message' => $message,
        ]);
    }
}
