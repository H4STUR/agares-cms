<x-app-layout>

<!--breadcrumb-->
<div class="page-breadcrumb d-none d-sm-flex align-items-center mb-3">
    <div class="breadcrumb-title pe-3">Analytics</div>
    {{-- <div class="ps-3">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0 p-0">
                <li class="breadcrumb-item">
                </li>
                <li class="breadcrumb-item active">Analytics</li>
            </ol>
        </nav>
    </div> --}}
    {{-- <div class="ms-auto">
        <div class="btn-group">
            <button type="button" class="btn btn-primary">Settings</button>
            <button type="button" class="btn btn-primary split-bg-primary dropdown-toggle dropdown-toggle-split" data-bs-toggle="dropdown">	<span class="visually-hidden">Toggle Dropdown</span>
            </button>
            <div class="dropdown-menu dropdown-menu-right dropdown-menu-lg-end">	<a class="dropdown-item" href="javascript:;">Action</a>
                <a class="dropdown-item" href="javascript:;">Another action</a>
                <a class="dropdown-item" href="javascript:;">Something else here</a>
                <div class="dropdown-divider"></div>
                <a class="dropdown-item" href="javascript:;">Separated link</a>
            </div>
        </div>
    </div> --}}
</div>

<div class="row">
  {{-- Big chart --}}
  <div class="col-12 col-xl-9 d-flex">
    <div class="card rounded-4 w-100">
      <div class="card-body">
        <div class="d-flex align-items-start justify-content-between mb-3">
          <div>
            <h5 class="mb-0 fw-bold">Website traffic</h5>
          </div>
        </div>

        <div id="gaTrafficChart"></div>

      </div>
    </div>
  </div>

  {{-- Small live counter --}}
  <div class="col-12 col-xl-3 d-flex">
    <div class="card rounded-4 w-100">
      <div class="card-body d-flex flex-column align-items-center justify-content-center">
        <div class="d-flex flex-column align-items-center justify-content-center text-center gap-2 py-2">

          <a href="javascript:;"
            class="mb-1 wh-64 bg-success bg-opacity-10 text-success rounded-circle d-flex align-items-center justify-content-center">
            <i class="material-icons-outlined font-22">groups</i>
          </a>

          <div class="d-flex align-items-baseline justify-content-center gap-2">
            <h2 class="mb-0 fw-bold" id="realtimeUsers">
              {{ !empty($realtime['ok']) ? (int) $realtime['activeUsers'] : 0 }}
            </h2>
          </div>

          <p class="mb-0 fw-semibold">Active users</p>
          <small class="text-muted">Realtime (last 30 min)</small>

          <div id="realtimeStatus"
              class="mt-2 small px-3 py-1 rounded-pill
              {{ empty($realtime['ok']) ? 'bg-warning bg-opacity-10 text-warning' : 'bg-success bg-opacity-10 text-success' }}">
            @if(empty($realtime['ok']))
              {{ $realtime['error'] ?? 'Realtime not available.' }}
            @else
              Updated just now
            @endif
          </div>

        </div>
      </div>
    </div>
  </div>
</div>

  <!--end breadcrumb-->

