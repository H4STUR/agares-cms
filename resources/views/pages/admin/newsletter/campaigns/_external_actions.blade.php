@php
  /** @var \App\Models\Newsletter\NewsletterCampaign $campaign */
  $driver = $driverKey ?? \App\Models\Setting::str('newsletter_sending_driver', 'disabled');
  $isExt  = $driver === 'external_api';
@endphp

@can('delegate newsletter campaigns')
  @if($isExt && $campaign->isDelegatable())
    <form method="POST" action="{{ route('admin.newsletter.campaigns.delegate', $campaign) }}" class="d-inline"
          onsubmit="return confirm('{{ __('Delegate this campaign to the external sender? Active subscribers in the selected lists will be sent for delivery.') }}')">
      @csrf
      <button class="btn btn-sm btn-primary">
        <i class="bi bi-cloud-upload me-1"></i>{{ __('Delegate to SaaS') }}
      </button>
    </form>
  @elseif($campaign->isDelegatable() && !$isExt)
    <button class="btn btn-sm btn-outline-secondary" disabled
            title="{{ __('Set the newsletter driver to external_api in Newsletter Settings to delegate.') }}">
      <i class="bi bi-cloud-upload me-1"></i>{{ __('Delegate (driver disabled)') }}
    </button>
  @endif
@endcan

@can('sync newsletter campaigns')
  @if($campaign->hasExternalReference())
    <form method="POST" action="{{ route('admin.newsletter.campaigns.sync', $campaign) }}" class="d-inline">
      @csrf
      <button class="btn btn-sm btn-outline-info">
        <i class="bi bi-arrow-clockwise me-1"></i>{{ __('Sync status') }}
      </button>
    </form>
  @endif
@endcan
