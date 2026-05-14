<x-app-layout>
  <h4 class="mb-3">{{ __('Edit subscriber') }}: {{ $subscriber->email }}</h4>

  <div class="row g-3">
    <div class="col-lg-8">
      <div class="card">
        <div class="card-body">
          <form method="POST" action="{{ route('admin.newsletter.subscribers.update', $subscriber) }}">
            @csrf
            @method('PATCH')

            <div class="row g-3">
              <div class="col-md-6">
                <label class="form-label">{{ __('Email') }}</label>
                <input type="email" class="form-control @error('email') is-invalid @enderror"
                       name="email" value="{{ old('email', $subscriber->email) }}" required>
                @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
              </div>

              <div class="col-md-6">
                <label class="form-label">{{ __('Name') }}</label>
                <input type="text" class="form-control" name="name" value="{{ old('name', $subscriber->name) }}">
              </div>

              <div class="col-md-6">
                <label class="form-label">{{ __('Status') }}</label>
                <select name="status" class="form-select" required>
                  @foreach($statuses as $st)
                    <option value="{{ $st }}" @selected(old('status', $subscriber->status) === $st)>{{ $st }}</option>
                  @endforeach
                </select>
              </div>

              <div class="col-md-6">
                <label class="form-label">{{ __('Lists') }}</label>
                @php $assigned = old('lists', $subscriber->lists->pluck('id')->all()); @endphp
                <select name="lists[]" class="form-select" multiple size="5">
                  @foreach($lists as $l)
                    <option value="{{ $l->id }}" @selected(in_array($l->id, (array) $assigned))>
                      {{ $l->name }}@if($l->is_default) ({{ __('default') }})@endif
                    </option>
                  @endforeach
                </select>
              </div>
            </div>

            <div class="d-flex gap-2 mt-3">
              <button class="btn btn-primary">{{ __('Save') }}</button>
              <a class="btn btn-outline-secondary" href="{{ route('admin.newsletter.subscribers.index') }}">{{ __('Back') }}</a>
            </div>
          </form>
        </div>
      </div>
    </div>

    <div class="col-lg-4">
      <div class="card">
        <div class="card-header fw-semibold">{{ __('Consent / Source') }}</div>
        <div class="card-body small">
          <p class="mb-1"><strong>{{ __('Source') }}:</strong> <span class="text-muted">{{ $subscriber->source ?: '—' }}</span></p>
          <p class="mb-1"><strong>{{ __('Subscribed at') }}:</strong> <span class="text-muted">{{ optional($subscriber->subscribed_at)->format('d M Y H:i') ?: '—' }}</span></p>
          <p class="mb-1"><strong>{{ __('Confirmed at') }}:</strong> <span class="text-muted">{{ optional($subscriber->confirmed_at)->format('d M Y H:i') ?: '—' }}</span></p>
          <p class="mb-1"><strong>{{ __('Unsubscribed at') }}:</strong> <span class="text-muted">{{ optional($subscriber->unsubscribed_at)->format('d M Y H:i') ?: '—' }}</span></p>
          <hr>
          <p class="mb-1"><strong>{{ __('Consent IP') }}:</strong> <span class="text-muted font-monospace">{{ $subscriber->consent_ip ?: '—' }}</span></p>
          <p class="mb-1"><strong>{{ __('User agent') }}:</strong></p>
          <p class="text-muted small text-break mb-2">{{ $subscriber->consent_user_agent ?: '—' }}</p>
          <p class="mb-1"><strong>{{ __('Consent text') }}:</strong></p>
          <p class="text-muted small text-break">{{ $subscriber->consent_text ?: '—' }}</p>
        </div>
      </div>
    </div>
  </div>
</x-app-layout>
