<?php

namespace Tests\Feature\TwoFactor;

use App\Models\Setting;
use App\Models\User;
use App\Services\TwoFactorService;
use Database\Seeders\RolesAndPermissionsSeeder;
use Database\Seeders\SettingsTableSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TwoFactorAdminResetTest extends TestCase
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

    public function test_admin_can_reset_another_users_two_factor(): void
    {
        /** @var User $admin */
        $admin = User::factory()->create();
        $admin->assignRole('admin');

        /** @var User $victim */
        $victim = User::factory()->create();
        $victim->assignRole('admin');

        $service = app(TwoFactorService::class);
        $service->enable($victim, $service->generateSecret(), $service->generateRecoveryCodes());
        $this->assertTrue($victim->fresh()->hasTwoFactorEnabled());

        $response = $this->actingAs($admin)
            ->post(route('admin.users.two-factor.reset', $victim));

        $response->assertRedirect();
        $this->assertFalse($victim->fresh()->hasTwoFactorEnabled());
        $this->assertNull($victim->fresh()->two_factor_secret);
        $this->assertNull($victim->fresh()->two_factor_recovery_codes);
        $this->assertNull($victim->fresh()->two_factor_confirmed_at);
    }

    public function test_non_admin_cannot_reset_other_users_two_factor(): void
    {
        /** @var User $moderator */
        $moderator = User::factory()->create();
        $moderator->assignRole('moderator');

        /** @var User $victim */
        $victim = User::factory()->create();
        $victim->assignRole('admin');

        $service = app(TwoFactorService::class);
        $service->enable($victim, $service->generateSecret(), $service->generateRecoveryCodes());

        $response = $this->actingAs($moderator)
            ->post(route('admin.users.two-factor.reset', $victim));

        $response->assertStatus(403);
        $this->assertTrue($victim->fresh()->hasTwoFactorEnabled());
    }
}
