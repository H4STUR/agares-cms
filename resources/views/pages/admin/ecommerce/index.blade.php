<x-app-layout>

  {{-- ── Header ── --}}
  <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
    <div>
      <h4 class="mb-0">{{ __('Ecommerce') }}</h4>
      <p class="text-muted mb-0 small">{{ __('Overview of your shop performance and configuration.') }}</p>
    </div>
    <div class="d-flex flex-wrap gap-2">
      <a class="btn btn-sm btn-primary" href="{{ route('admin.ecommerce.products.create') }}">
        <i class="bi bi-plus-lg me-1"></i>{{ __('Add Product') }}
      </a>
      <a class="btn btn-sm btn-outline-secondary" href="{{ route('admin.ecommerce.orders.index') }}">{{ __('All Orders') }}</a>
      <a class="btn btn-sm btn-outline-secondary" href="{{ route('admin.ecommerce.settings.index') }}">{{ __('Settings') }}</a>
    </div>
  </div>

  {{-- ── Stat Cards ── --}}
  @php
    $pending    = $orderStatusCounts->get('pending_payment', 0);
    $processing = $orderStatusCounts->get('processing', 0);
    $completed  = $orderStatusCounts->get('completed', 0);
    $totalOrders = $orderStatusCounts->sum();
    $currency   = $shopSettings->get('currency', 'USD');
    $revenueChange = $revenueLastMonth > 0
        ? round((($revenueThisMonth - $revenueLastMonth) / $revenueLastMonth) * 100, 1)
        : null;
  @endphp

  <div class="row g-3 mb-3">
    {{-- Revenue --}}
    <div class="col-6 col-xl-3">
      <div class="card h-100">
        <div class="card-body">
          <div class="d-flex align-items-center gap-3 mb-2">
            <div class="wh-48 bg-success bg-opacity-10 text-success rounded-3 d-flex align-items-center justify-content-center flex-shrink-0">
              <i class="bi bi-currency-dollar fs-5"></i>
            </div>
            <div class="overflow-hidden">
              <p class="mb-0 small text-muted text-truncate">{{ __('Revenue this month') }}</p>
              <h5 class="mb-0 fw-bold">{{ number_format($revenueThisMonth, 2) }} {{ $currency }}</h5>
            </div>
          </div>
          @if($revenueChange !== null)
            <p class="mb-0 small {{ $revenueChange >= 0 ? 'text-success' : 'text-danger' }}">
              <i class="bi bi-arrow-{{ $revenueChange >= 0 ? 'up' : 'down' }}-short"></i>
              {{ abs($revenueChange) }}% {{ __('vs last month') }}
            </p>
          @else
            <p class="mb-0 small text-muted">{{ __('No data for last month') }}</p>
          @endif
        </div>
      </div>
    </div>

    {{-- Orders --}}
    <div class="col-6 col-xl-3">
      <div class="card h-100">
        <div class="card-body">
          <div class="d-flex align-items-center gap-3 mb-2">
            <div class="wh-48 bg-primary bg-opacity-10 text-primary rounded-3 d-flex align-items-center justify-content-center flex-shrink-0">
              <i class="bi bi-cart3 fs-5"></i>
            </div>
            <div class="overflow-hidden">
              <p class="mb-0 small text-muted text-truncate">{{ __('Total orders') }}</p>
              <h5 class="mb-0 fw-bold">{{ number_format($totalOrders) }}</h5>
            </div>
          </div>
          <div class="d-flex gap-2 flex-wrap">
            @if($pending)
              <span class="badge text-bg-warning small">{{ $pending }} {{ __('pending') }}</span>
            @endif
            @if($processing)
              <span class="badge text-bg-info small">{{ $processing }} {{ __('processing') }}</span>
            @endif
            @if($completed)
              <span class="badge text-bg-success small">{{ $completed }} {{ __('completed') }}</span>
            @endif
            @if(!$pending && !$processing && !$completed)
              <span class="text-muted small">{{ __('No orders yet') }}</span>
            @endif
          </div>
        </div>
      </div>
    </div>

    {{-- Products --}}
    <div class="col-6 col-xl-3">
      <div class="card h-100">
        <div class="card-body">
          <div class="d-flex align-items-center gap-3 mb-2">
            <div class="wh-48 bg-info bg-opacity-10 text-info rounded-3 d-flex align-items-center justify-content-center flex-shrink-0">
              <i class="bi bi-box-seam fs-5"></i>
            </div>
            <div class="overflow-hidden">
              <p class="mb-0 small text-muted text-truncate">{{ __('Products') }}</p>
              <h5 class="mb-0 fw-bold">{{ number_format($productCount) }}</h5>
            </div>
          </div>
          @if($outOfStock > 0)
            <p class="mb-0 small text-danger">
              <i class="bi bi-exclamation-triangle-fill me-1"></i>{{ $outOfStock }} {{ __('out of stock') }}
            </p>
          @else
            <p class="mb-0 small text-muted">{{ __('All products in stock') }}</p>
          @endif
        </div>
      </div>
    </div>

    {{-- Pending attention --}}
    <div class="col-6 col-xl-3">
      <div class="card h-100 {{ $pending > 0 ? 'border-warning' : '' }}">
        <div class="card-body">
          <div class="d-flex align-items-center gap-3 mb-2">
            <div class="wh-48 {{ $pending > 0 ? 'bg-warning bg-opacity-10 text-warning' : 'bg-secondary bg-opacity-10 text-secondary' }} rounded-3 d-flex align-items-center justify-content-center flex-shrink-0">
              <i class="bi bi-hourglass-split fs-5"></i>
            </div>
            <div class="overflow-hidden">
              <p class="mb-0 small text-muted text-truncate">{{ __('Awaiting payment') }}</p>
              <h5 class="mb-0 fw-bold">{{ $pending }}</h5>
            </div>
          </div>
          @if($pending > 0)
            <a href="{{ route('admin.ecommerce.orders.index') }}?status=pending_payment" class="btn btn-sm btn-warning py-0 px-2">
              {{ __('Review') }}
            </a>
          @else
            <p class="mb-0 small text-muted">{{ __('Nothing awaiting payment') }}</p>
          @endif
        </div>
      </div>
    </div>
  </div>

  <div class="row g-3">

    {{-- ── Recent Orders ── --}}
    <div class="col-12 col-xl-7">
      <div class="card h-100">
        <div class="card-header d-flex justify-content-between align-items-center">
          <span class="fw-semibold">{{ __('Recent Orders') }}</span>
          <a href="{{ route('admin.ecommerce.orders.index') }}" class="btn btn-sm btn-outline-primary">{{ __('View all') }}</a>
        </div>
        <div class="card-body p-0">
          @if($recentOrders->isEmpty())
            <p class="text-muted text-center py-4 mb-0">{{ __('No orders yet.') }}</p>
          @else
            <div class="table-responsive">
              <table class="table align-middle mb-0">
                <thead class="table-light">
                  <tr>
                    <th class="ps-3">{{ __('Order') }}</th>
                    <th>{{ __('Status') }}</th>
                    <th>{{ __('Total') }}</th>
                    <th>{{ __('Date') }}</th>
                    <th class="pe-3"></th>
                  </tr>
                </thead>
                <tbody>
                  @foreach($recentOrders as $o)
                    @php
                      $badgeClass = match($o->status) {
                        'completed'       => 'text-bg-success',
                        'processing'      => 'text-bg-info',
                        'pending_payment' => 'text-bg-warning',
                        'cancelled','failed','refunded' => 'text-bg-danger',
                        default           => 'text-bg-secondary',
                      };
                    @endphp
                    <tr>
                      <td class="ps-3 fw-semibold">{{ $o->order_number }}</td>
                      <td><span class="badge {{ $badgeClass }}">{{ $o->status }}</span></td>
                      <td>{{ number_format($o->grand_total, 2) }} {{ $o->currency }}</td>
                      <td class="text-muted small">{{ optional($o->placed_at)->format('d M, H:i') }}</td>
                      <td class="pe-3 text-end">
                        <a href="{{ route('admin.ecommerce.orders.show', $o) }}" class="btn btn-sm btn-outline-secondary py-0">{{ __('View') }}</a>
                      </td>
                    </tr>
                  @endforeach
                </tbody>
              </table>
            </div>
          @endif
        </div>
      </div>
    </div>

    {{-- ── Right Column ── --}}
    <div class="col-12 col-xl-5 d-flex flex-column gap-3">

      {{-- Setup Health --}}
      <div class="card">
        <div class="card-header fw-semibold">{{ __('Shop Setup') }}</div>
        <div class="card-body p-0">
          <ul class="list-group list-group-flush">
            @php
              $checks = [
                [
                  'label' => __('Products added'),
                  'ok'    => $productCount > 0,
                  'link'  => route('admin.ecommerce.products.index'),
                ],
                [
                  'label' => __('Shipping method enabled'),
                  'ok'    => $shippingEnabled > 0,
                  'link'  => route('admin.ecommerce.shipping-methods.index'),
                ],
                [
                  'label' => __('Payment provider enabled'),
                  'ok'    => $paymentProviders->where('enabled', true)->isNotEmpty(),
                  'link'  => route('admin.ecommerce.payment-providers.index'),
                ],
                [
                  'label' => __('Tax rules configured'),
                  'ok'    => $taxRuleCount > 0,
                  'link'  => route('admin.ecommerce.tax-rules.index'),
                ],
                [
                  'label' => __('Admin email set'),
                  'ok'    => !empty($shopSettings->get('admin_email')),
                  'link'  => route('admin.ecommerce.settings.index'),
                ],
              ];
            @endphp
            @foreach($checks as $check)
              <li class="list-group-item d-flex justify-content-between align-items-center py-2 px-3">
                <span class="{{ $check['ok'] ? '' : 'text-danger fw-semibold' }}">
                  <i class="bi bi-{{ $check['ok'] ? 'check-circle-fill text-success' : 'exclamation-circle-fill text-danger' }} me-2"></i>
                  {{ $check['label'] }}
                </span>
                @if(!$check['ok'])
                  <a href="{{ $check['link'] }}" class="btn btn-sm btn-outline-danger py-0 px-2">{{ __('Fix') }}</a>
                @endif
              </li>
            @endforeach
          </ul>
        </div>
      </div>

      {{-- Payment Providers --}}
      <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
          <span class="fw-semibold">{{ __('Payment Providers') }}</span>
          <a href="{{ route('admin.ecommerce.payment-providers.index') }}" class="btn btn-sm btn-outline-secondary py-0 px-2">{{ __('Manage') }}</a>
        </div>
        <div class="card-body p-0">
          @if($paymentProviders->isEmpty())
            <p class="text-muted text-center py-3 mb-0 small">{{ __('No providers found. Visit the page once to seed defaults.') }}</p>
          @else
            <ul class="list-group list-group-flush">
              @foreach($paymentProviders as $provider)
                <li class="list-group-item d-flex justify-content-between align-items-center py-2 px-3">
                  <span class="small text-capitalize">{{ $provider->driver }}</span>
                  @if($provider->enabled)
                    <span class="badge text-bg-success">{{ __('Enabled') }}</span>
                  @else
                    <span class="badge text-bg-secondary">{{ __('Disabled') }}</span>
                  @endif
                </li>
              @endforeach
            </ul>
          @endif
        </div>
      </div>

    </div>
  </div>

  {{-- ── Key Settings & Quick Links ── --}}
  <div class="row g-3 mt-0">

    {{-- Key Settings --}}
    <div class="col-12 col-xl-6">
      <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
          <span class="fw-semibold">{{ __('Key Settings') }}</span>
          <a href="{{ route('admin.ecommerce.settings.index') }}" class="btn btn-sm btn-outline-secondary py-0 px-2">{{ __('Edit') }}</a>
        </div>
        <div class="card-body p-0">
          @php
            $keySettings = [
              ['key' => 'currency',                   'label' => __('Currency'),                    'type' => 'text'],
              ['key' => 'order_number_prefix',        'label' => __('Order number prefix'),         'type' => 'text'],
              ['key' => 'admin_email',                'label' => __('Admin notification email'),    'type' => 'text'],
              ['key' => 'guest_checkout',             'label' => __('Guest checkout'),              'type' => 'bool'],
              ['key' => 'allow_register_at_checkout', 'label' => __('Register at checkout'),        'type' => 'bool'],
              ['key' => 'checkout_require_phone',     'label' => __('Require phone at checkout'),   'type' => 'bool'],
            ];
          @endphp
          <ul class="list-group list-group-flush">
            @foreach($keySettings as $s)
              @php
                $val = $shopSettings->get($s['key']);
                $isEmpty = is_null($val) || $val === '';
              @endphp
              <li class="list-group-item d-flex justify-content-between align-items-center py-2 px-3">
                <span class="small text-muted">{{ $s['label'] }}</span>
                @if($isEmpty)
                  <span class="badge text-bg-warning">{{ __('Not set') }}</span>
                @elseif($s['type'] === 'bool')
                  @if((int)$val)
                    <span class="badge text-bg-success">{{ __('On') }}</span>
                  @else
                    <span class="badge text-bg-secondary">{{ __('Off') }}</span>
                  @endif
                @else
                  <span class="small fw-semibold">{{ $val }}</span>
                @endif
              </li>
            @endforeach
          </ul>
        </div>
      </div>
    </div>

    {{-- Quick Navigation --}}
    <div class="col-12 col-xl-6">
      <div class="card h-100">
        <div class="card-header fw-semibold">{{ __('Quick Navigation') }}</div>
        <div class="card-body">
          <div class="row g-2">
            @php
              $navLinks = [
                ['label' => __('Products'),         'route' => 'admin.ecommerce.products.index',          'icon' => 'bi-box-seam',        'color' => 'primary'],
                ['label' => __('Orders'),           'route' => 'admin.ecommerce.orders.index',            'icon' => 'bi-cart3',           'color' => 'success'],
                ['label' => __('Categories'),       'route' => 'admin.ecommerce.categories.index',        'icon' => 'bi-folder2',         'color' => 'info'],
                ['label' => __('Coupons'),          'route' => 'admin.ecommerce.coupons.index',           'icon' => 'bi-tag',             'color' => 'warning'],
                ['label' => __('Shipping'),         'route' => 'admin.ecommerce.shipping-methods.index',  'icon' => 'bi-truck',           'color' => 'secondary'],
                ['label' => __('Tax Rules'),        'route' => 'admin.ecommerce.tax-rules.index',         'icon' => 'bi-percent',         'color' => 'danger'],
                ['label' => __('Attributes'),       'route' => 'admin.ecommerce.attributes.index',        'icon' => 'bi-sliders',         'color' => 'secondary'],
                ['label' => __('Settings'),         'route' => 'admin.ecommerce.settings.index',          'icon' => 'bi-gear',            'color' => 'secondary'],
              ];
            @endphp
            @foreach($navLinks as $link)
              <div class="col-6 col-sm-3">
                <a href="{{ route($link['route']) }}" class="btn btn-outline-{{ $link['color'] }} w-100 d-flex flex-column align-items-center gap-1 py-2">
                  <i class="bi {{ $link['icon'] }} fs-5"></i>
                  <span class="small">{{ $link['label'] }}</span>
                  @if($link['route'] === 'admin.ecommerce.coupons.index' && $activeCoupons > 0)
                    <span class="badge text-bg-warning">{{ $activeCoupons }}</span>
                  @endif
                </a>
              </div>
            @endforeach
          </div>
        </div>
      </div>
    </div>

  </div>

</x-app-layout>
