{{-- 
you can get manu with

\App\Support\MenuTree::byId(1)

or

\App\Support\MenuTree::byName('Main Menu')
--}}
@php
  $items = $items ?? collect();
  $level = $level ?? 0; // 0 = top navbar ul, 1+ = dropdown content
@endphp

@foreach($items as $site)

  @php
    $children = $site->children ?? collect();
    $hasChildren = $children->isNotEmpty();

    $isActive = isset($data['site']) && ($data['site']->slug === $site->slug);
    $isRedirect = (bool) ($site->is_redirect ?? false) && !empty($site->redirect_url);

    $url = $isRedirect
      ? (preg_match('#^https?://#i', $site->redirect_url) ? $site->redirect_url : url('/' . ltrim($site->redirect_url, '/')))
      : url('/' . $site->slug);

    $openNewTab = $isRedirect && (bool) ($site->redirect_new_tab ?? false);
  @endphp

  {{-- TOP LEVEL: must be <li> inside <ul class="navbar-menu"> --}}
  @if($level === 0)
    @if($hasChildren)
      <li class="navbar-dropdown">
        <span class="navbar-dropdown-toggle">
          {{ $site->name }}
          <svg width="16" height="16" viewBox="0 0 16 16" fill="currentColor" aria-hidden="true">
            <path d="M4 6l4 4 4-4" stroke="currentColor" stroke-width="2" fill="none"/>
          </svg>
        </span>

        <div class="navbar-dropdown-menu" role="menu">
          {{-- children inside dropdown must be <a>, not <li> --}}
          @include('navigation.nav', ['items' => $children, 'level' => 1])
        </div>
      </li>
    @else
      <li>
        <a class="{{ $isActive ? 'active' : '' }}" href="{{ $url }}" @if($openNewTab) target="_blank" rel="noopener" @endif>
          {{ $site->name }}
        </a>
      </li>
    @endif

  {{-- DROPDOWN LEVELS: render as <a role="menuitem"> --}}
  @else
    @if($hasChildren)
      {{-- If you want nested dropdowns later, you can style this as a group --}}
      <div class="navbar-dropdown" style="position: relative;">
        <a class="{{ $isActive ? 'active' : '' }}" href="{{ $url }}" role="menuitem">
          {{ $site->name }}
        </a>

        {{-- nested children (optional) --}}
        <div class="navbar-dropdown-menu" role="menu" style="position: static; margin-top: .25rem;">
          @include('navigation.nav', ['items' => $children, 'level' => $level + 1])
        </div>
      </div>
    @else
      <a class="{{ $isActive ? 'active' : '' }}" href="{{ $url }}" @if($openNewTab) target="_blank" rel="noopener" @endif role="menuitem">
        {{ $site->name }}
      </a>
    @endif
  @endif
@endforeach

{{-- Cart + account icons only on shop routes; marketing pages stay clean --}}
@if (($settings['enable_ecommerce'] ?? false) && (request()->is('shop*') || request()->is('user/*')))
    <div class="navbar-actions d-flex align-items-center gap-2 ms-3">

      {{-- Account icon --}}
        @auth
            <a href="{{ route('admin.user.profile', auth()->user()) }}" class="navbar-action-btn" aria-label="Account">
                <i class="material-icons-outlined">account_circle</i>
            </a>
        @else
            <a href="{{ route('login') }}" class="navbar-action-btn" aria-label="Sign in">
                <i class="material-icons-outlined">account_circle</i>
            </a>
        @endauth

        {{-- Cart icon --}}
        @php $cartCount = collect(session()->get('cart', []))->sum(); @endphp
        <a href="{{ route('shop.cart') }}" class="navbar-action-btn position-relative" aria-label="Cart">
            <i class="material-icons-outlined">shopping_cart</i>
            @if($cartCount > 0)
                <span class="navbar-action-badge">{{ $cartCount > 99 ? '99+' : $cartCount }}</span>
            @endif
        </a>
        
    </div>
@endif
