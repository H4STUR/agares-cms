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
            <span class="breadcrumb-current">Shop</span>
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

    {{-- Page header --}}
    <div style="margin-bottom:28px;">
        <h1 style="font-size:clamp(1.5rem,3vw,2rem);font-weight:800;margin-bottom:6px;">
            @if(request('search'))
                Results for "{{ request('search') }}"
            @elseif(request('category') && ($activeCategory = $data['ecommerceCategories']->firstWhere('slug', request('category'))))
                {{ $activeCategory->name }}
            @else
                All Products
            @endif
        </h1>
        <p style="color:var(--muted);font-size:0.9rem;">{{ $data['products']->total() }} products found</p>
    </div>

    {{-- Mobile filter backdrop --}}
    <div class="filter-backdrop" aria-hidden="true"></div>

    <div class="shop-layout">

        {{-- ===== SIDEBAR FILTERS ===== --}}
        <aside class="filter-sidebar" aria-label="Product filters">
            <div class="filter-sidebar-header">
                <h3><i class="bi bi-sliders"></i> Filters</h3>
                @if(request('category'))
                    <a href="{{ route('shop.home', request()->except('category')) }}" class="btn btn-ghost btn-sm">Clear</a>
                @endif
            </div>

            <div class="filter-group">
                <div class="filter-group-header">
                    <span class="filter-group-title">Category</span>
                    <span class="filter-group-toggle">▲</span>
                </div>
                <ul class="filter-list">
                    <li class="filter-item">
                        <a href="{{ route('shop.home') }}"
                           class="filter-item-left"
                           style="text-decoration:none;{{ !request('category') ? 'color:var(--primary);font-weight:600;' : '' }}">
                            All Products
                        </a>
                        {{-- <span class="filter-item-count">{{ $data['products']->total() }}</span> --}}
                    </li>
                    @foreach($data['ecommerceCategories'] as $category)
                        <li class="filter-item">
                            <a href="{{ route('shop.home') }}?category={{ $category->slug }}"
                               class="filter-item-left"
                               style="text-decoration:none;{{ request('category') === $category->slug ? 'color:var(--primary);font-weight:600;' : '' }}">
                                {{ $category->name }}
                            </a>
                            <span class="filter-item-count">{{ $category->products_count }}</span>
                        </li>
                    @endforeach
                </ul>
            </div>
        </aside>
        {{-- /SIDEBAR --}}

        {{-- ===== PRODUCTS MAIN ===== --}}
        <div>
            {{-- Toolbar --}}
            <div class="shop-toolbar">
                <div class="shop-toolbar-left">
                    <button class="btn btn-ghost btn-sm mobile-filter-btn">
                        <i class="bi bi-sliders"></i> Filters
                    </button>
                    <p class="result-count"><strong>{{ $data['products']->total() }}</strong> products found</p>
                </div>
                <div style="flex:1;max-width:380px;">
                    @include('pages.frontend.ecommerce.snippets.search-bar')
                </div>
            </div>

            {{-- Active filter tags --}}
            @php
                $activeCategory = request('category') ? $data['ecommerceCategories']->firstWhere('slug', request('category')) : null;
                $activeSearch   = request('search');
            @endphp
            @if($activeCategory || $activeSearch)
                <div style="display:flex;flex-wrap:wrap;gap:8px;margin-bottom:20px;">
                    @if($activeCategory)
                        <span style="padding:5px 12px;background:rgba(124,58,237,.12);border:1px solid rgba(124,58,237,.3);border-radius:99px;font-size:0.78rem;color:var(--primary);display:flex;align-items:center;gap:6px;">
                            {{ $activeCategory->name }}
                            <a href="{{ route('shop.home', request()->except('category')) }}" style="color:inherit;text-decoration:none;line-height:1;" aria-label="Remove category filter">×</a>
                        </span>
                    @endif
                    @if($activeSearch)
                        <span style="padding:5px 12px;background:rgba(124,58,237,.12);border:1px solid rgba(124,58,237,.3);border-radius:99px;font-size:0.78rem;color:var(--primary);display:flex;align-items:center;gap:6px;">
                            <i class="bi bi-search" style="font-size:0.7rem;"></i> "{{ $activeSearch }}"
                            <a href="{{ route('shop.home', request()->except('search')) }}" style="color:inherit;text-decoration:none;line-height:1;" aria-label="Clear search">×</a>
                        </span>
                    @endif
                </div>
            @endif

            {{-- Product Grid --}}
            <div class="products-grid" role="list">
                @forelse($data['products'] as $product)
                    <article class="product-card" role="listitem">

                        {{-- Image area --}}
                        <a href="{{ route('shop.product.show', $product->slug) }}" style="display:block;text-decoration:none;">
                            <div class="product-card-img">
                                <div style="width:100%;aspect-ratio:1;display:flex;align-items:center;justify-content:center;background:var(--bg-card,#13131f);">
                                    <i class="bi bi-box-seam" style="font-size:3rem;color:var(--muted);opacity:0.35;"></i>
                                </div>
                                @if($product->sale_price)
                                    <div class="product-badge-wrap">
                                        <span class="badge badge-danger">
                                            -{{ round((($product->base_price - $product->sale_price) / $product->base_price) * 100) }}%
                                        </span>
                                    </div>
                                @endif
                            </div>
                        </a>

                        {{-- Body --}}
                        <div class="product-card-body">
                            @if($product->categories->isNotEmpty())
                                <span class="product-card-category">{{ $product->categories->first()->name }}</span>
                            @endif

                            <a href="{{ route('shop.product.show', $product->slug) }}" class="product-card-title">
                                {{ $product->name }}
                            </a>

                            @if($product->short_description)
                                <p style="font-size:0.78rem;color:var(--muted);margin:4px 0 8px;line-height:1.5;">
                                    {{ Str::limit($product->short_description, 65) }}
                                </p>
                            @endif

                            {{-- Stock indicator --}}
                            @if($product->manage_stock)
                                <div style="margin-bottom:8px;">
                                    @if($product->is_in_stock)
                                        <span class="stock-indicator in-stock">
                                            <span class="stock-dot"></span>
                                            @if($product->stock !== null)
                                                {{ $product->stock }} in stock
                                            @else
                                                In stock
                                            @endif
                                        </span>
                                    @else
                                        <span class="stock-indicator out-stock">
                                            <span class="stock-dot" style="background:var(--danger);"></span>
                                            Out of stock
                                        </span>
                                    @endif
                                </div>
                            @endif

                            {{-- Price + Add to cart --}}
                            <div class="product-card-footer" style="flex-direction:column;align-items:stretch;gap:10px;">
                                <div class="product-price-wrap">
                                    @if($product->sale_price)
                                        <span class="product-price">${{ number_format($product->sale_price, 2) }}</span>
                                        <span class="product-price-old">${{ number_format($product->base_price, 2) }}</span>
                                    @else
                                        <span class="product-price">${{ number_format($product->base_price, 2) }}</span>
                                    @endif
                                </div>

                                <form method="POST" action="{{ route('shop.cart.add') }}">
                                    @csrf
                                    <input type="hidden" name="product_id" value="{{ $product->id }}">
                                    <div style="display:flex;align-items:center;gap:8px;">
                                        <div class="quantity-ctrl" style="flex-shrink:0;">
                                            <button type="button" class="qty-btn" data-qty="dec" aria-label="Decrease">−</button>
                                            <input class="qty-input" type="number" name="quantity" value="1" min="1" style="width:34px;" aria-label="Quantity">
                                            <button type="button" class="qty-btn" data-qty="inc" aria-label="Increase">+</button>
                                        </div>
                                        <button type="submit" class="btn btn-primary" style="flex:1;display:flex;align-items:center;justify-content:center;gap:5px;padding-left:10px;padding-right:10px;"
                                            @if($product->manage_stock && !$product->is_in_stock) disabled @endif>
                                            <i class="bi bi-bag-plus"></i> Add
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>

                    </article>
                @empty
                    <div style="grid-column:1/-1;">
                        <div class="empty-state">
                            <div class="empty-state-icon"><i class="bi bi-bag-x"></i></div>
                            <h3>No products found</h3>
                            <p>Try adjusting your filters or browse all products.</p>
                            <a href="{{ route('shop.home') }}" class="btn btn-primary">
                                <i class="bi bi-grid-fill"></i> Browse All Products
                            </a>
                        </div>
                    </div>
                @endforelse
            </div>
            {{-- /Product Grid --}}

            {{-- Pagination --}}
            <div style="margin-top:32px;">
                {{ $data['products']->links() }}
            </div>

        </div>
        {{-- /PRODUCTS MAIN --}}

    </div>
</div>

@endsection
