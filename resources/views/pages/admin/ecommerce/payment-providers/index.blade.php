<x-app-layout>
  <div class="d-flex justify-content-between align-items-center mb-3">
    <div>
      <h4 class="mb-1">{{ __('Payment providers') }}</h4>
      <div class="text-muted small">{{ __('Enable and configure payment gateways.') }}</div>
    </div>
  </div>

  @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show mb-3" role="alert">
      {{ session('success') }}
      <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
  @endif

  <div class="card">
    <div class="card-body">
      <div class="table-responsive">
        <table class="table align-middle">
          <thead>
            <tr>
              <th>{{ __('Driver') }}</th>
              <th>{{ __('Status') }}</th>
              <th>{{ __('Config') }}</th>
              <th class="text-end">{{ __('Actions') }}</th>
            </tr>
          </thead>
          <tbody>
            @forelse($providers as $provider)
              <tr>
                <td class="fw-semibold text-capitalize">{{ $provider->driver }}</td>

                <td>
                  <span class="badge {{ $provider->enabled ? 'text-bg-success' : 'text-bg-secondary' }}">
                    {{ $provider->enabled ? __('Enabled') : __('Disabled') }}
                  </span>
                </td>

                <td class="text-muted small">
                  @if(is_array($provider->config) && count($provider->config))
                    @php
                      $filled = collect($provider->config)->filter(fn($v) => $v !== '' && $v !== null)->keys();
                    @endphp
                    @if($filled->count())
                      <span class="text-success">{{ $filled->count() }} {{ __('key(s) set') }}</span>
                    @else
                      <span class="text-warning">{{ __('Not configured') }}</span>
                    @endif
                  @else
                    <span class="text-muted">—</span>
                  @endif
                </td>

                <td class="text-end">
                  <div class="d-inline-flex gap-2">
                    @if($provider->driver !== 'cod')
                      <a class="btn btn-sm btn-outline-secondary"
                         href="{{ route('admin.ecommerce.payment-providers.edit', $provider) }}">
                        {{ __('Configure') }}
                      </a>
                    @endif

                    <form method="POST" action="{{ route('admin.ecommerce.payment-providers.update', $provider) }}">
                      @csrf
                      @method('PATCH')
                      <input type="hidden" name="enabled" value="{{ $provider->enabled ? 0 : 1 }}">
                      <button class="btn btn-sm {{ $provider->enabled ? 'btn-outline-danger' : 'btn-outline-primary' }}"
                              onclick="return confirm('{{ $provider->enabled ? __('Disable this provider?') : __('Enable this provider?') }}')">
                        {{ $provider->enabled ? __('Disable') : __('Enable') }}
                      </button>
                    </form>
                  </div>
                </td>
              </tr>
            @empty
              <tr>
                <td colspan="4" class="text-center text-muted py-4">{{ __('No payment providers found.') }}</td>
              </tr>
            @endforelse
          </tbody>
        </table>
      </div>
    </div>
  </div>
</x-app-layout>
