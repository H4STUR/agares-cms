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
use Illuminate\Support\Facades\Mail;
use PragmaRX\Google2FA\Google2FA;
use Tests\TestCase;

class SecurityAuditLogTest extends TestCase
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

    public function test_enrolment_creates_audit_entry(): void
    {
        $user = User::factory()->create();
        $user->assignRole('admin');

        $service = app(TwoFactorService::class);
        $service->enable($user, $service->generateSecret(), $service->generateRecoveryCodes());

        $this->assertDatabaseHas('security_audit_log', [
            'user_id' => $user->id,
            'event'   => SecurityAuditService::EVT_2FA_ENROLLED,
        ]);
    }

    public function test_self_disable_creates_audit_entry(): void
    {
        $user = User::factory()->create();
        $user->assignRole('admin');

        $service = app(TwoFactorService::class);
        $service->enable($user, $service->generateSecret(), $service->generateRecoveryCodes());
        SecurityAuditLog::truncate();

        $this->actingAs($user);
        $service->disable($user);

        $this->assertDatabaseHas('security_audit_log', [
            'user_id' => $user->id,
            'event'   => SecurityAuditService::EVT_2FA_DISABLED_SELF,
        ]);
    }

    public function test_admin_reset_records_admin_as_actor(): void
    {
        $admin  = User::factory()->create();
        $admin->assignRole('admin');
        $victim = User::factory()->create();
        $victim->assignRole('admin');

        $service = app(TwoFactorService::class);
        $service->enable($victim, $service->generateSecret(), $service->generateRecoveryCodes());
        SecurityAuditLog::truncate();

        $this->actingAs($admin)
            ->post(route('admin.users.two-factor.reset', $victim));

        $this->assertDatabaseHas('security_audit_log', [
            'user_id'  => $victim->id,
            'actor_id' => $admin->id,
            'event'    => SecurityAuditService::EVT_2FA_ADMIN_RESET,
        ]);
    }

    public function test_successful_challenge_is_logged(): void
    {
        $user = User::factory()->create(['password' => bcrypt('password')]);
        $user->assignRole('admin');
        $user->givePermissionTo('view admin panel');

        $service = app(TwoFactorService::class);
        $secret  = $service->generateSecret();
        $service->enable($user, $secret, $service->generateRecoveryCodes());
        SecurityAuditLog::truncate();

        $this->withSession([
            TwoFactorService::SESSION_LOGIN_USER_ID  => $user->id,
            TwoFactorService::SESSION_LOGIN_REMEMBER => false,
        ]);

        $code = app(Google2FA::class)->getCurrentOtp($secret);
        $this->post('/2fa/challenge', ['code' => $code]);

        $this->assertDatabaseHas('security_audit_log', [
            'user_id' => $user->id,
            'event'   => SecurityAuditService::EVT_2FA_CHALLENGE_SUCCEEDED,
        ]);
    }

    public function test_failed_challenge_is_logged(): void
    {
        $user = User::factory()->create(['password' => bcrypt('password')]);
        $user->assignRole('admin');

        $service = app(TwoFactorService::class);
        $service->enable($user, $service->generateSecret(), $service->generateRecoveryCodes());
        SecurityAuditLog::truncate();

        $this->withSession([
            TwoFactorService::SESSION_LOGIN_USER_ID  => $user->id,
            TwoFactorService::SESSION_LOGIN_REMEMBER => false,
        ]);

        $this->post('/2fa/challenge', ['code' => '000000']);

        $this->assertDatabaseHas('security_audit_log', [
            'user_id' => $user->id,
            'event'   => SecurityAuditService::EVT_2FA_CHALLENGE_FAILED,
        ]);
    }

    public function test_recovery_code_use_is_logged(): void
    {
        $user = User::factory()->create();
        $user->assignRole('admin');

        $service = app(TwoFactorService::class);
        $codes   = $service->generateRecoveryCodes();
        $service->enable($user, $service->generateSecret(), $codes);
        SecurityAuditLog::truncate();

        $service->consumeRecoveryCode($user, $codes[0]);

        $this->assertDatabaseHas('security_audit_log', [
            'user_id' => $user->id,
            'event'   => SecurityAuditService::EVT_2FA_RECOVERY_CODE_USED,
        ]);
    }

    public function test_email_code_sent_is_logged(): void
    {
        Mail::fake();

        $user = User::factory()->create();
        $user->assignRole('admin');

        $service = app(TwoFactorService::class);
        $service->issueEmailCode($user);

        $this->assertDatabaseHas('security_audit_log', [
            'user_id' => $user->id,
            'event'   => SecurityAuditService::EVT_2FA_EMAIL_CODE_SENT,
        ]);
    }
}
