<?php

namespace Tests\Feature\TwoFactor;

use App\Models\SecurityAuditLog;
use App\Models\Setting;
use App\Models\User;
use App\Services\SecurityAuditService;
use App\Services\TwoFactorService;
use Database\Seeders\RolesAndPermissionsSeeder;
use Database\Seeders\SettingsTableSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Socialite\Contracts\User as SocialiteUser;
use Laravel\Socialite\Facades\Socialite;
use Mockery;
use Tests\TestCase;

class TwoFactorOAuthTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);
        $this->seed(SettingsTableSeeder::class);

        Setting::updateOrCreate(['key' => '2FA_enabled'], ['value' => '1', 'category' => 'security', 'type' => 'boolean']);
        cache()->flush();
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    private function fakeSocialiteUser(string $email, string $providerId = '12345'): void
    {
        $abstractUser = Mockery::mock(SocialiteUser::class);
        $abstractUser->shouldReceive('getId')->andReturn($providerId);
        $abstractUser->shouldReceive('getEmail')->andReturn($email);
        $abstractUser->shouldReceive('getName')->andReturn('Test User');
        $abstractUser->shouldReceive('getNickname')->andReturn(null);
        $abstractUser->shouldReceive('getAvatar')->andReturn(null);

        Socialite::shouldReceive('driver->user')->andReturn($abstractUser);
    }

    public function test_oauth_callback_with_enrolled_user_redirects_to_challenge(): void
    {
        $user = User::factory()->create(['email' => 'oauth@example.com']);
        $user->assignRole('admin');

        $service = app(TwoFactorService::class);
        $service->enable($user, $service->generateSecret(), $service->generateRecoveryCodes());

        $this->fakeSocialiteUser('oauth@example.com');

        $response = $this->get('/oauth/google/callback');

        $response->assertRedirect(route('two-factor.challenge'));
        $this->assertGuest();
        $this->assertSame($user->id, session(TwoFactorService::SESSION_LOGIN_USER_ID));
    }

    public function test_oauth_callback_logs_audit_event(): void
    {
        $user = User::factory()->create(['email' => 'oauth@example.com']);
        $user->assignRole('admin');

        $service = app(TwoFactorService::class);
        $service->enable($user, $service->generateSecret(), $service->generateRecoveryCodes());

        // Clear logs from the enable() call above
        SecurityAuditLog::truncate();

        $this->fakeSocialiteUser('oauth@example.com');
        $this->get('/oauth/google/callback');

        $this->assertDatabaseHas('security_audit_log', [
            'user_id' => $user->id,
            'event'   => SecurityAuditService::EVT_2FA_OAUTH_CHALLENGED,
        ]);
    }

    public function test_oauth_callback_without_2fa_logs_in_directly(): void
    {
        $user = User::factory()->create(['email' => 'oauth-no-2fa@example.com']);
        $user->assignRole('admin');
        $user->givePermissionTo('view admin panel');

        $this->fakeSocialiteUser('oauth-no-2fa@example.com');

        $response = $this->get('/oauth/google/callback');

        $this->assertAuthenticatedAs($user);
        $response->assertRedirect(route('admin.dashboard'));
    }

    public function test_oauth_callback_with_2fa_globally_disabled_logs_in_directly(): void
    {
        Setting::updateOrCreate(['key' => '2FA_enabled'], ['value' => '0', 'category' => 'security', 'type' => 'boolean']);
        cache()->flush();

        $user = User::factory()->create(['email' => 'oauth-disabled@example.com']);
        $user->assignRole('admin');
        $user->givePermissionTo('view admin panel');

        $service = app(TwoFactorService::class);
        $service->enable($user, $service->generateSecret(), $service->generateRecoveryCodes());

        $this->fakeSocialiteUser('oauth-disabled@example.com');

        $response = $this->get('/oauth/google/callback');

        // 2FA is off site-wide → should not challenge
        $this->assertAuthenticatedAs($user);
        $response->assertRedirect(route('admin.dashboard'));
    }
}
