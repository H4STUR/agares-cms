<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>{{ __('Verification code') }}</title>
</head>
<body style="margin:0; padding:24px; background:#f3f4f6; font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,Arial,sans-serif; color:#111827;">
    <table role="presentation" cellpadding="0" cellspacing="0" border="0" width="100%" style="max-width:520px; margin:0 auto; background:#ffffff; border-radius:8px; overflow:hidden; box-shadow:0 1px 3px rgba(0,0,0,0.08);">
        <tr>
            <td style="padding:24px 28px 0; ">
                <h2 style="margin:0 0 8px; font-size:20px; color:#111827;">{{ __('Your verification code') }}</h2>
                <p style="margin:0 0 20px; font-size:14px; color:#6b7280; line-height:1.5;">
                    {{ __('Use this code to finish signing in. It will expire in :n minutes.', ['n' => $ttlMinutes]) }}
                </p>
            </td>
        </tr>
        <tr>
            <td align="center" style="padding:8px 28px 24px;">
                <div style="display:inline-block; padding:18px 28px; background:#f9fafb; border:1px solid #e5e7eb; border-radius:6px; font-family:'SFMono-Regular',Menlo,Consolas,monospace; font-size:32px; letter-spacing:8px; font-weight:600; color:#111827;">
                    {{ $code }}
                </div>
            </td>
        </tr>
        <tr>
            <td style="padding:0 28px 24px;">
                <p style="margin:0; font-size:13px; color:#6b7280; line-height:1.5;">
                    {{ __("If you didn't try to sign in, you can ignore this email — your account is safe. Do not share this code with anyone; staff will never ask you for it.") }}
                </p>
            </td>
        </tr>
    </table>
</body>
</html>
