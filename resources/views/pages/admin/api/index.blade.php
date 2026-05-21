<x-app-layout>
    <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-3">
        <div>
            <h4 class="mb-1">API</h4>
            <div class="text-muted small">Base URL: <code>{{ $apiBase }}</code></div>
        </div>
    </div>

    {{-- Show plaintext key ONCE after creation --}}
    @if(session('new_api_key'))
        <div class="alert alert-warning d-flex align-items-start gap-3">
            <div>
                <div class="fw-bold mb-1">New API key (copy now - shown once)</div>
                <div class="d-flex flex-wrap gap-2 align-items-center">
                    <code class="p-2 bg-light border rounded" id="newApiKey">{{ session('new_api_key') }}</code>
                    <button type="button" class="btn btn-sm btn-dark" onclick="copyNewKey(this)">Copy</button>
                    <span class="text-success small ms-2 d-none" id="copyFeedback">Copied!</span>

                </div>
                <div class="text-muted small mt-2">If you lose it, revoke and create a new one.</div>
            </div>
        </div>

        <script>
            function copyNewKey() {
                const text = document.getElementById('newApiKey').innerText;
                navigator.clipboard.writeText(text);
            }
        </script>
    @endif

    <div class="row g-3">
        {{-- Create key --}}
        <div class="col-12 col-lg-5">
            <div class="card rounded-4 shadow-sm">
                <div class="card-body">
                    <h6 class="mb-3">Create API key</h6>

                    <form method="POST" action="{{ route('admin.api.keys.store') }}" class="d-grid gap-3">
                        @csrf

                        <div>
                            <label class="form-label">Name</label>
                            <input name="name" class="form-control" placeholder="e.g. React Frontend Server" required>
                        </div>

                        <div>
                            <label class="form-label">Abilities (scopes)</label>
                            <div class="d-grid gap-2">
                                @foreach($abilities as $key => $label)
                                    <label class="d-flex align-items-center gap-2">
                                        <input type="checkbox" name="abilities[]" value="{{ $key }}">
                                        <span><code>{{ $key }}</code> - <span class="text-muted">{{ $label }}</span></span>
                                    </label>
                                @endforeach
                            </div>
                        </div>

                        <div>
                            <label class="form-label">Expires at (optional)</label>
                            <input type="date" name="expires_at" class="form-control">
                        </div>

                        <button class="btn btn-primary">Create key</button>
                    </form>
                </div>
            </div>
        </div>

        {{-- Keys list --}}
        <div class="col-12 col-lg-7">
            <div class="card rounded-4 shadow-sm">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h6 class="mb-0">API keys</h6>
                        <span class="text-muted small">Total: {{ $keys->total() }}</span>
                    </div>

                    <div class="table-responsive">
                        <table class="table align-middle">
                            <thead>
                                <tr>
                                    <th>Name</th>
                                    <th>Scopes</th>
                                    <th>Last used</th>
                                    <th>Expires</th>
                                    <th>Status</th>
                                    <th class="text-end">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                            @forelse($keys as $k)
                                <tr>
                                    <td class="fw-semibold">{{ $k->name }}</td>
                                    <td>
                                        @php($scopes = $k->abilities ?? [])
                                        @if(empty($scopes))
                                            <span class="text-muted small">-</span>
                                        @else
                                            <div class="d-flex flex-wrap gap-1">
                                                @foreach($scopes as $s)
                                                    <span class="badge bg-secondary">{{ $s }}</span>
                                                @endforeach
                                            </div>
                                        @endif
                                    </td>
                                    <td class="text-muted small">
                                        {{ $k->last_used_at ? $k->last_used_at->diffForHumans() : '-' }}
                                    </td>
                                    <td class="text-muted small">
                                        {{ $k->expires_at ? $k->expires_at->toDateString() : '-' }}
                                    </td>
                                    <td>
                                        @if($k->revoked_at)
                                            <span class="badge bg-danger">Revoked</span>
                                        @else
                                            <span class="badge bg-success">Active</span>
                                        @endif
                                    </td>
                                    <td class="text-end">
                                        @if(!$k->revoked_at)
                                            <form method="POST" action="{{ route('admin.api.keys.revoke', $k->id) }}" class="d-inline">
                                                @csrf
                                                <button class="btn btn-sm btn-outline-danger"
                                                    onclick="return confirm('Revoke this key? Apps using it will stop working.')">
                                                    Revoke
                                                </button>
                                            </form>
                                        @else
                                            <span class="text-muted small">-</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-muted">No keys yet.</td>
                                </tr>
                            @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-3">
                        {{ $keys->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>

@push('scripts')

<script>
    function copyNewKey(btn) {
        const text = document.getElementById('newApiKey').innerText;

        navigator.clipboard.writeText(text).then(() => {
            const feedback = document.getElementById('copyFeedback');

            feedback.classList.remove('d-none');

            btn.innerText = 'Copied';
            btn.classList.remove('btn-dark');
            btn.classList.add('btn-success');

            setTimeout(() => {
                feedback.classList.add('d-none');
                btn.innerText = 'Copy';
                btn.classList.remove('btn-success');
                btn.classList.add('btn-dark');
            }, 1500);
        });
    }
</script>

    
@endpush
</x-app-layout>
