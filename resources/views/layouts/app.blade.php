<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" style="visibility:hidden;">

<script>
    (function () {
      const theme = localStorage.getItem('theme') || 'dark';
      document.documentElement.setAttribute('data-bs-theme', theme);
      document.documentElement.style.visibility = 'visible';
    })();
  </script>

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="icon" href="{{ asset('assets/admin/images/agares-logo.png') }}" type="image/x-icon">
    <title>{{ config('app.name', 'Agares') }}</title>

    <!-- Fonts -->
    {{-- <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
    
    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link href="{{ asset('build/assets/app.css') }}" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('assets/admin/css/style.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/admin/css/custom.css') }}"> --}}

    <!-- Include the Monaco Editor script (from a CDN or your local setup) -->
    {{-- <script src="https://cdnjs.cloudflare.com/ajax/libs/monaco-editor/0.20.0/min/vs/loader.min.js"></script> --}}

    {{-- theme --}}

    <!--plugins-->
    <link href="{{ asset('assets/admin/theme/assets/plugins/perfect-scrollbar/css/perfect-scrollbar.css') }}" rel="stylesheet">
    <link rel="stylesheet" type="text/css" href="{{ asset('assets/admin/theme/assets/plugins/metismenu/metisMenu.min.css') }}">
    <link rel="stylesheet" type="text/css" href="{{ asset('assets/admin/theme/assets/plugins/metismenu/mm-vertical.css') }}">
    <link rel="stylesheet" type="text/css" href="{{ asset('assets/admin/theme/assets/plugins/simplebar/css/simplebar.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/admin/theme/assets/plugins/notifications/css/lobibox.min.css') }}">

    <!--bootstrap css-->
    <link href="{{ asset('assets/admin/theme/assets/css/bootstrap.min.css') }}" rel="stylesheet">
    <link href="{{ asset('assets/admin/theme/assets/css/extra-icons.css') }}" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Noto+Sans:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css?family=Material+Icons+Outlined" rel="stylesheet">

    <!--main css-->
    <link href="{{ asset('assets/admin/theme/assets/css/bootstrap-extended.css') }}" rel="stylesheet">
    <link href="{{ asset('assets/admin/theme/sass/main.css') }}" rel="stylesheet">
    <link href="{{ asset('assets/admin/theme/sass/dark-theme.css') }}" rel="stylesheet">
    <link href="{{ asset('assets/admin/theme/sass/semi-dark.css') }}" rel="stylesheet">
    <link href="{{ asset('assets/admin/theme/sass/bordered-theme.css') }}" rel="stylesheet">
    <link href="{{ asset('assets/admin/theme/sass/responsive.css') }}" rel="stylesheet">
    
    {{-- fancy fileupload --}}
    <link href="{{ asset('assets/admin/theme/assets/plugins/fancy-file-uploader/fancy_fileupload.css') }}" rel="stylesheet">
    
    {{-- custom --}}
    <link href="{{ asset('assets/admin/css/custom.css') }}" rel="stylesheet">

    <style>
      .menu-site-link {
        color: var(--bs-body-color);
        text-decoration: none;
        transition: background-color .15s ease, color .15s ease;
      }

      .menu-site-link:hover {
        background: var(--bs-tertiary-bg);
        color: var(--bs-body-color);
      }

      /* Active item */
      .menu-site-link.is-active {
        background: var(--bs-primary-bg-subtle);
        color: var(--bs-primary);
        font-weight: 600;
      }

      /* Accordion improvements (theme-safe) */
      #menuAccordion .accordion-item {
        background: var(--bs-body-bg);
        border-color: var(--bs-border-color);
      }

      #menuAccordion .accordion-button {
        background: var(--bs-body-bg);
        color: var(--bs-body-color);
      }

      #menuAccordion .accordion-button:not(.collapsed) {
        background: var(--bs-tertiary-bg);
        color: var(--bs-body-color);
      }

      #menuAccordion .accordion-body {
        background: var(--bs-body-bg);
      }

      .menu-tree-marker{
        width: 10px;
        height: 10px;
        border-left: 2px solid var(--bs-border-color);
        border-bottom: 2px solid var(--bs-border-color);
        display: inline-block;
        margin-right: .25rem;
        border-bottom-left-radius: 2px;
        opacity: .75;
      }

      .children-list-group
      {
        margin-left: 20px;
      }

      /* smoother bootstrap modal */
      .modal.fade .modal-dialog {
        transform: translateY(8px) scale(0.98);
        transition: transform .18s ease, opacity .18s ease;
      }
      .modal.show .modal-dialog {
        transform: translateY(0) scale(1);
      }

    .sticky-bottom-bar {
        position: fixed;
        bottom: 0;
        left: 260px;
        right: 0;
        z-index: 10;
        transition: ease-out 0.3s;
    }

    .toggled .sticky-bottom-bar {
        left: 70px;
    }

    </style>

    @stack('styles')
