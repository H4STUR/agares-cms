<x-app-layout>

    <div class="page-breadcrumb d-none d-sm-flex align-items-center mb-3">
        <div class="breadcrumb-title pe-3">{{ __('Cookies') }}</div>
        <div class="ps-3">
            <span class="text-muted">/</span>
            <span class="ms-2 fw-semibold">{{ __('Scan history') }}</span>
        </div>
    </div>

    <div class="container-fluid">

        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
            <div>
                <h5 class="mb-0">{{ __('Scan history') }}</h5>
                <small class="text-muted">
                    {{ __('Domain') }}: <code>{{ $domain }}</code>
                </small>
            </div>

            <div class="d-flex gap-2 flex-wrap">
                <a href="{{ route('admin.cookies') }}" class="btn btn-outline-secondary">
                    <i class="material-icons-outlined align-middle me-1">arrow_back</i>
                    {{ __('Back') }}
                </a>

                <form action="{{ route('admin.cookies.scan.async') }}" method="POST">
                    @csrf
                    <button class="btn btn-primary">
                        <i class="material-icons-outlined align-middle me-1">search</i>
                        {{ __('Scan now') }}
                    </button>
                </form>
            </div>
        </div>

        <div class="card">
            <div class="card-body">

                @if($scans->count() === 0)
                    <p class="text-muted mb-0">{{ __('No scans found.') }}</p>
                @else
                    <div class="table-responsive">
                        <table class="table align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th style="width: 190px;">{{ __('Date') }}</th>
                                    <th>{{ __('URL') }}</th>
                                    <th class="text-center">{{ __('Total') }}</th>
                                    <th class="text-center">{{ __('1st') }}</th>
                                    <th class="text-center">{{ __('3rd') }}</th>
                                    <th class="text-center">{{ __('Grade') }}</th>
                                    <th style="width: 120px;" class="text-end">{{ __('Actions') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($scans as $scan)
                                    <tr>
                                        <td>
                                            <div class="fw-semibold">
                                                {{ $scan->scanned_at?->format('Y-m-d') }}
                                            </div>
                                            <div class="text-muted small">
                                                {{ $scan->scanned_at?->format('H:i') }}
                                            </div>
                                        </td>

                                        <td>
                                            <div class="text-muted small">
                                                <code>{{ $scan->url }}</code>
                                            </div>

                                            <div class="mt-1 d-flex flex-wrap gap-1">
                                                <span class="badge bg-success">{{ __('Essential') }}: {{ $scan->essential }}</span>
                                                <span class="badge bg-info text-dark">{{ __('Functional') }}: {{ $scan->functional }}</span>
                                                <span class="badge bg-warning text-dark">{{ __('Analytics') }}: {{ $scan->analytics }}</span>
                                                <span class="badge bg-danger">{{ __('Marketing') }}: {{ $scan->marketing }}</span>
                                            </div>
                                        </td>

                                        <td class="text-center fw-semibold">{{ $scan->total }}</td>
                                        <td class="text-center">{{ $scan->first_party }}</td>
                                        <td class="text-center">{{ $scan->third_party }}</td>

                                        <td class="text-center">
                                            <span class="badge rounded-pill {{ ($scan->privacy_grade ?? '') === 'A' ? 'bg-success' : 'bg-secondary' }}">
                                                {{ $scan->privacy_grade ?? '-' }}
                                            </span>
                                            <div class="text-muted small">
                                                {{ $scan->privacy_score ?? '-' }}/100
                                            </div>
                                        </td>

                                        <td class="text-end">
                                            <a href="{{ route('admin.cookies.scans.show', $scan->id) }}" class="btn btn-sm btn-outline-secondary">
                                                <i class="material-icons-outlined align-middle">visibility</i>
                                            </a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-3">
                        {{ $scans->links() }}
                    </div>
                @endif

            </div>
        </div>

    </div>
</x-app-layout>
