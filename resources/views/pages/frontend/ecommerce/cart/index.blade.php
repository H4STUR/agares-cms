@extends('pages.frontend.base')

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/frontend/ecommerce/theme/assets/css/theme.css') }}">
@endpush

@push('scripts')
<script src="{{ asset('assets/frontend/ecommerce/theme/assets/js/theme.js') }}"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('.cart-qty-form').forEach(function (form) {
        form.querySelectorAll('.qty-btn').forEach(function (btn) {
            btn.addEventListener('click', function () {
                // Let theme.js update the input value first, then submit
                setTimeout(function () { form.submit(); }, 80);
            });
        });
    });
});
</script>
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
            <span class="breadcrumb-current">Shopping Cart</span>
        </nav>
    </div>
</div>

<div class="container section">

    {{-- Flash messages --}}
    @if(session('success'))
        <div style="padding:12px 16px;background:rgba(16,185,129,0.1);border:1px solid rgba(16,185,129,0.25);border-radius:var(--radius-sm);color:var(--success);margin-bottom:20px;display:flex;align-items:center;gap:8px;">
            <i class="bi bi-check-circle-fill"></i> {{ session('success') }}
        </div>
    @endif
    @if(session('error'))
        <div style="padding:12px 16px;background:rgba(239,68,68,0.1);border:1px solid rgba(239,68,68,0.25);border-radius:var(--radius-sm);color:var(--danger);margin-bottom:20px;display:flex;align-items:center;gap:8px;">
            <i class="bi bi-exclamation-circle-fill"></i> {{ session('error') }}
        </div>
    @endif

    <div style="display:flex;align-items:baseline;justify-content:space-between;margin-bottom:28px;flex-wrap:wrap;gap:12px;">
        <h1 style="font-size:clamp(1.5rem,3vw,2rem);font-weight:800;">Shopping Cart</h1>
        @if(!$data['cartItems']->isEmpty())
            <span style="color:var(--muted);font-size:0.9rem;">{{ $data['cartItems']->count() }} {{ Str::plural('item', $data['cartItems']->count()) }}</span>
        @endif
    </div>

    @if($data['cartItems']->isEmpty())

        {{-- Empty cart --}}
        <div class="empty-state" style="padding:48px 0;">
            <div class="empty-state-icon"><i class="bi bi-bag-x"></i></div>
            <h3>Your cart is empty</h3>
            <p>Looks like you haven't added anything yet. Start exploring our products!</p>
            <a href="{{ route('shop.home') }}" class="btn btn-primary">
                <i class="bi bi-grid-fill"></i> Browse Products
            </a>
        </div>

    @else

        <div class="cart-layout">

            {{-- ===== CART ITEMS ===== --}}
            <div>

                {{-- Table Header --}}
                <div class="cart-table-header" role="row">
                    <span role="columnheader">Product</span>
                    <span role="columnheader">Price</span>
                    <span role="columnheader">Quantity</span>
                    <span role="columnheader">Subtotal</span>
                    <span role="columnheader"><span class="sr-only">Remove</span></span>
                </div>

                {{-- Cart Items --}}
                @foreach($data['cartItems'] as $item)
                    <div class="cart-item" role="row">

                        {{-- Product info --}}
                        <div class="cart-item-product">
                            <div class="cart-item-img">
                                <div style="width:80px;height:80px;display:flex;align-items:center;justify-content:center;background:var(--bg-card,#13131f);border-radius:var(--radius-sm);">
                                    <i class="bi bi-box-seam" style="font-size:1.6rem;color:var(--muted);opacity:0.4;"></i>
                                </div>
                            </div>
                            <div class="cart-item-details">
                                <h4>
                                    <a href="{{ route('shop.product.show', $item['product']->slug) }}" style="color:var(--text);text-decoration:none;">
                                        {{ $item['product']->name }}
                                    </a>
                                </h4>
                                @if($item['product']->sku)
                                    <p class="variant">SKU: {{ $item['product']->sku }}</p>
                                @endif
                                <div style="display:flex;gap:6px;margin-top:8px;">
                                    <a href="{{ route('shop.product.show', $item['product']->slug) }}" class="btn btn-ghost btn-sm" style="padding:4px 10px;font-size:0.75rem;">
                                        <i class="bi bi-eye"></i> View
                                    </a>
                                </div>
                            </div>
                        </div>

                        {{-- Price --}}
                        <div class="cart-item-price">${{ number_format($item['price'], 2) }}</div>

                        {{-- Quantity --}}
                        <div class="cart-item-qty">
                            <form method="POST" action="{{ route('shop.cart.update', $item['product']->id) }}" class="cart-qty-form">
                                @csrf
                                @method('PATCH')
                                <div class="quantity-ctrl">
                                    <button type="button" class="qty-btn" data-qty="dec" aria-label="Decrease quantity">−</button>
                                    <input class="qty-input" type="number" name="quantity" value="{{ $item['quantity'] }}" min="1" aria-label="Quantity">
                                    <button type="button" class="qty-btn" data-qty="inc" aria-label="Increase quantity">+</button>
                                </div>
                            </form>
                        </div>

                        {{-- Subtotal --}}
                        <div class="cart-item-subtotal">${{ number_format($item['subtotal'], 2) }}</div>

                        {{-- Remove --}}
                        <form method="POST" action="{{ route('shop.cart.remove', $item['product']->id) }}">
                            @csrf
                            @method('DELETE')
                            <button class="cart-remove-btn" type="submit" aria-label="Remove item">
                                <i class="bi bi-trash3"></i>
                            </button>
                        </form>

                    </div>
                @endforeach

                {{-- Cart Actions --}}
                <div style="display:flex;justify-content:space-between;align-items:center;margin-top:16px;flex-wrap:wrap;gap:10px;">
                    <a href="{{ route('shop.home') }}" class="btn btn-ghost">
                        <i class="bi bi-arrow-left"></i> Continue Shopping
                    </a>
                    <form method="POST" action="{{ route('shop.cart.clear') }}">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-ghost" onclick="return confirm('Clear entire cart?')">
                            <i class="bi bi-trash3"></i> Clear Cart
                        </button>
                    </form>
                </div>

            </div>

            {{-- ===== CART SUMMARY ===== --}}
            <aside class="cart-summary" aria-label="Order summary">
                <h3>Order Summary</h3>

                <div class="summary-divider"></div>

                <div>
                    <div class="summary-row">
                        <span>Subtotal ({{ $data['cartItems']->count() }} {{ Str::plural('item', $data['cartItems']->count()) }})</span>
                        <span>${{ number_format($data['cartTotal'], 2) }}</span>
                    </div>
                    <div class="summary-row">
                        <span>Shipping</span>
                        <span style="color:var(--success);font-weight:600;">Calculated at checkout</span>
                    </div>
                </div>

                <div class="summary-divider"></div>

                <div class="summary-row summary-total">
                    <span>Total</span>
                    <span style="font-size:1.25rem;background:var(--primary-grad);-webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text;">${{ number_format($data['cartTotal'], 2) }}</span>
                </div>

                <a href="{{ route('shop.checkout') }}" class="btn btn-primary btn-xl btn-full">
                    <i class="bi bi-lock-fill"></i> Proceed to Checkout
                </a>

                <div style="text-align:center;">
                    <p style="font-size:0.75rem;color:var(--muted);margin-bottom:8px;">Secure checkout — all payments encrypted</p>
                    <div class="payment-methods" style="justify-content:center;">
                        <span class="payment-method">VISA</span>
                        <span class="payment-method">MC</span>
                        <span class="payment-method">AMEX</span>
                        <span class="payment-method">PayPal</span>
                    </div>
                </div>

                <div style="background:rgba(16,185,129,0.08);border:1px solid rgba(16,185,129,0.2);border-radius:var(--radius-sm);padding:12px;">
                    <p style="font-size:0.82rem;color:var(--success);display:flex;align-items:center;gap:6px;margin:0;">
                        <i class="bi bi-truck"></i>
                        <span>Free shipping on qualifying orders</span>
                    </p>
                </div>

            </aside>

        </div>

    @endif

</div>

@endsection
