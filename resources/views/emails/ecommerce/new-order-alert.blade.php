@php
  $billing = $order->billing_address ?? [];
  $adminUrl = route('admin.ecommerce.orders.show', $order);
@endphp
<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
</head>
<body style="font-family:Arial,sans-serif;background:#f6f7fb;padding:24px;margin:0;">
  <div style="max-width:640px;margin:0 auto;background:#ffffff;border-radius:12px;overflow:hidden;box-shadow:0 6px 22px rgba(0,0,0,.08);">

    {{-- Header --}}
    <div style="padding:24px 28px;background:#111827;color:#fff;">
      <div style="font-size:18px;font-weight:700;">🛒 New Order Received</div>
      <div style="opacity:.75;margin-top:4px;font-size:14px;">{{ $order->order_number }} — {{ optional($order->placed_at)->format('d M Y, H:i') }}</div>
    </div>

    <div style="padding:28px;">

      {{-- Summary bar --}}
      <table width="100%" cellpadding="0" cellspacing="0" style="border-collapse:collapse;background:#f9fafb;border:1px solid #e5e7eb;border-radius:8px;overflow:hidden;margin-bottom:24px;">
        <tr>
          <td style="padding:14px 18px;border-right:1px solid #e5e7eb;">
            <div style="font-size:11px;color:#9ca3af;text-transform:uppercase;letter-spacing:.05em;">Order</div>
            <div style="font-weight:800;font-size:16px;font-family:monospace;margin-top:2px;">{{ $order->order_number }}</div>
          </td>
          <td style="padding:14px 18px;border-right:1px solid #e5e7eb;">
            <div style="font-size:11px;color:#9ca3af;text-transform:uppercase;letter-spacing:.05em;">Customer</div>
            <div style="font-weight:600;margin-top:2px;">{{ $billing['name'] ?? '—' }}</div>
            <div style="font-size:12px;color:#6b7280;">{{ $billing['email'] ?? '' }}</div>
          </td>
          <td style="padding:14px 18px;">
            <div style="font-size:11px;color:#9ca3af;text-transform:uppercase;letter-spacing:.05em;">Grand Total</div>
            <div style="font-weight:800;font-size:18px;color:#111827;margin-top:2px;">
              {{ number_format($order->grand_total, 2) }} {{ $order->currency }}
            </div>
          </td>
        </tr>
      </table>

      {{-- Items --}}
      <div style="font-weight:700;font-size:15px;margin-bottom:12px;">Items ({{ $order->items->count() }})</div>
      <table width="100%" cellpadding="0" cellspacing="0" style="border-collapse:collapse;margin-bottom:24px;">
        <thead>
          <tr style="background:#f9fafb;">
            <th style="padding:8px 12px;text-align:left;font-size:12px;color:#6b7280;font-weight:600;border-bottom:1px solid #e5e7eb;">Product</th>
            <th style="padding:8px 12px;text-align:center;font-size:12px;color:#6b7280;font-weight:600;border-bottom:1px solid #e5e7eb;">Qty</th>
            <th style="padding:8px 12px;text-align:right;font-size:12px;color:#6b7280;font-weight:600;border-bottom:1px solid #e5e7eb;">Total</th>
          </tr>
        </thead>
        <tbody>
          @foreach($order->items as $item)
          <tr>
            <td style="padding:10px 12px;border-bottom:1px solid #f3f4f6;">
              <div style="font-weight:600;">{{ $item->name }}</div>
              @if($item->sku)<div style="font-size:12px;color:#9ca3af;">{{ $item->sku }}</div>@endif
            </td>
            <td style="padding:10px 12px;text-align:center;border-bottom:1px solid #f3f4f6;color:#374151;">{{ $item->qty }}</td>
            <td style="padding:10px 12px;text-align:right;font-weight:600;border-bottom:1px solid #f3f4f6;white-space:nowrap;">
              {{ number_format($item->total, 2) }} {{ $order->currency }}
            </td>
          </tr>
          @endforeach
        </tbody>
      </table>

      {{-- Shipping address --}}
      @if(!empty($billing['address1']))
      <div style="font-weight:700;font-size:14px;margin-bottom:8px;">Ship to</div>
      <div style="font-size:14px;color:#374151;line-height:1.7;margin-bottom:24px;">
        @if(!empty($billing['address1']))<div>{{ $billing['address1'] }}</div>@endif
        @if(!empty($billing['city']))<div>{{ ($billing['postcode'] ?? '') }} {{ $billing['city'] }}</div>@endif
        @if(!empty($billing['country']))<div>{{ $billing['country'] }}</div>@endif
      </div>
      @endif

      {{-- CTA --}}
      <div style="text-align:center;margin-bottom:8px;">
        <a href="{{ $adminUrl }}"
           style="display:inline-block;padding:12px 28px;background:#111827;color:#ffffff;text-decoration:none;border-radius:8px;font-weight:700;font-size:14px;">
          View Order in Admin
        </a>
      </div>

      <div style="font-size:12px;color:#9ca3af;text-align:center;margin-top:16px;">
        This is an automated notification.
      </div>

    </div>
  </div>
</body>
</html>