@if (($settings['enable_ecommerce'] ?? false) && !empty($ecommerce))
  @php
    $cur = $ecommerce['currency'];
    $money = fn ($v) => number_format((float) $v, 2) . ' ' . $cur;
    $weekDelta = $ecommerce['weekDelta'];
    $usersDelta = $ecommerce['usersDelta'];
    $salesYTDDelta = $ecommerce['salesYTDDelta'];
  @endphp
  <!--breadcrumb-->
  <div class="page-breadcrumb d-none d-sm-flex align-items-center mb-3">
      <div class="breadcrumb-title pe-3">eCommerce</div>
  </div>
  <!--end breadcrumb-->

  <div class="row">
    <div class="col-12 col-xl-4 d-flex">
        <div class="card rounded-4 w-100">
          <div class="card-body">
            <div class="d-flex align-items-center gap-3 mb-2">
              <div class="">
                <h2 class="mb-0">{{ $money($ecommerce['avgWeeklySales']) }}</h2>
              </div>
              @if (!is_null($weekDelta))
                @php
                  $up = $weekDelta >= 0;
                  $cls = $up ? 'bg-success text-success' : 'bg-danger text-danger';
                  $arrow = $up ? 'arrow_upward' : 'arrow_downward';
                @endphp
                <div class="">
                  <p class="dash-lable d-flex align-items-center gap-1 rounded mb-0 {{ $cls }} bg-opacity-10">
                    <span class="material-icons-outlined fs-6">{{ $arrow }}</span>{{ number_format(abs($weekDelta), 1) }}%
                  </p>
                </div>
              @endif
            </div>
            <p class="mb-0">Average Daily Sales (last 7 days)</p>
            <div id="ecomAvgSales"></div>
          </div>
        </div>
    </div>
    <div class="col-12 col-xl-8 d-flex">
      <div class="card rounded-4 w-100">
        <div class="card-body">
          <div class="d-flex align-items-center justify-content-around flex-wrap gap-4 p-4">
            <a href="{{ route('admin.ecommerce.orders.index') }}" class="d-flex flex-column align-items-center justify-content-center gap-2 text-decoration-none text-reset">
              <span class="mb-2 wh-48 bg-primary bg-opacity-10 text-primary rounded-circle d-flex align-items-center justify-content-center">
                <i class="material-icons-outlined">shopping_cart</i>
              </span>
              <h3 class="mb-0">{{ number_format($ecommerce['totalOrders']) }}</h3>
              <p class="mb-0">Orders</p>
            </a>
            <div class="vr"></div>
            <div class="d-flex flex-column align-items-center justify-content-center gap-2">
              <span class="mb-2 wh-48 bg-success bg-opacity-10 text-success rounded-circle d-flex align-items-center justify-content-center">
                <i class="material-icons-outlined">print</i>
              </span>
              <h3 class="mb-0">{{ $money($ecommerce['totalIncome']) }}</h3>
              <p class="mb-0">Income</p>
            </div>
            <div class="vr"></div>
            <a href="{{ route('admin.ecommerce.orders.index', ['status' => 'pending_payment']) }}" class="d-flex flex-column align-items-center justify-content-center gap-2 text-decoration-none text-reset">
              <span class="mb-2 wh-48 bg-danger bg-opacity-10 text-danger rounded-circle d-flex align-items-center justify-content-center">
                <i class="material-icons-outlined">notifications</i>
              </span>
              <h3 class="mb-0">{{ number_format($ecommerce['pendingOrders']) }}</h3>
              <p class="mb-0">Pending Orders</p>
            </a>
            <div class="vr"></div>
            <div class="d-flex flex-column align-items-center justify-content-center gap-2">
              <span class="mb-2 wh-48 bg-info bg-opacity-10 text-info rounded-circle d-flex align-items-center justify-content-center">
                <i class="material-icons-outlined">payment</i>
              </span>
              <h3 class="mb-0">{{ $money($ecommerce['totalPayments']) }}</h3>
              <p class="mb-0">Captured Payments</p>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div><!--end row-->
  
  {{-- <div class="row">
    <div class="col-12 col-xl-5 col-xxl-4 d-flex">
      <div class="card rounded-4 w-100 shadow-none bg-transparent border-0">
          <div class="card-body p-0">
            <div class="row g-4">
              <div class="col-12 col-xl-6 d-flex">
                <div class="card mb-0 rounded-4 w-100">
                  <div class="card-body">
                    <div class="d-flex align-items-start justify-content-between mb-3">
                      <div class="">
                        <h4 class="mb-0">97.4K</h4>
                        <p class="mb-0">Total Users</p>
                      </div>
                      <div class="dropdown">
                        <a href="javascript:;" class="dropdown-toggle-nocaret options dropdown-toggle"
                          data-bs-toggle="dropdown">
                          <span class="material-icons-outlined fs-5">more_vert</span>
                        </a>
                        <ul class="dropdown-menu">
                          <li><a class="dropdown-item" href="javascript:;">Action</a></li>
                          <li><a class="dropdown-item" href="javascript:;">Another action</a></li>
                          <li><a class="dropdown-item" href="javascript:;">Something else here</a></li>
                        </ul>
                      </div>
                    </div>
                    <div class="chart-container2">
                      <div id="chart3"></div>
                    </div>
                    <div class="text-center">
                    <p class="mb-0"><span class="text-success me-1">12.5%</span> from last month</p>
                  </div>
                  </div>
                </div>
              </div>
              <div class="col-12 col-xl-6 d-flex">
              <div class="card mb-0 rounded-4 w-100">
                <div class="card-body">
                  <div class="d-flex align-items-start justify-content-between mb-1">
                    <div class="">
                      <h4 class="mb-0">42.5K</h4>
                      <p class="mb-0">Active Users</p>
                    </div>
                    <div class="dropdown">
                      <a href="javascript:;" class="dropdown-toggle-nocaret options dropdown-toggle"
                        data-bs-toggle="dropdown">
                        <span class="material-icons-outlined fs-5">more_vert</span>
                      </a>
                      <ul class="dropdown-menu">
                        <li><a class="dropdown-item" href="javascript:;">Action</a></li>
                        <li><a class="dropdown-item" href="javascript:;">Another action</a></li>
                        <li><a class="dropdown-item" href="javascript:;">Something else here</a></li>
                      </ul>
                    </div>
                  </div>
                  <div class="chart-container2">
                    <div id="chart2"></div>
                  </div>
                  <div class="text-center">
                    <p class="mb-0">24K users increased from last month</p>
                  </div>
                </div>
              </div>
            </div>
              <div class="col-12 col-xl-12">
              <div class="card rounded-4 mb-0">
                <div class="card-body">
                  <div class="d-flex align-items-center gap-3 mb-2">
                      <div class="">
                        <h2 class="mb-0">$65,129</h2>
                      </div>
                      <div class="">
                        <p class="dash-lable d-flex align-items-center gap-1 rounded mb-0 bg-success text-success bg-opacity-10"><span class="material-icons-outlined fs-6">arrow_upward</span>8.6%</p>
                      </div>
                    </div>
                    <p class="mb-0">Sales This Year</p>
                    <div class="mt-4">
                      <p class="mb-2 d-flex align-items-center justify-content-between">285 left to Goal<span class="">78%</span></p>
                      <div class="progress w-100" style="height: 7px;">
                        <div class="progress-bar bg-primary" style="width: 65%"></div>
                      </div>
                    </div>
                    
                </div>
              </div>
            </div>

            </div><!--end row-->
          </div>
      </div>  
    </div> 
    <div class="col-12 col-xl-7 col-xxl-8 d-flex">
      <div class="card w-100 rounded-4">
          <div class="card-body">
          <div class="d-flex align-items-start justify-content-between mb-3">
            <div class="">
              <h5 class="mb-0 fw-bold">Sales & Views</h5>
            </div>
            <div class="dropdown">
              <a href="javascript:;" class="dropdown-toggle-nocaret options dropdown-toggle"
                data-bs-toggle="dropdown">
                <span class="material-icons-outlined fs-5">more_vert</span>
              </a>
              <ul class="dropdown-menu">
                <li><a class="dropdown-item" href="javascript:;">Action</a></li>
                <li><a class="dropdown-item" href="javascript:;">Another action</a></li>
                <li><a class="dropdown-item" href="javascript:;">Something else here</a></li>
              </ul>
            </div>
            </div>
            <div id="chart4"></div>
            <div class="d-flex flex-column flex-lg-row align-items-start justify-content-around border p-3 rounded-4 mt-3 gap-3">
              <div class="d-flex align-items-center gap-4">
                <div class="">
                  <p class="mb-0 data-attributes">
                    <span
                      data-peity='{ "fill": ["#0d6efd", "rgb(0 0 0 / 10%)"], "innerRadius": 32, "radius": 40 }'>5/7</span>
                  </p>
                </div>
                <div class="">
                  <p class="mb-1 fs-6 fw-bold">Monthly</p>
                  <h2 class="mb-0">65,127</h2>
                  <p class="mb-0"><span class="text-success me-2 fw-medium">16.5%</span><span>55.21 USD</span></p>
                </div>
              </div>
              <div class="vr"></div>
              <div class="d-flex align-items-center gap-4">
                <div class="">
                  <p class="mb-0 data-attributes">
                    <span
                      data-peity='{ "fill": ["#6f42c1", "rgb(0 0 0 / 10%)"], "innerRadius": 32, "radius": 40 }'>5/7</span>
                  </p>
                </div>
                <div class="">
                  <p class="mb-1 fs-6 fw-bold">Yearly</p>
                  <h2 class="mb-0">984,246</h2>
                  <p class="mb-0"><span class="text-success me-2 fw-medium">24.9%</span><span>267.35 USD</span></p>
                </div>
              </div>
            </div>
          </div>
      </div>  
    </div> 
  </div><!--end row--> --}}

  {{-- <div class="row">
      <div class="col-12 col-xl-4 d-flex">
      <div class="card w-100 rounded-4">
          <div class="card-body">
          <div class="d-flex align-items-start justify-content-between mb-3">
            <div class="">
              <h5 class="mb-0 fw-bold">Ongoing Projects</h5>
            </div>
            <div class="dropdown">
              <a href="javascript:;" class="dropdown-toggle-nocaret options dropdown-toggle"
                data-bs-toggle="dropdown">
                <span class="material-icons-outlined fs-5">more_vert</span>
              </a>
              <ul class="dropdown-menu">
                <li><a class="dropdown-item" href="javascript:;">Action</a></li>
                <li><a class="dropdown-item" href="javascript:;">Another action</a></li>
                <li><a class="dropdown-item" href="javascript:;">Something else here</a></li>
              </ul>
            </div>
            </div>
            <div class="d-flex flex-column gap-4">
                <div class="d-flex align-items-center gap-4">
                  <div class="d-flex align-items-center gap-3 flex-grow-1 flex-shrink-0">
                  <div class="wh-48 d-flex align-items-center justify-content-center rounded-3 border">
                    <img src="{{ asset('assets/admin/theme/assets/images/projects/angular.png') }}" width="30" alt="">
                  </div>
                    <div class="">
                      <h6 class="mb-0 fw-bold">Angular 12</h6>
                      <p class="mb-0">Admin Template</p>
                    </div>
                  </div>
                  <div class="progress w-25" style="height: 5px;">
                    <div class="progress-bar bg-danger" style="width: 95%"></div>
                  </div>
                  <div class="">
                  <p class="mb-0 fs-6">95%</p>
                  </div>
                </div>
                <div class="d-flex align-items-center gap-4">
                <div class="d-flex align-items-center gap-3 flex-grow-1 flex-shrink-0">
                  <div class="wh-48 d-flex align-items-center justify-content-center rounded-3 border">
                    <img src="{{ asset('assets/admin/theme/assets/images/projects/react.png') }}" width="30" alt="">
                  </div>
                    <div class="">
                      <h6 class="mb-0 fw-bold">React Js</h6>
                      <p class="mb-0">eCommerce Admin</p>
                    </div>
                </div>
                <div class="progress w-25" style="height: 5px;">
                    <div class="progress-bar bg-info" style="width: 90%"></div>
                </div>
                <div class="">
                  <p class="mb-0 fs-6">90%</p>
                </div>
              </div>
              <div class="d-flex align-items-center gap-4">
                <div class="d-flex align-items-center gap-3 flex-grow-1 flex-shrink-0">
                  <div class="wh-48 d-flex align-items-center justify-content-center rounded-3 border">
                    <img src="{{ asset('assets/admin/theme/assets/images/projects/vue.png') }}" width="30" alt="">
                  </div>
                    <div class="">
                      <h6 class="mb-0 fw-bold">Vue Js</h6>
                      <p class="mb-0">Dashboard Template</p>
                    </div>
                </div>
                <div class="progress w-25" style="height: 5px;">
                    <div class="progress-bar bg-success" style="width: 85%"></div>
                </div>
                <div class="">
                  <p class="mb-0 fs-6">85%</p>
                </div>
              </div>
              <div class="d-flex align-items-center gap-4">
                <div class="d-flex align-items-center gap-3 flex-grow-1 flex-shrink-0">
                  <div class="wh-48 d-flex align-items-center justify-content-center rounded-3 border">
                    <img src="{{ asset('assets/admin/theme/assets/images/projects/bootstrap.png') }}" width="30" alt="">
                  </div>
                    <div class="">
                      <h6 class="mb-0 fw-bold">Bootstrap 5</h6>
                      <p class="mb-0">Corporate Website</p>
                    </div>
                </div>
                <div class="progress w-25" style="height: 5px;">
                    <div class="progress-bar bg-voilet" style="width: 75%"></div>
                </div>
                <div class="">
                  <p class="mb-0 fs-6">75%</p>
                </div>
              </div>
              <div class="d-flex align-items-center gap-4">
                <div class="d-flex align-items-center gap-3 flex-grow-1 flex-shrink-0">
                  <div class="wh-48 d-flex align-items-center justify-content-center rounded-3 border">
                    <img src="{{ asset('assets/admin/theme/assets/images/projects/magento.png') }}" width="30" alt="">
                  </div>
                    <div class="">
                      <h6 class="mb-0 fw-bold">Magento</h6>
                      <p class="mb-0">Shoping Portal</p>
                    </div>
                </div>
                <div class="progress w-25" style="height: 5px;">
                    <div class="progress-bar bg-orange" style="width: 65%"></div>
                </div>
                <div class="">
                  <p class="mb-0 fs-6">65%</p>
                </div>
              </div>
              <div class="d-flex align-items-center gap-4">
                <div class="d-flex align-items-center gap-3 flex-grow-1 flex-shrink-0">
                  <div class="wh-48 d-flex align-items-center justify-content-center rounded-3 border">
                    <img src="{{ asset('assets/admin/theme/assets/images/projects/django.png') }}" width="30" alt="">
                  </div>
                    <div class="">
                      <h6 class="mb-0 fw-bold">Django</h6>
                      <p class="mb-0">Backend Admin</p>
                    </div>
                </div>
                <div class="progress w-25" style="height: 5px;">
                    <div class="progress-bar bg-cyne" style="width: 55%"></div>
                </div>
                <div class="">
                  <p class="mb-0 fs-6">55%</p>
                </div>
              </div>
              <div class="d-flex align-items-center gap-4">
                <div class="d-flex align-items-center gap-3 flex-grow-1 flex-shrink-0">
                  <div class="wh-48 d-flex align-items-center justify-content-center rounded-3 border">
                    <img src="{{ asset('assets/admin/theme/assets/images/projects/python.png') }}" width="30" alt="">
                  </div>
                    <div class="">
                      <h6 class="mb-0 fw-bold">Python</h6>
                      <p class="mb-0">User Panel</p>
                    </div>
                </div>
                <div class="progress w-25" style="height: 5px;">
                    <div class="progress-bar" style="width: 45%"></div>
                </div>
                <div class="">
                  <p class="mb-0 fs-6">45%</p>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <div class="col-12 col-xl-4 d-flex">
      <div class="card w-100 rounded-4">
        <div class="card-body">
          <div class="d-flex align-items-start justify-content-between mb-3">
            <div class="">
              <h5 class="mb-0 fw-bold">Campaign</h5>
            </div>
            <div class="dropdown">
              <a href="javascript:;" class="dropdown-toggle-nocaret options dropdown-toggle"
                data-bs-toggle="dropdown">
                <span class="material-icons-outlined fs-5">more_vert</span>
              </a>
              <ul class="dropdown-menu">
                <li><a class="dropdown-item" href="javascript:;">Action</a></li>
                <li><a class="dropdown-item" href="javascript:;">Another action</a></li>
                <li><a class="dropdown-item" href="javascript:;">Something else here</a></li>
              </ul>
            </div>
            </div>
          <div class="d-flex flex-column justify-content-between gap-4">
            <div class="d-flex align-items-center gap-4">
              <div class="d-flex align-items-center gap-3 flex-grow-1">
                <img src="{{ asset('assets/admin/theme/assets/images/apps/17.png') }}" width="32" alt="">
                <p class="mb-0">Facebook</p>
              </div>
              <div class="">
                <p class="mb-0 fs-6">55%</p>
              </div>
              <div class="">
                <p class="mb-0 data-attributes">
                  <span
                    data-peity='{ "fill": ["#0d6efd", "rgb(0 0 0 / 10%)"], "innerRadius": 14, "radius": 18 }'>5/7</span>
                </p>
              </div>
            </div>
            <div class="d-flex align-items-center gap-4">
              <div class="d-flex align-items-center gap-3 flex-grow-1">
                <img src="{{ asset('assets/admin/theme/assets/images/apps/18.png') }}" width="32" alt="">
                <p class="mb-0">LinkedIn</p>
              </div>
              <div class="">
                <p class="mb-0 fs-6">67%</p>
              </div>
              <div class="">
                <p class="mb-0 data-attributes">
                  <span
                    data-peity='{ "fill": ["#fc185a", "rgb(0 0 0 / 10%)"], "innerRadius": 14, "radius": 18 }'>5/7</span>
                </p>
              </div>
            </div>
            <div class="d-flex align-items-center gap-4">
              <div class="d-flex align-items-center gap-3 flex-grow-1">
                <img src="{{ asset('assets/admin/theme/assets/images/apps/19.png') }}" width="32" alt="">
                <p class="mb-0">Instagram</p>
              </div>
              <div class="">
                <p class="mb-0 fs-6">78%</p>
              </div>
              <div class="">
                <p class="mb-0 data-attributes">
                  <span
                    data-peity='{ "fill": ["#02c27a", "rgb(0 0 0 / 10%)"], "innerRadius": 14, "radius": 18 }'>5/7</span>
                </p>
              </div>
            </div>
            <div class="d-flex align-items-center gap-4">
              <div class="d-flex align-items-center gap-3 flex-grow-1">
                <img src="{{ asset('assets/admin/theme/assets/images/apps/20.png') }}" width="32" alt="">
                <p class="mb-0">Snapchat</p>
              </div>
              <div class="">
                <p class="mb-0 fs-6">46%</p>
              </div>
              <div class="">
                <p class="mb-0 data-attributes">
                  <span
                    data-peity='{ "fill": ["#fd7e14", "rgb(0 0 0 / 10%)"], "innerRadius": 14, "radius": 18 }'>5/7</span>
                </p>
              </div>
            </div>
            <div class="d-flex align-items-center gap-4">
              <div class="d-flex align-items-center gap-3 flex-grow-1">
                <img src="{{ asset('assets/admin/theme/assets/images/apps/05.png') }}" width="32" alt="">
                <p class="mb-0">Google</p>
              </div>
              <div class="">
                <p class="mb-0 fs-6">38%</p>
              </div>
              <div class="">
                <p class="mb-0 data-attributes">
                  <span
                    data-peity='{ "fill": ["#0dcaf0", "rgb(0 0 0 / 10%)"], "innerRadius": 14, "radius": 18 }'>5/7</span>
                </p>
              </div>
            </div>
            <div class="d-flex align-items-center gap-4">
              <div class="d-flex align-items-center gap-3 flex-grow-1">
                <img src="{{ asset('assets/admin/theme/assets/images/apps/08.png') }}" width="32" alt="">
                <p class="mb-0">Altaba</p>
              </div>
              <div class="">
                <p class="mb-0 fs-6">15%</p>
              </div>
              <div class="">
                <p class="mb-0 data-attributes">
                  <span
                    data-peity='{ "fill": ["#6f42c1", "rgb(0 0 0 / 10%)"], "innerRadius": 14, "radius": 18 }'>5/7</span>
                </p>
              </div>
            </div>
            <div class="d-flex align-items-center gap-4">
              <div class="d-flex align-items-center gap-3 flex-grow-1">
                <img src="{{ asset('assets/admin/theme/assets/images/apps/07.png') }}" width="32" alt="">
                <p class="mb-0">Spotify</p>
              </div>
              <div class="">
                <p class="mb-0 fs-6">12%</p>
              </div>
              <div class="">
                <p class="mb-0 data-attributes">
                  <span
                    data-peity='{ "fill": ["#ff00b3", "rgb(0 0 0 / 10%)"], "innerRadius": 14, "radius": 18 }'>5/7</span>
                </p>
              </div>
            </div>
            <div class="d-flex align-items-center gap-4">
              <div class="d-flex align-items-center gap-3 flex-grow-1">
                <img src="{{ asset('assets/admin/theme/assets/images/apps/12.png') }}" width="32" alt="">
                <p class="mb-0">Photoes</p>
              </div>
              <div class="">
                <p class="mb-0 fs-6">24%</p>
              </div>
              <div class="">
                <p class="mb-0 data-attributes">
                  <span
                    data-peity='{ "fill": ["#22e3aa", "rgb(0 0 0 / 10%)"], "innerRadius": 14, "radius": 18 }'>5/7</span>
                </p>
              </div>
            </div>
            
          </div>
        </div>
      </div>  
    </div>

      <div class="col-12 col-xl-4 d-flex">
      <div class="card rounded-4 w-100">
        <div class="card-body">
          <div class="d-flex align-items-start justify-content-between mb-3">
            <div class="">
              <h5 class="mb-0 fw-bold">Recent Transactions</h5>
            </div>
            <div class="dropdown">
              <a href="javascript:;" class="dropdown-toggle-nocaret options dropdown-toggle"
                data-bs-toggle="dropdown">
                <span class="material-icons-outlined fs-5">more_vert</span>
              </a>
              <ul class="dropdown-menu">
                <li><a class="dropdown-item" href="javascript:;">Action</a></li>
                <li><a class="dropdown-item" href="javascript:;">Another action</a></li>
                <li><a class="dropdown-item" href="javascript:;">Something else here</a></li>
              </ul>
            </div>
            </div>
          <div class="payments-list">
            <div class="d-flex flex-column gap-4">
              <div class="d-flex align-items-center gap-3">
                <div class="wh-48 d-flex align-items-center justify-content-center bg-danger rounded-circle">
                  <span class="material-icons-outlined text-white">shopping_cart</span>
                </div>
                <div class="flex-grow-1">
                  <h6 class="mb-0 fw-bold">Online Purchase</h6>
                  <p class="mb-0">03/10/2022</p>
                </div>
                <div class="d-flex align-items-center">
                  <h6 class="mb-0 fw-bold">$97,896</h6>
                </div>
              </div>
              <div class="d-flex align-items-center gap-3">
                <div class="wh-48 d-flex align-items-center justify-content-center rounded-circle bg-primary">
                  <span class="material-icons-outlined text-white">monetization_on</span>
                </div>
                <div class="flex-grow-1">
                  <h6 class="mb-0">Bank Transfer</h6>
                  <p class="mb-0">03/10/2022</p>
                </div>
                <div class="d-flex align-items-center gap-1">
                  <h6 class="mb-0 fw-bold">$86,469</h6>
                </div>
              </div>
              <div class="d-flex align-items-center gap-3">
                <div class="wh-48 d-flex align-items-center justify-content-center rounded-circle bg-success">
                  <span class="material-icons-outlined text-white">credit_card</span>
                </div>
                <div class="flex-grow-1">
                  <h6 class="mb-0">Credit Card</h6>
                  <p class="mb-0">03/10/2022</p>
                </div>
                <div class="d-flex align-items-center gap-1">
                  <h6 class="mb-0 fw-bold">$45,259</h6>
                </div>
              </div>
              <div class="d-flex align-items-center gap-3">
                <div class="wh-48 d-flex align-items-center justify-content-center rounded-circle bg-purple">
                  <span class="material-icons-outlined text-white">account_balance</span>
                </div>
                <div class="flex-grow-1">
                  <h6 class="mb-0">Laptop Payment</h6>
                  <p class="mb-0">03/10/2022</p>
                </div>
                <div class="d-flex align-items-center gap-1">
                  <h6 class="mb-0 fw-bold">$35,249</h6>
                </div>
              </div>
              <div class="d-flex align-items-center gap-3">
                <div class="wh-48 d-flex align-items-center justify-content-center rounded-circle bg-orange">
                  <span class="material-icons-outlined text-white">savings</span>
                </div>
                <div class="flex-grow-1">
                  <h6 class="mb-0">Template Payment</h6>
                  <p class="mb-0">03/10/2022</p>
                </div>
                <div class="d-flex align-items-center gap-1">
                  <h6 class="mb-0 fw-bold">$68,478</h6>
                </div>
              </div>
              <div class="d-flex align-items-center gap-3">
                <div class="wh-48 d-flex align-items-center justify-content-center rounded-circle bg-info">
                  <span class="material-icons-outlined text-white">paid</span>
                </div>
                <div class="flex-grow-1">
                  <h6 class="mb-0">iPhone Purchase</h6>
                  <p class="mb-0">03/10/2022</p>
                </div>
                <div class="d-flex align-items-center gap-1">
                  <h6 class="mb-0 fw-bold">$55,128</h6>
                </div>
              </div>
              <div class="d-flex align-items-center gap-3">
                <div class="wh-48 d-flex align-items-center justify-content-center rounded-circle bg-pink">
                  <span class="material-icons-outlined text-white">card_giftcard</span>
                </div>
                <div class="flex-grow-1">
                  <h6 class="mb-0">Account Credit</h6>
                  <p class="mb-0">03/10/2022</p>
                </div>
                <div class="d-flex align-items-center gap-1">
                  <h6 class="mb-0 fw-bold">$24,568</h6>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div> --}}

    {{-- <div class="col-12 col-xl-12 d-flex">
    <div class="card w-100 rounded-4">
      <div class="card-body">
        <div class="d-flex align-items-start justify-content-between mb-3">
          <div class="">
            <h5 class="mb-0 fw-bold">Popular Products</h5>
          </div>
          <div class="dropdown">
            <a href="javascript:;" class="dropdown-toggle-nocaret options dropdown-toggle"
              data-bs-toggle="dropdown">
              <span class="material-icons-outlined fs-5">more_vert</span>
            </a>
            <ul class="dropdown-menu">
              <li><a class="dropdown-item" href="javascript:;">Action</a></li>
              <li><a class="dropdown-item" href="javascript:;">Another action</a></li>
              <li><a class="dropdown-item" href="javascript:;">Something else here</a></li>
            </ul>
          </div>
          </div>
        <div class="d-flex flex-column gap-4">
          <div class="d-flex align-items-center gap-3">
            <img src="{{ asset('assets/admin/theme/assets/images/orders/01.png') }}" width="78" class="rounded-3" alt="">
            <div class="flex-grow-1">
              <h6 class="mb-0 fw-bold">Apple Hand Watch</h6>
              <p class="mb-0">Sale: 258</p>
            </div>
            <div class="">
              <h6 class="mb-0">$199</h6>
            </div>
          </div>
          <div class="d-flex align-items-center gap-3">
            <img src="{{ asset('assets/admin/theme/assets/images/orders/08.png') }}" width="78" class="rounded-3" alt="">
            <div class="flex-grow-1">
              <h6 class="mb-0 fw-bold">Mobile Phone Set</h6>
              <p class="mb-0">Sale: 169</p>
            </div>
            <div class="">
              <h6 class="mb-0">$159</h6>
            </div>
          </div>
          <div class="d-flex align-items-center gap-3">
            <img src="{{ asset('assets/admin/theme/assets/images/orders/03.png') }}" width="78" class="rounded-3" alt="">
            <div class="flex-grow-1">
              <h6 class="mb-0 fw-bold">Fancy Chair</h6>
              <p class="mb-0">Sale: 268</p>
            </div>
            <div class="">
              <h6 class="mb-0">$678</h6>
            </div>
          </div>
          <div class="d-flex align-items-center gap-3">
            <img src="{{ asset('assets/admin/theme/assets/images/orders/04.png') }}" width="78" class="rounded-3" alt="">
            <div class="flex-grow-1">
              <h6 class="mb-0 fw-bold">Blue Shoes Pair</h6>
              <p class="mb-0">Sale: 859</p>
            </div>
            <div class="">
              <h6 class="mb-0">$279</h6>
            </div>
          </div>
          <div class="d-flex align-items-center gap-3">
            <img src="{{ asset('assets/admin/theme/assets/images/orders/05.png') }}" width="78" class="rounded-3" alt="">
            <div class="flex-grow-1">
              <h6 class="mb-0 fw-bold">Blue Yoga Mat</h6>
              <p class="mb-0">Sale: 328</p>
            </div>
            <div class="">
              <h6 class="mb-0">$389</h6>
            </div>
          </div>
          <div class="d-flex align-items-center gap-3">
            <img src="{{ asset('assets/admin/theme/assets/images/orders/06.png') }}" width="75" class="rounded-3" alt="">
            <div class="flex-grow-1">
              <h6 class="mb-0 fw-bold">White water Bottle</h6>
              <p class="mb-0">Sale: 992</p>
            </div>
            <div class="">
              <h6 class="mb-0">$584</h6>
            </div>
          </div>
          <div class="d-flex align-items-center gap-3">
            <img src="{{ asset('assets/admin/theme/assets/images/orders/07.png') }}" width="78" class="rounded-3" alt="">
            <div class="flex-grow-1">
              <h6 class="mb-0 fw-bold">Laptop Full HD</h6>
              <p class="mb-0">Sale: 489</p>
            </div>
            <div class="">
              <h6 class="mb-0">$398</h6>
            </div>
          </div>
          
        </div>
      </div>
    </div>
  </div> 
</div><!--end row-->--}}
@endif


