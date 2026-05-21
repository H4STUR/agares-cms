<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Jobs\FetchSaasCookieScanJob;
use App\Models\CookieScan;
use App\Models\CookieScanCookie;
use App\Models\CookieConsentSetting;
use App\Models\Setting;
use App\Services\AgaresSaasService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\DB;

class CookieController extends Controller
{
    private function domain(): string
    {
        return parse_url(config('app.url'), PHP_URL_HOST) ?: 'unknown-domain';
    }

    public function index()
    {
        $domain       = $this->domain();
        $lastScan     = CookieScan::where('domain', $domain)
                            ->whereIn('status', ['completed'])
                            ->latest('scanned_at')
                            ->first();
        $pendingScan  = CookieScan::where('domain', $domain)
                            ->whereIn('status', ['pending', 'scanning'])
                            ->latest()
                            ->first();
        $cookieSettings = CookieConsentSetting::firstOrCreate(['domain' => $domain]);

        $saasConfigured = filled(Setting::str('agares_saas_api_key'))
                       && filled(Setting::str('agares_saas_url'));

        return view('pages.admin.cookies.index', compact(
            'domain', 'lastScan', 'pendingScan', 'cookieSettings', 'saasConfigured'
        ));
    }

    public function scans()
    {
        $domain = $this->domain();

        $scans = CookieScan::where('domain', $domain)
            ->latest('created_at')
            ->paginate(20);

        return view('pages.admin.cookies.scans', compact('domain', 'scans'));
    }

    public function showScan(CookieScan $scan)
    {
        $scan->load(['cookies' => function ($q) {
            $q->orderBy('type')->orderBy('is_first_party', 'desc')->orderBy('name');
        }]);

        return view('pages.admin.cookies.scan-show', compact('scan'));
    }

    public function editSettings()
    {
        $domain = $this->domain();
        $cookieSettings = CookieConsentSetting::firstOrCreate(['domain' => $domain]);

        return view('pages.admin.cookies.settings', compact('domain', 'cookieSettings'));
    }

    public function updateSettings(Request $request)
    {
        $domain = $this->domain();

        $validated = $request->validate([
            'enabled' => 'nullable|boolean',
            'block_until_choice' => 'nullable|boolean',
            'remember_consent' => 'nullable|boolean',

            'title' => 'required|string|max:255',
            'message' => 'nullable|string',

            'btn_accept_all' => 'required|string|max:50',
            'btn_reject_all' => 'required|string|max:50',
            'btn_manage' => 'required|string|max:50',
            'btn_save' => 'required|string|max:50',

            'allow_functional' => 'nullable|boolean',
            'allow_analytics' => 'nullable|boolean',
            'allow_marketing' => 'nullable|boolean',

            'desc_essential' => 'nullable|string',
            'desc_functional' => 'nullable|string',
            'desc_analytics' => 'nullable|string',
            'desc_marketing' => 'nullable|string',
        ]);

        $cookieSettings = CookieConsentSetting::firstOrCreate(['domain' => $domain]);

        $validated['enabled']           = (bool)($request->boolean('enabled'));
        $validated['block_until_choice']= (bool)($request->boolean('block_until_choice'));
        $validated['remember_consent']  = (bool)($request->boolean('remember_consent'));
        $validated['allow_essential']   = true;
        $validated['allow_functional']  = (bool)($request->boolean('allow_functional'));
        $validated['allow_analytics']   = (bool)($request->boolean('allow_analytics'));
        $validated['allow_marketing']   = (bool)($request->boolean('allow_marketing'));

        $cookieSettings->update($validated);

        return back()->with('success', __('Saved.'));
    }

    // ── Agares SaaS integration ──────────────────────────────────────────────

    public function saveSaasSettings(Request $request)
    {
        $keyAlreadySet = filled(Setting::str('agares_saas_api_key'));

        $request->validate([
            'agares_saas_url'     => 'required|url|max:255',
            'agares_saas_api_key' => ($keyAlreadySet ? 'nullable' : 'required') . '|string|max:255',
        ]);

        $this->upsertSetting('agares_saas_url', $request->input('agares_saas_url'));

        if (filled($request->input('agares_saas_api_key'))) {
            $this->upsertSetting('agares_saas_api_key', $request->input('agares_saas_api_key'));
        }

        return back()->with('success', __('SaaS settings saved.'));
    }

    public function checkConnection(AgaresSaasService $saas)
    {
        return response()->json($saas->health());
    }

