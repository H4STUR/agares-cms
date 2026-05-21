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
            @if($data['product']->categories->isNotEmpty())
                <svg class="breadcrumb-sep" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 18l6-6-6-6"/></svg>
                <a href="{{ route('shop.home') }}?category={{ $data['product']->categories->first()->slug }}">
                    {{ $data['product']->categories->first()->name }}
                </a>
            @endif
            <svg class="breadcrumb-sep" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 18l6-6-6-6"/></svg>
            <span class="breadcrumb-current">{{ $data['product']->name }}</span>
        </nav>
    </div>
</div>

<div class="container">

    {{-- Flash messages --}}
    @if(session('success'))
        <div style="padding:12px 16px;background:rgba(16,185,129,0.1);border:1px solid rgba(16,185,129,0.25);border-radius:var(--radius-sm);color:var(--success);margin-top:24px;display:flex;align-items:center;gap:8px;">
            <i class="bi bi-check-circle-fill"></i> {{ session('success') }}
        </div>
    @endif
    @if(session('error'))
        <div style="padding:12px 16px;background:rgba(239,68,68,0.1);border:1px solid rgba(239,68,68,0.25);border-radius:var(--radius-sm);color:var(--danger);margin-top:24px;display:flex;align-items:center;gap:8px;">
            <i class="bi bi-exclamation-circle-fill"></i> {{ session('error') }}
        </div>
    @endif

    {{-- ===== PRODUCT DETAIL ===== --}}
    <div class="product-detail">

        {{-- Gallery --}}
        <div class="product-gallery">
            <div class="gallery-main" aria-label="Product image">
                <div style="width:100%;height:100%;min-height:380px;display:flex;align-items:center;justify-content:center;background:var(--bg-card,#13131f);border-radius:var(--radius);">
                    <i class="bi bi-box-seam" style="font-size:5rem;color:var(--muted);opacity:0.25;"></i>
                </div>
            </div>
            <div class="gallery-thumbs" role="list" aria-label="Product images">
                <button class="gallery-thumb active" aria-label="View image 1">
                    <div style="width:100%;height:100%;display:flex;align-items:center;justify-content:center;background:var(--bg-card,#13131f);">
                        <i class="bi bi-image" style="font-size:1.2rem;color:var(--muted);opacity:0.4;"></i>
                    </div>
                </button>
            </div>
        </div>

        {{-- ===== PRODUCT INFO ===== --}}
        <div class="product-info">

            {{-- Brand / SKU / Stock --}}
            <div class="product-brand">
                @if($data['product']->sku)
                    <span class="badge badge-muted">SKU: {{ $data['product']->sku }}</span>
                @endif
                @if($data['product']->manage_stock)
                    @if($data['product']->is_in_stock)
                        <span class="stock-indicator in-stock">
                            <span class="stock-dot"></span>
                            In Stock
                            @if($data['product']->stock !== null)
                                — {{ $data['product']->stock }} left
                            @endif
                        </span>
                    @else
                        <span class="stock-indicator out-stock">
                            <span class="stock-dot" style="background:var(--danger);box-shadow:0 0 6px var(--danger);"></span>
                            Out of Stock
                        </span>
                    @endif
                @endif
            </div>

            <h1 class="product-detail-title">{{ $data['product']->name }}</h1>

            {{-- Pricing --}}
            <div class="product-pricing">
                @if($data['product']->sale_price)
                    <span class="price-current">${{ number_format($data['product']->sale_price, 2) }}</span>
                    <span class="price-old-lg">${{ number_format($data['product']->base_price, 2) }}</span>
                    <span class="price-save">
                        Save ${{ number_format($data['product']->base_price - $data['product']->sale_price, 2) }}
                        ({{ round((($data['product']->base_price - $data['product']->sale_price) / $data['product']->base_price) * 100) }}% off)
                    </span>
                @else
                    <span class="price-current">${{ number_format($data['product']->base_price, 2) }}</span>
                @endif
            </div>

            {{-- Short description --}}
            @if($data['product']->short_description)
                <p style="font-size:0.92rem;color:var(--text-soft);line-height:1.7;padding:16px;background:var(--bg-soft);border-radius:var(--radius-sm);border-left:3px solid var(--primary);">
                    {{ $data['product']->short_description }}
                </p>
            @endif

            {{-- Categories --}}
            @if($data['product']->categories->isNotEmpty())
                <div style="display:flex;flex-wrap:wrap;gap:6px;margin-bottom:4px;">
                    @foreach($data['product']->categories as $category)
                        <a href="{{ route('shop.home') }}?category={{ $category->slug }}"
                           style="padding:4px 12px;background:rgba(124,58,237,.1);border:1px solid rgba(124,58,237,.2);border-radius:99px;font-size:0.78rem;color:var(--primary);text-decoration:none;">
                            {{ $category->name }}
                        </a>
                    @endforeach
                </div>
            @endif

            {{-- Quantity + Add to Cart --}}
            <form method="POST" action="{{ route('shop.cart.add') }}">
                @csrf
                <input type="hidden" name="product_id" value="{{ $data['product']->id }}">

                <div class="quantity-wrap">
                    <div class="quantity-ctrl">
                        <button type="button" class="qty-btn" data-qty="dec" aria-label="Decrease">−</button>
                        <input class="qty-input" type="number" name="quantity" value="1" min="1"
                               @if($data['product']->manage_stock && $data['product']->stock !== null) max="{{ $data['product']->stock }}" @endif
                               aria-label="Quantity">
                        <button type="button" class="qty-btn" data-qty="inc" aria-label="Increase">+</button>
                    </div>
                </div>

                <div class="product-cta-row">
                    <button type="submit" class="btn btn-primary btn-xl" style="flex:1;"
                        @if($data['product']->manage_stock && !$data['product']->is_in_stock) disabled @endif>
                        <i class="bi bi-bag-plus"></i>
                        {{ $data['product']->manage_stock && !$data['product']->is_in_stock ? 'Out of Stock' : 'Add to Cart' }}
                    </button>
                    <a href="{{ route('shop.checkout') }}" class="btn btn-secondary btn-xl" style="flex:1;">
                        <i class="bi bi-lightning-fill"></i> Buy Now
                    </a>
                </div>
            </form>

            {{-- Trust Badges --}}
            <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:10px;margin-top:4px;">
                <div style="text-align:center;padding:12px;background:var(--bg-soft);border-radius:var(--radius-sm);border:1px solid var(--border);">
                    <i class="bi bi-truck" style="color:var(--primary);font-size:18px;"></i>
                    <p style="font-size:0.72rem;color:var(--muted);margin-top:4px;">Free Shipping</p>
                </div>
                <div style="text-align:center;padding:12px;background:var(--bg-soft);border-radius:var(--radius-sm);border:1px solid var(--border);">
                    <i class="bi bi-arrow-counterclockwise" style="color:var(--success);font-size:18px;"></i>
                    <p style="font-size:0.72rem;color:var(--muted);margin-top:4px;">30-Day Return</p>
                </div>
                <div style="text-align:center;padding:12px;background:var(--bg-soft);border-radius:var(--radius-sm);border:1px solid var(--border);">
                    <i class="bi bi-shield-check" style="color:#3b82f6;font-size:18px;"></i>
                    <p style="font-size:0.72rem;color:var(--muted);margin-top:4px;">Secure Payment</p>
                </div>
            </div>

        </div>
        {{-- /PRODUCT INFO --}}

    </div>

    {{-- ===== PRODUCT TABS ===== --}}
    @if($data['product']->description)
        <div class="product-tabs" data-tabs>
            <div class="tab-nav" role="tablist">
                <button class="tab-btn active" data-tab="description" role="tab" aria-selected="true">Description</button>
            </div>

            <div class="tab-panel active" data-tab-panel="description" role="tabpanel">
                <div style="color:var(--text-soft);line-height:1.8;">
                    {!! safe_html($data['product']->description) !!}
                </div>
            </div>
        </div>
    @endif

    {{-- Back to shop --}}
    <div style="margin-top:48px;margin-bottom:48px;text-align:center;">
        <a href="{{ route('shop.home') }}" class="btn btn-secondary">
            <i class="bi bi-arrow-left"></i> Back to Shop
        </a>
    </div>

</div>

@endsection
