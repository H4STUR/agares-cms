@extends('pages.frontend.base')

@php
  // CMS-overridable fields with strong fallbacks for the demo
  $heroTitleRaw = $data['header']->value ?? null;
  $heroSubtitle = $data['content']->value ?? null;
  $heroImg = $data['preview_img'] ?? null;
  $heroImgSrc = ($heroImg && $heroImg->files && $heroImg->files->count())
      ? asset($heroImg->files->first()->file_path)
      : asset('assets/frontend/images/agares_cms_dashboard.jpg');
@endphp

@section('content')

  {{-- ============ HERO ============ --}}
  <section class="hero">
    <div class="container">
      <div class="hero-eyebrow">
        <span class="pill">NEW</span>
        <span>v2.0 · Two-factor auth, newsletter &amp; full ecommerce shipped</span>
      </div>

      @if($heroTitleRaw)
        <h1 class="hero-title">{{ $heroTitleRaw }}</h1>
      @else
        <h1 class="hero-title">
          Ship every site<br>
          from <span class="text-gradient-magic">one&nbsp;dashboard</span>.
        </h1>
      @endif

      <p class="hero-subtitle">
        @if($heroSubtitle)
          {!! safe_html($heroSubtitle) !!}
        @else
          Agares is the multi-site CMS for developers and agencies who'd rather build
          than fight their tools. Sites, blog, ecommerce, RBAC, REST API — all in one
          Laravel app you actually own.
        @endif
      </p>

      <div class="hero-buttons">
        <button type="button" class="btn btn-primary btn-lg btn-icon-after" data-demo-open>
          Try the Demo
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
            <path d="M5 12h14M13 6l6 6-6 6"/>
          </svg>
        </button>
        <a href="/features" class="btn btn-secondary btn-lg">
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
            <polygon points="5 3 19 12 5 21 5 3"/>
          </svg>
          See features
        </a>
      </div>

      <div class="hero-stats">
        <div class="hero-stat">
          <div class="num">9+</div>
          <div class="label">Core modules</div>
        </div>
        <div class="hero-stat">
          <div class="num">4</div>
          <div class="label">Payment gateways</div>
        </div>
        <div class="hero-stat">
          <div class="num">∞</div>
          <div class="label">Sites per install</div>
        </div>
        <div class="hero-stat">
          <div class="num">100%</div>
          <div class="label">Yours to host</div>
        </div>
      </div>
    </div>

    <div class="container-wide">
      <div class="hero-showcase">
        <div class="hero-showcase-glow" aria-hidden="true"></div>
        <div class="dashboard-preview tilt">
          <div class="preview-bar">
            <span class="preview-dot"></span>
            <span class="preview-dot"></span>
            <span class="preview-dot"></span>
            <span class="preview-url">agares.app/admin/dashboard</span>
          </div>
          <div class="preview-content">
            <img src="{{ $heroImgSrc }}" alt="Agares CMS admin dashboard showing analytics, traffic timeline, and recent content" loading="eager" fetchpriority="high">
          </div>
        </div>
      </div>
    </div>
  </section>

  {{-- ============ INTEGRATION MARQUEE ============ --}}
  <section class="section-sm" style="padding-top: var(--space-2xl); padding-bottom: var(--space-2xl);">
    <div class="container">
      <p style="text-align: center; font-family: var(--font-mono); font-size: var(--text-xs); color: var(--color-text-tertiary); text-transform: uppercase; letter-spacing: 0.15em; margin-bottom: var(--space-xl);">
        Built on a stack you can trust
      </p>
      @php
        $stack = [
          'Laravel 13', 'PHP 8.3+', 'MySQL 8', 'Spatie Permissions', 'Vite 5',
          'Tailwind 3', 'Alpine.js', 'Bootstrap 5', 'Monaco Editor',
          'Stripe', 'PayU', 'Przelewy24', 'PayPal',
          'Google OAuth', 'Facebook OAuth', 'GA4 Analytics',
        ];
      @endphp
      <div class="marquee" aria-hidden="true">
        <div class="marquee-track">
          @foreach($stack as $tech)
            <span class="marquee-item">
              <span class="marquee-dot"></span>
              {{ $tech }}
            </span>
          @endforeach
          {{-- Duplicate for seamless loop --}}
          @foreach($stack as $tech)
            <span class="marquee-item">
              <span class="marquee-dot"></span>
              {{ $tech }}
            </span>
          @endforeach
        </div>
      </div>
    </div>
  </section>

  {{-- ============ BENTO MODULES ============ --}}
  <section>
    <div class="container-wide">
      <div class="section-header">
        <span class="eyebrow">The Modules</span>
        <h2>Everything you need, <span class="text-gradient">none of what you don't</span>.</h2>
        <p>Nine production-grade modules. Toggle them on per project — every flag is honoured in the routing, the sidebar, and the API surface.</p>
      </div>

      {{-- HERO BENTO: Multi-site with screenshot --}}
      <div class="bento">
        <div class="bento-hero">
          <div class="bento-hero-text">
            <div class="bento-icon">
              <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <rect x="3" y="3" width="7" height="7" rx="1"/>
                <rect x="14" y="3" width="7" height="7" rx="1"/>
                <rect x="14" y="14" width="7" height="7" rx="1"/>
                <rect x="3" y="14" width="7" height="7" rx="1"/>
              </svg>
            </div>
            <h3>Multi-site, one brain.</h3>
            <p>Run every client site from a single Laravel install. Sites → Categories → Articles, with soft deletes, scheduling, drafts and per-site permissions baked in.</p>
            <div class="bento-hero-pills">
              <span class="badge badge-primary">Unlimited sites</span>
              <span class="badge badge-cyan">Per-site RBAC</span>
              <span class="badge badge-success">Soft deletes</span>
              <span class="badge badge-primary">Scheduled publishing</span>
            </div>
          </div>
          <div class="bento-hero-visual">
            <img src="{{ asset('assets/frontend/images/agares_cms_sites.jpg') }}" alt="Agares CMS sites management showing four sites with publish status and quick actions" loading="lazy">
          </div>
        </div>

        {{-- 3-COL GRID of feature cards --}}
        <div class="bento-grid">
          {{-- Ecommerce --}}
          <div class="bento-item" style="background-image: linear-gradient(180deg, rgba(244, 114, 182, 0.10), transparent 70%);">
            <div class="bento-icon" style="background: rgba(244, 114, 182, 0.12); border-color: rgba(244, 114, 182, 0.3); color: #f9a8d4;">
              <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <circle cx="9" cy="21" r="1"/><circle cx="20" cy="21" r="1"/>
                <path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"/>
              </svg>
            </div>
            <h3>Ecommerce that just&nbsp;works</h3>
            <p style="margin-bottom: var(--space-md);">Products, variants, coupons, tax, shipping, full order lifecycle.</p>
            <div style="display: flex; flex-wrap: wrap; gap: 0.35rem; margin-top: auto;">
              <span class="badge badge-pink">Stripe</span>
              <span class="badge badge-pink">PayU</span>
              <span class="badge badge-pink">P24</span>
              <span class="badge badge-pink">PayPal</span>
              <span class="badge badge-pink">COD</span>
            </div>
          </div>

          {{-- Custom fields --}}
          <div class="bento-item">
            <div class="bento-icon">
              <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"/>
                <polyline points="3.27 6.96 12 12.01 20.73 6.96"/>
                <line x1="12" y1="22.08" x2="12" y2="12"/>
              </svg>
            </div>
            <h3>Polymorphic custom fields</h3>
            <p>Attach text, rich text, gallery, file or form to any Site, Category or Article. Reusable templates, no schema migrations.</p>
          </div>

          {{-- Page editor --}}
          <div class="bento-item" style="background-image: linear-gradient(135deg, rgba(34, 211, 238, 0.08), transparent 70%);">
            <div class="bento-icon" style="background: rgba(34, 211, 238, 0.12); border-color: rgba(34, 211, 238, 0.3); color: var(--color-accent-secondary);">
              <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <rect x="3" y="3" width="18" height="18" rx="2"/>
                <path d="M3 9h18M9 21V9"/>
              </svg>
            </div>
            <h3>Page editor + Blade</h3>
            <p>Visual editor for the team, raw Blade for you. Monaco-powered code injection — CSS, JS, scripts — gated by RBAC.</p>
          </div>

          {{-- RBAC --}}
          <div class="bento-item">
            <div class="bento-icon">
              <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>
                <path d="M9 12l2 2 4-4"/>
              </svg>
            </div>
            <h3>Roles, 2FA &amp; audit&nbsp;log</h3>
            <p>Spatie RBAC with site-scoped grants. TOTP + email OTP. OAuth coverage. Queryable security audit log.</p>
          </div>

          {{-- API --}}
          <div class="bento-item">
            <div class="bento-icon">
              <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <polyline points="16 18 22 12 16 6"/>
                <polyline points="8 6 2 12 8 18"/>
                <line x1="14" y1="4" x2="10" y2="20"/>
              </svg>
            </div>
            <h3>REST API + scoped keys</h3>
            <p>Versioned API with <code style="color:#67e8f9; font-family:var(--font-mono); font-size:0.85em;">X-API-Key</code> auth, scoped abilities and per-route rate limits. Headless-ready.</p>
          </div>

          {{-- Newsletter --}}
          <div class="bento-item">
            <div class="bento-icon" style="background: rgba(52, 211, 153, 0.12); border-color: rgba(52, 211, 153, 0.3); color: var(--color-accent-green);">
              <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/>
                <polyline points="22,6 12,13 2,6"/>
              </svg>
            </div>
            <h3>Newsletter engine</h3>
            <p>Subscribers, lists, templates, campaigns. GDPR consent capture, double opt-in, webhook delivery sync.</p>
          </div>

          {{-- Cookies --}}
          <div class="bento-item">
            <div class="bento-icon">
              <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M21.5 12a9.5 9.5 0 1 1-9.5-9.5 4 4 0 0 0 4 4 4 4 0 0 0 4 4 4 4 0 0 0 1.5 1.5z"/>
                <circle cx="8" cy="10" r="0.8"/><circle cx="13" cy="14" r="0.8"/><circle cx="9" cy="16" r="0.8"/>
              </svg>
            </div>
            <h3>GDPR cookie consent</h3>
            <p>Live cookie scanner, granular consent UI, third-party script gating, consent audit log. Compliant out of the box.</p>
          </div>

          {{-- Media --}}
          <div class="bento-item">
            <div class="bento-icon" style="background: rgba(251, 191, 36, 0.12); border-color: rgba(251, 191, 36, 0.3); color: var(--color-accent-amber);">
              <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <rect x="3" y="3" width="18" height="18" rx="2" ry="2"/>
                <circle cx="8.5" cy="8.5" r="1.5"/>
                <polyline points="21 15 16 10 5 21"/>
              </svg>
            </div>
            <h3>Global media library</h3>
            <p>One library, every site. Galleries, smart filters, MIME allowlist, signed uploads. Reuse media across projects.</p>
          </div>

          {{-- Forms --}}
          <div class="bento-item">
            <div class="bento-icon">
              <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M9 11l3 3L22 4"/>
                <path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"/>
              </svg>
            </div>
            <h3>Forms &amp; submissions</h3>
            <p>Drag-build any form, embed it on any page. Submissions land in the admin, email notifications optional.</p>
          </div>
        </div>
      </div>
    </div>
  </section>

  {{-- ============ SECURITY SPLIT ============ --}}
  <section style="padding-top: var(--space-3xl);">
    <div class="container-wide">
      <div class="split">
        <div>
          <span class="eyebrow">Security &amp; compliance</span>
          <h2 style="margin-bottom: var(--space-md);">Two-factor auth.<br>Row-level&nbsp;permissions.<br>A real audit log.</h2>
          <p style="font-size: var(--text-lg); color: var(--color-text-secondary); margin-bottom: var(--space-xl);">
            Agares ships with TOTP + email OTP + OAuth coverage, a Spatie-powered RBAC matrix
            with site-scoped grants, and a queryable <code style="color:#67e8f9; font-family:var(--font-mono);">security_audit_log</code> table.
            No bolt-ons, no third-party SaaS, no extra invoice.
          </p>

          <div style="display: grid; gap: var(--space-md); margin-bottom: var(--space-xl);">
            @foreach([
              ['TOTP (Google Authenticator, Authy, 1Password, Bitwarden)', 'shield'],
              ['Email-OTP fallback + 8× single-use recovery codes', 'mail'],
              ['Force-2FA per role; admin-reset with audit trail', 'lock'],
              ['Encrypted at rest — secrets &amp; recovery codes both', 'key'],
            ] as $row)
              <div style="display: flex; gap: 0.85rem; align-items: flex-start;">
                <span style="flex-shrink: 0; width: 24px; height: 24px; display: inline-flex; align-items: center; justify-content: center; background: rgba(52, 211, 153, 0.12); border: 1px solid rgba(52, 211, 153, 0.3); border-radius: 50%; color: var(--color-accent-green);">
                  <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
                </span>
                <span style="color: var(--color-text-secondary); line-height: 1.5;">{!! $row[0] !!}</span>
              </div>
            @endforeach
          </div>

          <a href="/security" class="btn btn-secondary btn-icon-after">
            Read the security architecture
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
              <path d="M5 12h14M13 6l6 6-6 6"/>
            </svg>
          </a>
        </div>

        <div>
          <div class="split-image tilt">
            <img src="{{ asset('assets/frontend/images/agares_cms_permissions.jpg') }}" alt="Agares permission matrix — sites, roles, granular abilities" loading="lazy">
          </div>
        </div>
      </div>
    </div>
  </section>

  {{-- ============ CODE WINDOW — API ============ --}}
  <section>
    <div class="container-wide">
      <div class="split reverse">
        <div>
          <div class="code-window">
            <div class="code-window-header">
              <span class="preview-dot"></span>
              <span class="preview-dot"></span>
              <span class="preview-dot"></span>
              <span class="code-window-title">GET /api/v1/articles</span>
            </div>
