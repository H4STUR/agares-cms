@extends('pages.frontend.base')

@push('styles')
<style>
  /* ============ Page editor page extras ============ */

  .audience-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: var(--space-xl);
  }
  @media (max-width: 900px) { .audience-grid { grid-template-columns: 1fr; } }

  .audience-card {
    padding: var(--space-2xl);
    border-radius: var(--radius-xl);
    border: 1px solid var(--color-border);
    position: relative;
    overflow: hidden;
    transition: all var(--transition-base);
  }
  .audience-card:hover { border-color: var(--color-border-hover); transform: translateY(-3px); }
  .audience-card.teams {
    background: linear-gradient(160deg, rgba(244, 114, 182, 0.10), transparent 60%);
  }
  .audience-card.devs {
    background: linear-gradient(160deg, rgba(34, 211, 238, 0.10), transparent 60%);
  }
  .audience-eyebrow {
    display: inline-flex; align-items: center; gap: 0.5rem;
    padding: 0.3rem 0.75rem;
    border-radius: var(--radius-full);
    font-family: var(--font-mono);
    font-size: 0.7rem;
    text-transform: uppercase;
    letter-spacing: 0.1em;
    margin-bottom: var(--space-md);
  }
  .audience-card.teams .audience-eyebrow { background: rgba(244, 114, 182, 0.12); color: #f9a8d4; border: 1px solid rgba(244, 114, 182, 0.3); }
  .audience-card.devs  .audience-eyebrow { background: rgba(34, 211, 238, 0.12); color: #67e8f9; border: 1px solid rgba(34, 211, 238, 0.3); }
  .audience-card h3 {
    font-size: var(--text-2xl);
    margin-bottom: var(--space-md);
    letter-spacing: -0.025em;
  }
  .audience-card p { color: var(--color-text-secondary); font-size: var(--text-base); margin-bottom: var(--space-lg); line-height: 1.65; }
  .audience-list { list-style: none; padding: 0; margin: 0; }
  .audience-list li {
    display: flex; gap: 0.55rem; align-items: flex-start;
    padding: 0.45rem 0;
    font-size: 0.88rem;
    color: var(--color-text-secondary);
  }
  .audience-list li svg { flex-shrink: 0; margin-top: 4px; color: var(--color-accent-green); }

  /* Field types showcase */
  .field-types {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: var(--space-md);
  }
  @media (max-width: 1000px) { .field-types { grid-template-columns: repeat(2, 1fr); } }
  @media (max-width: 540px)  { .field-types { grid-template-columns: 1fr; } }

  .field-type {
    padding: var(--space-lg);
    background: var(--color-surface);
    border: 1px solid var(--color-border);
    border-radius: var(--radius-lg);
    transition: all var(--transition-base);
  }
  .field-type:hover { border-color: var(--color-border-hover); transform: translateY(-2px); }
  .field-type-ico {
    width: 40px; height: 40px;
    display: inline-flex; align-items: center; justify-content: center;
    background: rgba(139, 92, 246, 0.12);
    border: 1px solid rgba(139, 92, 246, 0.25);
    border-radius: var(--radius-sm);
    color: #c4b5fd;
    margin-bottom: var(--space-md);
  }
  .field-type h4 { font-family: var(--font-display); font-size: 1rem; margin-bottom: 0.3rem; letter-spacing: -0.01em; }
  .field-type p { font-size: 0.82rem; color: var(--color-text-tertiary); margin: 0; line-height: 1.5; }
  .field-type .var {
    display: inline-block;
    margin-top: 0.5rem;
    font-family: var(--font-mono);
    font-size: 0.72rem;
    color: #67e8f9;
  }

  /* Workflow timeline */
  .workflow {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: var(--space-lg);
    position: relative;
  }
  @media (max-width: 1000px) { .workflow { grid-template-columns: repeat(2, 1fr); } }
  @media (max-width: 540px)  { .workflow { grid-template-columns: 1fr; } }

  .workflow-step {
    padding: var(--space-lg);
    background: var(--color-surface);
    border: 1px solid var(--color-border);
    border-radius: var(--radius-lg);
    transition: all var(--transition-base);
    position: relative;
  }
  .workflow-step:hover { border-color: var(--color-border-hover); transform: translateY(-2px); }
  .workflow-step-num {
    font-family: var(--font-mono);
    font-size: 0.75rem;
    color: var(--color-accent-secondary);
    margin-bottom: 0.4rem;
    letter-spacing: 0.1em;
  }
  .workflow-step h4 { font-family: var(--font-display); font-size: 1.1rem; margin-bottom: 0.4rem; letter-spacing: -0.01em; }
  .workflow-step p { font-size: 0.85rem; color: var(--color-text-secondary); margin: 0; line-height: 1.6; }
</style>
@endpush

@section('content')

  {{-- ============ HERO ============ --}}
  <section class="hero">
    <div class="container">
      <div class="hero-eyebrow">
        <span class="pill">PAGE EDITOR</span>
        <span>Visual mode · Blade mode · Monaco · per-role gating</span>
      </div>

      <h1 class="hero-title">
        The editor your team<br>
        <span class="text-gradient-magic">won't fight with.</span>
      </h1>

      <p class="hero-subtitle">
        A visual editor your content team gets in five minutes, and a Blade escape hatch for
        when you need full control. Monaco-powered code injection, polymorphic custom fields,
        per-role gating — same screen, no plugins, no juggling.
      </p>

      <div class="hero-buttons">
        <button type="button" class="btn btn-primary btn-lg btn-icon-after" data-demo-open>
          Open the editor
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14M13 6l6 6-6 6"/></svg>
        </button>
        <a href="#fields" class="btn btn-secondary btn-lg">
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="18" height="18" rx="2"/><path d="M3 9h18M9 21V9"/></svg>
          Browse the field types
        </a>
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
            <span class="preview-url">agares.app/admin/articles/42/edit</span>
          </div>
          <div class="preview-content">
            <img src="{{ asset('assets/frontend/images/agares_cms_edit.jpg') }}" alt="Agares page editor with custom fields, code injection and live preview" loading="eager" fetchpriority="high">
          </div>
        </div>
      </div>
    </div>
  </section>

  {{-- ============ TWO AUDIENCES ============ --}}
  <section style="padding-top: var(--space-2xl);">
    <div class="container-wide">
      <div class="section-header">
        <span class="eyebrow">Two minds, one editor</span>
        <h2>Whichever side of the team<br>opens it, they <span class="text-gradient">feel at home</span>.</h2>
        <p>The same page can be edited visually by a writer, then code-tuned by a developer — no copy-paste, no exports.</p>
      </div>

      <div class="audience-grid">
        <div class="audience-card teams reveal">
          <span class="audience-eyebrow">For content teams</span>
          <h3>Click. Type. Save.</h3>
          <p>The visual editor uses TinyMCE for rich text, drag-to-reorder for blocks, and a media picker that hits the global library. Nothing to learn, nothing to break.</p>
          <ul class="audience-list">
            <li><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg> Rich text with style controls, links, embeds</li>
            <li><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg> Drag-to-reorder for custom-field blocks</li>
            <li><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg> Media picker pulls from the global library</li>
            <li><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg> Draft + scheduled publishing</li>
            <li><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg> Auto-sanitised HTML — no broken markup, ever</li>
          </ul>
        </div>

        <div class="audience-card devs reveal">
          <span class="audience-eyebrow">For developers</span>
          <h3>Raw Blade. Monaco. Done.</h3>
          <p>When the visual editor isn't enough, drop into the Blade template for the site. Monaco editor, syntax highlighting, custom-code injection per page. Gated by RBAC so juniors can't ship JS.</p>
          <ul class="audience-list">
            <li><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg> Edit any Blade template from the admin</li>
            <li><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg> Monaco-powered code injection (CSS, JS, scripts)</li>
            <li><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg> <code style="font-family:var(--font-mono);color:#67e8f9;">input_value()</code> helper to read custom fields</li>
            <li><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg> Gated by <code style="font-family:var(--font-mono);color:#67e8f9;">manage custom code</code> permission</li>
            <li><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg> Edit shipping templates without redeploying</li>
          </ul>
        </div>
      </div>
    </div>
  </section>

  {{-- ============ FIELD TYPES ============ --}}
  <section id="fields">
    <div class="container-wide">
      <div class="section-header">
        <span class="eyebrow">Polymorphic custom fields</span>
        <h2>Eight field types.<br>One <span class="text-gradient">composable system</span>.</h2>
        <p>Attach any field to any Site, Category, or Article. Reusable templates mean no schema migrations when content evolves.</p>
      </div>

      <div class="field-types">
        @foreach([
          ['Text',         'Single-line strings — titles, labels, captions.',                'M5 7h14M5 12h14M5 17h8', 'text'],
          ['Number',       'Integers or decimals with optional min/max validation.',          'M3 17l6-6 4 4 8-8', 'number'],
          ['Rich text',    'TinyMCE-powered HTML with auto-sanitisation via safe_html().',    'M4 6h16M4 12h10M4 18h16', 'wysiwyg'],
          ['File',         'Single or multi-file upload with MIME allowlist.',                'M14.5 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V7.5L14.5 2z', 'file'],
          ['Gallery',      'Link a Media gallery — auto-renders responsive grid on output.',  'M3 3h18v18H3zM8.5 8.5l3 3L15 9l5 6', 'gallery'],
          ['Form',         'Attach a Forms-module form — submissions land in the admin.',     'M9 11l3 3L22 4', 'form'],
          ['Boolean',      'Yes/no toggle with custom labels for true/false.',                'M3 12h6M15 12h6M12 9v6', 'enabled'],
          ['JSON',         'Free-form structured data with admin syntax highlighting.',       'M16 18l6-6-6-6M8 6l-6 6 6 6', 'meta'],
        ] as $f)
          <div class="field-type reveal">
            <div class="field-type-ico">
              <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="{{ $f[2] }}"/></svg>
            </div>
            <h4>{{ $f[0] }}</h4>
            <p>{{ $f[1] }}</p>
            <span class="var">input_value('{{ $f[3] }}')</span>
          </div>
        @endforeach
      </div>
    </div>
  </section>

  {{-- ============ CODE WINDOW — Blade integration ============ --}}
  <section>
    <div class="container-wide">
      <div class="split">
        <div>
          <span class="eyebrow">Blade integration</span>
          <h2 style="margin-bottom: var(--space-md);">Read any field<br>in <span class="text-gradient">one line</span>.</h2>
          <p style="color: var(--color-text-secondary); font-size: var(--text-lg); margin-bottom: var(--space-xl); line-height: 1.65;">
            The <code style="font-family:var(--font-mono);color:#67e8f9;">input_value()</code> helper walks the
            Site → Category → Article hierarchy, returning the most specific value with a graceful fallback.
            Helpers like <code style="font-family:var(--font-mono);color:#67e8f9;">safe_html()</code> and
            <code style="font-family:var(--font-mono);color:#67e8f9;">contact_form_from_instance()</code>
            handle the rest.
          </p>

          <div style="display: grid; gap: 0.6rem;">
            <div style="display: flex; gap: 0.75rem; align-items: flex-start; padding: 0.85rem 1rem; background: var(--color-surface); border: 1px solid var(--color-border); border-radius: var(--radius-md);">
              <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#34d399" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
              <div style="font-size: 0.88rem; color: var(--color-text-secondary); line-height: 1.55;">
                <strong style="color: var(--color-text-primary); display: block; margin-bottom: 2px;">No raw DB queries</strong>
                The helper hides the InputInstance lookup.
              </div>
            </div>
            <div style="display: flex; gap: 0.75rem; align-items: flex-start; padding: 0.85rem 1rem; background: var(--color-surface); border: 1px solid var(--color-border); border-radius: var(--radius-md);">
              <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#34d399" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
              <div style="font-size: 0.88rem; color: var(--color-text-secondary); line-height: 1.55;">
                <strong style="color: var(--color-text-primary); display: block; margin-bottom: 2px;">Cascading defaults</strong>
                Article overrides Category overrides Site.
              </div>
            </div>
            <div style="display: flex; gap: 0.75rem; align-items: flex-start; padding: 0.85rem 1rem; background: var(--color-surface); border: 1px solid var(--color-border); border-radius: var(--radius-md);">
              <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#34d399" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
              <div style="font-size: 0.88rem; color: var(--color-text-secondary); line-height: 1.55;">
                <strong style="color: var(--color-text-primary); display: block; margin-bottom: 2px;">Auto-sanitised output</strong>
                Use <code style="font-family:var(--font-mono);color:#67e8f9;">safe_html()</code> for unescaped rich-text rendering.
              </div>
            </div>
          </div>
        </div>

        <div>
          <div class="code-window">
            <div class="code-window-header">
              <span class="preview-dot"></span><span class="preview-dot"></span><span class="preview-dot"></span>
              <span class="code-window-title">resources/views/pages/frontend/sites/lookbook.blade.php</span>
            </div>
@verbatim
<pre><span class="kw">@extends</span>(<span class="str">'pages.frontend.base'</span>)

<span class="kw">@section</span>(<span class="str">'content'</span>)
  <span class="punct">&lt;</span><span class="fn">section</span> <span class="fn">class</span>=<span class="str">"hero"</span><span class="punct">&gt;</span>
    <span class="punct">&lt;</span><span class="fn">h1</span><span class="punct">&gt;</span><span class="punct">{{</span> <span class="fn">input_value</span>(<span class="str">'headline'</span>, <span class="punct">$site</span>) <span class="punct">}}</span><span class="punct">&lt;/</span><span class="fn">h1</span><span class="punct">&gt;</span>

    <span class="com">{{-- Rich-text — sanitised --}}</span>
    <span class="punct">{!!</span> <span class="fn">safe_html</span>(<span class="fn">input_value</span>(<span class="str">'intro'</span>, <span class="punct">$site</span>)) <span class="punct">!!}</span>

    <span class="com">{{-- Cascade: article → category → site --}}</span>
    <span class="punct">@php</span> <span class="punct">$hero</span> = <span class="fn">input_value</span>(<span class="str">'hero_image'</span>, <span class="punct">$site</span>, <span class="punct">$category</span>, <span class="punct">$article</span>)<span class="punct">; @endphp</span>
    <span class="kw">@if</span>(<span class="punct">$hero</span> &amp;&amp; <span class="punct">$hero</span>-&gt;files-&gt;<span class="fn">count</span>())
      <span class="punct">&lt;</span><span class="fn">img</span> <span class="fn">src</span>=<span class="str">"{{</span> <span class="fn">asset</span>(<span class="punct">$hero</span>-&gt;files-&gt;<span class="fn">first</span>()-&gt;file_path) <span class="str">}}"</span><span class="punct">&gt;</span>
    <span class="kw">@endif</span>

    <span class="com">{{-- Contact form from a Form-type field --}}</span>
    <span class="punct">{!!</span> <span class="fn">contact_form_from_instance</span>(<span class="fn">input_value</span>(<span class="str">'contact'</span>, <span class="punct">$site</span>)) <span class="punct">!!}</span>
  <span class="punct">&lt;/</span><span class="fn">section</span><span class="punct">&gt;</span>
<span class="kw">@stop</span></pre>
@endverbatim
          </div>
        </div>
      </div>
    </div>
  </section>

  {{-- ============ WORKFLOW ============ --}}
  <section>
    <div class="container-wide">
      <div class="section-header">
        <span class="eyebrow">From draft to live</span>
        <h2>The whole publishing flow<br>in <span class="text-gradient">four moves</span>.</h2>
      </div>

      <div class="workflow">
        <div class="workflow-step reveal">
          <div class="workflow-step-num">STEP 01</div>
          <h4>Pick a template</h4>
          <p>Site, Category and Article each carry a custom <code style="font-family:var(--font-mono);font-size:0.8em;color:#67e8f9;">template</code> field — switch layouts without redeploying.</p>
        </div>
        <div class="workflow-step reveal">
          <div class="workflow-step-num">STEP 02</div>
          <h4>Fill the fields</h4>
          <p>Custom fields render in a tabbed editor. Text, rich text, media, JSON — each gets the right input.</p>
        </div>
        <div class="workflow-step reveal">
          <div class="workflow-step-num">STEP 03</div>
          <h4>Draft or schedule</h4>
          <p>Status: <code style="font-family:var(--font-mono);font-size:0.8em;color:#67e8f9;">draft</code> / <code style="font-family:var(--font-mono);font-size:0.8em;color:#67e8f9;">scheduled</code> / <code style="font-family:var(--font-mono);font-size:0.8em;color:#67e8f9;">published</code> / <code style="font-family:var(--font-mono);font-size:0.8em;color:#67e8f9;">private</code>. Pick a publish-at date and the scheduler does the rest.</p>
        </div>
        <div class="workflow-step reveal">
          <div class="workflow-step-num">STEP 04</div>
          <h4>Preview &amp; ship</h4>
          <p>Preview as published, drafted or admin-only. One click and it's live — soft-deleted if you change your mind.</p>
        </div>
      </div>
    </div>
  </section>

  {{-- ============ CTA ============ --}}
  <section>
    <div class="container">
      <div class="cta-banner reveal">
        <span class="badge badge-pink mb-md">Editor demo</span>
        <h2>Open a page. Click around.<br>The team will <span class="text-gradient">get it instantly</span>.</h2>
        <p>The demo lets you walk through a real article in the editor — visual mode and Blade mode both.</p>
        <div class="hero-buttons">
          <button type="button" class="btn btn-primary btn-lg btn-icon-after" data-demo-open>
            Open the editor in the demo
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14M13 6l6 6-6 6"/></svg>
          </button>
          <a href="/blog" class="btn btn-secondary btn-lg">Read about the blog engine</a>
        </div>
      </div>
    </div>
  </section>

@stop
