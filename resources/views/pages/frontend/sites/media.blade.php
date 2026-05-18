@extends('pages.frontend.base')

@push('styles')
<style>
  /* ============ Media library page extras ============ */

  /* MIME allowlist visual: two columns (allowed / blocked) */
  .mime-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: var(--space-xl);
  }
  @media (max-width: 800px) { .mime-grid { grid-template-columns: 1fr; } }

  .mime-card {
    padding: var(--space-xl);
    border-radius: var(--radius-xl);
    border: 1px solid var(--color-border);
    display: flex; flex-direction: column;
  }
  .mime-card.allowed {
    background: linear-gradient(180deg, rgba(52, 211, 153, 0.06), transparent 70%);
    border-color: rgba(52, 211, 153, 0.25);
  }
  .mime-card.blocked {
    background: linear-gradient(180deg, rgba(248, 113, 113, 0.05), transparent 70%);
    border-color: rgba(248, 113, 113, 0.25);
  }
  .mime-card-label {
    display: inline-flex; align-items: center; gap: 0.5rem;
    padding: 0.25rem 0.6rem;
    border-radius: var(--radius-full);
    font-family: var(--font-mono);
    font-size: 0.7rem;
    text-transform: uppercase;
    letter-spacing: 0.08em;
    margin-bottom: var(--space-md);
  }
  .mime-card.allowed .mime-card-label { background: rgba(52, 211, 153, 0.12); color: #6ee7b7; border: 1px solid rgba(52, 211, 153, 0.3); }
  .mime-card.blocked .mime-card-label { background: rgba(248, 113, 113, 0.10); color: #fca5a5; border: 1px solid rgba(248, 113, 113, 0.3); }
  .mime-card h3 {
    font-family: var(--font-display);
    font-size: var(--text-xl);
    margin-bottom: var(--space-md);
    letter-spacing: -0.02em;
  }
  .mime-tag-cloud {
    display: flex; flex-wrap: wrap; gap: 0.4rem;
    margin-top: var(--space-md);
  }
  .mime-tag {
    padding: 0.3rem 0.7rem;
    background: rgba(7, 8, 13, 0.5);
    border: 1px solid var(--color-border-hover);
    border-radius: var(--radius-sm);
    font-family: var(--font-mono);
    font-size: 0.78rem;
    color: var(--color-text-secondary);
  }
  .mime-card.allowed .mime-tag { border-color: rgba(52, 211, 153, 0.25); }
  .mime-card.blocked .mime-tag { border-color: rgba(248, 113, 113, 0.25); color: #fca5a5; }
  .mime-card p { font-size: 0.85rem; color: var(--color-text-secondary); margin: 0 0 var(--space-md); line-height: 1.6; }

  /* Gallery mock — image grid */
  .lib-mock {
    background: var(--color-bg-secondary);
    border: 1px solid var(--color-border-hover);
    border-radius: var(--radius-xl);
    overflow: hidden;
    box-shadow: var(--shadow-xl);
  }
  .lib-bar {
    display: flex; align-items: center; gap: 0.5rem;
    padding: 0.5rem 0.85rem;
    background: rgba(255, 255, 255, 0.025);
    border-bottom: 1px solid var(--color-border);
  }
  .lib-bar .dot { width: 9px; height: 9px; border-radius: 50%; }
  .lib-bar .dot:nth-child(1) { background: #ff5f57; }
  .lib-bar .dot:nth-child(2) { background: #febc2e; }
  .lib-bar .dot:nth-child(3) { background: #28c840; }
  .lib-bar .title {
    margin-left: auto; margin-right: auto;
    font-family: var(--font-mono);
    font-size: 0.72rem;
    color: var(--color-text-tertiary);
  }
  .lib-body {
    padding: var(--space-lg);
  }
  .lib-toolbar {
    display: flex; align-items: center; gap: var(--space-sm);
    margin-bottom: var(--space-md);
    padding-bottom: var(--space-md);
    border-bottom: 1px solid var(--color-border);
  }
  .lib-search {
    flex: 1;
    padding: 0.5rem 0.8rem;
    background: rgba(7, 8, 13, 0.5);
    border: 1px solid var(--color-border);
    border-radius: var(--radius-sm);
    font-family: var(--font-mono);
    font-size: 0.78rem;
    color: var(--color-text-tertiary);
  }
  .lib-filter {
    padding: 0.4rem 0.7rem;
    background: var(--color-surface);
    border: 1px solid var(--color-border);
    border-radius: var(--radius-sm);
    font-family: var(--font-mono);
    font-size: 0.72rem;
    color: var(--color-text-secondary);
    text-transform: uppercase;
    letter-spacing: 0.06em;
  }
  .lib-filter.active { background: rgba(139, 92, 246, 0.12); color: #c4b5fd; border-color: rgba(139, 92, 246, 0.3); }
  .lib-grid {
    display: grid;
    grid-template-columns: repeat(5, 1fr);
    gap: 0.6rem;
  }
  @media (max-width: 800px) { .lib-grid { grid-template-columns: repeat(3, 1fr); } }
  @media (max-width: 480px) { .lib-grid { grid-template-columns: repeat(2, 1fr); } }
  .lib-tile {
    aspect-ratio: 1;
    background: linear-gradient(135deg, rgba(139, 92, 246, 0.15), rgba(34, 211, 238, 0.08));
    border: 1px solid var(--color-border-hover);
    border-radius: var(--radius-sm);
    display: flex; align-items: center; justify-content: center;
    position: relative;
    transition: all var(--transition-base);
  }
  .lib-tile:hover { transform: scale(1.02); border-color: var(--color-border-strong); }
  .lib-tile svg { color: rgba(255, 255, 255, 0.5); }
  .lib-tile.selected { box-shadow: 0 0 0 2px var(--color-accent-primary); }
  .lib-tile-label {
    position: absolute;
    bottom: 0; left: 0; right: 0;
    padding: 0.25rem 0.4rem;
    background: rgba(7, 8, 13, 0.7);
    backdrop-filter: blur(4px);
    font-family: var(--font-mono);
    font-size: 0.6rem;
    color: var(--color-text-tertiary);
    text-align: center;
    border-radius: 0 0 var(--radius-sm) var(--radius-sm);
  }

  /* Feature grid */
  .media-features {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: var(--space-lg);
  }
  @media (max-width: 1000px) { .media-features { grid-template-columns: repeat(2, 1fr); } }
  @media (max-width: 600px)  { .media-features { grid-template-columns: 1fr; } }

  .media-feature {
    padding: var(--space-xl);
    background: var(--color-surface);
    border: 1px solid var(--color-border);
    border-radius: var(--radius-lg);
    transition: all var(--transition-base);
  }
  .media-feature:hover { border-color: var(--color-border-hover); transform: translateY(-2px); }
  .media-feature-ico {
    width: 44px; height: 44px;
    display: inline-flex; align-items: center; justify-content: center;
    background: rgba(251, 191, 36, 0.12);
    border: 1px solid rgba(251, 191, 36, 0.3);
    color: #fde68a;
    border-radius: var(--radius-md);
    margin-bottom: var(--space-md);
  }
  .media-feature h4 { font-family: var(--font-display); font-size: 1.1rem; margin-bottom: 0.4rem; letter-spacing: -0.015em; }
  .media-feature p { font-size: 0.88rem; color: var(--color-text-secondary); margin: 0; line-height: 1.65; }
</style>
@endpush

@section('content')

  {{-- ============ HERO ============ --}}
  <section class="hero">
    <div class="container">
      <div class="hero-eyebrow">
        <span class="pill">MEDIA LIBRARY</span>
        <span>One library · every site · MIME allowlist · signed uploads</span>
      </div>

      <h1 class="hero-title">
        Upload once.<br>
        <span class="text-gradient-magic">Reuse everywhere.</span>
      </h1>

      <p class="hero-subtitle">
        A single media library shared across every site in your install. Galleries with
        many-to-many pivots, a strict MIME allowlist that won't accept <code style="font-family:var(--font-mono);color:#67e8f9;">.php</code>,
        signed uploads, and per-site usage tracking so you know what's referenced where.
      </p>

      <div class="hero-buttons">
        <button type="button" class="btn btn-primary btn-lg btn-icon-after" data-demo-open>
          Open the library
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14M13 6l6 6-6 6"/></svg>
        </button>
        <a href="/multi-site" class="btn btn-secondary btn-lg">
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="7" height="7" rx="1"/><rect x="14" y="3" width="7" height="7" rx="1"/><rect x="14" y="14" width="7" height="7" rx="1"/><rect x="3" y="14" width="7" height="7" rx="1"/></svg>
          How multi-site uses it
        </a>
      </div>

      <div class="hero-stats">
        <div class="hero-stat"><div class="num">1</div><div class="label">Library, all sites</div></div>
        <div class="hero-stat"><div class="num">∞</div><div class="label">Galleries</div></div>
        <div class="hero-stat"><div class="num">0</div><div class="label">.php uploads accepted</div></div>
        <div class="hero-stat"><div class="num">100%</div><div class="label">Signed URLs</div></div>
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
            <span class="preview-url">agares.app/admin/media</span>
          </div>
          <div class="preview-content">
            <img src="{{ asset('assets/frontend/images/agares_cms_media.jpg') }}" alt="Agares media library — image grid, filters, upload zone" loading="eager" fetchpriority="high">
          </div>
        </div>
      </div>
    </div>
  </section>

  {{-- ============ LIBRARY MOCK ============ --}}
  <section style="padding-top: var(--space-2xl);">
    <div class="container-wide">
      <div class="section-header">
        <span class="eyebrow">Inside the library</span>
        <h2>One grid.<br><span class="text-gradient">Every asset, every site</span>.</h2>
        <p>Filter by site, by gallery, by file type. Search by filename or alt text. Drag to upload.</p>
      </div>

      <div class="lib-mock reveal">
        <div class="lib-bar">
          <span class="dot"></span><span class="dot"></span><span class="dot"></span>
          <span class="title">Media library — all sites</span>
        </div>
        <div class="lib-body">
          <div class="lib-toolbar">
            <span class="lib-search">🔍 Search by filename, alt, gallery…</span>
            <span class="lib-filter active">All</span>
            <span class="lib-filter">Images</span>
            <span class="lib-filter">PDF</span>
            <span class="lib-filter">Video</span>
          </div>

          <div class="lib-grid">
            @php
              // Pull 5 real screenshots we already have, cycle to fill the grid
              $tiles = [
                'agares_cms_dashboard.jpg', 'agares_cms_sites.jpg', 'agares_cms_edit.jpg',
                'agares_cms_media.jpg', 'agares_cms_menus.jpg',
                'pcml-home.jpg', 'pcml-hotel.jpg', 'pcml-oferta.jpg',
                'saas-dashboard.jpg', 'saas-rag.jpg',
                'agares_cms_payments.jpg', 'agares_cms_permissions.jpg',
                'agares_cms_cookies.jpg', 'agares_cms_settings.jpg', 'agares_cms_settings_addons.jpg',
              ];
            @endphp
            @foreach($tiles as $i => $t)
              <div class="lib-tile {{ $i === 3 ? 'selected' : '' }}" style="background-image: url('{{ asset('assets/frontend/images/' . $t) }}'); background-size: cover; background-position: center;">
                <span class="lib-tile-label">{{ explode('.', $t)[0] }}</span>
              </div>
            @endforeach
          </div>
        </div>
      </div>
    </div>
  </section>

  {{-- ============ MIME ALLOWLIST ============ --}}
  <section>
    <div class="container-wide">
      <div class="section-header">
        <span class="eyebrow">The allowlist</span>
        <h2>What's accepted.<br><span class="text-gradient">What never is</span>.</h2>
        <p>Authenticated user ≠ trusted upload. The MIME check is enforced at the controller, on every upload, no exceptions.</p>
      </div>

      <div class="mime-grid">
        <div class="mime-card allowed reveal">
          <span class="mime-card-label"><svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg> Allowed</span>
          <h3>What you can upload</h3>
          <p>Common content types — images, documents, video, audio. Sized-checked too: per-file limit configurable in settings.</p>
          <div class="mime-tag-cloud">
            <span class="mime-tag">.jpg</span>
            <span class="mime-tag">.jpeg</span>
            <span class="mime-tag">.png</span>
            <span class="mime-tag">.gif</span>
            <span class="mime-tag">.webp</span>
            <span class="mime-tag">.svg</span>
            <span class="mime-tag">.pdf</span>
            <span class="mime-tag">.doc</span>
            <span class="mime-tag">.docx</span>
            <span class="mime-tag">.xls</span>
            <span class="mime-tag">.xlsx</span>
            <span class="mime-tag">.zip</span>
            <span class="mime-tag">.mp4</span>
            <span class="mime-tag">.webm</span>
            <span class="mime-tag">.mp3</span>
            <span class="mime-tag">.wav</span>
          </div>
        </div>

        <div class="mime-card blocked reveal">
          <span class="mime-card-label"><svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg> Blocked</span>
          <h3>What's rejected at the door</h3>
          <p>Anything server-executable. Even with a fake MIME type — we check the actual file signature, not just what the browser claims.</p>
          <div class="mime-tag-cloud">
            <span class="mime-tag">.php</span>
            <span class="mime-tag">.phtml</span>
            <span class="mime-tag">.phar</span>
            <span class="mime-tag">.php3 / .php4 / .php5</span>
            <span class="mime-tag">.cgi</span>
            <span class="mime-tag">.pl</span>
            <span class="mime-tag">.py</span>
            <span class="mime-tag">.sh</span>
            <span class="mime-tag">.bat</span>
            <span class="mime-tag">.exe</span>
            <span class="mime-tag">.dll</span>
            <span class="mime-tag">.htaccess</span>
            <span class="mime-tag">.htpasswd</span>
          </div>
        </div>
      </div>
    </div>
  </section>

  {{-- ============ FEATURES ============ --}}
  <section>
    <div class="container-wide">
      <div class="section-header">
        <span class="eyebrow">What the library does</span>
        <h2>More than a folder.<br><span class="text-gradient">An asset graph</span>.</h2>
      </div>

      <div class="media-features">
        @foreach([
          ['M3 3h18v18H3z M3 9h18 M9 21V9', 'Galleries', 'Many-to-many pivot between media and galleries. One image can live in N galleries. One gallery, N sites.'],
          ['M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z', 'Per-site usage tracking', 'Hover any asset to see every page it\'s referenced on. Delete with confidence — you know what breaks.'],
          ['M14.5 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V7.5L14.5 2z M14 2v6h6 M16 13H8 M16 17H8 M10 9H8', 'Alt text first-class', 'Every upload prompts for alt text. SEO and accessibility — both win.'],
          ['M22 11.08V12a10 10 0 1 1-5.93-9.14 M22 4 12 14.01l-3-3', 'MIME allowlist', '<code style="font-family:var(--font-mono);font-size:0.85em;color:#67e8f9;">MediaController::ALLOWED_EXTENSIONS</code> is the single source. Server-side, file-signature checked.'],
          ['M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z', 'Signed URLs', 'Uploaded files served via signed, time-limited URLs. Direct path enumeration won\'t work.'],
          ['M9 11l3 3L22 4 M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11', 'Drag &amp; drop upload', 'Drop multiple files at once. Per-file progress bar, per-file error report, per-file retry.'],
          ['M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2 M12 7a4 4 0 1 0 0 8 4 4 0 0 0 0-8z', 'Per-site permissions', '<code style="font-family:var(--font-mono);font-size:0.85em;color:#67e8f9;">view media</code> / <code style="font-family:var(--font-mono);font-size:0.85em;color:#67e8f9;">manage media</code> per role. Site-scoped grants if you need finer.'],
          ['M16 18l6-6-6-6 M8 6l-6 6 6 6', 'API-accessible', 'Reachable via REST API with <code style="font-family:var(--font-mono);font-size:0.85em;color:#67e8f9;">media:read</code> scope. Build a Next.js front-end and reuse the same assets.'],
          ['M3 6h18 M19 6v14c0 1-1 2-2 2H7c-1 0-2-1-2-2V6 M8 6V4c0-1 1-2 2-2h4c1 0 2 1 2 2v2', 'Soft delete + restore', 'Deleted assets go to a 30-day bin. Restore in one click. Hard-delete needs an explicit confirm.'],
        ] as $f)
          <div class="media-feature reveal">
            <div class="media-feature-ico">
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
        <span class="badge badge-warning mb-md">Library demo</span>
        <h2>Upload an image. Reuse it<br><span class="text-gradient">across two client sites</span>.</h2>
        <p>The demo lets you upload a file, attach it to a gallery, see it appear on whatever page references that gallery. Try a <code style="font-family:var(--font-mono);font-size:0.85em;color:#67e8f9;">.php</code> upload — watch it bounce.</p>
        <div class="hero-buttons">
          <button type="button" class="btn btn-primary btn-lg btn-icon-after" data-demo-open>
            Open the media library
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14M13 6l6 6-6 6"/></svg>
          </button>
          <a href="/security" class="btn btn-secondary btn-lg">Why the allowlist matters</a>
        </div>
      </div>
    </div>
  </section>

@stop
