@php
    $tfEnabledGlobally = \App\Models\Setting::bool('2FA_enabled');
    $tfActive          = $user->hasTwoFactorEnabled();
    $remaining         = is_array($user->two_factor_recovery_codes ?? null)
        ? count($user->two_factor_recovery_codes)
        : 0;
@endphp

<div class="col-12 col-xl-6">
    <div class="card shadow-sm">
        <div class="card-body">
            <h5 class="mb-3 text-success">
                <i class="bi bi-shield-lock me-1"></i>{{ __('Two-Factor Authentication') }}
            </h5>

            @if (!$tfEnabledGlobally)
                <div class="alert alert-secondary mb-0">
                    <i class="bi bi-info-circle me-2"></i>
                    {{ __('Two-factor authentication is disabled site-wide. Ask an administrator to enable it under Settings → Security.') }}
                </div>
            @elseif ($tfActive)
                <p class="mb-2">
                    <span class="badge bg-success">
                        <i class="bi bi-check-circle me-1"></i>{{ __('Enabled') }}
                    </span>
                    <span class="text-muted small ms-2">
                        {{ __('Confirmed :date', ['date' => optional($user->two_factor_confirmed_at)->format('Y-m-d H:i')]) }}
                    </span>
                </p>
                @php
                    $methodLabel = match ($user->two_factor_method) {
                        'email' => __('Email — codes sent to :email', ['email' => $user->email]),
                        'totp'  => __('Authenticator app'),
                        default => $user->two_factor_method ?? 'totp',
                    };
                @endphp
                <p class="text-muted small mb-3">
                    {{ __('Method:') }} <strong>{{ $methodLabel }}</strong>
                    &middot;
                    {{ __(':n recovery code(s) remaining', ['n' => $remaining]) }}
                </p>

                <a href="{{ route('two-factor.recovery-codes') }}" class="btn btn-outline-secondary btn-sm">
                    <i class="bi bi-key me-1"></i>{{ __('Manage recovery codes') }}
                </a>

                <form method="POST" action="{{ route('two-factor.disable') }}" class="mt-3"
                      onsubmit="return confirm('{{ __('Disable two-factor authentication on your account?') }}');">
                    @csrf
                    @method('DELETE')
                    <div class="mb-2">
                        <label for="tf_disable_password" class="form-label small">{{ __('Confirm your password to disable') }}</label>
                        <input type="password" id="tf_disable_password" name="password" class="form-control form-control-sm" required>
                    </div>
                    <x-danger-button type="submit">
                        <i class="bi bi-shield-slash me-1"></i>{{ __('Disable 2FA') }}
                    </x-danger-button>
                </form>
            @else
                <p class="mb-3 text-muted">
                    {{ __('Protect your account with an authenticator app. After setup, signing in will require both your password and a 6-digit code.') }}
                </p>
                <a href="{{ route('two-factor.setup') }}" class="btn btn-success">
                    <i class="bi bi-shield-plus me-1"></i>{{ __('Enable two-factor authentication') }}
                </a>
            @endif
        </div>
    </div>
</div>
