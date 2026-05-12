<!--start header-->
<header class="top-header">
    <nav class="navbar navbar-expand align-items-center gap-4">
      <div class="btn-toggle">
        <a href="javascript:;"><i class="material-icons-outlined">menu</i></a>
      </div>
      <div class="search-bar flex-grow-1" x-data="globalSearch()" @keydown.escape.window="closeSearch()" @open-global-search.window="openSearch()">
        <div class="position-relative">
          <input
            class="form-control rounded-5 px-5 search-control d-lg-block d-none"
            type="text"
            placeholder="Search pages, articles, media..."
            x-model="query"
            @input.debounce.300ms="performSearch()"
            @focus="showPopup = true"
            @keydown.arrow-down.prevent="navigateResults(1)"
            @keydown.arrow-up.prevent="navigateResults(-1)"
            @keydown.enter.prevent="goToSelected()"
          >
          <span class="material-icons-outlined position-absolute d-lg-block d-none ms-3 translate-middle-y start-0 top-50">search</span>
          <span
            class="material-icons-outlined position-absolute me-3 translate-middle-y end-0 top-50 search-close"
            style="cursor: pointer;"
            @click="closeSearch()"
            x-show="showPopup || query.length > 0"
          >close</span>

          <!-- Search Popup -->
          <div class="search-popup p-3" x-show="showPopup" @click.outside="closeSearch()" x-cloak>
            <div class="card rounded-4 overflow-hidden">
              <!-- Mobile search input -->
              <div class="card-header d-lg-none">
                <div class="position-relative">
                  <input
                    class="form-control rounded-5 px-5 mobile-search-control"
                    type="text"
                    placeholder="Search..."
                    x-model="query"
                    @input.debounce.300ms="performSearch()"
                  >
                  <span class="material-icons-outlined position-absolute ms-3 translate-middle-y start-0 top-50">search</span>
                  <span class="material-icons-outlined position-absolute me-3 translate-middle-y end-0 top-50 mobile-search-close" @click="closeSearch()">close</span>
                </div>
              </div>

              <div class="card-body search-content" style="max-height: 400px; overflow-y: auto;">
                <!-- Loading state -->
                <div x-show="loading" class="text-center py-4">
                  <div class="spinner-border spinner-border-sm text-primary" role="status">
                    <span class="visually-hidden">Searching...</span>
                  </div>
                  <p class="mb-0 mt-2 text-muted small">Searching...</p>
                </div>

                <!-- Empty state - show tips when no search yet -->
                <template x-if="!loading && query.length < 2 && Object.keys(results).length === 0">
                  <div>
                    <p class="search-title">Quick Navigation</p>
                    <div class="search-list d-flex flex-column gap-2">
                      <a href="{{ route('admin.sites') }}" class="search-list-item d-flex align-items-center gap-3 text-decoration-none">
                        <div class="list-icon"><i class="material-icons-outlined fs-5">web</i></div>
                        <div><h5 class="mb-0 search-list-title">All Pages</h5></div>
                      </a>
                      <a href="{{ route('admin.media') }}" class="search-list-item d-flex align-items-center gap-3 text-decoration-none">
                        <div class="list-icon"><i class="material-icons-outlined fs-5">perm_media</i></div>
                        <div><h5 class="mb-0 search-list-title">Media Library</h5></div>
                      </a>
                      @can('view users')
                      <a href="{{ route('admin.users') }}" class="search-list-item d-flex align-items-center gap-3 text-decoration-none">
                        <div class="list-icon"><i class="material-icons-outlined fs-5">people</i></div>
                        <div><h5 class="mb-0 search-list-title">Users</h5></div>
                      </a>
                      @endcan
                      <a href="{{ route('admin.settings') }}" class="search-list-item d-flex align-items-center gap-3 text-decoration-none">
                        <div class="list-icon"><i class="material-icons-outlined fs-5">settings</i></div>
                        <div><h5 class="mb-0 search-list-title">Settings</h5></div>
                      </a>
                    </div>
                    <hr>
                    <p class="text-muted small mb-0">
                      <i class="material-icons-outlined align-middle" style="font-size: 14px;">keyboard</i>
                      Type at least 2 characters to search
                      <span class="ms-2 badge bg-secondary bg-opacity-50">Ctrl+K</span>
                    </p>
                  </div>
                </template>

                <!-- No results found -->
                <template x-if="!loading && query.length >= 2 && Object.keys(results).length === 0 && searchPerformed">
                  <div class="text-center py-4">
                    <i class="material-icons-outlined text-muted" style="font-size: 48px;">search_off</i>
                    <p class="mb-0 mt-2 text-muted">No results found for "<span x-text="query"></span>"</p>
                    <p class="small text-muted">Try different keywords or check spelling</p>
                  </div>
                </template>

                <!-- Search Results -->
                <template x-if="!loading && Object.keys(results).length > 0">
                  <div>
                    <template x-for="(group, groupKey) in results" :key="groupKey">
                      <div class="mb-3">
                        <p class="search-title d-flex align-items-center gap-2">
                          <i class="material-icons-outlined fs-6" x-text="group.icon"></i>
                          <span x-text="group.label"></span>
                          <span class="badge bg-light text-dark ms-auto" x-text="group.items.length"></span>
                        </p>
                        <div class="search-list d-flex flex-column gap-1">
                          <template x-for="(item, itemIndex) in group.items" :key="item.id">
                            <a
                              :href="item.url"
                              class="search-list-item d-flex align-items-center gap-3 text-decoration-none p-2 rounded"
                              :class="{ 'bg-light': selectedIndex === getGlobalIndex(groupKey, itemIndex) }"
                              @mouseenter="selectedIndex = getGlobalIndex(groupKey, itemIndex)"
                            >
                              <!-- Avatar for users -->
                              <template x-if="item.avatar">
                                <div class="flex-shrink-0">
                                  <img :src="item.avatar" width="32" height="32" class="rounded-circle" alt="">
                                </div>
                              </template>

                              <!-- Thumbnail for media -->
                              <template x-if="item.thumbnail && !item.avatar">
                                <div class="flex-shrink-0">
                                  <img :src="item.thumbnail" width="32" height="32" class="rounded" style="object-fit: cover;" alt="">
                                </div>
                              </template>

                              <!-- Icon fallback -->
                              <template x-if="!item.avatar && !item.thumbnail">
                                <div class="list-icon flex-shrink-0">
                                  <i class="material-icons-outlined fs-5" x-text="group.icon"></i>
                                </div>
                              </template>

                              <div class="flex-grow-1 min-width-0">
                                <h5 class="mb-0 search-list-title text-truncate" x-text="item.title"></h5>
                                <small class="text-muted text-truncate d-block" x-text="item.subtitle"></small>
                              </div>

                              <!-- Status badge for pages/articles -->
                              <template x-if="item.status">
                                <span
                                  class="badge rounded-pill"
                                  :class="{
                                    'bg-success': item.status === 'published',
                                    'bg-warning text-dark': item.status === 'draft',
                                    'bg-info': item.status === 'scheduled',
                                    'bg-secondary': item.status === 'private'
                                  }"
                                  x-text="item.status"
                                ></span>
                              </template>
                            </a>
                          </template>
                        </div>
                        <hr class="my-2">
                      </div>
                    </template>
                  </div>
                </template>
              </div>

              <!-- Footer with result count -->
              <template x-if="!loading && totalCount > 0">
                <div class="card-footer text-center bg-transparent py-2">
                  <small class="text-muted">
                    Found <span x-text="totalCount"></span> result<span x-show="totalCount !== 1">s</span>
                  </small>
                </div>
              </template>
            </div>
          </div>
        </div>
      </div>
      <ul class="navbar-nav gap-1 nav-right-links align-items-center">
        <li class="nav-item d-lg-none mobile-search-btn" x-data @click="$dispatch('open-global-search')">
          <a class="nav-link" href="javascript:;"><i class="material-icons-outlined">search</i></a>
        </li>
        <li class="nav-item dropdown position-static">
          <a class="nav-link dropdown-toggle dropdown-toggle-nocaret" data-bs-auto-close="outside"
          data-bs-toggle="dropdown" href="javascript:;"><i class="material-icons-outlined">apps</i></a>
          <div class="dropdown-menu dropdown-menu-end mega-menu shadow-lg p-4 p-lg-5">
            <div class="mega-menu-widgets">
             <div class="row row-cols-1 row-cols-lg-2 row-cols-xl-3 g-4 g-lg-5">
                
              <div class="col">
                  <a href="{{ route('admin.tools.qr-generator') }}" class="text-decoration-none text-reset">
                    <div class="card rounded-4 shadow-none border mb-0 h-100" style="transition:box-shadow .15s" onmouseover="this.style.boxShadow='0 4px 16px rgba(0,0,0,.1)'" onmouseout="this.style.boxShadow=''">
                      <div class="card-body">
                        <div class="d-flex align-items-start gap-3">
                          <div class="flex-shrink-0 d-flex align-items-center justify-content-center rounded-3 bg-primary bg-opacity-10" style="width:40px;height:40px">
                            <i class="material-icons-outlined text-primary">qr_code_2</i>
                          </div>
                          <div class="mega-menu-content">
                            <h5 class="mb-1">QR Generator</h5>
                            <p class="mb-0 f-14">Generate QR codes for URLs, text, emails and more. Customize size, colors and error correction, then download as PNG.</p>
                          </div>
                        </div>
                      </div>
                    </div>
                  </a>
                </div>

                {{-- <div class="col">
                  <div class="card rounded-4 shadow-none border mb-0">
                    <div class="card-body">
                      <div class="d-flex align-items-start gap-3">
                        <img src="{{ asset('assets/admin/theme/assets/images/megaIcons/02.png') }}" width="40" alt="">
                        <div class="mega-menu-content">
                           <h5>Website</h5>
                           <p class="mb-0 f-14">In publishing and graphic design, Lorem ipsum is a placeholder text commonly used to demonstrate
                             the visual form of a document.</p>
                        </div>
                     </div>
                    </div>
                  </div>
                </div>
                <div class="col">
                  <div class="card rounded-4 shadow-none border mb-0">
                    <div class="card-body">
                      <div class="d-flex align-items-start gap-3">
                        <img src="{{ asset('assets/admin/theme/assets/images/megaIcons/03.png') }}" width="40" alt="">
                        <div class="mega-menu-content">
                            <h5>Subscribers</h5>
                           <p class="mb-0 f-14">In publishing and graphic design, Lorem ipsum is a placeholder text commonly used to demonstrate
                             the visual form of a document.</p>
                        </div>
                     </div>
                    </div>
                  </div>
                </div>
                <div class="col">
                  <div class="card rounded-4 shadow-none border mb-0">
                    <div class="card-body">
                      <div class="d-flex align-items-start gap-3">
                        <img src="{{ asset('assets/admin/theme/assets/images/megaIcons/01.png') }}" width="40" alt="">
                        <div class="mega-menu-content">
                           <h5>Hubspot</h5>
                           <p class="mb-0 f-14">In publishing and graphic design, Lorem ipsum is a placeholder text commonly used to demonstrate
                             the visual form of a document.</p>
                        </div>
                     </div>
                    </div>
                  </div>
                </div>
                <div class="col">
                  <div class="card rounded-4 shadow-none border mb-0">
                    <div class="card-body">
                      <div class="d-flex align-items-start gap-3">
                        <img src="{{ asset('assets/admin/theme/assets/images/megaIcons/11.png') }}" width="40" alt="">
                        <div class="mega-menu-content">
                           <h5>Templates</h5>
                           <p class="mb-0 f-14">In publishing and graphic design, Lorem ipsum is a placeholder text commonly used to demonstrate
                             the visual form of a document.</p>
                        </div>
                     </div>
                    </div>
                  </div>
                </div>
                <div class="col">
                  <div class="card rounded-4 shadow-none border mb-0">
                    <div class="card-body">
                      <div class="d-flex align-items-start gap-3">
                        <img src="{{ asset('assets/admin/theme/assets/images/megaIcons/13.png') }}" width="40" alt="">
                        <div class="mega-menu-content">
                           <h5>Ebooks</h5>
                           <p class="mb-0 f-14">In publishing and graphic design, Lorem ipsum is a placeholder text commonly used to demonstrate
                             the visual form of a document.</p>
                        </div>
                     </div>
                    </div>
                  </div>
                </div>
                <div class="col">
                  <div class="card rounded-4 shadow-none border mb-0">
                    <div class="card-body">
                      <div class="d-flex align-items-start gap-3">
                        <img src="{{ asset('assets/admin/theme/assets/images/megaIcons/12.png') }}" width="40" alt="">
                        <div class="mega-menu-content">
                           <h5>Sales</h5>
                           <p class="mb-0 f-14">In publishing and graphic design, Lorem ipsum is a placeholder text commonly used to demonstrate
                             the visual form of a document.</p>
                        </div>
                     </div>
                    </div>
                  </div>
                </div>
                <div class="col">
                  <div class="card rounded-4 shadow-none border mb-0">
                    <div class="card-body">
                      <div class="d-flex align-items-start gap-3">
                        <img src="{{ asset('assets/admin/theme/assets/images/megaIcons/08.png') }}" width="40" alt="">
                        <div class="mega-menu-content">
                           <h5>Tools</h5>
                           <p class="mb-0 f-14">In publishing and graphic design, Lorem ipsum is a placeholder text commonly used to demonstrate
                             the visual form of a document.</p>
                        </div>
                     </div>
                    </div>
                  </div>
                </div>
                <div class="col">
                  <div class="card rounded-4 shadow-none border mb-0">
                    <div class="card-body">
                      <div class="d-flex align-items-start gap-3">
                        <img src="{{ asset('assets/admin/theme/assets/images/megaIcons/09.png') }}" width="40" alt="">
                        <div class="mega-menu-content">
                           <h5>Academy</h5>
                           <p class="mb-0 f-14">In publishing and graphic design, Lorem ipsum is a placeholder text commonly used to demonstrate
                             the visual form of a document.</p>
                        </div>
                     </div>
                    </div>
                  </div>
                </div> --}}
             </div><!--end row-->
            </div>
          </div>
        </li>
        {{-- 
        <li class="nav-item dropdown">
          <a class="nav-link dropdown-toggle dropdown-toggle-nocaret" data-bs-auto-close="outside"
            data-bs-toggle="dropdown" href="javascript:;"><i class="material-icons-outlined">done_all</i></a>
          <div class="dropdown-menu dropdown-menu-end dropdown-apps shadow-lg p-3">
            <div class="border rounded-4 overflow-hidden">
              <div class="row row-cols-3 g-0 border-bottom">
                <div class="col border-end">
                  <div class="app-wrapper d-flex flex-column gap-2 text-center">
                    <div class="app-icon">
                      <img src="{{ asset('assets/admin/theme/assets/images/apps/01.png') }}" width="36" alt="">
                    </div>
                    <div class="app-name">
                      <p class="mb-0">Gmail</p>
                    </div>
                  </div>
                </div>
                <div class="col border-end">
                  <div class="app-wrapper d-flex flex-column gap-2 text-center">
                    <div class="app-icon">
                      <img src="{{ asset('assets/admin/theme/assets/images/apps/02.png') }}" width="36" alt="">
                    </div>
                    <div class="app-name">
                      <p class="mb-0">Skype</p>
                    </div>
                  </div>
                </div>
                <div class="col">
                  <div class="app-wrapper d-flex flex-column gap-2 text-center">
                    <div class="app-icon">
                      <img src="{{ asset('assets/admin/theme/assets/images/apps/03.png') }}" width="36" alt="">
                    </div>
                    <div class="app-name">
                      <p class="mb-0">Slack</p>
                    </div>
                  </div>
                </div>
              </div><!--end row-->

              <div class="row row-cols-3 g-0 border-bottom">
                <div class="col border-end">
                  <div class="app-wrapper d-flex flex-column gap-2 text-center">
                    <div class="app-icon">
                      <img src="{{ asset('assets/admin/theme/assets/images/apps/04.png') }}" width="36" alt="">
                    </div>
                    <div class="app-name">
                      <p class="mb-0">YouTube</p>
                    </div>
                  </div>
                </div>
                <div class="col border-end">
                  <div class="app-wrapper d-flex flex-column gap-2 text-center">
                    <div class="app-icon">
                      <img src="{{ asset('assets/admin/theme/assets/images/apps/05.png') }}" width="36" alt="">
                    </div>
                    <div class="app-name">
                      <p class="mb-0">Google</p>
                    </div>
                  </div>
                </div>
                <div class="col">
                  <div class="app-wrapper d-flex flex-column gap-2 text-center">
                    <div class="app-icon">
                      <img src="{{ asset('assets/admin/theme/assets/images/apps/06.png') }}" width="36" alt="">
                    </div>
                    <div class="app-name">
                      <p class="mb-0">Instagram</p>
                    </div>
                  </div>
                </div>
              </div><!--end row-->

              <div class="row row-cols-3 g-0 border-bottom">
                <div class="col border-end">
                  <div class="app-wrapper d-flex flex-column gap-2 text-center">
                    <div class="app-icon">
                      <img src="{{ asset('assets/admin/theme/assets/images/apps/07.png') }}" width="36" alt="">
                    </div>
                    <div class="app-name">
                      <p class="mb-0">Spotify</p>
                    </div>
                  </div>
                </div>
                <div class="col border-end">
                  <div class="app-wrapper d-flex flex-column gap-2 text-center">
                    <div class="app-icon">
                      <img src="{{ asset('assets/admin/theme/assets/images/apps/08.png') }}" width="36" alt="">
                    </div>
                    <div class="app-name">
                      <p class="mb-0">Yahoo</p>
                    </div>
                  </div>
                </div>
                <div class="col">
                  <div class="app-wrapper d-flex flex-column gap-2 text-center">
                    <div class="app-icon">
                      <img src="{{ asset('assets/admin/theme/assets/images/apps/09.png') }}" width="36" alt="">
                    </div>
                    <div class="app-name">
                      <p class="mb-0">Facebook</p>
                    </div>
                  </div>
                </div>
              </div><!--end row-->

              <div class="row row-cols-3 g-0">
                <div class="col border-end">
                  <div class="app-wrapper d-flex flex-column gap-2 text-center">
                    <div class="app-icon">
                      <img src="{{ asset('assets/admin/theme/assets/images/apps/10.png') }}" width="36" alt="">
                    </div>
                    <div class="app-name">
                      <p class="mb-0">Figma</p>
                    </div>
                  </div>
                </div>
                <div class="col border-end">
                  <div class="app-wrapper d-flex flex-column gap-2 text-center">
                    <div class="app-icon">
                      <img src="{{ asset('assets/admin/theme/assets/images/apps/11.png') }}" width="36" alt="">
                    </div>
                    <div class="app-name">
                      <p class="mb-0">Paypal</p>
                    </div>
                  </div>
                </div>
                <div class="col">
                  <div class="app-wrapper d-flex flex-column gap-2 text-center">
                    <div class="app-icon">
                      <img src="{{ asset('assets/admin/theme/assets/images/apps/12.png') }}" width="36" alt="">
                    </div>
                    <div class="app-name">
                      <p class="mb-0">Photo</p>
                    </div>
                  </div>
                </div>
              </div><!--end row-->
            </div>
          </div>
        </li> --}}
        <li class="nav-item dropdown"
            x-data="adminNotifications({{ $adminNotifications->toJson() }}, {{ $notificationUnreadCount }})">
          <a class="nav-link dropdown-toggle dropdown-toggle-nocaret position-relative" data-bs-auto-close="outside"
            data-bs-toggle="dropdown" href="javascript:;">
            <i class="material-icons-outlined">notifications</i>
            <span class="badge-notify" x-show="unreadCount > 0" x-text="unreadCount > 99 ? '99+' : unreadCount"></span>
          </a>
          <div class="dropdown-menu dropdown-notify dropdown-menu-end shadow">
            <div class="px-3 py-1 d-flex align-items-center justify-content-between border-bottom">
              <h5 class="notiy-title mb-0">Notifications</h5>
              <div class="dropdown">
                <button class="btn btn-secondary dropdown-toggle dropdown-toggle-nocaret option" type="button"
                  data-bs-toggle="dropdown" aria-expanded="false">
                  <span class="material-icons-outlined">more_vert</span>
                </button>
                <div class="dropdown-menu dropdown-option dropdown-menu-end shadow">
                  <div>
                    <a class="dropdown-item d-flex align-items-center gap-2 py-2" href="javascript:;"
                       @click="markAllRead()">
                      <i class="material-icons-outlined fs-6">done_all</i>Mark all as read
                    </a>
                  </div>
                  <div>
                    <a class="dropdown-item d-flex align-items-center gap-2 py-2 text-danger" href="javascript:;"
                       @click="dismissAll()">
                      <i class="material-icons-outlined fs-6">delete_sweep</i>Dismiss all
                    </a>
                  </div>
                </div>
              </div>
            </div>
            <div class="notify-list">
              <template x-if="notifications.length === 0">
                <div class="text-center py-4 px-3">
                  <i class="material-icons-outlined text-muted" style="font-size:40px;">notifications_none</i>
                  <p class="mb-0 mt-2 text-muted small">No notifications</p>
                </div>
              </template>
              <template x-for="(n, index) in notifications" :key="n.id">
                <div>
                  <a class="dropdown-item py-2 position-relative"
                     :class="{ 'border-bottom': index < notifications.length - 1, 'bg-light bg-opacity-75': !n.read_at }"
                     :href="n.url ?? 'javascript:;'"
                     @click="n.url ? markRead(n.id) : null">
                    <div class="d-flex align-items-center gap-3">
                      <div class="flex-shrink-0 d-flex align-items-center justify-content-center rounded-circle"
                           :class="`bg-${n.icon_color} bg-opacity-10 text-${n.icon_color}`"
                           style="width:42px;height:42px">
                        <i class="material-icons-outlined fs-5" x-text="n.icon"></i>
                      </div>
                      <div class="flex-grow-1 min-width-0 pe-4">
                        <h5 class="notify-title mb-0 d-flex align-items-center gap-2">
                          <span x-text="n.title"></span>
                          <span x-show="!n.read_at" class="badge rounded-pill"
                                :class="`bg-${n.icon_color}`" style="font-size:9px">new</span>
                        </h5>
                        <p class="mb-0 notify-desc text-truncate" x-text="n.message"></p>
                        <p class="mb-0 notify-time" x-text="n.created_at_human"></p>
                      </div>
                      <div class="notify-close position-absolute end-0 me-3"
                           @click.prevent.stop="dismiss(n.id)">
                        <i class="material-icons-outlined fs-6">close</i>
                      </div>
                    </div>
                  </a>
                </div>
              </template>
            </div>
          </div>
        </li>
        
        <li class="nav-item dropdown">
          <a href="javascript:;" class="dropdown-toggle dropdown-toggle-nocaret" data-bs-toggle="dropdown">
            <img src="{{ auth()->user()->avatar_url }}" class="rounded-circle p-1 border" width="45" height="45" alt="{{ __('User avatar') }}">
          </a>

          <div class="dropdown-menu dropdown-user dropdown-menu-end shadow">
            <a class="dropdown-item gap-2 py-2" href="{{ route('admin.user.profile', auth()->user()->id) }}">
              <div class="text-center">
                <img src="{{ auth()->user()->avatar_url }}" class="rounded-circle p-1 shadow mb-3" width="90" height="90" alt="{{ __('User avatar') }}">
                <h5 class="user-name mb-0 fw-bold">Hello, {{ auth()->user()->username }}</h5>
              </div>
            </a>

            <hr class="dropdown-divider">
            <a class="dropdown-item d-flex align-items-center gap-2 py-2" href="{{ route('admin.user.profile', auth()->user()->id) }}">
                <i class="material-icons-outlined">person_outline</i>Profile
            </a>
        

            <a class="dropdown-item d-flex align-items-center gap-2 py-2" href="{{ route('admin.user.settings', auth()->id()) }}">
              <i class="material-icons-outlined">settings</i>{{ __('Settings') }}
            </a>
            

            {{-- <a class="dropdown-item d-flex align-items-center gap-2 py-2" href="javascript:;"><i
              class="material-icons-outlined">dashboard</i>Dashboard</a>

            <a class="dropdown-item d-flex align-items-center gap-2 py-2" href="javascript:;"><i
              class="material-icons-outlined">account_balance</i>Earning</a>

            <a class="dropdown-item d-flex align-items-center gap-2 py-2" href="javascript:;"><i
              class="material-icons-outlined">cloud_download</i>Downloads</a> --}}

            <hr class="dropdown-divider">

            <form method="POST" action="{{ route('logout') }}" class="dropdown-item p-0 m-0">
              @csrf
              <button type="submit" class="btn w-100 text-start d-flex align-items-center gap-2 py-2 px-3 border-0 bg-transparent">
                  <i class="material-icons-outlined">power_settings_new</i> {{ __('Logout') }}
              </button>
          </form>
          
          </div>
        </li>
      </ul>

    </nav>
  </header>
  <!--end top header-->