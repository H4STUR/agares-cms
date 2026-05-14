<?php

namespace App\Http\Controllers\Admin\Newsletter;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use App\Services\Newsletter\AgaresNewsletterApiClient;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

/**
 * Dedicated admin page for newsletter integration / driver settings.
 *
 * Secrets (api key, webhook secret) follow a write-only pattern:
 *  - the form never renders the stored value
 *  - submitting an empty value KEEPS the existing one
 *  - submitting "_clear" wipes it
 *  - the value is never written to logs
 */
class NewsletterSettingsController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('can:view newsletter settings', only: ['index']),
            new Middleware('can:edit newsletter settings', only: ['update']),
            new Middleware('can:test newsletter integration', only: ['testConnection']),
        ];
    }

    private const PLAIN_KEYS = [
        'newsletter_sending_driver',
        'newsletter_external_api_url',
        'newsletter_external_project_id',
        'newsletter_from_name',
        'newsletter_from_email',
        'newsletter_reply_to',
    ];

    private const SECRET_KEYS = [
        'newsletter_external_api_key',
        'newsletter_external_webhook_secret',
    ];

    public function index()
    {
        $values = [];
        foreach (self::PLAIN_KEYS as $key) {
            $values[$key] = (string) Setting::str($key, '');
        }
        foreach (self::SECRET_KEYS as $key) {
            $stored          = (string) Setting::str($key, '');
            $values[$key]    = ''; // never render stored secret
            $values[$key . '__has'] = $stored !== '';
            $values[$key . '__hint'] = $stored !== '' ? $this->mask($stored) : '';
        }

        return view('pages.admin.newsletter.settings.index', [
            'values'         => $values,
            'drivers'        => ['disabled', 'local', 'external_api'],
            'lastTestStatus' => Cache::get('newsletter.last_connection_test'),
        ]);
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'newsletter_sending_driver'           => ['required', 'in:disabled,local,external_api'],
            'newsletter_external_api_url'         => ['nullable', 'url', 'max:500'],
            'newsletter_external_project_id'      => ['nullable', 'string', 'max:255'],
            'newsletter_external_api_key'         => ['nullable', 'string', 'max:500'],
            'newsletter_external_webhook_secret'  => ['nullable', 'string', 'max:500'],
            'newsletter_from_name'                => ['nullable', 'string', 'max:255'],
            'newsletter_from_email'               => ['nullable', 'email:rfc', 'max:255'],
            'newsletter_reply_to'                 => ['nullable', 'email:rfc', 'max:255'],
        ]);

        // Plain keys: write directly.
        foreach (self::PLAIN_KEYS as $key) {
            $this->writeSetting($key, $validated[$key] ?? '');
        }

        // Secret keys: only update if non-empty submitted; "_clear" wipes.
        foreach (self::SECRET_KEYS as $key) {
            $submitted = $request->input($key);
            if ($submitted === '_clear') {
                $this->writeSetting($key, '');
                continue;
            }
            if (!is_string($submitted) || trim($submitted) === '') {
                continue; // keep existing
            }
            $this->writeSetting($key, $submitted);
        }

        // bust the global settings cache used by AppServiceProvider view composer
        Cache::forget('settings.all.kv');

        return redirect()
            ->route('admin.newsletter.settings.index')
            ->with('success', __('Newsletter settings saved.'));
    }

    public function testConnection(AgaresNewsletterApiClient $client)
    {
        $result = $client->testConnection();

        // Persist the latest result so the index page can show it on next load.
        Cache::put('newsletter.last_connection_test', [
            'ok'      => (bool) ($result['ok'] ?? false),
            'message' => (string) ($result['message'] ?? ''),
            'at'      => now()->toIso8601String(),
        ], now()->addDay());

        Log::info('Newsletter integration test connection', [
            'ok'      => $result['ok'] ?? false,
            'status'  => $result['http_status'] ?? null,
        ]);

        return back()->with($result['ok'] ? 'success' : 'error', $result['message']);
    }

    private function writeSetting(string $key, ?string $value): void
    {
        $setting = Setting::firstOrNew(['key' => $key]);
        $setting->value = (string) ($value ?? '');
        if (empty($setting->category)) {
            $setting->category = 'newsletter';
        }
        if (empty($setting->type)) {
            $setting->type = in_array($key, self::SECRET_KEYS, true) ? 'secret' : 'string';
        }
        $setting->save();
    }

    private function mask(string $secret): string
    {
        $len = strlen($secret);
        if ($len <= 4) {
            return str_repeat('•', $len);
        }
        return str_repeat('•', max(8, $len - 4)) . substr($secret, -4);
    }
}
