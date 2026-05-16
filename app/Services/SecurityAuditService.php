<?php

namespace App\Services;

use App\Models\SecurityAuditLog;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SecurityAuditService
{
    /* 2FA events */
    public const EVT_2FA_ENROLLED                 = 'two_factor.enrolled';
    public const EVT_2FA_DISABLED_SELF            = 'two_factor.disabled_self';
    public const EVT_2FA_ADMIN_RESET              = 'two_factor.admin_reset';
    public const EVT_2FA_RECOVERY_CODE_USED       = 'two_factor.recovery_code_used';
    public const EVT_2FA_RECOVERY_CODES_REGENERATED = 'two_factor.recovery_codes_regenerated';
    public const EVT_2FA_CHALLENGE_FAILED         = 'two_factor.challenge_failed';
    public const EVT_2FA_CHALLENGE_SUCCEEDED      = 'two_factor.challenge_succeeded';
    public const EVT_2FA_EMAIL_CODE_SENT          = 'two_factor.email_code_sent';
    public const EVT_2FA_OAUTH_CHALLENGED         = 'two_factor.oauth_challenged';

    /**
     * Record a security event.
     *
     * @param  User|int|null  $user      The user the event is about (null = system)
     * @param  array<string,mixed>  $metadata  Optional event-specific context
     */
    public function log(string $event, User|int|null $user = null, array $metadata = [], ?Request $request = null): void
    {
        $request = $request ?: request();

        $userId = $user instanceof User ? $user->id : $user;

        // Actor: who performed the action. Falls back to the authenticated user.
        // For self-service events actor === user; for admin actions actor is the admin.
        $actorId = Auth::id();
        if ($actorId === null && $userId !== null) {
            $actorId = $userId;
        }

        SecurityAuditLog::create([
            'user_id'    => $userId,
            'actor_id'   => $actorId,
            'event'      => $event,
            'ip'         => $request?->ip(),
            'user_agent' => substr((string) ($request?->userAgent() ?? ''), 0, 500) ?: null,
            'metadata'   => !empty($metadata) ? $metadata : null,
            'created_at' => now(),
        ]);
    }

    /** Convenience helper: human-readable label for a stored event string. */
    public static function label(string $event): string
    {
        return match ($event) {
            self::EVT_2FA_ENROLLED                  => __('Two-factor enabled'),
            self::EVT_2FA_DISABLED_SELF             => __('Two-factor disabled by user'),
            self::EVT_2FA_ADMIN_RESET               => __('Two-factor reset by admin'),
            self::EVT_2FA_RECOVERY_CODE_USED        => __('Recovery code used'),
            self::EVT_2FA_RECOVERY_CODES_REGENERATED => __('Recovery codes regenerated'),
            self::EVT_2FA_CHALLENGE_FAILED          => __('Failed verification attempt'),
            self::EVT_2FA_CHALLENGE_SUCCEEDED       => __('Verified at sign-in'),
            self::EVT_2FA_EMAIL_CODE_SENT           => __('Email verification code sent'),
            self::EVT_2FA_OAUTH_CHALLENGED          => __('OAuth sign-in challenged for two-factor'),
            default                                 => $event,
        };
    }
}
