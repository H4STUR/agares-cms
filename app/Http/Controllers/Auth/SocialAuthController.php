<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
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

    public function callback(string $provider)
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
