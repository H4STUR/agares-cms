<x-guest-layout :coverImage="asset('assets/admin/theme/assets/images/auth/register1.png')">
    <h4 class="fw-bold">{{ __('Get Started Now') }}</h4>
    <p class="mb-0">{{ __('Enter your credentials to create your account') }}</p>

    {{-- Social login (leave as-is for now) --}}
    @php
        $googleEnabled = \App\Models\Setting::oauthEnabled('google');
        $facebookEnabled = \App\Models\Setting::oauthEnabled('facebook');
    @endphp

    @if ($googleEnabled || $facebookEnabled)

        <div class="row g-3 mb-4 mt-4">

            @if ($googleEnabled)
                <div class="col-12 col-lg-6">
                    <a
                        href="{{ route('oauth.redirect', ['provider' => 'google']) }}"
                        class="btn btn-filter py-2 fw-bold d-flex align-items-center justify-content-center w-100"
                    >
                        <img
                            src="{{ asset('assets/admin/theme/assets/images/apps/05.png') }}"
                            width="20"
                            class="me-2"
                            alt=""
                        >
                        Google
                    </a>
                </div>
            @endif

            @if ($facebookEnabled)
                <div class="col-12 col-lg-6">
                    <a
                        href="{{ route('oauth.redirect', ['provider' => 'facebook']) }}"
                        class="btn btn-filter py-2 fw-bold d-flex align-items-center justify-content-center w-100"
                    >
                        <img
                            src="{{ asset('assets/admin/theme/assets/images/apps/17.png') }}"
                            width="20"
                            class="me-2"
                            alt=""
                        >
                        Facebook
                    </a>
                </div>
            @endif

        </div>

    @endif


    <div class="separator section-padding">
        <div class="line"></div>
        <p class="mb-0 fw-bold">OR</p>
        <div class="line"></div>
    </div>

    {{-- Global validation summary --}}
    @if ($errors->any())
        <div class="alert alert-danger mb-2 mt-2" role="alert">
            <div class="fw-bold mb-1">{{ __('Please fix the following:') }}</div>
            <ul class="mb-0 ps-3">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('register') }}" class="row g-3" novalidate>
        @csrf

        {{-- Username --}}
        <div class="col-12">
            <label for="username" class="form-label">{{ __('Username') }}</label>
            <input
                id="username"
                type="text"
                name="username"
                value="{{ old('username') }}"
                class="form-control @error('username') is-invalid @enderror"
                required
                autocomplete="username"
            >
            @error('username')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        {{-- Name --}}
        <div class="col-6">
            <label for="name" class="form-label">{{ __('Name') }}</label>
            <input
                id="name"
                type="text"
                name="name"
                value="{{ old('name') }}"
                class="form-control @error('name') is-invalid @enderror"
                required
                autocomplete="given-name"
                autofocus
            >
            @error('name')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        {{-- Surname --}}
        <div class="col-6">
            <label for="surname" class="form-label">{{ __('Surname') }}</label>
            <input
                id="surname"
                type="text"
                name="surname"
                value="{{ old('surname') }}"
                class="form-control @error('surname') is-invalid @enderror"
                autocomplete="family-name"
            >
            @error('surname')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        {{-- Email --}}
        <div class="col-12">
            <label for="email" class="form-label">{{ __('Email Address') }}</label>
            <input
                id="email"
                type="email"
                name="email"
                value="{{ old('email') }}"
                class="form-control @error('email') is-invalid @enderror"
                required
                autocomplete="email"
            >
            @error('email')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        {{-- Password --}}
        <div class="col-12">
            <label for="password" class="form-label">{{ __('Password') }}</label>

            <div class="input-group" id="show_hide_password">
                <input
                    id="password"
                    type="password"
                    name="password"
                    class="form-control @error('password') is-invalid @enderror"
                    required
                    autocomplete="new-password"
                    placeholder="{{ __('Enter Password') }}"
                    aria-describedby="password-help"
                >
                <a href="javascript:;" class="input-group-text bg-transparent" aria-label="{{ __('Show/Hide password') }}">
                    <i class="bi bi-eye-slash-fill"></i>
                </a>
            </div>

            <div id="password-help" class="form-text">
                {{ __('Use at least 8 characters. Mix letters, numbers, and symbols if possible.') }}
            </div>

            @error('password')
                <div class="invalid-feedback d-block">{{ $message }}</div>
            @enderror
        </div>

        {{-- Confirm Password (separate toggle group) --}}
        <div class="col-12">
            <label for="password_confirmation" class="form-label">{{ __('Confirm Password') }}</label>

            <div class="input-group" id="show_hide_password_confirm">
                <input
                    id="password_confirmation"
                    type="password"
                    name="password_confirmation"
                    class="form-control @error('password_confirmation') is-invalid @enderror"
                    required
                    autocomplete="new-password"
                    placeholder="{{ __('Confirm Password') }}"
                >
                <a href="javascript:;" class="input-group-text bg-transparent" aria-label="{{ __('Show/Hide password') }}">
                    <i class="bi bi-eye-slash-fill"></i>
                </a>
            </div>

            @error('password_confirmation')
                <div class="invalid-feedback d-block">{{ $message }}</div>
            @enderror
        </div>

        {{-- Submit --}}
        <div class="col-12 mt-4">
            <div class="d-grid">
                <button type="submit" class="btn btn-primary">
                    {{ __('Register') }}
                </button>
            </div>
        </div>

        {{-- Already have account --}}
        <div class="col-12">
            <div class="text-start">
                <p class="mb-0">
                    {{ __('Already have an account?') }}
                    <a href="{{ route('login') }}">{{ __('Sign in here') }}</a>
                </p>
            </div>
        </div>
    </form>

    {{-- Tiny script to enable confirm-password eye toggle too --}}
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const root = document.getElementById('show_hide_password_confirm');
            if (!root) return;

            const input = root.querySelector('input');
            const icon  = root.querySelector('i');
            const link  = root.querySelector('a');
            if (!input || !icon || !link) return;

            link.addEventListener('click', (e) => {
                e.preventDefault();
                if (input.type === 'text') {
                    input.type = 'password';
                    icon.classList.add('bi-eye-slash-fill');
                    icon.classList.remove('bi-eye-fill');
                } else {
                    input.type = 'text';
                    icon.classList.add('bi-eye-fill');
                    icon.classList.remove('bi-eye-slash-fill');
                }
            });
        });
    </script>
</x-guest-layout>
