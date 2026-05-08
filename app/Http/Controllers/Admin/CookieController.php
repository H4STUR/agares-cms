<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CookieScan;
use App\Models\CookieScanCookie;
use App\Models\CookieConsentSetting;
use App\Models\Setting;
use App\Services\AgaresSaasService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CookieController extends Controller
{
    private function domain(): string
    {
        return request()->getHost();
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

        $url    = $request->input('url') ?: $request->getSchemeAndHttpHost();
        $domain = parse_url($url, PHP_URL_HOST) ?: $request->getHost();

        try {
            $saasId = $saas->requestScan($url);
        } catch (\Throwable $e) {
            return response()->json(['error' => 'Failed to submit scan: ' . $e->getMessage()], 500);
        }

        $scan = CookieScan::create([
            'status'       => 'scanning',
            'domain'       => $domain,
            'url'          => $url,
            'scanned_at'   => now(),
            'saas_scan_id' => $saasId,
            'created_by'   => auth()->id(),
        ]);

        return response()->json([
            'scan_id' => $scan->id,
            'status'  => 'scanning',
        ]);
    }

    public function scanProgress(CookieScan $scan, AgaresSaasService $saas)
    {
        if (in_array($scan->status, ['completed', 'failed', 'cancelled'])) {
            return response()->json([
                'scan_id'       => $scan->id,
                'status'        => $scan->status,
                'error_message' => $scan->error_message,
            ]);
        }

        $data = $saas->fetchScan($scan->saas_scan_id);

        if (! $data) {
            return response()->json(['scan_id' => $scan->id, 'status' => $scan->status]);
        }

        $saasStatus = data_get($data, 'status', 'pending');

        if ($saasStatus === 'completed') {
            $this->persistScanResult($scan, $data);
            $scan->refresh();
        } elseif ($saasStatus === 'failed') {
            $scan->update([
                'status'        => 'failed',
                'error_message' => data_get($data, 'error_message', 'Scan failed.'),
            ]);
        }

        return response()->json([
            'scan_id'       => $scan->id,
            'status'        => $scan->status,
            'error_message' => $scan->error_message,
        ]);
    }

    private function persistScanResult(CookieScan $scan, array $data): void
    {
        DB::beginTransaction();
        try {
            $scan->update([
                'status'     => 'completed',
                'url'        => data_get($data, 'url', $scan->url),
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

                'requested_domains'   => data_get($data, 'requested_domains') ?? data_get($data, 'requestedDomains'),
                'third_party_domains' => data_get($data, 'third_party_domains') ?? data_get($data, 'thirdPartyDomains'),
                'ga_detected'         => data_get($data, 'ga_detected') ?? data_get($data, 'gaDetected'),

                'raw_payload' => $data,
            ]);

            foreach (data_get($data, 'cookies', []) as $c) {
                CookieScanCookie::create([
                    'cookie_scan_id'    => $scan->id,
                    'name'              => data_get($c, 'name', ''),
                    'value'             => data_get($c, 'value'),
                    'domain'            => data_get($c, 'domain', ''),
                    'path'              => data_get($c, 'path', '/'),
                    'expires'           => data_get($c, 'expires'),
                    'expires_timestamp' => data_get($c, 'expiresTimestamp') ?? data_get($c, 'expires_timestamp'),
                    'size'              => data_get($c, 'size', 0),
                    'http_only'         => (bool) (data_get($c, 'httpOnly') ?? data_get($c, 'http_only', false)),
                    'secure'            => (bool) data_get($c, 'secure', false),
                    'same_site'         => data_get($c, 'sameSite') ?? data_get($c, 'same_site'),
                    'session'           => (bool) data_get($c, 'session', false),
                    'type'              => data_get($c, 'type', 'functional'),
                    'is_first_party'    => (bool) (data_get($c, 'isFirstParty') ?? data_get($c, 'is_first_party', true)),
                    'description'       => data_get($c, 'description'),
                ]);
            }

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Cookie scan persist failed', ['scan_id' => $scan->id, 'error' => $e->getMessage()]);
            $scan->update(['status' => 'failed', 'error_message' => 'Failed to save scan data.']);
        }
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

    private function upsertSetting(string $key, string $value): void
    {
        Setting::updateOrCreate(['key' => $key], [
            'value'    => $value,
            'category' => 'integrations',
            'type'     => 'string',
        ]);
    }
}
