@extends('pages.frontend.base')

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/frontend/ecommerce/theme/assets/css/theme.css') }}">
@endpush

@section('content')

{{-- BREADCRUMB --}}
<div style="background:var(--bg-soft);border-bottom:1px solid var(--border);">
    <div class="container">
        <nav class="breadcrumb" aria-label="Breadcrumb">
            <a href="/">Home</a>
            <svg class="breadcrumb-sep" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 18l6-6-6-6"/></svg>
            <a href="{{ route('shop.home') }}">Shop</a>
            <svg class="breadcrumb-sep" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 18l6-6-6-6"/></svg>
            <span class="breadcrumb-current">Order Confirmed</span>
        </nav>
    </div>
</div>

<div class="container section">

    {{-- Success banner --}}
    <div style="text-align:center;padding:48px 0 32px;">
        <div style="width:72px;height:72px;border-radius:50%;background:rgba(34,197,94,.12);display:inline-flex;align-items:center;justify-content:center;margin-bottom:20px;">
            <svg viewBox="0 0 24 24" fill="none" stroke="var(--success,#22c55e)" stroke-width="2.5" style="width:36px;height:36px;">
                <path d="M20 6L9 17l-5-5"/>
            </svg>
        </div>
        <h1 style="font-size:1.75rem;font-weight:800;margin-bottom:8px;">Thank you for your order!</h1>
        <p style="color:var(--muted);max-width:480px;margin:0 auto 12px;">
            Your order has been placed successfully. We've sent a confirmation to
            <strong>{{ $order->billing_address['email'] ?? '' }}</strong>.
        </p>
        <div style="display:inline-block;padding:10px 24px;background:var(--bg-card,#13131f);border:1px solid var(--border);border-radius:var(--radius-sm);font-family:monospace;font-size:1.1rem;font-weight:700;letter-spacing:.05em;">
            {{ $order->order_number }}
        </div>
    </div>

    <div style="max-width:720px;margin:0 auto;display:flex;flex-direction:column;gap:20px;">

        {{-- Order items --}}
        <div style="background:var(--bg-card,#13131f);border:1px solid var(--border);border-radius:var(--radius);overflow:hidden;">
            <div style="padding:18px 20px;border-bottom:1px solid var(--border);font-weight:700;">
                Order Items
            </div>
            <div style="padding:0 20px;">
                @foreach($order->items as $item)
                    <div style="display:flex;align-items:center;gap:12px;padding:14px 0;{{ !$loop->last ? 'border-bottom:1px solid var(--border);' : '' }}">
                        <div style="flex:1;min-width:0;">
                            <div style="font-weight:600;">{{ $item->name }}</div>
                            @if($item->sku)
                                <div style="font-size:0.78rem;color:var(--muted);margin-top:2px;">SKU: {{ $item->sku }}</div>
                            @endif
                        </div>
                        <div style="color:var(--muted);font-size:0.875rem;white-space:nowrap;">× {{ $item->qty }}</div>
                        <div style="font-weight:600;white-space:nowrap;min-width:90px;text-align:right;">
                            {{ number_format($item->total, 2) }} {{ $order->currency }}
                        </div>
                    </div>
                @endforeach
            </div>

            {{-- Totals --}}
            <div style="padding:14px 20px;border-top:1px solid var(--border);background:var(--bg-soft);display:flex;flex-direction:column;gap:6px;">
                @if($order->discount_total > 0)
                    <div style="display:flex;justify-content:space-between;font-size:0.875rem;">
                        <span style="color:var(--muted);">Subtotal</span>
                        <span>{{ number_format($order->subtotal, 2) }} {{ $order->currency }}</span>
                    </div>
                    <div style="display:flex;justify-content:space-between;font-size:0.875rem;color:var(--success,#22c55e);">
                        <span>Discount</span>
                        <span>-{{ number_format($order->discount_total, 2) }} {{ $order->currency }}</span>
                    </div>
                @endif
                @if($order->shipping_total > 0)
                    <div style="display:flex;justify-content:space-between;font-size:0.875rem;">
                        <span style="color:var(--muted);">Shipping</span>
                        <span>{{ number_format($order->shipping_total, 2) }} {{ $order->currency }}</span>
                    </div>
                @endif
                @if($order->tax_total > 0)
                    <div style="display:flex;justify-content:space-between;font-size:0.875rem;">
                        <span style="color:var(--muted);">Tax</span>
                        <span>{{ number_format($order->tax_total, 2) }} {{ $order->currency }}</span>
                    </div>
                @endif
                <div style="display:flex;justify-content:space-between;font-weight:800;font-size:1.05rem;padding-top:6px;border-top:1px solid var(--border);margin-top:2px;">
                    <span>Total</span>
                    <span>{{ number_format($order->grand_total, 2) }} {{ $order->currency }}</span>
                </div>
            </div>
        </div>

        {{-- Details row: billing + payment --}}
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;">

            {{-- Billing address --}}
            <div style="background:var(--bg-card,#13131f);border:1px solid var(--border);border-radius:var(--radius);padding:18px 20px;">
                <div style="font-weight:700;margin-bottom:12px;">Billing Address</div>
                @php $billing = $order->billing_address ?? []; @endphp
                <address style="font-style:normal;font-size:0.875rem;color:var(--muted);line-height:1.7;margin:0;">
                    @if(!empty($billing['name']))     <div style="color:var(--text);font-weight:600;">{{ $billing['name'] }}</div>@endif
                    @if(!empty($billing['address1'])) <div>{{ $billing['address1'] }}</div>@endif
                    @if(!empty($billing['city']))     <div>{{ ($billing['postcode'] ?? '').' '.$billing['city'] }}</div>@endif
                    @if(!empty($billing['country']))  <div>{{ $billing['country'] }}</div>@endif
                    @if(!empty($billing['phone']))    <div style="margin-top:6px;">{{ $billing['phone'] }}</div>@endif
                    @if(!empty($billing['email']))    <div>{{ $billing['email'] }}</div>@endif
                </address>
            </div>

            {{-- Payment info --}}
            <div style="background:var(--bg-card,#13131f);border:1px solid var(--border);border-radius:var(--radius);padding:18px 20px;">
                <div style="font-weight:700;margin-bottom:12px;">Payment</div>
                @php $payment = $order->payments->first(); @endphp
                @if($payment)
                    <div style="font-size:0.875rem;color:var(--muted);line-height:1.7;">
                        <div style="color:var(--text);font-weight:600;text-transform:capitalize;">
                            @if(optional($payment->provider)->driver === 'cod') Cash on Delivery
                            @elseif(optional($payment->provider)->driver === 'stripe') Stripe (Card)
                            @elseif(optional($payment->provider)->driver === 'payu') PayU
                            @elseif(optional($payment->provider)->driver === 'p24') Przelewy24
                            @elseif(optional($payment->provider)->driver === 'paypal') PayPal
                            @else {{ ucfirst(optional($payment->provider)->driver ?? '—') }}
                            @endif
                        </div>
                        <div style="margin-top:4px;">
                            Status:
                            <span style="font-weight:600;
                                color:{{ $order->payment_status === 'paid' ? 'var(--success,#22c55e)' : 'var(--warning,#f59e0b)' }}">
                                {{ ucfirst($order->payment_status) }}
                            </span>
                        </div>
                        @if($order->payment_status === 'unpaid' && optional($payment->provider)->driver === 'cod')
                            <div style="margin-top:8px;font-size:0.8rem;">You will pay when your order arrives.</div>
                        @endif
                    </div>
                @else
                    <div style="font-size:0.875rem;color:var(--muted);">No payment record yet.</div>
                @endif
            </div>
        </div>

        @if(!empty($billing['notes']))
            <div style="background:var(--bg-card,#13131f);border:1px solid var(--border);border-radius:var(--radius);padding:18px 20px;">
                <div style="font-weight:700;margin-bottom:8px;">Order Notes</div>
                <p style="margin:0;font-size:0.875rem;color:var(--muted);">{{ $billing['notes'] }}</p>
            </div>
        @endif

        {{-- Actions --}}
        <div style="display:flex;gap:12px;justify-content:center;padding-bottom:48px;">
            <a href="{{ route('shop.home') }}" class="btn btn-primary">
                <i class="bi bi-bag"></i> Continue Shopping
            </a>
            @auth
                <a href="{{ route('shop.home') }}" class="btn btn-ghost">
                    <i class="bi bi-list-ul"></i> View My Orders
                </a>
            @endauth
        </div>

    </div>
</div>

@endsection
