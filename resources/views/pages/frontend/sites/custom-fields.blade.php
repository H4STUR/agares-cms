@extends('pages.frontend.base')

@push('styles')
<style>
  /* ============ Custom fields page extras ============ */

  /* Hierarchy diagram (Site → Category → Article) */
  .cf-hierarchy {
    display: grid;
    grid-template-columns: 1fr;
    gap: var(--space-md);
    padding: clamp(1.5rem, 3vw, 2.5rem);
    background: var(--color-bg-code);
    border: 1px solid var(--color-border-hover);
    border-radius: var(--radius-2xl);
    box-shadow: var(--shadow-xl);
  }
  .cf-tier {
    padding: var(--space-md) var(--space-lg);
    background: linear-gradient(180deg, rgba(255, 255, 255, 0.04), rgba(255, 255, 255, 0.015));
    border: 1px solid var(--color-border-hover);
    border-radius: var(--radius-md);
    display: grid;
    grid-template-columns: auto 1fr auto;
    gap: var(--space-md);
    align-items: center;
    transition: all var(--transition-base);
  }
  .cf-tier:hover { border-color: var(--color-border-strong); }
  .cf-tier-num {
    display: inline-flex; align-items: center; justify-content: center;
    width: 36px; height: 36px;
    background: var(--color-accent-gradient);
    border-radius: var(--radius-sm);
    color: white;
    font-family: var(--font-display);
    font-weight: 700;
    font-size: 0.95rem;
  }
  .cf-tier-name { font-family: var(--font-display); font-size: 1rem; font-weight: 600; letter-spacing: -0.01em; }
  .cf-tier-name code { display: block; font-family: var(--font-mono); font-size: 0.75rem; color: var(--color-accent-secondary); font-weight: 400; margin-top: 0.2rem; }
  .cf-tier-meta { font-family: var(--font-mono); font-size: 0.72rem; color: var(--color-text-tertiary); text-align: right; }
  .cf-arrow {
    text-align: center;
    color: var(--color-text-tertiary);
    font-family: var(--font-mono);
    font-size: 0.78rem;
  }

  /* Field-type grid (same look as page-editor) */
  .cf-types {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: var(--space-md);
  }
  @media (max-width: 1000px) { .cf-types { grid-template-columns: repeat(2, 1fr); } }
  @media (max-width: 540px)  { .cf-types { grid-template-columns: 1fr; } }

  .cf-type {
    padding: var(--space-lg);
    background: var(--color-surface);
    border: 1px solid var(--color-border);
    border-radius: var(--radius-lg);
    transition: all var(--transition-base);
  }
  .cf-type:hover { border-color: var(--color-border-hover); transform: translateY(-2px); }
  .cf-type-ico {
    width: 36px; height: 36px;
    display: inline-flex; align-items: center; justify-content: center;
    background: rgba(139, 92, 246, 0.12);
    border: 1px solid rgba(139, 92, 246, 0.25);
    color: #c4b5fd;
    border-radius: var(--radius-sm);
    margin-bottom: var(--space-md);
  }
  .cf-type h4 { font-family: var(--font-display); font-size: 0.98rem; margin-bottom: 0.3rem; letter-spacing: -0.01em; }
  .cf-type p { font-size: 0.8rem; color: var(--color-text-tertiary); margin: 0 0 0.5rem; line-height: 1.5; }
  .cf-type .var {
    display: inline-block;
    font-family: var(--font-mono);
    font-size: 0.72rem;
    color: #67e8f9;
  }
</style>
@endpush