<pre><span class="com"># Fetch published articles from any site, with scoped API key</span>
<span class="kw">curl</span> https://demo.agares.co.uk/api/v1/articles?site=lookbook \
  <span class="punct">-H</span> <span class="str">"X-API-Key: ak_live_a9f4..._public"</span>

<span class="punct">{</span>
  <span class="str">"data"</span>: <span class="punct">[</span>
    <span class="punct">{</span>
      <span class="str">"id"</span>: <span class="num">42</span>,
      <span class="str">"title"</span>: <span class="str">"Launching the SS26 collection"</span>,
      <span class="str">"slug"</span>: <span class="str">"ss26-launch"</span>,
      <span class="str">"site"</span>: <span class="str">"lookbook"</span>,
      <span class="str">"status"</span>: <span class="str">"published"</span>,
      <span class="str">"published_at"</span>: <span class="str">"2026-05-17T09:00:00Z"</span>,
      <span class="str">"fields"</span>: <span class="punct">{</span>
        <span class="str">"hero_image"</span>: <span class="str">"/media/ss26.jpg"</span>,
        <span class="str">"author"</span>: <span class="str">"Editorial Team"</span>
      <span class="punct">}</span>
    <span class="punct">}</span>
  <span class="punct">]</span>,
  <span class="str">"meta"</span>: <span class="punct">{</span> <span class="str">"total"</span>: <span class="num">128</span>, <span class="str">"per_page"</span>: <span class="num">15</span> <span class="punct">}</span>
<span class="punct">}</span></pre>
          </div>
        </div>
        <div>
          <span class="eyebrow">REST API</span>
          <h2 style="margin-bottom: var(--space-md);">Headless when you<br>want it. <span class="text-gradient">Full-stack</span><br>when you don't.</h2>
          <p style="font-size: var(--text-lg); color: var(--color-text-secondary); margin-bottom: var(--space-xl);">
            Every CMS resource is reachable via a versioned REST API with scoped keys
            (<code style="font-family:var(--font-mono); color:#67e8f9;">content:read</code>, <code style="font-family:var(--font-mono); color:#67e8f9;">preview:read</code>, <code style="font-family:var(--font-mono); color:#67e8f9;">media:read</code>, <code style="font-family:var(--font-mono); color:#67e8f9;">admin:read</code>),
            rate limits, and per-site abilities. Build a Next.js, Astro, or mobile front-end and
            keep the same admin.
          </p>

          <div style="display: flex; gap: var(--space-md); flex-wrap: wrap;">
            <a href="/documentation" class="btn btn-secondary btn-icon-after">
              API reference
              <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14M13 6l6 6-6 6"/></svg>
            </a>
            <button type="button" class="btn btn-ghost" data-demo-open>
              Try it in the demo →
            </button>
          </div>
        </div>
      </div>
    </div>
  </section>

  {{-- ============ PERSONAS ============ --}}
  <section>
    <div class="container">
      <div class="section-header">
        <span class="eyebrow">Built for</span>
        <h2>Designed for the people who <span class="text-gradient">ship</span>.</h2>
        <p>Agares stays out of your way whether you're a solo developer, an agency, or a content team.</p>
      </div>

      <div class="persona-grid">
        <div class="persona-card">
          <div class="icon">
            <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <polyline points="16 18 22 12 16 6"/><polyline points="8 6 2 12 8 18"/>
            </svg>
          </div>
          <h4>Developers</h4>
          <p>Laravel 13, Blade, Vite, a real REST API, Monaco editor for custom CSS/JS, RBAC you can extend in PHP. No black boxes.</p>
        </div>

        <div class="persona-card">
          <div class="icon">
            <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <rect x="3" y="3" width="7" height="7" rx="1"/>
              <rect x="14" y="3" width="7" height="7" rx="1"/>
              <rect x="14" y="14" width="7" height="7" rx="1"/>
              <rect x="3" y="14" width="7" height="7" rx="1"/>
            </svg>
          </div>
          <h4>Agencies</h4>
          <p>One install, every client. Per-site users and permissions. Custom domains via the deploy pipeline. Bill less, ship more.</p>
        </div>

        <div class="persona-card">
          <div class="icon">
            <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <path d="M14.5 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V7.5L14.5 2z"/>
              <polyline points="14 2 14 8 20 8"/>
              <line x1="8" y1="13" x2="16" y2="13"/>
            </svg>
          </div>
          <h4>Content teams</h4>
          <p>Drafts, schedules, custom field templates, gallery management, role-based publishing. No fear of breaking the site.</p>
        </div>

        <div class="persona-card">
          <div class="icon">
            <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <circle cx="9" cy="21" r="1"/><circle cx="20" cy="21" r="1"/>
              <path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"/>
            </svg>
          </div>
          <h4>SaaS founders</h4>
          <p>Turn the ecommerce module on, plug a gateway, ship a checkout. 2FA, audit, GDPR — already covered.</p>
        </div>
      </div>
    </div>
  </section>

  {{-- ============ STAT STRIP ============ --}}
  <section>
    <div class="container-wide">
      <div class="stat-strip">
        <div class="stat-strip-item">
          <div class="stat-strip-num">17</div>
          <div class="stat-strip-label">Newsletter permissions</div>
        </div>
        <div class="stat-strip-item">
          <div class="stat-strip-num">5</div>
          <div class="stat-strip-label">Auth methods</div>
        </div>
        <div class="stat-strip-item">
          <div class="stat-strip-num">119</div>
          <div class="stat-strip-label">Passing tests</div>
        </div>
        <div class="stat-strip-item">
          <div class="stat-strip-num">100%</div>
          <div class="stat-strip-label">Source available</div>
        </div>
      </div>
    </div>
  </section>

  {{-- ============ TESTIMONIAL / CASE STUDY TEASER ============ --}}
  <section>
    <div class="container">
      <div class="split">
        <div>
          <div class="quote-card">
            <blockquote>
              Agares runs three of our production sites — the editor team owns content,
              we own the code, nobody fights about it. Best decision we made this year.
            </blockquote>
            <div class="quote-author">
              <div class="avatar">PCM</div>
              <div>
                <div style="font-weight: 600; color: var(--color-text-primary);">PiesCiMordeLizal</div>
                <div style="color: var(--color-text-tertiary); font-size: var(--text-sm);">Hospitality &amp; pet hotel · live on Agares CMS</div>
              </div>
            </div>
          </div>
        </div>

        <div>
          <span class="eyebrow">Real sites, real owners</span>
          <h2 style="margin-bottom: var(--space-md);">See the platform doing <span class="text-gradient">actual work</span>.</h2>
          <p style="color: var(--color-text-secondary); font-size: var(--text-lg); margin-bottom: var(--space-xl);">
            A growing roster of agencies and SaaS products run on Agares — from hospitality
            booking pages to multi-tenant SaaS dashboards.
          </p>
          <a href="/projects" class="btn btn-secondary btn-icon-after">
            Explore the showcase
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14M13 6l6 6-6 6"/></svg>
          </a>
        </div>
      </div>
    </div>
  </section>

  {{-- ============ CTA BANNER ============ --}}
  <section>
    <div class="container">
      <div class="cta-banner">
        <span class="badge badge-primary mb-md">Try it free</span>
        <h2>Walk through every screen.<br>No signup, no credit card.</h2>
        <p>Hop into the admin as a read-only viewer. Click, prod, get a feel for it.<br>If you like what you see, the source is on GitHub.</p>
        <div class="hero-buttons">
          <button type="button" class="btn btn-primary btn-lg btn-icon-after" data-demo-open>
            Open the demo
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
              <path d="M5 12h14M13 6l6 6-6 6"/>
            </svg>
          </button>
          <a href="/contact" class="btn btn-secondary btn-lg">Get in touch</a>
        </div>
      </div>
    </div>
  </section>

@stop
