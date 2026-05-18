@extends('pages.frontend.base')

@push('styles')
<style>
  /* ============ Multi-site page extras ============ */

  /* Big visual contrast: them (typical CMS) vs us (Agares) */
  .contrast {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: var(--space-xl);
    align-items: stretch;
  }
  @media (max-width: 900px) { .contrast { grid-template-columns: 1fr; } }

  .contrast-card {
    padding: clamp(1.75rem, 3vw, 2.5rem);
    border-radius: var(--radius-2xl);
    border: 1px solid var(--color-border);
    position: relative;
    overflow: hidden;
    display: flex; flex-direction: column;
  }
  .contrast.them {
    background: linear-gradient(180deg, rgba(248, 113, 113, 0.04), transparent 70%);
  }
  .contrast.us {
    background:
      radial-gradient(ellipse 70% 50% at 50% 0%, rgba(139, 92, 246, 0.14), transparent 60%),
      linear-gradient(180deg, rgba(255, 255, 255, 0.04), rgba(255, 255, 255, 0.015));
    border-color: rgba(139, 92, 246, 0.35);
    box-shadow: 0 30px 80px -25px rgba(139, 92, 246, 0.35);
  }
  .contrast-eyebrow {
    display: inline-flex; align-items: center; gap: 0.5rem;
    padding: 0.3rem 0.85rem 0.3rem 0.45rem;
    border-radius: var(--radius-full);
    font-family: var(--font-mono);
    font-size: 0.7rem;
    text-transform: uppercase;
    letter-spacing: 0.1em;
    margin-bottom: var(--space-md);
  }
  .them .contrast-eyebrow { background: rgba(248, 113, 113, 0.10); border: 1px solid rgba(248, 113, 113, 0.3); color: #fca5a5; }
  .us   .contrast-eyebrow { background: rgba(139, 92, 246, 0.12); border: 1px solid rgba(139, 92, 246, 0.3); color: #c4b5fd; }
  .contrast-eyebrow .dot { width: 7px; height: 7px; border-radius: 50%; }
  .them .contrast-eyebrow .dot { background: #f87171; }
  .us   .contrast-eyebrow .dot { background: #8b5cf6; box-shadow: 0 0 10px rgba(139, 92, 246, 0.7); }

  .contrast-card h3 {
    font-family: var(--font-display);
    font-size: var(--text-2xl);
    margin-bottom: var(--space-md);
    letter-spacing: -0.025em;
  }
  .contrast-list { list-style: none; padding: 0; margin: 0; flex: 1; }
  .contrast-list li {
    display: flex; gap: 0.65rem; align-items: flex-start;
    padding: 0.55rem 0;
    font-size: 0.9rem;
    color: var(--color-text-secondary);
    line-height: 1.55;
  }
  .contrast-list li svg { flex-shrink: 0; margin-top: 3px; }
  .them .contrast-list li svg { color: #fca5a5; }
  .us   .contrast-list li svg { color: var(--color-accent-green); }
  .them .contrast-list li strong { color: var(--color-text-primary); font-weight: 600; }
  .us   .contrast-list li strong { color: var(--color-text-primary); font-weight: 600; }

  /* Architecture diagram (CSS-only) */
  .arch {
    display: grid;
    grid-template-columns: 1fr;
    gap: var(--space-md);
    padding: clamp(1.5rem, 3vw, 2.5rem);
    background: var(--color-bg-code);
    border: 1px solid var(--color-border-hover);
    border-radius: var(--radius-2xl);
    box-shadow: var(--shadow-xl);
  }

  .arch-tier {
    padding: var(--space-md) var(--space-lg);
    border-radius: var(--radius-md);
    border: 1px solid var(--color-border);
    text-align: center;
    transition: all var(--transition-base);
  }
  .arch-tier-label {
    font-family: var(--font-mono);
    font-size: 0.65rem;
    text-transform: uppercase;
    letter-spacing: 0.12em;
    color: var(--color-text-tertiary);
    margin-bottom: 0.3rem;
  }
  .arch-tier-name {
    font-family: var(--font-display);
    font-size: 1rem;
    font-weight: 600;
    color: var(--color-text-primary);
    letter-spacing: -0.01em;
  }
  .arch-tier.install {
    background: linear-gradient(135deg, rgba(139, 92, 246, 0.14), rgba(34, 211, 238, 0.06));
    border-color: rgba(139, 92, 246, 0.3);
  }
  .arch-tier.install .arch-tier-name {
    background: var(--color-accent-gradient);
    -webkit-background-clip: text; background-clip: text; -webkit-text-fill-color: transparent;
  }
  .arch-sites {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 0.6rem;
  }
  @media (max-width: 700px) { .arch-sites { grid-template-columns: repeat(2, 1fr); } }
  .arch-site {
    padding: var(--space-md);
    background: var(--color-surface);
    border: 1px solid var(--color-border-hover);
    border-radius: var(--radius-md);
    text-align: center;
    transition: all var(--transition-base);
  }
  .arch-site:hover { border-color: var(--color-border-strong); transform: translateY(-2px); }
  .arch-site-domain {
    font-family: var(--font-mono);
    font-size: 0.78rem;
    color: var(--color-accent-secondary);
    margin-bottom: 0.4rem;
    word-break: break-all;
  }
  .arch-site-meta {
    font-size: 0.72rem;
    color: var(--color-text-tertiary);
    line-height: 1.4;
  }
  .arch-line {
    text-align: center;
    color: var(--color-text-tertiary);
    font-family: var(--font-mono);
    font-size: 0.75rem;
    padding: 0.2rem 0;
  }
  .arch-line::before, .arch-line::after { content: '│'; display: block; line-height: 1; }
  .arch-line span { display: block; padding: 0.2rem 0; }

  /* Power-features grid */
  .ms-features {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: var(--space-lg);
  }
  @media (max-width: 1000px) { .ms-features { grid-template-columns: repeat(2, 1fr); } }
  @media (max-width: 600px)  { .ms-features { grid-template-columns: 1fr; } }

  .ms-feature {
    padding: var(--space-xl);
    background: var(--color-surface);
    border: 1px solid var(--color-border);
    border-radius: var(--radius-lg);
    transition: all var(--transition-base);
  }
  .ms-feature:hover { border-color: var(--color-border-hover); transform: translateY(-2px); }
  .ms-feature-ico {
    width: 44px; height: 44px;
    display: inline-flex; align-items: center; justify-content: center;
    background: rgba(139, 92, 246, 0.12);
    border: 1px solid rgba(139, 92, 246, 0.3);
    color: #c4b5fd;
    border-radius: var(--radius-md);
    margin-bottom: var(--space-md);
  }
  .ms-feature h4 { font-family: var(--font-display); font-size: 1.1rem; margin-bottom: 0.4rem; letter-spacing: -0.015em; }
  .ms-feature p { font-size: 0.88rem; color: var(--color-text-secondary); margin: 0; line-height: 1.65; }
</style>
@endpush

@section('content')

  {{-- ============ HERO ============ --}}
  <section class="hero">
    <div class="container">
      <div class="hero-eyebrow">
        <span class="pill">MULTI-SITE</span>
        <span>One install · every client · zero juggling</span>
      </div>

      <h1 class="hero-title">
        Stop running one CMS per client.<br>
        <span class="text-gradient-magic">Run one for all of them.</span>
      </h1>

      <p class="hero-subtitle">
        Agares is multi-site from the kernel up. Sites → Categories → Articles, with
        per-site permissions, per-site menus, per-site media reuse, per-site domains.
        One Laravel install, one database, every client.
      </p>

      <div class="hero-buttons">
        <button type="button" class="btn btn-primary btn-lg btn-icon-after" data-demo-open>
          See sites in the admin
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14M13 6l6 6-6 6"/></svg>
        </button>
        <a href="/pricing" class="btn btn-secondary btn-lg">
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 11.5a8.38 8.38 0 0 1-.9 3.8 8.5 8.5 0 0 1-7.6 4.7 8.38 8.38 0 0 1-3.8-.9L3 21l1.9-5.7a8.38 8.38 0 0 1-.9-3.8 8.5 8.5 0 0 1 4.7-7.6 8.38 8.38 0 0 1 3.8-.9h.5a8.48 8.48 0 0 1 8 8v.5z"/></svg>
          See the pricing
        </a>
      </div>

      <div class="hero-stats">
        <div class="hero-stat"><div class="num">∞</div><div class="label">Sites per install</div></div>
        <div class="hero-stat"><div class="num">1</div><div class="label">Database to back up</div></div>
        <div class="hero-stat"><div class="num">0</div><div class="label">Plugin licenses to renew</div></div>
        <div class="hero-stat"><div class="num">100%</div><div class="label">Per-site RBAC</div></div>
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
            <span class="preview-url">agares.app/admin/sites</span>
          </div>
          <div class="preview-content">
            <img src="{{ asset('assets/frontend/images/agares_cms_sites.jpg') }}" alt="Agares sites manager showing every client site in one place" loading="eager" fetchpriority="high">
          </div>
        </div>
      </div>
    </div>
  </section>

  {{-- ============ THEM vs US ============ --}}
  <section style="padding-top: var(--space-2xl);">
    <div class="container-wide">
      <div class="section-header">
        <span class="eyebrow">The agency tax</span>
        <h2>The old way costs you<br><span class="text-gradient">an hour per client per week</span>.</h2>
        <p>If you've ever managed five WordPress installs, you know exactly what we mean.</p>
      </div>

      <div class="contrast">
        <div class="contrast-card them reveal">
          <div class="contrast-eyebrow"><span class="dot"></span>The typical CMS</div>
          <h3>One install per client</h3>
          <ul class="contrast-list">
            <li><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg> <span><strong>N databases</strong> to back up, monitor, upgrade.</span></li>
            <li><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg> <span><strong>N plugin licenses</strong> to renew, audit, patch.</span></li>
            <li><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg> <span><strong>N user accounts per editor</strong> who works on more than one site.</span></li>
            <li><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg> <span><strong>N times the media library</strong>, with no reuse across clients.</span></li>
            <li><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg> <span><strong>Cross-client features</strong> need cross-database hacks or a SaaS in between.</span></li>
            <li><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg> <span>Onboarding a new client = <strong>a fresh install</strong>, not a button click.</span></li>
          </ul>
        </div>

        <div class="contrast-card us reveal">
          <div class="contrast-eyebrow"><span class="dot"></span>Agares</div>
          <h3>One install, every client</h3>
          <ul class="contrast-list">
            <li><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg> <span><strong>One database</strong>. One backup script. One upgrade path.</span></li>
            <li><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg> <span><strong>Zero plugin licenses</strong>. Every module is in the core, gated by a setting.</span></li>
            <li><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg> <span><strong>One account</strong> per editor — site-scoped permissions decide what they can touch.</span></li>
            <li><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg> <span><strong>One media library</strong>. Reuse hero images across every client. MIME-allowlist enforced.</span></li>
            <li><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg> <span><strong>Cross-client features</strong> just work — one Eloquent query covers every site.</span></li>
            <li><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg> <span>Onboarding a new client = <strong>"Add New Site" button</strong>. Two minutes to a working admin.</span></li>
          </ul>
        </div>
      </div>
    </div>
  </section>

  {{-- ============ ARCHITECTURE DIAGRAM ============ --}}
  <section>
    <div class="container">
      <div class="section-header">
        <span class="eyebrow">The shape of it</span>
        <h2>One Laravel install.<br><span class="text-gradient">N sites, each their own domain</span>.</h2>
        <p>Every site has its own slug, its own template, its own menu, its own articles. They all share the same code, the same admin, the same one media library.</p>
      </div>

      <div class="arch">
        <div class="arch-tier install">
          <div class="arch-tier-label">Single deploy</div>
          <div class="arch-tier-name">agares-cms (Laravel app + 1 MySQL database)</div>
        </div>

        <div class="arch-line"><span>↓ <code style="font-family:var(--font-mono);color:#67e8f9;">/{site:slug}</code> routing dispatches to per-site templates ↓</span></div>

        <div class="arch-sites">
          <div class="arch-site">
            <div class="arch-site-domain">piescimordelizal.pl</div>
            <div class="arch-site-meta">Hospitality<br>5 sections · live</div>
          </div>
          <div class="arch-site">
            <div class="arch-site-domain">studio-x.eu</div>
            <div class="arch-site-meta">Creative agency<br>blog + portfolio</div>
          </div>
          <div class="arch-site">
            <div class="arch-site-domain">shop.boutique.pl</div>
            <div class="arch-site-meta">Ecommerce<br>4 gateways · live</div>
          </div>
          <div class="arch-site">
            <div class="arch-site-domain">+ N more</div>
            <div class="arch-site-meta">Add a new site<br>in &lt; 2 minutes</div>
          </div>
        </div>

        <div class="arch-line"><span>↑ One admin · per-site permissions · shared media · shared 2FA · shared audit log ↑</span></div>
      </div>

      <p style="text-align: center; margin-top: var(--space-md); font-family: var(--font-mono); font-size: 0.78rem; color: var(--color-text-tertiary);">
        Production pattern: domains alias to <code style="color:#c4b5fd;">/{site:slug}</code> via DNS + nginx server_name. No code branching per site.
      </p>
    </div>
  </section>

  {{-- ============ POWER FEATURES ============ --}}
  <section>
    <div class="container-wide">
      <div class="section-header">
        <span class="eyebrow">What multi-site unlocks</span>
        <h2>Powers you didn't know<br><span class="text-gradient">you were missing</span>.</h2>
      </div>

      <div class="ms-features">
        @foreach([
          ['M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z', 'Site-scoped permissions', 'A user can be admin on Client A and editor on Client B. The <code style="font-family:var(--font-mono);font-size:0.85em;color:#67e8f9;">RoleSitePermission</code> model handles per-site grants — same Spatie RBAC, finer scope.'],
          ['M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z M3.27 6.96 12 12.01 20.73 6.96 M12 22.08V12', 'Per-site templates', 'Same content model, different visual treatment. Each site picks its <code style="font-family:var(--font-mono);font-size:0.85em;color:#67e8f9;">template</code> field — Blade view files swap, content stays.'],
          ['M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2zM22 6l-10 7L2 6', 'Per-site SEO', 'Each site has its own <code style="font-family:var(--font-mono);font-size:0.85em;color:#67e8f9;">meta_title</code>, <code style="font-family:var(--font-mono);font-size:0.85em;color:#67e8f9;">og_image</code>, robots policy. Cascade to category and article level.'],
          ['M22 12c0 5.52-4.48 10-10 10S2 17.52 2 12 6.48 2 12 2s10 4.48 10 10z', 'Per-site domains', 'Map any number of domains via DNS + nginx <code style="font-family:var(--font-mono);font-size:0.85em;color:#67e8f9;">server_name</code>. The CMS resolves them to a site slug — no per-site code.'],
          ['M9 11l3 3L22 4 M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11', 'Shared media library', 'Upload once. Reuse across every site. The library knows which sites are using each asset.'],
          ['M12 2L2 7l10 5 10-5-10-5z M2 17l10 5 10-5 M2 12l10 5 10-5', 'Per-site feature flags', 'Toggle ecommerce on for Site A, off for Site B. <code style="font-family:var(--font-mono);font-size:0.85em;color:#67e8f9;">EnsureSetting</code> middleware enforces it on every route.'],
          ['M16 18l6-6-6-6 M8 6l-6 6 6 6 M14 4l-4 16', 'API key per site', 'Issue an API key scoped to a single site. The frontend you build for that client only sees its own content.'],
          ['M12 8v4l3 3 M22 12c0 5.52-4.48 10-10 10S2 17.52 2 12 6.48 2 12 2', 'Site-level scheduling', 'Different time zones, different publish windows. Each site\'s scheduler runs independently against the shared queue.'],
          ['M3 3h18v18H3z M3 9h18 M9 21V9', 'One admin, every site', 'The site switcher in the top bar swaps the entire admin context. No re-login, no tab-juggling, no "wait which client am I editing?".'],
        ] as $f)
          <div class="ms-feature reveal">
            <div class="ms-feature-ico">
              <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="{{ $f[0] }}"/></svg>
            </div>
            <h4>{!! $f[1] !!}</h4>
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
        <span class="badge badge-primary mb-md">Agencies love this</span>
        <h2>How many client sites<br>do you run <span class="text-gradient">right now</span>?</h2>
        <p>Whatever the number, you could be running them from one tab. Hop into the demo and see what your week could look like.</p>
        <div class="hero-buttons">
          <button type="button" class="btn btn-primary btn-lg btn-icon-after" data-demo-open>
            Open the sites manager
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14M13 6l6 6-6 6"/></svg>
          </button>
          <a href="/contact" class="btn btn-secondary btn-lg">Talk to us</a>
        </div>
      </div>
    </div>
  </section>

@stop