@section('content')

  {{-- ============ HERO ============ --}}
  <section class="hero">
    <div class="container">
      <div class="hero-eyebrow">
        <span class="pill">CUSTOM FIELDS</span>
        <span>Polymorphic · cascading · template-driven · no migrations</span>
      </div>

      <h1 class="hero-title">
        Add a field.<br>
        <span class="text-gradient-magic">Not a migration.</span>
      </h1>

      <p class="hero-subtitle">
        The Input System is a polymorphic custom-field layer: define a field once in a template,
        attach it to any Site / Category / Article. Values cascade from the most specific to
        the least. Eight field types, zero schema migrations.
      </p>

      <div class="hero-buttons">
        <button type="button" class="btn btn-primary btn-lg btn-icon-after" data-demo-open>
          See the field templates
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14M13 6l6 6-6 6"/></svg>
        </button>
        <a href="/page-editor" class="btn btn-secondary btn-lg">
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="18" height="18" rx="2"/><path d="M3 9h18M9 21V9"/></svg>
          See the page editor
        </a>
      </div>

      <div class="hero-stats">
        <div class="hero-stat"><div class="num">8</div><div class="label">Field types</div></div>
        <div class="hero-stat"><div class="num">3</div><div class="label">Hierarchy levels</div></div>
        <div class="hero-stat"><div class="num">∞</div><div class="label">Fields per owner</div></div>
        <div class="hero-stat"><div class="num">0</div><div class="label">Schema migrations needed</div></div>
      </div>
    </div>
  </section>

  {{-- ============ HIERARCHY DIAGRAM ============ --}}
  <section style="padding-top: var(--space-2xl);">
    <div class="container">
      <div class="section-header">
        <span class="eyebrow">The mental model</span>
        <h2>Three layers.<br><span class="text-gradient">Always cascading</span>.</h2>
        <p>Every field value lives on the most specific owner you set it on, and falls back through the hierarchy when missing.</p>
      </div>

      <div class="cf-hierarchy reveal">
        <div class="cf-tier">
          <span class="cf-tier-num">1</span>
          <div class="cf-tier-name">Site<code>InputInstance::owner_type = App\Models\Site</code></div>
          <span class="cf-tier-meta">Site-wide defaults<br>(brand name, OG image, theme)</span>
        </div>
        <div class="cf-arrow">↓ cascade ↓</div>
        <div class="cf-tier">
          <span class="cf-tier-num">2</span>
          <div class="cf-tier-name">Category<code>InputInstance::owner_type = App\Models\Category</code></div>
          <span class="cf-tier-meta">Category overrides<br>(category hero, category meta)</span>
        </div>
        <div class="cf-arrow">↓ cascade ↓</div>
        <div class="cf-tier">
          <span class="cf-tier-num">3</span>
          <div class="cf-tier-name">Article<code>InputInstance::owner_type = App\Models\Article</code></div>
          <span class="cf-tier-meta">Article-specific values<br>(hero image, author bio, custom JSON)</span>
        </div>
      </div>

      <p style="text-align: center; margin-top: var(--space-md); font-family: var(--font-mono); font-size: 0.78rem; color: var(--color-text-tertiary);">
        Helper: <code style="color:#c4b5fd;">input_value('hero_image', $site, $category, $article)</code> — returns the article value if set, else category, else site.
      </p>
    </div>
  </section>

  {{-- ============ SPLIT: HOW IT WORKS ============ --}}
  <section>
    <div class="container-wide">
      <div class="split">
        <div>
          <span class="eyebrow">How it works</span>
          <h2 style="margin-bottom: var(--space-md);">Define once.<br>Attach <span class="text-gradient">anywhere</span>.</h2>
          <p style="color: var(--color-text-secondary); font-size: var(--text-lg); margin-bottom: var(--space-xl); line-height: 1.65;">
            An <code style="font-family:var(--font-mono);color:#67e8f9;">InputTemplate</code> defines a reusable set of field slots
            (a "recipe template" might be: ingredients, prep time, hero shot). Attach the template to any owner
            and the admin auto-renders the right inputs.
          </p>

          <div style="display: grid; gap: 0.6rem; margin-bottom: var(--space-xl);">
            @foreach([
              ['InputTemplate', 'Reusable bundle of field slots, scoped to a site. Versioned independently of content.'],
              ['InputTemplateItem', 'A single slot inside a template — type + label + variable name + placeholder + required flag + sort order.'],
              ['InputInstance', 'Actual stored value — bound polymorphically to a Site / Category / Article via owner_type + owner_id.'],
              ['InputField', 'Field-type registry — text, number, rich text, gallery, file, form, boolean, JSON.'],
            ] as $row)
              <div style="display: grid; grid-template-columns: 180px 1fr; gap: 1rem; padding: 1rem; background: var(--color-surface); border: 1px solid var(--color-border); border-radius: var(--radius-md);">
                <div style="font-family: var(--font-mono); font-size: 0.82rem; color: #67e8f9;">{{ $row[0] }}</div>
                <div style="font-size: 0.88rem; color: var(--color-text-secondary); line-height: 1.55;">{{ $row[1] }}</div>
              </div>
            @endforeach
          </div>
        </div>

        <div>
          <div class="code-window">
            <div class="code-window-header">
              <span class="preview-dot"></span><span class="preview-dot"></span><span class="preview-dot"></span>
              <span class="code-window-title">Define once → use everywhere</span>
            </div>
@verbatim
<pre><span class="com">// 1. Admin creates an InputTemplate named "Recipe"</span>
<span class="com">//    with 3 InputTemplateItems: title, prep_time, hero_image</span>

<span class="com">// 2. Apply the template to any owner — admin clicks "Apply"</span>
<span class="kw">app</span>(<span class="kw">InputInstanceService</span>::<span class="kw">class</span>)
    -&gt;<span class="fn">apply</span>(<span class="punct">$article</span>, <span class="kw">InputTemplate</span>::<span class="fn">find</span>(<span class="num">3</span>))<span class="punct">;</span>

<span class="com">// 3. Three InputInstance rows are auto-created on the article</span>
<span class="com">//    The admin renders the right input per type. No schema change.</span>

