<x-app-layout>
  <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
    <h4 class="mb-0">{{ __('Send test email') }}</h4>
    <a class="btn btn-sm btn-outline-secondary" href="{{ route('admin.newsletter.campaigns.edit', $campaign) }}">{{ __('Back to campaign') }}</a>
  </div>

  @include('pages.admin.newsletter.campaigns._safety_notice')

  <div class="row g-3">
    <div class="col-lg-6">
      <div class="card">
        <div class="card-header fw-semibold">
          {{ __('Test send') }}
        </div>
        <div class="card-body">
          <p class="small text-muted">
            {{ __('Sends a single email synchronously to the recipient below. The email is clearly labelled as a test/preview. No subscriber list is touched.') }}
          </p>

          <div class="mb-2 small">
            <strong>{{ __('Driver') }}:</strong>
            @if($sender->isEnabled())
              <span class="badge text-bg-success">{{ $driverKey }}</span>
            @else
              <span class="badge text-bg-secondary">{{ $driverKey }}</span>
              <span class="text-danger">— {{ __('disabled') }}</span>
            @endif
          </div>

          @if(!$sender->isEnabled())
            <div class="alert alert-warning small mb-3">
              {{ __('The newsletter sender is currently disabled. Visit Settings and set "newsletter_sending_driver" to "local" to enable test emails.') }}
            </div>
          @endif

          <form method="POST" action="{{ route('admin.newsletter.campaigns.test.send', $campaign) }}">
            @csrf
            <div class="mb-3">
              <label class="form-label">{{ __('Recipient email') }}</label>
              <input type="email" class="form-control @error('recipient') is-invalid @enderror"
                     name="recipient"
                     value="{{ old('recipient', $defaultTo) }}"
                     required>
              @error('recipient')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            <button class="btn btn-primary" {{ $sender->isEnabled() ? '' : 'disabled' }}>
              <i class="bi bi-envelope-paper me-1"></i>{{ __('Send test') }}
            </button>
          </form>
        </div>
      </div>
    </div>

    <div class="col-lg-6">
      <div class="card">
        <div class="card-header fw-semibold">{{ __('Campaign summary') }}</div>
        <div class="card-body small">
          <p class="mb-1"><strong>{{ __('Title') }}:</strong> {{ $campaign->title ?: '—' }}</p>
          <p class="mb-1"><strong>{{ __('Subject') }}:</strong> {{ $campaign->subject }}</p>
          <p class="mb-1"><strong>{{ __('Status') }}:</strong> {{ $campaign->status }}</p>
          <p class="mb-0"><strong>{{ __('Body length') }}:</strong> {{ number_format(strlen((string) $campaign->body)) }} {{ __('chars') }}</p>
        </div>
      </div>
    </div>
  </div>
</x-app-layout>
