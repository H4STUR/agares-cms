@php
  $statusColors = [
      'pending_payment' => '#f59e0b',
      'processing'      => '#3b82f6',
      'on_hold'         => '#6b7280',
      'completed'       => '#22c55e',
      'cancelled'       => '#ef4444',
      'refunded'        => '#06b6d4',
      'failed'          => '#374151',
  ];
  $fromColor = $statusColors[$fromStatus] ?? '#6b7280';
  $toColor   = $statusColors[$toStatus]   ?? '#6b7280';
  $billing   = $order->billing_address ?? [];
@endphp
<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
</head>
<body style="font-family:Arial,sans-serif;background:#f6f7fb;padding:24px;margin:0;">
  <div style="max-width:580px;margin:0 auto;background:#ffffff;border-radius:12px;overflow:hidden;box-shadow:0 6px 22px rgba(0,0,0,.08);">

    {{-- Header --}}
    <div style="padding:24px 28px;background:#111827;color:#fff;">
      <div style="font-size:18px;font-weight:700;">Order Status Update</div>
      <div style="opacity:.75;margin-top:4px;font-size:14px;">Order {{ $order->order_number }}</div>
    </div>

    <div style="padding:28px;">

      <p style="margin:0 0 24px;font-size:15px;color:#374151;">
        Hi{{ !empty($billing['name']) ? ', '.$billing['name'] : '' }}. Your order status has been updated.
      </p>

      {{-- Status transition --}}
      <div style="display:flex;align-items:center;justify-content:center;gap:16px;background:#f9fafb;border:1px solid #e5e7eb;border-radius:8px;padding:20px 28px;margin-bottom:24px;">
        <div style="text-align:center;">
          <div style="font-size:11px;color:#9ca3af;text-transform:uppercase;letter-spacing:.05em;margin-bottom:6px;">Previous</div>
          <span style="display:inline-block;padding:5px 14px;border-radius:999px;font-size:13px;font-weight:700;background:{{ $fromColor }}22;color:{{ $fromColor }};">
            {{ ucwords(str_replace('_', ' ', $fromStatus)) }}
          </span>
        </div>
        <div style="font-size:22px;color:#9ca3af;">→</div>
        <div style="text-align:center;">
          <div style="font-size:11px;color:#9ca3af;text-transform:uppercase;letter-spacing:.05em;margin-bottom:6px;">New Status</div>
          <span style="display:inline-block;padding:5px 14px;border-radius:999px;font-size:13px;font-weight:700;background:{{ $toColor }}22;color:{{ $toColor }};">
            {{ ucwords(str_replace('_', ' ', $toStatus)) }}
          </span>
        </div>
      </div>

      @if($comment)
      <div style="background:#f0f9ff;border:1px solid #bae6fd;border-radius:8px;padding:14px 18px;margin-bottom:24px;">
        <div style="font-size:12px;color:#0284c7;font-weight:700;text-transform:uppercase;letter-spacing:.04em;margin-bottom:4px;">Note from us</div>
        <div style="font-size:14px;color:#0c4a6e;">{{ $comment }}</div>
      </div>
      @endif

      <div style="font-size:13px;color:#9ca3af;border-top:1px solid #e5e7eb;padding-top:16px;">
        Questions? Reply to this email or contact our support team.
        <br>Order placed {{ optional($order->placed_at)->format('d M Y') }}.
      </div>

    </div>
  </div>
</body>
</html>
