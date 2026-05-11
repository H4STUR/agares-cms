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
    <title>{{ $user->name }} &mdash; {{ $settings['meta_title'] ?? config('app.name') }}</title>
    <link rel="icon" href="{{ asset('assets/admin/images/agares-logo.png') }}" type="image/x-icon">

    <link href="{{ asset('assets/admin/theme/assets/css/bootstrap.min.css') }}" rel="stylesheet">
    <link href="{{ asset('assets/admin/theme/assets/css/extra-icons.css') }}" rel="stylesheet">
    <link href="{{ asset('assets/admin/theme/assets/css/bootstrap-extended.css') }}" rel="stylesheet">
    <link href="{{ asset('assets/admin/theme/sass/main.css') }}" rel="stylesheet">
    <link href="{{ asset('assets/admin/theme/sass/dark-theme.css') }}" rel="stylesheet">
    <link href="{{ asset('assets/admin/css/admin-nav.css') }}" rel="stylesheet">
    <link href="{{ asset('assets/admin/css/custom.css') }}" rel="stylesheet">
    <link href="{{ asset('assets/css/styles.css') }}" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Noto+Sans:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css?family=Material+Icons+Outlined" rel="stylesheet">

    <style>
        .user-layout-grid {
            display: grid;
            grid-template-columns: 220px 1fr;
            gap: 24px;
            padding: 24px 0 48px;
            align-items: start;
        }

        /* Sidebar — matches admin theme link colours exactly */
        .user-sidebar {
            background: var(--bs-body-bg);
            border: 1px solid var(--bs-border-color);
            border-radius: 8px;
            overflow: hidden;
            position: sticky;
            top: 76px;
        }

        .user-sidebar-section-label {
            padding: 8px 14px 6px;
            font-size: 0.68rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: .08em;
            color: var(--bs-secondary-color);
            background: var(--bs-tertiary-bg);
            border-top: 1px solid var(--bs-border-color);
            border-bottom: 1px solid var(--bs-border-color);
        }

        .user-sidebar-link {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 11px 14px;
            font-size: 0.875rem;
            color: #5f5f5f;
            text-decoration: none;
            border-bottom: 1px solid var(--bs-border-color);
            transition: background .15s, color .15s;
        }

        [data-bs-theme=dark] .user-sidebar-link { color: #a7acb1; }

        .user-sidebar-link:last-child { border-bottom: none; }

        .user-sidebar-link:hover,
        .user-sidebar-link.active {
            color: #008cff;
            background-color: rgba(0, 140, 255, 0.05);
            text-decoration: none;
        }

        [data-bs-theme=dark] .user-sidebar-link:hover,
        [data-bs-theme=dark] .user-sidebar-link.active {
            color: #ffffff;
            background-color: rgba(255, 255, 255, 0.05);
        }

        .user-sidebar-link i {
            font-size: 1.1rem;
            width: 20px;
            text-align: center;
            flex-shrink: 0;
        }

        .user-sidebar-footer {
            padding: 10px 14px;
            border-top: 1px solid var(--bs-border-color);
            background: var(--bs-tertiary-bg);
        }

        .theme-toggle-btn {
            display: flex;
            align-items: center;
            gap: 8px;
            width: 100%;
            padding: 6px 8px;
            background: none;
            border: 1px solid var(--bs-border-color);
            border-radius: 6px;
            color: var(--bs-body-color);
            font-size: 0.8rem;
            cursor: pointer;
            transition: background .12s;
        }

        .theme-toggle-btn:hover { background: var(--bs-secondary-bg); }
        .theme-toggle-btn i { font-size: 1rem; }

        /* Profile card: avatar overlap */
        .user-profile-card-avatar {
            width: 110px;
            height: 110px;
            border-radius: 50%;
            object-fit: cover;
            padding: 3px;
            background: var(--bs-body-bg);
            box-shadow: 0 4px 15px rgba(0,0,0,.2);
        }

        @media (max-width: 768px) {
            .user-layout-grid { grid-template-columns: 1fr; }
            .user-sidebar { position: static; }
        }
    </style>

    <style>{{ $customStyle ?? '' }}</style>
    @stack('styles')
</head>
<body>

@include('pages.frontend.snippets.admin-bar')

{{-- Top bar --}}
<nav class="navbar border-bottom px-3 px-lg-4" style="height:56px;">
    <a href="/" class="navbar-brand d-flex align-items-center gap-2 p-0">
        <img src="{{ asset('assets/admin/images/agares-logo.png') }}" height="32" alt="Logo">
        <span class="fw-bold fs-6">{{ config('app.name', 'Agares') }}</span>
    </a>
    <div class="ms-auto d-flex align-items-center gap-2">
        @auth
            @if(auth()->user()->can('view admin panel'))
                <a href="{{ url('/admin') }}" class="btn btn-sm btn-outline-secondary d-flex align-items-center gap-1">
                    <i class="material-icons-outlined" style="font-size:16px;">grid_view</i>
                    Admin
                </a>
            @endif
            <form method="POST" action="{{ route('logout') }}" class="d-inline">
                @csrf
                <button class="btn btn-sm btn-outline-danger">Logout</button>
            </form>
        @else
            <a href="{{ route('login') }}" class="btn btn-sm btn-primary">Login</a>
        @endauth
    </div>
</nav>

{{-- Main --}}
<div class="container" style="padding-top:24px;">
    {{-- Profile banner card — same structure as admin user profile --}}
    <div class="card overflow-hidden mb-4">
        <div class="card-body p-0">
            <div class="position-relative text-center">
                <div class="ratio" style="--bs-aspect-ratio: calc(5 / 18 * 100%)">
                    <img src="{{ $user->background_image_url }}" class="img-fluid rounded object-fit-cover w-100 h-100" alt="">
                </div>
                <div class="position-absolute top-100 start-50 translate-middle">
                    <img src="{{ $user->avatar_url }}" class="user-profile-card-avatar" alt="{{ $user->name }}">
                </div>
            </div>
            <div class="mt-5 px-4 pb-3 d-flex align-items-start justify-content-between">
                <div>
                    <h4 class="mb-1">{{ $user->name }}</h4>
                    <p class="text-body-secondary mb-0 small">{{ $user->roles->first()?->name ?? 'Member' }}</p>
                </div>
                <div class="d-flex gap-2">
                    @auth
                        @if(auth()->id() === $user->id)
                            <a href="{{ route('admin.user.settings', $user) }}" class="btn btn-sm btn-outline-secondary d-flex align-items-center gap-1">
                                <i class="material-icons-outlined" style="font-size:16px;">edit</i> Edit Profile
                            </a>
                        @endif
                    @endauth
                </div>
            </div>
        </div>
    </div>

    <div class="user-layout-grid">

        {{-- Sidebar --}}
        <aside class="user-sidebar">
            <a href="{{ route('admin.user.profile', $user) }}"
               class="user-sidebar-link {{ request()->routeIs('admin.user.profile') ? 'active' : '' }}">
                <i class="material-icons-outlined">person</i> Profile
            </a>

            @auth
                @if(auth()->id() === $user->id || auth()->user()->can('manage users'))
                    <a href="{{ route('admin.user.settings', $user) }}"
                       class="user-sidebar-link {{ request()->routeIs('admin.user.settings') ? 'active' : '' }}">
                        <i class="material-icons-outlined">settings</i> Settings
                    </a>
                @endif
            @endauth

            @if(($settings['enable_ecommerce'] ?? '0') == '1')
                <div class="user-sidebar-section-label">Shop</div>
                @auth
                    @if(auth()->id() === $user->id || auth()->user()->can('manage users'))
                        <a href="{{ route('admin.user.orders', $user) }}"
                           class="user-sidebar-link {{ request()->routeIs('admin.user.orders') ? 'active' : '' }}">
                            <i class="material-icons-outlined">shopping_bag</i> My Orders
                        </a>
                        <a href="{{ route('admin.user.favorites', $user) }}"
                           class="user-sidebar-link {{ request()->routeIs('admin.user.favorites') ? 'active' : '' }}">
                            <i class="material-icons-outlined">favorite</i> Favorites
                        </a>
                        <a href="{{ route('admin.user.tracking', $user) }}"
                           class="user-sidebar-link {{ request()->routeIs('admin.user.tracking') ? 'active' : '' }}">
                            <i class="material-icons-outlined">location_on</i> Tracking
                        </a>
                        <a href="{{ route('admin.user.invoices', $user) }}"
                           class="user-sidebar-link {{ request()->routeIs('admin.user.invoices') ? 'active' : '' }}">
                            <i class="material-icons-outlined">receipt</i> Invoices
                        </a>
                        <a href="{{ route('admin.user.returns', $user) }}"
                           class="user-sidebar-link {{ request()->routeIs('admin.user.returns') ? 'active' : '' }}">
                            <i class="material-icons-outlined">assignment_return</i> Returns
                        </a>
                    @endif
                @endauth
            @endif

            {{-- Theme toggle + logout --}}
            <div class="user-sidebar-footer" style="display:flex;flex-direction:column;gap:6px;">
                <button class="theme-toggle-btn" id="theme-toggle">
                    <i class="material-icons-outlined" id="theme-icon">light_mode</i>
                    <span id="theme-label">Light mode</span>
                </button>
                @auth
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="theme-toggle-btn w-100" style="color:#dc3545;border-color:#dc3545;">
                            <i class="material-icons-outlined">logout</i>
                            <span>Logout</span>
                        </button>
                    </form>
                @endauth
            </div>
        </aside>

        {{-- Content --}}
        <main>
            @if(session('success'))
                <div class="alert alert-success d-flex align-items-center gap-2 mb-4">
                    <i class="material-icons-outlined">check_circle</i> {{ session('success') }}
                </div>
            @endif
            @if($errors->any())
                <div class="alert alert-danger d-flex align-items-center gap-2 mb-4">
                    <i class="material-icons-outlined">error</i> {{ $errors->first() }}
                </div>
            @endif

            @yield('user-content')
        </main>

    </div>
</div>

<footer class="py-4 border-top mt-4">
    <div class="container">
        <p class="text-body-secondary small mb-0">&copy; {{ date('Y') }} {{ config('app.name', 'Agares') }}. All rights reserved.</p>
    </div>
</footer>

<script src="{{ asset('assets/admin/theme/assets/js/jquery.min.js') }}"></script>
<script src="{{ asset('assets/admin/theme/assets/js/bootstrap.bundle.min.js') }}"></script>

<script>
    (function () {
        const icon  = document.getElementById('theme-icon');
        const label = document.getElementById('theme-label');

        function applyTheme(theme) {
            document.documentElement.setAttribute('data-bs-theme', theme);
            localStorage.setItem('theme', theme);
            icon.textContent  = theme === 'dark' ? 'light_mode' : 'dark_mode';
            label.textContent = theme === 'dark' ? 'Light mode' : 'Dark mode';
        }

        applyTheme(localStorage.getItem('theme') || 'dark');

        document.getElementById('theme-toggle').addEventListener('click', function () {
            applyTheme((localStorage.getItem('theme') || 'dark') === 'dark' ? 'light' : 'dark');
        });
    })();
</script>

<link rel="stylesheet" href="{{ asset('assets/css/site-prefs.css') }}?v={{ config('app.version', '1') }}">
<div id="site-prefs-root"></div>
<script src="{{ asset('assets/js/site-prefs.js') }}?v={{ config('app.version', '1') }}"></script>
@stack('scripts')
{!! $customScript ?? '' !!}
</body>
</html>
