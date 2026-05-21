@extends('pages.frontend.base')

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/frontend/ecommerce/theme/assets/css/theme.css') }}">
@endpush

@push('scripts')
<script src="{{ asset('assets/frontend/ecommerce/theme/assets/js/theme.js') }}"></script>
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
            <a href="{{ route('shop.cart') }}">Cart</a>
            <svg class="breadcrumb-sep" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 18l6-6-6-6"/></svg>
            <span class="breadcrumb-current">Checkout</span>
        </nav>
    </div>
</div>

<div class="container section">

    {{-- Flash messages --}}
    @if(session('error'))
        <div style="padding:12px 16px;background:rgba(239,68,68,0.1);border:1px solid rgba(239,68,68,0.25);border-radius:var(--radius-sm);color:var(--danger);margin-bottom:20px;display:flex;align-items:center;gap:8px;">
            <i class="bi bi-exclamation-circle-fill"></i> {{ session('error') }}
        </div>
    @endif

    <div class="checkout-layout">

        {{-- ===== CHECKOUT FORMS ===== --}}
        <div>

            <form method="POST" action="{{ route('shop.checkout.store') }}">
                @csrf

                {{-- Account (guests only) --}}
                @guest
                @if($data['guest_checkout'])
                <div class="checkout-card"
                     x-data="{ mode: '{{ old('create_account') === '1' ? 'register' : 'guest' }}' }">
                    <h3><i class="bi bi-person-circle" style="color:var(--primary);"></i> Account</h3>

                    @if($data['allow_register_at_checkout'])
                    <div style="display:flex;gap:24px;margin-bottom:16px;flex-wrap:wrap;">
                        <label style="display:flex;align-items:center;gap:8px;cursor:pointer;font-weight:500;">
                            <input type="radio" name="_account_mode" value="guest"
                                   x-model="mode"
                                   style="accent-color:var(--primary);width:16px;height:16px;">
                            Continue as guest
                        </label>
                        <label style="display:flex;align-items:center;gap:8px;cursor:pointer;font-weight:500;">
                            <input type="radio" name="_account_mode" value="register"
                                   x-model="mode"
                                   style="accent-color:var(--primary);width:16px;height:16px;">
                            Create an account
                        </label>
                    </div>
                    @else
                    <p style="font-size:0.875rem;color:var(--muted);margin-bottom:4px;">
                        Checking out as a guest.
                        <a href="{{ route('login') }}" style="color:var(--primary);">Log in</a> to track your orders.
                    </p>
                    @endif

                    <input type="hidden" name="create_account" :value="mode === 'register' ? '1' : '0'">

                    <div x-show="mode === 'register'" x-cloak
                         style="display:flex;flex-direction:column;gap:14px;margin-top:4px;">
                        <p style="font-size:0.8rem;color:var(--muted);margin:0;">
                            <i class="bi bi-info-circle"></i>
                            Your account will be created using the email address you enter below.
                        </p>
                        <div class="form-row">
                            <div class="form-group">
                                <label class="form-label">Password <span style="color:var(--danger);">*</span></label>
                                <input class="form-control @error('password') is-invalid @enderror"
                                       name="password" type="password" autocomplete="new-password"
                                       placeholder="Min. 8 characters"
                                       :required="mode === 'register'">
                                @error('password')
                                    <span style="font-size:0.8rem;color:var(--danger);margin-top:4px;display:block;">{{ $message }}</span>
                                @enderror
                            </div>
                            <div class="form-group">
                                <label class="form-label">Confirm Password <span style="color:var(--danger);">*</span></label>
                                <input class="form-control"
                                       name="password_confirmation" type="password" autocomplete="new-password"
                                       placeholder="Repeat password"
                                       :required="mode === 'register'">
                            </div>
                        </div>
                    </div>
                </div>
                @endif
                @endguest

                {{-- Contact Info --}}
                <div class="checkout-card">
                    <h3><i class="bi bi-person" style="color:var(--primary);"></i> Contact Information</h3>
                    <div style="display:flex;flex-direction:column;gap:14px;">
                        <div class="form-row">
                            <div class="form-group">
                                <label class="form-label" for="first_name">First Name <span style="color:var(--danger);">*</span></label>
                                <input class="form-control @error('first_name') is-invalid @enderror"
                                       id="first_name" name="first_name" type="text"
                                       placeholder="John"
                                       value="{{ old('first_name', auth()->user()?->name) }}"
                                       autocomplete="given-name" required>
                                @error('first_name')
                                    <span style="font-size:0.8rem;color:var(--danger);margin-top:4px;display:block;">{{ $message }}</span>
                                @enderror
                            </div>
                            <div class="form-group">
                                <label class="form-label" for="last_name">Last Name <span style="color:var(--danger);">*</span></label>
                                <input class="form-control @error('last_name') is-invalid @enderror"
                                       id="last_name" name="last_name" type="text"
                                       placeholder="Doe"
                                       value="{{ old('last_name') }}"
                                       autocomplete="family-name" required>
                                @error('last_name')
                                    <span style="font-size:0.8rem;color:var(--danger);margin-top:4px;display:block;">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="form-label" for="email">Email Address <span style="color:var(--danger);">*</span></label>
                            <input class="form-control @error('email') is-invalid @enderror"
                                   id="email" name="email" type="email"
                                   placeholder="john@example.com"
                                   value="{{ old('email', auth()->user()?->email) }}"
                                   autocomplete="email" required>
                            @error('email')
                                <span style="font-size:0.8rem;color:var(--danger);margin-top:4px;display:block;">{{ $message }}</span>
                            @enderror
                        </div>
                        <div class="form-group">
                            <label class="form-label" for="phone">Phone Number</label>
                            <input class="form-control @error('phone') is-invalid @enderror"
                                   id="phone" name="phone" type="tel"
                                   placeholder="+1 (555) 000-0000"
                                   value="{{ old('phone') }}"
                                   autocomplete="tel">
                            @error('phone')
                                <span style="font-size:0.8rem;color:var(--danger);margin-top:4px;display:block;">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                </div>

                {{-- Shipping Address --}}
                <div class="checkout-card">
                    <h3><i class="bi bi-geo-alt" style="color:var(--primary);"></i> Shipping Address</h3>
                    <div style="display:flex;flex-direction:column;gap:14px;">
                        <div class="form-group">
                            <label class="form-label" for="address">Street Address <span style="color:var(--danger);">*</span></label>
                            <input class="form-control @error('address') is-invalid @enderror"
                                   id="address" name="address" type="text"
                                   placeholder="123 Main Street"
                                   value="{{ old('address') }}"
                                   autocomplete="street-address" required>
                            @error('address')
                                <span style="font-size:0.8rem;color:var(--danger);margin-top:4px;display:block;">{{ $message }}</span>
                            @enderror
                        </div>
                        <div class="form-row">
                            <div class="form-group">
                                <label class="form-label" for="city">City <span style="color:var(--danger);">*</span></label>
                                <input class="form-control @error('city') is-invalid @enderror"
                                       id="city" name="city" type="text"
                                       placeholder="New York"
                                       value="{{ old('city') }}"
                                       autocomplete="address-level2" required>
                                @error('city')
                                    <span style="font-size:0.8rem;color:var(--danger);margin-top:4px;display:block;">{{ $message }}</span>
                                @enderror
                            </div>
                            <div class="form-group">
                                <label class="form-label" for="postal_code">Postal Code <span style="color:var(--danger);">*</span></label>
                                <input class="form-control @error('postal_code') is-invalid @enderror"
                                       id="postal_code" name="postal_code" type="text"
                                       placeholder="10001"
                                       value="{{ old('postal_code') }}"
                                       autocomplete="postal-code" required>
                                @error('postal_code')
                                    <span style="font-size:0.8rem;color:var(--danger);margin-top:4px;display:block;">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="form-label" for="country">Country <span style="color:var(--danger);">*</span></label>
                            <input class="form-control @error('country') is-invalid @enderror"
                                   id="country" name="country" type="text"
                                   placeholder="United States"
                                   value="{{ old('country') }}"
                                   autocomplete="country-name" required>
                            @error('country')
                                <span style="font-size:0.8rem;color:var(--danger);margin-top:4px;display:block;">{{ $message }}</span>
                            @enderror
                        </div>
                        <div class="form-group">
                            <label class="form-label" for="notes">Order Notes <span style="color:var(--muted);font-weight:400;">(optional)</span></label>
                            <textarea class="form-control" id="notes" name="notes" rows="3"
                                      placeholder="Any special instructions for your order…">{{ old('notes') }}</textarea>
                        </div>
                    </div>
                </div>

                {{-- Payment Method --}}
                <div class="checkout-card">
                    <h3><i class="bi bi-credit-card" style="color:var(--primary);"></i> Payment Method</h3>

                    @error('payment_method')
                        <div style="padding:10px 14px;background:rgba(239,68,68,0.1);border:1px solid rgba(239,68,68,0.25);border-radius:var(--radius-sm);color:var(--danger);margin-bottom:14px;font-size:0.875rem;">
                            {{ $message }}
                        </div>
                    @enderror

                    @if($data['providers']->isEmpty())
                        <p style="color:var(--muted);font-size:0.875rem;">No payment methods are currently available. Please try again later.</p>
                    @else
                        <div style="display:flex;flex-direction:column;gap:10px;">
                            @foreach($data['providers'] as $provider)
                                <label style="display:flex;align-items:center;gap:12px;padding:14px 16px;border:1px solid var(--border);border-radius:var(--radius-sm);cursor:pointer;transition:border-color .15s;"
                                       x-data
                                       :style="$el.querySelector('input').checked ? 'border-color:var(--primary);background:rgba(var(--primary-rgb,99,102,241),.05)' : ''">
                                    <input type="radio" name="payment_method" value="{{ $provider->driver }}"
                                           {{ old('payment_method') === $provider->driver || ($loop->first && !old('payment_method')) ? 'checked' : '' }}
                                           style="accent-color:var(--primary);width:16px;height:16px;flex-shrink:0;">
                                    <span style="font-weight:600;text-transform:capitalize;">
                                        @if($provider->driver === 'cod') Cash on Delivery
                                        @elseif($provider->driver === 'stripe') Stripe (Card)
                                        @elseif($provider->driver === 'payu') PayU
                                        @elseif($provider->driver === 'p24') Przelewy24
                                        @elseif($provider->driver === 'paypal') PayPal
                                        @else {{ ucfirst($provider->driver) }}
                                        @endif
                                    </span>
                                    @if($provider->driver === 'cod')
                                        <span style="margin-left:auto;font-size:0.78rem;color:var(--muted);">Pay when you receive</span>
                                    @endif
                                </label>
                            @endforeach
                        </div>
                    @endif
                </div>

                {{-- Place Order --}}
                <div class="checkout-card">
                    <button class="btn btn-primary btn-xl btn-full" type="submit" {{ $data['providers']->isEmpty() ? 'disabled' : '' }}>
                        <i class="bi bi-lock-fill"></i> Place Order — {{ number_format($data['cartTotal'], 2) }} {{ \App\Models\Ecommerce\Setting::where('key','currency')->value('value') ?? 'PLN' }}
                    </button>
                    <p style="font-size:0.75rem;color:var(--muted);text-align:center;margin-top:12px;display:flex;align-items:center;justify-content:center;gap:5px;">
                        <i class="bi bi-shield-check" style="color:var(--success);"></i>
                        Your information is encrypted and secure.
                    </p>
                </div>

            </form>

        </div>

        {{-- ===== ORDER SUMMARY ===== --}}
        <aside class="checkout-order-summary" aria-label="Order summary">
            <h3>Order Summary</h3>

            {{-- Products --}}
            <div class="checkout-product-list">
                @foreach($data['cartItems'] as $item)
                    <div class="checkout-product-item">
                        <div class="checkout-product-thumb">
                            <div style="width:64px;height:64px;display:flex;align-items:center;justify-content:center;background:var(--bg-card,#13131f);border-radius:var(--radius-sm);">
                                <i class="bi bi-box-seam" style="font-size:1.4rem;color:var(--muted);opacity:0.4;"></i>
                            </div>
                            <span class="qty-badge">{{ $item['quantity'] }}</span>
                        </div>
                        <div class="checkout-product-info">
                            <h5>{{ $item['product']->name }}</h5>
                            @if($item['product']->sku)
                                <span class="variant">SKU: {{ $item['product']->sku }}</span>
                            @endif
                        </div>
                        <span class="checkout-product-price">${{ number_format($item['subtotal'], 2) }}</span>
                    </div>
                @endforeach
            </div>

            {{-- Totals --}}
            <div style="display:flex;flex-direction:column;gap:8px;padding-block:16px;border-block:1px solid var(--border);">
                <div class="summary-row">
                    <span>Subtotal</span>
                    <span>${{ number_format($data['cartTotal'], 2) }}</span>
                </div>
                <div class="summary-row">
                    <span>Shipping</span>
                    <span style="color:var(--muted);">Calculated at next step</span>
                </div>
            </div>

            <div class="summary-row summary-total" style="padding-top:8px;">
                <span>Total</span>
                <span style="font-size:1.3rem;font-weight:900;background:var(--primary-grad);-webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text;">
                    ${{ number_format($data['cartTotal'], 2) }}
                </span>
            </div>

            <a href="{{ route('shop.cart') }}" class="btn btn-ghost btn-full" style="margin-top:8px;">
                <i class="bi bi-arrow-left"></i> Edit Cart
            </a>

            {{-- Trust signals --}}
            <div style="display:flex;flex-direction:column;gap:8px;padding-top:16px;border-top:1px solid var(--border);">
                <div style="display:flex;align-items:center;gap:8px;font-size:0.8rem;color:var(--muted);">
                    <i class="bi bi-shield-check" style="color:var(--success);"></i> 256-bit SSL encryption
                </div>
                <div style="display:flex;align-items:center;gap:8px;font-size:0.8rem;color:var(--muted);">
                    <i class="bi bi-arrow-counterclockwise" style="color:#3b82f6;"></i> 30-day hassle-free returns
                </div>
                <div style="display:flex;align-items:center;gap:8px;font-size:0.8rem;color:var(--muted);">
                    <i class="bi bi-truck" style="color:var(--primary);"></i> Free standard shipping available
                </div>
            </div>

        </aside>

    </div>
</div>

@endsection
