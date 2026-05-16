<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Services\TwoFactorService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view.
     */
    public function create(): View
    {
        return view('auth.login');
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request, TwoFactorService $twoFactor): RedirectResponse
    {
        $request->authenticate();

        $user = $request->user();

        if ($twoFactor->shouldChallenge($user)) {
            $remember = $request->boolean('remember');
            Auth::guard('web')->logout();

            $request->session()->put(TwoFactorService::SESSION_LOGIN_USER_ID, $user->id);
            $request->session()->put(TwoFactorService::SESSION_LOGIN_REMEMBER, $remember);

            return redirect()->route('two-factor.challenge');
        }

        $request->session()->regenerate();

        $fallback = $user->can('view admin panel')
            ? route('admin.dashboard', absolute: false)
            : route('admin.user.profile', $user);

        return redirect()->intended($fallback);
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/');
    }
}
