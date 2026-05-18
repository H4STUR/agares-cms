@extends('pages.frontend.base')

@push('styles')
<style>
  /* ============ Blog engine page extras ============ */

  /* Mock blog feed — sample article cards */
  .blog-mock {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: var(--space-lg);
  }
  @media (max-width: 1000px) { .blog-mock { grid-template-columns: repeat(2, 1fr); } }
  @media (max-width: 600px)  { .blog-mock { grid-template-columns: 1fr; } }

  .blog-article {
    background: linear-gradient(180deg, rgba(255, 255, 255, 0.035), rgba(255, 255, 255, 0.01));
    border: 1px solid var(--color-border);
    border-radius: var(--radius-xl);
    overflow: hidden;
    transition: all var(--transition-base);
    display: flex;
    flex-direction: column;
  }
  .blog-article:hover { border-color: var(--color-border-hover); transform: translateY(-3px); }
  .blog-article-cover {
    aspect-ratio: 16 / 9;
    position: relative;
    background: linear-gradient(135deg, rgba(139, 92, 246, 0.20), rgba(34, 211, 238, 0.10));
    display: flex; align-items: center; justify-content: center;
    overflow: hidden;
  }
  .blog-article-cover::before {
    content: '';
    position: absolute;
    inset: 0;
    background-image:
      linear-gradient(rgba(255, 255, 255, 0.05) 1px, transparent 1px),
      linear-gradient(90deg, rgba(255, 255, 255, 0.05) 1px, transparent 1px);
    background-size: 28px 28px;
  }
  .blog-article-cover .glyph {
    position: relative;
    width: 64px; height: 64px;
    display: inline-flex; align-items: center; justify-content: center;
    background: rgba(7, 8, 13, 0.6);
    backdrop-filter: blur(10px);
    border: 1px solid var(--color-border-hover);
    border-radius: var(--radius-md);
    color: var(--color-text-primary);
  }
  .blog-article-body { padding: var(--space-lg) var(--space-xl) var(--space-xl); display: flex; flex-direction: column; flex: 1; }
  .blog-article-meta { display: flex; gap: 0.5rem; align-items: center; margin-bottom: var(--space-sm); }
  .blog-article-cat {
    padding: 0.2rem 0.6rem;
    background: rgba(139, 92, 246, 0.12);
    border: 1px solid rgba(139, 92, 246, 0.3);
    border-radius: var(--radius-sm);
    font-family: var(--font-mono);
    font-size: 0.68rem;
    color: #c4b5fd;
    text-transform: uppercase;
    letter-spacing: 0.06em;
  }
  .blog-article-date { font-family: var(--font-mono); font-size: 0.7rem; color: var(--color-text-tertiary); }
  .blog-article h3 {
    font-family: var(--font-display);
    font-size: 1.15rem;
    margin-bottom: var(--space-sm);
    letter-spacing: -0.015em;
    line-height: 1.3;
  }
  .blog-article p { font-size: 0.85rem; color: var(--color-text-secondary); margin: 0 0 var(--space-md); line-height: 1.6; flex: 1; }
  .blog-article-author {
    display: flex; align-items: center; gap: 0.55rem;
    padding-top: var(--space-md);
    border-top: 1px solid var(--color-border);
    font-size: 0.78rem;
    color: var(--color-text-tertiary);
  }
  .blog-article-author .avatar {
    width: 22px; height: 22px;
    border-radius: 50%;
    background: var(--color-accent-gradient);
    display: inline-flex; align-items: center; justify-content: center;
    color: white;
    font-family: var(--font-display);
    font-size: 0.65rem;
    font-weight: 700;
  }

  /* Feature row */
  .blog-feature-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: var(--space-lg);
  }
  @media (max-width: 1000px) { .blog-feature-grid { grid-template-columns: repeat(2, 1fr); } }
  @media (max-width: 600px)  { .blog-feature-grid { grid-template-columns: 1fr; } }

  .blog-feature {
    padding: var(--space-xl);
    background: var(--color-surface);
    border: 1px solid var(--color-border);
    border-radius: var(--radius-lg);
    transition: all var(--transition-base);
  }
  .blog-feature:hover { border-color: var(--color-border-hover); transform: translateY(-2px); }
  .blog-feature-ico {
    width: 44px; height: 44px;
    display: inline-flex; align-items: center; justify-content: center;
    background: rgba(52, 211, 153, 0.12);
    border: 1px solid rgba(52, 211, 153, 0.3);
    color: #6ee7b7;
    border-radius: var(--radius-md);
    margin-bottom: var(--space-md);
  }
  .blog-feature h4 { font-family: var(--font-display); font-size: 1.1rem; margin-bottom: 0.4rem; letter-spacing: -0.015em; }
  .blog-feature p { font-size: 0.88rem; color: var(--color-text-secondary); margin: 0; line-height: 1.65; }

  /* SEO checklist */
  .seo-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 0.6rem;
  }
  @media (max-width: 700px) { .seo-grid { grid-template-columns: 1fr; } }
  .seo-row {
    display: flex; gap: 0.6rem; align-items: flex-start;
    padding: 0.75rem 1rem;
    background: var(--color-surface);
    border: 1px solid var(--color-border);
    border-radius: var(--radius-md);
    font-size: 0.85rem;
    color: var(--color-text-secondary);
  }
  .seo-row svg { flex-shrink: 0; margin-top: 3px; color: var(--color-accent-green); }
