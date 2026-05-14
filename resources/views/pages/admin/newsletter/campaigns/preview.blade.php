<x-app-layout>
  <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
    <h4 class="mb-0">{{ __('Preview campaign') }}{{ $campaign->title ? ': ' . $campaign->title : '' }}</h4>
    <div class="d-flex gap-2 flex-wrap">
      @can('send test newsletter campaigns')
        <a class="btn btn-sm btn-outline-warning" href="{{ route('admin.newsletter.campaigns.test.form', $campaign) }}">{{ __('Send test email') }}</a>
      @endcan
      @include('pages.admin.newsletter.campaigns._external_actions', ['campaign' => $campaign, 'driverKey' => $driverKey])
      @can('view newsletter campaigns')
        <a class="btn btn-sm btn-outline-primary" href="{{ route('admin.newsletter.campaigns.edit', $campaign) }}">{{ __('Edit') }}</a>
      @endcan
      <a class="btn btn-sm btn-outline-secondary" href="{{ route('admin.newsletter.campaigns.index') }}">{{ __('Back') }}</a>
    </div>
  </div>

  <div class="alert alert-warning small">
    <i class="bi bi-info-circle me-1"></i>
    {{ __('This is a local preview only. Nothing is sent.') }}
  </div>

  <div class="row g-3">
    <div class="col-lg-4">
      <div class="card mb-3">
        <div class="card-header fw-semibold">{{ __('Headers') }}</div>
        <div class="card-body small">
          <p class="mb-1"><strong>{{ __('Status') }}:</strong> {{ $campaign->status }}</p>
          <p class="mb-1"><strong>{{ __('Template') }}:</strong> {{ optional($campaign->template)->name ?: '—' }}</p>
          <p class="mb-1"><strong>{{ __('Lists') }}:</strong>
            @forelse($campaign->lists as $l)
              <span class="badge text-bg-light border">{{ $l->name }}</span>
            @empty
              <span class="text-danger">{{ __('none selected — required for delegation') }}</span>
            @endforelse
          </p>
          <hr>
          <p class="mb-1"><strong>{{ __('From name') }}:</strong> <span class="text-muted">{{ $campaign->from_name ?: __('(default)') }}</span></p>
          <p class="mb-1"><strong>{{ __('From email') }}:</strong> <span class="text-muted">{{ $campaign->from_email ?: __('(default)') }}</span></p>
          <p class="mb-1"><strong>{{ __('Reply-To') }}:</strong> <span class="text-muted">{{ $campaign->reply_to ?: '—' }}</span></p>
          @if($campaign->test_sent_at)
            <hr>
            <p class="mb-0"><strong>{{ __('Last test sent') }}:</strong> <span class="text-muted">{{ $campaign->test_sent_at->format('d M Y H:i') }}</span></p>
          @endif
        </div>
      </div>

      <div class="card mb-3">
        <div class="card-header fw-semibold">{{ __('External delegation') }}</div>
        <div class="card-body small">
          <p class="mb-1"><strong>{{ __('Driver') }}:</strong> <code>{{ $driverKey }}</code></p>
          @if($campaign->external_campaign_id)
            <p class="mb-1"><strong>{{ __('External ID') }}:</strong> <code class="text-break">{{ $campaign->external_campaign_id }}</code></p>
            <p class="mb-1"><strong>{{ __('External status') }}:</strong> <span class="badge text-bg-light border">{{ $campaign->external_status ?: '—' }}</span></p>
            <p class="mb-1"><strong>{{ __('Sent') }}:</strong> {{ $campaign->external_sent_count ?? '—' }} &nbsp; <strong>{{ __('Failed') }}:</strong> {{ $campaign->external_failed_count ?? '—' }}</p>
            <p class="mb-1"><strong>{{ __('Opens') }}:</strong> {{ $campaign->external_open_count ?? '—' }} &nbsp; <strong>{{ __('Clicks') }}:</strong> {{ $campaign->external_click_count ?? '—' }}</p>
            <p class="mb-1"><strong>{{ __('Delegated at') }}:</strong> {{ optional($campaign->delegated_at)->format('d M Y H:i') ?: '—' }}</p>
            <p class="mb-1"><strong>{{ __('Last synced') }}:</strong> {{ optional($campaign->external_last_synced_at)->format('d M Y H:i') ?: '—' }}</p>
            @if($campaign->external_last_error)
              <p class="mb-0 text-danger"><strong>{{ __('Last error') }}:</strong> {{ $campaign->external_last_error }}</p>
            @endif
          @else
            <p class="text-muted small mb-0">{{ __('Not delegated yet.') }}</p>
            @if($driverKey !== 'external_api')
              <p class="text-warning small mt-2 mb-0">
                {{ __('External API driver is not active. Configure it in Newsletter Settings to enable delegation.') }}
              </p>
            @endif
          @endif
        </div>
      </div>
    </div>

    <div class="col-lg-8">
      <div class="card">
        <div class="card-header">
          <strong>{{ __('Subject') }}:</strong> {{ $campaign->subject }}
        </div>
        <div class="card-body" style="background:#fff;color:#212529;">
          {!! safe_html((string) $campaign->body) !!}
          <hr>
          <p class="text-muted small mb-0">
            <em>{{ __('Unsubscribe link will be appended here automatically when delegated to the external sender (Phase 3).') }}</em>
          </p>
        </div>
      </div>
    </div>
  </div>
</x-app-layout>
