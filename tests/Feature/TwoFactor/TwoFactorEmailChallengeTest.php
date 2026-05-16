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

class TwoFactorEmailChallengeTest extends TestCase
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

    private function emailEnrolledUser(string $password = 'password'): User
    {
        /** @var User $user */
        $user = User::factory()->create(['password' => bcrypt($password)]);
        $user->assignRole('admin');
        $user->givePermissionTo('view admin panel');

        $service = app(TwoFactorService::class);
        $service->enable($user, null, $service->generateRecoveryCodes(), TwoFactorService::METHOD_EMAIL);

        return $user->fresh();
    }

    public function test_login_with_email_2fa_issues_code_and_shows_challenge(): void
    {
        Mail::fake();
        $user = $this->emailEnrolledUser();

        $response = $this->post('/login', [
            'input_type' => $user->email,
            'password'   => 'password',
        ]);

        $response->assertRedirect(route('two-factor.challenge'));

        // Visiting the challenge issues an email code
        $this->get('/2fa/challenge')->assertStatus(200);
        Mail::assertSent(TwoFactorChallengeMail::class, fn ($m) => $m->user->is($user));

        $user->refresh();
        $this->assertNotNull($user->two_factor_email_code);
    }

    public function test_valid_email_code_completes_login(): void
    {
        Mail::fake();
        $user = $this->emailEnrolledUser();

        $this->withSession([
            TwoFactorService::SESSION_LOGIN_USER_ID  => $user->id,
            TwoFactorService::SESSION_LOGIN_REMEMBER => false,
        ]);

        // Trigger GET so a code is issued
        $this->get('/2fa/challenge');
        $user->refresh();

        // Issue a fresh code we can verify against (the controller-generated one is hashed; we re-issue to know plaintext)
        $service = app(TwoFactorService::class);
        $code = $service->issueEmailCode($user);

        $this->withSession([
            TwoFactorService::SESSION_LOGIN_USER_ID  => $user->id,
            TwoFactorService::SESSION_LOGIN_REMEMBER => false,
        ]);

        $response = $this->post('/2fa/challenge', ['code' => $code]);

        $this->assertAuthenticatedAs($user);
        $response->assertRedirect(route('admin.dashboard', absolute: false));

        $user->refresh();
        $this->assertNull($user->two_factor_email_code); // consumed
    }

    public function test_used_email_code_cannot_be_reused(): void
    {
        Mail::fake();
        $user = $this->emailEnrolledUser();

        $service = app(TwoFactorService::class);
        $code = $service->issueEmailCode($user);

        $this->withSession([
            TwoFactorService::SESSION_LOGIN_USER_ID  => $user->id,
            TwoFactorService::SESSION_LOGIN_REMEMBER => false,
        ])->post('/2fa/challenge', ['code' => $code]);

        $this->post('/logout');

        $this->withSession([
            TwoFactorService::SESSION_LOGIN_USER_ID  => $user->id,
            TwoFactorService::SESSION_LOGIN_REMEMBER => false,
        ]);

        $response = $this->post('/2fa/challenge', ['code' => $code]);

        $response->assertSessionHasErrors('code');
        $this->assertGuest();
    }

    public function test_resend_email_code_sends_new_mail(): void
    {
        Mail::fake();
        $user = $this->emailEnrolledUser();

        $service = app(TwoFactorService::class);
        $service->issueEmailCode($user);
        Mail::assertSent(TwoFactorChallengeMail::class, 1);

        $this->withSession([
            TwoFactorService::SESSION_LOGIN_USER_ID => $user->id,
        ]);

        $response = $this->post('/2fa/challenge/resend');

        $response->assertRedirect();
        Mail::assertSent(TwoFactorChallengeMail::class, 2);
    }

    public function test_resend_is_throttled(): void
    {
        Mail::fake();
        $user = $this->emailEnrolledUser();

        $this->withSession([
            TwoFactorService::SESSION_LOGIN_USER_ID => $user->id,
        ]);
        $this->post('/2fa/challenge/resend');

        // Second call within 60s should fail
        $response = $this->withSession([
            TwoFactorService::SESSION_LOGIN_USER_ID => $user->id,
        ])->post('/2fa/challenge/resend');

        $response->assertSessionHasErrors('code');
        Mail::assertSent(TwoFactorChallengeMail::class, 1);
    }

    public function test_must_reenrol_redirects_after_successful_challenge_when_method_disallowed(): void
    {
        Mail::fake();
        $user = $this->emailEnrolledUser();

        // After enrolment, admin disables email method site-wide
        $this->setSetting('2FA_method', 'totp', 'string');

        $service = app(TwoFactorService::class);
        $code = $service->issueEmailCode($user);

        $this->withSession([
            TwoFactorService::SESSION_LOGIN_USER_ID  => $user->id,
            TwoFactorService::SESSION_LOGIN_REMEMBER => false,
        ]);

        $response = $this->post('/2fa/challenge', ['code' => $code]);

        $this->assertAuthenticatedAs($user);
        $response->assertRedirect(route('two-factor.setup'));
    }
}
