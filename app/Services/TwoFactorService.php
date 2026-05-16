<?php

namespace App\Services;

use App\Mail\Auth\TwoFactorChallengeMail;
use App\Models\Setting;
use App\Models\User;
use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Renderer\Image\SvgImageBackEnd;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;
use BaconQrCode\Writer;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use PragmaRX\Google2FA\Google2FA;

class TwoFactorService
{
    public const METHOD_TOTP  = 'totp';
    public const METHOD_EMAIL = 'email';

    public const SESSION_PENDING_SECRET   = 'two_factor.pending_secret';
    public const SESSION_PENDING_METHOD   = 'two_factor.pending_method';
    public const SESSION_PENDING_CODES    = 'two_factor.pending_codes';
    public const SESSION_LOGIN_USER_ID    = 'login.2fa_user_id';
    public const SESSION_LOGIN_REMEMBER   = 'login.2fa_remember';
    public const SESSION_SHOW_RECOVERY    = 'two_factor.show_recovery';

    public function __construct(
        private readonly Google2FA $google2fa,
        private readonly SecurityAuditService $audit,
    ) {
    }

    public function generateSecret(): string
    {
        return $this->google2fa->generateSecretKey(32);
    }

    public function otpauthUrl(User $user, string $secret): string
    {
        $issuer = $this->issuer();
        $label  = $user->email ?: ('user-'.$user->id);

        return $this->google2fa->getQRCodeUrl($issuer, $label, $secret);
    }

    public function qrCodeSvg(User $user, string $secret, int $size = 220): string
    {
        $renderer = new ImageRenderer(
            new RendererStyle($size, 1),
            new SvgImageBackEnd()
        );

        return (new Writer($renderer))->writeString($this->otpauthUrl($user, $secret));
    }

    public function verifyTotp(string $secret, string $code, int $window = 2): bool
    {
        $code = preg_replace('/\s+/', '', (string) $code);
        if (!preg_match('/^\d{6}$/', $code)) {
            return false;
        }

        return (bool) $this->google2fa->verifyKey($secret, $code, $window);
    }

    /** @return array<int,string> */
    public function generateRecoveryCodes(int $count = 8): array
    {
        $codes = [];
        for ($i = 0; $i < $count; $i++) {
            $codes[] = strtoupper(Str::random(4)).'-'.strtoupper(Str::random(4));
        }

        return $codes;
    }

    /**
     * Consume a recovery code on the given user. Hashed-comparison; matched code is removed.
     */
    public function consumeRecoveryCode(User $user, string $code): bool
    {
        $code = strtoupper(trim((string) $code));
        if ($code === '') {
            return false;
        }

        $codes = $user->two_factor_recovery_codes ?? [];
        if (!is_array($codes) || empty($codes)) {
            return false;
        }

        foreach ($codes as $i => $candidate) {
            if (hash_equals(strtoupper((string) $candidate), $code)) {
                array_splice($codes, $i, 1);
                $user->two_factor_recovery_codes = $codes;
                $user->save();
                $this->audit->log(SecurityAuditService::EVT_2FA_RECOVERY_CODE_USED, $user, [
                    'codes_remaining' => count($codes),
                ]);
                return true;
            }
        }

        return false;
    }

    public function enable(User $user, ?string $secret, array $recoveryCodes, string $method = self::METHOD_TOTP): void
    {
        $user->two_factor_secret = $method === self::METHOD_EMAIL ? null : $secret;
        $user->two_factor_recovery_codes = $recoveryCodes;
        $user->two_factor_method = $method;
        $user->two_factor_confirmed_at = now();
        $user->two_factor_email_code = null;
        $user->two_factor_email_code_sent_at = null;
        $user->save();

        $this->audit->log(SecurityAuditService::EVT_2FA_ENROLLED, $user, ['method' => $method]);
    }

    /**
     * @param  bool  $byAdmin  When true the event is recorded as `admin_reset` and the
     *                         actor is taken from Auth::id() (the admin), not the user.
     */
    public function disable(User $user, bool $byAdmin = false): void
    {
        $user->two_factor_secret = null;
        $user->two_factor_recovery_codes = null;
        $user->two_factor_method = null;
        $user->two_factor_confirmed_at = null;
        $user->two_factor_email_code = null;
        $user->two_factor_email_code_sent_at = null;
        $user->save();

        $this->audit->log(
            $byAdmin ? SecurityAuditService::EVT_2FA_ADMIN_RESET : SecurityAuditService::EVT_2FA_DISABLED_SELF,
            $user
        );
    }