@push('scripts')
  @php
    $timelinePayload = (!empty($trafficTimeline) && !empty($trafficTimeline['ok']))
        ? $trafficTimeline
        : ['ok' => false, 'labels' => [], 'series' => [], 'error' => $trafficTimeline['error'] ?? 'Timeline not available'];
  @endphp

  <script>
    // expose timeline for the chart file
    window.GA_TRAFFIC_TIMELINE = @json($timelinePayload);
  </script>

  {{-- <script>
    //random data
    window.GA_TRAFFIC_TIMELINE = {
      ok: true,
      labels: [
        'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul',
        'Aug', 'Sep', 'Oct', 'Nov', 'Dec', 'Jan'
      ],
      series: [
        {
          name: 'Users',
          data: [612, 548, 721, 689, 845, 792, 911, 864, 1032, 987, 1214, 1098]
        },
        {
          name: 'Page views',
          data: [1420, 1312, 1765, 1651, 2089, 1924, 2298, 2140, 2645, 2487, 3096, 2811]
        }
      ]
    };
  </script> --}}


  <script>
  (function () {
    // -----------------------------
    // Realtime "Active users" counter
    // -----------------------------
    const counterEl = document.getElementById('realtimeUsers');
    const statusEl  = document.getElementById('realtimeStatus');
    const url = @json(route('admin.dashboard.realtime-users'));

    async function refreshRealtime() {
      try {
        const res = await fetch(url, { headers: { 'Accept': 'application/json' } });
        const data = await res.json();

        if (data && data.ok) {
          if (counterEl) counterEl.textContent = String(data.activeUsers ?? 0);
          if (statusEl) statusEl.textContent = 'Updated ' + new Date().toLocaleTimeString();
        } else {
          if (statusEl) statusEl.textContent = data?.error ?? 'Realtime unavailable';
        }
      } catch (e) {
        if (statusEl) statusEl.textContent = 'Realtime error (check GA config)';
      }
    }

    // run once immediately + then poll
    refreshRealtime();
    setInterval(refreshRealtime, 15000);
  })();
  </script>

  {{-- GA chart renderer (Apex) --}}
  <script src="{{ asset('assets/admin/js/ga-traffic-chart.js') }}"></script>
@endpush

  
</x-app-layout>