<span class="com">// 4. Read it in Blade — cascade lookup</span>
<span class="punct">@php</span> <span class="punct">$prep</span> = <span class="fn">input_value</span>(<span class="str">'prep_time'</span>, <span class="punct">$site</span>, <span class="punct">$category</span>, <span class="punct">$article</span>)<span class="punct">; @endphp</span>

<span class="kw">@if</span>(<span class="punct">$prep</span>)
  <span class="punct">&lt;</span><span class="fn">span</span> <span class="fn">class</span>=<span class="str">"meta"</span><span class="punct">&gt;</span>Prep: <span class="punct">{{</span> <span class="punct">$prep</span>-&gt;value <span class="punct">}}</span> min<span class="punct">&lt;/</span><span class="fn">span</span><span class="punct">&gt;</span>
<span class="kw">@endif</span></pre>
@endverbatim
          </div>
        </div>
      </div>
    </div>
  </section>

  {{-- ============ EIGHT FIELD TYPES ============ --}}
  <section>
    <div class="container-wide">
      <div class="section-header">
        <span class="eyebrow">Eight field types</span>
        <h2>Every shape of content,<br><span class="text-gradient">one consistent API</span>.</h2>
        <p>Same <code style="font-family:var(--font-mono);color:#67e8f9;">input_value()</code> helper returns the right thing per type — string, array, related media, parsed JSON.</p>
      </div>

      <div class="cf-types">
        @foreach([
          ['Text',         'Single-line strings.',                                'M5 7h14M5 12h14M5 17h8', 'text'],
          ['Number',       'Int or decimal, min/max validation.',                 'M3 17l6-6 4 4 8-8', 'number'],
          ['Rich text',    'TinyMCE → sanitised via safe_html().',                'M4 6h16M4 12h10M4 18h16', 'wysiwyg'],
          ['File',         'One or many; MIME allowlist enforced.',               'M14.5 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V7.5L14.5 2z', 'file'],
          ['Gallery',      'Reference to a Media gallery — renders responsive grid.', 'M3 3h18v18H3zM8.5 8.5l3 3L15 9l5 6', 'gallery'],
          ['Form',         'Picks a Forms-module form. contact_form_from_instance() renders it.', 'M9 11l3 3L22 4', 'form'],
          ['Boolean',      'Yes/no toggle with custom labels.',                   'M3 12h6M15 12h6M12 9v6', 'enabled'],
          ['JSON',         'Free-form structured data, validated against schema.', 'M16 18l6-6-6-6M8 6l-6 6 6 6', 'meta'],
        ] as $f)
          <div class="cf-type reveal">
            <div class="cf-type-ico">
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

  {{-- ============ WHY ALSO POLYMORPHIC ============ --}}
  <section>
    <div class="container">
      <div class="cta-banner reveal" style="text-align: left;">
        <div style="display: grid; grid-template-columns: 1.4fr 1fr; gap: var(--space-2xl); align-items: center;">
          <div>
            <span class="badge badge-primary mb-md">Why polymorphic</span>
            <h2 style="font-size: clamp(1.75rem, 3vw, 2.25rem); margin-bottom: var(--space-md);">No <code style="font-family:var(--font-mono);">article_meta</code>,<br>no <code style="font-family:var(--font-mono);">category_extra</code>,<br><span class="text-gradient">no extra tables</span>.</h2>
            <p style="color: var(--color-text-secondary); margin: 0; line-height: 1.65; font-size: var(--text-base);">
              One <code style="font-family:var(--font-mono);color:#67e8f9;">input_instances</code> table covers every custom-field value across every content type.
              <code style="font-family:var(--font-mono);color:#67e8f9;">owner_type</code> + <code style="font-family:var(--font-mono);color:#67e8f9;">owner_id</code> point to whatever model the value lives on.
              Adding new content types (Forum posts? FAQ entries?) means zero schema work.
            </p>
          </div>
          <div style="display: flex; flex-direction: column; gap: 0.5rem;">
            <span class="badge badge-primary">1 table</span>
            <span class="badge badge-primary">Any owner type</span>
            <span class="badge badge-primary">Zero migrations on add</span>
            <span class="badge badge-primary">Soft-deletable</span>
          </div>
        </div>
      </div>
    </div>
  </section>

  {{-- ============ CTA ============ --}}
  <section>
    <div class="container">
      <div class="cta-banner reveal">
        <span class="badge badge-cyan mb-md">Try the system</span>
        <h2>Build a template.<br>Apply it to any article.<br><span class="text-gradient">Watch the form materialise</span>.</h2>
        <p>The demo lets you create a template, apply it to an article, and see Blade pick up the values via input_value() — without writing a single migration.</p>
        <div class="hero-buttons">
          <button type="button" class="btn btn-primary btn-lg btn-icon-after" data-demo-open>
            Open templates in admin
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14M13 6l6 6-6 6"/></svg>
          </button>
          <a href="/documentation" class="btn btn-secondary btn-lg">Read the Input System docs</a>
        </div>
      </div>
    </div>
  </section>

@stop
