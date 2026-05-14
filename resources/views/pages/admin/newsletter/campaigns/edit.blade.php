<x-app-layout>
  <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
    <h4 class="mb-0">{{ __('Edit campaign') }}{{ $campaign->title ? ': ' . $campaign->title : '' }}</h4>
    <div class="d-flex gap-2 flex-wrap">
      @can('preview newsletter campaigns')
        <a class="btn btn-sm btn-outline-secondary" href="{{ route('admin.newsletter.campaigns.preview', $campaign) }}">{{ __('Preview') }}</a>
      @endcan
      @can('send test newsletter campaigns')
        <a class="btn btn-sm btn-outline-warning" href="{{ route('admin.newsletter.campaigns.test.form', $campaign) }}">{{ __('Send test') }}</a>
      @endcan
      @include('pages.admin.newsletter.campaigns._external_actions', ['campaign' => $campaign])
      @can('manage newsletter campaigns')
        @if(in_array($campaign->status, [\App\Models\Newsletter\NewsletterCampaign::STATUS_DRAFT, \App\Models\Newsletter\NewsletterCampaign::STATUS_READY, \App\Models\Newsletter\NewsletterCampaign::STATUS_TEST_SENT, \App\Models\Newsletter\NewsletterCampaign::STATUS_EXTERNAL_FAILED], true))
          <form method="POST" action="{{ route('admin.newsletter.campaigns.cancel', $campaign) }}"
                onsubmit="return confirm('{{ __('Cancel this campaign?') }}')">
            @csrf
            @method('PATCH')
            <button class="btn btn-sm btn-outline-danger">{{ __('Cancel campaign') }}</button>
          </form>
        @endif
      @endcan
    </div>
  </div>

  @if($campaign->isLocked())
    <div class="alert alert-info small">
      <i class="bi bi-lock-fill me-1"></i>
      {{ __('This campaign is owned by the external sender. Editing is disabled. Use "Sync status" to refresh counters.') }}
    </div>
  @endif

  @if($campaign->external_last_error)
    <div class="alert alert-danger small">
      <strong>{{ __('Last external error') }}:</strong> {{ $campaign->external_last_error }}
    </div>
  @endif

  @include('pages.admin.newsletter.campaigns._safety_notice')

  <form method="POST" action="{{ route('admin.newsletter.campaigns.update', $campaign) }}">
    @csrf
    @method('PATCH')
    @include('pages.admin.newsletter.campaigns._form', ['campaign' => $campaign])

    <div class="d-flex gap-2 mt-3">
      <button class="btn btn-primary">{{ __('Save') }}</button>
      <a class="btn btn-outline-secondary" href="{{ route('admin.newsletter.campaigns.index') }}">{{ __('Back') }}</a>
    </div>
  </form>
</x-app-layout>