    public function scanAsync(Request $request, AgaresSaasService $saas)
    {
        if (! $saas->isConfigured()) {
            return response()->json(['error' => 'Agares SaaS not configured.'], 422);
        }

        $url    = $request->input('url') ?: config('app.url');
        $domain = parse_url($url, PHP_URL_HOST)
               ?: parse_url(config('app.url'), PHP_URL_HOST)
               ?: 'unknown-domain';

        $scan = CookieScan::create([
            'status'     => 'pending',
            'domain'     => $domain,
            'url'        => $url,
            'scanned_at' => now(),
            'created_by' => auth()->id(),
        ]);

        FetchSaasCookieScanJob::dispatch($scan, $url);

        return response()->json([
            'scan_id' => $scan->id,
            'status'  => 'pending',
        ]);
    }

    public function scanProgress(CookieScan $scan)
    {
        return response()->json([
            'scan_id'       => $scan->id,
            'status'        => $scan->status,
            'error_message' => $scan->error_message,
        ]);
    }

    public function cancelScan(CookieScan $scan)
    {
        if (! $scan->isCancellable()) {
            return response()->json(['error' => 'Scan cannot be cancelled in its current state.'], 422);
        }

        $scan->update([
            'status'        => 'cancelled',
            'error_message' => 'Cancelled by user.',
        ]);

        return response()->json(['status' => 'cancelled']);
    }

    // ── Legacy direct scan (kept as fallback when SaaS not configured) ───────

    public function scan(Request $request)
    {
        $url    = $request->input('url') ?: config('app.url');
        $domain = parse_url($url, PHP_URL_HOST) ?: parse_url(config('app.url'), PHP_URL_HOST) ?: 'unknown-domain';

        $api  = rtrim(config('services.cookie_scanner.base', 'https://cookie-scanner.fly.dev'), '/');

        DB::beginTransaction();
        try {
            $resp = Http::timeout(120)->post($api . '/api/scan', ['url' => $url]);

            if (!$resp->ok()) {
                throw new \RuntimeException('Scanner API error: ' . $resp->status() . ' ' . $resp->body());
            }

            $data = $resp->json();

            $scan = CookieScan::create([
                'status'      => 'completed',
                'domain'      => $domain,
                'url'         => $data['url'] ?? $url,
                'scanned_at'  => $data['scannedAt'] ?? now(),

                'total'       => data_get($data, 'stats.total', 0),
                'first_party' => data_get($data, 'stats.firstParty', 0),
                'third_party' => data_get($data, 'stats.thirdParty', 0),
                'secure'      => data_get($data, 'stats.secure', 0),
                'http_only'   => data_get($data, 'stats.httpOnly', 0),

                'essential'  => data_get($data, 'stats.byType.essential', 0),
                'functional' => data_get($data, 'stats.byType.functional', 0),
                'analytics'  => data_get($data, 'stats.byType.analytics', 0),
                'marketing'  => data_get($data, 'stats.byType.marketing', 0),

                'privacy_score' => data_get($data, 'privacyAnalysis.score'),
                'privacy_grade' => data_get($data, 'privacyAnalysis.grade'),

                'requested_domains'  => $data['requestedDomains'] ?? null,
                'third_party_domains'=> $data['thirdPartyDomains'] ?? null,
                'ga_detected'        => $data['gaDetected'] ?? null,
                'raw_payload'        => $data,
                'created_by'         => auth()->id(),
            ]);

            foreach ($data['cookies'] ?? [] as $c) {
                CookieScanCookie::create([
                    'cookie_scan_id'  => $scan->id,
                    'name'            => $c['name'] ?? '',
                    'value'           => $c['value'] ?? null,
                    'domain'          => $c['domain'] ?? '',
                    'path'            => $c['path'] ?? '/',
                    'expires'         => $c['expires'] ?? null,
                    'expires_timestamp' => $c['expiresTimestamp'] ?? null,
                    'size'            => $c['size'] ?? 0,
                    'http_only'       => (bool)($c['httpOnly'] ?? false),
                    'secure'          => (bool)($c['secure'] ?? false),
                    'same_site'       => $c['sameSite'] ?? null,
                    'session'         => (bool)($c['session'] ?? false),
                    'type'            => $c['type'] ?? 'functional',
                    'is_first_party'  => (bool)($c['isFirstParty'] ?? true),
                    'description'     => $c['description'] ?? null,
                ]);
            }

            DB::commit();
            return redirect()->route('admin.cookies.scans.show', $scan)->with('success', __('Scan saved.'));
        } catch (\Throwable $e) {
            DB::rollBack();
            return back()->with('error', $e->getMessage());
        }
    }

    // ─────────────────────────────────────────────────────────────────────────

    private function upsertSetting(string $key, string $value): void
    {
        Setting::updateOrCreate(['key' => $key], [
            'value'    => $value,
            'category' => 'integrations',
            'type'     => 'string',
        ]);
    }
}