</head>

<body>
    
    {{-- all notifications --}}
    <x-notification />

    @include('includes.header')
    
    <!--start sidebar-->
    <aside class="sidebar-wrapper">
        @include('includes.sidebar')
    </aside>
    <!--end sidebar-->
  
    <!--start main wrapper-->
    <main class="main-wrapper">
      <div class="main-content">
        {{ $slot }}
      </div>
    </main>
    <!--end main wrapper-->
  
    <!--start overlay-->
      <div class="overlay btn-toggle"></div>
    <!--end overlay-->
  
    <!--start footer-->
        @include('includes.footer')
    <!--top footer-->
  
    <!--plugins-->
    <script src="{{ asset('assets/admin/theme/assets/js/jquery.min.js') }}"></script>
  
    <!--bootstrap js-->
    <script src="{{ asset('assets/admin/theme/assets/js/bootstrap.bundle.min.js') }}"></script>
  
    <!--plugins-->
    <script src="{{ asset('assets/admin/theme/assets/plugins/perfect-scrollbar/js/perfect-scrollbar.js') }}"></script>
    <script src="{{ asset('assets/admin/theme/assets/plugins/metismenu/metisMenu.min.js') }}"></script>
    <script src="{{ asset('assets/admin/theme/assets/plugins/apexchart/apexcharts.min.js') }}"></script>
    @if (request()->routeIs('admin.dashboard*'))
        <script src="{{ asset('assets/admin/theme/assets/js/index.js') }}"></script>
    @endif

    <!--notification js -->
	<script src="{{ asset('assets/admin/theme/assets/plugins/notifications/js/lobibox.min.js') }}"></script>
	<script src="{{ asset('assets/admin/theme/assets/plugins/notifications/js/notifications.min.js') }}"></script>
	<script src="{{ asset('assets/admin/theme/assets/plugins/notifications/js/notification-custom-script.js') }}"></script>

    <script src="{{ asset('assets/admin/theme/assets/plugins/peity/jquery.peity.min.js') }}"></script>
    <script>
      $(".data-attributes span").peity("donut")
    </script>
    <script src="{{ asset('assets/admin/theme/assets/plugins/simplebar/js/simplebar.min.js') }}"></script>
    <script src="{{ asset('assets/admin/theme/assets/js/main.js') }}"></script>

    <!-- Alpine.js -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <!-- Admin Notifications Component -->
    <script>
      document.addEventListener('alpine:init', () => {
        Alpine.data('adminNotifications', (initialNotifications, initialUnreadCount) => ({
          notifications: initialNotifications,
          unreadCount: initialUnreadCount,

          _fetch(url, method) {
            return fetch(url, {
              method,
              headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json',
              },
            });
          },

          markRead(id) {
            const n = this.notifications.find(n => n.id === id);
            if (!n || n.read_at) return;
            this._fetch(`/admin/notifications/${id}/read`, 'PATCH').then(() => {
              n.read_at = new Date().toISOString();
              this.unreadCount = Math.max(0, this.unreadCount - 1);
            });
          },

          markAllRead() {
            this._fetch('/admin/notifications/read-all', 'PATCH').then(() => {
              this.notifications.forEach(n => n.read_at = new Date().toISOString());
              this.unreadCount = 0;
            });
          },

          dismiss(id) {
            const idx = this.notifications.findIndex(n => n.id === id);
            if (idx === -1) return;
            this._fetch(`/admin/notifications/${id}`, 'DELETE').then(() => {
              if (!this.notifications[idx].read_at) {
                this.unreadCount = Math.max(0, this.unreadCount - 1);
              }
              this.notifications.splice(idx, 1);
            });
          },

          dismissAll() {
            this._fetch('/admin/notifications/dismiss-all', 'DELETE').then(() => {
              this.notifications = [];
              this.unreadCount = 0;
            });
          },
        }));
      });
    </script>

    <!-- Global Search Component -->
    <script>
      document.addEventListener('alpine:init', () => {
        Alpine.data('globalSearch', () => ({
          query: '',
          results: {},
          loading: false,
          showPopup: false,
          selectedIndex: -1,
          totalCount: 0,
          searchPerformed: false,
          abortController: null,

          init() {
            // Global keyboard shortcut: Ctrl+K or Cmd+K to open search
            document.addEventListener('keydown', (e) => {
              if ((e.ctrlKey || e.metaKey) && e.key === 'k') {
                e.preventDefault();
                this.openSearch();
              }
            });
          },

          openSearch() {
            this.showPopup = true;
            // Focus the search input after Alpine updates the DOM
            this.$nextTick(() => {
              const input = this.$el.querySelector('.search-control');
              if (input) input.focus();
            });
          },

          async performSearch() {
            if (this.query.length < 2) {
              this.results = {};
              this.totalCount = 0;
              this.searchPerformed = false;
              return;
            }

            // Cancel any pending request
            if (this.abortController) {
              this.abortController.abort();
            }

            this.loading = true;
            this.showPopup = true;
            this.abortController = new AbortController();

            try {
              const response = await fetch(`{{ route('admin.search') }}?q=${encodeURIComponent(this.query)}`, {
                headers: {
                  'Accept': 'application/json',
                  'X-Requested-With': 'XMLHttpRequest',
                },
                signal: this.abortController.signal
              });

              const data = await response.json();

              if (data.success) {
                this.results = data.results;
                this.totalCount = data.total_count;
              } else {
                this.results = {};
                this.totalCount = 0;
              }

              this.searchPerformed = true;
              this.selectedIndex = -1;
            } catch (error) {
              if (error.name !== 'AbortError') {
                console.error('Search error:', error);
                this.results = {};
                this.totalCount = 0;
              }
            } finally {
              this.loading = false;
            }
          },

          closeSearch() {
            this.showPopup = false;
            this.query = '';
            this.results = {};
            this.selectedIndex = -1;
            this.searchPerformed = false;
          },

          getAllItems() {
            const items = [];
            for (const groupKey in this.results) {
              for (const item of this.results[groupKey].items) {
                items.push({ groupKey, item });
              }
            }
            return items;
          },

          getGlobalIndex(groupKey, itemIndex) {
            let index = 0;
            for (const key in this.results) {
              if (key === groupKey) {
                return index + itemIndex;
              }
              index += this.results[key].items.length;
            }
            return -1;
          },

          navigateResults(direction) {
            const allItems = this.getAllItems();
            if (allItems.length === 0) return;

            this.selectedIndex += direction;

            if (this.selectedIndex < 0) {
              this.selectedIndex = allItems.length - 1;
            } else if (this.selectedIndex >= allItems.length) {
              this.selectedIndex = 0;
            }
          },

          goToSelected() {
            const allItems = this.getAllItems();
            if (this.selectedIndex >= 0 && this.selectedIndex < allItems.length) {
              window.location.href = allItems[this.selectedIndex].item.url;
            }
          }
        }));
      });
    </script>
  
    <script>
        $(function () {
            const $icon = $(".dark-mode i");

            // Set icon based on current theme
            const initialTheme = localStorage.getItem('theme') || 'dark';
            $icon.text(initialTheme === 'dark' ? 'light_mode' : 'dark_mode');

            // Toggle icon and theme on click
            $(".dark-mode").click(function () {
                // Get the current theme dynamically
                const currentTheme = localStorage.getItem('theme') || 'dark';
                const newTheme = currentTheme === 'dark' ? 'light' : 'dark';

                $("html").attr("data-bs-theme", newTheme);
                localStorage.setItem('theme', newTheme);

                $icon.text(newTheme === 'dark' ? 'light_mode' : 'dark_mode');

                // Update radio button (if used)
                const radio = document.getElementById(newTheme.charAt(0).toUpperCase() + newTheme.slice(1) + 'Theme');
                if (radio) radio.checked = true;
            });
        });

        </script>


      <!-- Fancy File Uploader dependencies -->
      @if(empty($disableFancyFileUpload) && empty($GLOBALS['disableFancyFileUpload']))
        <script src="{{ asset('assets/admin/theme/assets/plugins/fancy-file-uploader/jquery.ui.widget.js') }}"></script>
        <script src="{{ asset('assets/admin/theme/assets/plugins/fancy-file-uploader/jquery.iframe-transport.js') }}"></script>
        <script src="{{ asset('assets/admin/theme/assets/plugins/fancy-file-uploader/jquery.fileupload.js') }}"></script>
        <script src="{{ asset('assets/admin/theme/assets/plugins/fancy-file-uploader/jquery.fancy-fileupload.js') }}"></script>
        

        <script>
            $(function () {
              // Only init if the input exists (prevents errors on other pages)
              const $el = $('#fancy-file-upload');
              if (!$el.length) return;

              $el.FancyFileUpload({
                // IMPORTANT: point it to your Laravel upload route, otherwise it will 405
                url: @json(route('admin.media.upload')),
                params: { _token: '{{ csrf_token() }}' },
                paramName: 'file',
                maxfilesize: 1000000,

                // remove Alpine coupling (this is what caused __x error)
                // added: function (e, data) { ... }
              });
            });
          </script>
      @endif


    {{ $scripts ?? '' }}
    @stack('scripts')

  </body>

</html>
