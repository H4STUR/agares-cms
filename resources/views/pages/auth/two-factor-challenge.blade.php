<x-guest-layout>
    <h4 class="fw-bold">{{ __('Two-Factor Verification') }}</h4>

    @if ($method === 'email')
        <p class="mb-4">
            {{ __('We sent a 6-digit code to :email. Enter it below — or use one of your recovery codes.', ['email' => $maskedEmail]) }}
        </p>
    @else
        <p class="mb-4">
            {{ __('Open your authenticator app and enter the 6-digit code, or use one of your recovery codes.') }}
        </p>
    @endif

    @if (session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    @if ($errors->any())
        <div class="alert alert-danger">
            @foreach ($errors->all() as $error)
                <div>{{ $error }}</div>
            @endforeach
        </div>
    @endif

    <form method="POST" action="{{ route('two-factor.challenge') }}" x-data="{ mode: 'code' }">
        @csrf

        <div class="mb-3" x-show="mode === 'code'">
            <label for="code" class="form-label">
                @if ($method === 'email')
                    {{ __('Email verification code') }}
                @else
                    {{ __('Authenticator code') }}
                @endif
            </label>
            <input type="text"
                   id="code"
                   name="code"
                   class="form-control form-control-lg text-center"
                   inputmode="numeric"
                   autocomplete="one-time-code"
                   maxlength="6"
                   pattern="[0-9]{6}"
                   autofocus
                   placeholder="000000">
            @if ($method === 'email')
                <div class="form-text">
                    {{ __('The code expires :n minutes after it was sent.', ['n' => $ttlMinutes]) }}
                </div>
            @endif
        </div>

        <div class="mb-3" x-show="mode === 'recovery'" x-cloak>
            <label for="recovery_code" class="form-label">{{ __('Recovery code') }}</label>
            <input type="text"
                   id="recovery_code"
                   name="recovery_code"
                   class="form-control"
                   autocomplete="off"
                   placeholder="XXXX-XXXX">
            <div class="form-text">{{ __('Each recovery code can only be used once.') }}</div>
        </div>

        <div class="d-flex justify-content-between align-items-center mb-3">
            <a href="#" class="small" @click.prevent="mode = mode === 'code' ? 'recovery' : 'code'">
                <span x-show="mode === 'code'">{{ __('Use a recovery code instead') }}</span>
                <span x-show="mode === 'recovery'" x-cloak>
                    @if ($method === 'email')
                        {{ __('Use email code instead') }}
                    @else
                        {{ __('Use authenticator app instead') }}
                    @endif
                </span>
            </a>
        </div>

        <div class="d-grid mb-2">
            <button type="submit" class="btn btn-primary py-2">{{ __('Verify') }}</button>
        </div>
    </form>

    @if ($method === 'email')
        <form method="POST" action="{{ route('two-factor.challenge.resend') }}" class="mb-2">
            @csrf
            <div class="d-grid">
                <button type="submit" class="btn btn-outline-secondary btn-sm">
                    <i class="bi bi-arrow-clockwise me-1"></i>{{ __('Send a new code') }}
                </button>
            </div>
        </form>
    @endif

    <form method="POST" action="{{ route('two-factor.challenge.cancel') }}">
        @csrf
        <div class="d-grid">
            <button type="submit" class="btn btn-link text-muted small">{{ __('Cancel and return to login') }}</button>
        </div>
    </form>
</x-guest-layout>