</style>
@endpush

@section('content')

  {{-- ============ HERO ============ --}}
  <section class="hero">
    <div class="container">
      <div class="hero-eyebrow">
        <span class="pill">BLOG ENGINE</span>
        <span>Categories · drafts · scheduling · SEO · RSS-ready</span>
      </div>

      <h1 class="hero-title">
        A blog engine<br>
        <span class="text-gradient-magic">your editors will love.</span>
      </h1>

      <p class="hero-subtitle">
        Hierarchical content — Sites → Categories → Articles — with drafts, scheduling,
        SEO meta per article, and a permissions model that lets writers write without
        breaking the build. No plugin, no separate database.
      </p>

      <div class="hero-buttons">
        <button type="button" class="btn btn-primary btn-lg btn-icon-after" data-demo-open>
          See the blog in the demo
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14M13 6l6 6-6 6"/></svg>
        </button>
        <a href="#features" class="btn btn-secondary btn-lg">
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/></svg>
          See the features
        </a>
      </div>

      <div class="hero-stats">
        <div class="hero-stat"><div class="num">4</div><div class="label">Status states</div></div>
        <div class="hero-stat"><div class="num">∞</div><div class="label">Categories per site</div></div>
        <div class="hero-stat"><div class="num">3</div><div class="label">Levels of cascading meta</div></div>
        <div class="hero-stat"><div class="num">100%</div><div class="label">CMS-managed</div></div>
      </div>
    </div>
  </section>

  {{-- ============ MOCK BLOG FEED ============ --}}
  <section style="padding-top: var(--space-2xl);">
    <div class="container-wide">
      <div class="section-header">
        <span class="eyebrow">What your visitors see</span>
        <h2>A real blog,<br><span class="text-gradient">in your brand's voice</span>.</h2>
        <p>Categories, tags, author bylines, hero images, SEO meta — all rendered through your Blade templates, all manageable from the admin.</p>
      </div>

      <div class="blog-mock">
        @foreach([
          ['Product', '2026-05-15', 'Two-factor auth shipped — TOTP, email OTP, OAuth coverage', 'Phase 3 of the 2FA rollout closes the OAuth gap and ships a queryable security audit log. Here\'s how each layer composes.', 'LM'],
          ['Engineering', '2026-05-13', 'Newsletter Phase 3: delegating bulk send to the SaaS', 'Why we kept the CMS queue-free and instead built a signed HTTP contract with the Agares SaaS. Trade-offs and the contract spec.', 'LM'],
          ['Case study', '2026-05-08', 'PiesCiMordeLizal: a hospitality site managed by its owners', 'Five sections, all editable. The team hasn\'t opened the codebase in months — and that\'s exactly the point.', 'LM'],
          ['Tutorial', '2026-05-02', 'Custom fields in 10 minutes: building a recipe template', 'Walking through the Input System with a worked example. Template → Items → Instance → Blade output.', 'LM'],
          ['Engineering', '2026-04-28', 'Defense-in-depth: why route gating wasn\'t enough', 'Adding controller-level HasMiddleware was the second wall. The reasoning, the tests, and the migration plan.', 'LM'],
          ['Roadmap', '2026-04-21', 'What\'s next: forum, reservations, and a unified search API', 'A look at the three modules we\'re prototyping for the second half of the year.', 'LM'],
        ] as $idx => $a)
          <article class="blog-article reveal">
            <div class="blog-article-cover">
              <div class="glyph">
                @if($idx === 0)
                  <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                @elseif($idx === 1)
                  <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg>
                @elseif($idx === 2)
                  <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
                @elseif($idx === 3)
                  <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><polyline points="16 18 22 12 16 6"/><polyline points="8 6 2 12 8 18"/></svg>
                @elseif($idx === 4)
                  <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="18" height="18" rx="2"/><line x1="9" y1="9" x2="15" y2="15"/><line x1="15" y1="9" x2="9" y2="15"/></svg>
                @else
                  <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                @endif
              </div>
            </div>
            <div class="blog-article-body">
              <div class="blog-article-meta">
                <span class="blog-article-cat">{{ $a[0] }}</span>
                <span class="blog-article-date">{{ $a[1] }}</span>
              </div>
              <h3>{{ $a[2] }}</h3>
              <p>{{ $a[3] }}</p>
              <div class="blog-article-author">
                <span class="avatar">{{ $a[4] }}</span>
                <span>By Łukasz Majerski</span>
              </div>
            </div>
          </article>
        @endforeach
      </div>

      <p style="text-align: center; margin-top: var(--space-xl); font-size: 0.85rem; color: var(--color-text-tertiary);">
        Layout, hero images, byline format — all controlled by your Blade templates. The admin only manages content.
      </p>
    </div>
  </section>

  {{-- ============ FEATURE ROW ============ --}}
  <section id="features">
    <div class="container-wide">
      <div class="section-header">
        <span class="eyebrow">Everything you'd expect</span>
        <h2>Built like a serious<br><span class="text-gradient">content platform</span>.</h2>
        <p>Not "the WordPress quick-setup" — a full editorial workflow with the boring stuff already done.</p>
      </div>

      <div class="blog-feature-grid">
        @foreach([
          ['M12 8v4l3 3', 'Scheduled publishing', 'Set a publish-at date. Articles flip to published automatically on the request that reads them — no scheduler, no cron.'],
          ['M21 15a4 4 0 0 1-4 4H7l-4 4V7a4 4 0 0 1 4-4h10a4 4 0 0 1 4 4z', 'Categories &amp; tags', 'Sites → Categories → Articles. Pivot tables for tags. Filter and paginate in the admin or via the REST API.'],
          ['M9 11l3 3L22 4', 'Draft &amp; private', 'Four status states: draft, published, scheduled, private. The scopePublic() query scope makes frontend filtering trivial.'],
          ['M21 12a9 9 0 1 1-18 0M3 12l9-9 9 9', 'Per-article SEO', 'Title, description, keywords, OG image — per article, with cascade to category and site defaults.'],
          ['M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z', 'Hierarchical templates', 'Pick a Blade template per article. Magazine layout for some, photo essay for others — same admin.'],
          ['M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z', 'Editorial permissions', 'view unpublished content lets editors preview drafts. manage articles controls who can publish.'],
        ] as $f)
          <div class="blog-feature reveal">
            <div class="blog-feature-ico">
              <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="{{ $f[0] }}"/></svg>
            </div>
            <h4>{!! $f[1] !!}</h4>
            <p>{{ $f[2] }}</p>
          </div>
        @endforeach
      </div>
    </div>
  </section>

  {{-- ============ SEO + REST API SPLIT ============ --}}
  <section>
    <div class="container-wide">
      <div class="split">
        <div>
          <span class="eyebrow">SEO, baked in</span>
          <h2 style="margin-bottom: var(--space-md);">Every checklist box,<br><span class="text-gradient">already ticked</span>.</h2>
          <p style="color: var(--color-text-secondary); font-size: var(--text-lg); margin-bottom: var(--space-xl); line-height: 1.65;">
            Per-article meta with cascade to category and site defaults. The shared
            <code style="font-family:var(--font-mono);color:#67e8f9;">seo.blade.php</code> snippet handles canonical URLs,
            Open Graph, Twitter cards, robots and structured data — no plugin.
          </p>

          <div class="seo-grid">
            @foreach([
              'Per-article title + description',
              'Canonical URLs auto-set',
              'Open Graph tags',
              'Twitter Card tags',
              'JSON-LD structured data ready',
              'robots / googlebot meta',
              'Cascading site → category → article',
              'Editable in admin, no code',
            ] as $item)
              <div class="seo-row reveal">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
                <span>{{ $item }}</span>
              </div>
            @endforeach
          </div>
        </div>

        <div>
          <span class="eyebrow">Headless-friendly</span>
          <h2 style="margin-bottom: var(--space-md);">Or skip the templates.<br><span class="text-gradient">Just pull the JSON</span>.</h2>
          <p style="color: var(--color-text-secondary); font-size: var(--text-lg); margin-bottom: var(--space-xl); line-height: 1.65;">
            Every article, every category, every menu is reachable via the REST API
            with a scoped <code style="font-family:var(--font-mono);color:#67e8f9;">content:read</code> key.
            Build a Next.js front-end and keep the same editorial admin.
          </p>

          <div class="code-window">
            <div class="code-window-header">
              <span class="preview-dot"></span><span class="preview-dot"></span><span class="preview-dot"></span>
              <span class="code-window-title">GET /api/v1/articles?site=blog</span>
            </div>
