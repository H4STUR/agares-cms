<?php

namespace Tests\Feature\TwoFactor;

use App\Models\Setting;
use App\Models\User;
use App\Services\TwoFactorService;
use Database\Seeders\RolesAndPermissionsSeeder;
use Database\Seeders\SettingsTableSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PragmaRX\Google2FA\Google2FA;
use Tests\TestCase;

class TwoFactorChallengeTest extends TestCase
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

    private function enrolledUser(string $password = 'password'): array
    {
        /** @var User $user */
        $user = User::factory()->create(['password' => bcrypt($password)]);
        $user->assignRole('admin');
        $user->givePermissionTo('view admin panel');

        $service = app(TwoFactorService::class);
        $secret  = $service->generateSecret();
        $codes   = $service->generateRecoveryCodes();
        $service->enable($user, $secret, $codes);

        return ['user' => $user->fresh(), 'secret' => $secret, 'codes' => $codes];
    }

    public function test_login_with_two_factor_redirects_to_challenge(): void
    {
        ['user' => $user] = $this->enrolledUser();

        $response = $this->post('/login', [
            'input_type' => $user->email,
            'password'   => 'password',
        ]);

        $response->assertRedirect(route('two-factor.challenge'));
        $this->assertGuest();
        $this->assertSame($user->id, session(TwoFactorService::SESSION_LOGIN_USER_ID));
    }

    public function test_valid_code_completes_login(): void
    {
        ['user' => $user, 'secret' => $secret] = $this->enrolledUser();

        $this->withSession([
            TwoFactorService::SESSION_LOGIN_USER_ID  => $user->id,
            TwoFactorService::SESSION_LOGIN_REMEMBER => false,
        ]);

        $code = app(Google2FA::class)->getCurrentOtp($secret);

        $response = $this->post('/2fa/challenge', ['code' => $code]);

        $this->assertAuthenticatedAs($user);
        $response->assertRedirect(route('admin.dashboard', absolute: false));
    }

    public function test_invalid_code_keeps_user_guest(): void
    {
        ['user' => $user] = $this->enrolledUser();

        $this->withSession([
            TwoFactorService::SESSION_LOGIN_USER_ID  => $user->id,
            TwoFactorService::SESSION_LOGIN_REMEMBER => false,
        ]);

        $response = $this->post('/2fa/challenge', ['code' => '000000']);

        $response->assertSessionHasErrors('code');
        $this->assertGuest();
    }

    public function test_recovery_code_completes_login_and_is_consumed(): void
    {
        ['user' => $user, 'codes' => $codes] = $this->enrolledUser();
        $recovery = $codes[0];

        $this->withSession([
            TwoFactorService::SESSION_LOGIN_USER_ID  => $user->id,
            TwoFactorService::SESSION_LOGIN_REMEMBER => false,
        ]);

        $response = $this->post('/2fa/challenge', ['recovery_code' => $recovery]);

        $this->assertAuthenticatedAs($user);
        $response->assertRedirect(route('admin.dashboard', absolute: false));

        $user->refresh();
        $this->assertCount(7, $user->two_factor_recovery_codes);
        $this->assertNotContains($recovery, $user->two_factor_recovery_codes);
    }

    public function test_used_recovery_code_cannot_be_reused(): void
    {
        ['user' => $user, 'codes' => $codes] = $this->enrolledUser();
        $recovery = $codes[0];

        // First use: succeeds
        $this->withSession([
            TwoFactorService::SESSION_LOGIN_USER_ID  => $user->id,
            TwoFactorService::SESSION_LOGIN_REMEMBER => false,
        ])->post('/2fa/challenge', ['recovery_code' => $recovery]);

        $this->post('/logout');

        // Second use: fails
        $this->withSession([
            TwoFactorService::SESSION_LOGIN_USER_ID  => $user->id,
            TwoFactorService::SESSION_LOGIN_REMEMBER => false,
        ]);
        $response = $this->post('/2fa/challenge', ['recovery_code' => $recovery]);

        $response->assertSessionHasErrors('code');
        $this->assertGuest();
    }

    public function test_challenge_without_session_redirects_to_login(): void
    {
        $response = $this->get('/2fa/challenge');
        $response->assertRedirect(route('login'));
    }

    public function test_cancel_clears_session_and_returns_to_login(): void
    {
        ['user' => $user] = $this->enrolledUser();

        $this->withSession([
            TwoFactorService::SESSION_LOGIN_USER_ID => $user->id,
        ]);

        $response = $this->post('/2fa/challenge/cancel');

        $response->assertRedirect(route('login'));
        $this->assertNull(session(TwoFactorService::SESSION_LOGIN_USER_ID));
    }
}
