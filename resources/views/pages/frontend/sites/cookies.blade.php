@extends('pages.frontend.base')

@push('styles')
<style>
  /* ============ Cookies page extras ============ */

  /* Consent UI mock (a realistic-looking consent dialog rendered in HTML) */
  .consent-mock {
    background: linear-gradient(180deg, rgba(255, 255, 255, 0.05), rgba(255, 255, 255, 0.02));
    border: 1px solid var(--color-border-hover);
    border-radius: var(--radius-xl);
    padding: var(--space-xl);
    box-shadow: var(--shadow-xl);
  }
  .consent-mock h4 {
    font-family: var(--font-display);
    font-size: 1.05rem;
    margin-bottom: 0.5rem;
    letter-spacing: -0.015em;
  }
  .consent-mock p { font-size: 0.85rem; color: var(--color-text-secondary); margin: 0 0 var(--space-md); line-height: 1.6; }
  .consent-toggle-row {
    display: flex; align-items: center; justify-content: space-between;
    padding: 0.75rem 0;
    border-top: 1px solid var(--color-border);
    font-size: 0.85rem;
    color: var(--color-text-secondary);
  }
  .consent-toggle-row:first-of-type { border-top: 1px solid var(--color-border); margin-top: var(--space-md); }
  .consent-toggle-row strong { color: var(--color-text-primary); font-weight: 500; }
  .consent-pill {
    display: inline-flex; align-items: center; gap: 0.3rem;
    padding: 0.2rem 0.55rem;
    border-radius: var(--radius-full);
    font-family: var(--font-mono);
    font-size: 0.68rem;
    text-transform: uppercase;
    letter-spacing: 0.06em;
  }
  .consent-pill.locked { background: rgba(255, 255, 255, 0.05); color: var(--color-text-tertiary); border: 1px solid var(--color-border); }
  .consent-pill.on     { background: rgba(52, 211, 153, 0.12); color: #6ee7b7; border: 1px solid rgba(52, 211, 153, 0.3); }
  .consent-pill.off    { background: rgba(248, 113, 113, 0.08); color: #fca5a5; border: 1px solid rgba(248, 113, 113, 0.3); }
  .consent-actions {
    display: flex; gap: 0.5rem;
    margin-top: var(--space-lg);
    padding-top: var(--space-md);
    border-top: 1px solid var(--color-border);
  }
  .consent-actions .ct {
    flex: 1;
    padding: 0.65rem 0.9rem;
    border-radius: var(--radius-md);
    font-family: var(--font-sans);
    font-size: 0.82rem;
    font-weight: 600;
    text-align: center;
    border: 1px solid;
  }
  .consent-actions .ct.primary { background: var(--color-accent-gradient); color: white; border-color: transparent; }
  .consent-actions .ct.ghost   { background: transparent; color: var(--color-text-secondary); border-color: var(--color-border-hover); }

  /* Scanner table */
  .scanner-table {
    background: var(--color-bg-code);
    border: 1px solid var(--color-border-hover);
    border-radius: var(--radius-xl);
    overflow: hidden;
    box-shadow: var(--shadow-xl);
    font-family: var(--font-mono);
  }
  .scanner-head, .scanner-row {
    display: grid;
    grid-template-columns: 1.4fr 0.9fr 1fr 0.8fr;
    align-items: center;
    padding: 0.75rem 1rem;
    font-size: 0.78rem;
  }
  .scanner-head {
    background: rgba(255, 255, 255, 0.03);
    border-bottom: 1px solid var(--color-border);
    color: var(--color-text-tertiary);
    text-transform: uppercase;
    letter-spacing: 0.08em;
    font-size: 0.7rem;
  }
  .scanner-row {
    border-bottom: 1px solid var(--color-border);
    color: var(--color-text-secondary);
  }
  .scanner-row:last-child { border-bottom: none; }
  .scanner-row strong { color: var(--color-text-primary); }
  .scanner-row .cat {
    display: inline-block;
    padding: 0.15rem 0.5rem;
    border-radius: var(--radius-sm);
    font-size: 0.7rem;
    text-transform: uppercase;
    letter-spacing: 0.05em;
  }
  .scanner-row .cat.nec { background: rgba(255, 255, 255, 0.05); color: var(--color-text-tertiary); border: 1px solid var(--color-border); }
  .scanner-row .cat.ana { background: rgba(34, 211, 238, 0.12); color: #67e8f9; border: 1px solid rgba(34, 211, 238, 0.3); }
  .scanner-row .cat.mkt { background: rgba(244, 114, 182, 0.12); color: #f9a8d4; border: 1px solid rgba(244, 114, 182, 0.3); }
  .scanner-row .grade { font-weight: 600; }
  .scanner-row .grade.a { color: var(--color-accent-green); }
  .scanner-row .grade.b { color: #67e8f9; }
  .scanner-row .grade.c { color: #fde68a; }
  @media (max-width: 800px) {
    .scanner-table { overflow-x: auto; }
    .scanner-head, .scanner-row { min-width: 600px; }
  }

  /* Compliance grid */
  .compliance-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: var(--space-lg);
  }
  @media (max-width: 1000px) { .compliance-grid { grid-template-columns: repeat(2, 1fr); } }
  @media (max-width: 600px)  { .compliance-grid { grid-template-columns: 1fr; } }

  .compliance-card {
    padding: var(--space-xl);
    background: var(--color-surface);
    border: 1px solid var(--color-border);
    border-radius: var(--radius-lg);
    transition: all var(--transition-base);
  }
  .compliance-card:hover { border-color: var(--color-border-hover); transform: translateY(-2px); }
  .compliance-card-ico {
    width: 44px; height: 44px;
    display: inline-flex; align-items: center; justify-content: center;
    background: rgba(251, 191, 36, 0.12);
    border: 1px solid rgba(251, 191, 36, 0.3);
    color: #fde68a;
    border-radius: var(--radius-md);
    margin-bottom: var(--space-md);
  }
  .compliance-card h4 { font-family: var(--font-display); font-size: 1.1rem; margin-bottom: 0.4rem; letter-spacing: -0.015em; }
  .compliance-card p { font-size: 0.88rem; color: var(--color-text-secondary); margin: 0; line-height: 1.65; }
</style>
@endpush

@section('content')

  {{-- ============ HERO ============ --}}
  <section class="hero">
    <div class="container">
      <div class="hero-eyebrow">
        <span class="pill">COOKIES &amp; GDPR</span>
        <span>Live scanner · granular consent · script gating · audit trail</span>
      </div>

      <h1 class="hero-title">
        GDPR compliance.<br>
        <span class="text-gradient-magic">Without the SaaS bill.</span>
      </h1>

      <p class="hero-subtitle">
        A real cookie scanner that crawls your site, a consent banner that visitors actually
        understand, and per-category script gating that stops third-party tags from loading
        until consent is given. All built in, no per-domain pricing.
      </p>

      <div class="hero-buttons">
        <button type="button" class="btn btn-primary btn-lg btn-icon-after" data-demo-open>
          See the cookies admin
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14M13 6l6 6-6 6"/></svg>
        </button>
        <a href="#scanner" class="btn btn-secondary btn-lg">
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
          See the scanner
        </a>
      </div>

      <div class="hero-stats">
        <div class="hero-stat"><div class="num">4</div><div class="label">Consent categories</div></div>
        <div class="hero-stat"><div class="num">3</div><div class="label">Signal types captured</div></div>
        <div class="hero-stat"><div class="num">∞</div><div class="label">Pages scannable</div></div>
        <div class="hero-stat"><div class="num">€0</div><div class="label">Per-domain fees</div></div>
      </div>
    </div>

    <div class="container-wide" style="margin-top: var(--space-3xl);">
      <div class="hero-showcase">
        <div class="hero-showcase-glow" aria-hidden="true"></div>
        <div class="dashboard-preview tilt">
          <div class="preview-bar">
            <span class="preview-dot"></span>
            <span class="preview-dot"></span>
            <span class="preview-dot"></span>
            <span class="preview-url">agares.app/admin/cookies</span>
          </div>
          <div class="preview-content">
            <img src="{{ asset('assets/frontend/images/agares_cms_cookies.jpg') }}" alt="Agares cookie consent admin — scan history, consent configuration, audit log" loading="eager" fetchpriority="high">
          </div>
        </div>
      </div>
    </div>
  </section>

  {{-- ============ CONSENT UI + SCANNER MOCK ============ --}}
  <section style="padding-top: var(--space-2xl);">
    <div class="container-wide">
      <div class="split">
        <div>
          <span class="eyebrow">What visitors see</span>
          <h2 style="margin-bottom: var(--space-md);">A consent banner<br>that <span class="text-gradient">actually informs</span>.</h2>
          <p style="color: var(--color-text-secondary); font-size: var(--text-lg); margin-bottom: var(--space-xl); line-height: 1.65;">
            Per-category toggles, plain-language descriptions, link to your cookie policy.
            "Reject all" is the same one click as "Accept all" — not a dark pattern in sight.
          </p>

          <div class="consent-mock reveal">
            <h4>We use cookies on this site.</h4>
            <p>Some are essential for the site to work. Others help us understand how visitors use it. You decide.</p>

            <div class="consent-toggle-row">
              <span><strong>Strictly necessary</strong><br><small style="color: var(--color-text-tertiary);">Login, cart, security — required.</small></span>
              <span class="consent-pill locked">Always on</span>
            </div>
            <div class="consent-toggle-row">
              <span><strong>Analytics</strong><br><small style="color: var(--color-text-tertiary);">GA4 — measures page views &amp; bounce.</small></span>
              <span class="consent-pill on">Allowed</span>
            </div>
            <div class="consent-toggle-row">
              <span><strong>Marketing</strong><br><small style="color: var(--color-text-tertiary);">Retargeting pixels, ad attribution.</small></span>
              <span class="consent-pill off">Blocked</span>
            </div>
            <div class="consent-toggle-row">
              <span><strong>Functional</strong><br><small style="color: var(--color-text-tertiary);">Embedded video, chat widget.</small></span>
              <span class="consent-pill on">Allowed</span>
            </div>

            <div class="consent-actions">
              <span class="ct primary">Save my choices</span>
              <span class="ct ghost">Reject all</span>
              <span class="ct ghost">Accept all</span>
            </div>
          </div>
        </div>

        <div>
          <span class="eyebrow">What you see</span>
          <h2 style="margin-bottom: var(--space-md);" id="scanner">The scanner finds<br>every cookie. <span class="text-gradient">Even the sneaky ones</span>.</h2>
          <p style="color: var(--color-text-secondary); font-size: var(--text-lg); margin-bottom: var(--space-xl); line-height: 1.65;">
            Click "Scan" in the admin. Agares crawls your pages with a real headless browser, records
            every cookie set, categorises it (necessary / analytics / marketing / functional), and
            grades the worst offenders. Re-run it after every deploy.
          </p>

          <div class="scanner-table reveal">
            <div class="scanner-head">
              <span>Cookie</span>
              <span>Set by</span>
              <span>Category</span>
              <span style="text-align: center;">Grade</span>
            </div>
            <div class="scanner-row">
              <strong>laravel_session</strong>
              <span>self</span>
              <span><span class="cat nec">Necessary</span></span>
              <span style="text-align: center;" class="grade a">A</span>
            </div>
            <div class="scanner-row">
              <strong>_ga, _ga_*</strong>
              <span>googletagmanager.com</span>
              <span><span class="cat ana">Analytics</span></span>
              <span style="text-align: center;" class="grade b">B</span>
            </div>
            <div class="scanner-row">
              <strong>_fbp</strong>
              <span>facebook.net</span>
              <span><span class="cat mkt">Marketing</span></span>
              <span style="text-align: center;" class="grade c">C</span>
            </div>
            <div class="scanner-row">
              <strong>XSRF-TOKEN</strong>
              <span>self</span>
              <span><span class="cat nec">Necessary</span></span>
              <span style="text-align: center;" class="grade a">A</span>
            </div>
            <div class="scanner-row">
              <strong>YSC, VISITOR_INFO1_LIVE</strong>
              <span>youtube.com</span>
              <span><span class="cat ana">Analytics</span></span>
              <span style="text-align: center;" class="grade b">B</span>
            </div>
            <div class="scanner-row">
              <strong>_hjSession_*</strong>
              <span>hotjar.com</span>
              <span><span class="cat ana">Analytics</span></span>
              <span style="text-align: center;" class="grade c">C</span>
            </div>
          </div>

          <p style="margin-top: var(--space-md); font-family: var(--font-mono); font-size: 0.78rem; color: var(--color-text-tertiary);">
            Scanner runs against the live SaaS scanner microservice. Every scan stored in <code style="color:#67e8f9;">cookie_scans</code>.
          </p>
        </div>
      </div>
    </div>
  </section>

  {{-- ============ COMPLIANCE FEATURES ============ --}}
  <section>
    <div class="container-wide">
      <div class="section-header">
        <span class="eyebrow">The compliance toolkit</span>
        <h2>Every box on the lawyer's checklist.<br><span class="text-gradient">Already ticked</span>.</h2>
      </div>

      <div class="compliance-grid">
        @foreach([
          ['M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z', 'Script gating', 'Third-party scripts (GA, FB Pixel, Hotjar) only load AFTER the matching consent category is allowed. No silent tracking.'],
          ['M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z', 'Consent audit log', 'Every consent decision logged with timestamp, IP, user-agent, version of the policy in effect. Defensible in a regulator audit.'],
          ['M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z', 'Granular categories', 'Necessary (locked on), Analytics, Marketing, Functional. Per-category script lists configured in the admin.'],
          ['M22 12c0 5.52-4.48 10-10 10S2 17.52 2 12 6.48 2 12 2', 'Re-consent triggers', 'Cookie policy changed → next-page visit re-opens the banner with a diff. Versioned policy means visitors are always reading the current text.'],
          ['M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z', 'Cookie policy page', 'Auto-generates a structured cookie policy from the latest scan. List every cookie, its purpose, its retention, its category.'],
          ['M22 11.08V12a10 10 0 1 1-5.93-9.14', 'No dark patterns', 'Reject button has the same prominence as Accept. No 18-click "manage preferences" maze. No pre-checked boxes.'],
          ['M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2 M12 7a4 4 0 1 0 0 8 4 4 0 0 0 0-8z', 'Visitor self-service', 'A persistent floating "Cookie preferences" button on every page. Visitors can withdraw consent in two clicks, any time.'],
          ['M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2zM22 6l-10 7L2 6', 'IAB TCF-ready', 'Consent state is exportable in IAB TCF v2 format if your ad partners require it. Optional, off by default.'],
          ['M3 3h18v18H3z M3 9h18 M9 21V9', 'Per-site consent', 'Each site has its own categories, its own policy, its own banner copy. Multi-site means multi-jurisdiction.'],
        ] as $f)
          <div class="compliance-card reveal">
            <div class="compliance-card-ico">
              <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="{{ $f[0] }}"/></svg>
            </div>
            <h4>{{ $f[1] }}</h4>
            <p>{{ $f[2] }}</p>
          </div>
        @endforeach
      </div>
    </div>
  </section>

  {{-- ============ CTA ============ --}}
  <section>
    <div class="container">
      <div class="cta-banner reveal">
        <span class="badge badge-warning mb-md">Try the scanner</span>
        <h2>Scan a site. Read the cookies.<br><span class="text-gradient">Fix what you find</span>.</h2>
        <p>The demo lets you trigger a scan and walk through the consent flow exactly as a visitor would.</p>
        <div class="hero-buttons">
          <button type="button" class="btn btn-primary btn-lg btn-icon-after" data-demo-open>
            Open the cookies module
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14M13 6l6 6-6 6"/></svg>
          </button>
          <a href="/security" class="btn btn-secondary btn-lg">See the full security stack</a>
        </div>
      </div>
    </div>
  </section>

@stop