    /* =========================
     * Email-OTP
     * ========================= */

    /**
     * Generates a fresh 6-digit code, hashes it, stores hash + sent_at on the user,
     * and sends it to the user's email synchronously. Returns the plaintext code
     * (callers should not log or persist it — it's returned for testing only).
     */
    public function issueEmailCode(User $user): string
    {
        $code = str_pad((string) random_int(0, 999_999), 6, '0', STR_PAD_LEFT);

        $user->two_factor_email_code = Hash::make($code);
        $user->two_factor_email_code_sent_at = now();
        $user->save();

        Mail::to($user->email)->send(new TwoFactorChallengeMail($user, $code, $this->emailCodeTtlMinutes()));

        $this->audit->log(SecurityAuditService::EVT_2FA_EMAIL_CODE_SENT, $user);

        return $code;
    }

    public function verifyEmailCode(User $user, string $code): bool
    {
        $code = preg_replace('/\s+/', '', (string) $code);
        if (!preg_match('/^\d{6}$/', $code)) {
            return false;
        }

        if (!$user->two_factor_email_code || !$user->two_factor_email_code_sent_at) {
            return false;
        }

        if ($user->two_factor_email_code_sent_at->addMinutes($this->emailCodeTtlMinutes())->isPast()) {
            return false;
        }

        if (!Hash::check($code, $user->two_factor_email_code)) {
            return false;
        }

        // Single-use: clear on success so the same code can't be reused.
        $user->two_factor_email_code = null;
        $user->two_factor_email_code_sent_at = null;
        $user->save();

        return true;
    }

    public function emailCodeTtlMinutes(): int
    {
        $ttl = (int) Setting::int('2FA_email_code_ttl', 10);
        return max(1, $ttl);
    }

    /* =========================
     * Method policy
     * ========================= */

    /** @return array<int,string> */
    public function allowedMethods(): array
    {
        $setting = strtolower(trim((string) Setting::str('2FA_method', self::METHOD_TOTP)));

        return match ($setting) {
            'email'        => [self::METHOD_EMAIL],
            'both', 'all'  => [self::METHOD_TOTP, self::METHOD_EMAIL],
            default        => [self::METHOD_TOTP],
        };
    }

    public function methodAllowed(string $method): bool
    {
        return in_array($method, $this->allowedMethods(), true);
    }

    /**
     * True if the user is enrolled but their chosen method is no longer permitted
     * by the current `2FA_method` setting. Such users must re-enrol — they should
     * still be allowed to complete an in-flight challenge using their legacy method,
     * then be redirected to /2fa/setup on the next request.
     */
    public function mustReEnrol(User $user): bool
    {
        if (!Setting::bool('2FA_enabled')) {
            return false;
        }
        if (!$user->hasTwoFactorEnabled()) {
            return false;
        }
        $method = (string) ($user->two_factor_method ?? '');
        if ($method === '') {
            return false;
        }
        return !$this->methodAllowed($method);
    }

    /**
     * True when a user attempting to log in should be redirected through the 2FA challenge.
     */
    public function shouldChallenge(User $user): bool
    {
        if (!Setting::bool('2FA_enabled')) {
            return false;
        }

        return $user->hasTwoFactorEnabled();
    }

    /**
     * True when the user must enrol in 2FA before continuing.
     */
    public function mustEnrol(User $user): bool
    {
        if (!Setting::bool('2FA_enabled')) {
            return false;
        }

        if ($user->hasTwoFactorEnabled()) {
            return false;
        }

        if (Setting::bool('2FA_required')) {
            return true;
        }

        $rolesCsv = trim((string) Setting::str('2FA_required_for_roles'));
        if ($rolesCsv === '') {
            return false;
        }

        $required = array_filter(array_map('trim', explode(',', $rolesCsv)));
        if (empty($required)) {
            return false;
        }

        $userRoles = $user->getRoleNames()->all();
        return (bool) array_intersect($required, $userRoles);
    }

    public function issuer(): string
    {
        $name = Setting::str('site_name', '');
        if ($name === '') {
            $name = (string) config('app.name', 'Agares CMS');
        }

        // otpauth issuer must not contain ":" — strip it defensively
        return str_replace(':', '', $name);
    }
}
