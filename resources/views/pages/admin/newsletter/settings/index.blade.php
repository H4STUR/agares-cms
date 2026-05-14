<x-app-layout>
  <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
    <h4 class="mb-0">{{ __('Newsletter integration') }}</h4>
    @can('test newsletter integration')
      <form method="POST" action="{{ route('admin.newsletter.settings.test-connection') }}">
        @csrf
        <button class="btn btn-sm btn-outline-info">
          <i class="bi bi-plug me-1"></i>{{ __('Test connection') }}
        </button>
      </form>
    @endcan
  </div>

  @include('pages.admin.newsletter.campaigns._safety_notice')

  @if($lastTestStatus)
    <div class="alert alert-{{ $lastTestStatus['ok'] ? 'success' : 'danger' }} small d-flex justify-content-between align-items-center">
      <div>
        <strong>{{ __('Last connection test') }}:</strong>
        {{ $lastTestStatus['message'] }}
      </div>
      <span class="text-muted small">{{ \Illuminate\Support\Carbon::parse($lastTestStatus['at'])->diffForHumans() }}</span>
    </div>
  @endif

  <form method="POST" action="{{ route('admin.newsletter.settings.update') }}">
    @csrf
    @method('PATCH')

    <div class="row g-3">
      <div class="col-lg-6">
        <div class="card">
          <div class="card-header fw-semibold">{{ __('Sending driver') }}</div>
          <div class="card-body">
            <div class="mb-3">
              <label class="form-label">{{ __('Driver') }}</label>
              <select name="newsletter_sending_driver" class="form-select"
                      {{ auth()->user()->can('edit newsletter settings') ? '' : 'disabled' }}>
                @foreach($drivers as $d)
                  <option value="{{ $d }}" @selected(old('newsletter_sending_driver', $values['newsletter_sending_driver']) === $d)>{{ $d }}</option>
                @endforeach
              </select>
              <div class="form-text">
                <strong>disabled</strong> — {{ __('test sending blocked.') }}<br>
                <strong>local</strong> — {{ __('local single test emails only; no bulk send.') }}<br>
                <strong>external_api</strong> — {{ __('delegate real campaigns to Agares SaaS; CMS still does not send bulk mail itself.') }}
              </div>
            </div>
          </div>
        </div>

        <div class="card mt-3">
          <div class="card-header fw-semibold">{{ __('External API (Agares SaaS)') }}</div>
          <div class="card-body">
            <div class="mb-3">
              <label class="form-label">{{ __('API URL') }}</label>
              <input type="url" class="form-control @error('newsletter_external_api_url') is-invalid @enderror"
                     name="newsletter_external_api_url"
                     value="{{ old('newsletter_external_api_url', $values['newsletter_external_api_url']) }}"
                     placeholder="https://api.agares.co.uk"
                     {{ auth()->user()->can('edit newsletter settings') ? '' : 'readonly' }}>
              @error('newsletter_external_api_url')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            <div class="mb-3">
              <label class="form-label">{{ __('Project / site identifier') }}</label>
              <input class="form-control"
                     name="newsletter_external_project_id"
                     value="{{ old('newsletter_external_project_id', $values['newsletter_external_project_id']) }}"
                     placeholder="e.g. cms-piesimordel"
                     {{ auth()->user()->can('edit newsletter settings') ? '' : 'readonly' }}>
              <div class="form-text">{{ __('Identifies this CMS install on the SaaS side.') }}</div>
            </div>

            <div class="mb-3">
              <label class="form-label d-flex justify-content-between align-items-center">
                <span>{{ __('API key') }}</span>
                @if($values['newsletter_external_api_key__has'])
                  <span class="text-muted small font-monospace">{{ $values['newsletter_external_api_key__hint'] }}</span>
                @endif
              </label>
              <input type="password" class="form-control"
                     name="newsletter_external_api_key"
                     value=""
                     autocomplete="new-password"
                     placeholder="{{ $values['newsletter_external_api_key__has'] ? __('Leave blank to keep current key.') : __('Paste a Bearer token from Agares SaaS.') }}"
                     {{ auth()->user()->can('edit newsletter settings') ? '' : 'readonly' }}>
              <div class="form-text">
                {{ __('Stored once. Submit "_clear" to remove. Never logged or echoed.') }}
              </div>
            </div>

            <div class="mb-0">
              <label class="form-label d-flex justify-content-between align-items-center">
                <span>{{ __('Webhook secret') }}</span>
                @if($values['newsletter_external_webhook_secret__has'])
                  <span class="text-muted small font-monospace">{{ $values['newsletter_external_webhook_secret__hint'] }}</span>
                @endif
              </label>
              <input type="password" class="form-control"
                     name="newsletter_external_webhook_secret"
                     value=""
                     autocomplete="new-password"
                     placeholder="{{ $values['newsletter_external_webhook_secret__has'] ? __('Leave blank to keep current secret.') : __('Optional. HMAC-SHA256 signs incoming webhook bodies.') }}"
                     {{ auth()->user()->can('edit newsletter settings') ? '' : 'readonly' }}>
              <div class="form-text">
                {{ __('Webhook URL') }}: <code>{{ route('newsletter.external.webhook') }}</code>
              </div>
            </div>
          </div>
        </div>
      </div>

      <div class="col-lg-6">
        <div class="card">
          <div class="card-header fw-semibold">{{ __('Default sender identity') }}</div>
          <div class="card-body">
            <div class="mb-3">
              <label class="form-label">{{ __('From name') }}</label>
              <input class="form-control" name="newsletter_from_name"
                     value="{{ old('newsletter_from_name', $values['newsletter_from_name']) }}"
                     placeholder="{{ __('(falls back to MAIL_FROM_NAME)') }}"
                     {{ auth()->user()->can('edit newsletter settings') ? '' : 'readonly' }}>
            </div>

            <div class="mb-3">
              <label class="form-label">{{ __('From email') }}</label>
              <input type="email" class="form-control @error('newsletter_from_email') is-invalid @enderror"
                     name="newsletter_from_email"
                     value="{{ old('newsletter_from_email', $values['newsletter_from_email']) }}"
                     placeholder="{{ __('(falls back to MAIL_FROM_ADDRESS)') }}"
                     {{ auth()->user()->can('edit newsletter settings') ? '' : 'readonly' }}>
              @error('newsletter_from_email')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            <div class="mb-0">
              <label class="form-label">{{ __('Reply-To') }}</label>
              <input type="email" class="form-control @error('newsletter_reply_to') is-invalid @enderror"
                     name="newsletter_reply_to"
                     value="{{ old('newsletter_reply_to', $values['newsletter_reply_to']) }}"
                     placeholder="{{ __('(no reply-to)') }}"
                     {{ auth()->user()->can('edit newsletter settings') ? '' : 'readonly' }}>
              @error('newsletter_reply_to')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
          </div>
        </div>

        <div class="card mt-3">
          <div class="card-body small">
            <p class="mb-2"><strong>{{ __('Why no queue?') }}</strong></p>
            <p class="mb-2">{{ __('AgaresCMS deploys to many shared-hosting clients without Supervisor or background workers. Bulk sending therefore lives outside the CMS.') }}</p>
            <ul class="mb-0">
              <li>{{ __('Local mode = single test emails only.') }}</li>
              <li>{{ __('External API mode = the SaaS owns the queue, retries, tracking, bounces.') }}</li>
              <li>{{ __('No cron / scheduler / queue worker setup is required inside the CMS.') }}</li>
            </ul>
          </div>
        </div>
      </div>
    </div>

    @can('edit newsletter settings')
      <div class="d-flex gap-2 mt-3">
        <button class="btn btn-primary">{{ __('Save settings') }}</button>
        <a class="btn btn-outline-secondary" href="{{ route('admin.newsletter.dashboard') }}">{{ __('Back') }}</a>
      </div>
    @endcan
  </form>
</x-app-layout>
