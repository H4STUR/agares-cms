<x-guest-layout>
    <h4 class="fw-bold">{{ __('Confirm Password') }}</h4>
    <p class="mb-0">
        {{ __('This is a secure area of the application. Please confirm your password before continuing.') }}
    </p>

    <div class="form-body mt-4">
        <form method="POST" action="{{ route('password.confirm') }}" class="row g-3">
            @csrf

            {{-- Password --}}
            <div class="col-12">
                <label for="password" class="form-label">{{ __('Password') }}</label>

                <div class="input-group" id="show_hide_password">
                    <input
                        id="password"
                        type="password"
                        name="password"
                        required
                        autocomplete="current-password"
                        class="form-control @error('password') is-invalid @enderror"
                        placeholder="{{ __('Enter your password') }}"
                    >
                    <a href="javascript:;" class="input-group-text bg-transparent" aria-label="{{ __('Show/Hide password') }}">
                        <i class="bi bi-eye-slash-fill"></i>
                    </a>
                </div>

                @error('password')
                    <div class="invalid-feedback d-block" role="alert">
                        {{ $message }}
                    </div>
                @enderror
            </div>

            {{-- Actions --}}
            <div class="col-12">
                <div class="d-grid">
                    <button type="submit" class="btn btn-primary">
                        {{ __('Confirm') }}
                    </button>
                </div>
            </div>
        </form>
    </div>
</x-guest-layout>
