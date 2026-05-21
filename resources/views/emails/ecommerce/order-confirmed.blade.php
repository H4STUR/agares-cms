@php
  $billing = $order->billing_address ?? [];
  $payment = $order->payments->first();
  $statusColors = [
      'pending_payment' => '#f59e0b',
      'processing'      => '#3b82f6',
      'on_hold'         => '#6b7280',
      'completed'       => '#22c55e',
      'cancelled'       => '#ef4444',
      'refunded'        => '#06b6d4',
      'failed'          => '#374151',
  ];
  $statusColor = $statusColors[$order->status] ?? '#6b7280';

  $driverLabels = [
      'cod'    => 'Cash on Delivery',
      'stripe' => 'Stripe (Card)',
      'payu'   => 'PayU',
      'p24'    => 'Przelewy24',
      'paypal' => 'PayPal',
  ];
  $driverLabel = $driverLabels[optional($payment?->provider)->driver ?? ''] ?? ucfirst(optional($payment?->provider)->driver ?? '—');
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
      <div style="font-size:18px;font-weight:700;">Order Confirmed ✓</div>
      <div style="opacity:.75;margin-top:4px;font-size:14px;">Thank you for your order!</div>
    </div>

    <div style="padding:28px;">

      {{-- Order number + date --}}
      <div style="background:#f9fafb;border:1px solid #e5e7eb;border-radius:8px;padding:14px 18px;margin-bottom:24px;display:flex;justify-content:space-between;align-items:center;">
        <div>
          <div style="font-size:12px;color:#6b7280;text-transform:uppercase;letter-spacing:.05em;">Order Number</div>
          <div style="font-size:18px;font-weight:800;font-family:monospace;margin-top:2px;">{{ $order->order_number }}</div>
        </div>
        <div style="text-align:right;">
          <div style="font-size:12px;color:#6b7280;">Placed</div>
          <div style="font-weight:600;">{{ optional($order->placed_at)->format('d M Y, H:i') }}</div>
        </div>
      </div>

      {{-- Items --}}
      <div style="font-weight:700;font-size:15px;margin-bottom:12px;">Items</div>
      <table width="100%" cellpadding="0" cellspacing="0" style="border-collapse:collapse;margin-bottom:20px;">
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
              @if($item->sku)<div style="font-size:12px;color:#9ca3af;">SKU: {{ $item->sku }}</div>@endif
            </td>
            <td style="padding:10px 12px;text-align:center;border-bottom:1px solid #f3f4f6;">{{ $item->qty }}</td>
            <td style="padding:10px 12px;text-align:right;font-weight:600;border-bottom:1px solid #f3f4f6;white-space:nowrap;">
              {{ number_format($item->total, 2) }} {{ $order->currency }}
            </td>
          </tr>
          @endforeach
        </tbody>
      </table>

      {{-- Totals --}}
      <table width="100%" cellpadding="0" cellspacing="0" style="border-collapse:collapse;margin-bottom:24px;">
        @if($order->tax_total > 0)
        <tr>
          <td style="padding:4px 0;color:#6b7280;font-size:14px;">Tax</td>
          <td style="padding:4px 0;text-align:right;font-size:14px;">{{ number_format($order->tax_total, 2) }} {{ $order->currency }}</td>
        </tr>
        @endif
        @if($order->shipping_total > 0)
        <tr>
          <td style="padding:4px 0;color:#6b7280;font-size:14px;">Shipping</td>
          <td style="padding:4px 0;text-align:right;font-size:14px;">{{ number_format($order->shipping_total, 2) }} {{ $order->currency }}</td>
        </tr>
        @endif
        @if($order->discount_total > 0)
        <tr>
          <td style="padding:4px 0;color:#22c55e;font-size:14px;">Discount</td>
          <td style="padding:4px 0;text-align:right;font-size:14px;color:#22c55e;">-{{ number_format($order->discount_total, 2) }} {{ $order->currency }}</td>
        </tr>
        @endif
        <tr>
          <td colspan="2" style="padding:0;border-top:2px solid #e5e7eb;"></td>
        </tr>
        <tr>
          <td style="padding:10px 0 0;font-weight:800;font-size:16px;">Total</td>
          <td style="padding:10px 0 0;text-align:right;font-weight:800;font-size:16px;white-space:nowrap;">
            {{ number_format($order->grand_total, 2) }} {{ $order->currency }}
          </td>
        </tr>
      </table>

      {{-- Details row --}}
      <table width="100%" cellpadding="0" cellspacing="0" style="border-collapse:collapse;margin-bottom:24px;">
        <tr>
          <td style="width:50%;vertical-align:top;padding-right:12px;">
            <div style="font-weight:700;font-size:14px;margin-bottom:8px;">Billing Address</div>
            <div style="font-size:14px;color:#374151;line-height:1.7;">
              @if(!empty($billing['name']))<div style="font-weight:600;">{{ $billing['name'] }}</div>@endif
              @if(!empty($billing['address1']))<div>{{ $billing['address1'] }}</div>@endif
              @if(!empty($billing['city']))<div>{{ ($billing['postcode'] ?? '') }} {{ $billing['city'] }}</div>@endif
              @if(!empty($billing['country']))<div>{{ $billing['country'] }}</div>@endif
              @if(!empty($billing['phone']))<div style="margin-top:4px;color:#6b7280;">{{ $billing['phone'] }}</div>@endif
            </div>
          </td>
          <td style="width:50%;vertical-align:top;padding-left:12px;border-left:1px solid #e5e7eb;">
            <div style="font-weight:700;font-size:14px;margin-bottom:8px;">Payment</div>
            <div style="font-size:14px;color:#374151;">
              <div style="font-weight:600;">{{ $driverLabel }}</div>
              <div style="margin-top:6px;">
                Status:
                <span style="display:inline-block;padding:2px 10px;border-radius:999px;font-size:12px;font-weight:700;background:{{ $statusColor }}22;color:{{ $statusColor }};">
                  {{ ucwords(str_replace('_', ' ', $order->status)) }}
                </span>
              </div>
              @if(optional($payment?->provider)->driver === 'cod')
                <div style="margin-top:6px;font-size:13px;color:#6b7280;">You will pay on delivery.</div>
              @endif
            </div>
          </td>
        </tr>
      </table>

      @if(!empty($billing['notes']))
      <div style="background:#fffbeb;border:1px solid #fde68a;border-radius:8px;padding:12px 16px;margin-bottom:24px;font-size:14px;color:#92400e;">
        <strong>Order note:</strong> {{ $billing['notes'] }}
      </div>
      @endif

      <div style="font-size:13px;color:#9ca3af;border-top:1px solid #e5e7eb;padding-top:16px;">
        Questions? Reply to this email or contact our support team.
      </div>

    </div>
  </div>
</body>
</html>
