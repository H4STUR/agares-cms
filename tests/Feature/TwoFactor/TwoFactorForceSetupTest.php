<?php

namespace Tests\Feature\TwoFactor;

use App\Models\Setting;
use App\Models\User;
use App\Services\TwoFactorService;
use Database\Seeders\RolesAndPermissionsSeeder;
use Database\Seeders\SettingsTableSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TwoFactorForceSetupTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);
        $this->seed(SettingsTableSeeder::class);
    }

    private function setSetting(string $key, string $value, string $type = 'boolean'): void
    {
        Setting::updateOrCreate(['key' => $key], ['value' => $value, 'category' => 'security', 'type' => $type]);
        cache()->flush();
    }

    public function test_force_setup_redirects_unenrolled_admin_when_globally_required(): void
    {
        $this->setSetting('2FA_enabled', '1');
        $this->setSetting('2FA_required', '1');

        /** @var User $user */
        $user = User::factory()->create(['email_verified_at' => now()]);
        $user->assignRole('admin');

        $response = $this->actingAs($user)->get('/admin');

        $response->assertRedirect(route('two-factor.setup'));
    }

    public function test_force_setup_redirects_when_user_role_is_in_csv(): void
    {
        $this->setSetting('2FA_enabled', '1');
        $this->setSetting('2FA_required', '0');
        $this->setSetting('2FA_required_for_roles', 'owner,admin', 'string');

        /** @var User $user */
        $user = User::factory()->create(['email_verified_at' => now()]);
        $user->assignRole('admin');

        $response = $this->actingAs($user)->get('/admin');

        $response->assertRedirect(route('two-factor.setup'));
    }

    public function test_force_setup_does_not_apply_to_roles_outside_csv(): void
    {
        $this->setSetting('2FA_enabled', '1');
        $this->setSetting('2FA_required', '0');
        $this->setSetting('2FA_required_for_roles', 'owner', 'string');

        /** @var User $user */
        $user = User::factory()->create(['email_verified_at' => now()]);
        $user->assignRole('admin');

        // Admin is not in the CSV — should not be redirected
        $response = $this->actingAs($user)->get('/2fa/setup');
        $response->assertStatus(200); // they can still opt in voluntarily
    }

    public function test_enrolled_user_is_not_redirected(): void
    {
        $this->setSetting('2FA_enabled', '1');
        $this->setSetting('2FA_required', '1');

        /** @var User $user */
        $user = User::factory()->create(['email_verified_at' => now()]);
        $user->assignRole('admin');

        $service = app(TwoFactorService::class);
        $service->enable($user, $service->generateSecret(), $service->generateRecoveryCodes());

        $response = $this->actingAs($user)->get('/admin');

        // Should not redirect to /2fa/setup — admin dashboard responds normally (200 or its own redirects)
        $response->assertDontSee('two-factor authentication is required');
        $this->assertNotSame(route('two-factor.setup'), $response->headers->get('Location'));
    }

    public function test_force_setup_is_off_when_globally_disabled(): void
    {
        $this->setSetting('2FA_enabled', '0');
        $this->setSetting('2FA_required', '1');

        /** @var User $user */
        $user = User::factory()->create(['email_verified_at' => now()]);
        $user->assignRole('admin');

        $response = $this->actingAs($user)->get('/admin');

        $this->assertNotSame(route('two-factor.setup'), $response->headers->get('Location'));
    }
}
