@extends('pages.frontend.base')

@push('styles')
<style>
  /* ============ Forms page extras ============ */

  /* Builder UI mock — left rail of field types + canvas */
  .builder-mock {
    background: var(--color-bg-secondary);
    border: 1px solid var(--color-border-hover);
    border-radius: var(--radius-xl);
    overflow: hidden;
    box-shadow: var(--shadow-xl);
  }
  .builder-bar {
    display: flex; align-items: center; gap: 0.5rem;
    padding: 0.5rem 0.85rem;
    background: rgba(255, 255, 255, 0.025);
    border-bottom: 1px solid var(--color-border);
  }
  .builder-bar .dot { width: 9px; height: 9px; border-radius: 50%; }
  .builder-bar .dot:nth-child(1) { background: #ff5f57; }
  .builder-bar .dot:nth-child(2) { background: #febc2e; }
  .builder-bar .dot:nth-child(3) { background: #28c840; }
  .builder-bar .title {
    margin-left: auto; margin-right: auto;
    font-family: var(--font-mono);
    font-size: 0.72rem;
    color: var(--color-text-tertiary);
  }
  .builder-body {
    display: grid;
    grid-template-columns: 180px 1fr;
    min-height: 380px;
  }
  @media (max-width: 700px) {
    .builder-body { grid-template-columns: 1fr; }
    .builder-rail { border-right: none; border-bottom: 1px solid var(--color-border); }
  }
  .builder-rail {
    padding: var(--space-md);
    background: rgba(255, 255, 255, 0.02);
    border-right: 1px solid var(--color-border);
  }
  .builder-rail-label {
    font-family: var(--font-mono);
    font-size: 0.66rem;
    text-transform: uppercase;
    letter-spacing: 0.1em;
    color: var(--color-text-tertiary);
    margin-bottom: var(--space-sm);
  }
  .builder-chip {
    display: flex; align-items: center; gap: 0.5rem;
    padding: 0.5rem 0.6rem;
    background: var(--color-surface);
    border: 1px solid var(--color-border);
    border-radius: var(--radius-sm);
    margin-bottom: 0.4rem;
    font-size: 0.78rem;
    color: var(--color-text-secondary);
    cursor: grab;
    transition: all var(--transition-base);
  }
  .builder-chip:hover { border-color: var(--color-border-hover); transform: translateX(2px); }
  .builder-chip svg { color: var(--color-accent-secondary); flex-shrink: 0; }
  .builder-canvas {
    padding: var(--space-lg);
    background: var(--color-bg-tertiary);
  }
  .builder-field {
    background: var(--color-surface);
    border: 1px dashed var(--color-border-hover);
    border-radius: var(--radius-md);
    padding: var(--space-md);
    margin-bottom: 0.6rem;
  }
  .builder-field-label {
    font-family: var(--font-mono);
    font-size: 0.7rem;
    text-transform: uppercase;
    letter-spacing: 0.06em;
    color: var(--color-text-tertiary);
    margin-bottom: 0.3rem;
  }
  .builder-field-preview {
    height: 32px;
    background: rgba(7, 8, 13, 0.5);
    border: 1px solid var(--color-border);
    border-radius: var(--radius-sm);
  }
  .builder-field-preview.tall { height: 64px; }
  .builder-field.required .builder-field-label::after {
    content: ' *';
    color: #fca5a5;
  }
  .builder-add {
    padding: 0.65rem 0.85rem;
    background: transparent;
    border: 1px dashed var(--color-border-hover);
    border-radius: var(--radius-md);
    color: var(--color-text-tertiary);
    font-family: var(--font-mono);
    font-size: 0.78rem;
    text-align: center;
    cursor: pointer;
  }

  /* Submissions mock table */
  .sub-table {
    background: var(--color-bg-code);
    border: 1px solid var(--color-border-hover);
    border-radius: var(--radius-xl);
    overflow: hidden;
    box-shadow: var(--shadow-xl);
    font-family: var(--font-mono);
  }
  .sub-head, .sub-row {
    display: grid;
    grid-template-columns: 0.8fr 1.3fr 1fr 0.9fr;
    align-items: center;
    padding: 0.7rem 1rem;
    font-size: 0.78rem;
  }
  .sub-head {
    background: rgba(255, 255, 255, 0.03);
    border-bottom: 1px solid var(--color-border);
    color: var(--color-text-tertiary);
    text-transform: uppercase;
    letter-spacing: 0.08em;
    font-size: 0.7rem;
  }
  .sub-row {
    border-bottom: 1px solid var(--color-border);
    color: var(--color-text-secondary);
  }
  .sub-row:last-child { border-bottom: none; }
  .sub-row strong { color: var(--color-text-primary); }
  .sub-row .stat {
    display: inline-flex; align-items: center; gap: 0.35rem;
    padding: 0.15rem 0.5rem;
    border-radius: var(--radius-sm);
    font-size: 0.7rem;
    text-transform: uppercase;
    letter-spacing: 0.05em;
  }
  .sub-row .stat.new   { background: rgba(34, 211, 238, 0.12); color: #67e8f9; border: 1px solid rgba(34, 211, 238, 0.3); }
  .sub-row .stat.read  { background: rgba(255, 255, 255, 0.05); color: var(--color-text-tertiary); border: 1px solid var(--color-border); }
  .sub-row .stat.spam  { background: rgba(248, 113, 113, 0.08); color: #fca5a5; border: 1px solid rgba(248, 113, 113, 0.3); }
  @media (max-width: 800px) {
    .sub-table { overflow-x: auto; }
    .sub-head, .sub-row { min-width: 560px; }
  }

  /* Feature grid */
  .form-features {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: var(--space-lg);
  }
  @media (max-width: 1000px) { .form-features { grid-template-columns: repeat(2, 1fr); } }
  @media (max-width: 600px)  { .form-features { grid-template-columns: 1fr; } }

  .form-feature {
    padding: var(--space-xl);
    background: var(--color-surface);
    border: 1px solid var(--color-border);
    border-radius: var(--radius-lg);
    transition: all var(--transition-base);
  }
  .form-feature:hover { border-color: var(--color-border-hover); transform: translateY(-2px); }
  .form-feature-ico {
    width: 44px; height: 44px;
    display: inline-flex; align-items: center; justify-content: center;
    background: rgba(34, 211, 238, 0.12);
    border: 1px solid rgba(34, 211, 238, 0.3);
    color: #67e8f9;
    border-radius: var(--radius-md);
    margin-bottom: var(--space-md);
  }
  .form-feature h4 { font-family: var(--font-display); font-size: 1.1rem; margin-bottom: 0.4rem; letter-spacing: -0.015em; }
  .form-feature p { font-size: 0.88rem; color: var(--color-text-secondary); margin: 0; line-height: 1.65; }
</style>
@endpush

@section('content')

  {{-- ============ HERO ============ --}}
  <section class="hero">
    <div class="container">
      <div class="hero-eyebrow">
        <span class="pill">FORMS</span>
        <span>Drag-build · embed anywhere · submissions in admin</span>
      </div>

      <h1 class="hero-title">
        Build a form. Embed&nbsp;it.<br>
        <span class="text-gradient-magic">Read submissions in&nbsp;the&nbsp;admin.</span>
      </h1>

      <p class="hero-subtitle">
        A drag-and-drop form builder that produces real Laravel forms — no Google Forms iframe,
        no Typeform subscription, no third-party data trip. Submissions land in your admin,
        every field type validated server-side, optional email notifications, anti-spam included.
      </p>

      <div class="hero-buttons">
        <button type="button" class="btn btn-primary btn-lg btn-icon-after" data-demo-open>
          See the form builder
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14M13 6l6 6-6 6"/></svg>
        </button>
        <a href="/contact" class="btn btn-secondary btn-lg">
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2zM22 6l-10 7L2 6"/></svg>
          See a live form
        </a>
      </div>

      <div class="hero-stats">
        <div class="hero-stat"><div class="num">5</div><div class="label">Field types</div></div>
        <div class="hero-stat"><div class="num">∞</div><div class="label">Forms per site</div></div>
        <div class="hero-stat"><div class="num">0</div><div class="label">Third-party services</div></div>
        <div class="hero-stat"><div class="num">100%</div><div class="label">Server-side validated</div></div>
      </div>
    </div>
  </section>

  {{-- ============ BUILDER + SUBMISSIONS MOCK ============ --}}
  <section style="padding-top: var(--space-2xl);">
    <div class="container-wide">
      <div class="split">
        <div>
          <span class="eyebrow">In the admin</span>
          <h2 style="margin-bottom: var(--space-md);">Drag a field.<br>Set a label. <span class="text-gradient">Done</span>.</h2>
          <p style="color: var(--color-text-secondary); font-size: var(--text-lg); margin-bottom: var(--space-xl); line-height: 1.65;">
            The builder is a tiny Alpine.js component on top of a normal Laravel form. Drag a field type
            from the left rail onto the canvas, set its label and validation, save. The frontend embed
            picks it up on the next request — no rebuild, no cache flush.
          </p>

          <div class="builder-mock reveal">
            <div class="builder-bar">
              <span class="dot"></span><span class="dot"></span><span class="dot"></span>
              <span class="title">Form builder — "Contact"</span>
            </div>
            <div class="builder-body">
              <div class="builder-rail">
                <div class="builder-rail-label">Drag a field</div>
                <div class="builder-chip"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="5" y1="12" x2="19" y2="12"/></svg> Text</div>
                <div class="builder-chip"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/></svg> Email</div>
                <div class="builder-chip"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72"/></svg> Phone</div>
                <div class="builder-chip"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="4" y1="6" x2="20" y2="6"/><line x1="4" y1="12" x2="20" y2="12"/><line x1="4" y1="18" x2="14" y2="18"/></svg> Textarea</div>
                <div class="builder-chip"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg> Checkbox</div>
                <div class="builder-chip"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14.5 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V7.5L14.5 2z"/></svg> File</div>
              </div>
              <div class="builder-canvas">
                <div class="builder-field required">
                  <div class="builder-field-label">Name</div>
                  <div class="builder-field-preview"></div>
                </div>
                <div class="builder-field required">
                  <div class="builder-field-label">Email</div>
                  <div class="builder-field-preview"></div>
                </div>
                <div class="builder-field">
                  <div class="builder-field-label">Subject</div>
                  <div class="builder-field-preview"></div>
                </div>
                <div class="builder-field required">
                  <div class="builder-field-label">Message</div>
                  <div class="builder-field-preview tall"></div>
                </div>
                <div class="builder-add">+ Drop a field here</div>
              </div>
            </div>
          </div>
        </div>

        <div>
          <span class="eyebrow">Where submissions land</span>
          <h2 style="margin-bottom: var(--space-md);">Every submission,<br><span class="text-gradient">in one queryable grid</span>.</h2>
          <p style="color: var(--color-text-secondary); font-size: var(--text-lg); margin-bottom: var(--space-xl); line-height: 1.65;">
            Submissions are stored in your database, taggable, exportable, and optionally emailed
            to a configurable recipient. Spam goes to its own bucket, originating page is captured
            on every row.
          </p>

          <div class="sub-table reveal">
            <div class="sub-head">
              <span>Status</span>
              <span>From</span>
              <span>Form</span>
              <span style="text-align: right;">When</span>
            </div>
            <div class="sub-row">
              <span><span class="stat new">New</span></span>
              <strong>maria@studio-x.eu</strong>
              <span>Contact (sales)</span>
              <span style="text-align: right;">2 min ago</span>
            </div>
            <div class="sub-row">
              <span><span class="stat new">New</span></span>
              <strong>tom.k@piesci.pl</strong>
              <span>Booking (PetCamp)</span>
              <span style="text-align: right;">38 min ago</span>
            </div>
            <div class="sub-row">
              <span><span class="stat read">Read</span></span>
              <strong>marek@agency.pl</strong>
              <span>Quote request</span>
              <span style="text-align: right;">3 h ago</span>
            </div>
            <div class="sub-row">
              <span><span class="stat read">Read</span></span>
              <strong>aleksandra@cms-user.co</strong>
              <span>Newsletter signup</span>
              <span style="text-align: right;">yesterday</span>
            </div>
            <div class="sub-row">
              <span><span class="stat spam">Spam</span></span>
              <strong>noreply@spam-net.ru</strong>
              <span>Contact (sales)</span>
              <span style="text-align: right;">yesterday</span>
            </div>
            <div class="sub-row">
              <span><span class="stat read">Read</span></span>
              <strong>jan@boutique.pl</strong>
              <span>Pricing inquiry</span>
              <span style="text-align: right;">2 days ago</span>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>

  {{-- ============ FEATURE GRID ============ --}}
  <section>
    <div class="container-wide">
      <div class="section-header">
        <span class="eyebrow">Everything you'd expect</span>
        <h2>Forms that <span class="text-gradient">don't suck</span>.</h2>
        <p>The boring stuff (validation, sanitisation, anti-spam, file uploads) all handled. The fun stuff (drag-build, embed anywhere) front and centre.</p>
      </div>

      <div class="form-features">
        @foreach([
          ['M9 11l3 3L22 4 M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11', 'Server-side validation', 'Required, email format, phone format, min/max length. Backend always re-validates — frontend nudges are courtesy, not security.'],
          ['M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2zM22 6l-10 7L2 6', 'Email notifications', 'Optional per-form recipient. Plain HTML email with every field value. Goes synchronously (no queue).'],
          ['M14.5 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V7.5L14.5 2z', 'File uploads', 'MIME allowlist enforced. No .php, .phar, .phtml — authenticated submission ≠ trusted upload.'],
          ['M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z', 'Anti-spam', 'Honeypot field + submission rate-limit per IP. No CAPTCHA mandatory — the demo runs without one.'],
          ['M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z', 'Embed anywhere', 'Use as a custom field on any page — drop <code style="font-family:var(--font-mono);font-size:0.85em;color:#67e8f9;">contact_form_from_instance($field)</code> in your Blade.'],
          ['M12 8v4l3 3', 'Submission grid', 'Filter by form, by status, by date. Mark read/unread. Mark as spam — moves to bucket, never deleted automatically.'],
          ['M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z', 'CSV export', 'Bulk-export submissions for a form to CSV. Per-form columns chosen by you.'],
          ['M3 3h18v18H3z', 'Custom labels &amp; placeholders', 'Per-field label, placeholder, help text. Auto-sanitised via <code style="font-family:var(--font-mono);font-size:0.85em;color:#67e8f9;">safe_label()</code> so admin-authored HTML can\'t bite.'],
          ['M22 12c0 5.52-4.48 10-10 10S2 17.52 2 12 6.48 2 12 2', 'Per-site permissions', '<code style="font-family:var(--font-mono);font-size:0.85em;color:#67e8f9;">view forms</code> / <code style="font-family:var(--font-mono);font-size:0.85em;color:#67e8f9;">manage forms</code> permissions decide who can build, who can read submissions.'],
        ] as $f)
          <div class="form-feature reveal">
            <div class="form-feature-ico">
              <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="{{ $f[0] }}"/></svg>
            </div>
            <h4>{{ $f[1] }}</h4>
            <p>{!! $f[2] !!}</p>
          </div>
        @endforeach
      </div>
    </div>
  </section>

  {{-- ============ CTA ============ --}}
  <section>
    <div class="container">
      <div class="cta-banner reveal">
        <span class="badge badge-cyan mb-md">Forms demo</span>
        <h2>Drag-build a form. Submit it.<br><span class="text-gradient">Read it in the admin</span>.</h2>
        <p>The demo lets you build a form, embed it on a page, send a test submission, and read it back in the submissions grid.</p>
        <div class="hero-buttons">
          <button type="button" class="btn btn-primary btn-lg btn-icon-after" data-demo-open>
            Open the form builder
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14M13 6l6 6-6 6"/></svg>
          </button>
          <a href="/contact" class="btn btn-secondary btn-lg">See a real form on /contact</a>
        </div>
      </div>
    </div>
  </section>

@stop
