<div class="sidebar-header">
    <div class="logo-icon">
      <img src="{{ asset('assets/admin/images/agares-logo.png') }}" class="logo-img" alt="">
    </div>
    <div class="logo-name flex-grow-1">
      <h5 class="mb-0">Admin Panel</h5>
    </div>

    <div class="sidebar-close">
      <span class="material-icons-outlined">close</span>
    </div>
    
  </div>
  <div class="sidebar-nav" data-simplebar="true">
    <ul class="metismenu" id="sidenav">
        <li>
            <x-sidebar-nav-link :href="route('home')" target="_blank">
                <div class="parent-icon"><i class="material-icons-outlined">home</i></div>
                <div class="menu-title">{{ __('Home Page') }}</div>
            </x-sidebar-nav-link>
        </li>
    
        <li>
            <x-sidebar-nav-link
                :href="auth()->user()->can('manage dashboard') ? route('admin.dashboard') : 'javascript:void(0);'"
                :active="request()->routeIs('admin.dashboard*')"
                :class="!auth()->user()->can('manage dashboard') ? 'pointer-events-none opacity-50 cursor-not-allowed' : ''"
                :title="auth()->user()->can('manage dashboard') ? '' : __('You don\'t have permission to view this page')"
            >
                <div class="parent-icon"><i class="material-icons-outlined">dashboard</i></div>
                <div class="menu-title">{{ __('Dashboard') }}</div>
            </x-sidebar-nav-link>
        </li>

        <hr>

        @if ($settings['enable_ecommerce'] ?? false)
            <li>
                <a href="javascript:;" class="has-arrow">
                    <div class="parent-icon">
                        <i class="material-icons-outlined">store</i>
                    </div>
                    <div class="menu-title">eCommerce</div>
                </a>

                <ul>
                    {{-- Dashboard --}}
                    <li>
                        <x-sidebar-nav-link
                            :href="auth()->user()->can('manage ecommerce') ? route('admin.ecommerce.dashboard') : 'javascript:void(0);'"
                            :active="request()->routeIs('admin.ecommerce.dashboard')"
                            :class="!auth()->user()->can('manage ecommerce') ? 'pointer-events-none opacity-50 cursor-not-allowed' : ''"
                            :title="auth()->user()->can('manage ecommerce') ? '' : __('You don\'t have permission to view this page')"
                        >
                            <i class="material-icons-outlined">dashboard</i>
                            <span>Dashboard</span>
                        </x-sidebar-nav-link>
                    </li>

                    {{-- Products --}}
                    <li>
                        <x-sidebar-nav-link
                            :href="auth()->user()->can('manage ecommerce') ? route('admin.ecommerce.products.index') : 'javascript:void(0);'"
                            :active="request()->routeIs('admin.ecommerce.products.*')"
                            :class="!auth()->user()->can('manage ecommerce') ? 'pointer-events-none opacity-50 cursor-not-allowed' : ''"
                        >
                            <i class="material-icons-outlined">inventory_2</i>
                            <span>Products</span>
                        </x-sidebar-nav-link>
                    </li>

                    {{-- Categories --}}
                    <li>
                        <x-sidebar-nav-link
                            :href="auth()->user()->can('manage ecommerce') ? route('admin.ecommerce.categories.index') : 'javascript:void(0);'"
                            :active="request()->routeIs('admin.ecommerce.categories.*')"
                            :class="!auth()->user()->can('manage ecommerce') ? 'pointer-events-none opacity-50 cursor-not-allowed' : ''"
                        >
                            <i class="material-icons-outlined">category</i>
                            <span>Categories</span>
                        </x-sidebar-nav-link>
                    </li>

                    {{-- Tags --}}
                    <li>
                        <x-sidebar-nav-link
                            :href="auth()->user()->can('manage ecommerce') ? route('admin.ecommerce.tags.index') : 'javascript:void(0);'"
                            :active="request()->routeIs('admin.ecommerce.tags.*')"
                            :class="!auth()->user()->can('manage ecommerce') ? 'pointer-events-none opacity-50 cursor-not-allowed' : ''"
                        >
                            <i class="material-icons-outlined">local_offer</i>
                            <span>Tags</span>
                        </x-sidebar-nav-link>
                    </li>

                    {{-- Orders --}}
                    <li>
                        <x-sidebar-nav-link
                            :href="auth()->user()->can('manage ecommerce') ? route('admin.ecommerce.orders.index') : 'javascript:void(0);'"
                            :active="request()->routeIs('admin.ecommerce.orders.*')"
                            :class="!auth()->user()->can('manage ecommerce') ? 'pointer-events-none opacity-50 cursor-not-allowed' : ''"
                        >
                            <i class="material-icons-outlined">shopping_cart</i>
                            <span>Orders</span>
                        </x-sidebar-nav-link>
                    </li>

                    {{-- Coupons --}}
                    <li>
                        <x-sidebar-nav-link
                            :href="auth()->user()->can('manage ecommerce') ? route('admin.ecommerce.coupons.index') : 'javascript:void(0);'"
                            :active="request()->routeIs('admin.ecommerce.coupons.*')"
                            :class="!auth()->user()->can('manage ecommerce') ? 'pointer-events-none opacity-50 cursor-not-allowed' : ''"
                        >
                            <i class="material-icons-outlined">confirmation_number</i>
                            <span>Coupons</span>
                        </x-sidebar-nav-link>
                    </li>

                    {{-- Shipping Methods --}}
                    <li>
                        <x-sidebar-nav-link
                            :href="auth()->user()->can('manage ecommerce') ? route('admin.ecommerce.shipping-methods.index') : 'javascript:void(0);'"
                            :active="request()->routeIs('admin.ecommerce.shipping-methods.*')"
                            :class="!auth()->user()->can('manage ecommerce') ? 'pointer-events-none opacity-50 cursor-not-allowed' : ''"
                        >
                            <i class="material-icons-outlined">local_shipping</i>
                            <span>Shipping</span>
                        </x-sidebar-nav-link>
                    </li>

                    {{-- Tax Rules --}}
                    <li>
                        <x-sidebar-nav-link
                            :href="auth()->user()->can('manage ecommerce') ? route('admin.ecommerce.tax-rules.index') : 'javascript:void(0);'"
                            :active="request()->routeIs('admin.ecommerce.tax-rules.*')"
                            :class="!auth()->user()->can('manage ecommerce') ? 'pointer-events-none opacity-50 cursor-not-allowed' : ''"
                        >
                            <i class="material-icons-outlined">percent</i>
                            <span>Tax Rules</span>
                        </x-sidebar-nav-link>
                    </li>

                    {{-- Payments --}}
                    <li>
                        <x-sidebar-nav-link
                            :href="auth()->user()->can('manage ecommerce') ? route('admin.ecommerce.payment-providers.index') : 'javascript:void(0);'"
                            :active="request()->routeIs('admin.ecommerce.payment-providers.*')"
                            :class="!auth()->user()->can('manage ecommerce') ? 'pointer-events-none opacity-50 cursor-not-allowed' : ''"
                        >
                            <i class="material-icons-outlined">credit_card</i>
                            <span>Payments</span>
                        </x-sidebar-nav-link>
                    </li>

                    {{-- Settings --}}
                    <li>
                        <x-sidebar-nav-link
                            :href="auth()->user()->can('manage ecommerce') ? route('admin.ecommerce.settings.index') : 'javascript:void(0);'"
                            :active="request()->routeIs('admin.ecommerce.settings.*')"
                            :class="!auth()->user()->can('manage ecommerce') ? 'pointer-events-none opacity-50 cursor-not-allowed' : ''"
                        >
                            <i class="material-icons-outlined">settings</i>
                            <span>Settings</span>
                        </x-sidebar-nav-link>
                    </li>
                </ul>
            </li>

            <hr>
        @endif


        @if ($settings['enable_forum'] ?? false)
            <li>
                <a href="javascript:;" class="has-arrow">
                    <div class="parent-icon"><i class="material-icons-outlined">window</i></div>
                    <div class="menu-title">Forum</div>
                </a>

                <ul>
                    <li>
                    <x-sidebar-nav-link
                        :href="auth()->user()->can('manage forum') ? route('admin.forum') : 'javascript:void(0);'"
                        :active="request()->routeIs('admin.forum')"
                        :class="!auth()->user()->can('manage forum') ? 'pointer-events-none opacity-50 cursor-not-allowed' : ''"
                        :title="auth()->user()->can('manage forum') ? '' : __('You don\'t have permission to view this page')"
                    >
                        <i class="material-icons-outlined">arrow_right</i>
                        <span>Basic</span>
                    </x-sidebar-nav-link>
                    </li>

                    <li>
                    <x-sidebar-nav-link
                        :href="auth()->user()->can('manage forum') ? route('admin.forum') : 'javascript:void(0);'"
                        :active="request()->routeIs('admin.forum')"
                        :class="!auth()->user()->can('manage forum') ? 'pointer-events-none opacity-50 cursor-not-allowed' : ''"
                        :title="auth()->user()->can('manage forum') ? '' : __('You don\'t have permission to view this page')"
                    >
                        <i class="material-icons-outlined">arrow_right</i>
                        <span>Advance</span>
                    </x-sidebar-nav-link>
                    </li>

                    <li>
                    <x-sidebar-nav-link
                        :href="auth()->user()->can('manage forum') ? route('admin.forum') : 'javascript:void(0);'"
                        :active="request()->routeIs('admin.forum')"
                        :class="!auth()->user()->can('manage forum') ? 'pointer-events-none opacity-50 cursor-not-allowed' : ''"
                        :title="auth()->user()->can('manage forum') ? '' : __('You don\'t have permission to view this page')"
                    >
                        <i class="material-icons-outlined">settings</i>
                        <span>Settings</span>
                    </x-sidebar-nav-link>
                    </li>
                </ul>
            </li>

            <hr>
        @endif


        <li>
            <x-sidebar-nav-link
                :href="auth()->user()->can('manage sites') ? route('admin.sites') : 'javascript:void(0);'"
                :active="request()->routeIs('admin.sites*')"
                :class="!auth()->user()->can('manage sites') ? 'pointer-events-none opacity-50 cursor-not-allowed' : ''"
                :title="auth()->user()->can('manage sites') ? '' : __('You don\'t have permission to view this page')"
            >
                <i class="fadeIn animated bx bx-window-alt" style="font-size: 24px;"></i>
                <div class="menu-title">{{ __('Sites') }}</div>
            </x-sidebar-nav-link>
        </li>

        <li>
          <x-sidebar-nav-link
              :href="auth()->user()->can('manage menus') ? route('admin.menus') : 'javascript:void(0);'"
              :active="request()->routeIs('admin.menus*')"
              :class="!auth()->user()->can('manage menus') ? 'pointer-events-none opacity-50 cursor-not-allowed' : ''"
              :title="auth()->user()->can('manage menus') ? '' : __('You don\'t have permission to view this page')"
          >
              <div class="parent-icon"><i class="material-icons-outlined">menu</i></div>
              <div class="menu-title">{{ __('Menus') }}</div>
            </x-sidebar-nav-link>
        </li>

        <li>
          <x-sidebar-nav-link
              :href="auth()->user()->can('manage media') ? route('admin.media') : 'javascript:void(0);'"
              :active="request()->routeIs('admin.media*')"
              :class="!auth()->user()->can('manage media') ? 'pointer-events-none opacity-50 cursor-not-allowed' : ''"
              :title="auth()->user()->can('manage media') ? '' : __('You don\'t have permission to view this page')"
          >
              <div class="parent-icon"><i class="material-icons-outlined">collections</i></div>
              <div class="menu-title">{{ __('Media') }}</div>
            </x-sidebar-nav-link>
        </li>

        <hr>

        <li>
            <x-sidebar-nav-link
                :href="auth()->user()->can('manage users') ? route('admin.users') : 'javascript:void(0);'"
                :active="request()->routeIs('admin.users*')"
                :class="!auth()->user()->can('manage users') ? 'pointer-events-none opacity-50 cursor-not-allowed' : ''"
                :title="auth()->user()->can('manage users') ? '' : __('You don\'t have permission to view this page')"
            >
                <div class="parent-icon"><i class="material-icons-outlined">people</i></div>
                <div class="menu-title">{{ __('Users') }}</div>
            </x-sidebar-nav-link>
        </li>
    
        <li>
            <x-sidebar-nav-link
                :href="auth()->user()->can('manage permissions') ? route('admin.permissions') : 'javascript:void(0);'"
                :active="request()->routeIs('admin.permissions*')"
                :class="!auth()->user()->can('manage permissions') ? 'pointer-events-none opacity-50 cursor-not-allowed' : ''"
                :title="auth()->user()->can('manage permissions') ? '' : __('You don\'t have permission to view this page')"
            >
                <div class="parent-icon"><i class="material-icons-outlined">vpn_key</i></div>
                <div class="menu-title">{{ __('Permissions') }}</div>
            </x-sidebar-nav-link>
        </li>

        <hr>

        <li>
          <x-sidebar-nav-link
              :href="auth()->user()->can('manage custom') ? route('admin.custom') : 'javascript:void(0);'"
              :active="request()->routeIs('admin.custom*')"
              :class="!auth()->user()->can('manage custom') ? 'pointer-events-none opacity-50 cursor-not-allowed' : ''"
              :title="auth()->user()->can('manage custom') ? '' : __('You don\'t have permission to view this page')"
          >
              <div class="parent-icon"><i class="material-icons-outlined">extension</i></div>
              <div class="menu-title">{{ __('Custom') }}</div>
            </x-sidebar-nav-link>
        </li>

        <li>
            <x-sidebar-nav-link
                :href="auth()->user()->can('manage settings') ? route('admin.cookies') : 'javascript:void(0);'"
                :active="request()->routeIs('admin.cookies*')"
                :class="!auth()->user()->can('manage settings') ? 'pointer-events-none opacity-50 cursor-not-allowed' : ''"
                :title="auth()->user()->can('manage settings') ? '' : __('You don\'t have permission to view this page')"
            >
                <div class="parent-icon"><i class="material-icons-outlined">cookie</i></div>
                <div class="menu-title">{{ __('Cookies') }}</div>
            </x-sidebar-nav-link>
        </li>
        {{-- <li>
            <a href="javascript:;" class="has-arrow">
                <div class="parent-icon"><i class="material-icons-outlined">construction</i></div>
                <div class="menu-title">Tools</div>
            </a>
            <ul>
                <li>
                    <x-sidebar-nav-link
                        :href="route('admin.tools.qr-generator')"
                        :active="request()->routeIs('admin.tools.qr-generator')"
                    >
                        <i class="material-icons-outlined">qr_code_2</i>
                        <span>QR Generator</span>
                    </x-sidebar-nav-link>
                </li>
            </ul>
        </li> --}}

        @if ($settings['enable_api'] ?? false)
            <li>
                <x-sidebar-nav-link
                    :href="auth()->user()->can('manage API') ? route('admin.api.index') : 'javascript:void(0);'"
                    :active="request()->routeIs('admin.api*')"
                    :class="!auth()->user()->can('manage API') ? 'pointer-events-none opacity-50 cursor-not-allowed' : ''"
                    :title="auth()->user()->can('manage API') ? '' : __('You don\'t have permission to view this page')"
                >
                    <div class="parent-icon"><i class="material-icons-outlined">code</i></div>
                    <div class="menu-title">{{ __('API') }}</div>
                </x-sidebar-nav-link>
            </li>
        @endif
        <li>
            <x-sidebar-nav-link
                :href="auth()->user()->can('manage settings') ? route('admin.settings') : 'javascript:void(0);'"
                :active="request()->routeIs('admin.settings*')"
                :class="!auth()->user()->can('manage settings') ? 'pointer-events-none opacity-50 cursor-not-allowed' : ''"
                :title="auth()->user()->can('manage settings') ? '' : __('You don\'t have permission to view this page')"
            >
                <div class="parent-icon"><i class="material-icons-outlined">settings</i></div>
                <div class="menu-title">{{ __('Settings') }}</div>
            </x-sidebar-nav-link>
        </li>
    
    </ul>
    
  </div>
  <div class="sidebar-bottom gap-4">
      <div class="dark-mode">
        <a href="javascript:;" class="footer-icon dark-mode-icon">
          <i class="material-icons-outlined">dark_mode</i>  
        </a>
      </div>
      
      <div class="dropdown dropup-center dropup dropdown-help">
        <a class="footer-icon  dropdown-toggle dropdown-toggle-nocaret option" href="javascript:;"
          data-bs-toggle="dropdown" aria-expanded="false">
          <span class="material-icons-outlined">
            info
          </span>
        </a>
        <div class="dropdown-menu dropdown-option dropdown-menu-end shadow">
            <div>
                <a class="dropdown-item d-flex align-items-center gap-2 py-2" href="{{ route('admin.info') }}">
                    <span class="material-icons-outlined">info</span>System Information
                </a>
            </div>

            <div>
                <hr class="dropdown-divider">
            </div>

            <div>
                <a class="dropdown-item d-flex align-items-center gap-2 py-2" href="{{ route('admin.api.documentation') }}">
                    <i class="fadeIn animated bx bx-code-alt" style="font-size: 24px;"></i>API Documentation
                </a>
            </div>
            {{-- <div>
                <a class="dropdown-item d-flex align-items-center gap-2 py-2" href="javascript:;">
                    <i class="fadeIn animated bx bx-list-ol" style="font-size: 24px;"></i>TODO List
                </a>
            </div> --}}
        
            <div>
                <hr class="dropdown-divider">
            </div>

            <div>
                <a class="dropdown-item d-flex align-items-center gap-2 py-2" href="{{ route('admin.documentation') }}">
                    <i class="fadeIn animated bx bx-file-blank" style="font-size: 24px;"></i>CMS Documentation
                </a>
            </div>
        </div>
      </div>
  </div>