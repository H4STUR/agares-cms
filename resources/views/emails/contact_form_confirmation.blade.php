@php
  $safe = function ($v) {
    if (is_bool($v)) return $v ? 'Yes' : 'No';
    if (is_array($v)) return json_encode($v, JSON_UNESCAPED_UNICODE);
    return (string)$v;
  };
@endphp

<!doctype html>
<html>
<head>
  <meta charset="utf-8">
</head>
<body style="font-family: Arial, sans-serif; background:#f6f7fb; padding:24px;">
  <div style="max-width:720px; margin:0 auto; background:#fff; border-radius:12px; padding:22px;">
    <h2 style="margin:0 0 10px 0;">Thank you for contacting us</h2>
    <p style="margin:0 0 14px 0;">
      We have received your message and will get back to you as soon as possible.
    </p>

    <div style="margin-top:16px; padding:14px; border:1px solid #eef0f4; border-radius:10px;">
      <div style="font-weight:bold; margin-bottom:8px;">Your message summary</div>

      <ul style="margin:0; padding-left:18px;">
        @foreach($values as $k => $v)
          @continue(is_array($v))
          <li><strong>{{ ucfirst($k) }}:</strong> {{ $safe($v) }}</li>
        @endforeach
      </ul>
    </div>

    <p style="margin-top:16px; color:#6b7280; font-size:12px;">
      {{ config('app.name') }}
    </p>
  </div>
</body>
</html>
