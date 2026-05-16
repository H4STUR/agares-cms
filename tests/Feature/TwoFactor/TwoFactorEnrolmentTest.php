<?php

namespace Tests\Feature\TwoFactor;

use App\Models\Setting;
use App\Models\User;
use App\Services\TwoFactorService;
use Database\Seeders\RolesAndPermissionsSeeder;
use Database\Seeders\SettingsTableSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TwoFactorEnrolmentTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);
        $this->seed(SettingsTableSeeder::class);

        // Enable 2FA site-wide for these tests
        Setting::updateOrCreate(['key' => '2FA_enabled'], ['value' => '1', 'category' => 'security', 'type' => 'boolean']);
        cache()->flush();
    }

    public function test_user_can_view_setup_page(): void
    {
        $user = User::factory()->create();
        $user->assignRole('admin');

        $response = $this->actingAs($user)->get('/2fa/setup');

        $response->assertStatus(200);
        $response->assertSee('Set up two-factor authentication');
    }

    public function test_user_without_permission_cannot_setup(): void
    {
        // 'user' role has manage own two factor; let's strip via a role with no perms.
        // Customer role *does* have it — so use an unassigned user with no role.
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/2fa/setup');

        $response->assertStatus(403);
    }

    public function test_user_can_confirm_setup_with_valid_code(): void
    {
        $user = User::factory()->create();
        $user->assignRole('admin');

        /** @var TwoFactorService $service */
        $service = app(TwoFactorService::class);
        $secret  = $service->generateSecret();

        $this->withSession([TwoFactorService::SESSION_PENDING_SECRET => $secret])
            ->actingAs($user);

        $google2fa = app(\PragmaRX\Google2FA\Google2FA::class);
        $code = $google2fa->getCurrentOtp($secret);

        $response = $this->post('/2fa/setup', ['code' => $code]);

        $response->assertRedirect('/2fa/recovery-codes');

        $user->refresh();
        $this->assertTrue($user->hasTwoFactorEnabled());
        $this->assertNotNull($user->two_factor_secret);
        $this->assertNotNull($user->two_factor_confirmed_at);
        $this->assertIsArray($user->two_factor_recovery_codes);
        $this->assertCount(8, $user->two_factor_recovery_codes);
        $this->assertSame('totp', $user->two_factor_method);
    }

    public function test_invalid_code_does_not_enable_two_factor(): void
    {
        $user = User::factory()->create();
        $user->assignRole('admin');

        $service = app(TwoFactorService::class);
        $secret  = $service->generateSecret();

        $this->withSession([TwoFactorService::SESSION_PENDING_SECRET => $secret])
            ->actingAs($user);

        $response = $this->post('/2fa/setup', ['code' => '000000']);

        $response->assertSessionHasErrors('code');
        $user->refresh();
        $this->assertFalse($user->hasTwoFactorEnabled());
    }

    public function test_user_can_disable_with_password(): void
    {
        $user = User::factory()->create(['password' => bcrypt('secret-password')]);
        $user->assignRole('admin');

        $service = app(TwoFactorService::class);
        $service->enable($user, $service->generateSecret(), $service->generateRecoveryCodes());
        $this->assertTrue($user->fresh()->hasTwoFactorEnabled());

        $response = $this->actingAs($user)->delete('/2fa', [
            'password' => 'secret-password',
        ]);

        $response->assertRedirect(route('admin.user.settings', $user));
        $this->assertFalse($user->fresh()->hasTwoFactorEnabled());
    }

    public function test_disable_requires_correct_password(): void
    {
        $user = User::factory()->create(['password' => bcrypt('secret-password')]);
        $user->assignRole('admin');

        $service = app(TwoFactorService::class);
        $service->enable($user, $service->generateSecret(), $service->generateRecoveryCodes());

        $response = $this->actingAs($user)->delete('/2fa', [
            'password' => 'wrong-password',
        ]);

        $response->assertSessionHasErrors('password');
        $this->assertTrue($user->fresh()->hasTwoFactorEnabled());
    }
}
