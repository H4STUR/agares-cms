{{--
    Shop Search Bar Snippet
    Props (all optional — reads from request() by default):
      $searchQuery  — current search string (defaults to request('search'))
      $placeholder  — input placeholder text
--}}
@php
    $searchQuery  = $searchQuery  ?? request('search', '');
    $placeholder  = $placeholder  ?? 'Search products…';
@endphp

<form method="GET" action="{{ route('shop.home') }}" role="search" style="width:100%;">
    @if(request('category'))
        <input type="hidden" name="category" value="{{ request('category') }}">
    @endif

    <div style="display:flex;align-items:center;gap:0;background:var(--bg-card);border:1px solid var(--border);border-radius:var(--radius-sm);overflow:hidden;transition:border-color .2s;">
        <label for="shop-search-input" class="visually-hidden">Search products</label>

        <span style="padding:0 12px;color:var(--muted);display:flex;align-items:center;flex-shrink:0;">
            <i class="bi bi-search" style="font-size:0.95rem;"></i>
        </span>

        <input
            id="shop-search-input"
            type="search"
            name="search"
            value="{{ $searchQuery }}"
            placeholder="{{ $placeholder }}"
            autocomplete="off"
            style="flex:1;border:none;background:transparent;padding:10px 0;font-size:0.9rem;color:var(--text);outline:none;min-width:0;"
            onfocus="this.closest('div').style.borderColor='var(--primary)'"
            onblur="this.closest('div').style.borderColor='var(--border)'"
        >

        @if($searchQuery)
            <a href="{{ route('shop.home', request()->except('search')) }}"
               title="Clear search"
               style="padding:0 10px;color:var(--muted);display:flex;align-items:center;flex-shrink:0;text-decoration:none;font-size:1rem;line-height:1;"
               aria-label="Clear search">
                <i class="bi bi-x-lg"></i>
            </a>
        @endif

        <button type="submit"
                style="padding:0 16px;height:100%;background:var(--primary);color:#fff;border:none;cursor:pointer;font-size:0.85rem;font-weight:600;white-space:nowrap;display:flex;align-items:center;gap:5px;transition:background .2s;align-self:stretch;"
                onmouseover="this.style.background='var(--primary-dark,#6d28d9)'"
                onmouseout="this.style.background='var(--primary)'">
            Search
        </button>
    </div>
</form>
