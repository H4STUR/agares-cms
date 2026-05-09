<?php

namespace Tests\Feature;

use App\Models\CookieConsentSetting;
use App\Models\CookieScan;
use App\Models\CookieScanCookie;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CookieCatalogTest extends TestCase
{
    use RefreshDatabase;

    private string $domain = 'demo.agares.co.uk';

    protected function setUp(): void
    {
        parent::setUp();
        // Point the test request host to match the factory domain so
        // request()->getHost() in the controller returns the same value.
        $this->withServerVariables(['HTTP_HOST' => $this->domain]);
    }

    // ── /api/cookies/consent ──────────────────────────────────────────────

    public function test_consent_returns_disabled_when_no_settings(): void
    {
        $this->getJson('/api/cookies/consent')
            ->assertOk()
            ->assertJson(['enabled' => false]);
    }

    public function test_consent_returns_disabled_when_setting_disabled(): void
    {
        CookieConsentSetting::factory()->disabled()->forDomain($this->domain)->create();

        $this->getJson('/api/cookies/consent')
            ->assertOk()
            ->assertJson(['enabled' => false]);
    }

    public function test_consent_returns_full_config_when_enabled(): void
    {
        CookieConsentSetting::factory()->forDomain($this->domain)->create([
            'title' => 'Cookie notice',
        ]);

        $this->getJson('/api/cookies/consent')
            ->assertOk()
            ->assertJsonFragment(['enabled' => true, 'title' => 'Cookie notice'])
            ->assertJsonStructure(['buttons' => ['accept_all', 'reject_all', 'manage', 'save']])
            ->assertJsonStructure(['categories' => ['essential', 'functional', 'analytics', 'marketing']]);
    }

    // ── /api/cookies/catalog ─────────────────────────────────────────────

    public function test_catalog_returns_empty_when_no_scans(): void
    {
        $this->getJson('/api/cookies/catalog')
            ->assertOk()
            ->assertJson([
                'scanned_at' => null,
                'categories' => [
                    'essential'  => [],
                    'functional' => [],
                    'analytics'  => [],
                    'marketing'  => [],
                ],
            ]);
    }

    public function test_catalog_returns_empty_when_only_pending_scan_exists(): void
    {
        CookieScan::factory()->pending()->forDomain($this->domain)->create();

        $this->getJson('/api/cookies/catalog')
            ->assertOk()
            ->assertJson(['categories' => ['functional' => []]]);
    }

    public function test_catalog_returns_empty_when_only_scanning_scan_exists(): void
    {
        // This was the original bug: a scanning scan has scanned_at=now() set at
        // creation, so without a status filter it sorts before older completed scans.
        CookieScan::factory()->scanning()->forDomain($this->domain)->create();

        $this->getJson('/api/cookies/catalog')
            ->assertOk()
            ->assertJson(['categories' => ['functional' => []]]);
    }

    public function test_catalog_returns_cookies_from_latest_completed_scan(): void
    {
        $scan = CookieScan::factory()->completed()->forDomain($this->domain)->create([
            'scanned_at' => now()->subHour(),
        ]);

        CookieScanCookie::factory()->functional()->create([
            'cookie_scan_id' => $scan->id,
            'name'           => 'agares_session',
            'domain'         => 'www.' . $this->domain,
            'description'    => 'Session management',
        ]);

        CookieScanCookie::factory()->functional()->create([
            'cookie_scan_id' => $scan->id,
            'name'           => 'XSRF-TOKEN',
            'domain'         => 'www.' . $this->domain,
            'description'    => 'CSRF protection',
        ]);

        $response = $this->getJson('/api/cookies/catalog')->assertOk();

        $functional = $response->json('categories.functional');
        $this->assertCount(2, $functional);

        $names = array_column($functional, 'name');
        $this->assertContains('agares_session', $names);
        $this->assertContains('XSRF-TOKEN', $names);
    }

    public function test_catalog_ignores_newer_scanning_scan_and_uses_completed(): void
    {
        // Completed scan 1 hour ago with cookies
        $completed = CookieScan::factory()->completed()->forDomain($this->domain)->create([
            'scanned_at' => now()->subHour(),
        ]);
        CookieScanCookie::factory()->functional()->create([
            'cookie_scan_id' => $completed->id,
            'name'           => 'agares_session',
        ]);

        // Newer scan that is still in progress (no cookies yet) — this caused the bug
        CookieScan::factory()->scanning()->forDomain($this->domain)->create([
            'scanned_at' => now(),
        ]);

        $response = $this->getJson('/api/cookies/catalog')->assertOk();

        $functional = $response->json('categories.functional');
        $this->assertCount(1, $functional);
        $this->assertEquals('agares_session', $functional[0]['name']);
    }

    public function test_catalog_groups_cookies_by_type(): void
    {
        $scan = CookieScan::factory()->completed()->forDomain($this->domain)->create();

        CookieScanCookie::factory()->essential()->create(['cookie_scan_id' => $scan->id, 'name' => 'c_essential']);
        CookieScanCookie::factory()->functional()->create(['cookie_scan_id' => $scan->id, 'name' => 'c_functional']);
        CookieScanCookie::factory()->analytics()->create(['cookie_scan_id' => $scan->id, 'name' => 'c_analytics']);
        CookieScanCookie::factory()->marketing()->create(['cookie_scan_id' => $scan->id, 'name' => 'c_marketing']);

        $response = $this->getJson('/api/cookies/catalog')->assertOk();

        $this->assertCount(1, $response->json('categories.essential'));
        $this->assertCount(1, $response->json('categories.functional'));
        $this->assertCount(1, $response->json('categories.analytics'));
        $this->assertCount(1, $response->json('categories.marketing'));

        $this->assertEquals('c_essential',  $response->json('categories.essential.0.name'));
        $this->assertEquals('c_functional', $response->json('categories.functional.0.name'));
        $this->assertEquals('c_analytics',  $response->json('categories.analytics.0.name'));
        $this->assertEquals('c_marketing',  $response->json('categories.marketing.0.name'));
    }

    public function test_catalog_uses_most_recent_completed_scan(): void
    {
        $older = CookieScan::factory()->completed()->forDomain($this->domain)->create([
            'scanned_at' => now()->subDay(),
        ]);
        CookieScanCookie::factory()->functional()->create([
            'cookie_scan_id' => $older->id,
            'name'           => 'old_cookie',
        ]);

        $newer = CookieScan::factory()->completed()->forDomain($this->domain)->create([
            'scanned_at' => now()->subMinutes(10),
        ]);
        CookieScanCookie::factory()->functional()->create([
            'cookie_scan_id' => $newer->id,
            'name'           => 'new_cookie',
        ]);

        $response = $this->getJson('/api/cookies/catalog')->assertOk();
        $this->assertEquals('new_cookie', $response->json('categories.functional.0.name'));
    }

    public function test_catalog_returns_cookie_fields(): void
    {
        $scan = CookieScan::factory()->completed()->forDomain($this->domain)->create();

        CookieScanCookie::factory()->functional()->create([
            'cookie_scan_id' => $scan->id,
            'name'           => 'my_cookie',
            'domain'         => 'example.com',
            'description'    => 'A test cookie',
        ]);

        $cookie = $this->getJson('/api/cookies/catalog')
            ->assertOk()
            ->json('categories.functional.0');

        $this->assertEquals('my_cookie',    $cookie['name']);
        $this->assertEquals('example.com',  $cookie['domain']);
        $this->assertEquals('A test cookie', $cookie['description']);
    }
}
