<x-app-layout>
    <div class="page-breadcrumb d-none d-sm-flex align-items-center mb-3">
        <div class="breadcrumb-title pe-3">{{ __('Two-Factor Authentication') }}</div>
    </div>

    <div class="container-fluid">
        @if (session('warning'))
            <div class="alert alert-warning">{{ session('warning') }}</div>
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

        @if ($mustReEnrol)
            <div class="alert alert-info">
                <i class="bi bi-info-circle me-2"></i>
                {{ __('The site admin has changed two-factor settings. Your previous method is no longer allowed — please set up a new one below.') }}
            </div>
        @endif

        {{-- Method picker — only shown when more than one method is allowed and one hasn't been chosen yet --}}
        @if (count($allowedMethods) > 1 && $method === '')
            <div class="row g-4 justify-content-center">
                <div class="col-12 col-xl-7">
                    <div class="card shadow-sm">
                        <div class="card-body">
                            <h5 class="mb-3 text-primary">{{ __('Choose a verification method') }}</h5>
                            <p class="text-muted mb-4">
                                {{ __("Pick how you'd like to receive your verification code each time you sign in.") }}
                            </p>
                            <div class="row g-3">
                                @if (in_array('totp', $allowedMethods, true))
                                    <div class="col-md-6">
                                        <a href="{{ route('two-factor.setup', ['method' => 'totp']) }}"
                                           class="d-block p-4 border rounded text-decoration-none text-dark h-100">
                                            <h6 class="mb-2"><i class="bi bi-phone me-2"></i>{{ __('Authenticator app') }}</h6>
                                            <p class="small text-muted mb-0">
                                                {{ __('Use Google Authenticator, Authy, 1Password, Microsoft Authenticator, Bitwarden, or another TOTP-compatible app.') }}
                                            </p>
                                        </a>
                                    </div>
                                @endif
                                @if (in_array('email', $allowedMethods, true))
                                    <div class="col-md-6">
                                        <a href="{{ route('two-factor.setup', ['method' => 'email']) }}"
                                           class="d-block p-4 border rounded text-decoration-none text-dark h-100">
                                            <h6 class="mb-2"><i class="bi bi-envelope me-2"></i>{{ __('Email') }}</h6>
                                            <p class="small text-muted mb-0">
                                                {{ __('We will email a 6-digit code to :email each time you sign in.', ['email' => $user->email]) }}
                                            </p>
                                        </a>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endif

        {{-- TOTP setup --}}
        @if ($method === 'totp')
            <div class="row g-4">
                <div class="col-12 col-xl-7">
                    <div class="card shadow-sm">
                        <div class="card-body">
                            <h5 class="mb-3 text-primary">
                                <i class="bi bi-phone me-2"></i>{{ __('Set up authenticator app') }}
                            </h5>

                            <ol class="mb-4">
                                <li class="mb-2">{{ __('Install an authenticator app (Google Authenticator, Authy, 1Password, Microsoft Authenticator, Bitwarden, etc.).') }}</li>
                                <li class="mb-2">{{ __('Scan the QR code below — or enter the secret key manually.') }}</li>
                                <li class="mb-2">{{ __('Enter the 6-digit code shown in your app to confirm.') }}</li>
                            </ol>

                            <div class="text-center mb-4 p-3 bg-light rounded">
                                {!! $qrSvg !!}
                            </div>

                            <div class="mb-4">
                                <label class="form-label small text-muted text-uppercase">{{ __('Or enter this key manually') }}</label>
                                <div class="input-group">
                                    <input type="text"
                                           class="form-control font-monospace"
                                           value="{{ $secret }}"
                                           readonly
                                           onclick="this.select()">
                                    <button type="button"
                                            class="btn btn-outline-secondary"
                                            onclick="navigator.clipboard.writeText('{{ $secret }}')">
                                        <i class="bi bi-clipboard"></i>
                                    </button>
                                </div>
                            </div>

                            <form method="POST" action="{{ route('two-factor.confirm') }}">
                                @csrf
                                <input type="hidden" name="method" value="totp">

                                <div class="mb-3">
                                    <label for="code" class="form-label">{{ __('Verification code') }}</label>
                                    <input type="text"
                                           id="code"
                                           name="code"
                                           class="form-control form-control-lg text-center"
                                           inputmode="numeric"
                                           autocomplete="one-time-code"
                                           maxlength="6"
                                           pattern="[0-9]{6}"
                                           placeholder="000000"
                                           autofocus
                                           required>
                                </div>

                                <x-primary-button type="submit">{{ __('Confirm and enable') }}</x-primary-button>
                                @if (count($allowedMethods) > 1)
                                    <a href="{{ route('two-factor.setup') }}" class="btn btn-link">{{ __('Choose another method') }}</a>
                                @else
                                    <a href="{{ route('admin.user.settings', auth()->user()) }}" class="btn btn-link">{{ __('Cancel') }}</a>
                                @endif
                            </form>
                        </div>
                    </div>
                </div>

                <div class="col-12 col-xl-5">
                    <div class="card border-info">
                        <div class="card-body">
                            <h6 class="text-info"><i class="bi bi-info-circle me-2"></i>{{ __('What happens next?') }}</h6>
                            <p class="text-muted mb-2 small">{{ __('After you confirm, we generate eight one-time recovery codes. Each can be used once to sign in if you lose access to your authenticator app.') }}</p>
                            <p class="text-muted mb-0 small">{{ __('Save them somewhere safe — we can only show them to you immediately after setup.') }}</p>
                        </div>
                    </div>
                </div>
            </div>
        @endif

        {{-- Email setup --}}
        @if ($method === 'email')
            <div class="row g-4">
                <div class="col-12 col-xl-7">
                    <div class="card shadow-sm">
                        <div class="card-body">
                            <h5 class="mb-3 text-primary">
                                <i class="bi bi-envelope me-2"></i>{{ __('Set up email verification') }}
                            </h5>

                            <p class="mb-4 text-muted">
                                {{ __("Each time you sign in we'll email a 6-digit code to :email. Codes expire after :n minutes.", [
                                    'email' => $user->email,
                                    'n'     => $ttlMinutes,
                                ]) }}
                            </p>

                            @if (!$emailCodeSentAt)
                                <form method="POST" action="{{ route('two-factor.setup.email.send') }}">
                                    @csrf
                                    <p class="text-muted small mb-3">
                                        {{ __('First we need to confirm that you can receive emails at this address. Click below to send a test code.') }}
                                    </p>
                                    <x-primary-button type="submit">
                                        <i class="bi bi-send me-1"></i>{{ __('Send code to my email') }}
                                    </x-primary-button>
                                    @if (count($allowedMethods) > 1)
                                        <a href="{{ route('two-factor.setup') }}" class="btn btn-link">{{ __('Choose another method') }}</a>
                                    @endif
                                </form>
                            @else
                                <form method="POST" action="{{ route('two-factor.confirm') }}">
                                    @csrf
                                    <input type="hidden" name="method" value="email">

                                    <div class="mb-3">
                                        <label for="code" class="form-label">{{ __('Verification code') }}</label>
                                        <input type="text"
                                               id="code"
                                               name="code"
                                               class="form-control form-control-lg text-center"
                                               inputmode="numeric"
                                               autocomplete="one-time-code"
                                               maxlength="6"
                                               pattern="[0-9]{6}"
                                               placeholder="000000"
                                               autofocus
                                               required>
                                        <div class="form-text">
                                            {{ __('Code sent at :time. Expires :n minutes after that.', [
                                                'time' => $emailCodeSentAt->format('H:i'),
                                                'n'    => $ttlMinutes,
                                            ]) }}
                                        </div>
                                    </div>

                                    <x-primary-button type="submit">{{ __('Confirm and enable') }}</x-primary-button>
                                </form>

                                <form method="POST" action="{{ route('two-factor.setup.email.send') }}" class="mt-2">
                                    @csrf
                                    <button type="submit" class="btn btn-outline-secondary btn-sm">
                                        <i class="bi bi-arrow-clockwise me-1"></i>{{ __('Send a new code') }}
                                    </button>
                                </form>
                            @endif
                        </div>
                    </div>
                </div>

                <div class="col-12 col-xl-5">
                    <div class="card border-warning">
                        <div class="card-body">
                            <h6 class="text-warning"><i class="bi bi-exclamation-triangle me-2"></i>{{ __('Note about email-based 2FA') }}</h6>
                            <p class="text-muted small mb-0">
                                {{ __('Email-based 2FA is convenient but slightly weaker than an authenticator app: if your email account is compromised, so is your second factor. If you have a smartphone, prefer the authenticator app method.') }}
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        @endif
    </div>
</x-app-layout>
