<x-guest-layout>
    {{-- Status (e.g. "We have emailed your password reset link!") --}}
    <x-auth-session-status class="mb-3" :status="session('status')" />

    <h4 class="fw-bold">{{ __('Forgot Password?') }}</h4>
    <p class="mb-0">
        {{ __('Enter your registered email address to receive a password reset link.') }}
    </p>

    <div class="form-body mt-4">
        <form method="POST" action="{{ route('password.email') }}" class="row g-3">
            @csrf

            {{-- Email --}}
            <div class="col-12">
                <label for="email" class="form-label">{{ __('Email') }}</label>
                <input
                    id="email"
                    type="email"
                    name="email"
                    value="{{ old('email') }}"
                    required
                    autofocus
                    autocomplete="email"
                    class="form-control @error('email') is-invalid @enderror"
                    placeholder="example@user.com"
                >

                @error('email')
                    <div class="invalid-feedback d-block" role="alert">
                        {{ $message }}
                    </div>
                @enderror
            </div>

            {{-- Actions --}}
            <div class="col-12">
                <div class="d-grid gap-2">
                    <button type="submit" class="btn btn-primary">
                        {{ __('Send reset link') }}
                    </button>

                    <a href="{{ route('login') }}" class="btn btn-light">
                        {{ __('Back to Login') }}
                    </a>
                </div>
            </div>
        </form>
    </div>
</x-guest-layout>
