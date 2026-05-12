<x-app-layout>

    <div class="page-breadcrumb d-none d-sm-flex align-items-center mb-3">
        <div class="breadcrumb-title pe-3">{{ __('Cookies') }}</div>
        <div class="ps-3">
            <span class="text-muted">/</span>
            <a class="text-decoration-none ms-2" href="{{ route('admin.cookies.scans') }}">{{ __('Scan history') }}</a>
            <span class="text-muted ms-2">/</span>
            <span class="ms-2 fw-semibold">{{ __('Scan details') }}</span>
        </div>
    </div>

    <div class="container-fluid">

        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
            <div>
                <h5 class="mb-0">{{ __('Scan details') }}</h5>
                <small class="text-muted">
                    {{ __('Scanned at') }}: <strong>{{ $scan->scanned_at?->format('Y-m-d H:i') }}</strong>
                    • {{ __('URL') }}: <code>{{ $scan->url }}</code>
                </small>
            </div>

            <div class="d-flex gap-2 flex-wrap">
                <a href="{{ route('admin.cookies.scans') }}" class="btn btn-outline-secondary">
                    <i class="material-icons-outlined align-middle me-1">arrow_back</i>
                    {{ __('Back') }}
                </a>

                <form action="{{ route('admin.cookies.scan.async') }}" method="POST">
                    @csrf
                    <input type="hidden" name="url" value="{{ $scan->url }}">
                    <button class="btn btn-primary">
                        <i class="material-icons-outlined align-middle me-1">refresh</i>
                        {{ __('Rescan this URL') }}
                    </button>
                </form>
            </div>
        </div>

        {{-- Summary --}}
        <div class="row g-3 mb-3">
            <div class="col-lg-12">
                <div class="card">
                    <div class="card-body">

                        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                            <div class="fw-semibold">{{ __('Summary') }}</div>
                            <div class="d-flex gap-2 flex-wrap">
                                <span class="badge bg-success">{{ __('Essential') }}: {{ $scan->essential }}</span>
                                <span class="badge bg-info text-dark">{{ __('Functional') }}: {{ $scan->functional }}</span>
                                <span class="badge bg-warning text-dark">{{ __('Analytics') }}: {{ $scan->analytics }}</span>
                                <span class="badge bg-danger">{{ __('Marketing') }}: {{ $scan->marketing }}</span>
                            </div>
                        </div>

                        <div class="row g-3 mt-2">
                            <div class="col-md-4">
                                <div class="p-3 rounded-4 border bg-body">
                                    <div class="text-muted small">{{ __('Total') }}</div>
                                    <div class="fs-4 fw-bold">{{ $scan->total }}</div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="p-3 rounded-4 border bg-body">
                                    <div class="text-muted small">{{ __('1st Party') }}</div>
                                    <div class="fs-4 fw-bold">{{ $scan->first_party }}</div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="p-3 rounded-4 border bg-body">
                                    <div class="text-muted small">{{ __('3rd Party') }}</div>
                                    <div class="fs-4 fw-bold">{{ $scan->third_party }}</div>
                                </div>
                            </div>
                            {{-- <div class="col-md-3">
                                <div class="p-3 rounded-4 border bg-body">
                                    <div class="text-muted small">{{ __('Grade') }}</div>
                                    <div class="fs-4 fw-bold">
                                        {{ $scan->privacy_grade ?? '-' }}
                                        <span class="text-muted fs-6">({{ $scan->privacy_score ?? '-' }}/100)</span>
                                    </div>
                                </div>
                            </div> --}}
                        </div>

                        @if(!empty($scan->requested_domains))
                            <hr class="my-3">
                            <div class="text-muted small mb-2">{{ __('Requested domains') }}</div>
                            <div class="d-flex flex-wrap gap-2">
                                @foreach(($scan->requested_domains ?? []) as $d)
                                    <span class="badge bg-light text-dark border">{{ $d }}</span>
                                @endforeach
                            </div>
                        @endif

                    </div>
                </div>
            </div>

            {{-- Privacy Issues --}}
            {{-- <div class="col-lg-4">
                <div class="card">
                    <div class="card-body">
                        <div class="fw-semibold mb-2">{{ __('Privacy analysis') }}</div>

                        @php
                            $issues = $scan->raw_payload['privacyAnalysis']['issues'] ?? [];
                            $recs = $scan->raw_payload['privacyAnalysis']['recommendations'] ?? [];
                        @endphp

                        @if(empty($issues) && empty($recs))
                            <p class="text-muted mb-0">{{ __('No analysis details saved for this scan.') }}</p>
                        @else
                            @if(!empty($issues))
                                <div class="mb-3">
                                    <div class="text-warning fw-semibold mb-2">⚠️ {{ __('Issues') }}</div>
                                    @foreach($issues as $i)
                                        <div class="p-2 rounded-3 border bg-body mb-2 small">
                                            {{ $i }}
                                        </div>
                                    @endforeach
                                </div>
                            @endif

                            @if(!empty($recs))
                                <div>
                                    <div class="text-success fw-semibold mb-2">💡 {{ __('Recommendations') }}</div>
                                    @foreach($recs as $r)
                                        <div class="p-2 rounded-3 border bg-body mb-2 small">
                                            {{ $r }}
                                        </div>
                                    @endforeach
                                </div>
                            @endif
                        @endif

                    </div>
                </div>
            </div> --}}
        </div>

        {{-- Filters --}}
        @php
            $type = request('type', 'all'); // all/essential/functional/analytics/marketing
            $party = request('party', 'all'); // all/first/third
            $q = trim((string)request('q', ''));

            $cookies = $scan->cookies;

            if ($type !== 'all') {
                $cookies = $cookies->where('type', $type);
            }
            if ($party === 'first') {
                $cookies = $cookies->where('is_first_party', true);
            } elseif ($party === 'third') {
                $cookies = $cookies->where('is_first_party', false);
            }
            if ($q !== '') {
                $cookies = $cookies->filter(function ($c) use ($q) {
                    $hay = strtolower(($c->name ?? '').' '.($c->domain ?? '').' '.($c->description ?? ''));
                    return str_contains($hay, strtolower($q));
                });
            }
        @endphp

        <div class="card">
            <div class="card-body">

                <form method="GET" class="row g-2 align-items-end mb-3">
                    <div class="col-md-4">
                        <label class="form-label">{{ __('Search') }}</label>
                        <input type="text" name="q" value="{{ request('q') }}" class="form-control" placeholder="{{ __('cookie name, domain...') }}">
                    </div>

                    <div class="col-md-3">
                        <label class="form-label">{{ __('Type') }}</label>
                        <select name="type" class="form-select">
                            <option value="all" {{ $type==='all'?'selected':'' }}>{{ __('All') }}</option>
                            <option value="essential" {{ $type==='essential'?'selected':'' }}>{{ __('Essential') }}</option>
                            <option value="functional" {{ $type==='functional'?'selected':'' }}>{{ __('Functional') }}</option>
                            <option value="analytics" {{ $type==='analytics'?'selected':'' }}>{{ __('Analytics') }}</option>
                            <option value="marketing" {{ $type==='marketing'?'selected':'' }}>{{ __('Marketing') }}</option>
                        </select>
                    </div>

                    <div class="col-md-3">
                        <label class="form-label">{{ __('Party') }}</label>
                        <select name="party" class="form-select">
                            <option value="all" {{ $party==='all'?'selected':'' }}>{{ __('All') }}</option>
                            <option value="first" {{ $party==='first'?'selected':'' }}>{{ __('1st party') }}</option>
                            <option value="third" {{ $party==='third'?'selected':'' }}>{{ __('3rd party') }}</option>
                        </select>
                    </div>

                    <div class="col-md-2 d-grid">
                        <button class="btn btn-outline-primary">
                            <i class="material-icons-outlined align-middle me-1">filter_alt</i>
                            {{ __('Filter') }}
                        </button>
                    </div>
                </form>

                <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-2">
                    <div class="text-muted small">
                        {{ __('Showing') }} <strong>{{ $cookies->count() }}</strong> {{ __('cookies') }}
                    </div>
                </div>

                @if($cookies->count() === 0)
                    <p class="text-muted mb-0">{{ __('No cookies match your filters.') }}</p>
                @else
                    <div class="row g-3">
                        @foreach($cookies as $cookie)
                            @php
                                $badgeClass = match($cookie->type) {
                                    'essential' => 'bg-success',
                                    'functional' => 'bg-info text-dark',
                                    'analytics' => 'bg-warning text-dark',
                                    'marketing' => 'bg-danger',
                                    default => 'bg-secondary',
                                };
                            @endphp

                            <div class="col-xl-6">
                                <div class="p-3 rounded-4 border bg-body">
                                    <div class="d-flex justify-content-between align-items-start gap-2">
                                        <div>
                                            <div class="fw-semibold">
                                                <code>{{ $cookie->name }}</code>
                                            </div>
                                            <div class="text-muted small">
                                                {{ $cookie->description ?? __('No description') }}
                                            </div>
                                        </div>

                                        <div class="text-end d-flex flex-column gap-1 align-items-end">
                                            <span class="badge {{ $badgeClass }}">{{ ucfirst($cookie->type) }}</span>
                                            <span class="badge {{ $cookie->is_first_party ? 'bg-success' : 'bg-warning text-dark' }}">
                                                {{ $cookie->is_first_party ? __('1st party') : __('3rd party') }}
                                            </span>
                                        </div>
                                    </div>

                                    <hr class="my-2">

                                    <div class="row g-2 small">
                                        <div class="col-md-6">
                                            <div class="text-muted">{{ __('Domain') }}</div>
                                            <div><code>{{ $cookie->domain }}</code></div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="text-muted">{{ __('Path') }}</div>
                                            <div><code>{{ $cookie->path }}</code></div>
                                        </div>

                                        <div class="col-md-6">
                                            <div class="text-muted">{{ __('Expires') }}</div>
                                            <div>
                                                <code>{{ $cookie->expires ?? '-' }}</code>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="text-muted">{{ __('SameSite') }}</div>
                                            <div><code>{{ $cookie->same_site ?? '-' }}</code></div>
                                        </div>

                                        <div class="col-md-6">
                                            <div class="text-muted">{{ __('Secure') }}</div>
                                            <div>{{ $cookie->secure ? '✓' : '✗' }}</div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="text-muted">{{ __('HttpOnly') }}</div>
                                            <div>{{ $cookie->http_only ? '✓' : '✗' }}</div>
                                        </div>

                                        {{-- Value (optional): show only first 80 chars to avoid leaking in UI --}}
                                        @if(!empty($cookie->value))
                                            <div class="col-12">
                                                <div class="text-muted">{{ __('Value (preview)') }}</div>
                                                <div>
                                                    <code>{{ \Illuminate\Support\Str::limit($cookie->value, 80) }}</code>
                                                </div>
                                            </div>
                                        @endif
                                    </div>

                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif

            </div>
        </div>

    </div>
</x-app-layout>
