<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\SecurityAuditService;
use App\Services\TwoFactorService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;

class SocialAuthController extends Controller
{
    private const PROVIDERS = ['google', 'facebook'];

    public function redirect(string $provider)
    {
        $this->validateProvider($provider);

        return Socialite::driver($provider)->redirect();
    }

    public function callback(string $provider, TwoFactorService $twoFactor, SecurityAuditService $audit)
    {
        $this->validateProvider($provider);

        $socialUser = Socialite::driver($provider)->user();

        $providerId = $socialUser->getId();
        $email      = $socialUser->getEmail();
        $name       = $socialUser->getName() ?: ($socialUser->getNickname() ?: 'User');
        $avatar     = $socialUser->getAvatar();

        // 1) If email matches existing user, use it
        $user = $email ? User::where('email', $email)->first() : null;

        // 2) Else if provider already linked, use it
        if (!$user) {
            $user = User::where('provider', $provider)
                ->where('provider_id', $providerId)
                ->first();
        }

        // 3) Else create user
        if (!$user) {
            $user = User::create([
                // If your users table doesn't have username/surname, remove these lines
                'username' => $this->makeUniqueUsername($name),
                'name'     => $name,
                'surname'  => null,
                'email'    => $email ?? (Str::uuid().'@no-email.local'),

                // Password not used for social logins
                'password' => bcrypt(Str::random(40)),

                'provider' => $provider,
                'provider_id' => $providerId,
                'avatar'   => $avatar,
            ]);
        } else {
            // attach provider if missing and update avatar
            $user->forceFill([
                'provider' => $user->provider ?? $provider,
                'provider_id' => $user->provider_id ?? $providerId,
                'avatar' => $avatar ?? $user->avatar,
            ])->save();
        }

        // 2FA enforcement — if the user has 2FA enrolled, route through the standard
        // challenge instead of logging them in immediately. New users created above
        // can't have 2FA enrolled yet, so this only applies to returning users.
        if ($twoFactor->shouldChallenge($user)) {
            session()->put(TwoFactorService::SESSION_LOGIN_USER_ID, $user->id);
            session()->put(TwoFactorService::SESSION_LOGIN_REMEMBER, true);

            $audit->log(SecurityAuditService::EVT_2FA_OAUTH_CHALLENGED, $user, [
                'provider' => $provider,
            ]);

            return redirect()->route('two-factor.challenge');
        }

        Auth::login($user, true);

        $fallback = $user->can('view admin panel')
            ? route('admin.dashboard')
            : route('admin.user.profile', $user);

        return redirect()->intended($fallback);
    }

    private function validateProvider(string $provider): void
    {
        abort_unless(in_array($provider, self::PROVIDERS, true), 404);
    }

    private function makeUniqueUsername(string $name): string
    {
        $base = Str::slug($name);
        if ($base === '') $base = 'user';

        $candidate = $base;
        $i = 1;

        while (User::where('username', $candidate)->exists()) {
            $candidate = $base . $i;
            $i++;
        }

        return $candidate;
    }
}
