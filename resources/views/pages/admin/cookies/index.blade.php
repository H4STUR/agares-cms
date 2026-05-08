{{-- resources/views/pages/admin/cookies/index.blade.php --}}

<x-app-layout>

    <div class="page-breadcrumb d-none d-sm-flex align-items-center mb-3">
        <div class="breadcrumb-title pe-3">{{ __('Cookies') }}</div>
        <div class="ps-3">
            <span class="text-muted">/</span>
            <span class="ms-2 fw-semibold">{{ __('Scanner & Consent') }}</span>
        </div>
    </div>

    <div class="container-fluid">

        {{-- Top Actions --}}
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
            <div>
                <h5 class="mb-0">{{ __('Cookies') }}</h5>
                <small class="text-muted">
                    {{ __('Scan your domain for cookies and configure consent settings via Agares SaaS.') }}
                </small>
            </div>

            <div class="d-flex gap-2 flex-wrap">
                <a href="{{ route('admin.cookies.settings.edit') }}" class="btn btn-outline-primary">
                    <i class="material-icons-outlined align-middle me-1">tune</i>
                    {{ __('Consent settings') }}
                </a>

                <a href="{{ route('admin.cookies.scans') }}" class="btn btn-outline-secondary">
                    <i class="material-icons-outlined align-middle me-1">history</i>
                    {{ __('Scan history') }}
                </a>
            </div>
        </div>

        {{-- ── Agares SaaS Integration Card ──────────────────────────────── --}}
        <div class="card mb-4">
            <div class="card-header d-flex align-items-center gap-2">
                <i class="material-icons-outlined text-primary">cloud</i>
                <span class="fw-semibold">{{ __('Agares SaaS Integration') }}</span>
            </div>
            <div class="card-body">

                <form action="{{ route('admin.cookies.saas.settings') }}" method="POST">
                    @csrf

                    <div class="row g-3 align-items-start">
                        <div class="col-lg-5">
                            <label class="form-label mb-1 fw-medium">
                                {{ __('SaaS URL') }}
                            </label>
                            <input
                                type="url"
                                name="agares_saas_url"
                                class="form-control @error('agares_saas_url') is-invalid @enderror"
                                placeholder="https://api.agares.co.uk"
                                value="{{ old('agares_saas_url', \App\Models\Setting::str('agares_saas_url')) }}"
                                autocomplete="off"
                            >
                            @error('agares_saas_url')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-lg-5">
                            <label class="form-label mb-1 fw-medium">
                                {{ __('API Key') }}
                                <span class="text-muted fw-normal">(agr_…)</span>
                            </label>
                            <input
                                type="password"
                                name="agares_saas_api_key"
                                class="form-control @error('agares_saas_api_key') is-invalid @enderror"
                                placeholder="{{ $saasConfigured ? '••••••••••••••••' : 'agr_…' }}"
                                autocomplete="new-password"
                            >
                            @error('agares_saas_api_key')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            @if($saasConfigured)
                                <small class="text-muted">{{ __('Leave blank to keep current key.') }}</small>
                            @endif
                        </div>

                        <div class="col-lg-2 d-grid">
                            <label class="form-label mb-1" style="visibility:hidden">_</label>
                            <button type="submit" class="btn btn-primary">
                                <i class="material-icons-outlined align-middle me-1">save</i>
                                {{ __('Save') }}
                            </button>
                        </div>
                    </div>
                </form>

                {{-- Connection status + check button (Alpine) --}}
                <div
                    class="mt-3 pt-3 border-top d-flex align-items-center gap-3 flex-wrap"
                    x-data="connectionCheck()"
                >
                    <div>
                        <span class="fw-medium me-2">{{ __('Connection status') }}:</span>

                        <span x-show="status === null" class="badge bg-secondary">
                            {{ __('Not checked') }}
                        </span>
                        <span x-show="status === 'checking'" class="badge bg-warning text-dark">
                            <span class="spinner-border spinner-border-sm me-1" style="width:.7rem;height:.7rem"></span>
                            {{ __('Checking…') }}
                        </span>
                        <span x-show="status === 'ok'" class="badge bg-success">
                            <i class="material-icons-outlined align-middle" style="font-size:13px">check_circle</i>
                            {{ __('Connected') }}
                        </span>
                        <span x-show="status === 'fail'" class="badge bg-danger">
                            <i class="material-icons-outlined align-middle" style="font-size:13px">error</i>
                            <span x-text="failMessage"></span>
                        </span>
                    </div>

                    <button
                        type="button"
                        class="btn btn-sm btn-outline-secondary"
                        :disabled="status === 'checking'"
                        @click="check()"
                    >
                        <i class="material-icons-outlined align-middle me-1" style="font-size:15px">refresh</i>
                        {{ __('Check connection') }}
                    </button>

                    @if(! $saasConfigured)
                        <small class="text-muted">{{ __('Configure your API key above first.') }}</small>
                    @endif
                </div>

            </div>
        </div>

        {{-- ── Scan + Consent Row ─────────────────────────────────────────── --}}
        <div class="card">
            <div class="card-body">

                <div class="row g-3 mb-4">

                    {{-- Scan panel --}}
                    <div class="col-lg-7">
                        <div
                            class="p-3 rounded-4 border bg-body"
                            x-data="cookieScan({
                                scanAsyncUrl: '{{ route('admin.cookies.scan.async') }}',
                                progressBaseUrl: '{{ url('admin/cookies/scan-progress') }}',
                                cancelUrl: '{{ url('admin/cookies/scan-cancel') }}',
                                showScanBaseUrl: '{{ url('admin/cookies/scans') }}',
                                pendingScanId: {{ $pendingScan?->id ?? 'null' }},
                                csrfToken: '{{ csrf_token() }}'
                            })"
                            x-init="init()"
                        >
                            <div class="d-flex align-items-center justify-content-between gap-3 flex-wrap mb-3">
                                <div>
                                    <div class="fw-semibold">{{ __('Current domain') }}</div>
                                    <div class="text-muted small"><code>{{ $domain }}</code></div>
                                </div>

                                {{-- Live status badge --}}
                                <div class="text-end">
                                    <div class="fw-semibold">{{ __('Scanner') }}</div>
                                    <span x-show="scanStatus === null || scanStatus === 'idle'" class="badge rounded-pill bg-secondary">{{ __('Idle') }}</span>
                                    <span x-show="scanStatus === 'pending' || scanStatus === 'scanning'" class="badge rounded-pill bg-warning text-dark">
                                        <span class="spinner-border spinner-border-sm me-1" style="width:.7rem;height:.7rem"></span>
                                        <span x-text="scanStatus === 'scanning' ? '{{ __('Scanning…') }}' : '{{ __('Queued') }}'"></span>
                                    </span>
                                    <span x-show="scanStatus === 'completed'" class="badge rounded-pill bg-success">{{ __('Done') }}</span>
                                    <span x-show="scanStatus === 'failed'" class="badge rounded-pill bg-danger">{{ __('Failed') }}</span>
                                </div>
                            </div>

                            {{-- URL input + trigger --}}
                            <div class="row g-2 align-items-end mb-2">
                                <div class="col-lg-9">
                                    <label class="form-label mb-1">{{ __('Scan URL (optional)') }}</label>
                                    <input
                                        type="text"
                                        x-model="scanUrl"
                                        class="form-control"
                                        placeholder="{{ __('Leave empty to scan current domain') }}"
                                        :disabled="isScanning"
                                    >
                                </div>

                                <div class="col-lg-3 d-grid">
                                    @if($saasConfigured)
                                        <button
                                            type="button"
                                            class="btn btn-primary"
                                            :disabled="isScanning"
                                            @click="startScan()"
                                        >
                                            <template x-if="!isScanning">
                                                <span>
                                                    <i class="material-icons-outlined align-middle me-1">cloud_sync</i>
                                                    {{ __('Request scan') }}
                                                </span>
                                            </template>
                                            <template x-if="isScanning">
                                                <span>
                                                    <span class="spinner-border spinner-border-sm me-1"></span>
                                                    {{ __('Scanning…') }}
                                                </span>
                                            </template>
                                        </button>
                                    @else
                                        {{-- Fallback: direct legacy scan when SaaS not configured --}}
                                        <form action="{{ route('admin.cookies.scan.async') }}" method="POST">
                                            @csrf
                                            <input type="hidden" name="url" x-bind:value="scanUrl">
                                            <button type="submit" class="btn btn-outline-secondary w-100">
                                                <i class="material-icons-outlined align-middle me-1">search</i>
                                                {{ __('Scan (direct)') }}
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </div>

                            @if($saasConfigured)
                                <small class="text-muted d-block">
                                    {{ __('Scan runs in background via Agares SaaS. Results saved locally when done.') }}
                                </small>
                            @else
                                <small class="text-warning d-block">
                                    <i class="material-icons-outlined align-middle" style="font-size:13px">warning</i>
                                    {{ __('SaaS not configured — using legacy direct scan.') }}
                                </small>
                            @endif

                            {{-- Progress bar + cancel (visible while scanning) --}}
                            <div x-show="isScanning" class="mt-3">
                                <div class="progress" style="height: 6px;">
                                    <div
                                        class="progress-bar progress-bar-striped progress-bar-animated bg-primary"
                                        role="progressbar"
                                        style="width: 100%"
                                    ></div>
                                </div>
                                <div class="d-flex align-items-center justify-content-between mt-2">
                                    <small class="text-muted">
                                        {{ __('Scan can take up to 90 seconds. This page will update automatically.') }}
                                    </small>
                                    <button
                                        type="button"
                                        class="btn btn-sm btn-outline-danger"
                                        :disabled="cancelling"
                                        @click="cancelScan()"
                                    >
                                        <template x-if="!cancelling">
                                            <span>
                                                <i class="material-icons-outlined align-middle" style="font-size:15px">stop_circle</i>
                                                {{ __('Cancel') }}
                                            </span>
                                        </template>
                                        <template x-if="cancelling">
                                            <span>
                                                <span class="spinner-border spinner-border-sm me-1"></span>
                                                {{ __('Cancelling…') }}
                                            </span>
                                        </template>
                                    </button>
                                </div>
                            </div>

                            {{-- Error message --}}
                            <div x-show="errorMessage" class="alert alert-danger mt-3 mb-0 py-2 small" x-text="errorMessage"></div>

                        </div>
                    </div>

                    {{-- Consent quick summary --}}
                    <div class="col-lg-5">
                        <div class="p-3 rounded-4 border bg-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div class="fw-semibold">{{ __('Consent configuration') }}</div>
                                <a href="{{ route('admin.cookies.settings.edit') }}" class="small text-decoration-none">
                                    {{ __('Edit') }}
                                </a>
                            </div>

                            <div class="mt-3 d-flex flex-wrap gap-2">
                                <span class="badge rounded-pill {{ ($cookieSettings->enabled ?? false) ? 'bg-success' : 'bg-secondary' }}">
                                    {{ ($cookieSettings->enabled ?? false) ? __('Enabled') : __('Disabled') }}
                                </span>

                                <span class="badge rounded-pill {{ ($cookieSettings->block_until_choice ?? false) ? 'bg-warning text-dark' : 'bg-secondary' }}">
                                    {{ ($cookieSettings->block_until_choice ?? false) ? __('Block until choice') : __('No blocking') }}
                                </span>

                                <span class="badge rounded-pill {{ ($cookieSettings->remember_consent ?? false) ? 'bg-info text-dark' : 'bg-secondary' }}">
                                    {{ ($cookieSettings->remember_consent ?? false) ? __('Remember consent') : __('No memory') }}
                                </span>
                            </div>

                            <div class="mt-3 small text-muted">
                                {{ __('Default categories') }}:
                            </div>

                            <div class="mt-2 d-flex flex-wrap gap-2">
                                <span class="badge rounded-pill bg-success">{{ __('Essential') }}</span>
                                <span class="badge rounded-pill {{ ($cookieSettings->allow_functional ?? true) ? 'bg-info text-dark' : 'bg-secondary' }}">{{ __('Functional') }}</span>
                                <span class="badge rounded-pill {{ ($cookieSettings->allow_analytics ?? false) ? 'bg-warning text-dark' : 'bg-secondary' }}">{{ __('Analytics') }}</span>
                                <span class="badge rounded-pill {{ ($cookieSettings->allow_marketing ?? false) ? 'bg-danger' : 'bg-secondary' }}">{{ __('Marketing') }}</span>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Last scan summary --}}
                <div class="p-3 rounded-4 border bg-body">
                    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                        <div class="fw-semibold">{{ __('Last completed scan') }}</div>

                        @if($lastScan)
                            <a href="{{ route('admin.cookies.scans.show', $lastScan->id) }}" class="btn btn-sm btn-outline-secondary">
                                <i class="material-icons-outlined align-middle me-1">visibility</i>
                                {{ __('View details') }}
                            </a>
                        @endif
                    </div>

                    @if(!$lastScan)
                        <p class="text-muted mb-0 mt-2">
                            {{ __('No completed scans yet. Request a scan above.') }}
                        </p>
                    @else
                        <div class="text-muted small mt-2">
                            {{ __('Scanned at') }}:
                            <strong>{{ $lastScan->scanned_at?->format('Y-m-d H:i') }}</strong>
                            • {{ __('URL') }}: <code>{{ $lastScan->url }}</code>
                        </div>

                        <div class="row g-3 mt-2">
                            <div class="col-md-3">
                                <div class="p-3 rounded-4 border bg-white">
                                    <div class="text-muted small">{{ __('Total cookies') }}</div>
                                    <div class="fs-4 fw-bold">{{ $lastScan->total }}</div>
                                </div>
                            </div>

                            <div class="col-md-3">
                                <div class="p-3 rounded-4 border bg-white">
                                    <div class="text-muted small">{{ __('1st party') }}</div>
                                    <div class="fs-4 fw-bold">{{ $lastScan->first_party }}</div>
                                </div>
                            </div>

                            <div class="col-md-3">
                                <div class="p-3 rounded-4 border bg-white">
                                    <div class="text-muted small">{{ __('3rd party') }}</div>
                                    <div class="fs-4 fw-bold">{{ $lastScan->third_party }}</div>
                                </div>
                            </div>

                            <div class="col-md-3">
                                <div class="p-3 rounded-4 border bg-white">
                                    <div class="text-muted small">{{ __('Privacy grade') }}</div>
                                    <div class="fs-4 fw-bold">
                                        {{ $lastScan->privacy_grade ?? '-' }}
                                        <span class="text-muted fs-6">({{ $lastScan->privacy_score ?? '-' }}/100)</span>
                                    </div>
                                </div>
                            </div>

                            <div class="col-12">
                                <div class="p-3 rounded-4 border bg-white">
                                    <div class="text-muted small mb-2">{{ __('By category') }}</div>
                                    <div class="d-flex flex-wrap gap-2">
                                        <span class="badge rounded-pill bg-success">
                                            {{ __('Essential') }}: {{ $lastScan->essential }}
                                        </span>
                                        <span class="badge rounded-pill bg-info text-dark">
                                            {{ __('Functional') }}: {{ $lastScan->functional }}
                                        </span>
                                        <span class="badge rounded-pill bg-warning text-dark">
                                            {{ __('Analytics') }}: {{ $lastScan->analytics }}
                                        </span>
                                        <span class="badge rounded-pill bg-danger">
                                            {{ __('Marketing') }}: {{ $lastScan->marketing }}
                                        </span>
                                    </div>

                                    @if(!empty($lastScan->requested_domains))
                                        <div class="mt-3">
                                            <div class="text-muted small mb-2">{{ __('Requested domains') }}</div>
                                            <div class="small">
                                                @foreach(($lastScan->requested_domains ?? []) as $d)
                                                    <span class="badge rounded-pill bg-light text-dark border">{{ $d }}</span>
                                                @endforeach
                                            </div>
                                        </div>
                                    @endif
                                </div>
                            </div>

                        </div>
                    @endif
                </div>

            </div>
        </div>

    </div>

    {{-- Alpine components --}}
    <script>
    function connectionCheck() {
        return {
            status: null,
            failMessage: 'Offline',

            async check() {
                this.status = 'checking';
                try {
                    const resp = await fetch('{{ route('admin.cookies.connection.check') }}', {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Accept': 'application/json',
                        },
                    });
                    const data = await resp.json();
                    if (data.ok) {
                        this.status = 'ok';
                    } else {
                        this.failMessage = data.message ?? 'Offline';
                        this.status = 'fail';
                    }
                } catch (e) {
                    this.failMessage = 'Network error';
                    this.status = 'fail';
                }
            }
        };
    }

    function cookieScan(cfg) {
        return {
            scanUrl: '',
            scanStatus: null,
            isScanning: false,
            cancelling: false,
            errorMessage: null,
            pollInterval: null,
            currentScanId: null,

            init() {
                // Resume polling if a scan was in progress when page loaded
                if (cfg.pendingScanId) {
                    this.currentScanId = cfg.pendingScanId;
                    this.isScanning    = true;
                    this.scanStatus    = 'pending';
                    this.startPolling();
                }
            },

            async startScan() {
                this.isScanning   = true;
                this.errorMessage = null;
                this.scanStatus   = 'pending';

                try {
                    const resp = await fetch(cfg.scanAsyncUrl, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': cfg.csrfToken,
                            'Accept': 'application/json',
                            'Content-Type': 'application/json',
                        },
                        body: JSON.stringify({ url: this.scanUrl }),
                    });

                    const data = await resp.json();

                    if (!resp.ok) {
                        this.errorMessage = data.error ?? 'Failed to start scan.';
                        this.isScanning   = false;
                        this.scanStatus   = 'failed';
                        return;
                    }

                    this.currentScanId = data.scan_id;
                    this.startPolling();
                } catch (e) {
                    this.errorMessage = 'Network error. Please try again.';
                    this.isScanning   = false;
                    this.scanStatus   = 'failed';
                }
            },

            startPolling() {
                this.pollInterval = setInterval(() => this.pollStatus(), 4000);
            },

            async pollStatus() {
                if (!this.currentScanId) return;

                try {
                    const resp = await fetch(cfg.progressBaseUrl + '/' + this.currentScanId, {
                        headers: { 'Accept': 'application/json' },
                    });

                    const data = await resp.json();
                    this.scanStatus = data.status;

                    if (data.status === 'completed') {
                        clearInterval(this.pollInterval);
                        this.isScanning = false;
                        window.location.href = cfg.showScanBaseUrl + '/' + this.currentScanId;
                        return;
                    }

                    if (data.status === 'failed') {
                        clearInterval(this.pollInterval);
                        this.isScanning   = false;
                        this.errorMessage = data.error_message ?? 'Scan failed.';
                    }

                    if (data.status === 'cancelled') {
                        clearInterval(this.pollInterval);
                        this.isScanning   = false;
                        this.cancelling   = false;
                        this.scanStatus   = 'idle';
                    }
                } catch (e) {
                    // network hiccup — keep polling
                }
            },

            async cancelScan() {
                if (!this.currentScanId || this.cancelling) return;

                this.cancelling = true;

                try {
                    await fetch(cfg.cancelUrl + '/' + this.currentScanId, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': cfg.csrfToken,
                            'Accept': 'application/json',
                        },
                    });
                    // Poll will pick up the cancelled status and reset the UI
                } catch (e) {
                    this.cancelling = false;
                }
            },
        };
    }
    </script>

</x-app-layout>
