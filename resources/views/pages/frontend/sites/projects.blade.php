@extends('pages.frontend.base')

@push('styles')
<style>
  /* ============ Projects page extras ============ */

  /* Case study hero — large featured tile */
  .case-hero {
    display: grid;
    grid-template-columns: 1fr 1.3fr;
    gap: var(--space-2xl);
    align-items: center;
    padding: clamp(2rem, 4vw, 3.5rem);
    background: linear-gradient(135deg, rgba(244, 114, 182, 0.10), rgba(139, 92, 246, 0.06) 50%, transparent 100%);
    border: 1px solid var(--color-border-hover);
    border-radius: var(--radius-2xl);
    position: relative;
    overflow: hidden;
  }
  .case-hero::before {
    content: '';
    position: absolute;
    inset: 0;
    background: radial-gradient(ellipse 80% 70% at 70% 50%, rgba(244, 114, 182, 0.15), transparent 60%);
    pointer-events: none;
  }
  .case-hero > * { position: relative; }
  @media (max-width: 1000px) {
    .case-hero { grid-template-columns: 1fr; gap: var(--space-xl); padding: var(--space-xl); }
  }

  .case-hero-eyebrow {
    display: inline-flex; align-items: center; gap: 0.5rem;
    padding: 0.35rem 0.85rem 0.35rem 0.45rem;
    background: rgba(244, 114, 182, 0.1);
    border: 1px solid rgba(244, 114, 182, 0.3);
    border-radius: var(--radius-full);
    font-family: var(--font-mono);
    font-size: 0.7rem;
    text-transform: uppercase;
    letter-spacing: 0.12em;
    color: #f9a8d4;
    margin-bottom: var(--space-md);
  }
  .case-hero-eyebrow .dot {
    width: 7px; height: 7px; border-radius: 50%;
    background: #f472b6;
    box-shadow: 0 0 10px rgba(244, 114, 182, 0.7);
  }

  .case-hero h2 {
    font-size: clamp(2rem, 3.5vw, 2.75rem);
    letter-spacing: -0.03em;
    margin-bottom: var(--space-md);
  }
  .case-hero p { color: var(--color-text-secondary); font-size: var(--text-base); line-height: 1.7; margin-bottom: var(--space-lg); }

  .case-meta {
    display: flex; flex-wrap: wrap; gap: 0.4rem;
    margin-bottom: var(--space-lg);
  }
  .case-stats {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: var(--space-md);
    padding: var(--space-md) 0;
    margin-top: var(--space-md);
    border-top: 1px solid var(--color-border);
  }
  .case-stat-num {
    font-family: var(--font-display);
    font-size: var(--text-2xl);
    font-weight: 700;
    background: var(--color-accent-gradient);
    -webkit-background-clip: text; background-clip: text; -webkit-text-fill-color: transparent;
    line-height: 1;
  }
  .case-stat-label { font-family: var(--font-mono); font-size: 0.7rem; text-transform: uppercase; letter-spacing: 0.08em; color: var(--color-text-tertiary); margin-top: 0.3rem; }

  /* Stacked screenshot gallery — overlapping browser windows on an angle */
  .case-stack {
    position: relative;
    width: 100%;
    aspect-ratio: 16 / 11;
    perspective: 1400px;
  }
  .case-stack-card {
    position: absolute;
    background: linear-gradient(180deg, rgba(255, 255, 255, 0.04), rgba(255, 255, 255, 0.015));
    border: 1px solid var(--color-border-hover);
    border-radius: var(--radius-lg);
    padding: 0.5rem;
    box-shadow: var(--shadow-xl);
    transition: transform var(--transition-spring), opacity var(--transition-base), box-shadow var(--transition-base);
    will-change: transform;
    overflow: hidden;
  }
  .case-stack-bar {
    display: flex; align-items: center; gap: 0.35rem;
    padding: 0.35rem 0.5rem 0.4rem;
  }
  .case-stack-bar span {
    width: 8px; height: 8px; border-radius: 50%; background: rgba(255, 255, 255, 0.2);
  }
  .case-stack-bar span:nth-child(1) { background: #ff5f57; }
  .case-stack-bar span:nth-child(2) { background: #febc2e; }
  .case-stack-bar span:nth-child(3) { background: #28c840; }
  .case-stack-card-image {
    position: relative;
    height: calc(100% - 30px);
    border-radius: var(--radius-md);
    overflow: hidden;
    background: var(--color-bg-secondary);
  }
  .case-stack-card-image img {
    width: 100%; height: 100%; object-fit: cover; object-position: top center;
    display: block;
  }

  /* Three overlapping cards — each peeks from a different angle */
  .case-stack-card.s1 { inset: 6% 20% 6% 0; z-index: 3; transform: rotate(-3deg); box-shadow: var(--shadow-xl), 0 0 60px -10px rgba(244, 114, 182, 0.35); }
  .case-stack-card.s2 { inset: 12% 0 14% 28%; z-index: 2; transform: rotate(4deg); opacity: 0.95; }
  .case-stack-card.s3 { inset: 18% 8% 0 14%; z-index: 1; transform: rotate(7deg); opacity: 0.75; }

  .case-stack:hover .s1 { transform: rotate(-4deg) translate(-10px, -8px); box-shadow: var(--shadow-2xl), 0 0 80px -10px rgba(244, 114, 182, 0.5); }
  .case-stack:hover .s2 { transform: rotate(3deg) translate(8px, 4px); opacity: 1; }
  .case-stack:hover .s3 { transform: rotate(8deg) translate(12px, 10px); opacity: 0.85; }

  @media (max-width: 1000px) {
    .case-stack { aspect-ratio: 16 / 11; max-width: 600px; margin: 0 auto; }
  }

  /* Page gallery (secondary screenshots from the same client) */
  .case-thumbs {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
    gap: var(--space-md);
    margin-top: var(--space-xl);
  }
  .case-thumb {
    aspect-ratio: 16 / 10;
    background: var(--color-bg-secondary);
    border-radius: var(--radius-lg);
    overflow: hidden;
    border: 1px solid var(--color-border);
    position: relative;
    transition: all var(--transition-base);
  }
  .case-thumb img { width: 100%; height: 100%; object-fit: cover; display: block; transition: transform var(--transition-spring); }
  .case-thumb:hover { border-color: var(--color-border-hover); transform: translateY(-3px); }
  .case-thumb:hover img { transform: scale(1.04); }
  .case-thumb-label {
    position: absolute; left: 0.75rem; bottom: 0.75rem;
    padding: 0.35rem 0.75rem;
    background: rgba(7, 8, 13, 0.7);
    backdrop-filter: blur(10px);
    border: 1px solid var(--color-border-hover);
    border-radius: var(--radius-full);
    font-family: var(--font-mono);
    font-size: 0.7rem;
    text-transform: uppercase;
    letter-spacing: 0.08em;
    color: var(--color-text-secondary);
  }

  /* "Inside the CMS" — bento-style screenshot gallery */
  .admin-gallery {
    display: grid;
    grid-template-columns: repeat(12, 1fr);
    grid-auto-rows: minmax(140px, auto);
    gap: var(--space-lg);
  }
  .admin-tile {
    position: relative;
    overflow: hidden;
    border-radius: var(--radius-xl);
    border: 1px solid var(--color-border);
    background: var(--color-bg-secondary);
    transition: all var(--transition-base);
  }
  .admin-tile:hover { border-color: var(--color-border-hover); transform: translateY(-3px); box-shadow: var(--shadow-xl); }
  .admin-tile img { width: 100%; height: 100%; object-fit: cover; object-position: top left; display: block; transition: transform 1.2s cubic-bezier(0.2, 0.7, 0.2, 1); }
  .admin-tile:hover img { transform: scale(1.03); }
  .admin-tile-caption {
    position: absolute;
    left: 1rem; right: 1rem; bottom: 1rem;
    padding: 0.85rem 1rem;
    background: rgba(7, 8, 13, 0.78);
    backdrop-filter: blur(14px);
    border: 1px solid var(--color-border-hover);
    border-radius: var(--radius-md);
    color: var(--color-text-primary);
  }
  .admin-tile-caption h4 {
    font-family: var(--font-display);
    font-size: 0.95rem;
    font-weight: 600;
    margin: 0 0 0.15rem;
    letter-spacing: -0.01em;
  }
  .admin-tile-caption p { margin: 0; font-size: 0.8rem; color: var(--color-text-tertiary); line-height: 1.4; }

  /* Tile spans */
  .admin-tile.large { grid-column: span 8; grid-row: span 3; }
  .admin-tile.tall  { grid-column: span 4; grid-row: span 3; }
  .admin-tile.wide  { grid-column: span 6; grid-row: span 2; }
  .admin-tile.med   { grid-column: span 6; grid-row: span 2; }
  .admin-tile.small { grid-column: span 4; grid-row: span 2; }
  .admin-tile.sq    { grid-column: span 4; grid-row: span 2; }

  @media (max-width: 1000px) {
    .admin-gallery { grid-template-columns: repeat(6, 1fr); }
    .admin-tile.large { grid-column: span 6; grid-row: span 2; }
    .admin-tile.tall  { grid-column: span 6; grid-row: span 2; }
    .admin-tile.wide  { grid-column: span 6; }
    .admin-tile.med   { grid-column: span 6; }
    .admin-tile.small { grid-column: span 3; }
    .admin-tile.sq    { grid-column: span 3; }
  }
  @media (max-width: 640px) {
    .admin-gallery { grid-template-columns: 1fr; }
    .admin-tile, .admin-tile.large, .admin-tile.tall, .admin-tile.wide, .admin-tile.med, .admin-tile.small, .admin-tile.sq {
      grid-column: 1 / -1; grid-row: auto; min-height: 240px;
    }
  }

  /* Per-project case-hero accents (override .case-hero base) */
  .case-hero.saas {
    background:
      radial-gradient(ellipse 70% 60% at 30% 50%, rgba(34, 211, 238, 0.10), transparent 60%),
      linear-gradient(135deg, rgba(34, 211, 238, 0.06), rgba(139, 92, 246, 0.04) 50%, transparent 100%);
  }
  .case-hero.saas::before {
    background: radial-gradient(ellipse 80% 70% at 30% 50%, rgba(34, 211, 238, 0.12), transparent 60%);
  }
  .case-hero.saas .case-hero-eyebrow {
    background: rgba(34, 211, 238, 0.10);
    border-color: rgba(34, 211, 238, 0.3);
    color: #67e8f9;
  }
  .case-hero.saas .case-hero-eyebrow .dot {
    background: #22d3ee;
    box-shadow: 0 0 10px rgba(34, 211, 238, 0.7);
  }
  .case-hero.saas .case-stack-card.s1 { box-shadow: var(--shadow-xl), 0 0 60px -10px rgba(34, 211, 238, 0.4); }

  .case-hero.bender {
    background:
      radial-gradient(ellipse 70% 60% at 70% 50%, rgba(251, 191, 36, 0.10), transparent 60%),
      linear-gradient(135deg, rgba(251, 191, 36, 0.06), rgba(244, 114, 182, 0.05) 50%, transparent 100%);
  }
  .case-hero.bender::before {
    background: radial-gradient(ellipse 80% 70% at 70% 50%, rgba(251, 191, 36, 0.14), transparent 60%);
  }
  .case-hero.bender .case-hero-eyebrow {
    background: rgba(251, 191, 36, 0.10);
    border-color: rgba(251, 191, 36, 0.3);
    color: #fde68a;
  }
  .case-hero.bender .case-hero-eyebrow .dot {
    background: #fbbf24;
    box-shadow: 0 0 10px rgba(251, 191, 36, 0.7);
  }
  .case-hero.bender .case-stack-card.s1 { box-shadow: var(--shadow-xl), 0 0 60px -10px rgba(251, 191, 36, 0.4); }

  /* "Reverse" variant — text on right, stack on left (for visual rhythm) */
  .case-hero.reverse {
    grid-template-columns: 1.3fr 1fr;
  }
  .case-hero.reverse > :first-child { order: 2; }
  @media (max-width: 1000px) {
    .case-hero.reverse { grid-template-columns: 1fr; }
    .case-hero.reverse > :first-child { order: 0; }
  }
</style>
@endpush

@section('content')

  {{-- ============ PAGE HEADER ============ --}}
  <section class="page-header">
    <div class="container">
      <span class="eyebrow">The Showcase</span>
      <h1>Real products.<br><span class="text-gradient-magic">Same engineering DNA.</span></h1>
      <p>Three live products on the Agares stack — a client hospitality site, a multi-tenant AI SaaS, and an adult party-game ecosystem. Plus a tour of the CMS admin itself.</p>
    </div>
  </section>

  {{-- ============ FEATURED CASE STUDY: PiesCiMordeLizal ============ --}}
  <section style="padding-top: var(--space-2xl);">
    <div class="container-wide">
      <div class="case-hero reveal">

        <div>
          <div class="case-hero-eyebrow">
            <span class="dot"></span>
            <span>Featured · live in production</span>
          </div>

          <h2>PiesCiMordeLizal</h2>
          <p>
            A full hospitality &amp; pet-hotel website — homepage, room types, offers, about, dedicated pet-camp section.
            All five sections, all media, all SEO meta, fully editable from the Agares admin.
            Built once, handed off to the team, runs without us.
          </p>

          <div class="case-meta">
            <span class="badge badge-pink">Hospitality</span>
            <span class="badge badge-cyan">5 page sections</span>
            <span class="badge badge-success">Live since 2024</span>
            <span class="badge badge-primary">Multi-language ready</span>
          </div>

          <div class="case-stats">
            <div>
              <div class="case-stat-num">5</div>
              <div class="case-stat-label">Sections</div>
            </div>
            <div>
              <div class="case-stat-num">100%</div>
              <div class="case-stat-label">Client-managed</div>
            </div>
            <div>
              <div class="case-stat-num">~2wk</div>
              <div class="case-stat-label">Build-to-launch</div>
            </div>
          </div>

          <div style="display: flex; gap: 0.75rem; flex-wrap: wrap; margin-top: var(--space-lg);">
            <a href="https://piescimordelizal.pl" target="_blank" rel="noopener" class="btn btn-secondary btn-icon-after">
              Visit the site
              <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"/><polyline points="15 3 21 3 21 9"/><line x1="10" y1="14" x2="21" y2="3"/></svg>
            </a>
            <button type="button" class="btn btn-ghost" data-demo-open>Try the admin →</button>
          </div>
        </div>

        <div class="case-stack" aria-label="Three PiesCiMordeLizal pages stacked at angles">
          {{-- back card --}}
          <div class="case-stack-card s3">
            <div class="case-stack-bar"><span></span><span></span><span></span></div>
            <div class="case-stack-card-image">
              <img src="{{ asset('assets/frontend/images/pcml-petcamp.jpg') }}" alt="" loading="lazy">
            </div>
          </div>
          {{-- middle card --}}
          <div class="case-stack-card s2">
            <div class="case-stack-bar"><span></span><span></span><span></span></div>
            <div class="case-stack-card-image">
              <img src="{{ asset('assets/frontend/images/pcml-hotel.jpg') }}" alt="" loading="lazy">
            </div>
          </div>
          {{-- front card --}}
          <div class="case-stack-card s1">
            <div class="case-stack-bar"><span></span><span></span><span></span></div>
            <div class="case-stack-card-image">
              <img src="{{ asset('assets/frontend/images/pcml-home.jpg') }}" alt="PiesCiMordeLizal homepage hero on Agares CMS" loading="lazy">
            </div>
          </div>
        </div>
      </div>

    </div>
  </section>

  {{-- ============ CASE STUDY: AGARES SAAS ============ --}}
  <section style="padding-top: var(--space-2xl);">
    <div class="container-wide">
      <div class="case-hero saas reverse reveal">

        <div class="case-stack" aria-label="Three Agares SaaS screens stacked at angles">
          <div class="case-stack-card s3">
            <div class="case-stack-bar"><span></span><span></span><span></span></div>
            <div class="case-stack-card-image">
              <img src="{{ asset('assets/frontend/images/saas-tenants.jpg') }}" alt="" loading="lazy">
            </div>
          </div>
          <div class="case-stack-card s2">
            <div class="case-stack-bar"><span></span><span></span><span></span></div>
            <div class="case-stack-card-image">
              <img src="{{ asset('assets/frontend/images/saas-rag.jpg') }}" alt="" loading="lazy">
            </div>
          </div>
          <div class="case-stack-card s1">
            <div class="case-stack-bar"><span></span><span></span><span></span></div>
            <div class="case-stack-card-image">
              <img src="{{ asset('assets/frontend/images/saas-dashboard.jpg') }}" alt="Agares SaaS multi-tenant dashboard on the same Laravel stack" loading="lazy">
            </div>
          </div>
        </div>

        <div>
          <div class="case-hero-eyebrow">
            <span class="dot"></span>
            <span>Sibling product · live · same stack</span>
          </div>

          <h2>Agares SaaS</h2>
          <p>
            The multi-tenant AI platform that lives alongside the CMS. Newsletter engine with HMAC-signed
            webhook callbacks, RAG chatbot on pgvector, scoped <code style="font-family:var(--font-mono);color:#67e8f9;">agr_</code> API keys,
            cookie scanner — and the CMS can delegate bulk sends to it over HTTP without spinning up a queue worker.
          </p>

          <div class="case-meta">
            <span class="badge badge-cyan">Multi-tenant</span>
            <span class="badge badge-cyan">pgvector + RAG</span>
            <span class="badge badge-cyan">HMAC webhooks</span>
            <span class="badge badge-cyan">Queue-backed</span>
            <span class="badge badge-success">Live in prod</span>
          </div>

          <div class="case-stats">
            <div>
              <div class="case-stat-num">3</div>
              <div class="case-stat-label">Services</div>
            </div>
            <div>
              <div class="case-stat-num">55</div>
              <div class="case-stat-label">Passing tests</div>
            </div>
            <div>
              <div class="case-stat-num">∞</div>
              <div class="case-stat-label">Tenants per install</div>
            </div>
          </div>

          <div style="display: flex; gap: 0.75rem; flex-wrap: wrap; margin-top: var(--space-lg);">
            <a href="/api" class="btn btn-ghost">How CMS talks to it →</a>
          </div>
        </div>
      </div>
    </div>
  </section>

  {{-- ============ CASE STUDY: BENDER ============ --}}
  <section style="padding-top: var(--space-2xl);">
    <div class="container-wide">
      <div class="case-hero bender reveal">

        <div>
          <div class="case-hero-eyebrow">
            <span class="dot"></span>
            <span>Personal product · live · React + Laravel</span>
          </div>

          <h2>Bender</h2>
          <p>
            Adult party-game web app (18+ age gate) with three live mini-games — Never Have I Ever,
            Truth or Dare, and Quiz. React frontend pulls cards from a Laravel admin API that also
            ships a generic CMS layer — same Sites → Categories → Articles pattern as Agares CMS.
          </p>

          <div class="case-meta">
            <span class="badge badge-warning">Adult content · 18+</span>
            <span class="badge badge-pink">React + Laravel</span>
            <span class="badge badge-cyan">PostgreSQL</span>
            <span class="badge badge-success">Live since 2024</span>
          </div>

          <div class="case-stats">
            <div>
              <div class="case-stat-num">3</div>
              <div class="case-stat-label">Live games</div>
            </div>
            <div>
              <div class="case-stat-num">5</div>
              <div class="case-stat-label">Spiciness levels</div>
            </div>
            <div>
              <div class="case-stat-num">2</div>
              <div class="case-stat-label">Apps (web + admin)</div>
            </div>
          </div>

          <div style="display: flex; gap: 0.75rem; flex-wrap: wrap; margin-top: var(--space-lg);">
            <a href="https://bender-app.eu" target="_blank" rel="noopener" class="btn btn-secondary btn-icon-after">
              Play the game
              <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"/><polyline points="15 3 21 3 21 9"/><line x1="10" y1="14" x2="21" y2="3"/></svg>
            </a>
          </div>
        </div>

        <div class="case-stack" aria-label="Three Bender screens stacked at angles">
          <div class="case-stack-card s3">
            <div class="case-stack-bar"><span></span><span></span><span></span></div>
            <div class="case-stack-card-image">
              <img src="{{ asset('assets/frontend/images/bender_tod_raw.jpg') }}" alt="" loading="lazy">
            </div>
          </div>
          <div class="case-stack-card s2">
            <div class="case-stack-bar"><span></span><span></span><span></span></div>
            <div class="case-stack-card-image">
              <img src="{{ asset('assets/frontend/images/bender_admin_raw.jpg') }}" alt="" loading="lazy">
            </div>
          </div>
          <div class="case-stack-card s1">
            <div class="case-stack-bar"><span></span><span></span><span></span></div>
            <div class="case-stack-card-image">
              <img src="{{ asset('assets/frontend/images/bender_nhie_raw.jpg') }}" alt="Bender Never Have I Ever game card" loading="lazy">
            </div>
          </div>
        </div>
      </div>

    </div>
  </section>

  {{-- ============ INSIDE THE CMS ============ --}}
  <section>
    <div class="container-wide">
      <div class="section-header">
        <span class="eyebrow">Inside the CMS</span>
        <h2>The admin panel,<br><span class="text-gradient">screen by screen</span>.</h2>
        <p>Every module shipped to production. No mockups, no Figma — this is the actual UI.</p>
      </div>

      <div class="admin-gallery">

        <div class="admin-tile large">
          <img src="{{ asset('assets/frontend/images/agares_cms_dashboard.jpg') }}" alt="Agares CMS dashboard with traffic analytics, active users and ecommerce metrics" loading="lazy">
          <div class="admin-tile-caption">
            <h4>Dashboard</h4>
            <p>Traffic timeline, realtime active users, ecommerce KPIs — all powered by GA4 with 30-min caching.</p>
          </div>
        </div>

        <div class="admin-tile tall">
          <img src="{{ asset('assets/frontend/images/agares_cms_permissions.jpg') }}" alt="RBAC permission matrix per-role, per-site" loading="lazy">
          <div class="admin-tile-caption">
            <h4>Roles &amp; permissions</h4>
            <p>Spatie RBAC with site-scoped grants. View / edit per page, per role.</p>
          </div>
        </div>

        <div class="admin-tile wide">
          <img src="{{ asset('assets/frontend/images/agares_cms_edit.jpg') }}" alt="Page editor with custom fields and visual layout" loading="lazy">
          <div class="admin-tile-caption">
            <h4>Page editor</h4>
            <p>Visual editor for the team, raw Blade for you. Monaco-powered custom code injection.</p>
          </div>
        </div>

        <div class="admin-tile med">
          <img src="{{ asset('assets/frontend/images/agares_cms_sites.jpg') }}" alt="Sites manager listing every page across all client sites" loading="lazy">
          <div class="admin-tile-caption">
            <h4>Sites &amp; pages</h4>
            <p>Multi-site at the top, pages underneath, drafts and bin separated.</p>
          </div>
        </div>

        <div class="admin-tile small">
          <img src="{{ asset('assets/frontend/images/agares_cms_media.jpg') }}" alt="Global media library with image grid" loading="lazy">
          <div class="admin-tile-caption">
            <h4>Media library</h4>
            <p>One library, every site. MIME allowlist, signed uploads.</p>
          </div>
        </div>

        <div class="admin-tile small">
          <img src="{{ asset('assets/frontend/images/agares_cms_menus.jpg') }}" alt="Drag-and-drop menu builder" loading="lazy">
          <div class="admin-tile-caption">
            <h4>Menus</h4>
            <p>Drag-and-drop tree with nested groups, redirects and new-tab flags.</p>
          </div>
        </div>

        <div class="admin-tile small">
          <img src="{{ asset('assets/frontend/images/agares_cms_payments.jpg') }}" alt="Payment provider configuration screen" loading="lazy">
          <div class="admin-tile-caption">
            <h4>Payment providers</h4>
            <p>Stripe, PayU, P24, PayPal &amp; COD. Per-driver write-only secrets.</p>
          </div>
        </div>

        <div class="admin-tile sq">
          <img src="{{ asset('assets/frontend/images/agares_cms_cookies.jpg') }}" alt="Cookie consent manager with scanner" loading="lazy">
          <div class="admin-tile-caption">
            <h4>Cookie consent</h4>
            <p>Live scanner + granular consent UI, GDPR audit log.</p>
          </div>
        </div>

        <div class="admin-tile sq">
          <img src="{{ asset('assets/frontend/images/agares_cms_settings.jpg') }}" alt="CMS settings with grouped sections" loading="lazy">
          <div class="admin-tile-caption">
            <h4>Settings</h4>
            <p>Every feature flag, secret &amp; tunable lives here. Per-category panels.</p>
          </div>
        </div>

        <div class="admin-tile sq">
          <img src="{{ asset('assets/frontend/images/agares_cms_settings_addons.jpg') }}" alt="Add-ons / feature flag toggles" loading="lazy">
          <div class="admin-tile-caption">
            <h4>Add-ons</h4>
            <p>Ecommerce, newsletter, forum — toggle them on per project.</p>
          </div>
        </div>

      </div>
    </div>
  </section>

  {{-- ============ CLOSING CTA ============ --}}
  <section>
    <div class="container">
      <div class="cta-banner reveal">
        <span class="badge badge-primary mb-md">Build with Agares</span>
        <h2>Your site, on this&nbsp;CMS,<br>shipped sooner than you think.</h2>
        <p>If you like what you've seen — the admin, the architecture, the security — let's talk about getting your project onto Agares.</p>
        <div class="hero-buttons">
          <a href="/contact" class="btn btn-primary btn-lg btn-icon-after">
            Start a conversation
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14M13 6l6 6-6 6"/></svg>
          </a>
          <button type="button" class="btn btn-secondary btn-lg" data-demo-open>Open the demo</button>
        </div>
      </div>
    </div>
  </section>

@stop
