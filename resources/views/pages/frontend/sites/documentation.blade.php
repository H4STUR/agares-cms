@extends('pages.frontend.base')

@push('styles')
<style>
  /* ============ Docs hub extras ============ */

  .docs-search {
    position: relative;
    max-width: 640px;
    margin: 0 auto;
  }
  .docs-search input {
    width: 100%;
    padding: 1rem 1.25rem 1rem 3.25rem;
    background: rgba(7, 8, 13, 0.6);
    border: 1px solid var(--color-border-hover);
    border-radius: var(--radius-full);
    color: var(--color-text-primary);
    font-family: var(--font-sans);
    font-size: 1rem;
    transition: all var(--transition-base);
  }
  .docs-search input:focus {
    outline: none;
    border-color: var(--color-accent-primary);
    background: rgba(7, 8, 13, 0.8);
    box-shadow: 0 0 0 4px rgba(139, 92, 246, 0.12);
  }
  .docs-search svg {
    position: absolute;
    top: 50%; left: 1.15rem;
    transform: translateY(-50%);
    color: var(--color-text-tertiary);
  }
  .docs-search .kbd {
    position: absolute;
    top: 50%; right: 0.85rem;
    transform: translateY(-50%);
    padding: 0.25rem 0.55rem;
    background: rgba(255, 255, 255, 0.06);
    border: 1px solid var(--color-border);
    border-radius: var(--radius-sm);
    font-family: var(--font-mono);
    font-size: 0.75rem;
    color: var(--color-text-tertiary);
  }

  .docs-hub {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: var(--space-lg);
  }
  @media (max-width: 1000px) { .docs-hub { grid-template-columns: repeat(2, 1fr); } }
  @media (max-width: 600px)  { .docs-hub { grid-template-columns: 1fr; } }

  .docs-section {
    padding: var(--space-2xl);
    background: linear-gradient(180deg, rgba(255, 255, 255, 0.035), rgba(255, 255, 255, 0.01));
    border: 1px solid var(--color-border);
    border-radius: var(--radius-xl);
    transition: all var(--transition-base);
    display: flex; flex-direction: column;
  }
  .docs-section:hover { border-color: var(--color-border-hover); transform: translateY(-3px); }
  .docs-section-ico {
    width: 44px; height: 44px;
    display: inline-flex; align-items: center; justify-content: center;
    border-radius: var(--radius-md);
    margin-bottom: var(--space-md);
    background: rgba(139, 92, 246, 0.12);
    border: 1px solid rgba(139, 92, 246, 0.25);
    color: #c4b5fd;
  }
  .docs-section.cyan  .docs-section-ico { background: rgba(34, 211, 238, 0.12); border-color: rgba(34, 211, 238, 0.3); color: #67e8f9; }
  .docs-section.green .docs-section-ico { background: rgba(52, 211, 153, 0.12); border-color: rgba(52, 211, 153, 0.3); color: #6ee7b7; }
  .docs-section.amber .docs-section-ico { background: rgba(251, 191, 36, 0.12); border-color: rgba(251, 191, 36, 0.3); color: #fde68a; }
  .docs-section.pink  .docs-section-ico { background: rgba(244, 114, 182, 0.12); border-color: rgba(244, 114, 182, 0.3); color: #f9a8d4; }

  .docs-section h3 {
    font-family: var(--font-display);
    font-size: 1.2rem;
    margin-bottom: 0.5rem;
    letter-spacing: -0.02em;
  }
  .docs-section p {
    font-size: 0.88rem;
    color: var(--color-text-secondary);
    margin: 0 0 var(--space-md);
    line-height: 1.65;
  }
  .docs-link-list {
    list-style: none; padding: 0; margin: 0;
    border-top: 1px solid var(--color-border);
    padding-top: var(--space-md);
    flex: 1;
  }
  .docs-link-list li { margin: 0; }
  .docs-link-list a {
    display: flex; justify-content: space-between; align-items: center;
    padding: 0.55rem 0;
    font-size: 0.88rem;
    color: var(--color-text-secondary);
    text-decoration: none;
    transition: color var(--transition-base);
  }
  .docs-link-list a:hover { color: var(--color-text-primary); }
  .docs-link-list a svg { opacity: 0; transition: opacity var(--transition-base), transform var(--transition-base); }
  .docs-link-list a:hover svg { opacity: 1; transform: translateX(2px); }

  .quickstart-strip {
    display: grid;
    grid-template-columns: 1.4fr 1fr;
    gap: var(--space-xl);
    align-items: center;
    padding: clamp(2rem, 3vw, 2.5rem);
    background:
      radial-gradient(ellipse 60% 80% at 80% 50%, rgba(34, 211, 238, 0.12), transparent 70%),
      linear-gradient(180deg, rgba(255, 255, 255, 0.04), rgba(255, 255, 255, 0.015));
    border: 1px solid var(--color-border-hover);
    border-radius: var(--radius-2xl);
  }
  .quickstart-strip > * { min-width: 0; }
  @media (max-width: 900px) { .quickstart-strip { grid-template-columns: 1fr; } }
  .quickstart-strip h2 {
    font-size: clamp(1.5rem, 2.5vw, 1.85rem);
    margin-bottom: var(--space-md);
    letter-spacing: -0.025em;
  }
  .quickstart-strip p { color: var(--color-text-secondary); margin-bottom: var(--space-lg); line-height: 1.65; }

  .status-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: var(--space-md);
  }
  @media (max-width: 800px) { .status-grid { grid-template-columns: 1fr; } }

  .status-card {
    padding: var(--space-lg) var(--space-xl);
    background: var(--color-surface);
    border: 1px solid var(--color-border);
    border-radius: var(--radius-lg);
    display: flex; align-items: center; gap: var(--space-md);
  }
  .status-card .pulse {
    flex-shrink: 0;
    width: 10px; height: 10px;
    border-radius: 50%;
  }
  .status-card .pulse.green {
    background: #34d399;
    box-shadow: 0 0 0 0 rgba(52, 211, 153, 0.5);
    animation: pulse-green 2s ease infinite;
  }
  @keyframes pulse-green {
    0% { box-shadow: 0 0 0 0 rgba(52, 211, 153, 0.5); }
    70% { box-shadow: 0 0 0 8px rgba(52, 211, 153, 0); }
    100% { box-shadow: 0 0 0 0 rgba(52, 211, 153, 0); }
  }
  .status-card strong { display: block; font-family: var(--font-display); font-size: 0.95rem; color: var(--color-text-primary); letter-spacing: -0.01em; }
  .status-card span { display: block; font-size: 0.78rem; color: var(--color-text-tertiary); }
</style>
@endpush

@section('content')

  {{-- ============ HERO ============ --}}
  <section class="hero" style="padding-bottom: var(--space-2xl);">
    <div class="container">
      <div class="hero-eyebrow">
        <span class="pill">DOCS</span>
        <span>Everything you need to ship · v2.0</span>
      </div>

      <h1 class="hero-title">
        Documentation.<br>
        <span class="text-gradient-magic">For builders, not browsers.</span>
      </h1>

      <p class="hero-subtitle">
        Pick a path: install in 10 minutes, dive into the API, build a custom module,
        or read the architecture notes that explain every weird design decision.
      </p>

      <div class="docs-search" style="margin-top: var(--space-2xl);">
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
        <input type="search" placeholder="Search the docs… (coming soon)" disabled>
        <span class="kbd">⌘ K</span>
      </div>
    </div>
  </section>

  {{-- ============ QUICKSTART STRIP ============ --}}
  <section style="padding-top: var(--space-md);">
    <div class="container-wide">
      <div class="quickstart-strip reveal">
        <div>
          <span class="badge badge-cyan mb-md">Get started in 10 minutes</span>
          <h2>Install Agares locally.<br>First page live before lunch.</h2>
          <p>Docker Compose dev environment, sample data seeder, and a walk-through covering sites, categories, articles and your first custom Blade template.</p>
          <div style="display: flex; gap: 0.6rem; flex-wrap: wrap;">
            <a href="https://github.com/H4STUR" target="_blank" rel="noopener" class="btn btn-primary btn-lg btn-icon-after">
              Clone on GitHub
              <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"/><polyline points="15 3 21 3 21 9"/><line x1="10" y1="14" x2="21" y2="3"/></svg>
            </a>
            <button type="button" class="btn btn-secondary btn-lg" data-demo-open>Try the live demo</button>
          </div>
        </div>

        <div>
          <div class="code-window">
            <div class="code-window-header">
              <span class="preview-dot"></span><span class="preview-dot"></span><span class="preview-dot"></span>
              <span class="code-window-title">Terminal</span>
            </div>
<pre><span class="com"># Clone &amp; spin up</span>
<span class="kw">git</span> clone https://github.com/H4STUR/agares-cms
<span class="kw">cd</span> agares-cms
<span class="kw">docker-compose</span> up -d

<span class="com"># Install &amp; seed</span>
<span class="kw">docker</span> exec agares composer install
<span class="kw">docker</span> exec agares <span class="kw">php</span> artisan migrate --seed

<span class="com"># Open the admin</span>
<span class="fn">open</span> http://localhost:8006/admin</pre>
          </div>
        </div>
      </div>
    </div>
  </section>

  {{-- ============ DOCS HUB GRID ============ --}}
  <section>
    <div class="container-wide">
      <div class="section-header">
        <span class="eyebrow">Pick your path</span>
        <h2>Six sections,<br><span class="text-gradient">every angle covered</span>.</h2>
        <p>The same docs power our internal onboarding. If something's missing, open an issue — we'll add it.</p>
      </div>

      <div class="docs-hub">

        <div class="docs-section cyan reveal">
          <div class="docs-section-ico">
            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="22 12 18 12 15 21 9 3 6 12 2 12"/></svg>
          </div>
          <h3>Getting started</h3>
          <p>From <code style="font-family:var(--font-mono);color:#67e8f9;">git clone</code> to your first published article.</p>
          <ul class="docs-link-list">
            <li><a href="#">Installation &amp; Docker setup <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M5 12h14M13 6l6 6-6 6"/></svg></a></li>
            <li><a href="#">Configuration &amp; .env <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M5 12h14M13 6l6 6-6 6"/></svg></a></li>
            <li><a href="#">Creating your first site <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M5 12h14M13 6l6 6-6 6"/></svg></a></li>
            <li><a href="#">Your first Blade template <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M5 12h14M13 6l6 6-6 6"/></svg></a></li>
          </ul>
        </div>

        <div class="docs-section reveal">
          <div class="docs-section-ico">
            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="7" height="7" rx="1"/><rect x="14" y="3" width="7" height="7" rx="1"/><rect x="14" y="14" width="7" height="7" rx="1"/><rect x="3" y="14" width="7" height="7" rx="1"/></svg>
          </div>
          <h3>Architecture</h3>
          <p>Sites → Categories → Articles. Polymorphic custom fields. The whole mental model.</p>
          <ul class="docs-link-list">
            <li><a href="#">Content hierarchy &amp; status states <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M5 12h14M13 6l6 6-6 6"/></svg></a></li>
            <li><a href="#">The Input System (custom fields) <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M5 12h14M13 6l6 6-6 6"/></svg></a></li>
            <li><a href="#">Feature flags &amp; EnsureSetting middleware <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M5 12h14M13 6l6 6-6 6"/></svg></a></li>
            <li><a href="#">Helpers: input_value(), safe_html() <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M5 12h14M13 6l6 6-6 6"/></svg></a></li>
          </ul>
        </div>

        <div class="docs-section green reveal">
          <div class="docs-section-ico">
            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="16 18 22 12 16 6"/><polyline points="8 6 2 12 8 18"/><line x1="14" y1="4" x2="10" y2="20"/></svg>
          </div>
          <h3>REST API</h3>
          <p>Scoped keys, ability scopes, every endpoint, every error code.</p>
          <ul class="docs-link-list">
            <li><a href="/api">Quickstart &amp; key issuing <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M5 12h14M13 6l6 6-6 6"/></svg></a></li>
            <li><a href="#">Ability scopes reference <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M5 12h14M13 6l6 6-6 6"/></svg></a></li>
            <li><a href="#">Endpoint catalog — v1 <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M5 12h14M13 6l6 6-6 6"/></svg></a></li>
            <li><a href="#">Rate limits &amp; pagination <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M5 12h14M13 6l6 6-6 6"/></svg></a></li>
          </ul>
        </div>

        <div class="docs-section amber reveal">
          <div class="docs-section-ico">
            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="9" cy="21" r="1"/><circle cx="20" cy="21" r="1"/><path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"/></svg>
          </div>
          <h3>Modules</h3>
          <p>Toggle on per project: ecommerce, newsletter, RBAC, 2FA, cookies, forms.</p>
          <ul class="docs-link-list">
            <li><a href="#">Ecommerce: products, orders, payments <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M5 12h14M13 6l6 6-6 6"/></svg></a></li>
            <li><a href="#">Newsletter: lists, campaigns, webhooks <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M5 12h14M13 6l6 6-6 6"/></svg></a></li>
            <li><a href="#">Forms: builder &amp; submission storage <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M5 12h14M13 6l6 6-6 6"/></svg></a></li>
            <li><a href="#">Cookies: scanner &amp; consent UI <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M5 12h14M13 6l6 6-6 6"/></svg></a></li>
          </ul>
        </div>

        <div class="docs-section pink reveal">
          <div class="docs-section-ico">
            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
          </div>
          <h3>Security &amp; RBAC</h3>
          <p>Roles, permissions, 2FA flows, audit log, and the defense-in-depth doctrine.</p>
          <ul class="docs-link-list">
            <li><a href="/security">Architecture overview <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M5 12h14M13 6l6 6-6 6"/></svg></a></li>
            <li><a href="#">Spatie permissions setup <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M5 12h14M13 6l6 6-6 6"/></svg></a></li>
            <li><a href="#">2FA enrolment &amp; recovery <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M5 12h14M13 6l6 6-6 6"/></svg></a></li>
            <li><a href="#">Reading the security audit log <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M5 12h14M13 6l6 6-6 6"/></svg></a></li>
          </ul>
        </div>

        <div class="docs-section reveal">
          <div class="docs-section-ico">
            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"/></svg>
          </div>
          <h3>Deployment</h3>
          <p>GitHub Actions to Cyber-Folks SFTP. Or roll your own — it's just Laravel.</p>
          <ul class="docs-link-list">
            <li><a href="#">CI/CD with GitHub Actions <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M5 12h14M13 6l6 6-6 6"/></svg></a></li>
            <li><a href="#">Cyber-Folks first-time setup <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M5 12h14M13 6l6 6-6 6"/></svg></a></li>
            <li><a href="#">Database migrations on deploy <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M5 12h14M13 6l6 6-6 6"/></svg></a></li>
            <li><a href="#">Zero-downtime rollouts <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M5 12h14M13 6l6 6-6 6"/></svg></a></li>
          </ul>
        </div>
      </div>
    </div>
  </section>

  {{-- ============ STATUS / VERSIONS ============ --}}
  <section>
    <div class="container-wide">
      <div class="section-header">
        <span class="eyebrow">Current state of play</span>
        <h2>Live versions, live status,<br><span class="text-gradient">no surprises</span>.</h2>
      </div>

      <div class="status-grid">
        <div class="status-card reveal">
          <span class="pulse green"></span>
          <div>
            <strong>Docs build · v2.0</strong>
            <span>Updated 2026-05-18 — current shipping release</span>
          </div>
        </div>
        <div class="status-card reveal">
          <span class="pulse green"></span>
          <div>
            <strong>Demo · operational</strong>
            <span>demo.agares.co.uk — all modules enabled</span>
          </div>
        </div>
        <div class="status-card reveal">
          <span class="pulse green"></span>
          <div>
            <strong>API v1 · stable</strong>
            <span>Backwards-compatible additions only</span>
          </div>
        </div>
      </div>
    </div>
  </section>

  {{-- ============ STILL CONFUSED CTA ============ --}}
  <section>
    <div class="container">
      <div class="cta-banner reveal">
        <span class="badge badge-warning mb-md">Still stuck?</span>
        <h2>Docs aren't always enough.<br>That's <span class="text-gradient">what we're for</span>.</h2>
        <p>Open an issue on GitHub, or just email us. We treat documentation gaps as bugs — bring us yours.</p>
        <div class="hero-buttons">
          <a href="https://github.com/H4STUR" target="_blank" rel="noopener" class="btn btn-primary btn-lg btn-icon-after">
            Open a GitHub issue
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"/><polyline points="15 3 21 3 21 9"/><line x1="10" y1="14" x2="21" y2="3"/></svg>
          </a>
          <a href="/contact" class="btn btn-secondary btn-lg">Email us</a>
        </div>
      </div>
    </div>
  </section>

@stop
