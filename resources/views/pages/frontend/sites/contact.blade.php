@extends('pages.frontend.base')

@php
  // Wire to existing CMS Form module — find the first form named like "contact"
  // (case-insensitive), falling back to the first form, falling back to null.
  $contactForm = null;
  try {
      if (class_exists(\App\Models\Form::class)) {
          $contactForm = \App\Models\Form::with('fields')
              ->whereRaw('LOWER(name) LIKE ?', ['%contact%'])
              ->first()
              ?? \App\Models\Form::with('fields')->first();
      }
  } catch (\Throwable $e) {
      $contactForm = null;
  }
@endphp

@push('styles')
<style>
  /* ============ Contact page extras ============ */

  .contact-grid {
    display: grid;
    grid-template-columns: 1.4fr 1fr;
    gap: var(--space-2xl);
    align-items: stretch;
  }
  @media (max-width: 1000px) { .contact-grid { grid-template-columns: 1fr; } }

  .contact-card {
    padding: clamp(1.75rem, 3vw, 2.5rem);
    background: linear-gradient(180deg, rgba(255, 255, 255, 0.04), rgba(255, 255, 255, 0.015));
    border: 1px solid var(--color-border);
    border-radius: var(--radius-2xl);
    position: relative;
    overflow: hidden;
  }
  .contact-card::before {
    content: '';
    position: absolute;
    inset: 0 0 auto 0;
    height: 2px;
    background: var(--color-accent-gradient);
  }

  .contact-form-wrap .form-group { margin-bottom: var(--space-md); }
  .contact-form-wrap .form-label,
  .contact-form-wrap label.form-label {
    display: block;
    margin-bottom: 0.4rem;
    font-family: var(--font-mono);
    font-size: 0.7rem;
    font-weight: 500;
    color: var(--color-text-secondary);
    text-transform: uppercase;
    letter-spacing: 0.08em;
  }
  .contact-form-wrap input.form-input,
  .contact-form-wrap input.form-control,
  .contact-form-wrap textarea.form-input,
  .contact-form-wrap textarea.form-control,
  .contact-form-wrap select.form-input,
  .contact-form-wrap select.form-control {
    width: 100%;
    padding: 0.85rem 1rem;
    background: rgba(7, 8, 13, 0.55);
    border: 1px solid var(--color-border-hover);
    border-radius: var(--radius-md);
    color: var(--color-text-primary);
    font-family: var(--font-sans);
    font-size: 0.95rem;
    transition: all var(--transition-base);
  }
  .contact-form-wrap input.form-input:focus,
  .contact-form-wrap input.form-control:focus,
  .contact-form-wrap textarea.form-input:focus,
  .contact-form-wrap textarea.form-control:focus {
    outline: none;
    border-color: var(--color-accent-primary);
    background: rgba(7, 8, 13, 0.7);
    box-shadow: 0 0 0 4px rgba(139, 92, 246, 0.14);
  }
  .contact-form-wrap textarea { min-height: 150px; resize: vertical; }
  .contact-form-wrap .form-text { font-size: 0.75rem; color: var(--color-text-tertiary); margin-top: 0.3rem; }
  .contact-form-wrap .invalid-feedback { color: #fca5a5; font-size: 0.78rem; margin-top: 0.3rem; }
  .contact-form-wrap .is-invalid { border-color: rgba(248, 113, 113, 0.5) !important; }
  .contact-form-wrap .is-valid    { border-color: rgba(52, 211, 153, 0.4) !important; }

  .contact-form-wrap .alert {
    padding: 0.85rem 1rem;
    border-radius: var(--radius-md);
    margin-bottom: var(--space-md);
    font-size: 0.88rem;
    border: 1px solid;
  }
  .contact-form-wrap .alert-success { background: rgba(52, 211, 153, 0.1); border-color: rgba(52, 211, 153, 0.3); color: #6ee7b7; }
  .contact-form-wrap .alert-danger  { background: rgba(248, 113, 113, 0.08); border-color: rgba(248, 113, 113, 0.3); color: #fca5a5; }
  .contact-form-wrap .alert-warning { background: rgba(251, 191, 36, 0.08); border-color: rgba(251, 191, 36, 0.3); color: #fde68a; }

  .contact-form-wrap button[type="submit"] {
    margin-top: var(--space-md);
    padding: 0.95rem 1.6rem;
    font-size: var(--text-base);
    font-weight: var(--font-weight-semibold);
    background: var(--color-accent-gradient);
    color: white;
    border: none;
    border-radius: var(--radius-md);
    cursor: pointer;
    transition: all var(--transition-base);
    box-shadow: 0 12px 32px -10px rgba(139, 92, 246, 0.55);
  }
  .contact-form-wrap button[type="submit"]:hover { transform: translateY(-2px); box-shadow: 0 18px 44px -10px rgba(139, 92, 246, 0.7); }

  .contact-channels { display: flex; flex-direction: column; gap: var(--space-md); }
  .contact-channel {
    display: flex; align-items: flex-start; gap: var(--space-md);
    padding: var(--space-lg);
    background: var(--color-surface);
    border: 1px solid var(--color-border);
    border-radius: var(--radius-lg);
    text-decoration: none;
    transition: all var(--transition-base);
  }
  .contact-channel:hover { border-color: var(--color-border-hover); transform: translateY(-2px); background: var(--color-surface-hover); }
  .contact-channel-ico {
    flex-shrink: 0;
    width: 44px; height: 44px;
    display: inline-flex; align-items: center; justify-content: center;
    background: rgba(139, 92, 246, 0.12);
    border: 1px solid rgba(139, 92, 246, 0.25);
    border-radius: var(--radius-md);
    color: #c4b5fd;
  }
  .contact-channel-body { min-width: 0; flex: 1; }
  .contact-channel-body strong {
    display: block;
    font-family: var(--font-display);
    font-size: 0.95rem;
    color: var(--color-text-primary);
    margin-bottom: 0.2rem;
    letter-spacing: -0.01em;
  }
  .contact-channel-body span {
    display: block;
    font-size: 0.82rem;
    color: var(--color-text-tertiary);
    margin-bottom: 0.3rem;
    line-height: 1.5;
  }
  .contact-channel-body em {
    font-style: normal;
    font-family: var(--font-mono);
    font-size: 0.82rem;
    color: var(--color-accent-secondary);
  }

  .response-card {
    margin-top: var(--space-md);
    padding: var(--space-lg);
    background: linear-gradient(135deg, rgba(52, 211, 153, 0.08), transparent 70%);
    border: 1px solid rgba(52, 211, 153, 0.25);
    border-left: 3px solid var(--color-accent-green);
    border-radius: var(--radius-lg);
  }
  .response-card strong { color: var(--color-text-primary); display: block; margin-bottom: 0.3rem; font-family: var(--font-display); }
  .response-card p { margin: 0; font-size: 0.85rem; color: var(--color-text-secondary); line-height: 1.6; }
</style>
@endpush

@section('content')

  {{-- ============ HERO ============ --}}
  <section class="hero" style="padding-bottom: var(--space-2xl);">
    <div class="container">
      <div class="hero-eyebrow">
        <span class="pill">CONTACT</span>
        <span>Sales · support · partnerships · just-saying-hi</span>
      </div>

      <h1 class="hero-title">
        Let's talk about<br>
        <span class="text-gradient-magic">your next site.</span>
      </h1>

      <p class="hero-subtitle">
        Pricing questions, custom modules, white-label, integrations — or a quick chat
        about whether Agares is right for your team. Pick the channel that suits you.
      </p>
    </div>
  </section>

  {{-- ============ CONTACT GRID ============ --}}
  <section style="padding-top: var(--space-md);">
    <div class="container-wide">

      <div class="contact-grid">

        {{-- Form column --}}
        <div class="contact-card contact-form-wrap reveal">
          <span class="eyebrow">Drop us a note</span>
          <h2 style="font-size: var(--text-2xl); margin-bottom: var(--space-md); letter-spacing: -0.025em;">Send a message</h2>
          <p style="color: var(--color-text-secondary); font-size: var(--text-base); margin-bottom: var(--space-xl); line-height: 1.65;">
            We read every message. Tell us what you're working on, what you're considering, or just say hi.
            Goes straight to the Agares inbox.
          </p>

          {{-- Flash messages --}}
          @if (session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
          @endif
          @if (session('error'))
            <div class="alert alert-danger">{{ session('error') }}</div>
          @endif
          @if ($errors->any())
            <div class="alert alert-danger">
              <div style="font-weight: 600; margin-bottom: 0.3rem;">Please fix the errors below:</div>
              <ul style="margin: 0; padding-left: 1.2rem;">
                @foreach ($errors->all() as $msg)
                  <li>{{ $msg }}</li>
                @endforeach
              </ul>
            </div>
          @endif

          @if($contactForm && $contactForm->fields && $contactForm->fields->count())
            {{-- Wire to the CMS Forms module: posts to forms.submit with the form ID --}}
            <form method="POST"
                  action="{{ route('forms.submit', $contactForm->id) }}"
                  enctype="multipart/form-data"
                  class="ag-contact-form"
                  novalidate>
              @csrf
              <input type="hidden" name="_context" value='@json(['source' => 'contact-page'])'>

              @foreach($contactForm->fields as $field)
                @php
                  $key   = $field->key;
                  $name  = "fields[$key]";
                  $id    = "cf{$contactForm->id}_{$key}";
                  $req   = (bool) $field->required;
                  $type  = $field->type ?? 'text';
                  $label = $field->label ?? ucfirst(str_replace('_', ' ', $key));
                @endphp

                @if($type === 'textarea')
                  <div class="form-group">
                    <label class="form-label" for="{{ $id }}">{!! function_exists('safe_label') ? safe_label($label) : e($label) !!}</label>
                    <textarea class="form-input" id="{{ $id }}" name="{{ $name }}"
                              placeholder="{{ $field->placeholder ?? '' }}"
                              {{ $req ? 'required' : '' }}>{{ old("fields.$key") }}</textarea>
                  </div>
                @elseif($type === 'checkbox')
                  <div class="form-group" style="display: flex; align-items: flex-start; gap: 0.6rem;">
                    <input type="checkbox" id="{{ $id }}" name="{{ $name }}" value="1"
                           {{ old("fields.$key") ? 'checked' : '' }} {{ $req ? 'required' : '' }}
                           style="margin-top: 4px; accent-color: var(--color-accent-primary);">
                    <label for="{{ $id }}" style="font-size: 0.88rem; color: var(--color-text-secondary); line-height: 1.55; text-transform: none; letter-spacing: normal; font-family: var(--font-sans); margin: 0;">
                      {!! function_exists('safe_label') ? safe_label($label) : e($label) !!}
                    </label>
                  </div>
                @elseif($type === 'file')
                  <div class="form-group">
                    <label class="form-label" for="{{ $id }}">{!! function_exists('safe_label') ? safe_label($label) : e($label) !!}</label>
                    <input type="file" class="form-input" id="{{ $id }}" name="{{ $name }}"
                           {{ $req ? 'required' : '' }}>
                    @if($field->placeholder)
                      <div class="form-text">{{ $field->placeholder }}</div>
                    @endif
                  </div>
                @else
                  @php
                    $htmlType = in_array($type, ['email','tel','number','date']) ? $type : 'text';
                  @endphp
                  <div class="form-group">
                    <label class="form-label" for="{{ $id }}">{!! function_exists('safe_label') ? safe_label($label) : e($label) !!}</label>
                    <input type="{{ $htmlType }}" class="form-input" id="{{ $id }}" name="{{ $name }}"
                           value="{{ old("fields.$key") }}"
                           placeholder="{{ $field->placeholder ?? '' }}"
                           data-ag-type="{{ $type }}"
                           {{ $req ? 'required' : '' }}>
                  </div>
                @endif
              @endforeach

              <button type="submit">
                Send message
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="margin-left: 0.4rem; vertical-align: -3px;"><path d="M5 12h14M13 6l6 6-6 6"/></svg>
              </button>
            </form>
          @else
            {{-- No CMS form configured — graceful fallback --}}
            <div class="alert alert-warning">
              <strong>Contact form isn't configured yet.</strong>
              <p style="margin: 0.4rem 0 0; font-size: 0.85rem;">
                The admin needs to create a Form in the CMS named "Contact". Until then,
                use one of the channels on the right — they all go to the same inbox.
              </p>
            </div>
            <a href="mailto:office@agares.co.uk" class="btn btn-primary btn-lg" style="margin-top: var(--space-md);">
              Email us directly
              <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14M13 6l6 6-6 6"/></svg>
            </a>
          @endif

          <div class="response-card">
            <strong>Typical response time: 1 business day</strong>
            <p>We're a small team in EU — replies hit your inbox within a working day, often sooner. Urgent? Mention it in the subject.</p>
          </div>
        </div>

        {{-- Channels column --}}
        <div>
          <span class="eyebrow">Other ways to reach us</span>
          <h2 style="font-size: var(--text-2xl); margin-bottom: var(--space-md); letter-spacing: -0.025em;">Pick a channel</h2>
          <p style="color: var(--color-text-secondary); font-size: var(--text-base); margin-bottom: var(--space-xl); line-height: 1.65;">
            Email for anything substantive. GitHub for issues + feature requests. LinkedIn if you'd rather not talk shop in the open.
          </p>

          <div class="contact-channels">

            <a href="mailto:office@agares.co.uk" class="contact-channel reveal">
              <div class="contact-channel-ico">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg>
              </div>
              <div class="contact-channel-body">
                <strong>Email</strong>
                <span>General inquiries, sales, support, custom modules.</span>
                <em>office@agares.co.uk</em>
              </div>
            </a>

            <a href="https://github.com/H4STUR" target="_blank" rel="noopener" class="contact-channel reveal">
              <div class="contact-channel-ico" style="background: rgba(255, 255, 255, 0.06); border-color: rgba(255, 255, 255, 0.15); color: var(--color-text-primary);">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 19c-5 1.5-5-2.5-7-3m14 6v-3.87a3.37 3.37 0 00-.94-2.61c3.14-.35 6.44-1.54 6.44-7A5.44 5.44 0 0020 4.77 5.07 5.07 0 0019.91 1S18.73.65 16 2.48a13.38 13.38 0 00-7 0C6.27.65 5.09 1 5.09 1A5.07 5.07 0 005 4.77a5.44 5.44 0 00-1.5 3.78c0 5.42 3.3 6.61 6.44 7A3.37 3.37 0 009 18.13V22"/></svg>
              </div>
              <div class="contact-channel-body">
                <strong>GitHub</strong>
                <span>Open an issue, request a feature, peek at the source.</span>
                <em>github.com/H4STUR</em>
              </div>
            </a>

            <a href="https://www.linkedin.com/in/lukasz-majerski/" target="_blank" rel="noopener" class="contact-channel reveal">
              <div class="contact-channel-ico" style="background: rgba(34, 211, 238, 0.12); border-color: rgba(34, 211, 238, 0.3); color: #67e8f9;">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor"><path d="M16 8a6 6 0 016 6v7h-4v-7a2 2 0 00-2-2 2 2 0 00-2 2v7h-4v-7a6 6 0 016-6zM2 9h4v12H2z"/><circle cx="4" cy="4" r="2"/></svg>
              </div>
              <div class="contact-channel-body">
                <strong>LinkedIn</strong>
                <span>Network introductions, partnership pitches, hello messages.</span>
                <em>linkedin.com/in/lukasz-majerski</em>
              </div>
            </a>

            <button type="button" class="contact-channel reveal" data-demo-open style="width: 100%; text-align: left; background: var(--color-surface); border: 1px solid var(--color-border); cursor: pointer; font-family: inherit;">
              <div class="contact-channel-ico" style="background: rgba(52, 211, 153, 0.12); border-color: rgba(52, 211, 153, 0.3); color: #6ee7b7;">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polygon points="5 3 19 12 5 21 5 3"/></svg>
              </div>
              <div class="contact-channel-body">
                <strong>Try the live demo</strong>
                <span>Walk through the admin yourself before talking to anyone.</span>
                <em>One click — no signup needed</em>
              </div>
            </button>
          </div>
        </div>
      </div>
    </div>
  </section>

  {{-- ============ CTA ============ --}}
  <section>
    <div class="container">
      <div class="cta-banner reveal">
        <span class="badge badge-cyan mb-md">Not ready to talk yet?</span>
        <h2>Read the architecture first.<br><span class="text-gradient">Then come back when you have questions</span>.</h2>
        <p>The Security, API and Page-editor pages cover what most people ask in the first call. Have a look — questions get sharper.</p>
        <div class="hero-buttons">
          <a href="/security" class="btn btn-secondary btn-lg">Security architecture</a>
          <a href="/api" class="btn btn-secondary btn-lg">REST API</a>
          <a href="/pricing" class="btn btn-secondary btn-lg">Pricing</a>
        </div>
      </div>
    </div>
  </section>

@stop
