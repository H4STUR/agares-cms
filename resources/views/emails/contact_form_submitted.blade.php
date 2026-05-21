@php
  // Helper to safely render values
  $val = function ($v) {
    if (is_bool($v)) return $v ? 'Yes' : 'No';
    if (is_array($v)) return json_encode($v, JSON_UNESCAPED_UNICODE);
    return (string)$v;
  };

  $ctx = $context;
  if (is_string($ctx)) {
    $tmp = json_decode($ctx, true);
    $ctx = is_array($tmp) ? $tmp : $ctx;
  }
@endphp

<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
</head>
<body style="font-family: Arial, sans-serif; background:#f6f7fb; padding:24px;">
  <div style="max-width:720px; margin:0 auto; background:#ffffff; border-radius:12px; overflow:hidden; box-shadow:0 6px 22px rgba(0,0,0,.08);">
    <div style="padding:18px 22px; background:#111827; color:#fff;">
      <div style="font-size:16px; font-weight:700;">New message</div>
      <div style="opacity:.9; margin-top:4px;">
        {{ $form->name }}
      </div>
    </div>

    <div style="padding:22px;">
      <table width="100%" cellpadding="0" cellspacing="0" style="border-collapse:collapse;">
        @foreach($fields as $field)
          @php
            $k = $field->key;
            $label = trim(strip_tags($field->label ?? $k)) ?: $k;
            $v = $values[$k] ?? '';
          @endphp
          <tr>
            <td style="padding:10px 10px; border-bottom:1px solid #eef0f4; width:32%; color:#6b7280; vertical-align:top;">
              <strong>{{ $label }}</strong>
              <div style="font-size:12px; opacity:.85;">{{ $k }} • {{ $field->type }}</div>
            </td>
            <td style="padding:10px 10px; border-bottom:1px solid #eef0f4; vertical-align:top;">
              @if($field->type === 'textarea')
                <div style="white-space:pre-wrap;">{{ $val($v) }}</div>
              @elseif($field->type === 'file')
                <em>Attached (if provided)</em>
              @else
                {{ $val($v) }}
              @endif
            </td>
          </tr>
        @endforeach
      </table>

      <div style="margin-top:18px; font-size:12px; color:#6b7280;">
        {{-- @if($ctx)
          <div><strong>Context:</strong> {{ is_array($ctx) ? json_encode($ctx, JSON_UNESCAPED_UNICODE) : $ctx }}</div>
        @endif --}}
        <div><strong>Sent:</strong> {{ now()->toDateTimeString() }}</div>
      </div>
    </div>
  </div>
</body>
</html>
