<nav x-data="{ open: false }" class="bg-white dark:bg-gray-800 w-64 h-screen fixed sm:relative border-r border-gray-200 dark:border-gray-700">
    <div class="flex justify-between items-center p-4">
        <!-- Logo -->
        <a href="{{ route('admin.dashboard') }}">
            <img src="{{ asset('assets/admin/images/agares-logo.png') }}" alt="Logo" class="h-10 sm:h-12" style="height: 50px;">
        </a>

        <!-- Hamburger Menu for Mobile (only visible on small screens) -->
        <button @click="open = !open" class="sm:hidden text-gray-600 dark:text-gray-400">
            <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                <path :class="{'hidden': open, 'inline-flex': !open }" class="inline-flex" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                <path :class="{'hidden': !open, 'inline-flex': open }" class="hidden" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
        </button>
    </div>

    <!-- Desktop Navigation Links (Sidebar) -->
    <div class="sm:block space-y-4 mt-6 sm:mt-0">
        <x-sidebar-nav-link :href="route('home')" target="_blank">
            {{ __("Home Page") }}
        </x-sidebar-nav-link>

        <x-sidebar-nav-link
            :href="auth()->user()->can('manage dashboard') ? route('admin.dashboard') : 'javascript:void(0);'"
            :active="request()->routeIs('admin.dashboard*')"
            :class="!auth()->user()->can('manage dashboard') ? 'pointer-events-none opacity-50 cursor-not-allowed' : ''"
            :title="auth()->user()->can('manage dashboard') ? '' : 'You don\'t have permission to view this page'"
        >
            {{ __('Dashboard') }}
        </x-sidebar-nav-link>

        <x-sidebar-nav-link
            :href="auth()->user()->can('manage users') ? route('admin.users') : 'javascript:void(0);'"
            :active="request()->routeIs('admin.users*')"
            :class="!auth()->user()->can('manage users') ? 'pointer-events-none opacity-50 cursor-not-allowed' : ''"
            :title="auth()->user()->can('manage users') ? '' : 'You don\'t have permission to view this page'"
        >
            {{ __('Users') }}
        </x-sidebar-nav-link>

        <x-sidebar-nav-link
            :href="auth()->user()->can('manage permissions') ? route('admin.permissions') : 'javascript:void(0);'"
            :active="request()->routeIs('admin.permissions*')"
            :class="!auth()->user()->can('manage permissions') ? 'pointer-events-none opacity-50 cursor-not-allowed' : ''"
            :title="auth()->user()->can('manage permissions') ? '' : 'You don\'t have permission to view this page'"
        >
            {{ __('Permissions') }}
        </x-sidebar-nav-link>

        <x-sidebar-nav-link
        :href="auth()->user()->can('manage sites') ? route('admin.sites') : 'javascript:void(0);'"
        :active="request()->routeIs('admin.sites*')"
        :class="!auth()->user()->can('manage sites') ? 'pointer-events-none opacity-50 cursor-not-allowed' : ''"
        :title="auth()->user()->can('manage sites') ? '' : 'You don\'t have permission to view this page'"
        >
            {{ __('Sites') }}
        </x-sidebar-nav-link>

        <x-sidebar-nav-link
        :href="auth()->user()->can('manage menus') ? route('admin.menus') : 'javascript:void(0);'"
        :active="request()->routeIs('admin.menus*')"
        :class="!auth()->user()->can('manage menus') ? 'pointer-events-none opacity-50 cursor-not-allowed' : ''"
        :title="auth()->user()->can('manage menus') ? '' : 'You don\'t have permission to view this page'"
        >
            {{ __('Menus') }}
        </x-sidebar-nav-link>

        <x-sidebar-nav-link
        :href="auth()->user()->can('manage media') ? route('admin.media') : 'javascript:void(0);'"
        :active="request()->routeIs('admin.media*')"
        :class="!auth()->user()->can('manage media') ? 'pointer-events-none opacity-50 cursor-not-allowed' : ''"
        :title="auth()->user()->can('manage media') ? '' : 'You don\'t have permission to view this page'"
        >
            {{ __('Media') }}
        </x-sidebar-nav-link>

        <x-sidebar-nav-link
        :href="auth()->user()->can('manage settings') ? route('admin.settings') : 'javascript:void(0);'"
        :active="request()->routeIs('admin.settings*')"
        :class="!auth()->user()->can('manage settings') ? 'pointer-events-none opacity-50 cursor-not-allowed' : ''"
        :title="auth()->user()->can('manage settings') ? '' : 'You don\'t have permission to view this page'"
        >
            {{ __('Settings') }}
        </x-sidebar-nav-link>

        <x-sidebar-nav-link
        :href="auth()->user()->can('manage custom') ? route('admin.custom') : 'javascript:void(0);'"
        :active="request()->routeIs('admin.custom*')"
        :class="!auth()->user()->can('manage custom') ? 'pointer-events-none opacity-50 cursor-not-allowed' : ''"
        :title="auth()->user()->can('manage custom') ? '' : 'You don\'t have permission to view this page'"
        >
            {{ __('Custom') }}
        </x-sidebar-nav-link>

    </div>

    <!-- Mobile Sidebar (Hidden by Default) -->
    <div x-show="open" x-transition class="sm:hidden fixed inset-0 bg-gray-900 bg-opacity-50 z-50">
        <div class="bg-white dark:bg-gray-800 w-64 h-full p-4">
            <x-sidebar-nav-link :href="route('home')" target="_blank">
                {{ __("Home Page") }}
            </x-sidebar-nav-link>
            <x-sidebar-nav-link :href="route('admin.dashboard')" :active="request()->routeIs('admin.dashboard')">
                {{ __("Dashboard") }}
            </x-sidebar-nav-link>
            <x-sidebar-nav-link
                :href="auth()->user()->can('view users') ? route('admin.users') : '#'"
                :active="request()->routeIs('admin.users*')"
                :class="!auth()->user()->can('view users') ? 'pointer-events-none opacity-50 cursor-not-allowed' : ''"
            >
                {{ __('Users') }}
            </x-sidebar-nav-link>
        
            <x-sidebar-nav-link :href="route('admin.permissions')" :active="request()->routeIs('admin.permissions')">
                {{ __("Permissions") }}
            </x-sidebar-nav-link>
            <x-sidebar-nav-link :href="route('admin.sites')" :active="request()->routeIs('admin.sites')">
                {{ __("Sites") }}
            </x-sidebar-nav-link>
            <x-sidebar-nav-link :href="route('admin.menus')" :active="request()->routeIs('admin.menus')">
                {{ __("Menus") }}
            </x-sidebar-nav-link>
            <x-sidebar-nav-link :href="route('admin.settings')" :active="request()->routeIs('admin.settings')">
                {{ __("Settings") }}
            </x-sidebar-nav-link>
            <x-sidebar-nav-link :href="route('admin.custom')" :active="request()->routeIs('admin.custom')">
                {{ __("Custom") }}
            </x-sidebar-nav-link>
            
        </div>
    </div>
</nav>
