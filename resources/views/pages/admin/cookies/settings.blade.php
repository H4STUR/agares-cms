<x-app-layout>

    <div class="page-breadcrumb d-none d-sm-flex align-items-center mb-3">
        <div class="breadcrumb-title pe-3">{{ __('Cookies') }}</div>
        <div class="ps-3">
            <span class="text-muted">/</span>
            <span class="ms-2 fw-semibold">{{ __('Consent settings') }}</span>
        </div>
    </div>

    <div class="container-fluid">

        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
            <div>
                <h5 class="mb-0">{{ __('Consent settings') }}</h5>
                <small class="text-muted">
                    {{ __('Domain') }}: <code>{{ $domain }}</code>
                </small>
            </div>

            <div class="d-flex gap-2 flex-wrap">
                <a href="{{ route('admin.cookies') }}" class="btn btn-outline-secondary">
                    <i class="material-icons-outlined align-middle me-1">arrow_back</i>
                    {{ __('Back') }}
                </a>
            </div>
        </div>

        <div class="card">
            <div class="card-body">

                <form action="{{ route('admin.cookies.settings.update') }}" method="POST">
                    @csrf

                    {{-- Toggles --}}
                    <div class="row g-3">
                        <div class="col-lg-4">
                            <div class="p-3 rounded-4 border bg-body">
                                <div class="fw-semibold mb-2">{{ __('General') }}</div>

                                <div class="form-check form-switch mb-2">
                                    <input class="form-check-input" type="checkbox" role="switch" id="enabled"
                                           name="enabled" value="1" {{ $cookieSettings->enabled ? 'checked' : '' }}>
                                    <label class="form-check-label" for="enabled">{{ __('Enable banner') }}</label>
                                </div>

                                <div class="form-check form-switch mb-2">
                                    <input class="form-check-input" type="checkbox" role="switch" id="block_until_choice"
                                           name="block_until_choice" value="1" {{ $cookieSettings->block_until_choice ? 'checked' : '' }}>
                                    <label class="form-check-label" for="block_until_choice">{{ __('Block non-essential until choice') }}</label>
                                </div>

                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" role="switch" id="remember_consent"
                                           name="remember_consent" value="1" {{ $cookieSettings->remember_consent ? 'checked' : '' }}>
                                    <label class="form-check-label" for="remember_consent">{{ __('Remember user choice') }}</label>
                                </div>

                                <hr class="my-3">

                                <div class="text-muted small">
                                    {{ __('Essential cookies are always enabled and cannot be disabled.') }}
                                </div>
                            </div>
                        </div>

                        <div class="col-lg-8">
                            <div class="p-3 rounded-4 border bg-body">
                                <div class="fw-semibold mb-2">{{ __('Banner content') }}</div>

                                <div class="mb-3">
                                    <label class="form-label">{{ __('Title') }}</label>
                                    <input type="text" class="form-control" name="title" value="{{ old('title', $cookieSettings->title) }}" required>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">{{ __('Message') }}</label>
                                    <textarea class="form-control" name="message" rows="4">{{ old('message', $cookieSettings->message) }}</textarea>
                                    <small class="text-muted">{{ __('Short description shown on the cookie banner.') }}</small>
                                </div>

                                <div class="row g-3">
                                    <div class="col-md-3">
                                        <label class="form-label">{{ __('Accept all') }}</label>
                                        <input type="text" class="form-control" name="btn_accept_all"
                                               value="{{ old('btn_accept_all', $cookieSettings->btn_accept_all) }}" required>
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label">{{ __('Reject all') }}</label>
                                        <input type="text" class="form-control" name="btn_reject_all"
                                               value="{{ old('btn_reject_all', $cookieSettings->btn_reject_all) }}" required>
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label">{{ __('Manage') }}</label>
                                        <input type="text" class="form-control" name="btn_manage"
                                               value="{{ old('btn_manage', $cookieSettings->btn_manage) }}" required>
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label">{{ __('Save') }}</label>
                                        <input type="text" class="form-control" name="btn_save"
                                               value="{{ old('btn_save', $cookieSettings->btn_save) }}" required>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Categories --}}
                    <div class="p-3 rounded-4 border bg-body mt-3">
                        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-2">
                            <div class="fw-semibold">{{ __('Cookie categories') }}</div>
                            <div class="text-muted small">
                                {{ __('Choose which categories can be enabled by default.') }}
                            </div>
                        </div>

                        <div class="row g-3">
                            <div class="col-md-3">
                                <div class="p-3 rounded-4 border">
                                    <div class="fw-semibold">{{ __('Essential') }}</div>
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" role="switch" checked disabled>
                                        <label class="form-check-label text-muted">{{ __('Locked') }}</label>
                                    </div>
                                    <textarea class="form-control mt-2" name="desc_essential" rows="3"
                                              placeholder="{{ __('Description (optional)') }}">{{ old('desc_essential', $cookieSettings->desc_essential) }}</textarea>
                                </div>
                            </div>

                            <div class="col-md-3">
                                <div class="p-3 rounded-4 border">
                                    <div class="fw-semibold">{{ __('Functional') }}</div>
                                    <div class="form-check form-switch mb-2">
                                        <input class="form-check-input" type="checkbox" role="switch" id="allow_functional"
                                               name="allow_functional" value="1" {{ $cookieSettings->allow_functional ? 'checked' : '' }}>
                                        <label class="form-check-label" for="allow_functional">{{ __('Enabled by default') }}</label>
                                    </div>
                                    <textarea class="form-control" name="desc_functional" rows="3"
                                              placeholder="{{ __('Description (optional)') }}">{{ old('desc_functional', $cookieSettings->desc_functional) }}</textarea>
                                </div>
                            </div>

                            <div class="col-md-3">
                                <div class="p-3 rounded-4 border">
                                    <div class="fw-semibold">{{ __('Analytics') }}</div>
                                    <div class="form-check form-switch mb-2">
                                        <input class="form-check-input" type="checkbox" role="switch" id="allow_analytics"
                                               name="allow_analytics" value="1" {{ $cookieSettings->allow_analytics ? 'checked' : '' }}>
                                        <label class="form-check-label" for="allow_analytics">{{ __('Enabled by default') }}</label>
                                    </div>
                                    <textarea class="form-control" name="desc_analytics" rows="3"
                                              placeholder="{{ __('Description (optional)') }}">{{ old('desc_analytics', $cookieSettings->desc_analytics) }}</textarea>
                                </div>
                            </div>

                            <div class="col-md-3">
                                <div class="p-3 rounded-4 border">
                                    <div class="fw-semibold">{{ __('Marketing') }}</div>
                                    <div class="form-check form-switch mb-2">
                                        <input class="form-check-input" type="checkbox" role="switch" id="allow_marketing"
                                               name="allow_marketing" value="1" {{ $cookieSettings->allow_marketing ? 'checked' : '' }}>
                                        <label class="form-check-label" for="allow_marketing">{{ __('Enabled by default') }}</label>
                                    </div>
                                    <textarea class="form-control" name="desc_marketing" rows="3"
                                              placeholder="{{ __('Description (optional)') }}">{{ old('desc_marketing', $cookieSettings->desc_marketing) }}</textarea>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="text-end mt-4">
                        <button type="submit" class="btn btn-primary">
                            <i class="material-icons-outlined align-middle me-1">save</i>
                            {{ __('Save') }}
                        </button>
                    </div>
                </form>

            </div>
        </div>

    </div>
</x-app-layout>
