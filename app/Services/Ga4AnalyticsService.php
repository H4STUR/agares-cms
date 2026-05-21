<?php

namespace App\Services;

use Google\Auth\Credentials\ServiceAccountCredentials;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class Ga4AnalyticsService
{
    /**
     * Validate config and return normalized creds path.
     */
    private function configOrError(): array
    {
        $propertyId = (string) config('services.ga4.property_id');
        $credsPath  = (string) config('services.ga4.credentials_path');

        $propertyId = trim($propertyId);
        $credsPath  = trim($credsPath);

        $resolved = null;
        if ($credsPath !== '') {
            $resolved = str_starts_with($credsPath, '/')
                ? $credsPath
                : base_path($credsPath);
        }

        if ($propertyId === '' || !$resolved || !is_file($resolved) || !is_readable($resolved)) {
            return [
                'ok' => false,
                'propertyId' => $propertyId ?: null,
                'credsPath' => $resolved,
                'error' => 'GA4 not configured (missing property ID or readable credentials file).',
            ];
        }

        return [
            'ok' => true,
            'propertyId' => $propertyId,
            'credsPath' => $resolved,
        ];
    }

    /**
     * Fetch OAuth access token for service account JSON.
     */
    private function accessToken(string $credsPath): string
    {
        $scopes = ['https://www.googleapis.com/auth/analytics.readonly'];

        $creds = new ServiceAccountCredentials($scopes, $credsPath);
        $tokenArr = $creds->fetchAuthToken();

        $token = $tokenArr['access_token'] ?? null;

        if (!is_string($token) || trim($token) === '') {
            throw new \RuntimeException('Could not obtain Google access token from service account credentials.');
        }

        return $token;
    }

    /**
     * Call GA4 Data API (v1beta) via JSON REST.
     *
     * $endpoint example: 'runReport' or 'runRealtimeReport'
     */
    private function callGa(string $propertyId, string $token, string $endpoint, array $payload): array
    {
        $url = "https://analyticsdata.googleapis.com/v1beta/properties/{$propertyId}:{$endpoint}";

        $res = Http::withToken($token)
            ->acceptJson()
            ->asJson()
            ->timeout(25)
            ->post($url, $payload);

        if (!$res->successful()) {
            $body = $res->body();
            $body = is_string($body) ? mb_substr($body, 0, 1200) : '';

            throw new \RuntimeException("GA4 API {$endpoint} failed ({$res->status()}): {$body}");
        }

        $json = $res->json();
        if (!is_array($json)) {
            throw new \RuntimeException("GA4 API {$endpoint} returned non-JSON response.");
        }

        return $json;
    }

    private function exceptionPayload(\Throwable $e, string $where): array
    {
        return [
            'ok' => false,
            'where' => $where,
            'error_type' => get_class($e),
            'error' => $e->getMessage(),
        ];
    }

    /**
     * Dashboard tiles summary (7 days).
     * Returns: ok, activeUsers, sessions, pageViews
     */
    public function summaryLast7Days(): array
    {
        $cacheKey = 'ga4.summary.7d';

        $cfg = $this->configOrError();
        if (!$cfg['ok']) {
            Cache::forget($cacheKey);
            return $cfg;
        }

        return Cache::remember($cacheKey, now()->addMinutes(30), function () use ($cfg, $cacheKey) {
            try {
                $token = $this->accessToken($cfg['credsPath']);

                $json = $this->callGa($cfg['propertyId'], $token, 'runReport', [
                    'dateRanges' => [
                        ['startDate' => '7daysAgo', 'endDate' => 'today'],
                    ],
                    'metrics' => [
                        ['name' => 'activeUsers'],
                        ['name' => 'sessions'],
                        ['name' => 'screenPageViews'],
                    ],
                ]);

                $metricValues = $json['rows'][0]['metricValues'] ?? [];

                return [
                    'ok' => true,
                    'activeUsers' => (int) ($metricValues[0]['value'] ?? 0),
                    'sessions'    => (int) ($metricValues[1]['value'] ?? 0),
                    'pageViews'   => (int) ($metricValues[2]['value'] ?? 0),
                ];
            } catch (\Throwable $e) {
                Cache::forget($cacheKey);
                return $this->exceptionPayload($e, 'summaryLast7Days');
            }
        });
    }

    /**
     * Timeline chart (12 months).
     * Returns: ok, labels[], series[{name,data[]}]
     */
    public function trafficTimelineLast12Months(): array
    {
        $cacheKey = 'ga4.timeline.12m';

        $cfg = $this->configOrError();
        if (!$cfg['ok']) {
            Cache::forget($cacheKey);
            return $cfg;
        }

        return Cache::remember($cacheKey, now()->addMinutes(30), function () use ($cfg, $cacheKey) {
            try {
                $token = $this->accessToken($cfg['credsPath']);

                $json = $this->callGa($cfg['propertyId'], $token, 'runReport', [
                    'dateRanges' => [
                        ['startDate' => '365daysAgo', 'endDate' => 'today'],
                    ],
                    'dimensions' => [
                        ['name' => 'yearMonth'],
                    ],
                    'metrics' => [
                        ['name' => 'activeUsers'],
                        ['name' => 'screenPageViews'],
                    ],
                    // optional ordering by yearMonth (usually already sorted, but safe)
                    'orderBys' => [
                        [
                            'dimension' => ['dimensionName' => 'yearMonth'],
                            'desc' => false,
                        ],
                    ],
                ]);

                // Build response lookup: "YYYYMM" => users/views
                $byMonth = [];

                foreach (($json['rows'] ?? []) as $row) {
                    $ym = $row['dimensionValues'][0]['value'] ?? null;
                    if (!is_string($ym) || $ym === '') continue;

                    $m = $row['metricValues'] ?? [];

                    $byMonth[$ym] = [
                        'users' => (int) ($m[0]['value'] ?? 0),
                        'views' => (int) ($m[1]['value'] ?? 0),
                    ];
                }

                // Build exactly 12 months: oldest -> newest
                $labels = [];
                $users  = [];
                $views  = [];

                $now = now()->startOfMonth();

                for ($i = 11; $i >= 0; $i--) {
                    $dt = $now->copy()->subMonths($i);
                    $ym = $dt->format('Ym');      // "202601"
                    $labels[] = $dt->format('M'); // "Jan"

                    $users[] = $byMonth[$ym]['users'] ?? 0;
                    $views[] = $byMonth[$ym]['views'] ?? 0;
                }

                return [
                    'ok' => true,
                    'labels' => $labels,
                    'series' => [
                        ['name' => 'Users', 'data' => $users],
                        ['name' => 'Page views', 'data' => $views],
                    ],
                ];
            } catch (\Throwable $e) {
                Cache::forget($cacheKey);
                return $this->exceptionPayload($e, 'trafficTimelineLast12Months');
            }
        });
    }

    /**
     * Realtime active users (last 30 minutes).
     * Returns: ok, activeUsers, window
     */
    public function realtimeActiveUsers(): array
    {
        $cacheKey = 'ga4.realtime.activeUsers';

        $cfg = $this->configOrError();
        if (!$cfg['ok']) {
            Cache::forget($cacheKey);
            return $cfg;
        }

        return Cache::remember($cacheKey, now()->addSeconds(10), function () use ($cfg, $cacheKey) {
            try {
                $token = $this->accessToken($cfg['credsPath']);

                $json = $this->callGa($cfg['propertyId'], $token, 'runRealtimeReport', [
                    'metrics' => [
                        ['name' => 'activeUsers'],
                    ],
                ]);

                $metricValues = $json['rows'][0]['metricValues'] ?? [];

                return [
                    'ok' => true,
                    'activeUsers' => (int) ($metricValues[0]['value'] ?? 0),
                    'window' => 'last 30 minutes',
                ];
            } catch (\Throwable $e) {
                Cache::forget($cacheKey);
                return $this->exceptionPayload($e, 'realtimeActiveUsers');
            }
        });
    }
}
