@extends('pages.frontend.base')

@section('content')
  <section class="section-sm" style="background: var(--color-bg-secondary);">
    <div class="container text-center">
      <h1>{{ __('Newsletter') }}</h1>
    </div>
  </section>

  <section>
    <div class="container" style="max-width: 640px;">
      <div class="card" style="padding: var(--space-2xl, 24px); text-align: center;">
        @if($success)
          <h2 style="margin-top: 0;">{{ __('You have been unsubscribed') }}</h2>
          <p style="color: var(--color-text-secondary);">
            {{ $message }}
            @if($subscriber && $subscriber->email)
              <br><strong>{{ $subscriber->email }}</strong>
            @endif
          </p>
          <p style="color: var(--color-text-tertiary); font-size: var(--text-sm, 0.9rem);">
            {{ __('We are sorry to see you go. You can resubscribe at any time from our website.') }}
          </p>
        @else
          <h2 style="margin-top: 0;">{{ __('Invalid link') }}</h2>
          <p style="color: var(--color-text-secondary);">{{ $message }}</p>
        @endif

        <p style="margin-top: var(--space-xl, 24px);">
          <a class="btn btn-primary" href="{{ url('/') }}">{{ __('Back to home') }}</a>
        </p>
      </div>
    </div>
  </section>
@endsection
