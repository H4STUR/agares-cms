<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\SecurityAuditService;
use App\Services\TwoFactorService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class TwoFactorController extends Controller implements HasMiddleware
{
    public function __construct(
        private readonly TwoFactorService $service,
        private readonly SecurityAuditService $audit,
    ) {
    }

    public static function middleware(): array
    {
        return [
            new Middleware('auth', only: [
                'setup', 'confirm', 'sendSetupEmail',
                'disable', 'regenerateCodes', 'showRecoveryCodes',
            ]),
            new Middleware('can:manage own two factor', only: [
                'setup', 'confirm', 'sendSetupEmail',
                'disable', 'regenerateCodes', 'showRecoveryCodes',
            ]),
            new Middleware('guest', only: [
                'challenge', 'verifyChallenge', 'cancelChallenge', 'resendEmailCode',
            ]),
        ];
    }

    /* =========================
     * Enrolment (authenticated)
     * ========================= */

    public function setup(Request $request): View
    {
        $user            = $request->user();
        $allowedMethods  = $this->service->allowedMethods();
        $requestedMethod = (string) $request->query('method', '');

        // If only one method is allowed, force it. Otherwise honour ?method= when valid.
        if (count($allowedMethods) === 1) {
            $method = $allowedMethods[0];
        } elseif ($this->service->methodAllowed($requestedMethod)) {
            $method = $requestedMethod;
        } else {
            $method = '';
        }

        $secret = null;
        $qrSvg  = null;
        $emailCodeSentAt = null;

        if ($method === TwoFactorService::METHOD_TOTP) {
            $secret = (string) $request->session()->get(TwoFactorService::SESSION_PENDING_SECRET);
            if ($secret === '' || $request->session()->get(TwoFactorService::SESSION_PENDING_METHOD) !== TwoFactorService::METHOD_TOTP) {
                $secret = $this->service->generateSecret();
                $request->session()->put(TwoFactorService::SESSION_PENDING_SECRET, $secret);
                $request->session()->put(TwoFactorService::SESSION_PENDING_METHOD, TwoFactorService::METHOD_TOTP);
            }
            $qrSvg = $this->service->qrCodeSvg($user, $secret);
        } elseif ($method === TwoFactorService::METHOD_EMAIL) {
            $request->session()->put(TwoFactorService::SESSION_PENDING_METHOD, TwoFactorService::METHOD_EMAIL);
            // Don't clear pending secret yet — user might switch back to TOTP.
            $emailCodeSentAt = $user->two_factor_email_code_sent_at;
        }

        return view('pages.auth.two-factor-setup', [
            'user'            => $user,
            'method'          => $method,
            'allowedMethods'  => $allowedMethods,
            'secret'          => $secret,
            'qrSvg'           => $qrSvg,
            'emailCodeSentAt' => $emailCodeSentAt,
            'ttlMinutes'      => $this->service->emailCodeTtlMinutes(),
            'mustReEnrol'     => $this->service->mustReEnrol($user),
        ]);
    }

    public function sendSetupEmail(Request $request): RedirectResponse
    {
        if (!$this->service->methodAllowed(TwoFactorService::METHOD_EMAIL)) {
            return redirect()->route('two-factor.setup');
        }

        $user = $request->user();

        // Throttle: 1 setup email per 60s per user
        $key = 'two-factor.send-setup:'.$user->id;
        if (RateLimiter::tooManyAttempts($key, 1)) {
            $seconds = RateLimiter::availableIn($key);
            return back()->withErrors([
                'email' => __('Please wait :n seconds before requesting another code.', ['n' => $seconds]),
            ]);
        }
        RateLimiter::hit($key, 60);

        $this->service->issueEmailCode($user);
        $request->session()->put(TwoFactorService::SESSION_PENDING_METHOD, TwoFactorService::METHOD_EMAIL);

        return redirect()
            ->route('two-factor.setup', ['method' => TwoFactorService::METHOD_EMAIL])
            ->with('success', __('A verification code has been sent to :email.', ['email' => $user->email]));
    }

    public function confirm(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'method' => ['required', 'string', 'in:totp,email'],
            'code'   => ['required', 'string', 'digits:6'],
        ]);

        if (!$this->service->methodAllowed($data['method'])) {
            throw ValidationException::withMessages([
                'method' => __('This method is not allowed by the current site settings.'),
            ]);
        }

        $user = $request->user();

        if ($data['method'] === TwoFactorService::METHOD_TOTP) {
            $secret = (string) $request->session()->get(TwoFactorService::SESSION_PENDING_SECRET);
            if ($secret === '') {
                return redirect()->route('two-factor.setup', ['method' => 'totp']);
            }
            if (!$this->service->verifyTotp($secret, $data['code'])) {
                throw ValidationException::withMessages([
                    'code' => __('The code is invalid. Make sure your authenticator app clock is in sync and try again.'),
                ]);
            }

            $codes = $this->service->generateRecoveryCodes();
            $this->service->enable($user, $secret, $codes, TwoFactorService::METHOD_TOTP);
        } else { // email
            if (!$this->service->verifyEmailCode($user, $data['code'])) {
                throw ValidationException::withMessages([
                    'code' => __('The code is invalid or has expired. Request a new one.'),
                ]);
            }

            $codes = $this->service->generateRecoveryCodes();
            $this->service->enable($user, null, $codes, TwoFactorService::METHOD_EMAIL);
        }

        $request->session()->forget([
            TwoFactorService::SESSION_PENDING_SECRET,
            TwoFactorService::SESSION_PENDING_METHOD,
        ]);
        $request->session()->flash(TwoFactorService::SESSION_SHOW_RECOVERY, $codes);

        return redirect()->route('two-factor.recovery-codes');
    }

    public function showRecoveryCodes(Request $request): View|RedirectResponse
    {
        $user = $request->user();
        if (!$user->hasTwoFactorEnabled()) {
            return redirect()->route('admin.user.settings', $user);
        }

        $oneTime = $request->session()->get(TwoFactorService::SESSION_SHOW_RECOVERY);

        return view('pages.auth.two-factor-recovery-codes', [
            'user'    => $user,
            'codes'   => is_array($oneTime) ? $oneTime : [],
            'fresh'   => is_array($oneTime),
            'remaining' => count($user->two_factor_recovery_codes ?? []),
        ]);
    }

    public function regenerateCodes(Request $request): RedirectResponse
    {
        $request->validate([
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();
        if (!$user->hasTwoFactorEnabled()) {
            return redirect()->route('admin.user.settings', $user);
        }

        $codes = $this->service->generateRecoveryCodes();
        $user->two_factor_recovery_codes = $codes;
        $user->save();

        $this->audit->log(SecurityAuditService::EVT_2FA_RECOVERY_CODES_REGENERATED, $user);

        $request->session()->flash(TwoFactorService::SESSION_SHOW_RECOVERY, $codes);

        return redirect()->route('two-factor.recovery-codes');
    }

    public function disable(Request $request): RedirectResponse
    {
        $request->validate([
            'password' => ['required', 'current_password'],
        ]);

        $this->service->disable($request->user());

        return redirect()
            ->route('admin.user.settings', $request->user())
            ->with('success', __('Two-factor authentication has been disabled.'));
    }

    /* =========================
     * Challenge (guest mid-login)
     * ========================= */

    public function challenge(Request $request): View|RedirectResponse
    {
        $userId = $request->session()->get(TwoFactorService::SESSION_LOGIN_USER_ID);
        if (!$userId) {
            return redirect()->route('login');
        }

        $user = User::find($userId);
        if (!$user || !$user->hasTwoFactorEnabled()) {
            $request->session()->forget([
                TwoFactorService::SESSION_LOGIN_USER_ID,
                TwoFactorService::SESSION_LOGIN_REMEMBER,
            ]);
            return redirect()->route('login');
        }

        $method = (string) ($user->two_factor_method ?? TwoFactorService::METHOD_TOTP);

        // For email method: issue a fresh code on first GET, but don't spam — only if
        // there's no recent one OR the current one is expired.
        if ($method === TwoFactorService::METHOD_EMAIL) {
            $needsCode = !$user->two_factor_email_code
                || !$user->two_factor_email_code_sent_at
                || $user->two_factor_email_code_sent_at->addMinutes($this->service->emailCodeTtlMinutes())->isPast();

            if ($needsCode) {
                $this->service->issueEmailCode($user);
            }
        }

        return view('pages.auth.two-factor-challenge', [
            'method'     => $method,
            'ttlMinutes' => $this->service->emailCodeTtlMinutes(),
            'maskedEmail' => $this->maskEmail($user->email),
        ]);
    }

    public function resendEmailCode(Request $request): RedirectResponse
    {
        $userId = $request->session()->get(TwoFactorService::SESSION_LOGIN_USER_ID);
        if (!$userId) {
            return redirect()->route('login');
        }

        $user = User::find($userId);
        if (!$user || !$user->hasTwoFactorEnabled() || $user->two_factor_method !== TwoFactorService::METHOD_EMAIL) {
            return redirect()->route('two-factor.challenge');
        }

        // Rate limit: 1 per 60s + 5 per 15min per user/IP
        $shortKey = 'two-factor.resend.short:'.$user->id.'|'.Str::lower((string) $request->ip());
        $longKey  = 'two-factor.resend.long:'.$user->id.'|'.Str::lower((string) $request->ip());

        if (RateLimiter::tooManyAttempts($shortKey, 1)) {
            $seconds = RateLimiter::availableIn($shortKey);
            return back()->withErrors([
                'code' => __('Please wait :n seconds before requesting another code.', ['n' => $seconds]),
            ]);
        }
        if (RateLimiter::tooManyAttempts($longKey, 5)) {
            $seconds = RateLimiter::availableIn($longKey);
            return back()->withErrors([
                'code' => __('Too many resends. Try again in :n seconds.', ['n' => $seconds]),
            ]);
        }

        RateLimiter::hit($shortKey, 60);
        RateLimiter::hit($longKey, 60 * 15);

        $this->service->issueEmailCode($user);

        return back()->with('success', __('A fresh verification code has been sent.'));
    }

    public function verifyChallenge(Request $request): RedirectResponse
    {
        $userId = $request->session()->get(TwoFactorService::SESSION_LOGIN_USER_ID);
        if (!$userId) {
            return redirect()->route('login');
        }

        $this->ensureNotRateLimited($request, $userId);

        $data = $request->validate([
            'code'          => ['nullable', 'string'],
            'recovery_code' => ['nullable', 'string'],
        ]);

        if (empty($data['code']) && empty($data['recovery_code'])) {
            throw ValidationException::withMessages([
                'code' => __('Please enter a verification code or a recovery code.'),
            ]);
        }

        $user = User::find($userId);
        if (!$user || !$user->hasTwoFactorEnabled()) {
            $request->session()->forget([
                TwoFactorService::SESSION_LOGIN_USER_ID,
                TwoFactorService::SESSION_LOGIN_REMEMBER,
            ]);
            return redirect()->route('login');
        }

        $verified = false;
        $method   = (string) ($user->two_factor_method ?? TwoFactorService::METHOD_TOTP);

        if (!empty($data['code'])) {
            if ($method === TwoFactorService::METHOD_EMAIL) {
                $verified = $this->service->verifyEmailCode($user, $data['code']);
            } else {
                $verified = $this->service->verifyTotp((string) $user->two_factor_secret, $data['code']);
            }
        }

        if (!$verified && !empty($data['recovery_code'])) {
            $verified = $this->service->consumeRecoveryCode($user, $data['recovery_code']);
        }

        if (!$verified) {
            RateLimiter::hit($this->throttleKey($userId, $request->ip()));
            $this->audit->log(SecurityAuditService::EVT_2FA_CHALLENGE_FAILED, $user, [
                'method' => $method,
                'tried_recovery' => !empty($data['recovery_code']),
            ]);
            throw ValidationException::withMessages([
                'code' => __('The code is invalid.'),
            ]);
        }

        RateLimiter::clear($this->throttleKey($userId, $request->ip()));

        $remember = (bool) $request->session()->pull(TwoFactorService::SESSION_LOGIN_REMEMBER, false);
        $request->session()->forget(TwoFactorService::SESSION_LOGIN_USER_ID);

        Auth::guard('web')->loginUsingId($user->id, $remember);
        $request->session()->regenerate();

        $this->audit->log(SecurityAuditService::EVT_2FA_CHALLENGE_SUCCEEDED, $user, [
            'method' => $method,
            'used_recovery' => !empty($data['recovery_code']),
        ]);

        // If admin has changed `2FA_method` to exclude this user's chosen method,
        // route them to /2fa/setup to re-enrol after login instead of the intended URL.
        if ($this->service->mustReEnrol($user)) {
            return redirect()
                ->route('two-factor.setup')
                ->with('warning', __('Two-factor settings have changed. Please set up your account again with an allowed method.'));
        }

        $fallback = $user->can('view admin panel')
            ? route('admin.dashboard', absolute: false)
            : route('admin.user.profile', $user);

        return redirect()->intended($fallback);
    }

    public function cancelChallenge(Request $request): RedirectResponse
    {
        $request->session()->forget([
            TwoFactorService::SESSION_LOGIN_USER_ID,
            TwoFactorService::SESSION_LOGIN_REMEMBER,
        ]);

        return redirect()->route('login');
    }

    private function ensureNotRateLimited(Request $request, int $userId): void
    {
        $key = $this->throttleKey($userId, $request->ip());
        if (!RateLimiter::tooManyAttempts($key, 5)) {
            return;
        }

        $seconds = RateLimiter::availableIn($key);
        throw ValidationException::withMessages([
            'code' => __('Too many attempts. Try again in :seconds seconds.', ['seconds' => $seconds]),
        ]);
    }

    private function throttleKey(int $userId, ?string $ip): string
    {
        return 'two-factor:'.$userId.'|'.Str::lower((string) $ip);
    }

    private function maskEmail(string $email): string
    {
        $parts = explode('@', $email, 2);
        if (count($parts) !== 2) {
            return $email;
        }
        [$local, $domain] = $parts;
        $localMasked = strlen($local) <= 2
            ? str_repeat('•', max(1, strlen($local)))
            : substr($local, 0, 1).str_repeat('•', max(1, strlen($local) - 2)).substr($local, -1);
        return $localMasked.'@'.$domain;
    }
}
