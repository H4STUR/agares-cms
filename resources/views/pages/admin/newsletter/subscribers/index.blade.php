<x-app-layout>
  <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
    <h4 class="mb-0">{{ __('Subscribers') }}</h4>
    @can('manage newsletter subscribers')
      <a class="btn btn-primary" href="{{ route('admin.newsletter.subscribers.create') }}">
        <i class="bi bi-plus-lg me-1"></i>{{ __('Add subscriber') }}
      </a>
    @endcan
  </div>

  <div class="card mb-3">
    <div class="card-body">
      <form method="GET" action="{{ route('admin.newsletter.subscribers.index') }}" class="row g-2">
        <div class="col-md-6">
          <input type="text" name="q" class="form-control" placeholder="{{ __('Search by email or name...') }}" value="{{ $q }}">
        </div>
        <div class="col-md-3">
          <select name="status" class="form-select">
            <option value="">{{ __('All statuses') }}</option>
            @foreach($statuses as $st)
              <option value="{{ $st }}" @selected($status === $st)>{{ $st }}</option>
            @endforeach
          </select>
        </div>
        <div class="col-md-3 d-flex gap-2">
          <button class="btn btn-outline-primary flex-grow-1">{{ __('Filter') }}</button>
          @if($q || $status)
            <a class="btn btn-outline-secondary" href="{{ route('admin.newsletter.subscribers.index') }}">{{ __('Reset') }}</a>
          @endif
        </div>
      </form>
    </div>
  </div>

  <div class="card">
    <div class="card-body">
      <div class="table-responsive">
        <table class="table align-middle">
          <thead>
            <tr>
              <th>{{ __('Email') }}</th>
              <th>{{ __('Name') }}</th>
              <th>{{ __('Status') }}</th>
              <th>{{ __('Lists') }}</th>
              <th>{{ __('Source') }}</th>
              <th>{{ __('Subscribed at') }}</th>
              <th class="text-end">{{ __('Actions') }}</th>
            </tr>
          </thead>
          <tbody>
            @forelse($subscribers as $s)
              @php
                $badge = match($s->status) {
                  'active'       => 'text-bg-success',
                  'pending'      => 'text-bg-warning',
                  'unsubscribed' => 'text-bg-secondary',
                  'bounced','complained' => 'text-bg-danger',
                  default        => 'text-bg-secondary',
                };
              @endphp
              <tr>
                <td class="fw-semibold">{{ $s->email }}</td>
                <td class="text-muted">{{ $s->name }}</td>
                <td><span class="badge {{ $badge }}">{{ $s->status }}</span></td>
                <td>
                  @forelse($s->lists as $l)
                    <span class="badge text-bg-light border">{{ $l->name }}</span>
                  @empty
                    <span class="text-muted small">—</span>
                  @endforelse
                </td>
                <td class="text-muted small">{{ $s->source }}</td>
                <td class="text-muted small">{{ optional($s->subscribed_at)->format('d M Y') }}</td>
                <td class="text-end">
                  @can('view newsletter subscribers')
                    <a class="btn btn-sm btn-outline-primary" href="{{ route('admin.newsletter.subscribers.edit', $s) }}">{{ __('Edit') }}</a>
                  @endcan
                  @can('manage newsletter subscribers')
                    <form class="d-inline" method="POST" action="{{ route('admin.newsletter.subscribers.destroy', $s) }}"
                          onsubmit="return confirm('{{ __('Delete subscriber?') }}')">
                      @csrf
                      @method('DELETE')
                      <button class="btn btn-sm btn-outline-danger">{{ __('Delete') }}</button>
                    </form>
                  @endcan
                </td>
              </tr>
            @empty
              <tr><td colspan="7" class="text-center text-muted py-4">{{ __('No subscribers yet.') }}</td></tr>
            @endforelse
          </tbody>
        </table>
      </div>
    </div>
  </div>

  @if($subscribers->hasPages())
    <div class="mt-3">{{ $subscribers->links() }}</div>
  @endif
</x-app-layout>
