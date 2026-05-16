<?php

namespace Tests\Feature\TwoFactor;

use App\Mail\Auth\TwoFactorChallengeMail;
use App\Models\Setting;
use App\Models\User;
use App\Services\TwoFactorService;
use Database\Seeders\RolesAndPermissionsSeeder;
use Database\Seeders\SettingsTableSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class TwoFactorEmailEnrolmentTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);
        $this->seed(SettingsTableSeeder::class);

        $this->setSetting('2FA_enabled', '1', 'boolean');
        $this->setSetting('2FA_method', 'both', 'string');
        cache()->flush();
    }

    private function setSetting(string $key, string $value, string $type = 'boolean'): void
    {
        Setting::updateOrCreate(['key' => $key], ['value' => $value, 'category' => 'security', 'type' => $type]);
        cache()->flush();
    }

    public function test_setup_page_shows_method_picker_when_both_allowed(): void
    {
        $user = User::factory()->create();
        $user->assignRole('admin');

        $response = $this->actingAs($user)->get('/2fa/setup');

        $response->assertStatus(200);
        $response->assertSee('Authenticator app');
        $response->assertSee('Email');
    }

    public function test_setup_picks_totp_directly_when_only_totp_allowed(): void
    {
        $this->setSetting('2FA_method', 'totp', 'string');

        $user = User::factory()->create();
        $user->assignRole('admin');

        $response = $this->actingAs($user)->get('/2fa/setup');

        $response->assertStatus(200);
        $response->assertSee('Set up authenticator app');
        $response->assertDontSee('Choose a verification method');
    }

    public function test_send_setup_email_dispatches_mail_and_persists_hash(): void
    {
        Mail::fake();

        $user = User::factory()->create();
        $user->assignRole('admin');

        $response = $this->actingAs($user)->post('/2fa/setup/email/send');

        $response->assertRedirect();
        Mail::assertSent(TwoFactorChallengeMail::class, fn ($mail) => $mail->user->is($user));

        $user->refresh();
        $this->assertNotNull($user->two_factor_email_code);
        $this->assertNotNull($user->two_factor_email_code_sent_at);
    }

    public function test_send_setup_email_is_throttled(): void
    {
        Mail::fake();

        $user = User::factory()->create();
        $user->assignRole('admin');

        $this->actingAs($user)->post('/2fa/setup/email/send');
        $response = $this->actingAs($user)->post('/2fa/setup/email/send');

        $response->assertSessionHasErrors('email');
        Mail::assertSent(TwoFactorChallengeMail::class, 1);
    }

    public function test_user_can_confirm_email_setup_with_valid_code(): void
    {
        $user = User::factory()->create();
        $user->assignRole('admin');

        $service = app(TwoFactorService::class);
        $code = $service->issueEmailCode($user);

        $response = $this->actingAs($user)->post('/2fa/setup', [
            'method' => 'email',
            'code'   => $code,
        ]);

        $response->assertRedirect(route('two-factor.recovery-codes'));

        $user->refresh();
        $this->assertTrue($user->hasTwoFactorEnabled());
        $this->assertSame('email', $user->two_factor_method);
        $this->assertNull($user->two_factor_secret); // no secret for email method
        $this->assertNull($user->two_factor_email_code); // cleared after use
        $this->assertIsArray($user->two_factor_recovery_codes);
        $this->assertCount(8, $user->two_factor_recovery_codes);
    }

    public function test_invalid_email_code_does_not_enable_two_factor(): void
    {
        $user = User::factory()->create();
        $user->assignRole('admin');

        $service = app(TwoFactorService::class);
        $service->issueEmailCode($user);

        $response = $this->actingAs($user)->post('/2fa/setup', [
            'method' => 'email',
            'code'   => '000000',
        ]);

        $response->assertSessionHasErrors('code');
        $this->assertFalse($user->fresh()->hasTwoFactorEnabled());
    }

    public function test_expired_email_code_is_rejected(): void
    {
        $user = User::factory()->create();
        $user->assignRole('admin');

        $this->setSetting('2FA_email_code_ttl', '5', 'integer');

        $service = app(TwoFactorService::class);
        $code = $service->issueEmailCode($user);

        // Backdate the sent_at past the TTL
        $user->two_factor_email_code_sent_at = now()->subMinutes(10);
        $user->save();

        $response = $this->actingAs($user)->post('/2fa/setup', [
            'method' => 'email',
            'code'   => $code,
        ]);

        $response->assertSessionHasErrors('code');
    }

    public function test_method_not_allowed_by_setting_rejects_confirm(): void
    {
        $this->setSetting('2FA_method', 'totp', 'string');

        $user = User::factory()->create();
        $user->assignRole('admin');

        $response = $this->actingAs($user)->post('/2fa/setup', [
            'method' => 'email',
            'code'   => '123456',
        ]);

        $response->assertSessionHasErrors('method');
    }
}
