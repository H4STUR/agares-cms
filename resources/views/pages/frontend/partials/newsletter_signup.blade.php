@if(($settings['enable_newsletter'] ?? false))
  @php
    $consentText = $consentText ?? __('I agree to receive the newsletter and accept the privacy policy.');
  @endphp

  <form method="POST" action="{{ route('newsletter.subscribe') }}" class="newsletter-signup">
    @csrf
    <input type="hidden" name="consent_text" value="{{ $consentText }}">

    @if(session('success'))
      <div class="alert alert-success small">{{ session('success') }}</div>
    @endif

    @if($errors->any())
      <div class="alert alert-danger small">
        <ul class="mb-0 ps-3">
          @foreach($errors->all() as $err)
            <li>{{ $err }}</li>
          @endforeach
        </ul>
      </div>
    @endif

    <div class="form-group" style="margin-bottom: var(--space-md, 12px);">
      <label class="form-label" for="newsletter-email">{{ __('Email') }} *</label>
      <input type="email" id="newsletter-email" name="email" class="form-input form-control"
             value="{{ old('email') }}" required>
    </div>

    <div class="form-group" style="margin-bottom: var(--space-md, 12px);">
      <label class="form-label" for="newsletter-name">{{ __('Name') }}</label>
      <input type="text" id="newsletter-name" name="name" class="form-input form-control"
             value="{{ old('name') }}">
    </div>

    <div class="form-check" style="margin-bottom: var(--space-md, 12px);">
      <input class="form-check-input" type="checkbox" id="newsletter-consent" name="consent" value="1" required>
      <label class="form-check-label small" for="newsletter-consent">
        {{ $consentText }}
      </label>
    </div>

    <button type="submit" class="btn btn-primary">{{ __('Subscribe') }}</button>
  </form>
@endif
