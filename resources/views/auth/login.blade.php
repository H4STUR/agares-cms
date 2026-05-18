<x-guest-layout>
    <!-- Session Status -->
    <x-auth-session-status class="mb-3" :status="session('status')" />

    @php $isDemo = request()->boolean('demo'); @endphp

    <h4 class="fw-bold">{{ __('Get Started Now') }}</h4>
    <p class="mb-0">{{ __('Enter your credentials to login your account') }}</p>

    @if($isDemo)
        <div class="alert alert-info mt-3 mb-0 d-flex align-items-start gap-2" role="status">
            <i class="bi bi-info-circle-fill"></i>
            <div>
                <strong>{{ __('Demo credentials prefilled.') }}</strong>
                {{ __('Click the Login button below to enter the admin panel as the demo user.') }}
            </div>
        </div>
    @endif

    {{-- Social login --}}
    @php
        $googleEnabled = \App\Models\Setting::oauthEnabled('google');
        $facebookEnabled = \App\Models\Setting::oauthEnabled('facebook');
    @endphp

    @if ($googleEnabled || $facebookEnabled)

        <div class="row g-3 mb-4 mt-4">

            @if ($googleEnabled)
                <div class="col-12 {{$facebookEnabled ? 'col-lg-6' : 'col-lg-12' }}">
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
                <div class="col-12 {{$googleEnabled ? 'col-lg-6' : 'col-lg-12' }}">
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

    {{-- Login form --}}
    <form method="POST" action="{{ route('login') }}" class="row g-3 mt-4">
        @csrf

        {{-- Email or Username --}}
        <div class="col-12">
            <label for="input_type" class="form-label">{{ __('Email or Username') }}</label>
            <input
                id="input_type"
                type="text"
                name="input_type"
                value="{{ old('input_type', $isDemo ? 'demo' : '') }}"
                class="form-control @error('email') is-invalid @enderror @error('username') is-invalid @enderror"
                autofocus
                autocomplete="username"
            >

            @if($errors->has('email'))
                <div class="invalid-feedback d-block">{{ $errors->first('email') }}</div>
            @endif
            @if($errors->has('username'))
                <div class="invalid-feedback d-block">{{ $errors->first('username') }}</div>
            @endif
        </div>

        {{-- Password --}}
        <div class="col-12">
            <label for="password" class="form-label">{{ __('Password') }}</label>

            <div class="input-group" id="show_hide_password">
                <input
                    id="password"
                    type="password"
                    name="password"
                    value="{{ $isDemo ? 'password' : '' }}"
                    class="form-control @error('password') is-invalid @enderror"
                    required
                    autocomplete="current-password"
                    placeholder="{{ __('Enter Password') }}"
                >
                <a href="javascript:;" class="input-group-text bg-transparent">
                    <i class="bi bi-eye-slash-fill"></i>
                </a>
            </div>

            @error('password')
                <div class="invalid-feedback d-block">{{ $message }}</div>
            @enderror
        </div>

        {{-- Remember + Forgot --}}
        <div class="col-md-6">
            <div class="form-check form-switch">
                <input class="form-check-input"
                       type="checkbox"
                       id="remember_me"
                       name="remember"
                       {{ old('remember') ? 'checked' : '' }}>
                <label class="form-check-label" for="remember_me">
                    {{ __('Remember Me') }}
                </label>
            </div>
        </div>

        <div class="col-md-6 text-end">
            @if (Route::has('password.request'))
                <a href="{{ route('password.request') }}">
                    {{ __('Forgot your password?') }}
                </a>
            @endif
        </div>

        {{-- Submit --}}
        <div class="col-12">
            <div class="d-grid">
                <button type="submit" class="btn btn-primary">
                    {{ __('Login') }}
                </button>
            </div>
        </div>

        {{-- Register link (only if enabled) --}}
        @if (
            Route::has('register') &&
            \App\Models\Setting::bool('enable_registration', false)
        )
            <div class="col-12">
                <p class="mb-0">
                    {{ __("Don't have an account yet?") }}
                    <a href="{{ route('register') }}">{{ __('Sign up here') }}</a>
                </p>
            </div>
        @endif
    </form>
</x-guest-layout>
