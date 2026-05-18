@extends('pages.frontend.base')

@push('styles')
<style>
  /* ============ API page extras ============ */

  .endpoint-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
    gap: var(--space-md);
  }
  .endpoint {
    display: flex; gap: var(--space-md); align-items: flex-start;
    padding: 1rem 1.1rem;
    background: var(--color-surface);
    border: 1px solid var(--color-border);
    border-radius: var(--radius-md);
    transition: all var(--transition-base);
  }
  .endpoint:hover { border-color: var(--color-border-hover); transform: translateY(-2px); }
  .endpoint-method {
    flex-shrink: 0;
    padding: 0.2rem 0.55rem;
    border-radius: var(--radius-sm);
    font-family: var(--font-mono);
    font-size: 0.7rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.06em;
    line-height: 1.6;
  }
  .endpoint-method.get  { background: rgba(34, 211, 238, 0.15); color: #67e8f9; border: 1px solid rgba(34, 211, 238, 0.3); }
  .endpoint-method.post { background: rgba(52, 211, 153, 0.15); color: #6ee7b7; border: 1px solid rgba(52, 211, 153, 0.3); }
  .endpoint-method.del  { background: rgba(248, 113, 113, 0.12); color: #fca5a5; border: 1px solid rgba(248, 113, 113, 0.3); }
  .endpoint-path {
    font-family: var(--font-mono);
    font-size: 0.85rem;
    color: var(--color-text-primary);
    margin: 0 0 0.2rem;
    line-height: 1.4;
    word-break: break-all;
  }
  .endpoint-desc { font-size: 0.78rem; color: var(--color-text-tertiary); margin: 0; line-height: 1.5; }

  /* Ability scope cards */
  .scope-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: var(--space-md);
  }
  @media (max-width: 900px) { .scope-grid { grid-template-columns: repeat(2, 1fr); } }
  @media (max-width: 540px) { .scope-grid { grid-template-columns: 1fr; } }
  .scope {
    padding: var(--space-lg);
    background: var(--color-surface);
    border: 1px solid var(--color-border);
    border-radius: var(--radius-lg);
    transition: all var(--transition-base);
  }
  .scope:hover { border-color: var(--color-border-hover); transform: translateY(-2px); }
  .scope-key {
    display: inline-block;
    padding: 0.2rem 0.55rem;
    font-family: var(--font-mono);
    font-size: 0.78rem;
    background: rgba(34, 211, 238, 0.12);
    color: #67e8f9;
    border: 1px solid rgba(34, 211, 238, 0.3);
    border-radius: var(--radius-sm);
    margin-bottom: 0.6rem;
  }
  .scope h4 { font-family: var(--font-display); font-size: 0.95rem; margin-bottom: 0.4rem; letter-spacing: -0.01em; }
  .scope p { font-size: 0.82rem; color: var(--color-text-secondary); margin: 0; line-height: 1.55; }

  /* Tabbed code blocks */
  .code-tabs {
    background: var(--color-bg-code);
    border: 1px solid var(--color-border-hover);
    border-radius: var(--radius-xl);
    overflow: hidden;
    box-shadow: var(--shadow-xl);
  }
  .code-tabs-bar {
    display: flex; gap: 0.15rem;
    padding: 0.4rem 0.6rem;
    background: rgba(255, 255, 255, 0.03);
    border-bottom: 1px solid var(--color-border);
  }
  .code-tab {
    padding: 0.4rem 0.9rem;
    background: transparent;
    border: none;
    border-radius: var(--radius-sm);
    color: var(--color-text-tertiary);
    font-family: var(--font-mono);
    font-size: 0.78rem;
    cursor: pointer;
    transition: all var(--transition-base);
  }
  .code-tab:hover { color: var(--color-text-primary); background: var(--color-surface-hover); }
  .code-tab.active { color: var(--color-text-primary); background: var(--color-surface-strong); }
  .code-tab-panel { display: none; }
  .code-tab-panel.active { display: block; }
  .code-tabs pre { margin: 0; padding: var(--space-lg); font-family: var(--font-mono); font-size: 0.85rem; line-height: 1.7; color: #d4d8ea; overflow-x: auto; }

  /* Quickstart steps */
  .quickstart {
    display: grid;
    gap: var(--space-md);
  }
  .qs-step {
    display: grid;
    grid-template-columns: 56px 1fr;
    gap: var(--space-md);
    padding: var(--space-lg);
    background: var(--color-surface);
    border: 1px solid var(--color-border);
    border-radius: var(--radius-lg);
    transition: all var(--transition-base);
  }
  .qs-step:hover { border-color: var(--color-border-hover); }
  .qs-num {
    display: inline-flex; align-items: center; justify-content: center;
    width: 40px; height: 40px;
    background: var(--color-accent-gradient);
    border-radius: var(--radius-md);
    font-family: var(--font-display);
    font-size: var(--text-lg);
    font-weight: 700;
    color: white;
    box-shadow: 0 8px 24px -8px rgba(139, 92, 246, 0.55);
  }
  .qs-step h4 { font-family: var(--font-display); font-size: var(--text-lg); margin-bottom: 0.4rem; letter-spacing: -0.01em; }
  .qs-step p { font-size: 0.88rem; color: var(--color-text-secondary); margin: 0 0 0.6rem; line-height: 1.6; }
  .qs-step code {
    display: inline-block;
    padding: 0.25rem 0.55rem;
    font-family: var(--font-mono);
    font-size: 0.78rem;
    background: var(--color-bg-code);
    border: 1px solid var(--color-border);
    border-radius: var(--radius-sm);
    color: #67e8f9;
  }
  @media (max-width: 600px) { .qs-step { grid-template-columns: 1fr; } }
</style>
@endpush

@section('content')

  {{-- ============ HERO ============ --}}
  <section class="hero">
    <div class="container">
      <div class="hero-eyebrow">
        <span class="pill">API v1</span>
        <span>Scoped keys · rate limits · versioned via glob</span>
      </div>

      <h1 class="hero-title">
        Headless when you want&nbsp;it.<br>
        <span class="text-gradient-magic">Full-stack when you don't.</span>
      </h1>

      <p class="hero-subtitle">
        Every CMS resource — sites, categories, articles, media, settings — is reachable
        over a versioned REST API. Scoped <code style="font-family:var(--font-mono);color:#67e8f9;">X-API-Key</code> auth,
        per-route rate limits, site-scoped abilities. Build the front-end you actually want.
      </p>

      <div class="hero-buttons">
        <a href="#quickstart" class="btn btn-primary btn-lg btn-icon-after">
          Show me a quickstart
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14M13 6l6 6-6 6"/></svg>
        </a>
        <a href="#endpoints" class="btn btn-secondary btn-lg">
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="16 18 22 12 16 6"/><polyline points="8 6 2 12 8 18"/></svg>
          Browse endpoints
        </a>
      </div>

      <div class="hero-stats">
        <div class="hero-stat"><div class="num">v1</div><div class="label">Stable, versioned</div></div>
        <div class="hero-stat"><div class="num">5</div><div class="label">Ability scopes</div></div>
        <div class="hero-stat"><div class="num">∞</div><div class="label">Keys per project</div></div>
        <div class="hero-stat"><div class="num">JSON</div><div class="label">Native + Laravel API resources</div></div>
      </div>
    </div>
  </section>

  {{-- ============ AT A GLANCE — code + scopes ============ --}}
  <section style="padding-top: var(--space-2xl);">
    <div class="container-wide">
      <div class="split">
        <div>
          <span class="eyebrow">At a glance</span>
          <h2 style="margin-bottom: var(--space-md);">One header.<br>One <span class="text-gradient">key</span>.</h2>
          <p style="color: var(--color-text-secondary); font-size: var(--text-lg); margin-bottom: var(--space-xl); line-height: 1.65;">
            Every request carries an <code style="font-family:var(--font-mono);color:#67e8f9;">X-API-Key</code> header.
            The key has explicit abilities baked in at issue time — <code style="font-family:var(--font-mono);color:#67e8f9;">content:read</code>,
            <code style="font-family:var(--font-mono);color:#67e8f9;">preview:read</code>, <code style="font-family:var(--font-mono);color:#67e8f9;">media:read</code>,
            <code style="font-family:var(--font-mono);color:#67e8f9;">admin:read</code>, <code style="font-family:var(--font-mono);color:#67e8f9;">settings:read_public</code>.
            No JWT dance, no OAuth roundtrips, no opaque session.
          </p>

          <div style="display: grid; gap: 0.6rem; margin-bottom: var(--space-xl);">
            <div style="display: flex; gap: 0.75rem; align-items: center; padding: 0.85rem 1rem; background: var(--color-surface); border: 1px solid var(--color-border); border-radius: var(--radius-md);">
              <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#34d399" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
              <span style="font-size: 0.88rem; color: var(--color-text-secondary);">Plaintext key shown <strong style="color: var(--color-text-primary);">once</strong> at issue, hashed at rest after</span>
            </div>
            <div style="display: flex; gap: 0.75rem; align-items: center; padding: 0.85rem 1rem; background: var(--color-surface); border: 1px solid var(--color-border); border-radius: var(--radius-md);">
              <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#34d399" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
              <span style="font-size: 0.88rem; color: var(--color-text-secondary);">Optional <code style="font-family:var(--font-mono);color:#67e8f9;">site_id</code> scope — key only sees that one site</span>
            </div>
            <div style="display: flex; gap: 0.75rem; align-items: center; padding: 0.85rem 1rem; background: var(--color-surface); border: 1px solid var(--color-border); border-radius: var(--radius-md);">
              <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#34d399" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
              <span style="font-size: 0.88rem; color: var(--color-text-secondary);">Optional <code style="font-family:var(--font-mono);color:#67e8f9;">expires_at</code> — auto-disabled past that date</span>
            </div>
            <div style="display: flex; gap: 0.75rem; align-items: center; padding: 0.85rem 1rem; background: var(--color-surface); border: 1px solid var(--color-border); border-radius: var(--radius-md);">
              <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#34d399" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
              <span style="font-size: 0.88rem; color: var(--color-text-secondary);">Per-key rate limit (default 60/min, configurable in settings)</span>
            </div>
          </div>

          <div style="padding: var(--space-md); background: rgba(251, 191, 36, 0.05); border: 1px solid rgba(251, 191, 36, 0.2); border-left: 3px solid var(--color-accent-amber); border-radius: var(--radius-md);">
            <div style="font-family: var(--font-mono); font-size: 0.7rem; text-transform: uppercase; letter-spacing: 0.1em; color: var(--color-accent-amber); margin-bottom: 0.4rem;">Feature-gated</div>
            <p style="font-size: 0.88rem; color: var(--color-text-secondary); margin: 0; line-height: 1.6;">
              The API surface is enabled by the <code style="font-family:var(--font-mono);color:#67e8f9;">enable_api</code> setting.
              Off by default on new installs — clients who don't need it never expose it.
            </p>
          </div>
        </div>

        <div>
          {{-- Tabbed code block --}}
          <div class="code-tabs">
            <div class="code-tabs-bar" role="tablist">
              <button class="code-tab active" data-tab="curl">curl</button>
              <button class="code-tab" data-tab="js">JavaScript</button>
              <button class="code-tab" data-tab="php">PHP</button>
              <button class="code-tab" data-tab="python">Python</button>
            </div>

            <div class="code-tab-panel active" data-panel="curl">
<pre><span class="com"># Fetch published articles from one site, scoped &amp; cached</span>
<span class="kw">curl</span> https://demo.agares.co.uk/api/v1/articles?site=lookbook \
  <span class="punct">-H</span> <span class="str">"X-API-Key: ak_live_a9f4..._public"</span> \
  <span class="punct">-H</span> <span class="str">"Accept: application/json"</span>

<span class="com"># With pagination + filter</span>
<span class="kw">curl</span> <span class="str">"https://demo.agares.co.uk/api/v1/articles?status=published&amp;per_page=10&amp;page=2"</span> \
  <span class="punct">-H</span> <span class="str">"X-API-Key: ak_live_a9f4..._public"</span></pre>
            </div>

            <div class="code-tab-panel" data-panel="js">
<pre><span class="com">// Modern fetch, with abort + JSON parse</span>
<span class="kw">const</span> ctrl = <span class="kw">new</span> <span class="fn">AbortController</span>()<span class="punct">;</span>
<span class="kw">const</span> res = <span class="kw">await</span> <span class="fn">fetch</span>(<span class="str">'https://demo.agares.co.uk/api/v1/articles'</span>, <span class="punct">{</span>
  <span class="str">headers</span><span class="punct">:</span> <span class="punct">{</span>
    <span class="str">'X-API-Key'</span><span class="punct">:</span> <span class="kw">process</span>.<span class="kw">env</span>.<span class="fn">AGARES_KEY</span>,
    <span class="str">'Accept'</span><span class="punct">:</span> <span class="str">'application/json'</span>,
  <span class="punct">},</span>
  <span class="str">signal</span><span class="punct">:</span> ctrl.<span class="fn">signal</span>,
<span class="punct">});</span>

<span class="kw">if</span> (!res.<span class="fn">ok</span>) <span class="kw">throw new</span> <span class="fn">Error</span>(<span class="str">`HTTP </span><span class="punct">${</span>res.<span class="fn">status</span><span class="punct">}</span><span class="str">`</span>)<span class="punct">;</span>
<span class="kw">const</span> <span class="punct">{</span> data, meta <span class="punct">}</span> = <span class="kw">await</span> res.<span class="fn">json</span>()<span class="punct">;</span></pre>
            </div>

            <div class="code-tab-panel" data-panel="php">
<pre><span class="com">// Laravel HTTP client — Agares calling Agares</span>
<span class="kw">use</span> <span class="kw">Illuminate</span>\<span class="kw">Support</span>\<span class="kw">Facades</span>\<span class="kw">Http</span><span class="punct">;</span>

<span class="punct">$response</span> = <span class="kw">Http</span>::<span class="fn">withHeaders</span>(<span class="punct">[</span>
    <span class="str">'X-API-Key'</span> =&gt; <span class="fn">config</span>(<span class="str">'services.agares.key'</span>),
    <span class="str">'Accept'</span>    =&gt; <span class="str">'application/json'</span>,
<span class="punct">])</span>-&gt;<span class="fn">get</span>(<span class="str">'https://demo.agares.co.uk/api/v1/articles'</span>, <span class="punct">[</span>
    <span class="str">'site'</span>     =&gt; <span class="str">'lookbook'</span>,
    <span class="str">'per_page'</span> =&gt; <span class="num">15</span>,
<span class="punct">]);</span>

<span class="punct">$articles</span> = <span class="punct">$response</span>-&gt;<span class="fn">json</span>(<span class="str">'data'</span>)<span class="punct">;</span></pre>
            </div>

            <div class="code-tab-panel" data-panel="python">
<pre><span class="com"># requests — for scripts &amp; data pipelines</span>
<span class="kw">import</span> os, requests

resp = requests.<span class="fn">get</span>(
    <span class="str">"https://demo.agares.co.uk/api/v1/articles"</span>,
    headers=<span class="punct">{</span>
        <span class="str">"X-API-Key"</span>: os.environ[<span class="str">"AGARES_KEY"</span>],
        <span class="str">"Accept"</span>:    <span class="str">"application/json"</span>,
    <span class="punct">},</span>
    params=<span class="punct">{</span><span class="str">"site"</span>: <span class="str">"lookbook"</span>, <span class="str">"per_page"</span>: <span class="num">25</span><span class="punct">}</span>,
    timeout=<span class="num">10</span>,
)
resp.<span class="fn">raise_for_status</span>()
articles = resp.<span class="fn">json</span>()[<span class="str">"data"</span>]</pre>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>

  {{-- ============ ABILITY SCOPES ============ --}}
  <section>
    <div class="container-wide">
      <div class="section-header">
        <span class="eyebrow">Ability scopes</span>
        <h2>One key.<br><span class="text-gradient">Exactly the access you grant.</span></h2>
        <p>Each key is issued with a fixed set of abilities. Mixing them gives precise least-privilege for every consumer.</p>
      </div>

      <div class="scope-grid">
        <div class="scope reveal">
          <span class="scope-key">content:read</span>
          <h4>Public content</h4>
          <p>Published sites, categories, articles, menus. Safe for static-site generators and CDN edge functions.</p>
        </div>
        <div class="scope reveal">
          <span class="scope-key">preview:read</span>
          <h4>Drafts &amp; scheduled</h4>
          <p>Unpublished and scheduled-future content. For preview environments and editor tooling.</p>
        </div>
        <div class="scope reveal">
          <span class="scope-key">media:read</span>
          <h4>Media library</h4>
          <p>Media records and signed URLs. Lets a mobile app or headless frontend reuse the same asset store.</p>
        </div>
        <div class="scope reveal">
          <span class="scope-key">settings:read_public</span>
          <h4>Public settings</h4>
          <p>Non-secret site config — title, OG image, locale, feature flags marked public. Never returns API keys.</p>
        </div>
        <div class="scope reveal">
          <span class="scope-key">admin:read</span>
          <h4>Admin read</h4>
          <p>Users, roles, permissions — read-only. For audit pipelines, dashboards and BI tools. Mutation requires a session.</p>
        </div>
        <div class="scope reveal">
          <span class="scope-key">*</span>
          <h4>All read scopes</h4>
          <p>Issued via <code style="font-family:var(--font-mono);color:#67e8f9;">--abilities=&quot;*&quot;</code> on the artisan command. Convenience for internal tooling only.</p>
        </div>
      </div>
    </div>
  </section>

  {{-- ============ ENDPOINTS ============ --}}
  <section id="endpoints">
    <div class="container-wide">
      <div class="section-header">
        <span class="eyebrow">Endpoints — v1</span>
        <h2>Every CMS resource,<br><span class="text-gradient">reachable in a single call</span>.</h2>
        <p>Versioned via <code style="font-family:var(--font-mono);color:#67e8f9;">routes/api/v1.php</code>, picked up via glob — drop in v2 without touching the loader.</p>
      </div>

      <div class="endpoint-grid">
        @foreach([
          ['GET', 'get', '/api/v1/sites',                 'List all sites the key can see'],
          ['GET', 'get', '/api/v1/sites/{slug}',          'Single site + its categories'],
          ['GET', 'get', '/api/v1/categories',            'All categories, paginate + filter by site'],
          ['GET', 'get', '/api/v1/articles',              'Published articles. Filter by site / category / status'],
          ['GET', 'get', '/api/v1/articles/{id}',         'Single article with custom-field values'],
          ['GET', 'get', '/api/v1/menus/{name}',          'Recursive menu tree, cached'],
          ['GET', 'get', '/api/v1/media',                 'Media library with signed-URL access'],
          ['GET', 'get', '/api/v1/media/{id}',            'Single media item'],
          ['GET', 'get', '/api/v1/settings/public',       'Public, non-secret settings'],
          ['GET', 'get', '/api/v1/admin/users',           'Users — requires admin:read'],
          ['GET', 'get', '/api/v1/admin/roles',           'Roles + their permissions'],
          ['GET', 'get', '/api/v1/admin/permissions',     'Single source of truth — Permissions.php'],
        ] as $row)
          <div class="endpoint reveal">
            <span class="endpoint-method {{ $row[1] }}">{{ $row[0] }}</span>
            <div style="min-width: 0;">
              <p class="endpoint-path">{{ $row[2] }}</p>
              <p class="endpoint-desc">{{ $row[3] }}</p>
            </div>
          </div>
        @endforeach
      </div>
    </div>
  </section>

  {{-- ============ QUICKSTART ============ --}}
  <section id="quickstart">
    <div class="container">
      <div class="section-header">
        <span class="eyebrow">Quickstart</span>
        <h2>From zero to first request<br>in <span class="text-gradient">three commands</span>.</h2>
      </div>

      <div class="quickstart">
        <div class="qs-step reveal">
          <span class="qs-num">1</span>
          <div>
            <h4>Enable the API surface</h4>
            <p>Flip the <code>enable_api</code> setting in the admin (or set it in <code>SettingsTableSeeder.php</code>). It's off by default on new installs.</p>
            <code>Settings → API → enable_api = true</code>
          </div>
        </div>
        <div class="qs-step reveal">
          <span class="qs-num">2</span>
          <div>
            <h4>Issue a scoped key</h4>
            <p>One artisan command. The plaintext is shown once — copy it into your secret manager, the DB only ever stores the hash.</p>
            <code>php artisan api-key:create "Lookbook frontend" --abilities="content:read" --site_id=1</code>
          </div>
        </div>
        <div class="qs-step reveal">
          <span class="qs-num">3</span>
          <div>
            <h4>Hit your first endpoint</h4>
            <p>Send the key in the <code>X-API-Key</code> header on every request. That's it — no token exchange, no refresh dance.</p>
            <code>curl https://demo.agares.co.uk/api/v1/articles -H "X-API-Key: ak_live_..."</code>
          </div>
        </div>
      </div>
    </div>
  </section>

  {{-- ============ CTA ============ --}}
  <section>
    <div class="container">
      <div class="cta-banner reveal">
        <span class="badge badge-cyan mb-md">Headless-ready</span>
        <h2>Build the frontend you actually want.<br>The CMS just feeds it.</h2>
        <p>Next.js, Astro, SvelteKit, a native mobile app — all the same API, all the same admin behind it.</p>
        <div class="hero-buttons">
          <button type="button" class="btn btn-primary btn-lg btn-icon-after" data-demo-open>
            See API keys in the admin
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14M13 6l6 6-6 6"/></svg>
          </button>
          <a href="/documentation" class="btn btn-secondary btn-lg">Read full docs</a>
        </div>
      </div>
    </div>
  </section>

@stop

@push('scripts')
<script>
  (function () {
    document.querySelectorAll('.code-tabs').forEach((tabs) => {
      const btns = tabs.querySelectorAll('.code-tab');
      const panels = tabs.querySelectorAll('.code-tab-panel');
      btns.forEach((btn) => {
        btn.addEventListener('click', () => {
          const target = btn.dataset.tab;
          btns.forEach((b) => b.classList.toggle('active', b === btn));
          panels.forEach((p) => p.classList.toggle('active', p.dataset.panel === target));
        });
      });
    });
  })();
</script>
@endpush