<pre><span class="punct">{</span>
  <span class="str">"data"</span>: <span class="punct">[</span>
    <span class="punct">{</span>
      <span class="str">"id"</span>: <span class="num">42</span>,
      <span class="str">"title"</span>: <span class="str">"Two-factor auth shipped"</span>,
      <span class="str">"slug"</span>: <span class="str">"2fa-shipped"</span>,
      <span class="str">"status"</span>: <span class="str">"published"</span>,
      <span class="str">"published_at"</span>: <span class="str">"2026-05-15T09:00:00Z"</span>,
      <span class="str">"category"</span>: <span class="str">"product"</span>,
      <span class="str">"author"</span>: <span class="str">"Łukasz Majerski"</span>,
      <span class="str">"seo"</span>: <span class="punct">{</span>
        <span class="str">"description"</span>: <span class="str">"Phase 3 closes OAuth..."</span>,
        <span class="str">"og_image"</span>: <span class="str">"/media/2fa.jpg"</span>
      <span class="punct">}</span>
    <span class="punct">}</span>
  <span class="punct">]</span>
<span class="punct">}</span></pre>
          </div>
          <a href="/api" class="btn btn-ghost mt-lg btn-icon-after">
            Full API reference →
          </a>
        </div>
      </div>
    </div>
  </section>

  {{-- ============ CTA ============ --}}
  <section>
    <div class="container">
      <div class="cta-banner reveal">
        <span class="badge badge-success mb-md">Editorial-ready</span>
        <h2>Publish your first article<br>in <span class="text-gradient">under five minutes</span>.</h2>
        <p>The demo lets you create a draft, schedule it, and see it appear on the frontend exactly as readers will.</p>
        <div class="hero-buttons">
          <button type="button" class="btn btn-primary btn-lg btn-icon-after" data-demo-open>
            Try the editorial flow
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14M13 6l6 6-6 6"/></svg>
          </button>
          <a href="/page-editor" class="btn btn-secondary btn-lg">See the page editor</a>
        </div>
      </div>
    </div>
  </section>

@stop
