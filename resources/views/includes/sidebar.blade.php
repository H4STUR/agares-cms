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
                            :href="auth()->user()->can('view ecommerce') ? route('admin.ecommerce.dashboard') : 'javascript:void(0);'"
                            :active="request()->routeIs('admin.ecommerce.dashboard')"
                            :class="!auth()->user()->can('view ecommerce') ? 'pointer-events-none opacity-50 cursor-not-allowed' : ''"
                            :title="auth()->user()->can('view ecommerce') ? '' : __('You don\'t have permission to view this page')"
                        >
                            <i class="material-icons-outlined">dashboard</i>
                            <span>Dashboard</span>
                        </x-sidebar-nav-link>
                    </li>

                    {{-- Products --}}
                    <li>
                        <x-sidebar-nav-link
                            :href="auth()->user()->can('view ecommerce') ? route('admin.ecommerce.products.index') : 'javascript:void(0);'"
                            :active="request()->routeIs('admin.ecommerce.products.*')"
                            :class="!auth()->user()->can('view ecommerce') ? 'pointer-events-none opacity-50 cursor-not-allowed' : ''"
                        >
                            <i class="material-icons-outlined">inventory_2</i>
                            <span>Products</span>
                        </x-sidebar-nav-link>
                    </li>

                    {{-- Categories --}}
                    <li>
                        <x-sidebar-nav-link
                            :href="auth()->user()->can('view ecommerce') ? route('admin.ecommerce.categories.index') : 'javascript:void(0);'"
                            :active="request()->routeIs('admin.ecommerce.categories.*')"
                            :class="!auth()->user()->can('view ecommerce') ? 'pointer-events-none opacity-50 cursor-not-allowed' : ''"
                        >
                            <i class="material-icons-outlined">category</i>
                            <span>Categories</span>
                        </x-sidebar-nav-link>
                    </li>

                    {{-- Tags --}}
                    <li>
                        <x-sidebar-nav-link
                            :href="auth()->user()->can('view ecommerce') ? route('admin.ecommerce.tags.index') : 'javascript:void(0);'"
                            :active="request()->routeIs('admin.ecommerce.tags.*')"
                            :class="!auth()->user()->can('view ecommerce') ? 'pointer-events-none opacity-50 cursor-not-allowed' : ''"
                        >
                            <i class="material-icons-outlined">local_offer</i>
                            <span>Tags</span>
                        </x-sidebar-nav-link>
                    </li>

                    {{-- Orders --}}
                    <li>
                        <x-sidebar-nav-link
                            :href="auth()->user()->can('view ecommerce') ? route('admin.ecommerce.orders.index') : 'javascript:void(0);'"
                            :active="request()->routeIs('admin.ecommerce.orders.*')"
                            :class="!auth()->user()->can('view ecommerce') ? 'pointer-events-none opacity-50 cursor-not-allowed' : ''"
                        >
                            <i class="material-icons-outlined">shopping_cart</i>
                            <span>Orders</span>
                        </x-sidebar-nav-link>
                    </li>

                    {{-- Coupons --}}
                    <li>
                        <x-sidebar-nav-link
                            :href="auth()->user()->can('view ecommerce') ? route('admin.ecommerce.coupons.index') : 'javascript:void(0);'"
                            :active="request()->routeIs('admin.ecommerce.coupons.*')"
                            :class="!auth()->user()->can('view ecommerce') ? 'pointer-events-none opacity-50 cursor-not-allowed' : ''"
                        >
                            <i class="material-icons-outlined">confirmation_number</i>
                            <span>Coupons</span>
                        </x-sidebar-nav-link>
                    </li>

                    {{-- Shipping Methods --}}
                    <li>
                        <x-sidebar-nav-link
                            :href="auth()->user()->can('view ecommerce') ? route('admin.ecommerce.shipping-methods.index') : 'javascript:void(0);'"
                            :active="request()->routeIs('admin.ecommerce.shipping-methods.*')"
                            :class="!auth()->user()->can('view ecommerce') ? 'pointer-events-none opacity-50 cursor-not-allowed' : ''"
                        >
                            <i class="material-icons-outlined">local_shipping</i>
                            <span>Shipping</span>
                        </x-sidebar-nav-link>
                    </li>

                    {{-- Tax Rules --}}
                    <li>
                        <x-sidebar-nav-link
                            :href="auth()->user()->can('view ecommerce') ? route('admin.ecommerce.tax-rules.index') : 'javascript:void(0);'"
                            :active="request()->routeIs('admin.ecommerce.tax-rules.*')"
                            :class="!auth()->user()->can('view ecommerce') ? 'pointer-events-none opacity-50 cursor-not-allowed' : ''"
                        >
                            <i class="material-icons-outlined">percent</i>
                            <span>Tax Rules</span>
                        </x-sidebar-nav-link>
                    </li>

                    {{-- Payments --}}
                    <li>
                        <x-sidebar-nav-link
                            :href="auth()->user()->can('view ecommerce') ? route('admin.ecommerce.payment-providers.index') : 'javascript:void(0);'"
                            :active="request()->routeIs('admin.ecommerce.payment-providers.*')"
                            :class="!auth()->user()->can('view ecommerce') ? 'pointer-events-none opacity-50 cursor-not-allowed' : ''"
                        >
                            <i class="material-icons-outlined">credit_card</i>
                            <span>Payments</span>
                        </x-sidebar-nav-link>
                    </li>

                    {{-- Settings --}}
                    <li>
                        <x-sidebar-nav-link
                            :href="auth()->user()->can('view ecommerce') ? route('admin.ecommerce.settings.index') : 'javascript:void(0);'"
                            :active="request()->routeIs('admin.ecommerce.settings.*')"
                            :class="!auth()->user()->can('view ecommerce') ? 'pointer-events-none opacity-50 cursor-not-allowed' : ''"
                        >
                            <i class="material-icons-outlined">settings</i>
                            <span>Settings</span>
                        </x-sidebar-nav-link>
                    </li>
                </ul>
            </li>

            <hr>
        @endif


        @if ($settings['enable_newsletter'] ?? false)
            <li>
                <a href="javascript:;" class="has-arrow">
                    <div class="parent-icon">
                        <i class="material-icons-outlined">mark_email_read</i>
                    </div>
                    <div class="menu-title">{{ __('Newsletter') }}</div>
                </a>

                <ul>
                    {{-- Dashboard --}}
                    <li>
                        <x-sidebar-nav-link
                            :href="auth()->user()->can('view newsletter') ? route('admin.newsletter.dashboard') : 'javascript:void(0);'"
                            :active="request()->routeIs('admin.newsletter.dashboard')"
                            :class="!auth()->user()->can('view newsletter') ? 'pointer-events-none opacity-50 cursor-not-allowed' : ''"
                            :title="auth()->user()->can('view newsletter') ? '' : __('You don\'t have permission to view this page')"
                        >
                            <i class="material-icons-outlined">dashboard</i>
                            <span>{{ __('Dashboard') }}</span>
                        </x-sidebar-nav-link>
                    </li>

                    {{-- Subscribers --}}
                    <li>
                        <x-sidebar-nav-link
                            :href="auth()->user()->can('view newsletter subscribers') ? route('admin.newsletter.subscribers.index') : 'javascript:void(0);'"
                            :active="request()->routeIs('admin.newsletter.subscribers.*')"
                            :class="!auth()->user()->can('view newsletter subscribers') ? 'pointer-events-none opacity-50 cursor-not-allowed' : ''"
                        >
                            <i class="material-icons-outlined">people</i>
                            <span>{{ __('Subscribers') }}</span>
                        </x-sidebar-nav-link>
                    </li>

                    {{-- Lists --}}
                    <li>
                        <x-sidebar-nav-link
                            :href="auth()->user()->can('view newsletter lists') ? route('admin.newsletter.lists.index') : 'javascript:void(0);'"
                            :active="request()->routeIs('admin.newsletter.lists.*')"
                            :class="!auth()->user()->can('view newsletter lists') ? 'pointer-events-none opacity-50 cursor-not-allowed' : ''"
                        >
                            <i class="material-icons-outlined">collections_bookmark</i>
                            <span>{{ __('Lists') }}</span>
                        </x-sidebar-nav-link>
                    </li>

                    {{-- Templates --}}
                    <li>
                        <x-sidebar-nav-link
                            :href="auth()->user()->can('view newsletter templates') ? route('admin.newsletter.templates.index') : 'javascript:void(0);'"
                            :active="request()->routeIs('admin.newsletter.templates.*')"
                            :class="!auth()->user()->can('view newsletter templates') ? 'pointer-events-none opacity-50 cursor-not-allowed' : ''"
                        >
                            <i class="material-icons-outlined">description</i>
                            <span>{{ __('Templates') }}</span>
                        </x-sidebar-nav-link>
                    </li>

                    {{-- Campaigns --}}
                    <li>
                        <x-sidebar-nav-link
                            :href="auth()->user()->can('view newsletter campaigns') ? route('admin.newsletter.campaigns.index') : 'javascript:void(0);'"
                            :active="request()->routeIs('admin.newsletter.campaigns.*')"
                            :class="!auth()->user()->can('view newsletter campaigns') ? 'pointer-events-none opacity-50 cursor-not-allowed' : ''"
                        >
                            <i class="material-icons-outlined">campaign</i>
                            <span>{{ __('Campaigns') }}</span>
                        </x-sidebar-nav-link>
                    </li>

                    {{-- Settings (integration / driver) --}}
                    <li>
                        <x-sidebar-nav-link
                            :href="auth()->user()->can('view newsletter settings') ? route('admin.newsletter.settings.index') : 'javascript:void(0);'"
                            :active="request()->routeIs('admin.newsletter.settings.*')"
                            :class="!auth()->user()->can('view newsletter settings') ? 'pointer-events-none opacity-50 cursor-not-allowed' : ''"
                        >
                            <i class="material-icons-outlined">settings_input_component</i>
                            <span>{{ __('Settings') }}</span>
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
                        :href="auth()->user()->can('view forum') ? route('admin.forum') : 'javascript:void(0);'"
                        :active="request()->routeIs('admin.forum')"
                        :class="!auth()->user()->can('view forum') ? 'pointer-events-none opacity-50 cursor-not-allowed' : ''"
                        :title="auth()->user()->can('view forum') ? '' : __('You don\'t have permission to view this page')"
                    >
                        <i class="material-icons-outlined">arrow_right</i>
                        <span>Basic</span>
                    </x-sidebar-nav-link>
                    </li>

                    <li>
                    <x-sidebar-nav-link
                        :href="auth()->user()->can('view forum') ? route('admin.forum') : 'javascript:void(0);'"
                        :active="request()->routeIs('admin.forum')"
                        :class="!auth()->user()->can('view forum') ? 'pointer-events-none opacity-50 cursor-not-allowed' : ''"
                        :title="auth()->user()->can('view forum') ? '' : __('You don\'t have permission to view this page')"
                    >
                        <i class="material-icons-outlined">arrow_right</i>
                        <span>Advance</span>
                    </x-sidebar-nav-link>
                    </li>

                    <li>
                    <x-sidebar-nav-link
                        :href="auth()->user()->can('view forum') ? route('admin.forum') : 'javascript:void(0);'"
                        :active="request()->routeIs('admin.forum')"
                        :class="!auth()->user()->can('view forum') ? 'pointer-events-none opacity-50 cursor-not-allowed' : ''"
                        :title="auth()->user()->can('view forum') ? '' : __('You don\'t have permission to view this page')"
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
                :href="auth()->user()->can('view sites') ? route('admin.sites') : 'javascript:void(0);'"
                :active="request()->routeIs('admin.sites*')"
                :class="!auth()->user()->can('view sites') ? 'pointer-events-none opacity-50 cursor-not-allowed' : ''"
                :title="auth()->user()->can('view sites') ? '' : __('You don\'t have permission to view this page')"
            >
                <i class="fadeIn animated bx bx-window-alt" style="font-size: 24px;"></i>
                <div class="menu-title">{{ __('Sites') }}</div>
            </x-sidebar-nav-link>
        </li>

        <li>
          <x-sidebar-nav-link
              :href="auth()->user()->can('view menus') ? route('admin.menus') : 'javascript:void(0);'"
              :active="request()->routeIs('admin.menus*')"
              :class="!auth()->user()->can('view menus') ? 'pointer-events-none opacity-50 cursor-not-allowed' : ''"
              :title="auth()->user()->can('view menus') ? '' : __('You don\'t have permission to view this page')"
          >
              <div class="parent-icon"><i class="material-icons-outlined">menu</i></div>
              <div class="menu-title">{{ __('Menus') }}</div>
            </x-sidebar-nav-link>
        </li>

        <li>
          <x-sidebar-nav-link
              :href="auth()->user()->can('view media') ? route('admin.media') : 'javascript:void(0);'"
              :active="request()->routeIs('admin.media*')"
              :class="!auth()->user()->can('view media') ? 'pointer-events-none opacity-50 cursor-not-allowed' : ''"
              :title="auth()->user()->can('view media') ? '' : __('You don\'t have permission to view this page')"
          >
              <div class="parent-icon"><i class="material-icons-outlined">collections</i></div>
              <div class="menu-title">{{ __('Media') }}</div>
            </x-sidebar-nav-link>
        </li>

        <hr>

        <li>
            <x-sidebar-nav-link
                :href="auth()->user()->can('view users') ? route('admin.users') : 'javascript:void(0);'"
                :active="request()->routeIs('admin.users*')"
                :class="!auth()->user()->can('view users') ? 'pointer-events-none opacity-50 cursor-not-allowed' : ''"
                :title="auth()->user()->can('view users') ? '' : __('You don\'t have permission to view this page')"
            >
                <div class="parent-icon"><i class="material-icons-outlined">people</i></div>
                <div class="menu-title">{{ __('Users') }}</div>
            </x-sidebar-nav-link>
        </li>
    
        <li>
            <x-sidebar-nav-link
                :href="auth()->user()->can('view permissions') ? route('admin.permissions') : 'javascript:void(0);'"
                :active="request()->routeIs('admin.permissions*')"
                :class="!auth()->user()->can('view permissions') ? 'pointer-events-none opacity-50 cursor-not-allowed' : ''"
                :title="auth()->user()->can('view permissions') ? '' : __('You don\'t have permission to view this page')"
            >
                <div class="parent-icon"><i class="material-icons-outlined">vpn_key</i></div>
                <div class="menu-title">{{ __('Permissions') }}</div>
            </x-sidebar-nav-link>
        </li>

        <hr>

        <li>
          <x-sidebar-nav-link
              :href="auth()->user()->can('view custom') ? route('admin.custom') : 'javascript:void(0);'"
              :active="request()->routeIs('admin.custom*')"
              :class="!auth()->user()->can('view custom') ? 'pointer-events-none opacity-50 cursor-not-allowed' : ''"
              :title="auth()->user()->can('view custom') ? '' : __('You don\'t have permission to view this page')"
          >
              <div class="parent-icon"><i class="material-icons-outlined">extension</i></div>
              <div class="menu-title">{{ __('Custom') }}</div>
            </x-sidebar-nav-link>
        </li>

        <li>
            <x-sidebar-nav-link
                :href="auth()->user()->can('view cookies') ? route('admin.cookies') : 'javascript:void(0);'"
                :active="request()->routeIs('admin.cookies*')"
                :class="!auth()->user()->can('view cookies') ? 'pointer-events-none opacity-50 cursor-not-allowed' : ''"
                :title="auth()->user()->can('view cookies') ? '' : __('You don\'t have permission to view this page')"
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
                    :href="auth()->user()->can('view API') ? route('admin.api.index') : 'javascript:void(0);'"
                    :active="request()->routeIs('admin.api*')"
                    :class="!auth()->user()->can('view API') ? 'pointer-events-none opacity-50 cursor-not-allowed' : ''"
                    :title="auth()->user()->can('view API') ? '' : __('You don\'t have permission to view this page')"
                >
                    <div class="parent-icon"><i class="material-icons-outlined">code</i></div>
                    <div class="menu-title">{{ __('API') }}</div>
                </x-sidebar-nav-link>
            </li>
        @endif
        <li>
            <x-sidebar-nav-link
                :href="auth()->user()->can('view settings') ? route('admin.settings') : 'javascript:void(0);'"
                :active="request()->routeIs('admin.settings*')"
                :class="!auth()->user()->can('view settings') ? 'pointer-events-none opacity-50 cursor-not-allowed' : ''"
                :title="auth()->user()->can('view settings') ? '' : __('You don\'t have permission to view this page')"
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