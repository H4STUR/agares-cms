<x-app-layout>
    <div class="page-breadcrumb d-none d-sm-flex align-items-center mb-3">
        <div class="breadcrumb-title pe-3">{{ __('Recovery Codes') }}</div>
    </div>

    <div class="container-fluid">
        <div class="row g-4 justify-content-center">
            <div class="col-12 col-lg-8">

                @if ($fresh && !empty($codes))
                    <div class="alert alert-warning">
                        <strong><i class="bi bi-exclamation-triangle me-2"></i>{{ __('Save these codes now.') }}</strong>
                        {{ __('They will not be shown again. Each code can be used once to sign in if you lose access to your authenticator app.') }}
                    </div>

                    <div class="card shadow-sm">
                        <div class="card-body">
                            <h5 class="mb-3">{{ __('Your recovery codes') }}</h5>

                            <div class="row g-2 mb-4">
                                @foreach ($codes as $code)
                                    <div class="col-6 col-md-4">
                                        <code class="d-block p-2 bg-light border rounded text-center font-monospace fs-6">{{ $code }}</code>
                                    </div>
                                @endforeach
                            </div>

                            <button type="button"
                                    class="btn btn-outline-secondary btn-sm"
                                    onclick="navigator.clipboard.writeText({{ json_encode(implode(PHP_EOL, $codes)) }})">
                                <i class="bi bi-clipboard me-1"></i>{{ __('Copy all') }}
                            </button>
                            <button type="button"
                                    class="btn btn-outline-secondary btn-sm"
                                    onclick="window.print()">
                                <i class="bi bi-printer me-1"></i>{{ __('Print') }}
                            </button>

                            <hr class="my-4">

                            <a href="{{ route('admin.user.settings', $user) }}" class="btn btn-primary">
                                {{ __("I've saved my codes") }}
                            </a>
                        </div>
                    </div>
                @else
                    <div class="card shadow-sm">
                        <div class="card-body">
                            <h5 class="mb-3">{{ __('Recovery codes') }}</h5>
                            <p class="text-muted">
                                {{ __('You have :n recovery code(s) remaining.', ['n' => $remaining]) }}
                            </p>
                            <p class="text-muted small">
                                {{ __('Existing recovery codes cannot be displayed again. If you have lost them, regenerate a new set — your old codes will stop working immediately.') }}
                            </p>

                            <form method="POST" action="{{ route('two-factor.recovery-codes.regenerate') }}">
                                @csrf
                                <div class="mb-3">
                                    <label for="password" class="form-label">{{ __('Confirm your password') }}</label>
                                    <input type="password" id="password" name="password" class="form-control" required>
                                </div>
                                <x-primary-button type="submit">{{ __('Regenerate codes') }}</x-primary-button>
                                <a href="{{ route('admin.user.settings', $user) }}" class="btn btn-link">{{ __('Back') }}</a>
                            </form>
                        </div>
                    </div>
                @endif

            </div>
        </div>
    </div>
</x-app-layout>
