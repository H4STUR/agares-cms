<x-app-layout>
  <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
    <h4 class="mb-0">{{ __('Newsletter campaigns') }}</h4>
    @can('manage newsletter campaigns')
      <a class="btn btn-primary" href="{{ route('admin.newsletter.campaigns.create') }}">
        <i class="bi bi-plus-lg me-1"></i>{{ __('Add campaign') }}
      </a>
    @endcan
  </div>

  @include('pages.admin.newsletter.campaigns._safety_notice')

  <div class="card mb-3">
    <div class="card-body d-flex flex-wrap gap-3 align-items-center">
      <form method="GET" action="{{ route('admin.newsletter.campaigns.index') }}" class="row g-2 flex-grow-1 m-0">
        <div class="col-md-3">
          <select name="status" class="form-select" onchange="this.form.submit()">
            <option value="">{{ __('All statuses') }}</option>
            @foreach($statuses as $st)
              <option value="{{ $st }}" @selected($status === $st)>{{ $st }}</option>
            @endforeach
          </select>
        </div>
        @if($status)
          <div class="col-md-2">
            <a class="btn btn-outline-secondary w-100" href="{{ route('admin.newsletter.campaigns.index') }}">{{ __('Reset') }}</a>
          </div>
        @endif
      </form>

      <div class="small text-muted ms-auto">
        {{ __('Active driver') }}:
        @if($driverKey === 'external_api')
          <span class="badge text-bg-primary">{{ $driverKey }}</span>
        @elseif($driverKey === 'local')
          <span class="badge text-bg-success">{{ $driverKey }}</span>
        @else
          <span class="badge text-bg-secondary">{{ $driverKey }}</span>
        @endif
      </div>
    </div>
  </div>

  <div class="card">
    <div class="card-body">
      <div class="table-responsive">
        <table class="table align-middle">
          <thead>
            <tr>
              <th>{{ __('Title') }}</th>
              <th>{{ __('Subject') }}</th>
              <th>{{ __('Template') }}</th>
              <th>{{ __('Status') }}</th>
              <th>{{ __('External') }}</th>
              <th>{{ __('Created by') }}</th>
              <th>{{ __('Updated') }}</th>
              <th class="text-end">{{ __('Actions') }}</th>
            </tr>
          </thead>
          <tbody>
            @forelse($campaigns as $c)
              @php
                $badge = match($c->status) {
                  'draft'     => 'text-bg-secondary',
                  'ready'     => 'text-bg-info',
                  'test_sent' => 'text-bg-warning',
                  'delegated' => 'text-bg-primary',
                  'cancelled' => 'text-bg-danger',
                  default     => 'text-bg-secondary',
                };
              @endphp
              <tr>
                <td class="fw-semibold">{{ $c->title ?: '—' }}</td>
                <td class="text-muted small">{{ \Illuminate\Support\Str::limit($c->subject, 60) }}</td>
                <td class="text-muted small">{{ optional($c->template)->name ?: '—' }}</td>
                <td><span class="badge {{ $badge }}">{{ $c->status }}</span></td>
                <td class="text-muted small">
                  @if($c->external_campaign_id)
                    <div class="font-monospace text-truncate" style="max-width:160px;" title="{{ $c->external_campaign_id }}">
                      {{ $c->external_campaign_id }}
                    </div>
                    @if($c->external_status)
                      <div class="badge text-bg-light border">{{ $c->external_status }}</div>
                    @endif
                    @if($c->external_sent_count !== null)
                      <div>{{ $c->external_sent_count }}@if($c->external_failed_count) / <span class="text-danger">{{ $c->external_failed_count }}</span>@endif</div>
                    @endif
                  @else
                    —
                  @endif
                </td>
                <td class="text-muted small">{{ optional($c->creator)->name ?: '—' }}</td>
                <td class="text-muted small">{{ optional($c->updated_at)->format('d M Y H:i') }}</td>
                <td class="text-end">
                  @can('preview newsletter campaigns')
                    <a class="btn btn-sm btn-outline-secondary" href="{{ route('admin.newsletter.campaigns.preview', $c) }}">{{ __('Preview') }}</a>
                  @endcan
                  @can('send test newsletter campaigns')
                    <a class="btn btn-sm btn-outline-warning" href="{{ route('admin.newsletter.campaigns.test.form', $c) }}">{{ __('Test') }}</a>
                  @endcan
                  @include('pages.admin.newsletter.campaigns._external_actions', ['campaign' => $c, 'driverKey' => $driverKey])
                  @can('view newsletter campaigns')
                    <a class="btn btn-sm btn-outline-primary" href="{{ route('admin.newsletter.campaigns.edit', $c) }}">{{ __('Edit') }}</a>
                  @endcan
                  @can('manage newsletter campaigns')
                    <form class="d-inline" method="POST" action="{{ route('admin.newsletter.campaigns.destroy', $c) }}"
                          onsubmit="return confirm('{{ __('Delete campaign?') }}')">
                      @csrf
                      @method('DELETE')
                      <button class="btn btn-sm btn-outline-danger">{{ __('Delete') }}</button>
                    </form>
                  @endcan
                </td>
              </tr>
            @empty
              <tr><td colspan="8" class="text-center text-muted py-4">{{ __('No campaigns yet.') }}</td></tr>
            @endforelse
          </tbody>
        </table>
      </div>
    </div>
  </div>

  @if($campaigns->hasPages())
    <div class="mt-3">{{ $campaigns->links() }}</div>
  @endif
</x-app-layout>
