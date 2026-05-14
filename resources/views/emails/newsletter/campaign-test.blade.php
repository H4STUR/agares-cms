<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $campaign->subject }}</title>
</head>
<body style="margin:0;padding:0;background:#f5f5f5;font-family:Arial,Helvetica,sans-serif;color:#222;">
    <div style="background:#fff3cd;border-bottom:1px solid #ffe69c;color:#664d03;padding:12px 16px;text-align:center;font-size:13px;font-weight:600;">
        TEST NEWSLETTER / PREVIEW — not sent to subscribers
    </div>

    <div style="max-width:640px;margin:0 auto;padding:24px 16px;">
        <div style="background:#fff;border:1px solid #e5e5e5;border-radius:6px;padding:24px;">
            <h2 style="margin:0 0 16px;font-size:18px;">{{ $campaign->subject }}</h2>

            <div style="font-size:14px;line-height:1.6;">
                {!! safe_html((string) $campaign->body) !!}
            </div>

            <hr style="margin:24px 0;border:none;border-top:1px solid #eee;">

            <p style="font-size:12px;color:#888;margin:0;">
                This is a test/preview email for the campaign
                <strong>{{ $campaign->title ?: $campaign->subject }}</strong>.
                If you are seeing it as an end-user, an admin has accidentally sent a test to your address.
            </p>

            <p style="font-size:12px;color:#888;margin:8px 0 0;">
                <em>Unsubscribe link will appear here in real campaigns.</em>
            </p>
        </div>
    </div>
</body>
</html>
